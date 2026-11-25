<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WhatsappLog extends Model
{
    /** @var list<string> */
    protected $fillable = [
        'student_id', 'attendance_id', 'phone', 'message', 'type',
        'status', 'response', 'sent_at',
    ];

    protected $table = 'whatsapp_logs';

    protected function casts(): array
    {
        return [
            'sent_at' => 'datetime',
        ];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function attendance(): BelongsTo
    {
        return $this->belongsTo(Attendance::class);
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            'sent' => 'Berhasil',
            'failed' => 'Gagal',
            default => 'Menunggu',
        };
    }

    public function statusClasses(): string
    {
        return match ($this->status) {
            'sent' => 'bg-emerald-50 text-emerald-700 ring-emerald-600/20',
            'failed' => 'bg-rose-50 text-rose-700 ring-rose-600/20',
            default => 'bg-amber-50 text-amber-700 ring-amber-600/20',
        };
    }
}
