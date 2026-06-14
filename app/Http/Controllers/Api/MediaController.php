<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Cloudinary\Cloudinary;

class MediaController extends Controller
{
    /**
     * Inisialisasi Instance Cloudinary
     */
    private function getCloudinary()
    {
        return new Cloudinary(env('CLOUDINARY_URL'));
    }

    // ==========================================
    // BAGIAN 1: MANAJEMEN GAMBAR (IMAGE)
    // ==========================================

    public function uploadImage(Request $request)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            'folder' => 'nullable|string'
        ]);

        try {
            $folder = $request->folder ? 'spotify-clone/' . $request->folder : 'spotify-clone';
            $cloudinary = $this->getCloudinary();
            $response = $cloudinary->uploadApi()->upload($request->file('image')->getRealPath(), [
                'folder' => $folder,
                'resource_type' => 'image'
            ]);

            return response()->json([
                'message' => 'Gambar berhasil diunggah',
                'secure_url' => $response['secure_url'],
                'public_id' => $response['public_id']
            ], 201);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Gagal mengunggah gambar: ' . $e->getMessage()], 500);
        }
    }

    public function deleteImage(Request $request)
    {
        $request->validate([
            'image_url' => 'required|url'
        ]);

        try {
            $publicId = $this->extractPublicId($request->image_url);

            $cloudinary = $this->getCloudinary();
            // Default resource_type untuk destroy adalah 'image'
            $cloudinary->uploadApi()->destroy($publicId);

            return response()->json([
                'message' => 'Gambar berhasil dihapus',
                'deleted_public_id' => $publicId
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Gagal menghapus gambar: ' . $e->getMessage()], 500);
        }
    }

    // ==========================================
    // BAGIAN 2: MANAJEMEN AUDIO (SONG/EPISODE)
    // ==========================================

    public function uploadAudio(Request $request)
    {
        $request->validate([
            'audio' => 'required|file|extensions:mp3,wav,aac,m4a,ogg|max:4096',
            'folder' => 'nullable|string'
        ]);

        try {
            $folder = $request->folder ? 'spotify-clone/audio/' . $request->folder : 'spotify-clone/audio';
            $cloudinary = $this->getCloudinary();

            // Upload dengan parameter optimasi kualitas dan bitrate
            $response = $cloudinary->uploadApi()->upload($request->file('audio')->getRealPath(), [
                'folder' => $folder,
                'resource_type' => 'video', // WAJIB 'video' untuk file audio di Cloudinary
                'quality' => 'auto',
                'bitrate' => '64k'          // Hemat bandwidth
            ]);

            return response()->json([
                'message' => 'Audio berhasil diunggah dan dioptimasi',
                'secure_url' => $response['secure_url'],
                'public_id' => $response['public_id'],
                'duration' => $response['duration'] ?? 0 // Cloudinary otomatis menghitung durasi audio (dalam detik)
            ], 201);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Gagal mengunggah audio: ' . $e->getMessage()], 500);
        }
    }

    public function deleteAudio(Request $request)
    {
        $request->validate([
            'audio_url' => 'required|url'
        ]);

        try {
            $publicId = $this->extractPublicId($request->audio_url);

            $cloudinary = $this->getCloudinary();

            // PENTING: Wajib menyertakan resource_type => 'video' saat menghapus audio
            $cloudinary->uploadApi()->destroy($publicId, [
                'resource_type' => 'video'
            ]);

            return response()->json([
                'message' => 'Audio berhasil dihapus dari penyimpanan',
                'deleted_public_id' => $publicId
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Gagal menghapus audio: ' . $e->getMessage()], 500);
        }
    }

    // ==========================================
    // BAGIAN 3: MANAJEMEN VIDEO (PODCAST)
    // ==========================================

    public function uploadVideo(Request $request)
    {
        // Validasi ekstensi video umum, batas tetap 4MB menyesuaikan Vercel
        $request->validate([
            'video' => 'required|file|extensions:mp4,mov,avi,webm,mkv|max:4096',
            'folder' => 'nullable|string'
        ]);

        try {
            $folder = $request->folder ? 'spotify-clone/video/' . $request->folder : 'spotify-clone/video';
            $cloudinary = $this->getCloudinary();

            // Upload dengan parameter kompresi video tingkat tinggi (Extreme Optimization)
            $response = $cloudinary->uploadApi()->upload($request->file('video')->getRealPath(), [
                'folder' => $folder,
                'resource_type' => 'video', // Cloudinary menggunakan 'video' untuk audio maupun video
                'quality' => 'auto:eco',    // Kompresi kualitas paling agresif/hemat
                'width' => 640,             // Scale down/turunkan resolusi maksimal ke lebar 640px (480p)
                'crop' => 'scale',
                'audio_codec' => 'aac'      // Standarisasi codec audio agar ringan
            ]);

            return response()->json([
                'message' => 'Video berhasil diunggah dan dikompresi maksimal',
                'secure_url' => $response['secure_url'],
                'public_id' => $response['public_id'],
                'duration' => $response['duration'] ?? 0 // Durasi video dalam detik
            ], 201);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Gagal mengunggah video: ' . $e->getMessage()], 500);
        }
    }

    public function deleteVideo(Request $request)
    {
        $request->validate([
            'video_url' => 'required|url'
        ]);

        try {
            $publicId = $this->extractPublicId($request->video_url);

            $cloudinary = $this->getCloudinary();

            // Logika penghapusan video sama persis dengan audio (resource_type => 'video')
            $cloudinary->uploadApi()->destroy($publicId, [
                'resource_type' => 'video'
            ]);

            return response()->json([
                'message' => 'Video podcast berhasil dihapus dari penyimpanan',
                'deleted_public_id' => $publicId
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Gagal menghapus video: ' . $e->getMessage()], 500);
        }
    }

    // ==========================================
    // FUNGSI HELPER
    // ==========================================

    /**
     * Mengekstrak Public ID murni dari URL Cloudinary
     */
    private function extractPublicId($url)
    {
        $pathParts = explode('/upload/', $url);
        if (count($pathParts) < 2) {
            throw new \Exception('URL tidak valid untuk Cloudinary');
        }

        $pathAfterUpload = $pathParts[1];

        // Hapus versi URL (contoh: v1234567890/)
        if (preg_match('/^v\d+\//', $pathAfterUpload)) {
            $pathAfterUpload = preg_replace('/^v\d+\//', '', $pathAfterUpload);
        }

        // Hapus ekstensi file
        return substr($pathAfterUpload, 0, strrpos($pathAfterUpload, '.'));
    }
}
