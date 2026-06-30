<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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
use App\Http\Controllers\Api\MediaController;
use App\Http\Controllers\Api\HomeController;
use App\Http\Controllers\Api\SearchController;

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
// 2. HOME PAGE — Optimized Endpoints
// ==========================================
Route::get('/home/popular-artists', [HomeController::class, 'popularArtists']);
Route::get('/home/popular-albums', [HomeController::class, 'popularAlbums']);
Route::get('/home/popular-podcasts', [HomeController::class, 'popularPodcasts']);
Route::get('/home/featured-playlists', [HomeController::class, 'featuredPlaylists']);
Route::get('/home/user-playlists', [HomeController::class, 'userPlaylists']);

// ==========================================
// 3. GLOBAL SEARCH — Server-Side
// ==========================================
Route::get('/search', [SearchController::class, 'search']);

// ==========================================
// 4. CUSTOM ROUTES
// ==========================================

// Kategori & Discover
Route::get('/songs/discover-weekly', [SongController::class, 'discoverWeekly']);
Route::get('/songs/search', [SongController::class, 'search']);

// Manajemen Isi Playlist (dengan validasi role)
Route::post('/playlists/{id}/songs', [PlaylistController::class, 'addSong']);
Route::delete('/playlists/{id}/songs/{song_id}', [PlaylistController::class, 'removeSong']);
Route::post('/playlists/{id}/collaborators', [PlaylistController::class, 'addCollaborator']);
Route::delete('/playlists/{id}/collaborators/{user_id}', [PlaylistController::class, 'removeCollaborator']);

// --- ARTIS: Galeri ---
Route::get('/artists/{id}/gallery', [ArtistController::class, 'getGallery']);
Route::post('/artists/{id}/gallery', [ArtistController::class, 'addGallery']);
Route::delete('/artists/{id}/gallery/{gallery_id}', [ArtistController::class, 'removeGallery']);

// --- ARTIS: Konser ---
Route::get('/artists/{id}/concerts', [ArtistController::class, 'getConcerts']);
Route::post('/artists/{id}/concerts', [ArtistController::class, 'addConcertToArtist']);
Route::delete('/artists/{id}/concerts/{concert_id}', [ArtistController::class, 'removeConcertFromArtist']);

// --- ARTIS: Album ---
Route::get('/artists/{id}/albums', [ArtistController::class, 'getAlbums']);
Route::post('/artists/{id}/albums', [ArtistController::class, 'addAlbumToArtist']);
Route::delete('/artists/{id}/albums/{album_id}', [ArtistController::class, 'removeAlbumFromArtist']);

// --- ARTIS: Song ---
Route::get('/artists/{id}/songs', [ArtistController::class, 'getSongs']);
Route::post('/artists/{id}/songs', [ArtistController::class, 'addSongToArtist']);
Route::delete('/artists/{id}/songs/{song_id}', [ArtistController::class, 'removeSongFromArtist']);

// --- ALBUM: Manajemen Artis ---
Route::post('/albums/{id}/artists', [AlbumController::class, 'addArtist']);
Route::delete('/albums/{id}/artists/{artist_id}', [AlbumController::class, 'removeArtist']);

// --- CONCERT: Manajemen Artis ---
Route::post('/concerts/{id}/artists', [ConcertController::class, 'addArtist']);
Route::delete('/concerts/{id}/artists/{artist_id}', [ConcertController::class, 'removeArtist']);

// --- PODCAST: Episode ---
Route::get('/podcasts/{id}/episodes', [PodcastController::class, 'getEpisodes']);

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

// ==========================================
// USER-SPECIFIC DATA ENDPOINTS
// ==========================================
Route::get('/users/{id}/streaming-history/songs', [SongStreamingHistoryController::class, 'getUserHistory']);
Route::get('/users/{id}/streaming-history/episodes', [EpisodeStreamingHistoryController::class, 'getUserHistory']);
Route::get('/users/{id}/liked-songs', [UserController::class, 'getLikedSongs']);
Route::get('/users/{id}/recent-songs', [UserController::class, 'getRecentSongs']);
Route::get('/users/{id}/playlists', [UserController::class, 'getUserPlaylists']);
Route::get('/users/{id}/podcasts', [UserController::class, 'getUserPodcasts']);

// ==========================================
// MEDIA STORAGE (CLOUDINARY)
// ==========================================
Route::post('/media/upload', [MediaController::class, 'uploadImage']);
Route::delete('/media/delete', [MediaController::class, 'deleteImage']);

Route::post('/media/audio/upload', [MediaController::class, 'uploadAudio']);
Route::delete('/media/audio/delete', [MediaController::class, 'deleteAudio']);

Route::post('/media/video/upload', [MediaController::class, 'uploadVideo']);
Route::delete('/media/video/delete', [MediaController::class, 'deleteVideo']);

Route::get('/artists/{id}/song-streaming', [SongStreamingHistoryController::class, 'getArtistHistory']);


// ==========================================
// USER PROFILE & SEARCH
// ==========================================
Route::get('/users/search', [UserController::class, 'search']);
Route::get('/users/{id}/profile', [UserController::class, 'getUserProfile']);

// ==========================================
// 5. RESOURCE ROUTES (CRUD Standar)
// ==========================================
Route::apiResource('users', UserController::class);
Route::apiResource('artists', ArtistController::class);
Route::apiResource('albums', AlbumController::class);
Route::apiResource('songs', SongController::class);
Route::apiResource('playlists', PlaylistController::class);
Route::apiResource('podcasts', PodcastController::class);
Route::apiResource('episodes', EpisodeController::class);
Route::apiResource('concerts', ConcertController::class);
Route::apiResource('playlist-categories', PlaylistCategoryController::class);
Route::apiResource('podcast-categories', PodcastCategoryController::class);
Route::apiResource('song-streaming', SongStreamingHistoryController::class);
Route::apiResource('episode-streaming', EpisodeStreamingHistoryController::class);