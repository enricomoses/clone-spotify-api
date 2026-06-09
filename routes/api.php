<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Import Semua Controller
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\SocialController;
use App\Http\Controllers\Api\ArtistController;
use App\Http\Controllers\Api\AlbumController;
use App\Http\Controllers\Api\SongController;
use App\Http\Controllers\Api\SongStreamingHistoryController;
use App\Http\Controllers\Api\PlaylistController;
use App\Http\Controllers\Api\PlaylistCategoryController;
use App\Http\Controllers\Api\PodcastController;
use App\Http\Controllers\Api\PodcastCategoryController;
use App\Http\Controllers\Api\EpisodeController;
use App\Http\Controllers\Api\ConcertController;
use App\Http\Controllers\Api\EpisodeStreamingHistoryController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// ==========================================
// 1. AUTHENTICATION
// ==========================================
Route::post('/login', [AuthController::class, 'login']);
Route::post('/login/artist', [AuthController::class, 'loginArtist']);


// ==========================================
// 2. CUSTOM ROUTES (Wajib di atas Resource!)
// ==========================================

// Kategori & Discover
Route::get('/playlist-categories', [PlaylistCategoryController::class, 'index']);
Route::get('/songs/discover-weekly', [SongController::class, 'discoverWeekly']);

// Manajemen Isi Playlist
Route::post('/playlists/{id}/songs', [PlaylistController::class, 'addSong']);
Route::delete('/playlists/{id}/songs/{song_id}', [PlaylistController::class, 'removeSong']);
Route::post('/playlists/{id}/collaborators', [PlaylistController::class, 'addCollaborator']);
Route::delete('/playlists/{id}/collaborators/{user_id}', [PlaylistController::class, 'removeCollaborator']);

// Detail Ekstra Artis (Galeri & Konser)
Route::get('/artists/{id}/concerts', [ArtistController::class, 'getConcerts']);
Route::get('/artists/{id}/gallery', [ArtistController::class, 'getGallery']);
Route::post('/artists/{id}/gallery', [ArtistController::class, 'addGallery']);
Route::delete('/artists/{id}/gallery/{gallery_id}', [ArtistController::class, 'removeGallery']);

// ==========================================
// FITUR SOSIAL USER (Like, Follow, Unfollow)
// ==========================================

// 1. Lagu
Route::post('/users/{id}/like-song', [SocialController::class, 'likeSong']);
Route::delete('/users/{id}/unlike-song/{song_id}', [SocialController::class, 'unlikeSong']);

// 2. User
Route::post('/users/{id}/follow-user', [SocialController::class, 'followUser']);
Route::delete('/users/{id}/unfollow-user/{target_id}', [SocialController::class, 'unfollowUser']);

// 3. Artis
Route::post('/users/{id}/follow-artist', [SocialController::class, 'followArtist']);
Route::delete('/users/{id}/unfollow-artist/{artist_id}', [SocialController::class, 'unfollowArtist']);

// 4. Playlist
Route::post('/users/{id}/follow-playlist', [SocialController::class, 'followPlaylist']);
Route::delete('/users/{id}/unfollow-playlist/{playlist_id}', [SocialController::class, 'unfollowPlaylist']);

// 5. Podcast
Route::post('/users/{id}/follow-podcast', [SocialController::class, 'followPodcast']);
Route::delete('/users/{id}/unfollow-podcast/{podcast_id}', [SocialController::class, 'unfollowPodcast']);

// --- CONCERT CUSTOM ROUTES ---
Route::post('/concerts/{id}/artists', [ConcertController::class, 'addArtist']);
Route::delete('/concerts/{id}/artists/{artist_id}', [ConcertController::class, 'removeArtist']);

Route::post('/artists/{id}/concerts', [ArtistController::class, 'addConcertToArtist']);
Route::delete('/artists/{id}/concerts/{concert_id}', [ArtistController::class, 'removeConcertFromArtist']);

// --- EPISODE CUSTOM ROUTES ---
Route::post('/episodes/{id}/stream', [EpisodeController::class, 'recordStream']);

// ==========================================
// 3. RESOURCE ROUTES (CRUD Standar)
// ==========================================
// (Route ini meng-generate index, store, show, update, destroy secara otomatis)

Route::apiResource('users', UserController::class);
Route::apiResource('artists', ArtistController::class);
Route::apiResource('albums', AlbumController::class);
Route::apiResource('songs', SongController::class);
Route::apiResource('playlists', PlaylistController::class);
Route::apiResource('podcasts', PodcastController::class);
Route::apiResource('episodes', EpisodeController::class);
Route::apiResource('concerts', ConcertController::class);
Route::apiResource('streaming-history', SongStreamingHistoryController::class);
Route::apiResource('podcast-categories', PodcastCategoryController::class);
Route::apiResource('song-streaming', SongStreamingHistoryController::class);
Route::apiResource('episode-streaming', EpisodeStreamingHistoryController::class);