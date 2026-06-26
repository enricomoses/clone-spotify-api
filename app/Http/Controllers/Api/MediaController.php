<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Cloudinary\Cloudinary;

class MediaController extends Controller
{
    /**
     * Initialize Cloudinary Instance
     */
    private function getCloudinary()
    {
        return new Cloudinary(env('CLOUDINARY_URL'));
    }

    // ==========================================
    // PART 1: IMAGE MANAGEMENT
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
                'message' => 'Image uploaded successfully',
                'secure_url' => $response['secure_url'],
                'public_id' => $response['public_id']
            ], 201);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to upload image: ' . $e->getMessage()], 500);
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
            // Default resource_type for destroy is 'image'
            $cloudinary->uploadApi()->destroy($publicId);

            return response()->json([
                'message' => 'Image deleted successfully',
                'deleted_public_id' => $publicId
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to delete image: ' . $e->getMessage()], 500);
        }
    }

    // ==========================================
    // PART 2: AUDIO MANAGEMENT (SONG/EPISODE)
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

            // Upload with quality and bitrate optimization parameters
            $response = $cloudinary->uploadApi()->upload($request->file('audio')->getRealPath(), [
                'folder' => $folder,
                'resource_type' => 'video', // REQUIRED to be 'video' for audio files on Cloudinary
                'quality' => 'auto',
                'bitrate' => '64k'          // Save bandwidth
            ]);

            return response()->json([
                'message' => 'Audio uploaded and optimized successfully',
                'secure_url' => $response['secure_url'],
                'public_id' => $response['public_id'],
                'duration' => $response['duration'] ?? 0 // Cloudinary automatically calculates audio duration (in seconds)
            ], 201);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to upload audio: ' . $e->getMessage()], 500);
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

            // IMPORTANT: Required to include resource_type => 'video' when deleting audio
            $cloudinary->uploadApi()->destroy($publicId, [
                'resource_type' => 'video'
            ]);

            return response()->json([
                'message' => 'Audio deleted successfully from storage',
                'deleted_public_id' => $publicId
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to delete audio: ' . $e->getMessage()], 500);
        }
    }

    // ==========================================
    // PART 3: VIDEO MANAGEMENT (PODCAST)
    // ==========================================

    public function uploadVideo(Request $request)
    {
        // Validate common video extensions, limit stays at 4MB to adjust to Vercel
        $request->validate([
            'video' => 'required|file|extensions:mp4,mov,avi,webm,mkv|max:4096',
            'folder' => 'nullable|string'
        ]);

        try {
            $folder = $request->folder ? 'spotify-clone/video/' . $request->folder : 'spotify-clone/video';
            $cloudinary = $this->getCloudinary();

            // Upload with high-level video compression parameters (Extreme Optimization)
            $response = $cloudinary->uploadApi()->upload($request->file('video')->getRealPath(), [
                'folder' => $folder,
                'resource_type' => 'video', // Cloudinary uses 'video' for both audio and video
                'quality' => 'auto:eco',    // Most aggressive/eco quality compression
                'width' => 640,             // Scale down max resolution to 640px width (480p)
                'crop' => 'scale',
                'audio_codec' => 'aac'      // Standardize audio codec to keep it lightweight
            ]);

            return response()->json([
                'message' => 'Video uploaded and fully compressed successfully',
                'secure_url' => $response['secure_url'],
                'public_id' => $response['public_id'],
                'duration' => $response['duration'] ?? 0 // Video duration in seconds
            ], 201);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to upload video: ' . $e->getMessage()], 500);
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

            // Video deletion logic is exactly the same as audio (resource_type => 'video')
            $cloudinary->uploadApi()->destroy($publicId, [
                'resource_type' => 'video'
            ]);

            return response()->json([
                'message' => 'Podcast video deleted successfully from storage',
                'deleted_public_id' => $publicId
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to delete video: ' . $e->getMessage()], 500);
        }
    }

    // ==========================================
    // HELPER FUNCTIONS
    // ==========================================

    /**
     * Extracts pure Public ID from Cloudinary URL
     */
    private function extractPublicId($url)
    {
        $pathParts = explode('/upload/', $url);
        if (count($pathParts) < 2) {
            throw new \Exception('Invalid URL for Cloudinary');
        }

        $pathAfterUpload = $pathParts[1];

        // Remove URL version (e.g.: v1234567890/)
        if (preg_match('/^v\d+\//', $pathAfterUpload)) {
            $pathAfterUpload = preg_replace('/^v\d+\//', '', $pathAfterUpload);
        }

        // Remove file extension
        return substr($pathAfterUpload, 0, strrpos($pathAfterUpload, '.'));
    }
}
