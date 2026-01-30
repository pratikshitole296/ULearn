-- Create the database with proper character set and collation
CREATE DATABASE lms CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE lms;

-- Users table
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    first_name VARCHAR(50),
    last_name VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Courses table
CREATE TABLE courses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(100) NOT NULL,
    description TEXT,
    duration VARCHAR(50),
    image_url VARCHAR(255),
    video_url VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Enrollments table with progress column included
CREATE TABLE enrollments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    course_id INT,
    enrolled_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    progress INT DEFAULT 0 CHECK (progress >= 0 AND progress <= 100),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE
) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Quizzes table
CREATE TABLE quizzes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    course_id INT,
    title VARCHAR(100) NOT NULL,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE
) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Quiz Questions table
CREATE TABLE quiz_questions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    quiz_id INT,
    question_text TEXT NOT NULL,
    option_a VARCHAR(255),
    option_b VARCHAR(255),
    option_c VARCHAR(255),
    option_d VARCHAR(255),
    correct_option CHAR(1),
    FOREIGN KEY (quiz_id) REFERENCES quizzes(id) ON DELETE CASCADE
) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Quiz Results table
CREATE TABLE quiz_results (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    quiz_id INT,
    score INT,
    completed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (quiz_id) REFERENCES quizzes(id) ON DELETE CASCADE
) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Certificates table
CREATE TABLE certificates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    course_id INT,
    issued_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    certificate_code VARCHAR(50) UNIQUE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE
) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Add indexes for performance
CREATE INDEX idx_enrollments_user_id ON enrollments(user_id);
CREATE INDEX idx_quiz_results_user_id ON quiz_results(user_id);
CREATE INDEX idx_certificates_user_id ON certificates(user_id);
CREATE INDEX idx_quiz_questions_quiz_id ON quiz_questions(quiz_id);

-- Insert sample users (passwords are placeholders; replace with actual hashes)
INSERT INTO users (username, email, password, first_name, last_name, created_at) VALUES
('john_doe', 'john@example.com', '$2y$10$3z2v9z2X9z5v9z2X9z5v9u2X9z5v9z2X9z5v9z2X9z5v9z2X9z5v9', 'John', 'Doe', '2025-01-01 10:00:00'),
('jane_smith', 'jane@example.com', '$2y$10$3z2v9z2X9z5v9z2X9z5v9u2X9z5v9z2X9z5v9z2X9z5v9z2X9z5v9', 'Jane', 'Smith', '2025-01-02 12:00:00'),
('alice_jones', 'alice@example.com', '$2y$10$3z2v9z2X9z5v9z2X9z5v9u2X9z5v9z2X9z5v9z2X9z5v9z2X9z5v9', 'Alice', 'Jones', '2025-01-03 14:00:00');

-- Insert sample courses
INSERT INTO courses (title, description, duration, image_url, video_url, created_at) VALUES
('Web Development Basics', 'Learn HTML, CSS, and JavaScript to build modern websites.', '4 weeks', 'https://via.placeholder.com/300x200?text=Web+Development', 'https://www.youtube.com/embed/UB1O30fR-EE', '2025-01-01 08:00:00'),
('Data Analysis with Python', 'Master data analysis using Python, Pandas, and Matplotlib.', '6 weeks', 'https://via.placeholder.com/300x200?text=Data+Analysis', 'https://www.youtube.com/embed/r-uOL6HxL4c', '2025-01-02 08:00:00'),
('Digital Marketing Essentials', 'Explore SEO, social media, and content marketing strategies.', '3 weeks', 'https://via.placeholder.com/300x200?text=Digital+Marketing', 'https://www.youtube.com/embed/Wm5Oa_e-5X8', '2025-01-03 08:00:00'),
('Graphic Design Fundamentals', 'Create stunning visuals using Adobe Photoshop and Illustrator.', '5 weeks', 'https://via.placeholder.com/300x200?text=Graphic+Design', 'https://www.youtube.com/embed/0p9M5r_0rI', '2025-01-04 08:00:00'),
('Introduction to AI', 'Understand the basics of artificial intelligence and machine learning.', '4 weeks', 'https://via.placeholder.com/300x200?text=AI+Intro', 'https://www.youtube.com/embed/JMUxmLyrhSk', '2025-01-05 08:00:00');

-- Insert sample enrollments
INSERT INTO enrollments (user_id, course_id, enrolled_at, progress) VALUES
(1, 1, '2025-02-01 09:00:00', 75), -- John enrolled in Web Development Basics
(1, 2, '2025-02-05 09:00:00', 50), -- John enrolled in Data Analysis with Python
(1, 3, '2025-02-10 09:00:00', 20), -- John enrolled in Digital Marketing Essentials
(2, 4, '2025-02-02 09:00:00', 90), -- Jane enrolled in Graphic Design Fundamentals
(2, 5, '2025-02-15 09:00:00', 30), -- Jane enrolled in Introduction to AI
(3, 1, '2025-02-03 09:00:00', 60), -- Alice enrolled in Web Development Basics
(3, 5, '2025-02-20 09:00:00', 45); -- Alice enrolled in Introduction to AI

-- Insert quizzes for each course
INSERT INTO quizzes (course_id, title) VALUES
(1, 'Web Development Basics Quiz'),
(2, 'Data Analysis with Python Quiz'),
(3, 'Digital Marketing Essentials Quiz'),
(4, 'Graphic Design Fundamentals Quiz'),
(5, 'Introduction to AI Quiz');

-- Insert 10 quiz questions for Web Development Basics (quiz_id = 1)
INSERT INTO quiz_questions (quiz_id, question_text, option_a, option_b, option_c, option_d, correct_option) VALUES
(1, 'What does HTML stand for?', 'Hyper Text Markup Language', 'High Text Machine Language', 'Hyper Transfer Markup Language', 'Home Tool Markup Language', 'A'),
(1, 'Which CSS property controls text size?', 'font-size', 'text-size', 'font-style', 'text-font', 'A'),
(1, 'What is the correct HTML element for inserting a line break?', '<br>', '<break>', '<lb>', '<newline>', 'A'),
(1, 'In JavaScript, which keyword is used to declare a variable?', 'var', 'int', 'string', 'let', 'D'),
(1, 'Which HTML attribute specifies an alternate text for an image?', 'alt', 'src', 'title', 'href', 'A'),
(1, 'What does CSS stand for?', 'Cascading Style Sheets', 'Creative Style System', 'Computer Style Sheets', 'Colorful Style System', 'A'),
(1, 'Which JavaScript method is used to add an element to an array?', 'push()', 'add()', 'append()', 'insert()', 'A'),
(1, 'In CSS, how do you select an element with id="demo"?', '#demo', '.demo', 'demo', '*demo', 'A'),
(1, 'Which HTML tag is used to define an unordered list?', '<ul>', '<ol>', '<li>', '<list>', 'A'),
(1, 'What is the default display value for a <div> element?', 'block', 'inline', 'flex', 'grid', 'A');

-- Insert 10 quiz questions for Data Analysis with Python (quiz_id = 2)
INSERT INTO quiz_questions (quiz_id, question_text, option_a, option_b, option_c, option_d, correct_option) VALUES
(2, 'Which Python library is used for data manipulation?', 'Pandas', 'NumPy', 'Matplotlib', 'Seaborn', 'A'),
(2, 'What function creates a DataFrame in Pandas?', 'pd.DataFrame()', 'pd.Series()', 'pd.Table()', 'pd.Data()', 'A'),
(2, 'Which Matplotlib function creates a line plot?', 'plt.plot()', 'plt.line()', 'plt.graph()', 'plt.draw()', 'A'),
(2, 'How do you select a column in a Pandas DataFrame?', 'df["column"]', 'df.column[]', 'df.get(column)', 'df.select(column)', 'A'),
(2, 'What is the output of len(df)?', 'Number of rows', 'Number of columns', 'Total elements', 'Index length', 'A'),
(2, 'Which function calculates the mean of a Pandas Series?', 'mean()', 'avg()', 'median()', 'sum()', 'A'),
(2, 'What does the groupby() function do in Pandas?', 'Groups data by column', 'Sorts data', 'Filters data', 'Joins tables', 'A'),
(2, 'Which library is used for numerical computations in Python?', 'NumPy', 'Pandas', 'Matplotlib', 'Scikit-learn', 'A'),
(2, 'How do you drop missing values in a DataFrame?', 'dropna()', 'remove_na()', 'drop_null()', 'clean()', 'A'),
(2, 'What does plt.show() do in Matplotlib?', 'Displays the plot', 'Saves the plot', 'Clears the plot', 'Updates the plot', 'A');

-- Insert 10 quiz questions for Digital Marketing Essentials (quiz_id = 3)
INSERT INTO quiz_questions (quiz_id, question_text, option_a, option_b, option_c, option_d, correct_option) VALUES
(3, 'What does SEO stand for?', 'Search Engine Optimization', 'Social Engine Optimization', 'Search Engine Operations', 'Social Engagement Optimization', 'A'),
(3, 'Which is a key component of content marketing?', 'Blog posts', 'Hardware upgrades', 'Database management', 'Network security', 'A'),
(3, 'What is the purpose of a call-to-action (CTA)?', 'Encourage user action', 'Display advertisements', 'Optimize search rankings', 'Track analytics', 'A'),
(3, 'Which platform is best for professional networking?', 'LinkedIn', 'Instagram', 'TikTok', 'Twitter', 'A'),
(3, 'What does PPC stand for?', 'Pay Per Click', 'Pay Per Conversion', 'Pay Per Campaign', 'Pay Per Channel', 'A'),
(3, 'What is a meta description used for?', 'SEO ranking', 'Social media sharing', 'Email marketing', 'Content creation', 'A'),
(3, 'Which metric measures website visitors?', 'Traffic', 'Conversion', 'Engagement', 'Retention', 'A'),
(3, 'What is the primary goal of email marketing?', 'Build customer relationships', 'Increase website traffic', 'Optimize search rankings', 'Create social media posts', 'A'),
(3, 'Which tool analyzes website performance?', 'Google Analytics', 'Photoshop', 'Canva', 'WordPress', 'A'),
(3, 'What does SMM stand for?', 'Social Media Marketing', 'Search Media Marketing', 'Social Media Management', 'Search Marketing Metrics', 'A');

-- Insert 10 quiz questions for Graphic Design Fundamentals (quiz_id = 4)
INSERT INTO quiz_questions (quiz_id, question_text, option_a, option_b, option_c, option_d, correct_option) VALUES
(4, 'What is the primary function of Adobe Photoshop?', 'Image editing', 'Vector graphics', '3D modeling', 'Web development', 'A'),
(4, 'Which Adobe tool is best for vector graphics?', 'Illustrator', 'Photoshop', 'InDesign', 'After Effects', 'A'),
(4, 'What does RGB stand for?', 'Red, Green, Blue', 'Red, Gray, Black', 'Raster, Graphics, Blend', 'Render, Gradient, Brightness', 'A'),
(4, 'Which principle balances elements in a design?', 'Balance', 'Contrast', 'Alignment', 'Proximity', 'A'),
(4, 'What is a vector graphic?', 'Scalable image', 'Pixel-based image', '3D model', 'Animated graphic', 'A'),
(4, 'What does the lasso tool do in Photoshop?', 'Selects irregular shapes', 'Draws straight lines', 'Fills colors', 'Crops images', 'A'),
(4, 'Which file format supports transparency?', 'PNG', 'JPEG', 'BMP', 'GIF', 'A'),
(4, 'What is typography in design?', 'Art of text arrangement', 'Color selection', 'Image editing', 'Layout structuring', 'A'),
(4, 'Which tool creates layouts for print media?', 'InDesign', 'Photoshop', 'Illustrator', 'Premiere Pro', 'A'),
(4, 'What does contrast achieve in design?', 'Visual interest', 'Uniformity', 'Alignment', 'Repetition', 'A');

-- Insert 10 quiz questions for Introduction to AI (quiz_id = 5)
INSERT INTO quiz_questions (quiz_id, question_text, option_a, option_b, option_c, option_d, correct_option) VALUES
(5, 'What does AI stand for?', 'Artificial Intelligence', 'Automated Intelligence', 'Advanced Integration', 'Algorithmic Inference', 'A'),
(5, 'Which is a type of machine learning?', 'Supervised Learning', 'Manual Learning', 'Static Learning', 'Dynamic Learning', 'A'),
(5, 'What is a neural network?', 'Model inspired by the brain', 'Database structure', 'Web framework', 'Hardware component', 'A'),
(5, 'What does NLP stand for?', 'Natural Language Processing', 'Neural Language Programming', 'Network Learning Protocol', 'Natural Logic Processing', 'A'),
(5, 'Which algorithm predicts numerical values?', 'Regression', 'Classification', 'Clustering', 'Association', 'A'),
(5, 'What is overfitting in machine learning?', 'Model too specific to training data', 'Model too general', 'Model with no errors', 'Model with high accuracy', 'A'),
(5, 'Which library is used for AI in Python?', 'TensorFlow', 'Pandas', 'Matplotlib', 'Seaborn', 'A'),
(5, 'What is the purpose of a training dataset?', 'Model learning', 'Model testing', 'Model deployment', 'Model validation', 'A'),
(5, 'What does CNN stand for?', 'Convolutional Neural Network', 'Cyclic Neural Network', 'Complex Neural Network', 'Central Neural Network', 'A'),
(5, 'What is reinforcement learning?', 'Learning through rewards', 'Learning through classification', 'Learning through clustering', 'Learning through regression', 'A');

-- Insert sample quiz results
INSERT INTO quiz_results (user_id, quiz_id, score, completed_at) VALUES
(1, 1, 80, '2025-03-01 10:00:00'), -- John scored 80 on Web Development Basics Quiz
(1, 2, 65, '2025-03-05 10:00:00'), -- John scored 65 on Data Analysis with Python Quiz
(1, 3, 70, '2025-03-10 10:00:00'), -- John scored 70 on Digital Marketing Essentials Quiz
(2, 4, 90, '2025-03-02 10:00:00'), -- Jane scored 90 on Graphic Design Fundamentals Quiz
(2, 5, 55, '2025-03-15 10:00:00'), -- Jane scored 55 on Introduction to AI Quiz
(3, 1, 85, '2025-03-03 10:00:00'), -- Alice scored 85 on Web Development Basics Quiz
(3, 5, 60, '2025-03-20 10:00:00'); -- Alice scored 60 on Introduction to AI Quiz

-- Insert sample certificates
INSERT INTO certificates (user_id, course_id, issued_at, certificate_code) VALUES
(1, 1, '2025-03-01 12:00:00', 'CERT-WEBDEV-001'), -- John earned a certificate for Web Development Basics
(2, 4, '2025-03-02 12:00:00', 'CERT-GRAPHIC-001'), -- Jane earned a certificate for Graphic Design Fundamentals
(3, 1, '2025-03-03 12:00:00', 'CERT-WEBDEV-002'); -- Alice earned a certificate for Web Development Basics