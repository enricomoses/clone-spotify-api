<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Podcast;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB; // Required for executing manual queries during deletion

class PodcastController extends Controller
{
    public function index()
    {
        // Eager load category and creator (User who created)
        $podcasts = Podcast::with(['category', 'creator'])->get();
        return response()->json(['message' => 'Podcast data', 'data' => $podcasts], 200);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'about' => 'nullable|string',
            'cover_image' => 'nullable|string',
            // Category is optional (nullable)
            'id_podcast_category' => 'nullable|string|exists:podcastcategory,id_podcast_category',
            // Creator/User is required
            'id_user' => 'required|string|exists:User,id_user'
        ]);

        $validated['id_podcast'] = Str::uuid()->toString();
        $podcast = Podcast::create($validated);

        // Load relations so the frontend gets complete data in the response
        $podcast->load(['category', 'creator']);

        return response()->json(['message' => 'Podcast created successfully', 'data' => $podcast], 201);
    }

    public function show($id)
    {
        // Using where()->first() for Intelephense safety
        // Assumption: Your Podcast Model has followers() relationship referencing user_followpodcast
        $podcast = Podcast::with(['episodes', 'category', 'creator', 'followers'])
            ->where('id_podcast', $id)
            ->first();

        if (!$podcast) return response()->json(['message' => 'Podcast not found'], 404);

        return response()->json(['message' => 'Podcast detail', 'data' => $podcast], 200);
    }

    public function update(Request $request, $id)
    {
        $podcast = Podcast::query()->where('id_podcast', $id)->first();
        if (!$podcast) return response()->json(['message' => 'Podcast not found'], 404);

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:150',
            'about' => 'nullable|string',
            'cover_image' => 'nullable|string',
            'id_podcast_category' => 'nullable|string|exists:podcastcategory,id_podcast_category',
            'id_user' => 'sometimes|required|string|exists:User,id_user'
        ]);

        $podcast->update($validated);
        
        // Reload updated relation data if changed
        $podcast->load(['category', 'creator']);

        return response()->json(['message' => 'Podcast updated successfully', 'data' => $podcast], 200);
    }

    public function destroy($id)
    {
        $podcast = Podcast::query()->where('id_podcast', $id)->first();
        
        if (!$podcast) {
            return response()->json(['message' => 'Podcast not found'], 404);
        }

        // ==========================================
        // CASCADE DELETE PROCESS (RELATIONS CLEANUP)
        // ==========================================

        // 1. Detach relations in user_followpodcast pivot table (remove all followers)
        DB::table('user_followpodcast')->where('id_podcast', $id)->delete();

        // 2. Find all episodes belonging to this podcast
        $episodes = DB::table('episode')->where('id_podcast', $id)->pluck('id_episode');
        
        if ($episodes->isNotEmpty()) {
            // 3. Delete streaming history of those episodes
            DB::table('episode_streaminghistory')->whereIn('id_episode', $episodes)->delete();
            
            // 4. Delete the episodes
            DB::table('episode')->where('id_podcast', $id)->delete();
        }

        // 5. After all relations are cleaned, delete the main podcast
        $podcast->delete();

        return response()->json(['message' => 'Podcast, its episodes, and followers deleted successfully'], 200);
    }

    // --- ENDPOINT TO VIEW EPISODES OF A PODCAST ---
    public function getEpisodes($id)
    {
        $podcast = Podcast::with('episodes')->where('id_podcast', $id)->first();
        if (!$podcast) return response()->json(['message' => 'Podcast not found'], 404);

        return response()->json([
            'message' => 'Podcast episodes list',
            'data' => $podcast->episodes
        ], 200);
    }
}