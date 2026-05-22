<?php

/**
 * Email Service Class
 * Handles sending emails including OTP for password reset
 */
class EmailService {
    
    private static $fromEmail;
    private static $fromName;
    
    /**
     * Initialize email service
     */
    public static function init() {
        self::$fromEmail = CONTACT_EMAIL ?? 'noreply@fern.test';
        self::$fromName = APP_NAME ?? 'FERN';
    }
    
    /**
     * Send email using PHP mail() function
     * 
     * @param string $to Recipient email
     * @param string $subject Email subject
     * @param string $message Email body (HTML)
     * @return bool Success status
     */
    public static function send($to, $subject, $message) {
        self::init();
        
        // Email headers
        $headers = [
            'MIME-Version: 1.0',
            'Content-type: text/html; charset=utf-8',
            'From: ' . self::$fromName . ' <' . self::$fromEmail . '>',
            'Reply-To: ' . self::$fromEmail,
            'X-Mailer: PHP/' . phpversion()
        ];
        
        // Send email
        $success = mail($to, $subject, $message, implode("\r\n", $headers));
        
        // Log email activity
        if ($success) {
            error_log("Email sent successfully to: {$to}");
        } else {
            error_log("Failed to send email to: {$to}");
        }
        
        return $success;
    }
    
    /**
     * Send OTP email for password reset
     * 
     * @param string $email User email
     * @param string $otp OTP code
     * @param string $userName User name
     * @return bool Success status
     */
    public static function sendPasswordResetOTP($email, $otp, $userName = '') {
        $subject = 'Kode OTP Reset Password - ' . APP_NAME;
        
        $message = self::getOTPEmailTemplate($otp, $userName);
        
        return self::send($email, $subject, $message);
    }
    
    /**
     * Get OTP email template
     * 
     * @param string $otp OTP code
     * @param string $userName User name
     * @return string HTML email template
     */
    private static function getOTPEmailTemplate($otp, $userName = '') {
        $greeting = $userName ? "Halo {$userName}," : "Halo,";
        $appName = APP_NAME;
        $contactEmail = CONTACT_EMAIL;
        
        return <<<HTML
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password OTP</title>
</head>
<body style="margin: 0; padding: 0; font-family: Arial, sans-serif; background-color: #f4f4f4;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background-color: #f4f4f4; padding: 20px;">
        <tr>
            <td align="center">
                <table width="600" cellpadding="0" cellspacing="0" style="background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                    <!-- Header -->
                    <tr>
                        <td style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 30px; text-align: center;">
                            <h1 style="color: #ffffff; margin: 0; font-size: 24px;">Reset Password</h1>
                        </td>
                    </tr>
                    
                    <!-- Content -->
                    <tr>
                        <td style="padding: 40px 30px;">
                            <p style="color: #333333; font-size: 16px; line-height: 1.6; margin: 0 0 20px 0;">
                                {$greeting}
                            </p>
                            
                            <p style="color: #333333; font-size: 16px; line-height: 1.6; margin: 0 0 20px 0;">
                                Kami menerima permintaan untuk mereset password akun Anda di <strong>{$appName}</strong>.
                            </p>
                            
                            <p style="color: #333333; font-size: 16px; line-height: 1.6; margin: 0 0 30px 0;">
                                Gunakan kode OTP berikut untuk mereset password Anda:
                            </p>
                            
                            <!-- OTP Box -->
                            <table width="100%" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td align="center" style="padding: 20px 0;">
                                        <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 8px; padding: 20px; display: inline-block;">
                                            <span style="color: #ffffff; font-size: 32px; font-weight: bold; letter-spacing: 8px; font-family: 'Courier New', monospace;">
                                                {$otp}
                                            </span>
                                        </div>
                                    </td>
                                </tr>
                            </table>
                            
                            <p style="color: #666666; font-size: 14px; line-height: 1.6; margin: 30px 0 20px 0; text-align: center;">
                                Kode OTP ini berlaku selama <strong>10 menit</strong>
                            </p>
                            
                            <!-- Warning Box -->
                            <table width="100%" cellpadding="0" cellspacing="0" style="margin: 20px 0;">
                                <tr>
                                    <td style="background-color: #fff3cd; border-left: 4px solid #ffc107; padding: 15px; border-radius: 4px;">
                                        <p style="color: #856404; font-size: 14px; line-height: 1.6; margin: 0;">
                                            ⚠️ <strong>Perhatian:</strong> Jika Anda tidak meminta reset password, abaikan email ini. Akun Anda tetap aman.
                                        </p>
                                    </td>
                                </tr>
                            </table>
                            
                            <p style="color: #666666; font-size: 14px; line-height: 1.6; margin: 20px 0 0 0;">
                                Jangan bagikan kode OTP ini kepada siapapun, termasuk staff kami.
                            </p>
                        </td>
                    </tr>
                    
                    <!-- Footer -->
                    <tr>
                        <td style="background-color: #f8f9fa; padding: 20px 30px; border-top: 1px solid #e9ecef;">
                            <p style="color: #6c757d; font-size: 12px; line-height: 1.6; margin: 0; text-align: center;">
                                Email ini dikirim secara otomatis, mohon tidak membalas email ini.
                            </p>
                            <p style="color: #6c757d; font-size: 12px; line-height: 1.6; margin: 10px 0 0 0; text-align: center;">
                                Jika ada pertanyaan, hubungi kami di <a href="mailto:{$contactEmail}" style="color: #667eea; text-decoration: none;">{$contactEmail}</a>
                            </p>
                            <p style="color: #6c757d; font-size: 12px; line-height: 1.6; margin: 10px 0 0 0; text-align: center;">
                                &copy; 2024 {$appName}. All rights reserved.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
HTML;
    }
    
    /**
     * Send password changed notification
     * 
     * @param string $email User email
     * @param string $userName User name
     * @return bool Success status
     */
    public static function sendPasswordChangedNotification($email, $userName = '') {
        $subject = 'Password Anda Telah Diubah - ' . APP_NAME;
        
        $greeting = $userName ? "Halo {$userName}," : "Halo,";
        $appName = APP_NAME;
        $contactEmail = CONTACT_EMAIL;
        
        $message = <<<HTML
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Password Changed</title>
</head>
<body style="margin: 0; padding: 0; font-family: Arial, sans-serif; background-color: #f4f4f4;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background-color: #f4f4f4; padding: 20px;">
        <tr>
            <td align="center">
                <table width="600" cellpadding="0" cellspacing="0" style="background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                    <!-- Header -->
                    <tr>
                        <td style="background: linear-gradient(135deg, #28a745 0%, #20c997 100%); padding: 30px; text-align: center;">
                            <h1 style="color: #ffffff; margin: 0; font-size: 24px;">✓ Password Berhasil Diubah</h1>
                        </td>
                    </tr>
                    
                    <!-- Content -->
                    <tr>
                        <td style="padding: 40px 30px;">
                            <p style="color: #333333; font-size: 16px; line-height: 1.6; margin: 0 0 20px 0;">
                                {$greeting}
                            </p>
                            
                            <p style="color: #333333; font-size: 16px; line-height: 1.6; margin: 0 0 20px 0;">
                                Password akun Anda di <strong>{$appName}</strong> telah berhasil diubah.
                            </p>
                            
                            <p style="color: #333333; font-size: 16px; line-height: 1.6; margin: 0 0 20px 0;">
                                Jika Anda yang melakukan perubahan ini, Anda dapat mengabaikan email ini.
                            </p>
                            
                            <!-- Warning Box -->
                            <table width="100%" cellpadding="0" cellspacing="0" style="margin: 20px 0;">
                                <tr>
                                    <td style="background-color: #f8d7da; border-left: 4px solid #dc3545; padding: 15px; border-radius: 4px;">
                                        <p style="color: #721c24; font-size: 14px; line-height: 1.6; margin: 0;">
                                            ⚠️ <strong>Perhatian:</strong> Jika Anda TIDAK melakukan perubahan ini, segera hubungi kami dan amankan akun Anda.
                                        </p>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    
                    <!-- Footer -->
                    <tr>
                        <td style="background-color: #f8f9fa; padding: 20px 30px; border-top: 1px solid #e9ecef;">
                            <p style="color: #6c757d; font-size: 12px; line-height: 1.6; margin: 0; text-align: center;">
                                Email ini dikirim secara otomatis, mohon tidak membalas email ini.
                            </p>
                            <p style="color: #6c757d; font-size: 12px; line-height: 1.6; margin: 10px 0 0 0; text-align: center;">
                                Jika ada pertanyaan, hubungi kami di <a href="mailto:{$contactEmail}" style="color: #667eea; text-decoration: none;">{$contactEmail}</a>
                            </p>
                            <p style="color: #6c757d; font-size: 12px; line-height: 1.6; margin: 10px 0 0 0; text-align: center;">
                                &copy; 2024 {$appName}. All rights reserved.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
HTML;
        
        return self::send($email, $subject, $message);
    }
}
