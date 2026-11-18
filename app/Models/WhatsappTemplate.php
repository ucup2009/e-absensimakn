<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['key', 'name', 'body', 'is_active'])]
class WhatsappTemplate extends Model
{
    protected $table = 'whatsapp_templates';

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }
}
