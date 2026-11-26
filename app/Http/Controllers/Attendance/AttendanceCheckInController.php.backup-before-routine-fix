<?php

namespace App\Http\Controllers\Attendance;

use App\Http\Controllers\Controller;
use App\Models\AttendanceRecord;
use App\Models\AttendanceSession;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;
use LogicException;

class AttendanceCheckInController extends Controller
{
    private const INTERNAL_ROLES = [
        'admin_koni',
        'staff_koni',
        'ketua_koni',
        'sekretaris_koni',
        'admin_cabor',
        'atlet',
        'pelatih',
        'atlet_pelatih',
    ];

    public function show(Request $request, string $token): View
    {
        return $this->renderCheckInPage(
            $request,
            $token,
            route('attendance.check-in.store', $token),
        );
    }

    private function renderCheckInPage(Request $request, string $token, string $formAction): View
    {
        $session = $this->resolveSession($token);
        $this->authorizeParticipant($request, $session);

        $existingRecord = $session->records()
            ->where('user_id', $request->user()->id)
            ->first();

        $attendanceAction = match (true) {
            ! $existingRecord => 'check_in',
            ! $existingRecord->checked_out_at => 'check_out',
            default => 'completed',
        };

        $canRecord = $session->status === 'open'
            && $session->isWithinSchedule()
            && $attendanceAction !== 'completed';

        return view('absensi.check-in', [
            'session' => $session->load('cabangOlahraga'),
            'existingRecord' => $existingRecord,
            'attendanceAction' => $attendanceAction,
            'canRecord' => $canRecord,
            'formAction' => $formAction,
        ]);
    }

    public function store(Request $request, string $token): RedirectResponse
    {
        return $this->storeForToken(
            $request,
            $token,
            route('attendance.check-in.show', $token),
        );
    }

    private function storeForToken(Request $request, string $token, string $returnUrl): RedirectResponse
    {
        $session = $this->resolveSession($token);
        $user = $request->user();
        $this->authorizeParticipant($request, $session);

        if ($session->status !== 'open') {
            return redirect()->to($returnUrl)->with('error', 'Sesi absensi sudah ditutup.');
        }

        if (! $session->isWithinSchedule()) {
            return redirect()->to($returnUrl)->with('error', 'Absensi hanya dapat dilakukan selama jadwal kegiatan.');
        }

        if (! $request->has('attendance_action')) {
            $request->merge(['attendance_action' => 'check_in']);
        }

        $validated = $request->validate([
            'attendance_action' => ['required', 'in:check_in,check_out'],
            'selfie_data' => ['required', 'string', 'max:2800000'],
        ]);

        $existingRecord = $session->records()
            ->where('user_id', $user->id)
            ->first();

        if ($validated['attendance_action'] === 'check_in' && $existingRecord) {
            return redirect()->to($returnUrl)
                ->with('error', 'Check-in untuk sesi ini sudah tercatat.');
        }

        if ($validated['attendance_action'] === 'check_out') {
            if (! $existingRecord) {
                return redirect()->to($returnUrl)
                    ->with('error', 'Lakukan check-in terlebih dahulu sebelum check-out.');
            }

            if ($existingRecord->checked_out_at) {
                return redirect()->to($returnUrl)
                    ->with('error', 'Check-out untuk sesi ini sudah tercatat.');
            }
        }

        $imageBytes = $this->decodeSelfie($validated['selfie_data']);
        abort_if($imageBytes === null, 422, 'Selfie harus berupa gambar JPEG yang valid.');

        $imageInfo = @getimagesizefromstring($imageBytes);
        abort_unless(
            is_array($imageInfo)
                && ($imageInfo['mime'] ?? null) === 'image/jpeg'
                && ($imageInfo[0] ?? 0) >= 160
                && ($imageInfo[1] ?? 0) >= 160
                && ($imageInfo[0] ?? 99999) <= 4000
                && ($imageInfo[1] ?? 99999) <= 4000,
            422,
            'Resolusi selfie tidak memenuhi ketentuan.',
        );

        $action = $validated['attendance_action'];
        $path = 'absensi/'.$session->id.'/'.$user->id.'_'.$action.'_'.now()->format('YmdHis').'_'.Str::random(8).'.jpg';

        if (! Storage::disk('local')->put($path, $imageBytes)) {
            return back()->withInput()->with('error', 'Selfie gagal disimpan. Silakan coba kembali.');
        }

        try {
            DB::transaction(function () use ($session, $user, $request, $path, $action): void {
                $record = $session->records()
                    ->where('user_id', $user->id)
                    ->lockForUpdate()
                    ->first();

                if ($action === 'check_in') {
                    if ($record) {
                        throw new LogicException('Check-in untuk sesi ini sudah tercatat.');
                    }

                    AttendanceRecord::query()->create([
                        'attendance_session_id' => $session->id,
                        'user_id' => $user->id,
                        'cabang_olahraga_id' => $this->participantCaborId($user, $session),
                        'participant_name' => $user->name,
                        'participant_email' => $user->email,
                        'role_snapshot' => $this->participantRole($user, $session),
                        'selfie_path' => $path,
                        'checked_in_at' => now(),
                        'ip_address' => $request->ip(),
                        'user_agent' => substr((string) $request->userAgent(), 0, 1000),
                    ]);

                    return;
                }

                if (! $record || $record->checked_out_at) {
                    throw new LogicException('Check-out tidak tersedia atau sudah tercatat.');
                }

                $record->update([
                    'check_out_selfie_path' => $path,
                    'checked_out_at' => now(),
                ]);
            });
        } catch (QueryException $exception) {
            Storage::disk('local')->delete($path);

            if ($session->records()->where('user_id', $user->id)->exists()) {
                return redirect()->to($returnUrl)
                    ->with('error', 'Status absensi sudah berubah. Muat ulang halaman untuk melanjutkan.');
            }

            throw $exception;
        } catch (LogicException $exception) {
            Storage::disk('local')->delete($path);

            return redirect()->to($returnUrl)
                ->with('error', $exception->getMessage());
        } catch (\Throwable $exception) {
            Storage::disk('local')->delete($path);
            throw $exception;
        }

        $message = $action === 'check_in'
            ? 'Check-in berhasil dicatat. Jangan lupa melakukan check-out setelah kegiatan selesai.'
            : 'Check-out berhasil dicatat. Terima kasih sudah mengikuti kegiatan.';

        return redirect()->to($returnUrl)->with('success', $message);
    }

    private function resolveSession(string $token): AttendanceSession
    {
        // Legacy rows without target_roles can still accept their old link.
        // New and migrated sessions use the signed token generated for today.
        $legacySession = AttendanceSession::query()->where('token', $token)->first();
        if ($legacySession && $legacySession->target_roles === null) {
            return $legacySession;
        }

        $payload = AttendanceSession::decodeDailyScanToken($token);
        abort_unless($payload, 404, 'QR Code tidak valid atau sudah berganti untuk hari ini.');

        $session = AttendanceSession::query()->findOrFail($payload['id']);
        abort_unless(
            hash_equals($session->dailyScanToken(), $token),
            404,
            'QR Code tidak valid atau sudah berganti untuk hari ini.',
        );

        return $session;
    }

    private function authorizeParticipant(Request $request, AttendanceSession $session): void
    {
        $user = $request->user();
        abort_unless($user && $user->is_active, 403);

        $userRoles = $user->getRoleNames()
            ->filter(fn (string $role): bool => in_array($role, self::INTERNAL_ROLES, true))
            ->values()
            ->all();

        abort_unless($userRoles !== [], 403, 'Role akun ini tidak memiliki akses absensi.');

        if (is_array($session->target_roles) && $session->target_roles !== []) {
            $eligibleRoles = array_values(array_intersect($userRoles, $session->target_roles));

            if (in_array('atlet_pelatih', $userRoles, true)) {
                $eligibleRoles = array_merge($eligibleRoles, array_intersect(['atlet', 'pelatih'], $session->target_roles));
            }

            abort_unless($eligibleRoles !== [], 403, 'Kegiatan ini tidak ditujukan untuk role akun Anda.');
        } else {
            // Backward compatibility for sessions created before this patch.
            $legacyAllowedRoles = match ($session->target_role) {
                'atlet' => ['atlet', 'atlet_pelatih'],
                'pelatih' => ['pelatih', 'atlet_pelatih'],
                default => ['atlet', 'pelatih', 'atlet_pelatih'],
            };

            abort_unless(
                array_intersect($userRoles, $legacyAllowedRoles) !== [],
                403,
                'Sesi absensi lama ini hanya menerima Atlet dan Pelatih.',
            );
        }

        if (! $session->cabang_olahraga_id) {
            return;
        }

        if ($user->hasRole('admin_cabor')) {
            abort_unless(
                (int) $user->cabang_olahraga_id === (int) $session->cabang_olahraga_id,
                403,
                'Sesi absensi ini khusus untuk cabang olahraga lain.',
            );

            return;
        }

        if ($user->hasAnyRole(['admin_koni', 'staff_koni', 'ketua_koni', 'sekretaris_koni'])) {
            return;
        }

        $user->loadMissing(['atlet', 'pelatih']);
        $caborIds = collect([
            $user->cabang_olahraga_id,
            $user->atlet?->cabang_olahraga_id,
            $user->pelatih?->cabang_olahraga_id,
        ])->filter()->map(fn ($id): int => (int) $id)->unique();

        abort_unless(
            $caborIds->contains((int) $session->cabang_olahraga_id),
            403,
            'Sesi absensi ini khusus untuk cabang olahraga tertentu.',
        );
    }

    private function participantCaborId($user, AttendanceSession $session): ?int
    {
        if ($session->cabang_olahraga_id) {
            return (int) $session->cabang_olahraga_id;
        }

        if ($user->hasAnyRole(['admin_koni', 'staff_koni', 'ketua_koni', 'sekretaris_koni'])) {
            return null;
        }

        $user->loadMissing(['atlet', 'pelatih']);

        foreach ([
            $user->cabang_olahraga_id,
            $user->atlet?->cabang_olahraga_id,
            $user->pelatih?->cabang_olahraga_id,
        ] as $caborId) {
            if ($caborId !== null) {
                return (int) $caborId;
            }
        }

        return null;
    }

    private function participantRole($user, AttendanceSession $session): string
    {
        $userRoles = $user->getRoleNames()->all();

        if (is_array($session->target_roles) && $session->target_roles !== []) {
            foreach ($session->target_roles as $targetRole) {
                if (in_array($targetRole, $userRoles, true)) {
                    return $targetRole;
                }

                if ($targetRole === 'atlet' && in_array('atlet_pelatih', $userRoles, true)) {
                    return 'atlet';
                }

                if ($targetRole === 'pelatih' && in_array('atlet_pelatih', $userRoles, true)) {
                    return 'pelatih';
                }
            }
        }

        foreach (self::INTERNAL_ROLES as $role) {
            if (in_array($role, $userRoles, true)) {
                return $role;
            }
        }

        return 'unknown';
    }

    private function decodeSelfie(string $dataUrl): ?string
    {
        if (! preg_match('/\Adata:image\/jpeg;base64,([A-Za-z0-9+\/=\r\n]+)\z/D', $dataUrl, $matches)) {
            return null;
        }

        $decoded = base64_decode(preg_replace('/\s+/', '', $matches[1]), true);
        if ($decoded === false || strlen($decoded) < 1024 || strlen($decoded) > 2_000_000) {
            return null;
        }

        return $decoded;
    }
}
