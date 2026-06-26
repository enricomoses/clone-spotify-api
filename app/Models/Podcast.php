<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Podcast extends Model
{
    use HasFactory;

    protected $table = 'podcasts';
    protected $primaryKey = 'id_podcast';
    protected $keyType = 'string';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'id_podcast',
        'name',
        'about',
        'cover_image',
        'id_podcast_category',
        'id_user'
    ];

    public function category()
    {
        return $this->belongsTo(PodcastCategory::class, 'id_podcast_category', 'id_podcast_category');
    }
    public function creator()
    {
        return $this->belongsTo(User::class, 'id_user', 'id_user');
    }
    public function episodes()
    {
        return $this->hasMany(Episode::class, 'id_podcast', 'id_podcast');
    }
    public function followers()
    {
        return $this->belongsToMany(User::class, 'user_followpodcast', 'id_podcast', 'id_user');
    }
}
