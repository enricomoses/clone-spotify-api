<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PlaylistCategory;
use Illuminate\Http\Request;

class PlaylistCategoryController extends Controller
{
    public function index()
    {
        $categories = PlaylistCategory::all();
        return response()->json(['message' => 'Data kategori playlist', 'data' => $categories], 200);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id_playlist_category' => 'required|string|unique:playlistcategory,id_playlist_category',
            'name' => 'required|string|max:100',
            'cover_image' => 'nullable|string',
            'icon' => 'nullable|string'
        ]);

        $category = PlaylistCategory::create($validated);
        return response()->json(['message' => 'Kategori berhasil ditambahkan', 'data' => $category], 201);
    }

    public function show($id)
    {
        $category = PlaylistCategory::with('playlists')->find($id);
        if (!$category) return response()->json(['message' => 'Kategori tidak ditemukan'], 404);
        return response()->json(['message' => 'Detail kategori', 'data' => $category], 200);
    }

    public function update(Request $request, $id)
    {
        $category = PlaylistCategory::find($id);
        if (!$category) return response()->json(['message' => 'Kategori tidak ditemukan'], 404);

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:100',
            'cover_image' => 'nullable|string',
            'icon' => 'nullable|string'
        ]);

        $category->update($validated);
        return response()->json(['message' => 'Kategori berhasil diupdate', 'data' => $category], 200);
    }

    public function destroy($id)
    {
        $category = PlaylistCategory::find($id);
        if (!$category) return response()->json(['message' => 'Kategori tidak ditemukan'], 404);

        $category->delete();
        return response()->json(['message' => 'Kategori berhasil dihapus'], 200);
    }
}
