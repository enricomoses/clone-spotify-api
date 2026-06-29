<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\EpisodeStreamingHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class EpisodeStreamingHistoryController extends Controller
{
    public function index()
    {
        $history = EpisodeStreamingHistory::with(['user', 'episode'])->orderBy('played_at', 'desc')->get();
        return response()->json(['message' => 'Episode streaming history retrieved successfully', 'data' => $history], 200);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id_user' => 'required|string|exists:users,id_user',
            'id_episode' => 'required|string|exists:episodes,id_episode'
        ]);

        $validated['id_stream'] = Str::uuid()->toString();
        $validated['played_at'] = now();
        $validated['play_duration'] = null;

        $stream = EpisodeStreamingHistory::create($validated);

        return response()->json(['message' => 'Episode playback started', 'data' => $stream], 201);
    }

    public function show($id)
    {
        $stream = EpisodeStreamingHistory::with(['user', 'episode'])->where('id_stream', $id)->first();
        if (!$stream) return response()->json(['message' => 'Stream record not found'], 404);

        return response()->json(['message' => 'Episode stream detail', 'data' => $stream], 200);
    }

    public function update(Request $request, $id)
    {
        $stream = EpisodeStreamingHistory::query()->where('id_stream', $id)->first();
        if (!$stream) return response()->json(['message' => 'Transaction not found'], 404);

        $validated = $request->validate([
            'play_duration' => 'required|integer|min:1'
        ]);

        $stream->update($validated);
        return response()->json(['message' => 'Episode play duration saved successfully', 'data' => $stream], 200);
    }

    public function destroy($id)
    {
        $stream = EpisodeStreamingHistory::query()->where('id_stream', $id)->first();
        if (!$stream) return response()->json(['message' => 'Transaction not found'], 404);

        $stream->delete();
        return response()->json(['message' => 'Episode stream record deleted successfully'], 200);
    }

    public function getUserHistory($userId)
    {
        $history = EpisodeStreamingHistory::with(['episode.podcast'])
            ->where('id_user', $userId)
            ->orderBy('played_at', 'desc')
            ->get();

        return response()->json([
            'message' => 'User episode streaming history',
            'data' => $history
        ], 200);
    }
}
