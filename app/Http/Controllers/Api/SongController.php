<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Song;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB; 
class SongController extends Controller
{
    public function index()
    {
        $songs = Song::with(['album', 'artists'])->get();
        return response()->json(['message' => 'Song data retrieved successfully', 'data' => $songs], 200);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:150',
            'duration' => 'required|integer|min:1',
            'id_album' => 'nullable|string|exists:albums,id_album',
            'audio_url' => 'required|string',

            'artist_ids' => 'required|array|min:1',
            'artist_ids.*' => 'exists:artists,id_artist',

            'playlist_ids' => 'nullable|array',
            'playlist_ids.*' => 'exists:playlists,id_playlist'
        ]);

        $songData = [
            'id_song' => Str::uuid()->toString(),
            'title' => $validated['title'],
            'duration' => $validated['duration'],
            'id_album' => $validated['id_album'] ?? null,
            'audio_url' => $validated['audio_url']
        ];

        $song = Song::create($songData);

        $song->artists()->attach($validated['artist_ids']);

        if (!empty($validated['playlist_ids'])) {
            $song->playlists()->attach($validated['playlist_ids']);
        }

        $song->load(['album', 'artists']);

        return response()->json(['message' => 'Song added successfully', 'data' => $song], 201);
    }

    public function show($id)
    {
        $song = Song::with(['album', 'artists', 'playlists'])->where('id_song', $id)->first();
        if (!$song) return response()->json(['message' => 'Song not found'], 404);

        return response()->json(['message' => 'Song detail', 'data' => $song], 200);
    }

    public function update(Request $request, $id)
    {
        $song = Song::where('id_song', $id)->first();
        if (!$song) return response()->json(['message' => 'Song not found'], 404);

        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:150',
            'duration' => 'sometimes|required|integer|min:1',
            'id_album' => 'nullable|string|exists:albums,id_album',
            'audio_url' => 'sometimes|required|string',
            'artist_ids' => 'sometimes|required|array',
            'artist_ids.*' => 'exists:artists,id_artist',
            'playlist_ids' => 'nullable|array',
            'playlist_ids.*' => 'exists:playlists,id_playlist'
        ]);

        $song->update($request->only(['title', 'duration', 'id_album', 'audio_url']));

        if ($request->has('artist_ids')) {
            $song->artists()->sync($validated['artist_ids']);
        }

        if ($request->has('playlist_ids')) {
            $song->playlists()->sync($validated['playlist_ids']);
        }

        $song->load(['album', 'artists']);
        return response()->json(['message' => 'Song updated successfully', 'data' => $song], 200);
    }

    public function destroy($id)
    {
        $song = Song::where('id_song', $id)->first();
        if (!$song) {
            return response()->json(['message' => 'Song not found'], 404);
        }

        $song->artists()->detach();

        $song->playlists()->detach();

        DB::table('user_likedsong')->where('id_song', $id)->delete();

        DB::table('song_streaming_histories')->where('id_song', $id)->delete();

        $song->delete();

        return response()->json(['message' => 'Song and all its history and relations deleted successfully'], 200);
    }

    public function discoverWeekly()
    {
        $topSongs = Song::with(['album', 'artists'])
            ->withCount('streamingHistories')
            ->orderBy('streaming_histories_count', 'desc')
            ->take(50)
            ->get();

        return response()->json([
            'message' => 'Discover Weekly (Top 50 Songs)',
            'data' => $topSongs
        ], 200);
    }

    /**
     * Search songs by title or artist name.
     * GET /songs/search?q=keyword
     */
    public function search(Request $request)
    {
        $query = $request->query('q', '');

        if (strlen($query) < 1) {
            return response()->json(['message' => 'Search query is required', 'data' => []], 200);
        }

        $q = "%{$query}%";

        $songs = Song::with(['album', 'artists'])
            ->where('title', 'ILIKE', $q)
            ->orWhereHas('artists', function ($qb) use ($q) {
                $qb->where('name', 'ILIKE', $q);
            })
            ->limit(30)
            ->get();

        return response()->json([
            'message' => 'Song search results',
            'data' => $songs
        ], 200);
    }
}
