<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AutomationConfig extends Model
{

    protected $table = 'automationconfig';

    public $timestamps = false;

    protected $fillable = [
        'name',
        'setup',
        'provider',
        'created_at',
        'created_by',
        'updated_at',
        'updated_by',
    ];
}
