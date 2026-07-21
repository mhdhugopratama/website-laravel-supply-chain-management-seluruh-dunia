# Global Risk Intelligence Dashboard

Sebuah sistem manajemen rantai pasok (Supply Chain Management) berskala global berbasis web yang dibangun menggunakan **Laravel**. Aplikasi ini membantu pengusaha, perusahaan logistik, dan analis untuk memantau tingkat risiko, cuaca ekstrim, berita terkini, dan fluktuasi mata uang dari berbagai negara yang dapat berdampak pada jalur distribusi.

## Fitur Utama 🚀

*   🌍 **Dashboard Peta Global (Interactive Map)**: Melihat visualisasi negara-negara berdasarkan tingkat risiko logistik menggunakan peta dinamis (Leaflet.js).
*   🌦️ **Analisis Dampak Cuaca (Weather Impact)**: Memantau data cuaca dari berbagai negara (suhu, angin, curah hujan) secara *real-time* via Open-Meteo API. Sistem otomatis mendeteksi negara mana yang mengalami cuaca paling ekstrim maupun paling stabil.
*   📰 **Berita Logistik Terkini (News Alert)**: Mengambil berita internasional terbaru terkait ekonomi, konflik, dan logistik menggunakan GNews API yang dilengkapi dengan sistem *caching* cerdas dan analisis sentimen (Positif/Negatif/Netral).
*   💱 **Pantauan Mata Uang (Currency Rates)**: Memantau fluktuasi kurs mata uang (Exchange Rate) dari berbagai negara terhadap USD.
*   ⚖️ **Perbandingan Negara (Compare)**: Membandingkan tingkat risiko dan intelijen rantai pasok antara dua negara secara spesifik (Stabilitas Ekonomi, Risiko Iklim, dan Sentimen Publik).
*   ⚓ **Manajemen Pelabuhan (Port Distribution)**: Menampilkan data sebaran pelabuhan penting yang ada di berbagai negara beserta status kemanannya.
*   ⚙️ **Admin Control Panel**: Sistem manajemen berbasis admin untuk mengatur data *User*, *Port*, dan menulis Artikel (Berita Internal).

## Teknologi yang Digunakan 💻

*   **Backend:** Laravel 11, PHP 8.2+
*   **Database:** SQLite / MySQL (bisa disesuaikan di `.env`)
*   **Frontend:** Blade Templating, Vanilla CSS (Custom Design System)
*   **External APIs:**
    *   REST Countries API (Data dasar negara)
    *   Open-Meteo API (Data cuaca)
    *   GNews API (Berita global)
    *   ExchangeRate-API (Mata uang)
*   **Libraries:** Leaflet.js (Map), Chart.js (Grafik)

## Persyaratan Instalasi (Requirements) 🛠️

Sebelum memulai, pastikan komputer Anda sudah terpasang:
*   [PHP](https://www.php.net/) (Versi >= 8.2)
*   [Composer](https://getcomposer.org/)
*   [Node.js & NPM](https://nodejs.org/)

## Cara Instalasi & Menjalankan Aplikasi 🏃‍♂️

1. **Clone repository ini** ke komputer Anda:
   ```bash
   git clone https://github.com/mhdhugopratama/website-laravel-supply-chain-management-seluruh-dunia.git
   cd website-laravel-supply-chain-management-seluruh-dunia
   ```

2. **Install dependency PHP** menggunakan Composer:
   ```bash
   composer install
   ```

3. **Salin konfigurasi environment**:
   ```bash
   cp .env.example .env
   ```

4. **Buat Application Key**:
   ```bash
   php artisan key:generate
   ```

5. **Konfigurasi API Keys (Opsional tapi Penting)**:
   Buka file `.env` dan masukkan API Key Anda jika ingin data *real-time* berjalan maksimal:
   ```env
   GNEWS_API_KEY=masukkan_api_key_gnews_anda_disini
   EXCHANGE_RATE_API_KEY=masukkan_api_key_exchange_rate_anda_disini
   ```
   *(Catatan: Tanpa API key, aplikasi tetap bisa berjalan menggunakan data statis/fallback yang sudah dikonfigurasi di dalam sistem agar tidak error bagi pemula).*

6. **Migrasi Database dan jalankan Seeder** (untuk membuat akun Admin & data awal):
   ```bash
   php artisan migrate:fresh --seed
   ```

7. **Jalankan server lokal**:
   ```bash
   php artisan serve
   ```

8. **Selesai!** Buka browser Anda dan kunjungi `http://127.0.0.1:8000`.

## Catatan Khusus untuk Programmer Pemula 💡

Aplikasi ini sudah dimodifikasi agar sangat ramah dipelajari:
*   Komentar pada kode (*code comments*) khususnya pada file `app/Services/WeatherService.php`, `app/Services/NewsService.php`, dan `app/Http/Controllers/DashboardController.php` sudah diubah menggunakan bahasa Indonesia yang sangat santai dan mudah dimengerti.
*   Terdapat sistem *Fallback* / *Mock Data* di mana jika Anda belum memasukkan API Key atau API mengalami *limit*, aplikasi **tidak akan error**, melainkan menampilkan data bohongan (dummy data) agar Anda tetap bisa melihat bagaimana UI (tampilan) web bekerja.
*   Meskipun penjelasan kode dan instruksi menggunakan bahasa Indonesia, seluruh teks pada tampilan (UI) dibuat menggunakan **Bahasa Inggris** secara penuh agar terlihat profesional dan bertaraf internasional.

---
*Dibuat untuk mempermudah pemantauan rantai pasok global secara cerdas.*
