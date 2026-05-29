<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Podcast;
use Illuminate\Http\Request;

class PodcastController extends Controller
{
    public function index()
    {
        $podcasts = Podcast::with(['category', 'creator'])->get();
        return response()->json(['message' => 'Data podcast', 'data' => $podcasts], 200);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id_podcast' => 'required|string|unique:podcast,id_podcast',
            'name' => 'required|string|max:150',
            'about' => 'nullable|string',
            'cover_image' => 'nullable|string',
            'id_podcast_category' => 'nullable|string|exists:podcastcategory,id_podcast_category',
            'id_user' => 'required|string|exists:User,id_user' // Constraint: Pembuat (creator) harus ada
        ]);

        $podcast = Podcast::create($validated);
        return response()->json(['message' => 'Podcast berhasil dibuat', 'data' => $podcast], 201);
    }

    public function show($id)
    {
        $podcast = Podcast::with(['episodes', 'category', 'creator'])->find($id);
        if (!$podcast) return response()->json(['message' => 'Podcast tidak ditemukan'], 404);
        return response()->json(['message' => 'Detail podcast', 'data' => $podcast], 200);
    }

    public function update(Request $request, $id)
    {
        $podcast = Podcast::find($id);
        if (!$podcast) return response()->json(['message' => 'Podcast tidak ditemukan'], 404);

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:150',
            'about' => 'nullable|string',
            'cover_image' => 'nullable|string',
            'id_podcast_category' => 'nullable|string|exists:podcastcategory,id_podcast_category',
            'id_user' => 'sometimes|required|string|exists:User,id_user'
        ]);

        $podcast->update($validated);
        return response()->json(['message' => 'Podcast berhasil diupdate', 'data' => $podcast], 200);
    }

    public function destroy($id)
    {
        $podcast = Podcast::find($id);
        if (!$podcast) return response()->json(['message' => 'Podcast tidak ditemukan'], 404);

        $podcast->delete();
        return response()->json(['message' => 'Podcast berhasil dihapus'], 200);
    }
}
