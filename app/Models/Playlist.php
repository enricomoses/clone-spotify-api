<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Playlist extends Model
{
    use HasFactory;

    protected $table = 'playlists';
    protected $primaryKey = 'id_playlist';
    protected $keyType = 'string';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'id_playlist',
        'name',
        'description',
        'cover_image',
        'id_playlist_category'
    ];

    public function category()
    {
        return $this->belongsTo(PlaylistCategory::class, 'id_playlist_category', 'id_playlist_category');
    }
    public function songs()
    {
        return $this->belongsToMany(Song::class, 'playlist_song', 'id_playlist', 'id_song');
    }
    public function collaborators()
    {
        return $this->belongsToMany(User::class, 'playlist_collaborator', 'id_playlist', 'id_user')->withPivot('role');
    }
    public function followers()
    {
        return $this->belongsToMany(User::class, 'user_followplaylist', 'id_playlist', 'id_user');
    }
}
