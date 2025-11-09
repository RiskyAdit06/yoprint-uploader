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

- PHP >= 7.3
- Composer
- Node.js & NPM (untuk asset compilation)
- Database (SQLite, MySQL, atau PostgreSQL)
- Redis atau Database untuk Queue (opsional, bisa menggunakan `sync` driver)

## 🔧 Installation

### 1. Clone Repository

```bash
git clone <repository-url>
cd laravel
```

### 2. Install Dependencies

```bash
# Install PHP dependencies
composer install

# Install NPM dependencies (jika diperlukan)
npm install
```

### 3. Environment Setup

```bash
# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate
```

### 4. Configure Environment

Edit file `.env` dan sesuaikan konfigurasi:

```env
APP_NAME="CSV Upload System"
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost

# Database Configuration
DB_CONNECTION=sqlite
# atau untuk MySQL/PostgreSQL:
# DB_CONNECTION=mysql
# DB_HOST=127.0.0.1
# DB_PORT=3306
# DB_DATABASE=laravel
# DB_USERNAME=root
# DB_PASSWORD=

# Queue Configuration
QUEUE_CONNECTION=redis
# atau untuk development:
# QUEUE_CONNECTION=sync
```

### 5. Database Setup

```bash
# Run migrations
php artisan migrate

# (Opsional) Run seeders
php artisan db:seed
```

### 6. Storage Setup

```bash
# Create storage link
php artisan storage:link
```

### 7. Queue Setup (jika menggunakan Queue)

```bash
# Untuk development (sync mode), tidak perlu menjalankan queue worker
# Untuk production, jalankan queue worker:
php artisan queue:work

# atau untuk daemon mode:
php artisan queue:work --daemon
```

## 📁 Struktur Project

```
laravel/
├── app/
│   ├── Http/
│   │   └── Controllers/
│   │       └── UploadController.php      # Controller untuk upload
│   ├── Jobs/
│   │   └── ProcessCsvUpload.php          # Background job untuk proses CSV
│   ├── Models/
│   │   ├── Product.php                   # Model Product
│   │   └── Upload.php                    # Model Upload
│   └── Http/
│       └── Resources/
│           └── UploadResource.php        # API Resource
├── database/
│   └── migrations/
│       ├── create_products_table.php
│       └── create_uploads_table.php
├── resources/
│   └── views/
│       └── uploads/
│           └── index.blade.php           # Web interface
└── routes/
    ├── web.php                           # Web routes
    └── api.php                           # API routes
```

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

# Production (daemon mode)
php artisan queue:work --daemon

# Dengan Redis
php artisan queue:work redis
```

## 🛠️ Development

### Menjalankan Development Server

```bash
php artisan serve
```

Akses aplikasi di: `http://localhost:8000`

### Clear Cache

```bash
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

### Restart Queue Worker

```bash
php artisan queue:restart
```

## 📝 Logging

Log aplikasi tersimpan di `storage/logs/laravel.log`. Sistem akan mencatat:

- Header CSV yang terdeteksi
- Kolom yang ditemukan
- Error processing
- Status upload

## 🧪 Testing

```bash
# Run tests
php artisan test

# atau
phpunit
```

## 🔒 Security

- File upload dibatasi maksimal 50MB
- Validasi format file (hanya CSV/TXT)
- Idempotent upload untuk mencegah duplikasi
- File disimpan dengan nama unik untuk mencegah overwrite

## 🐛 Troubleshooting

### Error: "UNIQUE_KEY column not found in CSV"

Pastikan file CSV memiliki kolom `UNIQUE_KEY` (case-insensitive). Cek log untuk melihat header yang terdeteksi.

### Error: "File tidak ditemukan"

Pastikan file sudah ter-upload dengan benar dan queue worker berjalan.

### Queue tidak berjalan

Pastikan queue worker berjalan:
```bash
php artisan queue:work
```

Atau gunakan `sync` driver untuk development:
```env
QUEUE_CONNECTION=sync
```

## 📄 License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

## 👥 Contributing

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit your changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

## 📞 Support

Untuk pertanyaan atau masalah, silakan buat issue di repository ini.

---

**Dibuat dengan ❤️ menggunakan Laravel**
