<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str; // PENTING: Tambahkan baris ini untuk menggunakan helper UUID

class UserController extends Controller
{
    public function index()
    {
        $users = User::all();
        return response()->json(['message' => 'Data user', 'data' => $users], 200);
    }

    public function store(Request $request)
    {
        // 1. Validasi input dari frontend
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'email' => 'required|email|unique:User,email',
            'password' => 'required|string|min:6',
            'birthday' => 'nullable|date|before:today',
            'gender' => 'nullable|string',
            'country' => 'nullable|string'
        ]);

        // 2. Generate UUID v4 secara otomatis
        $validated['id_user'] = Str::uuid()->toString();

        // 3. Simpan ke database
        $user = User::create($validated);

        return response()->json(['message' => 'User berhasil dibuat', 'data' => $user], 201);
    }

    public function show($id)
    {
        $user = User::with([
            'followedPlaylists',
            'following',
            'followedArtists',
            'followedPodcasts',
            'likedSongs'
        ])->where('id_user', $id)->first();

        if (!$user) return response()->json(['message' => 'User tidak ditemukan'], 404);

        return response()->json(['message' => 'Detail user', 'data' => $user], 200);
    }

    public function update(Request $request, $id)
    {
        $user = User::find($id);
        if (!$user) {
            return response()->json(['message' => 'User tidak ditemukan'], 404);
        }

        // CONSTRAINT / VALIDASI ATURAN BISNIS SAAT UPDATE
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:100',
            // Rule unik ini mengecualikan id_user yang sedang diupdate agar tidak error
            'email' => 'sometimes|required|email|unique:User,email,' . $id . ',id_user',
            'password' => 'sometimes|required|string|min:6',
            'birthday' => 'nullable|date|before:today', // Constraint: Tanggal lahir harus sebelum hari ini
            'gender' => 'nullable|string',
            'country' => 'nullable|string',
            'profile_picture' => 'nullable|string'
        ]);

        $user->update($validated);
        return response()->json(['message' => 'User berhasil diupdate', 'data' => $user], 200);
    }

    public function destroy($id)
    {
        $user = User::find($id);
        if (!$user) {
            return response()->json(['message' => 'User tidak ditemukan'], 404);
        }

        // 1. Hapus riwayat streaming
        DB::table('song_streaminghistory')->where('id_user', $id)->delete();
        DB::table('episode_streaminghistory')->where('id_user', $id)->delete();

        // 2. Hapus data sosial (Like & Follow)
        DB::table('user_likedsong')->where('id_user', $id)->delete();
        DB::table('user_followartist')->where('id_user', $id)->delete();
        DB::table('user_followplaylist')->where('id_user', $id)->delete();
        DB::table('user_followpodcast')->where('id_user', $id)->delete();

        // 3. Hapus relasi follow antar user (baik sebagai follower maupun followed)
        DB::table('user_followuser')->where('follower_id', $id)->orWhere('followed_id', $id)->delete();

        // 4. Hapus dari daftar kolaborator playlist
        DB::table('playlist_collaborator')->where('id_user', $id)->delete();

        // 5. Tangani Podcast buatan user ini (Opsional: hapus podcast-nya, atau biarkan tapi set id_user menjadi null jika diizinkan database)
        // Karena di ERD podcast dibuat oleh user, amannya kita hapus podcast-nya menggunakan logika dari PodcastController
        $podcasts = DB::table('podcast')->where('id_user', $id)->pluck('id_podcast');
        foreach ($podcasts as $podcastId) {
            // Panggil logika hapus podcast agar episode dan history-nya ikut terhapus
            app(\App\Http\Controllers\Api\PodcastController::class)->destroy($podcastId);
        }
        
        $user->delete();
        return response()->json(['message' => 'User berhasil dihapus'], 200);
    }
}
