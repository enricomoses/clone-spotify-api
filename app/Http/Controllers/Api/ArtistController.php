<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Artist;
use Illuminate\Http\Request;

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
}
