<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Artist extends Model
{
    use HasFactory;

    // Konfigurasi tabel sesuai pgAdmin
    protected $table = 'artist';
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

    // Hashing password secara otomatis
    protected function casts(): array
    {
        return [
            'password' => 'hashed',
        ];
    }
}
