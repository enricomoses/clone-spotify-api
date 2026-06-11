<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Album;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AlbumController extends Controller
{
    public function index()
    {
        $albums = Album::with('artists', 'songs')->get();
        return response()->json(['message' => 'Data album', 'data' => $albums], 200);
    }

    public function store(Request $request)
    {
        // PERBAIKAN: Menambahkan validasi array artist_ids wajib dari frontend
        $validated = $request->validate([
            'title' => 'required|string|max:150',
            'release' => 'required|date',
            'cover_image' => 'nullable|string',
            'artist_ids' => 'required|array',
            'artist_ids.*' => 'exists:artist,id_artist'
        ]);

        $albumData = [
            'id_album' => Str::uuid()->toString(),
            'title' => $validated['title'],
            'release' => $validated['release'],
            'cover_image' => $validated['cover_image'] ?? null,
        ];

        // 1. Simpan data ke tabel album
        $album = Album::create($albumData);

        // 2. Simpan relasi Many-to-Many ke tabel pivot artist_album
        $album->artists()->attach($validated['artist_ids']);

        // 3. Load relasi agar data yang dikirim balik ke Next.js langsung lengkap
        $album->load('artists', 'songs');

        return response()->json(['message' => 'Album berhasil ditambahkan', 'data' => $album], 201);
    }

    public function show($id)
    {
        // PERBAIKAN: Menggunakan where()->first() agar aman dari alarm false-positive Intelephense
        $album = Album::with('artists', 'songs')->where('id_album', $id)->first();

        if (!$album) return response()->json(['message' => 'Album tidak ditemukan'], 404);
        return response()->json(['message' => 'Detail album', 'data' => $album], 200);
    }

    public function update(Request $request, $id)
    {
        $album = Album::query()->where('id_album', $id)->first();
        if (!$album) return response()->json(['message' => 'Album tidak ditemukan'], 404);

        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:150',
            'release' => 'sometimes|required|date',
            'cover_image' => 'nullable|string',
            'artist_ids' => 'sometimes|required|array',
            'artist_ids.*' => 'exists:artist,id_artist'
        ]);

        // Update data dasar album
        $album->update($request->only(['title', 'release', 'cover_image']));

        // PERBAIKAN: Sinkronisasi data tabel pivot jika frontend mengirimkan array artist_ids yang baru
        if ($request->has('artist_ids')) {
            $album->artists()->sync($validated['artist_ids']);
        }

        $album->load('artists', 'songs');
        return response()->json(['message' => 'Album berhasil diupdate', 'data' => $album], 200);
    }

    public function destroy($id)
    {
        $album = Album::query()->where('id_album', $id)->first();
        if (!$album) {
            return response()->json(['message' => 'Album tidak ditemukan'], 404);
        }

        // PERBAIKAN: Putus relasi di tabel pivot artist_album terlebih dahulu agar tidak menyisakan data sampah
        $album->artists()->detach();

        DB::table('song')->where('id_album', $id)->update(['id_album' => null]);

        // Hapus data album utama
        $album->delete();

        return response()->json(['message' => 'Album berhasil dihapus'], 200);
    }

    // --- MANAJEMEN ARTIS PADA ALBUM SECARA MANUAL (ENDPOINT TAMBAHAN) ---
    public function addArtist(Request $request, $id)
    {
        $album = Album::query()->where('id_album', $id)->first();
        if (!$album) return response()->json(['message' => 'Album tidak ditemukan'], 404);

        $request->validate(['artist_id' => 'required|exists:artist,id_artist']);

        $album->artists()->syncWithoutDetaching([$request->artist_id]);
        return response()->json(['message' => 'Artis berhasil ditambahkan ke album'], 200);
    }

    public function removeArtist($id, $artist_id)
    {
        $album = Album::query()->where('id_album', $id)->first();
        if (!$album) return response()->json(['message' => 'Album tidak ditemukan'], 404);

        $album->artists()->detach($artist_id);
        return response()->json(['message' => 'Artis dihapus dari album'], 200);
    }
}
