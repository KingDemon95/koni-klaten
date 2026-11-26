<?php

namespace App\Console\Commands;

use App\Models\AttendanceSchedule;
use App\Models\AttendanceSession;
use Carbon\CarbonImmutable;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class GenerateAttendanceSessions extends Command
{
    protected $signature = 'koni:attendance-generate
                            {--date= : Tanggal yang akan dibuat (YYYY-MM-DD)}';

    protected $description = 'Generate sesi absensi kerja dan latihan berdasarkan jadwal aktif KONI';

    public function handle(): int
    {
        $timezone = config('app.timezone', 'Asia/Jakarta');

        try {
            $date = $this->option('date')
                ? CarbonImmutable::parse($this->option('date'), $timezone)
                : CarbonImmutable::now($timezone);

            $dateString = $date->toDateString();
        } catch (\Throwable) {
            $this->error('Format tanggal tidak valid. Gunakan YYYY-MM-DD.');
            return self::FAILURE;
        }

        $dayOfWeek = $date->dayOfWeekIso;

        $this->info("Generate absensi tanggal {$dateString}");

        $schedules = AttendanceSchedule::query()
            ->with([
                'slots' => function ($query) use ($dayOfWeek) {
                    $query->where('day_of_week', $dayOfWeek)
                        ->where('is_active', true);
                },
            ])
            ->where('is_active', true)
            ->whereIn('schedule_type', [
                'staff_work',
                'cabor_training',
            ])
            ->whereDate('effective_from', '<=', $dateString)
            ->where(function ($query) use ($dateString) {
                $query->whereNull('effective_until')
                    ->orWhereDate('effective_until', '>=', $dateString);
            })
            ->get();

        $created = 0;
        $existing = 0;

        foreach ($schedules as $schedule) {
            foreach ($schedule->slots as $slot) {
                $start = CarbonImmutable::parse(
                    $dateString.' '.$slot->starts_at,
                    $timezone
                );

                $end = CarbonImmutable::parse(
                    $dateString.' '.$slot->ends_at,
                    $timezone
                );

                if ($end->lessThanOrEqualTo($start)) {
                    $this->warn("Slot {$slot->id} dilewati: jam tidak valid.");
                    continue;
                }

                $isWork = $schedule->schedule_type === 'staff_work';

                $targetRoles = $isWork
                    ? ['staff_koni']
                    : ['atlet', 'pelatih'];

                $label = $slot->label ?: (
                    $isWork ? 'Jam Kerja Staff' : 'Sesi Latihan'
                );

                $title = $isWork
                    ? "Absensi Kerja Staff - {$label}"
                    : "Absensi Latihan - {$label}";

                $session = AttendanceSession::query()->firstOrCreate(
                    [
                        'schedule_slot_id' => $slot->id,
                        'attendance_date' => $dateString,
                    ],
                    [
                        'created_by' => $schedule->created_by,
                        'cabang_olahraga_id' => $schedule->cabang_olahraga_id,
                        'attendance_mode' => $isWork ? 'work' : 'training',
                        'title' => Str::limit($title, 160, ''),
                        'description' => $schedule->title.' | '.$label,
                        'target_role' => $isWork ? 'staff_koni' : 'custom',
                        'target_roles' => $targetRoles,
                        'token' => Str::random(48),
                        'starts_at' => $start,
                        'ends_at' => $end,
                        'status' => 'open',
                    ]
                );

                if ($session->wasRecentlyCreated) {
                    $created++;

                    $this->line(
                        "[DIBUAT] {$title} | {$start->format('H:i')} - {$end->format('H:i')}"
                    );
                } else {
                    $existing++;

                    $this->line(
                        "[SUDAH ADA] {$title} | {$dateString}"
                    );
                }
            }
        }

        $this->newLine();
        $this->info("Selesai. Dibuat: {$created}, sudah ada: {$existing}.");

        return self::SUCCESS;
    }
}
