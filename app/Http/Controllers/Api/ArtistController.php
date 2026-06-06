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
        $artists = Artist::all();
        return response()->json(['message' => 'Data artist', 'data' => $artists], 200);
    }

    public function show($id)
    {
        // Menampilkan artis beserta album dan lagu-lagunya (Nested Eager Loading)
        $artist = Artist::with(['albums', 'songs'])->find($id);
        if (!$artist) return response()->json(['message' => 'Artist tidak ditemukan'], 404);

        return response()->json(['message' => 'Detail artist', 'data' => $artist], 200);
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
        \App\Models\ArtistGallery::where('id_gallery', $gallery_id)
            ->where('id_artist', $id)
            ->delete();
        return response()->json(['message' => 'Foto galeri dihapus'], 200);
    }
}
