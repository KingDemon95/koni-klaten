<?php

namespace App\Http\Controllers;

use App\Models\Surat;
use App\Models\SuratActivity;
use App\Models\SuratMessage;
use App\Support\WorkflowNotifier;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class SuratController extends Controller
{
    public function index(Request $request): View
    {
        $prefix = $this->routePrefix($request);
        $searchInput = $request->query('q', '');
        $statusInput = $request->query('status', '');

        abort_unless(is_string($searchInput) && is_string($statusInput), 422);
        abort_unless(in_array($statusInput, array_merge([''], array_keys(Surat::statusLabels())), true), 422);

        $search = mb_substr(trim($searchInput), 0, 100);
        $status = $statusInput;
        $baseQuery = $this->queryForUser($request);
        $query = clone $baseQuery;

        if ($search !== '') {
            $query->where(function (Builder $nested) use ($search): void {
                $nested->where('perihal', 'like', "%{$search}%")
                    ->orWhere('nomor_surat', 'like', "%{$search}%")
                    ->orWhereHas('cabangOlahraga', fn (Builder $cabor) => $cabor->where('nama', 'like', "%{$search}%"));
            });
        }

        if ($status !== '') {
            $query->where('status', $status);
        }

        $items = $query
            ->with(['cabangOlahraga', 'creator', 'staff', 'decisionMaker'])
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $stats = [
            'total' => (clone $baseQuery)->count(),
            'menunggu' => (clone $baseQuery)->whereIn('status', [
                Surat::STATUS_DIAJUKAN,
                Surat::STATUS_DITERUSKAN,
                Surat::STATUS_PERLU_REVISI_STAFF,
                Surat::STATUS_MENUNGGU_PENYAMPAIAN,
            ])->count(),
            'revisi' => (clone $baseQuery)->where('status', Surat::STATUS_REVISI)->count(),
            'selesai' => (clone $baseQuery)->whereIn('status', [Surat::STATUS_DITERIMA, Surat::STATUS_DITOLAK])->count(),
        ];

        return view('surat.index', $this->viewData($request) + compact('items', 'search', 'status', 'stats', 'prefix'));
    }

    public function create(Request $request): View
    {
        abort_unless($this->routePrefix($request) === 'cabor', 403);

        return view('surat.create', $this->viewData($request) + ['surat' => new Surat()]);
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless($this->routePrefix($request) === 'cabor', 403);

        $cabor = $request->user()->cabangOlahraga;
        abort_unless($cabor, 403, 'Akun Admin Cabor belum terhubung dengan cabang olahraga.');

        $validated = $request->validate($this->rules(true));
        $file = $request->file('file');
        $path = $file->store('surat/original', 'local');

        try {
            $surat = DB::transaction(function () use ($request, $validated, $file, $path, $cabor): Surat {
                $surat = Surat::create([
                    'cabang_olahraga_id' => $cabor->id,
                    'created_by' => $request->user()->id,
                    'perihal' => $validated['perihal'],
                    'jenis_surat' => $validated['jenis_surat'],
                    'nomor_surat' => $validated['nomor_surat'] ?? null,
                    'tanggal_surat' => $validated['tanggal_surat'],
                    'ringkasan' => $validated['ringkasan'] ?? null,
                    'file_path' => $path,
                    'file_original_name' => $file->getClientOriginalName(),
                    'status' => Surat::STATUS_DRAFT,
                ]);

                $this->recordActivity($surat, $request, 'surat_dibuat', null, Surat::STATUS_DRAFT, 'Surat dibuat sebagai draft.');

                return $surat;
            });
        } catch (Throwable $exception) {
            Storage::disk('local')->delete($path);
            throw $exception;
        }

        return redirect()->route('cabor.surat.show', $surat)->with('success', 'Surat berhasil disimpan sebagai draft. Periksa kembali lalu ajukan ke Staff KONI.');
    }

    public function show(Request $request, Surat $surat): View
    {
        $surat = $this->findScopedSurat($request, $surat)
            ->load(['cabangOlahraga', 'creator', 'staff', 'decisionMaker', 'messages.sender']);
        $prefix = $this->routePrefix($request);
        $activities = $surat->activities()->with('actor')->get();

        // Keputusan pimpinan KONI tidak dibuka ke Admin Cabor sebelum Staff menyampaikan hasil.
        if ($prefix === 'cabor' && $surat->status === Surat::STATUS_MENUNGGU_PENYAMPAIAN) {
            $activities = $activities->reject(fn (SuratActivity $activity) => $activity->action === 'keputusan_admin');
        }

        return view('surat.show', $this->viewData($request) + compact('surat', 'activities', 'prefix'));
    }

    public function edit(Request $request, Surat $surat): View
    {
        abort_unless($this->routePrefix($request) === 'cabor', 403);
        $surat = $this->findScopedSurat($request, $surat);
        abort_unless(in_array($surat->status, [Surat::STATUS_DRAFT, Surat::STATUS_REVISI], true), 403);

        return view('surat.edit', $this->viewData($request) + compact('surat'));
    }

    public function update(Request $request, Surat $surat): RedirectResponse
    {
        abort_unless($this->routePrefix($request) === 'cabor', 403);
        $surat = $this->findScopedSurat($request, $surat);
        abort_unless(in_array($surat->status, [Surat::STATUS_DRAFT, Surat::STATUS_REVISI], true), 403);

        $validated = $request->validate($this->rules(false));
        $from = $surat->status;
        $oldPath = $surat->file_path;
        $path = $oldPath;
        $originalName = $surat->file_original_name;
        $newPath = null;

        if ($request->hasFile('file')) {
            $newFile = $request->file('file');
            $newPath = $newFile->store('surat/original', 'local');
            $path = $newPath;
            $originalName = $newFile->getClientOriginalName();
        }

        try {
            DB::transaction(function () use ($surat, $request, $validated, $from, $path, $originalName): void {
                $surat->update([
                    'perihal' => $validated['perihal'],
                    'jenis_surat' => $validated['jenis_surat'],
                    'nomor_surat' => $validated['nomor_surat'] ?? null,
                    'tanggal_surat' => $validated['tanggal_surat'],
                    'ringkasan' => $validated['ringkasan'] ?? null,
                    'file_path' => $path,
                    'file_original_name' => $originalName,
                    'status' => Surat::STATUS_DRAFT,
                    'catatan_staff' => null,
                ]);

                $this->recordActivity($surat, $request, 'surat_diperbarui', $from, Surat::STATUS_DRAFT, 'Admin Cabor memperbarui surat.');
            });
        } catch (Throwable $exception) {
            if ($newPath) {
                Storage::disk('local')->delete($newPath);
            }
            throw $exception;
        }

        if ($newPath && $oldPath !== $newPath) {
            Storage::disk('local')->delete($oldPath);
        }

        return redirect()->route('cabor.surat.show', $surat)->with('success', 'Surat diperbarui sebagai draft. Silakan ajukan ulang kepada Staff KONI.');
    }

    public function destroy(Request $request, Surat $surat): RedirectResponse
    {
        abort_unless($this->routePrefix($request) === 'cabor', 403);
        $surat = $this->findScopedSurat($request, $surat);
        abort_unless(in_array($surat->status, [Surat::STATUS_DRAFT, Surat::STATUS_REVISI], true), 403);

        $paths = array_filter([
            $surat->file_path,
            $surat->staff_attachment_path,
            $surat->admin_response_path,
        ]);

        DB::transaction(fn () => $surat->delete());
        Storage::disk('local')->delete($paths);

        return redirect()->route('cabor.surat.index')->with('success', 'Surat berhasil dihapus.');
    }

    public function submit(Request $request, Surat $surat): RedirectResponse
    {
        abort_unless($this->routePrefix($request) === 'cabor', 403);
        $surat = $this->findScopedSurat($request, $surat);
        abort_unless(in_array($surat->status, [Surat::STATUS_DRAFT, Surat::STATUS_REVISI], true), 422);

        $from = $surat->status;
        DB::transaction(function () use ($surat, $request, $from): void {
            $surat->update([
                'status' => Surat::STATUS_DIAJUKAN,
                'diajukan_at' => now(),
                'catatan_staff' => null,
            ]);
            $this->recordActivity($surat, $request, 'surat_diajukan', $from, Surat::STATUS_DIAJUKAN, 'Surat diajukan kepada Staff KONI.');
        });

        $surat->loadMissing('cabangOlahraga');
        WorkflowNotifier::toRole(
            'staff_koni',
            'Surat baru menunggu pemeriksaan',
            'Cabang '.($surat->cabangOlahraga?->nama ?? 'olahraga').' mengajukan surat "'.$surat->perihal.'".',
            route('staff.surat.show', $surat, false),
            'persuratan',
        );

        return redirect()->route('cabor.surat.show', $surat)->with('success', 'Surat berhasil dikirim ke antrean Staff KONI.');
    }

    public function requestRevision(Request $request, Surat $surat): RedirectResponse
    {
        abort_unless($this->routePrefix($request) === 'staff', 403);
        $surat = $this->findScopedSurat($request, $surat);
        abort_unless(in_array($surat->status, [Surat::STATUS_DIAJUKAN, Surat::STATUS_PERLU_REVISI_STAFF], true), 422);

        $validated = $request->validate(['catatan_staff' => ['required', 'string', 'max:3000']]);
        $from = $surat->status;
        $oldAdminResponsePath = $from === Surat::STATUS_DIAJUKAN ? $surat->admin_response_path : null;

        DB::transaction(function () use ($surat, $request, $validated, $from): void {
            $data = [
                'status' => Surat::STATUS_REVISI,
                'staff_id' => $request->user()->id,
                'catatan_staff' => $validated['catatan_staff'],
            ];

            // Jika revisi berasal dari pemeriksaan awal Staff, tidak ada keputusan Admin yang perlu dibawa.
            if ($from === Surat::STATUS_DIAJUKAN) {
                $data += [
                    'keputusan_admin' => null,
                    'catatan_admin' => null,
                    'admin_koni_id' => null,
                    'admin_response_path' => null,
                    'admin_response_original_name' => null,
                    'diputuskan_at' => null,
                ];
            }

            $surat->update($data);
            $surat->messages()->create([
                'sender_id' => $request->user()->id,
                'sender_role' => 'staff_koni',
                'message_type' => SuratMessage::TYPE_DISPOSISI,
                'body' => $validated['catatan_staff'],
            ]);
            $this->recordActivity($surat, $request, 'revisi_diminta_staff', $from, Surat::STATUS_REVISI, $validated['catatan_staff']);
        });

        if ($oldAdminResponsePath) {
            Storage::disk('local')->delete($oldAdminResponsePath);
        }

        WorkflowNotifier::toCaborAdmins(
            (int) $surat->cabang_olahraga_id,
            'Disposisi revisi dari Staff KONI',
            'Surat "'.$surat->perihal.'" perlu diperbaiki. Catatan Staff: '.$validated['catatan_staff'],
            route('cabor.surat.show', $surat, false),
            'persuratan',
        );

        return redirect()->route('staff.surat.show', $surat)->with('success', 'Permintaan revisi berhasil dikirim kepada Admin Cabor.');
    }

    public function forward(Request $request, Surat $surat): RedirectResponse
    {
        abort_unless($this->routePrefix($request) === 'staff', 403);
        $surat = $this->findScopedSurat($request, $surat);

        // Surat yang merupakan keputusan revisi Admin harus dikembalikan ke Admin Cabor,
        // bukan boleh diteruskan kembali kepada pimpinan KONI tanpa perbaikan.
        abort_unless($surat->status === Surat::STATUS_DIAJUKAN, 422);

        $validated = $request->validate([
            'catatan_staff' => ['nullable', 'string', 'max:3000'],
            'lampiran_staff' => ['nullable', 'file', 'mimes:pdf,doc,docx,jpg,jpeg,png', 'max:10240'],
        ]);

        $oldStaffPath = $surat->staff_attachment_path;
        $oldAdminResponsePath = $surat->admin_response_path;
        $attachmentPath = null;
        $attachmentName = null;

        if ($request->hasFile('lampiran_staff')) {
            $file = $request->file('lampiran_staff');
            $attachmentPath = $file->store('surat/staff', 'local');
            $attachmentName = $file->getClientOriginalName();
        }

        try {
            DB::transaction(function () use ($surat, $request, $validated, $attachmentPath, $attachmentName): void {
                $surat->update([
                    'status' => Surat::STATUS_DITERUSKAN,
                    'staff_id' => $request->user()->id,
                    'catatan_staff' => $validated['catatan_staff'] ?? null,
                    'staff_attachment_path' => $attachmentPath,
                    'staff_attachment_original_name' => $attachmentName,
                    'diteruskan_at' => now(),
                    'keputusan_admin' => null,
                    'catatan_admin' => null,
                    'admin_response_path' => null,
                    'admin_response_original_name' => null,
                    'admin_koni_id' => null,
                    'decision_by_role' => null,
                    'diputuskan_at' => null,
                    'disampaikan_at' => null,
                ]);

                $this->recordActivity($surat, $request, 'surat_diteruskan_admin', Surat::STATUS_DIAJUKAN, Surat::STATUS_DITERUSKAN, $validated['catatan_staff'] ?? 'Surat diteruskan kepada pimpinan KONI.');
            });
        } catch (Throwable $exception) {
            if ($attachmentPath) {
                Storage::disk('local')->delete($attachmentPath);
            }
            throw $exception;
        }

        Storage::disk('local')->delete(array_filter([$oldStaffPath, $oldAdminResponsePath]));

        $notificationMessage = 'Staff KONI meneruskan surat "'.$surat->perihal.'" untuk mendapatkan keputusan pimpinan KONI.';

        WorkflowNotifier::toRole(
            'ketua_koni',
            'Surat menunggu persetujuan Ketua KONI',
            $notificationMessage,
            route('admin.persuratan.show', $surat, false),
            'persuratan',
        );
        WorkflowNotifier::toRole(
            'sekretaris_koni',
            'Surat menunggu persetujuan Sekretaris KONI',
            $notificationMessage,
            route('sekretaris.surat.show', $surat, false),
            'persuratan',
        );

        return redirect()->route('staff.surat.show', $surat)->with('success', 'Surat berhasil diteruskan kepada Ketua dan Sekretaris KONI untuk diputuskan. Admin KONI tetap dapat memantau sebagai master administrator.');
    }

    public function decide(Request $request, Surat $surat): RedirectResponse
    {
        $prefix = $this->routePrefix($request);
        abort_unless(in_array($prefix, ['admin', 'sekretaris'], true), 403);
        abort_unless($request->user()->hasAnyRole(['ketua_koni', 'sekretaris_koni']), 403, 'Hanya Ketua dan Sekretaris KONI yang dapat memberikan keputusan surat.');

        $surat = $this->findScopedSurat($request, $surat);
        abort_unless($surat->status === Surat::STATUS_DITERUSKAN, 422);

        $validated = $request->validate([
            'keputusan_admin' => ['required', 'in:diterima,ditolak,revisi'],
            'catatan_admin' => ['nullable', 'string', 'max:3000', 'required_if:keputusan_admin,ditolak,revisi'],
            'file_balasan' => ['nullable', 'file', 'mimes:pdf,doc,docx,jpg,jpeg,png', 'max:10240'],
        ]);

        $oldResponsePath = $surat->admin_response_path;
        $responsePath = $oldResponsePath;
        $responseName = $surat->admin_response_original_name;
        $newResponsePath = null;

        if ($request->hasFile('file_balasan')) {
            $file = $request->file('file_balasan');
            $newResponsePath = $file->store('surat/admin', 'local');
            $responsePath = $newResponsePath;
            $responseName = $file->getClientOriginalName();
        }

        $newStatus = $validated['keputusan_admin'] === 'revisi'
            ? Surat::STATUS_PERLU_REVISI_STAFF
            : Surat::STATUS_MENUNGGU_PENYAMPAIAN;

        $decisionRole = $request->user()->hasRole('ketua_koni')
            ? 'ketua_koni'
            : 'sekretaris_koni';
        $decisionRoleLabel = match ($decisionRole) {
            'ketua_koni' => 'Ketua KONI',
            'sekretaris_koni' => 'Sekretaris KONI',
            default => 'Admin KONI',
        };

        try {
            DB::transaction(function () use ($surat, $request, $validated, $responsePath, $responseName, $newStatus, $decisionRole, $decisionRoleLabel): void {
                // Kunci baris agar keputusan Ketua dan Sekretaris tidak dapat diproses bersamaan.
                $lockedSurat = Surat::query()->lockForUpdate()->findOrFail($surat->id);
                abort_unless($lockedSurat->status === Surat::STATUS_DITERUSKAN, 422, 'Surat sudah diputuskan oleh pengguna lain.');

                $lockedSurat->update([
                    'status' => $newStatus,
                    'keputusan_admin' => $validated['keputusan_admin'],
                    'catatan_admin' => $validated['catatan_admin'] ?? null,
                    'admin_response_path' => $responsePath,
                    'admin_response_original_name' => $responseName,
                    'admin_koni_id' => $request->user()->id,
                    'decision_by_role' => $decisionRole,
                    'diputuskan_at' => now(),
                ]);

                $this->recordActivity(
                    $lockedSurat,
                    $request,
                    'keputusan_admin',
                    Surat::STATUS_DITERUSKAN,
                    $newStatus,
                    $validated['catatan_admin'] ?? ('Keputusan '.$decisionRoleLabel.': '.$validated['keputusan_admin'].'.'),
                );
            });
        } catch (Throwable $exception) {
            if ($newResponsePath) {
                Storage::disk('local')->delete($newResponsePath);
            }
            throw $exception;
        }

        if ($newResponsePath && $oldResponsePath && $oldResponsePath !== $newResponsePath) {
            Storage::disk('local')->delete($oldResponsePath);
        }

        $surat->loadMissing('staff');
        $decisionLabel = match ($validated['keputusan_admin']) {
            'diterima' => 'disetujui',
            'ditolak' => 'ditolak',
            default => 'memerlukan revisi',
        };
        WorkflowNotifier::toUser(
            $surat->staff,
            'Keputusan surat tersedia',
            $decisionRoleLabel.' telah memutuskan surat "'.$surat->perihal.'": '.$decisionLabel.'. Silakan tindak lanjuti dan sampaikan hasilnya.',
            route('staff.surat.show', $surat, false),
            'persuratan',
        );

        $otherApproverRole = match ($decisionRole) {
            'ketua_koni' => 'sekretaris_koni',
            'sekretaris_koni' => 'ketua_koni',
            default => null,
        };
        if ($otherApproverRole) {
            $otherApproverUrl = $otherApproverRole === 'ketua_koni'
                ? route('admin.persuratan.show', $surat, false)
                : route('sekretaris.surat.show', $surat, false);

            WorkflowNotifier::toRole(
                $otherApproverRole,
                'Surat telah diputuskan',
                $decisionRoleLabel.' telah memberikan keputusan atas surat "'.$surat->perihal.'". Tidak diperlukan persetujuan kedua.',
                $otherApproverUrl,
                'persuratan',
            );
        }

        $message = $validated['keputusan_admin'] === 'revisi'
            ? 'Keputusan revisi disimpan. Staff KONI perlu meneruskan catatan kepada Admin Cabor.'
            : 'Keputusan '.$decisionRoleLabel.' tersimpan. Staff KONI perlu menyampaikan hasil kepada Admin Cabor.';
        $detailRoute = $prefix === 'sekretaris' ? 'sekretaris.surat.show' : 'admin.persuratan.show';

        return redirect()->route($detailRoute, $surat)->with('success', $message);
    }

    public function deliver(Request $request, Surat $surat): RedirectResponse
    {
        abort_unless($this->routePrefix($request) === 'staff', 403);
        $surat = $this->findScopedSurat($request, $surat);
        abort_unless(
            $surat->status === Surat::STATUS_MENUNGGU_PENYAMPAIAN
                && in_array($surat->keputusan_admin, ['diterima', 'ditolak'], true),
            422
        );

        $validated = $request->validate([
            'catatan_disposisi' => ['nullable', 'string', 'max:3000'],
            'lampiran_disposisi' => ['nullable', 'file', 'mimes:pdf,doc,docx,jpg,jpeg,png', 'max:10240'],
        ]);

        $attachmentPath = null;
        $attachmentName = null;

        if ($request->hasFile('lampiran_disposisi')) {
            $file = $request->file('lampiran_disposisi');
            $attachmentPath = $file->store('surat/disposisi', 'local');
            $attachmentName = $file->getClientOriginalName();
        }

        $from = $surat->status;
        $finalStatus = $surat->keputusan_admin === 'diterima'
            ? Surat::STATUS_DITERIMA
            : Surat::STATUS_DITOLAK;
        $defaultMessage = 'Staff KONI menyampaikan hasil keputusan pimpinan KONI: '
            .($finalStatus === Surat::STATUS_DITERIMA ? 'surat diterima.' : 'surat ditolak.');
        $dispositionBody = trim((string) ($validated['catatan_disposisi'] ?? '')) ?: $defaultMessage;

        try {
            DB::transaction(function () use ($surat, $request, $from, $finalStatus, $dispositionBody, $attachmentPath, $attachmentName): void {
                $surat->update([
                    'status' => $finalStatus,
                    'staff_id' => $request->user()->id,
                    'disampaikan_at' => now(),
                ]);

                $surat->messages()->create([
                    'sender_id' => $request->user()->id,
                    'sender_role' => 'staff_koni',
                    'message_type' => SuratMessage::TYPE_DISPOSISI,
                    'body' => $dispositionBody,
                    'attachment_path' => $attachmentPath,
                    'attachment_original_name' => $attachmentName,
                ]);

                $this->recordActivity($surat, $request, 'hasil_disampaikan', $from, $finalStatus, $dispositionBody);
            });
        } catch (Throwable $exception) {
            if ($attachmentPath) {
                Storage::disk('local')->delete($attachmentPath);
            }

            throw $exception;
        }

        WorkflowNotifier::toCaborAdmins(
            (int) $surat->cabang_olahraga_id,
            'Disposisi surat dari Staff KONI',
            'Hasil keputusan untuk surat "'.$surat->perihal.'" sudah disampaikan. Buka detail surat untuk membaca disposisi dan memberikan balasan.',
            route('cabor.surat.show', $surat, false),
            'persuratan',
        );

        return redirect()->route('staff.surat.show', $surat)->with('success', 'Hasil keputusan dan disposisi berhasil disampaikan kepada seluruh Admin Cabor terkait.');
    }

    public function sendDisposition(Request $request, Surat $surat): RedirectResponse
    {
        abort_unless($this->routePrefix($request) === 'staff', 403);
        $surat = $this->findScopedSurat($request, $surat);
        abort_unless(in_array($surat->status, [
            Surat::STATUS_REVISI,
            Surat::STATUS_DITERIMA,
            Surat::STATUS_DITOLAK,
        ], true), 422);

        $validated = $request->validate([
            'body' => ['required', 'string', 'max:3000'],
            'attachment' => ['nullable', 'file', 'mimes:pdf,doc,docx,jpg,jpeg,png', 'max:10240'],
        ]);

        $validated['body'] = trim($validated['body']);
        if ($validated['body'] === '') {
            return back()->withErrors(['body' => 'Isi pesan disposisi wajib diisi.'])->withInput();
        }

        $path = null;
        $originalName = null;
        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $path = $file->store('surat/pesan', 'local');
            $originalName = $file->getClientOriginalName();
        }

        try {
            DB::transaction(function () use ($surat, $request, $validated, $path, $originalName): void {
                $surat->messages()->create([
                    'sender_id' => $request->user()->id,
                    'sender_role' => 'staff_koni',
                    'message_type' => SuratMessage::TYPE_DISPOSISI,
                    'body' => $validated['body'],
                    'attachment_path' => $path,
                    'attachment_original_name' => $originalName,
                ]);

                $this->recordActivity($surat, $request, 'disposisi_dikirim_ke_cabor', $surat->status, $surat->status, $validated['body']);
            });
        } catch (Throwable $exception) {
            if ($path) {
                Storage::disk('local')->delete($path);
            }

            throw $exception;
        }

        WorkflowNotifier::toCaborAdmins(
            (int) $surat->cabang_olahraga_id,
            'Pesan baru dari Staff KONI',
            'Staff KONI mengirim pesan untuk surat "'.$surat->perihal.'".',
            route('cabor.surat.show', $surat, false),
            'persuratan',
        );

        return redirect()->route('staff.surat.show', $surat)->with('success', 'Pesan disposisi berhasil dikirim kepada Admin Cabor.');
    }

    public function reply(Request $request, Surat $surat): RedirectResponse
    {
        $prefix = $this->routePrefix($request);
        $user = $request->user();
        $isCabor = $prefix === 'cabor' && $user->hasRole('admin_cabor');
        $isKetua = $prefix === 'admin' && $user->hasRole('ketua_koni');
        $isSekretaris = $prefix === 'sekretaris' && $user->hasRole('sekretaris_koni');

        // Route admin juga dapat diakses Admin Master untuk fungsi lain.
        // Balasan percakapan secara eksplisit hanya boleh dilakukan Ketua.
        abort_unless($isCabor || $isKetua || $isSekretaris, 403);

        $surat = $this->findScopedSurat($request, $surat);
        if ($isCabor) {
            abort_unless(in_array($surat->status, [
                Surat::STATUS_REVISI,
                Surat::STATUS_DITERIMA,
                Surat::STATUS_DITOLAK,
            ], true), 422);
        }

        $validated = $request->validate([
            'body' => ['nullable', 'string', 'max:3000', 'required_without:attachment'],
            'attachment' => ['nullable', 'file', 'mimes:pdf,doc,docx,jpg,jpeg,png', 'max:10240', 'required_without:body'],
        ]);

        $senderRole = $isCabor ? 'admin_cabor' : ($isKetua ? 'ketua_koni' : 'sekretaris_koni');
        $senderLabel = match ($senderRole) {
            'admin_cabor' => 'Admin Cabor',
            'ketua_koni' => 'Ketua KONI',
            'sekretaris_koni' => 'Sekretaris KONI',
        };
        $path = null;
        $originalName = null;
        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $path = $file->store('surat/balasan-'.$senderRole, 'local');
            $originalName = $file->getClientOriginalName();
        }

        $body = trim((string) ($validated['body'] ?? ''));
        if ($body === '' && ! $request->hasFile('attachment')) {
            return back()->withErrors(['body' => 'Isi komentar atau unggah dokumen balasan.'])->withInput();
        }

        try {
            DB::transaction(function () use ($surat, $request, $body, $path, $originalName, $senderRole, $senderLabel): void {
                $surat->messages()->create([
                    'sender_id' => $request->user()->id,
                    'sender_role' => $senderRole,
                    'message_type' => SuratMessage::TYPE_BALASAN,
                    'body' => $body !== '' ? $body : null,
                    'attachment_path' => $path,
                    'attachment_original_name' => $originalName,
                ]);

                $this->recordActivity(
                    $surat,
                    $request,
                    'balasan_'.$senderRole.'_dikirim',
                    $surat->status,
                    $surat->status,
                    $body !== '' ? $body : $senderLabel.' mengunggah dokumen balasan.'
                );
            });
        } catch (Throwable $exception) {
            if ($path) {
                Storage::disk('local')->delete($path);
            }

            throw $exception;
        }

        $notificationTitle = 'Balasan baru dari '.$senderLabel;
        $notificationBody = $senderLabel.' mengirim komentar atau dokumen balasan untuk surat "'.$surat->perihal.'".';
        $staffNotificationUrl = route('staff.surat.show', $surat, false);
        $caborNotificationUrl = route('cabor.surat.show', $surat, false);

        if ($isCabor) {
            if ($surat->staff) {
                WorkflowNotifier::toUser($surat->staff, $notificationTitle, $notificationBody, $staffNotificationUrl, 'persuratan', $user->id);
            } else {
                WorkflowNotifier::toRole('staff_koni', $notificationTitle, $notificationBody, $staffNotificationUrl, 'persuratan', null, $user->id);
            }
        } else {
            WorkflowNotifier::toUser($surat->staff, $notificationTitle, $notificationBody, $staffNotificationUrl, 'persuratan', $user->id);
            WorkflowNotifier::toRole('admin_cabor', $notificationTitle, $notificationBody, $caborNotificationUrl, 'persuratan', $surat->cabang_olahraga_id, $user->id);
        }

        $redirectRoute = match ($prefix) {
            'admin' => 'admin.persuratan.show',
            'sekretaris' => 'sekretaris.surat.show',
            default => 'cabor.surat.show',
        };

        return redirect()->route($redirectRoute, $surat)->with('success', 'Balasan berhasil dikirim.');
    }

    public function downloadMessageAttachment(
        Request $request,
        Surat $surat,
        SuratMessage $message,
    ): StreamedResponse {
        $surat = $this->findScopedSurat($request, $surat);
        abort_unless((int) $message->surat_id === (int) $surat->id, 404);
        abort_unless($message->attachment_path && Storage::disk('local')->exists($message->attachment_path), 404);

        return Storage::disk('local')->download(
            $message->attachment_path,
            $message->attachment_original_name ?: basename($message->attachment_path),
        );
    }

    public function download(Request $request, Surat $surat, string $jenis): StreamedResponse
    {
        $surat = $this->findScopedSurat($request, $surat);
        $prefix = $this->routePrefix($request);
        $file = match ($jenis) {
            'asli' => [$surat->file_path, $surat->file_original_name],
            'staff' => [$surat->staff_attachment_path, $surat->staff_attachment_original_name],
            'balasan' => [$surat->admin_response_path, $surat->admin_response_original_name],
            default => abort(404),
        };

        if ($prefix === 'cabor' && $jenis === 'staff') {
            abort_unless(in_array($surat->status, [Surat::STATUS_REVISI, Surat::STATUS_DITERIMA, Surat::STATUS_DITOLAK], true), 404);
        }

        if ($prefix === 'cabor' && $jenis === 'balasan') {
            abort_unless(in_array($surat->status, [Surat::STATUS_REVISI, Surat::STATUS_DITERIMA, Surat::STATUS_DITOLAK], true), 404);
        }

        abort_unless($file[0] && Storage::disk('local')->exists($file[0]), 404);

        return Storage::disk('local')->download($file[0], $file[1] ?: basename($file[0]));
    }

    private function rules(bool $fileRequired): array
    {
        return [
            'perihal' => ['required', 'string', 'max:200'],
            'jenis_surat' => ['required', 'in:permohonan,izin,rekomendasi,pemberitahuan,lainnya'],
            'nomor_surat' => ['nullable', 'string', 'max:100'],
            'tanggal_surat' => ['required', 'date'],
            'ringkasan' => ['nullable', 'string', 'max:5000'],
            'file' => [$fileRequired ? 'required' : 'nullable', 'file', 'mimes:pdf,doc,docx,jpg,jpeg,png', 'max:10240'],
        ];
    }

    private function queryForUser(Request $request): Builder
    {
        $prefix = $this->routePrefix($request);
        $query = Surat::query()->with(['cabangOlahraga', 'creator', 'staff', 'decisionMaker']);

        if ($prefix === 'cabor') {
            $query->where('cabang_olahraga_id', $request->user()->cabang_olahraga_id)
                ->where('status', '!=', Surat::STATUS_PERLU_REVISI_STAFF);
        } elseif ($prefix === 'staff') {
            // Staff hanya menerima surat yang sudah diajukan, bukan draft internal Admin Cabor.
            $query->where('status', '!=', Surat::STATUS_DRAFT);
        } else {
            // Admin, Ketua, dan Sekretaris hanya menerima surat yang sudah diteruskan Staff dan memantau tindak lanjutnya.
            $query->whereIn('status', [
                Surat::STATUS_DITERUSKAN,
                Surat::STATUS_PERLU_REVISI_STAFF,
                Surat::STATUS_MENUNGGU_PENYAMPAIAN,
                Surat::STATUS_DITERIMA,
                Surat::STATUS_DITOLAK,
            ]);
        }

        return $query;
    }

    private function findScopedSurat(Request $request, Surat $surat): Surat
    {
        return $this->queryForUser($request)->whereKey($surat->getKey())->firstOrFail();
    }

    private function viewData(Request $request): array
    {
        $prefix = $this->routePrefix($request);

        return [
            'layout' => match ($prefix) {
                'admin' => 'layouts.admin',
                'sekretaris' => 'layouts.sekretaris',
                'staff' => 'layouts.staff',
                'cabor' => 'layouts.cabor',
            },
            'routePrefix' => $prefix,
            'routeBase' => $prefix === 'admin' ? 'admin.persuratan' : $prefix.'.surat',
            'cabor' => $prefix === 'cabor' ? $request->user()->cabangOlahraga : null,
        ];
    }

    private function routePrefix(Request $request): string
    {
        $name = $request->route()?->getName() ?? '';

        if (str_starts_with($name, 'admin.persuratan.') || str_starts_with($name, 'admin.surat.')) {
            return 'admin';
        }

        foreach (['staff', 'sekretaris', 'cabor'] as $prefix) {
            if (str_starts_with($name, $prefix.'.surat.')) {
                return $prefix;
            }
        }

        abort(403);
    }

    private function recordActivity(
        Surat $surat,
        Request $request,
        string $action,
        ?string $from,
        ?string $to,
        ?string $note = null,
    ): void {
        $surat->activities()->create([
            'actor_id' => $request->user()->id,
            'action' => $action,
            'status_from' => $from,
            'status_to' => $to,
            'catatan' => $note,
        ]);
    }
}
