<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TeslaRegistration extends Model
{
    protected $fillable = [
        'registration_date',
        'model',
        'color',
        'variant',
        'count',
    ];

    protected function casts(): array
    {
        return [
            'registration_date' => 'date',
            'count' => 'integer',
        ];
    }
}
