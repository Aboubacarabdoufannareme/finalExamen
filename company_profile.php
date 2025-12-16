<?php
$page_title = 'Company Profile - DigiCareer Niger';
require_once 'header.php';
require_once 'db_connect.php';
require_role('employer');

$user_id = get_user_id();
$success = '';
$error = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $company_name = sanitize($_POST['company_name'] ?? '');
    $industry = sanitize($_POST['industry'] ?? '');
    $company_size = sanitize($_POST['company_size'] ?? '');
    $website = sanitize($_POST['website'] ?? '');
    $location = sanitize($_POST['location'] ?? '');
    $description = sanitize($_POST['description'] ?? '');

    // Get current company profile
    $stmt = $pdo->prepare("SELECT * FROM niger_company_profiles WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $current_company = $stmt->fetch();

    $company_logo = $current_company['company_logo'] ?? null;

    // Handle logo upload
    if (isset($_FILES['company_logo']) && $_FILES['company_logo']['error'] === 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $filename = $_FILES['company_logo']['name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        if (in_array($ext, $allowed)) {
            $upload_dir = 'uploads/logos/';
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }

            $new_filename = 'logo_' . $user_id . '_' . time() . '.' . $ext;
            $upload_path = $upload_dir . $new_filename;

            if (move_uploaded_file($_FILES['company_logo']['tmp_name'], $upload_path)) {
                $company_logo = $upload_path;
            }
        }
    }

    if ($current_company) {
        // Update existing profile
        $stmt = $pdo->prepare("UPDATE niger_company_profiles SET company_name = ?, industry = ?, company_size = ?, website = ?, location = ?, description = ?, company_logo = ? WHERE user_id = ?");
        if ($stmt->execute([$company_name, $industry, $company_size, $website, $location, $description, $company_logo, $user_id])) {
            $success = 'Company profile updated successfully!';
        } else {
            $error = 'Failed to update profile.';
        }
    } else {
        // Create new profile
        $stmt = $pdo->prepare("INSERT INTO niger_company_profiles (user_id, company_name, industry, company_size, website, location, description, company_logo) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        if ($stmt->execute([$user_id, $company_name, $industry, $company_size, $website, $location, $description, $company_logo])) {
            $success = 'Company profile created successfully!';
        } else {
            $error = 'Failed to create profile.';
        }
    }
}

// Get company profile
$stmt = $pdo->prepare("SELECT * FROM niger_company_profiles WHERE user_id = ?");
$stmt->execute([$user_id]);
$company = $stmt->fetch();
?>

<div class="dashboard">
    <aside class="sidebar">
        <ul class="sidebar-menu">
            <li class="sidebar-item">
                <a href="employer_dashboard.php" class="sidebar-link">📊 Dashboard</a>
            </li>
            <li class="sidebar-item">
                <a href="company_profile.php" class="sidebar-link active">🏢 Company Profile</a>
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
                <a href="search_candidates.php" class="sidebar-link">🔍 Search Candidates</a>
            </li>
        </ul>
    </aside>

    <main class="main-content">
        <h1 style="margin-bottom: 2rem;">Company Profile</h1>

        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <!-- Edit Profile -->
        <div class="card" style="margin-bottom: 2rem;">
            <div class="card-header">
                <h3 class="card-title">Edit Company Profile</h3>
            </div>
            <div class="card-body">
                <form method="POST" enctype="multipart/form-data">
                    <div class="grid grid-2 gap-md">
                        <div class="form-group">
                            <label class="form-label">Company Name</label>
                            <input type="text" name="company_name" class="form-control"
                                value="<?php echo htmlspecialchars($company['company_name'] ?? ''); ?>" required>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Industry</label>
                            <input type="text" name="industry" class="form-control"
                                value="<?php echo htmlspecialchars($company['industry'] ?? ''); ?>"
                                placeholder="e.g., Information Technology">
                        </div>

                        <div class="form-group">
                            <label class="form-label">Company Size</label>
                            <select name="company_size" class="form-control">
                                <option value="">Select size...</option>
                                <option value="1-10" <?php echo ($company['company_size'] ?? '') === '1-10' ? 'selected' : ''; ?>>1-10 employees</option>
                                <option value="11-50" <?php echo ($company['company_size'] ?? '') === '11-50' ? 'selected' : ''; ?>>11-50 employees</option>
                                <option value="51-100" <?php echo ($company['company_size'] ?? '') === '51-100' ? 'selected' : ''; ?>>51-100 employees</option>
                                <option value="101-500" <?php echo ($company['company_size'] ?? '') === '101-500' ? 'selected' : ''; ?>>101-500 employees</option>
                                <option value="500+" <?php echo ($company['company_size'] ?? '') === '500+' ? 'selected' : ''; ?>>500+ employees</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Website</label>
                            <input type="url" name="website" class="form-control"
                                value="<?php echo htmlspecialchars($company['website'] ?? ''); ?>"
                                placeholder="https://yourcompany.com">
                        </div>

                        <div class="form-group">
                            <label class="form-label">Location</label>
                            <input type="text" name="location" class="form-control"
                                value="<?php echo htmlspecialchars($company['location'] ?? ''); ?>"
                                placeholder="e.g., Niamey, Niger">
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Company Description</label>
                        <textarea name="description" class="form-control" rows="5"
                            placeholder="Tell candidates about your company..."><?php echo htmlspecialchars($company['description'] ?? ''); ?></textarea>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Company Logo</label>
                        <?php if ($company && $company['company_logo']): ?>
                            <div style="margin-bottom: 1rem;">
                                <img src="<?php echo htmlspecialchars($company['company_logo']); ?>" alt="Logo"
                                    class="avatar-xl">
                            </div>
                        <?php endif; ?>
                        <input type="file" name="company_logo" class="form-control" accept="image/*">
                    </div>

                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </form>
            </div>
        </div>

        <!-- Profile Preview -->
        <?php if ($company): ?>
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Profile Preview</h3>
                    <p style="color: var(--text-muted); margin: 0; font-size: 0.875rem;">This is how candidates see your
                        company</p>
                </div>
                <div class="card-body">
                    <div class="flex gap-lg" style="align-items: start;">
                        <?php if ($company['company_logo']): ?>
                            <img src="<?php echo htmlspecialchars($company['company_logo']); ?>" alt="Logo" class="avatar-xl">
                        <?php else: ?>
                            <div class="avatar-xl"
                                style="background: linear-gradient(135deg, var(--primary-color), var(--secondary-color)); display: flex; align-items: center; justify-content: center; font-size: 2rem; font-weight: 700;">
                                <?php echo strtoupper(substr($company['company_name'], 0, 1)); ?>
                            </div>
                        <?php endif; ?>

                        <div style="flex: 1;">
                            <h2><?php echo htmlspecialchars($company['company_name']); ?></h2>

                            <div style="display: flex; gap: 1rem; flex-wrap: wrap; margin: 1rem 0;">
                                <?php if ($company['industry']): ?>
                                    <span class="badge badge-info"><?php echo htmlspecialchars($company['industry']); ?></span>
                                <?php endif; ?>
                                <?php if ($company['company_size']): ?>
                                    <span class="badge badge-info"><?php echo htmlspecialchars($company['company_size']); ?>
                                        employees</span>
                                <?php endif; ?>
                                <?php if ($company['location']): ?>
                                    <span class="badge badge-info">📍
                                        <?php echo htmlspecialchars($company['location']); ?></span>
                                <?php endif; ?>
                            </div>

                            <?php if ($company['website']): ?>
                                <p style="color: var(--text-secondary);">
                                    🌐 <a href="<?php echo htmlspecialchars($company['website']); ?>"
                                        target="_blank"><?php echo htmlspecialchars($company['website']); ?></a>
                                </p>
                            <?php endif; ?>

                            <?php if ($company['description']): ?>
                                <p style="margin-top: 1rem;"><?php echo nl2br(htmlspecialchars($company['description'])); ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </main>
</div>

<?php require_once 'footer.php'; ?>
