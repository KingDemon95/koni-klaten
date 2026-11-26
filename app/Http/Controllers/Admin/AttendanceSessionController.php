<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AttendanceRecord;
use App\Models\AttendanceSession;
use App\Models\CabangOlahraga;
use App\Models\User;
use App\Support\QrCode\QrCodeSvg;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AttendanceSessionController extends Controller
{
    private const PARTICIPANT_ROLES = ['atlet', 'pelatih', 'atlet_pelatih'];

    private const SELECTABLE_ROLES = [
        'admin_koni',
        'staff_koni',
        'ketua_koni',
        'sekretaris_koni',
        'admin_cabor',
        'atlet',
        'pelatih',
    ];

    public function index(Request $request): View
    {
        $user = $request->user();
        $routePrefix = $this->routePrefix($user);
        $layout = $this->layoutFor($user);
        $canManage = $this->canManage($user);
        $canViewRecords = $this->canViewOrganizationRecords($user);
        $filters = $request->validate([
            'role' => ['nullable', Rule::in(self::SELECTABLE_ROLES)],
            'cabor_id' => ['nullable', 'integer', Rule::exists('cabang_olahraga', 'id')],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
        ]);

        $sessionQuery = AttendanceSession::query()
            ->with('cabangOlahraga')
            ->withCount('records')
            ->latest('starts_at');

        // Every eligible internal user sees the QR for each session they may attend.
        // The QR token remains signed and date-bound; the server still validates
        // role, cabor, session status, and schedule after the scan.
        $mySessions = AttendanceSession::query()
            ->with('cabangOlahraga')
            ->with(['records' => fn (HasMany $query) => $query->where('user_id', $user->id)])
            ->where('status', 'open')
            ->where('ends_at', '>=', now())
            ->latest('starts_at')
            ->limit(100)
            ->get()
            ->filter(fn (AttendanceSession $session): bool => $this->userCanJoinSession($user, $session))
            ->values();

        $mySessions->each(function (AttendanceSession $session): void {
            $scanUrl = $this->scanUrlFor($session);
            $session->setAttribute('scan_url', $scanUrl);
            $session->setAttribute('qr_svg', QrCodeSvg::render($scanUrl));
        });

        if ($user->hasRole('admin_cabor')) {
            $sessionQuery->where('cabang_olahraga_id', $user->cabang_olahraga_id);
        } elseif ($this->isParticipant($user)) {
            $sessionQuery->where('status', 'open')
                ->whereDate('starts_at', '<=', now()->toDateString())
                ->whereDate('ends_at', '>=', now()->toDateString());
        }

        $sessions = $this->isParticipant($user)
            ? $sessionQuery->limit(100)->get()
                ->filter(fn (AttendanceSession $session): bool => $this->participantCanJoin($user, $session))
                ->values()
            : $sessionQuery->paginate(12)->withQueryString();

        $recordsQuery = AttendanceRecord::query()
            ->with(['session', 'cabangOlahraga'])
            ->latest('checked_in_at');

        if ($this->isParticipant($user)) {
            $recordsQuery->where('user_id', $user->id);
        } elseif ($user->hasRole('admin_cabor')) {
            $recordsQuery->where('cabang_olahraga_id', $user->cabang_olahraga_id);
        }

        if ($canViewRecords) {
            if (! empty($filters['role'])) {
                $recordsQuery->where('role_snapshot', $filters['role']);
            }

            if (! empty($filters['cabor_id']) && ! $user->hasRole('admin_cabor')) {
                $recordsQuery->where('cabang_olahraga_id', $filters['cabor_id']);
            }

            if (! empty($filters['date_from'])) {
                $recordsQuery->whereDate('checked_in_at', '>=', $filters['date_from']);
            }

            if (! empty($filters['date_to'])) {
                $recordsQuery->whereDate('checked_in_at', '<=', $filters['date_to']);
            }
        }

        $summaryByRole = $canViewRecords
            ? (clone $recordsQuery)
                ->reorder()
                ->selectRaw('role_snapshot, COUNT(*) as total, SUM(CASE WHEN checked_out_at IS NOT NULL THEN 1 ELSE 0 END) as completed')
                ->groupBy('role_snapshot')
                ->orderBy('role_snapshot')
                ->get()
            : collect();

        $summaryByCabor = $canViewRecords
            ? (clone $recordsQuery)
                ->reorder()
                ->leftJoin('cabang_olahraga', 'attendance_records.cabang_olahraga_id', '=', 'cabang_olahraga.id')
                ->selectRaw("COALESCE(cabang_olahraga.nama, 'Non-Cabor') as cabor, COUNT(attendance_records.id) as total")
                ->groupBy('cabang_olahraga.id', 'cabang_olahraga.nama')
                ->orderBy('cabor')
                ->get()
            : collect();

        $records = $recordsQuery->paginate(20)->withQueryString();
        $cabors = $canViewRecords
            ? CabangOlahraga::query()->where('status', 'aktif')->orderBy('nama')->get(['id', 'nama'])
            : collect();

        return view('attendance.index', compact(
            'sessions',
            'mySessions',
            'records',
            'summaryByRole',
            'summaryByCabor',
            'cabors',
            'filters',
            'routePrefix',
            'layout',
            'canManage',
            'canViewRecords',
        ));
    }

    public function create(Request $request): View
    {
        $user = $request->user();
        abort_unless($this->canManage($user), 403);

        $cabors = $user->hasRole('admin_cabor')
            ? CabangOlahraga::query()->whereKey($user->cabang_olahraga_id)->where('status', 'aktif')->orderBy('nama')->get()
            : CabangOlahraga::query()->where('status', 'aktif')->orderBy('nama')->get();

        abort_if($user->hasRole('admin_cabor') && $cabors->isEmpty(), 403, 'Akun Admin Cabor belum terhubung dengan cabang olahraga aktif.');

        return view('attendance.create', [
            'cabors' => $cabors,
            'routePrefix' => $this->routePrefix($user),
            'layout' => $this->layoutFor($user),
            'isCaborAdmin' => $user->hasRole('admin_cabor'),
            'roles' => self::SELECTABLE_ROLES,
            'roleLabels' => $this->roleLabels(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();
        abort_unless($this->canManage($user), 403);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:160'],
            'description' => ['nullable', 'string', 'max:3000'],
            'cabang_olahraga_id' => ['nullable', 'integer', Rule::exists('cabang_olahraga', 'id')->where('status', 'aktif')],
            'target_roles' => ['nullable', 'array', 'min:1'],
            'target_roles.*' => ['required', 'string', 'distinct', Rule::in(self::SELECTABLE_ROLES)],
            'target_role' => ['nullable', Rule::in(['all', 'atlet', 'pelatih'])],
            'starts_at' => ['required', 'date'],
            'ends_at' => ['required', 'date', 'after:starts_at'],
        ]);

        if (empty($validated['target_roles']) && empty($validated['target_role'])) {
            return back()->withInput()->withErrors([
                'target_roles' => 'Pilih minimal satu role peserta yang boleh melakukan absensi.',
            ]);
        }

        if ($user->hasRole('admin_cabor')) {
            abort_unless($user->cabang_olahraga_id, 403);
            $validated['cabang_olahraga_id'] = $user->cabang_olahraga_id;
        }

        $targetRoles = array_values(array_unique($validated['target_roles'] ?? []));
        if ($targetRoles === []) {
            $targetRoles = match ($validated['target_role'] ?? 'all') {
                'atlet' => ['atlet'],
                'pelatih' => ['pelatih'],
                default => self::SELECTABLE_ROLES,
            };
        }

        $legacyTargetRole = count($targetRoles) === 1
            ? $targetRoles[0]
            : (count($targetRoles) === count(self::SELECTABLE_ROLES) ? 'all' : 'custom');

        $session = AttendanceSession::query()->create([
            'created_by' => $user->id,
            'cabang_olahraga_id' => $validated['cabang_olahraga_id'] ?? null,
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'target_role' => $legacyTargetRole,
            'target_roles' => $targetRoles,
            'token' => Str::random(48),
            'starts_at' => $validated['starts_at'],
            'ends_at' => $validated['ends_at'],
            'status' => 'open',
        ]);

        return redirect()->route($this->routePrefix($user).'.absensi.show', $session)
            ->with('success', 'Sesi absensi berhasil dibuat. QR harian siap dibagikan.');
    }

    public function show(Request $request, AttendanceSession $attendanceSession): View
    {
        $user = $request->user();
        $this->authorizeSessionView($user, $attendanceSession);

        $attendanceSession->load(['cabangOlahraga', 'creator']);
        $recordsQuery = $attendanceSession->records()
            ->with(['user', 'cabangOlahraga'])
            ->latest('checked_in_at');

        if ($user->hasRole('admin_cabor')) {
            $recordsQuery->where('cabang_olahraga_id', $user->cabang_olahraga_id);
        }

        $records = $recordsQuery->paginate(25)->withQueryString();
        $scanUrl = $this->scanUrlFor($attendanceSession);
        $qrSvg = QrCodeSvg::render($scanUrl);

        return view('attendance.show', [
            'attendanceSession' => $attendanceSession,
            'records' => $records,
            'qrSvg' => $qrSvg,
            'scanUrl' => $scanUrl,
            'routePrefix' => $this->routePrefix($user),
            'layout' => $this->layoutFor($user),
            'canManage' => $this->canManageSession($user, $attendanceSession),
            'canViewSelfies' => $user->hasAnyRole(['admin_koni', 'staff_koni', 'admin_cabor']),
        ]);
    }

    public function downloadQr(Request $request, AttendanceSession $attendanceSession)
    {
        $this->authorizeSessionView($request->user(), $attendanceSession);

        abort_unless(
            $attendanceSession->status === 'open' && $attendanceSession->ends_at->isFuture(),
            404,
            'QR tidak tersedia karena sesi sudah ditutup atau berakhir.',
        );

        $qrSvg = QrCodeSvg::render($this->scanUrlFor($attendanceSession));
        $filename = 'qr-absensi-'.Str::slug($attendanceSession->title).'-'.now()->format('Ymd').'.svg';

        return response($qrSvg, 200, [
            'Content-Type' => 'image/svg+xml; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
            'Cache-Control' => 'private, no-store',
        ]);
    }

    public function close(Request $request, AttendanceSession $attendanceSession): RedirectResponse
    {
        $user = $request->user();
        abort_unless($this->canManageSession($user, $attendanceSession), 403);

        $attendanceSession->update(['status' => 'closed']);

        return redirect()->route($this->routePrefix($user).'.absensi.show', $attendanceSession)
            ->with('success', 'Sesi absensi telah ditutup. QR tidak dapat digunakan lagi.');
    }

    public function export(Request $request, AttendanceSession $attendanceSession): StreamedResponse
    {
        $user = $request->user();
        $this->authorizeSessionView($user, $attendanceSession);

        $filename = 'rekap-absensi-'.Str::slug($attendanceSession->title).'-'.$attendanceSession->starts_at->format('Ymd').'.csv';

        return response()->streamDownload(function () use ($attendanceSession, $user): void {
            $output = fopen('php://output', 'w');
            fwrite($output, "\xEF\xBB\xBF");
            fputcsv($output, [
                'Nama Peserta',
                'Email',
                'Peran',
                'Cabang Olahraga',
                'Waktu Check-in',
                'Waktu Check-out',
                'Status',
            ]);

            $query = $attendanceSession->records()
                ->with('cabangOlahraga')
                ->orderBy('checked_in_at');

            if ($user->hasRole('admin_cabor')) {
                $query->where('cabang_olahraga_id', $user->cabang_olahraga_id);
            }

            $query->chunk(200, function ($records) use ($output): void {
                foreach ($records as $record) {
                    fputcsv($output, [
                        $this->safeCsvValue($record->participant_name),
                        $this->safeCsvValue($record->participant_email),
                        $this->safeCsvValue($this->roleLabels()[$record->role_snapshot] ?? $record->role_snapshot),
                        $this->safeCsvValue($record->cabangOlahraga?->nama ?? 'Non-Cabor'),
                        $record->checked_in_at?->format('d/m/Y H:i:s'),
                        $record->checked_out_at?->format('d/m/Y H:i:s'),
                        $record->checked_out_at ? 'Check-in dan check-out' : 'Belum check-out',
                    ]);
                }
            });

            fclose($output);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function selfie(Request $request, AttendanceRecord $attendanceRecord)
    {
        $user = $request->user();
        $attendanceRecord->loadMissing('session');
        $this->authorizeRecordView($user, $attendanceRecord);

        $kind = $request->query('kind', 'check-in');
        abort_unless(in_array($kind, ['check-in', 'check-out'], true), 404);

        $path = $kind === 'check-out'
            ? $attendanceRecord->check_out_selfie_path
            : $attendanceRecord->selfie_path;

        abort_unless($path && Storage::disk('local')->exists($path), 404);

        return Storage::disk('local')->response(
            $path,
            null,
            ['Cache-Control' => 'private, no-store'],
        );
    }

    private function canManage(User $user): bool
    {
        return $user->hasAnyRole(['admin_koni', 'staff_koni', 'admin_cabor']);
    }

    private function canViewOrganizationRecords(User $user): bool
    {
        return $user->hasAnyRole([
            'admin_koni',
            'staff_koni',
            'ketua_koni',
            'sekretaris_koni',
            'admin_cabor',
        ]);
    }

    private function isParticipant(User $user): bool
    {
        return $user->hasAnyRole(self::PARTICIPANT_ROLES);
    }

    private function canManageSession(User $user, AttendanceSession $session): bool
    {
        if (! $this->canManage($user)) {
            return false;
        }

        if ($user->hasRole('admin_cabor')) {
            return $user->cabang_olahraga_id !== null
                && (int) $session->cabang_olahraga_id === (int) $user->cabang_olahraga_id;
        }

        return true;
    }

    private function authorizeSessionView(User $user, AttendanceSession $session): void
    {
        abort_unless(
            $user->hasAnyRole(['admin_koni', 'staff_koni', 'ketua_koni', 'sekretaris_koni'])
                || ($user->hasRole('admin_cabor') && $this->canManageSession($user, $session)),
            403,
        );
    }

    private function authorizeRecordView(User $user, AttendanceRecord $record): void
    {
        if ($user->hasAnyRole(['admin_koni', 'staff_koni'])) {
            return;
        }

        if ($user->hasRole('admin_cabor')) {
            abort_unless(
                $user->cabang_olahraga_id !== null
                    && (int) $record->cabang_olahraga_id === (int) $user->cabang_olahraga_id,
                403,
            );

            return;
        }

        abort_unless(false, 403, 'Foto selfie absensi hanya dapat dilihat oleh pengelola yang berwenang.');
    }

    private function participantCanJoin(User $user, AttendanceSession $session): bool
    {
        return $this->userCanJoinSession($user, $session, true);
    }

    private function userCanJoinSession(User $user, AttendanceSession $session, bool $requireCurrentWindow = false): bool
    {
        if (
            $session->status !== 'open'
            || $session->ends_at->isPast()
            || ($requireCurrentWindow && ! $session->isWithinSchedule())
        ) {
            return false;
        }

        $userRoles = $user->getRoleNames()->all();
        if (is_array($session->target_roles) && $session->target_roles !== []) {
            if (array_intersect($userRoles, $session->target_roles) === []
                && ! (in_array('atlet_pelatih', $userRoles, true)
                    && array_intersect(['atlet', 'pelatih'], $session->target_roles) !== [])) {
                return false;
            }
        } else {
            $legacyAllowed = match ($session->target_role) {
                'atlet' => ['atlet', 'atlet_pelatih'],
                'pelatih' => ['pelatih', 'atlet_pelatih'],
                default => ['atlet', 'pelatih', 'atlet_pelatih'],
            };

            if (array_intersect($userRoles, $legacyAllowed) === []) {
                return false;
            }
        }

        if ($session->cabang_olahraga_id) {
            if ($user->hasRole('admin_cabor')) {
                return $user->cabang_olahraga_id !== null
                    && (int) $user->cabang_olahraga_id === (int) $session->cabang_olahraga_id;
            }

            if ($user->hasAnyRole(['admin_koni', 'staff_koni', 'ketua_koni', 'sekretaris_koni'])) {
                return true;
            }

            $user->loadMissing(['atlet', 'pelatih']);
            $caborIds = collect([
                $user->cabang_olahraga_id,
                $user->atlet?->cabang_olahraga_id,
                $user->pelatih?->cabang_olahraga_id,
            ])->filter()->map(fn ($id): int => (int) $id);

            return $caborIds->contains((int) $session->cabang_olahraga_id);
        }

        return true;
    }

    private function scanUrlFor(AttendanceSession $session): string
    {
        $tokenDate = in_array(
            $session->attendance_mode,
            ['work', 'training'],
            true
        ) && $session->attendance_date
            ? $session->attendance_date
            : now();

        $token = $session->dailyScanToken($tokenDate);

        $relativeUrl = route(
            'attendance.check-in.show',
            $token,
            false
        );

        $baseUrl = trim((string) config('app.attendance_base_url'));

        return $baseUrl !== ''
            ? rtrim($baseUrl, '/').'/'.ltrim($relativeUrl, '/')
            : route('attendance.check-in.show', $token);
    }

    private function routePrefix(User $user): string
    {
        return match (true) {
            $user->hasAnyRole(['admin_koni', 'ketua_koni']) => 'admin',
            $user->hasRole('staff_koni') => 'staff',
            $user->hasRole('sekretaris_koni') => 'sekretaris',
            $user->hasRole('admin_cabor') => 'cabor',
            $user->hasRole('pelatih') => 'pelatih',
            default => 'atlet',
        };
    }

    private function layoutFor(User $user): string
    {
        return match (true) {
            $user->hasAnyRole(['admin_koni', 'ketua_koni']) => 'layouts.admin',
            $user->hasRole('staff_koni') => 'layouts.staff',
            $user->hasRole('sekretaris_koni') => 'layouts.sekretaris',
            $user->hasRole('admin_cabor') => 'layouts.cabor',
            $user->hasRole('pelatih') => 'layouts.pelatih',
            default => 'layouts.atlet',
        };
    }

    private function roleLabels(): array
    {
        return [
            'admin_koni' => 'Admin KONI',
            'staff_koni' => 'Staff KONI',
            'ketua_koni' => 'Ketua KONI',
            'sekretaris_koni' => 'Sekretaris KONI',
            'admin_cabor' => 'Admin Cabor',
            'atlet' => 'Atlet',
            'pelatih' => 'Pelatih',
        ];
    }

    private function safeCsvValue(?string $value): string
    {
        $value = trim((string) $value);

        return $value !== '' && preg_match('/^[=+@\-\t\r]/', $value)
            ? "'".$value
            : $value;
    }
}
