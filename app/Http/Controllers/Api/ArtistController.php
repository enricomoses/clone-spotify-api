<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Artist;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ArtistController extends Controller
{
    public function index()
    {
        // Menampilkan data artis lengkap dengan album yang dimilikinya
        $artists = Artist::with('albums')->get();
        return response()->json(['message' => 'Data artist', 'data' => $artists], 200);
    }

    public function show($id)
    {
        // Perbaikan Intelephense: Menggunakan where()->first()
        $artist = Artist::with(['albums', 'songs', 'concerts', 'galleries'])
            ->where('id_artist', $id)
            ->first();

        if (!$artist) return response()->json(['message' => 'Artist tidak ditemukan'], 404);

        return response()->json(['message' => 'Detail artist', 'data' => $artist], 200);
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
            'password' => 'sometimes|required|string', // Ingat: tidak di-hash sesuai business rule Anda
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

    // --- MANAJEMEN RELASI DARI SISI ARTIS ---

    public function addAlbumToArtist(Request $request, $id)
    {
        $artist = Artist::query()->where('id_artist', $id)->first();
        $request->validate(['album_id' => 'required|exists:album,id_album']);

        $artist->albums()->syncWithoutDetaching([$request->album_id]);
        return response()->json(['message' => 'Album berhasil ditambahkan ke profil artis'], 200);
    }

    public function addSongToArtist(Request $request, $id)
    {
        $artist = Artist::query()->where('id_artist', $id)->first();
        $request->validate(['song_id' => 'required|exists:song,id_song']);

        $artist->songs()->syncWithoutDetaching([$request->song_id]);
        return response()->json(['message' => 'Lagu berhasil ditambahkan ke profil artis'], 200);
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
        return response()->json(['data' => $artist->galleries], 200);
    }

    public function addGallery(Request $request, $id)
    {
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
