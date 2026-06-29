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
        $users = User::withCount(['followers', 'following'])->get();
        return response()->json(['message' => 'User data', 'data' => $users], 200);
    }

    public function search(Request $request)
    {
        $query = $request->query('q', '');

        if (strlen($query) < 1) {
            return response()->json(['message' => 'Search query is required', 'data' => []], 200);
        }

        $users = User::where('name', 'ILIKE', "%{$query}%")
            ->withCount(['followers', 'following'])
            ->limit(20)
            ->get();

        return response()->json(['message' => 'Search results', 'data' => $users], 200);
    }

    public function getUserProfile($id)
    {
        $user = User::with([
            'following',
            'followers',
            'followedArtists',
            'podcasts',
        ])->withCount(['followers', 'following'])->where('id_user', $id)->first();

        if (!$user) return response()->json(['message' => 'User not found'], 404);

        $userPlaylists = Playlist::with(['songs', 'collaborators', 'category'])
            ->whereHas('collaborators', function ($q) use ($id) {
                $q->where('playlist_collaborator.id_user', $id)->where('playlist_collaborator.role', 'Owner');
            })->get();

        $recentArtists = DB::table('song_streaming_histories')
            ->join('song_artist', 'song_streaming_histories.id_song', '=', 'song_artist.id_song')
            ->join('artists', 'song_artist.id_artist', '=', 'artists.id_artist')
            ->where('song_streaming_histories.id_user', $id)
            ->select('artists.id_artist', 'artists.name', 'artists.profile_picture', DB::raw('COUNT(*) as play_count'), DB::raw('MAX(song_streaming_histories.played_at) as last_played'))
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
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
            'birthday' => 'nullable|date|before:today',
            'gender' => 'nullable|string',
            'country' => 'nullable|string'
        ]);

        $validated['id_user'] = Str::uuid()->toString();

        $user = User::create($validated);

        return response()->json(['message' => 'User created successfully', 'data' => $user], 201);
    }

    public function show($id)
    {
        $user = User::with([
            'followedPlaylists.collaborators',
            'following',
            'followers',
            'followedArtists',
            'followedPodcasts',
            'likedSongs.artists',
            'likedSongs.album',
            'collaborativePlaylists.collaborators',
            'podcasts'
        ])->where('id_user', $id)->first();

        if (!$user) return response()->json(['message' => 'User not found'], 404);

        return response()->json(['message' => 'User detail', 'data' => $user], 200);
    }

    public function update(Request $request, $id)
    {
        $user = User::where('id_user', $id)->first();
        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:100',
            'birthday' => 'nullable|date|before:today',
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

        unset($validated['current_password']);

        $user->update($validated);
        return response()->json(['message' => 'User updated successfully', 'data' => $user], 200);
    }

    public function destroy($id)
    {
        $user = User::where('id_user', $id)->first();
        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }


        DB::table('song_streaming_histories')->where('id_user', $id)->delete();
        DB::table('episode_streaming_histories')->where('id_user', $id)->delete();

        DB::table('user_likedsong')->where('id_user', $id)->delete();
        DB::table('user_followartist')->where('id_user', $id)->delete();
        DB::table('user_followplaylist')->where('id_user', $id)->delete();
        DB::table('user_followpodcast')->where('id_user', $id)->delete();

        DB::table('user_followuser')->where('follower_id', $id)->orWhere('followed_id', $id)->delete();

        $collaborations = DB::table('playlist_collaborator')->where('id_user', $id)->get();

        foreach ($collaborations as $collab) {
            if ($collab->role === 'Owner') {
                $playlist = Playlist::where('id_playlist', $collab->id_playlist)->first();
                if ($playlist) {
                    $playlist->collaborators()->detach();
                    $playlist->songs()->detach();
                    DB::table('user_followplaylist')->where('id_playlist', $collab->id_playlist)->delete();
                    $playlist->delete();
                }
            }
        }

        DB::table('playlist_collaborator')->where('id_user', $id)->delete();

        $podcasts = DB::table('podcasts')->where('id_user', $id)->pluck('id_podcast');
        foreach ($podcasts as $podcastId) {
            DB::table('user_followpodcast')->where('id_podcast', $podcastId)->delete();

            $episodes = DB::table('episodes')->where('id_podcast', $podcastId)->pluck('id_episode');
            if ($episodes->isNotEmpty()) {
                DB::table('episode_streaming_histories')->whereIn('id_episode', $episodes)->delete();
                DB::table('episodes')->where('id_podcast', $podcastId)->delete();
            }

            DB::table('podcasts')->where('id_podcast', $podcastId)->delete();
        }

        $user->delete();
        return response()->json(['message' => 'User and all associated data deleted successfully'], 200);
    }
}
