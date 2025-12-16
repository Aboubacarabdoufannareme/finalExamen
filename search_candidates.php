<?php
$page_title = 'Search Candidates - DigiCareer Niger';
require_once 'header.php';
require_once 'db_connect.php';
require_role('employer');

$user_id = get_user_id();
$success = '';
$error = '';

// Handle invitation sending
if (isset($_POST['send_invitation'])) {
    $candidate_id = (int)$_POST['candidate_id'];
    $job_id = isset($_POST['job_id']) && !empty($_POST['job_id']) ? (int)$_POST['job_id'] : null;
    $message = sanitize($_POST['message'] ?? '');
    
    // Check if invitation already exists
    $stmt = $pdo->prepare("SELECT id FROM niger_invitations WHERE employer_id = ? AND candidate_id = ? AND status = 'pending'");
    $stmt->execute([$user_id, $candidate_id]);
    
    if ($stmt->fetch()) {
        $error = 'You already have a pending invitation for this candidate.';
    } else {
        $stmt = $pdo->prepare("INSERT INTO niger_invitations (employer_id, candidate_id, job_id, message) VALUES (?, ?, ?, ?)");
        if ($stmt->execute([$user_id, $candidate_id, $job_id, $message])) {
            $success = 'Invitation sent successfully!';
        } else {
            $error = 'Failed to send invitation.';
        }
    }
}

// Search parameters
$search_query = sanitize($_GET['q'] ?? '');
$skill_filter = sanitize($_GET['skill'] ?? '');

// Build search query
$sql = "SELECT DISTINCT u.*, 
        GROUP_CONCAT(DISTINCT s.skill_name) as skills,
        GROUP_CONCAT(DISTINCT e.degree) as degrees
        FROM niger_users u
        LEFT JOIN niger_skills s ON u.id = s.user_id
        LEFT JOIN niger_education e ON u.id = e.user_id
        WHERE u.role = 'candidate'";

$params = [];

if ($search_query) {
    $sql .= " AND (u.name LIKE ? OR u.bio LIKE ?)";
    $params[] = "%$search_query%";
    $params[] = "%$search_query%";
}

$sql .= " GROUP BY u.id ORDER BY u.created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$candidates = $stmt->fetchAll();

// Filter by skill if specified
if ($skill_filter && !empty($candidates)) {
    $candidates = array_filter($candidates, function($candidate) use ($skill_filter) {
        return stripos($candidate['skills'] ?? '', $skill_filter) !== false;
    });
}

// Get employer's jobs for invitation dropdown
$stmt = $pdo->prepare("SELECT id, title FROM niger_jobs WHERE employer_id = ? AND status = 'active' ORDER BY created_at DESC");
$stmt->execute([$user_id]);
$employer_jobs = $stmt->fetchAll();
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
        <h1 style="margin-bottom: 2rem;">Search Candidates</h1>
        
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <!-- Search Form -->
        <div class="card" style="margin-bottom: 2rem;">
            <div class="card-body">
                <form method="GET" class="grid grid-2 gap-md">
                    <div class="form-group">
                        <label class="form-label">Search by name or keywords</label>
                        <input type="text" name="q" class="form-control" placeholder="Enter name or keywords..." value="<?php echo htmlspecialchars($search_query); ?>">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Filter by skill</label>
                        <input type="text" name="skill" class="form-control" placeholder="e.g., JavaScript" value="<?php echo htmlspecialchars($skill_filter); ?>">
                    </div>
                    <div style="grid-column: span 2;">
                        <button type="submit" class="btn btn-primary">Search</button>
                        <a href="search_candidates.php" class="btn btn-outline">Clear</a>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Results -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Candidates (<?php echo count($candidates); ?>)</h3>
            </div>
            <div class="card-body">
                <?php if (empty($candidates)): ?>
                    <p style="text-align: center; color: var(--text-muted); padding: 2rem;">
                        No candidates found. Try adjusting your search criteria.
                    </p>
                <?php else: ?>
                    <div class="grid gap-md">
                        <?php foreach ($candidates as $candidate): ?>
                            <div class="card" style="padding: 1.5rem;">
                                <div class="flex gap-lg">
                                    <?php if ($candidate['profile_pic']): ?>
                                        <img src="<?php echo htmlspecialchars($candidate['profile_pic']); ?>" alt="Profile" class="avatar-lg">
                                    <?php else: ?>
                                        <div class="avatar-lg" style="background: linear-gradient(135deg, var(--primary-color), var(--secondary-color)); display: flex; align-items: center; justify-content: center; font-size: 1.5rem; font-weight: 700;">
                                            <?php echo strtoupper(substr($candidate['name'], 0, 1)); ?>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <div style="flex: 1;">
                                        <h3 style="margin-bottom: 0.5rem;"><?php echo htmlspecialchars($candidate['name']); ?></h3>
                                        <p style="color: var(--text-secondary); margin-bottom: 0.5rem;">
                                            <?php echo htmlspecialchars($candidate['email']); ?>
                                            <?php if ($candidate['phone']): ?>
                                                | <?php echo htmlspecialchars($candidate['phone']); ?>
                                            <?php endif; ?>
                                        </p>
                                        
                                        <?php if ($candidate['bio']): ?>
                                            <p style="margin: 1rem 0;"><?php echo htmlspecialchars(substr($candidate['bio'], 0, 150)) . (strlen($candidate['bio']) > 150 ? '...' : ''); ?></p>
                                        <?php endif; ?>
                                        
                                        <?php if ($candidate['skills']): ?>
                                            <div style="margin: 1rem 0;">
                                                <strong style="font-size: 0.875rem; color: var(--text-muted);">Skills:</strong>
                                                <div class="flex gap-sm" style="flex-wrap: wrap; margin-top: 0.5rem;">
                                                    <?php foreach (array_slice(explode(',', $candidate['skills']), 0, 5) as $skill): ?>
                                                        <span class="badge badge-info"><?php echo htmlspecialchars($skill); ?></span>
                                                    <?php endforeach; ?>
                                                </div>
                                            </div>
                                        <?php endif; ?>
                                        
                                        <?php if ($candidate['degrees']): ?>
                                            <div style="margin: 1rem 0;">
                                                <strong style="font-size: 0.875rem; color: var(--text-muted);">Education:</strong>
                                                <p style="color: var(--text-secondary); margin-top: 0.25rem;">
                                                    <?php echo htmlspecialchars(implode(', ', array_unique(explode(',', $candidate['degrees'])))); ?>
                                                </p>
                                            </div>
                                        <?php endif; ?>
                                        
                                        <div class="flex gap-sm" style="margin-top: 1rem;">
                                            <a href="view_candidate.php?id=<?php echo $candidate['id']; ?>" class="btn btn-sm btn-primary">View Full Profile</a>
                                            <button onclick="showInviteModal(<?php echo $candidate['id']; ?>, '<?php echo htmlspecialchars($candidate['name'], ENT_QUOTES); ?>')" class="btn btn-sm btn-secondary">Send Invitation</button>
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

<!-- Invitation Modal -->
<div id="inviteModal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0, 0, 0, 0.8); z-index: 9999; align-items: center; justify-content: center;">
    <div class="card" style="max-width: 500px; width: 90%; margin: 2rem;">
        <div class="card-header">
            <h3 class="card-title">Send Invitation</h3>
        </div>
        <div class="card-body">
            <form method="POST">
                <input type="hidden" name="candidate_id" id="invite_candidate_id">
                
                <p style="margin-bottom: 1rem;">Inviting: <strong id="invite_candidate_name"></strong></p>
                
                <div class="form-group">
                    <label class="form-label">For Job (Optional)</label>
                    <select name="job_id" class="form-control">
                        <option value="">General invitation</option>
                        <?php foreach ($employer_jobs as $job): ?>
                            <option value="<?php echo $job['id']; ?>"><?php echo htmlspecialchars($job['title']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Message</label>
                    <textarea name="message" class="form-control" rows="4" placeholder="Write a personalized message to the candidate..."></textarea>
                </div>
                
                <div class="flex gap-sm">
                    <button type="submit" name="send_invitation" class="btn btn-primary">Send Invitation</button>
                    <button type="button" onclick="closeInviteModal()" class="btn btn-outline">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function showInviteModal(candidateId, candidateName) {
    document.getElementById('invite_candidate_id').value = candidateId;
    document.getElementById('invite_candidate_name').textContent = candidateName;
    document.getElementById('inviteModal').style.display = 'flex';
}

function closeInviteModal() {
    document.getElementById('inviteModal').style.display = 'none';
}
</script>

<?php require_once 'footer.php'; ?>
