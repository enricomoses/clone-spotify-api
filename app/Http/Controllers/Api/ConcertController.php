<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Concert;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ConcertController extends Controller
{
    public function index()
    {
        $concerts = Concert::with('artists')->get();
        return response()->json(['message' => 'Concert data', 'data' => $concerts], 200);
    }

    public function show($id)
    {
        // Fix Intelephense warnings with where()->first()
        $concert = Concert::with('artists')->where('id_concert', $id)->first();
        if (!$concert) return response()->json(['message' => 'Concert not found'], 404);

        return response()->json(['message' => 'Concert detail', 'data' => $concert], 200);
    }

    // --- CRUD BASIC ---
    public function store(Request $request)
    {
        // FIX: Add required artist_ids array validation from frontend
        $validated = $request->validate([
            'location' => 'required|string',
            'location_url' => 'nullable|url',
            'event_at' => 'required|date',
            'ticket_url' => 'required|url',
            'artist_ids' => 'required|array',
            'artist_ids.*' => 'exists:artist,id_artist'
        ]);

        $concertData = [
            'id_concert' => Str::uuid()->toString(),
            'location' => $validated['location'],
            'location_url' => $validated['location_url'] ?? null,
            'event_at' => $validated['event_at'],
            'ticket_url' => $validated['ticket_url'],
        ];

        // 1. Save data to concert table
        $concert = Concert::create($concertData);

        // 2. Save Many-to-Many relation to artist_concert pivot table
        $concert->artists()->attach($validated['artist_ids']);

        // 3. Load relations so response data includes the artist list
        $concert->load('artists');

        return response()->json(['message' => 'Concert added successfully', 'data' => $concert], 201);
    }

    public function update(Request $request, $id)
    {
        $concert = Concert::query()->where('id_concert', $id)->first();
        if (!$concert) return response()->json(['message' => 'Concert not found'], 404);

        $validated = $request->validate([
            'location' => 'sometimes|required|string',
            'location_url' => 'nullable|url',
            'event_at' => 'sometimes|required|date',
            'ticket_url' => 'sometimes|required|url',
            'artist_ids' => 'sometimes|required|array',
            'artist_ids.*' => 'exists:artist,id_artist'
        ]);

        // Update basic concert data
        $concert->update($request->only(['location', 'location_url', 'event_at', 'ticket_url']));

        // FIX: Sync pivot table data if frontend sends new artist_ids array
        if ($request->has('artist_ids')) {
            $concert->artists()->sync($validated['artist_ids']);
        }

        $concert->load('artists');
        return response()->json(['message' => 'Concert updated successfully', 'data' => $concert], 200);
    }

    public function destroy($id)
    {
        $concert = Concert::query()->where('id_concert', $id)->first();
        if (!$concert) {
            return response()->json(['message' => 'Concert not found'], 404);
        }

        // FIX: Detach relations in artist_concert pivot table first
        $concert->artists()->detach();

        // Delete main concert data
        $concert->delete();

        return response()->json(['message' => 'Concert deleted successfully'], 200);
    }

    // --- MANUAL ARTIST MANAGEMENT ON CONCERT (ADDITIONAL ENDPOINTS) ---
    public function addArtist(Request $request, $id)
    {
        $concert = Concert::query()->where('id_concert', $id)->first();
        if (!$concert) return response()->json(['message' => 'Concert not found'], 404);

        $request->validate(['artist_id' => 'required|exists:artist,id_artist']);

        $concert->artists()->syncWithoutDetaching([$request->artist_id]);
        return response()->json(['message' => 'Artist added to concert successfully'], 200);
    }

    public function removeArtist($id, $artist_id)
    {
        $concert = Concert::query()->where('id_concert', $id)->first();
        if (!$concert) return response()->json(['message' => 'Concert not found'], 404);

        // Check that concert does not end up with 0 artists (at least 1 artist)
        $currentArtistCount = $concert->artists()->count();
        if ($currentArtistCount <= 1) {
            return response()->json([
                'message' => 'Cannot remove the last artist from the concert. Concert must have at least 1 artist.'
            ], 400);
        }

        $concert->artists()->detach($artist_id);
        return response()->json(['message' => 'Artist removed from concert'], 200);
    }
}
