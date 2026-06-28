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

        // 1. Save song data
        $song = Song::create($songData);

        // 2. Save song creator relation to song_artist pivot table
        $song->artists()->attach($validated['artist_ids']);

        // 3. (Optional) Save relation to playlist_song pivot table if provided
        if (!empty($validated['playlist_ids'])) {
            $song->playlists()->attach($validated['playlist_ids']);
        }

        // Load relations so the response to Next.js is complete
        $song->load(['album', 'artists']);

        return response()->json(['message' => 'Song added successfully', 'data' => $song], 201);
    }

    public function show($id)
    {
        // Display complete song data (artists, album, and which playlists it's in)
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

        // Update core data
        $song->update($request->only(['title', 'duration', 'id_album', 'audio_url']));

        // Sync artist pivot if frontend sends new artist_ids array
        if ($request->has('artist_ids')) {
            $song->artists()->sync($validated['artist_ids']);
        }

        // Sync playlist pivot if frontend sends new playlist_ids array
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

        // ==========================================
        // MANUAL CASCADE DELETE PROCESS
        // ==========================================

        // 1. Detach relation with artists (song_artist table)
        $song->artists()->detach();

        // 2. Detach relation with playlists (playlist_song table)
        $song->playlists()->detach();

        // 3. Detach relation with users who liked the song (user_likedsong table)
        DB::table('user_likedsong')->where('id_song', $id)->delete();

        // 4. Delete streaming history for this song (song_streaming_histories table)
        DB::table('song_streaming_histories')->where('id_song', $id)->delete();

        // 5. After all relations are cleaned, delete the song
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
}
