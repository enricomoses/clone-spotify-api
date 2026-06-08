<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\EpisodeStreamingHistory; // Pastikan model ini sudah ada
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class EpisodeStreamingHistoryController extends Controller
{
    public function index()
    {
        $history = EpisodeStreamingHistory::with(['user', 'episode'])->orderBy('played_at', 'desc')->get();
        return response()->json(['message' => 'Riwayat streaming episode berhasil diambil', 'data' => $history], 200);
    }

    // --- LOGIKA SAAT TOMBOL PLAY DITEKAN ---
    public function store(Request $request)
    {
        $validated = $request->validate([
            'id_user' => 'required|string|exists:User,id_user',
            'id_episode' => 'required|string|exists:episode,id_episode'
        ]);

        $validated['id_stream'] = Str::uuid()->toString();
        $validated['played_at'] = now();
        $validated['play_duration'] = null;

        $stream = EpisodeStreamingHistory::create($validated);

        return response()->json(['message' => 'Episode mulai diputar', 'data' => $stream], 201);
    }

    public function show($id)
    {
        $stream = EpisodeStreamingHistory::with(['user', 'episode'])->where('id_stream', $id)->first();
        if (!$stream) return response()->json(['message' => 'Transaksi tidak ditemukan'], 404);

        return response()->json(['message' => 'Detail transaksi episode', 'data' => $stream], 200);
    }

    // --- LOGIKA SAAT TOMBOL PAUSE DITEKAN ATAU EPISODE SELESAI ---
    public function update(Request $request, $id)
    {
        $stream = EpisodeStreamingHistory::query()->where('id_stream', $id)->first();
        if (!$stream) return response()->json(['message' => 'Transaksi tidak ditemukan'], 404);

        $validated = $request->validate([
            'play_duration' => 'required|integer|min:1'
        ]);

        $stream->update($validated);
        return response()->json(['message' => 'Durasi putar episode berhasil disimpan', 'data' => $stream], 200);
    }

    public function destroy($id)
    {
        $stream = EpisodeStreamingHistory::query()->where('id_stream', $id)->first();
        if (!$stream) return response()->json(['message' => 'Transaksi tidak ditemukan'], 404);

        $stream->delete();
        return response()->json(['message' => 'Transaksi episode berhasil dihapus'], 200);
    }
}
