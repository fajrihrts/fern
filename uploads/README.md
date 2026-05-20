# Uploads Directory

Folder ini digunakan untuk menyimpan file yang di-upload oleh user.

## ⚠️ PENTING

- Folder ini **TIDAK** di-commit ke Git repository
- File `.htaccess` melindungi folder ini dari eksekusi script
- Hanya file image dan document yang bisa diakses

## 📁 Struktur

```
uploads/
├── .htaccess          # Protection rules
├── README.md          # This file
├── documents/         # User documents (PDF, DOC, etc)
├── photos/            # User photos
└── posts/             # Post images
```

## 🔒 Security

- PHP execution disabled
- Directory listing disabled
- Only allowed file types accessible
- File type validation on upload

## 🚀 Deployment

Saat deployment ke cPanel:
1. Folder ini akan dibuat otomatis jika belum ada
2. Permissions akan di-set ke 755
3. `.htaccess` akan di-copy untuk proteksi

## 📝 Notes

- Backup file uploads secara terpisah dari code
- Monitor disk usage secara berkala
- Implement file cleanup untuk file lama
