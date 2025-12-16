<?php
$page_title = 'Dashboard - DigiCareer Niger';
require_once 'header.php';
require_once 'db_connect.php';
require_role('employer');

$user_id = get_user_id();

// Get stats
$stmt = $pdo->prepare("SELECT COUNT(*) FROM niger_jobs WHERE employer_id = ?");
$stmt->execute([$user_id]);
$total_jobs = $stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT COUNT(*) FROM niger_jobs WHERE employer_id = ? AND status = 'active'");
$stmt->execute([$user_id]);
$active_jobs = $stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT COUNT(*) FROM niger_applications a JOIN niger_jobs j ON a.job_id = j.id WHERE j.employer_id = ?");
$stmt->execute([$user_id]);
$total_applications = $stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT COUNT(*) FROM niger_applications a JOIN niger_jobs j ON a.job_id = j.id WHERE j.employer_id = ? AND a.status = 'pending'");
$stmt->execute([$user_id]);
$pending_applications = $stmt->fetchColumn();

// Get recent jobs
$stmt = $pdo->prepare("SELECT * FROM niger_jobs WHERE employer_id = ? ORDER BY created_at DESC LIMIT 5");
$stmt->execute([$user_id]);
$recent_jobs = $stmt->fetchAll();

// Get company profile
$stmt = $pdo->prepare("SELECT * FROM niger_company_profiles WHERE user_id = ?");
$stmt->execute([$user_id]);
$company = $stmt->fetch();
?>

<div class="dashboard">
    <aside class="sidebar">
        <ul class="sidebar-menu">
            <li class="sidebar-item">
                <a href="employer_dashboard.php" class="sidebar-link active">📊 Dashboard</a>
            </li>
            <li class="sidebar-item">
                <a href="company_profile.php" class="sidebar-link">🏢 Company Profile</a>
            </li>
            <li class="sidebar-item">
                <a href="post_job.php" class="sidebar-link">➕ Post Job</a>
            </li>
            <li class="sidebar-item">
                <a href="my_jobs.php" class="sidebar-link">📋 My Jobs</a>
            </li>
            <li class="sidebar-item">
                <a href="applications_received.php" class="sidebar-link">
                    📬 Applications
                    <?php if ($pending_applications > 0): ?>
                        <span class="badge badge-warning"><?php echo $pending_applications; ?></span>
                    <?php endif; ?>
                </a>
            </li>
            <li class="sidebar-item">
                <a href="search_candidates.php" class="sidebar-link">🔍 Search Candidates</a>
            </li>
        </ul>
    </aside>

    <main class="main-content">
        <div class="flex-between" style="margin-bottom: 2rem;">
            <div>
                <h1>Welcome back, <?php echo htmlspecialchars($company['company_name'] ?? get_user_name()); ?>!</h1>
                <p style="color: var(--text-muted);">Manage your recruitment activities</p>
            </div>
            <a href="post_job.php" class="btn btn-primary">Post New Job</a>
        </div>

        <!-- Stats Cards -->
        <div class="stats-grid">
            <div class="stat-card fade-in">
                <div class="stat-value"><?php echo $total_jobs; ?></div>
                <div class="stat-label">Total Jobs Posted</div>
            </div>

            <div class="stat-card fade-in">
                <div class="stat-value"><?php echo $active_jobs; ?></div>
                <div class="stat-label">Active Jobs</div>
            </div>

            <div class="stat-card fade-in">
                <div class="stat-value"><?php echo $total_applications; ?></div>
                <div class="stat-label">Total Applications</div>
            </div>

            <div class="stat-card fade-in">
                <div class="stat-value"><?php echo $pending_applications; ?></div>
                <div class="stat-label">Pending Reviews</div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="card" style="margin-bottom: 2rem;">
            <h3 style="margin-bottom: 1rem;">Quick Actions</h3>
            <div class="grid grid-3 gap-md">
                <a href="post_job.php" class="btn btn-primary">Post a Job</a>
                <a href="search_candidates.php" class="btn btn-secondary">Find Candidates</a>
                <a href="applications_received.php" class="btn btn-outline">Review Applications</a>
            </div>
        </div>

        <!-- Recent Jobs -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Recent Job Postings</h3>
            </div>
            <div class="card-body">
                <?php if (empty($recent_jobs)): ?>
                    <p style="color: var(--text-muted); text-align: center; padding: 2rem;">
                        No jobs posted yet. <a href="post_job.php">Post your first job</a>!
                    </p>
                <?php else: ?>
                    <div style="overflow-x: auto;">
                        <table style="width: 100%; border-collapse: collapse;">
                            <thead>
                                <tr style="border-bottom: 1px solid rgba(255, 255, 255, 0.1);">
                                    <th style="padding: 1rem; text-align: left;">Job Title</th>
                                    <th style="padding: 1rem; text-align: left;">Type</th>
                                    <th style="padding: 1rem; text-align: left;">Location</th>
                                    <th style="padding: 1rem; text-align: left;">Status</th>
                                    <th style="padding: 1rem; text-align: left;">Posted</th>
                                    <th style="padding: 1rem; text-align: left;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recent_jobs as $job): ?>
                                    <tr style="border-bottom: 1px solid rgba(255, 255, 255, 0.05);">
                                        <td style="padding: 1rem;">
                                            <strong><?php echo htmlspecialchars($job['title']); ?></strong>
                                        </td>
                                        <td style="padding: 1rem;">
                                            <span
                                                class="badge badge-info"><?php echo htmlspecialchars($job['job_type']); ?></span>
                                        </td>
                                        <td style="padding: 1rem; color: var(--text-secondary);">
                                            <?php echo htmlspecialchars($job['location'] ?? 'N/A'); ?>
                                        </td>
                                        <td style="padding: 1rem;">
                                            <span
                                                class="badge <?php echo $job['status'] === 'active' ? 'badge-success' : 'badge-error'; ?>">
                                                <?php echo htmlspecialchars($job['status']); ?>
                                            </span>
                                        </td>
                                        <td style="padding: 1rem; color: var(--text-secondary);">
                                            <?php echo date('M d, Y', strtotime($job['created_at'])); ?>
                                        </td>
                                        <td style="padding: 1rem;">
                                            <a href="job_applicants.php?id=<?php echo $job['id']; ?>"
                                                class="btn btn-sm btn-outline">View Applicants</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <div style="margin-top: 1rem; text-align: center;">
                        <a href="my_jobs.php" class="btn btn-outline">View All Jobs</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>
</div>

<?php require_once 'footer.php'; ?>
