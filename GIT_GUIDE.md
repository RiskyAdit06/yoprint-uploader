# Panduan Push ke Git

Langkah-langkah untuk push project ke Git repository.

## 📋 Persiapan

### 1. Pastikan Git sudah terinstall

```bash
git --version
```

Jika belum terinstall, download di: https://git-scm.com/downloads

### 2. Konfigurasi Git (jika belum)

```bash
git config --global user.name "Nama Anda"
git config --global user.email "email@example.com"
```

## 🚀 Langkah-langkah Push ke Git

### Langkah 1: Inisialisasi Git Repository (jika belum)

```bash
# Jika folder belum ada repository Git
git init
```

### Langkah 2: Tambahkan Remote Repository

```bash
# Ganti <repository-url> dengan URL repository Git Anda
git remote add origin <repository-url>

# Contoh:
# git remote add origin https://github.com/username/repository-name.git
# atau
# git remote add origin git@github.com:username/repository-name.git
```

**Jika remote sudah ada, cek dengan:**
```bash
git remote -v
```

**Jika perlu mengubah remote:**
```bash
git remote set-url origin <repository-url>
```

### Langkah 3: Cek Status File

```bash
git status
```

### Langkah 4: Tambahkan File ke Staging

```bash
# Tambahkan semua file
git add .

# Atau tambahkan file tertentu
git add README.md
git add app/
git add database/
```

**Catatan:** File yang ada di `.gitignore` tidak akan ditambahkan.

### Langkah 5: Commit Perubahan

```bash
git commit -m "Initial commit: CSV Upload System dengan background processing"

# Atau dengan deskripsi lebih detail
git commit -m "feat: Add CSV upload system with queue processing

- Add upload controller with validation
- Add background job for CSV processing
- Add flexible column detection
- Add API endpoints
- Add web interface
- Support multiple encoding and delimiter"
```

### Langkah 6: Push ke Repository

```bash
# Push ke branch main/master
git push -u origin main

# Atau jika branch Anda bernama master
git push -u origin master

# Jika branch belum ada di remote, buat branch baru
git push -u origin main --set-upstream
```

## 🔄 Update Repository (Setelah Perubahan)

Jika sudah pernah push dan ingin update:

```bash
# 1. Cek status
git status

# 2. Tambahkan perubahan
git add .

# 3. Commit
git commit -m "Update: deskripsi perubahan"

# 4. Push
git push
```

## 📝 Best Practices

### 1. Buat Branch untuk Fitur Baru

```bash
# Buat branch baru
git checkout -b feature/nama-fitur

# Lakukan perubahan, lalu commit
git add .
git commit -m "feat: Add new feature"

# Push branch
git push -u origin feature/nama-fitur
```

### 2. Commit Message yang Baik

Gunakan format:
```
<type>: <subject>

<body>
```

**Type:**
- `feat`: Fitur baru
- `fix`: Perbaikan bug
- `docs`: Dokumentasi
- `style`: Formatting, tidak mengubah kode
- `refactor`: Refactoring kode
- `test`: Menambah test
- `chore`: Maintenance

**Contoh:**
```bash
git commit -m "feat: Add flexible column detection for CSV upload"
git commit -m "fix: Fix UNIQUE_KEY column not found error"
git commit -m "docs: Update README with installation guide"
```

### 3. Jangan Commit File Sensitif

Pastikan file berikut ada di `.gitignore`:
- `.env`
- `storage/logs/*`
- `vendor/`
- `node_modules/`
- `database/*.sqlite`

## 🔍 Perintah Git Berguna

```bash
# Lihat history commit
git log

# Lihat perubahan yang belum di-commit
git diff

# Lihat branch
git branch

# Pindah branch
git checkout <branch-name>

# Pull perubahan dari remote
git pull origin main

# Clone repository
git clone <repository-url>

# Lihat remote
git remote -v
```

## ⚠️ Troubleshooting

### Error: "fatal: remote origin already exists"

```bash
# Hapus remote yang ada
git remote remove origin

# Tambahkan remote baru
git remote add origin <repository-url>
```

### Error: "Updates were rejected"

```bash
# Pull dulu perubahan terbaru
git pull origin main --rebase

# Lalu push lagi
git push
```

### Error: "Permission denied"

Pastikan:
1. SSH key sudah di-setup (untuk SSH)
2. Atau gunakan HTTPS dengan personal access token

### Undo Last Commit (belum push)

```bash
git reset --soft HEAD~1
```

### Undo Last Commit (sudah push)

```bash
# Hati-hati! Ini akan mengubah history
git revert HEAD
git push
```

## 📦 File yang Harus di-Ignore

Pastikan `.gitignore` sudah berisi:

```
/node_modules
/public/hot
/public/storage
/storage/*.key
/vendor
.env
.env.backup
.env.production
.phpunit.result.cache
Homestead.json
Homestead.yaml
npm-debug.log
yarn-error.log
/.idea
/.vscode
```

## ✅ Checklist Sebelum Push

- [ ] Semua perubahan sudah di-commit
- [ ] File `.env` tidak ikut ter-commit
- [ ] File `vendor/` dan `node_modules/` tidak ikut ter-commit
- [ ] README.md sudah di-update
- [ ] Tidak ada file sensitif yang ter-commit
- [ ] Commit message sudah jelas dan deskriptif

---

**Selamat! Project Anda sudah ter-push ke Git! 🎉**

