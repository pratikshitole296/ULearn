<?php
$page_title = 'Courses';
require_once 'header.php';
require_once 'config.php';


$course_images = [
    1 => 'img/web.jpg',
    2 => 'img/python.jpg',
    3 => 'img/digital.jpg',
    4 => 'img/graphic.jpg',
    5 => 'img/ai.jpg'
];
?>
<main>
    <section class="courses">
        <h2>Available Courses</h2>
        <div class="course-grid">
            <?php
            try {
                
                if (!$pdo) {
                    echo "<p>Error: Database connection failed.</p>";
                } else {
                    $stmt = $pdo->query("SELECT * FROM courses");
                    $rowCount = $stmt->rowCount();
                    
                    echo "<!-- Debug: Found $rowCount courses -->";
                    if ($rowCount === 0) {
                        echo "<p>No courses available at the moment.</p>";
                    } else {
                        while ($course = $stmt->fetch(PDO::FETCH_ASSOC)) {
                            $image_url = isset($course_images[$course['id']]) 
                                ? htmlspecialchars($course_images[$course['id']]) 
                                : (!empty($course['image_url']) ? htmlspecialchars($course['image_url']) : 'https://via.placeholder.com/300x200?text=No+Image');
                            echo "<div class='course-card'>";
                            echo "<img src='$image_url' alt='" . htmlspecialchars($course['title']) . "' class='course-image'>";
                            echo "<h4>" . htmlspecialchars($course['title']) . "</h4>";
                            echo "<p>" . htmlspecialchars($course['description']) . "</p>";
                            echo "<a href='course_details.php?id=" . htmlspecialchars($course['id']) . "' class='cta-button'>View Details</a>";
                            echo "</div>";
                        }
                    }
                }
            } catch (PDOException $e) {
                echo "<p>Error retrieving courses: " . htmlspecialchars($e->getMessage()) . "</p>";
            }
            ?>
        </div>
    </section>
</main>
<script>
document.addEventListener('DOMContentLoaded', () => {

    const courseCards = document.querySelectorAll('.course-card');
    courseCards.forEach((card, index) => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(20px)';
        card.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
        setTimeout(() => {
            card.style.opacity = '1';
            card.style.transform = 'translateY(0)';
        }, 200 + index * 100);
    });
});
</script>
<?php require_once 'footer.php'; ?>