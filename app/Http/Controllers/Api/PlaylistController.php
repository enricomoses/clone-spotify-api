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
        // PERBAIKAN: Menambahkan relasi 'collaborators' agar frontend bisa membaca pembuat playlist
        $playlists = Playlist::with(['category', 'collaborators'])->get();
        return response()->json(['message' => 'Data playlist', 'data' => $playlists], 200);
    }

    public function show($id)
    {
        // PERBAIKAN: Menggunakan query()->where() agar lebih konsisten
        $playlist = Playlist::with(['songs', 'collaborators', 'category'])
            ->where('id_playlist', $id)
            ->first();

        if (!$playlist) return response()->json(['message' => 'Playlist tidak ditemukan'], 404);

        return response()->json(['message' => 'Detail playlist', 'data' => $playlist], 200);
    }

    // --- CRUD BASIC ---
    public function store(Request $request)
    {
        // PERBAIKAN: Menambahkan validasi id_user untuk menangkap siapa yang membuat playlist
        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'description' => 'nullable|string',
            'cover_image' => 'nullable|string',
            'id_playlist_category' => 'nullable|string|exists:playlistcategory,id_playlist_category',
            'id_user' => 'required|string|exists:User,id_user'
        ]);

        // Ekstrak data khusus untuk tabel playlist
        $playlistData = [
            'id_playlist' => Str::uuid()->toString(),
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'cover_image' => $validated['cover_image'] ?? null,
            'id_playlist_category' => $validated['id_playlist_category'] ?? null,
        ];

        // 1. Simpan ke tabel playlist
        $playlist = Playlist::create($playlistData);

        // 2. Simpan relasinya ke tabel playlist_collaborator dengan role 'Owner'
        $playlist->collaborators()->attach($validated['id_user'], ['role' => 'Owner']);

        // 3. Load relasi agar data yang dikembalikan ke frontend sudah langsung lengkap
        $playlist->load('collaborators');

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
