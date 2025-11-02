<?php
// Set a custom welcome message for the header
$welcome_subtext = 'Add new questions to your personal question bank or review existing ones.';

// Include the standard header (handles session, login check, and db connection)
require_once 'includes/header.php';

$user_id = $_SESSION['user_id'];
$form_error = '';
$form_success = '';

// Check for a success message from editing or deleting
if (isset($_GET['status']) && $_GET['status'] == 'edited') {
    $form_success = "Question updated successfully!";
} elseif (isset($_GET['status']) && $_GET['status'] == 'deleted') {
    $form_success = "Question deleted successfully!";
}

// --- Handle New Question Form Submission ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_question'])) {
    $subject = trim($_POST['subject']);
    $question_type = $_POST['question_type'];
    $question_text = trim($_POST['question_text']);
    $marks = $_POST['marks'];

    if (empty($subject) || empty($question_type) || empty($question_text) || empty($marks)) {
        $form_error = "All fields are required.";
    } else {
        try {
            $sql = "INSERT INTO question_bank (user_id, subject, question_type, question_text, marks)
                    VALUES (:user_id, :subject, :type, :question, :marks)";
            $stmt = $conn->prepare($sql);
            $stmt->execute([
                ':user_id' => $user_id,
                ':subject' => $subject,
                ':type' => $question_type,
                ':question' => $question_text,
                ':marks' => $marks
            ]);
            $form_success = "Question added successfully!";
        } catch (PDOException $e) {
            $form_error = "Database error: " . $e->getMessage();
        }
    }
}

// --- Fetch All Existing Questions for this User ---
$questions = [];
try {
    $stmt = $conn->prepare("SELECT * FROM question_bank WHERE user_id = ? ORDER BY subject, question_id DESC");
    $stmt->execute([$user_id]);
    $questions = $stmt->fetchAll();
} catch (PDOException $e) {
    $form_error = "Could not fetch questions: " . $e->getMessage();
}
?>

<div class="generator-box">
    <h3>Add to Question Bank</h3>
    
    <?php if ($form_error): ?><p style="color: red; text-align: center;"><?php echo $form_error; ?></p><?php endif; ?>
    <?php if ($form_success): ?><p style="color: green; text-align: center;"><?php echo $form_success; ?></p><?php endif; ?>

    <form action="question_bank.php" method="POST">
        <input type="hidden" name="add_question" value="1">
        
        <div class="form-row">
            <div class="form-group">
                <label for="subject">Subject</label>
                <input type="text" id="subject" name="subject" placeholder="e.g., Physics" required>
            </div>
            
            <div class="form-group">
                <label for="question_type">Question Type</label>
                <select id="question_type" name="question_type" required>
                    <option value="MCQ">MCQ</option>
                    <option value="Short Answer">Short Answer</option>
                    <option value="Long Answer">Long Answer</option>
                    <option value="Descriptive">Descriptive</option>
                </select>
            </div>
        </div>

        <div class="form-group">
            <label for="question_text">Question Text</label>
            <textarea id="question_text" name="question_text" rows="4" placeholder="Enter the full question text" required></textarea>
        </div>

        <div class="form-group">
            <label for="marks">Marks</label>
            <input type="number" id="marks" name="marks" placeholder="e.g., 1 for MCQ, 2 for Short, 5 for Long, 10 for Descriptive" required>
        </div>

        <input type="submit" value="Add Question" class="btn-submit">
    </form>
</div>

<div class="generator-box" style="margin-top: 30px;">
    <h3>My Question Bank (<?php echo count($questions); ?>)</h3>
    <table style="width: 100%; border-collapse: collapse;">
        <thead>
            <tr style="border-bottom: 2px solid #333;">
                <th style="padding: 10px; text-align: left;">Subject</th>
                <th style="padding: 10px; text-align: left;">Question</th>
                <th style="padding: 10px; text-align: left;">Type</th>
                <th style="padding: 10px; text-align: right;">Marks</th>
                <th style="padding: 10px; text-align: center;">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($questions)): ?>
                <tr>
                    <td colspan="5" style="padding: 20px; text-align: center;">Your question bank is empty.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($questions as $q): ?>
                    <tr style="border-bottom: 1px solid #eee;">
                        <td style="padding: 10px;"><?php echo htmlspecialchars($q['subject']); ?></td>
                        <td style="padding: 10px;"><?php echo htmlspecialchars(substr($q['question_text'], 0, 100)); ?>...</td>
                        <td style="padding: 10px;"><?php echo htmlspecialchars($q['question_type']); ?></td>
                        <td style="padding: 10px; text-align: center;"><?php echo htmlspecialchars($q['marks']); ?></td>
                        <td style="padding: 10px; text-align: center;" class="action-cell">
                            <a href="edit_question.php?id=<?php echo $q['question_id']; ?>" class="btn-edit">Edit</a>
                            
                            <a href="delete_question.php?id=<?php echo $q['question_id']; ?>" 
                               class="btn-delete" 
                               onclick="return confirm('Are you sure you want to permanently delete this question?');">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php
// Include the standard footer
require_once 'includes/footer.php';
?>