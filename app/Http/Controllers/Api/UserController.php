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
        // PERBAIKAN: Load semua relasi penting termasuk followers, collaborativePlaylists, podcasts
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

        if (!$user) return response()->json(['message' => 'User tidak ditemukan'], 404);

        return response()->json(['message' => 'Detail user', 'data' => $user], 200);
    }

    public function update(Request $request, $id)
    {
        // PERBAIKAN: Menggunakan where()->first() karena PK bukan 'id' default
        $user = User::where('id_user', $id)->first();
        if (!$user) {
            return response()->json(['message' => 'User tidak ditemukan'], 404);
        }

        // CONSTRAINT / VALIDASI ATURAN BISNIS SAAT UPDATE
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:100',
            'birthday' => 'nullable|date|before:today', // Constraint: Tanggal lahir harus sebelum hari ini
            'gender' => 'nullable|string',
            'country' => 'nullable|string',
            'profile_picture' => 'nullable|string',
            'current_password' => [
                'required_with:password',
                'string',
                function ($attribute, $value, $fail) use ($user) {
                    if ($value && !Hash::check($value, $user->password)) {
                        $fail('Password lama tidak sesuai.');
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
                        $fail('Password baru tidak boleh sama dengan password lama.');
                    }
                }
            ]
        ]);

        // Hapus current_password agar tidak ikut dimasukkan ke update query database
        unset($validated['current_password']);

        $user->update($validated);
        return response()->json(['message' => 'User berhasil diupdate', 'data' => $user], 200);
    }

    public function destroy($id)
    {
        // PERBAIKAN: Menggunakan where()->first() karena PK bukan 'id' default
        $user = User::where('id_user', $id)->first();
        if (!$user) {
            return response()->json(['message' => 'User tidak ditemukan'], 404);
        }

        // ==========================================
        // PROSES CASCADE DELETE (PEMBERSIHAN RELASI)
        // ==========================================

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

        // 4. Handle playlist yang dimiliki user ini
        //    - Jika user adalah Owner → hapus playlist beserta relasinya
        //    - Jika user adalah Editor → cukup hapus dari collaborator
        $collaborations = DB::table('playlist_collaborator')->where('id_user', $id)->get();

        foreach ($collaborations as $collab) {
            if ($collab->role === 'Owner') {
                // User ini adalah Owner playlist → hapus playlist beserta semua relasinya
                $playlist = Playlist::where('id_playlist', $collab->id_playlist)->first();
                if ($playlist) {
                    $playlist->collaborators()->detach();
                    $playlist->songs()->detach();
                    DB::table('user_followplaylist')->where('id_playlist', $collab->id_playlist)->delete();
                    $playlist->delete();
                }
            }
        }

        // Hapus sisa entri collaborator (untuk playlist dimana user hanya Editor)
        DB::table('playlist_collaborator')->where('id_user', $id)->delete();

        // 5. Handle Podcast buatan user ini
        //    Karena di ERD podcast dibuat oleh user (mandatory FK), hapus podcast beserta relasinya
        $podcasts = DB::table('podcast')->where('id_user', $id)->pluck('id_podcast');
        foreach ($podcasts as $podcastId) {
            // Hapus followers podcast
            DB::table('user_followpodcast')->where('id_podcast', $podcastId)->delete();

            // Hapus episode beserta streaming history-nya
            $episodes = DB::table('episode')->where('id_podcast', $podcastId)->pluck('id_episode');
            if ($episodes->isNotEmpty()) {
                DB::table('episode_streaminghistory')->whereIn('id_episode', $episodes)->delete();
                DB::table('episode')->where('id_podcast', $podcastId)->delete();
            }

            // Hapus podcast
            DB::table('podcast')->where('id_podcast', $podcastId)->delete();
        }

        // 6. Hapus user utama
        $user->delete();
        return response()->json(['message' => 'User berhasil dihapus beserta seluruh datanya'], 200);
    }
}
