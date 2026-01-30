<?php
require_once 'header.php';
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$user_id = filter_var($_SESSION['user_id'], FILTER_VALIDATE_INT);
if ($user_id === false || $user_id <= 0) {
    header("Location: login.php");
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT id, username, email, first_name, last_name, password FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$user) {
        header("Location: login.php");
        exit;
    }
} catch (PDOException $e) {
    error_log("User query error: " . $e->getMessage());
    echo "<p>Sorry, an error occurred. Please try again later.</p>";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $error = "Invalid CSRF token.";
    } else {
        $first_name = filter_var(trim($_POST['first_name']), FILTER_SANITIZE_STRING);
        $last_name = filter_var(trim($_POST['last_name']), FILTER_SANITIZE_STRING);
        $password = $_POST['password'] ?? '';
        if (strlen($first_name) > 50 || strlen($last_name) > 50) {
            $error = "First name and last name must be 50 characters or less.";
        } elseif (!empty($password) && strlen($password) < 6) {
            $error = "Password must be at least 6 characters long.";
        } else {
            $password = !empty($password) ? password_hash($password, PASSWORD_DEFAULT) : $user['password'];
            try {
                $stmt = $pdo->prepare("UPDATE users SET first_name = ?, last_name = ?, password = ? WHERE id = ?");
                $stmt->execute([$first_name, $last_name, $password, $user_id]);
                $success = "Profile updated successfully!";
                $stmt = $pdo->prepare("SELECT id, username, email, first_name, last_name, password FROM users WHERE id = ?");
                $stmt->execute([$user_id]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
            } catch (PDOException $e) {
                error_log("Profile update error: " . $e->getMessage());
                $error = "Update failed. Please try again later.";
            }
        }
    }
}

try {
    $stmt = $pdo->prepare("SELECT c.id, c.title, c.description, c.image_url, e.progress 
                           FROM courses c 
                           JOIN enrollments e ON c.id = e.course_id 
                           WHERE e.user_id = ?");
    $stmt->execute([$user_id]);
    $enrolled_courses = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Enrolled courses query error: " . $e->getMessage());
    echo "<p>Sorry, an error occurred while fetching your courses. Please try again later.</p>";
    $enrolled_courses = [];
}

try {
    $stmt = $pdo->prepare("SELECT c.title, cert.certificate_code, cert.issued_at, cert.course_id 
                           FROM courses c 
                           JOIN certificates cert ON c.id = cert.course_id 
                           WHERE cert.user_id = ?");
    $stmt->execute([$user_id]);
    $certificates = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Certificates query error: " . $e->getMessage());
    $certificates = [];
}

try {
    $stmt = $pdo->prepare("SELECT q.title, qr.score, qr.completed_at, c.title AS course_title 
                           FROM quiz_results qr 
                           JOIN quizzes q ON qr.quiz_id = q.id 
                           JOIN courses c ON q.course_id = c.id 
                           WHERE qr.user_id = ? 
                           ORDER BY qr.completed_at DESC LIMIT 1");
    $stmt->execute([$user_id]);
    $recent_activity = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Recent activity query error: " . $e->getMessage());
    $recent_activity = null;
}

$course_images = [
    'Web Development Basics' => 'img/web_dev.jpg',
    'Data Analysis with Python' => 'img/data_analysis.jpg',
    'Digital Marketing Essentials' => 'img/digital_marketing.jpg',
    'Graphic Design Fundamentals' => 'img/graphic_design.jpg',
    'Introduction to AI' => 'img/intro_ai.jpg'
];

?>
<main>
    <section class="profile-header">
        <div class="profile-avatar">
            <img src="img/prof.jpg" alt="User Avatar">
        </div>
        <div class="profile-summary">
            <h2>Welcome back, <?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?>!</h2>
            <p>Keep learning and earning new skills!</p>
        </div>
    </section>

    <section class="profile-details">
        <h3>Profile Settings</h3>
        <div class="auth-form">
            <?php if (isset($success)): ?>
                <p class="success"><?php echo htmlspecialchars($success); ?></p>
            <?php endif; ?>
            <?php if (isset($error)): ?>
                <p class="error"><?php echo htmlspecialchars($error); ?></p>
            <?php endif; ?>
            <form method="POST" onsubmit="return validateProfile()">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                <label for="username">Username:</label>
                <input type="text" id="username" value="<?php echo htmlspecialchars($user['username']); ?>" disabled>
                <label for="email">Email:</label>
                <input type="email" id="email" value="<?php echo htmlspecialchars($user['email']); ?>" disabled>
                <label for="first_name">First Name:</label>
                <input type="text" id="first_name" name="first_name" value="<?php echo htmlspecialchars($user['first_name']); ?>" required>
                <label for="last_name">Last Name:</label>
                <input type="text" id="last_name" name="last_name" value="<?php echo htmlspecialchars($user['last_name']); ?>" required>
                <label for="password">New Password (leave blank to keep current):</label>
                <input type="password" id="password" name="password">
                <button type="submit">Update Profile</button>
            </form>
        </div>
    </section>

    <section class="profile-courses">
        <h3>Your Learning Journey</h3>
        <?php if (empty($enrolled_courses)): ?>
            <p>You have not started any courses yet.</p>
            <a href="course.php" class="cta-button">Browse Courses</a>
        <?php else: ?>
            <div class="course-grid">
                <?php foreach ($enrolled_courses as $course): ?>
                    <?php 
                    $image_url = isset($course_images[$course['title']]) 
                        ? htmlspecialchars($course_images[$course['title']]) 
                        : (!empty($course['image_url']) && filter_var($course['image_url'], FILTER_VALIDATE_URL) 
                            ? htmlspecialchars($course['image_url']) 
                            : htmlspecialchars($default_image));
                    $progress = filter_var($course['progress'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 0, 'max_range' => 100]]);
                    $progress = $progress !== false ? $progress : 0;
                    ?>
                    <div class="course-card">
                        <img src="<?php echo $image_url; ?>" alt="<?php echo htmlspecialchars($course['title']); ?>" class="course-image">
                        <h4><?php echo htmlspecialchars($course['title']); ?></h4>
                        <p><?php echo htmlspecialchars($course['description']); ?></p>
                        <div class="progress-bar">
                            <div class="progress" style="width: <?php echo $progress; ?>%;"></div>
                        </div>
                        <p>Progress: <?php echo $progress; ?>%</p>
                        <a href="learn.php?course_id=<?php echo htmlspecialchars($course['id']); ?>" class="cta-button">Continue Learning</a>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>

    <section class="profile-achievements">
        <h3>Your Achievements</h3>
        <?php if (empty($certificates)): ?>
            <p>You have not earned any certificates yet.</p>
            <a href="course.php" class="cta-button">Explore Courses</a>
        <?php else: ?>
            <div class="certificate-grid">
                <?php foreach ($certificates as $certificate): ?>
                    <div class="certificate-card">
                        <h4><?php echo htmlspecialchars($certificate['title']); ?></h4>
                        <p><strong>Certificate Code:</strong> <?php echo htmlspecialchars($certificate['certificate_code']); ?></p>
                        <p><strong>Issued On:</strong> <?php echo date('F d, Y', strtotime($certificate['issued_at'])); ?></p>
                        <a href="certificate.php?course_id=<?php echo htmlspecialchars($certificate['course_id']); ?>" class="cta-button">View Certificate</a>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>

    <section class="profile-activity">
        <h3>Recent Activity</h3>
        <?php if ($recent_activity): ?>
            <div class="activity-card">
                <p><strong>Last Quiz:</strong> <?php echo htmlspecialchars($recent_activity['title']); ?> (<?php echo htmlspecialchars($recent_activity['course_title']); ?>)</p>
                <p><strong>Score:</strong> <?php echo htmlspecialchars($recent_activity['score']); ?></p>
                <p><strong>Date:</strong> <?php echo date('F d, Y, H:i', strtotime($recent_activity['completed_at'])); ?></p>
            </div>
        <?php else: ?>
            <p>No recent activity to display.</p>
        <?php endif; ?>
    </section>
</main>

<style>
.profile-header {
    max-width: 1200px;
    margin: 2rem auto;
    padding: 2rem;
    display: flex;
    align-items: center;
    gap: 2rem;
    background-color: var(--background-offwhite);
    border-radius: 10px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

.profile-avatar img {
    width: 100px;
    height: 100px;
    border-radius: 50%;
    border: 2px solid var(--primary-teal);
}

.profile-summary h2 {
    color: var(--primary-teal);
    margin-bottom: 0.5rem;
}

.profile-summary p {
    color: var(--text-dark-teal);
}

.profile-details, .profile-courses, .profile-achievements, .profile-activity {
    max-width: 1200px;
    margin: 2rem auto;
    padding: 2rem;
}

.profile-details h3, .profile-courses h3, .profile-achievements h3, .profile-activity h3 {
    color: var(--primary-teal);
    margin-bottom: 1.5rem;
}

.course-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1.5rem;
    margin-top: 1rem;
}

.course-card {
    background-color: var(--background-offwhite);
    padding: 1rem;
    border-radius: 10px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.course-image {
    width: 100%;
    height: auto;
    border-radius: 5px;
}

.certificate-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1.5rem;
    margin-top: 1rem;
}

.certificate-card {
    background-color: var(--background-offwhite);
    padding: 1rem;
    border-radius: 10px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.progress-bar {
    width: 100%;
    height: 10px;
    background-color: var(--muted-aqua);
    border-radius: 5px;
    margin: 0.5rem 0;
}

.progress {
    height: 100%;
    background-color: var(--vibrant-teal);
    border-radius: 5px;
    transition: width 0.3s ease;
}

.activity-card {
    background-color: var(--background-offwhite);
    padding: 1.5rem;
    border-radius: 10px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.activity-card p {
    margin-bottom: 0.5rem;
    color: var(--text-dark-teal);
}

.auth-form .success {
    color: green;
    margin-bottom: 1rem;
}

.auth-form .error {
    color: red;
    margin-bottom: 1rem;
}

@media (max-width: 768px) {
    .profile-header {
        flex-direction: column;
        text-align: center;
    }

    .profile-avatar img {
        margin-bottom: 1rem;
    }
}


<script>
function validateProfile() {
    let password = document.getElementById('password').value;
    if (password && password.length < 6) {
        alert('Password must be at least 6 characters long.');
        return false;
    }
    return true;
}
</script>
<?php require_once 'footer.php'; ?>