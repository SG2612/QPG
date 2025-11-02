<?php
// We don't want the regular welcome message here
$welcome_subtext = 'Edit your question and save the changes.';
require_once 'includes/header.php'; // This includes session_start(), db_connect.php, and login check

$user_id = $_SESSION['user_id'];
$question_id = $_GET['id'] ?? null;
$form_error = '';
$form_success = '';
$question = null;

if (!$question_id) {
    die("No question ID provided.");
}

// --- First, fetch the question and make sure it belongs to the user ---
try {
    $stmt = $conn->prepare("SELECT * FROM question_bank WHERE question_id = :qid AND user_id = :uid");
    $stmt->execute([':qid' => $question_id, ':uid' => $user_id]);
    $question = $stmt->fetch();

    if (!$question) {
        // If question doesn't exist or doesn't belong to the user, stop.
        die("Error: Question not found or you do not have permission to edit it.");
    }
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}


// --- Handle Form Submission (POST) ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $subject = trim($_POST['subject']);
    $question_type = $_POST['question_type'];
    $question_text = trim($_POST['question_text']);
    $marks = $_POST['marks'];

    if (empty($subject) || empty($question_type) || empty($question_text) || empty($marks)) {
        $form_error = "All fields are required.";
        // Re-populate $question with the failed data
        $question['subject'] = $subject;
        $question['question_type'] = $question_type;
        $question['question_text'] = $question_text;
        $question['marks'] = $marks;
    } else {
        try {
            $sql = "UPDATE question_bank SET
                        subject = :subject,
                        question_type = :type,
                        question_text = :text,
                        marks = :marks
                    WHERE question_id = :qid AND user_id = :uid";
            
            $stmt = $conn->prepare($sql);
            $stmt->execute([
                ':subject' => $subject,
                ':type' => $question_type,
                ':text' => $question_text,
                ':marks' => $marks,
                ':qid' => $question_id,
                ':uid' => $user_id
            ]);
            
            // Redirect back to the bank on success
            header("Location: question_bank.php?status=edited");
            exit;

        } catch (PDOException $e) {
            $form_error = "Database error: " . $e->getMessage();
        }
    }
}
?>

<div class="generator-box">
    <h3>Edit Question</h3>
    
    <?php if ($form_error): ?><p style="color: red;"><?php echo $form_error; ?></p><?php endif; ?>
    <?php if ($form_success): ?><p style="color: green;"><?php echo $form_success; ?></p><?php endif; ?>

    <form action="edit_question.php?id=<?php echo $question_id; ?>" method="POST">
        
        <div class="form-row">
            <div class="form-group">
                <label for="subject">Subject</label>
                <input type="text" id="subject" name="subject" value="<?php echo htmlspecialchars($question['subject']); ?>" required>
            </div>
            
            <div class="form-group">
                <label for="question_type">Question Type</label>
                <select id="question_type" name="question_type" required>
                    <option <?php if($question['question_type'] == 'MCQ') echo 'selected'; ?>>MCQ</option>
                    <option <?php if($question['question_type'] == 'Short Answer') echo 'selected'; ?>>Short Answer</option>
                    <option <?php if($question['question_type'] == 'Long Answer') echo 'selected'; ?>>Long Answer</option>
                    <option <?php if($question['question_type'] == 'Descriptive') echo 'selected'; ?>>Descriptive</option>
                </select>
            </div>
        </div>

        <div class="form-group">
            <label for="question_text">Question Text</label>
            <textarea id="question_text" name="question_text" rows="6" required><?php echo htmlspecialchars($question['question_text']); ?></textarea>
        </div>

        <div class="form-group">
            <label for="marks">Marks</label>
            <input type="number" id="marks" name="marks" value="<?php echo htmlspecialchars($question['marks']); ?>" required>
        </div>

        <input type="submit" value="Save Changes" class="btn-submit">
        <a href="question_bank.php" style="display: block; text-align: center; margin-top: 15px;">Cancel</a>
    </form>
</div>

<?php
// Include the standard footer
require_once 'includes/footer.php';
?>