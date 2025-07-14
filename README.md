<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

# Peduly Donation Management System

Sistem manajemen donasi yang dibangun dengan Laravel untuk mengelola fundraising, donasi, dan subscription berbagai organisasi.

## Requirements

Pastikan sistem Anda memiliki:

-   PHP >= 8.1
-   Composer
-   Node.js & NPM
-   MySQL atau PostgreSQL
-   Git
-   Laragon/XAMPP untuk DB local

## Installation & Setup

### 1. Clone Repository

```bash
git clone https://github.com/your-username/pedulydonationmanagement.git
cd pedulydonationmanagement
```

### 2. Install Dependencies

```bash
# Install PHP dependencies
composer install

# Install JavaScript dependencies (jika ada)
npm install
```

### 3. Environment Configuration

```bash
# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate
```

### 4. Database Configuration

Edit file `.env` dan sesuaikan konfigurasi database:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=peduly_donation
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

### 5. Database Setup

```bash
# Buat database terlebih dahulu di MySQL/PostgreSQL
# Kemudian jalankan migrasi
php artisan migrate

# Jalankan seeder (jika ada)
php artisan db:seed
```

### 6. Storage Link (untuk file uploads)

```bash
php artisan storage:link
```

### 7. Cache & Config

```bash
# Clear cache jika diperlukan
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# Optimize untuk production (opsional)
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### 8. Start Development Server

```bash
# Jalankan Laravel development server
php artisan serve

# Server akan berjalan di http://localhost:8000
```

## Additional Configuration

### Queue Configuration (jika menggunakan jobs)

```bash
# Jalankan queue worker
php artisan queue:work
```

### Scheduler (jika ada scheduled tasks)

Tambahkan ke crontab:

```bash
* * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
```

### Payment Gateway Setup

Sesuaikan konfigurasi payment gateway di file `.env`:

```env
MIDTRANS_SERVER_KEY=your_server_key
MIDTRANS_CLIENT_KEY=your_client_key
MIDTRANS_IS_PRODUCTION=false
```

## API Documentation

Aplikasi ini menyediakan REST API. Endpoint utama:
yang bisa diakses di link berikut: [Dashboard Peduly](https://docs.google.com/document/d/18jYvBmPI109j-Ru_ifxFTfCXwN4mcQr6Uf5467KG18c/edit?usp=sharing)

## Development Commands

```bash
# Jalankan tests
php artisan test

# Generate dokumentasi API (jika menggunakan package)
php artisan l5-swagger:generate

# Refresh database dengan seeder
php artisan migrate:refresh --seed
```

## Troubleshooting

### Permission Issues (Linux/Mac)

```bash
sudo chmod -R 775 storage
sudo chmod -R 775 bootstrap/cache
```

### Composer Memory Limit

```bash
composer install --no-dev --optimize-autoloader
# atau
COMPOSER_MEMORY_LIMIT=-1 composer install
```

### Database Connection Error

-   Pastikan MySQL/PostgreSQL service berjalan
-   Periksa kredensial database di `.env`
-   Pastikan database sudah dibuat

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

-   [Simple, fast routing engine](https://laravel.com/docs/routing).
-   [Powerful dependency injection container](https://laravel.com/docs/container).
-   Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
-   Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
-   Database agnostic [schema migrations](https://laravel.com/docs/migrations).
-   [Robust background job processing](https://laravel.com/docs/queues).
-   [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework.

You may also try the [Laravel Bootcamp](https://bootcamp.laravel.com), where you will be guided through building a modern Laravel application from scratch.

If you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

## Laravel Sponsors

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the [Laravel Partners program](https://partners.laravel.com).

### Premium Partners

-   **[Vehikl](https://vehikl.com/)**
-   **[Tighten Co.](https://tighten.co)**
-   **[WebReinvent](https://webreinvent.com/)**
-   **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
-   **[64 Robots](https://64robots.com)**
-   **[Curotec](https://www.curotec.com/services/technologies/laravel/)**
-   **[Cyber-Duck](https://cyber-duck.co.uk)**
-   **[DevSquad](https://devsquad.com/hire-laravel-developers)**
-   **[Jump24](https://jump24.co.uk)**
-   **[Redberry](https://redberry.international/laravel/)**
-   **[Active Logic](https://activelogic.com)**
-   **[byte5](https://byte5.de)**
-   **[OP.GG](https://op.gg)**

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
