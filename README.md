# Artikel API dengan Laravel dan JWT

Aplikasi ini adalah API sederhana yang dibangun menggunakan Laravel 9.5.2 dengan otentikasi berbasis JWT untuk mengelola artikel. API ini mendukung pembuatan, pengambilan, dan pencarian artikel.

## Prerequisites

- **PHP** >= 7.4 (yang saya gunakan 8.3.8)
- **Composer**
- **MySQL** atau database yang kompatibel
- **Laravel 9.5.2**

## Setup

### 1. Clone Repository

```bash
git clone https://github.com/fatnan/articles_api.git
cd articles_api
```

### 2. Install Dependencies

Jalankan perintah berikut untuk menginstal semua dependensi yang diperlukan:

```bash
composer install
```

### 3. Buat File .env

Salin file .env.example ke file .env:

```bash
cp .env.example .env
```

### 4. Konfigurasi .env

Edit file .env untuk konfigurasi database, lalu Jalankan perintah berikut untuk menghasilkan secret key JWT :

```bash
php artisan jwt:secret
```

### 5. Migrasi Database

Jalankan migrasi untuk membuat tabel-tabel yang diperlukan:

```bash
php artisan migrate
```

### 6. Menjalankan Server

Jalankan server lokal Laravel:

```bash
php artisan serve
```

Server akan berjalan di http://localhost:8000.

## Endpoints API
Registrasi

POST /api/register

Body:

```json
{
  "name": "Nama Pengguna",
  "email": "email@example.com",
  "password": "password",
  "password_confirmation": "password"
}
```
Response:
```json
{
  "token": "<JWT_TOKEN>"
}
```
Login

POST /api/login

Body:
```json
{
  "email": "email@example.com",
  "password": "password"
}
```

Response :
```json
{
  "token": "<JWT_TOKEN>"
}
```

Create Article

POST /api/articles

Headers:
```text
Authorization: Bearer <JWT_TOKEN>
```
Body:
```json
{
  "author": "Nama Penulis",
  "title": "Judul Artikel",
  "body": "Isi Artikel"
}
```
Response:
```json
{
  "author": "Nama Penulis",
  "title": "Judul Artikel",
  "body": "Isi Artikel",
  "created_at": "2024-08-17T00:00:00.000000Z",
  "updated_at": "2024-08-17T00:00:00.000000Z"
}
```

Get Articles

GET /api/articles

Headers:
```text
Authorization: Bearer <JWT_TOKEN>
```
Query Parameters:

    query - keyword untuk pencarian pada body dan title artikel
    author - filter berdasarkan nama penulis

Response:
```json
[
  {
    "author": "Nama Penulis",
    "title": "Judul Artikel",
    "body": "Isi Artikel",
    "created_at": "2024-08-17T00:00:00.000000Z",
    "updated_at": "2024-08-17T00:00:00.000000Z"
  }
]
```

Testing

Untuk menjalankan testing, Anda perlu memastikan bahwa environment testing telah diatur dengan benar. Jalankan perintah berikut untuk menjalankan semua test:
```bash
php artisan test
```
