<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Episode;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB; // PENTING: Tambahkan ini untuk akses Database Query Builder

class EpisodeController extends Controller
{
    public function index()
    {
        $episodes = Episode::with('podcast')->get();
        return response()->json(['message' => 'Data episode', 'data' => $episodes], 200);
    }

    public function show($id)
    {
        // PERBAIKAN: Menggunakan where()->first()
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
            'id_podcast' => 'required|string|exists:podcast,id_podcast'
        ]);

        $validated['id_episode'] = Str::uuid()->toString();
        $episode = Episode::create($validated);

        return response()->json(['message' => 'Episode berhasil ditambahkan', 'data' => $episode], 201);
    }

    public function update(Request $request, $id)
    {
        $episode = Episode::query()->where('id_episode', $id)->first();
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
        $episode = Episode::query()->where('id_episode', $id)->first();
        if (!$episode) {
            return response()->json(['message' => 'Episode tidak ditemukan'], 404);
        }

        // PERBAIKAN: Hapus riwayat streaming yang terkait dengan episode ini terlebih dahulu
        // Mencegah error Foreign Key Constraint di PostgreSQL
        DB::table('episode_streaminghistory')->where('id_episode', $id)->delete();

        // Hapus episode utama
        $episode->delete();

        return response()->json(['message' => 'Episode berhasil dihapus'], 200);
    }

    // --- FITUR MENCATAT STREAMING HISTORY (ENDPOINT TAMBAHAN) ---
    public function recordStream(Request $request, $id)
    {
        $episode = Episode::query()->where('id_episode', $id)->first();
        if (!$episode) return response()->json(['message' => 'Episode tidak ditemukan'], 404);

        $request->validate([
            'id_user' => 'required|exists:User,id_user',
            'play_duration' => 'nullable|integer|min:1' // Durasi seberapa lama user mendengarkan
        ]);

        // Catat sebagai transaksi baru di tabel history
        DB::table('episode_streaminghistory')->insert([
            'id_stream' => Str::uuid()->toString(),
            'id_user' => $request->id_user,
            'id_episode' => $id,
            'play_duration' => $request->play_duration ?? null,
            'played_at' => now(), // Waktu diputar otomatis mengambil waktu server saat ini
        ]);

        return response()->json(['message' => 'Riwayat streaming episode berhasil dicatat'], 201);
    }
}
