<?php
$page_title = 'Home - DigiCareer Niger';
require_once 'header.php';
require_once 'db_connect.php';

// Fetch latest jobs
$stmt = $pdo->prepare("
    SELECT j.*, u.name as employer_name, cp.company_name, cp.company_logo 
    FROM niger_jobs j 
    JOIN niger_users u ON j.employer_id = u.id 
    LEFT JOIN niger_company_profiles cp ON u.id = cp.user_id 
    WHERE j.status = 'active' 
    ORDER BY j.created_at DESC 
    LIMIT 6
");
$stmt->execute();
$latest_jobs = $stmt->fetchAll();
?>

<section class="hero">
    <div class="container">
        <h1 class="hero-title fade-in">Your Digital Career Starts Here</h1>
        <p class="hero-subtitle fade-in">Connect with top employers in Niger. Build your profile, showcase your skills,
            and land your dream job.</p>
        <div class="flex-center gap-md" style="margin-top: 2rem;">
            <?php if (!is_logged_in()): ?>
                <a href="register.php" class="btn btn-primary btn-lg">Get Started</a>
                <a href="login.php" class="btn btn-outline btn-lg">Login</a>
            <?php else: ?>
                <?php if (has_role('candidate')): ?>
                    <a href="candidate_dashboard.php" class="btn btn-primary btn-lg">Go to Dashboard</a>
                    <a href="cv_builder.php" class="btn btn-outline btn-lg">Build Your CV</a>
                <?php else: ?>
                    <a href="employer_dashboard.php" class="btn btn-primary btn-lg">Go to Dashboard</a>
                    <a href="post_job.php" class="btn btn-outline btn-lg">Post a Job</a>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</section>

<section class="container" style="padding: 3rem 1.5rem;">
    <div class="flex-between" style="margin-bottom: 2rem;">
        <h2>Latest Opportunities</h2>
        <?php if (is_logged_in() && has_role('candidate')): ?>
            <a href="browse_jobs.php" class="btn btn-outline">View All Jobs</a>
        <?php endif; ?>
    </div>

    <?php if (empty($latest_jobs)): ?>
        <div class="card text-center">
            <p style="color: var(--text-muted);">No jobs posted yet. Check back soon!</p>
        </div>
    <?php else: ?>
        <div class="grid grid-3">
            <?php foreach ($latest_jobs as $job): ?>
                <div class="card fade-in">
                    <div class="flex-between" style="margin-bottom: 1rem;">
                        <?php if ($job['company_logo']): ?>
                            <img src="<?php echo htmlspecialchars($job['company_logo']); ?>" alt="Company Logo" class="avatar">
                        <?php else: ?>
                            <div class="avatar"
                                style="background: linear-gradient(135deg, var(--primary-color), var(--secondary-color)); display: flex; align-items: center; justify-content: center; font-weight: 700;">
                                <?php echo strtoupper(substr($job['company_name'] ?? $job['employer_name'], 0, 1)); ?>
                            </div>
                        <?php endif; ?>
                        <span class="badge badge-info"><?php echo htmlspecialchars($job['job_type']); ?></span>
                    </div>

                    <h3 style="font-size: 1.25rem; margin-bottom: 0.5rem;">
                        <?php echo htmlspecialchars($job['title']); ?>
                    </h3>

                    <p style="color: var(--text-muted); font-size: 0.875rem; margin-bottom: 0.5rem;">
                        <?php echo htmlspecialchars($job['company_name'] ?? $job['employer_name']); ?>
                    </p>

                    <?php if ($job['location']): ?>
                        <p style="color: var(--text-secondary); font-size: 0.875rem; margin-bottom: 1rem;">
                            📍 <?php echo htmlspecialchars($job['location']); ?>
                        </p>
                    <?php endif; ?>

                    <p style="color: var(--text-secondary); margin-bottom: 1rem;">
                        <?php echo htmlspecialchars(substr($job['description'], 0, 100)) . '...'; ?>
                    </p>

                    <?php if (is_logged_in() && has_role('candidate')): ?>
                        <a href="job_details.php?id=<?php echo $job['id']; ?>" class="btn btn-primary" style="width: 100%;">View
                            Details</a>
                    <?php else: ?>
                        <a href="login.php" class="btn btn-outline" style="width: 100%;">Login to Apply</a>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>

<section class="container" style="padding: 3rem 1.5rem;">
    <div class="grid grid-3">
        <div class="card text-center fade-in">
            <div style="font-size: 3rem; margin-bottom: 1rem;">📄</div>
            <h3>Build Your Profile</h3>
            <p style="color: var(--text-secondary);">Create a comprehensive digital profile with your education, skills,
                and experience.</p>
        </div>

        <div class="card text-center fade-in">
            <div style="font-size: 3rem; margin-bottom: 1rem;">🎯</div>
            <h3>Find Opportunities</h3>
            <p style="color: var(--text-secondary);">Browse and apply to jobs that match your skills and career goals.
            </p>
        </div>

        <div class="card text-center fade-in">
            <div style="font-size: 3rem; margin-bottom: 1rem;">🤝</div>
            <h3>Get Hired</h3>
            <p style="color: var(--text-secondary);">Connect with top employers and take the next step in your career.
            </p>
        </div>
    </div>
</section>

<?php require_once 'footer.php'; ?>
