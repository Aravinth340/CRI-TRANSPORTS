<?php
// Load content from dashboard_content.json
require_once 'content_loader.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CRI Travels - Explore Tamil Nadu with Us</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        /* Inline additions for the hero image and highlights */
        .landing-hero {
            background: linear-gradient(rgba(32,88,135,0.7),rgba(15,52,96,0.7)), url('img/tn-landscape.jpg') center/cover no-repeat;
            color: #fff;
            padding: 70px 24px 50px 24px;
            text-align: center;
            border-radius: 0 0 18px 18px;
        }
        .landing-highlights {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 22px;
            margin: 34px auto;
            max-width: 850px;
        }
        .highlight-card {
            background: #fff;
            border-radius: 14px;
            box-shadow: 0 2px 10px rgba(32,88,135,0.06);
            padding: 22px 18px;
            flex: 1 1 210px;
            max-width: 220px;
            min-width: 160px;
            text-align: center;
        }
        .highlight-card img {
            width: 48px;
            height: 48px;
            margin-bottom: 10px;
        }
        .landing-cta {
            display: flex;
            justify-content: center;
            margin-top: 38px;
            margin-bottom: 24px;
        }
        @media (max-width: 720px) {
            .landing-highlights {
                flex-direction: column;
                align-items: center;
                gap: 18px;
            }
        }
    </style>
</head>
<body>
    <header>
        <img src="img\logo.png" alt="logo">
        <h1>CRI Travels</h1>
        <p>Your Trusted Travel Partner in Tamil Nadu</p>
        <li><a href="login.php">Login</a></li>
    </header>
    <nav>
            <ul class="sidebar">
           <li onclick=hideSidebar()><a href=""><svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#e3e3e3"><path d="m256-200-56-56 224-224-224-224 56-56 224 224 224-224 56 56-224 224 224 224-56 56-224-224-224 224Z"/></svg></a>
            <a href="home.php">Home</a>
            <a href="services.html">Services</a>
            <a href="about.html" class="active">About Us</a>
            <a href="contact.html">Contact</a>
            <a href="index.html">Main Menu</a>
            <a href="events.php">Events</a>
            <a href="feedback.html">Feedback</a>
            <a href="login.php">Login</a>
            </ul>
            <ul>
            <li class="hideOnMobile"><a href="home.php">Home</a></li>
            <li class="hideOnMobile"><a href="services.html">Services</a></li>
            <li class="hideOnMobile"><a href="about.html" class="active">About Us</a></li>
            <li class="hideOnMobile"><a href="contact.html">Contact</a></li>
            <li class="hideOnMobile"><a href="index.html">Main Menu</a></li>
            <li class="hideOnMobile"><a href="events.php">Events</a></li>
            <li class="menu-button" onclick=showSidebar()><a href="#"><svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#e3e3e3"><path d="M120-240v-80h720v80H120Zm0-200v-80h720v80H120Zm0-200v-80h720v80H120Z"/></svg></a></li>
            </ul>            
        </nav>
        <script>
        function showSidebar() {
            const sidebar = document.querySelector('.sidebar');
            sidebar.style.display = 'flex';
            sidebar.style.flexDirection = 'column';     
            sidebar.style.alignItems = 'flex-start';
        }        function hideSidebar() {
            const sidebar = document.querySelector('.sidebar');
            sidebar.style.display = 'none';
        }
    </script>
    <marquee behavior="slideIn" direction="right"><b><?php echo htmlspecialchars($content['banner_text']); ?></b></marquee>
     <div class="slider">
<?php foreach ($content['slider_images'] as $img): ?>
    <img src="<?php echo htmlspecialchars($img); ?>" alt="Slide">
<?php endforeach; ?>
</div>
    <section class="landing-hero">
        <h2><?php echo $content['index_page']['hero_title']; ?></h2>
        <p><?php echo $content['index_page']['hero_description']; ?></p>
        <div class="landing-cta">
            <a href="services.html" class="cta-btn">Explore Our Services</a>
            <a href="login.php" class="cta-btn" style="margin-left:18px;">Contact & Book</a>
        </div>
    </section>

    <section class="landing-highlights">
        <?php foreach ($content['index_page']['highlights'] as $highlight): ?>
        <div class="highlight-card">
            <img src="<?php echo htmlspecialchars($highlight['image']); ?>" alt="<?php echo htmlspecialchars($highlight['title']); ?>" />
            <h3><?php echo htmlspecialchars($highlight['title']); ?></h3>
            <p><?php echo $highlight['description']; ?></p>
        </div>
        <?php endforeach; ?>
    </section>

    <section class="about">
        <h2><?php echo htmlspecialchars($content['index_page']['why_choose_title']); ?></h2>
        <ul>
            <?php foreach ($content['index_page']['why_choose_items'] as $item): ?>
            <li><?php echo htmlspecialchars($item); ?></li>
            <?php endforeach; ?>
        </ul>
    </section>
    <marquee behavior="slideIn" direction="left"><b>Thanks for visiting our website</b></marquee>
    <footer>
        <p>&copy; 2025 CRI Travels. All rights reserved.</p>
        <li class="hideOnMobile"><a href="feedback.html">Feedback</a></li>
    </footer>
</body>
</html>