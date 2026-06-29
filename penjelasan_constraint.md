# Penjelasan Integrity Constraints (Batasan Integritas)
## Proyek Basis Data: Spotify Clone

Dalam mengimplementasikan database Spotify Clone pada PostgreSQL, batasan integritas (integrity constraints) diterapkan secara ketat guna menjaga konsistensi, validitas, dan keandalan data dari kesalahan input. Batasan-batasan ini dikelompokkan menjadi tiga kategori utama: **Entity Integrity (Primary Key)**, **Referential Integrity (Foreign Key)**, dan **Domain Integrity / Business Rules (CHECK Constraints)**.

---

### 1. Entity Integrity (Primary Key Constraints)

Primary Key (PK) digunakan untuk menjamin bahwa setiap baris data di dalam tabel bersifat unik dan tidak bernilai kosong (`NOT NULL`).
* **Penerapan**: Ditetapkan pada setiap tabel master dan tabel relasi.
  * Contoh pada tabel master mandiri: `CONSTRAINT pk_users PRIMARY KEY (id_user)` dan `CONSTRAINT pk_artists PRIMARY KEY (id_artist)`.
  * Contoh pada tabel relasi banyak-ke-banyak (composite primary key): `CONSTRAINT pk_song_artist PRIMARY KEY (id_song, id_artist)`.
* **Tujuan**: Mencegah adanya duplikasi data entitas (misalnya, dua pengguna dengan ID yang sama) serta memudahkan proses pencarian dan relasi data secara cepat.

---

### 2. Referential Integrity (Foreign Key Constraints)

Foreign Key (FK) digunakan untuk menjaga konsistensi hubungan antartabel dan memastikan bahwa nilai pada kolom kunci tamu benar-benar eksis di tabel referensinya.
* **Penerapan**: Ditetapkan pada tabel master dependen, tabel pivot, dan tabel transaksi.
  * Contoh pada tabel master dependen: `CONSTRAINT song_id_album_fkey FOREIGN KEY (id_album) REFERENCES albums(id_album)`.
  * Contoh pada tabel transaksi: `CONSTRAINT song_streaminghistory_id_song_fkey FOREIGN KEY (id_song) REFERENCES songs(id_song)`.
* **Tujuan**: Mencegah terjadinya data yatim piatu (*orphan data*). Sebagai contoh:
  * Tidak boleh ada lagu (`songs`) yang merujuk ke ID album (`id_album`) yang tidak terdaftar di tabel `albums`.
  * Tidak boleh ada transaksi streaming (`song_streaming_histories`) dengan ID lagu (`id_song`) atau ID pengguna (`id_user`) yang tidak terdaftar di database.

---

### 3. Domain Integrity & Business Rules (CHECK Constraints)

Check Constraints merupakan batasan yang didefinisikan secara khusus untuk mencerminkan aturan bisnis (*business rules*) dari sistem Spotify Clone. Terdapat **6 Check Constraints** yang diterapkan langsung di dalam file `spotify_clone.sql`:

#### A. Batasan Validasi Format Email
1. **chk_user_email** (pada tabel `users`)
   * *Definisi*: `CONSTRAINT chk_user_email CHECK (email ~* '^[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,}$')`
2. **chk_artist_email** (pada tabel `artists`)
   * *Definisi*: `CONSTRAINT chk_artist_email CHECK (email ~* '^[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,}$')`
   * *Aturan Bisnis & Penjelasan*: Alamat email yang didaftarkan oleh pengguna (`users`) maupun artis (`artists`) wajib mengikuti format email standar internasional. Batasan ini menggunakan ekspresi reguler (Regular Expression / Regex) untuk memastikan adanya karakter sebelum simbol `@`, diikuti oleh domain, dan diakhiri dengan ekstensi domain minimal 2 karakter (misalnya: `.com`, `.id`, `.net`). Batasan ini mencegah kesalahan ketik (seperti mendaftarkan email tanpa `@` atau tanpa domain) saat registrasi akun.

#### B. Batasan Validasi Durasi Karya Musik / Konten
3. **chk_song_duration** (pada tabel `songs`)
   * *Definisi*: `CONSTRAINT chk_song_duration CHECK (duration > 0)`
4. **chk_episode_duration** (pada tabel `episodes`)
   * *Definisi*: `CONSTRAINT chk_episode_duration CHECK (duration > 0)`
   * *Aturan Bisnis & Penjelasan*: Durasi waktu untuk lagu (`songs`) maupun episode podcast (`episodes`) wajib bernilai positif (> 0 detik). Secara logika bisnis, tidak mungkin ada file audio lagu atau rekaman podcast yang berdurasi 0 detik atau bernilai negatif. Batasan ini menjaga kualitas katalog agar pemutar musik di aplikasi tidak mengalami *crash* akibat membaca durasi nol atau minus.

#### C. Batasan Validasi Nilai Transaksi Streaming
5. **chk_song_stream_duration** (pada tabel `song_streaming_histories`)
   * *Definisi*: `CONSTRAINT chk_song_stream_duration CHECK (play_duration >= 0)`
6. **chk_episode_stream_duration** (pada tabel `episode_streaming_histories`)
   * *Definisi*: `CONSTRAINT chk_episode_stream_duration CHECK (play_duration >= 0)`
   * *Aturan Bisnis & Penjelasan*: Durasi pemutaran musik/episode oleh pendengar dalam satu kali transaksi streaming wajib bernilai non-negatif (>= 0). Batasan ini mengizinkan nilai 0 detik (jika pengguna menekan tombol putar dan langsung menghentikannya / *pause* pada detik itu juga), namun memblokir nilai minus yang tidak masuk akal dalam pencatatan riwayat pemutaran. Hal ini penting untuk akurasi analisis bisnis mengenai total waktu dengar pengguna dan penentuan royalti artis.
