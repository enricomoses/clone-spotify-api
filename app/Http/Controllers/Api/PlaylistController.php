<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Playlist;
use Illuminate\Http\Request;

class PlaylistController extends Controller
{
    public function index()
    {
        $playlists = Playlist::with('category')->get();
        return response()->json(['message' => 'Data playlist', 'data' => $playlists], 200);
    }

    public function show($id)
    {
        // Menarik data playlist yang sangat kaya (relasi ke lagu dan kolaborator)
        $playlist = Playlist::with(['songs', 'collaborators', 'category'])->find($id);
        if (!$playlist) return response()->json(['message' => 'Playlist tidak ditemukan'], 404);

        return response()->json(['message' => 'Detail playlist', 'data' => $playlist], 200);
    }
}
