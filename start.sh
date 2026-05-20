#!/bin/bash

# Script untuk menjalankan aplikasi Fern
# Usage: ./start.sh

echo "🚀 Starting Fern Application..."
echo ""

# Check if PHP is installed
if ! command -v php &> /dev/null; then
    echo "❌ PHP tidak ditemukan. Silakan install PHP terlebih dahulu."
    exit 1
fi

# Display PHP version
PHP_VERSION=$(php -v | head -n 1)
echo "✅ $PHP_VERSION"
echo ""

# Check if uploads directory exists
if [ ! -d "uploads" ]; then
    echo "📁 Membuat folder uploads..."
    mkdir -p uploads/{proposals,transcripts,recommendation_letters,certificates,profile_photos,attendance_photos,posts}
fi

# Set permissions
echo "🔐 Setting permissions untuk folder uploads..."
chmod -R 755 uploads/

echo ""
echo "✨ Aplikasi siap dijalankan!"
echo ""
echo "📍 URL: http://localhost:8000"
echo "👤 Login Admin:"
echo "   Email: admin@fern.test"
echo "   Password: password"
echo ""
echo "⚠️  Pastikan database 'fern' sudah dibuat dan schema sudah diimport!"
echo "   Cara import: mysql -u root -p fern < sql/schema.sql"
echo ""
echo "🛑 Tekan Ctrl+C untuk menghentikan server"
echo ""
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""

# Start PHP built-in server
php -S localhost:8000 router.php