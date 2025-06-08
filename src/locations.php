<?php

require_once 'config/database.php';
require_once 'includes/functions.php';

startSession();

$locations = [
    [
        'id' => 1,
        'name' => 'Crust Pizza Annandale',
        'address' => '123 Parramatta Road, Annandale NSW 2038',
        'phone' => '(02) 9560 1234',
        'hours' => [
            'Mon-Thu' => '11:00 AM - 10:00 PM',
            'Fri-Sat' => '11:00 AM - 11:00 PM',
            'Sunday' => '11:00 AM - 9:00 PM'
        ],
        'services' => ['Delivery', 'Pickup', 'Dine-in'],
        'lat' => -33.8816,
        'lng' => 151.1669,
        'manager' => 'Sarah Johnson',
        'features' => ['Parking Available', 'Wheelchair Accessible', 'WiFi']
    ],
    [
        'id' => 2,
        'name' => 'Crust Pizza Richmond',
        'address' => '456 Swan Street, Richmond VIC 3121',
        'phone' => '(03) 9428 5678',
        'hours' => [
            'Mon-Thu' => '11:00 AM - 10:00 PM',
            'Fri-Sat' => '11:00 AM - 11:00 PM',
            'Sunday' => '11:00 AM - 9:00 PM'
        ],
        'services' => ['Delivery', 'Pickup'],
        'lat' => -37.8197,
        'lng' => 144.9975,
        'manager' => 'Michael Chen',
        'features' => ['Express Pickup', 'Online Ordering', 'Contactless Delivery']
    ],
    [
        'id' => 3,
        'name' => 'Crust Pizza Surfers Paradise',
        'address' => '789 Gold Coast Highway, Surfers Paradise QLD 4217',
        'phone' => '(07) 5538 9012',
        'hours' => [
            'Mon-Thu' => '10:00 AM - 11:00 PM',
            'Fri-Sat' => '10:00 AM - 12:00 AM',
            'Sunday' => '10:00 AM - 10:00 PM'
        ],
        'services' => ['Delivery', 'Pickup', 'Dine-in'],
        'lat' => -28.0023,
        'lng' => 153.4145,
        'manager' => 'Emma Wilson',
        'features' => ['Beach Views', 'Outdoor Seating', 'Late Night Service']
    ],
    [
        'id' => 4,
        'name' => 'Crust Pizza Adelaide Central',
        'address' => '321 Rundle Mall, Adelaide SA 5000',
        'phone' => '(08) 8223 3456',
        'hours' => [
            'Mon-Thu' => '11:00 AM - 9:30 PM',
            'Fri-Sat' => '11:00 AM - 10:30 PM',
            'Sunday' => '11:00 AM - 9:00 PM'
        ],
        'services' => ['Delivery', 'Pickup', 'Dine-in'],
        'lat' => -34.9215,
        'lng' => 138.6007,
        'manager' => 'David Thompson',
        'features' => ['City Center Location', 'Quick Service', 'Student Discounts']
    ],
    [
        'id' => 5,
        'name' => 'Crust Pizza Fremantle',
        'address' => '654 South Terrace, Fremantle WA 6160',
        'phone' => '(08) 9335 7890',
        'hours' => [
            'Mon-Thu' => '11:00 AM - 10:00 PM',
            'Fri-Sat' => '11:00 AM - 11:00 PM',
            'Sunday' => '11:00 AM - 9:00 PM'
        ],
        'services' => ['Delivery', 'Pickup', 'Dine-in'],
        'lat' => -32.0569,
        'lng' => 115.7439,
        'manager' => 'Lisa Rodriguez',
        'features' => ['Historic Building', 'Craft Beer Available', 'Live Music Fridays']
    ],
    [
        'id' => 6,
        'name' => 'Crust Pizza Darwin Waterfront',
        'address' => '987 Mitchell Street, Darwin NT 0800',
        'phone' => '(08) 8941 2345',
        'hours' => [
            'Mon-Thu' => '11:00 AM - 10:00 PM',
            'Fri-Sat' => '11:00 AM - 11:00 PM',
            'Sunday' => '11:00 AM - 9:00 PM'
        ],
        'services' => ['Delivery', 'Pickup', 'Dine-in'],
        'lat' => -12.4634,
        'lng' => 130.8456,
        'manager' => 'James Aboriginal',
        'features' => ['Waterfront Views', 'Tropical Atmosphere', 'Extended Summer Hours']
    ]
];

// Get search parameters
$search_location = isset($_GET['location']) ? sanitizeInput($_GET['location']) : '';
$service_filter = isset($_GET['service']) ? sanitizeInput($_GET['service']) : '';

// Filter locations based on search
$filtered_locations = $locations;
if (!empty($search_location)) {
    $filtered_locations = array_filter($locations, function ($location) use ($search_location) {
        return stripos($location['name'], $search_location) !== false ||
            stripos($location['address'], $search_location) !== false;
    });
}

if (!empty($service_filter)) {
    $filtered_locations = array_filter($filtered_locations, function ($location) use ($service_filter) {
        return in_array($service_filter, $location['services']);
    });
}
?>

<?php include 'header.php'; ?>

<main style="margin-top: 70px; padding: 40px 20px;">
    <div class="container">
        <!-- Page Header -->
        <div class="page-header text-center" style="margin-bottom: 50px;">
            <h1 style="font-size: 3rem; margin-bottom: 20px; color: #333;">
                <i class="fas fa-map-marker-alt" style="color: #ff6b35;"></i>
                Our Locations
            </h1>
            <p style="font-size: 1.2rem; color: #666; max-width: 600px; margin: 0 auto;">
                Find your nearest Crust Pizza store across Australia. Fresh, gourmet pizzas delivered to your door or ready for pickup.
            </p>
        </div>

        <!-- Search and Filter -->
        <div class="location-filters" style="background: white; padding: 30px; border-radius: 15px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); margin-bottom: 40px;">
            <form method="GET" class="filter-form" style="display: grid; grid-template-columns: 1fr 1fr auto auto; gap: 20px; align-items: end;">
                <div class="form-group">
                    <label for="location" style="display: block; margin-bottom: 8px; font-weight: 600; color: #333;">
                        <i class="fas fa-search"></i> Search Location
                    </label>
                    <input type="text" id="location" name="location" placeholder="Enter city, suburb, or postcode..."
                        value="<?php echo htmlspecialchars($search_location); ?>"
                        class="form-control" style="width: 100%; padding: 12px; border: 2px solid #eee; border-radius: 8px;">
                </div>

                <div class="form-group">
                    <label for="service" style="display: block; margin-bottom: 8px; font-weight: 600; color: #333;">
                        <i class="fas fa-filter"></i> Service Type
                    </label>
                    <select id="service" name="service" class="form-control" style="width: 100%; padding: 12px; border: 2px solid #eee; border-radius: 8px;">
                        <option value="">All Services</option>
                        <option value="Delivery" <?php echo $service_filter === 'Delivery' ? 'selected' : ''; ?>>Delivery</option>
                        <option value="Pickup" <?php echo $service_filter === 'Pickup' ? 'selected' : ''; ?>>Pickup</option>
                        <option value="Dine-in" <?php echo $service_filter === 'Dine-in' ? 'selected' : ''; ?>>Dine-in</option>
                    </select>
                </div>

                <button type="submit" class="btn btn-primary" style="padding: 12px 30px; height: fit-content;">
                    <i class="fas fa-search"></i> Search
                </button>

                <a href="locations.php" class="btn btn-outline" style="padding: 12px 20px; height: fit-content;">
                    <i class="fas fa-times"></i> Clear
                </a>
            </form>
        </div>

        <!-- Flash Messages -->
        <?php displayFlashMessages(); ?>

        <!-- Store Count -->
        <div class="store-count" style="margin-bottom: 30px; text-align: center;">
            <p style="font-size: 1.1rem; color: #666;">
                <strong><?php echo count($filtered_locations); ?></strong> store<?php echo count($filtered_locations) !== 1 ? 's' : ''; ?> found
                <?php if (!empty($search_location) || !empty($service_filter)): ?>
                    matching your criteria
                <?php endif; ?>
            </p>
        </div>

        <!-- Locations Grid -->
        <?php if (empty($filtered_locations)): ?>
            <div class="no-results" style="text-align: center; padding: 60px 20px; background: #f8f9fa; border-radius: 15px;">
                <i class="fas fa-map-marker-alt" style="font-size: 4rem; color: #ddd; margin-bottom: 20px;"></i>
                <h3 style="color: #666; margin-bottom: 15px;">No stores found</h3>
                <p style="color: #888; margin-bottom: 25px;">Try adjusting your search criteria or browse all locations.</p>
                <a href="locations.php" class="btn btn-primary">View All Locations</a>
            </div>
        <?php else: ?>
            <div class="locations-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(400px, 1fr)); gap: 30px;">
                <?php foreach ($filtered_locations as $location): ?>
                    <div class="location-card" style="background: white; border-radius: 15px; overflow: hidden; box-shadow: 0 10px 30px rgba(0,0,0,0.1); transition: transform 0.3s ease, box-shadow 0.3s ease;">
                        <!-- Store Header -->
                        <div class="store-header" style="background: linear-gradient(135deg, #ff6b35, #f7931e); color: white; padding: 25px;">
                            <h3 style="margin: 0 0 10px 0; font-size: 1.4rem;">
                                <i class="fas fa-store"></i> <?php echo htmlspecialchars($location['name']); ?>
                            </h3>
                            <p style="margin: 0; opacity: 0.9; font-size: 1rem;">
                                <i class="fas fa-user-tie"></i> Manager: <?php echo htmlspecialchars($location['manager']); ?>
                            </p>
                        </div>

                        <!-- Store Details -->
                        <div class="store-details" style="padding: 25px;">
                            <!-- Address -->
                            <div class="detail-item" style="margin-bottom: 20px; display: flex; align-items: flex-start; gap: 12px;">
                                <i class="fas fa-map-marker-alt" style="color: #ff6b35; margin-top: 3px; font-size: 1.1rem;"></i>
                                <div>
                                    <strong style="display: block; margin-bottom: 5px; color: #333;">Address</strong>
                                    <span style="color: #666; line-height: 1.4;"><?php echo htmlspecialchars($location['address']); ?></span>
                                </div>
                            </div>

                            <!-- Phone -->
                            <div class="detail-item" style="margin-bottom: 20px; display: flex; align-items: center; gap: 12px;">
                                <i class="fas fa-phone" style="color: #ff6b35; font-size: 1.1rem;"></i>
                                <div>
                                    <strong style="display: block; margin-bottom: 5px; color: #333;">Phone</strong>
                                    <a href="tel:<?php echo str_replace(['(', ')', ' ', '-'], '', $location['phone']); ?>"
                                        style="color: #ff6b35; text-decoration: none; font-weight: 500;">
                                        <?php echo htmlspecialchars($location['phone']); ?>
                                    </a>
                                </div>
                            </div>

                            <!-- Hours -->
                            <div class="detail-item" style="margin-bottom: 20px;">
                                <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 10px;">
                                    <i class="fas fa-clock" style="color: #ff6b35; font-size: 1.1rem;"></i>
                                    <strong style="color: #333;">Opening Hours</strong>
                                </div>
                                <div style="margin-left: 24px;">
                                    <?php foreach ($location['hours'] as $days => $hours): ?>
                                        <div style="display: flex; justify-content: space-between; margin-bottom: 5px; font-size: 0.95rem;">
                                            <span style="color: #666;"><?php echo $days; ?></span>
                                            <span style="color: #333; font-weight: 500;"><?php echo $hours; ?></span>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>

                            <!-- Services -->
                            <div class="detail-item" style="margin-bottom: 20px;">
                                <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 10px;">
                                    <i class="fas fa-concierge-bell" style="color: #ff6b35; font-size: 1.1rem;"></i>
                                    <strong style="color: #333;">Services</strong>
                                </div>
                                <div style="margin-left: 24px; display: flex; flex-wrap: wrap; gap: 8px;">
                                    <?php foreach ($location['services'] as $service): ?>
                                        <span class="service-badge" style="background: #e8f5e8; color: #28a745; padding: 4px 12px; border-radius: 20px; font-size: 0.85rem; font-weight: 500;">
                                            <?php echo htmlspecialchars($service); ?>
                                        </span>
                                    <?php endforeach; ?>
                                </div>
                            </div>

                            <!-- Features -->
                            <?php if (!empty($location['features'])): ?>
                                <div class="detail-item" style="margin-bottom: 25px;">
                                    <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 10px;">
                                        <i class="fas fa-star" style="color: #ff6b35; font-size: 1.1rem;"></i>
                                        <strong style="color: #333;">Features</strong>
                                    </div>
                                    <div style="margin-left: 24px;">
                                        <?php foreach ($location['features'] as $feature): ?>
                                            <div style="margin-bottom: 5px; color: #666; font-size: 0.95rem;">
                                                <i class="fas fa-check" style="color: #28a745; margin-right: 8px; font-size: 0.8rem;"></i>
                                                <?php echo htmlspecialchars($feature); ?>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <!-- Action Buttons -->
                            <div class="store-actions" style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                                <a href="https://maps.google.com/?q=<?php echo urlencode($location['address']); ?>"
                                    target="_blank"
                                    class="btn btn-outline"
                                    style="text-align: center; padding: 12px; text-decoration: none; display: flex; align-items: center; justify-content: center; gap: 8px;">
                                    <i class="fas fa-directions"></i> Get Directions
                                </a>
                                <a href="menu.php"
                                    class="btn btn-primary"
                                    style="text-align: center; padding: 12px; text-decoration: none; display: flex; align-items: center; justify-content: center; gap: 8px;">
                                    <i class="fas fa-shopping-cart"></i> Order Now
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <!-- Additional Info Section -->
        <div class="additional-info" style="margin-top: 60px; background: linear-gradient(135deg, #f8f9fa, #e9ecef); padding: 50px 30px; border-radius: 20px; text-align: center;">
            <h2 style="color: #333; margin-bottom: 30px; font-size: 2.5rem;">
                <i class="fas fa-info-circle" style="color: #ff6b35;"></i>
                Store Information
            </h2>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 30px; margin-top: 40px;">
                <div class="info-card" style="background: white; padding: 30px; border-radius: 15px; box-shadow: 0 5px 15px rgba(0,0,0,0.1);">
                    <i class="fas fa-truck" style="font-size: 3rem; color: #ff6b35; margin-bottom: 20px;"></i>
                    <h4 style="margin-bottom: 15px; color: #333;">Free Delivery</h4>
                    <p style="color: #666; line-height: 1.6;">Free delivery on orders over $30 within our delivery zones. Fast and reliable service guaranteed.</p>
                </div>

                <div class="info-card" style="background: white; padding: 30px; border-radius: 15px; box-shadow: 0 5px 15px rgba(0,0,0,0.1);">
                    <i class="fas fa-clock" style="font-size: 3rem; color: #28a745; margin-bottom: 20px;"></i>
                    <h4 style="margin-bottom: 15px; color: #333;">Quick Pickup</h4>
                    <p style="color: #666; line-height: 1.6;">Order online and pickup in-store. Most orders ready in 15-20 minutes during peak times.</p>
                </div>

                <div class="info-card" style="background: white; padding: 30px; border-radius: 15px; box-shadow: 0 5px 15px rgba(0,0,0,0.1);">
                    <i class="fas fa-mobile-alt" style="font-size: 3rem; color: #6f42c1; margin-bottom: 20px;"></i>
                    <h4 style="margin-bottom: 15px; color: #333;">Order Tracking</h4>
                    <p style="color: #666; line-height: 1.6;">Track your order in real-time from preparation to delivery. Get SMS updates on your order status.</p>
                </div>
            </div>
        </div>

        <!-- Contact Section -->
        <div class="contact-section" style="margin-top: 50px; text-align: center; padding: 40px; background: white; border-radius: 15px; box-shadow: 0 10px 30px rgba(0,0,0,0.1);">
            <h3 style="color: #333; margin-bottom: 20px; font-size: 2rem;">
                <i class="fas fa-headset" style="color: #ff6b35;"></i>
                Need Help?
            </h3>
            <p style="color: #666; margin-bottom: 30px; font-size: 1.1rem;">
                Can't find what you're looking for? Our customer service team is here to help!
            </p>
            <div style="display: flex; justify-content: center; gap: 20px; flex-wrap: wrap;">
                <a href="tel:1300278787" class="btn btn-primary" style="padding: 15px 30px; text-decoration: none;">
                    <i class="fas fa-phone"></i> Call 1300 CRUST
                </a>
                <a href="mailto:info@crustpizza.com.au" class="btn btn-outline" style="padding: 15px 30px; text-decoration: none;">
                    <i class="fas fa-envelope"></i> Email Us
                </a>
            </div>
        </div>
    </div>
</main>

<?php include 'footer.php'; ?>

<script src="assets/js/main.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Add hover effects to location cards
        const locationCards = document.querySelectorAll('.location-card');
        locationCards.forEach(card => {
            card.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-5px)';
                this.style.boxShadow = '0 20px 40px rgba(0,0,0,0.15)';
            });

            card.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0)';
                this.style.boxShadow = '0 10px 30px rgba(0,0,0,0.1)';
            });
        });

        // Add smooth scrolling for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function(e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });

        // Update cart count on page load
        updateCartCount();
    });

    function updateCartCount() {
        const cart = JSON.parse(localStorage.getItem('crustPizzaCart') || '[]');
        const cartCount = cart.reduce((total, item) => total + (item.quantity || 1), 0);
        const cartCountElement = document.getElementById('cartCount');
        if (cartCountElement) {
            cartCountElement.textContent = cartCount;
        }
    }

    function toggleDropdown() {
        const dropdownMenu = document.getElementById('dropdownMenu');
        const isOpen = dropdownMenu.classList.toggle('show');
        document.querySelector('.dropdown-toggle').setAttribute('aria-expanded', isOpen);
    }

    function toggleNavMenu() {
        const navMenu = document.getElementById('navMenu');
        navMenu.classList.toggle('active');
    }

    // Close dropdown and nav menu when clicking outside
    document.addEventListener('click', function(event) {
        const dropdown = document.querySelector('.dropdown');
        const dropdownMenu = document.getElementById('dropdownMenu');
        const navMenu = document.getElementById('navMenu');
        const navToggle = document.querySelector('.nav-toggle');
        if (!dropdown.contains(event.target) && !navToggle.contains(event.target)) {
            dropdownMenu.classList.remove('show');
            navMenu.classList.remove('active');
            document.querySelector('.dropdown-toggle').setAttribute('aria-expanded', 'false');
        }
    });

    // Close dropdown and nav menu on Escape key
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            document.getElementById('dropdownMenu').classList.remove('show');
            document.getElementById('navMenu').classList.remove('active');
            document.querySelector('.dropdown-toggle').setAttribute('aria-expanded', 'false');
        }
    });
</script>

<style>
    /* Additional responsive styles */
    @media (max-width: 768px) {
        .filter-form {
            grid-template-columns: 1fr !important;
        }

        .locations-grid {
            grid-template-columns: 1fr !important;
        }

        .store-actions {
            grid-template-columns: 1fr !important;
        }

        .page-header h1 {
            font-size: 2rem !important;
        }

        .location-filters {
            padding: 20px !important;
        }
    }

    @media (max-width: 480px) {
        .container {
            padding: 0 15px;
        }

        .location-card .store-details {
            padding: 20px !important;
        }

        .additional-info {
            padding: 30px 20px !important;
        }
    }

    /* Smooth transitions */
    .location-card {
        transition: all 0.3s ease;
    }

    .btn {
        transition: all 0.2s ease;
    }

    .btn:hover {
        transform: translateY(-2px);
    }

    /* Service badge hover effect */
    .service-badge {
        transition: all 0.2s ease;
    }

    .service-badge:hover {
        background: #d4edda !important;
        transform: scale(1.05);
    }
</style>