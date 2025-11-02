<?php
// Set a custom welcome message for the header
$welcome_subtext = 'Use the form below to generate a new question paper from your question bank.';

// Include the standard header
require_once 'includes/header.php';

// Fetch user's subjects to populate the dropdown
$subjects = [];
try {
    $stmt = $conn->prepare("SELECT DISTINCT subject FROM question_bank WHERE user_id = ? ORDER BY subject ASC");
    $stmt->execute([$_SESSION['user_id']]);
    $subjects = $stmt->fetchAll(PDO::FETCH_COLUMN);
} catch (PDOException $e) {
    // Handle error
}
?>

<div class="generator-box">
    <h3>Generate New Question Paper</h3>
    
    <form action="generate_paper.php" method="POST" target="_blank">
        
        <div class="form-row">
            <div class="form-group">
                <label for="exam_title">Exam Title</label>
                <input type="text" id="exam_title" name="exam_title" placeholder="e.g., Mid-Term Examination" required>
            </div>
            <div class="form-group">
                <label for="subject">Subject</label>
                <select id="subject" name="subject" required>
                    <option value="">-- Select Subject --</option>
                    <?php foreach ($subjects as $subject): ?>
                        <option value="<?php echo htmlspecialchars($subject); ?>">
                            <?php echo htmlspecialchars($subject); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <hr style="border: 0; border-top: 1px solid #eee; margin: 20px 0;">
        
        <h4>Number of Questions</h4>
        
        <div class="form-row">
            <div class="form-group">
                <label for="num_mcq">MCQs (1 Mark)</label>
                <input type="number" id="num_mcq" name="num_mcq" value="0" min="0" required>
            </div>
            <div class="form-group">
                <label for="num_short">Short Answers (2 Marks)</label>
                <input type="number" id="num_short" name="num_short" value="0" min="0" required>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="num_long">Long Answers (5 Marks)</label>
                <input type="number" id="num_long" name="num_long" value="0" min="0" required>
            </div>
            <div class="form-group">
                <label for="num_desc">Descriptive (10 Marks)</label>
                <input type="number" id="num_desc" name="num_desc" value="0" min="0" required>
            </div>
        </div>

        <hr style="border: 0; border-top: 1px solid #eee; margin: 20px 0;">

         <div class="form-group">
            <label for="duration">Duration (in minutes)</label>
            <input type="number" id="duration" name="duration" placeholder="e.g., 180" required>
        </div>

         <div class="form-group">
            <label for="instructions">Instructions for Students</label>
            <textarea id="instructions" name="instructions" rows="4" placeholder="e.g., Section A is compulsory."></textarea>
        </div>

        <input type="submit" value="Generate Paper" class="btn-submit">
    </form>
</div>

<?php
// Include the standard footer
require_once 'includes/footer.php';
?>