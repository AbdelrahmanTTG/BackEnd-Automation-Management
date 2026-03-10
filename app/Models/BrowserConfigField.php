<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BrowserConfigField extends Model
{
    protected $table = 'browser_config_fields';

    protected $fillable = ['fields'];

    protected $casts = ['fields' => 'array'];

    public static function getFields(): array
    {
        $row = static::first();
        return $row ? $row->fields : [];
    }
}
