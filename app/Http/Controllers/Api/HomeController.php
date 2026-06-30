<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Artist;
use App\Models\Album;
use App\Models\Podcast;
use App\Models\Playlist;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * Popular Artists — sorted by follower count descending.
     * GET /home/popular-artists?limit=12
     */
    public function popularArtists(Request $request)
    {
        $limit = (int) $request->query('limit', 12);

        $artists = Artist::withCount('followers')
            ->orderBy('followers_count', 'desc')
            ->take($limit)
            ->get();

        return response()->json([
            'message' => 'Popular artists',
            'data' => $artists
        ], 200);
    }

    /**
     * Popular Albums — most recent releases, with artists.
     * GET /home/popular-albums?limit=12
     */
    public function popularAlbums(Request $request)
    {
        $limit = (int) $request->query('limit', 12);

        $albums = Album::with('artists')
            ->orderBy('release', 'desc')
            ->take($limit)
            ->get();

        return response()->json([
            'message' => 'Popular albums',
            'data' => $albums
        ], 200);
    }

    /**
     * Popular Podcasts — sorted by follower count descending.
     * GET /home/popular-podcasts?limit=6
     */
    public function popularPodcasts(Request $request)
    {
        $limit = (int) $request->query('limit', 6);

        $podcasts = Podcast::with(['category', 'creator'])
            ->withCount('followers')
            ->orderBy('followers_count', 'desc')
            ->take($limit)
            ->get();

        return response()->json([
            'message' => 'Popular podcasts',
            'data' => $podcasts
        ], 200);
    }

    /**
     * Featured Playlists — public playlists with songs count, limited.
     * GET /home/featured-playlists?limit=12
     */
    public function featuredPlaylists(Request $request)
    {
        $limit = (int) $request->query('limit', 12);

        $playlists = Playlist::with(['category', 'collaborators'])
            ->withCount('songs')
            ->orderBy('songs_count', 'desc')
            ->take($limit)
            ->get();

        return response()->json([
            'message' => 'Featured playlists',
            'data' => $playlists
        ], 200);
    }

    /**
     * User Playlists — playlists where user is Owner or collaborator.
     * GET /home/user-playlists?user_id=xxx&limit=6
     */
    public function userPlaylists(Request $request)
    {
        $userId = $request->query('user_id');
        $limit = (int) $request->query('limit', 6);

        if (!$userId) {
            return response()->json(['message' => 'user_id is required', 'data' => []], 200);
        }

        $playlists = Playlist::with(['category', 'collaborators', 'songs'])
            ->whereHas('collaborators', function ($q) use ($userId) {
                $q->where('playlist_collaborator.id_user', $userId);
            })
            ->take($limit)
            ->get();

        return response()->json([
            'message' => 'User playlists',
            'data' => $playlists
        ], 200);
    }
}
