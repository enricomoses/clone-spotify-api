<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Song;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB; // Wajib ditambahkan untuk eksekusi query pembersihan relasi

class SongController extends Controller
{
    public function index()
    {
        // Menampilkan semua lagu beserta data album dan artisnya
        $songs = Song::with(['album', 'artists'])->get();
        return response()->json(['message' => 'Data lagu berhasil diambil', 'data' => $songs], 200);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:150',
            'duration' => 'required|integer|min:1',
            'id_album' => 'nullable|string|exists:album,id_album',
            'audio_url' => 'required|string',

            // Wajib ada artis yang menyanyikan (array)
            'artist_ids' => 'required|array',
            'artist_ids.*' => 'exists:artist,id_artist',

            // Opsional: Jika lagu langsung dimasukkan ke playlist saat dibuat
            'playlist_ids' => 'nullable|array',
            'playlist_ids.*' => 'exists:playlist,id_playlist'
        ]);

        $songData = [
            'id_song' => Str::uuid()->toString(),
            'title' => $validated['title'],
            'duration' => $validated['duration'],
            'id_album' => $validated['id_album'] ?? null,
            'audio_url' => $validated['audio_url']
        ];

        // 1. Simpan data lagu
        $song = Song::create($songData);

        // 2. Simpan relasi pembuat lagu ke tabel pivot song_artist
        $song->artists()->attach($validated['artist_ids']);

        // 3. (Opsional) Simpan relasi ke tabel pivot playlist_song jika ada
        if (!empty($validated['playlist_ids'])) {
            $song->playlists()->attach($validated['playlist_ids']);
        }

        // Load relasi agar respons ke Next.js langsung lengkap
        $song->load(['album', 'artists']);

        return response()->json(['message' => 'Lagu berhasil ditambahkan', 'data' => $song], 201);
    }

    public function show($id)
    {
        // Menampilkan lagu super lengkap (artis, album, dan ada di playlist mana saja)
        $song = Song::with(['album', 'artists', 'playlists'])->where('id_song', $id)->first();
        if (!$song) return response()->json(['message' => 'Lagu tidak ditemukan'], 404);

        return response()->json(['message' => 'Detail lagu', 'data' => $song], 200);
    }

    public function update(Request $request, $id)
    {
        $song = Song::where('id_song', $id)->first();
        if (!$song) return response()->json(['message' => 'Lagu tidak ditemukan'], 404);

        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:150',
            'duration' => 'sometimes|required|integer|min:1',
            'id_album' => 'nullable|string|exists:album,id_album',
            'audio_url' => 'sometimes|required|string',
            'artist_ids' => 'sometimes|required|array',
            'artist_ids.*' => 'exists:artist,id_artist',
            'playlist_ids' => 'nullable|array',
            'playlist_ids.*' => 'exists:playlist,id_playlist'
        ]);

        // Update data inti
        $song->update($request->only(['title', 'duration', 'id_album', 'audio_url']));

        // Sinkronisasi pivot artis jika frontend mengirim array artist_ids baru
        if ($request->has('artist_ids')) {
            $song->artists()->sync($validated['artist_ids']);
        }

        // Sinkronisasi pivot playlist jika frontend mengirim array playlist_ids baru
        if ($request->has('playlist_ids')) {
            $song->playlists()->sync($validated['playlist_ids']);
        }

        $song->load(['album', 'artists']);
        return response()->json(['message' => 'Lagu berhasil diupdate', 'data' => $song], 200);
    }

    public function destroy($id)
    {
        $song = Song::where('id_song', $id)->first();
        if (!$song) {
            return response()->json(['message' => 'Lagu tidak ditemukan'], 404);
        }

        // ==========================================
        // PROSES CASCADE DELETE MANUAL
        // ==========================================

        // 1. Putus relasi dengan artist (tabel song_artist)
        $song->artists()->detach();

        // 2. Putus relasi dengan playlist (tabel playlist_song)
        // (Pastikan fungsi playlists() ada di model Song Anda)
        if (method_exists($song, 'playlists')) {
            $song->playlists()->detach();
        } else {
            DB::table('playlist_song')->where('id_song', $id)->delete();
        }

        // 3. Putus relasi dengan user yang me-like (tabel user_likedsong)
        DB::table('user_likedsong')->where('id_song', $id)->delete();

        // 4. Hapus riwayat streaming lagu ini (tabel song_streaminghistory)
        DB::table('song_streaminghistory')->where('id_song', $id)->delete();

        // 5. Setelah semua "akar" dibersihkan, hapus lagunya
        $song->delete();

        return response()->json(['message' => 'Lagu beserta seluruh riwayat dan relasinya berhasil dihapus'], 200);
    }

    public function discoverWeekly()
    {
        // PERBAIKAN: Menambahkan 'artists' agar frontend bisa menampilkan nama penyanyi
        $topSongs = Song::with(['album', 'artists'])
            ->withCount('streamingHistories')
            ->orderBy('streaming_histories_count', 'desc')
            ->take(50)
            ->get();

        return response()->json([
            'message' => 'Discover Weekly (Top 50 Lagu)',
            'data' => $topSongs
        ], 200);
    }
}
