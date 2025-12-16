<?php
$page_title = 'Login - DigiCareer Niger';
require_once 'header.php';
require_once 'db_connect.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitize($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $remember = isset($_POST['remember']);

    if (empty($email) || empty($password)) {
        $error = 'Please enter both email and password';
    } else {
        $stmt = $pdo->prepare("SELECT * FROM niger_users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && verify_password($password, $user['password'])) {
            login_user($user);

            // Handle Remember Me
            if ($remember) {
                // Set cookie for 30 days
                setcookie('remember_email', $email, time() + (30 * 24 * 60 * 60), '/');
            } else {
                // Clear cookie if exists
                if (isset($_COOKIE['remember_email'])) {
                    setcookie('remember_email', '', time() - 3600, '/');
                }
            }

            // Redirect based on role
            if ($user['role'] === 'candidate') {
                header('Location: candidate_dashboard.php');
            } else {
                header('Location: employer_dashboard.php');
            }
            exit;
        } else {
            $error = 'Invalid email or password';
        }
    }
}
?>

<div class="container" style="padding: 3rem 1.5rem; max-width: 450px;">
    <div class="card fade-in">
        <div class="card-header text-center">
            <h2 class="card-title">Welcome Back</h2>
            <p style="color: var(--text-muted); margin: 0;">Login to your account</p>
        </div>

        <div class="card-body">
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="form-group">
                    <label class="form-label">Email Address</label>
                    <input type="email" name="email" class="form-control" placeholder="your@email.com" required
                        value="<?php echo htmlspecialchars($_POST['email'] ?? $_COOKIE['remember_email'] ?? ''); ?>">
                </div>

                <div class="form-group">
                    <label class="form-label">Password</label>
                    <input type="password" name="password" class="form-control" placeholder="Enter your password"
                        required>
                </div>
                
                <div class="form-group" style="display: flex; align-items: center; gap: 0.5rem;">
                    <input type="checkbox" name="remember" id="remember" <?php echo isset($_COOKIE['remember_email']) ? 'checked' : ''; ?>>
                    <label for="remember" style="margin: 0; cursor: pointer; font-weight: normal;">Remember me</label>
                </div>

                <button type="submit" class="btn btn-primary" style="width: 100%;">Login</button>
            </form>

            <p class="text-center" style="margin-top: 1.5rem;">
                Don't have an account? <a href="register.php">Register here</a>
            </p>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?>