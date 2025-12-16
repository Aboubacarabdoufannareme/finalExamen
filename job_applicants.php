<?php
$page_title = 'Job Applicants - DigiCareer Niger';
require_once 'header.php';
require_once 'db_connect.php';
require_role('employer');

$user_id = get_user_id();
$job_id = (int)($_GET['id'] ?? 0);

// Verify job belongs to employer
$stmt = $pdo->prepare("SELECT * FROM niger_jobs WHERE id = ? AND employer_id = ?");
$stmt->execute([$job_id, $user_id]);
$job = $stmt->fetch();

if (!$job) {
    header('Location: my_jobs.php');
    exit;
}

// Get all applicants
$stmt = $pdo->prepare("
    SELECT a.*, u.name, u.email, u.phone, u.profile_pic
    FROM niger_applications a
    JOIN niger_users u ON a.candidate_id = u.id
    WHERE a.job_id = ?
    ORDER BY a.applied_at DESC
");
$stmt->execute([$job_id]);
$applicants = $stmt->fetchAll();
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
        <a href="my_jobs.php" class="btn btn-outline" style="margin-bottom: 2rem;">← Back to My Jobs</a>
        
        <div class="card" style="margin-bottom: 2rem;">
            <div class="card-body">
                <h1 style="margin-bottom: 0.5rem;"><?php echo htmlspecialchars($job['title']); ?></h1>
                <div class="flex gap-md" style="flex-wrap: wrap;">
                    <span class="badge badge-info"><?php echo htmlspecialchars($job['job_type']); ?></span>
                    <span class="badge <?php echo $job['status'] === 'active' ? 'badge-success' : 'badge-error'; ?>">
                        <?php echo htmlspecialchars($job['status']); ?>
                    </span>
                    <?php if ($job['location']): ?>
                        <span style="color: var(--text-secondary);">📍 <?php echo htmlspecialchars($job['location']); ?></span>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Applicants (<?php echo count($applicants); ?>)</h3>
            </div>
            <div class="card-body">
                <?php if (empty($applicants)): ?>
                    <p style="text-align: center; color: var(--text-muted); padding: 2rem;">
                        No applications received yet for this job.
                    </p>
                <?php else: ?>
                    <div class="grid gap-md">
                        <?php foreach ($applicants as $app): ?>
                            <div class="card" style="padding: 1.5rem;">
                                <div class="flex gap-lg">
                                    <?php if ($app['profile_pic']): ?>
                                        <img src="<?php echo htmlspecialchars($app['profile_pic']); ?>" alt="Profile" class="avatar-lg">
                                    <?php else: ?>
                                        <div class="avatar-lg" style="background: linear-gradient(135deg, var(--primary-color), var(--secondary-color)); display: flex; align-items: center; justify-content: center; font-size: 1.5rem; font-weight: 700;">
                                            <?php echo strtoupper(substr($app['name'], 0, 1)); ?>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <div style="flex: 1;">
                                        <div class="flex-between" style="margin-bottom: 0.5rem;">
                                            <h3><?php echo htmlspecialchars($app['name']); ?></h3>
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
                                        </div>
                                        
                                        <p style="color: var(--text-secondary); margin-bottom: 0.5rem;">
                                            <?php echo htmlspecialchars($app['email']); ?>
                                            <?php if ($app['phone']): ?>
                                                | <?php echo htmlspecialchars($app['phone']); ?>
                                            <?php endif; ?>
                                        </p>
                                        
                                        <p style="color: var(--text-muted); font-size: 0.875rem; margin-bottom: 1rem;">
                                            Applied: <?php echo date('M d, Y', strtotime($app['applied_at'])); ?>
                                        </p>
                                        
                                        <?php if ($app['cover_letter']): ?>
                                            <div style="margin-bottom: 1rem; padding: 1rem; background: rgba(255, 255, 255, 0.05); border-radius: var(--radius-md);">
                                                <strong style="display: block; margin-bottom: 0.5rem;">Cover Letter:</strong>
                                                <p style="margin: 0;"><?php echo nl2br(htmlspecialchars($app['cover_letter'])); ?></p>
                                            </div>
                                        <?php endif; ?>
                                        
                                        <a href="view_candidate.php?id=<?php echo $app['candidate_id']; ?>" class="btn btn-sm btn-primary">View Full Profile</a>
                                    </div>
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
