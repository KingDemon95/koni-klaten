<?php

namespace App\Models;

use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AttendanceSession extends Model
{
    protected $fillable = [
        'created_by',
        'cabang_olahraga_id',
        'schedule_slot_id',
        'attendance_date',
        'attendance_mode',
        'title',
        'description',
        'target_role',
        'target_roles',
        'token',
        'starts_at',
        'ends_at',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'target_roles' => 'array',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'attendance_date' => 'date',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function cabangOlahraga(): BelongsTo
    {
        return $this->belongsTo(CabangOlahraga::class, 'cabang_olahraga_id');
    }

    public function scheduleSlot(): BelongsTo
    {
        return $this->belongsTo(
            AttendanceScheduleSlot::class,
            'schedule_slot_id'
        );
    }

    public function records(): HasMany
    {
        return $this->hasMany(AttendanceRecord::class);
    }

    public function scopeOpen(Builder $query): Builder
    {
        return $query->where('status', 'open');
    }

    public function isWithinSchedule(): bool
    {
        return now()->greaterThanOrEqualTo($this->starts_at)
            && now()->lessThanOrEqualTo($this->ends_at);
    }

    /**
     * Return a signed scan token that changes at the start of each local day.
     */
    public function dailyScanToken(?CarbonInterface $date = null): string
    {
        $id = base_convert((string) $this->getKey(), 10, 36);
        $dateValue = ($date ?? now())->format('Ymd');
        $payload = $id.'|'.$dateValue;
        $signature = substr(
            hash_hmac('sha256', $payload, (string) config('app.key')),
            0,
            32
        );

        return $id.'.'.$dateValue.'.'.$signature;
    }

    /**
     * @return array{id:int,date:string}|null
     */
    public static function decodeDailyScanToken(
        string $token,
        ?CarbonInterface $expectedDate = null
    ): ?array {
        if (! preg_match('/\A([0-9a-z]+)\.(\d{8})\.([a-f0-9]{32})\z/D', $token, $matches)) {
            return null;
        }

        [, $encodedId, $dateValue, $signature] = $matches;

        $id = base_convert($encodedId, 36, 10);

        if (! ctype_digit($id)) {
            return null;
        }

        $parsedDate = \DateTimeImmutable::createFromFormat('!Ymd', $dateValue);

        if (! $parsedDate || $parsedDate->format('Ymd') !== $dateValue) {
            return null;
        }

        $payload = $encodedId.'|'.$dateValue;

        $expectedSignature = substr(
            hash_hmac('sha256', $payload, (string) config('app.key')),
            0,
            32
        );

        if (! hash_equals($expectedSignature, $signature)) {
            return null;
        }

        $requiredDate = ($expectedDate ?? now())->format('Ymd');

        if ($dateValue !== $requiredDate) {
            return null;
        }

        return [
            'id' => (int) $id,
            'date' => $parsedDate->format('Y-m-d'),
        ];
    }
}
