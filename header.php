<?php
require_once 'auth.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title ?? 'DigiCareer Niger'; ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Outfit:wght@700;800&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <header class="header">
        <nav class="nav container">
            <a href="index.php" class="nav-brand">DigiCareer Niger</a>
            <ul class="nav-menu">
                <?php if (is_logged_in()): ?>
                    <?php if (has_role('candidate')): ?>
                        <li><a href="candidate_dashboard.php" class="nav-link">Dashboard</a></li>
                        <li><a href="profile.php" class="nav-link">Profile</a></li>
                        <li><a href="my_documents.php" class="nav-link">Documents</a></li>
                        <li><a href="cv_builder.php" class="nav-link">CV Builder</a></li>
                        <li><a href="invitations.php" class="nav-link">Invitations</a></li>
                    <?php elseif (has_role('employer')): ?>
                        <li><a href="employer_dashboard.php" class="nav-link">Dashboard</a></li>
                        <li><a href="company_profile.php" class="nav-link">Company Profile</a></li>
                        <li><a href="post_job.php" class="nav-link">Post Job</a></li>
                        <li><a href="applications_received.php" class="nav-link">Applications</a></li>
                        <li><a href="search_candidates.php" class="nav-link">Search Candidates</a></li>
                    <?php endif; ?>
                    <li>
                        <div class="flex" style="align-items: center; gap: 1rem;">
                            <?php if (!empty($_SESSION['profile_pic'])): ?>
                                <img src="<?php echo htmlspecialchars($_SESSION['profile_pic']); ?>" alt="Profile"
                                    class="avatar">
                            <?php endif; ?>
                            <span class="nav-link"><?php echo htmlspecialchars(get_user_name()); ?></span>
                            <a href="logout.php" class="btn btn-sm btn-outline">Logout</a>
                        </div>
                    </li>
                <?php else: ?>
                    <li><a href="index.php" class="nav-link">Home</a></li>
                    <li><a href="login.php" class="btn btn-sm btn-primary">Login</a></li>
                    <li><a href="register.php" class="btn btn-sm btn-secondary">Register</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </header>
