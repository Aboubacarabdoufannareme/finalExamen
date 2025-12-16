<?php
$page_title = 'Browse Jobs - DigiCareer Niger';
require_once 'header.php';
require_once 'db_connect.php';
require_role('candidate');

$user_id = get_user_id();

// Search and filter
$search = sanitize($_GET['q'] ?? '');
$job_type = sanitize($_GET['type'] ?? '');
$location = sanitize($_GET['location'] ?? '');

// Build query
$sql = "SELECT j.*, u.name as employer_name, cp.company_name, cp.company_logo,
        (SELECT COUNT(*) FROM niger_applications WHERE job_id = j.id AND candidate_id = ?) as has_applied
        FROM niger_jobs j
        JOIN niger_users u ON j.employer_id = u.id
        LEFT JOIN niger_company_profiles cp ON u.id = cp.user_id
        WHERE j.status = 'active'";

$params = [$user_id];

if ($search) {
    $sql .= " AND (j.title LIKE ? OR j.description LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if ($job_type) {
    $sql .= " AND j.job_type = ?";
    $params[] = $job_type;
}

if ($location) {
    $sql .= " AND j.location LIKE ?";
    $params[] = "%$location%";
}

$sql .= " ORDER BY j.created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$jobs = $stmt->fetchAll();
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
                <a href="browse_jobs.php" class="sidebar-link active">🔍 Browse Jobs</a>
            </li>
            <li class="sidebar-item">
                <a href="my_applications.php" class="sidebar-link">📋 My Applications</a>
            </li>
            <li class="sidebar-item">
                <a href="invitations.php" class="sidebar-link">✉️ Invitations</a>
            </li>
        </ul>
    </aside>

    <main class="main-content">
        <h1 style="margin-bottom: 2rem;">Browse Jobs</h1>

        <!-- Search & Filter -->
        <div class="card" style="margin-bottom: 2rem;">
            <div class="card-body">
                <form method="GET">
                    <div class="grid grid-3 gap-md">
                        <div class="form-group">
                            <label class="form-label">Search</label>
                            <input type="text" name="q" class="form-control" placeholder="Job title or keywords..."
                                value="<?php echo htmlspecialchars($search); ?>">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Job Type</label>
                            <select name="type" class="form-control">
                                <option value="">All Types</option>
                                <option value="full-time" <?php echo $job_type === 'full-time' ? 'selected' : ''; ?>>
                                    Full-time</option>
                                <option value="part-time" <?php echo $job_type === 'part-time' ? 'selected' : ''; ?>>
                                    Part-time</option>
                                <option value="internship" <?php echo $job_type === 'internship' ? 'selected' : ''; ?>>
                                    Internship</option>
                                <option value="contract" <?php echo $job_type === 'contract' ? 'selected' : ''; ?>>
                                    Contract</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Location</label>
                            <input type="text" name="location" class="form-control" placeholder="City or region..."
                                value="<?php echo htmlspecialchars($location); ?>">
                        </div>
                    </div>
                    <div class="flex gap-sm">
                        <button type="submit" class="btn btn-primary">Search</button>
                        <a href="browse_jobs.php" class="btn btn-outline">Clear</a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Jobs List -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Available Jobs (<?php echo count($jobs); ?>)</h3>
            </div>
            <div class="card-body">
                <?php if (empty($jobs)): ?>
                    <p style="text-align: center; color: var(--text-muted); padding: 2rem;">
                        No jobs found. Try adjusting your search criteria.
                    </p>
                <?php else: ?>
                    <div class="grid gap-md">
                        <?php foreach ($jobs as $job): ?>
                            <div class="card" style="padding: 1.5rem;">
                                <div class="flex gap-lg">
                                    <?php if ($job['company_logo']): ?>
                                        <img src="<?php echo htmlspecialchars($job['company_logo']); ?>" alt="Logo"
                                            class="avatar-lg">
                                    <?php else: ?>
                                        <div class="avatar-lg"
                                            style="background: linear-gradient(135deg, var(--primary-color), var(--secondary-color)); display: flex; align-items: center; justify-content: center; font-size: 1.5rem; font-weight: 700;">
                                            <?php echo strtoupper(substr($job['company_name'] ?? $job['employer_name'], 0, 1)); ?>
                                        </div>
                                    <?php endif; ?>

                                    <div style="flex: 1;">
                                        <div class="flex-between" style="margin-bottom: 0.5rem;">
                                            <h3><?php echo htmlspecialchars($job['title']); ?></h3>
                                            <span
                                                class="badge badge-info"><?php echo htmlspecialchars($job['job_type']); ?></span>
                                        </div>

                                        <p style="color: var(--text-secondary); margin-bottom: 0.5rem;">
                                            <?php echo htmlspecialchars($job['company_name'] ?? $job['employer_name']); ?>
                                        </p>

                                        <?php if ($job['location']): ?>
                                            <p style="color: var(--text-secondary); font-size: 0.875rem; margin-bottom: 1rem;">
                                                📍 <?php echo htmlspecialchars($job['location']); ?>
                                                <?php if ($job['salary_range']): ?>
                                                    | 💰 <?php echo htmlspecialchars($job['salary_range']); ?>
                                                <?php endif; ?>
                                            </p>
                                        <?php endif; ?>

                                        <p style="margin-bottom: 1rem;">
                                            <?php echo htmlspecialchars(substr($job['description'], 0, 200)) . '...'; ?>
                                        </p>

                                        <p style="color: var(--text-muted); font-size: 0.875rem; margin-bottom: 1rem;">
                                            Posted: <?php echo date('M d, Y', strtotime($job['created_at'])); ?>
                                        </p>

                                        <div class="flex gap-sm">
                                            <a href="job_details.php?id=<?php echo $job['id']; ?>"
                                                class="btn btn-sm btn-primary">View Details</a>
                                            <?php if ($job['has_applied'] > 0): ?>
                                                <span class="btn btn-sm btn-outline" style="cursor: default; opacity: 0.6;">Already
                                                    Applied</span>
                                            <?php else: ?>
                                                <a href="job_details.php?id=<?php echo $job['id']; ?>#apply"
                                                    class="btn btn-sm btn-secondary">Apply Now</a>
                                            <?php endif; ?>
                                        </div>
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
