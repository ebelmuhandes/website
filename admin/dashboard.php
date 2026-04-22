<?php
// admin/dashboard.php - لوحة التحكم الرئيسية
require_once '../config.php';

if (!isLoggedIn()) {
    redirect('login.php');
}

// إحصائيات
$stats = [
    'projects' => $pdo->query("SELECT COUNT(*) FROM projects")->fetchColumn(),
    'messages' => $pdo->query("SELECT COUNT(*) FROM messages")->fetchColumn(),
    'unread_messages' => $pdo->query("SELECT COUNT(*) FROM messages WHERE is_read = 0")->fetchColumn(),
    'sections' => $pdo->query("SELECT COUNT(*) FROM sections")->fetchColumn()
];

// آخر الرسائل
$recent_messages = $pdo->query("SELECT * FROM messages ORDER BY created_at DESC LIMIT 5")->fetchAll();

// المشاريع
$projects_list = $pdo->query("SELECT * FROM projects ORDER BY created_at DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>لوحة التحكم</title>
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

        /* التخطيط */
        .admin-layout {
            display: grid;
            grid-template-columns: 280px 1fr;
            min-height: 100vh;
        }

        /* الشريط الجانبي */
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

        .sidebar-header p {
            color: var(--gray);
            font-size: 0.9rem;
            margin-top: 0.5rem;
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

        .user-details h4 {
            font-size: 0.95rem;
        }

        .user-details p {
            color: var(--gray);
            font-size: 0.8rem;
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

        .btn-logout:hover {
            background: rgba(239, 68, 68, 0.2);
        }

        /* المحتوى الرئيسي */
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

        .page-title p {
            color: var(--gray);
            font-size: 0.9rem;
        }

        .top-actions {
            display: flex;
            gap: 1rem;
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
            transform: translateY(-2px);
        }

        /* البطاقات الإحصائية */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: var(--glass);
            backdrop-filter: blur(20px);
            border: 1px solid var(--glass-border);
            border-radius: 20px;
            padding: 1.5rem;
            transition: all 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            border-color: var(--primary);
        }

        .stat-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 1rem;
        }

        .stat-icon {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.3rem;
        }

        .stat-icon.blue { background: rgba(99, 102, 241, 0.1); color: var(--primary); }
        .stat-icon.pink { background: rgba(236, 72, 153, 0.1); color: var(--secondary); }
        .stat-icon.cyan { background: rgba(6, 182, 212, 0.1); color: var(--accent); }
        .stat-icon.green { background: rgba(16, 185, 129, 0.1); color: var(--success); }

        .stat-badge {
            padding: 0.3rem 0.8rem;
            border-radius: 50px;
            font-size: 0.75rem;
            font-weight: 700;
        }

        .stat-badge.up {
            background: rgba(16, 185, 129, 0.1);
            color: var(--success);
        }

        .stat-value {
            font-size: 2rem;
            font-weight: 900;
            margin-bottom: 0.3rem;
        }

        .stat-label {
            color: var(--gray);
            font-size: 0.9rem;
        }

        /* الجداول */
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
            text-transform: uppercase;
        }

        td {
            font-size: 0.95rem;
        }

        tr:hover td {
            background: rgba(255, 255, 255, 0.02);
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

        .status.unread {
            background: rgba(245, 158, 11, 0.1);
            color: var(--warning);
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
            color: var(--light);
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

        /* النماذج */
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

        /* التصميم المتجاوب */
        @media (max-width: 968px) {
            .admin-layout {
                grid-template-columns: 1fr;
            }

            .sidebar {
                transform: translateX(100%);
                transition: transform 0.3s ease;
            }

            .sidebar.active {
                transform: translateX(0);
            }

            .main-content {
                margin-right: 0;
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }
        }

        /* التبويبات */
        .tabs {
            display: flex;
            gap: 0.5rem;
            margin-bottom: 1.5rem;
            border-bottom: 1px solid var(--glass-border);
            padding-bottom: 1rem;
        }

        .tab {
            padding: 0.8rem 1.5rem;
            background: none;
            border: none;
            color: var(--gray);
            font-family: inherit;
            font-size: 1rem;
            cursor: pointer;
            border-radius: 10px;
            transition: all 0.3s;
        }

        .tab:hover {
            color: var(--light);
            background: var(--glass);
        }

        .tab.active {
            color: var(--light);
            background: var(--primary);
        }

        .tab-content {
            display: none;
        }

        .tab-content.active {
            display: block;
        }
    </style>
</head>
<body>
    <div class="admin-layout">
        <!-- الشريط الجانبي -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <div class="logo"><i class="fas fa-cube"></i> لوحة التحكم</div>
                <p>إدارة الموقع الشخصي</p>
            </div>

            <ul class="nav-menu">
                <li><a href="dashboard.php" class="active"><i class="fas fa-home"></i> الرئيسية</a></li>
                <li><a href="sections.php"><i class="fas fa-layer-group"></i> الأقسام</a></li>
                <li><a href="projects.php"><i class="fas fa-project-diagram"></i> المشاريع</a></li>
                <li><a href="messages.php"><i class="fas fa-envelope"></i> الرسائل 
                    <?php if ($stats['unread_messages'] > 0): ?>
                    <span style="background: var(--secondary); color: white; padding: 0.2rem 0.6rem; border-radius: 50px; font-size: 0.75rem; margin-right: auto;"><?php echo $stats['unread_messages']; ?></span>
                    <?php endif; ?>
                </a></li>
                <li><a href="settings.php"><i class="fas fa-cog"></i> الإعدادات</a></li>
            </ul>

            <div class="sidebar-footer">
                <div class="user-info">
                    <div class="user-avatar">
                        <i class="fas fa-user"></i>
                    </div>
                    <div class="user-details">
                        <h4><?php echo $_SESSION['username']; ?></h4>
                        <p>مدير الموقع</p>
                    </div>
                </div>
                <a href="logout.php" class="btn-logout">
                    <i class="fas fa-sign-out-alt"></i> تسجيل الخروج
                </a>
            </div>
        </aside>

        <!-- المحتوى الرئيسي -->
        <main class="main-content">
            <div class="top-bar">
                <div class="page-title">
                    <h1>لوحة التحكم</h1>
                    <p>نظرة عامة على موقعك</p>
                </div>
                <div class="top-actions">
                    <a href="../index.php" target="_blank" class="btn btn-primary">
                        <i class="fas fa-external-link-alt"></i> عرض الموقع
                    </a>
                </div>
            </div>

            <!-- الإحصائيات -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-header">
                        <div class="stat-icon blue"><i class="fas fa-project-diagram"></i></div>
                        <span class="stat-badge up">+12%</span>
                    </div>
                    <div class="stat-value"><?php echo $stats['projects']; ?></div>
                    <div class="stat-label">إجمالي المشاريع</div>
                </div>
                <div class="stat-card">
                    <div class="stat-header">
                        <div class="stat-icon pink"><i class="fas fa-envelope"></i></div>
                        <span class="stat-badge up">+5%</span>
                    </div>
                    <div class="stat-value"><?php echo $stats['messages']; ?></div>
                    <div class="stat-label">إجمالي الرسائل</div>
                </div>
                <div class="stat-card">
                    <div class="stat-header">
                        <div class="stat-icon cyan"><i class="fas fa-eye"></i></div>
                        <span class="stat-badge up">+25%</span>
                    </div>
                    <div class="stat-value">1,234</div>
                    <div class="stat-label">زيارات هذا الشهر</div>
                </div>
                <div class="stat-card">
                    <div class="stat-header">
                        <div class="stat-icon green"><i class="fas fa-layer-group"></i></div>
                    </div>
                    <div class="stat-value"><?php echo $stats['sections']; ?></div>
                    <div class="stat-label">الأقسام النشطة</div>
                </div>
            </div>

            <!-- آخر الرسائل -->
            <div class="content-section">
                <div class="section-header">
                    <h3><i class="fas fa-envelope"></i> آخر الرسائل</h3>
                    <a href="messages.php" class="btn btn-primary" style="padding: 0.5rem 1rem; font-size: 0.9rem;">
                        عرض الكل
                    </a>
                </div>
                <table>
                    <thead>
                        <tr>
                            <th>المرسل</th>
                            <th>الموضوع</th>
                            <th>التاريخ</th>
                            <th>الحالة</th>
                            <th>إجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recent_messages as $msg): ?>
                        <tr>
                            <td>
                                <strong><?php echo clean($msg['name']); ?></strong><br>
                                <small style="color: var(--gray);"><?php echo clean($msg['email']); ?></small>
                            </td>
                            <td><?php echo clean($msg['subject'] ?: 'بدون موضوع'); ?></td>
                            <td><?php echo date('Y/m/d', strtotime($msg['created_at'])); ?></td>
                            <td>
                                <span class="status <?php echo $msg['is_read'] ? 'active' : 'unread'; ?>">
                                    <?php echo $msg['is_read'] ? 'مقروءة' : 'جديدة'; ?>
                                </span>
                            </td>
                            <td>
                                <div class="action-btns">
                                    <a href="messages.php?action=view&id=<?php echo $msg['id']; ?>" class="btn-icon btn-edit">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="messages.php?action=delete&id=<?php echo $msg['id']; ?>" class="btn-icon btn-delete" onclick="return confirm('هل أنت متأكد؟')">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($recent_messages)): ?>
                        <tr>
                            <td colspan="5" style="text-align: center; color: var(--gray); padding: 2rem;">
                                لا توجد رسائل حالياً
                            </td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>

    <script>
        // تفعيل التبويبات
        document.querySelectorAll('.tab').forEach(tab => {
            tab.addEventListener('click', () => {
                document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
                document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
                
                tab.classList.add('active');
                document.getElementById(tab.dataset.tab).classList.add('active');
            });
        });
    </script>
</body>
</html>
