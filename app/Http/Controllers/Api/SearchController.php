<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Song;
use App\Models\Artist;
use App\Models\Album;
use App\Models\Playlist;
use App\Models\Podcast;
use App\Models\User;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    /**
     * Global search across all entities.
     * GET /search?q=keyword
     *
     * Returns filtered songs, artists, albums, playlists, podcasts, users
     * in a single response. Frontend doesn't need to fetch 6 endpoints separately.
     */
    public function search(Request $request)
    {
        $query = $request->query('q', '');

        if (strlen($query) < 1) {
            return response()->json([
                'message' => 'Search query is required',
                'data' => [
                    'songs' => [],
                    'artists' => [],
                    'albums' => [],
                    'playlists' => [],
                    'podcasts' => [],
                    'users' => [],
                ]
            ], 200);
        }

        $q = "%{$query}%";

        // Songs: search by title or artist name
        $songs = Song::with(['album', 'artists'])
            ->where('title', 'ILIKE', $q)
            ->orWhereHas('artists', function ($qb) use ($q) {
                $qb->where('name', 'ILIKE', $q);
            })
            ->limit(30)
            ->get();

        // Artists: search by name
        $artists = Artist::where('name', 'ILIKE', $q)
            ->limit(20)
            ->get();

        // Albums: search by title or artist name
        $albums = Album::with('artists')
            ->where('title', 'ILIKE', $q)
            ->orWhereHas('artists', function ($qb) use ($q) {
                $qb->where('name', 'ILIKE', $q);
            })
            ->limit(20)
            ->get();

        // Playlists: search by name
        $playlists = Playlist::with(['category', 'collaborators'])
            ->where('name', 'ILIKE', $q)
            ->limit(20)
            ->get();

        // Podcasts: search by name or about
        $podcasts = Podcast::with(['category', 'creator'])
            ->where('name', 'ILIKE', $q)
            ->orWhere('about', 'ILIKE', $q)
            ->limit(20)
            ->get();

        // Users: search by name
        $users = User::where('name', 'ILIKE', $q)
            ->withCount(['followers', 'following'])
            ->limit(20)
            ->get();

        return response()->json([
            'message' => 'Search results',
            'data' => [
                'songs' => $songs,
                'artists' => $artists,
                'albums' => $albums,
                'playlists' => $playlists,
                'podcasts' => $podcasts,
                'users' => $users,
            ]
        ], 200);
    }
}
