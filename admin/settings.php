<?php
// admin/settings.php - إعدادات الموقع
require_once '../config.php';

if (!isLoggedIn()) {
    redirect('login.php');
}

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $settings = [
        'site_title' => clean($_POST['site_title'] ?? ''),
        'site_description' => clean($_POST['site_description'] ?? ''),
        'contact_email' => clean($_POST['contact_email'] ?? ''),
        'contact_phone' => clean($_POST['contact_phone'] ?? ''),
        'contact_address' => clean($_POST['contact_address'] ?? ''),
        'facebook' => clean($_POST['facebook'] ?? ''),
        'twitter' => clean($_POST['twitter'] ?? ''),
        'linkedin' => clean($_POST['linkedin'] ?? ''),
        'github' => clean($_POST['github'] ?? ''),
        'instagram' => clean($_POST['instagram'] ?? ''),
    ];

    try {
        $stmt = $pdo->prepare("INSERT OR REPLACE INTO site_settings (setting_key, setting_value) VALUES (?, ?)");
        foreach ($settings as $key => $value) {
            $stmt->execute([$key, $value]);
        }
        $message = 'تم حفظ الإعدادات بنجاح';
    } catch (PDOException $e) {
        $error = 'خطأ في الحفظ';
    }
}

// جلب الإعدادات الحالية
$stmt = $pdo->query("SELECT * FROM site_settings");
$current_settings = [];
while ($row = $stmt->fetch()) {
    $current_settings[$row['setting_key']] = $row['setting_value'];
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>الإعدادات - لوحة التحكم</title>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #6366f1;
            --primary-dark: #4f46e5;
            --secondary: #ec4899;
            --accent: #06b6d4;
            --success: #10b981;
            --warning: #f59e0b;
            --danger: #ef4444;
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
            color: var(--light);
            min-height: 100vh;
        }

        .admin-layout {
            display: grid;
            grid-template-columns: 280px 1fr;
            min-height: 100vh;
        }

        .sidebar {
            background: var(--dark);
            border-left: 1px solid var(--glass-border);
            padding: 2rem;
            position: fixed;
            right: 0;
            top: 0;
            height: 100vh;
            width: 280px;
            overflow-y: auto;
            z-index: 100;
        }

        .sidebar-header {
            text-align: center;
            padding-bottom: 2rem;
            border-bottom: 1px solid var(--glass-border);
            margin-bottom: 2rem;
        }

        .sidebar-header .logo {
            font-size: 1.8rem;
            font-weight: 900;
            background: linear-gradient(135deg, #6366f1 0%, #ec4899 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .nav-menu {
            list-style: none;
        }

        .nav-menu li {
            margin-bottom: 0.5rem;
        }

        .nav-menu a {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1rem 1.5rem;
            color: var(--gray);
            text-decoration: none;
            border-radius: 12px;
            transition: all 0.3s ease;
        }

        .nav-menu a:hover,
        .nav-menu a.active {
            background: var(--glass);
            color: var(--light);
            border: 1px solid var(--glass-border);
        }

        .nav-menu a i {
            width: 24px;
            text-align: center;
        }

        .sidebar-footer {
            margin-top: auto;
            padding-top: 2rem;
            border-top: 1px solid var(--glass-border);
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1rem;
        }

        .user-avatar {
            width: 45px;
            height: 45px;
            background: var(--gradient-1);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 900;
        }

        .btn-logout {
            width: 100%;
            padding: 0.8rem;
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid rgba(239, 68, 68, 0.3);
            color: #ef4444;
            border-radius: 10px;
            cursor: pointer;
            font-family: inherit;
            font-size: 0.9rem;
            transition: all 0.3s;
            text-decoration: none;
            display: block;
            text-align: center;
        }

        .main-content {
            margin-right: 280px;
            padding: 2rem;
        }

        .top-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            padding: 1.5rem;
            background: var(--glass);
            backdrop-filter: blur(20px);
            border: 1px solid var(--glass-border);
            border-radius: 20px;
        }

        .page-title h1 {
            font-size: 1.5rem;
            font-weight: 900;
        }

        .btn {
            padding: 0.8rem 1.5rem;
            border-radius: 10px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s;
            border: none;
            cursor: pointer;
            font-family: inherit;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn-primary {
            background: var(--primary);
            color: white;
        }

        .btn-primary:hover {
            background: var(--primary-dark);
        }

        .alert {
            padding: 1rem 1.5rem;
            border-radius: 12px;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .alert-success {
            background: rgba(16, 185, 129, 0.1);
            border: 1px solid rgba(16, 185, 129, 0.3);
            color: var(--success);
        }

        .content-section {
            background: var(--glass);
            backdrop-filter: blur(20px);
            border: 1px solid var(--glass-border);
            border-radius: 20px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
        }

        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }

        .section-header h3 {
            font-size: 1.2rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
        }

        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 0.8rem 1rem;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid var(--glass-border);
            border-radius: 10px;
            color: var(--light);
            font-family: inherit;
            font-size: 1rem;
            transition: all 0.3s;
        }

        .form-group input:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: var(--primary);
        }

        .form-group textarea {
            min-height: 100px;
            resize: vertical;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }

        .social-input {
            position: relative;
        }

        .social-input i {
            position: absolute;
            right: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--gray);
        }

        .social-input input {
            padding-right: 2.5rem;
        }

        @media (max-width: 968px) {
            .admin-layout {
                grid-template-columns: 1fr;
            }

            .sidebar {
                transform: translateX(100%);
                transition: transform 0.3s ease;
            }

            .main-content {
                margin-right: 0;
            }

            .form-row {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="admin-layout">
        <aside class="sidebar">
            <div class="sidebar-header">
                <div class="logo"><i class="fas fa-cube"></i> لوحة التحكم</div>
            </div>
            <ul class="nav-menu">
                <li><a href="dashboard.php"><i class="fas fa-home"></i> الرئيسية</a></li>
                <li><a href="sections.php"><i class="fas fa-layer-group"></i> الأقسام</a></li>
                <li><a href="projects.php"><i class="fas fa-project-diagram"></i> المشاريع</a></li>
                <li><a href="messages.php"><i class="fas fa-envelope"></i> الرسائل</a></li>
                <li><a href="settings.php" class="active"><i class="fas fa-cog"></i> الإعدادات</a></li>
            </ul>
            <div class="sidebar-footer">
                <div class="user-info">
                    <div class="user-avatar"><i class="fas fa-user"></i></div>
                    <div>
                        <h4><?php echo $_SESSION['username']; ?></h4>
                        <p style="color: var(--gray); font-size: 0.8rem;">مدير الموقع</p>
                    </div>
                </div>
                <a href="logout.php" class="btn-logout"><i class="fas fa-sign-out-alt"></i> تسجيل الخروج</a>
            </div>
        </aside>

        <main class="main-content">
            <div class="top-bar">
                <div class="page-title">
                    <h1>إعدادات الموقع</h1>
                </div>
            </div>

            <?php if ($message): ?>
            <div class="alert alert-success"><i class="fas fa-check-circle"></i> <?php echo $message; ?></div>
            <?php endif; ?>

            <form method="POST" action="">
                <!-- إعدادات عامة -->
                <div class="content-section">
                    <div class="section-header">
                        <h3><i class="fas fa-info-circle"></i> معلومات الموقع</h3>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="site_title">عنوان الموقع</label>
                            <input type="text" id="site_title" name="site_title" 
                                   value="<?php echo $current_settings['site_title'] ?? 'موقعي الشخصي'; ?>">
                        </div>
                        <div class="form-group">
                            <label for="contact_email">البريد الإلكتروني للتواصل</label>
                            <input type="email" id="contact_email" name="contact_email" 
                                   value="<?php echo $current_settings['contact_email'] ?? ''; ?>">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="site_description">وصف الموقع</label>
                        <textarea id="site_description" name="site_description" placeholder="وصف مختصر يظهر في نتائج البحث..."><?php echo $current_settings['site_description'] ?? ''; ?></textarea>
                    </div>
                </div>

                <!-- معلومات التواصل -->
                <div class="content-section">
                    <div class="section-header">
                        <h3><i class="fas fa-address-card"></i> معلومات التواصل</h3>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="contact_phone">رقم الهاتف</label>
                            <input type="text" id="contact_phone" name="contact_phone" 
                                   value="<?php echo $current_settings['contact_phone'] ?? ''; ?>">
                        </div>
                        <div class="form-group">
                            <label for="contact_address">العنوان</label>
                            <input type="text" id="contact_address" name="contact_address" 
                                   value="<?php echo $current_settings['contact_address'] ?? ''; ?>">
                        </div>
                    </div>
                </div>

                <!-- وسائل التواصل الاجتماعي -->
                <div class="content-section">
                    <div class="section-header">
                        <h3><i class="fas fa-share-alt"></i> وسائل التواصل الاجتماعي</h3>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="facebook">فيسبوك</label>
                            <div class="social-input">
                                <input type="url" id="facebook" name="facebook" 
                                       value="<?php echo $current_settings['facebook'] ?? ''; ?>"
                                       placeholder="https://facebook.com/username">
                                <i class="fab fa-facebook-f"></i>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="twitter">تويتر</label>
                            <div class="social-input">
                                <input type="url" id="twitter" name="twitter" 
                                       value="<?php echo $current_settings['twitter'] ?? ''; ?>"
                                       placeholder="https://twitter.com/username">
                                <i class="fab fa-twitter"></i>
                            </div>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="linkedin">لينكد إن</label>
                            <div class="social-input">
                                <input type="url" id="linkedin" name="linkedin" 
                                       value="<?php echo $current_settings['linkedin'] ?? ''; ?>"
                                       placeholder="https://linkedin.com/in/username">
                                <i class="fab fa-linkedin-in"></i>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="github">جيت هاب</label>
                            <div class="social-input">
                                <input type="url" id="github" name="github" 
                                       value="<?php echo $current_settings['github'] ?? ''; ?>"
                                       placeholder="https://github.com/username">
                                <i class="fab fa-github"></i>
                            </div>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="instagram">إنستغرام</label>
                            <div class="social-input">
                                <input type="url" id="instagram" name="instagram" 
                                       value="<?php echo $current_settings['instagram'] ?? ''; ?>"
                                       placeholder="https://instagram.com/username">
                                <i class="fab fa-instagram"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary" style="padding: 1rem 3rem; font-size: 1.1rem;">
                    <i class="fas fa-save"></i> حفظ الإعدادات
                </button>
            </form>
        </main>
    </div>
</body>
</html>
