<?php
// admin/sections.php - إدارة الأقسام
require_once '../config.php';

if (!isLoggedIn()) {
    redirect('login.php');
}

$message = '';
$error = '';

// إضافة/تعديل قسم
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? '';
    $title = clean($_POST['title'] ?? '');
    $content = clean($_POST['content'] ?? '');
    $section_type = clean($_POST['section_type'] ?? 'about');
    $display_order = (int)($_POST['display_order'] ?? 0);
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    
    if (empty($title)) {
        $error = 'عنوان القسم مطلوب';
    } else {
        try {
            if ($id) {
                $stmt = $pdo->prepare("UPDATE sections SET title = ?, content = ?, section_type = ?, display_order = ?, is_active = ? WHERE id = ?");
                $stmt->execute([$title, $content, $section_type, $display_order, $is_active, $id]);
                $message = 'تم تحديث القسم بنجاح';
            } else {
                $stmt = $pdo->prepare("INSERT INTO sections (title, content, section_type, display_order, is_active) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$title, $content, $section_type, $display_order, $is_active]);
                $message = 'تم إضافة القسم بنجاح';
            }
        } catch (PDOException $e) {
            $error = 'خطأ في قاعدة البيانات';
        }
    }
}

// حذف قسم
if (isset($_GET['delete'])) {
    try {
        $stmt = $pdo->prepare("DELETE FROM sections WHERE id = ?");
        $stmt->execute([$_GET['delete']]);
        $message = 'تم حذف القسم بنجاح';
    } catch (PDOException $e) {
        $error = 'خطأ في الحذف';
    }
}

// جلب الأقسام
$sections = $pdo->query("SELECT * FROM sections ORDER BY display_order")->fetchAll();

// تعديل قسم
$edit_section = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM sections WHERE id = ?");
    $stmt->execute([$_GET['edit']]);
    $edit_section = $stmt->fetch();
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إدارة الأقسام - لوحة التحكم</title>
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

        .btn-secondary {
            background: var(--glass);
            color: var(--light);
            border: 1px solid var(--glass-border);
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

        .alert-danger {
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid rgba(239, 68, 68, 0.3);
            color: var(--danger);
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
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            padding: 1rem;
            text-align: right;
            border-bottom: 1px solid var(--glass-border);
        }

        th {
            color: var(--gray);
            font-weight: 600;
            font-size: 0.85rem;
        }

        .status {
            padding: 0.3rem 0.8rem;
            border-radius: 50px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .status.active {
            background: rgba(16, 185, 129, 0.1);
            color: var(--success);
        }

        .status.inactive {
            background: rgba(239, 68, 68, 0.1);
            color: var(--danger);
        }

        .action-btns {
            display: flex;
            gap: 0.5rem;
        }

        .btn-icon {
            width: 35px;
            height: 35px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            border: none;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
        }

        .btn-edit {
            background: rgba(99, 102, 241, 0.1);
            color: var(--primary);
        }

        .btn-delete {
            background: rgba(239, 68, 68, 0.1);
            color: var(--danger);
        }

        .btn-icon:hover {
            transform: scale(1.1);
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
        .form-group textarea,
        .form-group select {
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
        .form-group textarea:focus,
        .form-group select:focus {
            outline: none;
            border-color: var(--primary);
        }

        .form-group textarea {
            min-height: 120px;
            resize: vertical;
        }

        .checkbox-group {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .checkbox-group input[type="checkbox"] {
            width: auto;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
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
                <li><a href="sections.php" class="active"><i class="fas fa-layer-group"></i> الأقسام</a></li>
                <li><a href="projects.php"><i class="fas fa-project-diagram"></i> المشاريع</a></li>
                <li><a href="messages.php"><i class="fas fa-envelope"></i> الرسائل</a></li>
                <li><a href="settings.php"><i class="fas fa-cog"></i> الإعدادات</a></li>
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
                    <h1>إدارة الأقسام</h1>
                </div>
                <a href="../index.php" target="_blank" class="btn btn-primary"><i class="fas fa-external-link-alt"></i> عرض الموقع</a>
            </div>

            <?php if ($message): ?>
            <div class="alert alert-success"><i class="fas fa-check-circle"></i> <?php echo $message; ?></div>
            <?php endif; ?>

            <?php if ($error): ?>
            <div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> <?php echo $error; ?></div>
            <?php endif; ?>

            <!-- نموذج الإضافة/التعديل -->
            <div class="content-section">
                <div class="section-header">
                    <h3><i class="fas fa-<?php echo $edit_section ? 'edit' : 'plus'; ?>"></i> 
                        <?php echo $edit_section ? 'تعديل القسم' : 'إضافة قسم جديد'; ?>
                    </h3>
                </div>
                <form method="POST" action="">
                    <input type="hidden" name="id" value="<?php echo $edit_section['id'] ?? ''; ?>">
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="title">عنوان القسم *</label>
                            <input type="text" id="title" name="title" required 
                                   value="<?php echo $edit_section['title'] ?? ''; ?>" 
                                   placeholder="مثال: عنّي">
                        </div>
                        <div class="form-group">
                            <label for="section_type">نوع القسم</label>
                            <select id="section_type" name="section_type">
                                <option value="about" <?php echo ($edit_section['section_type'] ?? '') === 'about' ? 'selected' : ''; ?>>عنّي</option>
                                <option value="services" <?php echo ($edit_section['section_type'] ?? '') === 'services' ? 'selected' : ''; ?>>خدمات</option>
                                <option value="skills" <?php echo ($edit_section['section_type'] ?? '') === 'skills' ? 'selected' : ''; ?>>مهارات</option>
                                <option value="custom" <?php echo ($edit_section['section_type'] ?? '') === 'custom' ? 'selected' : ''; ?>>مخصص</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="content">محتوى القسم</label>
                        <textarea id="content" name="content" placeholder="محتوى القسم..."><?php echo $edit_section['content'] ?? ''; ?></textarea>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="display_order">ترتيب العرض</label>
                            <input type="number" id="display_order" name="display_order" 
                                   value="<?php echo $edit_section['display_order'] ?? '0'; ?>" min="0">
                        </div>
                        <div class="form-group">
                            <label class="checkbox-group" style="margin-top: 2rem;">
                                <input type="checkbox" name="is_active" <?php echo ($edit_section['is_active'] ?? 1) ? 'checked' : ''; ?>>
                                <span>نشط</span>
                            </label>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> <?php echo $edit_section ? 'تحديث' : 'حفظ'; ?>
                    </button>
                    
                    <?php if ($edit_section): ?>
                    <a href="sections.php" class="btn btn-secondary">إلغاء</a>
                    <?php endif; ?>
                </form>
            </div>

            <!-- قائمة الأقسام -->
            <div class="content-section">
                <div class="section-header">
                    <h3><i class="fas fa-list"></i> قائمة الأقسام</h3>
                </div>
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>العنوان</th>
                            <th>النوع</th>
                            <th>الترتيب</th>
                            <th>الحالة</th>
                            <th>إجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($sections as $section): ?>
                        <tr>
                            <td><?php echo $section['id']; ?></td>
                            <td><?php echo clean($section['title']); ?></td>
                            <td><?php echo $section['section_type']; ?></td>
                            <td><?php echo $section['display_order']; ?></td>
                            <td>
                                <span class="status <?php echo $section['is_active'] ? 'active' : 'inactive'; ?>">
                                    <?php echo $section['is_active'] ? 'نشط' : 'معطل'; ?>
                                </span>
                            </td>
                            <td>
                                <div class="action-btns">
                                    <a href="?edit=<?php echo $section['id']; ?>" class="btn-icon btn-edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="?delete=<?php echo $section['id']; ?>" class="btn-icon btn-delete" onclick="return confirm('هل أنت متأكد من الحذف؟')">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($sections)): ?>
                        <tr>
                            <td colspan="6" style="text-align: center; color: var(--gray); padding: 2rem;">لا توجد أقسام</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>
</body>
</html>
