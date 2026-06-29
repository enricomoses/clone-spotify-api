-- =====================================================
-- DATABASE DDL (CREATE TABLES AND CONSTRAINTS)
-- =====================================================

DROP TABLE IF EXISTS episode_streaming_histories CASCADE;
DROP TABLE IF EXISTS song_streaming_histories CASCADE;
DROP TABLE IF EXISTS user_followuser CASCADE;
DROP TABLE IF EXISTS user_followpodcast CASCADE;
DROP TABLE IF EXISTS user_followplaylist CASCADE;
DROP TABLE IF EXISTS user_followartist CASCADE;
DROP TABLE IF EXISTS user_likedsong CASCADE;
DROP TABLE IF EXISTS playlist_collaborator CASCADE;
DROP TABLE IF EXISTS playlist_song CASCADE;
DROP TABLE IF EXISTS artist_concert CASCADE;
DROP TABLE IF EXISTS artist_album CASCADE;
DROP TABLE IF EXISTS song_artist CASCADE;
DROP TABLE IF EXISTS artist_galleries CASCADE;
DROP TABLE IF EXISTS episodes CASCADE;
DROP TABLE IF EXISTS podcasts CASCADE;
DROP TABLE IF EXISTS playlists CASCADE;
DROP TABLE IF EXISTS songs CASCADE;
DROP TABLE IF EXISTS concerts CASCADE;
DROP TABLE IF EXISTS podcast_categories CASCADE;
DROP TABLE IF EXISTS playlist_categories CASCADE;
DROP TABLE IF EXISTS albums CASCADE;
DROP TABLE IF EXISTS artists CASCADE;
DROP TABLE IF EXISTS users CASCADE;

-- Table: users
CREATE TABLE users (
    id_user varchar(255) NOT NULL,
    name varchar(100) NOT NULL,
    email varchar(100) NOT NULL,
    password varchar(255) NOT NULL,
    birthday date ,
    gender varchar(20) ,
    country varchar(50) ,
    profile_picture varchar(255) ,
    created_at timestamp with time zone NOT NULL DEFAULT now(),
    updated_at timestamp with time zone NOT NULL DEFAULT now(),
    CONSTRAINT pk_users PRIMARY KEY (id_user),
    CONSTRAINT chk_user_email CHECK (((email)::text ~* '^[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,}$'::text))
);

-- Table: artists
CREATE TABLE artists (
    id_artist varchar(255) NOT NULL,
    name varchar(100) NOT NULL,
    email varchar(100) NOT NULL,
    password varchar(255) NOT NULL,
    instagram varchar(100) ,
    facebook varchar(100) ,
    wikipedia varchar(255) ,
    x varchar(100) ,
    bio text ,
    profile_picture varchar(255) ,
    created_at timestamp with time zone NOT NULL DEFAULT now(),
    updated_at timestamp with time zone NOT NULL DEFAULT now(),
    CONSTRAINT pk_artists PRIMARY KEY (id_artist),
    CONSTRAINT chk_artist_email CHECK (((email)::text ~* '^[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,}$'::text))
);

-- Table: albums
CREATE TABLE albums (
    id_album varchar(255) NOT NULL,
    title varchar(150) NOT NULL,
    release date NOT NULL,
    cover_image varchar(255) NOT NULL,
    created_at timestamp with time zone NOT NULL DEFAULT now(),
    updated_at timestamp with time zone NOT NULL DEFAULT now(),
    CONSTRAINT pk_albums PRIMARY KEY (id_album)
);

-- Table: playlist_categories
CREATE TABLE playlist_categories (
    id_playlist_category varchar(255) NOT NULL,
    name varchar(100) NOT NULL,
    cover_image varchar(255) NOT NULL,
    icon varchar(255) NOT NULL,
    created_at timestamp with time zone NOT NULL DEFAULT now(),
    updated_at timestamp with time zone NOT NULL DEFAULT now(),
    CONSTRAINT pk_playlist_categories PRIMARY KEY (id_playlist_category)
);

-- Table: podcast_categories
CREATE TABLE podcast_categories (
    id_podcast_category varchar(255) NOT NULL,
    name varchar(100) NOT NULL,
    cover_image varchar(255) NOT NULL,
    icon varchar(255) NOT NULL,
    created_at timestamp with time zone NOT NULL DEFAULT now(),
    updated_at timestamp with time zone NOT NULL DEFAULT now(),
    CONSTRAINT pk_podcast_categories PRIMARY KEY (id_podcast_category)
);

-- Table: concerts
CREATE TABLE concerts (
    id_concert varchar(255) NOT NULL,
    location varchar(200) NOT NULL,
    location_url varchar(255) ,
    event_at timestamp NOT NULL,
    ticket_url varchar(255) NOT NULL,
    created_at timestamp with time zone NOT NULL DEFAULT now(),
    updated_at timestamp with time zone NOT NULL DEFAULT now(),
    CONSTRAINT pk_concerts PRIMARY KEY (id_concert)
);

-- Table: songs
CREATE TABLE songs (
    id_song varchar(255) NOT NULL,
    title varchar(150) NOT NULL,
    duration integer NOT NULL,
    id_album varchar(255) ,
    audio_url varchar(255) ,
    created_at timestamp with time zone NOT NULL DEFAULT now(),
    updated_at timestamp with time zone NOT NULL DEFAULT now(),
    CONSTRAINT pk_songs PRIMARY KEY (id_song),
    CONSTRAINT song_id_album_fkey FOREIGN KEY (id_album) REFERENCES albums(id_album),
    CONSTRAINT chk_song_duration CHECK ((duration > 0))
);

-- Table: playlists
CREATE TABLE playlists (
    id_playlist varchar(255) NOT NULL,
    name varchar(100) NOT NULL,
    description text ,
    cover_image varchar(255) ,
    id_playlist_category varchar(255) ,
    created_at timestamp with time zone NOT NULL DEFAULT now(),
    updated_at timestamp with time zone NOT NULL DEFAULT now(),
    CONSTRAINT pk_playlists PRIMARY KEY (id_playlist),
    CONSTRAINT playlist_id_playlist_category_fkey FOREIGN KEY (id_playlist_category) REFERENCES playlist_categories(id_playlist_category)
);

-- Table: podcasts
CREATE TABLE podcasts (
    id_podcast varchar(255) NOT NULL,
    name varchar(150) NOT NULL,
    about text ,
    cover_image varchar(255) ,
    id_podcast_category varchar(255) ,
    id_user varchar(255) NOT NULL,
    created_at timestamp with time zone NOT NULL DEFAULT now(),
    updated_at timestamp with time zone NOT NULL DEFAULT now(),
    CONSTRAINT pk_podcasts PRIMARY KEY (id_podcast),
    CONSTRAINT podcast_id_podcast_category_fkey FOREIGN KEY (id_podcast_category) REFERENCES podcast_categories(id_podcast_category),
    CONSTRAINT podcast_id_user_fkey FOREIGN KEY (id_user) REFERENCES users(id_user)
);

-- Table: episodes
CREATE TABLE episodes (
    id_episode varchar(255) NOT NULL,
    title varchar(150) NOT NULL,
    description text ,
    published_at timestamp NOT NULL,
    duration integer NOT NULL,
    audio_url varchar(255) NOT NULL,
    video_url varchar(255) ,
    thumbnail_image varchar(255) ,
    id_podcast varchar(255) NOT NULL,
    created_at timestamp with time zone NOT NULL DEFAULT now(),
    updated_at timestamp with time zone NOT NULL DEFAULT now(),
    CONSTRAINT pk_episodes PRIMARY KEY (id_episode),
    CONSTRAINT episode_id_podcast_fkey FOREIGN KEY (id_podcast) REFERENCES podcasts(id_podcast),
    CONSTRAINT chk_episode_duration CHECK ((duration > 0))
);

-- Table: artist_galleries
CREATE TABLE artist_galleries (
    id_gallery varchar(255) NOT NULL,
    image_url varchar(255) NOT NULL,
    description_pict text ,
    uploaded_at timestamp NOT NULL,
    id_artist varchar(255) NOT NULL,
    created_at timestamp with time zone NOT NULL DEFAULT now(),
    updated_at timestamp with time zone NOT NULL DEFAULT now(),
    CONSTRAINT pk_artist_galleries PRIMARY KEY (id_gallery),
    CONSTRAINT artist_gallery_id_artist_fkey FOREIGN KEY (id_artist) REFERENCES artists(id_artist)
);

-- Table: song_artist
CREATE TABLE song_artist (
    id_song varchar(255) NOT NULL,
    id_artist varchar(255) NOT NULL,
    created_at timestamp with time zone NOT NULL DEFAULT now(),
    updated_at timestamp with time zone NOT NULL DEFAULT now(),
    CONSTRAINT pk_song_artist PRIMARY KEY (id_song, id_artist),
    CONSTRAINT song_artist_id_artist_fkey FOREIGN KEY (id_artist) REFERENCES artists(id_artist),
    CONSTRAINT song_artist_id_song_fkey FOREIGN KEY (id_song) REFERENCES songs(id_song)
);

-- Table: artist_album
CREATE TABLE artist_album (
    id_artist varchar(255) NOT NULL,
    id_album varchar(255) NOT NULL,
    created_at timestamp with time zone NOT NULL DEFAULT now(),
    updated_at timestamp with time zone NOT NULL DEFAULT now(),
    CONSTRAINT pk_artist_album PRIMARY KEY (id_artist, id_album),
    CONSTRAINT artist_album_id_album_fkey FOREIGN KEY (id_album) REFERENCES albums(id_album),
    CONSTRAINT artist_album_id_artist_fkey FOREIGN KEY (id_artist) REFERENCES artists(id_artist)
);

-- Table: artist_concert
CREATE TABLE artist_concert (
    id_artist varchar(255) NOT NULL,
    id_concert varchar(255) NOT NULL,
    created_at timestamp with time zone NOT NULL DEFAULT now(),
    updated_at timestamp with time zone NOT NULL DEFAULT now(),
    CONSTRAINT pk_artist_concert PRIMARY KEY (id_artist, id_concert),
    CONSTRAINT artist_concert_id_artist_fkey FOREIGN KEY (id_artist) REFERENCES artists(id_artist),
    CONSTRAINT artist_concert_id_concert_fkey FOREIGN KEY (id_concert) REFERENCES concerts(id_concert)
);

-- Table: playlist_song
CREATE TABLE playlist_song (
    id_playlist varchar(255) NOT NULL,
    id_song varchar(255) NOT NULL,
    created_at timestamp with time zone NOT NULL DEFAULT now(),
    updated_at timestamp with time zone NOT NULL DEFAULT now(),
    CONSTRAINT pk_playlist_song PRIMARY KEY (id_playlist, id_song),
    CONSTRAINT playlist_song_id_playlist_fkey FOREIGN KEY (id_playlist) REFERENCES playlists(id_playlist),
    CONSTRAINT playlist_song_id_song_fkey FOREIGN KEY (id_song) REFERENCES songs(id_song)
);

-- Table: playlist_collaborator
CREATE TABLE playlist_collaborator (
    id_playlist varchar(255) NOT NULL,
    id_user varchar(255) NOT NULL,
    role varchar(50) NOT NULL,
    created_at timestamp with time zone NOT NULL DEFAULT now(),
    updated_at timestamp with time zone NOT NULL DEFAULT now(),
    CONSTRAINT pk_playlist_collaborator PRIMARY KEY (id_playlist, id_user),
    CONSTRAINT playlist_collaborator_id_playlist_fkey FOREIGN KEY (id_playlist) REFERENCES playlists(id_playlist),
    CONSTRAINT playlist_collaborator_id_user_fkey FOREIGN KEY (id_user) REFERENCES users(id_user)
);

-- Table: user_likedsong
CREATE TABLE user_likedsong (
    id_user varchar(255) NOT NULL,
    id_song varchar(255) NOT NULL,
    created_at timestamp with time zone NOT NULL DEFAULT now(),
    updated_at timestamp with time zone NOT NULL DEFAULT now(),
    CONSTRAINT pk_user_likedsong PRIMARY KEY (id_user, id_song),
    CONSTRAINT user_likedsong_id_song_fkey FOREIGN KEY (id_song) REFERENCES songs(id_song),
    CONSTRAINT user_likedsong_id_user_fkey FOREIGN KEY (id_user) REFERENCES users(id_user)
);

-- Table: user_followartist
CREATE TABLE user_followartist (
    id_user varchar(255) NOT NULL,
    id_artist varchar(255) NOT NULL,
    created_at timestamp with time zone NOT NULL DEFAULT now(),
    updated_at timestamp with time zone NOT NULL DEFAULT now(),
    CONSTRAINT pk_user_followartist PRIMARY KEY (id_user, id_artist),
    CONSTRAINT user_followartist_id_artist_fkey FOREIGN KEY (id_artist) REFERENCES artists(id_artist),
    CONSTRAINT user_followartist_id_user_fkey FOREIGN KEY (id_user) REFERENCES users(id_user)
);

-- Table: user_followplaylist
CREATE TABLE user_followplaylist (
    id_user varchar(255) NOT NULL,
    id_playlist varchar(255) NOT NULL,
    created_at timestamp with time zone NOT NULL DEFAULT now(),
    updated_at timestamp with time zone NOT NULL DEFAULT now(),
    CONSTRAINT pk_user_followplaylist PRIMARY KEY (id_user, id_playlist),
    CONSTRAINT user_followplaylist_id_playlist_fkey FOREIGN KEY (id_playlist) REFERENCES playlists(id_playlist),
    CONSTRAINT user_followplaylist_id_user_fkey FOREIGN KEY (id_user) REFERENCES users(id_user)
);

-- Table: user_followpodcast
CREATE TABLE user_followpodcast (
    id_user varchar(255) NOT NULL,
    id_podcast varchar(255) NOT NULL,
    created_at timestamp with time zone NOT NULL DEFAULT now(),
    updated_at timestamp with time zone NOT NULL DEFAULT now(),
    CONSTRAINT pk_user_followpodcast PRIMARY KEY (id_user, id_podcast),
    CONSTRAINT user_followpodcast_id_podcast_fkey FOREIGN KEY (id_podcast) REFERENCES podcasts(id_podcast),
    CONSTRAINT user_followpodcast_id_user_fkey FOREIGN KEY (id_user) REFERENCES users(id_user)
);

-- Table: user_followuser
CREATE TABLE user_followuser (
    follower_id varchar(255) NOT NULL,
    followed_id varchar(255) NOT NULL,
    created_at timestamp with time zone NOT NULL DEFAULT now(),
    updated_at timestamp with time zone NOT NULL DEFAULT now(),
    CONSTRAINT pk_user_followuser PRIMARY KEY (follower_id, followed_id),
    CONSTRAINT user_followuser_followed_id_fkey FOREIGN KEY (followed_id) REFERENCES users(id_user),
    CONSTRAINT user_followuser_follower_id_fkey FOREIGN KEY (follower_id) REFERENCES users(id_user)
);

-- Table: song_streaming_histories
CREATE TABLE song_streaming_histories (
    id_stream varchar(255) NOT NULL,
    played_at timestamp NOT NULL,
    play_duration integer ,
    id_user varchar(255) NOT NULL,
    id_song varchar(255) NOT NULL,
    created_at timestamp with time zone NOT NULL DEFAULT now(),
    updated_at timestamp with time zone NOT NULL DEFAULT now(),
    CONSTRAINT pk_song_streaming_histories PRIMARY KEY (id_stream),
    CONSTRAINT song_streaminghistory_id_song_fkey FOREIGN KEY (id_song) REFERENCES songs(id_song),
    CONSTRAINT song_streaminghistory_id_user_fkey FOREIGN KEY (id_user) REFERENCES users(id_user),
    CONSTRAINT chk_song_stream_duration CHECK ((play_duration >= 0))
);

-- Table: episode_streaming_histories
CREATE TABLE episode_streaming_histories (
    id_stream varchar(255) NOT NULL,
    played_at timestamp NOT NULL,
    play_duration integer ,
    id_user varchar(255) NOT NULL,
    id_episode varchar(255) NOT NULL,
    created_at timestamp with time zone NOT NULL DEFAULT now(),
    updated_at timestamp with time zone NOT NULL DEFAULT now(),
    CONSTRAINT pk_episode_streaming_histories PRIMARY KEY (id_stream),
    CONSTRAINT episode_streaminghistory_id_episode_fkey FOREIGN KEY (id_episode) REFERENCES episodes(id_episode),
    CONSTRAINT episode_streaminghistory_id_user_fkey FOREIGN KEY (id_user) REFERENCES users(id_user),
    CONSTRAINT chk_episode_stream_duration CHECK ((play_duration >= 0))
);

-- =====================================================
-- DATABASE DML (INSERT DATA)
-- =====================================================

-- Data for: users
INSERT INTO users (id_user, name, email, password, birthday, gender, country, profile_picture, created_at, updated_at) VALUES ('04d8d10e-2b96-4c65-a17a-f0bde9dbe72c', 'brando', 'brando@gmail.com', '$2y$12$mc0gRed1ZXNPDqNppHID5.t7crp1tSscBFO9ZAgDihin6UrVKjLs.', '2026-06-02', 'Female', 'Indonesia', NULL, '2026-06-26 19:54:49.101417+00', '2026-06-26 19:54:49.101417+00');
INSERT INTO users (id_user, name, email, password, birthday, gender, country, profile_picture, created_at, updated_at) VALUES ('7f05287e-c9f6-4ccc-a88e-348930144198', 'Taylor Swift', 'taylor@artist.com', '$2y$12$1aukBDplBX9RH1O2iUy/We.jku9iJMqcEOesP892bjsrcU9ZI7wzG', NULL, NULL, NULL, NULL, '2026-06-26 19:54:49.101417+00', '2026-06-26 19:54:49.101417+00');
INSERT INTO users (id_user, name, email, password, birthday, gender, country, profile_picture, created_at, updated_at) VALUES ('c75b7ee7-b21f-4105-a7f5-d5278bc38283', 'kevin', 'kevin3@gmail.com', '$2y$12$J3/xmuOI5ql7wRJIHFHvTOnxNkq1kzWALCeqJ207mTOJjZWDfmSwS', '1990-09-30', 'Male', 'kolombia', NULL, '2026-06-26 19:54:49.101417+00', '2026-06-26 19:54:49.101417+00');
INSERT INTO users (id_user, name, email, password, birthday, gender, country, profile_picture, created_at, updated_at) VALUES ('83e6d65e-5e46-4019-9bbb-aa1b712e1c48', 'reg', 'rexclaymentg@gmail.com', '$2y$12$PP/IZGPPqDNnYtzfo2oJmuK8xFPf4SC8D4xIymTqrIsG.e5jtaNFS', '2007-09-16', 'Male', 'Indonesia', NULL, '2026-06-26 19:54:49.101417+00', '2026-06-26 19:54:49.101417+00');
INSERT INTO users (id_user, name, email, password, birthday, gender, country, profile_picture, created_at, updated_at) VALUES ('90b0f848-6607-4497-88ff-97ed75c71390', 'icir', 'icir@gmail.com', '$2y$12$fA7wK3xorgJFqKYc3EbqcOIYSYx1aG.KZMRBhK9LDCSlTDAUE1NOO', '1992-06-09', 'Non-binary', 'Indonesia', NULL, '2026-06-26 19:54:49.101417+00', '2026-06-26 19:54:49.101417+00');
INSERT INTO users (id_user, name, email, password, birthday, gender, country, profile_picture, created_at, updated_at) VALUES ('54f4fc17-0c13-4382-a069-a716156e4802', 'james devenn', 'jamesdevenn3010@gmail.com', '$2y$12$vC3lFet/hDGIZiIyIfn88uwF2cPU/XsoEtdGKorDE/4nnQE2eR2qa', '2026-06-16', 'Female', 'kolombia', NULL, '2026-06-26 19:54:49.101417+00', '2026-06-26 19:54:49.101417+00');
INSERT INTO users (id_user, name, email, password, birthday, gender, country, profile_picture, created_at, updated_at) VALUES ('b0846169-50a6-416c-8687-c33a48a06a7c', 'Enrico mosessss', 'enricomosessss@gmail.com', '$2y$12$5szUfkJdGxembPJ/lgwLweLMGVsc2amYOxKYa7ROMU4a7PJGXTbzi', '2026-06-16', 'Female', 'Indonesia', NULL, '2026-06-26 19:54:49.101417+00', '2026-06-26 19:54:49.101417+00');
INSERT INTO users (id_user, name, email, password, birthday, gender, country, profile_picture, created_at, updated_at) VALUES ('691b4806-8271-4857-a588-501c27fff15d', 'lala', 'lal@gmail.com', '$2y$12$N/4GJYlAAqftYIltPIfwTOWRiXEeXgmDqerv26AsxIWy6YxJ0.Q1O', '2003-02-01', 'Non-binary', 'India', NULL, '2026-06-26 19:54:49.101417+00', '2026-06-26 19:54:49.101417+00');
INSERT INTO users (id_user, name, email, password, birthday, gender, country, profile_picture, created_at, updated_at) VALUES ('e0362ced-2fbe-4f25-8cb4-433c8c8a8bdf', 'Enricoo', 'enricomoses@gmail.com', '$2y$12$fUBC.s4sQ7Q7BiMFfeCK6u/XOWpUX1YZc2aNZu23M4Daicc4mfewS', '2026-06-03', 'Male', 'Indonesia', 'https://res.cloudinary.com/dmjjmsha3/image/upload/v1782499937/spotify-clone/avatars/i3jfsznnrhpu8njmqsp0.png', '2026-06-26 19:54:49.101417+00', '2026-06-26 19:54:49.101417+00');

-- Data for: artists
INSERT INTO artists (id_artist, name, email, password, instagram, facebook, wikipedia, x, bio, profile_picture, created_at, updated_at) VALUES ('A_03', 'Hindia', 'hindia@artist.com', 'art123', NULL, NULL, NULL, NULL, 'Indie lokal.', NULL, '2026-06-26 19:54:49.101417+00', '2026-06-26 19:54:49.101417+00');
INSERT INTO artists (id_artist, name, email, password, instagram, facebook, wikipedia, x, bio, profile_picture, created_at, updated_at) VALUES ('A_13', 'Dewa 19', 'dewa19@artist.com', 'art123', 'https://www.instagram.com/officialdewa19/', 'https://www.facebook.com/Dewa19/', 'https://en.wikipedia.org/wiki/Dewa_19', 'https://x.com/de19wa', 'More commonly known as Dewa (and for a time, Down Beat), Dewa 19 was considered by some to be one of Indonesia''s biggest rock acts. Formed in 1986, the band would go through a number of lineup changes -- and the occasional name change, due in part to the original inspiration for the band''s moniker being derived from the founder''s names -- but they maintained their momentum and by 2008 had released a fair number of albums, nearly ten full- lengths, a pair of compilations, and some live work, and built a fanbase that was formidable, to say the least. Their first album, Dewa 19 (the 19 referring to the ages of the founding members at the time of naming the band), released in 1992, was a huge hit, winning the band Best Newcomers and Most Popular Album at the 1993 BASF Awards. While drummers came and went, along with some other roster changes, Dewa 19 pressed ever forward,releasing Format Masa Depan in 1994, and Terbaik Terbaik in 1997. 1999 would see Dewa 19 close out the decade with a hits compilation, The Best of Dewa 19. As the new century dawned, some new issues -- including alleged drug abuse and copyright disagreements -- popped up, but Dewa managed to overcome these hurdles, and in 2005 Dewa 19 released the live DVD Live in Japan, and a best-of DVD compilation. In 2007 the band issued its ninth studio album, Kerajaan Cinta. ~ Chris True, Rovi', 'https://res.cloudinary.com/dmjjmsha3/image/upload/v1781430067/spotify-clone/avatars/ztr0p0l04ptzoshjp7wr.jpg', '2026-06-26 19:54:49.101417+00', '2026-06-26 19:54:49.101417+00');
INSERT INTO artists (id_artist, name, email, password, instagram, facebook, wikipedia, x, bio, profile_picture, created_at, updated_at) VALUES ('A_17', 'Joji', 'joji@artist.com', 'art123', 'https://www.instagram.com/sushitrash', 'https://www.facebook.com/jojikansai', 'https://en.wikipedia.org/wiki/Joji_(musician)', 'https://x.com/sushitrash', 'George Kusunoki Miller (ジョージ・楠木・ミラー, Jōji Kusunoki Mirā), known professionally as Joji and formerly as Filthy Frank and Pink Guy, is a Japanese-Australian singer, songwriter, rapper, and internet personality. His music has been described as a mix between R&B, lo-fi, and trip-hop.', 'https://res.cloudinary.com/dmjjmsha3/image/upload/v1781430270/spotify-clone/avatars/i0y2ntf6pdcvfbhxz7ti.jpg', '2026-06-26 19:54:49.101417+00', '2026-06-26 19:54:49.101417+00');
INSERT INTO artists (id_artist, name, email, password, instagram, facebook, wikipedia, x, bio, profile_picture, created_at, updated_at) VALUES ('A_08', 'Tulus', 'tulus@artist.com', 'art123', NULL, NULL, NULL, NULL, 'Muhammad Tulus Rusydi adalah seorang penyanyi, pencipta lagu, dan arsitek berkebangsaan Indonesia.', 'https://images.unsplash.com/photo-1511671782779-c97d3d27a1d4?q=80&w=400', '2026-06-26 19:54:49.101417+00', '2026-06-26 19:54:49.101417+00');
INSERT INTO artists (id_artist, name, email, password, instagram, facebook, wikipedia, x, bio, profile_picture, created_at, updated_at) VALUES ('A_09', 'Rizky Febrian', 'rizky@artist.com', 'art123', NULL, NULL, NULL, NULL, 'Rizky Febian Adriansyah Sutisna adalah penyanyi-penulis lagu dan aktor berkebangsaan Indonesia.', 'https://images.unsplash.com/photo-1506157786151-b8491531f063?q=80&w=400', '2026-06-26 19:54:49.101417+00', '2026-06-26 19:54:49.101417+00');
INSERT INTO artists (id_artist, name, email, password, instagram, facebook, wikipedia, x, bio, profile_picture, created_at, updated_at) VALUES ('A_10', 'Juicy Luicy', 'juicy@artist.com', 'art123', NULL, NULL, NULL, NULL, 'Juicy Luicy adalah band pop-indie asal Bandung yang terkenal dengan lagu-lagu melankolisnya.', 'https://images.unsplash.com/photo-1493225457124-a1a2a5f5f923?q=80&w=400', '2026-06-26 19:54:49.101417+00', '2026-06-26 19:54:49.101417+00');
INSERT INTO artists (id_artist, name, email, password, instagram, facebook, wikipedia, x, bio, profile_picture, created_at, updated_at) VALUES ('A_11', 'Maudy Ayunda', 'maudy@artist.com', 'art123', NULL, NULL, NULL, NULL, 'Maudy Ayunda adalah penyanyi, aktris, penulis, dan aktivis pendidikan lulusan Oxford dan Stanford.', 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?q=80&w=400', '2026-06-26 19:54:49.101417+00', '2026-06-26 19:54:49.101417+00');
INSERT INTO artists (id_artist, name, email, password, instagram, facebook, wikipedia, x, bio, profile_picture, created_at, updated_at) VALUES ('A_12', 'Tenxi', 'tenxi@artist.com', 'art123', NULL, NULL, NULL, NULL, 'Tenxi adalah grup musik akustik dengan harmoni melodi yang menyentuh hati.', 'https://images.unsplash.com/photo-1486591978090-58e619d37fe7?q=80&w=400', '2026-06-26 19:54:49.101417+00', '2026-06-26 19:54:49.101417+00');
INSERT INTO artists (id_artist, name, email, password, instagram, facebook, wikipedia, x, bio, profile_picture, created_at, updated_at) VALUES ('A_14', 'Billie Eilish', 'billie@artist.com', 'art123', NULL, NULL, NULL, NULL, 'Billie Eilish Pirate Baird O''Connell is an American singer-songwriter who rose to fame in 2015.', 'https://images.unsplash.com/photo-1546707011-49ce8bf4281f?q=80&w=400', '2026-06-26 19:54:49.101417+00', '2026-06-26 19:54:49.101417+00');
INSERT INTO artists (id_artist, name, email, password, instagram, facebook, wikipedia, x, bio, profile_picture, created_at, updated_at) VALUES ('A_01', 'Taylor Swift', 'taylor@artist.com', 'art123', NULL, NULL, NULL, NULL, 'Pop icon... update ppp', 'https://res.cloudinary.com/dmjjmsha3/image/upload/v1781027554/spotify-clone/avatars/cwa4dgivvjxebunwu2tq.png', '2026-06-26 19:54:49.101417+00', '2026-06-26 19:54:49.101417+00');
INSERT INTO artists (id_artist, name, email, password, instagram, facebook, wikipedia, x, bio, profile_picture, created_at, updated_at) VALUES ('A_02', 'Bruno Mars', 'bruno@artist.com', 'art123', 'https://www.instagram.com/brunomars/?hl=en', 'https://www.facebook.com/brunomars/', 'https://en.wikipedia.org/wiki/Bruno_Mars', 'https://x.com/BrunoMars', '16x GRAMMY® winning global superstar, singer, songwriter, multi-instrumentalist, producer, and one of the most-streamed artists.', 'https://res.cloudinary.com/dmjjmsha3/image/upload/v1781268437/spotify-clone/avatars/rpdmzq6a9wshyorfumd3.jpg', '2026-06-26 19:54:49.101417+00', '2026-06-26 19:54:49.101417+00');
INSERT INTO artists (id_artist, name, email, password, instagram, facebook, wikipedia, x, bio, profile_picture, created_at, updated_at) VALUES ('A_04', 'NIKI', 'niki@artist.com', 'art123', NULL, NULL, NULL, NULL, '88rising star.', 'https://res.cloudinary.com/dmjjmsha3/image/upload/v1781277681/spotify-clone/avatars/jkc0tphbdzas0kchftng.jpg', '2026-06-26 19:54:49.101417+00', '2026-06-26 19:54:49.101417+00');
INSERT INTO artists (id_artist, name, email, password, instagram, facebook, wikipedia, x, bio, profile_picture, created_at, updated_at) VALUES ('A_06', 'The Weeknd', 'theweeknd@artist.com', 'art123', NULL, NULL, NULL, NULL, 'Starboy', 'https://res.cloudinary.com/dmjjmsha3/image/upload/v1781279059/spotify-clone/avatars/hjveu0v0kdmm6onibkfy.jpg', '2026-06-26 19:54:49.101417+00', '2026-06-26 19:54:49.101417+00');
INSERT INTO artists (id_artist, name, email, password, instagram, facebook, wikipedia, x, bio, profile_picture, created_at, updated_at) VALUES ('A_05', 'Justin Bieber', 'justin@artist.com', 'art123', NULL, NULL, NULL, NULL, NULL, 'https://res.cloudinary.com/dmjjmsha3/image/upload/v1781279893/spotify-clone/avatars/evmsllwi3xmkgzfgl61e.jpg', '2026-06-26 19:54:49.101417+00', '2026-06-26 19:54:49.101417+00');
INSERT INTO artists (id_artist, name, email, password, instagram, facebook, wikipedia, x, bio, profile_picture, created_at, updated_at) VALUES ('A_07', 'LANY', 'lany@artist.com', 'art123', NULL, NULL, NULL, NULL, NULL, 'https://res.cloudinary.com/dmjjmsha3/image/upload/v1781281286/spotify-clone/avatars/pgv15i2bvkenlmh7v4rx.jpg', '2026-06-26 19:54:49.101417+00', '2026-06-26 19:54:49.101417+00');
INSERT INTO artists (id_artist, name, email, password, instagram, facebook, wikipedia, x, bio, profile_picture, created_at, updated_at) VALUES ('A_15', 'Charlie Puth', 'charlie@artist.com', 'art123', NULL, NULL, NULL, NULL, 'Charles Otto Puth Jr. is an American singer, songwriter, and record producer noted for his perfect pitch.', 'https://images.unsplash.com/photo-1510915361894-db8b60106cb1?q=80&w=400', '2026-06-26 19:54:49.101417+00', '2026-06-26 19:54:49.101417+00');
INSERT INTO artists (id_artist, name, email, password, instagram, facebook, wikipedia, x, bio, profile_picture, created_at, updated_at) VALUES ('A_16', 'Rich Brian', 'richbrian@artist.com', 'art123', 'https://instagram.com/brianimanuel', 'https://facebook.com/iamrichbrian', 'https://en.wikipedia.org/wiki/Rich_Brian', 'https://x.com/richbrian', 'On Rich Brian''s first full-length album since 2019, Where is My Head?, the Indonesian-born artist redefines and coheres his vision in a staggering way. He''s making the most vulnerable and authentic music of his life, in turn deepening the connection with his ever growing army of devoted fans. With Where Is My Head?, Rich Brian''s definition of success now means something different than ever before. He''s had the hits. Now? He wants something more. Brian decided to handle most of the performances himself, honing in on an analog sound thanks to his intense immersion learning to play synthesizers and keyboards. He blends his unimpeachable rap delivery with knotty narratives and confident singing performances. It was a bold switch-up from Brian, who could have coasted on his success last decade. He
became the first Asian artist to reach number one on the iTunes Hip- Hop charts. Who wouldn''t want to coast on that success? The accolades are nice, but Rich Brian wants something more with Where Is My Head? Brian has finally discovered himself. His priority is simple: How can he make art that is honest? He asks: "How can I continue to make music that pushes me to be the best I can be?" On Where Is My Head? Rich Brian answers that question on each and every track.', 'https://res.cloudinary.com/dmjjmsha3/image/upload/v1781428508/spotify-clone/avatars/k51ydmxwwux4ipxq1fom.jpg', '2026-06-26 19:54:49.101417+00', '2026-06-26 19:54:49.101417+00');

-- Data for: albums
INSERT INTO albums (id_album, title, release, cover_image, created_at, updated_at) VALUES ('ALB_01', '1989 (Taylor Version)', '2023-10-27', 'https://res.cloudinary.com/dmjjmsha3/image/upload/v1781025051/spotify-clone/albums/btk9lo6wsf95rln8trtb.jpg', '2026-06-26 19:54:49.101417+00', '2026-06-26 19:54:49.101417+00');
INSERT INTO albums (id_album, title, release, cover_image, created_at, updated_at) VALUES ('c625d477-b844-4430-8040-32b43375bf56', 'album est', '2026-06-11', 'https://res.cloudinary.com/dmjjmsha3/image/upload/v1781240392/spotify-clone/albums/xutstq6ytzs5w9eeqbcz.png', '2026-06-26 19:54:49.101417+00', '2026-06-26 19:54:49.101417+00');
INSERT INTO albums (id_album, title, release, cover_image, created_at, updated_at) VALUES ('0911b737-157c-4bfd-bb56-45ca524a1190', 'The Romantic', '2026-02-27', 'https://res.cloudinary.com/dmjjmsha3/image/upload/v1781266643/spotify-clone/albums/pjmj9ovltwr6ao3w1yev.jpg', '2026-06-26 19:54:49.101417+00', '2026-06-26 19:54:49.101417+00');
INSERT INTO albums (id_album, title, release, cover_image, created_at, updated_at) VALUES ('d10c3ed3-55a9-44a0-84f4-89a4c99e9d5c', '24K Magic', '2016-10-02', 'https://res.cloudinary.com/dmjjmsha3/image/upload/v1781266853/spotify-clone/albums/cw86sqzp4cw9kvnoubje.jpg', '2026-06-26 19:54:49.101417+00', '2026-06-26 19:54:49.101417+00');
INSERT INTO albums (id_album, title, release, cover_image, created_at, updated_at) VALUES ('a119e13c-580b-4ddb-b2ef-7a1341238464', 'Unorthodox Jukebox', '2012-05-30', 'https://res.cloudinary.com/dmjjmsha3/image/upload/v1781266910/spotify-clone/albums/aulcrmua02jflf1r9u5y.jpg', '2026-06-26 19:54:49.101417+00', '2026-06-26 19:54:49.101417+00');
INSERT INTO albums (id_album, title, release, cover_image, created_at, updated_at) VALUES ('2c468183-bcfa-4406-8b65-5febdfa6237f', 'Doo-Wops & Hooligans', '2010-10-04', 'https://res.cloudinary.com/dmjjmsha3/image/upload/v1781267504/spotify-clone/albums/mq7avcyzvwi985mqv41b.jpg', '2026-06-26 19:54:49.101417+00', '2026-06-26 19:54:49.101417+00');
INSERT INTO albums (id_album, title, release, cover_image, created_at, updated_at) VALUES ('ALB_03', 'Moonchild', '2020-09-10', 'https://res.cloudinary.com/dmjjmsha3/image/upload/v1781276727/spotify-clone/albums/qcbv5shyvu8ckdag0byy.png', '2026-06-26 19:54:49.101417+00', '2026-06-26 19:54:49.101417+00');
INSERT INTO albums (id_album, title, release, cover_image, created_at, updated_at) VALUES ('236a974f-fd58-4c5c-9e17-5711d43fcf79', 'Nicole', '2022-08-11', 'https://res.cloudinary.com/dmjjmsha3/image/upload/v1781276784/spotify-clone/albums/ouaeqxum6tdwq83ep8qx.jpg', '2026-06-26 19:54:49.101417+00', '2026-06-26 19:54:49.101417+00');
INSERT INTO albums (id_album, title, release, cover_image, created_at, updated_at) VALUES ('7c54ead0-43de-48e6-bfcc-b4a59e3640af', 'Buzz', '2024-08-09', 'https://res.cloudinary.com/dmjjmsha3/image/upload/v1781276826/spotify-clone/albums/zy662mfopl8sjmqunluz.jpg', '2026-06-26 19:54:49.101417+00', '2026-06-26 19:54:49.101417+00');
INSERT INTO albums (id_album, title, release, cover_image, created_at, updated_at) VALUES ('ee1d1302-8a7b-4281-8299-136491fadc4c', 'Live at The Wiltern', '2023-05-12', 'https://res.cloudinary.com/dmjjmsha3/image/upload/v1781276896/spotify-clone/albums/yw1besytwllwsyld7hwp.jpg', '2026-06-26 19:54:49.101417+00', '2026-06-26 19:54:49.101417+00');
INSERT INTO albums (id_album, title, release, cover_image, created_at, updated_at) VALUES ('6b494f68-6fab-4a61-8522-f1c80d6dfac1', 'After Hours', '2020-05-23', 'https://res.cloudinary.com/dmjjmsha3/image/upload/v1781278176/spotify-clone/albums/xqadi6g6obdwkxr65k0t.jpg', '2026-06-26 19:54:49.101417+00', '2026-06-26 19:54:49.101417+00');
INSERT INTO albums (id_album, title, release, cover_image, created_at, updated_at) VALUES ('2c31f2e7-fcbf-4b49-bc06-6bc91ab27703', 'Hurry Up Tomorrow', '2025-01-31', 'https://res.cloudinary.com/dmjjmsha3/image/upload/v1781278205/spotify-clone/albums/vdx8ishobcujlglzdkrd.png', '2026-06-26 19:54:49.101417+00', '2026-06-26 19:54:49.101417+00');
INSERT INTO albums (id_album, title, release, cover_image, created_at, updated_at) VALUES ('42099e1b-fdca-4bfa-bfc3-2d38f12983f7', 'Starboy', '2016-11-25', 'https://res.cloudinary.com/dmjjmsha3/image/upload/v1781278238/spotify-clone/albums/hqdpqopi9g7cripueibw.jpg', '2026-06-26 19:54:49.101417+00', '2026-06-26 19:54:49.101417+00');
INSERT INTO albums (id_album, title, release, cover_image, created_at, updated_at) VALUES ('86cf72ae-9cbb-490a-8819-00b9ef37bd07', 'House Of Balloons', '2011-03-21', 'https://res.cloudinary.com/dmjjmsha3/image/upload/v1781278386/spotify-clone/albums/ghtuxscpz1wzfqtrgjwt.png', '2026-06-26 19:54:49.101417+00', '2026-06-26 19:54:49.101417+00');
INSERT INTO albums (id_album, title, release, cover_image, created_at, updated_at) VALUES ('2fcd55d3-c314-4171-a8d9-cbbd747a88a0', 'My Worlds', '2010-03-22', 'https://res.cloudinary.com/dmjjmsha3/image/upload/v1781279366/spotify-clone/albums/brdhocx95ufhfd87c0tz.jpg', '2026-06-26 19:54:49.101417+00', '2026-06-26 19:54:49.101417+00');
INSERT INTO albums (id_album, title, release, cover_image, created_at, updated_at) VALUES ('d79b6312-38ad-4f7e-b605-0e65cab1b9ce', 'Believe', '2012-06-15', 'https://res.cloudinary.com/dmjjmsha3/image/upload/v1781279397/spotify-clone/albums/evw3zozxwk40oqnmqbfv.jpg', '2026-06-26 19:54:49.101417+00', '2026-06-26 19:54:49.101417+00');
INSERT INTO albums (id_album, title, release, cover_image, created_at, updated_at) VALUES ('dddd1ab3-cadd-45ec-af3d-cfa17ed50310', 'Purpose', '2020-06-25', 'https://res.cloudinary.com/dmjjmsha3/image/upload/v1781279439/spotify-clone/albums/stu6nkgcx7nlhkqgdod6.jpg', '2026-06-26 19:54:49.101417+00', '2026-06-26 19:54:49.101417+00');
INSERT INTO albums (id_album, title, release, cover_image, created_at, updated_at) VALUES ('7b2946ce-f644-4047-8197-dc032841b29a', 'LANY', '2017-07-30', 'https://res.cloudinary.com/dmjjmsha3/image/upload/v1781280607/spotify-clone/albums/lyxyjtype6ntjh2kedr9.png', '2026-06-26 19:54:49.101417+00', '2026-06-26 19:54:49.101417+00');
INSERT INTO albums (id_album, title, release, cover_image, created_at, updated_at) VALUES ('d3e65927-7627-421b-b328-d047dc2f30d1', 'Mama''s Boy', '2020-10-02', 'https://res.cloudinary.com/dmjjmsha3/image/upload/v1781280652/spotify-clone/albums/kopcda39iqqo2wxcpspy.jpg', '2026-06-26 19:54:49.101417+00', '2026-06-26 19:54:49.101417+00');
INSERT INTO albums (id_album, title, release, cover_image, created_at, updated_at) VALUES ('9fc88563-45fe-4a3a-a580-77b1bf3d0505', 'A Beautiful Blur', '2023-09-29', 'https://res.cloudinary.com/dmjjmsha3/image/upload/v1781280698/spotify-clone/albums/klepbczmuq83fg0fhxn7.jpg', '2026-06-26 19:54:49.101417+00', '2026-06-26 19:54:49.101417+00');
INSERT INTO albums (id_album, title, release, cover_image, created_at, updated_at) VALUES ('076b5c2d-49c8-4e2d-a98f-4476ca0d58cf', 'Gg bb xx', '2021-09-03', 'https://res.cloudinary.com/dmjjmsha3/image/upload/v1781280750/spotify-clone/albums/jfd54nenotuucoqac4se.png', '2026-06-26 19:54:49.101417+00', '2026-06-26 19:54:49.101417+00');
INSERT INTO albums (id_album, title, release, cover_image, created_at, updated_at) VALUES ('ALB_TULUS', 'Manusia', '2022-03-03', 'https://images.unsplash.com/photo-1470225620780-dba8ba36b745?q=80&w=400', '2026-06-28 18:14:14.157497+00', '2026-06-28 18:14:14.157497+00');
INSERT INTO albums (id_album, title, release, cover_image, created_at, updated_at) VALUES ('ALB_RIZKY', 'Jejak', '2018-11-23', 'https://images.unsplash.com/photo-1514525253161-7a46d19cd819?q=80&w=400', '2026-06-28 18:14:14.157497+00', '2026-06-28 18:14:14.157497+00');
INSERT INTO albums (id_album, title, release, cover_image, created_at, updated_at) VALUES ('ALB_JUICY', 'Sentimental', '2020-11-06', 'https://images.unsplash.com/photo-1508700115892-45ecd05ae2ad?q=80&w=400', '2026-06-28 18:14:14.157497+00', '2026-06-28 18:14:14.157497+00');
INSERT INTO albums (id_album, title, release, cover_image, created_at, updated_at) VALUES ('ALB_MAUDY', 'Oxygen', '2018-02-15', 'https://images.unsplash.com/photo-1510915361894-db8b60106cb1?q=80&w=400', '2026-06-28 18:14:14.157497+00', '2026-06-28 18:14:14.157497+00');
INSERT INTO albums (id_album, title, release, cover_image, created_at, updated_at) VALUES ('ALB_TENXI', 'Harmoni Akustik', '2024-05-10', 'https://images.unsplash.com/photo-1460036521480-ff49c08c2781?q=80&w=400', '2026-06-28 18:14:14.157497+00', '2026-06-28 18:14:14.157497+00');
INSERT INTO albums (id_album, title, release, cover_image, created_at, updated_at) VALUES ('ALB_DEWA', 'Bintang Lima', '2000-04-30', 'https://images.unsplash.com/photo-1498038432885-c6f3f1b912ee?q=80&w=400', '2026-06-28 18:14:14.157497+00', '2026-06-28 18:14:14.157497+00');
INSERT INTO albums (id_album, title, release, cover_image, created_at, updated_at) VALUES ('ALB_BILLIE', 'Hit Me Hard and Soft', '2024-05-17', 'https://images.unsplash.com/photo-1470229722913-7c0e2dbbafd3?q=80&w=400', '2026-06-28 18:14:14.157497+00', '2026-06-28 18:14:14.157497+00');
INSERT INTO albums (id_album, title, release, cover_image, created_at, updated_at) VALUES ('ALB_CHARLIE', 'CHARLIE', '2022-10-07', 'https://images.unsplash.com/photo-1504711434969-e33886168f5c?q=80&w=400', '2026-06-28 18:14:14.157497+00', '2026-06-28 18:14:14.157497+00');
INSERT INTO albums (id_album, title, release, cover_image, created_at, updated_at) VALUES ('ALB_BRIAN', 'The Sailor', '2019-07-26', 'https://images.unsplash.com/photo-1502224562085-6dfe2f2eafc6?q=80&w=400', '2026-06-28 18:14:14.157497+00', '2026-06-28 18:14:14.157497+00');
INSERT INTO albums (id_album, title, release, cover_image, created_at, updated_at) VALUES ('ALB_JOJI', 'Smithereens', '2022-11-04', 'https://images.unsplash.com/photo-1508700115892-45ecd05ae2ad?q=80&w=400', '2026-06-28 18:14:14.157497+00', '2026-06-28 18:14:14.157497+00');
INSERT INTO albums (id_album, title, release, cover_image, created_at, updated_at) VALUES ('ALB_02', 'Menari Dengan Bayangan', '2019-11-29', 'https://res.cloudinary.com/dmjjmsha3/image/upload/v1782671530/spotify-clone/albums/e2md0zczqbljrk39bjli.png', '2026-06-26 19:54:49.101417+00', '2026-06-26 19:54:49.101417+00');

-- Data for: playlist_categories
INSERT INTO playlist_categories (id_playlist_category, name, cover_image, icon, created_at, updated_at) VALUES ('PC_01', 'Pop', 'https://images.unsplash.com/photo-1514525253161-7a46d19cd819?auto=format&fit=crop&w=1280&h=400&q=80', 'https://images.unsplash.com/photo-1514525253161-7a46d19cd819?auto=format&fit=crop&w=300&h=300&q=80', '2026-06-26 19:54:49.101417+00', '2026-06-26 19:54:49.101417+00');
INSERT INTO playlist_categories (id_playlist_category, name, cover_image, icon, created_at, updated_at) VALUES ('PC_02', 'Indo Pop', 'https://images.unsplash.com/photo-1526478806334-5fd488fcaabc?auto=format&fit=crop&w=1280&h=400&q=80', 'https://images.unsplash.com/photo-1526478806334-5fd488fcaabc?auto=format&fit=crop&w=300&h=300&q=80', '2026-06-26 19:54:49.101417+00', '2026-06-26 19:54:49.101417+00');
INSERT INTO playlist_categories (id_playlist_category, name, cover_image, icon, created_at, updated_at) VALUES ('PC_03', 'R&B / Soul', 'https://images.unsplash.com/photo-1508700115892-45ecd05ae2ad?auto=format&fit=crop&w=1280&h=400&q=80', 'https://images.unsplash.com/photo-1508700115892-45ecd05ae2ad?auto=format&fit=crop&w=300&h=300&q=80', '2026-06-26 19:54:49.101417+00', '2026-06-26 19:54:49.101417+00');
INSERT INTO playlist_categories (id_playlist_category, name, cover_image, icon, created_at, updated_at) VALUES ('PC_04', 'Hip-Hop / Rap', 'https://images.unsplash.com/photo-1502224562085-6dfe2f2eafc6?auto=format&fit=crop&w=1280&h=400&q=80', 'https://images.unsplash.com/photo-1502224562085-6dfe2f2eafc6?auto=format&fit=crop&w=300&h=300&q=80', '2026-06-26 19:54:49.101417+00', '2026-06-26 19:54:49.101417+00');
INSERT INTO playlist_categories (id_playlist_category, name, cover_image, icon, created_at, updated_at) VALUES ('PC_05', 'Indie / Alternative', 'https://images.unsplash.com/photo-1493225457124-a1a2a5f5f923?auto=format&fit=crop&w=1280&h=400&q=80', 'https://images.unsplash.com/photo-1493225457124-a1a2a5f5f923?auto=format&fit=crop&w=300&h=300&q=80', '2026-06-26 19:54:49.101417+00', '2026-06-26 19:54:49.101417+00');
INSERT INTO playlist_categories (id_playlist_category, name, cover_image, icon, created_at, updated_at) VALUES ('PC_06', 'Rock', 'https://images.unsplash.com/photo-1498038432885-c6f3f1b912ee?auto=format&fit=crop&w=1280&h=400&q=80', 'https://images.unsplash.com/photo-1498038432885-c6f3f1b912ee?auto=format&fit=crop&w=300&h=300&q=80', '2026-06-26 19:54:49.101417+00', '2026-06-26 19:54:49.101417+00');
INSERT INTO playlist_categories (id_playlist_category, name, cover_image, icon, created_at, updated_at) VALUES ('PC_07', 'Acoustic / Chill', 'https://images.unsplash.com/photo-1510915361894-db8b60106cb1?auto=format&fit=crop&w=1280&h=400&q=80', 'https://images.unsplash.com/photo-1510915361894-db8b60106cb1?auto=format&fit=crop&w=300&h=300&q=80', '2026-06-26 19:54:49.101417+00', '2026-06-26 19:54:49.101417+00');
INSERT INTO playlist_categories (id_playlist_category, name, cover_image, icon, created_at, updated_at) VALUES ('PC_08', 'K-Pop', 'https://images.unsplash.com/photo-1546707011-49ce8bf4281f?auto=format&fit=crop&w=1280&h=400&q=80', 'https://images.unsplash.com/photo-1546707011-49ce8bf4281f?auto=format&fit=crop&w=300&h=300&q=80', '2026-06-26 19:54:49.101417+00', '2026-06-26 19:54:49.101417+00');
INSERT INTO playlist_categories (id_playlist_category, name, cover_image, icon, created_at, updated_at) VALUES ('PC_09', 'EDM / Dance', 'https://images.unsplash.com/photo-1470229722913-7c0e2dbbafd3?auto=format&fit=crop&w=1280&h=400&q=80', 'https://images.unsplash.com/photo-1470229722913-7c0e2dbbafd3?auto=format&fit=crop&w=300&h=300&q=80', '2026-06-26 19:54:49.101417+00', '2026-06-26 19:54:49.101417+00');
INSERT INTO playlist_categories (id_playlist_category, name, cover_image, icon, created_at, updated_at) VALUES ('PC_10', 'Nostalgia', 'https://images.unsplash.com/photo-1460036521480-ff49c08c2781?auto=format&fit=crop&w=1280&h=400&q=80', 'https://images.unsplash.com/photo-1460036521480-ff49c08c2781?auto=format&fit=crop&w=300&h=300&q=80', '2026-06-26 19:54:49.101417+00', '2026-06-26 19:54:49.101417+00');

-- Data for: podcast_categories
INSERT INTO podcast_categories (id_podcast_category, name, cover_image, icon, created_at, updated_at) VALUES ('PDC_01', 'Comedy', 'https://images.unsplash.com/photo-1585699324551-f6c309eedeca?auto=format&fit=crop&w=1280&h=400&q=80', 'https://images.unsplash.com/photo-1585699324551-f6c309eedeca?auto=format&fit=crop&w=300&h=300&q=80', '2026-06-26 19:54:49.101417+00', '2026-06-26 19:54:49.101417+00');
INSERT INTO podcast_categories (id_podcast_category, name, cover_image, icon, created_at, updated_at) VALUES ('PDC_02', 'True Crime', 'https://images.unsplash.com/photo-1506506200949-df8644f002d1?auto=format&fit=crop&w=1280&h=400&q=80', 'https://images.unsplash.com/photo-1506506200949-df8644f002d1?auto=format&fit=crop&w=300&h=300&q=80', '2026-06-26 19:54:49.101417+00', '2026-06-26 19:54:49.101417+00');
INSERT INTO podcast_categories (id_podcast_category, name, cover_image, icon, created_at, updated_at) VALUES ('PDC_03', 'Horror & Paranormal', 'https://images.unsplash.com/photo-1505635552518-45e0544f129d?auto=format&fit=crop&w=1280&h=400&q=80', 'https://images.unsplash.com/photo-1505635552518-45e0544f129d?auto=format&fit=crop&w=300&h=300&q=80', '2026-06-26 19:54:49.101417+00', '2026-06-26 19:54:49.101417+00');
INSERT INTO podcast_categories (id_podcast_category, name, cover_image, icon, created_at, updated_at) VALUES ('PDC_04', 'Education', 'https://images.unsplash.com/photo-1497633762265-9d179a990aa6?auto=format&fit=crop&w=1280&h=400&q=80', 'https://images.unsplash.com/photo-1497633762265-9d179a990aa6?auto=format&fit=crop&w=300&h=300&q=80', '2026-06-26 19:54:49.101417+00', '2026-06-26 19:54:49.101417+00');
INSERT INTO podcast_categories (id_podcast_category, name, cover_image, icon, created_at, updated_at) VALUES ('PDC_05', 'News & Politics', 'https://images.unsplash.com/photo-1504711434969-e33886168f5c?auto=format&fit=crop&w=1280&h=400&q=80', 'https://images.unsplash.com/photo-1504711434969-e33886168f5c?auto=format&fit=crop&w=300&h=300&q=80', '2026-06-26 19:54:49.101417+00', '2026-06-26 19:54:49.101417+00');
INSERT INTO podcast_categories (id_podcast_category, name, cover_image, icon, created_at, updated_at) VALUES ('PDC_06', 'Business & Tech', 'https://images.unsplash.com/photo-1486406146926-c627a92ad1ab?auto=format&fit=crop&w=1280&h=400&q=80', 'https://images.unsplash.com/photo-1486406146926-c627a92ad1ab?auto=format&fit=crop&w=300&h=300&q=80', '2026-06-26 19:54:49.101417+00', '2026-06-26 19:54:49.101417+00');
INSERT INTO podcast_categories (id_podcast_category, name, cover_image, icon, created_at, updated_at) VALUES ('PDC_07', 'Sports', 'https://images.unsplash.com/photo-1461896836934-ffe607ba8211?auto=format&fit=crop&w=1280&h=400&q=80', 'https://images.unsplash.com/photo-1461896836934-ffe607ba8211?auto=format&fit=crop&w=300&h=300&q=80', '2026-06-26 19:54:49.101417+00', '2026-06-26 19:54:49.101417+00');
INSERT INTO podcast_categories (id_podcast_category, name, cover_image, icon, created_at, updated_at) VALUES ('PDC_08', 'Health & Fitness', 'https://images.unsplash.com/photo-1506126613632-00a12e2e50bb?auto=format&fit=crop&w=1280&h=400&q=80', 'https://images.unsplash.com/photo-1506126613632-00a12e2e50bb?auto=format&fit=crop&w=300&h=300&q=80', '2026-06-26 19:54:49.101417+00', '2026-06-26 19:54:49.101417+00');
INSERT INTO podcast_categories (id_podcast_category, name, cover_image, icon, created_at, updated_at) VALUES ('PDC_09', 'Society & Culture', 'https://images.unsplash.com/photo-1511632765486-a53c43376b32?auto=format&fit=crop&w=1280&h=400&q=80', 'https://images.unsplash.com/photo-1511632765486-a53c43376b32?auto=format&fit=crop&w=300&h=300&q=80', '2026-06-26 19:54:49.101417+00', '2026-06-26 19:54:49.101417+00');
INSERT INTO podcast_categories (id_podcast_category, name, cover_image, icon, created_at, updated_at) VALUES ('PDC_10', 'Fiction & Storytelling', 'https://images.unsplash.com/photo-1478720568478-884a595a0658?auto=format&fit=crop&w=1280&h=400&q=80', 'https://images.unsplash.com/photo-1478720568478-884a595a0658?auto=format&fit=crop&w=300&h=300&q=80', '2026-06-26 19:54:49.101417+00', '2026-06-26 19:54:49.101417+00');

-- Data for: concerts
INSERT INTO concerts (id_concert, location, location_url, event_at, ticket_url, created_at, updated_at) VALUES ('03c99390-c98d-457a-84e8-f12b34d139de', 'Jakarta', 'https://maps.google.com', '2026-06-10 08:11:00', 'https://google.com', '2026-06-26 19:54:49.101417+00', '2026-06-26 19:54:49.101417+00');
INSERT INTO concerts (id_concert, location, location_url, event_at, ticket_url, created_at, updated_at) VALUES ('ebb7bd7b-bfb2-4e89-9763-c26207684a74', 'Jakarta', 'https://maps.google.com', '2026-06-13 01:12:00', 'https://google.com', '2026-06-26 19:54:49.101417+00', '2026-06-26 19:54:49.101417+00');
INSERT INTO concerts (id_concert, location, location_url, event_at, ticket_url, created_at, updated_at) VALUES ('2811e691-103e-46d9-b4ab-9d50d98d2195', 'Hard Rock Stadium', 'https://maps.app.goo.gl/q1Kj4uPfR9LLetgVA', '2026-09-19 19:00:00', 'https://www.hardrockstadium.com/events/bruno-mars-the-romantic-tour/', '2026-06-26 19:54:49.101417+00', '2026-06-26 19:54:49.101417+00');
INSERT INTO concerts (id_concert, location, location_url, event_at, ticket_url, created_at, updated_at) VALUES ('ed8e547d-8054-4d01-bb70-37139cfd8415', 'Echo Music Park', 'https://ditu.amap.com/regeo?spm=a2oeg.project.projectinfo.daddress.541f10eaEKf9i7&lng=113.373663&lat=23.052805&name=%E5%B9%BF%E5%B7%9E%E5%B8%82&src=damai&callnative=0&platform=pc', '2026-07-10 20:00:00', 'https://detail.damai.cn/item.htm?id=1055300653034', '2026-06-26 19:54:49.101417+00', '2026-06-26 19:54:49.101417+00');
INSERT INTO concerts (id_concert, location, location_url, event_at, ticket_url, created_at, updated_at) VALUES ('969c390f-806f-409f-9090-e2787a3197c8', 'Jakarta International Stadium', NULL, '2026-09-26 20:00:00', 'https://www.stubhub.com/id/the-weeknd-jakarta-tickets-9-26-2026/event/161116275/?lt=-6.17511&lg=106.8650395&quantity=2', '2026-06-26 19:54:49.101417+00', '2026-06-26 19:54:49.101417+00');
INSERT INTO concerts (id_concert, location, location_url, event_at, ticket_url, created_at, updated_at) VALUES ('8cea2e8c-b560-47df-a598-825290e7381b', 'Taipei Arena', NULL, '2026-09-26 19:00:00', 'https://www.bandsintown.com/e/108104192?app_id=ggl_feed&came_from=289&utm_medium=web&utm_source=ggl_feed&utm_campaign=event&source=ea', '2026-06-26 19:54:49.101417+00', '2026-06-26 19:54:49.101417+00');
INSERT INTO concerts (id_concert, location, location_url, event_at, ticket_url, created_at, updated_at) VALUES ('92d68c30-acb3-4cf5-bd50-e2c3058a576f', 'Kintex Exhibition 2', NULL, '2026-10-03 19:00:00', 'https://www.bandsintown.com/e/108104222?app_id=ggl_feed&came_from=289&utm_medium=web&utm_source=ggl_feed&utm_campaign=event&source=ea', '2026-06-26 19:54:49.101417+00', '2026-06-26 19:54:49.101417+00');
INSERT INTO concerts (id_concert, location, location_url, event_at, ticket_url, created_at, updated_at) VALUES ('9f19d270-3f3f-40da-a583-74543c06f258', 'Hard Rock Stadium', NULL, '2026-11-04 19:30:00', 'https://www.ticketmaster.com/justin-bieber-tickets/artist/1369961', '2026-06-26 19:54:49.101417+00', '2026-06-26 19:54:49.101417+00');
INSERT INTO concerts (id_concert, location, location_url, event_at, ticket_url, created_at, updated_at) VALUES ('CON_TULUS', 'Istora Senayan, Jakarta', 'https://maps.google.com', '2026-08-15 19:30:00', 'https://tix.id', '2026-06-28 18:14:14.157497+00', '2026-06-28 18:14:14.157497+00');
INSERT INTO concerts (id_concert, location, location_url, event_at, ticket_url, created_at, updated_at) VALUES ('CON_RIZKY', 'Dago Tea House, Bandung', 'https://maps.google.com', '2026-08-20 20:00:00', 'https://loket.com', '2026-06-28 18:14:14.157497+00', '2026-06-28 18:14:14.157497+00');
INSERT INTO concerts (id_concert, location, location_url, event_at, ticket_url, created_at, updated_at) VALUES ('CON_DEWA', 'Stadion Utama Gelora Bung Karno, Jakarta', 'https://maps.google.com', '2026-09-05 19:00:00', 'https://dewa19tickets.com', '2026-06-28 18:14:14.157497+00', '2026-06-28 18:14:14.157497+00');
INSERT INTO concerts (id_concert, location, location_url, event_at, ticket_url, created_at, updated_at) VALUES ('CON_BILLIE', 'Singapore National Stadium', 'https://maps.google.com', '2026-10-12 20:00:00', 'https://ticketmaster.sg', '2026-06-28 18:14:14.157497+00', '2026-06-28 18:14:14.157497+00');
INSERT INTO concerts (id_concert, location, location_url, event_at, ticket_url, created_at, updated_at) VALUES ('CON_JOJI', 'JIExpo Kemayoran, Jakarta', 'https://maps.google.com', '2026-11-22 19:00:00', 'https://loket.com', '2026-06-28 18:14:14.157497+00', '2026-06-28 18:14:14.157497+00');

-- Data for: songs
INSERT INTO songs (id_song, title, duration, id_album, audio_url, created_at, updated_at) VALUES ('S_999', 'Lagu Sukses', '210', NULL, NULL, '2026-06-26 19:54:49.101417+00', '2026-06-26 19:54:49.101417+00');
INSERT INTO songs (id_song, title, duration, id_album, audio_url, created_at, updated_at) VALUES ('212ede59-e53c-4f26-bfdb-c62599aabbb6', 'edamame', '141', NULL, 'https://res.cloudinary.com/dmjjmsha3/video/upload/v1781429321/spotify-clone/audio/songs/buekgzdhzeuaissjczna.mp3', '2026-06-26 19:54:49.101417+00', '2026-06-26 19:54:49.101417+00');
INSERT INTO songs (id_song, title, duration, id_album, audio_url, created_at, updated_at) VALUES ('S_01', 'Blank Space', '231', 'ALB_01', 'https://www.soundhelix.com/examples/mp3/SoundHelix-Song-1.mp3', '2026-06-26 19:54:49.101417+00', '2026-06-26 19:54:49.101417+00');
INSERT INTO songs (id_song, title, duration, id_album, audio_url, created_at, updated_at) VALUES ('S_02', 'Style', '231', 'ALB_01', 'https://www.soundhelix.com/examples/mp3/SoundHelix-Song-2.mp3', '2026-06-26 19:54:49.101417+00', '2026-06-26 19:54:49.101417+00');
INSERT INTO songs (id_song, title, duration, id_album, audio_url, created_at, updated_at) VALUES ('S_03', 'Evaluasi', '205', 'ALB_02', 'https://www.soundhelix.com/examples/mp3/SoundHelix-Song-3.mp3', '2026-06-26 19:54:49.101417+00', '2026-06-26 19:54:49.101417+00');
INSERT INTO songs (id_song, title, duration, id_album, audio_url, created_at, updated_at) VALUES ('S_04', 'Every Summertime', '198', 'ALB_03', 'https://www.soundhelix.com/examples/mp3/SoundHelix-Song-4.mp3', '2026-06-26 19:54:49.101417+00', '2026-06-26 19:54:49.101417+00');
INSERT INTO songs (id_song, title, duration, id_album, audio_url, created_at, updated_at) VALUES ('b88d8951-d233-42e4-93c8-62fcd79cf975', 'you!', '273', 'd3e65927-7627-421b-b328-d047dc2f30d1', 'https://www.soundhelix.com/examples/mp3/SoundHelix-Song-5.mp3', '2026-06-26 19:54:49.101417+00', '2026-06-26 19:54:49.101417+00');
INSERT INTO songs (id_song, title, duration, id_album, audio_url, created_at, updated_at) VALUES ('55397a89-f0b0-4c3b-a8b8-f21c7526f5f9', 'anything 4 u', '195', 'd3e65927-7627-421b-b328-d047dc2f30d1', 'https://www.soundhelix.com/examples/mp3/SoundHelix-Song-6.mp3', '2026-06-26 19:54:49.101417+00', '2026-06-26 19:54:49.101417+00');
INSERT INTO songs (id_song, title, duration, id_album, audio_url, created_at, updated_at) VALUES ('S_05', 'Risk It All', '208', '0911b737-157c-4bfd-bb56-45ca524a1190', 'https://www.soundhelix.com/examples/mp3/SoundHelix-Song-7.mp3', '2026-06-26 19:54:49.101417+00', '2026-06-26 19:54:49.101417+00');
INSERT INTO songs (id_song, title, duration, id_album, audio_url, created_at, updated_at) VALUES ('86da840a-ac14-4400-9a01-1896c9544fac', 'Cha Cha Cha', '237', '0911b737-157c-4bfd-bb56-45ca524a1190', 'https://www.soundhelix.com/examples/mp3/SoundHelix-Song-8.mp3', '2026-06-26 19:54:49.101417+00', '2026-06-26 19:54:49.101417+00');
INSERT INTO songs (id_song, title, duration, id_album, audio_url, created_at, updated_at) VALUES ('d9b753ab-1077-4f5f-8f8a-9bf9844cf194', '24K Magic', '227', 'd10c3ed3-55a9-44a0-84f4-89a4c99e9d5c', 'https://www.soundhelix.com/examples/mp3/SoundHelix-Song-9.mp3', '2026-06-26 19:54:49.101417+00', '2026-06-26 19:54:49.101417+00');
INSERT INTO songs (id_song, title, duration, id_album, audio_url, created_at, updated_at) VALUES ('9d747333-b9dc-4bbe-b805-9322e41b6b0f', 'Versace On The Floor', '262', 'd10c3ed3-55a9-44a0-84f4-89a4c99e9d5c', 'https://www.soundhelix.com/examples/mp3/SoundHelix-Song-10.mp3', '2026-06-26 19:54:49.101417+00', '2026-06-26 19:54:49.101417+00');
INSERT INTO songs (id_song, title, duration, id_album, audio_url, created_at, updated_at) VALUES ('ce5edf44-9e71-40a8-b4ba-0a4cdfb98777', 'Locked Out of Heaven', '235', 'a119e13c-580b-4ddb-b2ef-7a1341238464', 'https://www.soundhelix.com/examples/mp3/SoundHelix-Song-11.mp3', '2026-06-26 19:54:49.101417+00', '2026-06-26 19:54:49.101417+00');
INSERT INTO songs (id_song, title, duration, id_album, audio_url, created_at, updated_at) VALUES ('7dc27706-3fc2-4aad-9269-2dd6c4461874', 'Treasure', '192', 'a119e13c-580b-4ddb-b2ef-7a1341238464', 'https://www.soundhelix.com/examples/mp3/SoundHelix-Song-12.mp3', '2026-06-26 19:54:49.101417+00', '2026-06-26 19:54:49.101417+00');
INSERT INTO songs (id_song, title, duration, id_album, audio_url, created_at, updated_at) VALUES ('ba19dac7-d8f4-4fb3-84e7-0e6a97f3ac4c', 'Marry You', '228', '2c468183-bcfa-4406-8b65-5febdfa6237f', 'https://www.soundhelix.com/examples/mp3/SoundHelix-Song-13.mp3', '2026-06-26 19:54:49.101417+00', '2026-06-26 19:54:49.101417+00');
INSERT INTO songs (id_song, title, duration, id_album, audio_url, created_at, updated_at) VALUES ('05958114-dd3c-4804-86cd-a9b42e57b51b', 'The Lazy Song', '200', '2c468183-bcfa-4406-8b65-5febdfa6237f', 'https://www.soundhelix.com/examples/mp3/SoundHelix-Song-14.mp3', '2026-06-26 19:54:49.101417+00', '2026-06-26 19:54:49.101417+00');
INSERT INTO songs (id_song, title, duration, id_album, audio_url, created_at, updated_at) VALUES ('18a652cf-56cc-45db-93ab-6a6d0d0db34d', 'Oceans & Engines', '425', 'ee1d1302-8a7b-4281-8299-136491fadc4c', 'https://www.soundhelix.com/examples/mp3/SoundHelix-Song-15.mp3', '2026-06-26 19:54:49.101417+00', '2026-06-26 19:54:49.101417+00');
INSERT INTO songs (id_song, title, duration, id_album, audio_url, created_at, updated_at) VALUES ('936f870f-47d7-4c8a-8790-595711b84fac', 'Before', '243', 'ee1d1302-8a7b-4281-8299-136491fadc4c', 'https://www.soundhelix.com/examples/mp3/SoundHelix-Song-16.mp3', '2026-06-26 19:54:49.101417+00', '2026-06-26 19:54:49.101417+00');
INSERT INTO songs (id_song, title, duration, id_album, audio_url, created_at, updated_at) VALUES ('c6631a3c-d881-406d-abda-f578a4a91212', 'Buzz', '176', '7c54ead0-43de-48e6-bfcc-b4a59e3640af', 'https://www.soundhelix.com/examples/mp3/SoundHelix-Song-1.mp3', '2026-06-26 19:54:49.101417+00', '2026-06-26 19:54:49.101417+00');
INSERT INTO songs (id_song, title, duration, id_album, audio_url, created_at, updated_at) VALUES ('8ec8937d-85f7-4abd-862b-9dbb963c4ea7', 'Did You Like Her In The Morning', '218', '7c54ead0-43de-48e6-bfcc-b4a59e3640af', 'https://www.soundhelix.com/examples/mp3/SoundHelix-Song-2.mp3', '2026-06-26 19:54:49.101417+00', '2026-06-26 19:54:49.101417+00');
INSERT INTO songs (id_song, title, duration, id_album, audio_url, created_at, updated_at) VALUES ('b99e6d2c-91c2-46a2-a47a-0c1abd2c5b1e', 'Backburner', '237', '236a974f-fd58-4c5c-9e17-5711d43fcf79', 'https://www.soundhelix.com/examples/mp3/SoundHelix-Song-3.mp3', '2026-06-26 19:54:49.101417+00', '2026-06-26 19:54:49.101417+00');
INSERT INTO songs (id_song, title, duration, id_album, audio_url, created_at, updated_at) VALUES ('3d903c1d-86bd-4618-aa08-fe733f2da76d', 'Autumn', '233', '236a974f-fd58-4c5c-9e17-5711d43fcf79', 'https://www.soundhelix.com/examples/mp3/SoundHelix-Song-4.mp3', '2026-06-26 19:54:49.101417+00', '2026-06-26 19:54:49.101417+00');
INSERT INTO songs (id_song, title, duration, id_album, audio_url, created_at, updated_at) VALUES ('9bc0c013-8515-4b02-bae6-0e99af54024e', 'The Apartment We Won''t Share', '150', '236a974f-fd58-4c5c-9e17-5711d43fcf79', 'https://www.soundhelix.com/examples/mp3/SoundHelix-Song-5.mp3', '2026-06-26 19:54:49.101417+00', '2026-06-26 19:54:49.101417+00');
INSERT INTO songs (id_song, title, duration, id_album, audio_url, created_at, updated_at) VALUES ('d482d0b6-7a43-43f8-8cb2-3c842c198a4f', 'Nightcrawlers', '207', 'ALB_03', 'https://www.soundhelix.com/examples/mp3/SoundHelix-Song-6.mp3', '2026-06-26 19:54:49.101417+00', '2026-06-26 19:54:49.101417+00');
INSERT INTO songs (id_song, title, duration, id_album, audio_url, created_at, updated_at) VALUES ('8f6dfd0d-d09e-4056-aca6-1ec3721c9274', 'Snowchild', '249', '6b494f68-6fab-4a61-8522-f1c80d6dfac1', 'https://www.soundhelix.com/examples/mp3/SoundHelix-Song-7.mp3', '2026-06-26 19:54:49.101417+00', '2026-06-26 19:54:49.101417+00');
INSERT INTO songs (id_song, title, duration, id_album, audio_url, created_at, updated_at) VALUES ('e6144aff-a92b-4979-8b45-d8d87c73c691', 'Save Your Tears', '216', '6b494f68-6fab-4a61-8522-f1c80d6dfac1', 'https://www.soundhelix.com/examples/mp3/SoundHelix-Song-8.mp3', '2026-06-26 19:54:49.101417+00', '2026-06-26 19:54:49.101417+00');
INSERT INTO songs (id_song, title, duration, id_album, audio_url, created_at, updated_at) VALUES ('fbff3c56-fdf6-4edf-b061-a957b6c1ec7a', 'After Hours', '362', '6b494f68-6fab-4a61-8522-f1c80d6dfac1', 'https://www.soundhelix.com/examples/mp3/SoundHelix-Song-9.mp3', '2026-06-26 19:54:49.101417+00', '2026-06-26 19:54:49.101417+00');
INSERT INTO songs (id_song, title, duration, id_album, audio_url, created_at, updated_at) VALUES ('fc512e68-2b07-43d8-84c4-7989a360e069', 'Starboy', '231', '42099e1b-fdca-4bfa-bfc3-2d38f12983f7', 'https://www.soundhelix.com/examples/mp3/SoundHelix-Song-10.mp3', '2026-06-26 19:54:49.101417+00', '2026-06-26 19:54:49.101417+00');
INSERT INTO songs (id_song, title, duration, id_album, audio_url, created_at, updated_at) VALUES ('b7b3fbfa-b752-4f6d-ba6a-21551f387d1c', 'Reminder', '219', '42099e1b-fdca-4bfa-bfc3-2d38f12983f7', 'https://www.soundhelix.com/examples/mp3/SoundHelix-Song-11.mp3', '2026-06-26 19:54:49.101417+00', '2026-06-26 19:54:49.101417+00');
INSERT INTO songs (id_song, title, duration, id_album, audio_url, created_at, updated_at) VALUES ('40e67c7d-507f-4da0-b134-9e3c54db1323', 'Timeless', '256', '2c31f2e7-fcbf-4b49-bc06-6bc91ab27703', 'https://www.soundhelix.com/examples/mp3/SoundHelix-Song-12.mp3', '2026-06-26 19:54:49.101417+00', '2026-06-26 19:54:49.101417+00');
INSERT INTO songs (id_song, title, duration, id_album, audio_url, created_at, updated_at) VALUES ('176b77a3-72da-4659-9094-7c83ebb4a691', 'São Paulo', '302', '2c31f2e7-fcbf-4b49-bc06-6bc91ab27703', 'https://www.soundhelix.com/examples/mp3/SoundHelix-Song-13.mp3', '2026-06-26 19:54:49.101417+00', '2026-06-26 19:54:49.101417+00');
INSERT INTO songs (id_song, title, duration, id_album, audio_url, created_at, updated_at) VALUES ('bc5935f3-fb5d-426e-9873-9a2575fe4f33', 'What Do You Mean?', '298', 'dddd1ab3-cadd-45ec-af3d-cfa17ed50310', 'https://www.soundhelix.com/examples/mp3/SoundHelix-Song-14.mp3', '2026-06-26 19:54:49.101417+00', '2026-06-26 19:54:49.101417+00');
INSERT INTO songs (id_song, title, duration, id_album, audio_url, created_at, updated_at) VALUES ('588804fe-37f8-4b47-b46f-154b5df21ef5', 'Love Yourself', '233', 'dddd1ab3-cadd-45ec-af3d-cfa17ed50310', 'https://www.soundhelix.com/examples/mp3/SoundHelix-Song-15.mp3', '2026-06-26 19:54:49.101417+00', '2026-06-26 19:54:49.101417+00');
INSERT INTO songs (id_song, title, duration, id_album, audio_url, created_at, updated_at) VALUES ('ff2c7f81-54f5-4e57-9eb8-cd58b0f3d448', 'Beauty and a Beat', '293', 'd79b6312-38ad-4f7e-b605-0e65cab1b9ce', 'https://www.soundhelix.com/examples/mp3/SoundHelix-Song-16.mp3', '2026-06-26 19:54:49.101417+00', '2026-06-26 19:54:49.101417+00');
INSERT INTO songs (id_song, title, duration, id_album, audio_url, created_at, updated_at) VALUES ('0a33c4c5-d610-4f48-9d09-4278c26da855', 'Boyfriend', '211', 'd79b6312-38ad-4f7e-b605-0e65cab1b9ce', 'https://www.soundhelix.com/examples/mp3/SoundHelix-Song-1.mp3', '2026-06-26 19:54:49.101417+00', '2026-06-26 19:54:49.101417+00');
INSERT INTO songs (id_song, title, duration, id_album, audio_url, created_at, updated_at) VALUES ('dcf2a4df-0e4c-4906-ad02-8687aba7207a', 'Baby', '220', '2fcd55d3-c314-4171-a8d9-cbbd747a88a0', 'https://www.soundhelix.com/examples/mp3/SoundHelix-Song-2.mp3', '2026-06-26 19:54:49.101417+00', '2026-06-26 19:54:49.101417+00');
INSERT INTO songs (id_song, title, duration, id_album, audio_url, created_at, updated_at) VALUES ('b83d6d91-dc18-453a-ba67-59f10608c845', 'One Less Lonely Girl', '232', 'd79b6312-38ad-4f7e-b605-0e65cab1b9ce', 'https://www.soundhelix.com/examples/mp3/SoundHelix-Song-3.mp3', '2026-06-26 19:54:49.101417+00', '2026-06-26 19:54:49.101417+00');
INSERT INTO songs (id_song, title, duration, id_album, audio_url, created_at, updated_at) VALUES ('13b468f3-9269-4fc0-9aa3-6b3ac8e4948e', 'Favorite Girl', '257', '2fcd55d3-c314-4171-a8d9-cbbd747a88a0', 'https://www.soundhelix.com/examples/mp3/SoundHelix-Song-4.mp3', '2026-06-26 19:54:49.101417+00', '2026-06-26 19:54:49.101417+00');
INSERT INTO songs (id_song, title, duration, id_album, audio_url, created_at, updated_at) VALUES ('322d9d12-97bb-4b0c-9fa8-d99904a9577d', 'Eenie Meenie', '205', '2fcd55d3-c314-4171-a8d9-cbbd747a88a0', 'https://www.soundhelix.com/examples/mp3/SoundHelix-Song-5.mp3', '2026-06-26 19:54:49.101417+00', '2026-06-26 19:54:49.101417+00');
INSERT INTO songs (id_song, title, duration, id_album, audio_url, created_at, updated_at) VALUES ('44f3eff2-026c-40e7-9949-7380e16377be', 'That Should Be Me', '237', '2fcd55d3-c314-4171-a8d9-cbbd747a88a0', 'https://www.soundhelix.com/examples/mp3/SoundHelix-Song-6.mp3', '2026-06-26 19:54:49.101417+00', '2026-06-26 19:54:49.101417+00');
INSERT INTO songs (id_song, title, duration, id_album, audio_url, created_at, updated_at) VALUES ('10f2cf30-f4b0-4d6c-9172-9b3c56980302', 'roll over, baby', '205', '076b5c2d-49c8-4e2d-a98f-4476ca0d58cf', 'https://www.soundhelix.com/examples/mp3/SoundHelix-Song-7.mp3', '2026-06-26 19:54:49.101417+00', '2026-06-26 19:54:49.101417+00');
INSERT INTO songs (id_song, title, duration, id_album, audio_url, created_at, updated_at) VALUES ('4e87a68e-112f-4b31-8217-20ec2eb8c2ca', 'XXL', '212', '9fc88563-45fe-4a3a-a580-77b1bf3d0505', 'https://www.soundhelix.com/examples/mp3/SoundHelix-Song-8.mp3', '2026-06-26 19:54:49.101417+00', '2026-06-26 19:54:49.101417+00');
INSERT INTO songs (id_song, title, duration, id_album, audio_url, created_at, updated_at) VALUES ('563f25f8-cd95-41dc-bf4b-30440aaaf6ba', 'Out of My League', '207', '9fc88563-45fe-4a3a-a580-77b1bf3d0505', 'https://www.soundhelix.com/examples/mp3/SoundHelix-Song-9.mp3', '2026-06-26 19:54:49.101417+00', '2026-06-26 19:54:49.101417+00');
INSERT INTO songs (id_song, title, duration, id_album, audio_url, created_at, updated_at) VALUES ('46973d0d-e995-45ad-bbeb-76d91d2701dc', '''Cause You Have To', '251', '9fc88563-45fe-4a3a-a580-77b1bf3d0505', 'https://www.soundhelix.com/examples/mp3/SoundHelix-Song-10.mp3', '2026-06-26 19:54:49.101417+00', '2026-06-26 19:54:49.101417+00');
INSERT INTO songs (id_song, title, duration, id_album, audio_url, created_at, updated_at) VALUES ('76760084-8b34-474d-b1e4-feb6b0e2fb61', 'Alonica', '258', '9fc88563-45fe-4a3a-a580-77b1bf3d0505', 'https://www.soundhelix.com/examples/mp3/SoundHelix-Song-11.mp3', '2026-06-26 19:54:49.101417+00', '2026-06-26 19:54:49.101417+00');
INSERT INTO songs (id_song, title, duration, id_album, audio_url, created_at, updated_at) VALUES ('96c61684-b365-43e4-9aad-da37f018d3e4', 'if this is the last time', '205', 'd3e65927-7627-421b-b328-d047dc2f30d1', 'https://www.soundhelix.com/examples/mp3/SoundHelix-Song-12.mp3', '2026-06-26 19:54:49.101417+00', '2026-06-26 19:54:49.101417+00');
INSERT INTO songs (id_song, title, duration, id_album, audio_url, created_at, updated_at) VALUES ('87e16efe-b738-4988-8f50-1f0737ee7779', 'Dumb Stuff', '153', '7b2946ce-f644-4047-8197-dc032841b29a', 'https://www.soundhelix.com/examples/mp3/SoundHelix-Song-13.mp3', '2026-06-26 19:54:49.101417+00', '2026-06-26 19:54:49.101417+00');
INSERT INTO songs (id_song, title, duration, id_album, audio_url, created_at, updated_at) VALUES ('3f6f03a0-46c4-4000-b3be-1c6189fcde82', 'ILYSB', '210', '7b2946ce-f644-4047-8197-dc032841b29a', 'https://www.soundhelix.com/examples/mp3/SoundHelix-Song-14.mp3', '2026-06-26 19:54:49.101417+00', '2026-06-26 19:54:49.101417+00');
INSERT INTO songs (id_song, title, duration, id_album, audio_url, created_at, updated_at) VALUES ('4ecff77c-5a8d-41c2-9777-768a454e5ba6', 'House Of Balloons', '413', '86cf72ae-9cbb-490a-8819-00b9ef37bd07', 'https://www.soundhelix.com/examples/mp3/SoundHelix-Song-15.mp3', '2026-06-26 19:54:49.101417+00', '2026-06-26 19:54:49.101417+00');
INSERT INTO songs (id_song, title, duration, id_album, audio_url, created_at, updated_at) VALUES ('S_TULUS_01', 'Hati-Hati di Jalan', '242', 'ALB_TULUS', 'https://www.soundhelix.com/examples/mp3/SoundHelix-Song-1.mp3', '2026-06-28 18:14:14.157497+00', '2026-06-28 18:14:14.157497+00');
INSERT INTO songs (id_song, title, duration, id_album, audio_url, created_at, updated_at) VALUES ('S_TULUS_02', 'Diri', '250', 'ALB_TULUS', 'https://www.soundhelix.com/examples/mp3/SoundHelix-Song-2.mp3', '2026-06-28 18:14:14.157497+00', '2026-06-28 18:14:14.157497+00');
INSERT INTO songs (id_song, title, duration, id_album, audio_url, created_at, updated_at) VALUES ('S_RIZKY_01', 'Kesempurnaan Cinta', '245', 'ALB_RIZKY', 'https://www.soundhelix.com/examples/mp3/SoundHelix-Song-3.mp3', '2026-06-28 18:14:14.157497+00', '2026-06-28 18:14:14.157497+00');
INSERT INTO songs (id_song, title, duration, id_album, audio_url, created_at, updated_at) VALUES ('S_RIZKY_02', 'Cuek', '241', 'ALB_RIZKY', 'https://www.soundhelix.com/examples/mp3/SoundHelix-Song-4.mp3', '2026-06-28 18:14:14.157497+00', '2026-06-28 18:14:14.157497+00');
INSERT INTO songs (id_song, title, duration, id_album, audio_url, created_at, updated_at) VALUES ('S_JUICY_01', 'Lantas', '234', 'ALB_JUICY', 'https://www.soundhelix.com/examples/mp3/SoundHelix-Song-5.mp3', '2026-06-28 18:14:14.157497+00', '2026-06-28 18:14:14.157497+00');
INSERT INTO songs (id_song, title, duration, id_album, audio_url, created_at, updated_at) VALUES ('S_JUICY_02', 'Tanpa Tergesa', '220', 'ALB_JUICY', 'https://www.soundhelix.com/examples/mp3/SoundHelix-Song-6.mp3', '2026-06-28 18:14:14.157497+00', '2026-06-28 18:14:14.157497+00');
INSERT INTO songs (id_song, title, duration, id_album, audio_url, created_at, updated_at) VALUES ('S_MAUDY_01', 'Perahu Kertas', '254', 'ALB_MAUDY', 'https://www.soundhelix.com/examples/mp3/SoundHelix-Song-7.mp3', '2026-06-28 18:14:14.157497+00', '2026-06-28 18:14:14.157497+00');
INSERT INTO songs (id_song, title, duration, id_album, audio_url, created_at, updated_at) VALUES ('S_MAUDY_02', 'Untuk Apa', '231', 'ALB_MAUDY', 'https://www.soundhelix.com/examples/mp3/SoundHelix-Song-8.mp3', '2026-06-28 18:14:14.157497+00', '2026-06-28 18:14:14.157497+00');
INSERT INTO songs (id_song, title, duration, id_album, audio_url, created_at, updated_at) VALUES ('S_TENXI_01', 'Melodi Indah', '200', 'ALB_TENXI', 'https://www.soundhelix.com/examples/mp3/SoundHelix-Song-9.mp3', '2026-06-28 18:14:14.157497+00', '2026-06-28 18:14:14.157497+00');
INSERT INTO songs (id_song, title, duration, id_album, audio_url, created_at, updated_at) VALUES ('S_TENXI_02', 'Senja Syahdu', '215', 'ALB_TENXI', 'https://www.soundhelix.com/examples/mp3/SoundHelix-Song-10.mp3', '2026-06-28 18:14:14.157497+00', '2026-06-28 18:14:14.157497+00');
INSERT INTO songs (id_song, title, duration, id_album, audio_url, created_at, updated_at) VALUES ('S_DEWA_01', 'Roman Picisan', '247', 'ALB_DEWA', 'https://www.soundhelix.com/examples/mp3/SoundHelix-Song-11.mp3', '2026-06-28 18:14:14.157497+00', '2026-06-28 18:14:14.157497+00');
INSERT INTO songs (id_song, title, duration, id_album, audio_url, created_at, updated_at) VALUES ('S_DEWA_02', 'Separuh Nafas', '301', 'ALB_DEWA', 'https://www.soundhelix.com/examples/mp3/SoundHelix-Song-12.mp3', '2026-06-28 18:14:14.157497+00', '2026-06-28 18:14:14.157497+00');
INSERT INTO songs (id_song, title, duration, id_album, audio_url, created_at, updated_at) VALUES ('S_BILLIE_01', 'LUNCH', '180', 'ALB_BILLIE', 'https://www.soundhelix.com/examples/mp3/SoundHelix-Song-13.mp3', '2026-06-28 18:14:14.157497+00', '2026-06-28 18:14:14.157497+00');
INSERT INTO songs (id_song, title, duration, id_album, audio_url, created_at, updated_at) VALUES ('S_BILLIE_02', 'CHIHIRO', '303', 'ALB_BILLIE', 'https://www.soundhelix.com/examples/mp3/SoundHelix-Song-14.mp3', '2026-06-28 18:14:14.157497+00', '2026-06-28 18:14:14.157497+00');
INSERT INTO songs (id_song, title, duration, id_album, audio_url, created_at, updated_at) VALUES ('S_CHARLIE_01', 'Light Switch', '211', 'ALB_CHARLIE', 'https://www.soundhelix.com/examples/mp3/SoundHelix-Song-15.mp3', '2026-06-28 18:14:14.157497+00', '2026-06-28 18:14:14.157497+00');
INSERT INTO songs (id_song, title, duration, id_album, audio_url, created_at, updated_at) VALUES ('S_CHARLIE_02', 'Left and Right', '177', 'ALB_CHARLIE', 'https://www.soundhelix.com/examples/mp3/SoundHelix-Song-16.mp3', '2026-06-28 18:14:14.157497+00', '2026-06-28 18:14:14.157497+00');
INSERT INTO songs (id_song, title, duration, id_album, audio_url, created_at, updated_at) VALUES ('S_BRIAN_01', 'Yellow', '288', 'ALB_BRIAN', 'https://www.soundhelix.com/examples/mp3/SoundHelix-Song-1.mp3', '2026-06-28 18:14:14.157497+00', '2026-06-28 18:14:14.157497+00');
INSERT INTO songs (id_song, title, duration, id_album, audio_url, created_at, updated_at) VALUES ('S_BRIAN_02', 'Kids', '270', 'ALB_BRIAN', 'https://www.soundhelix.com/examples/mp3/SoundHelix-Song-2.mp3', '2026-06-28 18:14:14.157497+00', '2026-06-28 18:14:14.157497+00');
INSERT INTO songs (id_song, title, duration, id_album, audio_url, created_at, updated_at) VALUES ('S_JOJI_01', 'Glimpse of Us', '233', 'ALB_JOJI', 'https://www.soundhelix.com/examples/mp3/SoundHelix-Song-3.mp3', '2026-06-28 18:14:14.157497+00', '2026-06-28 18:14:14.157497+00');
INSERT INTO songs (id_song, title, duration, id_album, audio_url, created_at, updated_at) VALUES ('S_JOJI_02', 'Die For You', '211', 'ALB_JOJI', 'https://www.soundhelix.com/examples/mp3/SoundHelix-Song-4.mp3', '2026-06-28 18:14:14.157497+00', '2026-06-28 18:14:14.157497+00');

-- Data for: playlists
INSERT INTO playlists (id_playlist, name, description, cover_image, id_playlist_category, created_at, updated_at) VALUES ('PL_01', 'Lagu Nugas Rombongan', 'Playlist kelar UTS', NULL, NULL, '2026-06-26 19:54:49.101417+00', '2026-06-26 19:54:49.101417+00');
INSERT INTO playlists (id_playlist, name, description, cover_image, id_playlist_category, created_at, updated_at) VALUES ('9350e255-f632-470d-a50c-a037077b602a', 'wrwrwww', 'wtwtwt', NULL, 'PC_01', '2026-06-26 19:54:49.101417+00', '2026-06-26 19:54:49.101417+00');
INSERT INTO playlists (id_playlist, name, description, cover_image, id_playlist_category, created_at, updated_at) VALUES ('210a18b4-de07-4ee0-9682-0d26720a7075', 'david jody', 'a,sfkalkfjljasfafkl', 'https://res.cloudinary.com/dmjjmsha3/image/upload/v1782280044/spotify-clone/playlists/u0fxhqd1qgzbp0mtqaln.jpg', 'PC_06', '2026-06-26 19:54:49.101417+00', '2026-06-26 19:54:49.101417+00');
INSERT INTO playlists (id_playlist, name, description, cover_image, id_playlist_category, created_at, updated_at) VALUES ('70142d91-141c-45ac-9fbe-d9eb1a5204b5', 'felix', 'shsahhhsa', 'https://res.cloudinary.com/dmjjmsha3/image/upload/v1782459880/spotify-clone/playlists/p8wmfqolvsl4ukqqg0cw.jpg', 'PC_02', '2026-06-26 19:54:49.101417+00', '2026-06-26 19:54:49.101417+00');
INSERT INTO playlists (id_playlist, name, description, cover_image, id_playlist_category, created_at, updated_at) VALUES ('PL_RANDOM_01', 'Galau Pop Indonesia', 'Koleksi lagu pop Indonesia paling syahdu dan bikin galau.', 'https://images.unsplash.com/photo-1514525253161-7a46d19cd819?q=80&w=400', 'PC_02', '2026-06-28 18:14:14.157497+00', '2026-06-28 18:14:14.157497+00');
INSERT INTO playlists (id_playlist, name, description, cover_image, id_playlist_category, created_at, updated_at) VALUES ('PL_RANDOM_02', 'Lagu Hits Barat', 'Pop hits teratas internasional masa kini.', 'https://images.unsplash.com/photo-1510915361894-db8b60106cb1?q=80&w=400', 'PC_01', '2026-06-28 18:14:14.157497+00', '2026-06-28 18:14:14.157497+00');
INSERT INTO playlists (id_playlist, name, description, cover_image, id_playlist_category, created_at, updated_at) VALUES ('PL_RANDOM_03', 'Dewa 19 & Friends Rock', 'Playlist lagu-lagu rock legendaris Indonesia.', 'https://images.unsplash.com/photo-1498038432885-c6f3f1b912ee?q=80&w=400', 'PC_06', '2026-06-28 18:14:14.157497+00', '2026-06-28 18:14:14.157497+00');

-- Data for: podcasts
INSERT INTO podcasts (id_podcast, name, about, cover_image, id_podcast_category, id_user, created_at, updated_at) VALUES ('POD_01', 'Ngoding Santai', 'Bahas IT dan kehidupan', 'pod.jpg', NULL, 'e0362ced-2fbe-4f25-8cb4-433c8c8a8bdf', '2026-06-26 19:54:49.101417+00', '2026-06-26 19:54:49.101417+00');
INSERT INTO podcasts (id_podcast, name, about, cover_image, id_podcast_category, id_user, created_at, updated_at) VALUES ('b755c32f-b692-4d02-808d-c9505d43be85', 'William Ngoceh', 'Diskusi santai seputar kehidupan sehari-hari, opini bebas, dan obrolan seru.', 'https://res.cloudinary.com/dmjjmsha3/image/upload/v1781379381/spotify-clone/podcasts/fpzg3bb9oju3qwmlucvf.png', 'PDC_01', 'e0362ced-2fbe-4f25-8cb4-433c8c8a8bdf', '2026-06-26 19:54:49.101417+00', '2026-06-26 19:54:49.101417+00');
INSERT INTO podcasts (id_podcast, name, about, cover_image, id_podcast_category, id_user, created_at, updated_at) VALUES ('POD_02', 'Podcaster Gaul', 'Celotehan seru anak muda zaman sekarang.', 'https://images.unsplash.com/photo-1590602847861-f357a9332bbc?q=80&w=400', 'PDC_01', 'c75b7ee7-b21f-4105-a7f5-d5278bc38283', '2026-06-28 17:54:33.913988+00', '2026-06-28 17:54:33.913988+00');
INSERT INTO podcasts (id_podcast, name, about, cover_image, id_podcast_category, id_user, created_at, updated_at) VALUES ('POD_03', 'Tech & Business Talk', 'Membahas perkembangan teknologi terbaru dan tips bisnis startup.', 'https://images.unsplash.com/photo-1519389950473-47ba0277781c?q=80&w=400', 'PDC_06', '04d8d10e-2b96-4c65-a17a-f0bde9dbe72c', '2026-06-28 17:54:33.913988+00', '2026-06-28 17:54:33.913988+00');
INSERT INTO podcasts (id_podcast, name, about, cover_image, id_podcast_category, id_user, created_at, updated_at) VALUES ('POD_04', 'Horor Malam Jumat', 'Kisah-kisah mistis nyata dan urban legend Indonesia.', 'https://images.unsplash.com/photo-1509248961158-e54f6934749c?q=80&w=400', 'PDC_03', '83e6d65e-5e46-4019-9bbb-aa1b712e1c48', '2026-06-28 17:54:33.913988+00', '2026-06-28 17:54:33.913988+00');

-- Data for: episodes
INSERT INTO episodes (id_episode, title, description, published_at, duration, audio_url, video_url, thumbnail_image, id_podcast, created_at, updated_at) VALUES ('EP_01', 'Cara Bertahan di Informatika', NULL, '2026-05-01 10:00:00', '3600', 'https://www.soundhelix.com/examples/mp3/SoundHelix-Song-1.mp3', NULL, NULL, 'POD_01', '2026-06-26 19:54:49.101417+00', '2026-06-26 19:54:49.101417+00');
INSERT INTO episodes (id_episode, title, description, published_at, duration, audio_url, video_url, thumbnail_image, id_podcast, created_at, updated_at) VALUES ('aeff83a5-43b7-4552-8aad-40266dbf24e0', 'Indonesia raya', 'hiduplah indonesia raya', '2026-06-27 00:00:00', '12', 'https://www.soundhelix.com/examples/mp3/SoundHelix-Song-2.mp3', 'https://res.cloudinary.com/dmjjmsha3/video/upload/v1782577089/spotify-clone/video/episodes/chsasjuqopd6aobxzcjf.mp4', NULL, 'b755c32f-b692-4d02-808d-c9505d43be85', '2026-06-27 16:18:23.363125+00', '2026-06-27 16:18:23.363125+00');
INSERT INTO episodes (id_episode, title, description, published_at, duration, audio_url, video_url, thumbnail_image, id_podcast, created_at, updated_at) VALUES ('EP_03', 'Tips Sukses Belajar Database', 'Bagaimana cara belajar SQL dengan cepat dan menyenangkan.', '2026-06-25 10:00:00', '1800', 'https://www.soundhelix.com/examples/mp3/SoundHelix-Song-3.mp3', NULL, NULL, 'POD_01', '2026-06-28 17:54:33.913988+00', '2026-06-28 17:54:33.913988+00');
INSERT INTO episodes (id_episode, title, description, published_at, duration, audio_url, video_url, thumbnail_image, id_podcast, created_at, updated_at) VALUES ('EP_04', 'Sejarah Komedi Indonesia', 'Membahas sejarah perkembangan stand up comedy di Indonesia.', '2026-06-26 12:00:00', '2400', 'https://www.soundhelix.com/examples/mp3/SoundHelix-Song-4.mp3', NULL, NULL, 'POD_02', '2026-06-28 17:54:33.913988+00', '2026-06-28 17:54:33.913988+00');
INSERT INTO episodes (id_episode, title, description, published_at, duration, audio_url, video_url, thumbnail_image, id_podcast, created_at, updated_at) VALUES ('EP_05', 'Masa Depan AI dan Web3', 'Diskusi panel mengenai kecerdasan buatan dan desentralisasi web.', '2026-06-27 15:30:00', '3000', 'https://www.soundhelix.com/examples/mp3/SoundHelix-Song-5.mp3', NULL, NULL, 'POD_03', '2026-06-28 17:54:33.913988+00', '2026-06-28 17:54:33.913988+00');

-- Data for: artist_galleries
INSERT INTO artist_galleries (id_gallery, image_url, description_pict, uploaded_at, id_artist, created_at, updated_at) VALUES ('36565b9a-0a9d-4553-baa3-f9b8debf4abd', 'https://res.cloudinary.com/dmjjmsha3/image/upload/v1781024422/spotify-clone/gallery/vwxkuwcsyyp9cokfeozl.png', NULL, '2026-06-10 00:00:28', 'A_01', '2026-06-26 19:54:49.101417+00', '2026-06-26 19:54:49.101417+00');
INSERT INTO artist_galleries (id_gallery, image_url, description_pict, uploaded_at, id_artist, created_at, updated_at) VALUES ('dd12eb74-3d49-4a33-bfbf-792157db9527', 'https://res.cloudinary.com/dmjjmsha3/image/upload/v1781267945/spotify-clone/gallery/x6hvnwzpjcjdncmyupk7.webp', 'nyantai sek', '2026-06-12 19:39:26', 'A_02', '2026-06-26 19:54:49.101417+00', '2026-06-26 19:54:49.101417+00');
INSERT INTO artist_galleries (id_gallery, image_url, description_pict, uploaded_at, id_artist, created_at, updated_at) VALUES ('f8cb8071-597a-41b0-bd82-dc0808a6efc9', 'https://res.cloudinary.com/dmjjmsha3/image/upload/v1781268040/spotify-clone/gallery/jeiaf8klmbmiglzx6eis.jpg', 'aku aja udah punya cewek, kamu kapan?', '2026-06-12 19:41:01', 'A_02', '2026-06-26 19:54:49.101417+00', '2026-06-26 19:54:49.101417+00');
INSERT INTO artist_galleries (id_gallery, image_url, description_pict, uploaded_at, id_artist, created_at, updated_at) VALUES ('6a7d95df-4ac1-499c-8d05-47b296602ff8', 'https://res.cloudinary.com/dmjjmsha3/image/upload/v1781268106/spotify-clone/gallery/tiei9sc17kszxyfjnnbj.jpg', 'macak wong yes', '2026-06-12 19:41:57', 'A_02', '2026-06-26 19:54:49.101417+00', '2026-06-26 19:54:49.101417+00');
INSERT INTO artist_galleries (id_gallery, image_url, description_pict, uploaded_at, id_artist, created_at, updated_at) VALUES ('229eb618-92dc-48f0-b03f-1c21045ee637', 'https://res.cloudinary.com/dmjjmsha3/image/upload/v1781268243/spotify-clone/gallery/a7fp03bazomqbjyte8m4.webp', 'ngonser sek karo kancaku', '2026-06-12 19:44:14', 'A_02', '2026-06-26 19:54:49.101417+00', '2026-06-26 19:54:49.101417+00');
INSERT INTO artist_galleries (id_gallery, image_url, description_pict, uploaded_at, id_artist, created_at, updated_at) VALUES ('7c45039e-60af-4997-a33d-0c89cf99e5ed', 'https://res.cloudinary.com/dmjjmsha3/image/upload/v1781277713/spotify-clone/gallery/jgd4vqhatcuo3kbmihsv.jpg', 'BeStIeEeQ', '2026-06-12 22:22:19', 'A_04', '2026-06-26 19:54:49.101417+00', '2026-06-26 19:54:49.101417+00');
INSERT INTO artist_galleries (id_gallery, image_url, description_pict, uploaded_at, id_artist, created_at, updated_at) VALUES ('c6acd9e6-418a-4bca-8991-33b3896042b4', 'https://res.cloudinary.com/dmjjmsha3/image/upload/v1781277785/spotify-clone/gallery/h2rpvynekvz6idtzvepn.jpg', 'candid', '2026-06-12 22:23:17', 'A_04', '2026-06-26 19:54:49.101417+00', '2026-06-26 19:54:49.101417+00');
INSERT INTO artist_galleries (id_gallery, image_url, description_pict, uploaded_at, id_artist, created_at, updated_at) VALUES ('5412f7c7-71dd-4fc2-a939-566a75fabf34', 'https://res.cloudinary.com/dmjjmsha3/image/upload/v1781277826/spotify-clone/gallery/huqucszrgo8j8b6ymsox.jpg', 'konser dulu bestie <3', '2026-06-12 22:24:00', 'A_04', '2026-06-26 19:54:49.101417+00', '2026-06-26 19:54:49.101417+00');
INSERT INTO artist_galleries (id_gallery, image_url, description_pict, uploaded_at, id_artist, created_at, updated_at) VALUES ('aba95b70-4363-4bd3-87c0-a14ab46ed4d9', 'https://res.cloudinary.com/dmjjmsha3/image/upload/v1781277913/spotify-clone/gallery/djkpxpb5uriw9hn3ojkb.jpg', 'pakyu men', '2026-06-12 22:25:21', 'A_04', '2026-06-26 19:54:49.101417+00', '2026-06-26 19:54:49.101417+00');
INSERT INTO artist_galleries (id_gallery, image_url, description_pict, uploaded_at, id_artist, created_at, updated_at) VALUES ('4b953888-6fd7-4038-80dd-2ac404d7ec7d', 'https://res.cloudinary.com/dmjjmsha3/image/upload/v1781279076/spotify-clone/gallery/s7u7raeedzezzbfhswzd.webp', 'sigmasigmaboy', '2026-06-12 22:44:50', 'A_06', '2026-06-26 19:54:49.101417+00', '2026-06-26 19:54:49.101417+00');
INSERT INTO artist_galleries (id_gallery, image_url, description_pict, uploaded_at, id_artist, created_at, updated_at) VALUES ('2129ba33-3676-488b-98c1-8141b016405c', 'https://res.cloudinary.com/dmjjmsha3/image/upload/v1781279124/spotify-clone/gallery/fzd6gtmeiiq4o1mtrod7.jpg', NULL, '2026-06-12 22:46:12', 'A_06', '2026-06-26 19:54:49.101417+00', '2026-06-26 19:54:49.101417+00');
INSERT INTO artist_galleries (id_gallery, image_url, description_pict, uploaded_at, id_artist, created_at, updated_at) VALUES ('9e25e9f4-05ba-4718-b619-5d28e94768aa', 'https://res.cloudinary.com/dmjjmsha3/image/upload/v1781279187/spotify-clone/gallery/casfabjolitw4n2okqau.jpg', NULL, '2026-06-12 22:46:36', 'A_06', '2026-06-26 19:54:49.101417+00', '2026-06-26 19:54:49.101417+00');
INSERT INTO artist_galleries (id_gallery, image_url, description_pict, uploaded_at, id_artist, created_at, updated_at) VALUES ('e8c7c992-67ec-439d-976b-e9d162d8b36a', 'https://res.cloudinary.com/dmjjmsha3/image/upload/v1781279934/spotify-clone/gallery/wwg0kspkppffbjqqp6kr.webp', 'halo mas', '2026-06-12 22:59:14', 'A_05', '2026-06-26 19:54:49.101417+00', '2026-06-26 19:54:49.101417+00');
INSERT INTO artist_galleries (id_gallery, image_url, description_pict, uploaded_at, id_artist, created_at, updated_at) VALUES ('b8d54838-e8fb-478a-8bf0-0bcc0eabc18a', 'https://res.cloudinary.com/dmjjmsha3/image/upload/v1781280311/spotify-clone/gallery/oax6dpvrwuwojjgnguvx.jpg', 'mbg my bini gweh', '2026-06-12 23:05:24', 'A_05', '2026-06-26 19:54:49.101417+00', '2026-06-26 19:54:49.101417+00');
INSERT INTO artist_galleries (id_gallery, image_url, description_pict, uploaded_at, id_artist, created_at, updated_at) VALUES ('7c79a307-e337-44f7-872f-c565b93da7cd', 'https://res.cloudinary.com/dmjjmsha3/image/upload/v1781280333/spotify-clone/gallery/zbep9wicr4myfd1fb5nx.jpg', 'old photo', '2026-06-12 23:05:58', 'A_05', '2026-06-26 19:54:49.101417+00', '2026-06-26 19:54:49.101417+00');
INSERT INTO artist_galleries (id_gallery, image_url, description_pict, uploaded_at, id_artist, created_at, updated_at) VALUES ('427e0405-d50c-4287-bb93-22591a4a545f', 'https://res.cloudinary.com/dmjjmsha3/image/upload/v1781280373/spotify-clone/gallery/ctubgpjvzsi271hfx6ak.webp', 'ganteng bet gw jir', '2026-06-12 23:06:30', 'A_05', '2026-06-26 19:54:49.101417+00', '2026-06-26 19:54:49.101417+00');
INSERT INTO artist_galleries (id_gallery, image_url, description_pict, uploaded_at, id_artist, created_at, updated_at) VALUES ('0f61d370-3735-4091-8f00-7c2ff8d2f10d', 'https://res.cloudinary.com/dmjjmsha3/image/upload/v1781281319/spotify-clone/gallery/bf4esrwyulegdax77wdm.png', 'kawan lama', '2026-06-12 23:22:17', 'A_07', '2026-06-26 19:54:49.101417+00', '2026-06-26 19:54:49.101417+00');
INSERT INTO artist_galleries (id_gallery, image_url, description_pict, uploaded_at, id_artist, created_at, updated_at) VALUES ('23b989f5-41e6-47c8-a1bc-cd809ba26c44', 'https://res.cloudinary.com/dmjjmsha3/image/upload/v1781281357/spotify-clone/gallery/xkg1j8hsfgt30hpjjzrm.webp', 'ngetour sek', '2026-06-12 23:22:45', 'A_07', '2026-06-26 19:54:49.101417+00', '2026-06-26 19:54:49.101417+00');
INSERT INTO artist_galleries (id_gallery, image_url, description_pict, uploaded_at, id_artist, created_at, updated_at) VALUES ('1545743e-10ce-47af-bdb2-49b919976f2a', 'https://res.cloudinary.com/dmjjmsha3/image/upload/v1781281406/spotify-clone/gallery/h3is3fby6ygvqwqvpish.jpg', NULL, '2026-06-12 23:23:32', 'A_07', '2026-06-26 19:54:49.101417+00', '2026-06-26 19:54:49.101417+00');
INSERT INTO artist_galleries (id_gallery, image_url, description_pict, uploaded_at, id_artist, created_at, updated_at) VALUES ('GAL_TULUS_01', 'https://images.unsplash.com/photo-1511671782779-c97d3d27a1d4?q=80&w=400', 'Tulus bernyanyi di konser Manusia 2026', '2026-06-25 12:00:00', 'A_08', '2026-06-28 18:14:14.157497+00', '2026-06-28 18:14:14.157497+00');
INSERT INTO artist_galleries (id_gallery, image_url, description_pict, uploaded_at, id_artist, created_at, updated_at) VALUES ('GAL_DEWA_01', 'https://images.unsplash.com/photo-1498038432885-c6f3f1b912ee?q=80&w=400', 'Dewa 19 Live in Jakarta Bintang Lima Reunion', '2026-06-26 14:00:00', 'A_13', '2026-06-28 18:14:14.157497+00', '2026-06-28 18:14:14.157497+00');
INSERT INTO artist_galleries (id_gallery, image_url, description_pict, uploaded_at, id_artist, created_at, updated_at) VALUES ('GAL_BILLIE_01', 'https://images.unsplash.com/photo-1470229722913-7c0e2dbbafd3?q=80&w=400', 'Billie Eilish World Tour Promo Pic', '2026-06-27 10:00:00', 'A_14', '2026-06-28 18:14:14.157497+00', '2026-06-28 18:14:14.157497+00');

-- Data for: song_artist
INSERT INTO song_artist (id_song, id_artist, created_at, updated_at) VALUES ('S_01', 'A_01', '2026-06-26 19:54:49.101417+00', '2026-06-26 19:54:49.101417+00');
INSERT INTO song_artist (id_song, id_artist, created_at, updated_at) VALUES ('S_02', 'A_01', '2026-06-26 19:54:49.101417+00', '2026-06-26 19:54:49.101417+00');
INSERT INTO song_artist (id_song, id_artist, created_at, updated_at) VALUES ('S_03', 'A_03', '2026-06-26 19:54:49.101417+00', '2026-06-26 19:54:49.101417+00');
INSERT INTO song_artist (id_song, id_artist, created_at, updated_at) VALUES ('S_04', 'A_04', '2026-06-26 19:54:49.101417+00', '2026-06-26 19:54:49.101417+00');
INSERT INTO song_artist (id_song, id_artist, created_at, updated_at) VALUES ('S_05', 'A_02', '2026-06-26 19:54:49.101417+00', '2026-06-26 19:54:49.101417+00');
INSERT INTO song_artist (id_song, id_artist, created_at, updated_at) VALUES ('86da840a-ac14-4400-9a01-1896c9544fac', 'A_02', '2026-06-26 19:54:49.101417+00', '2026-06-26 19:54:49.101417+00');
INSERT INTO song_artist (id_song, id_artist, created_at, updated_at) VALUES ('d9b753ab-1077-4f5f-8f8a-9bf9844cf194', 'A_02', '2026-06-26 19:54:49.101417+00', '2026-06-26 19:54:49.101417+00');
INSERT INTO song_artist (id_song, id_artist, created_at, updated_at) VALUES ('9d747333-b9dc-4bbe-b805-9322e41b6b0f', 'A_02', '2026-06-26 19:54:49.101417+00', '2026-06-26 19:54:49.101417+00');
INSERT INTO song_artist (id_song, id_artist, created_at, updated_at) VALUES ('ce5edf44-9e71-40a8-b4ba-0a4cdfb98777', 'A_02', '2026-06-26 19:54:49.101417+00', '2026-06-26 19:54:49.101417+00');
INSERT INTO song_artist (id_song, id_artist, created_at, updated_at) VALUES ('7dc27706-3fc2-4aad-9269-2dd6c4461874', 'A_02', '2026-06-26 19:54:49.101417+00', '2026-06-26 19:54:49.101417+00');
INSERT INTO song_artist (id_song, id_artist, created_at, updated_at) VALUES ('ba19dac7-d8f4-4fb3-84e7-0e6a97f3ac4c', 'A_02', '2026-06-26 19:54:49.101417+00', '2026-06-26 19:54:49.101417+00');
INSERT INTO song_artist (id_song, id_artist, created_at, updated_at) VALUES ('05958114-dd3c-4804-86cd-a9b42e57b51b', 'A_02', '2026-06-26 19:54:49.101417+00', '2026-06-26 19:54:49.101417+00');
INSERT INTO song_artist (id_song, id_artist, created_at, updated_at) VALUES ('18a652cf-56cc-45db-93ab-6a6d0d0db34d', 'A_04', '2026-06-26 19:54:49.101417+00', '2026-06-26 19:54:49.101417+00');
INSERT INTO song_artist (id_song, id_artist, created_at, updated_at) VALUES ('936f870f-47d7-4c8a-8790-595711b84fac', 'A_04', '2026-06-26 19:54:49.101417+00', '2026-06-26 19:54:49.101417+00');
INSERT INTO song_artist (id_song, id_artist, created_at, updated_at) VALUES ('c6631a3c-d881-406d-abda-f578a4a91212', 'A_04', '2026-06-26 19:54:49.101417+00', '2026-06-26 19:54:49.101417+00');
INSERT INTO song_artist (id_song, id_artist, created_at, updated_at) VALUES ('8ec8937d-85f7-4abd-862b-9dbb963c4ea7', 'A_04', '2026-06-26 19:54:49.101417+00', '2026-06-26 19:54:49.101417+00');
INSERT INTO song_artist (id_song, id_artist, created_at, updated_at) VALUES ('b99e6d2c-91c2-46a2-a47a-0c1abd2c5b1e', 'A_04', '2026-06-26 19:54:49.101417+00', '2026-06-26 19:54:49.101417+00');
INSERT INTO song_artist (id_song, id_artist, created_at, updated_at) VALUES ('3d903c1d-86bd-4618-aa08-fe733f2da76d', 'A_04', '2026-06-26 19:54:49.101417+00', '2026-06-26 19:54:49.101417+00');
INSERT INTO song_artist (id_song, id_artist, created_at, updated_at) VALUES ('9bc0c013-8515-4b02-bae6-0e99af54024e', 'A_04', '2026-06-26 19:54:49.101417+00', '2026-06-26 19:54:49.101417+00');
INSERT INTO song_artist (id_song, id_artist, created_at, updated_at) VALUES ('d482d0b6-7a43-43f8-8cb2-3c842c198a4f', 'A_04', '2026-06-26 19:54:49.101417+00', '2026-06-26 19:54:49.101417+00');
INSERT INTO song_artist (id_song, id_artist, created_at, updated_at) VALUES ('8f6dfd0d-d09e-4056-aca6-1ec3721c9274', 'A_06', '2026-06-26 19:54:49.101417+00', '2026-06-26 19:54:49.101417+00');
INSERT INTO song_artist (id_song, id_artist, created_at, updated_at) VALUES ('e6144aff-a92b-4979-8b45-d8d87c73c691', 'A_06', '2026-06-26 19:54:49.101417+00', '2026-06-26 19:54:49.101417+00');
INSERT INTO song_artist (id_song, id_artist, created_at, updated_at) VALUES ('fbff3c56-fdf6-4edf-b061-a957b6c1ec7a', 'A_06', '2026-06-26 19:54:49.101417+00', '2026-06-26 19:54:49.101417+00');
INSERT INTO song_artist (id_song, id_artist, created_at, updated_at) VALUES ('fc512e68-2b07-43d8-84c4-7989a360e069', 'A_06', '2026-06-26 19:54:49.101417+00', '2026-06-26 19:54:49.101417+00');
INSERT INTO song_artist (id_song, id_artist, created_at, updated_at) VALUES ('b7b3fbfa-b752-4f6d-ba6a-21551f387d1c', 'A_06', '2026-06-26 19:54:49.101417+00', '2026-06-26 19:54:49.101417+00');
INSERT INTO song_artist (id_song, id_artist, created_at, updated_at) VALUES ('40e67c7d-507f-4da0-b134-9e3c54db1323', 'A_06', '2026-06-26 19:54:49.101417+00', '2026-06-26 19:54:49.101417+00');
INSERT INTO song_artist (id_song, id_artist, created_at, updated_at) VALUES ('176b77a3-72da-4659-9094-7c83ebb4a691', 'A_06', '2026-06-26 19:54:49.101417+00', '2026-06-26 19:54:49.101417+00');
INSERT INTO song_artist (id_song, id_artist, created_at, updated_at) VALUES ('4ecff77c-5a8d-41c2-9777-768a454e5ba6', 'A_06', '2026-06-26 19:54:49.101417+00', '2026-06-26 19:54:49.101417+00');
INSERT INTO song_artist (id_song, id_artist, created_at, updated_at) VALUES ('bc5935f3-fb5d-426e-9873-9a2575fe4f33', 'A_05', '2026-06-26 19:54:49.101417+00', '2026-06-26 19:54:49.101417+00');
INSERT INTO song_artist (id_song, id_artist, created_at, updated_at) VALUES ('588804fe-37f8-4b47-b46f-154b5df21ef5', 'A_05', '2026-06-26 19:54:49.101417+00', '2026-06-26 19:54:49.101417+00');
INSERT INTO song_artist (id_song, id_artist, created_at, updated_at) VALUES ('ff2c7f81-54f5-4e57-9eb8-cd58b0f3d448', 'A_05', '2026-06-26 19:54:49.101417+00', '2026-06-26 19:54:49.101417+00');
INSERT INTO song_artist (id_song, id_artist, created_at, updated_at) VALUES ('0a33c4c5-d610-4f48-9d09-4278c26da855', 'A_05', '2026-06-26 19:54:49.101417+00', '2026-06-26 19:54:49.101417+00');
INSERT INTO song_artist (id_song, id_artist, created_at, updated_at) VALUES ('dcf2a4df-0e4c-4906-ad02-8687aba7207a', 'A_05', '2026-06-26 19:54:49.101417+00', '2026-06-26 19:54:49.101417+00');
INSERT INTO song_artist (id_song, id_artist, created_at, updated_at) VALUES ('b83d6d91-dc18-453a-ba67-59f10608c845', 'A_05', '2026-06-26 19:54:49.101417+00', '2026-06-26 19:54:49.101417+00');
INSERT INTO song_artist (id_song, id_artist, created_at, updated_at) VALUES ('13b468f3-9269-4fc0-9aa3-6b3ac8e4948e', 'A_05', '2026-06-26 19:54:49.101417+00', '2026-06-26 19:54:49.101417+00');
INSERT INTO song_artist (id_song, id_artist, created_at, updated_at) VALUES ('322d9d12-97bb-4b0c-9fa8-d99904a9577d', 'A_05', '2026-06-26 19:54:49.101417+00', '2026-06-26 19:54:49.101417+00');
INSERT INTO song_artist (id_song, id_artist, created_at, updated_at) VALUES ('44f3eff2-026c-40e7-9949-7380e16377be', 'A_05', '2026-06-26 19:54:49.101417+00', '2026-06-26 19:54:49.101417+00');
INSERT INTO song_artist (id_song, id_artist, created_at, updated_at) VALUES ('10f2cf30-f4b0-4d6c-9172-9b3c56980302', 'A_07', '2026-06-26 19:54:49.101417+00', '2026-06-26 19:54:49.101417+00');
INSERT INTO song_artist (id_song, id_artist, created_at, updated_at) VALUES ('4e87a68e-112f-4b31-8217-20ec2eb8c2ca', 'A_07', '2026-06-26 19:54:49.101417+00', '2026-06-26 19:54:49.101417+00');
INSERT INTO song_artist (id_song, id_artist, created_at, updated_at) VALUES ('563f25f8-cd95-41dc-bf4b-30440aaaf6ba', 'A_07', '2026-06-26 19:54:49.101417+00', '2026-06-26 19:54:49.101417+00');
INSERT INTO song_artist (id_song, id_artist, created_at, updated_at) VALUES ('46973d0d-e995-45ad-bbeb-76d91d2701dc', 'A_07', '2026-06-26 19:54:49.101417+00', '2026-06-26 19:54:49.101417+00');
INSERT INTO song_artist (id_song, id_artist, created_at, updated_at) VALUES ('76760084-8b34-474d-b1e4-feb6b0e2fb61', 'A_07', '2026-06-26 19:54:49.101417+00', '2026-06-26 19:54:49.101417+00');
INSERT INTO song_artist (id_song, id_artist, created_at, updated_at) VALUES ('b88d8951-d233-42e4-93c8-62fcd79cf975', 'A_07', '2026-06-26 19:54:49.101417+00', '2026-06-26 19:54:49.101417+00');
INSERT INTO song_artist (id_song, id_artist, created_at, updated_at) VALUES ('55397a89-f0b0-4c3b-a8b8-f21c7526f5f9', 'A_07', '2026-06-26 19:54:49.101417+00', '2026-06-26 19:54:49.101417+00');
INSERT INTO song_artist (id_song, id_artist, created_at, updated_at) VALUES ('96c61684-b365-43e4-9aad-da37f018d3e4', 'A_07', '2026-06-26 19:54:49.101417+00', '2026-06-26 19:54:49.101417+00');
INSERT INTO song_artist (id_song, id_artist, created_at, updated_at) VALUES ('87e16efe-b738-4988-8f50-1f0737ee7779', 'A_07', '2026-06-26 19:54:49.101417+00', '2026-06-26 19:54:49.101417+00');
INSERT INTO song_artist (id_song, id_artist, created_at, updated_at) VALUES ('3f6f03a0-46c4-4000-b3be-1c6189fcde82', 'A_07', '2026-06-26 19:54:49.101417+00', '2026-06-26 19:54:49.101417+00');
INSERT INTO song_artist (id_song, id_artist, created_at, updated_at) VALUES ('212ede59-e53c-4f26-bfdb-c62599aabbb6', 'A_16', '2026-06-26 19:54:49.101417+00', '2026-06-26 19:54:49.101417+00');
INSERT INTO song_artist (id_song, id_artist, created_at, updated_at) VALUES ('S_TULUS_01', 'A_08', '2026-06-28 18:14:14.157497+00', '2026-06-28 18:14:14.157497+00');
INSERT INTO song_artist (id_song, id_artist, created_at, updated_at) VALUES ('S_TULUS_02', 'A_08', '2026-06-28 18:14:14.157497+00', '2026-06-28 18:14:14.157497+00');
INSERT INTO song_artist (id_song, id_artist, created_at, updated_at) VALUES ('S_RIZKY_01', 'A_09', '2026-06-28 18:14:14.157497+00', '2026-06-28 18:14:14.157497+00');
INSERT INTO song_artist (id_song, id_artist, created_at, updated_at) VALUES ('S_RIZKY_02', 'A_09', '2026-06-28 18:14:14.157497+00', '2026-06-28 18:14:14.157497+00');
INSERT INTO song_artist (id_song, id_artist, created_at, updated_at) VALUES ('S_JUICY_01', 'A_10', '2026-06-28 18:14:14.157497+00', '2026-06-28 18:14:14.157497+00');
INSERT INTO song_artist (id_song, id_artist, created_at, updated_at) VALUES ('S_JUICY_02', 'A_10', '2026-06-28 18:14:14.157497+00', '2026-06-28 18:14:14.157497+00');
INSERT INTO song_artist (id_song, id_artist, created_at, updated_at) VALUES ('S_MAUDY_01', 'A_11', '2026-06-28 18:14:14.157497+00', '2026-06-28 18:14:14.157497+00');
INSERT INTO song_artist (id_song, id_artist, created_at, updated_at) VALUES ('S_MAUDY_02', 'A_11', '2026-06-28 18:14:14.157497+00', '2026-06-28 18:14:14.157497+00');
INSERT INTO song_artist (id_song, id_artist, created_at, updated_at) VALUES ('S_TENXI_01', 'A_12', '2026-06-28 18:14:14.157497+00', '2026-06-28 18:14:14.157497+00');
INSERT INTO song_artist (id_song, id_artist, created_at, updated_at) VALUES ('S_TENXI_02', 'A_12', '2026-06-28 18:14:14.157497+00', '2026-06-28 18:14:14.157497+00');
INSERT INTO song_artist (id_song, id_artist, created_at, updated_at) VALUES ('S_DEWA_01', 'A_13', '2026-06-28 18:14:14.157497+00', '2026-06-28 18:14:14.157497+00');
INSERT INTO song_artist (id_song, id_artist, created_at, updated_at) VALUES ('S_DEWA_02', 'A_13', '2026-06-28 18:14:14.157497+00', '2026-06-28 18:14:14.157497+00');
INSERT INTO song_artist (id_song, id_artist, created_at, updated_at) VALUES ('S_BILLIE_01', 'A_14', '2026-06-28 18:14:14.157497+00', '2026-06-28 18:14:14.157497+00');
INSERT INTO song_artist (id_song, id_artist, created_at, updated_at) VALUES ('S_BILLIE_02', 'A_14', '2026-06-28 18:14:14.157497+00', '2026-06-28 18:14:14.157497+00');
INSERT INTO song_artist (id_song, id_artist, created_at, updated_at) VALUES ('S_CHARLIE_01', 'A_15', '2026-06-28 18:14:14.157497+00', '2026-06-28 18:14:14.157497+00');
INSERT INTO song_artist (id_song, id_artist, created_at, updated_at) VALUES ('S_CHARLIE_02', 'A_15', '2026-06-28 18:14:14.157497+00', '2026-06-28 18:14:14.157497+00');
INSERT INTO song_artist (id_song, id_artist, created_at, updated_at) VALUES ('S_BRIAN_01', 'A_16', '2026-06-28 18:14:14.157497+00', '2026-06-28 18:14:14.157497+00');
INSERT INTO song_artist (id_song, id_artist, created_at, updated_at) VALUES ('S_BRIAN_02', 'A_16', '2026-06-28 18:14:14.157497+00', '2026-06-28 18:14:14.157497+00');
INSERT INTO song_artist (id_song, id_artist, created_at, updated_at) VALUES ('S_JOJI_01', 'A_17', '2026-06-28 18:14:14.157497+00', '2026-06-28 18:14:14.157497+00');
INSERT INTO song_artist (id_song, id_artist, created_at, updated_at) VALUES ('S_JOJI_02', 'A_17', '2026-06-28 18:14:14.157497+00', '2026-06-28 18:14:14.157497+00');

-- Data for: artist_album
INSERT INTO artist_album (id_artist, id_album, created_at, updated_at) VALUES ('A_01', 'ALB_01', '2026-06-26 19:54:49.101417+00', '2026-06-26 19:54:49.101417+00');
INSERT INTO artist_album (id_artist, id_album, created_at, updated_at) VALUES ('A_03', 'ALB_02', '2026-06-26 19:54:49.101417+00', '2026-06-26 19:54:49.101417+00');
INSERT INTO artist_album (id_artist, id_album, created_at, updated_at) VALUES ('A_04', 'ALB_03', '2026-06-26 19:54:49.101417+00', '2026-06-26 19:54:49.101417+00');
INSERT INTO artist_album (id_artist, id_album, created_at, updated_at) VALUES ('A_01', 'c625d477-b844-4430-8040-32b43375bf56', '2026-06-26 19:54:49.101417+00', '2026-06-26 19:54:49.101417+00');
INSERT INTO artist_album (id_artist, id_album, created_at, updated_at) VALUES ('A_02', '0911b737-157c-4bfd-bb56-45ca524a1190', '2026-06-26 19:54:49.101417+00', '2026-06-26 19:54:49.101417+00');
INSERT INTO artist_album (id_artist, id_album, created_at, updated_at) VALUES ('A_02', 'd10c3ed3-55a9-44a0-84f4-89a4c99e9d5c', '2026-06-26 19:54:49.101417+00', '2026-06-26 19:54:49.101417+00');
INSERT INTO artist_album (id_artist, id_album, created_at, updated_at) VALUES ('A_02', 'a119e13c-580b-4ddb-b2ef-7a1341238464', '2026-06-26 19:54:49.101417+00', '2026-06-26 19:54:49.101417+00');
INSERT INTO artist_album (id_artist, id_album, created_at, updated_at) VALUES ('A_02', '2c468183-bcfa-4406-8b65-5febdfa6237f', '2026-06-26 19:54:49.101417+00', '2026-06-26 19:54:49.101417+00');
INSERT INTO artist_album (id_artist, id_album, created_at, updated_at) VALUES ('A_04', '236a974f-fd58-4c5c-9e17-5711d43fcf79', '2026-06-26 19:54:49.101417+00', '2026-06-26 19:54:49.101417+00');
INSERT INTO artist_album (id_artist, id_album, created_at, updated_at) VALUES ('A_04', '7c54ead0-43de-48e6-bfcc-b4a59e3640af', '2026-06-26 19:54:49.101417+00', '2026-06-26 19:54:49.101417+00');
INSERT INTO artist_album (id_artist, id_album, created_at, updated_at) VALUES ('A_04', 'ee1d1302-8a7b-4281-8299-136491fadc4c', '2026-06-26 19:54:49.101417+00', '2026-06-26 19:54:49.101417+00');
INSERT INTO artist_album (id_artist, id_album, created_at, updated_at) VALUES ('A_06', '6b494f68-6fab-4a61-8522-f1c80d6dfac1', '2026-06-26 19:54:49.101417+00', '2026-06-26 19:54:49.101417+00');
INSERT INTO artist_album (id_artist, id_album, created_at, updated_at) VALUES ('A_06', '2c31f2e7-fcbf-4b49-bc06-6bc91ab27703', '2026-06-26 19:54:49.101417+00', '2026-06-26 19:54:49.101417+00');
INSERT INTO artist_album (id_artist, id_album, created_at, updated_at) VALUES ('A_06', '42099e1b-fdca-4bfa-bfc3-2d38f12983f7', '2026-06-26 19:54:49.101417+00', '2026-06-26 19:54:49.101417+00');
INSERT INTO artist_album (id_artist, id_album, created_at, updated_at) VALUES ('A_06', '86cf72ae-9cbb-490a-8819-00b9ef37bd07', '2026-06-26 19:54:49.101417+00', '2026-06-26 19:54:49.101417+00');
INSERT INTO artist_album (id_artist, id_album, created_at, updated_at) VALUES ('A_05', '2fcd55d3-c314-4171-a8d9-cbbd747a88a0', '2026-06-26 19:54:49.101417+00', '2026-06-26 19:54:49.101417+00');
INSERT INTO artist_album (id_artist, id_album, created_at, updated_at) VALUES ('A_05', 'd79b6312-38ad-4f7e-b605-0e65cab1b9ce', '2026-06-26 19:54:49.101417+00', '2026-06-26 19:54:49.101417+00');
INSERT INTO artist_album (id_artist, id_album, created_at, updated_at) VALUES ('A_05', 'dddd1ab3-cadd-45ec-af3d-cfa17ed50310', '2026-06-26 19:54:49.101417+00', '2026-06-26 19:54:49.101417+00');
INSERT INTO artist_album (id_artist, id_album, created_at, updated_at) VALUES ('A_07', '7b2946ce-f644-4047-8197-dc032841b29a', '2026-06-26 19:54:49.101417+00', '2026-06-26 19:54:49.101417+00');
INSERT INTO artist_album (id_artist, id_album, created_at, updated_at) VALUES ('A_07', 'd3e65927-7627-421b-b328-d047dc2f30d1', '2026-06-26 19:54:49.101417+00', '2026-06-26 19:54:49.101417+00');
INSERT INTO artist_album (id_artist, id_album, created_at, updated_at) VALUES ('A_07', '9fc88563-45fe-4a3a-a580-77b1bf3d0505', '2026-06-26 19:54:49.101417+00', '2026-06-26 19:54:49.101417+00');
INSERT INTO artist_album (id_artist, id_album, created_at, updated_at) VALUES ('A_07', '076b5c2d-49c8-4e2d-a98f-4476ca0d58cf', '2026-06-26 19:54:49.101417+00', '2026-06-26 19:54:49.101417+00');
INSERT INTO artist_album (id_artist, id_album, created_at, updated_at) VALUES ('A_08', 'ALB_TULUS', '2026-06-28 18:14:14.157497+00', '2026-06-28 18:14:14.157497+00');
INSERT INTO artist_album (id_artist, id_album, created_at, updated_at) VALUES ('A_09', 'ALB_RIZKY', '2026-06-28 18:14:14.157497+00', '2026-06-28 18:14:14.157497+00');
INSERT INTO artist_album (id_artist, id_album, created_at, updated_at) VALUES ('A_10', 'ALB_JUICY', '2026-06-28 18:14:14.157497+00', '2026-06-28 18:14:14.157497+00');
INSERT INTO artist_album (id_artist, id_album, created_at, updated_at) VALUES ('A_11', 'ALB_MAUDY', '2026-06-28 18:14:14.157497+00', '2026-06-28 18:14:14.157497+00');
INSERT INTO artist_album (id_artist, id_album, created_at, updated_at) VALUES ('A_12', 'ALB_TENXI', '2026-06-28 18:14:14.157497+00', '2026-06-28 18:14:14.157497+00');
INSERT INTO artist_album (id_artist, id_album, created_at, updated_at) VALUES ('A_13', 'ALB_DEWA', '2026-06-28 18:14:14.157497+00', '2026-06-28 18:14:14.157497+00');
INSERT INTO artist_album (id_artist, id_album, created_at, updated_at) VALUES ('A_14', 'ALB_BILLIE', '2026-06-28 18:14:14.157497+00', '2026-06-28 18:14:14.157497+00');
INSERT INTO artist_album (id_artist, id_album, created_at, updated_at) VALUES ('A_15', 'ALB_CHARLIE', '2026-06-28 18:14:14.157497+00', '2026-06-28 18:14:14.157497+00');
INSERT INTO artist_album (id_artist, id_album, created_at, updated_at) VALUES ('A_16', 'ALB_BRIAN', '2026-06-28 18:14:14.157497+00', '2026-06-28 18:14:14.157497+00');
INSERT INTO artist_album (id_artist, id_album, created_at, updated_at) VALUES ('A_17', 'ALB_JOJI', '2026-06-28 18:14:14.157497+00', '2026-06-28 18:14:14.157497+00');

-- Data for: artist_concert
INSERT INTO artist_concert (id_artist, id_concert, created_at, updated_at) VALUES ('A_01', '03c99390-c98d-457a-84e8-f12b34d139de', '2026-06-26 19:54:49.101417+00', '2026-06-26 19:54:49.101417+00');
INSERT INTO artist_concert (id_artist, id_concert, created_at, updated_at) VALUES ('A_01', 'ebb7bd7b-bfb2-4e89-9763-c26207684a74', '2026-06-26 19:54:49.101417+00', '2026-06-26 19:54:49.101417+00');
INSERT INTO artist_concert (id_artist, id_concert, created_at, updated_at) VALUES ('A_04', 'ebb7bd7b-bfb2-4e89-9763-c26207684a74', '2026-06-26 19:54:49.101417+00', '2026-06-26 19:54:49.101417+00');
INSERT INTO artist_concert (id_artist, id_concert, created_at, updated_at) VALUES ('A_02', '2811e691-103e-46d9-b4ab-9d50d98d2195', '2026-06-26 19:54:49.101417+00', '2026-06-26 19:54:49.101417+00');
INSERT INTO artist_concert (id_artist, id_concert, created_at, updated_at) VALUES ('A_04', 'ed8e547d-8054-4d01-bb70-37139cfd8415', '2026-06-26 19:54:49.101417+00', '2026-06-26 19:54:49.101417+00');
INSERT INTO artist_concert (id_artist, id_concert, created_at, updated_at) VALUES ('A_06', '969c390f-806f-409f-9090-e2787a3197c8', '2026-06-26 19:54:49.101417+00', '2026-06-26 19:54:49.101417+00');
INSERT INTO artist_concert (id_artist, id_concert, created_at, updated_at) VALUES ('A_07', '8cea2e8c-b560-47df-a598-825290e7381b', '2026-06-26 19:54:49.101417+00', '2026-06-26 19:54:49.101417+00');
INSERT INTO artist_concert (id_artist, id_concert, created_at, updated_at) VALUES ('A_07', '92d68c30-acb3-4cf5-bd50-e2c3058a576f', '2026-06-26 19:54:49.101417+00', '2026-06-26 19:54:49.101417+00');
INSERT INTO artist_concert (id_artist, id_concert, created_at, updated_at) VALUES ('A_05', '9f19d270-3f3f-40da-a583-74543c06f258', '2026-06-26 19:54:49.101417+00', '2026-06-26 19:54:49.101417+00');
INSERT INTO artist_concert (id_artist, id_concert, created_at, updated_at) VALUES ('A_08', 'CON_TULUS', '2026-06-28 18:14:14.157497+00', '2026-06-28 18:14:14.157497+00');
INSERT INTO artist_concert (id_artist, id_concert, created_at, updated_at) VALUES ('A_09', 'CON_RIZKY', '2026-06-28 18:14:14.157497+00', '2026-06-28 18:14:14.157497+00');
INSERT INTO artist_concert (id_artist, id_concert, created_at, updated_at) VALUES ('A_13', 'CON_DEWA', '2026-06-28 18:14:14.157497+00', '2026-06-28 18:14:14.157497+00');
INSERT INTO artist_concert (id_artist, id_concert, created_at, updated_at) VALUES ('A_14', 'CON_BILLIE', '2026-06-28 18:14:14.157497+00', '2026-06-28 18:14:14.157497+00');
INSERT INTO artist_concert (id_artist, id_concert, created_at, updated_at) VALUES ('A_17', 'CON_JOJI', '2026-06-28 18:14:14.157497+00', '2026-06-28 18:14:14.157497+00');

-- Data for: playlist_song
INSERT INTO playlist_song (id_playlist, id_song, created_at, updated_at) VALUES ('PL_01', 'S_03', '2026-06-26 19:54:49.101417+00', '2026-06-26 19:54:49.101417+00');
INSERT INTO playlist_song (id_playlist, id_song, created_at, updated_at) VALUES ('PL_RANDOM_01', 'S_TULUS_01', '2026-06-28 18:14:14.157497+00', '2026-06-28 18:14:14.157497+00');
INSERT INTO playlist_song (id_playlist, id_song, created_at, updated_at) VALUES ('PL_RANDOM_01', 'S_JUICY_01', '2026-06-28 18:14:14.157497+00', '2026-06-28 18:14:14.157497+00');
INSERT INTO playlist_song (id_playlist, id_song, created_at, updated_at) VALUES ('PL_RANDOM_01', 'S_MAUDY_01', '2026-06-28 18:14:14.157497+00', '2026-06-28 18:14:14.157497+00');
INSERT INTO playlist_song (id_playlist, id_song, created_at, updated_at) VALUES ('PL_RANDOM_01', 'S_TULUS_02', '2026-06-28 18:14:14.157497+00', '2026-06-28 18:14:14.157497+00');
INSERT INTO playlist_song (id_playlist, id_song, created_at, updated_at) VALUES ('PL_RANDOM_01', 'S_03', '2026-06-28 18:14:14.157497+00', '2026-06-28 18:14:14.157497+00');
INSERT INTO playlist_song (id_playlist, id_song, created_at, updated_at) VALUES ('PL_RANDOM_02', 'S_BILLIE_01', '2026-06-28 18:14:14.157497+00', '2026-06-28 18:14:14.157497+00');
INSERT INTO playlist_song (id_playlist, id_song, created_at, updated_at) VALUES ('PL_RANDOM_02', 'S_CHARLIE_01', '2026-06-28 18:14:14.157497+00', '2026-06-28 18:14:14.157497+00');
INSERT INTO playlist_song (id_playlist, id_song, created_at, updated_at) VALUES ('PL_RANDOM_02', 'S_JOJI_01', '2026-06-28 18:14:14.157497+00', '2026-06-28 18:14:14.157497+00');
INSERT INTO playlist_song (id_playlist, id_song, created_at, updated_at) VALUES ('PL_RANDOM_02', 'S_01', '2026-06-28 18:14:14.157497+00', '2026-06-28 18:14:14.157497+00');
INSERT INTO playlist_song (id_playlist, id_song, created_at, updated_at) VALUES ('PL_RANDOM_02', 'S_BILLIE_02', '2026-06-28 18:14:14.157497+00', '2026-06-28 18:14:14.157497+00');
INSERT INTO playlist_song (id_playlist, id_song, created_at, updated_at) VALUES ('PL_RANDOM_03', 'S_DEWA_01', '2026-06-28 18:14:14.157497+00', '2026-06-28 18:14:14.157497+00');
INSERT INTO playlist_song (id_playlist, id_song, created_at, updated_at) VALUES ('PL_RANDOM_03', 'S_DEWA_02', '2026-06-28 18:14:14.157497+00', '2026-06-28 18:14:14.157497+00');
INSERT INTO playlist_song (id_playlist, id_song, created_at, updated_at) VALUES ('PL_RANDOM_03', 'S_TULUS_01', '2026-06-28 18:14:14.157497+00', '2026-06-28 18:14:14.157497+00');

-- Data for: playlist_collaborator
INSERT INTO playlist_collaborator (id_playlist, id_user, role, created_at, updated_at) VALUES ('9350e255-f632-470d-a50c-a037077b602a', 'e0362ced-2fbe-4f25-8cb4-433c8c8a8bdf', 'Owner', '2026-06-26 19:54:49.101417+00', '2026-06-26 19:54:49.101417+00');
INSERT INTO playlist_collaborator (id_playlist, id_user, role, created_at, updated_at) VALUES ('210a18b4-de07-4ee0-9682-0d26720a7075', 'e0362ced-2fbe-4f25-8cb4-433c8c8a8bdf', 'Owner', '2026-06-26 19:54:49.101417+00', '2026-06-26 19:54:49.101417+00');
INSERT INTO playlist_collaborator (id_playlist, id_user, role, created_at, updated_at) VALUES ('70142d91-141c-45ac-9fbe-d9eb1a5204b5', 'e0362ced-2fbe-4f25-8cb4-433c8c8a8bdf', 'Owner', '2026-06-26 19:54:49.101417+00', '2026-06-26 19:54:49.101417+00');
INSERT INTO playlist_collaborator (id_playlist, id_user, role, created_at, updated_at) VALUES ('PL_RANDOM_01', 'c75b7ee7-b21f-4105-a7f5-d5278bc38283', 'Owner', '2026-06-28 18:14:14.157497+00', '2026-06-28 18:14:14.157497+00');
INSERT INTO playlist_collaborator (id_playlist, id_user, role, created_at, updated_at) VALUES ('PL_RANDOM_02', '04d8d10e-2b96-4c65-a17a-f0bde9dbe72c', 'Owner', '2026-06-28 18:14:14.157497+00', '2026-06-28 18:14:14.157497+00');
INSERT INTO playlist_collaborator (id_playlist, id_user, role, created_at, updated_at) VALUES ('PL_RANDOM_03', '83e6d65e-5e46-4019-9bbb-aa1b712e1c48', 'Owner', '2026-06-28 18:14:14.157497+00', '2026-06-28 18:14:14.157497+00');

-- Data for: user_likedsong
INSERT INTO user_likedsong (id_user, id_song, created_at, updated_at) VALUES ('e0362ced-2fbe-4f25-8cb4-433c8c8a8bdf', 'S_03', '2026-06-26 19:54:49.101417+00', '2026-06-26 19:54:49.101417+00');
INSERT INTO user_likedsong (id_user, id_song, created_at, updated_at) VALUES ('90b0f848-6607-4497-88ff-97ed75c71390', 'S_05', '2026-06-26 19:54:49.101417+00', '2026-06-26 19:54:49.101417+00');
INSERT INTO user_likedsong (id_user, id_song, created_at, updated_at) VALUES ('90b0f848-6607-4497-88ff-97ed75c71390', '86da840a-ac14-4400-9a01-1896c9544fac', '2026-06-26 19:54:49.101417+00', '2026-06-26 19:54:49.101417+00');
INSERT INTO user_likedsong (id_user, id_song, created_at, updated_at) VALUES ('90b0f848-6607-4497-88ff-97ed75c71390', 'd9b753ab-1077-4f5f-8f8a-9bf9844cf194', '2026-06-26 19:54:49.101417+00', '2026-06-26 19:54:49.101417+00');
INSERT INTO user_likedsong (id_user, id_song, created_at, updated_at) VALUES ('e0362ced-2fbe-4f25-8cb4-433c8c8a8bdf', 'S_TULUS_01', '2026-06-28 18:14:14.157497+00', '2026-06-28 18:14:14.157497+00');
INSERT INTO user_likedsong (id_user, id_song, created_at, updated_at) VALUES ('e0362ced-2fbe-4f25-8cb4-433c8c8a8bdf', 'S_DEWA_02', '2026-06-28 18:14:14.157497+00', '2026-06-28 18:14:14.157497+00');
INSERT INTO user_likedsong (id_user, id_song, created_at, updated_at) VALUES ('c75b7ee7-b21f-4105-a7f5-d5278bc38283', 'S_JUICY_01', '2026-06-28 18:14:14.157497+00', '2026-06-28 18:14:14.157497+00');
INSERT INTO user_likedsong (id_user, id_song, created_at, updated_at) VALUES ('04d8d10e-2b96-4c65-a17a-f0bde9dbe72c', 'S_BILLIE_02', '2026-06-28 18:14:14.157497+00', '2026-06-28 18:14:14.157497+00');
INSERT INTO user_likedsong (id_user, id_song, created_at, updated_at) VALUES ('83e6d65e-5e46-4019-9bbb-aa1b712e1c48', 'S_DEWA_01', '2026-06-28 18:14:14.157497+00', '2026-06-28 18:14:14.157497+00');
INSERT INTO user_likedsong (id_user, id_song, created_at, updated_at) VALUES ('691b4806-8271-4857-a588-501c27fff15d', 'S_MAUDY_01', '2026-06-28 18:14:14.157497+00', '2026-06-28 18:14:14.157497+00');

-- Data for: user_followartist
INSERT INTO user_followartist (id_user, id_artist, created_at, updated_at) VALUES ('e0362ced-2fbe-4f25-8cb4-433c8c8a8bdf', 'A_08', '2026-06-28 18:14:14.157497+00', '2026-06-28 18:14:14.157497+00');
INSERT INTO user_followartist (id_user, id_artist, created_at, updated_at) VALUES ('e0362ced-2fbe-4f25-8cb4-433c8c8a8bdf', 'A_13', '2026-06-28 18:14:14.157497+00', '2026-06-28 18:14:14.157497+00');
INSERT INTO user_followartist (id_user, id_artist, created_at, updated_at) VALUES ('c75b7ee7-b21f-4105-a7f5-d5278bc38283', 'A_08', '2026-06-28 18:14:14.157497+00', '2026-06-28 18:14:14.157497+00');
INSERT INTO user_followartist (id_user, id_artist, created_at, updated_at) VALUES ('c75b7ee7-b21f-4105-a7f5-d5278bc38283', 'A_10', '2026-06-28 18:14:14.157497+00', '2026-06-28 18:14:14.157497+00');
INSERT INTO user_followartist (id_user, id_artist, created_at, updated_at) VALUES ('04d8d10e-2b96-4c65-a17a-f0bde9dbe72c', 'A_14', '2026-06-28 18:14:14.157497+00', '2026-06-28 18:14:14.157497+00');
INSERT INTO user_followartist (id_user, id_artist, created_at, updated_at) VALUES ('04d8d10e-2b96-4c65-a17a-f0bde9dbe72c', 'A_17', '2026-06-28 18:14:14.157497+00', '2026-06-28 18:14:14.157497+00');
INSERT INTO user_followartist (id_user, id_artist, created_at, updated_at) VALUES ('83e6d65e-5e46-4019-9bbb-aa1b712e1c48', 'A_13', '2026-06-28 18:14:14.157497+00', '2026-06-28 18:14:14.157497+00');
INSERT INTO user_followartist (id_user, id_artist, created_at, updated_at) VALUES ('691b4806-8271-4857-a588-501c27fff15d', 'A_11', '2026-06-28 18:14:14.157497+00', '2026-06-28 18:14:14.157497+00');

-- Data for: user_followplaylist
-- (No data)

-- Data for: user_followpodcast
INSERT INTO user_followpodcast (id_user, id_podcast, created_at, updated_at) VALUES ('e0362ced-2fbe-4f25-8cb4-433c8c8a8bdf', 'POD_01', '2026-06-26 19:54:49.101417+00', '2026-06-26 19:54:49.101417+00');

-- Data for: user_followuser
-- (No data)

-- Data for: song_streaming_histories
INSERT INTO song_streaming_histories (id_stream, played_at, play_duration, id_user, id_song, created_at, updated_at) VALUES ('989a5658-49e3-4a57-852c-8a66156b1228', '2026-06-09 20:10:14', NULL, 'e0362ced-2fbe-4f25-8cb4-433c8c8a8bdf', 'S_03', '2026-06-26 19:54:49.101417+00', '2026-06-26 19:54:49.101417+00');
INSERT INTO song_streaming_histories (id_stream, played_at, play_duration, id_user, id_song, created_at, updated_at) VALUES ('8bb0d979-0fed-45c2-bd55-95467f043fcd', '2026-06-09 20:11:12', NULL, 'e0362ced-2fbe-4f25-8cb4-433c8c8a8bdf', 'S_999', '2026-06-26 19:54:49.101417+00', '2026-06-26 19:54:49.101417+00');
INSERT INTO song_streaming_histories (id_stream, played_at, play_duration, id_user, id_song, created_at, updated_at) VALUES ('ad47337e-9598-46ee-9f62-6d4ee377dc8d', '2026-06-09 20:11:13', NULL, 'e0362ced-2fbe-4f25-8cb4-433c8c8a8bdf', 'S_05', '2026-06-26 19:54:49.101417+00', '2026-06-26 19:54:49.101417+00');
INSERT INTO song_streaming_histories (id_stream, played_at, play_duration, id_user, id_song, created_at, updated_at) VALUES ('28b5761a-dfe9-43a0-a5be-ace048aea743', '2026-06-09 20:13:51', NULL, 'e0362ced-2fbe-4f25-8cb4-433c8c8a8bdf', 'S_03', '2026-06-26 19:54:49.101417+00', '2026-06-26 19:54:49.101417+00');
INSERT INTO song_streaming_histories (id_stream, played_at, play_duration, id_user, id_song, created_at, updated_at) VALUES ('356e955e-2840-4914-ad17-cf6c1dfd58e6', '2026-06-09 20:13:52', NULL, 'e0362ced-2fbe-4f25-8cb4-433c8c8a8bdf', 'S_05', '2026-06-26 19:54:49.101417+00', '2026-06-26 19:54:49.101417+00');
INSERT INTO song_streaming_histories (id_stream, played_at, play_duration, id_user, id_song, created_at, updated_at) VALUES ('7d385182-7004-4c92-bb2b-3673efa25c3b', '2026-06-09 20:13:54', NULL, 'e0362ced-2fbe-4f25-8cb4-433c8c8a8bdf', 'S_999', '2026-06-26 19:54:49.101417+00', '2026-06-26 19:54:49.101417+00');
INSERT INTO song_streaming_histories (id_stream, played_at, play_duration, id_user, id_song, created_at, updated_at) VALUES ('d1705a3d-434f-436f-bb3c-f9e87ff7e462', '2026-06-09 20:13:55', NULL, 'e0362ced-2fbe-4f25-8cb4-433c8c8a8bdf', 'S_01', '2026-06-26 19:54:49.101417+00', '2026-06-26 19:54:49.101417+00');
INSERT INTO song_streaming_histories (id_stream, played_at, play_duration, id_user, id_song, created_at, updated_at) VALUES ('1e0ae4e9-7c0d-4bc2-baf1-be129a756d82', '2026-06-09 20:13:57', NULL, 'e0362ced-2fbe-4f25-8cb4-433c8c8a8bdf', 'S_999', '2026-06-26 19:54:49.101417+00', '2026-06-26 19:54:49.101417+00');
INSERT INTO song_streaming_histories (id_stream, played_at, play_duration, id_user, id_song, created_at, updated_at) VALUES ('47b72344-91f4-4e8d-a94c-37d93ff58ef1', '2026-06-09 20:13:58', NULL, 'e0362ced-2fbe-4f25-8cb4-433c8c8a8bdf', 'S_05', '2026-06-26 19:54:49.101417+00', '2026-06-26 19:54:49.101417+00');
INSERT INTO song_streaming_histories (id_stream, played_at, play_duration, id_user, id_song, created_at, updated_at) VALUES ('3bdbaddd-8383-4e89-9b95-74e5329fa8c3', '2026-06-09 22:06:01', NULL, 'e0362ced-2fbe-4f25-8cb4-433c8c8a8bdf', 'S_03', '2026-06-26 19:54:49.101417+00', '2026-06-26 19:54:49.101417+00');
INSERT INTO song_streaming_histories (id_stream, played_at, play_duration, id_user, id_song, created_at, updated_at) VALUES ('96c580ef-40ed-41ab-98c5-62043657c68b', '2026-06-09 22:07:01', NULL, 'e0362ced-2fbe-4f25-8cb4-433c8c8a8bdf', 'S_03', '2026-06-26 19:54:49.101417+00', '2026-06-26 19:54:49.101417+00');
INSERT INTO song_streaming_histories (id_stream, played_at, play_duration, id_user, id_song, created_at, updated_at) VALUES ('abe2bb56-26c4-4093-9b50-35eb444139a0', '2026-06-09 22:13:04', NULL, 'e0362ced-2fbe-4f25-8cb4-433c8c8a8bdf', 'S_03', '2026-06-26 19:54:49.101417+00', '2026-06-26 19:54:49.101417+00');
INSERT INTO song_streaming_histories (id_stream, played_at, play_duration, id_user, id_song, created_at, updated_at) VALUES ('cc962a50-cbda-4922-b53a-3239912e33eb', '2026-06-09 22:14:09', NULL, 'e0362ced-2fbe-4f25-8cb4-433c8c8a8bdf', 'S_05', '2026-06-26 19:54:49.101417+00', '2026-06-26 19:54:49.101417+00');
INSERT INTO song_streaming_histories (id_stream, played_at, play_duration, id_user, id_song, created_at, updated_at) VALUES ('d0a4c912-7028-451a-96b5-af3d344b22b6', '2026-06-09 22:14:44', '4', 'e0362ced-2fbe-4f25-8cb4-433c8c8a8bdf', 'S_999', '2026-06-26 19:54:49.101417+00', '2026-06-26 19:54:49.101417+00');
INSERT INTO song_streaming_histories (id_stream, played_at, play_duration, id_user, id_song, created_at, updated_at) VALUES ('a527d115-7143-4da8-9849-61ee7af97293', '2026-06-12 10:24:06', NULL, 'e0362ced-2fbe-4f25-8cb4-433c8c8a8bdf', 'S_05', '2026-06-26 19:54:49.101417+00', '2026-06-26 19:54:49.101417+00');
INSERT INTO song_streaming_histories (id_stream, played_at, play_duration, id_user, id_song, created_at, updated_at) VALUES ('80ab2c6f-7103-4cda-84f9-cad49a1af03c', '2026-06-12 10:35:24', NULL, 'e0362ced-2fbe-4f25-8cb4-433c8c8a8bdf', 'S_05', '2026-06-26 19:54:49.101417+00', '2026-06-26 19:54:49.101417+00');
INSERT INTO song_streaming_histories (id_stream, played_at, play_duration, id_user, id_song, created_at, updated_at) VALUES ('f27c07c1-f3b1-4b3a-95ab-7a84b74bd5bf', '2026-06-12 12:44:27', '4', 'e0362ced-2fbe-4f25-8cb4-433c8c8a8bdf', 'S_999', '2026-06-26 19:54:49.101417+00', '2026-06-26 19:54:49.101417+00');
INSERT INTO song_streaming_histories (id_stream, played_at, play_duration, id_user, id_song, created_at, updated_at) VALUES ('3bc88b32-3693-4cd0-bb27-e2091b6a1ff8', '2026-06-12 12:44:36', '6', 'e0362ced-2fbe-4f25-8cb4-433c8c8a8bdf', 'S_03', '2026-06-26 19:54:49.101417+00', '2026-06-26 19:54:49.101417+00');
INSERT INTO song_streaming_histories (id_stream, played_at, play_duration, id_user, id_song, created_at, updated_at) VALUES ('8b343466-5938-4154-ab20-e5f0e8f35a12', '2026-06-12 12:44:48', NULL, 'e0362ced-2fbe-4f25-8cb4-433c8c8a8bdf', 'S_01', '2026-06-26 19:54:49.101417+00', '2026-06-26 19:54:49.101417+00');
INSERT INTO song_streaming_histories (id_stream, played_at, play_duration, id_user, id_song, created_at, updated_at) VALUES ('7f3df93b-367a-4a09-aeae-10aa7d638d1b', '2026-06-12 15:21:56', NULL, 'c75b7ee7-b21f-4105-a7f5-d5278bc38283', 'S_01', '2026-06-26 19:54:49.101417+00', '2026-06-26 19:54:49.101417+00');
INSERT INTO song_streaming_histories (id_stream, played_at, play_duration, id_user, id_song, created_at, updated_at) VALUES ('3d51f12f-0560-4a44-a01e-f284a1bb45fb', '2026-06-12 21:23:31', '146', 'e0362ced-2fbe-4f25-8cb4-433c8c8a8bdf', 'S_05', '2026-06-26 19:54:49.101417+00', '2026-06-26 19:54:49.101417+00');
INSERT INTO song_streaming_histories (id_stream, played_at, play_duration, id_user, id_song, created_at, updated_at) VALUES ('415945fb-6a02-4076-aeec-9943f068d14a', '2026-06-12 21:26:02', '13', 'e0362ced-2fbe-4f25-8cb4-433c8c8a8bdf', 'S_01', '2026-06-26 19:54:49.101417+00', '2026-06-26 19:54:49.101417+00');
INSERT INTO song_streaming_histories (id_stream, played_at, play_duration, id_user, id_song, created_at, updated_at) VALUES ('ee82950b-7671-49bd-beb0-fd58c6d7d6e5', '2026-06-12 21:26:19', NULL, 'e0362ced-2fbe-4f25-8cb4-433c8c8a8bdf', 'S_02', '2026-06-26 19:54:49.101417+00', '2026-06-26 19:54:49.101417+00');
INSERT INTO song_streaming_histories (id_stream, played_at, play_duration, id_user, id_song, created_at, updated_at) VALUES ('8aae92b3-e10f-4890-bafd-5c77a8e26d59', '2026-06-12 22:46:52', NULL, 'e0362ced-2fbe-4f25-8cb4-433c8c8a8bdf', 'S_05', '2026-06-26 19:54:49.101417+00', '2026-06-26 19:54:49.101417+00');
INSERT INTO song_streaming_histories (id_stream, played_at, play_duration, id_user, id_song, created_at, updated_at) VALUES ('a338e8fe-be43-4c4a-a990-99ca4571a085', '2026-06-14 01:57:14', NULL, 'e0362ced-2fbe-4f25-8cb4-433c8c8a8bdf', 'S_01', '2026-06-26 19:54:49.101417+00', '2026-06-26 19:54:49.101417+00');
INSERT INTO song_streaming_histories (id_stream, played_at, play_duration, id_user, id_song, created_at, updated_at) VALUES ('122e4109-ec6b-41ce-9478-832b05f36c5b', '2026-06-14 02:22:20', NULL, 'e0362ced-2fbe-4f25-8cb4-433c8c8a8bdf', 'S_03', '2026-06-26 19:54:49.101417+00', '2026-06-26 19:54:49.101417+00');
INSERT INTO song_streaming_histories (id_stream, played_at, play_duration, id_user, id_song, created_at, updated_at) VALUES ('54e6b2f8-d499-41b4-b6a8-79e44df3b297', '2026-06-14 02:24:53', NULL, 'e0362ced-2fbe-4f25-8cb4-433c8c8a8bdf', 'S_02', '2026-06-26 19:54:49.101417+00', '2026-06-26 19:54:49.101417+00');
INSERT INTO song_streaming_histories (id_stream, played_at, play_duration, id_user, id_song, created_at, updated_at) VALUES ('b0fe0961-095c-4fab-8736-ceea76e01c41', '2026-06-14 16:34:10', '6', '83e6d65e-5e46-4019-9bbb-aa1b712e1c48', '212ede59-e53c-4f26-bfdb-c62599aabbb6', '2026-06-26 19:54:49.101417+00', '2026-06-26 19:54:49.101417+00');
INSERT INTO song_streaming_histories (id_stream, played_at, play_duration, id_user, id_song, created_at, updated_at) VALUES ('b44b554f-d477-40ae-8287-49c98511a0ec', '2026-06-14 16:55:40', '39', '90b0f848-6607-4497-88ff-97ed75c71390', '8f6dfd0d-d09e-4056-aca6-1ec3721c9274', '2026-06-26 19:54:49.101417+00', '2026-06-26 19:54:49.101417+00');
INSERT INTO song_streaming_histories (id_stream, played_at, play_duration, id_user, id_song, created_at, updated_at) VALUES ('9e249d83-a979-4763-a744-fc789bf33076', '2026-06-14 16:56:25', NULL, '90b0f848-6607-4497-88ff-97ed75c71390', 'S_05', '2026-06-26 19:54:49.101417+00', '2026-06-26 19:54:49.101417+00');
INSERT INTO song_streaming_histories (id_stream, played_at, play_duration, id_user, id_song, created_at, updated_at) VALUES ('785bca75-f64b-4c30-b7f7-248a3b6fd360', '2026-06-15 20:00:22', NULL, 'e0362ced-2fbe-4f25-8cb4-433c8c8a8bdf', 'S_05', '2026-06-26 19:54:49.101417+00', '2026-06-26 19:54:49.101417+00');
INSERT INTO song_streaming_histories (id_stream, played_at, play_duration, id_user, id_song, created_at, updated_at) VALUES ('ba0b549d-1a74-4dca-a6a0-806f57414ee4', '2026-06-15 20:01:05', NULL, 'e0362ced-2fbe-4f25-8cb4-433c8c8a8bdf', 'S_05', '2026-06-26 19:54:49.101417+00', '2026-06-26 19:54:49.101417+00');
INSERT INTO song_streaming_histories (id_stream, played_at, play_duration, id_user, id_song, created_at, updated_at) VALUES ('fe3e4843-2faf-4a05-897d-d192daccf6d7', '2026-06-15 20:01:07', NULL, 'e0362ced-2fbe-4f25-8cb4-433c8c8a8bdf', '86da840a-ac14-4400-9a01-1896c9544fac', '2026-06-26 19:54:49.101417+00', '2026-06-26 19:54:49.101417+00');
INSERT INTO song_streaming_histories (id_stream, played_at, play_duration, id_user, id_song, created_at, updated_at) VALUES ('cf4b5a48-ba58-4387-8a92-5f894662dbb7', '2026-06-15 20:01:09', NULL, 'e0362ced-2fbe-4f25-8cb4-433c8c8a8bdf', '05958114-dd3c-4804-86cd-a9b42e57b51b', '2026-06-26 19:54:49.101417+00', '2026-06-26 19:54:49.101417+00');
INSERT INTO song_streaming_histories (id_stream, played_at, play_duration, id_user, id_song, created_at, updated_at) VALUES ('7e3326a9-eb80-4cbc-96e2-d1ac17728907', '2026-06-15 20:01:46', NULL, 'e0362ced-2fbe-4f25-8cb4-433c8c8a8bdf', '9d747333-b9dc-4bbe-b805-9322e41b6b0f', '2026-06-26 19:54:49.101417+00', '2026-06-26 19:54:49.101417+00');
INSERT INTO song_streaming_histories (id_stream, played_at, play_duration, id_user, id_song, created_at, updated_at) VALUES ('e8bad6b2-5fd7-4de1-9012-46d68f711372', '2026-06-15 20:01:50', NULL, 'e0362ced-2fbe-4f25-8cb4-433c8c8a8bdf', 'd9b753ab-1077-4f5f-8f8a-9bf9844cf194', '2026-06-26 19:54:49.101417+00', '2026-06-26 19:54:49.101417+00');
INSERT INTO song_streaming_histories (id_stream, played_at, play_duration, id_user, id_song, created_at, updated_at) VALUES ('dd01d73a-e55d-428f-b74d-ddda60f48d8b', '2026-06-18 23:37:41', NULL, 'e0362ced-2fbe-4f25-8cb4-433c8c8a8bdf', 'd9b753ab-1077-4f5f-8f8a-9bf9844cf194', '2026-06-26 19:54:49.101417+00', '2026-06-26 19:54:49.101417+00');
INSERT INTO song_streaming_histories (id_stream, played_at, play_duration, id_user, id_song, created_at, updated_at) VALUES ('aadbf7a4-a0ae-4a53-9e13-bd818273e401', '2026-06-18 23:37:43', NULL, 'e0362ced-2fbe-4f25-8cb4-433c8c8a8bdf', 'ce5edf44-9e71-40a8-b4ba-0a4cdfb98777', '2026-06-26 19:54:49.101417+00', '2026-06-26 19:54:49.101417+00');
INSERT INTO song_streaming_histories (id_stream, played_at, play_duration, id_user, id_song, created_at, updated_at) VALUES ('bd6a6f66-2ed4-4a96-b213-bb64b6823a08', '2026-06-18 23:37:45', NULL, 'e0362ced-2fbe-4f25-8cb4-433c8c8a8bdf', 'b99e6d2c-91c2-46a2-a47a-0c1abd2c5b1e', '2026-06-26 19:54:49.101417+00', '2026-06-26 19:54:49.101417+00');
INSERT INTO song_streaming_histories (id_stream, played_at, play_duration, id_user, id_song, created_at, updated_at) VALUES ('5468a4d9-913d-4df9-bffb-0c33ab1e09dc', '2026-06-18 23:40:17', NULL, '54f4fc17-0c13-4382-a069-a716156e4802', 'S_999', '2026-06-26 19:54:49.101417+00', '2026-06-26 19:54:49.101417+00');
INSERT INTO song_streaming_histories (id_stream, played_at, play_duration, id_user, id_song, created_at, updated_at) VALUES ('aac8aa5e-501a-4c26-9887-21853c8197e1', '2026-06-18 23:41:10', NULL, '54f4fc17-0c13-4382-a069-a716156e4802', 'e6144aff-a92b-4979-8b45-d8d87c73c691', '2026-06-26 19:54:49.101417+00', '2026-06-26 19:54:49.101417+00');
INSERT INTO song_streaming_histories (id_stream, played_at, play_duration, id_user, id_song, created_at, updated_at) VALUES ('ad6d7b24-f69f-4395-9e8d-26634ddd8a64', '2026-06-18 23:40:21', NULL, '54f4fc17-0c13-4382-a069-a716156e4802', 'S_03', '2026-06-26 19:54:49.101417+00', '2026-06-26 19:54:49.101417+00');
INSERT INTO song_streaming_histories (id_stream, played_at, play_duration, id_user, id_song, created_at, updated_at) VALUES ('b129b67d-2efb-401a-9b4d-d770a448e751', '2026-06-18 23:40:21', NULL, '54f4fc17-0c13-4382-a069-a716156e4802', 'S_05', '2026-06-26 19:54:49.101417+00', '2026-06-26 19:54:49.101417+00');
INSERT INTO song_streaming_histories (id_stream, played_at, play_duration, id_user, id_song, created_at, updated_at) VALUES ('f5b1ab8e-c9bb-4690-9d16-bab5216449de', '2026-06-18 23:40:21', NULL, '54f4fc17-0c13-4382-a069-a716156e4802', 'e6144aff-a92b-4979-8b45-d8d87c73c691', '2026-06-26 19:54:49.101417+00', '2026-06-26 19:54:49.101417+00');
INSERT INTO song_streaming_histories (id_stream, played_at, play_duration, id_user, id_song, created_at, updated_at) VALUES ('stream_004', '2026-06-20 14:15:00', '198', '04d8d10e-2b96-4c65-a17a-f0bde9dbe72c', 'S_04', '2026-06-28 17:54:33.913988+00', '2026-06-28 17:54:33.913988+00');
INSERT INTO song_streaming_histories (id_stream, played_at, play_duration, id_user, id_song, created_at, updated_at) VALUES ('22ad2575-c098-494f-b7d2-1a04f964d32b', '2026-06-19 13:38:47', NULL, 'e0362ced-2fbe-4f25-8cb4-433c8c8a8bdf', 'S_05', '2026-06-26 19:54:49.101417+00', '2026-06-26 19:54:49.101417+00');
INSERT INTO song_streaming_histories (id_stream, played_at, play_duration, id_user, id_song, created_at, updated_at) VALUES ('f8c5581c-7d89-44b1-8197-1a3ab8052b7a', '2026-06-19 22:26:00', NULL, '90b0f848-6607-4497-88ff-97ed75c71390', 'S_03', '2026-06-26 19:54:49.101417+00', '2026-06-26 19:54:49.101417+00');
INSERT INTO song_streaming_histories (id_stream, played_at, play_duration, id_user, id_song, created_at, updated_at) VALUES ('e62c22d9-a92e-4e83-af1a-48d68f0ec1fb', '2026-06-19 22:25:49', '8', '90b0f848-6607-4497-88ff-97ed75c71390', 'S_05', '2026-06-26 19:54:49.101417+00', '2026-06-26 19:54:49.101417+00');
INSERT INTO song_streaming_histories (id_stream, played_at, play_duration, id_user, id_song, created_at, updated_at) VALUES ('7c58efee-cef3-4300-bc05-50e76b30bdbb', '2026-06-19 22:26:03', NULL, '90b0f848-6607-4497-88ff-97ed75c71390', 'S_999', '2026-06-26 19:54:49.101417+00', '2026-06-26 19:54:49.101417+00');
INSERT INTO song_streaming_histories (id_stream, played_at, play_duration, id_user, id_song, created_at, updated_at) VALUES ('cc2449c1-86e9-46d4-a7bd-0f151d58f939', '2026-06-19 22:26:05', '11', '90b0f848-6607-4497-88ff-97ed75c71390', 'S_01', '2026-06-26 19:54:49.101417+00', '2026-06-26 19:54:49.101417+00');
INSERT INTO song_streaming_histories (id_stream, played_at, play_duration, id_user, id_song, created_at, updated_at) VALUES ('a8249bd9-a010-41b1-bca6-80d87a673758', '2026-06-19 22:26:22', NULL, '90b0f848-6607-4497-88ff-97ed75c71390', 'b99e6d2c-91c2-46a2-a47a-0c1abd2c5b1e', '2026-06-26 19:54:49.101417+00', '2026-06-26 19:54:49.101417+00');
INSERT INTO song_streaming_histories (id_stream, played_at, play_duration, id_user, id_song, created_at, updated_at) VALUES ('b9579227-39f3-47c7-886d-29ea1a2784d1', '2026-06-19 22:26:29', NULL, '90b0f848-6607-4497-88ff-97ed75c71390', 'c6631a3c-d881-406d-abda-f578a4a91212', '2026-06-26 19:54:49.101417+00', '2026-06-26 19:54:49.101417+00');
INSERT INTO song_streaming_histories (id_stream, played_at, play_duration, id_user, id_song, created_at, updated_at) VALUES ('0954df62-193d-4514-bc3a-d49ab685c087', '2026-06-19 22:26:32', '8', '90b0f848-6607-4497-88ff-97ed75c71390', 'S_04', '2026-06-26 19:54:49.101417+00', '2026-06-26 19:54:49.101417+00');
INSERT INTO song_streaming_histories (id_stream, played_at, play_duration, id_user, id_song, created_at, updated_at) VALUES ('d112d4a0-162f-4ebf-a13a-0024c80cc39c', '2026-06-19 22:26:45', NULL, '90b0f848-6607-4497-88ff-97ed75c71390', 'S_03', '2026-06-26 19:54:49.101417+00', '2026-06-26 19:54:49.101417+00');
INSERT INTO song_streaming_histories (id_stream, played_at, play_duration, id_user, id_song, created_at, updated_at) VALUES ('ff5b9675-c947-4b9a-b6bc-3296ff93f0c8', '2026-06-24 12:45:25', NULL, 'e0362ced-2fbe-4f25-8cb4-433c8c8a8bdf', 'S_05', '2026-06-26 19:54:49.101417+00', '2026-06-26 19:54:49.101417+00');
INSERT INTO song_streaming_histories (id_stream, played_at, play_duration, id_user, id_song, created_at, updated_at) VALUES ('add4fec4-5838-4d91-82ba-38b4067ca342', '2026-06-24 12:45:34', NULL, 'e0362ced-2fbe-4f25-8cb4-433c8c8a8bdf', 'S_05', '2026-06-26 19:54:49.101417+00', '2026-06-26 19:54:49.101417+00');
INSERT INTO song_streaming_histories (id_stream, played_at, play_duration, id_user, id_song, created_at, updated_at) VALUES ('6d1e6eea-6a58-494a-859a-0986cea214fb', '2026-06-24 12:45:35', NULL, 'e0362ced-2fbe-4f25-8cb4-433c8c8a8bdf', 'S_03', '2026-06-26 19:54:49.101417+00', '2026-06-26 19:54:49.101417+00');
INSERT INTO song_streaming_histories (id_stream, played_at, play_duration, id_user, id_song, created_at, updated_at) VALUES ('799cc9c2-6291-4f49-bec5-c1008a2b1c33', '2026-06-24 12:45:44', NULL, 'e0362ced-2fbe-4f25-8cb4-433c8c8a8bdf', '9d747333-b9dc-4bbe-b805-9322e41b6b0f', '2026-06-26 19:54:49.101417+00', '2026-06-26 19:54:49.101417+00');
INSERT INTO song_streaming_histories (id_stream, played_at, play_duration, id_user, id_song, created_at, updated_at) VALUES ('b386fe7d-dd4a-4516-bd2a-de08d802e271', '2026-06-26 14:43:44', NULL, 'e0362ced-2fbe-4f25-8cb4-433c8c8a8bdf', 'S_05', '2026-06-26 19:54:49.101417+00', '2026-06-26 19:54:49.101417+00');
INSERT INTO song_streaming_histories (id_stream, played_at, play_duration, id_user, id_song, created_at, updated_at) VALUES ('73d171de-7b6c-44c0-b8b9-1ca1268d9b87', '2026-06-26 14:44:01', NULL, 'e0362ced-2fbe-4f25-8cb4-433c8c8a8bdf', 'S_05', '2026-06-26 19:54:49.101417+00', '2026-06-26 19:54:49.101417+00');
INSERT INTO song_streaming_histories (id_stream, played_at, play_duration, id_user, id_song, created_at, updated_at) VALUES ('ec6e13c1-da1c-49b6-a89a-2206b7806ded', '2026-06-26 14:44:06', NULL, 'e0362ced-2fbe-4f25-8cb4-433c8c8a8bdf', 'S_05', '2026-06-26 19:54:49.101417+00', '2026-06-26 19:54:49.101417+00');
INSERT INTO song_streaming_histories (id_stream, played_at, play_duration, id_user, id_song, created_at, updated_at) VALUES ('c3f95957-d4ff-44ed-8445-0f7d9dd719ec', '2026-06-26 14:44:07', NULL, 'e0362ced-2fbe-4f25-8cb4-433c8c8a8bdf', 'S_03', '2026-06-26 19:54:49.101417+00', '2026-06-26 19:54:49.101417+00');
INSERT INTO song_streaming_histories (id_stream, played_at, play_duration, id_user, id_song, created_at, updated_at) VALUES ('4006c2d1-e1e9-44aa-af7f-cf71c2e95626', '2026-06-26 14:44:08', NULL, 'e0362ced-2fbe-4f25-8cb4-433c8c8a8bdf', 'S_05', '2026-06-26 19:54:49.101417+00', '2026-06-26 19:54:49.101417+00');
INSERT INTO song_streaming_histories (id_stream, played_at, play_duration, id_user, id_song, created_at, updated_at) VALUES ('1c114c16-8290-4a41-8738-9bb048a985e5', '2026-06-26 14:57:27', NULL, 'e0362ced-2fbe-4f25-8cb4-433c8c8a8bdf', '40e67c7d-507f-4da0-b134-9e3c54db1323', '2026-06-26 19:54:49.101417+00', '2026-06-26 19:54:49.101417+00');
INSERT INTO song_streaming_histories (id_stream, played_at, play_duration, id_user, id_song, created_at, updated_at) VALUES ('f9713b04-0929-4691-843f-8b0907f1b85b', '2026-06-26 14:57:27', NULL, 'e0362ced-2fbe-4f25-8cb4-433c8c8a8bdf', 'b7b3fbfa-b752-4f6d-ba6a-21551f387d1c', '2026-06-26 19:54:49.101417+00', '2026-06-26 19:54:49.101417+00');
INSERT INTO song_streaming_histories (id_stream, played_at, play_duration, id_user, id_song, created_at, updated_at) VALUES ('5821c334-14c6-4647-9a27-c23b6e238b39', '2026-06-26 14:57:28', NULL, 'e0362ced-2fbe-4f25-8cb4-433c8c8a8bdf', 'fc512e68-2b07-43d8-84c4-7989a360e069', '2026-06-26 19:54:49.101417+00', '2026-06-26 19:54:49.101417+00');
INSERT INTO song_streaming_histories (id_stream, played_at, play_duration, id_user, id_song, created_at, updated_at) VALUES ('82ac8d83-f8e2-455f-b821-6f2af09725cf', '2026-06-26 14:57:34', NULL, 'e0362ced-2fbe-4f25-8cb4-433c8c8a8bdf', 'S_05', '2026-06-26 19:54:49.101417+00', '2026-06-26 19:54:49.101417+00');
INSERT INTO song_streaming_histories (id_stream, played_at, play_duration, id_user, id_song, created_at, updated_at) VALUES ('9bb6710b-a467-4995-a250-ca0759885431', '2026-06-26 14:57:49', NULL, 'e0362ced-2fbe-4f25-8cb4-433c8c8a8bdf', 'S_05', '2026-06-26 19:54:49.101417+00', '2026-06-26 19:54:49.101417+00');
INSERT INTO song_streaming_histories (id_stream, played_at, play_duration, id_user, id_song, created_at, updated_at) VALUES ('068e0735-a18e-4181-928c-ba529936fa63', '2026-06-26 14:57:52', NULL, 'e0362ced-2fbe-4f25-8cb4-433c8c8a8bdf', '86da840a-ac14-4400-9a01-1896c9544fac', '2026-06-26 19:54:49.101417+00', '2026-06-26 19:54:49.101417+00');
INSERT INTO song_streaming_histories (id_stream, played_at, play_duration, id_user, id_song, created_at, updated_at) VALUES ('0873a6a0-df02-4bc6-b979-0b9cd92c883c', '2026-06-27 23:32:12', NULL, 'e0362ced-2fbe-4f25-8cb4-433c8c8a8bdf', 'S_05', '2026-06-27 16:32:13.234486+00', '2026-06-27 16:32:13.234486+00');
INSERT INTO song_streaming_histories (id_stream, played_at, play_duration, id_user, id_song, created_at, updated_at) VALUES ('ce6bb1fb-94ed-46c0-8020-32b2abedfc2a', '2026-06-27 23:32:15', NULL, 'e0362ced-2fbe-4f25-8cb4-433c8c8a8bdf', '86da840a-ac14-4400-9a01-1896c9544fac', '2026-06-27 16:32:15.812564+00', '2026-06-27 16:32:15.812564+00');
INSERT INTO song_streaming_histories (id_stream, played_at, play_duration, id_user, id_song, created_at, updated_at) VALUES ('f1b5e0b0-ac9e-475b-a28c-11bd1cb9c2f9', '2026-06-27 23:32:18', NULL, 'e0362ced-2fbe-4f25-8cb4-433c8c8a8bdf', '7dc27706-3fc2-4aad-9269-2dd6c4461874', '2026-06-27 16:32:18.713947+00', '2026-06-27 16:32:18.713947+00');
INSERT INTO song_streaming_histories (id_stream, played_at, play_duration, id_user, id_song, created_at, updated_at) VALUES ('107a60cb-0578-4142-96dc-58e1b07335b8', '2026-06-27 23:32:21', NULL, 'e0362ced-2fbe-4f25-8cb4-433c8c8a8bdf', '9d747333-b9dc-4bbe-b805-9322e41b6b0f', '2026-06-27 16:32:22.042231+00', '2026-06-27 16:32:22.042231+00');
INSERT INTO song_streaming_histories (id_stream, played_at, play_duration, id_user, id_song, created_at, updated_at) VALUES ('baeb0ff7-7c82-40fd-ac94-059b6c0dc143', '2026-06-27 23:32:22', NULL, 'e0362ced-2fbe-4f25-8cb4-433c8c8a8bdf', 'ce5edf44-9e71-40a8-b4ba-0a4cdfb98777', '2026-06-27 16:32:22.920465+00', '2026-06-27 16:32:22.920465+00');
INSERT INTO song_streaming_histories (id_stream, played_at, play_duration, id_user, id_song, created_at, updated_at) VALUES ('f3e7a144-6539-428d-8a59-79e50ccec7e6', '2026-06-27 23:32:25', NULL, 'e0362ced-2fbe-4f25-8cb4-433c8c8a8bdf', '86da840a-ac14-4400-9a01-1896c9544fac', '2026-06-27 16:32:25.823426+00', '2026-06-27 16:32:25.823426+00');
INSERT INTO song_streaming_histories (id_stream, played_at, play_duration, id_user, id_song, created_at, updated_at) VALUES ('ac5009f7-bd35-44d9-bbcc-3ee7e6fa138d', '2026-06-27 23:32:26', NULL, 'e0362ced-2fbe-4f25-8cb4-433c8c8a8bdf', 'd9b753ab-1077-4f5f-8f8a-9bf9844cf194', '2026-06-27 16:32:27.036011+00', '2026-06-27 16:32:27.036011+00');
INSERT INTO song_streaming_histories (id_stream, played_at, play_duration, id_user, id_song, created_at, updated_at) VALUES ('9c8904b7-e39c-4b67-ae8d-6ab48a7e03a6', '2026-06-27 23:32:28', NULL, 'e0362ced-2fbe-4f25-8cb4-433c8c8a8bdf', '9d747333-b9dc-4bbe-b805-9322e41b6b0f', '2026-06-27 16:32:28.89337+00', '2026-06-27 16:32:28.89337+00');
INSERT INTO song_streaming_histories (id_stream, played_at, play_duration, id_user, id_song, created_at, updated_at) VALUES ('9eb349d7-24ef-4f00-893b-6ca75523841a', '2026-06-27 23:32:29', '8', 'e0362ced-2fbe-4f25-8cb4-433c8c8a8bdf', 'ce5edf44-9e71-40a8-b4ba-0a4cdfb98777', '2026-06-27 16:32:29.627562+00', '2026-06-27 16:32:29.627562+00');
INSERT INTO song_streaming_histories (id_stream, played_at, play_duration, id_user, id_song, created_at, updated_at) VALUES ('stream_001', '2026-06-20 08:30:00', '231', 'e0362ced-2fbe-4f25-8cb4-433c8c8a8bdf', 'S_01', '2026-06-28 17:54:33.913988+00', '2026-06-28 17:54:33.913988+00');
INSERT INTO song_streaming_histories (id_stream, played_at, play_duration, id_user, id_song, created_at, updated_at) VALUES ('stream_002', '2026-06-20 08:35:00', '231', 'e0362ced-2fbe-4f25-8cb4-433c8c8a8bdf', 'S_02', '2026-06-28 17:54:33.913988+00', '2026-06-28 17:54:33.913988+00');
INSERT INTO song_streaming_histories (id_stream, played_at, play_duration, id_user, id_song, created_at, updated_at) VALUES ('stream_003', '2026-06-20 09:00:00', '205', 'c75b7ee7-b21f-4105-a7f5-d5278bc38283', 'S_03', '2026-06-28 17:54:33.913988+00', '2026-06-28 17:54:33.913988+00');
INSERT INTO song_streaming_histories (id_stream, played_at, play_duration, id_user, id_song, created_at, updated_at) VALUES ('stream_005', '2026-06-21 19:22:00', '208', '83e6d65e-5e46-4019-9bbb-aa1b712e1c48', 'S_05', '2026-06-28 17:54:33.913988+00', '2026-06-28 17:54:33.913988+00');
INSERT INTO song_streaming_histories (id_stream, played_at, play_duration, id_user, id_song, created_at, updated_at) VALUES ('stream_006', '2026-06-21 21:05:00', '231', 'e0362ced-2fbe-4f25-8cb4-433c8c8a8bdf', 'S_01', '2026-06-28 17:54:33.913988+00', '2026-06-28 17:54:33.913988+00');
INSERT INTO song_streaming_histories (id_stream, played_at, play_duration, id_user, id_song, created_at, updated_at) VALUES ('stream_007', '2026-06-21 21:10:00', '205', 'e0362ced-2fbe-4f25-8cb4-433c8c8a8bdf', 'S_03', '2026-06-28 17:54:33.913988+00', '2026-06-28 17:54:33.913988+00');
INSERT INTO song_streaming_histories (id_stream, played_at, play_duration, id_user, id_song, created_at, updated_at) VALUES ('stream_008', '2026-06-21 23:45:00', '231', '691b4806-8271-4857-a588-501c27fff15d', 'S_01', '2026-06-28 17:54:33.913988+00', '2026-06-28 17:54:33.913988+00');
INSERT INTO song_streaming_histories (id_stream, played_at, play_duration, id_user, id_song, created_at, updated_at) VALUES ('stream_009', '2026-06-22 08:12:00', '231', 'c75b7ee7-b21f-4105-a7f5-d5278bc38283', 'S_01', '2026-06-28 17:54:33.913988+00', '2026-06-28 17:54:33.913988+00');
INSERT INTO song_streaming_histories (id_stream, played_at, play_duration, id_user, id_song, created_at, updated_at) VALUES ('stream_010', '2026-06-22 10:30:00', '208', 'c75b7ee7-b21f-4105-a7f5-d5278bc38283', 'S_05', '2026-06-28 17:54:33.913988+00', '2026-06-28 17:54:33.913988+00');
INSERT INTO song_streaming_histories (id_stream, played_at, play_duration, id_user, id_song, created_at, updated_at) VALUES ('stream_011', '2026-06-22 12:45:00', '231', '04d8d10e-2b96-4c65-a17a-f0bde9dbe72c', 'S_01', '2026-06-28 17:54:33.913988+00', '2026-06-28 17:54:33.913988+00');
INSERT INTO song_streaming_histories (id_stream, played_at, play_duration, id_user, id_song, created_at, updated_at) VALUES ('stream_012', '2026-06-22 16:20:00', '205', '83e6d65e-5e46-4019-9bbb-aa1b712e1c48', 'S_03', '2026-06-28 17:54:33.913988+00', '2026-06-28 17:54:33.913988+00');
INSERT INTO song_streaming_histories (id_stream, played_at, play_duration, id_user, id_song, created_at, updated_at) VALUES ('stream_013', '2026-06-23 09:15:00', '231', 'e0362ced-2fbe-4f25-8cb4-433c8c8a8bdf', 'S_01', '2026-06-28 17:54:33.913988+00', '2026-06-28 17:54:33.913988+00');
INSERT INTO song_streaming_histories (id_stream, played_at, play_duration, id_user, id_song, created_at, updated_at) VALUES ('stream_014', '2026-06-23 10:20:00', '205', 'e0362ced-2fbe-4f25-8cb4-433c8c8a8bdf', 'S_03', '2026-06-28 17:54:33.913988+00', '2026-06-28 17:54:33.913988+00');
INSERT INTO song_streaming_histories (id_stream, played_at, play_duration, id_user, id_song, created_at, updated_at) VALUES ('stream_015', '2026-06-23 14:00:00', '198', 'c75b7ee7-b21f-4105-a7f5-d5278bc38283', 'S_04', '2026-06-28 17:54:33.913988+00', '2026-06-28 17:54:33.913988+00');
INSERT INTO song_streaming_histories (id_stream, played_at, play_duration, id_user, id_song, created_at, updated_at) VALUES ('stream_016', '2026-06-23 18:30:00', '208', '04d8d10e-2b96-4c65-a17a-f0bde9dbe72c', 'S_05', '2026-06-28 17:54:33.913988+00', '2026-06-28 17:54:33.913988+00');
INSERT INTO song_streaming_histories (id_stream, played_at, play_duration, id_user, id_song, created_at, updated_at) VALUES ('stream_017', '2026-06-23 20:10:00', '231', '691b4806-8271-4857-a588-501c27fff15d', 'S_02', '2026-06-28 17:54:33.913988+00', '2026-06-28 17:54:33.913988+00');
INSERT INTO song_streaming_histories (id_stream, played_at, play_duration, id_user, id_song, created_at, updated_at) VALUES ('stream_018', '2026-06-24 07:45:00', '231', 'e0362ced-2fbe-4f25-8cb4-433c8c8a8bdf', 'S_01', '2026-06-28 17:54:33.913988+00', '2026-06-28 17:54:33.913988+00');
INSERT INTO song_streaming_histories (id_stream, played_at, play_duration, id_user, id_song, created_at, updated_at) VALUES ('stream_019', '2026-06-24 08:00:00', '205', 'e0362ced-2fbe-4f25-8cb4-433c8c8a8bdf', 'S_03', '2026-06-28 17:54:33.913988+00', '2026-06-28 17:54:33.913988+00');
INSERT INTO song_streaming_histories (id_stream, played_at, play_duration, id_user, id_song, created_at, updated_at) VALUES ('stream_020', '2026-06-24 11:30:00', '231', 'c75b7ee7-b21f-4105-a7f5-d5278bc38283', 'S_01', '2026-06-28 17:54:33.913988+00', '2026-06-28 17:54:33.913988+00');
INSERT INTO song_streaming_histories (id_stream, played_at, play_duration, id_user, id_song, created_at, updated_at) VALUES ('stream_021', '2026-06-24 13:15:00', '205', 'c75b7ee7-b21f-4105-a7f5-d5278bc38283', 'S_03', '2026-06-28 17:54:33.913988+00', '2026-06-28 17:54:33.913988+00');
INSERT INTO song_streaming_histories (id_stream, played_at, play_duration, id_user, id_song, created_at, updated_at) VALUES ('stream_022', '2026-06-24 15:40:00', '208', '04d8d10e-2b96-4c65-a17a-f0bde9dbe72c', 'S_05', '2026-06-28 17:54:33.913988+00', '2026-06-28 17:54:33.913988+00');
INSERT INTO song_streaming_histories (id_stream, played_at, play_duration, id_user, id_song, created_at, updated_at) VALUES ('stream_023', '2026-06-25 09:00:00', '231', '83e6d65e-5e46-4019-9bbb-aa1b712e1c48', 'S_01', '2026-06-28 17:54:33.913988+00', '2026-06-28 17:54:33.913988+00');
INSERT INTO song_streaming_histories (id_stream, played_at, play_duration, id_user, id_song, created_at, updated_at) VALUES ('stream_024', '2026-06-25 10:10:00', '231', '691b4806-8271-4857-a588-501c27fff15d', 'S_01', '2026-06-28 17:54:33.913988+00', '2026-06-28 17:54:33.913988+00');
INSERT INTO song_streaming_histories (id_stream, played_at, play_duration, id_user, id_song, created_at, updated_at) VALUES ('stream_025', '2026-06-25 14:20:00', '205', 'e0362ced-2fbe-4f25-8cb4-433c8c8a8bdf', 'S_03', '2026-06-28 17:54:33.913988+00', '2026-06-28 17:54:33.913988+00');
INSERT INTO song_streaming_histories (id_stream, played_at, play_duration, id_user, id_song, created_at, updated_at) VALUES ('stream_026', '2026-06-25 16:30:00', '198', 'e0362ced-2fbe-4f25-8cb4-433c8c8a8bdf', 'S_04', '2026-06-28 17:54:33.913988+00', '2026-06-28 17:54:33.913988+00');
INSERT INTO song_streaming_histories (id_stream, played_at, play_duration, id_user, id_song, created_at, updated_at) VALUES ('stream_027', '2026-06-26 10:15:00', '231', 'c75b7ee7-b21f-4105-a7f5-d5278bc38283', 'S_02', '2026-06-28 17:54:33.913988+00', '2026-06-28 17:54:33.913988+00');
INSERT INTO song_streaming_histories (id_stream, played_at, play_duration, id_user, id_song, created_at, updated_at) VALUES ('stream_028', '2026-06-26 11:30:00', '205', 'c75b7ee7-b21f-4105-a7f5-d5278bc38283', 'S_03', '2026-06-28 17:54:33.913988+00', '2026-06-28 17:54:33.913988+00');
INSERT INTO song_streaming_histories (id_stream, played_at, play_duration, id_user, id_song, created_at, updated_at) VALUES ('stream_029', '2026-06-26 15:45:00', '208', '04d8d10e-2b96-4c65-a17a-f0bde9dbe72c', 'S_05', '2026-06-28 17:54:33.913988+00', '2026-06-28 17:54:33.913988+00');
INSERT INTO song_streaming_histories (id_stream, played_at, play_duration, id_user, id_song, created_at, updated_at) VALUES ('stream_030', '2026-06-26 21:00:00', '231', '83e6d65e-5e46-4019-9bbb-aa1b712e1c48', 'S_01', '2026-06-28 17:54:33.913988+00', '2026-06-28 17:54:33.913988+00');
INSERT INTO song_streaming_histories (id_stream, played_at, play_duration, id_user, id_song, created_at, updated_at) VALUES ('39022e22-28c9-4411-bef6-7a98de3c2c26', '2026-06-29 00:57:48', '9', 'e0362ced-2fbe-4f25-8cb4-433c8c8a8bdf', 'S_05', '2026-06-28 17:57:48.871652+00', '2026-06-28 17:57:48.871652+00');
INSERT INTO song_streaming_histories (id_stream, played_at, play_duration, id_user, id_song, created_at, updated_at) VALUES ('91c36348-456a-4ef2-ae2c-46e7e201db4b', '2026-06-29 00:58:20', '6', 'e0362ced-2fbe-4f25-8cb4-433c8c8a8bdf', 'S_999', '2026-06-28 17:58:20.981044+00', '2026-06-28 17:58:20.981044+00');
INSERT INTO song_streaming_histories (id_stream, played_at, play_duration, id_user, id_song, created_at, updated_at) VALUES ('84853551-a3d3-487e-a3ef-9bb05b4a37e7', '2026-06-29 00:58:32', NULL, 'e0362ced-2fbe-4f25-8cb4-433c8c8a8bdf', 'S_01', '2026-06-28 17:58:32.814796+00', '2026-06-28 17:58:32.814796+00');
INSERT INTO song_streaming_histories (id_stream, played_at, play_duration, id_user, id_song, created_at, updated_at) VALUES ('41888a01-de9e-4eb2-82fa-4dda27423b93', '2026-06-29 01:00:14', NULL, 'e0362ced-2fbe-4f25-8cb4-433c8c8a8bdf', 'S_01', '2026-06-28 18:00:15.263855+00', '2026-06-28 18:00:15.263855+00');
INSERT INTO song_streaming_histories (id_stream, played_at, play_duration, id_user, id_song, created_at, updated_at) VALUES ('17276728-f700-4ac1-9469-d1197746d6d7', '2026-06-29 01:00:20', NULL, 'e0362ced-2fbe-4f25-8cb4-433c8c8a8bdf', 'S_02', '2026-06-28 18:00:20.740037+00', '2026-06-28 18:00:20.740037+00');
INSERT INTO song_streaming_histories (id_stream, played_at, play_duration, id_user, id_song, created_at, updated_at) VALUES ('stream_new_01', '2026-06-25 18:30:00', '242', 'e0362ced-2fbe-4f25-8cb4-433c8c8a8bdf', 'S_TULUS_01', '2026-06-28 18:14:14.157497+00', '2026-06-28 18:14:14.157497+00');
INSERT INTO song_streaming_histories (id_stream, played_at, play_duration, id_user, id_song, created_at, updated_at) VALUES ('stream_new_02', '2026-06-25 18:35:00', '234', 'e0362ced-2fbe-4f25-8cb4-433c8c8a8bdf', 'S_JUICY_01', '2026-06-28 18:14:14.157497+00', '2026-06-28 18:14:14.157497+00');
INSERT INTO song_streaming_histories (id_stream, played_at, play_duration, id_user, id_song, created_at, updated_at) VALUES ('stream_new_03', '2026-06-25 19:00:00', '247', 'c75b7ee7-b21f-4105-a7f5-d5278bc38283', 'S_DEWA_01', '2026-06-28 18:14:14.157497+00', '2026-06-28 18:14:14.157497+00');
INSERT INTO song_streaming_histories (id_stream, played_at, play_duration, id_user, id_song, created_at, updated_at) VALUES ('stream_new_04', '2026-06-26 14:15:00', '301', '04d8d10e-2b96-4c65-a17a-f0bde9dbe72c', 'S_DEWA_02', '2026-06-28 18:14:14.157497+00', '2026-06-28 18:14:14.157497+00');
INSERT INTO song_streaming_histories (id_stream, played_at, play_duration, id_user, id_song, created_at, updated_at) VALUES ('stream_new_05', '2026-06-26 19:22:00', '180', '83e6d65e-5e46-4019-9bbb-aa1b712e1c48', 'S_BILLIE_01', '2026-06-28 18:14:14.157497+00', '2026-06-28 18:14:14.157497+00');
INSERT INTO song_streaming_histories (id_stream, played_at, play_duration, id_user, id_song, created_at, updated_at) VALUES ('stream_new_06', '2026-06-26 21:05:00', '233', '691b4806-8271-4857-a588-501c27fff15d', 'S_JOJI_01', '2026-06-28 18:14:14.157497+00', '2026-06-28 18:14:14.157497+00');
INSERT INTO song_streaming_histories (id_stream, played_at, play_duration, id_user, id_song, created_at, updated_at) VALUES ('b30e3e5d-a82e-4abe-9118-d051bc17c217', '2026-06-29 01:20:41', NULL, 'e0362ced-2fbe-4f25-8cb4-433c8c8a8bdf', '86da840a-ac14-4400-9a01-1896c9544fac', '2026-06-28 18:20:41.88436+00', '2026-06-28 18:20:41.88436+00');

-- Data for: episode_streaming_histories
INSERT INTO episode_streaming_histories (id_stream, played_at, play_duration, id_user, id_episode, created_at, updated_at) VALUES ('abdbccfb-9229-4cb5-a30d-56d3847553e6', '2026-06-27 23:18:38', '13', 'e0362ced-2fbe-4f25-8cb4-433c8c8a8bdf', 'aeff83a5-43b7-4552-8aad-40266dbf24e0', '2026-06-27 16:18:38.605602+00', '2026-06-27 16:18:38.605602+00');
INSERT INTO episode_streaming_histories (id_stream, played_at, play_duration, id_user, id_episode, created_at, updated_at) VALUES ('stream_ep_001', '2026-06-20 10:00:00', '3600', 'e0362ced-2fbe-4f25-8cb4-433c8c8a8bdf', 'EP_01', '2026-06-28 17:54:33.913988+00', '2026-06-28 17:54:33.913988+00');
INSERT INTO episode_streaming_histories (id_stream, played_at, play_duration, id_user, id_episode, created_at, updated_at) VALUES ('stream_ep_002', '2026-06-21 11:30:00', '12', 'e0362ced-2fbe-4f25-8cb4-433c8c8a8bdf', 'aeff83a5-43b7-4552-8aad-40266dbf24e0', '2026-06-28 17:54:33.913988+00', '2026-06-28 17:54:33.913988+00');
INSERT INTO episode_streaming_histories (id_stream, played_at, play_duration, id_user, id_episode, created_at, updated_at) VALUES ('stream_ep_003', '2026-06-22 14:00:00', '1800', 'c75b7ee7-b21f-4105-a7f5-d5278bc38283', 'EP_03', '2026-06-28 17:54:33.913988+00', '2026-06-28 17:54:33.913988+00');
INSERT INTO episode_streaming_histories (id_stream, played_at, play_duration, id_user, id_episode, created_at, updated_at) VALUES ('stream_ep_004', '2026-06-23 16:00:00', '2400', '04d8d10e-2b96-4c65-a17a-f0bde9dbe72c', 'EP_04', '2026-06-28 17:54:33.913988+00', '2026-06-28 17:54:33.913988+00');
INSERT INTO episode_streaming_histories (id_stream, played_at, play_duration, id_user, id_episode, created_at, updated_at) VALUES ('stream_ep_005', '2026-06-24 17:30:00', '3000', '83e6d65e-5e46-4019-9bbb-aa1b712e1c48', 'EP_05', '2026-06-28 17:54:33.913988+00', '2026-06-28 17:54:33.913988+00');
INSERT INTO episode_streaming_histories (id_stream, played_at, play_duration, id_user, id_episode, created_at, updated_at) VALUES ('edb8eb62-088e-41e7-bd92-a8689f4f5d7b', '2026-06-29 00:58:56', NULL, 'e0362ced-2fbe-4f25-8cb4-433c8c8a8bdf', 'EP_04', '2026-06-28 17:58:57.147368+00', '2026-06-28 17:58:57.147368+00');

-- =====================================================
-- DATABASE REPORT QUERIES
-- =====================================================

-- -----------------------------------------------------
-- A. REPORT AKTIVITAS PENGGUNA (1 Query)
-- Riwayat streaming lagu oleh pengguna tertentu (Enrico) dalam periode tertentu.
-- -----------------------------------------------------
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
    u.id_user = 'e0362ced-2fbe-4f25-8cb4-433c8c8a8bdf' -- Filter user Enrico
ORDER BY 
    h.played_at DESC;


-- -----------------------------------------------------
-- B. REPORT AGREGASI DATA (2 Query)
-- -----------------------------------------------------

-- Query 1: Statistik Pemutaran Lagu per Artis (Halaman Portal Artis - Analytics / Top Songs)
-- Menghitung total pemutaran dan total durasi dengar per lagu milik artis tertentu (Taylor Swift 'A_01') yang memiliki minimal 3 kali streaming.
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
    sa.id_artist = 'A_01'
GROUP BY 
    s.id_song, 
    s.title
HAVING 
    COUNT(h.id_stream) >= 3
ORDER BY 
    total_streams DESC;


-- Query 2: Jumlah Lagu Terunggah per Album oleh Artis (Halaman Detail Album / Album Catalog)
-- Menampilkan total lagu dan total durasi musik untuk masing-masing album (Minimal 2 lagu per album).
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


-- -----------------------------------------------------
-- D. REPORT BUSINESS INSIGHT (2 Query)
-- -----------------------------------------------------

-- Query 1: Analisis Lagu Paling Populer (Halaman Discover Weekly / Top Hits)
-- Pertanyaan Bisnis: Lagu mana saja yang paling populer (paling sering diputar) secara global di platform?
-- Alasan Bisnis: Menyusun playlist kurasi rekomendasi Discover Weekly dan acuan perhitungan royalti artis.
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


-- Query 2: Analisis Artis Terpopuler (Halaman Beranda - Popular Artists)
-- Pertanyaan Bisnis: Siapa saja artis yang memiliki jumlah pemutaran (exposure) tertinggi secara global di platform?
-- Alasan Bisnis: Membantu tim marketing mengidentifikasi artis-artis dengan basis penggemar terbesar untuk program promosi eksklusif atau iklan komersial terarah.
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


-- =====================================================
-- APPLICATION FRONT-END & PORTAL QUERIES
-- =====================================================

-- -----------------------------------------------------
-- 1. FITUR FRONT END (PENDENGAR / USER-FACING)
-- -----------------------------------------------------

-- A. GOOD MORNING (HOME SCREEN - ALBUMS LIST)
-- Menampilkan daftar album terbaru untuk menyambut pengguna di Home Screen.
SELECT id_album, title, cover_image, release
FROM albums
ORDER BY release DESC
LIMIT 6;


-- B. DISCOVER WEEKLY (REKOMENDASI PERSONAL PENGGUNA)
-- Mencari lagu dari kategori/genre playlist yang diikuti pengguna, 
-- tetapi belum pernah didengarkan oleh pengguna tersebut, diurutkan berdasarkan popularitas lagu.
SELECT s.id_song, s.title AS song_title, s.audio_url, s.duration, a.name AS artist_name
FROM songs s
JOIN song_artist sa ON s.id_song = sa.id_song
JOIN artists a ON sa.id_artist = a.id_artist
WHERE s.id_album IN (
    -- Album yang memiliki kategori playlist yang diikuti oleh user
    SELECT DISTINCT s2.id_album
    FROM user_followplaylist ufp
    JOIN playlist_song ps ON ufp.id_playlist = ps.id_playlist
    JOIN songs s2 ON ps.id_song = s2.id_song
    WHERE ufp.id_user = 'e0362ced-2fbe-4f25-8cb4-433c8c8a8bdf' -- Filter user Enrico
)
AND s.id_song NOT IN (
    -- Mengecualikan lagu yang sudah pernah didengar oleh user
    SELECT DISTINCT id_song 
    FROM song_streaming_histories 
    WHERE id_user = 'e0362ced-2fbe-4f25-8cb4-433c8c8a8bdf'
)
LIMIT 5;


-- C. POPULAR ARTISTS
-- Menampilkan daftar artis yang paling banyak didengarkan (berdasarkan total streaming histories).
SELECT a.id_artist, a.name, a.profile_picture, COUNT(h.id_stream) AS total_streams
FROM artists a
JOIN song_artist sa ON a.id_artist = sa.id_artist
JOIN songs s ON sa.id_song = s.id_song
LEFT JOIN song_streaming_histories h ON s.id_song = h.id_song
GROUP BY a.id_artist, a.name, a.profile_picture
ORDER BY total_streams DESC
LIMIT 12;


-- D. ALBUMS (DAFTAR ALBUM / ALBUM TERPOPULER)
-- Menampilkan daftar seluruh album diurutkan berdasarkan total pemutaran lagu di dalamnya (untuk fitur Popular Album di front end).
SELECT al.id_album, al.title, al.cover_image, COUNT(h.id_stream) AS total_streams
FROM albums al
JOIN songs s ON al.id_album = s.id_album
LEFT JOIN song_streaming_histories h ON s.id_song = h.id_song
GROUP BY al.id_album, al.title, al.cover_image
ORDER BY total_streams DESC;


-- E. PODCASTS (DAFTAR PODCAST / PODCAST TERPOPULER)
-- Menampilkan daftar seluruh podcast diurutkan berdasarkan total pemutaran episodenya (untuk fitur Popular Podcast di front end).
SELECT p.id_podcast, p.name, p.cover_image, COUNT(h.id_stream) AS total_streams
FROM podcasts p
JOIN episodes e ON p.id_podcast = e.id_podcast
LEFT JOIN episode_streaming_histories h ON e.id_episode = h.id_episode
GROUP BY p.id_podcast, p.name, p.cover_image
ORDER BY total_streams DESC;


-- F. PLAYLISTS (DAFTAR PLAYLIST)
-- Menampilkan daftar seluruh playlist umum di platform beserta kategori/genrenya.
SELECT pl.id_playlist, pl.name AS playlist_name, pl.description, pl.cover_image, 
       plc.name AS category_name
FROM playlists pl
LEFT JOIN playlist_categories plc ON pl.id_playlist_category = plc.id_playlist_category
ORDER BY pl.name ASC;


-- G. PLAYLIST ANDA (YOUR PLAYLISTS)
-- Menampilkan playlist yang dibuat/dikolaborasikan oleh pengguna atau diikuti oleh pengguna tertentu (Enrico).
SELECT DISTINCT pl.id_playlist, pl.name, pl.description, pl.cover_image, pc.role
FROM playlists pl
JOIN playlist_collaborator pc ON pl.id_playlist = pc.id_playlist
WHERE pc.id_user = 'e0362ced-2fbe-4f25-8cb4-433c8c8a8bdf'
UNION
SELECT pl.id_playlist, pl.name, pl.description, pl.cover_image, 'Follower' AS role
FROM playlists pl
JOIN user_followplaylist fp ON pl.id_playlist = fp.id_playlist
WHERE fp.id_user = 'e0362ced-2fbe-4f25-8cb4-433c8c8a8bdf';


-- H. LIKED SONGS (LAGU YANG DISUKAI)
-- Menampilkan daftar lagu yang disukai oleh pengguna tertentu (Enrico) beserta nama artis dan albumnya.
SELECT s.id_song, s.title AS song_title, s.duration, s.audio_url, al.title AS album_title, 
       COALESCE(string_agg(art.name, ', '), 'Unknown Artist') AS artist_names
FROM songs s
JOIN user_likedsong uls ON s.id_song = uls.id_song
LEFT JOIN albums al ON s.id_album = al.id_album
LEFT JOIN song_artist sa ON s.id_song = sa.id_song
LEFT JOIN artists art ON sa.id_artist = art.id_artist
WHERE uls.id_user = 'e0362ced-2fbe-4f25-8cb4-433c8c8a8bdf'
GROUP BY s.id_song, s.title, s.duration, s.audio_url, al.title, uls.created_at
ORDER BY uls.created_at DESC;


-- I. PODCAST SAYA (MY PODCASTS)
-- Menampilkan podcast yang dibuat oleh pengguna tertentu (Enrico) yang login sebagai pembuat konten.
SELECT p.id_podcast, p.name AS podcast_name, p.cover_image, p.about, 
       pc.name AS category_name
FROM podcasts p
JOIN podcast_categories pc ON p.id_podcast_category = pc.id_podcast_category
WHERE p.id_user = 'e0362ced-2fbe-4f25-8cb4-433c8c8a8bdf'
ORDER BY p.name ASC;


-- J. SEARCH (FITUR PENCARIAN GLOBAL)
-- Mencari lagu, artis, atau album berdasarkan keyword tertentu (Contoh keyword: 'Evaluasi' / 'Hindia').
SELECT 'Song' AS result_type, s.id_song AS id, s.title AS name, al.title AS detail
FROM songs s
LEFT JOIN albums al ON s.id_album = al.id_album
WHERE s.title ILIKE '%Evaluasi%'
UNION ALL
SELECT 'Artist' AS result_type, a.id_artist AS id, a.name AS name, a.bio AS detail
FROM artists a
WHERE a.name ILIKE '%Evaluasi%'
UNION ALL
SELECT 'Album' AS result_type, al.id_album AS id, al.title AS name, al.release::text AS detail
FROM albums al
WHERE al.title ILIKE '%Evaluasi%'
UNION ALL
SELECT 'User' AS result_type, u.id_user AS id, u.name AS name, u.country AS detail
FROM users u
WHERE u.name ILIKE '%Evaluasi%';


-- K. GENRE (KATEGORI PLAYLIST)
-- Menampilkan seluruh kategori musik/genre yang tersedia di halaman genre.
SELECT id_playlist_category, name, cover_image, icon
FROM playlist_categories
ORDER BY name ASC;


-- L. CONCERTS (DAFTAR KONSER)
-- Menampilkan seluruh jadwal konser artis beserta nama artis yang tampil.
SELECT c.id_concert, c.location, c.location_url, c.event_at, c.ticket_url,
       COALESCE(string_agg(art.name, ', '), 'Unknown Artist') AS performing_artists
FROM concerts c
JOIN artist_concert ac ON c.id_concert = ac.id_concert
JOIN artists art ON ac.id_artist = art.id_artist
GROUP BY c.id_concert, c.location, c.location_url, c.event_at, c.ticket_url
ORDER BY c.event_at ASC;


-- -----------------------------------------------------
-- 2. FITUR SPOTIFY FOR ARTISTS (PORTAL ARTIS)
-- Filter contoh menggunakan ID Artis Taylor Swift ('A_01')
-- -----------------------------------------------------

-- A. DASHBOARD STATISTICS OVERVIEW (TOTAL SONG, TOTAL ALBUM, TOTAL STREAMS, STREAM THIS WEEK, STREAM TODAY)
-- Total Songs
SELECT COUNT(id_song) AS total_songs
FROM song_artist
WHERE id_artist = 'A_01';

-- Total Albums
SELECT COUNT(DISTINCT id_album) AS total_albums
FROM artist_album
WHERE id_artist = 'A_01';

-- Total Streams (All Time)
SELECT COUNT(h.id_stream) AS total_streams
FROM song_streaming_histories h
JOIN song_artist sa ON h.id_song = sa.id_song
WHERE sa.id_artist = 'A_01';

-- Streams This Week (Dimulai dari tanggal 2026-06-22 sesuai timeline data simulasi)
SELECT COUNT(h.id_stream) AS streams_this_week
FROM song_streaming_histories h
JOIN song_artist sa ON h.id_song = sa.id_song
WHERE sa.id_artist = 'A_01'
  AND h.played_at >= '2026-06-22 00:00:00'; -- Jika dinamis: h.played_at >= DATE_TRUNC('week', CURRENT_DATE)

-- Streams Today (Hari terakhir pada timeline simulasi 2026-06-29)
SELECT COUNT(h.id_stream) AS streams_today
FROM song_streaming_histories h
JOIN song_artist sa ON h.id_song = sa.id_song
WHERE sa.id_artist = 'A_01'
  AND h.played_at >= '2026-06-29 00:00:00'; -- Jika dinamis: h.played_at >= CURRENT_DATE


-- B. TOP SONGS BY STREAMS
-- Menampilkan lagu-lagu milik artis yang diurutkan berdasarkan jumlah stream terbanyak.
SELECT s.title AS song_title, COUNT(h.id_stream) AS streams_count
FROM songs s
JOIN song_artist sa ON s.id_song = sa.id_song
LEFT JOIN song_streaming_histories h ON s.id_song = h.id_song
WHERE sa.id_artist = 'A_01'
GROUP BY s.id_song, s.title
ORDER BY streams_count DESC;


-- C. DETAILED STREAMING ANALYTICS BREAKDOWN TABLE
-- Menyajikan analisis detail per lagu meliputi durasi, total streams, streams minggu ini, dan streams hari ini.
SELECT 
    s.title AS song_title,
    al.title AS album_title,
    TO_CHAR((s.duration * INTERVAL '1 second'), 'MI:SS') AS duration,
    COUNT(h.id_stream) AS total,
    SUM(CASE WHEN h.played_at >= '2026-06-22 00:00:00' THEN 1 ELSE 0 END) AS this_week,
    SUM(CASE WHEN h.played_at >= '2026-06-29 00:00:00' THEN 1 ELSE 0 END) AS today
FROM songs s
JOIN song_artist sa ON s.id_song = sa.id_song
LEFT JOIN albums al ON s.id_album = al.id_album
LEFT JOIN song_streaming_histories h ON s.id_song = h.id_song
WHERE sa.id_artist = 'A_01'
GROUP BY s.id_song, s.title, al.title, s.duration
ORDER BY total DESC;


-- D. SONG MANAGEMENT CATALOG (SONG)
-- Menampilkan katalog lagu milik artis bersangkutan.
SELECT s.id_song, s.title AS song_title, al.title AS album_title, s.duration, s.audio_url
FROM songs s
JOIN song_artist sa ON s.id_song = sa.id_song
LEFT JOIN albums al ON s.id_album = al.id_album
WHERE sa.id_artist = 'A_01'
ORDER BY s.title ASC;


-- E. ALBUM MANAGEMENT CATALOG (ALBUM)
-- Menampilkan katalog album yang dirilis oleh artis bersangkutan.
SELECT al.id_album, al.title, al.release, al.cover_image
FROM albums al
JOIN artist_album aa ON al.id_album = aa.id_album
WHERE aa.id_artist = 'A_01'
ORDER BY al.release DESC;


-- F. GALLERY MANAGEMENT (GALLERY)
-- Menampilkan galeri foto yang diunggah oleh artis bersangkutan.
SELECT ag.id_gallery, ag.image_url, ag.description_pict, ag.uploaded_at
FROM artist_galleries ag
WHERE ag.id_artist = 'A_01'
ORDER BY ag.uploaded_at DESC;