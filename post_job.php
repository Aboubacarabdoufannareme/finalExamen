<?php
$page_title = 'Post Job - DigiCareer Niger';
require_once 'header.php';
require_once 'db_connect.php';
require_role('employer');

$user_id = get_user_id();
$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = sanitize($_POST['title'] ?? '');
    $description = sanitize($_POST['description'] ?? '');
    $requirements = sanitize($_POST['requirements'] ?? '');
    $location = sanitize($_POST['location'] ?? '');
    $job_type = sanitize($_POST['job_type'] ?? 'full-time');
    $salary_range = sanitize($_POST['salary_range'] ?? '');

    if (empty($title) || empty($description)) {
        $error = 'Title and description are required';
    } else {
        $stmt = $pdo->prepare("INSERT INTO niger_jobs (employer_id, title, description, requirements, location, job_type, salary_range, status) VALUES (?, ?, ?, ?, ?, ?, ?, 'active')");
        if ($stmt->execute([$user_id, $title, $description, $requirements, $location, $job_type, $salary_range])) {
            $success = 'Job posted successfully!';
            // Clear form
            $_POST = [];
        } else {
            $error = 'Failed to post job. Please try again.';
        }
    }
}
?>

<div class="dashboard">
    <aside class="sidebar">
        <ul class="sidebar-menu">
            <li class="sidebar-item">
                <a href="employer_dashboard.php" class="sidebar-link">📊 Dashboard</a>
            </li>
            <li class="sidebar-item">
                <a href="company_profile.php" class="sidebar-link">🏢 Company Profile</a>
            </li>
            <li class="sidebar-item">
                <a href="post_job.php" class="sidebar-link active">➕ Post Job</a>
            </li>
            <li class="sidebar-item">
                <a href="my_jobs.php" class="sidebar-link">📋 My Jobs</a>
            </li>
            <li class="sidebar-item">
                <a href="applications_received.php" class="sidebar-link">📬 Applications</a>
            </li>
            <li class="sidebar-item">
                <a href="search_candidates.php" class="sidebar-link">🔍 Search Candidates</a>
            </li>
        </ul>
    </aside>

    <main class="main-content">
        <h1 style="margin-bottom: 2rem;">Post a New Job</h1>

        <?php if ($success): ?>
            <div class="alert alert-success">
                <?php echo htmlspecialchars($success); ?>
                <a href="my_jobs.php" style="display: block; margin-top: 0.5rem;">View all jobs</a>
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <div class="card">
            <div class="card-body">
                <form method="POST">
                    <div class="form-group">
                        <label class="form-label">Job Title *</label>
                        <input type="text" name="title" class="form-control"
                            placeholder="e.g., Senior Software Developer" required
                            value="<?php echo htmlspecialchars($_POST['title'] ?? ''); ?>">
                    </div>

                    <div class="grid grid-2 gap-md">
                        <div class="form-group">
                            <label class="form-label">Job Type *</label>
                            <select name="job_type" class="form-control" required>
                                <option value="full-time" <?php echo ($_POST['job_type'] ?? '') === 'full-time' ? 'selected' : ''; ?>>Full-time</option>
                                <option value="part-time" <?php echo ($_POST['job_type'] ?? '') === 'part-time' ? 'selected' : ''; ?>>Part-time</option>
                                <option value="internship" <?php echo ($_POST['job_type'] ?? '') === 'internship' ? 'selected' : ''; ?>>Internship</option>
                                <option value="contract" <?php echo ($_POST['job_type'] ?? '') === 'contract' ? 'selected' : ''; ?>>Contract</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Location</label>
                            <input type="text" name="location" class="form-control" placeholder="e.g., Niamey, Niger"
                                value="<?php echo htmlspecialchars($_POST['location'] ?? ''); ?>">
                        </div>

                        <div class="form-group">
                            <label class="form-label">Salary Range (Optional)</label>
                            <input type="text" name="salary_range" class="form-control"
                                placeholder="e.g., 500,000 - 800,000 FCFA"
                                value="<?php echo htmlspecialchars($_POST['salary_range'] ?? ''); ?>">
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Job Description *</label>
                        <textarea name="description" class="form-control" rows="6"
                            placeholder="Describe the role, responsibilities, and what you're looking for..."
                            required><?php echo htmlspecialchars($_POST['description'] ?? ''); ?></textarea>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Requirements</label>
                        <textarea name="requirements" class="form-control" rows="6"
                            placeholder="List the qualifications, skills, and experience required..."><?php echo htmlspecialchars($_POST['requirements'] ?? ''); ?></textarea>
                    </div>

                    <div class="flex gap-md">
                        <button type="submit" class="btn btn-primary">Post Job</button>
                        <a href="employer_dashboard.php" class="btn btn-outline">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </main>
</div>

<?php require_once 'footer.php'; ?>
