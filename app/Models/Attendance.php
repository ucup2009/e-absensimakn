<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Attendance extends Model
{
    /** @var list<string> */
    protected $fillable = [
        'student_id', 'date', 'time_in', 'time_out', 'status',
        'notification_status', 'notification_sent_at', 'note', 'recorded_by',
    ];

    public const STATUS_HADIR = 'hadir';

    public const STATUS_TERLAMBAT = 'terlambat';

    public const STATUS_IZIN = 'izin';

    public const STATUS_SAKIT = 'sakit';

    public const STATUS_ALPA = 'alpa';

    public const NOTIF_PENDING = 'pending';

    public const NOTIF_SENT = 'sent';

    public const NOTIF_FAILED = 'failed';

    public static function statuses(): array
    {
        return [
            self::STATUS_HADIR => 'Hadir',
            self::STATUS_TERLAMBAT => 'Terlambat',
            self::STATUS_IZIN => 'Izin',
            self::STATUS_SAKIT => 'Sakit',
            self::STATUS_ALPA => 'Alpa',
        ];
    }

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'notification_sent_at' => 'datetime',
        ];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function recorder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    public function statusLabel(): string
    {
        return self::statuses()[$this->status] ?? ucfirst($this->status);
    }

    public function statusClasses(): string
    {
        return match ($this->status) {
            self::STATUS_HADIR => 'bg-emerald-50 text-emerald-700 ring-emerald-600/20',
            self::STATUS_TERLAMBAT => 'bg-amber-50 text-amber-700 ring-amber-600/20',
            self::STATUS_IZIN => 'bg-sky-50 text-sky-700 ring-sky-600/20',
            self::STATUS_SAKIT => 'bg-violet-50 text-violet-700 ring-violet-600/20',
            default => 'bg-rose-50 text-rose-700 ring-rose-600/20',
        };
    }

    public function notificationLabel(): string
    {
        return match ($this->notification_status) {
            self::NOTIF_SENT => 'Terkirim',
            self::NOTIF_FAILED => 'Gagal',
            default => 'Menunggu',
        };
    }

    public function notificationClasses(): string
    {
        return match ($this->notification_status) {
            self::NOTIF_SENT => 'bg-emerald-50 text-emerald-700',
            self::NOTIF_FAILED => 'bg-rose-50 text-rose-700',
            default => 'bg-slate-100 text-slate-600',
        };
    }

    public function durationLabel(): string
    {
        if (! $this->time_in || ! $this->time_out) {
            return '-';
        }

        $in = strtotime($this->time_in);
        $out = strtotime($this->time_out);

        if ($out <= $in) {
            return '-';
        }

        $minutes = (int) round(($out - $in) / 60);
        $hours = intdiv($minutes, 60);
        $rest = $minutes % 60;

        return trim(($hours ? $hours.'j ' : '').($rest ? $rest.'m' : '')) ?: '0m';
    }

    public function isLate(): bool
    {
        return $this->status === self::STATUS_TERLAMBAT;
    }

    public function scopeBetweenDates($query, ?string $from, ?string $to)
    {
        return $query
            ->when($from, fn ($q) => $q->whereDate('date', '>=', $from))
            ->when($to, fn ($q) => $q->whereDate('date', '<=', $to));
    }
}
