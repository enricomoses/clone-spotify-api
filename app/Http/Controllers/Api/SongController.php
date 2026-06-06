<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Song;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SongController extends Controller
{
    public function index()
    {
        // Menampilkan semua lagu beserta data albumnya (jika ada)
        $songs = Song::with('album')->get();
        return response()->json(['message' => 'Data lagu berhasil diambil', 'data' => $songs], 200);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:150',
            'duration' => 'required|integer|min:1',
            'id_album' => 'nullable|string|exists:album,id_album',
            'audio_url' => 'required|string'
        ]);

        $validated['id_song'] = Str::uuid()->toString();
        $song = Song::create($validated);

        return response()->json(['message' => 'Lagu berhasil ditambahkan', 'data' => $song], 201);
    }

    public function show($id)
    {
        $song = Song::with('album')->find($id);
        if (!$song) return response()->json(['message' => 'Lagu tidak ditemukan'], 404);

        return response()->json(['message' => 'Detail lagu', 'data' => $song], 200);
    }

    public function update(Request $request, $id)
    {
        $song = Song::find($id);
        if (!$song) return response()->json(['message' => 'Lagu tidak ditemukan'], 404);

        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:150',
            'duration' => 'sometimes|required|integer|min:1',
            'id_album' => 'nullable|string|exists:album,id_album',
            'audio_url' => 'sometimes|required|string',
        ]);

        $song->update($validated);
        return response()->json(['message' => 'Lagu berhasil diupdate', 'data' => $song], 200);
    }

    public function destroy($id)
    {
        $song = Song::find($id);
        if (!$song) return response()->json(['message' => 'Lagu tidak ditemukan'], 404);

        $song->delete();
        return response()->json(['message' => 'Lagu berhasil dihapus'], 200);
    }

    public function discoverWeekly()
    {
        // Mengambil lagu beserta jumlah riwayat putarannya (Agregasi COUNT)
        // lalu diurutkan dari yang terbanyak, dibatasi 50 lagu.
        $topSongs = Song::with('album')
            ->withCount('streamingHistories')
            ->orderBy('streaming_histories_count', 'desc')
            ->take(50)
            ->get();

        return response()->json([
            'message' => 'Discover Weekly (Top 50 Lagu)',
            'data' => $topSongs
        ], 200);
    }
}
