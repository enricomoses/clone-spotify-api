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
        return response()->json(['message' => 'Podcast category data', 'data' => $categories], 200);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'cover_image' => 'required|string',
            'icon' => 'required|string'
        ]);

        $validated['id_podcast_category'] = Str::uuid()->toString();
        $category = PodcastCategory::create($validated);

        return response()->json(['message' => 'Podcast category added successfully', 'data' => $category], 201);
    }

    public function show($id)
    {
        $category = PodcastCategory::query()->with('podcasts')->where('id_podcast_category', $id)->first();
        if (!$category) return response()->json(['message' => 'Podcast category not found'], 404);

        $category->type = 'podcast';
        return response()->json(['message' => 'Podcast category detail', 'data' => $category], 200);
    }

    public function update(Request $request, $id)
    {
        $category = PodcastCategory::query()->where('id_podcast_category', $id)->first();
        if (!$category) return response()->json(['message' => 'Podcast category not found'], 404);

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:100',
            'cover_image' => 'sometimes|required|string',
            'icon' => 'sometimes|required|string'
        ]);

        $category->update($validated);
        return response()->json(['message' => 'Podcast category updated successfully', 'data' => $category], 200);
    }

    public function destroy($id)
    {
        $category = PodcastCategory::query()->where('id_podcast_category', $id)->first();

        if (!$category) {
            {
                return response()->json(['message' => 'Podcast category not found'], 404);
            }
        }

        DB::table('podcasts')
            ->where('id_podcast_category', $id)
            ->update(['id_podcast_category' => null]);

        $category->delete();

        return response()->json(['message' => 'Podcast category deleted successfully and associated podcasts have been updated'], 200);
    }
}
