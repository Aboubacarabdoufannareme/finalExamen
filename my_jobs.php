<?php
$page_title = 'My Jobs - DigiCareer Niger';
require_once 'header.php';
require_once 'db_connect.php';
require_role('employer');

$user_id = get_user_id();
$success = '';

// Handle job status toggle
if (isset($_POST['toggle_status'])) {
    $job_id = (int) $_POST['job_id'];
    $new_status = sanitize($_POST['new_status']);

    if (in_array($new_status, ['active', 'closed'])) {
        $stmt = $pdo->prepare("UPDATE niger_jobs SET status = ? WHERE id = ? AND employer_id = ?");
        if ($stmt->execute([$new_status, $job_id, $user_id])) {
            $success = 'Job status updated!';
        }
    }
}

// Get all jobs
$stmt = $pdo->prepare("
    SELECT j.*, 
    (SELECT COUNT(*) FROM niger_applications WHERE job_id = j.id) as total_applications,
    (SELECT COUNT(*) FROM niger_applications WHERE job_id = j.id AND status = 'pending') as pending_applications
    FROM niger_jobs j
    WHERE j.employer_id = ?
    ORDER BY j.created_at DESC
");
$stmt->execute([$user_id]);
$jobs = $stmt->fetchAll();
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
                <a href="post_job.php" class="sidebar-link">➕ Post Job</a>
            </li>
            <li class="sidebar-item">
                <a href="my_jobs.php" class="sidebar-link active">📋 My Jobs</a>
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
        <div class="flex-between" style="margin-bottom: 2rem;">
            <h1>My Job Postings</h1>
            <a href="post_job.php" class="btn btn-primary">Post New Job</a>
        </div>

        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>

        <div class="card">
            <div class="card-header">
                <h3 class="card-title">All Jobs (<?php echo count($jobs); ?>)</h3>
            </div>
            <div class="card-body">
                <?php if (empty($jobs)): ?>
                    <p style="text-align: center; color: var(--text-muted); padding: 2rem;">
                        No jobs posted yet. <a href="post_job.php">Post your first job</a>!
                    </p>
                <?php else: ?>
                    <div class="grid gap-md">
                        <?php foreach ($jobs as $job): ?>
                            <div class="card" style="padding: 1.5rem;">
                                <div class="flex-between" style="margin-bottom: 1rem;">
                                    <h3><?php echo htmlspecialchars($job['title']); ?></h3>
                                    <span
                                        class="badge <?php echo $job['status'] === 'active' ? 'badge-success' : 'badge-error'; ?>">
                                        <?php echo htmlspecialchars($job['status']); ?>
                                    </span>
                                </div>

                                <div class="flex gap-md" style="flex-wrap: wrap; margin-bottom: 1rem;">
                                    <span class="badge badge-info"><?php echo htmlspecialchars($job['job_type']); ?></span>
                                    <?php if ($job['location']): ?>
                                        <span style="color: var(--text-secondary); font-size: 0.875rem;">
                                            📍 <?php echo htmlspecialchars($job['location']); ?>
                                        </span>
                                    <?php endif; ?>
                                </div>

                                <p style="margin-bottom: 1rem;">
                                    <?php echo htmlspecialchars(substr($job['description'], 0, 150)) . '...'; ?>
                                </p>

                                <div class="grid grid-3 gap-md" style="margin-bottom: 1rem;">
                                    <div
                                        style="padding: 0.75rem; background: rgba(99, 102, 241, 0.1); border-radius: var(--radius-md); text-align: center;">
                                        <div style="font-size: 1.5rem; font-weight: 700; color: var(--primary-light);">
                                            <?php echo $job['total_applications']; ?>
                                        </div>
                                        <div style="font-size: 0.75rem; color: var(--text-muted);">Total Applications</div>
                                    </div>
                                    <div
                                        style="padding: 0.75rem; background: rgba(245, 158, 11, 0.1); border-radius: var(--radius-md); text-align: center;">
                                        <div style="font-size: 1.5rem; font-weight: 700; color: var(--warning);">
                                            <?php echo $job['pending_applications']; ?>
                                        </div>
                                        <div style="font-size: 0.75rem; color: var(--text-muted);">Pending</div>
                                    </div>
                                    <div
                                        style="padding: 0.75rem; background: rgba(255, 255, 255, 0.05); border-radius: var(--radius-md); text-align: center;">
                                        <div style="font-size: 0.875rem; color: var(--text-muted); margin-bottom: 0.25rem;">
                                            Posted</div>
                                        <div style="font-size: 0.875rem; font-weight: 600;">
                                            <?php echo date('M d, Y', strtotime($job['created_at'])); ?>
                                        </div>
                                    </div>
                                </div>

                                <div class="flex gap-sm">
                                    <a href="job_applicants.php?id=<?php echo $job['id']; ?>" class="btn btn-sm btn-primary">
                                        View Applicants (<?php echo $job['total_applications']; ?>)
                                    </a>
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="job_id" value="<?php echo $job['id']; ?>">
                                        <input type="hidden" name="new_status"
                                            value="<?php echo $job['status'] === 'active' ? 'closed' : 'active'; ?>">
                                        <button type="submit" name="toggle_status" class="btn btn-sm btn-outline">
                                            <?php echo $job['status'] === 'active' ? 'Close Job' : 'Reopen Job'; ?>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>
</div>

<?php require_once 'footer.php'; ?>
