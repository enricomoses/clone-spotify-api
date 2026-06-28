<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class SocialController extends Controller
{
    private function getUser($id)
    {
        return User::query()->where('id_user', $id)->first();
    }

    // --- LIKE SONG FEATURE ---
    public function likeSong(Request $request, $id)
    {
        $user = $this->getUser($id);
        if (!$user) return response()->json(['message' => 'User not found'], 404);

        $request->validate(['song_id' => 'required|exists:songs,id_song']);

        $user->likedSongs()->syncWithoutDetaching([$request->song_id]);
        return response()->json(['message' => 'Song liked successfully'], 200);
    }

    public function unlikeSong($id, $song_id)
    {
        $user = $this->getUser($id);
        if (!$user) return response()->json(['message' => 'User not found'], 404);

        $user->likedSongs()->detach($song_id);
        return response()->json(['message' => 'Song unliked successfully'], 200);
    }

    // --- FOLLOW USER FEATURE ---
    public function followUser(Request $request, $id)
    {
        $user = $this->getUser($id);
        if (!$user) return response()->json(['message' => 'User not found'], 404);

        $request->validate(['target_id' => 'required|exists:users,id_user']);

        // Prevent user from following themselves
        if ($id === $request->target_id) {
            return response()->json(['message' => 'You cannot follow yourself'], 400);
        }

        $user->following()->syncWithoutDetaching([$request->target_id]);
        return response()->json(['message' => 'User followed successfully'], 200);
    }

    public function unfollowUser($id, $target_id)
    {
        $user = $this->getUser($id);
        if (!$user) return response()->json(['message' => 'User not found'], 404);

        $user->following()->detach($target_id);
        return response()->json(['message' => 'User unfollowed successfully'], 200);
    }

    // --- FOLLOW ARTIST FEATURE ---
    public function followArtist(Request $request, $id)
    {
        $user = $this->getUser($id);
        if (!$user) return response()->json(['message' => 'User not found'], 404);

        $request->validate(['artist_id' => 'required|exists:artists,id_artist']);

        $user->followedArtists()->syncWithoutDetaching([$request->artist_id]);
        return response()->json(['message' => 'Artist followed successfully'], 200);
    }

    public function unfollowArtist($id, $artist_id)
    {
        $user = $this->getUser($id);
        if (!$user) return response()->json(['message' => 'User not found'], 404);

        $user->followedArtists()->detach($artist_id);
        return response()->json(['message' => 'Artist unfollowed successfully'], 200);
    }

    // --- FOLLOW PLAYLIST FEATURE ---
    public function followPlaylist(Request $request, $id)
    {
        $user = $this->getUser($id);
        if (!$user) return response()->json(['message' => 'User not found'], 404);

        $request->validate(['playlist_id' => 'required|exists:playlists,id_playlist']);

        $user->followedPlaylists()->syncWithoutDetaching([$request->playlist_id]);
        return response()->json(['message' => 'Playlist followed successfully'], 200);
    }

    public function unfollowPlaylist($id, $playlist_id)
    {
        $user = $this->getUser($id);
        if (!$user) return response()->json(['message' => 'User not found'], 404);

        $user->followedPlaylists()->detach($playlist_id);
        return response()->json(['message' => 'Playlist unfollowed successfully'], 200);
    }

    // --- FOLLOW PODCAST FEATURE ---
    public function followPodcast(Request $request, $id)
    {
        $user = $this->getUser($id);
        if (!$user) return response()->json(['message' => 'User not found'], 404);

        $request->validate(['podcast_id' => 'required|exists:podcasts,id_podcast']);

        $user->followedPodcasts()->syncWithoutDetaching([$request->podcast_id]);
        return response()->json(['message' => 'Podcast followed successfully'], 200);
    }

    public function unfollowPodcast($id, $podcast_id)
    {
        $user = $this->getUser($id);
        if (!$user) return response()->json(['message' => 'User not found'], 404);

        $user->followedPodcasts()->detach($podcast_id);
        return response()->json(['message' => 'Podcast unfollowed successfully'], 200);
    }
}
