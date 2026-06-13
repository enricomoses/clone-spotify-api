<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PodcastCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PodcastCategoryController extends Controller
{
    public function index()
    {
        $categories = PodcastCategory::all()->map(function ($category) {
            $category->type = 'podcast';
            return $category;
        });
        return response()->json(['message' => 'Data kategori podcast', 'data' => $categories], 200);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'cover_image' => 'required|string',  // ERD: mandatory (*)
            'icon' => 'required|string'           // ERD: mandatory (*)
        ]);

        $validated['id_podcast_category'] = Str::uuid()->toString();
        $category = PodcastCategory::create($validated);

        return response()->json(['message' => 'Kategori podcast berhasil ditambahkan', 'data' => $category], 201);
    }

    public function show($id)
    {
        $category = PodcastCategory::query()->with('podcasts')->where('id_podcast_category', $id)->first();
        if (!$category) return response()->json(['message' => 'Kategori podcast tidak ditemukan'], 404);

        $category->type = 'podcast';
        return response()->json(['message' => 'Detail kategori podcast', 'data' => $category], 200);
    }

    public function update(Request $request, $id)
    {
        $category = PodcastCategory::query()->where('id_podcast_category', $id)->first();
        if (!$category) return response()->json(['message' => 'Kategori podcast tidak ditemukan'], 404);

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:100',
            'cover_image' => 'sometimes|required|string',
            'icon' => 'sometimes|required|string'
        ]);

        $category->update($validated);
        return response()->json(['message' => 'Kategori podcast berhasil diupdate', 'data' => $category], 200);
    }

    public function destroy($id)
    {
        $category = PodcastCategory::query()->where('id_podcast_category', $id)->first();

        if (!$category) {
            return response()->json(['message' => 'Kategori podcast tidak ditemukan'], 404);
        }

        // ==========================================
        // PROSES PEMBERSIHAN RELASI
        // ==========================================
        // Ubah semua podcast yang memakai kategori ini menjadi 'Tanpa Kategori' (null)
        // Mencegah error Foreign Key Constraint dan mencegah podcast ikut terhapus
        DB::table('podcast')
            ->where('id_podcast_category', $id)
            ->update(['id_podcast_category' => null]);

        // Setelah relasinya dibersihkan, hapus kategori utama dengan aman
        $category->delete();

        return response()->json(['message' => 'Kategori podcast berhasil dihapus dan podcast terkait telah disesuaikan'], 200);
    }
}
