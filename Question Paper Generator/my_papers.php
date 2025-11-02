<?php
// Set a custom welcome message for the header
$welcome_subtext = 'View and print your previously generated question papers.';

// Include the standard header
require_once 'includes/header.php';

// Fetch all saved papers for this user
$papers = [];
try {
    $stmt = $conn->prepare("SELECT * FROM generated_papers WHERE user_id = ? ORDER BY created_at DESC");
    $stmt->execute([$_SESSION['user_id']]);
    $papers = $stmt->fetchAll();
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>

<div class="generator-box">
    <h3>My Saved Papers (<?php echo count($papers); ?>)</h3>
    <table style="width: 100%; border-collapse: collapse;">
        <thead>
            <tr style="border-bottom: 2px solid #333;">
                <th style="padding: 10px; text-align: left;">Title</th>
                <th style="padding: 10px; text-align: left;">Subject</th>
                <th style="padding: 10px; text-align: right;">Marks</th>
                <th style="padding: 10px; text-align: left;">Date</th>
                <th style="padding: 10px; text-align: right;">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($papers)): ?>
                <tr>
                    <td colspan="5" style="padding: 20px; text-align: center;">You have not generated any papers yet.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($papers as $paper): ?>
                    <tr style="border-bottom: 1px solid #eee;">
                        <td style="padding: 10px;"><?php echo htmlspecialchars($paper['title']); ?></td>
                        <td style="padding: 10px;"><?php echo htmlspecialchars($paper['subject']); ?></td>
                        <td style="padding: 10px; text-align: right;"><?php echo htmlspecialchars($paper['total_marks']); ?></td>
                        <td style="padding: 10px;"><?php echo date('d M Y, H:i', strtotime($paper['created_at'])); ?></td>
                        <td style="padding: 10px; text-align: right;">
                            <a href="view_paper.php?id=<?php echo $paper['paper_id']; ?>" target="_blank" class="btn-submit" style="padding: 5px 10px; font-size: 14px; width: auto; display: inline-block; text-decoration: none;">View</a>
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