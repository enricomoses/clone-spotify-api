<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Song extends Model
{
    use HasFactory;

    protected $table = 'song';
    protected $primaryKey = 'id_song';
    protected $keyType = 'string';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'id_song',
        'title',
        'duration',
        'id_album'
    ];

    public function album()
    {
        return $this->belongsTo(Album::class, 'id_album', 'id_album');
    }
    public function artists()
    {
        return $this->belongsToMany(Artist::class, 'song_artist', 'id_song', 'id_artist');
    }
    public function playlists()
    {
        return $this->belongsToMany(Playlist::class, 'playlist_song', 'id_song', 'id_playlist');
    }
    public function likedByUsers()
    {
        return $this->belongsToMany(User::class, 'user_likedsong', 'id_song', 'id_user');
    }
    public function streamingHistories()
    {
        return $this->hasMany(SongStreamingHistory::class, 'id_song', 'id_song');
    }
}
