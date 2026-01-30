<?php
require_once 'header.php';
require_once 'config.php';

try {
    if (!isset($_SESSION['user_id']) || !isset($_GET['quiz_id'])) {
        $_SESSION['error'] = "Please log in or select a valid quiz.";
        header("Location: login.php");
        exit;
    }

    $quiz_id = filter_input(INPUT_GET, 'quiz_id', FILTER_VALIDATE_INT);
    $user_id = $_SESSION['user_id'];

    if (!$quiz_id) {
        $_SESSION['error'] = "Invalid quiz ID.";
        header("Location: course.php");
        exit;
    }

    $stmt = $pdo->prepare("SELECT * FROM quizzes WHERE id = ?");
    $stmt->execute([$quiz_id]);
    $quiz = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$quiz) {
        $_SESSION['error'] = "Quiz not found.";
        header("Location: course.php");
        exit;
    }

    
    $stmt = $pdo->prepare("SELECT * FROM quiz_results WHERE user_id = ? AND quiz_id = ? 
                           ORDER BY completed_at DESC LIMIT 1");
    $stmt->execute([$user_id, $quiz_id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$result) {
        $_SESSION['error'] = "No quiz results found.";
        header("Location: quiz.php?quiz_id=$quiz_id");
        exit;
    }

   
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM quiz_questions WHERE quiz_id = ?");
    $stmt->execute([$quiz_id]);
    $total_questions = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

   
    $passing_score = ceil($total_questions * 0.7);
    $passed = $result['score'] >= $passing_score;

    if ($passed && $result) {
        $stmt = $pdo->prepare("UPDATE enrollments SET progress = 100 
                               WHERE user_id = ? AND course_id = ?");
        $stmt->execute([$user_id, $quiz['course_id']]);

     
        $stmt = $pdo->prepare("SELECT * FROM certificates WHERE user_id = ? AND course_id = ?");
        $stmt->execute([$user_id, $quiz['course_id']]);
        $certificate = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$certificate) {
            $certificate_code = uniqid('CERT_');
            $stmt = $pdo->prepare("INSERT INTO certificates (user_id, course_id, certificate_code, issued_at) 
                                   VALUES (?, ?, ?, NOW())");
            $stmt->execute([$user_id, $quiz['course_id'], $certificate_code]);
        }
    }
} catch (PDOException $e) {
    $_SESSION['error'] = "Database error: " . $e->getMessage();
    header("Location: course.php");
    exit;
}
?>
<main>
    <section class="quiz-results">
        <h2>Quiz Results: <?php echo htmlspecialchars($quiz['title']); ?></h2>
        <?php if (isset($_SESSION['error'])): ?>
            <p class="error"><?php echo htmlspecialchars($_SESSION['error']); ?></p>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>
        <p>Your Score: <?php echo $result['score']; ?> / <?php echo $total_questions; ?></p>
        <p><?php echo $passed ? "Congratulations, you passed!" : "Sorry, you did not pass. Try again!"; ?></p>
        <?php if ($passed): ?>
            <a href="certificate.php?course_id=<?php echo $quiz['course_id']; ?>" class="cta-button">View Certificate</a>
        <?php else: ?>
            <a href="quiz.php?quiz_id=<?php echo $quiz_id; ?>" class="cta-button">Retake Quiz</a>
        <?php endif; ?>
        <a href="course.php?course_id=<?php echo $quiz['course_id']; ?>" class="cta-button">Back to Course</a>
    </section>
</main>
<?php require_once 'footer.php'; ?>