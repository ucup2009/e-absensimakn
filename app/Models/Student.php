<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Student extends Model
{
    use HasFactory;

    /** @var list<string> */
    protected $fillable = [
        'nisn', 'name', 'class_id', 'gender', 'parent_name', 'parent_phone',
        'address', 'birth_date', 'photo', 'qr_code', 'qr_generated_at', 'is_active',
    ];


    protected function casts(): array
    {
        return [
            'birth_date' => 'date',
            'qr_generated_at' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    public function schoolClass(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    public function todayAttendance(): ?Attendance
    {
        return $this->attendances()->whereDate('date', today())->first();
    }

    public function initials(): string
    {
        $words = preg_split('/\s+/', trim($this->name)) ?: [];
        $initials = '';
        foreach (array_slice($words, 0, 2) as $word) {
            $initials .= mb_strtoupper(mb_substr($word, 0, 1));
        }

        return $initials ?: 'S';
    }

    public function photoUrl(): ?string
    {
        return $this->photo ? asset('storage/'.$this->photo) : null;
    }

    public function genderLabel(): string
    {
        return $this->gender === 'P' ? 'Perempuan' : 'Laki-laki';
    }

    public function waConnected(): bool
    {
        return filled($this->parent_phone) && str_starts_with((string) $this->parent_phone, '62');
    }

    public function qrPayload(): string
    {
        return (string) ($this->qr_code ?: $this->nisn ?: '');
    }

    public function scopeSearch($query, ?string $term)
    {
        if (! $term) {
            return $query;
        }

        return $query->where(function ($q) use ($term) {
            $q->where('name', 'like', "%{$term}%")
                ->orWhere('nisn', 'like', "%{$term}%")
                ->orWhere('parent_phone', 'like', "%{$term}%")
                ->orWhere('parent_name', 'like', "%{$term}%");
        });
    }
}
