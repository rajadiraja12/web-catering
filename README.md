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
composer install

**Environment Configuration**
Salin file .env.example menjadi .env dan sesuaikan konfigurasi database Anda.

cp .env.example .env
Buka file .env dan atur koneksi database (DB_DATABASE, DB_USERNAME, DB_PASSWORD).

**Generate Application Key**
php artisan key:generate

**Run Database Migrations & Seeder (Opsional)**
Jika proyek Anda menyertakan data dummy untuk demo:
php artisan migrate --seed

**Install Frontend Dependencies & Build Assets**
npm install
npm run build

**Start the Development Server**
php artisan serve
Aplikasi akan berjalan di http://localhost:8000.
