<?php
// admin/login.php - صفحة تسجيل الدخول
require_once '../config.php';

if (isLoggedIn()) {
    redirect('dashboard.php');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();
    
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        redirect('dashboard.php');
    } else {
        $error = 'اسم المستخدم أو كلمة المرور غير صحيحة';
    }
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تسجيل الدخول - لوحة التحكم</title>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #6366f1;
            --primary-dark: #4f46e5;
            --secondary: #ec4899;
            --dark: #0f172a;
            --darker: #020617;
            --light: #f8fafc;
            --gray: #94a3b8;
            --glass: rgba(255, 255, 255, 0.05);
            --glass-border: rgba(255, 255, 255, 0.1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Tajawal', sans-serif;
            background: var(--darker);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }

        .bg-animation {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
        }

        .orb {
            position: absolute;
            border-radius: 50%;
            filter: blur(100px);
            opacity: 0.3;
        }

        .orb-1 {
            width: 400px;
            height: 400px;
            background: var(--primary);
            top: -100px;
            right: -100px;
        }

        .orb-2 {
            width: 300px;
            height: 300px;
            background: var(--secondary);
            bottom: -50px;
            left: -50px;
        }

        .login-container {
            background: var(--glass);
            backdrop-filter: blur(20px);
            border: 1px solid var(--glass-border);
            border-radius: 30px;
            padding: 3rem;
            width: 100%;
            max-width: 450px;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.3);
            position: relative;
            overflow: hidden;
        }

        .login-container::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: linear-gradient(45deg, transparent, rgba(255, 255, 255, 0.03), transparent);
            transform: rotate(45deg);
            animation: shine 3s infinite;
        }

        @keyframes shine {
            0% { transform: translateX(-100%) translateY(-100%) rotate(45deg); }
            100% { transform: translateX(100%) translateY(100%) rotate(45deg); }
        }

        .login-header {
            text-align: center;
            margin-bottom: 2rem;
            position: relative;
            z-index: 1;
        }

        .login-header .logo {
            font-size: 2.5rem;
            font-weight: 900;
            background: linear-gradient(135deg, #6366f1 0%, #ec4899 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 0.5rem;
        }

        .login-header p {
            color: var(--gray);
        }

        .form-group {
            margin-bottom: 1.5rem;
            position: relative;
            z-index: 1;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: var(--light);
        }

        .input-group {
            position: relative;
        }

        .input-group i {
            position: absolute;
            right: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--gray);
            transition: color 0.3s;
        }

        .input-group input {
            width: 100%;
            padding: 1rem 1rem 1rem 3rem;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid var(--glass-border);
            border-radius: 12px;
            color: var(--light);
            font-family: inherit;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .input-group input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 20px rgba(99, 102, 241, 0.1);
        }

        .input-group input:focus + i {
            color: var(--primary);
        }

        .btn-login {
            width: 100%;
            padding: 1rem;
            background: linear-gradient(135deg, #6366f1 0%, #ec4899 100%);
            border: none;
            border-radius: 12px;
            color: white;
            font-family: inherit;
            font-size: 1.1rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
            z-index: 1;
            overflow: hidden;
        }

        .btn-login::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.5s;
        }

        .btn-login:hover::before {
            left: 100%;
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 30px rgba(99, 102, 241, 0.3);
        }

        .error-message {
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid rgba(239, 68, 68, 0.3);
            color: #ef4444;
            padding: 1rem;
            border-radius: 10px;
            margin-bottom: 1.5rem;
            text-align: center;
            position: relative;
            z-index: 1;
        }

        .back-link {
            display: block;
            text-align: center;
            margin-top: 1.5rem;
            color: var(--gray);
            text-decoration: none;
            transition: color 0.3s;
            position: relative;
            z-index: 1;
        }

        .back-link:hover {
            color: var(--primary);
        }

        @media (max-width: 480px) {
            .login-container {
                margin: 1rem;
                padding: 2rem;
            }
        }
    </style>
</head>
<body>
    <div class="bg-animation">
        <div class="orb orb-1"></div>
        <div class="orb orb-2"></div>
    </div>

    <div class="login-container">
        <div class="login-header">
            <div class="logo"><i class="fas fa-shield-alt"></i></div>
            <h2>لوحة التحكم</h2>
            <p>قم بتسجيل الدخول للوصول إلى لوحة الإدارة</p>
        </div>

        <?php if ($error): ?>
        <div class="error-message">
            <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
        </div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-group">
                <label for="username">اسم المستخدم</label>
                <div class="input-group">
                    <input type="text" id="username" name="username" required placeholder="أدخل اسم المستخدم">
                    <i class="fas fa-user"></i>
                </div>
            </div>
            <div class="form-group">
                <label for="password">كلمة المرور</label>
                <div class="input-group">
                    <input type="password" id="password" name="password" required placeholder="أدخل كلمة المرور">
                    <i class="fas fa-lock"></i>
                </div>
            </div>
            <button type="submit" class="btn-login">
                <i class="fas fa-sign-in-alt"></i> تسجيل الدخول
            </button>
        </form>

        <a href="../index.php" class="back-link">
            <i class="fas fa-arrow-right"></i> العودة إلى الموقع
        </a>
    </div>
</body>
</html>
