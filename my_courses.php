<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'header.php';
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}


$course_images = [
    1 => 'img/web.jpg',
    2 => 'img/python.jpg',
    3 => 'img/digital.jpg',
    4 => 'img/graphic.jpg',
    5 => 'img/ai.jpg'
];

$user_id = filter_var($_SESSION['user_id'], FILTER_VALIDATE_INT);
if ($user_id === false) {
    header("Location: login.php");
    exit;
}

try {
    
    if (!$pdo) {
        throw new PDOException("Database connection failed");
    }
    $stmt = $pdo->prepare("SELECT c.* FROM courses c JOIN enrollments e ON c.id = e.course_id WHERE e.user_id = ?");
    $stmt->execute([$user_id]);
    $enrolled_courses = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "<p>Error retrieving courses: " . htmlspecialchars($e->getMessage()) . "</p>";
    exit;
}
?>
<main>
    <section class="courses">
        <h2>My Courses</h2>
        <?php if (empty($enrolled_courses)): ?>
            <p>You are not enrolled in any courses.</p>
            <a href="course.php" class="cta-button">Browse Courses</a>
        <?php else: ?>
            <div class="course-grid">
                <?php foreach ($enrolled_courses as $course): ?>
                    <div class="course-card">
                        <?php
                        $image_url = isset($course_images[$course['id']])
                            ? htmlspecialchars($course_images[$course['id']])
                            : (!empty($course['image_url']) ? htmlspecialchars($course['image_url']) : 'https://via.placeholder.com/300x200?text=No+Image');
                        ?>
                        <img src="<?php echo $image_url; ?>" alt="<?php echo htmlspecialchars($course['title']); ?>" class="course-image">
                        <h4><?php echo htmlspecialchars($course['title']); ?></h4>
                        <p><?php echo htmlspecialchars($course['description']); ?></p>
                        <a href="learn.php?course_id=<?php echo htmlspecialchars($course['id']); ?>" class="cta-button">Continue Learning</a>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>
</main>
<script>
document.addEventListener('DOMContentLoaded', () => {
    
    const courses = document.querySelector('.courses');
    if (courses) {
        courses.style.opacity = '0';
        courses.style.transition = 'opacity 1s ease-in-out';
        setTimeout(() => courses.style.opacity = '1', 100);
    }

   
    const cards = document.querySelectorAll('.course-card');
    cards.forEach((card, index) => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(20px)';
        card.style.transition = 'opacity 0.5s ease-in-out, transform 0.5s ease-in-out';
        setTimeout(() => {
            card.style.opacity = '1';
            card.style.transform = 'translateY(0)';
        }, 200 + index * 100);
    });
});
</script>
<?php require_once 'footer.php'; ?>