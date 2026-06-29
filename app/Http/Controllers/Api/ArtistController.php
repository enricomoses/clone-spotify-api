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
        $artists = Artist::with(['albums', 'songs.album', 'songs.artists', 'concerts', 'galleries'])->get();
        return response()->json(['message' => 'Artist data', 'data' => $artists], 200);
    }

    public function show($id)
    {
        $artist = Artist::with(['albums', 'songs.album', 'songs.artists', 'concerts', 'galleries', 'followers'])
            ->where('id_artist', $id)
            ->first();

        if (!$artist) return response()->json(['message' => 'Artist not found'], 404);

        return response()->json(['message' => 'Artist detail', 'data' => $artist], 200);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'email' => 'required|email|unique:artists,email',
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

        return response()->json(['message' => 'Artist registered successfully', 'data' => $artist], 201);
    }

    public function update(Request $request, $id)
    {
        $artist = Artist::query()->where('id_artist', $id)->first();
        if (!$artist) {
            return response()->json(['message' => 'Artist not found'], 404);
        }

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:150',
            'email' => 'sometimes|required|email|unique:artists,email,' . $id . ',id_artist',
            'password' => 'sometimes|required|string|min:6',
            'instagram' => 'nullable|string',
            'facebook' => 'nullable|string',
            'wikipedia' => 'nullable|string',
            'x' => 'nullable|string',
            'bio' => 'nullable|string',
            'profile_picture' => 'nullable|string'
        ]);

        $artist->update($validated);

        return response()->json(['message' => 'Artist data updated successfully', 'data' => $artist], 200);
    }

    public function destroy($id)
    {
        $artist = Artist::query()->where('id_artist', $id)->first();
        if (!$artist) {
            return response()->json(['message' => 'Artist not found'], 404);
        }

        $songIds = DB::table('song_artist')->where('id_artist', $id)->pluck('id_song');

        foreach ($songIds as $songId) {
            $artistCount = DB::table('song_artist')->where('id_song', $songId)->count();

            if ($artistCount <= 1) {
                $song = Song::where('id_song', $songId)->first();
                if ($song) {
                    $song->artists()->detach();
                    $song->playlists()->detach();
                    DB::table('user_likedsong')->where('id_song', $songId)->delete();
                    DB::table('song_streaming_histories')->where('id_song', $songId)->delete();
                    $song->delete();
                }
            }
        }

        $artist->songs()->detach();

        $artist->albums()->detach();

        $artist->concerts()->detach();

        DB::table('artist_galleries')->where('id_artist', $id)->delete();

        DB::table('user_followartist')->where('id_artist', $id)->delete();

        $artist->delete();

        return response()->json(['message' => 'Artist and all related data deleted successfully'], 200);
    }

    public function addAlbumToArtist(Request $request, $id)
    {
        $artist = Artist::query()->where('id_artist', $id)->first();
        if (!$artist) return response()->json(['message' => 'Artist not found'], 404);

        $request->validate(['album_id' => 'required|exists:albums,id_album']);

        $artist->albums()->syncWithoutDetaching([$request->album_id]);
        return response()->json(['message' => 'Album added to artist profile successfully'], 200);
    }

    public function removeAlbumFromArtist($id, $album_id)
    {
        $artist = Artist::query()->where('id_artist', $id)->first();
        if (!$artist) return response()->json(['message' => 'Artist not found'], 404);

        $artist->albums()->detach($album_id);
        return response()->json(['message' => 'Album removed from artist profile'], 200);
    }

    public function addSongToArtist(Request $request, $id)
    {
        $artist = Artist::query()->where('id_artist', $id)->first();
        if (!$artist) return response()->json(['message' => 'Artist not found'], 404);

        $request->validate(['song_id' => 'required|exists:songs,id_song']);

        $artist->songs()->syncWithoutDetaching([$request->song_id]);
        return response()->json(['message' => 'Song added to artist profile successfully'], 200);
    }

    public function removeSongFromArtist($id, $song_id)
    {
        $artist = Artist::query()->where('id_artist', $id)->first();
        if (!$artist) return response()->json(['message' => 'Artist not found'], 404);

        $artistCount = DB::table('song_artist')->where('id_song', $song_id)->count();
        if ($artistCount <= 1) {
            return response()->json([
                'message' => 'Cannot remove the last artist from the song. Song must have at least 1 artist.'
            ], 400);
        }

        $artist->songs()->detach($song_id);
        return response()->json(['message' => 'Song removed from artist profile'], 200);
    }

    public function getConcerts($id)
    {
        $artist = Artist::with('concerts')->where('id_artist', $id)->first();
        if (!$artist) return response()->json(['message' => 'Artist not found'], 404);

        return response()->json(['data' => $artist->concerts], 200);
    }

    public function getGallery($id)
    {
        $artist = Artist::with('galleries')->where('id_artist', $id)->first();
        if (!$artist) return response()->json(['message' => 'Artist not found'], 404);

        return response()->json(['data' => $artist->galleries], 200);
    }

    public function addGallery(Request $request, $id)
    {
        $artist = Artist::query()->where('id_artist', $id)->first();
        if (!$artist) return response()->json(['message' => 'Artist not found'], 404);

        $validated = $request->validate([
            'image_url' => 'required|string',
            'description_pict' => 'nullable|string'
        ]);

        $validated['id_gallery'] = Str::uuid()->toString();
        $validated['id_artist'] = $id;
        $validated['uploaded_at'] = now();

        $gallery = \App\Models\ArtistGallery::create($validated);

        return response()->json(['message' => 'Gallery photo added successfully', 'data' => $gallery], 201);
    }

    public function removeGallery($id, $gallery_id)
    {
        $deleted = \App\Models\ArtistGallery::query()
            ->where('id_gallery', $gallery_id)
            ->where('id_artist', $id)
            ->delete();

        if (!$deleted) {
            return response()->json(['message' => 'Gallery photo not found'], 404);
        }

        return response()->json(['message' => 'Gallery photo deleted successfully'], 200);
    }

    public function addConcertToArtist(Request $request, $id)
    {
        $artist = Artist::query()->where('id_artist', $id)->first();
        if (!$artist) return response()->json(['message' => 'Artist not found'], 404);

        $request->validate(['concert_id' => 'required|exists:concerts,id_concert']);

        $artist->concerts()->syncWithoutDetaching([$request->concert_id]);
        return response()->json(['message' => 'Concert added to artist profile successfully'], 200);
    }

    public function removeConcertFromArtist($id, $concert_id)
    {
        $artist = Artist::query()->where('id_artist', $id)->first();
        if (!$artist) return response()->json(['message' => 'Artist not found'], 404);

        $artist->concerts()->detach($concert_id);
        return response()->json(['message' => 'Concert removed from artist profile'], 200);
    }
}
