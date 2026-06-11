<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Playlist;
use App\Models\PlaylistCollaborator;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class PlaylistController extends Controller
{
    public function index()
    {
        $playlists = Playlist::with(['category', 'collaborators', 'songs'])->get();
        return response()->json(['message' => 'Data playlist', 'data' => $playlists], 200);
    }

    public function show($id)
    {
        $playlist = Playlist::with(['songs.artists', 'collaborators', 'category', 'followers'])
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

        // Otomatis buat pivot playlist_collaborator dengan role 'Owner'
        $playlist->collaborators()->attach($validated['id_user'], ['role' => 'Owner']);
        $playlist->load(['collaborators', 'songs']);

        return response()->json(['message' => 'Playlist berhasil dibuat', 'data' => $playlist], 201);
    }

    public function update(Request $request, $id)
    {
        $playlist = Playlist::query()->where('id_playlist', $id)->first();
        if (!$playlist) return response()->json(['message' => 'Playlist tidak ditemukan'], 404);

        // Validasi: hanya Owner yang boleh update playlist
        $request->validate([
            'id_user' => 'required|string|exists:User,id_user'
        ]);

        $isOwner = PlaylistCollaborator::where('id_playlist', $id)
            ->where('id_user', $request->id_user)
            ->where('role', 'Owner')
            ->exists();

        if (!$isOwner) {
            return response()->json(['message' => 'Hanya Owner yang bisa mengupdate playlist'], 403);
        }

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:150',
            'description' => 'nullable|string',
            'cover_image' => 'nullable|string',
            'id_playlist_category' => 'nullable|string|exists:playlistcategory,id_playlist_category'
        ]);

        $playlist->update($validated);
        $playlist->load(['collaborators', 'songs', 'category']);
        return response()->json(['message' => 'Playlist berhasil diupdate', 'data' => $playlist], 200);
    }

    public function destroy(Request $request, $id)
    {
        $playlist = Playlist::query()->where('id_playlist', $id)->first();

        if (!$playlist) {
            return response()->json(['message' => 'Playlist tidak ditemukan'], 404);
        }

        // Validasi: hanya Owner yang boleh hapus playlist
        $idUser = $request->query('id_user') ?? $request->id_user;
        if (!$idUser) {
            return response()->json(['message' => 'Parameter id_user diperlukan untuk validasi ownership'], 400);
        }

        $isOwner = PlaylistCollaborator::where('id_playlist', $id)
            ->where('id_user', $idUser)
            ->where('role', 'Owner')
            ->exists();

        if (!$isOwner) {
            return response()->json(['message' => 'Hanya Owner yang bisa menghapus playlist'], 403);
        }

        // ==========================================
        // PROSES CASCADE DELETE (PEMBERSIHAN RELASI)
        // ==========================================

        // 1. Putus relasi dengan kolaborator / pembuat (tabel playlist_collaborator)
        $playlist->collaborators()->detach();

        // 2. Putus relasi dengan lagu-lagu di dalam playlist (tabel playlist_song)
        $playlist->songs()->detach();

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
        if (!$playlist) return response()->json(['message' => 'Playlist tidak ditemukan'], 404);

        $request->validate([
            'song_id' => 'required|exists:song,id_song',
            'id_user' => 'required|string|exists:User,id_user'
        ]);

        // Validasi: hanya Owner atau Editor yang boleh menambah lagu
        $hasAccess = PlaylistCollaborator::where('id_playlist', $id)
            ->where('id_user', $request->id_user)
            ->whereIn('role', ['Owner', 'Editor'])
            ->exists();

        if (!$hasAccess) {
            return response()->json(['message' => 'Hanya Owner atau Editor yang bisa menambah lagu ke playlist'], 403);
        }

        $playlist->songs()->syncWithoutDetaching([$request->song_id]);
        return response()->json(['message' => 'Lagu berhasil ditambahkan ke playlist'], 200);
    }

    public function removeSong(Request $request, $id, $song_id)
    {
        $playlist = Playlist::query()->where('id_playlist', $id)->first();
        if (!$playlist) return response()->json(['message' => 'Playlist tidak ditemukan'], 404);

        // Validasi: hanya Owner atau Editor yang boleh menghapus lagu
        $idUser = $request->query('id_user') ?? $request->id_user;
        if (!$idUser) {
            return response()->json(['message' => 'Parameter id_user diperlukan untuk validasi'], 400);
        }

        $hasAccess = PlaylistCollaborator::where('id_playlist', $id)
            ->where('id_user', $idUser)
            ->whereIn('role', ['Owner', 'Editor'])
            ->exists();

        if (!$hasAccess) {
            return response()->json(['message' => 'Hanya Owner atau Editor yang bisa menghapus lagu dari playlist'], 403);
        }

        $playlist->songs()->detach($song_id);
        return response()->json(['message' => 'Lagu dihapus dari playlist'], 200);
    }

    // --- MANAJEMEN KOLABORATOR ---
    public function addCollaborator(Request $request, $id)
    {
        $playlist = Playlist::query()->where('id_playlist', $id)->first();
        if (!$playlist) return response()->json(['message' => 'Playlist tidak ditemukan'], 404);

        $request->validate([
            'user_id' => 'required|exists:User,id_user',
            'id_user' => 'required|string|exists:User,id_user' // User yang melakukan request (harus Owner)
        ]);

        // Validasi: hanya Owner yang boleh menambah kolaborator
        $isOwner = PlaylistCollaborator::where('id_playlist', $id)
            ->where('id_user', $request->id_user)
            ->where('role', 'Owner')
            ->exists();

        if (!$isOwner) {
            return response()->json(['message' => 'Hanya Owner yang bisa menambah kolaborator'], 403);
        }

        // Cek apakah user yang akan ditambah sudah jadi kolaborator
        $alreadyCollaborator = PlaylistCollaborator::where('id_playlist', $id)
            ->where('id_user', $request->user_id)
            ->exists();

        if ($alreadyCollaborator) {
            return response()->json(['message' => 'User sudah menjadi kolaborator di playlist ini'], 400);
        }

        // Kolaborator yang ditambahkan selalu mendapat role 'Editor'
        $playlist->collaborators()->attach($request->user_id, ['role' => 'Editor']);
        $playlist->load('collaborators');

        return response()->json(['message' => 'Kolaborator berhasil ditambahkan dengan role Editor', 'data' => $playlist], 200);
    }

    public function removeCollaborator(Request $request, $id, $user_id)
    {
        $playlist = Playlist::query()->where('id_playlist', $id)->first();

        if (!$playlist) {
            return response()->json(['message' => 'Playlist tidak ditemukan'], 404);
        }

        // Tidak boleh menghapus user yang role-nya Owner
        $targetRole = PlaylistCollaborator::where('id_playlist', $id)
            ->where('id_user', $user_id)
            ->first();

        if (!$targetRole) {
            return response()->json(['message' => 'User bukan kolaborator di playlist ini'], 404);
        }

        if ($targetRole->role === 'Owner') {
            return response()->json(['message' => 'Tidak bisa menghapus Owner dari playlist. Hapus playlist jika ingin menghapus kepemilikan.'], 400);
        }

        // Validasi: hanya Owner yang boleh menghapus kolaborator
        $idUser = $request->query('id_user') ?? $request->id_user;
        if ($idUser) {
            $isOwner = PlaylistCollaborator::where('id_playlist', $id)
                ->where('id_user', $idUser)
                ->where('role', 'Owner')
                ->exists();

            if (!$isOwner) {
                return response()->json(['message' => 'Hanya Owner yang bisa menghapus kolaborator'], 403);
            }
        }

        $playlist->collaborators()->detach($user_id);
        return response()->json(['message' => 'Kolaborator dihapus'], 200);
    }
}
