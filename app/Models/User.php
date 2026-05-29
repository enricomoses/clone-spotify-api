<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    // 1. Konfigurasi Custom Database (Sesuai dengan pgAdmin Anda)
    protected $table = 'User'; // Gunakan huruf kapital jika di pgAdmin namanya "User"
    protected $primaryKey = 'id_user';
    protected $keyType = 'string';
    public $incrementing = false;
    public $timestamps = false; // Karena tabel Anda tidak punya created_at & updated_at

    // 2. Kolom yang boleh diisi (Mass Assignment)
    protected $fillable = [
        'id_user',
        'name',
        'email',
        'password',
        'birthday',
        'gender',
        'country',
        'profile_picture'
    ];

    // 3. Sembunyikan password agar tidak bocor saat data dipanggil via API
    protected $hidden = [
        'password',
    ];

    // 4. Casting tipe data (Bawaan Laravel untuk hashing password)
    protected function casts(): array
    {
        return [
            'password' => 'hashed',
        ];
    }
}
