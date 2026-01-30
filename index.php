<?php
$page_title = 'Home';
require_once 'header.php';
require_once 'config.php';

// Define image mappings for specific courses
$courses = [
    1 => 'img/web.jpg',         // Web Development Basics
    2 => 'img/python.jpg',      // Data Analysis with Python
    3 => 'img/digital.jpg',     // Digital Marketing Essentials
    4 => 'img/graphic.jpg',     // Graphic Design Fundamentals
    5 => 'img/ai.jpg'           // Introduction to AI
];
?>
<main>
    <div class="blob-container">
        <div class="blob"></div>
        <div class="blob"></div>
        <div class="blob"></div>
    </div>
    <section class="hero">
        <h2>Learn More Courses</h2>
        <p>Master new skills with our latest, engaging courses.</p>
        <a href="course.php" class="cta-button">Explore Courses</a>
    </section>
    <section class="featured-courses">
        <h3>Featured Courses</h3>
        <div class="course-grid">
            <?php
            try {
                $stmt = $pdo->query("SELECT * FROM courses LIMIT 3");
                if ($stmt->rowCount() === 0) {
                    echo "<p>No featured courses available at the moment.</p>";
                } else {
                    while ($course = $stmt->fetch(PDO::FETCH_ASSOC)) {
                        $image_url = isset($courses[$course['id']]) 
                            ? htmlspecialchars($courses[$course['id']]) 
                            : (!empty($course['image_url']) ? htmlspecialchars($course['image_url']) : 'https://via.placeholder.com/300x200?text=No+Image');
                        echo "<div class='course-card'>";
                        echo "<img src='$image_url' alt='" . htmlspecialchars($course['title']) . "' class='course-image'>";
                        echo "<h4>" . htmlspecialchars($course['title']) . "</h4>";
                        echo "<p>" . htmlspecialchars($course['description']) . "</p>";
                        echo "<a href='course_details.php?id=" . htmlspecialchars($course['id']) . "' class='cta-button'>Learn More</a>";
                        echo "</div>";
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
  
    const hero = document.querySelector('.hero');
    hero.style.opacity = '0';
    hero.style.transition = 'opacity 1s ease';
    setTimeout(() => hero.style.opacity = '1', 100);

    
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

  
    const blobs = document.querySelectorAll('.blob');
    blobs.forEach(blob => {
        const maxX = window.innerWidth - parseInt(getComputedStyle(blob).width);
        const maxY = window.innerHeight - parseInt(getComputedStyle(blob).height);
        blob.style.left = `${Math.random() * maxX}px`;
        blob.style.top = `${Math.random() * maxY}px`;
    });
});
</script>
<?php require_once 'footer.php'; ?>