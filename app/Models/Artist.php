<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Sanctum\HasApiTokens;

class Artist extends Model
{
    use HasApiTokens, HasFactory;

    // Konfigurasi tabel sesuai pgAdmin
    protected $table = 'artists';
    protected $primaryKey = 'id_artist';
    protected $keyType = 'string';
    public $incrementing = false;
    public $timestamps = false;

    // Kolom yang diizinkan untuk diisi via API (Mass Assignment)
    protected $fillable = [
        'id_artist',
        'name',
        'email',
        'password',
        'instagram',
        'facebook',
        'wikipedia',
        'x',
        'bio',
        'profile_picture'
    ];

    // Menyembunyikan password dari response JSON API
    protected $hidden = [
        'password',
    ];


    public function galleries()
    {
        return $this->hasMany(ArtistGallery::class, 'id_artist', 'id_artist');
    }
    public function albums()
    {
        return $this->belongsToMany(Album::class, 'artist_album', 'id_artist', 'id_album');
    }
    public function songs()
    {
        return $this->belongsToMany(Song::class, 'song_artist', 'id_artist', 'id_song');
    }
    public function concerts()
    {
        return $this->belongsToMany(Concert::class, 'artist_concert', 'id_artist', 'id_concert');
    }
    public function followers()
    {
        return $this->belongsToMany(User::class, 'user_followartist', 'id_artist', 'id_user');
    }
}
