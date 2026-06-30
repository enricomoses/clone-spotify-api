<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Artist;
use App\Models\Song;
use App\Models\SongStreamingHistory;
use Illuminate\Support\Facades\DB;

class ArtistAnalyticsController extends Controller
{
    /**
     * Dashboard stats for an artist.
     * GET /artists/{id}/dashboard
     *
     * Returns: total_songs, total_albums, total_streams, weekly_streams,
     *          top_5_songs, stream_distribution
     */
    public function dashboard($id)
    {
        $artist = Artist::where('id_artist', $id)->first();
        if (!$artist) {
            return response()->json(['message' => 'Artist not found'], 404);
        }

        // Get artist's song IDs
        $songIds = $artist->songs()->pluck('songs.id_song')->toArray();

        // Total songs
        $totalSongs = count($songIds);

        // Total albums
        $totalAlbums = $artist->albums()->count();

        // All streaming history for artist's songs
        $allHistory = SongStreamingHistory::whereIn('id_song', $songIds)->get();
        $totalStreams = $allHistory->count();

        // Weekly streams (last 7 days)
        $sevenDaysAgo = now()->subDays(7);
        $weeklyStreams = $allHistory->filter(function ($h) use ($sevenDaysAgo) {
            return $h->played_at >= $sevenDaysAgo;
        })->count();

        // Count streams per song
        $streamCounts = $allHistory->groupBy('id_song')->map->count();

        // Get full song details for songs that have streams
        $songsWithDetails = Song::with(['album', 'artists'])
            ->whereIn('id_song', $songIds)
            ->get()
            ->keyBy('id_song');

        // Top 5 songs
        $top5Songs = $streamCounts
            ->sortDesc()
            ->take(5)
            ->map(function ($count, $songId) use ($songsWithDetails) {
                return [
                    'song' => $songsWithDetails->get($songId),
                    'count' => $count,
                ];
            })
            ->values()
            ->toArray();

        // Stream distribution (all songs sorted by count)
        $streamDistribution = $streamCounts
            ->sortDesc()
            ->map(function ($count, $songId) use ($songsWithDetails) {
                $song = $songsWithDetails->get($songId);
                return [
                    'id' => $songId,
                    'title' => $song ? $song->title : 'Unknown',
                    'count' => $count,
                ];
            })
            ->values()
            ->toArray();

        return response()->json([
            'message' => 'Artist dashboard data',
            'data' => [
                'total_songs' => $totalSongs,
                'total_albums' => $totalAlbums,
                'total_streams' => $totalStreams,
                'weekly_streams' => $weeklyStreams,
                'top_5_songs' => $top5Songs,
                'stream_distribution' => $streamDistribution,
            ]
        ], 200);
    }

    /**
     * Detailed analytics for an artist.
     * GET /artists/{id}/analytics
     *
     * Returns: total_streams, weekly_streams, today_streams,
     *          top_songs_by_stream, detailed_breakdown
     */
    public function analytics($id)
    {
        $artist = Artist::where('id_artist', $id)->first();
        if (!$artist) {
            return response()->json(['message' => 'Artist not found'], 404);
        }

        // Get artist's song IDs
        $songIds = $artist->songs()->pluck('songs.id_song')->toArray();

        // All streaming history for artist's songs
        $allHistory = SongStreamingHistory::whereIn('id_song', $songIds)->get();
        $totalStreams = $allHistory->count();

        // Time boundaries
        $sevenDaysAgo = now()->subDays(7);
        $todayStart = now()->startOfDay();

        // Weekly & today streams
        $weeklyStreams = $allHistory->filter(fn($h) => $h->played_at >= $sevenDaysAgo)->count();
        $todayStreams = $allHistory->filter(fn($h) => $h->played_at >= $todayStart)->count();

        // Per-song counts
        $countsAll = [];
        $countsWeek = [];
        $countsToday = [];

        foreach ($allHistory as $h) {
            $songId = $h->id_song;
            $playedAt = $h->played_at;

            $countsAll[$songId] = ($countsAll[$songId] ?? 0) + 1;

            if ($playedAt >= $sevenDaysAgo) {
                $countsWeek[$songId] = ($countsWeek[$songId] ?? 0) + 1;
            }
            if ($playedAt >= $todayStart) {
                $countsToday[$songId] = ($countsToday[$songId] ?? 0) + 1;
            }
        }

        // Get full song details
        $songsWithDetails = Song::with(['album', 'artists'])
            ->whereIn('id_song', $songIds)
            ->get()
            ->keyBy('id_song');

        // Top songs by stream (for chart)
        arsort($countsAll);
        $topSongsByStream = [];
        foreach ($countsAll as $songId => $count) {
            $song = $songsWithDetails->get($songId);
            $topSongsByStream[] = [
                'id' => $songId,
                'title' => $song ? $song->title : 'Unknown',
                'count' => $count,
            ];
        }

        // Detailed breakdown — all artist songs with total/weekly/today
        $detailedBreakdown = [];
        foreach ($songsWithDetails as $songId => $song) {
            $detailedBreakdown[] = [
                'song' => $song,
                'total' => $countsAll[$songId] ?? 0,
                'weekly' => $countsWeek[$songId] ?? 0,
                'today' => $countsToday[$songId] ?? 0,
            ];
        }

        // Sort breakdown by total descending
        usort($detailedBreakdown, fn($a, $b) => $b['total'] - $a['total']);

        return response()->json([
            'message' => 'Artist analytics data',
            'data' => [
                'total_streams' => $totalStreams,
                'weekly_streams' => $weeklyStreams,
                'today_streams' => $todayStreams,
                'top_songs_by_stream' => $topSongsByStream,
                'detailed_breakdown' => $detailedBreakdown,
            ]
        ], 200);
    }
}
