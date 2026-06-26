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
        $albums = Album::with('artists', 'songs.artists')->get();
        return response()->json(['message' => 'Album data', 'data' => $albums], 200);
    }

    public function store(Request $request)
    {
        // FIX: Adding required artist_ids array validation from frontend
        $validated = $request->validate([
            'title' => 'required|string|max:150',
            'release' => 'required|date',
            'cover_image' => 'required|string',  // ERD: mandatory (*)
            'artist_ids' => 'required|array',
            'artist_ids.*' => 'exists:artists,id_artist'
        ]);

        $albumData = [
            'id_album' => Str::uuid()->toString(),
            'title' => $validated['title'],
            'release' => $validated['release'],
            'cover_image' => $validated['cover_image'] ?? null,
        ];

        // 1. Save data to album table
        $album = Album::create($albumData);

        // 2. Save Many-to-Many relation to artist_album pivot table
        $album->artists()->attach($validated['artist_ids']);

        // 3. Load relations so the response to Next.js is fully populated
        $album->load('artists', 'songs.artists');

        return response()->json(['message' => 'Album added successfully', 'data' => $album], 201);
    }

    public function show($id)
    {
        // FIX: Using where()->first() to prevent Intelephense false-positive warning
        $album = Album::with('artists', 'songs.artists')->where('id_album', $id)->first();

        if (!$album) return response()->json(['message' => 'Album not found'], 404);
        return response()->json(['message' => 'Album detail', 'data' => $album], 200);
    }

    public function update(Request $request, $id)
    {
        $album = Album::query()->where('id_album', $id)->first();
        if (!$album) return response()->json(['message' => 'Album not found'], 404);

        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:150',
            'release' => 'sometimes|required|date',
            'cover_image' => 'nullable|string',
            'artist_ids' => 'sometimes|required|array',
            'artist_ids.*' => 'exists:artists,id_artist'
        ]);

        // Update basic album data
        $album->update($request->only(['title', 'release', 'cover_image']));

        // FIX: Synchronize pivot table data if frontend sends new artist_ids array
        if ($request->has('artist_ids')) {
            $album->artists()->sync($validated['artist_ids']);
        }

        $album->load('artists', 'songs.artists');
        return response()->json(['message' => 'Album updated successfully', 'data' => $album], 200);
    }

    public function destroy($id)
    {
        $album = Album::query()->where('id_album', $id)->first();
        if (!$album) {
            return response()->json(['message' => 'Album not found'], 404);
        }

        // FIX: Detach relations in artist_album pivot table first to avoid orphaned data
        $album->artists()->detach();

        DB::table('song')->where('id_album', $id)->update(['id_album' => null]);

        // Delete main album data
        $album->delete();

        return response()->json(['message' => 'Album deleted successfully'], 200);
    }

    // --- MANUAL ARTIST MANAGEMENT ON ALBUM (ADDITIONAL ENDPOINTS) ---
    public function addArtist(Request $request, $id)
    {
        $album = Album::query()->where('id_album', $id)->first();
        if (!$album) return response()->json(['message' => 'Album not found'], 404);

        $request->validate(['artist_id' => 'required|exists:artists,id_artist']);

        $album->artists()->syncWithoutDetaching([$request->artist_id]);
        return response()->json(['message' => 'Artist added to album successfully'], 200);
    }

    public function removeArtist($id, $artist_id)
    {
        $album = Album::query()->where('id_album', $id)->first();
        if (!$album) return response()->json(['message' => 'Album not found'], 404);

        // Check that album does not end up with 0 artists (at least 1 artist is required)
        $currentArtistCount = $album->artists()->count();
        if ($currentArtistCount <= 1) {
            return response()->json([
                'message' => 'Cannot remove the last artist from the album. Album must have at least 1 artist.'
            ], 400);
        }

        $album->artists()->detach($artist_id);
        return response()->json(['message' => 'Artist removed from album'], 200);
    }
}
