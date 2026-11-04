-- =============================================================
-- Student Task Management — MySQL Database Schema
-- =============================================================
-- Run this file once to bootstrap the database and seed data.
-- Compatible with MySQL 5.7+ / MariaDB 10.3+
-- =============================================================

-- Create (or switch to) the application database
CREATE DATABASE IF NOT EXISTS student_tasks
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE student_tasks;

-- -------------------------------------------------------------
-- Table: tasks
-- -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS tasks (
    id          INT UNSIGNED    NOT NULL AUTO_INCREMENT,

    -- Core task fields
    title       VARCHAR(255)    NOT NULL,
    description TEXT            DEFAULT NULL,          -- optional detail
    subject     VARCHAR(100)    DEFAULT NULL,          -- e.g. "Fisika", "Informatika"

    -- Scheduling
    deadline    DATE            NOT NULL,

    -- priority: 'high' | 'medium' | 'low'
    priority    ENUM('high','medium','low')
                                NOT NULL DEFAULT 'medium',

    -- Reminder: free-text label shown on the card, e.g. "2 hari lagi"
    reminder    VARCHAR(100)    DEFAULT NULL,

    -- status: drives which Kanban column the card lives in
    status      ENUM('todo','inprogress','completed','overdue')
                                NOT NULL DEFAULT 'todo',

    -- Audit timestamps
    created_at  TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at  TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP
                                ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (id),
    INDEX idx_status   (status),
    INDEX idx_deadline (deadline),
    INDEX idx_priority (priority)
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;


-- -------------------------------------------------------------
-- Seed: sample tasks so the board is populated on first load
-- -------------------------------------------------------------
INSERT INTO tasks (title, description, subject, deadline, priority, reminder, status) VALUES
-- To Do
('Laporan Praktikum Fisika Dasar',
 'Buat laporan lengkap hasil eksperimen ayunan bandul.',
 'Fisika', '2025-11-12', 'high', '1 hari lagi', 'todo'),

('Rangkuman Bab 3 Kalkulus',
 'Buat rangkuman integral tak tentu dan teknik substitusi.',
 'Matematika', '2025-11-15', 'medium', '4 hari lagi', 'todo'),

('Esai Sejarah Pergerakan Nasional',
 'Tulis esai 1500 kata mengenai tokoh pergerakan 1908.',
 'Sejarah', '2025-11-20', 'low', '9 hari lagi', 'todo'),

('Presentasi Kelompok Kimia Organik',
 'Siapkan slide PPT untuk topik alkohol dan eter.',
 'Kimia', '2025-11-25', 'medium', '14 hari lagi', 'todo'),

-- In Progress
('Makalah Pancasila & Kebangsaan',
 'Tulis makalah 10 halaman tentang implementasi nilai Pancasila.',
 'PKN', '2025-11-13', 'high', '2 hari lagi', 'inprogress'),

('Tugas Pemrograman Python Flask API',
 'Buat REST API sederhana dengan Flask dan SQLite.',
 'Informatika', '2025-11-18', 'medium', '7 hari lagi', 'inprogress'),

('Analisis Data Statistik Penelitian',
 'Olah data survei menggunakan SPSS atau Python Pandas.',
 'Statistik', '2025-11-22', 'low', '11 hari lagi', 'inprogress'),

-- Completed
('Kuis Online Biologi Sel',
 'Mengerjakan kuis LMS tentang mitosis dan meiosis.',
 'Biologi', '2025-10-30', 'medium', NULL, 'completed'),

('Resume Jurnal Ekonomi Makro',
 'Buat resume 1 halaman dari jurnal inflasi Bank Indonesia.',
 'Ekonomi', '2025-10-28', 'low', NULL, 'completed'),

('Tugas Desain UI Figma Prototype',
 'Buat prototype mobile app untuk toko online.',
 'Desain', '2025-10-25', 'high', NULL, 'completed'),

-- Overdue
('Laporan KKN Semester Awal',
 'Laporan akhir kegiatan KKN desa Cibitung belum dikumpulkan.',
 'KKN', '2025-11-01', 'high', '3 hari terlambat', 'overdue'),

('Tes Bahasa Inggris TOEFL Prep',
 'Kerjakan paket soal latihan TOEFL bagian reading & listening.',
 'Bahasa', '2025-11-03', 'high', '1 hari terlambat', 'overdue');
