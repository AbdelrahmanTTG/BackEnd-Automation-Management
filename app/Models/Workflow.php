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
        'name',
        'description',
        'nodes',
        'edges',
        'status',
        'version',
        'user_id',
    ];

    protected $casts = [
        'nodes' => 'array',
        'edges' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function executions()
    {
        return $this->hasMany(WorkflowExecution::class);
    }

    public function activeExecutions()
    {
        return $this->executions()->where('status', 'active');
    }

    public function duplicate()
    {
        $newWorkflow = $this->replicate();
        $newWorkflow->name = $this->name . ' (Copy)';
        $newWorkflow->status = 'draft';
        $newWorkflow->version = 1;
        $newWorkflow->save();

        return $newWorkflow;
    }
}
