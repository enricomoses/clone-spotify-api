<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SongStreamingHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SongStreamingHistoryController extends Controller
{
    public function index()
    {
        $history = SongStreamingHistory::with(['user', 'song'])->orderBy('played_at', 'desc')->get();
        return response()->json(['message' => 'Song streaming history retrieved successfully', 'data' => $history], 200);
    }

    // --- LOGIC WHEN PLAY BUTTON IS PRESSED ---
    public function store(Request $request)
    {
        $validated = $request->validate([
            'id_user' => 'required|string|exists:User,id_user',
            'id_song' => 'required|string|exists:song,id_song'
        ]);

        $validated['id_stream'] = Str::uuid()->toString();
        $validated['played_at'] = now(); // Automatically record current server time
        $validated['play_duration'] = null; // Leave empty because song just started

        $stream = SongStreamingHistory::create($validated);

        // IMPORTANT: Frontend must save the returned 'id_stream'
        // to be used when sending duration updates (pause/finished)
        return response()->json(['message' => 'Song playback started', 'data' => $stream], 201);
    }

    public function show($id)
    {
        $stream = SongStreamingHistory::with(['user', 'song'])->where('id_stream', $id)->first();
        if (!$stream) return response()->json(['message' => 'Stream record not found'], 404);

        return response()->json(['message' => 'Song stream detail', 'data' => $stream], 200);
    }

    // --- LOGIC WHEN PAUSE IS PRESSED OR SONG FINISHES ---
    public function update(Request $request, $id)
    {
        $stream = SongStreamingHistory::query()->where('id_stream', $id)->first();
        if (!$stream) return response()->json(['message' => 'Transaction not found'], 404);

        // Frontend only needs to send the duration (in seconds)
        $validated = $request->validate([
            'play_duration' => 'required|integer|min:1'
        ]);

        $stream->update($validated);
        return response()->json(['message' => 'Song play duration saved successfully', 'data' => $stream], 200);
    }

    public function destroy($id)
    {
        $stream = SongStreamingHistory::query()->where('id_stream', $id)->first();
        if (!$stream) return response()->json(['message' => 'Transaction not found'], 404);

        $stream->delete();
        return response()->json(['message' => 'Song stream record deleted successfully'], 200);
    }

    // --- SONG STREAMING HISTORY PER USER ---
    public function getUserHistory($userId)
    {
        $history = SongStreamingHistory::with(['song.artists', 'song.album'])
            ->where('id_user', $userId)
            ->orderBy('played_at', 'desc')
            ->get();

        return response()->json([
            'message' => 'User song streaming history',
            'data' => $history
        ], 200);
    }

    public function getArtistHistory($artistId)
    {
        // Get streaming history where the song's relation belongs to the artist with given ID
        $history = SongStreamingHistory::with(['user', 'song.album'])
            ->whereHas('song.artists', function ($query) use ($artistId) {
                $query->where('artist.id_artist', $artistId);
            })
            ->orderBy('played_at', 'desc')
            ->get();

        return response()->json([
            'message' => 'Artist song streaming history retrieved successfully',
            'total_streams' => $history->count(),
            'data' => $history
        ], 200);
    }
}
