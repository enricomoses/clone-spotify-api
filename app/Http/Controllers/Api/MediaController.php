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
            $cloudinary->uploadApi()->destroy($publicId);

            return response()->json([
                'message' => 'Image deleted successfully',
                'deleted_public_id' => $publicId
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to delete image: ' . $e->getMessage()], 500);
        }
    }

    public function uploadAudio(Request $request)
    {
        $request->validate([
            'audio' => 'required|file|extensions:mp3,wav,aac,m4a,ogg|max:4096',
            'folder' => 'nullable|string'
        ]);

        try {
            $folder = $request->folder ? 'spotify-clone/audio/' . $request->folder : 'spotify-clone/audio';
            $cloudinary = $this->getCloudinary();

            $response = $cloudinary->uploadApi()->upload($request->file('audio')->getRealPath(), [
                'folder' => $folder,
                'resource_type' => 'video',
                'quality' => 'auto',
                'bitrate' => '64k'
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

    public function uploadVideo(Request $request)
    {
        $request->validate([
            'video' => 'required|file|extensions:mp4,mov,avi,webm,mkv|max:4096',
            'folder' => 'nullable|string'
        ]);

        try {
            $folder = $request->folder ? 'spotify-clone/video/' . $request->folder : 'spotify-clone/video';
            $cloudinary = $this->getCloudinary();

            $response = $cloudinary->uploadApi()->upload($request->file('video')->getRealPath(), [
                'folder' => $folder,
                'resource_type' => 'video',
                'quality' => 'auto:eco',
                'width' => 640,
                'crop' => 'scale',
                'audio_codec' => 'aac'
            ]);

            return response()->json([
                'message' => 'Video uploaded and fully compressed successfully',
                'secure_url' => $response['secure_url'],
                'public_id' => $response['public_id'],
                'duration' => $response['duration'] ?? 0
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

        if (preg_match('/^v\d+\//', $pathAfterUpload)) {
            $pathAfterUpload = preg_replace('/^v\d+\//', '', $pathAfterUpload);
        }

        return substr($pathAfterUpload, 0, strrpos($pathAfterUpload, '.'));
    }
}
