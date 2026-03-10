<?php
// app/Http/Controllers/WorkflowController.php

namespace App\Http\Controllers;

use App\Models\Workflow;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;

class WorkflowController extends Controller
{
    /**
     * Get all workflows for authenticated user
     */
    public function index(Request $request)
    {
        $query = Workflow::with(['executions'])
            ->where('user_id', $request->user()->id);

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $workflows = $query->latest()->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $workflows
        ]);
    }

    /**
     * Create new workflow
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'nodes' => 'required|array|min:1',
            'nodes.*.id' => 'required|string',
            'nodes.*.actionId' => 'required|string',
            'nodes.*.config' => 'required|array',
            'nodes.*.position' => 'required|array',
            'edges' => 'required|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $workflow = Workflow::create([
            'user_id' => $request->user()->id,
            'name' => $request->name,
            'description' => $request->description,
            'nodes' => $request->nodes,
            'edges' => $request->edges,
            'status' => 'draft',
            'version' => 1,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Workflow created successfully',
            'data' => $workflow
        ], 201);
    }

    /**
     * Get single workflow
     */
    public function show($id)
    {
        $workflow = Workflow::with('executions')->findOrFail($id);

        $user = JWTAuth::parseToken()->authenticate();
        if ($workflow->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access'
            ], 403);
        }

        return response()->json([
            'success' => true,
            'data' => $workflow
        ]);
    }

    /**
     * Update workflow
     */
    public function update(Request $request, $id)
    {
        $workflow = Workflow::findOrFail($id);

        $user = JWTAuth::parseToken()->authenticate();
        if ($workflow->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'nodes' => 'sometimes|required|array|min:1',
            'edges' => 'sometimes|required|array',
            'status' => 'sometimes|in:draft,active,inactive',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        // Increment version if nodes changed
        if ($request->has('nodes') && $request->nodes != $workflow->nodes) {
            $workflow->version += 1;
        }

        $workflow->update($request->only([
            'name',
            'description',
            'nodes',
            'edges',
            'status'
        ]));

        return response()->json([
            'success' => true,
            'message' => 'Workflow updated successfully',
            'data' => $workflow
        ]);
    }

    /**
     * Delete workflow
     */
    public function destroy($id)
    {
        $workflow = Workflow::findOrFail($id);

        $user = JWTAuth::parseToken()->authenticate();
        if ($workflow->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access'
            ], 403);
        }

        // Check for online executions
        if ($workflow->onlineExecutions()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete workflow with online executions. Stop all executions first.'
            ], 400);
        }

        $workflow->delete();

        return response()->json([
            'success' => true,
            'message' => 'Workflow deleted successfully'
        ]);
    }

    /**
     * Duplicate workflow
     */
    public function duplicate($id)
    {
        $workflow = Workflow::findOrFail($id);

        $user = JWTAuth::parseToken()->authenticate();
        if ($workflow->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access'
            ], 403);
        }

        $newWorkflow = $workflow->duplicate();

        return response()->json([
            'success' => true,
            'message' => 'Workflow duplicated successfully',
            'data' => $newWorkflow
        ], 201);
    }

    /**
     * Update workflow status
     */
    public function updateStatus(Request $request, $id)
    {
        $workflow = Workflow::findOrFail($id);

        $user = JWTAuth::parseToken()->authenticate();
        if ($workflow->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'status' => 'required|in:draft,active,inactive',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $workflow->update(['status' => $request->status]);

        return response()->json([
            'success' => true,
            'message' => 'Workflow status updated successfully',
            'data' => $workflow
        ]);
    }
}
