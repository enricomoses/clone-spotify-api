<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Episode extends Model
{
    use HasFactory;

    protected $table = 'episodes';
    protected $primaryKey = 'id_episode';
    protected $keyType = 'string';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'id_episode',
        'title',
        'description',
        'published_at',
        'duration',
        'audio_url',
        'video_url',
        'thumbnail_image',
        'id_podcast'
    ];

    public function podcast()
    {
        return $this->belongsTo(Podcast::class, 'id_podcast', 'id_podcast');
    }
    public function streamingHistories()
    {
        return $this->hasMany(EpisodeStreamingHistory::class, 'id_episode', 'id_episode');
    }
}
