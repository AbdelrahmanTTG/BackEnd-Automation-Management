<?php
// app/Http/Controllers/WorkflowExecutionController.php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Workflow;
use App\Models\WorkflowExecution;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;

class WorkflowExecutionController extends Controller
{
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

        // if ($workflow->status !== 'active') {
        //     return response()->json([
        //         'success' => false,
        //         'message' => 'Workflow is not active'
        //     ], 400);
        // }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'input_data' => 'nullable|array',
            'schedule' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $execution = WorkflowExecution::create([
            'workflow_id' => $workflowId,
            'name' => $request->name,
            'input_data' => $request->input_data,
            'status' => 'active',
            'schedule' => $request->schedule,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Execution created successfully',
            'data' => $execution
        ], 201);
    }

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
            'status' => 'sometimes|in:active,paused,stopped',
            'schedule' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $execution->update($request->only(['name', 'input_data', 'status', 'schedule']));

        return response()->json([
            'success' => true,
            'message' => 'Execution updated successfully',
            'data' => $execution
        ]);
    }

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

        $execution->delete();

        return response()->json([
            'success' => true,
            'message' => 'Execution deleted successfully'
        ]);
    }

    public function start($workflowId, $executionId)
    {
        return $this->updateExecutionStatus($executionId, 'launching');
    }

    public function stop($workflowId, $executionId)
    {
        return $this->updateExecutionStatus($executionId, 'stopped');
    }

    private function updateExecutionStatus($executionId, $status)
    {
        $execution = WorkflowExecution::with('workflow')->findOrFail($executionId);

        $user = JWTAuth::parseToken()->authenticate();
        if ($execution->workflow->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access'
            ], 403);
        }

        $execution->update(['status' => $status]);

        $messages = [
            'launching' => 'Execution is launching',
            'online'    => 'Execution is online',
            'stopped'   => 'Execution stopped successfully',
            'errored'   => 'Execution encountered an error',
        ];

        return response()->json([
            'success' => true,
            'message' => $messages[$status],
            'data' => $execution
        ]);
    }
}
