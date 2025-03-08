<?php
// Database connection
$conn = new mysqli('localhost', 'root', '', 'pixelbyte_blog');

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Include tag functions
require_once 'blog/includes/tag_functions.php';

// Site root paths
$site_root = '/pixelbyte-blog/'; // Root of the site
$blog_root = '/pixelbyte-blog/blog/'; // Root of the blog section

// Get featured blog posts
$sql_featured = "SELECT id, title, slug, excerpt, image_url, category, created_at FROM blog_posts 
                WHERE is_featured = 1 ORDER BY created_at DESC LIMIT 4";
$featured_result = $conn->query($sql_featured);

// Placeholder store data (until we create the actual store)
$store_items = [
    [
        'id' => 1, 
        'title' => 'Nebula', 
        'description' => 'Minimal portfolio theme with a stunning dark mode and smooth animations.', 
        'price' => 49,
        'image' => 'https://via.placeholder.com/600x400/121212/ffffff?text=Nebula',
        'category' => 'Portfolio'
    ],
    [
        'id' => 2, 
        'title' => 'Pulse', 
        'description' => 'E-commerce template with cutting-edge payment integrations and analytics dashboard.', 
        'price' => 79,
        'image' => 'https://via.placeholder.com/600x400/7E57C2/ffffff?text=Pulse',
        'category' => 'E-commerce'
    ],
    [
        'id' => 3, 
        'title' => 'Quantum', 
        'description' => 'Blog theme focusing on readability with advanced SEO features built-in.', 
        'price' => 39,
        'image' => 'https://via.placeholder.com/600x400/26A69A/ffffff?text=Quantum', 
        'category' => 'Blog'
    ],
    [
        'id' => 4, 
        'title' => 'Prism', 
        'description' => 'Creative agency template with interactive elements and parallax effects.', 
        'price' => 59,
        'image' => 'https://via.placeholder.com/600x400/FF5722/ffffff?text=Prism',
        'category' => 'Agency'
    ]
];

// Calculate featured stats
$stats = [
    'projects' => 250,
    'clients' => 120,
    'themes' => count($store_items) + 15, // Pretend we have more
    'awards' => 12
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="PIXELBYTE - Pushing the boundaries of web design with cutting-edge templates and themes. Avant-garde digital experiences.">
    
    <!-- Open Graph Meta Tags -->
    <meta property="og:title" content="PIXELBYTE - Avant-Garde Web Design Templates">
    <meta property="og:description" content="Redefine digital presence with boundary-pushing web design templates. Where code meets art.">
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?php echo 'https://' . $_SERVER['HTTP_HOST'] . $site_root; ?>">
    
    <title>PIXELBYTE - Avant-Garde Web Design Templates</title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300;400;500;600;700&family=Darker+Grotesque:wght@300;400;500;600;700;800;900&family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <style>
        /* CSS Reset */
        *, *::before, *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        /* CSS Variables */
        :root {
            --primary: #7E57C2;
            --primary-light: #B39DDB;
            --secondary: #26A69A;
            --dark: #121212;
            --light: #F8F9FA;
            --accent1: #FF5722;
            --accent2: #FFC107;
            --glass-bg: rgba(255, 255, 255, 0.7);
            --glass-border: rgba(255, 255, 255, 0.5);
            --glass-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            --card-shadow: 0 8px 25px rgba(0, 0, 0, 0.07);
            --font-main: 'Space Grotesk', sans-serif;
            --font-heading: 'Darker Grotesque', sans-serif;
            --font-alt: 'Inter', sans-serif;
            --easing: cubic-bezier(0.76, 0, 0.24, 1);
        }

        /* Base styles */
        html {
            scroll-behavior: smooth;
            scroll-padding-top: 80px;
        }

        body {
            font-family: var(--font-main);
            background-color: #f8f9fc;
            color: var(--dark);
            line-height: 1.6;
            overflow-x: hidden;
            position: relative;
            min-height: 100vh;
        }

        /* Typography */
        h1, h2, h3, h4, h5, h6 {
            font-family: var(--font-heading);
            font-weight: 700;
            line-height: 1;
            margin-bottom: 1rem;
        }

        h1 {
            font-size: clamp(3.5rem, 10vw, 9rem);
            letter-spacing: -3px;
            line-height: 0.9;
        }

        h2 {
            font-size: clamp(2rem, 5vw, 4rem);
            letter-spacing: -1px;
        }

        h3 {
            font-size: clamp(1.5rem, 3vw, 2.5rem);
            letter-spacing: -0.5px;
        }

        p {
            margin-bottom: 1.5rem;
            font-size: clamp(1rem, 1.2vw, 1.1rem);
            color: rgba(18, 18, 18, 0.8);
        }

        a {
            color: var(--primary);
            text-decoration: none;
            font-weight: 500;
            transition: all 0.5s var(--easing);
        }

        a:hover {
            color: var(--secondary);
        }

        /* Utilities */
        .text-gradient {
            background: linear-gradient(90deg, var(--primary), var(--secondary));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            color: transparent;
        }

        .text-accent {
            background: linear-gradient(90deg, var(--accent1), var(--accent2));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            color: transparent;
        }

        .text-stroke {
            -webkit-text-stroke: 1px var(--dark);
            -webkit-text-fill-color: transparent;
        }

        .hide-on-mobile {
            display: none;
        }

        @media (min-width: 768px) {
            .hide-on-mobile {
                display: block;
            }
        }

        /* Magnetic Buttons */
        .magnetic-wrap {
            display: inline-block;
            position: relative;
        }

        .magnetic-area {
            position: relative;
            height: 100%;
            width: 100%;
        }

        /* Buttons */
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.8rem 2rem;
            background: var(--primary);
            color: white;
            font-weight: 600;
            border-radius: 2rem;
            border: none;
            cursor: pointer;
            transition: all 0.5s var(--easing);
            position: relative;
            overflow: hidden;
            z-index: 1;
            font-family: var(--font-main);
        }

        .btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 0%;
            height: 100%;
            background: var(--secondary);
            transition: all 0.5s var(--easing);
            z-index: -1;
        }

        .btn:hover {
            transform: scale(1.05);
            color: white;
        }

        .btn:hover::before {
            width: 100%;
        }

        .btn-outline {
            background: transparent;
            border: 1px solid var(--primary);
            color: var(--primary);
        }

        .btn-outline:hover {
            color: white;
        }

        .btn-large {
            padding: 1rem 3rem;
            font-size: 1.1rem;
            letter-spacing: 1px;
        }

        /* Layout */
        .container {
            width: 90%;
            max-width: 1600px;
            margin: 0 auto;
            padding: 0 1rem;
        }

        .section {
            padding: 6rem 0;
            position: relative;
        }

        header {
    position: fixed;
    width: 100%;
    top: 0;
    left: 0;
    z-index: 1000;
    padding: 1rem 0;
    transition: all 0.5s var(--easing);
    background: rgba(255, 255, 255, 0.8);
    backdrop-filter: var(--blur-effect);
    -webkit-backdrop-filter: var(--blur-effect);
    box-shadow: 0 5px 20px rgba(0, 0, 0, 0.05);
}

.header-inner {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.logo {
    font-family: var(--font-heading);
    font-size: 1.8rem;
    font-weight: 800;
    color: var(--primary);
    position: relative;
    padding: 0.5rem 0;
}

.nav-links {
    display: none;
}

.nav-links ul {
    display: flex;
    list-style: none;
    gap: 2rem;
}

.nav-links a {
    font-weight: 600;
    position: relative;
    padding: 0.5rem 0;
    color: var(--dark);
}

.nav-links a::after {
    content: '';
    position: absolute;
    width: 0;
    height: 2px;
    bottom: 0;
    left: 0;
    background: linear-gradient(90deg, var(--primary) 0%, var(--secondary) 100%);
    transition: width 0.3s ease;
}

.nav-links a:hover::after {
    width: 100%;
}

.mobile-menu-btn {
    display: block;
    background: none;
    border: none;
    cursor: pointer;
    z-index: 100;
}

.mobile-menu-btn span {
    display: block;
    width: 30px;
    height: 2px;
    margin: 7px;
    background-color: var(--primary);
    border-radius: 2px;
    transition: all 0.3s ease;
}

.mobile-menu {
    position: fixed;
    top: 0;
    right: -100%;
    width: 100%;
    height: 100vh;
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: var(--blur-effect);
    -webkit-backdrop-filter: var(--blur-effect);
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    transition: all 0.5s var(--easing);
    z-index: 99;
}

.mobile-menu.active {
    right: 0;
}

.mobile-menu ul {
    list-style: none;
    text-align: center;
}

.mobile-menu li {
    margin: 2rem 0;
}

.mobile-menu a {
    font-size: 1.8rem;
    font-weight: 700;
    color: var(--dark);
    position: relative;
    padding: 0.5rem 0;
}

@media (min-width: 768px) {
    .nav-links {
        display: block;
    }
    .mobile-menu-btn {
        display: none;
    }
}

        /* Hero Section */
        .hero {
            min-height: 100vh;
            position: relative;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .hero-content {
            position: relative;
            z-index: 1;
            padding: 8rem 0;
        }

        .hero-title-container {
            overflow: hidden;
            margin-bottom: 1.5rem;
        }

        .hero-title {
            transform: translateY(100%);
            opacity: 0;
            animation: slideUp 1s var(--easing) forwards;
            animation-delay: 0.3s;
        }

        .hero-subtitle {
            font-family: var(--font-main);
            font-size: clamp(1.1rem, 2vw, 1.3rem);
            max-width: 600px;
            margin-bottom: 2.5rem;
            opacity: 0;
            animation: fadeIn 1s var(--easing) forwards;
            animation-delay: 0.8s;
        }

        .hero-cta {
            display: flex;
            gap: 1.5rem;
            flex-wrap: wrap;
            opacity: 0;
            animation: fadeIn 1s var(--easing) forwards;
            animation-delay: 1s;
        }

        .hero-scroll-indicator {
            position: absolute;
            bottom: 2rem;
            left: 50%;
            transform: translateX(-50%);
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0.5rem;
            color: var(--dark);
            opacity: 0;
            animation: fadeIn 1s var(--easing) forwards;
            animation-delay: 1.5s;
        }

        .hero-scroll-text {
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 2px;
        }

        .hero-scroll-line {
            width: 1px;
            height: 50px;
            background: var(--dark);
            position: relative;
            overflow: hidden;
        }

        .hero-scroll-line::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: var(--primary);
            animation: scrollLine 2s infinite;
        }

        @keyframes scrollLine {
            0% {
                transform: translateY(-100%);
            }
            100% {
                transform: translateY(100%);
            }
        }

        .hero-image {
            position: absolute;
            top: 0;
            right: 0;
            width: 50%;
            height: 100%;
            clip-path: polygon(20% 0%, 100% 0%, 100% 100%, 0% 100%);
            background-size: cover;
            background-position: center;
            background-image: url('https://via.placeholder.com/1200x800/121212/ffffff?text=PIXELBYTE');
            z-index: 0;
            opacity: 0;
            animation: fadeInRight 1s var(--easing) forwards;
            animation-delay: 0.5s;
        }

        .hero-shapes {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
            overflow: hidden;
        }

        .hero-shape {
            position: absolute;
            transition: all 1s var(--easing);
            opacity: 0;
            animation: fadeIn 1s var(--easing) forwards;
        }

        .hero-shape.shape1 {
            width: 300px;
            height: 300px;
            border-radius: 57% 43% 70% 30% / 49% 31% 69% 51%;
            background: linear-gradient(45deg, var(--primary), var(--primary-light));
            top: 20%;
            right: -50px;
            opacity: 0.1;
            animation-delay: 0.7s;
            animation: morph 15s linear infinite alternate, fadeIn 1s var(--easing) forwards;
            animation-delay: 0, 0.7s;
        }

        .hero-shape.shape2 {
            width: 200px;
            height: 200px;
            border-radius: 32% 68% 33% 67% / 65% 34% 66% 35%;
            background: linear-gradient(45deg, var(--secondary), #64FFDA);
            bottom: 20%;
            left: 10%;
            opacity: 0.1;
            animation-delay: 0.9s;
            animation: morph 12s linear infinite alternate-reverse, fadeIn 1s var(--easing) forwards;
            animation-delay: 0, 0.9s;
        }

        .hero-shape.shape3 {
            width: 150px;
            height: 150px;
            border-radius: 50% 50% 70% 30% / 30% 50% 50% 70%;
            background: linear-gradient(45deg, var(--accent1), var(--accent2));
            top: 30%;
            left: 30%;
            opacity: 0.1;
            animation-delay: 1.1s;
            animation: morph 18s linear infinite alternate, fadeIn 1s var(--easing) forwards;
            animation-delay: 0, 1.1s;
        }

        @keyframes morph {
            0% {
                border-radius: 40% 60% 60% 40% / 60% 30% 70% 40%;
            }
            100% {
                border-radius: 40% 60% 30% 70% / 50% 60% 30% 60%;
            }
        }

        @keyframes slideUp {
            0% {
                transform: translateY(100%);
                opacity: 0;
            }
            100% {
                transform: translateY(0);
                opacity: 1;
            }
        }

        @keyframes fadeIn {
            0% {
                opacity: 0;
            }
            100% {
                opacity: 1;
            }
        }

        @keyframes fadeInRight {
            0% {
                opacity: 0;
                transform: translateX(50px);
            }
            100% {
                opacity: 1;
                transform: translateX(0);
            }
        }

        /* Showcase Section */
        .showcase {
            padding: 10rem 0;
            background: linear-gradient(rgba(240, 240, 245, 1), rgba(255, 255, 255, 0.8));
            position: relative;
            overflow: hidden;
        }

        .showcase-title {
            text-align: center;
            margin-bottom: 5rem;
            position: relative;
        }

        .showcase-title h2 {
            font-size: clamp(2.5rem, 8vw, 5rem);
            position: relative;
            display: inline-block;
        }

        .showcase-title h2::after {
            content: '';
            position: absolute;
            width: 100px;
            height: 8px;
            background: linear-gradient(90deg, var(--primary), var(--secondary));
            bottom: -1rem;
            left: 50%;
            transform: translateX(-50%);
        }

        .showcase-content {
            display: flex;
            flex-direction: column;
            gap: 5rem;
        }

        .showcase-item {
            display: grid;
            grid-template-columns: 1fr;
            gap: 3rem;
            position: relative;
        }

        @media (min-width: 768px) {
            .showcase-item {
                grid-template-columns: 1fr 1fr;
                align-items: center;
            }
            
            .showcase-item:nth-child(even) {
                grid-template-columns: 1fr 1fr;
            }
            
            .showcase-item:nth-child(even) .showcase-item-image {
                order: -1;
            }
        }

        .showcase-item-text {
            padding: 2rem;
        }

        .showcase-item-title {
            font-size: clamp(2rem, 5vw, 3rem);
            margin-bottom: 1.5rem;
        }

        .showcase-item-description {
            margin-bottom: 2rem;
            font-size: clamp(1rem, 1.5vw, 1.1rem);
        }

        .showcase-item-image {
            position: relative;
            overflow: hidden;
            border-radius: 2rem;
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.1);
            transform: perspective(1000px) rotateY(5deg) rotateX(5deg);
            transition: all 0.5s var(--easing);
        }

        .showcase-item:nth-child(even) .showcase-item-image {
            transform: perspective(1000px) rotateY(-5deg) rotateX(5deg);
        }

        .showcase-item-image:hover {
            transform: perspective(1000px) rotateY(0) rotateX(0);
        }

        .showcase-item-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            object-position: top;
            transition: all 0.5s var(--easing);
        }

        .showcase-item-image:hover img {
            transform: scale(1.05);
        }

        .showcase-item-category {
            display: inline-block;
            padding: 0.3rem 1rem;
            background: rgba(126, 87, 194, 0.1);
            border: 1px solid rgba(126, 87, 194, 0.2);
            color: var(--primary);
            border-radius: 2rem;
            font-weight: 600;
            font-size: 0.9rem;
            margin-bottom: 1rem;
        }

        .showcase-item-price {
            font-family: var(--font-heading);
            font-size: 2rem;
            font-weight: 700;
            color: var(--primary);
            margin-bottom: 2rem;
        }

        /* Blog Section */
        .blog-section {
            padding: 8rem 0;
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.8), rgba(240, 240, 245, 0.8));
            position: relative;
            overflow: hidden;
        }

        .blog-section-title {
            text-align: center;
            margin-bottom: 5rem;
        }

        .blog-section-title h2 {
            font-size: clamp(2.5rem, 8vw, 5rem);
            position: relative;
            display: inline-block;
        }

        .blog-section-title h2::after {
            content: '';
            position: absolute;
            width: 100px;
            height: 8px;
            background: linear-gradient(90deg, var(--primary), var(--secondary));
            bottom: -1rem;
            left: 50%;
            transform: translateX(-50%);
        }

        .blog-container {
            display: grid;
            grid-template-columns: 1fr;
            gap: 3rem;
        }

        @media (min-width: 768px) {
            .blog-container {
                grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            }
        }

        .blog-card {
            position: relative;
            overflow: hidden;
            border-radius: 2rem;
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.1);
            transition: all 0.5s var(--easing);
            height: 450px;
        }

        .blog-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.15);
        }

        .blog-card-img {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            z-index: 1;
            transition: all 0.5s var(--easing);
        }

        .blog-card:hover .blog-card-img {
            transform: scale(1.05);
        }

        .blog-card-overlay {
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(to top, rgba(0, 0, 0, 0.8), transparent);
            z-index: 2;
            padding: 2rem;
            display: flex;
            flex-direction: column;
            justify-content: flex-end;
            transition: all 0.5s var(--easing);
        }

        .blog-card-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }

        .blog-card-category {
            display: inline-block;
            padding: 0.3rem 1rem;
            background: rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(10px);
            border-radius: 2rem;
            color: white;
            font-size: 0.8rem;
        }

        .blog-card-date {
            color: rgba(255, 255, 255, 0.7);
            font-size: 0.8rem;
        }

        .blog-card-title {
            color: white;
            font-size: 1.5rem;
            margin-bottom: 1rem;
            line-height: 1.3;
        }

        .blog-card-excerpt {
            color: rgba(255, 255, 255, 0.7);
            margin-bottom: 1.5rem;
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .blog-card-link {
            color: white;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.3s ease;
        }

        .blog-card-link:hover {
            gap: 0.8rem;
            color: var(--secondary);
        }

        .blog-cta {
            text-align: center;
            margin-top: 4rem;
        }

        /* Stats Section */
        .stats-section {
            padding: 5rem 0;
            background: var(--dark);
            color: white;
            position: relative;
            overflow: hidden;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 3rem;
        }

        .stat-item {
            text-align: center;
            position: relative;
        }

        .stat-number {
            font-family: var(--font-heading);
            font-size: clamp(3rem, 10vw, 5rem);
            font-weight: 800;
            margin-bottom: 0.5rem;
            line-height: 1;
            background: linear-gradient(135deg, var(--primary-light), var(--secondary));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .stat-label {
            font-size: 1.2rem;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 2px;
            opacity: 0.7;
        }

        /* CTA Section */
        .cta-section {
            padding: 10rem 0;
            background: linear-gradient(rgba(255, 255, 255, 0.8), rgba(240, 240, 245, 0.9));
            position: relative;
            overflow: hidden;
            text-align: center;
        }

        .cta-title {
            font-size: clamp(2.5rem, 8vw, 5rem);
            margin-bottom: 2rem;
            position: relative;
            display: inline-block;
        }

        .cta-description {
            font-size: clamp(1.1rem, 2vw, 1.3rem);
            max-width: 700px;
            margin: 0 auto 3rem;
        }

        .cta-shapes {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
        }

        .cta-shape {
            position: absolute;
            opacity: 0.05;
        }

        .cta-shape.shape1 {
            width: 300px;
            height: 300px;
            border-radius: 30% 70% 70% 30% / 30% 30% 70% 70%;
            background: var(--primary);
            top: 10%;
            right: 10%;
            animation: morph 15s linear infinite alternate;
        }

        .cta-shape.shape2 {
            width: 200px;
            height: 200px;
            border-radius: 60% 40% 30% 70% / 60% 30% 70% 40%;
            background: var(--secondary);
            bottom: 10%;
            left: 10%;
            animation: morph 12s linear infinite alternate-reverse;
        }

        /* Magnetic button hover effect */
        .magnetic-wrap {
            position: relative;
            display: inline-block;
            margin: 0 auto;
        }

        /* Footer */
        footer {
            background: var(--dark);
            color: white;
            padding: 5rem 0 2rem;
            position: relative;
        }

        .footer-content {
            display: grid;
            grid-template-columns: 1fr;
            gap: 4rem;
        }

        @media (min-width: 768px) {
            .footer-content {
                grid-template-columns: 2fr 1fr 1fr;
            }
        }

        .footer-logo {
            font-family: var(--font-heading);
            font-size: 2rem;
            font-weight: 800;
            margin-bottom: 1.5rem;
            background: linear-gradient(135deg, var(--primary-light), var(--secondary));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .footer-about {
            max-width: 400px;
            margin-bottom: 2rem;
            opacity: 0.7;
        }

        .footer-social {
            display: flex;
            gap: 1.5rem;
        }

        .footer-social a {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 44px;
            height: 44px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.1);
            color: white;
            transition: all 0.5s var(--easing);
        }

        .footer-social a:hover {
            background: var(--primary);
            transform: translateY(-5px);
        }

        .footer-nav h3 {
            font-size: 1.5rem;
            margin-bottom: 2rem;
            position: relative;
        }

        .footer-nav h3::after {
            content: '';
            position: absolute;
            width: 50px;
            height: 3px;
            background: linear-gradient(90deg, var(--primary), var(--secondary));
            bottom: -0.8rem;
            left: 0;
        }

        .footer-nav ul {
            list-style: none;
        }

        .footer-nav li {
            margin-bottom: 1rem;
        }

        .footer-nav a {
            color: rgba(255, 255, 255, 0.7);
            transition: all 0.5s var(--easing);
            position: relative;
        }

        .footer-nav a::before {
            content: '';
            position: absolute;
            width: 0;
            height: 1px;
            background: var(--secondary);
            bottom: -5px;
            left: 0;
            transition: all 0.5s var(--easing);
        }

        .footer-nav a:hover {
            color: white;
        }

        .footer-nav a:hover::before {
            width: 100%;
        }

        .copyright {
            text-align: center;
            padding-top: 3rem;
            margin-top: 3rem;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            font-size: 0.9rem;
            opacity: 0.5;
        }

        /* Scroll Animations */
        .reveal {
            position: relative;
            opacity: 0;
            transform: translateY(50px);
            transition: all 1s var(--easing);
        }

        .reveal.active {
            opacity: 1;
            transform: translateY(0);
        }

        /* Cursor */
        .cursor {
            position: fixed;
            width: 20px;
            height: 20px;
            border-radius: 50%;
            background: rgba(126, 87, 194, 0.5);
            pointer-events: none;
            z-index: 9999;
            transform: translate(-50%, -50%);
            transition: all 0.1s ease;
            display: none;
        }

        .cursor-follower {
            position: fixed;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            border: 1px solid var(--primary);
            pointer-events: none;
            z-index: 9998;
            transform: translate(-50%, -50%);
            transition: all 0.3s var(--easing);
            display: none;
        }

        @media (min-width: 768px) {
            .cursor, .cursor-follower {
                display: block;
            }
        }

        .cursor.active, .cursor-follower.active {
            transform: translate(-50%, -50%) scale(1.5);
        }
    </style>
</head>
<body>
    <!-- Cursor -->
    <div class="cursor"></div>
    <div class="cursor-follower"></div>

   <!-- Header - Updated to include normal navigation -->
<header>
    <div class="container">
        <div class="header-inner">
            <a href="<?php echo $site_root; ?>" class="logo">PIXELBYTE</a>
            <div class="nav-links">
                <ul>
                    <li><a href="<?php echo $site_root; ?>">Home</a></li>
                    <li><a href="#showcase">Templates</a></li>
                    <li><a href="<?php echo $blog_root; ?>">Blog</a></li>
                    <li><a href="#features">Features</a></li>
                    <li><a href="#contact">Contact</a></li>
                </ul>
            </div>
            <button class="mobile-menu-btn" aria-label="Toggle Menu">
                <span></span>
                <span></span>
                <span></span>
            </button>
        </div>
    </div>
</header>

<!-- Mobile Menu - Keeping this simpler but matching the style -->
<div class="mobile-menu">
    <ul>
        <li><a href="<?php echo $site_root; ?>">Home</a></li>
        <li><a href="#showcase">Templates</a></li>
        <li><a href="<?php echo $blog_root; ?>">Blog</a></li>
        <li><a href="#features">Features</a></li>
        <li><a href="#contact">Contact</a></li>
    </ul>
</div>

<!-- Remove the circular menu button that was previously in the corner -->
<!-- Delete the following section:
<div class="menu-toggle">
    <div class="menu-icon">
        <span></span>
        <span></span>
        <span></span>
    </div>
</div>

<div class="menu">
    <nav class="menu-nav">
        <ul>
            <li style="--i: 1;"><a href="<?php echo $site_root; ?>">Home</a></li>
            <li style="--i: 2;"><a href="#showcase">Templates</a></li>
            <li style="--i: 3;"><a href="<?php echo $blog_root; ?>">Blog</a></li>
            <li style="--i: 4;"><a href="#contact">Contact</a></li>
        </ul>
    </nav>
    <div class="menu-socials">
        <a href="#" aria-label="Twitter">T</a>
        <a href="#" aria-label="Instagram">I</a>
        <a href="#" aria-label="Dribbble">D</a>
        <a href="#" aria-label="LinkedIn">L</a>
    </div>
</div>
    <!-- Hero Section -->
    <section class="hero">
        <div class="container">
            <div class="hero-content">
                <div class="hero-title-container">
                    <h1 class="hero-title"><span class="text-gradient">Design</span> Meets <span class="text-accent">Code</span></h1>
                </div>
                <p class="hero-subtitle">Pushing the boundaries of web design with avant-garde templates that combine stunning aesthetics with flawless functionality.</p>
                <div class="hero-cta">
                    <a href="#showcase" class="btn btn-large">Explore Templates</a>
                    <a href="<?php echo $blog_root; ?>" class="btn btn-outline btn-large">Read Blog</a>
                </div>
            </div>
        </div>
        <div class="hero-image"></div>
        <div class="hero-shapes">
            <div class="hero-shape shape1"></div>
            <div class="hero-shape shape2"></div>
            <div class="hero-shape shape3"></div>
        </div>
        <div class="hero-scroll-indicator">
            <div class="hero-scroll-text">Scroll</div>
            <div class="hero-scroll-line"></div>
        </div>
    </section>

    <!-- Stats Section -->
    <section class="stats-section">
        <div class="container">
            <div class="stats-grid">
                <div class="stat-item reveal">
                    <div class="stat-number"><?php echo $stats['projects']; ?>+</div>
                    <div class="stat-label">Projects</div>
                </div>
                <div class="stat-item reveal">
                    <div class="stat-number"><?php echo $stats['clients']; ?>+</div>
                    <div class="stat-label">Clients</div>
                </div>
                <div class="stat-item reveal">
                    <div class="stat-number"><?php echo $stats['themes']; ?></div>
                    <div class="stat-label">Templates</div>
                </div>
                <div class="stat-item reveal">
                    <div class="stat-number"><?php echo $stats['awards']; ?></div>
                    <div class="stat-label">Awards</div>
                </div>
            </div>
        </div>
    </section>

    <!-- Showcase Section -->
    <section class="showcase section" id="showcase">
        <div class="container">
            <div class="showcase-title reveal">
                <h2>Premium <span class="text-gradient">Templates</span></h2>
            </div>
            <div class="showcase-content">
                <?php foreach($store_items as $index => $item): ?>
                <div class="showcase-item reveal">
                    <div class="showcase-item-text">
                        <span class="showcase-item-category"><?php echo htmlspecialchars($item['category']); ?></span>
                        <h3 class="showcase-item-title"><?php echo htmlspecialchars($item['title']); ?></h3>
                        <p class="showcase-item-description"><?php echo htmlspecialchars($item['description']); ?></p>
                        <div class="showcase-item-price">$<?php echo $item['price']; ?></div>
                        <a href="#" class="btn">View Details</a>
                    </div>
                    <div class="showcase-item-image">
                        <img src="<?php echo $item['image']; ?>" alt="<?php echo htmlspecialchars($item['title']); ?>">
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Blog Section -->
    <section class="blog-section section">
        <div class="container">
            <div class="blog-section-title reveal">
                <h2>Latest <span class="text-gradient">Articles</span></h2>
            </div>
            <div class="blog-container">
                <?php if($featured_result->num_rows > 0): ?>
                    <?php while($post = $featured_result->fetch_assoc()): ?>
                    <div class="blog-card reveal">
                        <img src="<?php echo htmlspecialchars($post['image_url']); ?>" alt="<?php echo htmlspecialchars($post['title']); ?>" class="blog-card-img">
                        <div class="blog-card-overlay">
                            <div class="blog-card-meta">
                                <span class="blog-card-category"><?php echo htmlspecialchars($post['category']); ?></span>
                                <span class="blog-card-date"><?php echo date('F j, Y', strtotime($post['created_at'])); ?></span>
                            </div>
                            <h3 class="blog-card-title"><?php echo htmlspecialchars($post['title']); ?></h3>
                            <p class="blog-card-excerpt"><?php echo htmlspecialchars(substr($post['excerpt'], 0, 120)); ?>...</p>
                            <a href="<?php echo $blog_root . $post['slug']; ?>" class="blog-card-link">Read Article →</a>
                        </div>
                    </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="blog-card reveal">
                        <div class="blog-card-overlay">
                            <h3 class="blog-card-title">No posts found</h3>
                            <p class="blog-card-excerpt">Check back soon for new content!</p>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
            <div class="blog-cta reveal">
                <a href="<?php echo $blog_root; ?>" class="btn btn-large">View All Articles</a>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="cta-section section" id="contact">
        <div class="container">
            <h2 class="cta-title reveal"><span class="text-gradient">Elevate</span> Your Digital Presence</h2>
            <p class="cta-description reveal">Take your website to the next level with our premium templates and expert guidance.</p>
            <div class="magnetic-wrap reveal">
                <div class="magnetic-area">
                    <a href="#showcase" class="btn btn-large">Get Started Today</a>
                </div>
            </div>
        </div>
        <div class="cta-shapes">
            <div class="cta-shape shape1"></div>
            <div class="cta-shape shape2"></div>
        </div>
    </section>

    <!-- Footer -->
    <footer>
        <div class="container">
            <div class="footer-content">
                <div>
                    <div class="footer-logo">PIXELBYTE</div>
                    <p class="footer-about">We push the boundaries of web design, creating avant-garde templates and themes that transform digital experiences.</p>
                    <div class="footer-social">
                        <a href="#" aria-label="Twitter">T</a>
                        <a href="#" aria-label="Instagram">I</a>
                        <a href="#" aria-label="Dribbble">D</a>
                        <a href="#" aria-label="GitHub">G</a>
                        <a href="#" aria-label="LinkedIn">L</a>
                    </div>
                </div>
                <div class="footer-nav">
                    <h3>Navigation</h3>
                    <ul>
                        <li><a href="<?php echo $site_root; ?>">Home</a></li>
                        <li><a href="#showcase">Templates</a></li>
                        <li><a href="<?php echo $blog_root; ?>">Blog</a></li>
                        <li><a href="#contact">Contact</a></li>
                    </ul>
                </div>
                <div class="footer-nav">
                    <h3>Categories</h3>
                    <ul>
                        <li><a href="#">Portfolio</a></li>
                        <li><a href="#">E-commerce</a></li>
                        <li><a href="#">Blog</a></li>
                        <li><a href="#">Agency</a></li>
                        <li><a href="#">Business</a></li>
                    </ul>
                </div>
            </div>
            <div class="copyright">
                <p>&copy; <?php echo date('Y'); ?> PIXELBYTE. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <!-- JavaScript -->
    <script>
       // Mobile menu toggle
const mobileMenuBtn = document.querySelector('.mobile-menu-btn');
const mobileMenu = document.querySelector('.mobile-menu');

mobileMenuBtn.addEventListener('click', () => {
    mobileMenu.classList.toggle('active');
    document.body.classList.toggle('no-scroll');
    
    // Transform hamburger to X
    const spans = mobileMenuBtn.querySelectorAll('span');
    
    if (mobileMenu.classList.contains('active')) {
        spans[0].style.transform = 'rotate(45deg) translate(5px, 5px)';
        spans[1].style.opacity = '0';
        spans[2].style.transform = 'rotate(-45deg) translate(8px, -8px)';
    } else {
        spans[0].style.transform = 'none';
        spans[1].style.opacity = '1';
        spans[2].style.transform = 'none';
    }
});

// Close mobile menu when clicking on a link
const mobileLinks = document.querySelectorAll('.mobile-menu a');
mobileLinks.forEach(link => {
    link.addEventListener('click', () => {
        mobileMenu.classList.remove('active');
        document.body.classList.remove('no-scroll');
        
        // Reset hamburger icon
        const spans = mobileMenuBtn.querySelectorAll('span');
        spans[0].style.transform = 'none';
        spans[1].style.opacity = '1';
        spans[2].style.transform = 'none';
    });
});
        
        // Scroll reveal animation
        function reveal() {
            const reveals = document.querySelectorAll('.reveal');
            
            for (let i = 0; i < reveals.length; i++) {
                const windowHeight = window.innerHeight;
                const elementTop = reveals[i].getBoundingClientRect().top;
                const elementVisible = 150;
                
                if (elementTop < windowHeight - elementVisible) {
                    reveals[i].classList.add('active');
                }
            }
        }
        
        window.addEventListener('scroll', reveal);
        reveal(); // Initial check
        
        // Custom cursor
        const cursor = document.querySelector('.cursor');
        const cursorFollower = document.querySelector('.cursor-follower');
        const links = document.querySelectorAll('a');
        const buttons = document.querySelectorAll('.btn');
        
        document.addEventListener('mousemove', e => {
            cursor.style.left = e.clientX + 'px';
            cursor.style.top = e.clientY + 'px';
            
            setTimeout(() => {
                cursorFollower.style.left = e.clientX + 'px';
                cursorFollower.style.top = e.clientY + 'px';
            }, 100);
        });
        
        links.forEach(link => {
            link.addEventListener('mouseenter', () => {
                cursor.classList.add('active');
                cursorFollower.classList.add('active');
            });
            link.addEventListener('mouseleave', () => {
                cursor.classList.remove('active');
                cursorFollower.classList.remove('active');
            });
        });
        
        buttons.forEach(button => {
            button.addEventListener('mouseenter', () => {
                cursor.classList.add('active');
                cursorFollower.classList.add('active');
            });
            button.addEventListener('mouseleave', () => {
                cursor.classList.remove('active');
                cursorFollower.classList.remove('active');
            });
        });
        
        // Magnetic button effect
        function magneticButtons() {
            const magnets = document.querySelectorAll('.magnetic-wrap');
            
            magnets.forEach(magnet => {
                const magnetArea = magnet.querySelector('.magnetic-area');
                const magnetButton = magnet.querySelector('.btn');
                
                magnet.addEventListener('mousemove', e => {
                    const rect = magnet.getBoundingClientRect();
                    const x = e.clientX - rect.left - rect.width / 2;
                    const y = e.clientY - rect.top - rect.height / 2;
                    
                    magnetButton.style.transform = `translate(${x * 0.3}px, ${y * 0.3}px)`;
                });
                
                magnet.addEventListener('mouseleave', () => {
                    magnetButton.style.transform = 'translate(0px, 0px)';
                });
            });
        }
        
        // Run magnetic button effect only on desktop
        if (window.innerWidth > 768) {
            magneticButtons();
        }
        
        // Parallax effect on scroll
        function parallax() {
            const shapes = document.querySelectorAll('.hero-shape');
            
            window.addEventListener('scroll', () => {
                const scrollY = window.scrollY;
                
                shapes.forEach((shape, index) => {
                    const speed = 0.1 * (index + 1);
                    shape.style.transform = `translateY(${scrollY * speed}px)`;
                });
            });
        }
        
        parallax();
    </script>
</body>
</html>