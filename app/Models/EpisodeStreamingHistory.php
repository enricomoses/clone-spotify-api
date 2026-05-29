<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EpisodeStreamingHistory extends Model
{
    use HasFactory;

    protected $table = 'episode_streaminghistory';
    protected $primaryKey = 'id_stream';
    protected $keyType = 'string';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'id_stream',
        'played_at',
        'play_duration',
        'id_user',
        'id_episode'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'id_user', 'id_user');
    }
    public function episode()
    {
        return $this->belongsTo(Episode::class, 'id_episode', 'id_episode');
    }
}
