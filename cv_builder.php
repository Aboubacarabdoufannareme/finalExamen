<?php
$page_title = 'CV Builder - DigiCareer Niger';
require_once 'header.php';
require_once 'db_connect.php';
require_role('candidate');

$user_id = get_user_id();
$success = '';
$error = '';

// Handle Education
if (isset($_POST['add_education'])) {
    $institution = sanitize($_POST['institution'] ?? '');
    $degree = sanitize($_POST['degree'] ?? '');
    $field_of_study = sanitize($_POST['field_of_study'] ?? '');
    $start_date = $_POST['start_date'] ?? null;
    $end_date = $_POST['end_date'] ?? null;
    $description = sanitize($_POST['edu_description'] ?? '');

    $stmt = $pdo->prepare("INSERT INTO niger_education (user_id, institution, degree, field_of_study, start_date, end_date, description) VALUES (?, ?, ?, ?, ?, ?, ?)");
    if ($stmt->execute([$user_id, $institution, $degree, $field_of_study, $start_date, $end_date, $description])) {
        $success = 'Education added successfully!';
    }
}

// Handle Skills
if (isset($_POST['add_skill'])) {
    $skill_name = sanitize($_POST['skill_name'] ?? '');
    $proficiency = sanitize($_POST['proficiency'] ?? 'intermediate');

    $stmt = $pdo->prepare("INSERT INTO niger_skills (user_id, skill_name, proficiency) VALUES (?, ?, ?)");
    if ($stmt->execute([$user_id, $skill_name, $proficiency])) {
        $success = 'Skill added successfully!';
    }
}

// Handle Experience
if (isset($_POST['add_experience'])) {
    $job_title = sanitize($_POST['job_title'] ?? '');
    $company = sanitize($_POST['company'] ?? '');
    $location = sanitize($_POST['exp_location'] ?? '');
    $start_date = $_POST['exp_start_date'] ?? null;
    $end_date = $_POST['exp_end_date'] ?? null;
    $is_current = isset($_POST['is_current']) ? 1 : 0;
    $description = sanitize($_POST['exp_description'] ?? '');

    $stmt = $pdo->prepare("INSERT INTO niger_experience (user_id, job_title, company, location, start_date, end_date, is_current, description) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    if ($stmt->execute([$user_id, $job_title, $company, $location, $start_date, $end_date, $is_current, $description])) {
        $success = 'Experience added successfully!';
    }
}

// Handle deletions
if (isset($_GET['delete_edu'])) {
    $stmt = $pdo->prepare("DELETE FROM niger_education WHERE id = ? AND user_id = ?");
    $stmt->execute([$_GET['delete_edu'], $user_id]);
    $success = 'Education deleted!';
}
if (isset($_GET['delete_skill'])) {
    $stmt = $pdo->prepare("DELETE FROM niger_skills WHERE id = ? AND user_id = ?");
    $stmt->execute([$_GET['delete_skill'], $user_id]);
    $success = 'Skill deleted!';
}
if (isset($_GET['delete_exp'])) {
    $stmt = $pdo->prepare("DELETE FROM niger_experience WHERE id = ? AND user_id = ?");
    $stmt->execute([$_GET['delete_exp'], $user_id]);
    $success = 'Experience deleted!';
}

// Get data
$stmt = $pdo->prepare("SELECT * FROM niger_education WHERE user_id = ? ORDER BY start_date DESC");
$stmt->execute([$user_id]);
$education = $stmt->fetchAll();

$stmt = $pdo->prepare("SELECT * FROM niger_skills WHERE user_id = ?");
$stmt->execute([$user_id]);
$skills = $stmt->fetchAll();

$stmt = $pdo->prepare("SELECT * FROM niger_experience WHERE user_id = ? ORDER BY start_date DESC");
$stmt->execute([$user_id]);
$experience = $stmt->fetchAll();
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
                <a href="cv_builder.php" class="sidebar-link active">📝 CV Builder</a>
            </li>
            <li class="sidebar-item">
                <a href="browse_jobs.php" class="sidebar-link">🔍 Browse Jobs</a>
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
        <h1 style="margin-bottom: 2rem;">CV Builder</h1>

        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>

        <!-- Education Section -->
        <div class="card" style="margin-bottom: 2rem;">
            <div class="card-header">
                <h3 class="card-title">Education</h3>
            </div>
            <div class="card-body">
                <form method="POST"
                    style="margin-bottom: 2rem; padding: 1rem; background: rgba(99, 102, 241, 0.05); border-radius: var(--radius-md);">
                    <h4 style="margin-bottom: 1rem;">Add Education</h4>
                    <div class="grid grid-2 gap-md">
                        <div class="form-group">
                            <label class="form-label">Institution</label>
                            <input type="text" name="institution" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Degree</label>
                            <input type="text" name="degree" class="form-control" placeholder="e.g., Bachelor's"
                                required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Field of Study</label>
                            <input type="text" name="field_of_study" class="form-control"
                                placeholder="e.g., Computer Science">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Start Date</label>
                            <input type="date" name="start_date" class="form-control">
                        </div>
                        <div class="form-group">
                            <label class="form-label">End Date</label>
                            <input type="date" name="end_date" class="form-control">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Description (Optional)</label>
                        <textarea name="edu_description" class="form-control" rows="2"></textarea>
                    </div>
                    <button type="submit" name="add_education" class="btn btn-primary">Add Education</button>
                </form>

                <?php if (empty($education)): ?>
                    <p style="text-align: center; color: var(--text-muted);">No education added yet.</p>
                <?php else: ?>
                    <?php foreach ($education as $edu): ?>
                        <div
                            style="padding: 1rem; background: rgba(255, 255, 255, 0.05); border-radius: var(--radius-md); margin-bottom: 1rem;">
                            <div class="flex-between">
                                <div>
                                    <strong><?php echo htmlspecialchars($edu['degree']); ?></strong>
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
                                </div>
                                <a href="?delete_edu=<?php echo $edu['id']; ?>" class="btn btn-sm btn-outline"
                                    onclick="return confirm('Delete this education?');"
                                    style="border-color: var(--error); color: var(--error);">Delete</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- Skills Section -->
        <div class="card" style="margin-bottom: 2rem;">
            <div class="card-header">
                <h3 class="card-title">Skills</h3>
            </div>
            <div class="card-body">
                <form method="POST"
                    style="margin-bottom: 2rem; padding: 1rem; background: rgba(99, 102, 241, 0.05); border-radius: var(--radius-md);">
                    <h4 style="margin-bottom: 1rem;">Add Skill</h4>
                    <div class="grid grid-2 gap-md">
                        <div class="form-group">
                            <label class="form-label">Skill Name</label>
                            <input type="text" name="skill_name" class="form-control" placeholder="e.g., JavaScript"
                                required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Proficiency</label>
                            <select name="proficiency" class="form-control">
                                <option value="beginner">Beginner</option>
                                <option value="intermediate" selected>Intermediate</option>
                                <option value="advanced">Advanced</option>
                                <option value="expert">Expert</option>
                            </select>
                        </div>
                    </div>
                    <button type="submit" name="add_skill" class="btn btn-primary">Add Skill</button>
                </form>

                <?php if (empty($skills)): ?>
                    <p style="text-align: center; color: var(--text-muted);">No skills added yet.</p>
                <?php else: ?>
                    <div class="flex gap-sm" style="flex-wrap: wrap;">
                        <?php foreach ($skills as $skill): ?>
                            <div
                                style="padding: 0.5rem 1rem; background: rgba(99, 102, 241, 0.2); border-radius: var(--radius-md); display: flex; align-items: center; gap: 0.5rem;">
                                <span><?php echo htmlspecialchars($skill['skill_name']); ?></span>
                                <span
                                    style="font-size: 0.75rem; color: var(--text-muted);">(<?php echo $skill['proficiency']; ?>)</span>
                                <a href="?delete_skill=<?php echo $skill['id']; ?>"
                                    onclick="return confirm('Delete this skill?');"
                                    style="color: var(--error); margin-left: 0.5rem;">×</a>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Experience Section -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Work Experience</h3>
            </div>
            <div class="card-body">
                <form method="POST"
                    style="margin-bottom: 2rem; padding: 1rem; background: rgba(99, 102, 241, 0.05); border-radius: var(--radius-md);">
                    <h4 style="margin-bottom: 1rem;">Add Experience</h4>
                    <div class="grid grid-2 gap-md">
                        <div class="form-group">
                            <label class="form-label">Job Title</label>
                            <input type="text" name="job_title" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Company</label>
                            <input type="text" name="company" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Location</label>
                            <input type="text" name="exp_location" class="form-control">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Start Date</label>
                            <input type="date" name="exp_start_date" class="form-control">
                        </div>
                        <div class="form-group">
                            <label class="form-label">End Date</label>
                            <input type="date" name="exp_end_date" class="form-control" id="exp_end_date">
                        </div>
                        <div class="form-group">
                            <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                                <input type="checkbox" name="is_current"
                                    onclick="document.getElementById('exp_end_date').disabled = this.checked;">
                                <span>I currently work here</span>
                            </label>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Description</label>
                        <textarea name="exp_description" class="form-control" rows="3"
                            placeholder="Describe your responsibilities and achievements..."></textarea>
                    </div>
                    <button type="submit" name="add_experience" class="btn btn-primary">Add Experience</button>
                </form>

                <?php if (empty($experience)): ?>
                    <p style="text-align: center; color: var(--text-muted);">No experience added yet.</p>
                <?php else: ?>
                    <?php foreach ($experience as $exp): ?>
                        <div
                            style="padding: 1rem; background: rgba(255, 255, 255, 0.05); border-radius: var(--radius-md); margin-bottom: 1rem;">
                            <div class="flex-between">
                                <div style="flex: 1;">
                                    <strong><?php echo htmlspecialchars($exp['job_title']); ?></strong>
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
                                        <p style="margin-top: 0.5rem;"><?php echo nl2br(htmlspecialchars($exp['description'])); ?>
                                        </p>
                                    <?php endif; ?>
                                </div>
                                <a href="?delete_exp=<?php echo $exp['id']; ?>" class="btn btn-sm btn-outline"
                                    onclick="return confirm('Delete this experience?');"
                                    style="border-color: var(--error); color: var(--error);">Delete</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </main>
</div>

<?php require_once 'footer.php'; ?>
