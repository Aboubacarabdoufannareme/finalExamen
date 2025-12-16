<?php
$page_title = 'Applications Received - DigiCareer Niger';
require_once 'header.php';
require_once 'db_connect.php';
require_role('employer');

$user_id = get_user_id();
$success = '';
$error = '';

// Handle status update
if (isset($_POST['update_status'])) {
    $application_id = (int) $_POST['application_id'];
    $new_status = sanitize($_POST['status']);

    if (in_array($new_status, ['pending', 'reviewed', 'accepted', 'rejected'])) {
        $stmt = $pdo->prepare("
            UPDATE niger_applications a
            JOIN niger_jobs j ON a.job_id = j.id
            SET a.status = ?
            WHERE a.id = ? AND j.employer_id = ?
        ");
        if ($stmt->execute([$new_status, $application_id, $user_id])) {
            $success = 'Application status updated!';
        }
    }
}

// Get all applications for employer's jobs
$stmt = $pdo->prepare("
    SELECT a.*, j.title as job_title, u.name as candidate_name, u.email as candidate_email, u.phone as candidate_phone, u.profile_pic
    FROM niger_applications a
    JOIN niger_jobs j ON a.job_id = j.id
    JOIN niger_users u ON a.candidate_id = u.id
    WHERE j.employer_id = ?
    ORDER BY a.applied_at DESC
");
$stmt->execute([$user_id]);
$applications = $stmt->fetchAll();

// Group by status
$by_status = [
    'pending' => [],
    'reviewed' => [],
    'accepted' => [],
    'rejected' => []
];
foreach ($applications as $app) {
    $by_status[$app['status']][] = $app;
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
                <a href="post_job.php" class="sidebar-link">➕ Post Job</a>
            </li>
            <li class="sidebar-item">
                <a href="my_jobs.php" class="sidebar-link">📋 My Jobs</a>
            </li>
            <li class="sidebar-item">
                <a href="applications_received.php" class="sidebar-link active">📬 Applications</a>
            </li>
            <li class="sidebar-item">
                <a href="search_candidates.php" class="sidebar-link">🔍 Search Candidates</a>
            </li>
        </ul>
    </aside>

    <main class="main-content">
        <h1 style="margin-bottom: 2rem;">Applications Received</h1>

        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>

        <!-- Stats -->
        <div class="stats-grid" style="margin-bottom: 2rem;">
            <div class="stat-card">
                <div class="stat-value"><?php echo count($by_status['pending']); ?></div>
                <div class="stat-label">Pending</div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?php echo count($by_status['reviewed']); ?></div>
                <div class="stat-label">Reviewed</div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?php echo count($by_status['accepted']); ?></div>
                <div class="stat-label">Accepted</div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?php echo count($by_status['rejected']); ?></div>
                <div class="stat-label">Rejected</div>
            </div>
        </div>

        <?php if (empty($applications)): ?>
            <div class="card">
                <p style="text-align: center; color: var(--text-muted); padding: 2rem;">
                    No applications received yet.
                </p>
            </div>
        <?php else: ?>
            <!-- Pending Applications -->
            <?php if (!empty($by_status['pending'])): ?>
                <div class="card" style="margin-bottom: 2rem;">
                    <div class="card-header">
                        <h3 class="card-title">Pending Applications (<?php echo count($by_status['pending']); ?>)</h3>
                    </div>
                    <div class="card-body">
                        <?php foreach ($by_status['pending'] as $app): ?>
                            <div class="card" style="padding: 1.5rem; margin-bottom: 1rem; border: 2px solid var(--warning);">
                                <div class="flex gap-lg">
                                    <?php if ($app['profile_pic']): ?>
                                        <img src="<?php echo htmlspecialchars($app['profile_pic']); ?>" alt="Profile" class="avatar-lg">
                                    <?php else: ?>
                                        <div class="avatar-lg"
                                            style="background: linear-gradient(135deg, var(--primary-color), var(--secondary-color)); display: flex; align-items: center; justify-content: center; font-size: 1.5rem; font-weight: 700;">
                                            <?php echo strtoupper(substr($app['candidate_name'], 0, 1)); ?>
                                        </div>
                                    <?php endif; ?>

                                    <div style="flex: 1;">
                                        <h3><?php echo htmlspecialchars($app['candidate_name']); ?></h3>
                                        <p style="color: var(--text-secondary); margin: 0.25rem 0;">
                                            Applied for: <strong><?php echo htmlspecialchars($app['job_title']); ?></strong>
                                        </p>
                                        <p style="color: var(--text-secondary); margin: 0.25rem 0;">
                                            <?php echo htmlspecialchars($app['candidate_email']); ?>
                                            <?php if ($app['candidate_phone']): ?>
                                                | <?php echo htmlspecialchars($app['candidate_phone']); ?>
                                            <?php endif; ?>
                                        </p>
                                        <p style="color: var(--text-muted); font-size: 0.875rem; margin: 0.5rem 0;">
                                            Applied: <?php echo date('M d, Y', strtotime($app['applied_at'])); ?>
                                        </p>

                                        <?php if ($app['cover_letter']): ?>
                                            <div
                                                style="margin: 1rem 0; padding: 1rem; background: rgba(255, 255, 255, 0.05); border-radius: var(--radius-md);">
                                                <strong style="display: block; margin-bottom: 0.5rem;">Cover Letter:</strong>
                                                <p style="margin: 0;"><?php echo nl2br(htmlspecialchars($app['cover_letter'])); ?></p>
                                            </div>
                                        <?php endif; ?>

                                        <div class="flex gap-sm" style="margin-top: 1rem;">
                                            <a href="view_candidate.php?id=<?php echo $app['candidate_id']; ?>"
                                                class="btn btn-sm btn-primary">View Profile</a>
                                            <form method="POST" style="display: inline;">
                                                <input type="hidden" name="application_id" value="<?php echo $app['id']; ?>">
                                                <input type="hidden" name="status" value="reviewed">
                                                <button type="submit" name="update_status" class="btn btn-sm btn-outline">Mark as
                                                    Reviewed</button>
                                            </form>
                                            <form method="POST" style="display: inline;">
                                                <input type="hidden" name="application_id" value="<?php echo $app['id']; ?>">
                                                <input type="hidden" name="status" value="accepted">
                                                <button type="submit" name="update_status"
                                                    class="btn btn-sm btn-secondary">Accept</button>
                                            </form>
                                            <form method="POST" style="display: inline;">
                                                <input type="hidden" name="application_id" value="<?php echo $app['id']; ?>">
                                                <input type="hidden" name="status" value="rejected">
                                                <button type="submit" name="update_status" class="btn btn-sm btn-outline"
                                                    style="border-color: var(--error); color: var(--error);">Reject</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- All Applications Table -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">All Applications (<?php echo count($applications); ?>)</h3>
                </div>
                <div class="card-body">
                    <div style="overflow-x: auto;">
                        <table style="width: 100%; border-collapse: collapse;">
                            <thead>
                                <tr style="border-bottom: 1px solid rgba(255, 255, 255, 0.1);">
                                    <th style="padding: 1rem; text-align: left;">Candidate</th>
                                    <th style="padding: 1rem; text-align: left;">Job</th>
                                    <th style="padding: 1rem; text-align: left;">Applied</th>
                                    <th style="padding: 1rem; text-align: left;">Status</th>
                                    <th style="padding: 1rem; text-align: left;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($applications as $app): ?>
                                    <tr style="border-bottom: 1px solid rgba(255, 255, 255, 0.05);">
                                        <td style="padding: 1rem;">
                                            <strong><?php echo htmlspecialchars($app['candidate_name']); ?></strong>
                                            <br>
                                            <span style="color: var(--text-secondary); font-size: 0.875rem;">
                                                <?php echo htmlspecialchars($app['candidate_email']); ?>
                                            </span>
                                        </td>
                                        <td style="padding: 1rem; color: var(--text-secondary);">
                                            <?php echo htmlspecialchars($app['job_title']); ?>
                                        </td>
                                        <td style="padding: 1rem; color: var(--text-secondary);">
                                            <?php echo date('M d, Y', strtotime($app['applied_at'])); ?>
                                        </td>
                                        <td style="padding: 1rem;">
                                            <?php
                                            $badge_class = [
                                                'pending' => 'badge-warning',
                                                'reviewed' => 'badge-info',
                                                'accepted' => 'badge-success',
                                                'rejected' => 'badge-error'
                                            ][$app['status']];
                                            ?>
                                            <span class="badge <?php echo $badge_class; ?>">
                                                <?php echo htmlspecialchars($app['status']); ?>
                                            </span>
                                        </td>
                                        <td style="padding: 1rem;">
                                            <a href="view_candidate.php?id=<?php echo $app['candidate_id']; ?>"
                                                class="btn btn-sm btn-outline">View</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </main>
</div>

<?php require_once 'footer.php'; ?>
