<?php
require_once __DIR__ . '/includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');

    if (!$name || !$email || !$message) {
        flash('error', 'Please fill in all required fields.');
    } else {
        $stmt = db()->prepare('INSERT INTO contact_messages (name, email, subject, message) VALUES (?, ?, ?, ?)');
        $stmt->bind_param('ssss', $name, $email, $subject, $message);
        if ($stmt->execute()) {
            flash('success', 'Message sent! We will get back to you soon.');
        } else {
            flash('error', 'Failed to send message. Please try again.');
        }
    }
    redirect('/contact.php');
}

$pageTitle = 'Contact';
include __DIR__ . '/includes/header.php';
?>

<div class="page-header">
    <div class="container">
        <h1>Contact Us</h1>
        <p>Have questions? We're here to help</p>
    </div>
</div>

<section class="section">
    <div class="container">
        <div class="contact-layout">
            <div class="contact-info">
                <h2>Get in Touch</h2>
                <p>Whether you're looking for a rental or want to list your property, we're here to help.</p>
                <div class="contact-items">
                    <div class="contact-item">
                        <div class="contact-icon"><i class="fas fa-map-marker-alt"></i></div>
                        <div>
                            <strong>Address</strong>
                            <p>Main Boulevard, Lahore, Pakistan</p>
                        </div>
                    </div>
                    <div class="contact-item">
                        <div class="contact-icon"><i class="fas fa-phone"></i></div>
                        <div>
                            <strong>Phone</strong>
                            <p>+92 300 1234567</p>
                        </div>
                    </div>
                    <div class="contact-item">
                        <div class="contact-icon"><i class="fas fa-envelope"></i></div>
                        <div>
                            <strong>Email</strong>
                            <p>info@mehmaanhub.com</p>
                        </div>
                    </div>
                    <div class="contact-item">
                        <div class="contact-icon"><i class="fas fa-clock"></i></div>
                        <div>
                            <strong>Hours</strong>
                            <p>Monday - Saturday: 9AM - 7PM</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="contact-form-wrap">
                <form method="POST" class="contact-form">
                    <h3>Send a Message</h3>
                    <div class="form-group">
                        <label for="name">Name <span class="required">*</span></label>
                        <input type="text" id="name" name="name" required>
                    </div>
                    <div class="form-group">
                        <label for="email">Email <span class="required">*</span></label>
                        <input type="email" id="email" name="email" required>
                    </div>
                    <div class="form-group">
                        <label for="subject">Subject</label>
                        <input type="text" id="subject" name="subject">
                    </div>
                    <div class="form-group">
                        <label for="message">Message <span class="required">*</span></label>
                        <textarea id="message" name="message" rows="5" required></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary btn-block"><i class="fas fa-paper-plane"></i> Send Message</button>
                </form>
            </div>
        </div>
    </div>
</section>
<?php include __DIR__ . '/includes/footer.php'; ?>
