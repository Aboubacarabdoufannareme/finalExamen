<?php
$page_title = 'Register - DigiCareer Niger';
require_once 'header.php';
require_once 'db_connect.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['name'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $role = sanitize($_POST['role'] ?? '');
    $terms = isset($_POST['terms']);

    // Validation
    if (empty($name) || empty($email) || empty($password) || empty($role)) {
        $error = 'All fields are required';
    } elseif (!$terms) {
        $error = 'You must agree to the Terms and Conditions';
    } elseif (!validate_email($email)) {
        $error = 'Invalid email format';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match';
    } elseif (!in_array($role, ['candidate', 'employer'])) {
        $error = 'Invalid role selected';
    } else {
        // Check if email already exists
        $stmt = $pdo->prepare("SELECT id FROM niger_users WHERE email = ?");
        $stmt->execute([$email]);

        if ($stmt->fetch()) {
            $error = 'Email already registered';
        } else {
            // Insert new user
            $hashed_password = hash_password($password);
            $stmt = $pdo->prepare("INSERT INTO niger_users (name, email, password, role) VALUES (?, ?, ?, ?)");

            if ($stmt->execute([$name, $email, $hashed_password, $role])) {
                $user_id = $pdo->lastInsertId();

                // If employer, create company profile
                if ($role === 'employer') {
                    $company_name = sanitize($_POST['company_name'] ?? $name);
                    $stmt = $pdo->prepare("INSERT INTO niger_company_profiles (user_id, company_name) VALUES (?, ?)");
                    $stmt->execute([$user_id, $company_name]);
                }

                $success = 'Registration successful! You can now login.';
            } else {
                $error = 'Registration failed. Please try again.';
            }
        }
    }
}
?>

<div class="container" style="padding: 3rem 1.5rem; max-width: 500px;">
    <div class="card fade-in">
        <div class="card-header text-center">
            <h2 class="card-title">Create Your Account</h2>
            <p style="color: var(--text-muted); margin: 0;">Join DigiCareer Niger today</p>
        </div>

        <div class="card-body">
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="alert alert-success">
                    <?php echo htmlspecialchars($success); ?>
                    <a href="login.php" style="display: block; margin-top: 0.5rem;">Click here to login</a>
                </div>
            <?php else: ?>
                <form method="POST" action="">
                    <div class="form-group">
                        <label class="form-label">I am a:</label>
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                            <label style="cursor: pointer;">
                                <input type="radio" name="role" value="candidate" required onchange="toggleCompanyField()"
                                    id="role-candidate">
                                <div class="card" style="padding: 1rem; text-align: center; margin-top: 0.5rem;">
                                    <strong>Candidate</strong>
                                    <p style="font-size: 0.875rem; margin: 0.5rem 0 0 0;">Looking for jobs</p>
                                </div>
                            </label>
                            <label style="cursor: pointer;">
                                <input type="radio" name="role" value="employer" required onchange="toggleCompanyField()"
                                    id="role-employer">
                                <div class="card" style="padding: 1rem; text-align: center; margin-top: 0.5rem;">
                                    <strong>Employer</strong>
                                    <p style="font-size: 0.875rem; margin: 0.5rem 0 0 0;">Hiring talent</p>
                                </div>
                            </label>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Full Name</label>
                        <input type="text" name="name" class="form-control" placeholder="Enter your full name" required
                            value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>">
                    </div>

                    <div class="form-group" id="company-field" style="display: none;">
                        <label class="form-label">Company Name</label>
                        <input type="text" name="company_name" class="form-control" placeholder="Enter company name"
                            value="<?php echo htmlspecialchars($_POST['company_name'] ?? ''); ?>">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Email Address</label>
                        <input type="email" name="email" class="form-control" placeholder="your@email.com" required
                            value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Password</label>
                        <input type="password" name="password" class="form-control" placeholder="At least 6 characters"
                            required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Confirm Password</label>
                        <input type="password" name="confirm_password" class="form-control" placeholder="Re-enter password"
                            required>
                    </div>

                    <div class="form-group"
                        style="display: flex; align-items: start; gap: 0.5rem; padding: 1rem; background: rgba(99, 102, 241, 0.05); border-radius: var(--radius-md); border: 1px solid rgba(99, 102, 241, 0.2);">
                        <input type="checkbox" name="terms" id="terms" required style="margin-top: 0.25rem;">
                        <label for="terms" style="margin: 0; cursor: pointer; font-weight: normal; font-size: 0.875rem;">
                            I agree to the <a href="#" style="color: var(--primary-color); font-weight: 600;">Terms and
                                Conditions</a> and <a href="#"
                                style="color: var(--primary-color); font-weight: 600;">Privacy Policy</a>
                        </label>
                    </div>

                    <button type="submit" class="btn btn-primary" style="width: 100%;">Create Account</button>
                </form>

                <p class="text-center" style="margin-top: 1.5rem;">
                    Already have an account? <a href="login.php">Login here</a>
                </p>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
    function toggleCompanyField() {
        const employerRadio = document.getElementById('role-employer');
        const companyField = document.getElementById('company-field');
        companyField.style.display = employerRadio.checked ? 'block' : 'none';
    }
</script>

<?php require_once 'footer.php'; ?>