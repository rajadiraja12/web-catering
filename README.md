## Panduan Instalasi
Gunakan bagian ini untuk menjelaskan cara menjalankan proyek Anda secara lokal. Pastikan perintahnya dapat disalin dan dijalankan.

## Installation & Local Development
Follow these steps to run this project on your local machine.

### Prerequisites
- **PHP** >= 8.2 (Recommended)
- **Composer** (Dependency Manager for PHP)
- **NPM** (For frontend assets)
- **Database** (MySQL)

### Step-by-Step Setup
**Clone the repository**
   git clone https://github.com/rajadiraja12/web-catering.git
   cd web-catering

**Install PHP Dependencies**
<br>composer install

**Environment Configuration**
<br>Salin file .env.example menjadi .env dan sesuaikan konfigurasi database Anda.
<br><br>
cp .env.example .env
<br>Buka file .env dan atur koneksi database (DB_DATABASE, DB_USERNAME, DB_PASSWORD).
<br><br>
**Generate Application Key**
<br>php artisan key:generate
<br><br>
**Run Database Migrations & Seeder (Opsional)**
<br>Jika proyek Anda menyertakan data dummy untuk demo:
<br>php artisan migrate --seed
<br><br>
**Install Frontend Dependencies & Build Assets**
<br>npm install
<br>npm run build
<br><br>
**Start the Development Server**
<br>php artisan serve
<br>Aplikasi akan berjalan di http://localhost:8000.
