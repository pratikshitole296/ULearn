<?php
require_once 'header.php';
require_once 'config.php';

if (!isset($_SESSION['user_id']) || !isset($_GET['course_id'])) {
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
$course_id = filter_var($_GET['course_id'], FILTER_VALIDATE_INT);

if ($user_id === false || $user_id <= 0 || $course_id === false || $course_id <= 0) {
    header("Location: login.php");
    exit;
}

try {
   
    $stmt = $pdo->prepare("SELECT * FROM enrollments WHERE user_id = ? AND course_id = ?");
    $stmt->execute([$user_id, $course_id]);
    $enrollment = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$enrollment) {
        header("Location: courses.php");
        exit;
    }

  
    $stmt = $pdo->prepare("SELECT id, title, description, image_url, video_url FROM courses WHERE id = ?");
    $stmt->execute([$course_id]);
    $course = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$course) {
        header("Location: courses.php");
        exit;
    }
} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    echo "<p>Sorry, an error occurred. Please try again later.</p>";
    exit;
}
?>
<main>
    <section class="course-content">
        <h2><?php echo htmlspecialchars($course['title']); ?></h2>
        <?php
        $image_url = isset($course_images[$course['id']]) 
            ? htmlspecialchars($course_images[$course['id']]) 
            : (!empty($course['image_url']) && filter_var($course['image_url'], FILTER_VALIDATE_URL) 
                ? htmlspecialchars($course['image_url']) 
                : 'https://via.placeholder.com/300x200?text=No+Image');
        ?>
        <img src="<?php echo $image_url; ?>" alt="<?php echo htmlspecialchars($course['title']); ?>" class="course-image">
        <p><?php echo htmlspecialchars($course['description']); ?></p>
        <div class="content">
            <h3>Course Video</h3>
            <?php
            $allowed_domains = ['https://www.youtube.com', 'https://player.vimeo.com'];
            $is_valid_video = false;
            if (!empty($course['video_url'])) {
                foreach ($allowed_domains as $domain) {
                    if (strpos($course['video_url'], $domain) === 0) {
                        $is_valid_video = true;
                        break;
                    }
                }
            }
            ?>
            <?php if ($is_valid_video): ?>
                <div class="video-container">
                    <iframe src="<?php echo htmlspecialchars($course['video_url']); ?>" title="Course Video: <?php echo htmlspecialchars($course['title']); ?>" frameborder="0" allowfullscreen></iframe>
                </div>
            <?php else: ?>
                <p>No video available for this course.</p>
            <?php endif; ?>
            <p>Watch the video above to start learning. Additional resources can be added here.</p>
        </div>
        <?php
        try {
            $stmt = $pdo->prepare("SELECT id, title FROM quizzes WHERE course_id = ?");
            $stmt->execute([$course_id]);
            $quizzes = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if ($quizzes): ?>
                <h3>Available Quizzes</h3>
                <ul>
                    <?php foreach ($quizzes as $quiz): ?>
                        <li><a href="quiz.php?quiz_id=<?php echo htmlspecialchars($quiz['id']); ?>" class="cta-button"><?php echo htmlspecialchars($quiz['title']); ?></a></li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <p>No quizzes available for this course.</p>
            <?php endif; ?>
        <?php } catch (PDOException $e) {
            error_log("Quiz retrieval error: " . $e->getMessage());
            echo "<p>Sorry, an error occurred while retrieving quizzes. Please try again later.</p>";
        } ?>
    </section>
</main>
<?php require_once 'footer.php'; ?>