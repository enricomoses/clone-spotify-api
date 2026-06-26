<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Artist;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        $user = User::with([
            'followedPlaylists',
            'following',
            'followedArtists',
            'followedPodcasts',
            'likedSongs'
        ])->where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Login successful',
            'user' => $user,
            'access_token' => $token
        ], 200);
    }

    public function loginArtist(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        $artist = Artist::query()->where('email', $request->email)->first();

        // Check if artist exists and password matches (without hashing)
        if (!$artist || $artist->password !== $request->password) {
            return response()->json(['message' => 'Invalid artist credentials'], 401);
        }

        $token = $artist->createToken('artist_token')->plainTextToken;

        return response()->json([
            'message' => 'Artist login successful',
            'artist' => $artist,
            'access_token' => $token
        ], 200);
    }
}
