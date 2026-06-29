# Dokumentasi Lengkap: Form Aplikasi dan Tampilan Report
## Proyek Basis Data: Spotify Clone

Dokumen ini menjelaskan secara rinci seluruh formulir (form) interaksi data serta seluruh tampilan laporan (report) yang diimplementasikan pada aplikasi Spotify Clone, baik untuk portal **User (Pendengar)** maupun **Artist (Portal Spotify for Artists)**.

---

## 1. DOKUMENTASI FORM APLIKASI (MANAJEMEN DATA & TRANSAKSI)

Formulir dalam aplikasi memproses masukan pengguna di frontend (Next.js), memanggil API endpoint di backend (Laravel), dan mengeksekusi perintah manipulasi data (DML) di database PostgreSQL.

### A. Form Registrasi & Autentikasi (Authentication Forms)

#### 1. Form Login User
* **Deskripsi**: Form untuk memverifikasi kredensial akun pendengar biasa.
* **Elemen Input**: Email (Text), Password (Password).
* **Endpoint API**: `POST /api/login`
* **Query SQL Terkait**: 
  `SELECT * FROM users WHERE email = :email LIMIT 1;`

#### 2. Form Login Artist
* **Deskripsi**: Form akses masuk bagi musisi ke dashboard portal Spotify for Artists.
* **Elemen Input**: Email (Text), Password (Password).
* **Endpoint API**: `POST /api/login/artist`
* **Query SQL Terkait**: 
  `SELECT * FROM artists WHERE email = :email LIMIT 1;`

#### 3. Form Sign Up User (Registrasi Pendengar)
* **Deskripsi**: Form pendaftaran akun pendengar baru.
* **Elemen Input**: Name (Text), Email (Text), Password (Password), Birthday (Date), Gender (Select), Country (Text), Profile Picture (File Upload/URL).
* **Endpoint API**: `POST /api/users` (melalui controller `UserController@store`)
* **Query SQL Terkait**:
  ```sql
  INSERT INTO users (id_user, name, email, password, birthday, gender, country, profile_picture, created_at, updated_at) 
  VALUES (:id_user, :name, :email, :password, :birthday, :gender, :country, :profile_picture, NOW(), NOW());
  ```

#### 4. Form Sign Up Artist (Registrasi Artis/Musisi)
* **Deskripsi**: Form pendaftaran profil musisi baru di platform.
* **Elemen Input**: Name (Text), Email (Text), Password (Password), Instagram (Text), Facebook (Text), Wikipedia (Text), X (Text), Bio (Text Area), Profile Picture (File Upload/URL).
* **Endpoint API**: `POST /api/artists` (melalui controller `ArtistController@store`)
* **Query SQL Terkait**:
  ```sql
  INSERT INTO artists (id_artist, name, email, password, instagram, facebook, wikipedia, x, bio, profile_picture, created_at, updated_at) 
  VALUES (:id_artist, :name, :email, :password, :instagram, :facebook, :wikipedia, :x, :bio, :profile_picture, NOW(), NOW());
  ```

---

### B. Form Pemutakhiran Profil (Profile Update Forms)

#### 1. Form Update Profile User
* **Deskripsi**: Form bagi user untuk mengubah data pribadi atau foto profil mereka.
* **Elemen Input**: Name (Text), Email (Text), Birthday (Date), Gender (Select), Country (Text), Profile Picture (URL/Upload).
* **Endpoint API**: `PUT /api/users/{id_user}`
* **Query SQL Terkait**:
  ```sql
  UPDATE users 
  SET name = :name, email = :email, birthday = :birthday, gender = :gender, country = :country, profile_picture = :profile_picture, updated_at = NOW() 
  WHERE id_user = :id_user;
  ```

#### 2. Form Update Profile Artist
* **Deskripsi**: Form bagi artis untuk mengubah biografi, media sosial, atau foto profil publik mereka.
* **Elemen Input**: Name (Text), Email (Text), Instagram (Text), Facebook (Text), Wikipedia (Text), X (Text), Bio (Text Area), Profile Picture (URL/Upload).
* **Endpoint API**: `PUT /api/artists/{id_artist}`
* **Query SQL Terkait**:
  ```sql
  UPDATE artists 
  SET name = :name, email = :email, instagram = :instagram, facebook = :facebook, wikipedia = :wikipedia, x = :x, bio = :bio, profile_picture = :profile_picture, updated_at = NOW() 
  WHERE id_artist = :id_artist;
  ```

---

### C. Form Manajemen Data Master (CRUD Standard)

#### 1. CRUD Songs (Kelola Lagu oleh Artist)
* **Fungsi**: Memproses manipulasi data lagu pada katalog musisi.
* **Elemen Input Form**: Title (Text), Duration (Number), Album (Select Dropdown), Audio URL (Upload/Text).
* **Endpoint API**: `POST /api/songs` (Create), `PUT /api/songs/{id}` (Update), `DELETE /api/songs/{id}` (Delete).
* **Query SQL Terkait**:
  * **Create**: 
    `INSERT INTO songs (id_song, title, duration, id_album, audio_url) VALUES (:id_song, :title, :duration, :id_album, :audio_url);`
    `INSERT INTO song_artist (id_song, id_artist) VALUES (:id_song, :id_artist);` (Tabel Pivot)
  * **Update**:
    `UPDATE songs SET title = :title, duration = :duration, id_album = :id_album, audio_url = :audio_url, updated_at = NOW() WHERE id_song = :id_song;`
  * **Delete**:
    `DELETE FROM songs WHERE id_song = :id_song;`

#### 2. CRUD Albums (Kelola Album oleh Artist)
* **Fungsi**: Memproses pembuatan atau penghapusan album rilis.
* **Elemen Input Form**: Title (Text), Release Date (Date), Cover Image (URL/Upload).
* **Endpoint API**: `POST /api/albums` (Create), `PUT /api/albums/{id}` (Update), `DELETE /api/albums/{id}` (Delete).
* **Query SQL Terkait**:
  * **Create**:
    `INSERT INTO albums (id_album, title, release, cover_image) VALUES (:id_album, :title, :release, :cover_image);`
    `INSERT INTO artist_album (id_artist, id_album) VALUES (:id_artist, :id_album);`
  * **Delete**:
    `DELETE FROM albums WHERE id_album = :id_album;`

#### 3. CRUD Playlists (Kelola Playlist oleh User)
* **Fungsi**: Form pembuatan wadah daftar putar lagu oleh pengguna.
* **Elemen Input Form**: Name (Text), Description (Text Area), Cover Image (Upload), Playlist Category (Select).
* **Endpoint API**: `POST /api/playlists` (Create), `PUT /api/playlists/{id}` (Update), `DELETE /api/playlists/{id}` (Delete).
* **Query SQL Terkait**:
  * **Create**:
    `INSERT INTO playlists (id_playlist, name, description, cover_image, id_playlist_category) VALUES (:id_playlist, :name, :description, :cover_image, :id_playlist_category);`
    `INSERT INTO playlist_collaborator (id_playlist, id_user, role) VALUES (:id_playlist, :id_user, 'Owner');`
  * **Delete**:
    `DELETE FROM playlists WHERE id_playlist = :id_playlist;`

#### 4. CRUD Podcasts (Kelola Podcast oleh Creator/User)
* **Fungsi**: Membuat profil podcast untuk mendistribusikan episode suara.
* **Elemen Input Form**: Name (Text), About (Text Area), Cover Image (Upload), Podcast Category (Select).
* **Endpoint API**: `POST /api/podcasts`, `DELETE /api/podcasts/{id}`.
* **Query SQL Terkait**:
  `INSERT INTO podcasts (id_podcast, name, about, cover_image, id_podcast_category, id_user) VALUES (:id, :name, :about, :cover, :id_category, :id_user);`

#### 5. CRUD Episodes (Kelola Episode oleh Podcast Creator)
* **Fungsi**: Mengunggah rekaman episode baru ke bawah naungan podcast tertentu.
* **Elemen Input Form**: Title (Text), Description (Text Area), Published At (DateTime), Duration (Number), Audio URL (Upload), Thumbnail Image (Upload), Podcast (Select).
* **Endpoint API**: `POST /api/episodes`, `DELETE /api/episodes/{id}`.
* **Query SQL Terkait**:
  `INSERT INTO episodes (id_episode, title, description, published_at, duration, audio_url, id_podcast) VALUES (:id, :title, :desc, :pub_at, :dur, :audio, :id_podcast);`

#### 6. CRUD Artist Galleries (Kelola Galeri Foto Artist)
* **Fungsi**: Menambahkan foto portofolio artis ke halaman profil mereka.
* **Elemen Input Form**: Image URL (Upload), Description Picture (Text).
* **Endpoint API**: `POST /api/artists/{id}/gallery`, `DELETE /api/artists/{id}/gallery/{gallery_id}`.
* **Query SQL Terkait**:
  `INSERT INTO artist_galleries (id_gallery, image_url, description_pict, uploaded_at, id_artist) VALUES (:id_gallery, :image_url, :description_pict, NOW(), :id_artist);`

#### 7. CRUD Concerts (Kelola Jadwal Konser)
* **Fungsi**: Artis mendaftarkan lokasi konser baru untuk tiket eksternal.
* **Elemen Input Form**: Location (Text), Location URL (Text), Event At (DateTime), Ticket URL (Text).
* **Endpoint API**: `POST /api/artists/{id}/concerts`, `DELETE /api/artists/{id}/concerts/{concert_id}`.
* **Query SQL Terkait**:
  `INSERT INTO concerts (id_concert, location, location_url, event_at, ticket_url) VALUES (:id_concert, :location, :location_url, :event_at, :ticket_url);`
  `INSERT INTO artist_concert (id_artist, id_concert) VALUES (:id_artist, :id_concert);`

---

### D. Form Aksi Interaksi & Transaksi Dinamis

#### 1. Form/Aksi Putar Musik (Streaming History)
* **Pemicu**: Pengguna menekan tombol "Play" pada lagu/episode di antarmuka web.
* **Endpoint API**: `POST /api/song-streaming` atau `POST /api/episode-streaming`
* **Query SQL Terkait**:
  ```sql
  INSERT INTO song_streaming_histories (id_stream, played_at, play_duration, id_user, id_song, created_at, updated_at) 
  VALUES (:id_stream, NOW(), :duration, :id_user, :id_song, NOW(), NOW());
  ```

#### 2. Form Tambah Lagu ke Playlist (Playlist Song Pivot)
* **Pemicu**: Tombol "Add to Playlist" pada daftar lagu.
* **Endpoint API**: `POST /api/playlists/{id}/songs`
* **Query SQL Terkait**:
  `INSERT INTO playlist_song (id_playlist, id_song, created_at, updated_at) VALUES (:id_playlist, :id_song, NOW(), NOW());`

#### 3. Form Suka Lagu (Like Song Transaction)
* **Pemicu**: Tombol ikon hati (Like) di sisi lagu.
* **Endpoint API**: `POST /api/users/{id}/like-song`
* **Query SQL Terkait**:
  `INSERT INTO user_likedsong (id_user, id_song, created_at, updated_at) VALUES (:id_user, :id_song, NOW(), NOW());`

#### 4. Form Follow Artist
* **Pemicu**: Tombol "Follow" pada profil musisi.
* **Endpoint API**: `POST /api/users/{id}/follow-artist`
* **Query SQL Terkait**:
  `INSERT INTO user_followartist (id_user, id_artist, created_at, updated_at) VALUES (:id_user, :id_artist, NOW(), NOW());`

#### 5. Form Tambah Kolaborator Playlist
* **Pemicu**: Memasukkan email/username teman ke form kolaborasi playlist.
* **Endpoint API**: `POST /api/playlists/{id}/collaborators`
* **Query SQL Terkait**:
  `INSERT INTO playlist_collaborator (id_playlist, id_user, role, created_at, updated_at) VALUES (:id_playlist, :id_user, :role, NOW(), NOW());`

---

## 2. DOKUMENTASI TAMPILAN REPORT (REPORT VIEWS)

Bagian ini menyinkronkan daftar query select yang tertulis di dalam file database `spotify_clone.sql` dengan antarmuka front-end visual yang dinikmati pengguna atau produser konten.

#### A. Laporan Tugas Akhir Semester (5 Laporan Utama - Terhubung dengan Frontend)

#### 1. Report A: Aktivitas Pengaliran Lagu Pengguna (Halaman User Profile - Recently Played Artists)
* **Visual Frontend**: Menampilkan barisan artis yang baru-baru ini didengarkan oleh pengguna di halaman profil pribadi (`/profile`) atau profil publik (`/profile/[id]`) pada bagian "Recently Played Artists".
* **Filter Masukan**: Dideteksi otomatis berdasarkan ID User (`id_user`) profil yang sedang dibuka.
* **Logika Query**: Menggabungkan tabel riwayat pemutaran musik `song_streaming_histories` dengan tabel `artists` untuk melacak artis mana saja yang lagunya baru-baru ini diputar oleh user bersangkutan.

#### 2. Report B1: Statistik Pemutaran Lagu per Artis (Halaman Portal Artis - Analytics - Top 5 Songs)
* **Visual Frontend**: Menampilkan daftar lagu paling sering diputar beserta jumlah dengarnya pada panel "Top 5 Songs" di halaman Dashboard Portal Artis (`/artist/dashboard`).
* **Filter Masukan**: Dideteksi otomatis berdasarkan ID Artis yang sedang login (`artist.id_artist`).
* **Logika Query**: Mengelompokkan log pemutaran berdasarkan ID Lagu milik artis bersangkutan, melakukan penghitungan total streams (`COUNT`), dan menyaring lagu dengan pemutaran minimal 3 kali (`HAVING COUNT(...) >= 3`).

#### 3. Report B2: Jumlah Lagu per Album milik Artis (Halaman Detail Album - `/album/[id]`)
* **Visual Frontend**: Menampilkan daftar lagu-lagu di halaman Detail Album lengkap dengan total jumlah lagu dan akumulasi durasi waktu album tersebut di bagian header banner.
* **Filter Masukan**: Dideteksi otomatis berdasarkan ID Album (`id_album`) dari parameter URL.
* **Logika Query**: Menggabungkan data dari tabel `albums` and `songs` dengan agregasi `COUNT` (untuk jumlah lagu) dan `SUM` (untuk total durasi detik) yang dikelompokkan per album.

#### 4. Report D1: Analisis Lagu Terpopuler (Halaman Discover Weekly - `/discover-weekly`)
* **Visual Frontend**: Menampilkan daftar 50 lagu rekomendasi terpopuler yang paling banyak diputar secara global di platform pada halaman Discover Weekly.
* **Filter Masukan**: Tidak ada (langsung dimuat oleh sistem secara global).
* **Logika Query**: Menggunakan fungsi analitik `RANK() OVER (ORDER BY COUNT(h.id_stream) DESC)` pada tabel transaksi streaming untuk mengurutkan lagu-lagu terpopuler di platform.

#### 5. Report D2: Analisis Artis Terpopuler (Halaman Utama Pendengar - Popular Artists - `/home`)
* **Visual Frontend**: Menampilkan barisan 12 kartu artis paling populer (Popular Artists) secara global di halaman beranda utama pendengar (`/home`).
* **Filter Masukan**: Tidak ada.
* **Logika Query**: Menghitung total streaming histories per artis secara global, mengurutkannya dari yang terbesar, dan mengambil 12 musisi terpopuler menggunakan `LIMIT 12` untuk rekomendasi utama.

---

### B. Fitur Report Antarmuka Pendengar (User-Facing Front End Reports)

Laporan penjelajahan musik di portal web pendengar:

#### 1. Good Morning (Home Screen)
* **Visual**: Grid 6 kartu di bagian atas Home Screen yang menyambut pengguna dengan album-album terbaru di sistem.
* **Logika Query**: Mengambil 6 data album teratas diurutkan berdasarkan tanggal rilis terbaru (`ORDER BY release DESC LIMIT 6`).

#### 2. Discover Weekly (Rekomendasi Mingguan)
* **Visual**: Daftar 5 lagu rekomendasi personal di halaman utama.
* **Logika Query**: Menyaring lagu dari kategori/genre yang diikuti pengguna (`user_followplaylist`), mengecualikan lagu yang sudah pernah didengar pengguna (`id_song NOT IN (...)`), dan menampilkan 5 teratas.

#### 3. Popular Artists
* **Visual**: Barisan kartu bulat profil artis di halaman utama atau halaman jelajah (dapat menampilkan hingga 12 artis sekaligus).
* **Logika Query**: Menghitung total streaming histories per artis secara global dan mengurutkannya secara menurun untuk menampilkan 12 musisi paling populer (menggunakan `LIMIT 12`).

#### 4. Albums & Playlist Anda (Your Library)
* **Visual**: Daftar isi koleksi library di panel kiri atau halaman profil yang memuat album terbitan dan playlist buatan/ikutan user.
* **Logika Query**: Melakukan gabungan (`UNION`) antara playlist buatan/kolaboratif (`playlist_collaborator`) dan playlist eksternal yang diikuti pengguna (`user_followplaylist`).

#### 5. Liked Songs (Koleksi Lagu Disukai)
* **Visual**: Halaman khusus "Liked Songs" dengan latar gradasi biru yang memuat lagu-lagu yang disukai user beserta detail album dan musisinya.
* **Logika Query**: Menyaring data dari tabel `user_likedsong` berdasarkan ID User yang login, digabungkan ke tabel `songs` dan `artists`.

#### 6. Podcast & Podcast Saya
* **Visual**: Tab "Podcasts" pada library yang memisahkan podcast langganan pendengar dan podcast yang dibuat sendiri oleh user tersebut.
* **Logika Query**: Memilih dari tabel `podcasts` disaring berdasarkan pembuat (`id_user = :logged_in_user`).

#### 7. Search (Pencarian Global)
* **Visual**: Hasil pencarian dinamis yang terbagi menjadi bagian Lagu, Artis, Album, dan Pengguna Lain (Users) berdasarkan kata kunci yang dimasukkan pengguna.
* **Logika Query**: Menggunakan gabungan `UNION ALL` di empat query pencarian terpisah (dari tabel `songs`, `artists`, `albums`, dan `users`) yang memanfaatkan operator `ILIKE` untuk pencocokan kata kunci.

#### 8. Genre (Music Categories)
* **Visual**: Grid kartu berwarna-warni di halaman Search yang merepresentasikan kategori genre musik.
* **Logika Query**: Membaca data kategori dari tabel `playlist_categories` secara alfabetis (`ORDER BY name ASC`).

#### 9. Concerts List
* **Visual**: Halaman konser yang memuat jadwal tour panggung terdekat dari artis platform.
* **Logika Query**: Memilih data konser dari tabel `concerts` digabungkan dengan tabel artis penampil (`artist_concert` dan `artists`).

---

### C. Fitur Report Analitis Portal Artis (Spotify for Artists Portal Reports)

Laporan performa musik pada portal khusus musisi:

#### 1. Dashboard Overview Metrics
* **Visual**: Panel ringkasan angka statistik penting di bagian atas dashboard artis (seperti profil Taylor Swift):
  * **Total Songs**: Menghitung total lagu terdaftar milik artis (`COUNT` pada `song_artist` berdasarkan `id_artist`).
  * **Total Albums**: Menghitung total album fisik/digital milik artis (`COUNT DISTINCT` pada `artist_album`).
  * **Total Streams (All-Time)**: Akumulasi seluruh pemutaran lagu milik artis dari awal sistem berjalan.
  * **Streams This Week**: Akumulasi pemutaran seminggu terakhir (disaring menggunakan filter tanggal mulai hari Senin minggu berjalan, misal: `>= '2026-06-22'`).
  * **Streams Today**: Akumulasi pemutaran khusus hari ini saja (disaring berdasarkan hari terakhir simulasi, misal: `>= '2026-06-29'`).

#### 2. Top Songs by Streams Chart
* **Visual**: Chart baris horizontal berwarna hijau yang memuat daftar lagu artis yang paling sering diputar, lengkap dengan total frekuensi pemutaran.
* **Logika Query**: Mengelompokkan tabel transaksi streaming berdasarkan ID Lagu milik artis bersangkutan, diurutkan dari yang tertinggi (`ORDER BY streams_count DESC`).

#### 3. Detailed Streaming Analytics Breakdown Table
* **Visual**: Tabel data terperinci di bagian bawah halaman analitik yang memetakan performa detail per lagu. Kolom memuat: Nomor, Judul Lagu (dan nama Album), Durasi Audio (MM:SS), Total All-Time Streams, Total Streams Minggu Ini, dan Total Streams Hari Ini.
* **Logika Query**: Menggunakan teknik *conditional aggregation* (`SUM(CASE WHEN...)`) untuk menghitung performa streaming mingguan dan harian secara efisien dalam satu kali eksekusi query gabungan:
  * Total All-Time: `COUNT(h.id_stream)`
  * This Week: `SUM(CASE WHEN h.played_at >= '2026-06-22' THEN 1 ELSE 0 END)`
  * Today: `SUM(CASE WHEN h.played_at >= '2026-06-29' THEN 1 ELSE 0 END)`
  * Konversi durasi menjadi menit dan detik: `TO_CHAR((s.duration * INTERVAL '1 second'), 'MI:SS')`.
