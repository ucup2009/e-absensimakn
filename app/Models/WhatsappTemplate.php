<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WhatsappTemplate extends Model
{
    /** @var list<string> */
    protected $fillable = ['key', 'name', 'body', 'is_active'];

    protected $table = 'whatsapp_templates';

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }
}
