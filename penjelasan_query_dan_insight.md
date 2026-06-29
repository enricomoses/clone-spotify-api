# Penjelasan Query SQL dan Analisis Insight Bisnis (Sesuai Frontend)
## Proyek Basis Data: Spotify Clone

Bagian ini menyajikan penjelasan teknis query SQL yang digunakan untuk menghasilkan laporan (report) tugas besar yang sepenuhnya terintegrasi dengan antarmuka front-end visual aplikasi Spotify Clone.

---

### A. Report Aktivitas Pengguna (Report A)
*Riwayat Pemutaran Musik Pengguna (Halaman User Profile - Recently Played)*

#### 1. Query SQL
```sql
SELECT 
    h.id_stream,
    u.name AS user_name,
    s.title AS song_title,
    a.name AS artist_name,
    h.played_at,
    h.play_duration
FROM 
    song_streaming_histories h
JOIN 
    users u ON h.id_user = u.id_user
JOIN 
    songs s ON h.id_song = s.id_song
JOIN 
    song_artist sa ON s.id_song = sa.id_song
JOIN 
    artists a ON sa.id_artist = a.id_artist
WHERE 
    u.id_user = 'e0362ced-2fbe-4f25-8cb4-433c8c8a8bdf' -- Filter user Enrico yang sedang login
ORDER BY 
    h.played_at DESC;
```

#### 2. Penjelasan Query & Penggunaan Function
* **Kegunaan Data**: Query ini digunakan untuk menyajikan riwayat dengar lagu pengguna secara kronologis pada halaman profil pribadi pendengar.
* **Teknik SQL & Function**:
  * **JOIN**: Menggabungkan tabel transaksi log streaming `song_streaming_histories` dengan tabel master `users`, `songs`, dan `artists` (melalui pivot `song_artist`) untuk memunculkan detail nama musisi dan lagu.
  * **Filter Kondisi**: Menggunakan klausa `WHERE u.id_user` untuk menyaring data riwayat dengar hanya milik user yang bersangkutan (Enrico).
  * **Sorting**: Diurutkan menggunakan `ORDER BY h.played_at DESC` agar aktivitas lagu terbaru yang diputar pendengar langsung berada di paling atas daftar.

#### 3. Penjelasan Insight dari Query
* **Analisis & Interpretasi**: Log riwayat dengar menunjukkan bahwa pengguna Enrico mendengarkan lagu-lagu dari artis Taylor Swift ("Blank Space", "Style") dan Hindia ("Evaluasi") secara berulang. Ini menandakan Enrico menyukai genre Pop Internasional dan Indie Lokal secara aktif.
* **Keputusan Bisnis**: Sistem dapat mendeteksi ketertarikan Enrico terhadap artis-artis tersebut untuk memodifikasi beranda utama Enrico, seperti mempromosikan album terbaru Hindia atau menampilkan jadwal konser Taylor Swift yang akan datang.

---

### B. Report Agregasi Data (Report B)

#### 1. Query 1: Statistik Pemutaran Lagu per Artis (Halaman Portal Artis - Analytics - Top 5 Songs)
```sql
SELECT 
    s.title AS song_title,
    COUNT(h.id_stream) AS total_streams,
    SUM(h.play_duration) AS total_play_duration_seconds,
    ROUND(AVG(h.play_duration), 2) AS avg_play_duration_seconds
FROM 
    songs s
JOIN 
    song_artist sa ON s.id_song = sa.id_song
JOIN 
    song_streaming_histories h ON s.id_song = h.id_song
WHERE 
    sa.id_artist = 'A_01' -- Filter ID Artis Taylor Swift
GROUP BY 
    s.id_song, 
    s.title
HAVING 
    COUNT(h.id_stream) >= 3
ORDER BY 
    total_streams DESC;
```

* **Penjelasan Query & Penggunaan Function**:
  * **COUNT(h.id_stream)**: Menghitung akumulasi jumlah pemutaran per lagu milik artis.
  * **SUM(h.play_duration)** & **AVG(...)**: Menghitung jumlah total durasi dan rata-rata waktu dengar lagu dalam satuan detik.
  * **GROUP BY**: Mengelompokkan riwayat putar berdasarkan data lagu artis (`s.id_song` dan `s.title`).
  * **HAVING**: Menyaring data setelah dikelompokkan, hanya menampilkan lagu yang sudah diputar minimal 3 kali (`HAVING COUNT(h.id_stream) >= 3`).
* **Visual Frontend**: Data agregasi ini ditampilkan secara visual pada **Tabel Detail Analytics** portal artis (`/artist/analytics`) dan panel **Top 5 Songs** pada dashboard portal artis.
* **Insight Bisnis**: Mengidentifikasi lagu-lagu utama artis yang berkinerja stabil. Lagu-lagu yang melampaui ambang batas dengar minimal (3 kali putar) tergolong lagu favorit yang memiliki tingkat retensi retensi pendengar tinggi.

#### 2. Query 2: Jumlah Lagu Terunggah per Album oleh Artis (Halaman Detail Album - `/album/[id]`)
```sql
SELECT 
    al.id_album,
    al.title AS album_title,
    art.name AS artist_name,
    COUNT(s.id_song) AS total_songs,
    SUM(s.duration) AS total_duration_seconds,
    ROUND(AVG(s.duration), 2) AS avg_song_duration_seconds
FROM 
    albums al
JOIN 
    artist_album aa ON al.id_album = aa.id_album
JOIN 
    artists art ON aa.id_artist = art.id_artist
JOIN 
    songs s ON al.id_album = s.id_album
GROUP BY 
    al.id_album, 
    al.title, 
    art.name
HAVING 
    COUNT(s.id_song) >= 2
ORDER BY 
    total_songs DESC;
```

* **Penjelasan Query & Penggunaan Function**:
  * **COUNT(s.id_song)**: Menghitung kuantitas lagu yang tergabung ke dalam album terkait.
  * **SUM(s.duration)**: Mengakumulasikan durasi audio seluruh lagu di dalam album (total durasi album dalam detik).
  * **GROUP BY**: Mengelompokkan berdasarkan entitas album dan artis terkait.
  * **HAVING**: Menyaring dan membatasi agar hanya menampilkan album berisi minimal 2 lagu (`HAVING COUNT(s.id_song) >= 2`), mengecualikan lagu-lagu berstatus *Single*.
* **Visual Frontend**: Data visual laporan ini ditampilkan pada bagian **Header Banner Halaman Detail Album** yang menyajikan ringkasan total lagu dan akumulasi durasi waktu album.
* **Insight Bisnis**: Membantu kurator platform mengklasifikasikan rilis musisi menjadi Album Penuh (LP) untuk dipromosikan pada banner halaman utama, guna mendorong sesi mendengarkan yang lebih lama di platform.

---

### C. Report Business Insight (Report D)

#### 1. Query 1: Analisis Peringkat Lagu Terpopuler (Halaman Discover Weekly - `/discover-weekly`)
* **Pertanyaan Bisnis**: Lagu mana saja yang memiliki performa paling populer (paling sering diputar secara global) di seluruh sistem, dan siapa artis di balik lagu tersebut?
* **Alasan Bisnis**: Membantu operasional platform dalam menyusun kurasi playlist rekomendasi mingguan otomatis (Discover Weekly Preview) dan sebagai dasar utama perhitungan royalti artis secara transparan.
* **Query SQL**:
```sql
SELECT 
    s.id_song,
    s.title AS song_title,
    a.name AS artist_name,
    al.title AS album_title,
    COUNT(h.id_stream) AS play_count,
    COALESCE(SUM(h.play_duration), 0) AS total_streamed_seconds,
    RANK() OVER (ORDER BY COUNT(h.id_stream) DESC) AS popularity_rank
FROM 
    songs s
JOIN 
    song_artist sa ON s.id_song = sa.id_song
JOIN 
    artists a ON sa.id_artist = a.id_artist
LEFT JOIN 
    albums al ON s.id_album = al.id_album
LEFT JOIN 
    song_streaming_histories h ON s.id_song = h.id_song
GROUP BY 
    s.id_song, 
    s.title, 
    a.name, 
    al.title
ORDER BY 
    play_count DESC, 
    song_title ASC
LIMIT 10;
```
* **Interpretasi Hasil (Insight)**:
  Lagu "Blank Space" menduduki posisi teratas (Rank 1) popularitas di platform dengan frekuensi pemutaran tertinggi. Dari visualisasi Discover Weekly, lagu-lagu dengan peringkat popularitas 1-10 didominasi oleh musisi Pop Internasional.
* **Keputusan Bisnis**: 
  * Lagu dengan `popularity_rank` tertinggi ditempatkan pada barisan pertama playlist kurasi global untuk memikat pengguna baru.
  * Pembayaran royalti dialokasikan secara proporsional sesuai data detik streaming (`total_streamed_seconds`) guna menjamin pembagian hak cipta yang adil bagi musisi platform.

#### 2. Query 2: Analisis Artis Terpopuler (Halaman Beranda - Popular Artists - `/home`)
* **Pertanyaan Bisnis**: Siapa saja artis/musisi yang mengumpulkan total streaming histories tertinggi secara global di platform?
* **Alasan Bisnis**: Membantu tim marketing mengidentifikasi artis-artis dengan basis penggemar terbesar secara riil untuk program promosi eksklusif, banner iklan terarah, atau sponsor konser platform.
* **Query SQL**:
```sql
SELECT 
    a.id_artist, 
    a.name AS artist_name, 
    COUNT(h.id_stream) AS total_streams
FROM 
    artists a
JOIN 
    song_artist sa ON a.id_artist = sa.id_artist
JOIN 
    songs s ON sa.id_song = s.id_song
LEFT JOIN 
    song_streaming_histories h ON s.id_song = h.id_song
GROUP BY 
    a.id_artist, 
    a.name
ORDER BY 
    total_streams DESC
LIMIT 12;
```
* **Interpretasi Hasil (Insight)**:
  Artis Taylor Swift memiliki angka akumulasi streaming terbesar secara global (mencakup 12 artis teratas), menjadikannya musisi paling berpengaruh di platform saat ini. Ini divisualisasikan dalam bentuk kartu bulat Popular Artists di beranda utama pengguna.
* **Keputusan Bisnis**: 
  * Menawarkan kemitraan iklan komersial eksklusif kepada perwakilan manajemen Taylor Swift karena jaminan jangkauan pendengarnya yang luas.
  * Menggunakan visual wajah artis terpopuler pada kampanye pemasaran luar ruang (billboard) platform untuk menarik pengguna baru mengunduh aplikasi.
