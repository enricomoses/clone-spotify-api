<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Album;
use Illuminate\Http\Request;

class AlbumController extends Controller
{
    public function index()
    {
        $albums = Album::with('artists', 'songs')->get();
        return response()->json(['message' => 'Data album', 'data' => $albums], 200);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id_album' => 'required|string|unique:album,id_album',
            'title' => 'required|string|max:150',
            'release' => 'required|date',
            'cover_image' => 'nullable|string'
        ]);

        $album = Album::create($validated);
        return response()->json(['message' => 'Album berhasil ditambahkan', 'data' => $album], 201);
    }

    public function show($id)
    {
        $album = Album::with('artists', 'songs')->find($id);
        if (!$album) return response()->json(['message' => 'Album tidak ditemukan'], 404);
        return response()->json(['message' => 'Detail album', 'data' => $album], 200);
    }

    public function update(Request $request, $id)
    {
        $album = Album::find($id);
        if (!$album) return response()->json(['message' => 'Album tidak ditemukan'], 404);

        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:150',
            'release' => 'sometimes|required|date',
            'cover_image' => 'nullable|string'
        ]);

        $album->update($validated);
        return response()->json(['message' => 'Album berhasil diupdate', 'data' => $album], 200);
    }

    public function destroy($id)
    {
        $album = Album::find($id);
        if (!$album) return response()->json(['message' => 'Album tidak ditemukan'], 404);

        $album->delete();
        return response()->json(['message' => 'Album berhasil dihapus'], 200);
    }
}
