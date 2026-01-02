<?php
// Website Content Editor - Admin Only
require_once 'config/auth.php';
requireRole('admin');

// File paths for content (relative to root directory, since editor is in admin folder)
$settings_file = '../dashboard_content.json';

// --- Default content structure ---
$default_content = [
    'banner_text' => 'Welcome to CRI TRAVELS',
    'slider_images' => [
        'image-1.jpeg',
        'image-2.jpeg',
        'image-3.jpeg',
        'image-4.jpeg'
    ],
    'events' => [
        [
            'date' => 'January 3-5, 2026',
            'title' => 'Ooty Hill Station Weekend Tour',
            'details' => 'Experience the beauty of the "Queen of Hill Stations" with our 3-day weekend package. Visit botanical gardens, Ooty Lake, tea estates, and enjoy the scenic Nilgiri Mountain Railway.<br><strong>Includes:</strong> Coach bus transportation, accommodation, breakfast, and guided tours.',
            'price' => 'Starting from ₹4,500 per person'
        ],
        [
            'date' => 'February 5-7, 2026',
            'title' => "Kodaikanal Nature Retreat",
            'details' => "Explore the misty mountains of Kodaikanal with our special group package. Visit Coaker's Walk, Bryant Park, Pillar Rocks, and the serene Kodai Lake.<br><strong>Includes:</strong> AC coach bus, hotel stay, meals, and sightseeing tours.",
            'price' => 'Starting from ₹5,200 per person'
        ],
        [
            'date' => 'February 10-15, 2026',
            'title' => 'Rameshwaram & Dhanushkodi Pilgrimage Tour',
            'details' => 'Spiritual journey to the sacred Rameshwaram temple and the ghost town of Dhanushkodi. Experience the confluence of Bay of Bengal and Indian Ocean.<br><strong>Includes:</strong> Coach bus transport, accommodation, and temple visits with guide.',
            'price' => 'Starting from ₹3,800 per person'
        ]
    ],
    'services' => [
        [
            'title' => 'Three-Wheeler Auto',
            'description' => 'Perfect for city rides and quick trips. Experienced auto drivers for your safe travel.',
            'image' => 'img\\auto.jpeg'
        ],
        [
            'title' => 'Maxi Cab',
            'description' => 'Spacious and comfortable, ideal for group and family travel. AC/non-AC options available.',
            'image' => 'img\\m.jpeg'
        ],
        [
            'title' => 'Car Rental',
            'description' => 'Choose from our fleet of well-maintained cars for personal or business travel within Tamil Nadu.',
            'image' => 'img\\Chevrolet-Tavera.jpeg'
        ],
        [
            'title' => 'Coach Bus',
            'description' => 'comfortable travel with A/c coach buses For College IV Trips friends trips family trips All over tamilnadu',
            'image' => 'img\\coach.jpg'
        ]
    ],
    'index_page' => [
        'hero_title' => 'Travel Across Tamil Nadu<br>with Ease & Comfort',
        'hero_description' => 'Book autos, maxi cabs, cars, or coach buses for any occasion.<br>Affordable, reliable, and available 24/7.',
        'highlights' => [
            [
                'title' => 'Autos',
                'description' => 'Quick city rides by professional drivers. Clean & sanitized autos for your comfort.',
                'image' => 'img/auto.jpeg'
            ],
            [
                'title' => 'Maxi Cabs',
                'description' => 'Spacious cabs for families & groups.<br>AC/Non-AC options available.',
                'image' => 'img/m.jpeg'
            ],
            [
                'title' => 'Car Rentals',
                'description' => 'Choose from well-maintained cars for all travels—business or personal.',
                'image' => 'img/Chevrolet-Tavera.jpeg'
            ],
            [
                'title' => 'Coach Buses',
                'description' => 'Comfortable journeys for college, family, or friends\' trips all over Tamil Nadu.',
                'image' => 'img/coach.jpg'
            ]
        ],
        'why_choose_title' => 'Why Choose CRI Travels?',
        'why_choose_items' => [
            'Available 24/7 across Tamil Nadu',
            'Sanitized, safe & reliable vehicles',
            'Professional, local drivers',
            'Easy booking by phone or email'
        ]
    ],
    'contact' => [
        'heading' => 'Contact Us',
        'intro_text' => 'Ready to book your ride or have a question? Reach out to us!',
        'phone' => '+91 75581 98405',
        'email' => 'critravels@gmail.com',
        'address' => '4/75 Pudukkottai to ponnamiravathi Main Road,<br> panayapatti,Pudukkottai<br> Tamil Nadu,india<br>622402'
    ]
];

// --- Helper functions ---
function get_content($settings_file, $default_content) {
    if (!file_exists($settings_file)) return $default_content;
    $content = json_decode(file_get_contents($settings_file), true);
    if (!is_array($content)) return $default_content;
    // Merge with defaults recursively to ensure all fields exist
    return array_replace_recursive($default_content, $content);
}
function save_content($settings_file, $content) {
    file_put_contents($settings_file, json_encode($content, JSON_PRETTY_PRINT));
}


// --- Form Submission Handling ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $content = get_content($settings_file, $default_content);

    // Banner text update
    if (isset($_POST['banner_text'])) {
        $content['banner_text'] = $_POST['banner_text'];
    }

    // Slider images update
    if (!empty($_FILES['slider_images']['name'][0])) {
        $upload_dir = '../uploads/';
        if (!file_exists($upload_dir)) mkdir($upload_dir, 0777, true);
        $new_slider_images = [];
        foreach ($_FILES['slider_images']['tmp_name'] as $key => $tmp_name) {
            if ($_FILES['slider_images']['error'][$key] === UPLOAD_ERR_OK) {
                $ext = pathinfo($_FILES['slider_images']['name'][$key], PATHINFO_EXTENSION);
                $filename = uniqid('slider_', true) . '.' . $ext;
                move_uploaded_file($tmp_name, $upload_dir . $filename);
                // Save path relative to root (not admin folder)
                $new_slider_images[] = 'uploads/' . $filename;
            }
        }
        if ($new_slider_images) {
            $content['slider_images'] = $new_slider_images;
        }
    }

    // Events update
    if (isset($_POST['events'])) {
        $content['events'] = [];
        foreach ($_POST['events'] as $i => $event) {
            $content['events'][] = [
                'date' => htmlspecialchars($event['date']),
                'title' => htmlspecialchars($event['title']),
                'details' => $event['details'], // Store HTML as-is for display on website
                'price' => htmlspecialchars($event['price'])
            ];
        }
    }

    // Services update
    if (isset($_POST['services'])) {
        $content['services'] = [];
        foreach ($_POST['services'] as $i => $service) {
            $content['services'][] = [
                'title' => htmlspecialchars($service['title']),
                'description' => htmlspecialchars($service['description']),
                'image' => htmlspecialchars($service['image'])
            ];
        }
    }

    // Index page update
    if (isset($_POST['index_hero_title'])) {
        if (!isset($content['index_page'])) $content['index_page'] = [];
        $content['index_page']['hero_title'] = $_POST['index_hero_title'];
        $content['index_page']['hero_description'] = $_POST['index_hero_description'];
        $content['index_page']['why_choose_title'] = htmlspecialchars($_POST['index_why_choose_title']);
    }
    
    if (isset($_POST['index_highlights'])) {
        $content['index_page']['highlights'] = [];
        foreach ($_POST['index_highlights'] as $i => $highlight) {
            $content['index_page']['highlights'][] = [
                'title' => htmlspecialchars($highlight['title']),
                'description' => $highlight['description'],
                'image' => htmlspecialchars($highlight['image'])
            ];
        }
    }
    
    if (isset($_POST['index_why_choose_items'])) {
        $content['index_page']['why_choose_items'] = [];
        foreach ($_POST['index_why_choose_items'] as $item) {
            if (!empty(trim($item))) {
                $content['index_page']['why_choose_items'][] = htmlspecialchars($item);
            }
        }
    }

    // Contact page update
    if (isset($_POST['contact_heading'])) {
        $content['contact'] = [
            'heading' => htmlspecialchars($_POST['contact_heading']),
            'intro_text' => htmlspecialchars($_POST['contact_intro_text']),
            'phone' => htmlspecialchars($_POST['contact_phone']),
            'email' => htmlspecialchars($_POST['contact_email']),
            'address' => $_POST['contact_address']
        ];
    }

    // Save and reload
    save_content($settings_file, $content);
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// --- Fetch current content for dashboard display ---
$content = get_content($settings_file, $default_content);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard - CRI Travels</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f8f8fc; color: #333; }
        .dashboard-container { max-width: 900px; margin: 40px auto; background: #fff; padding: 40px; border-radius: 12px; box-shadow: 0 0 16px #ddd; }
        h2 { color:#205887; }
        input[type="text"], textarea { width: 100%; padding: 8px; margin: 4px 0 14px; border:1px solid #b9c6d0; border-radius: 5px;}
        input[type="file"] { margin-bottom: 14px; }
        .event-group { border: 1px solid #ddd; padding: 16px; border-radius: 7px; margin-bottom: 10px; }
        button, input[type=submit] { background: #ef8f2d; color: #fff; border: none; padding: 10px 24px; border-radius: 5px; cursor: pointer; margin: 8px 0; }
        button:hover, input[type=submit]:hover { background: #205887; }
        .slider-preview img{max-width:80px;margin:4px;border-radius:5px;}
    </style>
</head>
<body>
<div class="dashboard-container">
    <div style="margin-bottom: 20px;">
        <a href="dashboard.php" style="background: #205887; color: #fff; padding: 10px 20px; border-radius: 5px; text-decoration: none; display: inline-block; margin-bottom: 20px;">← Back to Admin Dashboard</a>
    </div>
    <h1>CRI Travels - Website Content Editor</h1>
    <form method="post" enctype="multipart/form-data">
        <h2>Edit Website Content</h2>
        
        <label><b>Banner Text (Marquee):</b></label>
        <input type="text" name="banner_text" value="<?php echo htmlspecialchars($content['banner_text']); ?>" />

        <label><b>Slider Images:</b> (Current images below. To replace, upload new images)</label>
        <div class="slider-preview">
            <?php foreach ($content['slider_images'] as $img): ?>
                <img src="../<?php echo htmlspecialchars($img); ?>" alt="slider">
            <?php endforeach; ?>
        </div>
        <input type="file" name="slider_images[]" multiple accept="image/*">

        <h3>Travel Events:</h3>
        <div id="events-list">
            <?php foreach ($content['events'] as $i => $event): ?>
                <div class="event-group">
                    <label>Date:</label>
                    <input type="text" name="events[<?php echo $i; ?>][date]" value="<?php echo htmlspecialchars($event['date']); ?>">
                    <label>Title:</label>
                    <input type="text" name="events[<?php echo $i; ?>][title]" value="<?php echo htmlspecialchars($event['title']); ?>">
                    <label>Details (can use HTML):</label>
                    <textarea name="events[<?php echo $i; ?>][details]" rows="3"><?php echo htmlspecialchars($event['details']); ?></textarea>
                    <label>Price:</label>
                    <input type="text" name="events[<?php echo $i; ?>][price]" value="<?php echo htmlspecialchars($event['price']); ?>">
                    <button type="button" onclick="removeEvent(this)">Remove</button>
                </div>
            <?php endforeach; ?>
        </div>
        <button type="button" onclick="addEvent()">Add New Event</button>

        <h3>Services Page Content:</h3>
        <div id="services-list">
            <?php foreach ($content['services'] as $i => $service): ?>
                <div class="service-group" style="border: 1px solid #ddd; padding: 16px; border-radius: 7px; margin-bottom: 10px;">
                    <label>Service Title:</label>
                    <input type="text" name="services[<?php echo $i; ?>][title]" value="<?php echo htmlspecialchars($service['title']); ?>">
                    <label>Description:</label>
                    <textarea name="services[<?php echo $i; ?>][description]" rows="2"><?php echo htmlspecialchars($service['description']); ?></textarea>
                    <label>Image Path:</label>
                    <input type="text" name="services[<?php echo $i; ?>][image]" value="<?php echo htmlspecialchars($service['image']); ?>">
                    <button type="button" onclick="removeService(this)">Remove</button>
                </div>
            <?php endforeach; ?>
        </div>
        <button type="button" onclick="addService()">Add New Service</button>

        <h3>Index/Landing Page Content:</h3>
        <label><b>Hero Section Title:</b></label>
        <textarea name="index_hero_title" rows="2"><?php echo htmlspecialchars($content['index_page']['hero_title']); ?></textarea>
        
        <label><b>Hero Section Description:</b></label>
        <textarea name="index_hero_description" rows="2"><?php echo htmlspecialchars($content['index_page']['hero_description']); ?></textarea>

        <label><b>Highlight Cards:</b></label>
        <div id="highlights-list">
            <?php foreach ($content['index_page']['highlights'] as $i => $highlight): ?>
                <div class="highlight-group" style="border: 1px solid #ddd; padding: 16px; border-radius: 7px; margin-bottom: 10px;">
                    <label>Title:</label>
                    <input type="text" name="index_highlights[<?php echo $i; ?>][title]" value="<?php echo htmlspecialchars($highlight['title']); ?>">
                    <label>Description (can use HTML):</label>
                    <textarea name="index_highlights[<?php echo $i; ?>][description]" rows="2"><?php echo htmlspecialchars($highlight['description']); ?></textarea>
                    <label>Image Path:</label>
                    <input type="text" name="index_highlights[<?php echo $i; ?>][image]" value="<?php echo htmlspecialchars($highlight['image']); ?>">
                    <button type="button" onclick="removeHighlight(this)">Remove</button>
                </div>
            <?php endforeach; ?>
        </div>
        <button type="button" onclick="addHighlight()">Add New Highlight</button>

        <label><b>"Why Choose" Section Title:</b></label>
        <input type="text" name="index_why_choose_title" value="<?php echo htmlspecialchars($content['index_page']['why_choose_title']); ?>">
        
        <label><b>"Why Choose" Items (one per line):</b></label>
        <div id="why-choose-list">
            <?php foreach ($content['index_page']['why_choose_items'] as $i => $item): ?>
                <input type="text" name="index_why_choose_items[]" value="<?php echo htmlspecialchars($item); ?>" style="margin-bottom: 5px;">
            <?php endforeach; ?>
        </div>
        <button type="button" onclick="addWhyChooseItem()">Add Item</button>

        <h3>Contact Page Content:</h3>
        <label><b>Heading:</b></label>
        <input type="text" name="contact_heading" value="<?php echo htmlspecialchars($content['contact']['heading']); ?>">
        
        <label><b>Introduction Text:</b></label>
        <textarea name="contact_intro_text" rows="2"><?php echo htmlspecialchars($content['contact']['intro_text']); ?></textarea>
        
        <label><b>Phone:</b></label>
        <input type="text" name="contact_phone" value="<?php echo htmlspecialchars($content['contact']['phone']); ?>">
        
        <label><b>Email:</b></label>
        <input type="text" name="contact_email" value="<?php echo htmlspecialchars($content['contact']['email']); ?>">
        
        <label><b>Address (can use HTML):</b></label>
        <textarea name="contact_address" rows="4"><?php echo htmlspecialchars($content['contact']['address']); ?></textarea>

        <br>
        <input type="submit" value="Save Changes">
    </form>
    <hr>
    <p><b>Note:</b> This is a basic admin panel. For live usage, please add secure authentication and security checks.</p>
</div>
<script>
function addEvent() {
    var cnt = document.querySelectorAll('.event-group').length;
    var html = `
        <div class="event-group">
            <label>Date:</label>
            <input type="text" name="events[`+cnt+`][date]">
            <label>Title:</label>
            <input type="text" name="events[`+cnt+`][title]">
            <label>Details (can use HTML):</label>
            <textarea name="events[`+cnt+`][details]" rows="3"></textarea>
            <label>Price:</label>
            <input type="text" name="events[`+cnt+`][price]">
            <button type="button" onclick="removeEvent(this)">Remove</button>
        </div>
    `;
    document.getElementById('events-list').insertAdjacentHTML('beforeend', html);
}

function removeEvent(btn) {
    btn.parentElement.remove();
}

function addService() {
    var cnt = document.querySelectorAll('.service-group').length;
    var html = `
        <div class="service-group" style="border: 1px solid #ddd; padding: 16px; border-radius: 7px; margin-bottom: 10px;">
            <label>Service Title:</label>
            <input type="text" name="services[`+cnt+`][title]">
            <label>Description:</label>
            <textarea name="services[`+cnt+`][description]" rows="2"></textarea>
            <label>Image Path:</label>
            <input type="text" name="services[`+cnt+`][image]">
            <button type="button" onclick="removeService(this)">Remove</button>
        </div>
    `;
    document.getElementById('services-list').insertAdjacentHTML('beforeend', html);
}

function removeService(btn) {
    btn.parentElement.remove();
}

function addHighlight() {
    var cnt = document.querySelectorAll('.highlight-group').length;
    var html = `
        <div class="highlight-group" style="border: 1px solid #ddd; padding: 16px; border-radius: 7px; margin-bottom: 10px;">
            <label>Title:</label>
            <input type="text" name="index_highlights[`+cnt+`][title]">
            <label>Description (can use HTML):</label>
            <textarea name="index_highlights[`+cnt+`][description]" rows="2"></textarea>
            <label>Image Path:</label>
            <input type="text" name="index_highlights[`+cnt+`][image]">
            <button type="button" onclick="removeHighlight(this)">Remove</button>
        </div>
    `;
    document.getElementById('highlights-list').insertAdjacentHTML('beforeend', html);
}

function removeHighlight(btn) {
    btn.parentElement.remove();
}

function addWhyChooseItem() {
    var html = `<input type="text" name="index_why_choose_items[]" value="" style="margin-bottom: 5px;">`;
    document.getElementById('why-choose-list').insertAdjacentHTML('beforeend', html);
}
</script>
</body>
</html>
