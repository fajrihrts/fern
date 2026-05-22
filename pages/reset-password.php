<?php
requireGuest();

$pageTitle = 'Reset Password';
$error = '';
$success = '';
$step = 1; // 1 = verify OTP, 2 = set new password

// Get email from session or form
$email = $_SESSION['reset_email'] ?? ($_POST['email'] ?? '');

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $otp = trim($_POST['otp'] ?? '');
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    
    if (empty($email)) {
        $error = 'Email tidak ditemukan. Silakan mulai dari awal.';
    } elseif (empty($otp)) {
        $error = 'Kode OTP harus diisi';
    } elseif (isset($_POST['new_password'])) {
        // Step 2: Reset password
        if (empty($newPassword)) {
            $error = 'Password baru harus diisi';
        } elseif (strlen($newPassword) < 8) {
            $error = 'Password minimal 8 karakter';
        } elseif ($newPassword !== $confirmPassword) {
            $error = 'Konfirmasi password tidak cocok';
        } else {
            // Reset password
            $result = PasswordReset::resetPassword($email, $otp, $newPassword);
            
            if ($result['success']) {
                $success = $result['message'];
                unset($_SESSION['reset_email']);
                $step = 3; // Success step
            } else {
                $error = $result['error'];
            }
        }
    } else {
        // Step 1: Verify OTP
        $verification = PasswordReset::verifyOTP($email, $otp);
        
        if ($verification['success']) {
            $step = 2; // Move to password reset step
            $_SESSION['verified_otp'] = $otp;
        } else {
            $error = $verification['error'];
        }
    }
}

// Check if OTP already verified
if (isset($_SESSION['verified_otp']) && $step === 1) {
    $step = 2;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?> - <?= APP_NAME ?></title>
    <link rel="icon" type="image/png" href="/assets/img/favicon.png">
    <link rel="stylesheet" href="/assets/css/style.css">
    <style>
        .auth-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 20px;
        }
        
        .auth-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            max-width: 450px;
            width: 100%;
            padding: 40px;
        }
        
        .auth-header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .auth-header img {
            width: 80px;
            height: 80px;
            margin-bottom: 20px;
        }
        
        .auth-header h1 {
            color: #2d3748;
            font-size: 24px;
            margin-bottom: 8px;
        }
        
        .auth-header p {
            color: #718096;
            font-size: 14px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            color: #4a5568;
            font-weight: 600;
            margin-bottom: 8px;
            font-size: 14px;
        }
        
        .form-group input {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            font-size: 14px;
            transition: all 0.3s;
        }
        
        .form-group input:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        
        .otp-input {
            text-align: center;
            font-size: 24px;
            letter-spacing: 8px;
            font-family: 'Courier New', monospace;
            font-weight: bold;
        }
        
        .alert {
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
        }
        
        .alert-danger {
            background-color: #fee;
            color: #c53030;
            border: 1px solid #fc8181;
        }
        
        .alert-success {
            background-color: #f0fff4;
            color: #22543d;
            border: 1px solid #68d391;
        }
        
        .alert-info {
            background-color: #ebf8ff;
            color: #2c5282;
            border: 1px solid #90cdf4;
        }
        
        .btn {
            width: 100%;
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
        }
        
        .btn-primary:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }
        
        .btn-success {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
        }
        
        .btn-success:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(40, 167, 69, 0.3);
        }
        
        .auth-footer {
            text-align: center;
            margin-top: 24px;
            padding-top: 24px;
            border-top: 1px solid #e2e8f0;
        }
        
        .auth-footer a {
            color: #667eea;
            text-decoration: none;
            font-weight: 600;
            font-size: 14px;
        }
        
        .auth-footer a:hover {
            text-decoration: underline;
        }
        
        .password-requirements {
            background: #f7fafc;
            border-radius: 8px;
            padding: 12px;
            margin-top: 8px;
            font-size: 12px;
            color: #4a5568;
        }
        
        .password-requirements ul {
            margin: 8px 0 0 0;
            padding-left: 20px;
        }
        
        .password-requirements li {
            margin: 4px 0;
        }
        
        .success-icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            font-size: 40px;
        }
        
        .step-indicator {
            display: flex;
            justify-content: center;
            margin-bottom: 30px;
            gap: 10px;
        }
        
        .step {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #e2e8f0;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            color: #718096;
        }
        
        .step.active {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        
        .step.completed {
            background: #48bb78;
            color: white;
        }
    </style>
</head>
<body>
    <div class="auth-container">
        <div class="auth-card">
            <?php if ($step === 3): ?>
                <!-- Success Step -->
                <div class="auth-header">
                    <div class="success-icon">✓</div>
                    <h1>Password Berhasil Direset!</h1>
                    <p>Anda sekarang dapat login dengan password baru</p>
                </div>
                
                <div class="alert alert-success">
                    <?= htmlspecialchars($success) ?>
                </div>
                
                <a href="/login" class="btn btn-success">
                    Login Sekarang
                </a>
                
            <?php elseif ($step === 2): ?>
                <!-- Step 2: Set New Password -->
                <div class="step-indicator">
                    <div class="step completed">1</div>
                    <div class="step active">2</div>
                </div>
                
                <div class="auth-header">
                    <img src="/assets/img/brand.png" alt="Logo" onerror="this.style.display='none'">
                    <h1>Buat Password Baru</h1>
                    <p>Masukkan password baru untuk akun Anda</p>
                </div>
                
                <?php if ($error): ?>
                    <div class="alert alert-danger">
                        <?= htmlspecialchars($error) ?>
                    </div>
                <?php endif; ?>
                
                <form method="POST" action="">
                    <input type="hidden" name="email" value="<?= htmlspecialchars($email) ?>">
                    <input type="hidden" name="otp" value="<?= htmlspecialchars($_SESSION['verified_otp'] ?? $_POST['otp'] ?? '') ?>">
                    
                    <div class="form-group">
                        <label for="new_password">Password Baru</label>
                        <input 
                            type="password" 
                            id="new_password" 
                            name="new_password" 
                            placeholder="Masukkan password baru"
                            required
                            autofocus
                            minlength="8"
                        >
                        <div class="password-requirements">
                            <strong>Persyaratan password:</strong>
                            <ul>
                                <li>Minimal 8 karakter</li>
                                <li>Gunakan kombinasi huruf dan angka</li>
                                <li>Hindari password yang mudah ditebak</li>
                            </ul>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="confirm_password">Konfirmasi Password</label>
                        <input 
                            type="password" 
                            id="confirm_password" 
                            name="confirm_password" 
                            placeholder="Masukkan ulang password baru"
                            required
                            minlength="8"
                        >
                    </div>
                    
                    <button type="submit" class="btn btn-primary">
                        Reset Password
                    </button>
                </form>
                
            <?php else: ?>
                <!-- Step 1: Verify OTP -->
                <div class="step-indicator">
                    <div class="step active">1</div>
                    <div class="step">2</div>
                </div>
                
                <div class="auth-header">
                    <img src="/assets/img/brand.png" alt="Logo" onerror="this.style.display='none'">
                    <h1>Verifikasi Kode OTP</h1>
                    <p>Masukkan kode 6 digit yang dikirim ke email Anda</p>
                </div>
                
                <?php if ($error): ?>
                    <div class="alert alert-danger">
                        <?= htmlspecialchars($error) ?>
                    </div>
                <?php endif; ?>
                
                <div class="alert alert-info">
                    📧 Kode OTP telah dikirim ke: <strong><?= htmlspecialchars($email) ?></strong>
                </div>
                
                <form method="POST" action="">
                    <input type="hidden" name="email" value="<?= htmlspecialchars($email) ?>">
                    
                    <div class="form-group">
                        <label for="otp">Kode OTP</label>
                        <input 
                            type="text" 
                            id="otp" 
                            name="otp" 
                            class="otp-input"
                            placeholder="000000"
                            maxlength="6"
                            pattern="[0-9]{6}"
                            required
                            autofocus
                        >
                    </div>
                    
                    <button type="submit" class="btn btn-primary">
                        Verifikasi OTP
                    </button>
                </form>
                
                <div class="auth-footer">
                    <p style="margin-bottom: 10px; color: #718096; font-size: 14px;">
                        Tidak menerima kode?
                    </p>
                    <a href="/forgot-password">Kirim Ulang Kode OTP</a>
                </div>
            <?php endif; ?>
            
            <?php if ($step !== 3): ?>
                <div class="auth-footer" style="margin-top: 16px;">
                    <a href="/login">← Kembali ke Login</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <script>
        // Auto-format OTP input
        const otpInput = document.getElementById('otp');
        if (otpInput) {
            otpInput.addEventListener('input', function(e) {
                this.value = this.value.replace(/[^0-9]/g, '');
            });
        }
        
        // Password match validation
        const newPassword = document.getElementById('new_password');
        const confirmPassword = document.getElementById('confirm_password');
        
        if (confirmPassword) {
            confirmPassword.addEventListener('input', function() {
                if (this.value !== newPassword.value) {
                    this.setCustomValidity('Password tidak cocok');
                } else {
                    this.setCustomValidity('');
                }
            });
        }
    </script>
</body>
</html>
