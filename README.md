# yoprint-uploader
Aplikasi ini melakukan hal berikut. Pengguna dapat mengunggah berkas CSV ke sistem kami. Setelah diunggah, kami akan memproses berkas tersebut di latar belakang. Kami kemudian akan memberi tahu pengguna ketika proses selesai. Kami juga akan menampilkan riwayat semua unggahan berkas kepada pengguna

# Laravel CSV Upload & Processing System

Sistem upload dan pemrosesan file CSV untuk produk dengan background job processing menggunakan Laravel Queue.

## 🚀 Fitur

- ✅ Upload file CSV (maksimal 50MB)
- ✅ Validasi format file (CSV/TXT)
- ✅ Background processing menggunakan Laravel Queue
- ✅ Deteksi encoding otomatis (UTF-8, UTF-16, ASCII)
- ✅ Deteksi delimiter otomatis (Comma atau Tab)
- ✅ Pencarian kolom fleksibel (case-insensitive, variasi nama)
- ✅ Idempotent upload (mencegah duplikasi file yang sama)
- ✅ Real-time status tracking
- ✅ RESTful API untuk integrasi
- ✅ Web interface untuk upload dan monitoring

## 📋 Requirements

- PHP >= 7.3 atau PHP 7.4.33 (existing local)
- Composer
- Database (SQLite, MySQL, atau PostgreSQL)
- Redis atau Database untuk Queue (opsional, bisa menggunakan `sync` driver)

## 🔧 Installation

### 1. Clone Repository

```bash
git clone <https://github.com/RiskyAdit06/yoprint-uploader>
cd yoprint-uploader
```

### 2. Install Dependencies

```bash
# Install PHP dependencies
composer install

### 3. Environment Setup

```bash
# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate
```
### 5. Database Setup

```bash
# Run migrations
php artisan migrate

### 6. Storage Setup

```bash

## 🎯 Usage

### Web Interface

1. Buka browser dan akses: `http://localhost/uploads`
2. Upload file CSV melalui form
3. Monitor status upload di halaman yang sama

### API Endpoints

#### Upload File
```http
POST /api/uploads
Content-Type: multipart/form-data

csv_file: <file>
```

**Response:**
```json
{
  "message": "File uploaded successfully",
  "upload": {
    "id": 1,
    "filename": "1234567890_abc123_filename.csv",
    "original_filename": "filename.csv",
    "status": "pending",
    "created_at": "2025-11-09T05:00:00.000000Z"
  }
}
```

#### List Uploads
```http
GET /api/uploads?per_page=20
```

#### Get Upload Status
```http
GET /api/uploads/{id}
```

## 📊 Format CSV

File CSV harus memiliki kolom berikut (case-insensitive, fleksibel dengan variasi nama):

| Kolom Wajib | Kolom Opsional |
|------------|----------------|
| `UNIQUE_KEY` | `PRODUCT_TITLE` |
| | `PRODUCT_DESCRIPTION` |
| | `STYLE#` |
| | `COLOR_NAME` |
| | `SANMAR_MAINFRAME_COLOR` |
| | `SIZE` |
| | `PIECE_PRICE` |

**Contoh Format CSV:**
```csv
UNIQUE_KEY,PRODUCT_TITLE,PRODUCT_DESCRIPTION,STYLE#,COLOR_NAME,SIZE,PIECE_PRICE
12345,Product Name,Description,STYLE-001,Red,L,29.99
```

**Catatan:**
- Kolom `UNIQUE_KEY` wajib ada
- Sistem akan otomatis mendeteksi variasi nama kolom (spasi, underscore, case-insensitive)
- Delimiter bisa menggunakan koma (`,`) atau tab (`\t`)
- Encoding otomatis dideteksi dan dikonversi ke UTF-8

## 🔄 Background Processing

Sistem menggunakan Laravel Queue untuk memproses file CSV di background:

1. File di-upload dan disimpan di `storage/app/uploads/`
2. Job `ProcessCsvUpload` di-dispatch ke queue
3. Queue worker memproses file secara asynchronous
4. Status update: `pending` → `processing` → `completed` / `failed`

### Menjalankan Queue Worker

```bash
# Development
php artisan queue:work

# Dengan Redis
php artisan queue:work redis
```

## 🛠️ Development

### Menjalankan Development Server

```bash
php artisan serve
```

Akses aplikasi di: `http://localhost:8000`

## 📝 Logging

Log aplikasi tersimpan di `storage/logs/laravel.log`. Sistem akan mencatat:

- Header CSV yang terdeteksi
- Kolom yang ditemukan
- Error processing
- Status upload

## 🔒 Security

- File upload dibatasi maksimal 50MB
- Validasi format file (hanya CSV/TXT)
- Idempotent upload untuk mencegah duplikasi- File disimpan dengan nama unik untuk mencegah overwrite

**Dibuat dengan ❤️ menggunakan Laravel**
