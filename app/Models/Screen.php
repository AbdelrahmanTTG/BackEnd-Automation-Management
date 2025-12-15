<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Screen extends Model
{
    use HasFactory;
    protected $table = 'screen';
    public static function getScreen($screen)
    {
        return self::select('name', 'url')->find($screen);
    }
}
