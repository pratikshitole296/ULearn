<?php
require_once 'header.php';
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $message = $_POST['message'];
    
    $success = "Thank you for your message! We'll get back to you soon.";
}
?>
<main>
    <section class="auth-form">
        <h2>Contact Us</h2>
        <?php if (isset($success)): ?>
            <p class="success"><?php echo $success; ?></p>
        <?php endif; ?>
        <form method="POST" onsubmit="return validateContact()">
            <label for="name">Name:</label>
            <input type="text" id="name" name="name" required>
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" required>
            <label for="message">Message:</label>
            <textarea id="message" name="message" required></textarea>
            <button type="submit">Send Message</button>
        </form>
    </section>
</main>
<script>
function validateContact() {
    let message = document.getElementById('message').value;
    if (message.length < 10) {
        alert('Message must be at least 10 characters long.');
        return false;
    }
    return true;
}
</script>
<?php require_once 'footer.php'; ?>