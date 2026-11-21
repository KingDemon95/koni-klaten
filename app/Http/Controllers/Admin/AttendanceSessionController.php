<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AttendanceRecord;
use App\Models\AttendanceSession;
use App\Models\CabangOlahraga;
use App\Support\QrCode\QrCodeSvg;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AttendanceSessionController extends Controller
{
    public function index(): View
    {
        $sessions = AttendanceSession::query()
            ->with('cabangOlahraga')
            ->withCount('records')
            ->latest('starts_at')
            ->paginate(12);

        return view('admin.absensi.index', compact('sessions'));
    }

    public function create(): View
    {
        return view('admin.absensi.create', [
            'cabors' => CabangOlahraga::query()->where('status', 'aktif')->orderBy('nama')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:160'],
            'description' => ['nullable', 'string', 'max:3000'],
            'cabang_olahraga_id' => ['nullable', 'integer', Rule::exists('cabang_olahraga', 'id')->where('status', 'aktif')],
            'target_role' => ['required', 'in:all,atlet,pelatih'],
            'starts_at' => ['required', 'date'],
            'ends_at' => ['required', 'date', 'after:starts_at'],
        ]);

        $session = AttendanceSession::query()->create([
            ...$validated,
            'created_by' => $request->user()->id,
            'token' => Str::random(40),
            'status' => 'open',
        ]);

        return redirect()->route('admin.absensi.show', $session)
            ->with('success', 'Sesi absensi berhasil dibuat. Bagikan QR Code kepada peserta.');
    }

    public function show(AttendanceSession $attendanceSession): View
    {
        $attendanceSession->load(['cabangOlahraga', 'creator']);
        $records = $attendanceSession->records()
            ->with(['user', 'cabangOlahraga'])
            ->latest('checked_in_at')
            ->paginate(25);
        $qrSvg = QrCodeSvg::render(route('attendance.check-in.show', $attendanceSession->token));

        return view('admin.absensi.show', compact('attendanceSession', 'records', 'qrSvg'));
    }

    public function close(AttendanceSession $attendanceSession): RedirectResponse
    {
        $attendanceSession->update(['status' => 'closed']);

        return redirect()->route('admin.absensi.show', $attendanceSession)
            ->with('success', 'Sesi absensi telah ditutup. QR Code tidak dapat digunakan lagi.');
    }

    public function export(AttendanceSession $attendanceSession): StreamedResponse
    {
        $filename = 'rekap-absensi-'.Str::slug($attendanceSession->title).'-'.$attendanceSession->starts_at->format('Ymd').'.csv';

        return response()->streamDownload(function () use ($attendanceSession): void {
            $output = fopen('php://output', 'w');
            fwrite($output, "\xEF\xBB\xBF");
            fputcsv($output, ['Nama Peserta', 'Email', 'Peran', 'Cabang Olahraga', 'Waktu Hadir']);

            $attendanceSession->records()
                ->with('cabangOlahraga')
                ->orderBy('checked_in_at')
                ->chunk(200, function ($records) use ($output): void {
                    foreach ($records as $record) {
                        fputcsv($output, [
                            $this->safeCsvValue($record->participant_name),
                            $this->safeCsvValue($record->participant_email),
                            $this->safeCsvValue(ucfirst($record->role_snapshot)),
                            $this->safeCsvValue($record->cabangOlahraga?->nama ?? '-'),
                            $record->checked_in_at?->format('d/m/Y H:i:s'),
                        ]);
                    }
                });

            fclose($output);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function selfie(AttendanceRecord $attendanceRecord)
    {
        abort_unless(Storage::disk('local')->exists($attendanceRecord->selfie_path), 404);

        return Storage::disk('local')->response(
            $attendanceRecord->selfie_path,
            null,
            ['Cache-Control' => 'private, no-store'],
        );
    }

    private function safeCsvValue(?string $value): string
    {
        $value = trim((string) $value);
        if ($value !== '' && preg_match('/^[=+@\-\t\r]/', $value)) {
            return "'".$value;
        }

        return $value;
    }
}
