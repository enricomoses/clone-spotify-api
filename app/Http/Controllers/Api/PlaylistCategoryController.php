<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PlaylistCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PlaylistCategoryController extends Controller
{
    public function index()
    {
        $categories = PlaylistCategory::all();
        return response()->json(['message' => 'Data kategori playlist', 'data' => $categories], 200);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'cover_image' => 'nullable|string',
            'icon' => 'nullable|string'
        ]);

        $validated['id_playlist_category'] = Str::uuid()->toString();
        $category = PlaylistCategory::create($validated);

        return response()->json(['message' => 'Kategori berhasil ditambahkan', 'data' => $category], 201);
    }

    public function show($id)
    {
        $category = PlaylistCategory::with('playlists')->find($id);
        if (!$category) return response()->json(['message' => 'Kategori tidak ditemukan'], 404);
        return response()->json(['message' => 'Detail kategori', 'data' => $category], 200);
    }

    public function update(Request $request, $id)
    {
        $category = PlaylistCategory::query()->find($id);
        if (!$category) return response()->json(['message' => 'Kategori tidak ditemukan'], 404);

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:100',
            'cover_image' => 'nullable|string',
            'icon' => 'nullable|string'
        ]);

        $category->update($validated);
        return response()->json(['message' => 'Kategori berhasil diupdate', 'data' => $category], 200);
    }

    public function destroy($id)
    {
        $category = PlaylistCategory::query()->where('id_playlist_category', $id)->first();

        if (!$category) {
            return response()->json(['message' => 'Kategori tidak ditemukan'], 404);
        }

        // ==========================================
        // PROSES PEMBERSIHAN RELASI
        // ==========================================
        // Ubah semua playlist yang memakai kategori ini menjadi 'Tanpa Kategori' (null)
        // Mencegah error Foreign Key Constraint dan mencegah playlist ikut terhapus
        DB::table('playlist')
            ->where('id_playlist_category', $id)
            ->update(['id_playlist_category' => null]);

        // Setelah relasinya dibersihkan, hapus kategori utama dengan aman
        $category->delete();

        return response()->json(['message' => 'Kategori berhasil dihapus dan playlist terkait telah disesuaikan'], 200);
    }
}
