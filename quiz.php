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

   
    $stmt = $pdo->prepare("SELECT * FROM quiz_results WHERE user_id = ? AND quiz_id = ?");
    $stmt->execute([$user_id, $quiz_id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($result) {
        header("Location: quiz_results.php?quiz_id=$quiz_id");
        exit;
    }

    $stmt = $pdo->prepare("SELECT * FROM quiz_questions WHERE quiz_id = ?");
    $stmt->execute([$quiz_id]);
    $questions = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($questions)) {
        $_SESSION['error'] = "No questions found for this quiz.";
        header("Location: course.php");
        exit;
    }

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $answers = $_POST['answers'] ?? [];
        $score = 0;

        foreach ($questions as $question) {
            if (isset($answers[$question['id']]) && $answers[$question['id']] == $question['correct_option']) {
                $score++;
            }
        }

        $stmt = $pdo->prepare("INSERT INTO quiz_results (user_id, quiz_id, score, completed_at) 
                               VALUES (?, ?, ?, NOW())");
        $stmt->execute([$user_id, $quiz_id, $score]);
        header("Location: quiz_results.php?quiz_id=$quiz_id");
        exit;
    }
} catch (PDOException $e) {
    $_SESSION['error'] = "Database error: " . $e->getMessage();
    header("Location: course.php");
    exit;
}
?>
<main>
    <section class="quiz-form">
        <h2><?php echo htmlspecialchars($quiz['title']); ?></h2>
        <?php if (isset($_SESSION['error'])): ?>
            <p class="error"><?php echo htmlspecialchars($_SESSION['error']); ?></p>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>
        <form method="POST">
            <?php foreach ($questions as $index => $question): ?>
                <div class="question">
                    <h4>Question <?php echo $index + 1; ?>: <?php echo htmlspecialchars($question['question_text']); ?></h4>
                    <?php
                    $options = [
                        'A' => $question['option_a'],
                        'B' => $question['option_b'],
                        'C' => $question['option_c'],
                        'D' => $question['option_d']
                    ];
                    foreach ($options as $key => $option):
                        if (!empty($option)): ?>
                            <label>
                                <input type="radio" name="answers[<?php echo $question['id']; ?>]" 
                                       value="<?php echo $key; ?>" required>
                                <?php echo htmlspecialchars($option); ?>
                            </label><br>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            <?php endforeach; ?>
            <button type="submit" class="cta-button">Submit Quiz</button>
        </form>
    </section>
</main>
<?php require_once 'footer.php'; ?>