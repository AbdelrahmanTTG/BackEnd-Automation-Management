<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Group extends Model
{
    use HasFactory;
    protected $table = 'group';
    public static function getGroup($group)
    {
        if ($group == 0) {
            return "";
        } else {
            return Group::find($group);
        }
    }
}
