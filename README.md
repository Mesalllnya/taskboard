# TaskBoard — Student Task Management

> Aplikasi manajemen tugas berbasis web untuk mahasiswa, dengan tampilan Kanban Board, kalender deadline, pelacak progres, dan notifikasi tugas yang mendekat atau terlambat.

---

## 📋 Daftar Isi

- [Tentang Proyek](#-tentang-proyek)
- [Fitur](#-fitur)
- [Teknologi](#-teknologi)
- [Struktur Proyek](#-struktur-proyek)
- [Persyaratan](#-persyaratan)
- [Instalasi & Cara Menjalankan](#-instalasi--cara-menjalankan)
- [Penggunaan](#-penggunaan)
- [REST API](#-rest-api)
- [Skema Database](#-skema-database)
- [Screenshot](#-screenshot)
- [Kontribusi](#-kontribusi)

---

## 📌 Tentang Proyek

**TaskBoard Student Edition** adalah aplikasi web manajemen tugas yang dirancang khusus untuk kebutuhan mahasiswa. Tugas-tugas diorganisir menggunakan sistem **Kanban Board** dengan empat kolom status, dilengkapi fitur pencarian, filter prioritas, kalender visual, dan ringkasan progres semester.

Proyek ini dibangun dengan pendekatan **fullstack ringan** — tanpa framework berat — menggunakan HTML/CSS/JavaScript murni di sisi frontend dan PHP + MySQL di sisi backend, cocok dijalankan di lingkungan XAMPP lokal maupun server shared hosting.

---

## ✨ Fitur

### 📊 Dashboard (Kanban Board)
- **4 kolom status**: To Do · In Progress · Completed · Overdue
- **Stat card** ringkasan jumlah tugas per status di bagian atas
- **Filter chip** berdasarkan prioritas: Semua / Tinggi / Sedang / Rendah
- **Live search** real-time untuk mencari tugas berdasarkan judul atau mata kuliah

### 📝 Manajemen Tugas (CRUD)
- Tambah tugas baru via modal form dengan field: Judul, Mata Kuliah, Deskripsi, Deadline, Prioritas, Status, Reminder
- Edit tugas yang sudah ada
- Hapus tugas dengan konfirmasi dialog
- Ubah status tugas langsung dari card dropdown (To Do → In Progress → Completed, dst.)

### 👤 Halaman Tugas Saya
- Daftar tugas milik mahasiswa yang dikelompokkan per status
- Badge jumlah tugas aktif di sidebar

### 📅 Kalender
- Tampilan kalender bulanan
- Navigasi bulan sebelumnya / berikutnya
- Tanggal dengan tugas ditandai secara visual sesuai prioritas
- Klik tanggal untuk melihat daftar tugas pada hari tersebut

### 📈 Progres
- Persentase penyelesaian keseluruhan
- Progress bar per kategori status
- Rincian tugas per mata kuliah

### 🔔 Notifikasi
- Daftar tugas yang sudah lewat deadline (overdue)
- Daftar tugas yang mendekati deadline
- Badge notifikasi di sidebar dan topbar

### 🎨 UI/UX
- Desain responsif — bekerja di desktop maupun mobile
- Sidebar collapsible dengan hamburger menu di mobile
- Toast notification untuk feedback aksi (berhasil/gagal)
- Loading indicator saat fetch data

---

## 🛠 Teknologi

| Layer | Teknologi |
|---|---|
| Frontend | HTML5, CSS3, Vanilla JavaScript (ES2022) |
| Backend | PHP 8.1+ (procedural, `declare(strict_types=1)`) |
| Database | MySQL 5.7+ / MariaDB 10.3+ |
| Server | Apache (via XAMPP) |
| API | REST API custom (`api.php`) |

> Tidak ada framework, tidak ada `node_modules`, tidak ada build step — cukup PHP + MySQL.

---

## 📁 Struktur Proyek

```
taskboard/
├── index.html       # Halaman utama (SPA shell, semua view di sini)
├── style.css        # Seluruh styling aplikasi
├── script.js        # Logic frontend: fetch, render, event handler
├── api.php          # REST API endpoint (semua route via ?action=...)
├── config.php       # Konfigurasi koneksi database (PDO)
├── schema.sql       # DDL database + seed data awal
└── README.md        # Dokumentasi proyek
```

---

## ⚙️ Persyaratan

Sebelum menjalankan proyek, pastikan sudah terinstal:

- **XAMPP** (atau setara: Laragon, WAMP, dll.) dengan:
  - Apache ✅
  - MySQL / MariaDB ✅
  - PHP 8.1 atau lebih baru ✅
- Browser modern (Chrome, Firefox, Edge, Safari)

---

## 🚀 Instalasi & Cara Menjalankan

### 1. Clone atau download proyek

```bash
git clone https://github.com/Mesalllnya/taskboard.git
```

Atau download ZIP lalu ekstrak.

### 2. Letakkan di direktori XAMPP

Pindahkan folder `taskboard` ke dalam:
```
C:\xampp\htdocs\taskboard\
```

### 3. Jalankan Apache dan MySQL

Buka **XAMPP Control Panel**, lalu klik **Start** pada:
- ✅ Apache
- ✅ MySQL

### 4. Buat database

Buka browser dan akses **phpMyAdmin**:
```
http://localhost/phpmyadmin
```

Kemudian:
1. Klik tab **SQL**
2. Salin seluruh isi file `schema.sql`
3. Tempelkan ke kolom SQL, lalu klik **Go**

> Ini akan membuat database `student_tasks`, tabel `tasks`, dan mengisi data contoh otomatis.

### 5. Buka aplikasi

```
http://localhost/taskboard/
```

> ⚠️ **Jangan** buka file `index.html` langsung dari File Explorer (`file:///...`), karena PHP tidak akan berjalan dan akan muncul error JSON. Selalu gunakan URL `http://localhost/...`.

---

## 📖 Penggunaan

### Menambah Tugas Baru
1. Klik tombol **+ Tambah Tugas** di pojok kanan atas
2. Isi form: Judul (wajib), Mata Kuliah, Deskripsi, Deadline (wajib), Prioritas, Status, Reminder
3. Klik **Simpan**

### Mengedit Tugas
1. Klik ikon **⋮** (titik tiga) pada card tugas
2. Pilih **Edit**
3. Ubah field yang diinginkan, klik **Simpan**

### Mengubah Status Tugas
- Klik ikon **⋮** pada card → pilih status baru dari dropdown, **atau**
- Gunakan halaman **Tugas Saya** dan ubah status dari sana

### Menghapus Tugas
1. Klik ikon **⋮** pada card tugas
2. Pilih **Hapus**
3. Konfirmasi di dialog yang muncul

### Mencari Tugas
- Gunakan kotak **Cari tugas…** di topbar — hasil difilter secara real-time

### Filter Prioritas
- Klik chip **Prioritas Tinggi / Sedang / Rendah** di bawah judul halaman Dashboard

---

## 🌐 REST API

Semua request dikirim ke `api.php` dengan query parameter `?action=`.

| Method | Endpoint | Deskripsi |
|--------|----------|-----------|
| `GET` | `api.php?action=all` | Ambil semua tugas |
| `GET` | `api.php?action=get&id={id}` | Ambil satu tugas |
| `POST` | `api.php?action=create` | Buat tugas baru (body: JSON) |
| `PUT` | `api.php?action=update&id={id}` | Update tugas (body: JSON) |
| `PUT` | `api.php?action=status&id={id}` | Update status saja (body: JSON) |
| `DELETE` | `api.php?action=delete&id={id}` | Hapus tugas |

### Format Response

```json
{
  "success": true,
  "data": { ... },
  "message": ""
}
```

### Contoh Body Request (Create/Update)

```json
{
  "title": "Laporan Praktikum Fisika",
  "subject": "Fisika",
  "description": "Buat laporan eksperimen ayunan bandul.",
  "deadline": "2025-11-20",
  "priority": "high",
  "status": "todo",
  "reminder": "5 hari lagi"
}
```

**Field wajib:** `title`, `deadline`  
**Nilai valid `priority`:** `high` · `medium` · `low`  
**Nilai valid `status`:** `todo` · `inprogress` · `completed` · `overdue`

---

## 🗄 Skema Database

**Database:** `student_tasks`  
**Tabel:** `tasks`

| Kolom | Tipe | Keterangan |
|-------|------|------------|
| `id` | INT UNSIGNED, AUTO_INCREMENT | Primary key |
| `title` | VARCHAR(255) | Judul tugas *(wajib)* |
| `description` | TEXT | Deskripsi opsional |
| `subject` | VARCHAR(100) | Nama mata kuliah |
| `deadline` | DATE | Batas waktu pengumpulan *(wajib)* |
| `priority` | ENUM('high','medium','low') | Tingkat prioritas |
| `reminder` | VARCHAR(100) | Label reminder (mis. "2 hari lagi") |
| `status` | ENUM('todo','inprogress','completed','overdue') | Status Kanban |
| `created_at` | TIMESTAMP | Waktu dibuat (otomatis) |
| `updated_at` | TIMESTAMP | Waktu diperbarui (otomatis) |

---

## 🤝 Kontribusi

Kontribusi sangat terbuka! Langkah-langkahnya:

1. Fork repositori ini
2. Buat branch fitur baru: `git checkout -b fitur/nama-fitur`
3. Commit perubahan: `git commit -m "Tambah fitur: nama-fitur"`
4. Push ke branch: `git push origin fitur/nama-fitur`
5. Buat Pull Request

---

## 📄 Lisensi

Proyek ini bersifat open-source dan bebas digunakan untuk keperluan belajar dan pengembangan.

---

<p align="center">Dibuat dengan ☕ untuk mahasiswa Indonesia</p>
