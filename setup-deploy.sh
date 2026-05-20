#!/bin/bash

# ============================================
# Setup Deployment Script
# ============================================
# Script untuk mempersiapkan project untuk deployment
# Run: bash setup-deploy.sh

echo "🚀 Setup Deployment untuk cPanel"
echo "=================================="
echo ""

# Check if git is initialized
if [ ! -d .git ]; then
    echo "❌ Git belum di-initialize!"
    echo "Run: git init"
    exit 1
fi

echo "✅ Git repository detected"
echo ""

# Check if config.php exists
if [ -f config.php ]; then
    echo "⚠️  config.php sudah ada"
    read -p "Apakah Anda ingin backup config.php? (y/n) " -n 1 -r
    echo
    if [[ $REPLY =~ ^[Yy]$ ]]; then
        cp config.php config.php.backup
        echo "✅ Backup dibuat: config.php.backup"
    fi
else
    echo "📝 Membuat config.php dari config.example.php..."
    cp config.example.php config.php
    echo "✅ config.php dibuat"
    echo "⚠️  PENTING: Edit config.php dengan kredensial database Anda!"
fi

echo ""

# Create necessary directories
echo "📁 Membuat directories yang diperlukan..."

directories=("logs" "cache" "cache/rate_limits" "uploads" "backups")

for dir in "${directories[@]}"; do
    if [ ! -d "$dir" ]; then
        mkdir -p "$dir"
        echo "✅ Created: $dir"
    else
        echo "✓ Exists: $dir"
    fi
done

echo ""

# Create .htaccess for protected directories
echo "🔒 Membuat .htaccess untuk protected directories..."

protected_dirs=("logs" "cache" "backups")

for dir in "${protected_dirs[@]}"; do
    if [ ! -f "$dir/.htaccess" ]; then
        echo "Deny from all" > "$dir/.htaccess"
        echo "✅ Created: $dir/.htaccess"
    else
        echo "✓ Exists: $dir/.htaccess"
    fi
done

echo ""

# Set permissions
echo "🔧 Setting permissions..."

chmod 755 logs cache uploads backups 2>/dev/null
chmod 755 deploy.php 2>/dev/null
chmod 644 .htaccess 2>/dev/null

echo "✅ Permissions set"
echo ""

# Check if remote is set
remote=$(git remote -v | grep origin)

if [ -z "$remote" ]; then
    echo "⚠️  Git remote belum di-set"
    echo ""
    read -p "Masukkan URL repository (contoh: https://github.com/username/repo.git): " repo_url
    
    if [ ! -z "$repo_url" ]; then
        git remote add origin "$repo_url"
        echo "✅ Remote origin added: $repo_url"
    fi
else
    echo "✅ Git remote sudah di-set:"
    echo "$remote"
fi

echo ""

# Generate deploy secret
echo "🔑 Generate Deploy Secret..."
echo ""

if command -v openssl &> /dev/null; then
    secret=$(openssl rand -hex 32)
    echo "Deploy Secret (simpan ini untuk webhook):"
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
    echo "$secret"
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
    echo ""
    echo "⚠️  PENTING: Copy token di atas dan paste ke:"
    echo "   1. config.php → DEPLOY_SECRET"
    echo "   2. GitHub/GitLab Webhook Settings → Secret"
else
    echo "⚠️  openssl tidak ditemukan"
    echo "Generate secret secara manual:"
    echo "   - Via online: https://randomkeygen.com/"
    echo "   - Via PHP: php -r \"echo bin2hex(random_bytes(32));\""
fi

echo ""

# Check .gitignore
if [ ! -f .gitignore ]; then
    echo "⚠️  .gitignore tidak ditemukan!"
    echo "File .gitignore penting untuk mencegah commit file sensitif"
else
    echo "✅ .gitignore exists"
fi

echo ""

# Summary
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "✅ Setup Deployment Selesai!"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""
echo "📋 Langkah Selanjutnya:"
echo ""
echo "1. Edit config.php dengan kredensial database Anda"
echo "2. Update DEPLOY_SECRET di config.php dengan token yang di-generate"
echo "3. Commit dan push ke repository:"
echo "   git add ."
echo "   git commit -m \"Setup deployment\""
echo "   git push origin main"
echo ""
echo "4. Setup di cPanel:"
echo "   - Git Version Control → Create repository"
echo "   - Setup deployment path"
echo "   - Import database (sql/schema.sql)"
echo ""
echo "5. Setup Webhook di GitHub/GitLab:"
echo "   - Payload URL: https://yourdomain.com/deploy.php"
echo "   - Secret: [paste DEPLOY_SECRET dari config.php]"
echo ""
echo "📖 Dokumentasi lengkap: DEPLOYMENT-GUIDE.md"
echo ""
echo "🎉 Happy Deploying!"
echo ""
