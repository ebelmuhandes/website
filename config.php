<?php
// config.php - إعدادات قاعدة البيانات والموقع
session_start();

define('SITE_NAME', 'موقعي الشخصي');
define('BASE_URL', 'http://localhost/personal-site/');
define('ADMIN_EMAIL', 'admin@example.com');

// بيانات قاعدة البيانات (SQLite للتبسيط)
define('DB_PATH', __DIR__ . '/data/database.sqlite');

// إنشاء مجلد البيانات إذا لم يكن موجوداً
if (!file_exists(__DIR__ . '/data')) {
    mkdir(__DIR__ . '/data', 0755, true);
}

// الاتصال بقاعدة البيانات
try {
    $pdo = new PDO('sqlite:' . DB_PATH);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    
    // إنشاء الجداول إذا لم تكن موجودة
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            username TEXT UNIQUE NOT NULL,
            password TEXT NOT NULL,
            email TEXT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        );
        
        CREATE TABLE IF NOT EXISTS sections (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            title TEXT NOT NULL,
            content TEXT,
            section_type TEXT NOT NULL,
            display_order INTEGER DEFAULT 0,
            is_active INTEGER DEFAULT 1,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
        );
        
        CREATE TABLE IF NOT EXISTS projects (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            title TEXT NOT NULL,
            description TEXT,
            image_path TEXT,
            link TEXT,
            category TEXT,
            is_active INTEGER DEFAULT 1,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        );
        
        CREATE TABLE IF NOT EXISTS messages (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            email TEXT NOT NULL,
            subject TEXT,
            message TEXT NOT NULL,
            is_read INTEGER DEFAULT 0,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        );
        
        CREATE TABLE IF NOT EXISTS site_settings (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            setting_key TEXT UNIQUE NOT NULL,
            setting_value TEXT
        );
    ");
    
    // إنشاء مستخدم افتراضي (admin/admin123)
    $stmt = $pdo->prepare("INSERT OR IGNORE INTO users (username, password, email) VALUES (?, ?, ?)");
    $stmt->execute(['admin', password_hash('admin123', PASSWORD_DEFAULT), 'admin@example.com']);
    
    // إضافة بيانات افتراضية
    $default_sections = [
        ['عنّي', 'أنا مطور ويب شغوف بإنشاء تجارب رقمية مذهلة. أمتلك خبرة واسعة في تطوير المواقع والتطبيقات.', 'about', 1],
        ['خدماتي', 'تطوير المواقع، تصميم واجهات المستخدم، تحسين الأداء، استشارات تقنية', 'services', 2],
        ['مهاراتي', '', 'skills', 3],
    ];
    
    $stmt = $pdo->prepare("INSERT OR IGNORE INTO sections (title, content, section_type, display_order) VALUES (?, ?, ?, ?)");
    foreach ($default_sections as $section) {
        $stmt->execute($section);
    }
    
} catch (PDOException $e) {
    die("خطأ في الاتصال بقاعدة البيانات: " . $e->getMessage());
}

// دالة مساعدة للتحقق من تسجيل الدخول
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// دالة مساعدة للتحويل الآمن
function redirect($url) {
    header("Location: " . $url);
    exit();
}

// دالة تنظيف المدخلات
function clean($data) {
    return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
}
?>
