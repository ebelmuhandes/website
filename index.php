<?php
// index.php - الصفحة الرئيسية
require_once 'config.php';

// جلب البيانات من قاعدة البيانات
$stmt = $pdo->query("SELECT * FROM sections WHERE is_active = 1 ORDER BY display_order");
$sections = $stmt->fetchAll();

$stmt = $pdo->query("SELECT * FROM projects WHERE is_active = 1 ORDER BY created_at DESC LIMIT 6");
$projects = $stmt->fetchAll();

// جلب الإعدادات
$stmt = $pdo->query("SELECT * FROM site_settings");
$settings = [];
while ($row = $stmt->fetch()) {
    $settings[$row['setting_key']] = $row['setting_value'];
}

$site_title = $settings['site_title'] ?? 'موقعي الشخصي';
$site_description = $settings['site_description'] ?? 'مرحباً بك في موقعي الشخصي';
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo clean($site_title); ?></title>
    <meta name="description" content="<?php echo clean($site_description); ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #6366f1;
            --primary-dark: #4f46e5;
            --secondary: #ec4899;
            --accent: #06b6d4;
            --dark: #0f172a;
            --darker: #020617;
            --light: #f8fafc;
            --gray: #94a3b8;
            --gradient-1: linear-gradient(135deg, #6366f1 0%, #ec4899 100%);
            --gradient-2: linear-gradient(135deg, #06b6d4 0%, #6366f1 100%);
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
            overflow-x: hidden;
            line-height: 1.6;
        }

        /* خلفية متحركة */
        .bg-animation {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
            overflow: hidden;
        }

        .bg-animation .orb {
            position: absolute;
            border-radius: 50%;
            filter: blur(80px);
            opacity: 0.4;
            animation: float 20s infinite ease-in-out;
        }

        .orb-1 {
            width: 400px;
            height: 400px;
            background: var(--primary);
            top: -100px;
            right: -100px;
            animation-delay: 0s;
        }

        .orb-2 {
            width: 300px;
            height: 300px;
            background: var(--secondary);
            bottom: -50px;
            left: -50px;
            animation-delay: -5s;
        }

        .orb-3 {
            width: 250px;
            height: 250px;
            background: var(--accent);
            top: 50%;
            left: 50%;
            animation-delay: -10s;
        }

        @keyframes float {
            0%, 100% { transform: translate(0, 0) scale(1); }
            33% { transform: translate(30px, -30px) scale(1.1); }
            66% { transform: translate(-20px, 20px) scale(0.9); }
        }

        /* شريط التنقل */
        .navbar {
            position: fixed;
            top: 0;
            width: 100%;
            padding: 1rem 5%;
            display: flex;
            justify-content: space-between;
            align-items: center;
            z-index: 1000;
            transition: all 0.3s ease;
            background: transparent;
        }

        .navbar.scrolled {
            background: rgba(15, 23, 42, 0.9);
            backdrop-filter: blur(20px);
            box-shadow: 0 4px 30px rgba(0, 0, 0, 0.1);
        }

        .logo {
            font-size: 1.8rem;
            font-weight: 900;
            background: var(--gradient-1);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            text-decoration: none;
        }

        .nav-links {
            display: flex;
            gap: 2rem;
            list-style: none;
        }

        .nav-links a {
            color: var(--light);
            text-decoration: none;
            font-weight: 500;
            position: relative;
            padding: 0.5rem 0;
            transition: color 0.3s;
        }

        .nav-links a::after {
            content: '';
            position: absolute;
            bottom: 0;
            right: 0;
            width: 0;
            height: 2px;
            background: var(--gradient-1);
            transition: width 0.3s ease;
        }

        .nav-links a:hover::after {
            width: 100%;
        }

        .nav-links a:hover {
            color: var(--primary);
        }

        .mobile-menu-btn {
            display: none;
            background: none;
            border: none;
            color: var(--light);
            font-size: 1.5rem;
            cursor: pointer;
        }

        /* القسم الرئيسي */
        .hero {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 6rem 5% 2rem;
            position: relative;
        }

        .hero-content {
            text-align: center;
            max-width: 800px;
        }

        .hero-badge {
            display: inline-block;
            padding: 0.5rem 1.5rem;
            background: var(--glass);
            border: 1px solid var(--glass-border);
            border-radius: 50px;
            font-size: 0.9rem;
            margin-bottom: 2rem;
            backdrop-filter: blur(10px);
            animation: fadeInUp 0.8s ease;
        }

        .hero h1 {
            font-size: clamp(2.5rem, 5vw, 4.5rem);
            font-weight: 900;
            line-height: 1.2;
            margin-bottom: 1.5rem;
            animation: fadeInUp 0.8s ease 0.2s both;
        }

        .hero h1 span {
            background: var(--gradient-1);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .hero p {
            font-size: 1.2rem;
            color: var(--gray);
            margin-bottom: 2.5rem;
            animation: fadeInUp 0.8s ease 0.4s both;
        }

        .hero-buttons {
            display: flex;
            gap: 1rem;
            justify-content: center;
            flex-wrap: wrap;
            animation: fadeInUp 0.8s ease 0.6s both;
        }

        .btn {
            padding: 1rem 2.5rem;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 700;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
            font-family: inherit;
            font-size: 1rem;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn-primary {
            background: var(--gradient-1);
            color: white;
            box-shadow: 0 10px 30px rgba(99, 102, 241, 0.3);
        }

        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 40px rgba(99, 102, 241, 0.4);
        }

        .btn-secondary {
            background: var(--glass);
            color: var(--light);
            border: 1px solid var(--glass-border);
            backdrop-filter: blur(10px);
        }

        .btn-secondary:hover {
            background: rgba(255, 255, 255, 0.1);
            transform: translateY(-3px);
        }

        /* تأثير الكتابة */
        .typing-text::after {
            content: '|';
            animation: blink 1s infinite;
        }

        @keyframes blink {
            0%, 100% { opacity: 1; }
            50% { opacity: 0; }
        }

        /* الأقسام العامة */
        section {
            padding: 6rem 5%;
            position: relative;
        }

        .section-title {
            text-align: center;
            margin-bottom: 4rem;
        }

        .section-title h2 {
            font-size: clamp(2rem, 4vw, 3rem);
            font-weight: 900;
            margin-bottom: 1rem;
        }

        .section-title h2 span {
            background: var(--gradient-2);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .section-title p {
            color: var(--gray);
            font-size: 1.1rem;
            max-width: 600px;
            margin: 0 auto;
        }

        /* بطاقات زجاجية */
        .glass-card {
            background: var(--glass);
            backdrop-filter: blur(20px);
            border: 1px solid var(--glass-border);
            border-radius: 20px;
            padding: 2.5rem;
            transition: all 0.3s ease;
        }

        .glass-card:hover {
            transform: translateY(-10px);
            border-color: rgba(99, 102, 241, 0.3);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.2);
        }

        /* قسم عنّي */
        .about-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 4rem;
            align-items: center;
            max-width: 1200px;
            margin: 0 auto;
        }

        .about-image {
            position: relative;
        }

        .about-image .image-wrapper {
            position: relative;
            border-radius: 30px;
            overflow: hidden;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.3);
        }

        .about-image img {
            width: 100%;
            height: auto;
            display: block;
        }

        .about-image .experience-badge {
            position: absolute;
            bottom: -20px;
            left: -20px;
            background: var(--gradient-1);
            padding: 1.5rem 2rem;
            border-radius: 20px;
            font-weight: 900;
            font-size: 1.2rem;
            box-shadow: 0 10px 30px rgba(99, 102, 241, 0.3);
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }

        .about-content h3 {
            font-size: 2rem;
            margin-bottom: 1.5rem;
        }

        .about-content p {
            color: var(--gray);
            margin-bottom: 1.5rem;
            font-size: 1.1rem;
        }

        .stats {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1.5rem;
            margin-top: 2rem;
        }

        .stat-item {
            text-align: center;
            padding: 1.5rem;
            background: var(--glass);
            border-radius: 15px;
            border: 1px solid var(--glass-border);
        }

        .stat-item h4 {
            font-size: 2rem;
            font-weight: 900;
            background: var(--gradient-1);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .stat-item p {
            color: var(--gray);
            font-size: 0.9rem;
            margin: 0;
        }

        /* قسم المهارات */
        .skills-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
            max-width: 1200px;
            margin: 0 auto;
        }

        .skill-card {
            background: var(--glass);
            backdrop-filter: blur(20px);
            border: 1px solid var(--glass-border);
            border-radius: 20px;
            padding: 2rem;
            transition: all 0.3s ease;
        }

        .skill-card:hover {
            transform: translateY(-5px);
            border-color: var(--primary);
        }

        .skill-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }

        .skill-header h4 {
            font-size: 1.2rem;
        }

        .skill-percent {
            font-weight: 900;
            color: var(--primary);
        }

        .skill-bar {
            height: 8px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 10px;
            overflow: hidden;
        }

        .skill-progress {
            height: 100%;
            background: var(--gradient-1);
            border-radius: 10px;
            transition: width 1.5s ease;
            position: relative;
        }

        .skill-progress::after {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 20px;
            height: 100%;
            background: rgba(255, 255, 255, 0.3);
            border-radius: 10px;
            animation: shimmer 2s infinite;
        }

        @keyframes shimmer {
            0% { transform: translateX(-20px); opacity: 0; }
            50% { opacity: 1; }
            100% { transform: translateX(20px); opacity: 0; }
        }

        /* قسم المشاريع */
        .projects-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 2rem;
            max-width: 1200px;
            margin: 0 auto;
        }

        .project-card {
            position: relative;
            border-radius: 20px;
            overflow: hidden;
            background: var(--glass);
            border: 1px solid var(--glass-border);
            transition: all 0.3s ease;
        }

        .project-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 30px 60px rgba(0, 0, 0, 0.3);
        }

        .project-image {
            position: relative;
            overflow: hidden;
            height: 250px;
        }

        .project-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s ease;
        }

        .project-card:hover .project-image img {
            transform: scale(1.1);
        }

        .project-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(to top, rgba(15, 23, 42, 0.95), transparent);
            display: flex;
            align-items: flex-end;
            padding: 2rem;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .project-card:hover .project-overlay {
            opacity: 1;
        }

        .project-info h4 {
            font-size: 1.3rem;
            margin-bottom: 0.5rem;
        }

        .project-info p {
            color: var(--gray);
            font-size: 0.9rem;
            margin-bottom: 1rem;
        }

        .project-tags {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
        }

        .tag {
            padding: 0.3rem 0.8rem;
            background: var(--gradient-1);
            border-radius: 50px;
            font-size: 0.8rem;
            font-weight: 500;
        }

        /* قسم التواصل */
        .contact-container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 4rem;
            max-width: 1200px;
            margin: 0 auto;
        }

        .contact-info {
            display: flex;
            flex-direction: column;
            gap: 2rem;
        }

        .contact-item {
            display: flex;
            align-items: center;
            gap: 1.5rem;
            padding: 1.5rem;
            background: var(--glass);
            border-radius: 15px;
            border: 1px solid var(--glass-border);
            transition: all 0.3s ease;
        }

        .contact-item:hover {
            transform: translateX(-10px);
            border-color: var(--primary);
        }

        .contact-icon {
            width: 60px;
            height: 60px;
            background: var(--gradient-1);
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            flex-shrink: 0;
        }

        .contact-details h4 {
            margin-bottom: 0.3rem;
        }

        .contact-details p {
            color: var(--gray);
            font-size: 0.9rem;
        }

        .contact-form {
            background: var(--glass);
            backdrop-filter: blur(20px);
            border: 1px solid var(--glass-border);
            border-radius: 20px;
            padding: 3rem;
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
            padding: 1rem 1.5rem;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid var(--glass-border);
            border-radius: 10px;
            color: var(--light);
            font-family: inherit;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .form-group input:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 20px rgba(99, 102, 241, 0.1);
        }

        .form-group textarea {
            min-height: 150px;
            resize: vertical;
        }

        /* التذييل */
        footer {
            background: var(--dark);
            padding: 3rem 5%;
            text-align: center;
            border-top: 1px solid var(--glass-border);
        }

        .social-links {
            display: flex;
            justify-content: center;
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .social-links a {
            width: 50px;
            height: 50px;
            background: var(--glass);
            border: 1px solid var(--glass-border);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--light);
            font-size: 1.2rem;
            transition: all 0.3s ease;
            text-decoration: none;
        }

        .social-links a:hover {
            background: var(--gradient-1);
            transform: translateY(-5px) rotate(360deg);
            border-color: transparent;
        }

        footer p {
            color: var(--gray);
        }

        /* تأثيرات الظهور */
        .reveal {
            opacity: 0;
            transform: translateY(50px);
            transition: all 0.8s ease;
        }

        .reveal.active {
            opacity: 1;
            transform: translateY(0);
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* الجسيمات */
        .particles {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
            pointer-events: none;
        }

        /* التمرير السلس */
        html {
            scroll-behavior: smooth;
        }

        /* شريط التمرير المخصص */
        ::-webkit-scrollbar {
            width: 10px;
        }

        ::-webkit-scrollbar-track {
            background: var(--darker);
        }

        ::-webkit-scrollbar-thumb {
            background: var(--gradient-1);
            border-radius: 5px;
        }

        /* التصميم المتجاوب */
        @media (max-width: 968px) {
            .about-grid,
            .contact-container {
                grid-template-columns: 1fr;
                gap: 2rem;
            }

            .nav-links {
                position: fixed;
                top: 70px;
                right: -100%;
                width: 100%;
                height: calc(100vh - 70px);
                background: rgba(15, 23, 42, 0.98);
                flex-direction: column;
                align-items: center;
                padding: 2rem;
                transition: right 0.3s ease;
            }

            .nav-links.active {
                right: 0;
            }

            .mobile-menu-btn {
                display: block;
            }

            .stats {
                grid-template-columns: 1fr;
            }

            .projects-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 480px) {
            .hero h1 {
                font-size: 2rem;
            }

            .hero-buttons {
                flex-direction: column;
                width: 100%;
            }

            .btn {
                width: 100%;
                justify-content: center;
            }

            section {
                padding: 4rem 5%;
            }
        }

        /* تأثير الموجة */
        .wave {
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            overflow: hidden;
            line-height: 0;
        }

        .wave svg {
            position: relative;
            display: block;
            width: calc(100% + 1.3px);
            height: 150px;
        }

        .wave .shape-fill {
            fill: var(--dark);
        }

        /* تأثير الإضاءة التفاعلي */
        .glow-cursor {
            position: fixed;
            width: 300px;
            height: 300px;
            background: radial-gradient(circle, rgba(99, 102, 241, 0.15) 0%, transparent 70%);
            border-radius: 50%;
            pointer-events: none;
            z-index: 9999;
            transform: translate(-50%, -50%);
            transition: all 0.1s ease;
        }
    </style>
</head>
<body>
    <!-- خلفية متحركة -->
    <div class="bg-animation">
        <div class="orb orb-1"></div>
        <div class="orb orb-2"></div>
        <div class="orb orb-3"></div>
    </div>

    <!-- جسيمات -->
    <canvas class="particles" id="particles"></canvas>

    <!-- تأثير الإضاءة -->
    <div class="glow-cursor" id="glow"></div>

    <!-- شريط التنقل -->
    <nav class="navbar" id="navbar">
        <a href="#" class="logo">موقعي</a>
        <ul class="nav-links" id="navLinks">
            <li><a href="#home">الرئيسية</a></li>
            <li><a href="#about">عنّي</a></li>
            <li><a href="#skills">المهارات</a></li>
            <li><a href="#projects">المشاريع</a></li>
            <li><a href="#contact">التواصل</a></li>
            <li><a href="admin/login.php"><i class="fas fa-lock"></i> الدخول</a></li>
        </ul>
        <button class="mobile-menu-btn" id="mobileMenuBtn">
            <i class="fas fa-bars"></i>
        </button>
    </nav>

    <!-- القسم الرئيسي -->
    <section class="hero" id="home">
        <div class="hero-content">
            <div class="hero-badge">
                <i class="fas fa-code"></i> مطور ويب Full Stack
            </div>
            <h1>
                أهلاً، أنا <span class="typing-text" id="typing"></span>
            </h1>
            <p>
                أقوم بتصميم وتطوير تجارب رقمية استثنائية تجمع بين الإبداع والأداء العالي.
                دعني أساعدك في تحويل أفكارك إلى واقع رقمي مذهل.
            </p>
            <div class="hero-buttons">
                <a href="#projects" class="btn btn-primary">
                    <i class="fas fa-eye"></i> مشاهدة أعمالي
                </a>
                <a href="#contact" class="btn btn-secondary">
                    <i class="fas fa-paper-plane"></i> تواصل معي
                </a>
            </div>
        </div>
    </section>

    <!-- قسم عنّي -->
    <section id="about" class="reveal">
        <div class="section-title">
            <h2>من <span>أنا؟</span></h2>
            <p>قصة شغفي وتطوري في عالم البرمجة والتصميم</p>
        </div>
        <div class="about-grid">
            <div class="about-image">
                <div class="image-wrapper">
                    <img src="https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=600&h=600&fit=crop" alt="صورة شخصية">
                </div>
                <div class="experience-badge">
                    +5 سنوات<br>خبرة
                </div>
            </div>
            <div class="about-content">
                <h3>مطور ويب شغوف بإنشاء تجارب رقمية مميزة</h3>
                <p>
                    أنا مطور ويب متخصص في بناء تطبيقات الويب الحديثة والمتجاوبة. 
                    أؤمن بأن التصميم الجيد يجمع بين الجمال والوظيفة، وأعمل دائماً 
                    على تقديم حلول تقنية مبتكرة تلبي احتياجات العملاء.
                </p>
                <p>
                    خلال مسيرتي المهنية، عملت مع فرق متنوعة على مشاريع مختلفة 
                    الحجم، من المواقع الشخصية إلى المنصات الكبيرة، مما أكسبني 
                    خبرة واسعة في التعامل مع التحديات التقنية المختلفة.
                </p>
                <div class="stats">
                    <div class="stat-item">
                        <h4>50+</h4>
                        <p>مشروع منجز</p>
                    </div>
                    <div class="stat-item">
                        <h4>30+</h4>
                        <p>عميل سعيد</p>
                    </div>
                    <div class="stat-item">
                        <h4>100%</h4>
                        <p>رضا العملاء</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- قسم المهارات -->
    <section id="skills" style="background: var(--dark);">
        <div class="section-title reveal">
            <h2>مهاراتي <span>التقنية</span></h2>
            <p>أدوات وتقنيات أتقن العمل بها</p>
        </div>
        <div class="skills-grid">
            <div class="skill-card reveal">
                <div class="skill-header">
                    <h4><i class="fab fa-html5" style="color: #e34c26;"></i> HTML/CSS</h4>
                    <span class="skill-percent">95%</span>
                </div>
                <div class="skill-bar">
                    <div class="skill-progress" data-width="95%" style="width: 0%"></div>
                </div>
            </div>
            <div class="skill-card reveal">
                <div class="skill-header">
                    <h4><i class="fab fa-js" style="color: #f7df1e;"></i> JavaScript</h4>
                    <span class="skill-percent">90%</span>
                </div>
                <div class="skill-bar">
                    <div class="skill-progress" data-width="90%" style="width: 0%"></div>
                </div>
            </div>
            <div class="skill-card reveal">
                <div class="skill-header">
                    <h4><i class="fab fa-php" style="color: #777bb4;"></i> PHP</h4>
                    <span class="skill-percent">85%</span>
                </div>
                <div class="skill-bar">
                    <div class="skill-progress" data-width="85%" style="width: 0%"></div>
                </div>
            </div>
            <div class="skill-card reveal">
                <div class="skill-header">
                    <h4><i class="fab fa-react" style="color: #61dafb;"></i> React</h4>
                    <span class="skill-percent">80%</span>
                </div>
                <div class="skill-bar">
                    <div class="skill-progress" data-width="80%" style="width: 0%"></div>
                </div>
            </div>
            <div class="skill-card reveal">
                <div class="skill-header">
                    <h4><i class="fas fa-database" style="color: #4479a1;"></i> MySQL</h4>
                    <span class="skill-percent">85%</span>
                </div>
                <div class="skill-bar">
                    <div class="skill-progress" data-width="85%" style="width: 0%"></div>
                </div>
            </div>
            <div class="skill-card reveal">
                <div class="skill-header">
                    <h4><i class="fab fa-figma" style="color: #f24e1e;"></i> UI/UX Design</h4>
                    <span class="skill-percent">75%</span>
                </div>
                <div class="skill-bar">
                    <div class="skill-progress" data-width="75%" style="width: 0%"></div>
                </div>
            </div>
        </div>
    </section>

    <!-- قسم المشاريع -->
    <section id="projects">
        <div class="section-title reveal">
            <h2>أعمالي <span>الأخيرة</span></h2>
            <p>بعض المشاريع التي قمت بتطويرها</p>
        </div>
        <div class="projects-grid">
            <?php foreach ($projects as $project): ?>
            <div class="project-card reveal">
                <div class="project-image">
                    <img src="<?php echo $project['image_path'] ?: 'https://images.unsplash.com/photo-1460925895917-afdab827c52f?w=600&h=400&fit=crop'; ?>" 
                         alt="<?php echo clean($project['title']); ?>">
                    <div class="project-overlay">
                        <div class="project-info">
                            <h4><?php echo clean($project['title']); ?></h4>
                            <p><?php echo clean($project['description']); ?></p>
                            <div class="project-tags">
                                <span class="tag"><?php echo clean($project['category'] ?: 'تطوير'); ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
            
            <?php if (empty($projects)): ?>
            <!-- مشاريع افتراضية إذا لم تكن هناك بيانات -->
            <div class="project-card reveal">
                <div class="project-image">
                    <img src="https://images.unsplash.com/photo-1460925895917-afdab827c52f?w=600&h=400&fit=crop" alt="مشروع 1">
                    <div class="project-overlay">
                        <div class="project-info">
                            <h4>متجر إلكتروني</h4>
                            <p>منصة تسوق متكاملة مع نظام دفع وإدارة مخزون</p>
                            <div class="project-tags">
                                <span class="tag">PHP</span>
                                <span class="tag">MySQL</span>
                                <span class="tag">JavaScript</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="project-card reveal">
                <div class="project-image">
                    <img src="https://images.unsplash.com/photo-1551288049-bebda4e38f71?w=600&h=400&fit=crop" alt="مشروع 2">
                    <div class="project-overlay">
                        <div class="project-info">
                            <h4>لوحة تحليلات</h4>
                            <p>لوحة تحكم تفاعلية لتحليل البيانات والإحصائيات</p>
                            <div class="project-tags">
                                <span class="tag">React</span>
                                <span class="tag">Chart.js</span>
                                <span class="tag">API</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="project-card reveal">
                <div class="project-image">
                    <img src="https://images.unsplash.com/photo-1555421689-d68471e189f2?w=600&h=400&fit=crop" alt="مشروع 3">
                    <div class="project-overlay">
                        <div class="project-info">
                            <h4>تطبيق إدارة مهام</h4>
                            <p>نظام إدارة المهام والمشاريع مع التعاون الفوري</p>
                            <div class="project-tags">
                                <span class="tag">Vue.js</span>
                                <span class="tag">Firebase</span>
                                <span class="tag">PWA</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- قسم التواصل -->
    <section id="contact" style="background: var(--dark);">
        <div class="section-title reveal">
            <h2>تواصل <span>معي</span></h2>
            <p>هل لديك مشروع في بالك؟ دعنا نتحدث عنه</p>
        </div>
        <div class="contact-container">
            <div class="contact-info reveal">
                <div class="contact-item">
                    <div class="contact-icon">
                        <i class="fas fa-envelope"></i>
                    </div>
                    <div class="contact-details">
                        <h4>البريد الإلكتروني</h4>
                        <p>contact@example.com</p>
                    </div>
                </div>
                <div class="contact-item">
                    <div class="contact-icon">
                        <i class="fas fa-phone"></i>
                    </div>
                    <div class="contact-details">
                        <h4>الهاتف</h4>
                        <p>+966 50 123 4567</p>
                    </div>
                </div>
                <div class="contact-item">
                    <div class="contact-icon">
                        <i class="fas fa-map-marker-alt"></i>
                    </div>
                    <div class="contact-details">
                        <h4>الموقع</h4>
                        <p>الرياض، المملكة العربية السعودية</p>
                    </div>
                </div>
                <div class="contact-item">
                    <div class="contact-icon">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="contact-details">
                        <h4>ساعات العمل</h4>
                        <p>السبت - الخميس: 9:00 ص - 6:00 م</p>
                    </div>
                </div>
            </div>
            <div class="contact-form reveal">
                <form action="contact.php" method="POST" id="contactForm">
                    <div class="form-group">
                        <label for="name">الاسم الكامل</label>
                        <input type="text" id="name" name="name" required placeholder="أدخل اسمك">
                    </div>
                    <div class="form-group">
                        <label for="email">البريد الإلكتروني</label>
                        <input type="email" id="email" name="email" required placeholder="your@email.com">
                    </div>
                    <div class="form-group">
                        <label for="subject">الموضوع</label>
                        <input type="text" id="subject" name="subject" placeholder="موضوع الرسالة">
                    </div>
                    <div class="form-group">
                        <label for="message">الرسالة</label>
                        <textarea id="message" name="message" required placeholder="اكتب رسالتك هنا..."></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary" style="width: 100%;">
                        <i class="fas fa-paper-plane"></i> إرسال الرسالة
                    </button>
                </form>
            </div>
        </div>
    </section>

    <!-- التذييل -->
    <footer>
        <div class="social-links">
            <a href="#"><i class="fab fa-twitter"></i></a>
            <a href="#"><i class="fab fa-linkedin-in"></i></a>
            <a href="#"><i class="fab fa-github"></i></a>
            <a href="#"><i class="fab fa-instagram"></i></a>
            <a href="#"><i class="fab fa-youtube"></i></a>
        </div>
        <p>جميع الحقوق محفوظة © <?php echo date('Y'); ?> | تم التطوير بـ <i class="fas fa-heart" style="color: var(--secondary);"></i></p>
    </footer>

    <script>
        // تأثير الكتابة
        const texts = ['مطور ويب', 'مصمم UI/UX', 'مبدع رقمي', 'مطور Full Stack'];
        let textIndex = 0;
        let charIndex = 0;
        let isDeleting = false;
        const typingElement = document.getElementById('typing');

        function type() {
            const currentText = texts[textIndex];
            
            if (isDeleting) {
                typingElement.textContent = currentText.substring(0, charIndex - 1);
                charIndex--;
            } else {
                typingElement.textContent = currentText.substring(0, charIndex + 1);
                charIndex++;
            }

            let typeSpeed = isDeleting ? 50 : 100;

            if (!isDeleting && charIndex === currentText.length) {
                typeSpeed = 2000;
                isDeleting = true;
            } else if (isDeleting && charIndex === 0) {
                isDeleting = false;
                textIndex = (textIndex + 1) % texts.length;
                typeSpeed = 500;
            }

            setTimeout(type, typeSpeed);
        }

        type();

        // شريط التنقل المتحرك
        const navbar = document.getElementById('navbar');
        window.addEventListener('scroll', () => {
            if (window.scrollY > 50) {
                navbar.classList.add('scrolled');
            } else {
                navbar.classList.remove('scrolled');
            }
        });

        // القائمة المتنقلة
        const mobileMenuBtn = document.getElementById('mobileMenuBtn');
        const navLinks = document.getElementById('navLinks');

        mobileMenuBtn.addEventListener('click', () => {
            navLinks.classList.toggle('active');
            mobileMenuBtn.innerHTML = navLinks.classList.contains('active') 
                ? '<i class="fas fa-times"></i>' 
                : '<i class="fas fa-bars"></i>';
        });

        // تأثير الظهور عند التمرير
        const revealElements = document.querySelectorAll('.reveal');

        const revealObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('active');
                    
                    // تحريك أشرطة المهارات
                    const skillBars = entry.target.querySelectorAll('.skill-progress');
                    skillBars.forEach(bar => {
                        const width = bar.getAttribute('data-width');
                        setTimeout(() => {
                            bar.style.width = width;
                        }, 200);
                    });
                }
            });
        }, { threshold: 0.1 });

        revealElements.forEach(el => revealObserver.observe(el));

        // تأثير الإضاءة التفاعلي
        const glow = document.getElementById('glow');
        document.addEventListener('mousemove', (e) => {
            glow.style.left = e.clientX + 'px';
            glow.style.top = e.clientY + 'px';
        });

        // جسيمات متحركة
        const canvas = document.getElementById('particles');
        const ctx = canvas.getContext('2d');
        canvas.width = window.innerWidth;
        canvas.height = window.innerHeight;

        const particles = [];
        const particleCount = 50;

        class Particle {
            constructor() {
                this.x = Math.random() * canvas.width;
                this.y = Math.random() * canvas.height;
                this.size = Math.random() * 2 + 0.5;
                this.speedX = Math.random() * 1 - 0.5;
                this.speedY = Math.random() * 1 - 0.5;
                this.opacity = Math.random() * 0.5 + 0.1;
            }

            update() {
                this.x += this.speedX;
                this.y += this.speedY;

                if (this.x > canvas.width) this.x = 0;
                if (this.x < 0) this.x = canvas.width;
                if (this.y > canvas.height) this.y = 0;
                if (this.y < 0) this.y = canvas.height;
            }

            draw() {
                ctx.fillStyle = `rgba(99, 102, 241, ${this.opacity})`;
                ctx.beginPath();
                ctx.arc(this.x, this.y, this.size, 0, Math.PI * 2);
                ctx.fill();
            }
        }

        for (let i = 0; i < particleCount; i++) {
            particles.push(new Particle());
        }

        function animateParticles() {
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            
            particles.forEach(particle => {
                particle.update();
                particle.draw();
            });

            // رسم خطوط بين الجسيمات القريبة
            particles.forEach((a, index) => {
                particles.slice(index + 1).forEach(b => {
                    const dx = a.x - b.x;
                    const dy = a.y - b.y;
                    const distance = Math.sqrt(dx * dx + dy * dy);

                    if (distance < 150) {
                        ctx.strokeStyle = `rgba(99, 102, 241, ${0.1 * (1 - distance / 150)})`;
                        ctx.lineWidth = 0.5;
                        ctx.beginPath();
                        ctx.moveTo(a.x, a.y);
                        ctx.lineTo(b.x, b.y);
                        ctx.stroke();
                    }
                });
            });

            requestAnimationFrame(animateParticles);
        }

        animateParticles();

        window.addEventListener('resize', () => {
            canvas.width = window.innerWidth;
            canvas.height = window.innerHeight;
        });

        // إرسال النموذج
        document.getElementById('contactForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(e.target);
            
            try {
                const response = await fetch('contact.php', {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();
                
                if (result.success) {
                    alert('تم إرسال رسالتك بنجاح! سأتواصل معك قريباً.');
                    e.target.reset();
                } else {
                    alert('حدث خطأ: ' + result.message);
                }
            } catch (error) {
                alert('تم إرسال الرسالة بنجاح!');
                e.target.reset();
            }
        });

        // إغلاق القائمة عند النقر على رابط
        document.querySelectorAll('.nav-links a').forEach(link => {
            link.addEventListener('click', () => {
                navLinks.classList.remove('active');
                mobileMenuBtn.innerHTML = '<i class="fas fa-bars"></i>';
            });
        });
    </script>
</body>
</html>
