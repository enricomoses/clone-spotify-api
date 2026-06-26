<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Artist;
use App\Models\Song;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ArtistController extends Controller
{
    public function index()
    {
        // Menampilkan data artis lengkap dengan semua relasinya
        $artists = Artist::with(['albums', 'songs.album', 'songs.artists', 'concerts', 'galleries'])->get();
        return response()->json(['message' => 'Data artist', 'data' => $artists], 200);
    }

    public function show($id)
    {
        $artist = Artist::with(['albums', 'songs.album', 'songs.artists', 'concerts', 'galleries', 'followers'])
            ->where('id_artist', $id)
            ->first();

        if (!$artist) return response()->json(['message' => 'Artist tidak ditemukan'], 404);

        return response()->json(['message' => 'Detail artist', 'data' => $artist], 200);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'email' => 'required|email|unique:artist,email',
            'password' => 'required|string|min:6',
            'instagram' => 'nullable|string',
            'facebook' => 'nullable|string',
            'wikipedia' => 'nullable|string',
            'x' => 'nullable|string',
            'bio' => 'nullable|string',
            'profile_picture' => 'nullable|string'
        ]);

        $validated['id_artist'] = Str::uuid()->toString();

        $artist = Artist::create($validated);

        return response()->json(['message' => 'Artist berhasil didaftarkan', 'data' => $artist], 201);
    }

    public function update(Request $request, $id)
    {
        $artist = Artist::query()->where('id_artist', $id)->first();
        if (!$artist) {
            return response()->json(['message' => 'Artist tidak ditemukan'], 404);
        }

        // Validasi disesuaikan dengan struktur ERD Anda
        // Menggunakan unique dengan pengecualian ID agar email yang sama tidak error saat update profil sendiri
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:150',
            'email' => 'sometimes|required|email|unique:artist,email,' . $id . ',id_artist',
            'password' => 'sometimes|required|string|min:6',
            'instagram' => 'nullable|string',
            'facebook' => 'nullable|string',
            'wikipedia' => 'nullable|string',
            'x' => 'nullable|string',
            'bio' => 'nullable|string',
            'profile_picture' => 'nullable|string'
        ]);

        $artist->update($validated);

        return response()->json(['message' => 'Data artist berhasil diupdate', 'data' => $artist], 200);
    }

    public function destroy($id)
    {
        $artist = Artist::query()->where('id_artist', $id)->first();
        if (!$artist) {
            return response()->json(['message' => 'Artist tidak ditemukan'], 404);
        }

        // ==========================================
        // PROSES CASCADE DELETE (PEMBERSIHAN RELASI)
        // ==========================================

        // 1. Cari song yang HANYA dimiliki artist ini (tidak ada artist lain)
        //    Jika song hanya punya 1 artist (artist ini), maka song harus dihapus
        //    karena menurut ERD song PASTI dimiliki minimal 1 artist
        $songIds = DB::table('song_artist')->where('id_artist', $id)->pluck('id_song');

        foreach ($songIds as $songId) {
            $artistCount = DB::table('song_artist')->where('id_song', $songId)->count();

            if ($artistCount <= 1) {
                // Song ini hanya dimiliki artist ini → hapus song beserta relasinya
                $song = Song::where('id_song', $songId)->first();
                if ($song) {
                    $song->artists()->detach();
                    $song->playlists()->detach();
                    DB::table('user_likedsong')->where('id_song', $songId)->delete();
                    DB::table('song_streaminghistory')->where('id_song', $songId)->delete();
                    $song->delete();
                }
            }
        }

        // 2. Putus relasi song_artist untuk song yang masih punya artist lain
        $artist->songs()->detach();

        // 3. Putus relasi album (tabel artist_album) — album tidak dihapus karena bisa milik artist lain
        $artist->albums()->detach();

        // 4. Putus relasi concert (tabel artist_concert) — concert tidak dihapus karena bisa milik artist lain
        $artist->concerts()->detach();

        // 5. Hapus gallery (tabel artist_gallery) — gallery mandatory milik 1 artist, jadi ikut dihapus
        DB::table('artist_gallery')->where('id_artist', $id)->delete();

        // 6. Hapus semua follower (tabel user_followartist)
        DB::table('user_followartist')->where('id_artist', $id)->delete();

        // 7. Hapus artist utama
        $artist->delete();

        return response()->json(['message' => 'Artist beserta seluruh relasinya berhasil dihapus'], 200);
    }

    // --- MANAJEMEN RELASI DARI SISI ARTIS ---

    public function addAlbumToArtist(Request $request, $id)
    {
        $artist = Artist::query()->where('id_artist', $id)->first();
        if (!$artist) return response()->json(['message' => 'Artist tidak ditemukan'], 404);

        $request->validate(['album_id' => 'required|exists:album,id_album']);

        $artist->albums()->syncWithoutDetaching([$request->album_id]);
        return response()->json(['message' => 'Album berhasil ditambahkan ke profil artis'], 200);
    }

    public function removeAlbumFromArtist($id, $album_id)
    {
        $artist = Artist::query()->where('id_artist', $id)->first();
        if (!$artist) return response()->json(['message' => 'Artist tidak ditemukan'], 404);

        $artist->albums()->detach($album_id);
        return response()->json(['message' => 'Album dihapus dari profil artis'], 200);
    }

    public function addSongToArtist(Request $request, $id)
    {
        $artist = Artist::query()->where('id_artist', $id)->first();
        if (!$artist) return response()->json(['message' => 'Artist tidak ditemukan'], 404);

        $request->validate(['song_id' => 'required|exists:song,id_song']);

        $artist->songs()->syncWithoutDetaching([$request->song_id]);
        return response()->json(['message' => 'Lagu berhasil ditambahkan ke profil artis'], 200);
    }

    public function removeSongFromArtist($id, $song_id)
    {
        $artist = Artist::query()->where('id_artist', $id)->first();
        if (!$artist) return response()->json(['message' => 'Artist tidak ditemukan'], 404);

        // Cek apakah song masih punya artist lain setelah detach
        $artistCount = DB::table('song_artist')->where('id_song', $song_id)->count();
        if ($artistCount <= 1) {
            return response()->json([
                'message' => 'Tidak bisa menghapus artis terakhir dari lagu. Song harus punya minimal 1 artist.'
            ], 400);
        }

        $artist->songs()->detach($song_id);
        return response()->json(['message' => 'Lagu dihapus dari profil artis'], 200);
    }

    // --- FITUR KONSER ---
    public function getConcerts($id)
    {
        $artist = Artist::with('concerts')->where('id_artist', $id)->first();
        if (!$artist) return response()->json(['message' => 'Artis tidak ditemukan'], 404);

        return response()->json(['data' => $artist->concerts], 200);
    }

    // --- FITUR GALERI ---
    public function getGallery($id)
    {
        $artist = Artist::with('galleries')->where('id_artist', $id)->first();
        if (!$artist) return response()->json(['message' => 'Artist tidak ditemukan'], 404);

        return response()->json(['data' => $artist->galleries], 200);
    }

    public function addGallery(Request $request, $id)
    {
        $artist = Artist::query()->where('id_artist', $id)->first();
        if (!$artist) return response()->json(['message' => 'Artist tidak ditemukan'], 404);

        $validated = $request->validate([
            'image_url' => 'required|string',
            'description_pict' => 'nullable|string'
        ]);

        $validated['id_gallery'] = Str::uuid()->toString();
        $validated['id_artist'] = $id;
        $validated['uploaded_at'] = now();

        $gallery = \App\Models\ArtistGallery::create($validated);

        return response()->json(['message' => 'Foto galeri ditambahkan', 'data' => $gallery], 201);
    }

    public function removeGallery($id, $gallery_id)
    {
        $deleted = \App\Models\ArtistGallery::query()
            ->where('id_gallery', $gallery_id)
            ->where('id_artist', $id)
            ->delete();

        if (!$deleted) {
            return response()->json(['message' => 'Foto galeri tidak ditemukan'], 404);
        }

        return response()->json(['message' => 'Foto galeri dihapus'], 200);
    }

    public function addConcertToArtist(Request $request, $id)
    {
        $artist = Artist::query()->where('id_artist', $id)->first();
        if (!$artist) return response()->json(['message' => 'Artist tidak ditemukan'], 404);

        $request->validate(['concert_id' => 'required|exists:concert,id_concert']);

        $artist->concerts()->syncWithoutDetaching([$request->concert_id]);
        return response()->json(['message' => 'Konser berhasil ditambahkan ke profil artis'], 200);
    }

    public function removeConcertFromArtist($id, $concert_id)
    {
        $artist = Artist::query()->where('id_artist', $id)->first();
        if (!$artist) return response()->json(['message' => 'Artist tidak ditemukan'], 404);

        $artist->concerts()->detach($concert_id);
        return response()->json(['message' => 'Konser dihapus dari profil artis'], 200);
    }
}
