<?php
// app/Models/WorkflowExecution.php

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
        'browser_config',
        'status',
        'last_run_at',
        'next_run_at',
        'schedule',
        'pm2_process_name',
    ];

    protected $casts = [
        'input_data' => 'array',
        'browser_config' => 'array',
        'last_run_at' => 'datetime',
        'next_run_at' => 'datetime',
    ];

    // ═══════════════════════════════════════════════════════════
    // Relationships
    // ═══════════════════════════════════════════════════════════

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

    // ═══════════════════════════════════════════════════════════
    // Helper Methods
    // ═══════════════════════════════════════════════════════════

    /**
     * Check if execution is currently running
     */
    public function isOnline(): bool
    {
        return $this->status === 'online';
    }

    /**
     * Check if execution is stopped
     */
    public function isStopped(): bool
    {
        return $this->status === 'stopped';
    }

    /**
     * Check if execution has error
     */
    public function hasError(): bool
    {
        return $this->status === 'errored';
    }

    /**
     * Get default browser config
     */
    public static function getDefaultBrowserConfig(): array
    {
        return \App\Models\BrowserDefaultConfig::getConfig();
    }
}
