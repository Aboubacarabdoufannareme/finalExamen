<?php
$page_title = 'My Documents - DigiCareer Niger';
require_once 'header.php';
require_once 'db_connect.php';
require_role('candidate');

$user_id = get_user_id();
$success = '';
$error = '';

// Handle file upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['upload_document'])) {
    $document_type = sanitize($_POST['document_type'] ?? '');
    $document_name = sanitize($_POST['document_name'] ?? '');

    if (isset($_FILES['document']) && $_FILES['document']['error'] === 0) {
        $allowed = ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png'];
        $filename = $_FILES['document']['name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        if (in_array($ext, $allowed)) {
            $upload_dir = 'uploads/documents/';
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }

            $new_filename = 'doc_' . $user_id . '_' . time() . '.' . $ext;
            $upload_path = $upload_dir . $new_filename;

            if (move_uploaded_file($_FILES['document']['tmp_name'], $upload_path)) {
                $stmt = $pdo->prepare("INSERT INTO niger_documents (user_id, document_type, document_name, file_path) VALUES (?, ?, ?, ?)");
                if ($stmt->execute([$user_id, $document_type, $document_name, $upload_path])) {
                    $success = 'Document uploaded successfully!';
                } else {
                    $error = 'Failed to save document information.';
                }
            } else {
                $error = 'Failed to upload file.';
            }
        } else {
            $error = 'Invalid file type. Allowed: PDF, DOC, DOCX, JPG, PNG';
        }
    } else {
        $error = 'Please select a file to upload.';
    }
}

// Handle document deletion
if (isset($_GET['delete'])) {
    $doc_id = (int) $_GET['delete'];
    $stmt = $pdo->prepare("SELECT file_path FROM niger_documents WHERE id = ? AND user_id = ?");
    $stmt->execute([$doc_id, $user_id]);
    $doc = $stmt->fetch();

    if ($doc) {
        if (file_exists($doc['file_path'])) {
            unlink($doc['file_path']);
        }
        $stmt = $pdo->prepare("DELETE FROM niger_documents WHERE id = ? AND user_id = ?");
        $stmt->execute([$doc_id, $user_id]);
        $success = 'Document deleted successfully!';
    }
}

// Get all documents
$stmt = $pdo->prepare("SELECT * FROM niger_documents WHERE user_id = ? ORDER BY uploaded_at DESC");
$stmt->execute([$user_id]);
$documents = $stmt->fetchAll();

// Group by type
$docs_by_type = [];
foreach ($documents as $doc) {
    $docs_by_type[$doc['document_type']][] = $doc;
}
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
                <a href="my_documents.php" class="sidebar-link active">📁 Documents</a>
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
        <h1 style="margin-bottom: 2rem;">My Documents</h1>

        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <!-- Upload Form -->
        <div class="card" style="margin-bottom: 2rem;">
            <div class="card-header">
                <h3 class="card-title">Upload New Document</h3>
            </div>
            <div class="card-body">
                <form method="POST" enctype="multipart/form-data">
                    <div class="grid grid-2 gap-md">
                        <div class="form-group">
                            <label class="form-label">Document Type</label>
                            <select name="document_type" class="form-control" required>
                                <option value="">Select type...</option>
                                <option value="diploma">Diploma</option>
                                <option value="certificate">Certificate</option>
                                <option value="cv">CV / Resume</option>
                                <option value="cover_letter">Cover Letter</option>
                                <option value="portfolio">Portfolio</option>
                                <option value="other">Other</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Document Name</label>
                            <input type="text" name="document_name" class="form-control"
                                placeholder="e.g., Bachelor's Degree" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Choose File</label>
                        <input type="file" name="document" class="form-control" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png"
                            required>
                        <p style="color: var(--text-muted); font-size: 0.875rem; margin-top: 0.5rem;">
                            Accepted formats: PDF, DOC, DOCX, JPG, PNG (Max 10MB)
                        </p>
                    </div>

                    <button type="submit" name="upload_document" class="btn btn-primary">Upload Document</button>
                </form>
            </div>
        </div>

        <!-- Documents List -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">My Documents (<?php echo count($documents); ?>)</h3>
            </div>
            <div class="card-body">
                <?php if (empty($documents)): ?>
                    <p style="text-align: center; color: var(--text-muted); padding: 2rem;">
                        No documents uploaded yet. Upload your first document above!
                    </p>
                <?php else: ?>
                    <?php foreach (['diploma', 'certificate', 'cv', 'cover_letter', 'portfolio', 'other'] as $type): ?>
                        <?php if (isset($docs_by_type[$type])): ?>
                            <div style="margin-bottom: 2rem;">
                                <h4 style="text-transform: capitalize; margin-bottom: 1rem;">
                                    <?php echo str_replace('_', ' ', $type); ?>s
                                </h4>
                                <div class="grid grid-2 gap-md">
                                    <?php foreach ($docs_by_type[$type] as $doc): ?>
                                        <div
                                            style="padding: 1rem; background: rgba(255, 255, 255, 0.05); border-radius: var(--radius-md); border: 1px solid rgba(255, 255, 255, 0.1);">
                                            <div class="flex-between" style="margin-bottom: 0.5rem;">
                                                <strong><?php echo htmlspecialchars($doc['document_name']); ?></strong>
                                                <span
                                                    class="badge badge-info"><?php echo strtoupper(pathinfo($doc['file_path'], PATHINFO_EXTENSION)); ?></span>
                                            </div>
                                            <p style="color: var(--text-muted); font-size: 0.875rem; margin-bottom: 1rem;">
                                                Uploaded: <?php echo date('M d, Y', strtotime($doc['uploaded_at'])); ?>
                                            </p>
                                            <div class="flex gap-sm">
                                                <a href="<?php echo htmlspecialchars($doc['file_path']); ?>" target="_blank"
                                                    class="btn btn-sm btn-primary">View</a>
                                                <a href="<?php echo htmlspecialchars($doc['file_path']); ?>" download
                                                    class="btn btn-sm btn-outline">Download</a>
                                                <a href="?delete=<?php echo $doc['id']; ?>" class="btn btn-sm btn-outline"
                                                    onclick="return confirm('Are you sure you want to delete this document?');"
                                                    style="border-color: var(--error); color: var(--error);">Delete</a>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </main>
</div>

<?php require_once 'footer.php'; ?>
