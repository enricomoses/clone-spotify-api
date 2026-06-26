<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ArtistGallery extends Model
{
    use HasFactory;

    protected $table = 'artist_galleries';
    protected $primaryKey = 'id_gallery';
    protected $keyType = 'string';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'id_gallery',
        'image_url',
        'description_pict',
        'uploaded_at',
        'id_artist'
    ];

    public function artist()
    {
        return $this->belongsTo(Artist::class, 'id_artist', 'id_artist');
    }
}
