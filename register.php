<?php
require_once 'header.php';
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    
    $stmt = $pdo->prepare("INSERT INTO users (username, email, password, first_name, last_name) VALUES (?, ?, ?, ?, ?)");
    try {
        $stmt->execute([$username, $email, $password, $first_name, $last_name]);
        $_SESSION['user_id'] = $pdo->lastInsertId();
        header("Location: index.php");
        exit;
    } catch (PDOException $e) {
        $error = "Registration failed: " . $e->getMessage();
    }
}
?>
<main>
    <section class="auth-form">
        <h2>Register</h2>
        <?php if (isset($error)): ?>
            <p class="error"><?php echo $error; ?></p>
        <?php endif; ?>
        <form method="POST" onsubmit="return validateRegister()">
            <label for="username">Username:</label>
            <input type="text" id="username" name="username" required>
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" required>
            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required>
            <label for="first_name">First Name:</label>
            <input type="text" id="first_name" name="first_name" required>
            <label for="last_name">Last Name:</label>
            <input type="text" id="last_name" name="last_name" required>
            <button type="submit">Register</button>
        </form>
        <p>Already have an account? <a href="login.php">Login here</a>.</p>
    </section>
</main>
<script>
function validateRegister() {
    let password = document.getElementById('password').value;
    if (password.length < 6) {
        alert('Password must be at least 6 characters long.');
        return false;
    }
    return true;
}
</script>
<?php require_once 'footer.php'; ?>