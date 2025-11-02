<?php

namespace App\Http\Controllers;
use App\Models\Icon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
class IconController extends Controller
{
    //
    public function addIcon(Request $request)
    {
        $request->validate([
            'type' => 'required|string',
            'icon' => 'required|file|mimes:png,jpg,jpeg,svg|max:2048',
            'status' => 'string|in:active,inactive',
        ]);
    
        // Store the uploaded file
        if ($request->hasFile('icon')) {
            $iconPath = $request->file('icon')->store('icons'); // Stores the file in the 'icons' directory
        }
    
        // Create the icon record
        $icon = Icon::create([
            'type' => $request->type,
            'icon_path' => $iconPath, // Store the path in the database
            'status' => $request->status ?? 'active',
        ]);
    
        return response()->json(['success' => true, 'icon' => $icon], 201);
    }
public function getIconsByType($type)
{
    $icons = Icon::where('type', $type)->get();
    return response()->json([ 'status' => 200,'success' => true, 'icons' => $icons], 200);
}
public function updateIcon(Request $request, $id)
{
    $icon = Icon::findOrFail($id);
    $icon->update($request->all());
    return response()->json([ 'status' => 200,'success' => true, 'icon' => $icon], 200);
}
public function deleteIcon($id)
{
    $icon = Icon::findOrFail($id);
    $icon->delete();
    return response()->json([ 'status' => 200,'success' => true, 'message' => 'Icon deleted'], 200);
}

}
