<?php

/**
 * Password Reset Class
 * Handles OTP generation, validation, and password reset logic
 */
class PasswordReset {
    
    const OTP_LENGTH = 6;
    const OTP_EXPIRY_MINUTES = 10;
    const MAX_ATTEMPTS_PER_HOUR = 3;
    
    /**
     * Generate and send OTP to user email
     * 
     * @param string $email User email
     * @return array Result with success status and message
     */
    public static function sendOTP($email) {
        $db = getDbConnection();
        
        // Check if email exists
        $stmt = $db->prepare("SELECT id, name FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        if (!$user) {
            return [
                'success' => false,
                'error' => 'Email tidak terdaftar dalam sistem'
            ];
        }
        
        // Check rate limiting (max 3 attempts per hour)
        $oneHourAgo = date('Y-m-d H:i:s', strtotime('-1 hour'));
        $stmt = $db->prepare("
            SELECT COUNT(*) as attempt_count 
            FROM password_resets 
            WHERE email = ? AND created_at > ?
        ");
        $stmt->execute([$email, $oneHourAgo]);
        $result = $stmt->fetch();
        
        if ($result['attempt_count'] >= self::MAX_ATTEMPTS_PER_HOUR) {
            return [
                'success' => false,
                'error' => 'Terlalu banyak percobaan. Silakan coba lagi dalam 1 jam.'
            ];
        }
        
        // Generate OTP
        $otp = self::generateOTP();
        $expiresAt = date('Y-m-d H:i:s', strtotime('+' . self::OTP_EXPIRY_MINUTES . ' minutes'));
        
        // Save OTP to database
        $stmt = $db->prepare("
            INSERT INTO password_resets (email, otp, expires_at, created_at)
            VALUES (?, ?, ?, NOW())
        ");
        $stmt->execute([$email, $otp, $expiresAt]);
        
        // Send OTP via email
        $emailSent = EmailService::sendPasswordResetOTP($email, $otp, $user['name']);
        
        if (!$emailSent) {
            return [
                'success' => false,
                'error' => 'Gagal mengirim email. Silakan coba lagi.'
            ];
        }
        
        // Log activity
        ActivityLog::log(
            'password_reset_requested',
            "OTP reset password dikirim ke email: {$email}",
            $user['id']
        );
        
        return [
            'success' => true,
            'message' => 'Kode OTP telah dikirim ke email Anda. Silakan cek inbox atau folder spam.',
            'expires_in_minutes' => self::OTP_EXPIRY_MINUTES
        ];
    }
    
    /**
     * Verify OTP code
     * 
     * @param string $email User email
     * @param string $otp OTP code
     * @return array Result with success status and message
     */
    public static function verifyOTP($email, $otp) {
        $db = getDbConnection();
        
        // Find valid OTP
        $stmt = $db->prepare("
            SELECT id, expires_at, is_used 
            FROM password_resets 
            WHERE email = ? AND otp = ? 
            ORDER BY created_at DESC 
            LIMIT 1
        ");
        $stmt->execute([$email, $otp]);
        $reset = $stmt->fetch();
        
        if (!$reset) {
            return [
                'success' => false,
                'error' => 'Kode OTP tidak valid'
            ];
        }
        
        // Check if already used
        if ($reset['is_used']) {
            return [
                'success' => false,
                'error' => 'Kode OTP sudah digunakan'
            ];
        }
        
        // Check if expired
        if (strtotime($reset['expires_at']) < time()) {
            return [
                'success' => false,
                'error' => 'Kode OTP sudah kadaluarsa. Silakan minta kode baru.'
            ];
        }
        
        return [
            'success' => true,
            'reset_id' => $reset['id']
        ];
    }
    
    /**
     * Reset password with OTP
     * 
     * @param string $email User email
     * @param string $otp OTP code
     * @param string $newPassword New password
     * @return array Result with success status and message
     */
    public static function resetPassword($email, $otp, $newPassword) {
        // Verify OTP first
        $verification = self::verifyOTP($email, $otp);
        
        if (!$verification['success']) {
            return $verification;
        }
        
        $db = getDbConnection();
        
        // Get user
        $stmt = $db->prepare("SELECT id, name FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        if (!$user) {
            return [
                'success' => false,
                'error' => 'User tidak ditemukan'
            ];
        }
        
        // Hash new password
        $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT, ['cost' => 12]);
        
        // Update password
        $stmt = $db->prepare("
            UPDATE users 
            SET password = ?, updated_at = NOW() 
            WHERE email = ?
        ");
        $stmt->execute([$hashedPassword, $email]);
        
        // Mark OTP as used
        $stmt = $db->prepare("
            UPDATE password_resets 
            SET is_used = 1 
            WHERE id = ?
        ");
        $stmt->execute([$verification['reset_id']]);
        
        // Clear remember token for security
        $stmt = $db->prepare("
            UPDATE users 
            SET remember_token = NULL 
            WHERE email = ?
        ");
        $stmt->execute([$email]);
        
        // Send notification email
        EmailService::sendPasswordChangedNotification($email, $user['name']);
        
        // Log activity
        ActivityLog::log(
            'password_reset_completed',
            "Password berhasil direset untuk email: {$email}",
            $user['id']
        );
        
        return [
            'success' => true,
            'message' => 'Password berhasil direset. Silakan login dengan password baru Anda.'
        ];
    }
    
    /**
     * Generate random OTP
     * 
     * @return string OTP code
     */
    private static function generateOTP() {
        $otp = '';
        for ($i = 0; $i < self::OTP_LENGTH; $i++) {
            $otp .= random_int(0, 9);
        }
        return $otp;
    }
    
    /**
     * Clean up expired OTPs (should be run periodically)
     * 
     * @return int Number of deleted records
     */
    public static function cleanupExpiredOTPs() {
        $db = getDbConnection();
        
        $stmt = $db->prepare("
            DELETE FROM password_resets 
            WHERE expires_at < NOW() OR is_used = 1
        ");
        $stmt->execute();
        
        return $stmt->rowCount();
    }
}
