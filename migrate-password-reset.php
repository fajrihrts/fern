<?php
/**
 * Migration Script: Add Password Reset Table
 * Run this once to add password_resets table to database
 */

require_once __DIR__ . '/config.php';

try {
    $db = getDbConnection();
    
    echo "🔄 Running migration: Create password_resets table...\n\n";
    
    // Create password_resets table
    $sql = "
    CREATE TABLE IF NOT EXISTS password_resets (
        id INT AUTO_INCREMENT PRIMARY KEY,
        email VARCHAR(255) NOT NULL,
        otp VARCHAR(6) NOT NULL,
        expires_at TIMESTAMP NOT NULL,
        is_used TINYINT(1) NOT NULL DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_email (email),
        INDEX idx_otp (otp),
        INDEX idx_expires (expires_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ";
    
    $db->exec($sql);
    
    echo "✅ Table 'password_resets' created successfully!\n\n";
    
    // Verify table exists
    $stmt = $db->query("SHOW TABLES LIKE 'password_resets'");
    if ($stmt->rowCount() > 0) {
        echo "✅ Verification: Table exists in database\n\n";
        
        // Show table structure
        echo "📋 Table structure:\n";
        $stmt = $db->query("DESCRIBE password_resets");
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($columns as $column) {
            echo "   - {$column['Field']} ({$column['Type']})\n";
        }
        
        echo "\n✅ Migration completed successfully!\n";
        echo "🎉 Password reset system is ready to use.\n\n";
        echo "📝 Next steps:\n";
        echo "   1. Configure email settings in config.php (CONTACT_EMAIL)\n";
        echo "   2. Test the forgot password flow at /forgot-password\n";
        echo "   3. Check email delivery (may need SMTP configuration)\n\n";
    } else {
        echo "❌ Error: Table was not created\n";
        exit(1);
    }
    
} catch (PDOException $e) {
    echo "❌ Migration failed!\n";
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
