<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Podcast;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB; // Wajib ditambahkan untuk eksekusi query manual saat hapus data

class PodcastController extends Controller
{
    public function index()
    {
        // Eager load category dan creator (User yang membuat)
        $podcasts = Podcast::with(['category', 'creator'])->get();
        return response()->json(['message' => 'Data podcast', 'data' => $podcasts], 200);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'about' => 'nullable|string',
            'cover_image' => 'nullable|string',
            // Kategori tidak wajib (nullable)
            'id_podcast_category' => 'nullable|string|exists:podcastcategory,id_podcast_category',
            // Kreator/User bersifat wajib (required)
            'id_user' => 'required|string|exists:User,id_user'
        ]);

        $validated['id_podcast'] = Str::uuid()->toString();
        $podcast = Podcast::create($validated);

        // Load relasi agar frontend langsung mendapat data yang lengkap saat response
        $podcast->load(['category', 'creator']);

        return response()->json(['message' => 'Podcast berhasil dibuat', 'data' => $podcast], 201);
    }

    public function show($id)
    {
        // Menggunakan where()->first() untuk keamanan Intelephense
        // Asumsi: Model Podcast Anda sudah memiliki fungsi relasi followers() yang merujuk ke user_followpodcast
        $podcast = Podcast::with(['episodes', 'category', 'creator', 'followers'])
            ->where('id_podcast', $id)
            ->first();

        if (!$podcast) return response()->json(['message' => 'Podcast tidak ditemukan'], 404);

        return response()->json(['message' => 'Detail podcast', 'data' => $podcast], 200);
    }

    public function update(Request $request, $id)
    {
        $podcast = Podcast::query()->where('id_podcast', $id)->first();
        if (!$podcast) return response()->json(['message' => 'Podcast tidak ditemukan'], 404);

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:150',
            'about' => 'nullable|string',
            'cover_image' => 'nullable|string',
            'id_podcast_category' => 'nullable|string|exists:podcastcategory,id_podcast_category',
            'id_user' => 'sometimes|required|string|exists:User,id_user'
        ]);

        $podcast->update($validated);
        
        // Memuat ulang data relasi terbaru jika ada perubahan
        $podcast->load(['category', 'creator']);

        return response()->json(['message' => 'Podcast berhasil diupdate', 'data' => $podcast], 200);
    }

    public function destroy($id)
    {
        $podcast = Podcast::query()->where('id_podcast', $id)->first();
        
        if (!$podcast) {
            return response()->json(['message' => 'Podcast tidak ditemukan'], 404);
        }

        // ==========================================
        // PROSES CASCADE DELETE (PEMBERSIHAN RELASI)
        // ==========================================

        // 1. Putus relasi di tabel pivot user_followpodcast (hapus semua followers)
        DB::table('user_followpodcast')->where('id_podcast', $id)->delete();

        // 2. Cari semua episode yang dimiliki oleh podcast ini
        $episodes = DB::table('episode')->where('id_podcast', $id)->pluck('id_episode');
        
        if ($episodes->isNotEmpty()) {
            // 3. Hapus riwayat streaming dari episode-episode tersebut
            DB::table('episode_streaminghistory')->whereIn('id_episode', $episodes)->delete();
            
            // 4. Hapus episode-episodenya
            DB::table('episode')->where('id_podcast', $id)->delete();
        }

        // 5. Setelah semua relasi bersih, hapus podcast utama
        $podcast->delete();

        return response()->json(['message' => 'Podcast beserta episode dan followers berhasil dihapus'], 200);
    }
}