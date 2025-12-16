<?php
$page_title = 'Invitations - DigiCareer Niger';
require_once 'header.php';
require_once 'db_connect.php';
require_role('candidate');

$user_id = get_user_id();
$success = '';
$error = '';

// Handle invitation response
if (isset($_POST['respond_invitation'])) {
    $invitation_id = (int) $_POST['invitation_id'];
    $response = sanitize($_POST['respond_invitation']); // The button value is in respond_invitation

    if (in_array($response, ['accepted', 'declined'])) {
        $stmt = $pdo->prepare("UPDATE niger_invitations SET status = ? WHERE id = ? AND candidate_id = ?");
        if ($stmt->execute([$response, $invitation_id, $user_id])) {
            $success = 'Invitation ' . $response . ' successfully!';
        }
    }
}

// Get all invitations
$stmt = $pdo->prepare("
    SELECT i.*, u.name as employer_name, cp.company_name, cp.company_logo, j.title as job_title
    FROM niger_invitations i
    JOIN niger_users u ON i.employer_id = u.id
    LEFT JOIN niger_company_profiles cp ON u.id = cp.user_id
    LEFT JOIN niger_jobs j ON i.job_id = j.id
    WHERE i.candidate_id = ?
    ORDER BY i.created_at DESC
");
$stmt->execute([$user_id]);
$invitations = $stmt->fetchAll();

// Separate by status
$pending = array_filter($invitations, fn($inv) => $inv['status'] === 'pending');
$responded = array_filter($invitations, fn($inv) => $inv['status'] !== 'pending');
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
                <a href="my_applications.php" class="sidebar-link">📋 My Applications</a>
            </li>
            <li class="sidebar-item">
                <a href="invitations.php" class="sidebar-link active">✉️ Invitations</a>
            </li>
        </ul>
    </aside>

    <main class="main-content">
        <h1 style="margin-bottom: 2rem;">My Invitations</h1>

        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>

        <!-- Pending Invitations -->
        <?php if (!empty($pending)): ?>
            <div class="card" style="margin-bottom: 2rem;">
                <div class="card-header">
                    <h3 class="card-title">Pending Invitations (<?php echo count($pending); ?>)</h3>
                </div>
                <div class="card-body">
                    <div class="grid gap-md">
                        <?php foreach ($pending as $inv): ?>
                            <div class="card" style="padding: 1.5rem; border: 2px solid var(--primary-color);">
                                <div class="flex gap-lg">
                                    <?php if ($inv['company_logo']): ?>
                                        <img src="<?php echo htmlspecialchars($inv['company_logo']); ?>" alt="Logo"
                                            class="avatar-lg">
                                    <?php else: ?>
                                        <div class="avatar-lg"
                                            style="background: linear-gradient(135deg, var(--primary-color), var(--secondary-color)); display: flex; align-items: center; justify-content: center; font-size: 1.5rem; font-weight: 700;">
                                            <?php echo strtoupper(substr($inv['company_name'] ?? $inv['employer_name'], 0, 1)); ?>
                                        </div>
                                    <?php endif; ?>

                                    <div style="flex: 1;">
                                        <h3 style="margin-bottom: 0.5rem;">
                                            <?php echo htmlspecialchars($inv['company_name'] ?? $inv['employer_name']); ?>
                                        </h3>

                                        <?php if ($inv['job_title']): ?>
                                            <p style="color: var(--text-secondary); margin-bottom: 0.5rem;">
                                                Position: <strong><?php echo htmlspecialchars($inv['job_title']); ?></strong>
                                            </p>
                                        <?php endif; ?>

                                        <?php if ($inv['message']): ?>
                                            <div
                                                style="margin: 1rem 0; padding: 1rem; background: rgba(99, 102, 241, 0.1); border-radius: var(--radius-md);">
                                                <p style="margin: 0;"><?php echo nl2br(htmlspecialchars($inv['message'])); ?></p>
                                            </div>
                                        <?php endif; ?>

                                        <p style="color: var(--text-muted); font-size: 0.875rem; margin-bottom: 1rem;">
                                            Received: <?php echo date('M d, Y', strtotime($inv['created_at'])); ?>
                                        </p>

                                        <form method="POST" class="flex gap-sm">
                                            <input type="hidden" name="invitation_id" value="<?php echo $inv['id']; ?>">
                                            <button type="submit" name="respond_invitation" value="accepted"
                                                class="btn btn-sm btn-primary">Accept</button>
                                            <button type="submit" name="respond_invitation" value="declined"
                                                class="btn btn-sm btn-outline"
                                                style="border-color: var(--error); color: var(--error);">Decline</button>
                                            <?php if ($inv['job_id']): ?>
                                                <a href="job_details.php?id=<?php echo $inv['job_id']; ?>"
                                                    class="btn btn-sm btn-outline">View Job</a>
                                            <?php endif; ?>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Past Invitations -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Past Invitations (<?php echo count($responded); ?>)</h3>
            </div>
            <div class="card-body">
                <?php if (empty($responded)): ?>
                    <p style="text-align: center; color: var(--text-muted); padding: 2rem;">
                        No past invitations.
                    </p>
                <?php else: ?>
                    <div class="grid gap-md">
                        <?php foreach ($responded as $inv): ?>
                            <div
                                style="padding: 1rem; background: rgba(255, 255, 255, 0.05); border-radius: var(--radius-md); display: flex; justify-content: space-between; align-items: center;">
                                <div>
                                    <strong><?php echo htmlspecialchars($inv['company_name'] ?? $inv['employer_name']); ?></strong>
                                    <?php if ($inv['job_title']): ?>
                                        <span style="color: var(--text-secondary);"> -
                                            <?php echo htmlspecialchars($inv['job_title']); ?></span>
                                    <?php endif; ?>
                                    <p style="color: var(--text-muted); font-size: 0.875rem; margin: 0.25rem 0 0 0;">
                                        <?php echo date('M d, Y', strtotime($inv['created_at'])); ?>
                                    </p>
                                </div>
                                <span
                                    class="badge <?php echo $inv['status'] === 'accepted' ? 'badge-success' : 'badge-error'; ?>">
                                    <?php echo htmlspecialchars($inv['status']); ?>
                                </span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>
</div>

<?php require_once 'footer.php'; ?>
