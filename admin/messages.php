<?php
// admin/messages.php - إدارة الرسائل
require_once '../config.php';

if (!isLoggedIn()) {
    redirect('login.php');
}

// تحديث حالة القراءة
if (isset($_GET['read'])) {
    $stmt = $pdo->prepare("UPDATE messages SET is_read = 1 WHERE id = ?");
    $stmt->execute([$_GET['read']]);
}

// حذف رسالة
if (isset($_GET['delete'])) {
    $stmt = $pdo->prepare("DELETE FROM messages WHERE id = ?");
    $stmt->execute([$_GET['delete']]);
}

// عرض رسالة واحدة
$view_message = null;
if (isset($_GET['view'])) {
    $stmt = $pdo->prepare("SELECT * FROM messages WHERE id = ?");
    $stmt->execute([$_GET['view']]);
    $view_message = $stmt->fetch();
    
    // تحديث حالة القراءة
    if ($view_message && !$view_message['is_read']) {
        $stmt = $pdo->prepare("UPDATE messages SET is_read = 1 WHERE id = ?");
        $stmt->execute([$_GET['view']]);
    }
}

$messages = $pdo->query("SELECT * FROM messages ORDER BY created_at DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>الرسائل - لوحة التحكم</title>
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

        tr.unread {
            background: rgba(99, 102, 241, 0.05);
        }

        tr.unread td {
            font-weight: 700;
        }

        .status {
            padding: 0.3rem 0.8rem;
            border-radius: 50px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .status.unread {
            background: rgba(245, 158, 11, 0.1);
            color: var(--warning);
        }

        .status.read {
            background: rgba(16, 185, 129, 0.1);
            color: var(--success);
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

        .btn-view {
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

        /* عرض الرسالة */
        .message-view {
            background: var(--dark);
            border-radius: 15px;
            padding: 2rem;
            border: 1px solid var(--glass-border);
        }

        .message-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 1.5rem;
            padding-bottom: 1.5rem;
            border-bottom: 1px solid var(--glass-border);
        }

        .message-sender h4 {
            font-size: 1.2rem;
            margin-bottom: 0.3rem;
        }

        .message-sender p {
            color: var(--gray);
            font-size: 0.9rem;
        }

        .message-date {
            color: var(--gray);
            font-size: 0.85rem;
        }

        .message-subject {
            font-size: 1.1rem;
            color: var(--primary);
            margin-bottom: 1rem;
        }

        .message-body {
            line-height: 1.8;
            color: var(--gray);
            white-space: pre-wrap;
        }

        .message-actions {
            margin-top: 2rem;
            display: flex;
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
                <li><a href="messages.php" class="active"><i class="fas fa-envelope"></i> الرسائل</a></li>
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
                    <h1>الرسائل الواردة</h1>
                </div>
            </div>

            <?php if ($view_message): ?>
            <!-- عرض رسالة واحدة -->
            <div class="content-section">
                <div class="section-header">
                    <h3><i class="fas fa-envelope-open"></i> عرض الرسالة</h3>
                    <a href="messages.php" class="btn btn-primary" style="padding: 0.5rem 1rem; font-size: 0.9rem;">
                        <i class="fas fa-arrow-right"></i> العودة للقائمة
                    </a>
                </div>
                
                <div class="message-view">
                    <div class="message-header">
                        <div class="message-sender">
                            <h4><?php echo clean($view_message['name']); ?></h4>
                            <p><i class="fas fa-envelope"></i> <?php echo clean($view_message['email']); ?></p>
                        </div>
                        <div class="message-date">
                            <i class="fas fa-clock"></i> 
                            <?php echo date('Y/m/d H:i', strtotime($view_message['created_at'])); ?>
                        </div>
                    </div>
                    
                    <div class="message-subject">
                        <i class="fas fa-tag"></i> 
                        <?php echo clean($view_message['subject'] ?: 'بدون موضوع'); ?>
                    </div>
                    
                    <div class="message-body">
                        <?php echo nl2br(clean($view_message['message'])); ?>
                    </div>
                    
                    <div class="message-actions">
                        <a href="mailto:<?php echo $view_message['email']; ?>" class="btn btn-primary">
                            <i class="fas fa-reply"></i> رد عبر البريد
                        </a>
                        <a href="?delete=<?php echo $view_message['id']; ?>" class="btn" style="background: rgba(239, 68, 68, 0.1); color: var(--danger);" onclick="return confirm('هل أنت متأكد؟')">
                            <i class="fas fa-trash"></i> حذف
                        </a>
                    </div>
                </div>
            </div>
            <?php else: ?>
            <!-- قائمة الرسائل -->
            <div class="content-section">
                <div class="section-header">
                    <h3><i class="fas fa-inbox"></i> قائمة الرسائل</h3>
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
                        <?php foreach ($messages as $msg): ?>
                        <tr class="<?php echo $msg['is_read'] ? '' : 'unread'; ?>">
                            <td>
                                <strong><?php echo clean($msg['name']); ?></strong><br>
                                <small style="color: var(--gray);"><?php echo clean($msg['email']); ?></small>
                            </td>
                            <td><?php echo clean($msg['subject'] ?: 'بدون موضوع'); ?></td>
                            <td><?php echo date('Y/m/d H:i', strtotime($msg['created_at'])); ?></td>
                            <td>
                                <span class="status <?php echo $msg['is_read'] ? 'read' : 'unread'; ?>">
                                    <?php echo $msg['is_read'] ? 'مقروءة' : 'جديدة'; ?>
                                </span>
                            </td>
                            <td>
                                <div class="action-btns">
                                    <a href="?view=<?php echo $msg['id']; ?>" class="btn-icon btn-view">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="?delete=<?php echo $msg['id']; ?>" class="btn-icon btn-delete" onclick="return confirm('هل أنت متأكد؟')">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($messages)): ?>
                        <tr>
                            <td colspan="5" style="text-align: center; color: var(--gray); padding: 2rem;">
                                <i class="fas fa-inbox" style="font-size: 3rem; margin-bottom: 1rem; display: block;"></i>
                                لا توجد رسائل حالياً
                            </td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </main>
    </div>
</body>
</html>
