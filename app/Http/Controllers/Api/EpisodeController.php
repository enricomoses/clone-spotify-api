<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Episode;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class EpisodeController extends Controller
{
    public function index()
    {
        $episodes = Episode::with('podcast')->get();
        return response()->json(['message' => 'Data episode', 'data' => $episodes], 200);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:150',
            'description' => 'nullable|string',
            'published_at' => 'required|date|before_or_equal:now',
            'duration' => 'required|integer|min:1',
            'audio_url' => 'required|string',
            'video_url' => 'nullable|string',
            'thumbnail_image' => 'nullable|string',
            'id_podcast' => 'required|string|exists:podcast,id_podcast'
        ]);

        $validated['id_episode'] = Str::uuid()->toString();
        $episode = Episode::create($validated);

        return response()->json(['message' => 'Episode berhasil ditambahkan', 'data' => $episode], 201);
    }

    public function show($id)
    {
        $episode = Episode::with('podcast')->find($id);
        if (!$episode) return response()->json(['message' => 'Episode tidak ditemukan'], 404);
        return response()->json(['message' => 'Detail episode', 'data' => $episode], 200);
    }

    public function update(Request $request, $id)
    {
        $episode = Episode::find($id);
        if (!$episode) return response()->json(['message' => 'Episode tidak ditemukan'], 404);

        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:150',
            'description' => 'nullable|string',
            'published_at' => 'sometimes|required|date|before_or_equal:now',
            'duration' => 'sometimes|required|integer|min:1',
            'audio_url' => 'sometimes|required|string',
            'video_url' => 'nullable|string',
            'thumbnail_image' => 'nullable|string',
            'id_podcast' => 'sometimes|required|string|exists:podcast,id_podcast'
        ]);

        $episode->update($validated);
        return response()->json(['message' => 'Episode berhasil diupdate', 'data' => $episode], 200);
    }

    public function destroy($id)
    {
        $episode = Episode::find($id);
        if (!$episode) return response()->json(['message' => 'Episode tidak ditemukan'], 404);

        $episode->delete();
        return response()->json(['message' => 'Episode berhasil dihapus'], 200);
    }
}
