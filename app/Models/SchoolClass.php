<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SchoolClass extends Model
{
    use HasFactory;

    /** @var list<string> */
    protected $fillable = ['name', 'level', 'department', 'homeroom_teacher_id', 'academic_year', 'capacity'];

    protected $table = 'classes';

    public function students(): HasMany
    {
        return $this->hasMany(Student::class, 'class_id');
    }

    public function homeroomTeacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'homeroom_teacher_id');
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class, 'class_id');
    }

    public function scopeForTeacher($query, User $user)
    {
        return $query->where('homeroom_teacher_id', $user->id);
    }

    public function attendancePercentage(?string $from = null, ?string $to = null): float
    {
        $query = Attendance::query()
            ->whereHas('student', fn ($q) => $q->where('class_id', $this->id))
            ->when($from, fn ($q) => $q->whereDate('date', '>=', $from))
            ->when($to, fn ($q) => $q->whereDate('date', '<=', $to));

        $total = (clone $query)->count();

        if ($total === 0) {
            return 0;
        }

        $present = (clone $query)->whereIn('status', [Attendance::STATUS_HADIR, Attendance::STATUS_TERLAMBAT])->count();

        return round($present / $total * 100, 1);
    }

    public function label(): string
    {
        return 'Kelas '.$this->name;
    }
}
