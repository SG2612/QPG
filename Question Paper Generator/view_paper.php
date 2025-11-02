<?php
session_start();
require_once 'includes/db_connect.php';

// Check login
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    die("Access denied. Please login.");
}

// 1. Get the paper ID and user ID
if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("No paper ID specified.");
}
$paper_id = $_GET['id'];
$user_id = $_SESSION['user_id'];

try {
    // 2. Fetch the main paper details
    // **SECURITY CHECK**: We also check that the user_id matches!
    $sql_paper = "SELECT * FROM generated_papers WHERE paper_id = :paper_id AND user_id = :user_id";
    $stmt_paper = $conn->prepare($sql_paper);
    $stmt_paper->execute([':paper_id' => $paper_id, ':user_id' => $user_id]);
    $paper = $stmt_paper->fetch();

    // If paper doesn't exist or doesn't belong to this user, block access
    if (!$paper) {
        die("Error: Paper not found or you do not have permission to view it.");
    }

    // 3. Fetch all questions for this paper, in order
    $sql_q = "SELECT * FROM generated_paper_questions WHERE paper_id = :paper_id ORDER BY question_order ASC";
    $stmt_q = $conn->prepare($sql_q);
    $stmt_q->execute([':paper_id' => $paper_id]);
    $questions = $stmt_q->fetchAll();

} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}

// Helper function to render a section
function renderSectionDynamic($title, $questions_set, &$q_index) {
    if (empty($questions_set)) {
        return;
    }
    
    $marks_per_q = $questions_set[0]['marks'];
    $total_marks = count($questions_set) * $marks_per_q;
    
    echo "<div class='section-header'>";
    echo "<h3>{$title}</h3>";
    echo "<span class='section-marks'>Total Marks: {$total_marks}</span>";
    echo "</div>";
    
    foreach ($questions_set as $q) {
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
    <title>View: <?php echo htmlspecialchars($paper['title']); ?></title>
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
            <h1><?php echo htmlspecialchars($paper['title']); ?></h1>
            <h2>Subject: <?php echo htmlspecialchars($paper['subject']); ?></h2>
            <div class="header-details">
                <span><strong>Total Marks:</strong> <?php echo htmlspecialchars($paper['total_marks']); ?></span>
                <span><strong>Duration:</strong> <?php echo htmlspecialchars($paper['duration_minutes']); ?> minutes</span>
            </div>
        </div>

        <?php if (!empty($paper['instructions'])): ?>
            <div class="instructions">
                <strong>Instructions:</strong>
                <p><?php echo nl2br(htmlspecialchars($paper['instructions'])); ?></p>
            </div>
        <?php endif; ?>

        <div class="question-list">
            <?php
            $question_number = 1;
            
            // Re-group questions by type
            $mcqs = array_filter($questions, fn($q) => $q['question_type'] == 'MCQ');
            $short_answers = array_filter($questions, fn($q) => $q['question_type'] == 'Short Answer');
            $long_answers = array_filter($questions, fn($q) => $q['question_type'] == 'Long Answer');
            $descriptives = array_filter($questions, fn($q) => $q['question_type'] == 'Descriptive');

            renderSectionDynamic('Section A: Multiple Choice Questions', $mcqs, $question_number);
            renderSectionDynamic('Section B: Short Answer Questions', $short_answers, $question_number);
            renderSectionDynamic('Section C: Long Answer Questions', $long_answers, $question_number);
            renderSectionDynamic('Section D: Descriptive Questions', $descriptives, $question_number);

            if ($question_number == 1) {
                echo "<p style='text-align: center; color: red;'>This paper contains no questions.</p>";
            }
            ?>
        </div>
    </div>

    <button onclick="window.print();" class="print-button">Print Paper</button>
</body>
</html>