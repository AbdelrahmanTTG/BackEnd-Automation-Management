<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{
    use HasFactory;
    protected $table = 'permission';
    public static  function getGroupByRole($role)
    {
        return self::distinct()
            ->select('groups')
            ->where('role', $role)
            ->where('groups', '!=', '0')
            ->orderBy('groups', 'asc')
            ->get();
    }
    public function screen()
    {
        return $this->belongsTo(Screen::class, 'screen', 'id');
    }
    public static function getScreenByGroupAndRole($groups, $role)
    {
        if (empty($groups)) {
            return collect();
        }
        return self::with('screen') 
            ->where('groups', $groups) 
            ->where('role', $role) 
            ->whereHas('screen', function ($query) use ($groups) { 
                $query->where('menu', '1') 
                    ->where('groups', $groups) 
                    ->where('use_system', 'like', '%AU%') 
                    ->groupBy('id');
            })
            ->orderBy('menu_order') 
            ->get();


    }
}
