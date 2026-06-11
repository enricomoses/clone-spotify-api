<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Playlist;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB; // PENTING: Tambahkan ini untuk eksekusi query pembersihan

class PlaylistController extends Controller
{
    public function index()
    {
        $playlists = Playlist::with(['category', 'collaborators'])->get();
        return response()->json(['message' => 'Data playlist', 'data' => $playlists], 200);
    }

    public function show($id)
    {
        $playlist = Playlist::with(['songs', 'collaborators', 'category'])
            ->where('id_playlist', $id)
            ->first();

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
            'id_playlist_category' => 'nullable|string|exists:playlistcategory,id_playlist_category',
            'id_user' => 'required|string|exists:User,id_user'
        ]);

        $playlistData = [
            'id_playlist' => Str::uuid()->toString(),
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'cover_image' => $validated['cover_image'] ?? null,
            'id_playlist_category' => $validated['id_playlist_category'] ?? null,
        ];

        $playlist = Playlist::create($playlistData);
        $playlist->collaborators()->attach($validated['id_user'], ['role' => 'Owner']);
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
        $playlist = Playlist::query()->where('id_playlist', $id)->first();

        if (!$playlist) {
            return response()->json(['message' => 'Playlist tidak ditemukan'], 404);
        }

        // ==========================================
        // PROSES CASCADE DELETE (PEMBERSIHAN RELASI)
        // ==========================================

        // 1. Putus relasi dengan kolaborator / pembuat (tabel playlist_collaborator)
        $playlist->collaborators()->detach();

        // 2. Putus relasi dengan lagu-lagu di dalam playlist (tabel playlist_song)
        if (method_exists($playlist, 'songs')) {
            $playlist->songs()->detach();
        } else {
            DB::table('playlist_song')->where('id_playlist', $id)->delete();
        }

        // 3. Putus relasi dengan user yang mem-follow playlist ini (tabel user_followplaylist)
        DB::table('user_followplaylist')->where('id_playlist', $id)->delete();

        // 4. Setelah semua "akar" dibersihkan, hapus playlist utama dengan aman
        $playlist->delete();

        return response()->json(['message' => 'Playlist beserta seluruh relasinya berhasil dihapus'], 200);
    }

    // --- MANAJEMEN ISI PLAYLIST ---
    public function addSong(Request $request, $id)
    {
        $playlist = Playlist::query()->where('id_playlist', $id)->first();
        $request->validate(['song_id' => 'required|exists:song,id_song']);

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

        if (!$playlist) {
            return response()->json(['message' => 'Playlist tidak ditemukan'], 404);
        }

        $playlist->collaborators()->detach($user_id);
        return response()->json(['message' => 'Kolaborator dihapus'], 200);
    }
}
