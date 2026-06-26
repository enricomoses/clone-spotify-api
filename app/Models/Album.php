<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Album extends Model
{
    use HasFactory;

    // Konfigurasi tabel sesuai pgAdmin
    protected $table = 'albums';
    protected $primaryKey = 'id_album';
    protected $keyType = 'string';
    public $incrementing = false;
    public $timestamps = false;

    // Kolom yang diizinkan untuk diisi via API
    protected $fillable = [
        'id_album',
        'title',
        'release',
        'cover_image'
    ];

    public function songs()
    {
        return $this->hasMany(Song::class, 'id_album', 'id_album');
    }
    public function artists()
    {
        return $this->belongsToMany(Artist::class, 'artist_album', 'id_album', 'id_artist');
    }
}
