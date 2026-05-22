<?php
requireGuest();

$pageTitle = 'Lupa Password';
$error = '';
$success = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    
    // Validate email
    if (empty($email)) {
        $error = 'Email harus diisi';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Format email tidak valid';
    } else {
        // Send OTP
        $result = PasswordReset::sendOTP($email);
        
        if ($result['success']) {
            $success = $result['message'];
            // Store email in session for next step
            $_SESSION['reset_email'] = $email;
        } else {
            $error = $result['error'];
        }
    }
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
        }
        
        .auth-footer a:hover {
            text-decoration: underline;
        }
        
        .info-box {
            background: #ebf8ff;
            border-left: 4px solid #4299e1;
            padding: 16px;
            border-radius: 4px;
            margin-bottom: 20px;
            font-size: 14px;
            color: #2c5282;
        }
        
        .info-box strong {
            display: block;
            margin-bottom: 8px;
        }
    </style>
</head>
<body>
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <img src="/assets/img/brand.png" alt="Logo" onerror="this.style.display='none'">
                <h1>Lupa Password?</h1>
                <p>Masukkan email Anda untuk menerima kode OTP</p>
            </div>
            
            <?php if ($error): ?>
                <div class="alert alert-danger">
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success">
                    <?= htmlspecialchars($success) ?>
                </div>
                <div class="info-box">
                    <strong>Langkah selanjutnya:</strong>
                    Cek email Anda dan masukkan kode OTP yang dikirimkan. Jika tidak menemukan email, periksa folder spam.
                </div>
                <a href="/reset-password" class="btn btn-primary">Lanjutkan ke Reset Password</a>
            <?php else: ?>
                <form method="POST" action="">
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input 
                            type="email" 
                            id="email" 
                            name="email" 
                            placeholder="nama@email.com"
                            value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                            required
                            autofocus
                        >
                    </div>
                    
                    <button type="submit" class="btn btn-primary">
                        Kirim Kode OTP
                    </button>
                </form>
            <?php endif; ?>
            
            <div class="auth-footer">
                <a href="/login">← Kembali ke Login</a>
            </div>
        </div>
    </div>
</body>
</html>
