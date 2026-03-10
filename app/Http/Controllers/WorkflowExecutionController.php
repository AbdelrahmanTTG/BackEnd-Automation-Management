<?php
// app/Http/Controllers/WorkflowExecutionController.php

namespace App\Http\Controllers;

use App\Models\Workflow;
use App\Models\WorkflowExecution;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;

class WorkflowExecutionController extends Controller
{
    /**
     * Get all executions for a workflow
     */
    public function index($workflowId)
    {
        $workflow = Workflow::findOrFail($workflowId);

        $user = JWTAuth::parseToken()->authenticate();
        if ($workflow->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access'
            ], 403);
        }

        $executions = WorkflowExecution::where('workflow_id', $workflowId)
            ->with('latestRun')
            ->latest()
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $executions
        ]);
    }

    /**
     * Create new execution
     */
    public function store(Request $request, $workflowId)
    {
        $workflow = Workflow::findOrFail($workflowId);

        $user = JWTAuth::parseToken()->authenticate();
        if ($workflow->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'input_data' => 'nullable|array',
            'browser_config' => 'nullable|array',
            'browser_config.headless' => 'sometimes|boolean',
            'browser_config.viewport' => 'sometimes|array',
            'browser_config.viewport.width' => 'sometimes|integer|min:800',
            'browser_config.viewport.height' => 'sometimes|integer|min:600',
            'browser_config.userAgent' => 'sometimes|string|max:512',
            'browser_config.timeout' => 'sometimes|integer|min:1000',
            'schedule' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        // Generate PM2 process name
        $pm2ProcessName = $this->generatePm2ProcessName($user, $workflow, $request->name);

        $execution = WorkflowExecution::create([
            'workflow_id' => $workflowId,
            'name' => $request->name,
            'input_data' => $request->input_data ?? [],
            'browser_config' => $request->browser_config ?? WorkflowExecution::getDefaultBrowserConfig(),
            'status' => 'stopped',
            'schedule' => $request->schedule,
            'pm2_process_name' => $pm2ProcessName,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Execution created successfully',
            'data' => $execution
        ], 201);
    }

    /**
     * Get single execution
     */
    public function show($workflowId, $executionId)
    {
        $execution = WorkflowExecution::with(['workflow', 'runs' => function ($q) {
            $q->latest()->limit(10);
        }])->findOrFail($executionId);

        $user = JWTAuth::parseToken()->authenticate();
        if ($execution->workflow->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access'
            ], 403);
        }

        return response()->json([
            'success' => true,
            'data' => $execution
        ]);
    }

    /**
     * Update execution
     */
    public function update(Request $request, $workflowId, $executionId)
    {
        $execution = WorkflowExecution::with('workflow')->findOrFail($executionId);

        $user = JWTAuth::parseToken()->authenticate();
        if ($execution->workflow->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'input_data' => 'sometimes|nullable|array',
            'browser_config' => 'sometimes|nullable|array',
            'schedule' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $execution->update($request->only([
            'name',
            'input_data',
            'browser_config',
            'schedule'
        ]));

        return response()->json([
            'success' => true,
            'message' => 'Execution updated successfully',
            'data' => $execution
        ]);
    }

    /**
     * Delete execution
     */
    public function destroy($workflowId, $executionId)
    {
        $execution = WorkflowExecution::with('workflow')->findOrFail($executionId);

        $user = JWTAuth::parseToken()->authenticate();
        if ($execution->workflow->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access'
            ], 403);
        }

        // Prevent deletion if execution is online
        if ($execution->isOnline()) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete online execution. Stop it first.'
            ], 400);
        }

        $execution->delete();

        return response()->json([
            'success' => true,
            'message' => 'Execution deleted successfully'
        ]);
    }

    /**
     * Generate unique PM2 process name
     */
    private function generatePm2ProcessName($user, $workflow, $executionName): string
    {
        $safeName = preg_replace('/[^a-zA-Z0-9_-]/', '_', $executionName);
        $safeName = substr($safeName, 0, 32);
        $userId = $user->id;
        $workflowId = $workflow->id;
        $timestamp = time();

        return "wf_{$workflowId}_exec_{$userId}_{$safeName}_{$timestamp}";
    }
}
