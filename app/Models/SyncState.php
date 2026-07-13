<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SyncState extends Model
{
    public $incrementing = false;

    protected $primaryKey = 'key';

    protected $keyType = 'string';

    protected $fillable = [
        'key',
        'value',
    ];

    public static function getValue(string $key): ?string
    {
        return static::query()->find($key)?->value;
    }

    public static function setValue(string $key, string $value): void
    {
        static::query()->updateOrCreate(
            ['key' => $key],
            ['value' => $value],
        );
    }
}
