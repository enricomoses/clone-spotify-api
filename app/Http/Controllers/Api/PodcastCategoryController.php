<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PodcastCategory;
use Illuminate\Http\Request;

class PodcastCategoryController extends Controller
{
    public function index()
    {
        $categories = PodcastCategory::all();
        return response()->json(['message' => 'Data kategori podcast', 'data' => $categories], 200);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id_podcast_category' => 'required|string|unique:podcastcategory,id_podcast_category',
            'name' => 'required|string|max:100',
            'cover_image' => 'nullable|string',
            'icon' => 'nullable|string'
        ]);

        $category = PodcastCategory::create($validated);
        return response()->json(['message' => 'Kategori podcast berhasil ditambahkan', 'data' => $category], 201);
    }

    public function show($id)
    {
        $category = PodcastCategory::with('podcasts')->where('id_podcast_category', $id)->first();
        if (!$category) return response()->json(['message' => 'Kategori podcast tidak ditemukan'], 404);

        return response()->json(['message' => 'Detail kategori podcast', 'data' => $category], 200);
    }

    public function update(Request $request, $id)
    {
        $category = PodcastCategory::where('id_podcast_category', $id)->first();
        if (!$category) return response()->json(['message' => 'Kategori podcast tidak ditemukan'], 404);

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:100',
            'cover_image' => 'nullable|string',
            'icon' => 'nullable|string'
        ]);

        $category->update($validated);
        return response()->json(['message' => 'Kategori podcast berhasil diupdate', 'data' => $category], 200);
    }

    public function destroy($id)
    {
        $category = PodcastCategory::where('id_podcast_category', $id)->first();
        if (!$category) return response()->json(['message' => 'Kategori podcast tidak ditemukan'], 404);

        $category->delete();
        return response()->json(['message' => 'Kategori podcast berhasil dihapus'], 200);
    }
}
