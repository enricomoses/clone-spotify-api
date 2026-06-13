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
        return response()->json(['message' => 'Riwayat streaming lagu berhasil diambil', 'data' => $history], 200);
    }

    // --- LOGIKA SAAT TOMBOL PLAY DITEKAN ---
    public function store(Request $request)
    {
        $validated = $request->validate([
            'id_user' => 'required|string|exists:User,id_user',
            'id_song' => 'required|string|exists:song,id_song'
        ]);

        $validated['id_stream'] = Str::uuid()->toString();
        $validated['played_at'] = now(); // Otomatis catat waktu server saat ini
        $validated['play_duration'] = null; // Kosongkan dulu karena lagu baru mulai

        $stream = SongStreamingHistory::create($validated);

        // PENTING: Frontend wajib menyimpan 'id_stream' yang dikembalikan ini
        // untuk digunakan saat mengirim update durasi (pause/selesai)
        return response()->json(['message' => 'Lagu mulai diputar', 'data' => $stream], 201);
    }

    public function show($id)
    {
        $stream = SongStreamingHistory::with(['user', 'song'])->where('id_stream', $id)->first();
        if (!$stream) return response()->json(['message' => 'Transaksi tidak ditemukan'], 404);

        return response()->json(['message' => 'Detail transaksi lagu', 'data' => $stream], 200);
    }

    // --- LOGIKA SAAT TOMBOL PAUSE DITEKAN ATAU LAGU SELESAI ---
    public function update(Request $request, $id)
    {
        $stream = SongStreamingHistory::query()->where('id_stream', $id)->first();
        if (!$stream) return response()->json(['message' => 'Transaksi tidak ditemukan'], 404);

        // Frontend hanya perlu mengirimkan durasi (dalam detik)
        $validated = $request->validate([
            'play_duration' => 'required|integer|min:1'
        ]);

        $stream->update($validated);
        return response()->json(['message' => 'Durasi putar lagu berhasil disimpan', 'data' => $stream], 200);
    }

    public function destroy($id)
    {
        $stream = SongStreamingHistory::query()->where('id_stream', $id)->first();
        if (!$stream) return response()->json(['message' => 'Transaksi tidak ditemukan'], 404);

        $stream->delete();
        return response()->json(['message' => 'Transaksi lagu berhasil dihapus'], 200);
    }

    // --- RIWAYAT STREAMING LAGU PER USER ---
    public function getUserHistory($userId)
    {
        $history = SongStreamingHistory::with(['song.artists', 'song.album'])
            ->where('id_user', $userId)
            ->orderBy('played_at', 'desc')
            ->get();

        return response()->json([
            'message' => 'Riwayat streaming lagu user',
            'data' => $history
        ], 200);
    }

    public function getArtistHistory($artistId)
    {
        // Ambil data streaming history yang relasi lagunya dimiliki oleh artist dengan ID tersebut
        $history = SongStreamingHistory::with(['user', 'song.album'])
            ->whereHas('song.artists', function ($query) use ($artistId) {
                $query->where('artist.id_artist', $artistId);
            })
            ->orderBy('played_at', 'desc')
            ->get();

        return response()->json([
            'message' => 'Riwayat streaming lagu milik artis berhasil diambil',
            'total_streams' => $history->count(),
            'data' => $history
        ], 200);
    }
}
