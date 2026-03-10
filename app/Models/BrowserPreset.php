<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BrowserPreset extends Model
{
    protected $table = 'browser_presets';

    protected $fillable = ['name', 'description', 'values', 'sort_order'];

    protected $casts = ['values' => 'array'];

    public function getValuesAttribute($value): array
    {
        if (is_array($value)) {
            return $value;
        }

        $decoded = json_decode($value, true);

        // double-encoded string
        if (is_string($decoded)) {
            $decoded = json_decode($decoded, true);
        }

        return is_array($decoded) ? $decoded : [];
    }
}
