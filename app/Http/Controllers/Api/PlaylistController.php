<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Playlist;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PlaylistController extends Controller
{
    public function index()
    {
        $playlists = Playlist::with('category')->get();
        return response()->json(['message' => 'Data playlist', 'data' => $playlists], 200);
    }

    public function show($id)
    {
        // Menarik data playlist yang sangat kaya (relasi ke lagu dan kolaborator)
        $playlist = Playlist::with(['songs', 'collaborators', 'category'])->find($id);
        if (!$playlist) return response()->json(['message' => 'Playlist tidak ditemukan'], 404);

        return response()->json(['message' => 'Detail playlist', 'data' => $playlist], 200);
    }

    // --- CRUD BASIC ---
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'description' => 'nullable|string',
            'cover_image' => 'nullable|string',
            'id_playlist_category' => 'nullable|string|exists:playlistcategory,id_playlist_category'
        ]);

        $validated['id_playlist'] = Str::uuid()->toString();
        $playlist = Playlist::create($validated);

        return response()->json(['message' => 'Playlist berhasil dibuat', 'data' => $playlist], 201);
    }

    public function update(Request $request, $id)
    {
        $playlist = Playlist::query()->where('id_playlist', $id)->first();
        if (!$playlist) return response()->json(['message' => 'Playlist tidak ditemukan'], 404);

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:150',
            'description' => 'nullable|string',
            'cover_image' => 'nullable|string',
            'id_playlist_category' => 'nullable|string|exists:playlistcategory,id_playlist_category'
        ]);

        $playlist->update($validated);
        return response()->json(['message' => 'Playlist berhasil diupdate', 'data' => $playlist], 200);
    }

   public function destroy($id)
{
    if (!Playlist::query()->where('id_playlist', $id)->delete()) {
        return response()->json(['message' => 'Playlist tidak ditemukan'], 404);
    }
    return response()->json(['message' => 'Playlist berhasil dihapus'], 200);
}

    // --- MANAJEMEN ISI PLAYLIST ---
    public function addSong(Request $request, $id)
    {
        $playlist = Playlist::query()->where('id_playlist', $id)->first();
        $request->validate(['song_id' => 'required|exists:song,id_song']);

        // syncWithoutDetaching mencegah duplikasi data lagu di dalam playlist
        $playlist->songs()->syncWithoutDetaching([$request->song_id]);
        return response()->json(['message' => 'Lagu berhasil ditambahkan ke playlist'], 200);
    }

    public function removeSong($id, $song_id)
    {
        $playlist = Playlist::query()->where('id_playlist', $id)->first();
        $playlist->songs()->detach($song_id);
        return response()->json(['message' => 'Lagu dihapus dari playlist'], 200);
    }

    // --- MANAJEMEN KOLABORATOR ---
    public function addCollaborator(Request $request, $id)
    {
        $playlist = Playlist::query()->where('id_playlist', $id)->first();
        $request->validate([
            'user_id' => 'required|exists:User,id_user',
            'role' => 'required|string'
        ]);

        // Contoh payload: { "user_id": "U_02", "role": "Editor" } untuk mengundang Rex Clement
        $playlist->collaborators()->syncWithoutDetaching([
            $request->user_id => ['role' => $request->role]
        ]);
        return response()->json(['message' => 'Kolaborator berhasil ditambahkan'], 200);
    }

    public function removeCollaborator($id, $user_id)
    {
        $playlist = Playlist::query()->where('id_playlist', $id)->first();

        // Tambahan validasi agar aplikasi tidak crash jika playlist tidak ada
        if (!$playlist) {
            return response()->json(['message' => 'Playlist tidak ditemukan'], 404);
        }

        $playlist->collaborators()->detach($user_id);
        return response()->json(['message' => 'Kolaborator dihapus'], 200);
    }
}
