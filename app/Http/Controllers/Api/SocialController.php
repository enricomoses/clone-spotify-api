<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class SocialController extends Controller
{
    // Helper function untuk mencari user agar kode tidak berulang-ulang
    private function getUser($id)
    {
        return User::query()->where('id_user', $id)->first();
    }

    // --- FITUR LIKE LAGU ---
    public function likeSong(Request $request, $id)
    {
        $user = $this->getUser($id);
        if (!$user) return response()->json(['message' => 'User tidak ditemukan'], 404);

        $request->validate(['song_id' => 'required|exists:song,id_song']);

        $user->likedSongs()->syncWithoutDetaching([$request->song_id]);
        return response()->json(['message' => 'Berhasil menyukai lagu'], 200);
    }

    public function unlikeSong($id, $song_id)
    {
        $user = $this->getUser($id);
        if (!$user) return response()->json(['message' => 'User tidak ditemukan'], 404);

        $user->likedSongs()->detach($song_id);
        return response()->json(['message' => 'Batal menyukai lagu'], 200);
    }

    // --- FITUR FOLLOW USER ---
    public function followUser(Request $request, $id)
    {
        $user = $this->getUser($id);
        if (!$user) return response()->json(['message' => 'User tidak ditemukan'], 404);

        $request->validate(['target_id' => 'required|exists:User,id_user']);

        // Mencegah user mem-follow dirinya sendiri
        if ($id === $request->target_id) {
            return response()->json(['message' => 'Anda tidak bisa mem-follow diri sendiri'], 400);
        }

        $user->following()->syncWithoutDetaching([$request->target_id]);
        return response()->json(['message' => 'Berhasil mem-follow user'], 200);
    }

    public function unfollowUser($id, $target_id)
    {
        $user = $this->getUser($id);
        if (!$user) return response()->json(['message' => 'User tidak ditemukan'], 404);

        $user->following()->detach($target_id);
        return response()->json(['message' => 'Berhasil unfollow user'], 200);
    }

    // --- FITUR FOLLOW ARTIST ---
    public function followArtist(Request $request, $id)
    {
        $user = $this->getUser($id);
        if (!$user) return response()->json(['message' => 'User tidak ditemukan'], 404);

        $request->validate(['artist_id' => 'required|exists:artist,id_artist']);

        $user->followedArtists()->syncWithoutDetaching([$request->artist_id]);
        return response()->json(['message' => 'Berhasil mem-follow artis'], 200);
    }

    public function unfollowArtist($id, $artist_id)
    {
        $user = $this->getUser($id);
        if (!$user) return response()->json(['message' => 'User tidak ditemukan'], 404);

        $user->followedArtists()->detach($artist_id);
        return response()->json(['message' => 'Berhasil unfollow artis'], 200);
    }

    // --- FITUR FOLLOW PLAYLIST ---
    public function followPlaylist(Request $request, $id)
    {
        $user = $this->getUser($id);
        if (!$user) return response()->json(['message' => 'User tidak ditemukan'], 404);

        $request->validate(['playlist_id' => 'required|exists:playlist,id_playlist']);

        $user->followedPlaylists()->syncWithoutDetaching([$request->playlist_id]);
        return response()->json(['message' => 'Berhasil mengikuti playlist'], 200);
    }

    public function unfollowPlaylist($id, $playlist_id)
    {
        $user = $this->getUser($id);
        if (!$user) return response()->json(['message' => 'User tidak ditemukan'], 404);

        $user->followedPlaylists()->detach($playlist_id);
        return response()->json(['message' => 'Berhasil berhenti mengikuti playlist'], 200);
    }

    // --- FITUR FOLLOW PODCAST ---
    public function followPodcast(Request $request, $id)
    {
        $user = $this->getUser($id);
        if (!$user) return response()->json(['message' => 'User tidak ditemukan'], 404);

        $request->validate(['podcast_id' => 'required|exists:podcast,id_podcast']);

        $user->followedPodcasts()->syncWithoutDetaching([$request->podcast_id]);
        return response()->json(['message' => 'Berhasil mengikuti podcast'], 200);
    }

    public function unfollowPodcast($id, $podcast_id)
    {
        $user = $this->getUser($id);
        if (!$user) return response()->json(['message' => 'User tidak ditemukan'], 404);

        $user->followedPodcasts()->detach($podcast_id);
        return response()->json(['message' => 'Berhasil berhenti mengikuti podcast'], 200);
    }
}
