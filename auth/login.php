<?php
require_once __DIR__ . '/../config/store.php';

$error = [];
$flash = get_flash();

if (request_is_post()) {
    $identifier = trim((string) ($_POST['email'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');

    enforce_csrf_or_errors($error, 'login_form');

    if ($identifier === '' || $password === '') {
        $error[] = "Please fill all fields.";
    } else {
        $user = fetch_user_for_login($conn, $identifier);

        if ($user) {
            if (verify_user_password($conn, $user, $password)) {
                login_user_session($user);
                update_user_last_login($conn, (int) $user['id']);

                if (($_SESSION['role'] ?? 'user') === 'admin') {
                    redirect_to("../dashboard/dashboard.php");
                }

                redirect_to("../dashboard/user_dashboard.php");
            } else {
                $error[] = "Invalid email or password.";
            }
        } else {
            $error[] = "Invalid email or password.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login page</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>

<body class="auth-page">
    <div class="auth-shell">
        <section class="auth-brand-panel">
            <a href="../index.php" class="brand-link">
                <span class="brand-icon"><i class="fas fa-leaf"></i></span>
                <span>Harvest Fresh</span>
            </a>
            <span class="auth-eyebrow">Fresh grocery commerce</span>
            <h1>Sign in to manage orders, cart, and delivery details.</h1>
            <p class="auth-copy">A cleaner store flow starts here. Access your dashboard, track placed orders, and keep your delivery profile updated from one place.</p>

            <div class="auth-feature-list">
                <div class="feature-card">
                    <i class="fa-solid fa-bag-shopping"></i>
                    <div>
                        <h3>Order Tracking</h3>
                        <p>Review order history and delivery details from your account dashboard.</p>
                    </div>
                </div>
                <div class="feature-card">
                    <i class="fa-solid fa-location-dot"></i>
                    <div>
                        <h3>Saved Delivery Profile</h3>
                        <p>Use a saved address and phone number for a smoother checkout flow.</p>
                    </div>
                </div>
                <div class="feature-card">
                    <i class="fa-solid fa-shield-heart"></i>
                    <div>
                        <h3>Secure Access</h3>
                        <p>Password hashing and session-based authentication keep account access protected.</p>
                    </div>
                </div>
            </div>
        </section>

        <section class="auth-form-panel">
            <div class="login-container">
                <div class="login-glass">
                    <div class="login-header">
                        <div class="logo">
                            <i class="fas fa-user-circle"></i>
                            <span>Account Login</span>
                        </div>
                        <h2>Welcome Back</h2>
                        <p>Enter your credentials to continue.</p>
                    </div>

                    <form class="login-form" id="loginForm" method="post" novalidate>
                        <?php echo csrf_field('login_form'); ?>
                        <div class="input-group">
                            <label for="email">Email / User ID / Phone</label>
                            <div class="input-field">
                                <i class="fas fa-envelope"></i>
                                <input type="text" id="loginEmail" name="email" placeholder="Enter email, user id, or phone" required value="<?php echo e($_POST['email'] ?? ''); ?>">
                            </div>
                            <p class="field-hint" id="loginEmailError" aria-live="polite"></p>
                        </div>

                        <div class="input-group">
                            <label for="password">Password</label>
                            <div class="input-field">
                                <i class="fas fa-lock"></i>
                                <input type="password" id="loginPassword" name="password" placeholder="Enter your password" required>
                                <button type="button" class="toggle-pw" aria-label="Toggle password visibility">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                            <p class="field-hint" id="loginPasswordError" aria-live="polite"></p>
                        </div>

                        <?php if ($flash): ?>
                            <span class="<?php echo $flash['type'] === 'error' ? 'error-msg' : 'success-msg'; ?>"><?php echo e($flash['message']); ?></span>
                        <?php endif; ?>

                        <?php if (!empty($error)): ?>
                            <span class="error-msg"><?php echo e(implode(' ', $error)); ?></span>
                        <?php endif; ?>

                        <button type="submit" name="login" class="login-btn">
                            <span>Sign In</span>
                            <i class="fas fa-arrow-right"></i>
                        </button>

                        <p class="signup-link">Do not have an account? <a href="./register.php" id="signupLink">Create one</a></p>
                    </form>
                </div>
            </div>
        </section>
    </div>
    <script src="../assets/js/auth.js"></script>
</body>

</html>
