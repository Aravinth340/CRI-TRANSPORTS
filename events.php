<?php
// Load content from dashboard_content.json
require_once 'content_loader.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CRI Travels - Events</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        .event-card {
            background: #f9f9f9;
            border-left: 4px solid #205887;
            padding: 20px;
            margin-bottom: 24px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }
        .event-card h3 {
            color: #205887;
            margin-bottom: 10px;
        }
        .event-date {
            color: #ef8f2d;
            font-weight: bold;
            margin-bottom: 8px;
        }
        .event-details {
            color: #555;
            line-height: 1.6;
        }
        .event-price {
            color: #205887;
            font-weight: bold;
            margin-top: 10px;
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
    <section class="services-list">
        <h2>Upcoming Travel Events & Tours</h2>
        <p style="text-align: center; margin-bottom: 30px;">Join our special travel events and group tours across Tamil Nadu. Book your spot today!</p>
        
<?php foreach ($content['events'] as $event): ?>
        <div class="event-card">
            <div class="event-date"><?php echo htmlspecialchars($event['date']); ?></div>
            <h3><?php echo htmlspecialchars($event['title']); ?></h3>
            <div class="event-details"><?php echo $event['details']; ?></div>
            <div class="event-price"><?php echo htmlspecialchars($event['price']); ?></div>
        </div>
<?php endforeach; ?>

        <div style="text-align: center; margin-top: 30px;">
            <a href="login.php" class="cta-btn">Book Your Event Tour Now</a>
        </div>
    </section>
    
    <marquee behavior="slideIn" direction="left"><b>Thanks for visiting our website</b></marquee>
    <footer>
        <script>
            const slides = document.querySelector('.slides');
const slideImages = document.querySelectorAll('.slide');

let counter = 0;
const size = slideImages[0].clientWidth;

// Function to move the slide
function autoSlide() {
  counter++;
  
  // Reset to first slide if at the end
  if (counter >= slideImages.length) {
    counter = 0;
  }
  
  slides.style.transform = 'translateX(' + (-size * counter) + 'px)';
}

// Run the function every 3 seconds (3000ms)
setInterval(autoSlide, 3000);
        </script>
        <p>&copy; 2025 CRI Travels. All rights reserved.</p>
        <li class="hideOnMobile"><a href="feedback.html">Feedback</a></li>
    </footer>
</body>
</html>

