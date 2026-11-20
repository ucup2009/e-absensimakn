<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password', 'role', 'phone', 'nip', 'photo', 'is_active', 'last_login_at'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    public const ROLE_ADMIN = 'admin';

    public const ROLE_WALI = 'wali_kelas';

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'is_active' => 'boolean',
            'password' => 'hashed',
        ];
    }

    public function homeroomClass(): HasOne
    {
        return $this->hasOne(SchoolClass::class, 'homeroom_teacher_id');
    }

    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    public function isWaliKelas(): bool
    {
        return $this->role === self::ROLE_WALI;
    }

    public function roleLabel(): string
    {
        return $this->isAdmin() ? 'Admin / Operator' : 'Wali Kelas & Guru';
    }

    public function initials(): string
    {
        $words = preg_split('/\s+/', trim($this->name)) ?: [];
        $initials = '';
        foreach (array_slice($words, 0, 2) as $word) {
            $initials .= mb_strtoupper(mb_substr($word, 0, 1));
        }

        return $initials ?: 'U';
    }

    /**
     * Nama panggilan singkat untuk sapaan: gelar kehormatan dan gelar
     * akademik dibuang, lalu diambil kata pertama saja.
     */
    public function shortName(): string
    {
        $name = trim($this->name);
        $name = preg_replace('/^(ust\.?|ustadz|ustadzah|ustad|hj\.?|h\.?|drs\.?|dra\.?|dr\.?)\s+/i', '', $name) ?? $name;
        $name = preg_replace('/,.*$/', '', $name) ?? $name;

        $first = preg_split('/\s+/', trim($name))[0] ?? '';

        return $first !== '' ? $first : trim($this->name);
    }

    public function avatarUrl(): ?string
    {
        if (blank($this->photo)) {
            return null;
        }

        // Tambah versi agar browser tidak menampilkan foto lama setelah diganti.
        $version = $this->updated_at?->timestamp;

        return asset('storage/'.$this->photo).($version ? '?v='.$version : '');
    }
}
