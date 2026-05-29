<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlaylistCollaborator extends Model
{
    use HasFactory;

    protected $table = 'playlist_collaborator';
    protected $primaryKey = null;
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'id_playlist',
        'id_user',
        'role'
    ];

    // Relasi balik ke Playlist
    public function playlist()
    {
        return $this->belongsTo(Playlist::class, 'id_playlist', 'id_playlist');
    }

    // Relasi balik ke User
    public function user()
    {
        return $this->belongsTo(User::class, 'id_user', 'id_user');
    }
}
