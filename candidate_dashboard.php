<?php
$page_title = 'Dashboard - DigiCareer Niger';
require_once 'header.php';
require_once 'db_connect.php';
require_role('candidate');

$user_id = get_user_id();

// Get user stats
$stmt = $pdo->prepare("SELECT COUNT(*) FROM niger_applications WHERE candidate_id = ?");
$stmt->execute([$user_id]);
$total_applications = $stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT COUNT(*) FROM niger_applications WHERE candidate_id = ? AND status = 'pending'");
$stmt->execute([$user_id]);
$pending_applications = $stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT COUNT(*) FROM niger_documents WHERE user_id = ?");
$stmt->execute([$user_id]);
$total_documents = $stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT COUNT(*) FROM niger_invitations WHERE candidate_id = ? AND status = 'pending'");
$stmt->execute([$user_id]);
$pending_invitations = $stmt->fetchColumn();

// Get recent applications
$stmt = $pdo->prepare("
    SELECT a.*, j.title, j.location, j.job_type, u.name as employer_name, cp.company_name 
    FROM niger_applications a 
    JOIN niger_jobs j ON a.job_id = j.id 
    JOIN niger_users u ON j.employer_id = u.id 
    LEFT JOIN niger_company_profiles cp ON u.id = cp.user_id 
    WHERE a.candidate_id = ? 
    ORDER BY a.applied_at DESC 
    LIMIT 5
");
$stmt->execute([$user_id]);
$recent_applications = $stmt->fetchAll();

// Get user profile
$stmt = $pdo->prepare("SELECT * FROM niger_users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();
?>

<div class="dashboard">
    <aside class="sidebar">
        <ul class="sidebar-menu">
            <li class="sidebar-item">
                <a href="candidate_dashboard.php" class="sidebar-link active">📊 Dashboard</a>
            </li>
            <li class="sidebar-item">
                <a href="profile.php" class="sidebar-link">👤 My Profile</a>
            </li>
            <li class="sidebar-item">
                <a href="my_documents.php" class="sidebar-link">📁 Documents</a>
            </li>
            <li class="sidebar-item">
                <a href="cv_builder.php" class="sidebar-link">📝 CV Builder</a>
            </li>
            <li class="sidebar-item">
                <a href="browse_jobs.php" class="sidebar-link">🔍 Browse Jobs</a>
            </li>
            <li class="sidebar-item">
                <a href="my_applications.php" class="sidebar-link">📋 My Applications</a>
            </li>
            <li class="sidebar-item">
                <a href="invitations.php" class="sidebar-link">
                    ✉️ Invitations
                    <?php if ($pending_invitations > 0): ?>
                        <span class="badge badge-warning"><?php echo $pending_invitations; ?></span>
                    <?php endif; ?>
                </a>
            </li>
        </ul>
    </aside>

    <main class="main-content">
        <div class="flex-between" style="margin-bottom: 2rem;">
            <div>
                <h1>Welcome back, <?php echo htmlspecialchars(get_user_name()); ?>!</h1>
                <p style="color: var(--text-muted);">Here's your career overview</p>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="stats-grid">
            <div class="stat-card fade-in">
                <div class="stat-value"><?php echo $total_applications; ?></div>
                <div class="stat-label">Total Applications</div>
            </div>

            <div class="stat-card fade-in">
                <div class="stat-value"><?php echo $pending_applications; ?></div>
                <div class="stat-label">Pending Reviews</div>
            </div>

            <div class="stat-card fade-in">
                <div class="stat-value"><?php echo $total_documents; ?></div>
                <div class="stat-label">Documents Uploaded</div>
            </div>

            <div class="stat-card fade-in">
                <div class="stat-value"><?php echo $pending_invitations; ?></div>
                <div class="stat-label">New Invitations</div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="card" style="margin-bottom: 2rem;">
            <h3 style="margin-bottom: 1rem;">Quick Actions</h3>
            <div class="grid grid-3 gap-md">
                <a href="browse_jobs.php" class="btn btn-primary">Browse Jobs</a>
                <a href="cv_builder.php" class="btn btn-secondary">Build CV</a>
                <a href="my_documents.php" class="btn btn-outline">Upload Documents</a>
            </div>
        </div>

        <!-- Recent Applications -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Recent Applications</h3>
            </div>
            <div class="card-body">
                <?php if (empty($recent_applications)): ?>
                    <p style="color: var(--text-muted); text-align: center; padding: 2rem;">
                        No applications yet. <a href="browse_jobs.php">Browse jobs</a> to get started!
                    </p>
                <?php else: ?>
                    <div style="overflow-x: auto;">
                        <table style="width: 100%; border-collapse: collapse;">
                            <thead>
                                <tr style="border-bottom: 1px solid rgba(255, 255, 255, 0.1);">
                                    <th style="padding: 1rem; text-align: left;">Job Title</th>
                                    <th style="padding: 1rem; text-align: left;">Company</th>
                                    <th style="padding: 1rem; text-align: left;">Location</th>
                                    <th style="padding: 1rem; text-align: left;">Status</th>
                                    <th style="padding: 1rem; text-align: left;">Applied</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recent_applications as $app): ?>
                                    <tr style="border-bottom: 1px solid rgba(255, 255, 255, 0.05);">
                                        <td style="padding: 1rem;">
                                            <strong><?php echo htmlspecialchars($app['title']); ?></strong>
                                            <br>
                                            <span class="badge badge-info" style="margin-top: 0.25rem;">
                                                <?php echo htmlspecialchars($app['job_type']); ?>
                                            </span>
                                        </td>
                                        <td style="padding: 1rem; color: var(--text-secondary);">
                                            <?php echo htmlspecialchars($app['company_name'] ?? $app['employer_name']); ?>
                                        </td>
                                        <td style="padding: 1rem; color: var(--text-secondary);">
                                            <?php echo htmlspecialchars($app['location'] ?? 'N/A'); ?>
                                        </td>
                                        <td style="padding: 1rem;">
                                            <?php
                                            $badge_class = [
                                                'pending' => 'badge-warning',
                                                'reviewed' => 'badge-info',
                                                'accepted' => 'badge-success',
                                                'rejected' => 'badge-error'
                                            ][$app['status']] ?? 'badge-info';
                                            ?>
                                            <span class="badge <?php echo $badge_class; ?>">
                                                <?php echo htmlspecialchars($app['status']); ?>
                                            </span>
                                        </td>
                                        <td style="padding: 1rem; color: var(--text-secondary);">
                                            <?php echo date('M d, Y', strtotime($app['applied_at'])); ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <div style="margin-top: 1rem; text-align: center;">
                        <a href="my_applications.php" class="btn btn-outline">View All Applications</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>
</div>

<?php require_once 'footer.php'; ?>
