#!/bin/bash

# Script untuk import database schema
# Usage: ./import-db.sh

echo "📦 Import Database Schema untuk Fern"
echo ""

# Check if mysql command exists
if ! command -v mysql &> /dev/null; then
    echo "❌ MySQL command tidak ditemukan."
    echo "   Jika menggunakan MAMP/XAMPP, gunakan path lengkap:"
    echo "   /Applications/MAMP/Library/bin/mysql -u root -p fern < sql/schema.sql"
    exit 1
fi

# Check if schema.sql exists
if [ ! -f "sql/schema.sql" ]; then
    echo "❌ File sql/schema.sql tidak ditemukan!"
    exit 1
fi

echo "Masukkan password MySQL (tekan Enter jika tidak ada password):"
read -s MYSQL_PASSWORD

if [ -z "$MYSQL_PASSWORD" ]; then
    # No password
    mysql -u root fern < sql/schema.sql 2>&1
else
    # With password
    mysql -u root -p"$MYSQL_PASSWORD" fern < sql/schema.sql 2>&1
fi

if [ $? -eq 0 ]; then
    echo ""
    echo "✅ Database schema berhasil diimport!"
    echo ""
    echo "📊 Tabel yang dibuat:"
    echo "   - users"
    echo "   - registrations"
    echo "   - attendance_reports"
    echo "   - posts"
    echo "   - testimonials"
    echo "   - holidays"
    echo "   - sessions"
    echo ""
    echo "👤 Akun default Super Admin:"
    echo "   Email: admin@fern.test"
    echo "   Password: password"
    echo ""
else
    echo ""
    echo "❌ Gagal import database!"
    echo "   Pastikan database 'fern' sudah dibuat terlebih dahulu."
    echo ""
    echo "Cara membuat database:"
    echo "   mysql -u root -p"
    echo "   CREATE DATABASE fern CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
    echo "   exit;"
fi
