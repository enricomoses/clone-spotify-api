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

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// ==========================================
// 1. AUTHENTICATION
// ==========================================
Route::post('/login', [AuthController::class, 'login']);


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

// Fitur Sosial User (Like, Follow)
Route::post('/users/{id}/like-song', [SocialController::class, 'likeSong']);
Route::delete('/users/{id}/unlike-song/{song_id}', [SocialController::class, 'unlikeSong']);

Route::post('/users/{id}/follow-user', [SocialController::class, 'followUser']);
Route::delete('/users/{id}/unfollow-user/{target_id}', [SocialController::class, 'unfollowUser']);

Route::post('/users/{id}/follow-artist', [SocialController::class, 'followArtist']);
Route::delete('/users/{id}/unfollow-artist/{artist_id}', [SocialController::class, 'unfollowArtist']);

Route::post('/users/{id}/follow-playlist', [SocialController::class, 'followPlaylist']);
Route::post('/users/{id}/follow-podcast', [SocialController::class, 'followPodcast']);


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