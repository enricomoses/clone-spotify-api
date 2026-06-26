<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Playlist;
use App\Models\PlaylistCollaborator;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class PlaylistController extends Controller
{
    public function index()
    {
        $playlists = Playlist::with(['category', 'collaborators', 'songs'])->get();
        return response()->json(['message' => 'Playlist data', 'data' => $playlists], 200);
    }

    public function show($id)
    {
        $playlist = Playlist::with(['songs.artists', 'songs.album', 'collaborators', 'category', 'followers'])
            ->where('id_playlist', $id)
            ->first();

        if (!$playlist) return response()->json(['message' => 'Playlist not found'], 404);

        return response()->json(['message' => 'Playlist detail', 'data' => $playlist], 200);
    }

    // --- CRUD BASIC ---
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'description' => 'nullable|string',
            'cover_image' => 'nullable|string',
            'id_playlist_category' => 'nullable|string|exists:playlist_categories,id_playlist_category',
            'id_user' => 'required|string|exists:users,id_user'
        ]);

        $playlistData = [
            'id_playlist' => Str::uuid()->toString(),
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'cover_image' => $validated['cover_image'] ?? null,
            'id_playlist_category' => $validated['id_playlist_category'] ?? null,
        ];

        $playlist = Playlist::create($playlistData);

        // Automatically create playlist_collaborator pivot with role 'Owner'
        $playlist->collaborators()->attach($validated['id_user'], ['role' => 'Owner']);
        $playlist->load(['collaborators', 'songs']);

        return response()->json(['message' => 'Playlist created successfully', 'data' => $playlist], 201);
    }

    public function update(Request $request, $id)
    {
        $playlist = Playlist::query()->where('id_playlist', $id)->first();
        if (!$playlist) return response()->json(['message' => 'Playlist not found'], 404);

        // Validation: only Owner can update playlist
        $request->validate([
            'id_user' => 'required|string|exists:users,id_user'
        ]);

        $isOwner = PlaylistCollaborator::where('id_playlist', $id)
            ->where('id_user', $request->id_user)
            ->where('role', 'Owner')
            ->exists();

        if (!$isOwner) {
            return response()->json(['message' => 'Only the Owner can update the playlist'], 403);
        }

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:150',
            'description' => 'nullable|string',
            'cover_image' => 'nullable|string',
            'id_playlist_category' => 'nullable|string|exists:playlist_categories,id_playlist_category'
        ]);

        $playlist->update($validated);
        $playlist->load(['collaborators', 'songs', 'category']);
        return response()->json(['message' => 'Playlist updated successfully', 'data' => $playlist], 200);
    }

    public function destroy(Request $request, $id)
    {
        $playlist = Playlist::query()->where('id_playlist', $id)->first();

        if (!$playlist) {
            return response()->json(['message' => 'Playlist not found'], 404);
        }

        // Validation: only Owner can delete playlist
        $idUser = $request->query('id_user') ?? $request->id_user;
        if (!$idUser) {
            return response()->json(['message' => 'Parameter id_user is required for ownership validation'], 400);
        }

        $isOwner = PlaylistCollaborator::where('id_playlist', $id)
            ->where('id_user', $idUser)
            ->where('role', 'Owner')
            ->exists();

        if (!$isOwner) {
            return response()->json(['message' => 'Only the Owner can delete the playlist'], 403);
        }

        // ==========================================
        // CASCADE DELETE PROCESS (RELATIONS CLEANUP)
        // ==========================================

        // 1. Detach relations with collaborators/creators (playlist_collaborator table)
        $playlist->collaborators()->detach();

        // 2. Detach relations with songs in the playlist (playlist_song table)
        $playlist->songs()->detach();

        // 3. Detach relations with users following this playlist (user_followplaylist table)
        DB::table('user_followplaylist')->where('id_playlist', $id)->delete();

        // 4. Once all dependencies are cleaned up, safely delete the main playlist
        $playlist->delete();

        return response()->json(['message' => 'Playlist and all its relations deleted successfully'], 200);
    }

    // --- PLAYLIST CONTENT MANAGEMENT ---
    public function addSong(Request $request, $id)
    {
        $playlist = Playlist::query()->where('id_playlist', $id)->first();
        if (!$playlist) return response()->json(['message' => 'Playlist not found'], 404);

        $request->validate([
            'song_id' => 'required|exists:songs,id_song',
            'id_user' => 'required|string|exists:users,id_user'
        ]);

        // Validation: only Owner or Editor can add songs
        $hasAccess = PlaylistCollaborator::where('id_playlist', $id)
            ->where('id_user', $request->id_user)
            ->whereIn('role', ['Owner', 'Editor'])
            ->exists();

        if (!$hasAccess) {
            return response()->json(['message' => 'Only the Owner or Editor can add songs to the playlist'], 403);
        }

        $playlist->songs()->syncWithoutDetaching([$request->song_id]);
        return response()->json(['message' => 'Song added to playlist successfully'], 200);
    }

    public function removeSong(Request $request, $id, $song_id)
    {
        $playlist = Playlist::query()->where('id_playlist', $id)->first();
        if (!$playlist) return response()->json(['message' => 'Playlist not found'], 404);

        // Validation: only Owner or Editor can delete songs
        $idUser = $request->query('id_user') ?? $request->id_user;
        if (!$idUser) {
            return response()->json(['message' => 'Parameter id_user is required for validation'], 400);
        }

        $hasAccess = PlaylistCollaborator::where('id_playlist', $id)
            ->where('id_user', $idUser)
            ->whereIn('role', ['Owner', 'Editor'])
            ->exists();

        if (!$hasAccess) {
            return response()->json(['message' => 'Only the Owner or Editor can remove songs from the playlist'], 403);
        }

        $playlist->songs()->detach($song_id);
        return response()->json(['message' => 'Song removed from playlist'], 200);
    }

    // --- COLLABORATOR MANAGEMENT ---
    public function addCollaborator(Request $request, $id)
    {
        $playlist = Playlist::query()->where('id_playlist', $id)->first();
        if (!$playlist) return response()->json(['message' => 'Playlist not found'], 404);

        $request->validate([
            'user_id' => 'required|exists:users,id_user',
            'id_user' => 'required|string|exists:users,id_user' // User making the request (must be Owner)
        ]);

        // Validation: only Owner can add collaborators
        $isOwner = PlaylistCollaborator::where('id_playlist', $id)
            ->where('id_user', $request->id_user)
            ->where('role', 'Owner')
            ->exists();

        if (!$isOwner) {
            return response()->json(['message' => 'Only the Owner can add collaborators'], 403);
        }

        // Check if user to be added is already a collaborator
        $alreadyCollaborator = PlaylistCollaborator::where('id_playlist', $id)
            ->where('id_user', $request->user_id)
            ->exists();

        if ($alreadyCollaborator) {
            return response()->json(['message' => 'User is already a collaborator in this playlist'], 400);
        }

        // Added collaborator always gets 'Editor' role
        $playlist->collaborators()->attach($request->user_id, ['role' => 'Editor']);
        $playlist->load('collaborators');

        return response()->json(['message' => 'Collaborator added successfully with Editor role', 'data' => $playlist], 200);
    }

    public function removeCollaborator(Request $request, $id, $user_id)
    {
        $playlist = Playlist::query()->where('id_playlist', $id)->first();

        if (!$playlist) {
            return response()->json(['message' => 'Playlist not found'], 404);
        }

        // Cannot delete user whose role is Owner
        $targetRole = PlaylistCollaborator::where('id_playlist', $id)
            ->where('id_user', $user_id)
            ->first();

        if (!$targetRole) {
            return response()->json(['message' => 'User is not a collaborator in this playlist'], 404);
        }

        if ($targetRole->role === 'Owner') {
            return response()->json(['message' => 'Cannot remove the Owner from the playlist. Delete the playlist to remove ownership.'], 400);
        }

        // Validation: only Owner can remove collaborators
        $idUser = $request->query('id_user') ?? $request->id_user;
        if ($idUser) {
            $isOwner = PlaylistCollaborator::where('id_playlist', $id)
                ->where('id_user', $idUser)
                ->where('role', 'Owner')
                ->exists();

            if (!$isOwner) {
                return response()->json(['message' => 'Only the Owner can remove collaborators'], 403);
            }
        }

        $playlist->collaborators()->detach($user_id);
        return response()->json(['message' => 'Collaborator removed'], 200);
    }
}
