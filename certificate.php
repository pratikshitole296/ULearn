<?php
require_once 'header.php';
require_once 'config.php';


if (!isset($_SESSION['user_id']) || !isset($_GET['course_id']) || !filter_var($_GET['course_id'], FILTER_VALIDATE_INT)) {
    header("Location: login.php");
    exit;
}

$course_id = (int)$_GET['course_id'];
$user_id = $_SESSION['user_id'];


$stmt = $pdo->prepare("SELECT c.*, cert.certificate_code, cert.issued_at 
                      FROM courses c 
                      JOIN certificates cert ON c.id = cert.course_id 
                      WHERE cert.user_id = ? AND cert.course_id = ?");
$stmt->execute([$user_id, $course_id]);
$certificate = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$certificate) {
    $error = "No certificate found for this course. Please complete the quiz to earn a certificate.";
} else {
  
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        $error = "User data not found. Please contact support.";
    }
}
?>
<main>
    <section class="certificate">
        <h2>Certificate of Completion</h2>
        <?php if (isset($error)): ?>
            <p class="error"><?php echo htmlspecialchars($error); ?></p>
            <a href="my_courses.php" class="cta-button">Back to My Courses</a>
        <?php else: ?>
            <div class="certificate-content">
                <p>This certifies that</p>
                <h3><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></h3>
                <p>has successfully completed the course</p>
                <h4><?php echo htmlspecialchars($certificate['title']); ?></h4>
                <p>Certificate Code: <?php echo htmlspecialchars($certificate['certificate_code']); ?></p>
                <p>Issued on: <?php echo date('F d, Y', strtotime($certificate['issued_at'])); ?></p>
            </div>
            <a href="#" class="cta-button" onclick="window.print()">Download as PDF</a>
        <?php endif; ?>
    </section>
</main>
<?php require_once 'footer.php'; ?>