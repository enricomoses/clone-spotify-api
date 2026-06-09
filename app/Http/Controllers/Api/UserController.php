<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
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

        // Menghapus data transaksi/relasi terlebih dahulu jika diperlukan cascade manual,
        // namun jika di PostgreSQL sudah diset ON DELETE CASCADE pada Foreign Key, ini bisa langsung dihapus.
        $user->delete();
        return response()->json(['message' => 'User berhasil dihapus'], 200);
    }
}
