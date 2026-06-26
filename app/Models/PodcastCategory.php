<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PodcastCategory extends Model
{
    use HasFactory;

    protected $table = 'podcast_categories'; // Sesuai nama di pgAdmin Anda
    protected $primaryKey = 'id_podcast_category';
    protected $keyType = 'string';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'id_podcast_category',
        'name',
        'cover_image',
        'icon'
    ];

    public function podcasts()
    {
        return $this->hasMany(Podcast::class, 'id_podcast_category', 'id_podcast_category');
    }
}
