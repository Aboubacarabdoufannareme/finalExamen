<?php
$page_title = 'My Profile - DigiCareer Niger';
require_once 'header.php';
require_once 'db_connect.php';
require_role('candidate');

$user_id = get_user_id();
$success = '';
$error = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_profile'])) {
        $name = sanitize($_POST['name'] ?? '');
        $phone = sanitize($_POST['phone'] ?? '');
        $bio = sanitize($_POST['bio'] ?? '');

        // Handle profile picture upload
        $profile_pic = $_SESSION['profile_pic'];
        if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] === 0) {
            $allowed = ['jpg', 'jpeg', 'png', 'gif'];
            $filename = $_FILES['profile_pic']['name'];
            $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

            if (in_array($ext, $allowed)) {
                $upload_dir = 'uploads/profiles/';
                if (!file_exists($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                }

                $new_filename = 'profile_' . $user_id . '_' . time() . '.' . $ext;
                $upload_path = $upload_dir . $new_filename;

                if (move_uploaded_file($_FILES['profile_pic']['tmp_name'], $upload_path)) {
                    $profile_pic = $upload_path;
                }
            }
        }

        $stmt = $pdo->prepare("UPDATE niger_users SET name = ?, phone = ?, bio = ?, profile_pic = ? WHERE id = ?");
        if ($stmt->execute([$name, $phone, $bio, $profile_pic, $user_id])) {
            $_SESSION['user_name'] = $name;
            $_SESSION['profile_pic'] = $profile_pic;
            $success = 'Profile updated successfully!';
        } else {
            $error = 'Failed to update profile.';
        }
    }
}

// Get user data
$stmt = $pdo->prepare("SELECT * FROM niger_users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

// Get education
$stmt = $pdo->prepare("SELECT * FROM niger_education WHERE user_id = ? ORDER BY start_date DESC");
$stmt->execute([$user_id]);
$education = $stmt->fetchAll();

// Get skills
$stmt = $pdo->prepare("SELECT * FROM niger_skills WHERE user_id = ?");
$stmt->execute([$user_id]);
$skills = $stmt->fetchAll();

// Get experience
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
                <a href="profile.php" class="sidebar-link active">👤 My Profile</a>
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
                <a href="invitations.php" class="sidebar-link">✉️ Invitations</a>
            </li>
        </ul>
    </aside>

    <main class="main-content">
        <h1 style="margin-bottom: 2rem;">My Profile</h1>

        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <!-- Edit Profile -->
        <div class="card" style="margin-bottom: 2rem;">
            <div class="card-header">
                <h3 class="card-title">Edit Profile</h3>
            </div>
            <div class="card-body">
                <form method="POST" enctype="multipart/form-data">
                    <div class="grid grid-2 gap-md">
                        <div class="form-group">
                            <label class="form-label">Full Name</label>
                            <input type="text" name="name" class="form-control"
                                value="<?php echo htmlspecialchars($user['name']); ?>" required>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Phone Number</label>
                            <input type="tel" name="phone" class="form-control"
                                value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>"
                                placeholder="+227 XX XX XX XX">
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Bio / About Me</label>
                        <textarea name="bio" class="form-control" rows="4"
                            placeholder="Tell employers about yourself..."><?php echo htmlspecialchars($user['bio'] ?? ''); ?></textarea>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Profile Picture</label>
                        <?php if ($user['profile_pic']): ?>
                            <div style="margin-bottom: 1rem;">
                                <img src="<?php echo htmlspecialchars($user['profile_pic']); ?>" alt="Profile"
                                    class="avatar-xl">
                            </div>
                        <?php endif; ?>
                        <input type="file" name="profile_pic" class="form-control" accept="image/*">
                    </div>

                    <button type="submit" name="update_profile" class="btn btn-primary">Save Changes</button>
                </form>
            </div>
        </div>

        <!-- Profile Preview -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Profile Preview</h3>
                <p style="color: var(--text-muted); margin: 0; font-size: 0.875rem;">This is how employers see your
                    profile</p>
            </div>
            <div class="card-body">
                <div class="flex gap-lg" style="align-items: start;">
                    <?php if ($user['profile_pic']): ?>
                        <img src="<?php echo htmlspecialchars($user['profile_pic']); ?>" alt="Profile" class="avatar-xl">
                    <?php else: ?>
                        <div class="avatar-xl"
                            style="background: linear-gradient(135deg, var(--primary-color), var(--secondary-color)); display: flex; align-items: center; justify-content: center; font-size: 2rem; font-weight: 700;">
                            <?php echo strtoupper(substr($user['name'], 0, 1)); ?>
                        </div>
                    <?php endif; ?>

                    <div style="flex: 1;">
                        <h2><?php echo htmlspecialchars($user['name']); ?></h2>
                        <p style="color: var(--text-secondary);">
                            <?php echo htmlspecialchars($user['email']); ?>
                            <?php if ($user['phone']): ?>
                                | <?php echo htmlspecialchars($user['phone']); ?>
                            <?php endif; ?>
                        </p>
                        <?php if ($user['bio']): ?>
                            <p style="margin-top: 1rem;"><?php echo nl2br(htmlspecialchars($user['bio'])); ?></p>
                        <?php endif; ?>
                    </div>
                </div>

                <?php if (!empty($skills)): ?>
                    <div style="margin-top: 2rem;">
                        <h4>Skills</h4>
                        <div class="flex gap-sm" style="flex-wrap: wrap; margin-top: 1rem;">
                            <?php foreach ($skills as $skill): ?>
                                <span class="badge badge-info"><?php echo htmlspecialchars($skill['skill_name']); ?></span>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if (!empty($education)): ?>
                    <div style="margin-top: 2rem;">
                        <h4>Education</h4>
                        <?php foreach ($education as $edu): ?>
                            <div
                                style="margin-top: 1rem; padding: 1rem; background: rgba(255, 255, 255, 0.05); border-radius: var(--radius-md);">
                                <strong><?php echo htmlspecialchars($edu['degree']); ?></strong>
                                <p style="color: var(--text-secondary); margin: 0.25rem 0;">
                                    <?php echo htmlspecialchars($edu['institution']); ?>
                                    <?php if ($edu['field_of_study']): ?>
                                        - <?php echo htmlspecialchars($edu['field_of_study']); ?>
                                    <?php endif; ?>
                                </p>
                                <?php if ($edu['start_date'] || $edu['end_date']): ?>
                                    <p style="color: var(--text-muted); font-size: 0.875rem; margin: 0;">
                                        <?php echo $edu['start_date'] ? date('M Y', strtotime($edu['start_date'])) : ''; ?>
                                        - <?php echo $edu['end_date'] ? date('M Y', strtotime($edu['end_date'])) : 'Present'; ?>
                                    </p>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <?php if (!empty($experience)): ?>
                    <div style="margin-top: 2rem;">
                        <h4>Experience</h4>
                        <?php foreach ($experience as $exp): ?>
                            <div
                                style="margin-top: 1rem; padding: 1rem; background: rgba(255, 255, 255, 0.05); border-radius: var(--radius-md);">
                                <strong><?php echo htmlspecialchars($exp['job_title']); ?></strong>
                                <p style="color: var(--text-secondary); margin: 0.25rem 0;">
                                    <?php echo htmlspecialchars($exp['company']); ?>
                                    <?php if ($exp['location']): ?>
                                        - <?php echo htmlspecialchars($exp['location']); ?>
                                    <?php endif; ?>
                                </p>
                                <?php if ($exp['start_date'] || $exp['end_date'] || $exp['is_current']): ?>
                                    <p style="color: var(--text-muted); font-size: 0.875rem; margin: 0;">
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
                <?php endif; ?>

                <div style="margin-top: 2rem; text-align: center;">
                    <a href="cv_builder.php" class="btn btn-secondary">Add Education, Skills & Experience</a>
                </div>
            </div>
        </div>
    </main>
</div>

<?php require_once 'footer.php'; ?>
