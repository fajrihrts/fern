#!/bin/bash

# ============================================
# Test Webhook Script
# ============================================
# Script untuk test webhook deployment secara manual
# Usage: bash test-webhook.sh https://yourdomain.com/deploy.php YOUR_SECRET

if [ -z "$1" ]; then
    echo "❌ Error: URL tidak diberikan"
    echo "Usage: bash test-webhook.sh https://yourdomain.com/deploy.php YOUR_SECRET"
    exit 1
fi

if [ -z "$2" ]; then
    echo "❌ Error: Secret tidak diberikan"
    echo "Usage: bash test-webhook.sh https://yourdomain.com/deploy.php YOUR_SECRET"
    exit 1
fi

URL="$1"
SECRET="$2"

echo "🧪 Testing Webhook Deployment"
echo "=================================="
echo "URL: $URL"
echo ""

# Create test payload (GitHub format)
PAYLOAD='{
  "ref": "refs/heads/main",
  "repository": {
    "name": "fern",
    "full_name": "test/fern"
  },
  "pusher": {
    "name": "test-user"
  },
  "commits": [
    {
      "message": "Test deployment",
      "author": {
        "name": "Test User"
      }
    }
  ]
}'

# Generate signature
SIGNATURE="sha256=$(echo -n "$PAYLOAD" | openssl dgst -sha256 -hmac "$SECRET" | sed 's/^.* //')"

echo "📤 Sending webhook request..."
echo ""

# Send request
RESPONSE=$(curl -s -w "\nHTTP_STATUS:%{http_code}" \
  -X POST \
  -H "Content-Type: application/json" \
  -H "X-Hub-Signature-256: $SIGNATURE" \
  -H "User-Agent: GitHub-Hookshot/test" \
  -d "$PAYLOAD" \
  "$URL")

# Extract HTTP status
HTTP_STATUS=$(echo "$RESPONSE" | grep "HTTP_STATUS:" | cut -d: -f2)
BODY=$(echo "$RESPONSE" | sed '/HTTP_STATUS:/d')

echo "📥 Response:"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "Status: $HTTP_STATUS"
echo ""
echo "$BODY"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""

# Check status
if [ "$HTTP_STATUS" = "200" ]; then
    echo "✅ Webhook test BERHASIL!"
    echo ""
    echo "📋 Next steps:"
    echo "1. Cek deployment log: $URL/../logs/deploy.log"
    echo "2. Verify changes di website"
    echo "3. Setup webhook di GitHub/GitLab"
else
    echo "❌ Webhook test GAGAL!"
    echo ""
    echo "🔍 Troubleshooting:"
    
    if [ "$HTTP_STATUS" = "403" ]; then
        echo "- Status 403: Invalid signature"
        echo "- Pastikan SECRET sama dengan DEPLOY_SECRET di config.php"
    elif [ "$HTTP_STATUS" = "401" ]; then
        echo "- Status 401: No signature provided"
        echo "- Cek apakah signature header terkirim"
    elif [ "$HTTP_STATUS" = "500" ]; then
        echo "- Status 500: Server error"
        echo "- Cek logs/deploy.log untuk error details"
        echo "- Cek PHP error log di cPanel"
    elif [ "$HTTP_STATUS" = "000" ]; then
        echo "- Status 000: Connection failed"
        echo "- Cek apakah URL benar"
        echo "- Cek apakah server accessible"
    fi
    
    echo ""
    echo "📖 Dokumentasi: DEPLOYMENT-GUIDE.md"
fi

echo ""
