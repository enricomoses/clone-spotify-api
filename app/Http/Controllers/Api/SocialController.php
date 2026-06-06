<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class SocialController extends Controller
{
    // --- FITUR LIKE LAGU ---
    public function likeSong(Request $request, $id)
    {
        $user = User::query()->where('id_user', $id)->first();
        $user->likedSongs()->syncWithoutDetaching([$request->song_id]);
        return response()->json(['message' => 'Berhasil menyukai lagu'], 200);
    }

    public function unlikeSong($id, $song_id)
    {
        $user = User::query()->where('id_user', $id)->first();
        $user->likedSongs()->detach($song_id);
        return response()->json(['message' => 'Batal menyukai lagu'], 200);
    }

    // --- FITUR FOLLOW USER ---
    public function followUser(Request $request, $id)
    {
        $user = User::query()->where('id_user', $id)->first();
        $user->following()->syncWithoutDetaching([$request->target_id]);
        return response()->json(['message' => 'Berhasil mem-follow user'], 200);
    }

    public function unfollowUser($id, $target_id)
    {
        $user = User::query()->where('id_user', $id)->first();
        $user->following()->detach($target_id);
        return response()->json(['message' => 'Berhasil unfollow user'], 200);
    }

    // --- FITUR FOLLOW ARTIST, PLAYLIST, PODCAST ---
    public function followArtist(Request $request, $id)
    {
        $user = User::query()->where('id_user', $id)->first();
        $user->followedArtists()->syncWithoutDetaching([$request->artist_id]);
        return response()->json(['message' => 'Berhasil mem-follow artis'], 200);
    }

    public function unfollowArtist($id, $artist_id)
    {
        $user = User::query()->where('id_user', $id)->first();
        $user->followedArtists()->detach($artist_id);
        return response()->json(['message' => 'Berhasil unfollow artis'], 200);
    }

    public function followPlaylist(Request $request, $id)
    {
        $user = User::query()->where('id_user', $id)->first();
        $user->followedPlaylists()->syncWithoutDetaching([$request->playlist_id]);
        return response()->json(['message' => 'Berhasil mengikuti playlist'], 200);
    }

    public function followPodcast(Request $request, $id)
    {
        $user = User::query()->where('id_user', $id)->first();
        $user->followedPodcasts()->syncWithoutDetaching([$request->podcast_id]);
        return response()->json(['message' => 'Berhasil mengikuti podcast'], 200);
    }
}
