<?php
require_once 'header.php';
require_once 'config.php';


$course_images = [
    1 => 'img/web.jpg', 
    2 => 'img/python.jpg',       
    3 => 'img/digital.jpg',    
    4 => 'img/graphic.jpg',       
    5 => 'img/ai.jpg'             
];

if (!isset($_GET['id'])) {
    header("Location: course.php"); 
    exit;
}

$course_id = filter_var($_GET['id'], FILTER_VALIDATE_INT);
if ($course_id === false) {
    header("Location: course.php");
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT * FROM courses WHERE id = ?");
    $stmt->execute([$course_id]);
    $course = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$course) {
        header("Location: course.php");
        exit;
    }

    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_SESSION['user_id'])) {
        $user_id = $_SESSION['user_id'];
        $stmt = $pdo->prepare("INSERT INTO enrollments (user_id, course_id) VALUES (?, ?)");
        try {
            $stmt->execute([$user_id, $course_id]);
            $success = "Successfully enrolled!";
        } catch (PDOException $e) {
            $error = "Enrollment failed: " . htmlspecialchars($e->getMessage());
        }
    }
} catch (PDOException $e) {
    echo "<p>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
    exit;
}
?>
<main>
    <section class="course-details">
        <h2><?php echo htmlspecialchars($course['title']); ?></h2>
        <?php
        
        $image_url = isset($course_images[$course['id']]) 
            ? htmlspecialchars($course_images[$course['id']]) 
            : (!empty($course['image_url']) ? htmlspecialchars($course['image_url']) : 'https://via.placeholder.com/300x200?text=No+Image');
        ?>
        <img src="<?php echo $image_url; ?>" alt="<?php echo htmlspecialchars($course['title']); ?>" class="course-image">
        <p><?php echo htmlspecialchars($course['description']); ?></p>
        <p><strong>Duration:</strong> <?php echo htmlspecialchars($course['duration']); ?></p>
        <?php if (isset($_SESSION['user_id'])): ?>
            <?php
            $stmt = $pdo->prepare("SELECT * FROM enrollments WHERE user_id = ? AND course_id = ?");
            $stmt->execute([$_SESSION['user_id'], $course_id]);
            $enrolled = $stmt->fetch(PDO::FETCH_ASSOC);
            ?>
            <?php if ($enrolled): ?>
                <p>You are enrolled in this course.</p>
                <a href="learn.php?course_id=<?php echo htmlspecialchars($course_id); ?>" class="cta-button">Start Learning</a>
            <?php else: ?>
                <form method="POST">
                    <button type="submit" class="cta-button">Enroll Now</button>
                </form>
                <?php if (isset($success)): ?>
                    <p class="success"><?php echo htmlspecialchars($success); ?></p>
                <?php endif; ?>
                <?php if (isset($error)): ?>
                    <p class="error"><?php echo htmlspecialchars($error); ?></p>
                <?php endif; ?>
            <?php endif; ?>
        <?php else: ?>
            <p><a href="login.php">Login</a> to enroll in this course.</p>
        <?php endif; ?>
    </section>
</main>
<?php require_once 'footer.php'; ?>