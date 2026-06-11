<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Episode;
use App\Models\Podcast;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class EpisodeController extends Controller
{
    public function index()
    {
        $episodes = Episode::with('podcast')->get();
        return response()->json(['message' => 'Data episode', 'data' => $episodes], 200);
    }

    public function show($id)
    {
        $episode = Episode::with('podcast')->where('id_episode', $id)->first();
        if (!$episode) return response()->json(['message' => 'Episode tidak ditemukan'], 404);

        return response()->json(['message' => 'Detail episode', 'data' => $episode], 200);
    }

    // --- CRUD BASIC ---
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
            'id_podcast' => 'required|string|exists:podcast,id_podcast',
            'id_user' => 'required|string|exists:User,id_user' // User yang melakukan request
        ]);

        // Validasi ownership: hanya pemilik podcast yang bisa tambah episode
        $podcast = Podcast::where('id_podcast', $validated['id_podcast'])->first();
        if ($podcast->id_user !== $validated['id_user']) {
            return response()->json([
                'message' => 'Anda bukan pemilik podcast ini. Hanya pemilik yang bisa menambah episode.'
            ], 403);
        }

        $episodeData = [
            'id_episode' => Str::uuid()->toString(),
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'published_at' => $validated['published_at'],
            'duration' => $validated['duration'],
            'audio_url' => $validated['audio_url'],
            'video_url' => $validated['video_url'] ?? null,
            'thumbnail_image' => $validated['thumbnail_image'] ?? null,
            'id_podcast' => $validated['id_podcast'],
        ];

        $episode = Episode::create($episodeData);
        $episode->load('podcast');

        return response()->json(['message' => 'Episode berhasil ditambahkan', 'data' => $episode], 201);
    }

    public function update(Request $request, $id)
    {
        $episode = Episode::query()->where('id_episode', $id)->first();
        if (!$episode) return response()->json(['message' => 'Episode tidak ditemukan'], 404);

        // Validasi ownership: hanya pemilik podcast yang bisa update episode
        $request->validate([
            'id_user' => 'required|string|exists:User,id_user'
        ]);

        $podcast = Podcast::where('id_podcast', $episode->id_podcast)->first();
        if ($podcast->id_user !== $request->id_user) {
            return response()->json([
                'message' => 'Anda bukan pemilik podcast ini. Hanya pemilik yang bisa mengupdate episode.'
            ], 403);
        }

        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:150',
            'description' => 'nullable|string',
            'published_at' => 'sometimes|required|date|before_or_equal:now',
            'duration' => 'sometimes|required|integer|min:1',
            'audio_url' => 'sometimes|required|string',
            'video_url' => 'nullable|string',
            'thumbnail_image' => 'nullable|string',
        ]);

        $episode->update($validated);
        $episode->load('podcast');
        return response()->json(['message' => 'Episode berhasil diupdate', 'data' => $episode], 200);
    }

    public function destroy(Request $request, $id)
    {
        $episode = Episode::query()->where('id_episode', $id)->first();
        if (!$episode) {
            return response()->json(['message' => 'Episode tidak ditemukan'], 404);
        }

        // Validasi ownership: hanya pemilik podcast yang bisa hapus episode
        $idUser = $request->query('id_user') ?? $request->id_user;
        if ($idUser) {
            $podcast = Podcast::where('id_podcast', $episode->id_podcast)->first();
            if ($podcast && $podcast->id_user !== $idUser) {
                return response()->json([
                    'message' => 'Anda bukan pemilik podcast ini. Hanya pemilik yang bisa menghapus episode.'
                ], 403);
            }
        }

        // Hapus riwayat streaming yang terkait dengan episode ini terlebih dahulu
        DB::table('episode_streaminghistory')->where('id_episode', $id)->delete();

        // Hapus episode utama
        $episode->delete();

        return response()->json(['message' => 'Episode berhasil dihapus'], 200);
    }
}
