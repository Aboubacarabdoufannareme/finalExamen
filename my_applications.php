<?php
$page_title = 'My Applications - DigiCareer Niger';
require_once 'header.php';
require_once 'db_connect.php';
require_role('candidate');

$user_id = get_user_id();

// Get all applications
$stmt = $pdo->prepare("
    SELECT a.*, j.title, j.location, j.job_type, u.name as employer_name, cp.company_name, cp.company_logo
    FROM niger_applications a
    JOIN niger_jobs j ON a.job_id = j.id
    JOIN niger_users u ON j.employer_id = u.id
    LEFT JOIN niger_company_profiles cp ON u.id = cp.user_id
    WHERE a.candidate_id = ?
    ORDER BY a.applied_at DESC
");
$stmt->execute([$user_id]);
$applications = $stmt->fetchAll();
?>

<div class="dashboard">
    <aside class="sidebar">
        <ul class="sidebar-menu">
            <li class="sidebar-item">
                <a href="candidate_dashboard.php" class="sidebar-link">📊 Dashboard</a>
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
                <a href="my_applications.php" class="sidebar-link active">📋 My Applications</a>
            </li>
            <li class="sidebar-item">
                <a href="invitations.php" class="sidebar-link">✉️ Invitations</a>
            </li>
        </ul>
    </aside>
    
    <main class="main-content">
        <h1 style="margin-bottom: 2rem;">My Applications</h1>
        
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">All Applications (<?php echo count($applications); ?>)</h3>
            </div>
            <div class="card-body">
                <?php if (empty($applications)): ?>
                    <p style="text-align: center; color: var(--text-muted); padding: 2rem;">
                        You haven't applied to any jobs yet. <a href="browse_jobs.php">Browse jobs</a> to get started!
                    </p>
                <?php else: ?>
                    <div class="grid gap-md">
                        <?php foreach ($applications as $app): ?>
                            <div class="card" style="padding: 1.5rem;">
                                <div class="flex gap-lg">
                                    <?php if ($app['company_logo']): ?>
                                        <img src="<?php echo htmlspecialchars($app['company_logo']); ?>" alt="Logo" class="avatar-lg">
                                    <?php else: ?>
                                        <div class="avatar-lg" style="background: linear-gradient(135deg, var(--primary-color), var(--secondary-color)); display: flex; align-items: center; justify-content: center; font-size: 1.5rem; font-weight: 700;">
                                            <?php echo strtoupper(substr($app['company_name'] ?? $app['employer_name'], 0, 1)); ?>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <div style="flex: 1;">
                                        <div class="flex-between" style="margin-bottom: 0.5rem;">
                                            <h3><?php echo htmlspecialchars($app['title']); ?></h3>
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
                                            <?php echo htmlspecialchars($app['company_name'] ?? $app['employer_name']); ?>
                                        </p>
                                        
                                        <div class="flex gap-md" style="flex-wrap: wrap; margin-bottom: 1rem;">
                                            <span class="badge badge-info"><?php echo htmlspecialchars($app['job_type']); ?></span>
                                            <?php if ($app['location']): ?>
                                                <span style="color: var(--text-secondary); font-size: 0.875rem;">
                                                    📍 <?php echo htmlspecialchars($app['location']); ?>
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                        
                                        <p style="color: var(--text-muted); font-size: 0.875rem; margin-bottom: 1rem;">
                                            Applied: <?php echo date('M d, Y', strtotime($app['applied_at'])); ?>
                                        </p>
                                        
                                        <?php if ($app['cover_letter']): ?>
                                            <div style="margin-bottom: 1rem; padding: 1rem; background: rgba(255, 255, 255, 0.05); border-radius: var(--radius-md);">
                                                <strong style="display: block; margin-bottom: 0.5rem; font-size: 0.875rem;">Your Cover Letter:</strong>
                                                <p style="margin: 0; font-size: 0.875rem;"><?php echo nl2br(htmlspecialchars(substr($app['cover_letter'], 0, 200))) . (strlen($app['cover_letter']) > 200 ? '...' : ''); ?></p>
                                            </div>
                                        <?php endif; ?>
                                        
                                        <a href="job_details.php?id=<?php echo $app['job_id']; ?>" class="btn btn-sm btn-outline">View Job</a>
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
