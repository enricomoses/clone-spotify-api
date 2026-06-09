<?php

// 1. Normalisasi REQUEST_URI yang dipotong oleh Vercel
if (isset($_SERVER['REQUEST_URI'])) {
    $uri = $_SERVER['REQUEST_URI'];
    
    // Jika Vercel memotong '/api', kita pasangkan kembali agar Laravel tidak bingung
    if (!str_starts_with($uri, '/api')) {
        $_SERVER['REQUEST_URI'] = '/api' . $uri;
    }
}

// 2. Kode bawaan untuk menjalankan Laravel
require __DIR__ . '/../public/index.php';
