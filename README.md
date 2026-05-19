# Proyek 2 - Aplikasi Chat Waktu Nyata (Real-Time Chat Application)

Aplikasi berbasis web untuk memfasilitasi komunikasi pesan instan secara aman dan terenkripsi. Aplikasi ini menggunakan teknologi modern berbasis arsitektur *Event-Driven Software* untuk memastikan pertukaran data terjadi secara waktu nyata (*real-time*) tanpa memerlukan muat ulang (*refresh*) halaman browser.

---

## Entitas Data (Database Schema)
Aplikasi ini mengelola 3 entitas utama yang saling berelasi di dalam database:
* **Users:** Menyimpan data otentikasi akun pengguna (`id`, `name`, `email`, `password`).
* **Conversations:** Menyimpan data ruang obrolan privat maupun kelompok (`id`, `name`, `is_group`).
* **Messages:** Menyimpan riwayat data setiap pesan yang dikirim oleh pengguna (`id`, `conversation_id`, `user_id`, `body`, `created_at`).

---

## Fitur Utama (Sesuai Kriteria Penilaian)
Sistem ini dirancang khusus untuk memenuhi 4 kriteria utama dari bapak dosen:
1.  **User Authentication:** Mengamankan hak akses masuk aplikasi menggunakan sistem *Session Guard* dari Laravel Breeze.
2.  **Websocket Integration:** Mengintegrasikan protokol TCP dupleks penuh (*full-duplex*) melalui Laravel Reverb untuk menjamin pengiriman pesan instan berskala milidetik.
3.  **Private and Group Chat:** Menyediakan ruang obrolan privat antar-pengguna yang aman di mana hak akses didasarkan pada token otentikasi.
4.  **User Presence Tracking (Online/Offline):** Melacak status aktif pengguna secara *live* melalui *Presence Channel* WebSocket yang diwujudkan melalui indikator lampu status hijau pada antarmuka aplikasi.

---

## Teknologi yang Digunakan

* **Backend Engine:** Framework Laravel 11 & PHP 8.2+
* **Real-time WebSocket Driver:** Laravel Reverb Server Protocol
* **Broadcasting Client:** Laravel Echo & Pusher JS Client
* **Database:** MariaDB / MySQL (XAMPP Environment)
* **Frontend Engine:** Blade Templating, Tailwind CSS, & Vanilla JavaScript (ES6 Fetch API)

---

## 🛠️ Langkah-Langkah Pembuatan Sistem (Lengkap dari Awal)

Berikut adalah urutan pembuatan sistem chat real-time ini dari tahap instalasi hingga selesai koding:

### Langkah 1: Instalasi Proyek dan Laravel Breeze
```bash
# 1. Membuat proyek baru Laravel
composer create-project laravel/laravel chat-app
cd chat-app

# 2. Menginstal Laravel Breeze untuk fitur Login & Register
composer require laravel/breeze --dev
php artisan breeze:install blade

# 3. Menginstal Laravel Reverb untuk server WebSocket Real-time
php artisan install:broadcasting