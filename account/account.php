<?php
require_once __DIR__ . '/../config/store.php';

require_login();

$userId = current_user_id();
$flash = get_flash();
$errors = [];
$profile = fetch_user_profile($conn, $userId);

if (!$profile) {
    set_flash('error', 'Unable to load account details.');
    redirect_to('../dashboard/user_dashboard.php?section=profile');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    enforce_csrf_or_errors($errors, 'account_form');
    $validated = validate_user_profile_data($_POST);
    $errors = array_merge($errors, $validated['errors']);
    $profile = array_merge($profile, $validated);

    if (empty($errors)) {
        if (update_user_profile($conn, $userId, $validated)) {
            set_flash('success', 'Profile and address details were updated successfully.');
            redirect_to('../dashboard/user_dashboard.php?section=profile');
        }

        $errors[] = 'Unable to save your details right now.';
    }
}

$name = $profile['name'] ?? current_user_name();
$email = $profile['email'] ?? current_user_email();
$role = ucfirst(current_user_role());
$fullAddress = trim(implode(', ', array_filter([
    $profile['address_line1'] ?? '',
    $profile['address_line2'] ?? '',
    $profile['city'] ?? '',
    $profile['state'] ?? '',
    $profile['postal_code'] ?? '',
])));
$saveButtonLabel = $fullAddress !== '' ? 'Update Profile & Address' : 'Save Profile & Address';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Manage Account</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../assets/css/account.css">
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body class="page">
    <div class="page-shell">
        <aside class="glass-panel side-panel">
            <div class="profile-card">
                <div class="profile-avatar"><?php echo e(get_avatar_letter($name)); ?></div>
                <h3><?php echo e($name); ?></h3>
                <p class="muted"><?php echo e($role); ?></p>
            </div>

            <ul class="nav-list">
                <li><a href="../dashboard/user_dashboard.php?section=profile"><i class="fa-solid fa-house"></i> Back to Dashboard</a></li>
                <li><a href="../auth/logout.php"><i class="fa-solid fa-right-from-bracket"></i> Logout</a></li>
            </ul>
        </aside>

        <main class="glass-panel main-panel">
            <div class="panel-header">
                <div class="panel-title">
                    <h1>My Account</h1>
                    <p class="muted">Manage your profile information and delivery details here.</p>
                </div>
            </div>

            <?php if ($flash): ?>
                <div class="content-card message-card <?php echo $flash['type'] === 'success' ? 'success-card' : 'error-card'; ?>">
                    <p><?php echo e($flash['message']); ?></p>
                </div>
            <?php endif; ?>

            <?php if (!empty($errors)): ?>
                <div class="content-card message-card error-card">
                    <?php foreach ($errors as $error): ?>
                        <p><?php echo e($error); ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <div class="content-card account-card quick-actions-card">
                <div class="quick-actions">
                    <a href="#profile-form" class="quick-link-chip"><i class="fa-solid fa-user-pen"></i> Edit Profile</a>
                    <a href="#address-form" class="quick-link-chip"><i class="fa-solid fa-location-dot"></i> Add Address</a>
                </div>
            </div>

            <div class="content-card account-card">
                <div class="account-overview">
                    <div class="data-row">
                        <span class="muted">Full Name</span>
                        <strong><?php echo e($name); ?></strong>
                    </div>
                    <div class="data-row">
                        <span class="muted">Email ID</span>
                        <strong><?php echo e($email); ?></strong>
                    </div>
                    <div class="data-row">
                        <span class="muted">Account Status</span>
                        <strong>Active</strong>
                    </div>
                    <div class="data-row">
                        <span class="muted">Phone Number</span>
                        <strong><?php echo e($profile['phone_number'] ?? 'Not added yet'); ?></strong>
                    </div>
                    <div class="data-row address-row">
                        <span class="muted">Delivery Address</span>
                        <strong><?php echo e($fullAddress !== '' ? $fullAddress : 'No delivery address saved yet.'); ?></strong>
                    </div>
                </div>
            </div>

            <div class="content-card account-card">
                <form method="POST" class="account-form">
                    <?php echo csrf_field('account_form'); ?>
                    <div id="profile-form" class="form-section-card">
                        <div class="section-copy">
                            <h2>Edit Profile</h2>
                            <p class="muted">Update your basic account information from this section.</p>
                        </div>

                        <div class="form-grid">
                            <div class="field-group">
                                <label for="name">Full Name</label>
                                <input type="text" id="name" name="name" class="input-field" value="<?php echo e($profile['name'] ?? ''); ?>" required>
                            </div>

                            <div class="field-group">
                                <label for="phone_number">Phone Number</label>
                                <input type="text" id="phone_number" name="phone_number" class="input-field" value="<?php echo e($profile['phone_number'] ?? ''); ?>" required>
                            </div>

                            <div class="field-group full-span">
                                <label for="email">Email ID</label>
                                <input type="email" id="email" class="input-field disabled-field" value="<?php echo e($email); ?>" readonly>
                            </div>
                        </div>
                    </div>

                    <div id="address-form" class="form-section-card">
                        <div class="section-copy">
                            <h2>Add Delivery Address</h2>
                            <p class="muted">Add the full address that should be used for future deliveries.</p>
                        </div>

                        <div class="form-grid">
                            <div class="field-group full-span">
                                <label for="address_line1">Address Line 1</label>
                                <input type="text" id="address_line1" name="address_line1" class="input-field" value="<?php echo e($profile['address_line1'] ?? ''); ?>" required>
                            </div>

                            <div class="field-group full-span">
                                <label for="address_line2">Address Line 2</label>
                                <input type="text" id="address_line2" name="address_line2" class="input-field" value="<?php echo e($profile['address_line2'] ?? ''); ?>">
                            </div>

                            <div class="field-group">
                                <label for="city">City</label>
                                <input type="text" id="city" name="city" class="input-field" value="<?php echo e($profile['city'] ?? ''); ?>" required>
                            </div>

                            <div class="field-group">
                                <label for="state">State</label>
                                <input type="text" id="state" name="state" class="input-field" value="<?php echo e($profile['state'] ?? ''); ?>" required>
                            </div>

                            <div class="field-group">
                                <label for="postal_code">Postal Code</label>
                                <input type="text" id="postal_code" name="postal_code" class="input-field" value="<?php echo e($profile['postal_code'] ?? ''); ?>" required>
                            </div>
                        </div>
                    </div>

                    <div class="form-actions">
                        <p class="form-note">Your saved details will appear in the dashboard profile section.</p>
                        <button type="submit" class="save-btn">
                            <i class="fa-solid fa-floppy-disk"></i> <?php echo e($saveButtonLabel); ?>
                        </button>
                    </div>
                </form>
            </div>
        </main>
    </div>
</body>

</html>
