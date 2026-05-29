<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Album extends Model
{
    use HasFactory;

    // Konfigurasi tabel sesuai pgAdmin
    protected $table = 'album';
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
}
