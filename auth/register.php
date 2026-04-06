<?php
require_once __DIR__ . '/../config/store.php';

$errors = [];
$flash = get_flash();
$formData = [
    'name' => trim((string) ($_POST['name'] ?? '')),
    'email' => trim((string) ($_POST['email'] ?? '')),
];

if (request_is_post()) {
    $name = trim((string) ($_POST['name'] ?? ''));
    $email = trim((string) ($_POST['email'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');
    $confirmPassword = (string) ($_POST['confirm_password'] ?? '');

    enforce_csrf_or_errors($errors, 'register_form');

    if ($name === '' || $email === '' || $password === '' || $confirmPassword === '') {
        $errors[] = 'Please fill all fields.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address.';
    } elseif (strlen($password) < 8) {
        $errors[] = 'Password must be at least 8 characters.';
    } elseif ($password !== $confirmPassword) {
        $errors[] = 'Password and confirm password must match.';
    } else {
        $existingUser = fetch_user_by_email($conn, $email);

        if ($existingUser) {
            $errors[] = 'Email already registered.';
        } else {
            $pass = password_hash($password, PASSWORD_DEFAULT);
            $role = 'user';
            $stmt = $conn->prepare("INSERT INTO users(name,email,role,password) VALUES(?,?,?,?)");
            $stmt->bind_param('ssss', $name, $email, $role, $pass);

            if ($stmt->execute()) {
                $stmt->close();
                $user = fetch_user_by_email($conn, $email);

                if ($user) {
                    login_user_session($user);
                    update_user_last_login($conn, (int) $user['id']);
                    set_flash('success', 'Account created successfully. Welcome!');
                    redirect_to('../dashboard/user_dashboard.php');
                }

                set_flash('success', 'Registration successful. You can now login.');
                redirect_to('login.php');
            }

            $stmt->close();
            $errors[] = 'Unable to register right now.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Sign Up page</title>
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
            <span class="auth-eyebrow">Create your customer account</span>
            <h1>Register once and manage your grocery journey in one place.</h1>
            <p class="auth-copy">Create an account to browse products, save delivery details, maintain a cart, and
                review your order history through the dashboard.</p>

            <div class="auth-feature-list">
                <div class="feature-card">
                    <i class="fa-solid fa-user-plus"></i>
                    <div>
                        <h3>Quick Onboarding</h3>
                        <p>Register with basic details first, then complete your delivery profile inside the account
                            area.</p>
                    </div>
                </div>
                <div class="feature-card">
                    <i class="fa-solid fa-cart-shopping"></i>
                    <div>
                        <h3>Smart Cart Flow</h3>
                        <p>Add products from the dashboard and place orders using your saved delivery information.</p>
                    </div>
                </div>
                <div class="feature-card">
                    <i class="fa-solid fa-box-open"></i>
                    <div>
                        <h3>Order Visibility</h3>
                        <p>Track recent orders, items, and delivery details from a single customer interface.</p>
                    </div>
                </div>
            </div>
        </section>

        <section class="auth-form-panel">
            <div class="login-container">
                <div class="login-glass">
                    <div class="login-header">
                        <div class="logo">
                            <i class="fas fa-id-card"></i>
                            <span>New Account</span>
                        </div>
                        <h2>Create Account</h2>
                        <p>Set up your customer account in a few steps.</p>
                    </div>

                    <form class="login-form" id="signupForm" method="post" novalidate>
                        <?php echo csrf_field('register_form'); ?>
                        <div class="input-group">
                            <label for="name">Full Name</label>
                            <div class="input-field">
                                <i class="fas fa-user"></i>
                                <input type="text" id="signupName" name="name" placeholder="Enter your full name"
                                    value="<?php echo e($formData['name']); ?>" required />
                            </div>
                            <p class="field-hint" id="signupNameError" aria-live="polite"></p>
                        </div>

                        <div class="input-group">
                            <label for="email">Email Address</label>
                            <div class="input-field">
                                <i class="fas fa-envelope"></i>
                                <input type="email" id="signupEmail" name="email" placeholder="Enter your email"
                                    value="<?php echo e($formData['email']); ?>" required />
                            </div>
                            <p class="field-hint" id="signupEmailError" aria-live="polite"></p>
                        </div>

                        <div class="input-group">
                            <label for="password">Password</label>
                            <div class="input-field">
                                <i class="fas fa-lock"></i>
                                <input type="password" id="signupPassword" name="password"
                                    placeholder="Create a password" required />
                                <button type="button" class="toggle-pw" aria-label="Toggle password visibility">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                            <p class="field-hint" id="signupPasswordError" aria-live="polite"></p>
                        </div>

                        <div class="input-group">
                            <label for="confirm_password">Confirm Password</label>
                            <div class="input-field">
                                <i class="fas fa-shield-halved"></i>
                                <input type="password" id="signupConfirmPassword" name="confirm_password"
                                    placeholder="Confirm your password" required />
                                <button type="button" class="toggle-pw" aria-label="Toggle password visibility">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                            <p class="field-hint" id="signupConfirmPasswordError" aria-live="polite"></p>
                        </div>

                        <?php if ($flash): ?>
                            <span
                                class="<?php echo $flash['type'] === 'error' ? 'error-msg' : 'success-msg'; ?>"><?php echo e($flash['message']); ?></span>
                        <?php endif; ?>

                        <?php if (!empty($errors)): ?>
                            <span class="error-msg"><?php echo e(implode(' ', $errors)); ?></span>
                        <?php endif; ?>

                        <button type="submit" name="register" class="login-btn">
                            <span>Create Account</span>
                            <i class="fas fa-user-plus"></i>
                        </button>

                        <p class="signup-link">Already have an account? <a href="./login.php">Sign in</a></p>
                    </form>
                </div>
            </div>
        </section>
    </div>
    <script src="../assets/js/auth.js"></script>
</body>

</html>
