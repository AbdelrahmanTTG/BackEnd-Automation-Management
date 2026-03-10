<?php
// app/Models/Workflow.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Workflow extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'name',
        'description',
        'nodes',
        'edges',
        'status',
        'version',
    ];

    protected $casts = [
        'nodes' => 'array',
        'edges' => 'array',
    ];

    // ═══════════════════════════════════════════════════════════
    // Relationships
    // ═══════════════════════════════════════════════════════════

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function executions()
    {
        return $this->hasMany(WorkflowExecution::class);
    }

    // ═══════════════════════════════════════════════════════════
    // Helper Methods
    // ═══════════════════════════════════════════════════════════

    /**
     * Get only online executions
     */
    public function onlineExecutions()
    {
        return $this->executions()->where('status', 'online');
    }

    /**
     * Duplicate workflow
     */
    public function duplicate()
    {
        $newWorkflow = $this->replicate();
        $newWorkflow->name = $this->name . ' (Copy)';
        $newWorkflow->status = 'draft';
        $newWorkflow->version = 1;
        $newWorkflow->save();

        return $newWorkflow;
    }

    /**
     * Count total steps in workflow
     */
    public function getTotalStepsAttribute()
    {
        return is_array($this->nodes) ? count($this->nodes) : 0;
    }
}
