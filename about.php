<?php
require_once __DIR__ . '/includes/functions.php';
$pageTitle = 'About';
include __DIR__ . '/includes/header.php';
?>

<div class="page-header">
    <div class="container">
        <h1>About Mehmaan Hub</h1>
        <p>Your trusted rental property platform in Pakistan</p>
    </div>
</div>

<section class="section">
    <div class="container">
        <div class="about-content">
            <div class="about-text">
                <h2>Our Story</h2>
                <p>Mehmaan Hub was created to simplify the rental property search in Pakistan. We connect property owners with potential tenants through a simple, transparent, and verified platform.</p>
                <p>Whether you're looking for an apartment in the city, a house in the suburbs, or a room for short-term stay, Mehmaan Hub has you covered. Our platform offers verified listings, direct owner contact, and a seamless booking process.</p>
            </div>
            <div class="about-image">
                <img src="https://images.pexels.com/photos/323780/pexels-photo-323780.jpeg?auto=compress&cs=tinysrgb&w=800" alt="About Mehmaan Hub">
            </div>
        </div>

        <div class="features-grid">
            <div class="feature-card">
                <div class="feature-icon"><i class="fas fa-shield-alt"></i></div>
                <h3>Verified Listings</h3>
                <p>All properties on our platform are verified to ensure authenticity and quality.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon"><i class="fas fa-user-check"></i></div>
                <h3>Direct Owner Contact</h3>
                <p>Connect directly with property owners without any middlemen or agents.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon"><i class="fas fa-search-dollar"></i></div>
                <h3>No Hidden Charges</h3>
                <p>Transparent pricing with no hidden fees. What you see is what you pay.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon"><i class="fas fa-headset"></i></div>
                <h3>24/7 Support</h3>
                <p>Our support team is always available to help you with any questions.</p>
            </div>
        </div>
    </div>
</section>
<?php include __DIR__ . '/includes/footer.php'; ?>
