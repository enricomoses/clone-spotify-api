<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SongStreamingHistory;
use Illuminate\Http\Request;

class SongStreamingHistoryController extends Controller
{
    public function index()
    {
        // Menampilkan riwayat transaksi beserta siapa yang memutar dan lagu apa
        $history = SongStreamingHistory::with(['user', 'song'])->orderBy('played_at', 'desc')->get();
        return response()->json(['message' => 'Riwayat streaming berhasil diambil', 'data' => $history], 200);
    }

    public function store(Request $request)
    {
        // CONSTRAINT / VALIDASI ATURAN BISNIS TRANSAKSI
        $validated = $request->validate([
            'id_stream' => 'required|string|unique:song_streaminghistory,id_stream',
            'played_at' => 'required|date|before_or_equal:now', // Constraint: Waktu putar tidak boleh di masa depan
            'play_duration' => 'nullable|integer|min:1',
            'id_user' => 'required|string|exists:User,id_user', // Constraint FK: User harus ada
            'id_song' => 'required|string|exists:song,id_song'  // Constraint FK: Lagu harus ada
        ]);

        $stream = SongStreamingHistory::create($validated);
        return response()->json(['message' => 'Transaksi streaming berhasil dicatat', 'data' => $stream], 201);
    }

    public function show($id)
    {
        $stream = SongStreamingHistory::with(['user', 'song'])->find($id);
        if (!$stream) return response()->json(['message' => 'Transaksi tidak ditemukan'], 404);

        return response()->json(['message' => 'Detail transaksi', 'data' => $stream], 200);
    }

    public function update(Request $request, $id)
    {
        $stream = SongStreamingHistory::find($id);
        if (!$stream) return response()->json(['message' => 'Transaksi tidak ditemukan'], 404);

        $validated = $request->validate([
            'played_at' => 'sometimes|required|date|before_or_equal:now',
            'play_duration' => 'nullable|integer|min:1',
            'id_user' => 'sometimes|required|string|exists:User,id_user',
            'id_song' => 'sometimes|required|string|exists:song,id_song'
        ]);

        $stream->update($validated);
        return response()->json(['message' => 'Transaksi berhasil diupdate', 'data' => $stream], 200);
    }

    public function destroy($id)
    {
        $stream = SongStreamingHistory::find($id);
        if (!$stream) return response()->json(['message' => 'Transaksi tidak ditemukan'], 404);

        $stream->delete();
        return response()->json(['message' => 'Transaksi berhasil dihapus'], 200);
    }
}
