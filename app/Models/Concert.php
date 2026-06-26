<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Concert extends Model
{
    use HasFactory;

    protected $table = 'concerts';
    protected $primaryKey = 'id_concert';
    protected $keyType = 'string';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'id_concert',
        'location',
        'location_url',
        'event_at',
        'ticket_url'
    ];

    public function artists()
    {
        return $this->belongsToMany(Artist::class, 'artist_concert', 'id_concert', 'id_artist');
    }
}
