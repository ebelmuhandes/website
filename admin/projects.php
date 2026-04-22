<?php
// admin/projects.php - إدارة المشاريع
require_once '../config.php';

if (!isLoggedIn()) {
    redirect('login.php');
}

$message = '';
$error = '';

// إضافة/تعديل مشروع
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? '';
    $title = clean($_POST['title'] ?? '');
    $description = clean($_POST['description'] ?? '');
    $link = clean($_POST['link'] ?? '');
    $category = clean($_POST['category'] ?? '');
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    
    $image_path = '';
    
    // رفع الصورة
    if (!empty($_FILES['image']['name'])) {
        $upload_dir = '../uploads/projects/';
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        
        $file_name = time() . '_' . basename($_FILES['image']['name']);
        $target_file = $upload_dir . $file_name;
        
        if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
            $image_path = 'uploads/projects/' . $file_name;
        }
    }
    
    if (empty($title)) {
        $error = 'عنوان المشروع مطلوب';
    } else {
        try {
            if ($id) {
                if ($image_path) {
                    $stmt = $pdo->prepare("UPDATE projects SET title = ?, description = ?, image_path = ?, link = ?, category = ?, is_active = ? WHERE id = ?");
                    $stmt->execute([$title, $description, $image_path, $link, $category, $is_active, $id]);
                } else {
                    $stmt = $pdo->prepare("UPDATE projects SET title = ?, description = ?, link = ?, category = ?, is_active = ? WHERE id = ?");
                    $stmt->execute([$title, $description, $link, $category, $is_active, $id]);
                }
                $message = 'تم تحديث المشروع بنجاح';
            } else {
                $stmt = $pdo->prepare("INSERT INTO projects (title, description, image_path, link, category, is_active) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->execute([$title, $description, $image_path, $link, $category, $is_active]);
                $message = 'تم إضافة المشروع بنجاح';
            }
        } catch (PDOException $e) {
            $error = 'خطأ في قاعدة البيانات';
        }
    }
}

// حذف مشروع
if (isset($_GET['delete'])) {
    try {
        $stmt = $pdo->prepare("DELETE FROM projects WHERE id = ?");
        $stmt->execute([$_GET['delete']]);
        $message = 'تم حذف المشروع بنجاح';
    } catch (PDOException $e) {
        $error = 'خطأ في الحذف';
    }
}

$projects = $pdo->query("SELECT * FROM projects ORDER BY created_at DESC")->fetchAll();
$edit_project = null;

if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM projects WHERE id = ?");
    $stmt->execute([$_GET['edit']]);
    $edit_project = $stmt->fetch();
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إدارة المشاريع - لوحة التحكم</title>
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
            min-height: 100px;
            resize: vertical;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }

        .file-input-wrapper {
            position: relative;
            overflow: hidden;
            display: inline-block;
            width: 100%;
        }

        .file-input-wrapper input[type=file] {
            position: absolute;
            left: -9999px;
        }

        .file-input-label {
            display: block;
            padding: 0.8rem 1rem;
            background: rgba(255, 255, 255, 0.05);
            border: 2px dashed var(--glass-border);
            border-radius: 10px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
        }

        .file-input-label:hover {
            border-color: var(--primary);
            background: rgba(99, 102, 241, 0.05);
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

        .project-thumb {
            width: 80px;
            height: 60px;
            object-fit: cover;
            border-radius: 8px;
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

        .checkbox-group {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .checkbox-group input[type="checkbox"] {
            width: auto;
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
                <li><a href="projects.php" class="active"><i class="fas fa-project-diagram"></i> المشاريع</a></li>
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
                    <h1>إدارة المشاريع</h1>
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
                    <h3><i class="fas fa-<?php echo $edit_project ? 'edit' : 'plus'; ?>"></i> 
                        <?php echo $edit_project ? 'تعديل المشروع' : 'إضافة مشروع جديد'; ?>
                    </h3>
                </div>
                <form method="POST" action="" enctype="multipart/form-data">
                    <input type="hidden" name="id" value="<?php echo $edit_project['id'] ?? ''; ?>">
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="title">عنوان المشروع *</label>
                            <input type="text" id="title" name="title" required 
                                   value="<?php echo $edit_project['title'] ?? ''; ?>">
                        </div>
                        <div class="form-group">
                            <label for="category">التصنيف</label>
                            <input type="text" id="category" name="category" 
                                   value="<?php echo $edit_project['category'] ?? ''; ?>"
                                   placeholder="مثال: PHP, React, تصميم">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="description">وصف المشروع</label>
                        <textarea id="description" name="description" placeholder="وصف مختصر للمشروع..."><?php echo $edit_project['description'] ?? ''; ?></textarea>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="link">رابط المشروع</label>
                            <input type="url" id="link" name="link" 
                                   value="<?php echo $edit_project['link'] ?? ''; ?>"
                                   placeholder="https://example.com">
                        </div>
                        <div class="form-group">
                            <label for="image">صورة المشروع</label>
                            <div class="file-input-wrapper">
                                <input type="file" id="image" name="image" accept="image/*">
                                <label for="image" class="file-input-label">
                                    <i class="fas fa-cloud-upload-alt"></i> اختر صورة
                                </label>
                            </div>
                            <?php if ($edit_project && $edit_project['image_path']): ?>
                            <small style="color: var(--gray); margin-top: 0.5rem; display: block;">
                                الصورة الحالية: <?php echo $edit_project['image_path']; ?>
                            </small>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="checkbox-group">
                            <input type="checkbox" name="is_active" <?php echo ($edit_project['is_active'] ?? 1) ? 'checked' : ''; ?>>
                            <span>نشط</span>
                        </label>
                    </div>

                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> <?php echo $edit_project ? 'تحديث' : 'حفظ'; ?>
                    </button>
                    <?php if ($edit_project): ?>
                    <a href="projects.php" class="btn btn-secondary">إلغاء</a>
                    <?php endif; ?>
                </form>
            </div>

            <!-- قائمة المشاريع -->
            <div class="content-section">
                <div class="section-header">
                    <h3><i class="fas fa-list"></i> قائمة المشاريع</h3>
                </div>
                <table>
                    <thead>
                        <tr>
                            <th>الصورة</th>
                            <th>العنوان</th>
                            <th>التصنيف</th>
                            <th>الحالة</th>
                            <th>التاريخ</th>
                            <th>إجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($projects as $project): ?>
                        <tr>
                            <td>
                                <?php if ($project['image_path']): ?>
                                <img src="../<?php echo $project['image_path']; ?>" alt="" class="project-thumb">
                                <?php else: ?>
                                <div style="width: 80px; height: 60px; background: var(--glass); border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                                    <i class="fas fa-image" style="color: var(--gray);"></i>
                                </div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <strong><?php echo clean($project['title']); ?></strong>
                                <?php if ($project['link']): ?>
                                <br><a href="<?php echo $project['link']; ?>" target="_blank" style="color: var(--primary); font-size: 0.8rem;">
                                    <i class="fas fa-external-link-alt"></i> زيارة
                                </a>
                                <?php endif; ?>
                            </td>
                            <td><?php echo clean($project['category']); ?></td>
                            <td>
                                <span class="status <?php echo $project['is_active'] ? 'active' : 'inactive'; ?>">
                                    <?php echo $project['is_active'] ? 'نشط' : 'معطل'; ?>
                                </span>
                            </td>
                            <td><?php echo date('Y/m/d', strtotime($project['created_at'])); ?></td>
                            <td>
                                <div class="action-btns">
                                    <a href="?edit=<?php echo $project['id']; ?>" class="btn-icon btn-edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="?delete=<?php echo $project['id']; ?>" class="btn-icon btn-delete" onclick="return confirm('هل أنت متأكد من الحذف؟')">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($projects)): ?>
                        <tr>
                            <td colspan="6" style="text-align: center; color: var(--gray); padding: 2rem;">لا توجد مشاريع</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>

    <script>
        // عرض اسم الملف المختار
        document.getElementById('image').addEventListener('change', function() {
            const label = this.nextElementSibling;
            if (this.files && this.files.length > 0) {
                label.innerHTML = '<i class="fas fa-check"></i> ' + this.files[0].name;
                label.style.borderColor = 'var(--success)';
            }
        });
    </script>
</body>
</html>
