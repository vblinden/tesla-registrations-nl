<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DailyRegistrationTotal extends Model
{
    protected $fillable = [
        'registration_date',
        'total_count',
    ];

    protected function casts(): array
    {
        return [
            'registration_date' => 'date',
            'total_count' => 'integer',
        ];
    }
}
