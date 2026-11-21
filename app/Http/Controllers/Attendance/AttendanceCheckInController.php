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

class AttendanceCheckInController extends Controller
{
    public function show(Request $request, string $token): View
    {
        $session = AttendanceSession::query()
            ->with('cabangOlahraga')
            ->where('token', $token)
            ->firstOrFail();

        $this->authorizeParticipant($request, $session);
        $existingRecord = $session->records()
            ->where('user_id', $request->user()->id)
            ->first();

        return view('absensi.check-in', [
            'session' => $session,
            'existingRecord' => $existingRecord,
            'canCheckIn' => $session->status === 'open' && $session->isWithinSchedule() && !$existingRecord,
        ]);
    }

    public function store(Request $request, string $token): RedirectResponse
    {
        $session = AttendanceSession::query()->where('token', $token)->firstOrFail();
        $user = $request->user();
        $this->authorizeParticipant($request, $session);

        if ($session->status !== 'open') {
            return back()->with('error', 'Sesi absensi sudah ditutup.');
        }

        if (now()->lt($session->starts_at)) {
            return back()->with('error', 'Sesi absensi belum dimulai.');
        }

        if (now()->gt($session->ends_at)) {
            return back()->with('error', 'Waktu absensi sudah berakhir.');
        }

        $existingRecord = $session->records()->where('user_id', $user->id)->exists();
        if ($existingRecord) {
            return redirect()->route('attendance.check-in.show', $token)
                ->with('error', 'Akun ini sudah melakukan absensi pada sesi tersebut.');
        }

        $validated = $request->validate([
            'selfie_data' => ['required', 'string', 'max:2800000'],
        ]);

        $imageBytes = $this->decodeSelfie($validated['selfie_data']);
        abort_if($imageBytes === null, 422, 'Selfie harus berasal dari kamera dan menggunakan format JPEG yang valid.');

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

        $path = 'absensi/'.$session->id.'/'.$user->id.'_'.now()->format('YmdHis').'_'.Str::random(8).'.jpg';
        if (!Storage::disk('local')->put($path, $imageBytes)) {
            return back()->withInput()->with('error', 'Selfie gagal disimpan. Silakan coba kembali.');
        }

        $caborId = $this->participantCaborId($user, $session);

        try {
            DB::transaction(function () use ($session, $user, $caborId, $path, $request): void {
                AttendanceRecord::query()->create([
                    'attendance_session_id' => $session->id,
                    'user_id' => $user->id,
                    'cabang_olahraga_id' => $caborId,
                    'participant_name' => $user->name,
                    'participant_email' => $user->email,
                    'role_snapshot' => $this->participantRole($user, $session->target_role),
                    'selfie_path' => $path,
                    'checked_in_at' => now(),
                    'ip_address' => $request->ip(),
                    'user_agent' => substr((string) $request->userAgent(), 0, 1000),
                ]);
            });
        } catch (QueryException $exception) {
            Storage::disk('local')->delete($path);
            if ($session->records()->where('user_id', $user->id)->exists()) {
                return redirect()->route('attendance.check-in.show', $token)
                    ->with('error', 'Akun ini sudah melakukan absensi pada sesi tersebut.');
            }
            throw $exception;
        } catch (\Throwable $exception) {
            Storage::disk('local')->delete($path);
            throw $exception;
        }

        return redirect()->route('attendance.check-in.show', $token)
            ->with('success', 'Absensi berhasil dicatat. Terima kasih.');
    }

    private function authorizeParticipant(Request $request, AttendanceSession $session): void
    {
        $user = $request->user();
        abort_unless($user && $user->is_active, 403);

        $allowedRoles = match ($session->target_role) {
            'atlet' => ['atlet', 'atlet_pelatih'],
            'pelatih' => ['pelatih', 'atlet_pelatih'],
            default => ['atlet', 'pelatih', 'atlet_pelatih'],
        };

        abort_unless($user->hasAnyRole($allowedRoles), 403, 'Akun ini tidak termasuk peserta sesi absensi.');

        if ($session->cabang_olahraga_id) {
            $user->loadMissing(['atlet', 'pelatih']);
            $caborIds = collect([
                $user->cabang_olahraga_id,
                $user->atlet?->cabang_olahraga_id,
                $user->pelatih?->cabang_olahraga_id,
            ])->filter()->map(fn ($id): int => (int) $id)->unique();

            abort_unless($caborIds->contains((int) $session->cabang_olahraga_id), 403, 'Sesi absensi ini khusus untuk cabang olahraga tertentu.');
        }
    }

    private function participantCaborId($user, AttendanceSession $session): ?int
    {
        $user->loadMissing(['atlet', 'pelatih']);
        $caborIds = [
            $user->cabang_olahraga_id,
            $user->atlet?->cabang_olahraga_id,
            $user->pelatih?->cabang_olahraga_id,
        ];

        if ($session->cabang_olahraga_id) {
            return (int) $session->cabang_olahraga_id;
        }

        foreach ($caborIds as $caborId) {
            if ($caborId !== null) {
                return (int) $caborId;
            }
        }

        return null;
    }

    private function participantRole($user, string $targetRole): string
    {
        if ($targetRole === 'atlet' && $user->hasAnyRole(['atlet', 'atlet_pelatih'])) {
            return 'atlet';
        }

        if ($targetRole === 'pelatih' && $user->hasAnyRole(['pelatih', 'atlet_pelatih'])) {
            return 'pelatih';
        }

        if ($user->hasRole('atlet')) {
            return 'atlet';
        }

        if ($user->hasRole('pelatih')) {
            return 'pelatih';
        }

        return 'atlet_pelatih';
    }

    private function decodeSelfie(string $dataUrl): ?string
    {
        if (!preg_match('/\Adata:image\/jpeg;base64,([A-Za-z0-9+\/=\r\n]+)\z/D', $dataUrl, $matches)) {
            return null;
        }

        $decoded = base64_decode(preg_replace('/\s+/', '', $matches[1]), true);
        if ($decoded === false || strlen($decoded) < 1024 || strlen($decoded) > 2_000_000) {
            return null;
        }

        return $decoded;
    }
}
