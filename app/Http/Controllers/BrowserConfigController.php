<?php

namespace App\Http\Controllers;

use App\Models\BrowserConfigField;
use App\Models\BrowserPreset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class BrowserConfigController extends Controller
{

    public function getFields()
    {
        return response()->json([
            'success' => true,
            'data'    => BrowserConfigField::getFields(),
        ]);
    }

    public function updateFields(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'fields' => 'required|array',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $row = BrowserConfigField::first();

        if ($row) {
            $row->update(['fields' => $request->fields]);
        } else {
            BrowserConfigField::create(['fields' => $request->fields]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Fields updated',
            'data'    => $request->fields,
        ]);
    }


    public function getPresets()
    {
        $presets = BrowserPreset::orderBy('sort_order')->orderBy('created_at')->get();

        return response()->json([
            'success' => true,
            'data'    => $presets,
        ]);
    }

    public function storePreset(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'        => 'required|string|max:100',
            'description' => 'nullable|string|max:255',
            'values'      => 'required|array',
            'sort_order'  => 'nullable|integer',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $preset = BrowserPreset::create([
            'name'        => $request->name,
            'description' => $request->description,
            'values'      => $request->values,
            'sort_order'  => $request->sort_order ?? BrowserPreset::max('sort_order') + 1,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Preset created',
            'data'    => $preset,
        ], 201);
    }

    public function updatePreset(Request $request, $id)
    {
        $preset = BrowserPreset::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name'        => 'sometimes|required|string|max:100',
            'description' => 'nullable|string|max:255',
            'values'      => 'sometimes|required|array',
            'sort_order'  => 'nullable|integer',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $preset->update($request->only(['name', 'description', 'values', 'sort_order']));

        return response()->json([
            'success' => true,
            'message' => 'Preset updated',
            'data'    => $preset->fresh(),
        ]);
    }

    public function destroyPreset($id)
    {
        BrowserPreset::findOrFail($id)->delete();

        return response()->json([
            'success' => true,
            'message' => 'Preset deleted',
        ]);
    }

    public function reorderPresets(Request $request)
    {
        foreach ($request->order as $index => $id) {
            BrowserPreset::where('id', $id)->update(['sort_order' => $index]);
        }

        return response()->json(['success' => true]);
    }


    public function all()
    {
        return response()->json([
            'success' => true,
            'data'    => [
                'fields'  => BrowserConfigField::getFields(),
                'presets' => BrowserPreset::orderBy('sort_order')->get(),
            ],
        ]);
    }
}
