-- =============================================================
-- TaskBoard MVC — MySQL Schema
-- Compatible: MySQL 8.0+ / MariaDB 10.6+
-- Run once to bootstrap. Safe to re-run (IF NOT EXISTS).
-- =============================================================

CREATE DATABASE IF NOT EXISTS taskboard_mvc
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE taskboard_mvc;

-- -------------------------------------------------------------
-- users
-- -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS users (
    id            INT UNSIGNED     NOT NULL AUTO_INCREMENT,
    name          VARCHAR(100)     NOT NULL,
    email         VARCHAR(150)     NOT NULL,
    password_hash VARCHAR(255)     NOT NULL,          -- bcrypt
    avatar_initials CHAR(2)        DEFAULT NULL,
    role          ENUM('student','admin') NOT NULL DEFAULT 'student',
    semester      VARCHAR(60)      DEFAULT NULL,      -- e.g. "Ganjil 2025/2026"
    major         VARCHAR(100)     DEFAULT NULL,
    created_at    TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at    TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------
-- projects
-- -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS projects (
    id          INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    user_id     INT UNSIGNED  NOT NULL,
    name        VARCHAR(150)  NOT NULL,
    description TEXT          DEFAULT NULL,
    color       VARCHAR(7)    DEFAULT '#0052CC',     -- hex colour for UI
    status      ENUM('active','archived') NOT NULL DEFAULT 'active',
    created_at  TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at  TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    INDEX idx_proj_user (user_id),
    CONSTRAINT fk_proj_user FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------
-- tasks
-- -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS tasks (
    id          INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    user_id     INT UNSIGNED  NOT NULL,
    project_id  INT UNSIGNED  DEFAULT NULL,          -- nullable → "no project"
    title       VARCHAR(255)  NOT NULL,
    description TEXT          DEFAULT NULL,
    subject     VARCHAR(100)  DEFAULT NULL,           -- course/subject label
    deadline    DATE          NOT NULL,
    priority    ENUM('high','medium','low')  NOT NULL DEFAULT 'medium',
    status      ENUM('todo','inprogress','completed','overdue') NOT NULL DEFAULT 'todo',
    reminder    VARCHAR(100)  DEFAULT NULL,
    created_at  TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at  TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    INDEX idx_task_user     (user_id),
    INDEX idx_task_project  (project_id),
    INDEX idx_task_status   (status),
    INDEX idx_task_deadline (deadline),
    INDEX idx_task_priority (priority),
    CONSTRAINT fk_task_user    FOREIGN KEY (user_id)    REFERENCES users    (id) ON DELETE CASCADE,
    CONSTRAINT fk_task_project FOREIGN KEY (project_id) REFERENCES projects (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------
-- notifications
-- -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS notifications (
    id          INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    user_id     INT UNSIGNED  NOT NULL,
    task_id     INT UNSIGNED  DEFAULT NULL,
    type        ENUM('overdue','deadline','info') NOT NULL DEFAULT 'info',
    message     VARCHAR(255)  NOT NULL,
    is_read     TINYINT(1)    NOT NULL DEFAULT 0,
    created_at  TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    INDEX idx_notif_user (user_id),
    CONSTRAINT fk_notif_user FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE,
    CONSTRAINT fk_notif_task FOREIGN KEY (task_id) REFERENCES tasks (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================================
-- SEED DATA (demo user + projects + tasks)
-- =============================================================

INSERT IGNORE INTO users (id, name, email, password_hash, avatar_initials, semester, major) VALUES
(1, 'Ahmad Rizky', 'ahmad@example.com',
 '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', -- password: "password"
 'AR', 'Ganjil 2025/2026', 'Teknik Informatika');

INSERT IGNORE INTO projects (id, user_id, name, description, color) VALUES
(1, 1, 'Tugas Semester',       'Semua tugas kuliah semester ini',   '#0052CC'),
(2, 1, 'Skripsi',              'Penelitian dan penulisan skripsi',  '#6554C0'),
(3, 1, 'Proyek Kelompok',      'Proyek tim lintas mata kuliah',     '#00875A');

INSERT IGNORE INTO tasks
  (id, user_id, project_id, title, description, subject, deadline, priority, status) VALUES
(1,  1, 1, 'Laporan Praktikum Fisika Dasar',  'Buat laporan lengkap hasil eksperimen ayunan bandul.', 'Fisika',      '2025-11-12', 'high',   'todo'),
(2,  1, 1, 'Rangkuman Bab 3 Kalkulus',         'Buat rangkuman integral tak tentu.',                   'Matematika',  '2025-11-15', 'medium', 'todo'),
(3,  1, 1, 'Esai Sejarah Pergerakan Nasional', 'Tulis esai 1500 kata mengenai tokoh pergerakan 1908.', 'Sejarah',     '2025-11-20', 'low',    'todo'),
(4,  1, 3, 'Presentasi Kelompok Kimia',        'Siapkan slide PPT untuk topik alkohol dan eter.',      'Kimia',       '2025-11-25', 'medium', 'todo'),
(5,  1, 1, 'Makalah Pancasila & Kebangsaan',   'Tulis makalah 10 halaman tentang nilai Pancasila.',    'PKN',         '2025-11-13', 'high',   'inprogress'),
(6,  1, 1, 'Tugas Pemrograman Python Flask',   'Buat REST API sederhana dengan Flask dan SQLite.',     'Informatika', '2025-11-18', 'medium', 'inprogress'),
(7,  1, 2, 'Analisis Data Statistik',          'Olah data survei menggunakan Python Pandas.',          'Statistik',   '2025-11-22', 'low',    'inprogress'),
(8,  1, 1, 'Kuis Online Biologi Sel',          'Kuis LMS tentang mitosis dan meiosis.',                'Biologi',     '2025-10-30', 'medium', 'completed'),
(9,  1, 1, 'Resume Jurnal Ekonomi Makro',      'Resume 1 halaman dari jurnal inflasi Bank Indonesia.', 'Ekonomi',     '2025-10-28', 'low',    'completed'),
(10, 1, 3, 'Tugas Desain UI Figma Prototype',  'Buat prototype mobile app untuk toko online.',         'Desain',      '2025-10-25', 'high',   'completed'),
(11, 1, 1, 'Laporan KKN Semester Awal',        'Laporan akhir kegiatan KKN desa Cibitung.',            'KKN',         '2025-11-01', 'high',   'overdue'),
(12, 1, 1, 'Tes Bahasa Inggris TOEFL Prep',    'Kerjakan paket soal latihan TOEFL reading & listening.','Bahasa',     '2025-11-03', 'high',   'overdue');
