<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    // 1. Konfigurasi Custom Database (Sesuai dengan pgAdmin Anda)
    protected $table = 'User'; // Gunakan huruf kapital jika di pgAdmin namanya "User"
    protected $primaryKey = 'id_user';
    protected $keyType = 'string';
    public $incrementing = false;
    public $timestamps = false; // Karena tabel Anda tidak punya created_at & updated_at

    // 2. Kolom yang boleh diisi (Mass Assignment)
    protected $fillable = [
        'id_user',
        'name',
        'email',
        'password',
        'birthday',
        'gender',
        'country',
        'profile_picture'
    ];

    // 3. Sembunyikan password agar tidak bocor saat data dipanggil via API
    protected $hidden = [
        'password',
    ];

    // 4. Casting tipe data (Bawaan Laravel untuk hashing password)
    protected function casts(): array
    {
        return [
            'password' => 'hashed',
        ];
    }

    // Relasi One-to-Many
    public function podcasts()
    {
        return $this->hasMany(Podcast::class, 'id_user', 'id_user');
    }
    public function songStreamingHistories()
    {
        return $this->hasMany(SongStreamingHistory::class, 'id_user', 'id_user');
    }
    public function episodeStreamingHistories()
    {
        return $this->hasMany(EpisodeStreamingHistory::class, 'id_user', 'id_user');
    }

    // Relasi Many-to-Many (Tabel Penghubung)
    public function likedSongs()
    {
        return $this->belongsToMany(Song::class, 'user_likedsong', 'id_user', 'id_song');
    }
    public function followedArtists()
    {
        return $this->belongsToMany(Artist::class, 'user_followartist', 'id_user', 'id_artist');
    }
    public function followedPlaylists()
    {
        return $this->belongsToMany(Playlist::class, 'user_followplaylist', 'id_user', 'id_playlist');
    }
    public function followedPodcasts()
    {
        return $this->belongsToMany(Podcast::class, 'user_followpodcast', 'id_user', 'id_podcast');
    }
    public function collaborativePlaylists()
    {
        // withPivot digunakan agar kolom 'role' di tabel penghubung bisa terbaca
        return $this->belongsToMany(Playlist::class, 'playlist_collaborator', 'id_user', 'id_playlist')->withPivot('role');
    }

    // Relasi Rekursif (Saling Follow antar User)
    public function followers()
    {
        return $this->belongsToMany(User::class, 'user_followuser', 'followed_id', 'follower_id');
    }
    public function following()
    {
        return $this->belongsToMany(User::class, 'user_followuser', 'follower_id', 'followed_id');
    }
}
