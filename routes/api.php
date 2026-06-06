<?php

use App\Http\Controllers\Api\AlbumController;
use App\Http\Controllers\Api\ArtistController;
use App\Http\Controllers\Api\EpisodeController;
use App\Http\Controllers\Api\PlaylistController;
use App\Http\Controllers\Api\PodcastController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\SongController;
use App\Http\Controllers\Api\SongStreamingHistoryController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\SocialController;

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');
Route::apiResource('songs', SongController::class);
Route::get('/songs/discover-weekly', [SongController::class, 'discoverWeekly']);
Route::apiResource('streaming-history', SongStreamingHistoryController::class);

Route::apiResource('users', UserController::class);
Route::apiResource('artists', ArtistController::class);
Route::apiResource('albums', AlbumController::class);
Route::apiResource('playlists', PlaylistController::class);
Route::apiResource('podcasts', PodcastController::class);
Route::apiResource('episodes', EpisodeController::class);

Route::post('/login', [AuthController::class, 'login']);

Route::post('/social/like-song', [SocialController::class, 'toggleLikeSong']);
Route::post('/social/follow-user', [SocialController::class, 'toggleFollowUser']);