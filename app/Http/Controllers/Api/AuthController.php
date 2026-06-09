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
            return response()->json(['message' => 'Kredensial tidak valid'], 401);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Login berhasil',
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

        // Mengecek apakah artist ada dan password string persis sama (Tanpa Hash)
        if (!$artist || $artist->password !== $request->password) {
            return response()->json(['message' => 'Kredensial artist tidak valid'], 401);
        }

        $token = $artist->createToken('artist_token')->plainTextToken;

        return response()->json([
            'message' => 'Login artist berhasil',
            'artist' => $artist,
            'access_token' => $token
        ], 200);
    }
}
