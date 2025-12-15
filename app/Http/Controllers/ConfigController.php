<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Models\AutomationConfig;

class ConfigController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'provider' => 'required',
        ]);
        $configs = AutomationConfig::where('provider' , $request->provider )->orderBy('created_at', 'desc')->get();
        $configs->transform(function ($config) {
            $config->setup = json_decode($config->setup, true);
            return $config;
        });
        return response()->json([
            'message' => 'All configs retrieved successfully',
            'data'    => $configs
        ], 200);
    }
    public function store(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();

        $request->validate([
            'name'  => 'required|string|max:255',
            'setup' => 'required|array',
            'provider' => 'required|string', 
        ]);
        $setupJson = json_encode($request->setup, JSON_UNESCAPED_UNICODE);
        $config = AutomationConfig::create([
            'name'        => $request->name,
            'setup'       => $setupJson,
            'provider'    => $request->provider,
            'created_at'  => now(),
            'created_by'  => $user->id,
            'updated_at'  => null,
            'updated_by'  => null,
        ]);
        return response()->json([
            'message' => 'Config created successfully',
            'data'    => $config
        ], 201);
    }
    public function update(Request $request){
        $user = JWTAuth::parseToken()->authenticate();
        $request->validate([
            'id'=> 'required',
            'name'  => 'required|string|max:255',
            'setup' => 'required|array', 
        ]);
        $setupJson = json_encode($request->setup, JSON_UNESCAPED_UNICODE);
        $config = AutomationConfig::where('id',$request->id)->update([
            'name'        => $request->name,
            'setup'       => $setupJson,
            'updated_at'  => now(),
            'updated_by'  => $user->id,
        ]);
        return response()->json([
            'message' => 'Config updated successfully',
            'data'    => $config
        ], 200);
    }
    public function delete($id)
    {
        try {
            $config = AutomationConfig::find($id);

            if (!$config) {
                return response()->json([
                    'success' => false,
                    'message' => 'Automation config not found'
                ], 404);
            }

            $config->delete();

            return response()->json([
                'success' => true,
                'message' => 'Automation config deleted successfully'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete automation config',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
