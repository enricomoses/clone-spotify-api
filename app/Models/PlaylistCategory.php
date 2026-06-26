<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlaylistCategory extends Model
{
    use HasFactory;

    protected $table = 'playlist_categories'; // Sesuai nama di pgAdmin Anda
    protected $primaryKey = 'id_playlist_category';
    protected $keyType = 'string';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'id_playlist_category',
        'name',
        'cover_image',
        'icon'
    ];

    public function playlists()
    {
        return $this->hasMany(Playlist::class, 'id_playlist_category', 'id_playlist_category');
    }
}
