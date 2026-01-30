<footer class="professional-footer">
        <div class="footer-container">
            <div class="footer-section footer-branding">
                <h2 class="footer-logo">LMS</h2>
                <p>Empowering lifelong learning through skill-based education.</p>
            </div>
            <div class="footer-section footer-nav">
                <h3>Explore</h3>
                <ul>
                    <li><a href="index.php">Home</a></li>
                    <li><a href="course.php">Courses</a></li>
                    <li><a href="about.php">About Us</a></li>
                    <li><a href="contact.php">Contact</a></li>
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <li><a href="my_courses.php">My Courses</a></li>
                        <li><a href="profile.php">Profile</a></li>
                    <?php endif; ?>
                </ul>
            </div>
            <div class="footer-section footer-contact">
                <h3>Contact</h3>
                <p><strong>Email:</strong> <a href="mailto:info@lmslearning.com">info@lmslearning.com</a></p>
                <p><strong>Phone:</strong> +1 (800) 555-1234</p>
                <p><strong>Address:</strong> 123 Learning St, Education City, EC 12345</p>
            </div>
            <div class="footer-section footer-social">
                <h3>Connect</h3>
                <div class="social-icons">
                    <a href="https://facebook.com" target="_blank" aria-label="Visit our Facebook page"><i class="fab fa-facebook-f"></i></a>
                    <a href="https://twitter.com" target="_blank" aria-label="Visit our Twitter page"><i class="fab fa-twitter"></i></a>
                    <a href="https://linkedin.com" target="_blank" aria-label="Visit our LinkedIn page"><i class="fab fa-linkedin-in"></i></a>
                    <a href="https://instagram.com" target="_blank" aria-label="Visit our Instagram page"><i class="fab fa-instagram"></i></a>
                </div>
            </div>
        </div>
        <div class="footer-legal">
            <ul>
                <li><a href="terms.php">Terms of Service</a></li>
                <li><a href="privacy.php">Privacy Policy</a></li>
            </ul>
            <p>&copy; <?php echo date('Y'); ?> LMS. All rights reserved.</p>
        </div>
    </footer>
</body>
</html>