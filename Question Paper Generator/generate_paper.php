<?php
session_start();
require_once 'includes/db_connect.php';

// Check login
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    die("Access denied. Please login.");
}
if ($_SERVER["REQUEST_METHOD"] != "POST") {
    die("Invalid request method.");
}

$user_id = $_SESSION['user_id'];

// --- 1. Get Form Data ---
$title = $_POST['exam_title'] ?? 'Question Paper';
$subject = $_POST['subject'] ?? '';
$duration = $_POST['duration'] ?? 180;
$instructions = $_POST['instructions'] ?? 'All questions are compulsory.';

$num_mcq = (int)($_POST['num_mcq'] ?? 0);
$num_short = (int)($_POST['num_short'] ?? 0);
$num_long = (int)($_POST['num_long'] ?? 0);
$num_desc = (int)($_POST['num_desc'] ?? 0);

// --- 2. Calculate Total Marks ---
$total_marks = ($num_mcq * 1) + ($num_short * 2) + ($num_long * 5) + ($num_desc * 10);

// --- 3. Helper function to fetch questions ---
function fetchQuestions($conn, $user_id, $subject, $type, $marks, $limit) {
    if ($limit <= 0) {
        return [];
    }
    
    $sql = "SELECT * FROM question_bank 
            WHERE user_id = :user_id 
              AND subject = :subject 
              AND question_type = :type 
              AND marks = :marks
            ORDER BY RAND() 
            LIMIT :limit";
    
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->bindParam(':subject', $subject, PDO::PARAM_STR);
    $stmt->bindParam(':type', $type, PDO::PARAM_STR);
    $stmt->bindParam(':marks', $marks, PDO::PARAM_INT);
    $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
    
    $stmt->execute();
    return $stmt->fetchAll();
}

// --- 4. Fetch all question sets ---
try {
    $mcqs = fetchQuestions($conn, $user_id, $subject, 'MCQ', 1, $num_mcq);
    $short_answers = fetchQuestions($conn, $user_id, $subject, 'Short Answer', 2, $num_short);
    $long_answers = fetchQuestions($conn, $user_id, $subject, 'Long Answer', 5, $num_long);
    $descriptives = fetchQuestions($conn, $user_id, $subject, 'Descriptive', 10, $num_desc);
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}

// --- 5. !!! NEW: SAVE THE PAPER TO THE DATABASE !!! ---
try {
    // Start a transaction
    $conn->beginTransaction();

    // a. Insert the main paper details
    $sql_paper = "INSERT INTO generated_papers (user_id, title, subject, total_marks, duration_minutes, instructions)
                  VALUES (:user_id, :title, :subject, :total_marks, :duration, :instructions)";
    $stmt_paper = $conn->prepare($sql_paper);
    $stmt_paper->execute([
        ':user_id' => $user_id,
        ':title' => $title,
        ':subject' => $subject,
        ':total_marks' => $total_marks,
        ':duration' => $duration,
        ':instructions' => $instructions
    ]);

    // Get the ID of the paper we just created
    $new_paper_id = $conn->lastInsertId();

    // b. Insert all the questions
    $sql_q = "INSERT INTO generated_paper_questions (paper_id, question_type, question_text, marks, question_order)
              VALUES (:paper_id, :type, :text, :marks, :order)";
    $stmt_q = $conn->prepare($sql_q);

    $question_number = 1;
    
    // Combine all fetched questions into one array with type
    $all_questions = [
        'MCQ' => $mcqs,
        'Short Answer' => $short_answers,
        'Long Answer' => $long_answers,
        'Descriptive' => $descriptives
    ];

    foreach ($all_questions as $type => $questions) {
        foreach ($questions as $q) {
            $stmt_q->execute([
                ':paper_id' => $new_paper_id,
                ':type' => $type,
                ':text' => $q['question_text'],
                ':marks' => $q['marks'],
                ':order' => $question_number
            ]);
            $question_number++;
        }
    }

    // c. If everything worked, commit the changes
    $conn->commit();

} catch (PDOException $e) {
    // If anything failed, roll back the changes
    $conn->rollBack();
    die("Error saving paper: " . $e->getMessage());
}
// --- END OF NEW SAVE LOGIC ---


// --- 6. Helper function to render a section (for display) ---
function renderSection($title, $questions, $marks_per_q, &$q_index) {
    if (empty($questions)) {
        return; 
    }
    
    $total_marks = count($questions) * $marks_per_q;
    
    echo "<div class='section-header'>";
    echo "<h3>{$title}</h3>";
    echo "<span class='section-marks'>Total Marks: {$total_marks}</span>";
    echo "</div>";
    
    foreach ($questions as $q) {
        echo "<div class='question-item'>";
        echo "<p><strong>{$q_index}.</strong> " . nl2br(htmlspecialchars($q['question_text'])) . "</p>";
        echo "<span class='marks'>[{$q['marks']}]</span>";
        echo "</div>";
        $q_index++;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Printable: <?php echo htmlspecialchars($title); ?></title>
    <link rel="stylesheet" href="css/print_style.css">
    <style>
        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background-color: #f4f4f4;
            padding: 10px;
            margin-top: 25px;
            border-bottom: 2px solid #ddd;
        }
        .section-header h3 {
            margin: 0;
            font-size: 18px;
        }
        .section-header .section-marks {
            font-weight: bold;
            font-size: 16px;
        }
    </style>
</head>
<body>
    <div class="paper-container">
        <div class="paper-header">
            <h1><?php echo htmlspecialchars($title); ?></h1>
            <h2>Subject: <?php echo htmlspecialchars($subject); ?></h2>
            <div class="header-details">
                <span><strong>Total Marks:</strong> <?php echo htmlspecialchars($total_marks); ?></span>
                <span><strong>Duration:</strong> <?php echo htmlspecialchars($duration); ?> minutes</span>
            </div>
        </div>

        <?php if (!empty($instructions)): ?>
            <div class="instructions">
                <strong>Instructions:</strong>
                <p><?php echo nl2br(htmlspecialchars($instructions)); ?></p>
            </div>
        <?php endif; ?>

        <div class="question-list">
            <?php
            $question_number = 1;
            
            renderSection('Section A: Multiple Choice Questions', $mcqs, 1, $question_number);
            renderSection('Section B: Short Answer Questions', $short_answers, 2, $question_number);
            renderSection('Section C: Long Answer Questions', $long_answers, 5, $question_number);
            renderSection('Section D: Descriptive Questions', $descriptives, 10, $question_number);

            if ($question_number == 1) {
                echo "<p style='text-align: center; color: red;'>Could not find any questions matching your criteria. Please add more questions to your bank with the correct type and marks.</p>";
            }
            ?>
        </div>
    </div>

    <button onclick="window.print();" class="print-button">Print Paper</button>
</body>
</html>