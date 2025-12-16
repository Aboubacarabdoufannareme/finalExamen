<?php
$page_title = 'Job Details - DigiCareer Niger';
require_once 'header.php';
require_once 'db_connect.php';
require_role('candidate');

$user_id = get_user_id();
$job_id = (int) ($_GET['id'] ?? 0);
$success = '';
$error = '';

// Get job details
$stmt = $pdo->prepare("
    SELECT j.*, u.name as employer_name, cp.company_name, cp.company_logo, cp.industry, cp.company_size, cp.location as company_location, cp.description as company_description
    FROM niger_jobs j
    JOIN niger_users u ON j.employer_id = u.id
    LEFT JOIN niger_company_profiles cp ON u.id = cp.user_id
    WHERE j.id = ? AND j.status = 'active'
");
$stmt->execute([$job_id]);
$job = $stmt->fetch();

if (!$job) {
    header('Location: browse_jobs.php');
    exit;
}

// Check if already applied
$stmt = $pdo->prepare("SELECT id FROM niger_applications WHERE job_id = ? AND candidate_id = ?");
$stmt->execute([$job_id, $user_id]);
$has_applied = $stmt->fetch();

// Handle application submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['apply'])) {
    if ($has_applied) {
        $error = 'You have already applied for this job.';
    } else {
        $cover_letter = sanitize($_POST['cover_letter'] ?? '');

        $stmt = $pdo->prepare("INSERT INTO niger_applications (job_id, candidate_id, cover_letter) VALUES (?, ?, ?)");
        if ($stmt->execute([$job_id, $user_id, $cover_letter])) {
            $success = 'Application submitted successfully!';
            $has_applied = true;
        } else {
            $error = 'Failed to submit application. Please try again.';
        }
    }
}
?>

<div class="container" style="padding: 2rem 1.5rem; max-width: 900px;">
    <a href="browse_jobs.php" class="btn btn-outline" style="margin-bottom: 2rem;">← Back to Jobs</a>

    <?php if ($success): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <!-- Job Header -->
    <div class="card" style="margin-bottom: 2rem;">
        <div class="card-body">
            <div class="flex gap-lg" style="margin-bottom: 2rem;">
                <?php if ($job['company_logo']): ?>
                    <img src="<?php echo htmlspecialchars($job['company_logo']); ?>" alt="Logo" class="avatar-xl">
                <?php else: ?>
                    <div class="avatar-xl"
                        style="background: linear-gradient(135deg, var(--primary-color), var(--secondary-color)); display: flex; align-items: center; justify-content: center; font-size: 2rem; font-weight: 700;">
                        <?php echo strtoupper(substr($job['company_name'] ?? $job['employer_name'], 0, 1)); ?>
                    </div>
                <?php endif; ?>

                <div style="flex: 1;">
                    <h1 style="margin-bottom: 0.5rem;"><?php echo htmlspecialchars($job['title']); ?></h1>
                    <h3 style="color: var(--text-secondary); margin-bottom: 1rem;">
                        <?php echo htmlspecialchars($job['company_name'] ?? $job['employer_name']); ?>
                    </h3>

                    <div class="flex gap-md" style="flex-wrap: wrap;">
                        <span class="badge badge-info"><?php echo htmlspecialchars($job['job_type']); ?></span>
                        <?php if ($job['location']): ?>
                            <span class="badge badge-info">📍 <?php echo htmlspecialchars($job['location']); ?></span>
                        <?php endif; ?>
                        <?php if ($job['salary_range']): ?>
                            <span class="badge badge-info">💰 <?php echo htmlspecialchars($job['salary_range']); ?></span>
                        <?php endif; ?>
                    </div>

                    <p style="color: var(--text-muted); margin-top: 1rem;">
                        Posted: <?php echo date('M d, Y', strtotime($job['created_at'])); ?>
                    </p>
                </div>
            </div>

            <?php if (!$has_applied): ?>
                <a href="#apply" class="btn btn-primary btn-lg" style="width: 100%;">Apply for this Position</a>
            <?php else: ?>
                <div class="alert alert-info">You have already applied for this position.</div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Job Description -->
    <div class="card" style="margin-bottom: 2rem;">
        <div class="card-header">
            <h3 class="card-title">Job Description</h3>
        </div>
        <div class="card-body">
            <p><?php echo nl2br(htmlspecialchars($job['description'])); ?></p>
        </div>
    </div>

    <!-- Requirements -->
    <?php if ($job['requirements']): ?>
        <div class="card" style="margin-bottom: 2rem;">
            <div class="card-header">
                <h3 class="card-title">Requirements</h3>
            </div>
            <div class="card-body">
                <p><?php echo nl2br(htmlspecialchars($job['requirements'])); ?></p>
            </div>
        </div>
    <?php endif; ?>

    <!-- Company Info -->
    <?php if ($job['company_description'] || $job['industry'] || $job['company_size']): ?>
        <div class="card" style="margin-bottom: 2rem;">
            <div class="card-header">
                <h3 class="card-title">About <?php echo htmlspecialchars($job['company_name'] ?? $job['employer_name']); ?>
                </h3>
            </div>
            <div class="card-body">
                <?php if ($job['industry'] || $job['company_size'] || $job['company_location']): ?>
                    <div class="flex gap-md" style="flex-wrap: wrap; margin-bottom: 1rem;">
                        <?php if ($job['industry']): ?>
                            <span class="badge badge-info"><?php echo htmlspecialchars($job['industry']); ?></span>
                        <?php endif; ?>
                        <?php if ($job['company_size']): ?>
                            <span class="badge badge-info"><?php echo htmlspecialchars($job['company_size']); ?> employees</span>
                        <?php endif; ?>
                        <?php if ($job['company_location']): ?>
                            <span class="badge badge-info">📍 <?php echo htmlspecialchars($job['company_location']); ?></span>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <?php if ($job['company_description']): ?>
                    <p><?php echo nl2br(htmlspecialchars($job['company_description'])); ?></p>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>

    <!-- Application Form -->
    <?php if (!$has_applied): ?>
        <div class="card" id="apply">
            <div class="card-header">
                <h3 class="card-title">Apply for this Position</h3>
            </div>
            <div class="card-body">
                <form method="POST">
                    <div class="form-group">
                        <label class="form-label">Cover Letter (Optional)</label>
                        <textarea name="cover_letter" class="form-control" rows="6"
                            placeholder="Tell the employer why you're a great fit for this role..."></textarea>
                    </div>

                    <div class="alert alert-info">
                        <strong>Note:</strong> Your profile, CV, and documents will be shared with the employer when you
                        apply.
                    </div>

                    <button type="submit" name="apply" class="btn btn-primary btn-lg" style="width: 100%;">Submit
                        Application</button>
                </form>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php require_once 'footer.php'; ?>
