<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PlaylistCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PlaylistCategoryController extends Controller
{
    public function index()
    {
        $categories = PlaylistCategory::with('playlists')->get()->map(function ($category) {
            $category->type = 'playlist';
            return $category;
        });
        return response()->json(['message' => 'Playlist category data', 'data' => $categories], 200);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'cover_image' => 'required|string',
            'icon' => 'required|string'
        ]);

        $validated['id_playlist_category'] = Str::uuid()->toString();
        $category = PlaylistCategory::create($validated);

        return response()->json(['message' => 'Category added successfully', 'data' => $category], 201);
    }

    public function show($id)
    {
        $category = PlaylistCategory::with('playlists')
            ->where('id_playlist_category', $id)
            ->first();

        if (!$category) return response()->json(['message' => 'Category not found'], 404);
        
        $category->type = 'playlist';
        return response()->json(['message' => 'Category detail', 'data' => $category], 200);
    }

    public function update(Request $request, $id)
    {
        $category = PlaylistCategory::query()->where('id_playlist_category', $id)->first();
        if (!$category) return response()->json(['message' => 'Category not found'], 404);

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:100',
            'cover_image' => 'sometimes|required|string',
            'icon' => 'sometimes|required|string'
        ]);

        $category->update($validated);
        return response()->json(['message' => 'Category updated successfully', 'data' => $category], 200);
    }

    public function destroy($id)
    {
        $category = PlaylistCategory::query()->where('id_playlist_category', $id)->first();

        if (!$category) {
            return response()->json(['message' => 'Category not found'], 404);
        }

        // ==========================================
        // RELATIONS CLEANUP PROCESS
        // ==========================================
        DB::table('playlist')
            ->where('id_playlist_category', $id)
            ->update(['id_playlist_category' => null]);

        $category->delete();

        return response()->json(['message' => 'Category deleted successfully and associated playlists have been updated'], 200);
    }
}
