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
        // Mengambil konfigurasi langsung dari .env
        return new Cloudinary(env('CLOUDINARY_URL'));
    }

    /**
     * UPLOAD GAMBAR KE CLOUDINARY
     */
    public function uploadImage(Request $request)
    {
        // Validasi file yang diupload (maksimal 5MB)
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            'folder' => 'nullable|string'
        ]);

        try {
            $folder = $request->folder ? 'spotify-clone/' . $request->folder : 'spotify-clone';

            // Menggunakan UploadApi dari SDK PHP murni
            $cloudinary = $this->getCloudinary();
            $response = $cloudinary->uploadApi()->upload($request->file('image')->getRealPath(), [
                'folder' => $folder
            ]);

            // Response dari SDK berbentuk array/ArrayAccess
            return response()->json([
                'message' => 'Gambar berhasil diunggah',
                'secure_url' => $response['secure_url'],
                'public_id' => $response['public_id']
            ], 201);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Gagal mengunggah gambar: ' . $e->getMessage()], 500);
        }
    }

    /**
     * HAPUS GAMBAR DARI CLOUDINARY
     */
    public function deleteImage(Request $request)
    {
        $request->validate([
            'image_url' => 'required|url'
        ]);

        try {
            $url = $request->image_url;

            // Logika Ekstraksi Public ID dari URL Cloudinary
            $pathParts = explode('/upload/', $url);

            if (count($pathParts) < 2) {
                return response()->json(['message' => 'URL gambar tidak valid untuk Cloudinary'], 400);
            }

            $pathAfterUpload = $pathParts[1];

            // Hapus versi URL (contoh: v1234567890/) jika ada
            if (preg_match('/^v\d+\//', $pathAfterUpload)) {
                $pathAfterUpload = preg_replace('/^v\d+\//', '', $pathAfterUpload);
            }

            // Hapus ekstensi file (.jpg, .png) untuk mendapatkan public_id murni
            $publicId = substr($pathAfterUpload, 0, strrpos($pathAfterUpload, '.'));

            // Eksekusi penghapusan menggunakan UploadApi
            $cloudinary = $this->getCloudinary();
            $cloudinary->uploadApi()->destroy($publicId);

            return response()->json([
                'message' => 'Gambar berhasil dihapus dari penyimpanan',
                'deleted_public_id' => $publicId
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Gagal menghapus gambar: ' . $e->getMessage()], 500);
        }
    }
}
