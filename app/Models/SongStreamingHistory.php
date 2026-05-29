<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SongStreamingHistory extends Model
{
    use HasFactory;

    protected $table = 'song_streaminghistory'; // Sesuai nama di pgAdmin Anda
    protected $primaryKey = 'id_stream';
    protected $keyType = 'string';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'id_stream',
        'played_at',
        'play_duration',
        'id_user',
        'id_song'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'id_user', 'id_user');
    }
    public function song()
    {
        return $this->belongsTo(Song::class, 'id_song', 'id_song');
    }
}
