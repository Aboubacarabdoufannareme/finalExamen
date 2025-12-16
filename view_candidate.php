<?php
$page_title = 'View Candidate - DigiCareer Niger';
require_once 'header.php';
require_once 'db_connect.php';
require_role('employer');

$candidate_id = (int) ($_GET['id'] ?? 0);

// Get candidate data
$stmt = $pdo->prepare("SELECT * FROM niger_users WHERE id = ? AND role = 'candidate'");
$stmt->execute([$candidate_id]);
$candidate = $stmt->fetch();

if (!$candidate) {
    header('Location: search_candidates.php');
    exit;
}

// Get education
$stmt = $pdo->prepare("SELECT * FROM niger_education WHERE user_id = ? ORDER BY start_date DESC");
$stmt->execute([$candidate_id]);
$education = $stmt->fetchAll();

// Get skills
$stmt = $pdo->prepare("SELECT * FROM niger_skills WHERE user_id = ?");
$stmt->execute([$candidate_id]);
$skills = $stmt->fetchAll();

// Get experience
$stmt = $pdo->prepare("SELECT * FROM niger_experience WHERE user_id = ? ORDER BY start_date DESC");
$stmt->execute([$candidate_id]);
$experience = $stmt->fetchAll();

// Get documents
$stmt = $pdo->prepare("SELECT * FROM niger_documents WHERE user_id = ? ORDER BY uploaded_at DESC");
$stmt->execute([$candidate_id]);
$documents = $stmt->fetchAll();
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
                <a href="applications_received.php" class="sidebar-link">📬 Applications</a>
            </li>
            <li class="sidebar-item">
                <a href="search_candidates.php" class="sidebar-link active">🔍 Search Candidates</a>
            </li>
        </ul>
    </aside>

    <main class="main-content">
        <div class="flex-between" style="margin-bottom: 2rem;">
            <h1>Candidate Profile</h1>
            <a href="search_candidates.php" class="btn btn-outline">← Back to Search</a>
        </div>

        <!-- Profile Header -->
        <div class="card" style="margin-bottom: 2rem;">
            <div class="card-body">
                <div class="flex gap-lg" style="align-items: start;">
                    <?php if ($candidate['profile_pic']): ?>
                        <img src="<?php echo htmlspecialchars($candidate['profile_pic']); ?>" alt="Profile"
                            class="avatar-xl">
                    <?php else: ?>
                        <div class="avatar-xl"
                            style="background: linear-gradient(135deg, var(--primary-color), var(--secondary-color)); display: flex; align-items: center; justify-content: center; font-size: 2rem; font-weight: 700;">
                            <?php echo strtoupper(substr($candidate['name'], 0, 1)); ?>
                        </div>
                    <?php endif; ?>

                    <div style="flex: 1;">
                        <h2><?php echo htmlspecialchars($candidate['name']); ?></h2>
                        <p style="color: var(--text-secondary);">
                            <?php echo htmlspecialchars($candidate['email']); ?>
                            <?php if ($candidate['phone']): ?>
                                | <?php echo htmlspecialchars($candidate['phone']); ?>
                            <?php endif; ?>
                        </p>
                        <?php if ($candidate['bio']): ?>
                            <p style="margin-top: 1rem;"><?php echo nl2br(htmlspecialchars($candidate['bio'])); ?></p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Skills -->
        <?php if (!empty($skills)): ?>
            <div class="card" style="margin-bottom: 2rem;">
                <div class="card-header">
                    <h3 class="card-title">Skills</h3>
                </div>
                <div class="card-body">
                    <div class="flex gap-sm" style="flex-wrap: wrap;">
                        <?php foreach ($skills as $skill): ?>
                            <span class="badge badge-info">
                                <?php echo htmlspecialchars($skill['skill_name']); ?>
                                <span style="font-size: 0.75rem; opacity: 0.8;">(<?php echo $skill['proficiency']; ?>)</span>
                            </span>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Education -->
        <?php if (!empty($education)): ?>
            <div class="card" style="margin-bottom: 2rem;">
                <div class="card-header">
                    <h3 class="card-title">Education</h3>
                </div>
                <div class="card-body">
                    <?php foreach ($education as $edu): ?>
                        <div
                            style="margin-bottom: 1.5rem; padding-bottom: 1.5rem; border-bottom: 1px solid rgba(255, 255, 255, 0.1);">
                            <strong style="font-size: 1.125rem;"><?php echo htmlspecialchars($edu['degree']); ?></strong>
                            <p style="color: var(--text-secondary); margin: 0.25rem 0;">
                                <?php echo htmlspecialchars($edu['institution']); ?>
                                <?php if ($edu['field_of_study']): ?>
                                    - <?php echo htmlspecialchars($edu['field_of_study']); ?>
                                <?php endif; ?>
                            </p>
                            <?php if ($edu['start_date'] || $edu['end_date']): ?>
                                <p style="color: var(--text-muted); font-size: 0.875rem;">
                                    <?php echo $edu['start_date'] ? date('M Y', strtotime($edu['start_date'])) : ''; ?>
                                    - <?php echo $edu['end_date'] ? date('M Y', strtotime($edu['end_date'])) : 'Present'; ?>
                                </p>
                            <?php endif; ?>
                            <?php if ($edu['description']): ?>
                                <p style="margin-top: 0.5rem;"><?php echo nl2br(htmlspecialchars($edu['description'])); ?></p>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- Experience -->
        <?php if (!empty($experience)): ?>
            <div class="card" style="margin-bottom: 2rem;">
                <div class="card-header">
                    <h3 class="card-title">Work Experience</h3>
                </div>
                <div class="card-body">
                    <?php foreach ($experience as $exp): ?>
                        <div
                            style="margin-bottom: 1.5rem; padding-bottom: 1.5rem; border-bottom: 1px solid rgba(255, 255, 255, 0.1);">
                            <strong style="font-size: 1.125rem;"><?php echo htmlspecialchars($exp['job_title']); ?></strong>
                            <p style="color: var(--text-secondary); margin: 0.25rem 0;">
                                <?php echo htmlspecialchars($exp['company']); ?>
                                <?php if ($exp['location']): ?>
                                    - <?php echo htmlspecialchars($exp['location']); ?>
                                <?php endif; ?>
                            </p>
                            <?php if ($exp['start_date'] || $exp['end_date'] || $exp['is_current']): ?>
                                <p style="color: var(--text-muted); font-size: 0.875rem;">
                                    <?php echo $exp['start_date'] ? date('M Y', strtotime($exp['start_date'])) : ''; ?>
                                    -
                                    <?php echo $exp['is_current'] ? 'Present' : ($exp['end_date'] ? date('M Y', strtotime($exp['end_date'])) : ''); ?>
                                </p>
                            <?php endif; ?>
                            <?php if ($exp['description']): ?>
                                <p style="margin-top: 0.5rem;"><?php echo nl2br(htmlspecialchars($exp['description'])); ?></p>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- Documents -->
        <?php if (!empty($documents)): ?>
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Documents & Certificates</h3>
                </div>
                <div class="card-body">
                    <div class="grid grid-2 gap-md">
                        <?php foreach ($documents as $doc): ?>
                            <div style="padding: 1rem; background: rgba(255, 255, 255, 0.05); border-radius: var(--radius-md);">
                                <div class="flex-between" style="margin-bottom: 0.5rem;">
                                    <strong><?php echo htmlspecialchars($doc['document_name']); ?></strong>
                                    <span class="badge badge-info"><?php echo strtoupper($doc['document_type']); ?></span>
                                </div>
                                <p style="color: var(--text-muted); font-size: 0.875rem; margin-bottom: 0.5rem;">
                                    <?php echo date('M d, Y', strtotime($doc['uploaded_at'])); ?>
                                </p>
                                <div class="flex gap-sm">
                                    <a href="<?php echo htmlspecialchars($doc['file_path']); ?>" target="_blank"
                                        class="btn btn-sm btn-primary">View</a>
                                    <a href="<?php echo htmlspecialchars($doc['file_path']); ?>" download
                                        class="btn btn-sm btn-outline">Download</a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </main>
</div>

<?php require_once 'footer.php'; ?>
