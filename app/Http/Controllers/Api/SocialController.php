<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class SocialController extends Controller
{
    // Fitur Like/Unlike Lagu
    public function toggleLikeSong(Request $request)
    {
        $request->validate([
            'id_user' => 'required|exists:User,id_user',
            'id_song' => 'required|exists:song,id_song'
        ]);

        $user = User::find($request->id_user);
        // Toggle akan menambah data ke tabel user_likedsong jika belum ada, atau menghapus jika sudah ada
        $user->likedSongs()->toggle($request->id_song);

        return response()->json(['message' => 'Status like lagu berhasil diubah'], 200);
    }

    // Fitur Follow/Unfollow User Lain (Mutual)
    public function toggleFollowUser(Request $request)
    {
        $request->validate([
            'follower_id' => 'required|exists:User,id_user',
            'followed_id' => 'required|exists:User,id_user'
        ]);

        $user = User::find($request->follower_id);
        $user->following()->toggle($request->followed_id);

        return response()->json(['message' => 'Status follow user berhasil diubah'], 200);
    }
}
