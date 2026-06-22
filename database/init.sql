-- database/init.sql
-- Создание БД и пользователя для приложения
CREATE DATABASE IF NOT EXISTS livequeue
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

CREATE USER IF NOT EXISTS 'app'@'%' IDENTIFIED BY 'app';
GRANT ALL PRIVILEGES ON livequeue.* TO 'app'@'%';
FLUSH PRIVILEGES;

USE livequeue;

-- =====================================================
-- 1. Пользователи
-- =====================================================
CREATE TABLE IF NOT EXISTS users (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name            VARCHAR(255) NOT NULL,
    email           VARCHAR(255) NOT NULL UNIQUE,
    email_verified_at TIMESTAMP NULL,
    password        VARCHAR(255) NULL,                -- NULL для OAuth-пользователей
    github_id       BIGINT UNSIGNED NULL UNIQUE,      -- для Socialite
    avatar          VARCHAR(500) NULL,
    remember_token  VARCHAR(100) NULL,
    created_at      TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_users_email (email),
    INDEX idx_users_github (github_id)
) ENGINE=InnoDB;

-- =====================================================
-- 2. Плейлисты (создаёт организатор)
-- =====================================================
CREATE TABLE IF NOT EXISTS playlists (
    id          BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id     BIGINT UNSIGNED NOT NULL,             -- владелец/организатор
    title       VARCHAR(255) NOT NULL,
    slug        VARCHAR(255) NOT NULL,                -- для публичной ссылки /p/{slug}
    description TEXT NULL,
    is_public   TINYINT(1) NOT NULL DEFAULT 1,
    created_at  TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at  TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_playlists_user
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,

    UNIQUE INDEX idx_playlists_slug (slug),
    INDEX idx_playlists_user (user_id),
    INDEX idx_playlists_public (is_public)
) ENGINE=InnoDB;

-- =====================================================
-- 3. Треки (библиотека — может переиспользоваться)
-- =====================================================
CREATE TABLE IF NOT EXISTS tracks (
    id          BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title       VARCHAR(255) NOT NULL,
    artist      VARCHAR(255) NOT NULL,
    duration    INT UNSIGNED NULL,                    -- в секундах
    youtube_url VARCHAR(500) NULL,
    cover_url   VARCHAR(500) NULL,
    created_at  TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,

    INDEX idx_tracks_artist_title (artist, title),
    INDEX idx_tracks_title (title)
) ENGINE=InnoDB;

-- =====================================================
-- 4. Элементы очереди (связь плейлист ↔ трек + мета)
-- =====================================================
CREATE TABLE IF NOT EXISTS queue_items (
    id          BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    playlist_id BIGINT UNSIGNED NOT NULL,
    track_id    BIGINT UNSIGNED NOT NULL,
    added_by    BIGINT UNSIGNED NOT NULL,             -- кто добавил трек
    position    INT UNSIGNED NOT NULL DEFAULT 0,      -- порядок в очереди
    status      ENUM('pending','playing','played','skipped')
                    NOT NULL DEFAULT 'pending',
    votes_up    INT UNSIGNED NOT NULL DEFAULT 0,      -- счётчик голосов
    played_at   TIMESTAMP NULL,                       -- когда трек заиграл
    created_at  TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at  TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_queue_playlist
        FOREIGN KEY (playlist_id) REFERENCES playlists(id) ON DELETE CASCADE,
    CONSTRAINT fk_queue_track
        FOREIGN KEY (track_id)    REFERENCES tracks(id)    ON DELETE CASCADE,
    CONSTRAINT fk_queue_added_by
        FOREIGN KEY (added_by)    REFERENCES users(id)     ON DELETE CASCADE,

    -- Составной индекс для главного запроса «активная очередь плейлиста»
    INDEX idx_queue_playlist_status (playlist_id, status),
    INDEX idx_queue_position (playlist_id, position),
    INDEX idx_queue_votes (playlist_id, votes_up DESC),
    INDEX idx_queue_added_by (added_by)
) ENGINE=InnoDB;
