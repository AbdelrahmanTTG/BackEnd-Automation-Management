<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class WorkflowExecution extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'workflow_id',
        'name',
        'input_data',
        'status',
        'execution_log',
        'last_run_at',
        'next_run_at',
        'schedule',
    ];

    protected $casts = [
        'input_data' => 'array',
        'execution_log' => 'array',
        'last_run_at' => 'datetime',
        'next_run_at' => 'datetime',
    ];

    public function workflow()
    {
        return $this->belongsTo(Workflow::class);
    }

    public function runs()
    {
        return $this->hasMany(WorkflowRun::class);
    }

    public function latestRun()
    {
        return $this->hasOne(WorkflowRun::class)->latestOfMany();
    }
}
