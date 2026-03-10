<?php
// app/Models/WorkflowRun.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkflowRun extends Model
{
    use HasFactory;

    protected $fillable = [
        'workflow_execution_id',
        'status',
        'input_data',
        'output_data',
        'step_results',
        'error_message',
        'error_step',
        'started_at',
        'completed_at',
        'duration_ms',
    ];

    protected $casts = [
        'input_data' => 'array',
        'output_data' => 'array',
        'step_results' => 'array',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    // ═══════════════════════════════════════════════════════════
    // Relationships
    // ═══════════════════════════════════════════════════════════

    public function workflowExecution()
    {
        return $this->belongsTo(WorkflowExecution::class);
    }

    // ═══════════════════════════════════════════════════════════
    // Helper Methods
    // ═══════════════════════════════════════════════════════════

    public function isRunning(): bool
    {
        return $this->status === 'running';
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }
}
