<?php

namespace App\Http\Controllers\Attendance;

use App\Http\Controllers\Controller;
use App\Models\AttendanceSchedule;
use App\Models\CabangOlahraga;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;
use Illuminate\Validation\ValidationException;

class AttendanceScheduleController extends Controller
{
    /**
     * Jadwal kerja Staff KONI.
     * Hanya Ketua KONI.
     */
    public function work(Request $request): View
    {
        abort_unless($request->user()->hasRole('ketua_koni'), 403);

        $schedule = $this->currentSchedule('staff_work');

        return view('attendance.schedules.work', [
            'schedule' => $schedule,
            'days' => $this->days(),
        ]);
    }

    /**
     * Simpan versi baru jadwal kerja Staff.
     */
    public function updateWork(Request $request): RedirectResponse
    {
        abort_unless($request->user()->hasRole('ketua_koni'), 403);

        $this->saveSchedule(
            $request,
            'staff_work',
            null,
            'Jadwal Kerja Staff KONI',
            true
        );

        return redirect()->route('admin.absensi.jadwal-kerja')
            ->with('success', 'Jadwal kerja Staff berhasil diperbarui.');
    }

    /**
     * Jadwal latihan cabang olahraga.
     * Hanya Admin Cabor yang bersangkutan.
     */
    public function training(Request $request): View
    {
        $user = $request->user();

        abort_unless(
            $user->hasRole('admin_cabor')
            && $user->cabang_olahraga_id !== null,
            403
        );

        $cabor = CabangOlahraga::findOrFail($user->cabang_olahraga_id);
        $schedule = $this->currentSchedule(
            'cabor_training',
            $cabor->id
        );

        return view('attendance.schedules.training', [
            'schedule' => $schedule,
            'cabor' => $cabor,
            'days' => $this->days(),
        ]);
    }

    /**
     * Simpan versi baru jadwal latihan.
     */
    public function updateTraining(Request $request): RedirectResponse
    {
        $user = $request->user();

        abort_unless(
            $user->hasRole('admin_cabor')
            && $user->cabang_olahraga_id !== null,
            403
        );

        $this->saveSchedule(
            $request,
            'cabor_training',
            (int) $user->cabang_olahraga_id,
            'Jadwal Latihan',
            false
        );

        return redirect()->route('cabor.absensi.jadwal-latihan')
            ->with('success', 'Jadwal latihan berhasil diperbarui.');
    }

    /**
     * Ambil jadwal yang berlaku pada tanggal hari ini.
     */
    private function currentSchedule(
        string $type,
        ?int $caborId = null
    ): ?AttendanceSchedule {
        return AttendanceSchedule::query()
            ->with('slots')
            ->where('schedule_type', $type)
            ->where('cabang_olahraga_id', $caborId)
            ->whereDate('effective_from', '<=', today())
            ->where(function ($query): void {
                $query->whereNull('effective_until')
                    ->orWhereDate('effective_until', '>=', today());
            })
            ->latest('version')
            ->first();
    }

    /**
     * Simpan jadwal sebagai versi baru tanpa menghapus riwayat lama.
     */
    private function saveSchedule(
        Request $request,
        string $type,
        ?int $caborId,
        string $title,
        bool $oneSlotPerDay
    ): void {
        $validator = Validator::make($request->all(), [
            'effective_from' => [
                'required',
                'date',
                'after_or_equal:today',
            ],
            'slots' => ['required', 'array', 'min:1', 'max:70'],
            'slots.*.day_of_week' => [
                'required',
                'integer',
                'between:1,7',
            ],
            'slots.*.label' => [
                'nullable',
                'string',
                'max:80',
            ],
            'slots.*.starts_at' => [
                'required',
                'date_format:H:i',
            ],
            'slots.*.ends_at' => [
                'required',
                'date_format:H:i',
                'after:slots.*.starts_at',
            ],
        ]);

        $validator->after(function ($validator) use (
            $request,
            $oneSlotPerDay
        ): void {
            $slots = $request->input('slots', []);

            if (! is_array($slots)) {
                return;
            }

            if ($oneSlotPerDay) {
                $days = [];

                foreach ($slots as $slot) {
                    $day = $slot['day_of_week'] ?? null;

                    if ($day !== null && in_array($day, $days, true)) {
                        $validator->errors()->add(
                            'slots',
                            'Jadwal kerja hanya boleh memiliki satu shift per hari.'
                        );

                        break;
                    }

                    $days[] = $day;
                }
            }

            foreach ($slots as $slot) {
                if (
                    ! empty($slot['starts_at'])
                    && ! empty($slot['ends_at'])
                    && $slot['ends_at'] <= $slot['starts_at']
                ) {
                    $validator->errors()->add(
                        'slots',
                        'Jam selesai harus lebih besar daripada jam mulai. Sesi lintas tengah malam belum didukung.'
                    );

                    break;
                }
            }
        });

        $validated = $validator->validate();

        $effectiveFrom = $validated['effective_from'];

        DB::transaction(function () use (
            $request,
            $type,
            $caborId,
            $title,
            $effectiveFrom,
            $validated
        ): void {
            $previous = AttendanceSchedule::query()
                ->where('schedule_type', $type)
                ->where('cabang_olahraga_id', $caborId)
                ->orderByDesc('effective_from')
                ->lockForUpdate()
                ->first();

            if (
                $previous
                && $effectiveFrom <= $previous->effective_from->format('Y-m-d')
            ) {
                throw ValidationException::withMessages([
                    'effective_from' => 'Tanggal berlaku harus lebih baru daripada awal jadwal versi sebelumnya.',
                ]);
            }

            $version = ($previous?->version ?? 0) + 1;

            if ($previous) {
                $previous->update([
                    'effective_until' => now()
                        ->parse($effectiveFrom)
                        ->subDay()
                        ->toDateString(),
                    'is_active' => false,
                ]);
            }

            $schedule = AttendanceSchedule::create([
                'schedule_type' => $type,
                'cabang_olahraga_id' => $caborId,
                'created_by' => $request->user()->id,
                'title' => $title,
                'effective_from' => $effectiveFrom,
                'effective_until' => null,
                'version' => $version,
                'is_active' => true,
            ]);

            foreach ($validated['slots'] as $index => $slot) {
                $schedule->slots()->create([
                    'day_of_week' => $slot['day_of_week'],
                    'label' => $slot['label'] ?? null,
                    'starts_at' => $slot['starts_at'],
                    'ends_at' => $slot['ends_at'],
                    'sort_order' => $index + 1,
                    'is_active' => true,
                ]);
            }
        });
    }

    private function days(): array
    {
        return [
            1 => 'Senin',
            2 => 'Selasa',
            3 => 'Rabu',
            4 => 'Kamis',
            5 => 'Jumat',
            6 => 'Sabtu',
            7 => 'Minggu',
        ];
    }
}
