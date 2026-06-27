<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Playlist;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserController extends Controller
{
    public function index()
    {
        $users = User::all();
        return response()->json(['message' => 'User data', 'data' => $users], 200);
    }

    /**
     * Search users by name (GET /users/search?q=...)
     */
    public function search(Request $request)
    {
        $query = $request->query('q', '');

        if (strlen($query) < 1) {
            return response()->json(['message' => 'Search query is required', 'data' => []], 200);
        }

        $users = User::where('name', 'LIKE', "%{$query}%")
            ->withCount(['followers', 'following'])
            ->limit(20)
            ->get();

        return response()->json(['message' => 'Search results', 'data' => $users], 200);
    }

    /**
     * Get user public profile (GET /users/{id}/profile)
     * Returns user info, playlists, podcasts, followers/following, recent played artists
     */
    public function getUserProfile($id)
    {
        $user = User::with([
            'following',
            'followers',
            'followedArtists',
            'podcasts',
        ])->withCount(['followers', 'following'])->where('id_user', $id)->first();

        if (!$user) return response()->json(['message' => 'User not found'], 404);

        // Get playlists where user is Owner (via playlist_collaborator)
        $userPlaylists = Playlist::with(['songs', 'collaborators', 'category'])
            ->whereHas('collaborators', function ($q) use ($id) {
                $q->where('id_user', $id)->where('role', 'Owner');
            })->get();

        // Get recent played artists from streaming history
        $recentArtists = DB::table('song_streaminghistory')
            ->join('song_artist', 'song_streaminghistory.id_song', '=', 'song_artist.id_song')
            ->join('artists', 'song_artist.id_artist', '=', 'artists.id_artist')
            ->where('song_streaminghistory.id_user', $id)
            ->select('artists.id_artist', 'artists.name', 'artists.profile_picture', DB::raw('COUNT(*) as play_count'), DB::raw('MAX(song_streaminghistory.played_at) as last_played'))
            ->groupBy('artists.id_artist', 'artists.name', 'artists.profile_picture')
            ->orderByDesc('last_played')
            ->limit(10)
            ->get();

        $responseData = $user->toArray();
        $responseData['user_playlists'] = $userPlaylists;
        $responseData['recent_artists'] = $recentArtists;

        return response()->json(['message' => 'User profile', 'data' => $responseData], 200);
    }

    public function store(Request $request)
    {
        // 1. Validate input from frontend
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
            'birthday' => 'nullable|date|before:today',
            'gender' => 'nullable|string',
            'country' => 'nullable|string'
        ]);

        // 2. Auto-generate UUID v4
        $validated['id_user'] = Str::uuid()->toString();

        // 3. Save to database
        $user = User::create($validated);

        return response()->json(['message' => 'User created successfully', 'data' => $user], 201);
    }

    public function show($id)
    {
        // FIX: Load all important relations including followers, collaborativePlaylists, podcasts
        $user = User::with([
            'followedPlaylists',
            'following',
            'followers',
            'followedArtists',
            'followedPodcasts',
            'likedSongs.artists',
            'likedSongs.album',
            'collaborativePlaylists',
            'podcasts'
        ])->where('id_user', $id)->first();

        if (!$user) return response()->json(['message' => 'User not found'], 404);

        return response()->json(['message' => 'User detail', 'data' => $user], 200);
    }

    public function update(Request $request, $id)
    {
        // FIX: Using where()->first() because PK is not the default 'id'
        $user = User::where('id_user', $id)->first();
        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        // CONSTRAINT / BUSINESS RULE VALIDATION ON UPDATE
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:100',
            'birthday' => 'nullable|date|before:today', // Constraint: Birthday must be before today
            'gender' => 'nullable|string',
            'country' => 'nullable|string',
            'profile_picture' => 'nullable|string',
            'current_password' => [
                'required_with:password',
                'string',
                function ($attribute, $value, $fail) use ($user) {
                    if ($value && !Hash::check($value, $user->password)) {
                        $fail('Current password is incorrect.');
                    }
                }
            ],
            'password' => [
                'sometimes',
                'required',
                'string',
                'min:6',
                function ($attribute, $value, $fail) use ($user) {
                    if ($value && Hash::check($value, $user->password)) {
                        $fail('New password cannot be the same as the current password.');
                    }
                }
            ]
        ]);

        // Remove current_password so it is not included in the database update query
        unset($validated['current_password']);

        $user->update($validated);
        return response()->json(['message' => 'User updated successfully', 'data' => $user], 200);
    }

    public function destroy($id)
    {
        // FIX: Using where()->first() because PK is not the default 'id'
        $user = User::where('id_user', $id)->first();
        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        // ==========================================
        // CASCADE DELETE PROCESS (RELATION CLEANUP)
        // ==========================================

        // 1. Delete streaming history
        DB::table('song_streaminghistory')->where('id_user', $id)->delete();
        DB::table('episode_streaminghistory')->where('id_user', $id)->delete();

        // 2. Delete social data (Like & Follow)
        DB::table('user_likedsong')->where('id_user', $id)->delete();
        DB::table('user_followartist')->where('id_user', $id)->delete();
        DB::table('user_followplaylist')->where('id_user', $id)->delete();
        DB::table('user_followpodcast')->where('id_user', $id)->delete();

        // 3. Delete follow relations between users (both as follower and followed)
        DB::table('user_followuser')->where('follower_id', $id)->orWhere('followed_id', $id)->delete();

        // 4. Handle playlists owned by this user
        //    - If user is Owner → delete playlist along with its relations
        //    - If user is Editor → just remove from collaborator
        $collaborations = DB::table('playlist_collaborator')->where('id_user', $id)->get();

        foreach ($collaborations as $collab) {
            if ($collab->role === 'Owner') {
                // This user is the playlist Owner → delete playlist along with all its relations
                $playlist = Playlist::where('id_playlist', $collab->id_playlist)->first();
                if ($playlist) {
                    $playlist->collaborators()->detach();
                    $playlist->songs()->detach();
                    DB::table('user_followplaylist')->where('id_playlist', $collab->id_playlist)->delete();
                    $playlist->delete();
                }
            }
        }

        // Delete remaining collaborator entries (for playlists where user was only Editor)
        DB::table('playlist_collaborator')->where('id_user', $id)->delete();

        // 5. Handle Podcasts created by this user
        //    Since in ERD podcast is created by user (mandatory FK), delete podcast along with its relations
        $podcasts = DB::table('podcast')->where('id_user', $id)->pluck('id_podcast');
        foreach ($podcasts as $podcastId) {
            // Delete podcast followers
            DB::table('user_followpodcast')->where('id_podcast', $podcastId)->delete();

            // Delete episodes along with their streaming history
            $episodes = DB::table('episode')->where('id_podcast', $podcastId)->pluck('id_episode');
            if ($episodes->isNotEmpty()) {
                DB::table('episode_streaminghistory')->whereIn('id_episode', $episodes)->delete();
                DB::table('episode')->where('id_podcast', $podcastId)->delete();
            }

            // Delete podcast
            DB::table('podcast')->where('id_podcast', $podcastId)->delete();
        }

        // 6. Delete the main user
        $user->delete();
        return response()->json(['message' => 'User and all associated data deleted successfully'], 200);
    }
}
