# Skema Relasional (Relational Schema)
## Proyek Basis Data: Spotify Clone

Berikut adalah rancangan skema relasional sistem basis data Spotify Clone. Penulisan menggunakan notasi akademik standar dengan ketentuan sebagai berikut:
* Kolom dengan garis bawah tunggal (`<u>nama_kolom</u>`) menandakan **Primary Key (PK)**.
* Kolom dengan tanda bintang / cetak miring (`*nama_kolom*`) menandakan **Foreign Key (FK)**.
* Composite Primary Key ditandai dengan garis bawah pada beberapa kolom kunci penyusunnya (`<u>nama_kolom1</u>, <u>nama_kolom2</u>`).

---

### 1. Kelompok Tabel Master Mandiri (Independent Master Tables)

Tabel master mandiri menyimpan entitas utama yang tidak bergantung langsung pada entitas lain:

* **users**
  * Skema: `users (<u>id_user</u>, name, email, password, birthday, gender, country, profile_picture, created_at, updated_at)`
  * Primary Key: `id_user`
  * Constraints:
    * `chk_user_email`: CHECK format email menggunakan regex (`email ~* '^[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,}$'`)

* **artists**
  * Skema: `artists (<u>id_artist</u>, name, email, password, instagram, facebook, wikipedia, x, bio, profile_picture, created_at, updated_at)`
  * Primary Key: `id_artist`
  * Constraints:
    * `chk_artist_email`: CHECK format email menggunakan regex (`email ~* '^[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,}$'`)

* **albums**
  * Skema: `albums (<u>id_album</u>, title, release, cover_image, created_at, updated_at)`
  * Primary Key: `id_album`

* **playlist_categories**
  * Skema: `playlist_categories (<u>id_playlist_category</u>, name, cover_image, icon, created_at, updated_at)`
  * Primary Key: `id_playlist_category`

* **podcast_categories**
  * Skema: `podcast_categories (<u>id_podcast_category</u>, name, cover_image, icon, created_at, updated_at)`
  * Primary Key: `id_podcast_category`

* **concerts**
  * Skema: `concerts (<u>id_concert</u>, location, location_url, event_at, ticket_url, created_at, updated_at)`
  * Primary Key: `id_concert`

---

### 2. Kelompok Tabel Master Dependen (Dependent Master Tables)

Tabel master dependen menyimpan entitas yang memiliki dependensi / hubungan referensi langsung (Foreign Key) ke tabel master mandiri:

* **songs**
  * Skema: `songs (<u>id_song</u>, title, duration, *id_album*, audio_url, created_at, updated_at)`
  * Primary Key: `id_song`
  * Foreign Key: `id_album` referensi ke `albums(id_album)` (ON DELETE RESTRICT/CASCADE)
  * Constraints:
    * `chk_song_duration`: CHECK durasi wajib bernilai positif (`duration > 0`)

* **playlists**
  * Skema: `playlists (<u>id_playlist</u>, name, description, cover_image, *id_playlist_category*, created_at, updated_at)`
  * Primary Key: `id_playlist`
  * Foreign Key: `id_playlist_category` referensi ke `playlist_categories(id_playlist_category)`

* **podcasts**
  * Skema: `podcasts (<u>id_podcast</u>, name, about, cover_image, *id_podcast_category*, *id_user*, created_at, updated_at)`
  * Primary Key: `id_podcast`
  * Foreign Key: `id_podcast_category` referensi ke `podcast_categories(id_podcast_category)`
  * Foreign Key: `id_user` referensi ke `users(id_user)`

* **episodes**
  * Skema: `episodes (<u>id_episode</u>, title, description, published_at, duration, audio_url, video_url, thumbnail_image, *id_podcast*, created_at, updated_at)`
  * Primary Key: `id_episode`
  * Foreign Key: `id_podcast` referensi ke `podcasts(id_podcast)`
  * Constraints:
    * `chk_episode_duration`: CHECK durasi wajib bernilai positif (`duration > 0`)

* **artist_galleries**
  * Skema: `artist_galleries (<u>id_gallery</u>, image_url, description_pict, uploaded_at, *id_artist*, created_at, updated_at)`
  * Primary Key: `id_gallery`
  * Foreign Key: `id_artist` referensi ke `artists(id_artist)`

---

### 3. Kelompok Tabel Penghubung / Relasi Banyak-ke-Banyak (Bridge / Pivot Tables)

Tabel penghubung digunakan untuk memodelkan hubungan Many-to-Many antarentitas:

* **song_artist**
  * Skema: `song_artist (<u>*id_song*</u>, <u>*id_artist*</u>, created_at, updated_at)`
  * Primary Key: Composite `(id_song, id_artist)`
  * Foreign Key: 
    * `id_song` referensi ke `songs(id_song)`
    * `id_artist` referensi ke `artists(id_artist)`

* **artist_album**
  * Skema: `artist_album (<u>*id_artist*</u>, <u>*id_album*</u>, created_at, updated_at)`
  * Primary Key: Composite `(id_artist, id_album)`
  * Foreign Key:
    * `id_artist` referensi ke `artists(id_artist)`
    * `id_album` referensi ke `albums(id_album)`

* **artist_concert**
  * Skema: `artist_concert (<u>*id_artist*</u>, <u>*id_concert*</u>, created_at, updated_at)`
  * Primary Key: Composite `(id_artist, id_concert)`
  * Foreign Key:
    * `id_artist` referensi ke `artists(id_artist)`
    * `id_concert` referensi ke `concerts(id_concert)`

* **playlist_song**
  * Skema: `playlist_song (<u>*id_playlist*</u>, <u>*id_song*</u>, created_at, updated_at)`
  * Primary Key: Composite `(id_playlist, id_song)`
  * Foreign Key:
    * `id_playlist` referensi ke `playlists(id_playlist)`
    * `id_song` referensi ke `songs(id_song)`

* **playlist_collaborator**
  * Skema: `playlist_collaborator (<u>*id_playlist*</u>, <u>*id_user*</u>, role, created_at, updated_at)`
  * Primary Key: Composite `(id_playlist, id_user)`
  * Foreign Key:
    * `id_playlist` referensi ke `playlists(id_playlist)`
    * `id_user` referensi ke `users(id_user)`

* **user_likedsong**
  * Skema: `user_likedsong (<u>*id_user*</u>, <u>*id_song*</u>, created_at, updated_at)`
  * Primary Key: Composite `(id_user, id_song)`
  * Foreign Key:
    * `id_user` referensi ke `users(id_user)`
    * `id_song` referensi ke `songs(id_song)`

* **user_followartist**
  * Skema: `user_followartist (<u>*id_user*</u>, <u>*id_artist*</u>, created_at, updated_at)`
  * Primary Key: Composite `(id_user, id_artist)`
  * Foreign Key:
    * `id_user` referensi ke `users(id_user)`
    * `id_artist` referensi ke `artists(id_artist)`

* **user_followplaylist**
  * Skema: `user_followplaylist (<u>*id_user*</u>, <u>*id_playlist*</u>, created_at, updated_at)`
  * Primary Key: Composite `(id_user, id_playlist)`
  * Foreign Key:
    * `id_user` referensi ke `users(id_user)`
    * `id_playlist` referensi ke `playlists(id_playlist)`

* **user_followpodcast**
  * Skema: `user_followpodcast (<u>*id_user*</u>, <u>*id_podcast*</u>, created_at, updated_at)`
  * Primary Key: Composite `(id_user, id_podcast)`
  * Foreign Key:
    * `id_user` referensi ke `users(id_user)`
    * `id_podcast` referensi ke `podcasts(id_podcast)`

* **user_followuser**
  * Skema: `user_followuser (<u>*follower_id*</u>, <u>*followed_id*</u>, created_at, updated_at)`
  * Primary Key: Composite `(follower_id, followed_id)`
  * Foreign Key:
    * `follower_id` referensi ke `users(id_user)`
    * `followed_id` referensi ke `users(id_user)`

---

### 4. Kelompok Tabel Transaksi / Riwayat (Transaction & Log Tables)

Tabel transaksi mencatat interaksi dinamis berorientasi waktu (streaming activity):

* **song_streaming_histories**
  * Skema: `song_streaming_histories (<u>id_stream</u>, played_at, play_duration, *id_user*, *id_song*, created_at, updated_at)`
  * Primary Key: `id_stream`
  * Foreign Key:
    * `id_user` referensi ke `users(id_user)`
    * `id_song` referensi ke `songs(id_song)`
  * Constraints:
    * `chk_song_stream_duration`: CHECK durasi dengar streaming minimal 0 (`play_duration >= 0`)

* **episode_streaming_histories**
  * Skema: `episode_streaming_histories (<u>id_stream</u>, played_at, play_duration, *id_user*, *id_episode*, created_at, updated_at)`
  * Primary Key: `id_stream`
  * Foreign Key:
    * `id_user` referensi ke `users(id_user)`
    * `id_episode` referensi ke `episodes(id_episode)`
  * Constraints:
    * `chk_episode_stream_duration`: CHECK durasi dengar streaming minimal 0 (`play_duration >= 0`)
