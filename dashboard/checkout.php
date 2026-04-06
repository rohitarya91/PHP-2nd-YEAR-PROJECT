<?php
require_once __DIR__ . '/../config/store.php';

require_login();

if (is_admin()) {
    redirect_to('dashboard.php');
}

$cart = get_cart_totals($conn);
if (empty($cart['items'])) {
    set_flash('error', 'Your cart is empty. Add products before checkout.');
    redirect_to('cart.php');
}

$flash = get_flash();
$errors = [];
$profile = fetch_user_profile($conn, current_user_id()) ?? [];
$paymentOptions = get_checkout_payment_options();
$bankOptions = get_netbanking_bank_options();
$checkoutData = [
    'shipping_name' => trim((string) ($profile['name'] ?? current_user_name())),
    'shipping_phone' => trim((string) ($profile['phone_number'] ?? '')),
    'address_line1' => trim((string) ($profile['address_line1'] ?? '')),
    'address_line2' => trim((string) ($profile['address_line2'] ?? '')),
    'city' => trim((string) ($profile['city'] ?? '')),
    'state' => trim((string) ($profile['state'] ?? '')),
    'postal_code' => trim((string) ($profile['postal_code'] ?? '')),
    'payment_method' => 'card',
    'card_name' => '',
    'card_number' => '4242424242424242',
    'card_expiry' => '12/30',
    'card_cvv' => '123',
    'upi_id' => 'demo@ok',
    'bank_code' => 'HDFC',
    'bank_account_name' => trim((string) ($profile['name'] ?? current_user_name())),
    'save_to_profile' => 1,
];

if (request_is_post() && isset($_POST['place_order'])) {
    enforce_csrf_or_errors($errors, 'checkout_form');
    $addressValidated = validate_checkout_address_data($_POST);
    $paymentValidated = validate_checkout_payment_data($_POST);
    $errors = array_merge($errors, $addressValidated['errors'], $paymentValidated['errors']);
    $checkoutData = array_merge($checkoutData, $addressValidated, $paymentValidated['payment']);

    if (empty($errors)) {
        $paymentResult = process_test_payment_gateway($paymentValidated['payment'], (float) $cart['total_amount']);

        if (!$paymentResult['success']) {
            $errors[] = $paymentResult['message'];
        } else {
            $orderResult = place_order_from_checkout($conn, current_user_id(), array_merge($addressValidated, [
                'payment_method' => get_payment_method_label($paymentValidated['payment']['payment_method']),
                'payment_status' => $paymentResult['status'],
                'gateway_name' => $paymentResult['gateway_name'],
                'payment_reference' => $paymentResult['payment_reference'],
                'payment_payload' => json_encode([
                    'mode' => PAYMENT_GATEWAY_MODE,
                    'payment_method' => $paymentValidated['payment']['payment_method'],
                    'gateway_message' => $paymentResult['message'],
                    'amount' => $cart['total_amount'],
                ]),
            ]));

            if (!empty($orderResult['success'])) {
                set_flash('success', 'Order #' . (int) ($orderResult['order_id'] ?? 0) . ' placed successfully. ' . $paymentResult['message']);
                redirect_to('user_dashboard.php?section=orders');
            }

            $errors[] = $orderResult['message'] ?? 'Unable to place the order right now.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <style>
        .checkout-layout {
            display: grid;
            grid-template-columns: minmax(0, 1.15fr) minmax(320px, 0.85fr);
            gap: 20px;
        }

        .checkout-form-grid {
            display: grid;
            gap: 18px;
        }

        .checkout-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 16px;
        }

        .checkout-grid .full-span {
            grid-column: 1 / -1;
        }

        .payment-option-list {
            display: grid;
            gap: 12px;
        }

        .payment-option-card {
            border: 1px solid rgba(255,255,255,0.12);
            border-radius: 16px;
            padding: 14px 16px;
            background: rgba(255,255,255,0.04);
        }

        .payment-option-card label {
            display: flex;
            gap: 12px;
            cursor: pointer;
        }

        .payment-detail-group {
            display: none;
            margin-top: 14px;
        }

        .payment-detail-group.active {
            display: grid;
            gap: 12px;
        }

        .checkout-product {
            display: grid;
            grid-template-columns: 68px minmax(0, 1fr);
            gap: 12px;
            align-items: center;
            padding: 12px 0;
            border-bottom: 1px solid rgba(255,255,255,0.08);
        }

        .checkout-product:last-child {
            border-bottom: none;
        }

        .checkout-product img {
            width: 68px;
            height: 68px;
            object-fit: cover;
            border-radius: 14px;
        }

        @media (max-width: 960px) {
            .checkout-layout {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 640px) {
            .checkout-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body class="page">
    <div class="page-shell">
        <aside class="glass-panel side-panel">
            <div class="brand">
                <h2>Harvest Fresh</h2>
            </div>

            <ul class="nav-list">
                <li><a href="user_dashboard.php"><i class="fa-solid fa-home"></i> Dashboard</a></li>
                <li><a href="cart.php"><i class="fa-solid fa-cart-shopping"></i> Cart</a></li>
                <li><a href="../account/account.php"><i class="fa-solid fa-user"></i> Account</a></li>
                <li><a href="../auth/logout.php"><i class="fa-solid fa-right-from-bracket"></i> Logout</a></li>
            </ul>

            <div class="profile-card bottom-profile">
                <div class="profile-avatar"><?php echo e(get_avatar_letter(current_user_name())); ?></div>
                <h3><?php echo e(current_user_name()); ?></h3>
            </div>
        </aside>

        <main class="glass-panel main-panel">
            <div class="panel-header">
                <div class="panel-title">
                    <h1>Checkout</h1>
                    <p class="muted">Confirm shipping details, choose a payment method, and place the order securely.</p>
                </div>
                <a href="cart.php" class="btn-ghost"><i class="fa-solid fa-arrow-left"></i> Back to Cart</a>
            </div>

            <?php if ($flash): ?>
                <div class="content-card message-card <?php echo $flash['type'] === 'error' ? 'error-card' : 'success-card'; ?>">
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

            <div class="checkout-layout">
                <section class="content-card">
                    <form method="POST" class="checkout-form-grid">
                        <?php echo csrf_field('checkout_form'); ?>

                        <div class="section-copy">
                            <h2>Delivery Address</h2>
                            <p class="muted">Edit the shipping address used for this order. You can also save it back to your account.</p>
                        </div>

                        <div class="checkout-grid">
                            <input type="text" name="shipping_name" class="input-field" placeholder="Full Name" value="<?php echo e($checkoutData['shipping_name']); ?>" required>
                            <input type="text" name="shipping_phone" class="input-field" placeholder="Phone Number" value="<?php echo e($checkoutData['shipping_phone']); ?>" required>
                            <input type="text" name="address_line1" class="input-field full-span" placeholder="Address Line 1" value="<?php echo e($checkoutData['address_line1']); ?>" required>
                            <input type="text" name="address_line2" class="input-field full-span" placeholder="Address Line 2" value="<?php echo e($checkoutData['address_line2']); ?>">
                            <input type="text" name="city" class="input-field" placeholder="City" value="<?php echo e($checkoutData['city']); ?>" required>
                            <input type="text" name="state" class="input-field" placeholder="State" value="<?php echo e($checkoutData['state']); ?>" required>
                            <input type="text" name="postal_code" class="input-field" placeholder="Postal Code" value="<?php echo e($checkoutData['postal_code']); ?>" required>
                        </div>

                        <label class="muted" style="display:flex; gap:10px; align-items:center;">
                            <input type="checkbox" name="save_to_profile" value="1" <?php echo (int) ($checkoutData['save_to_profile'] ?? 0) === 1 ? 'checked' : ''; ?>>
                            Save this address to my profile for future orders
                        </label>

                        <div class="section-copy">
                            <h2>Payment Method</h2>
                            <p class="muted">Test gateway mode is active. Use the sample values below to simulate payment success or failure.</p>
                        </div>

                        <div class="payment-option-list">
                            <?php foreach ($paymentOptions as $method => $option): ?>
                                <div class="payment-option-card">
                                    <label>
                                        <input type="radio" name="payment_method" value="<?php echo e($method); ?>" <?php echo $checkoutData['payment_method'] === $method ? 'checked' : ''; ?>>
                                        <span>
                                            <strong><?php echo e($option['label']); ?></strong><br>
                                            <span class="muted"><?php echo e($option['description']); ?></span>
                                        </span>
                                    </label>

                                    <div class="payment-detail-group <?php echo $checkoutData['payment_method'] === $method ? 'active' : ''; ?>" data-payment-method="<?php echo e($method); ?>">
                                        <?php if ($method === 'card'): ?>
                                            <input type="text" name="card_name" class="input-field" placeholder="Cardholder Name" value="<?php echo e($checkoutData['card_name']); ?>">
                                            <input type="text" name="card_number" class="input-field" placeholder="Test Card Number" value="<?php echo e($checkoutData['card_number']); ?>">
                                            <div class="checkout-grid">
                                                <input type="text" name="card_expiry" class="input-field" placeholder="MM/YY" value="<?php echo e($checkoutData['card_expiry']); ?>">
                                                <input type="text" name="card_cvv" class="input-field" placeholder="CVV" value="<?php echo e($checkoutData['card_cvv']); ?>">
                                            </div>
                                            <p class="muted">Use `4242424242424242` for success or `4000000000000002` for failure.</p>
                                        <?php elseif ($method === 'upi'): ?>
                                            <input type="text" name="upi_id" class="input-field" placeholder="UPI ID" value="<?php echo e($checkoutData['upi_id']); ?>">
                                            <p class="muted">Use `demo@ok` for success or any ID ending with `@fail` for failure.</p>
                                        <?php elseif ($method === 'netbanking'): ?>
                                            <select name="bank_code" class="input-field">
                                                <?php foreach ($bankOptions as $bankCode => $bankName): ?>
                                                    <option value="<?php echo e($bankCode); ?>" <?php echo $checkoutData['bank_code'] === $bankCode ? 'selected' : ''; ?>><?php echo e($bankName); ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                            <input type="text" name="bank_account_name" class="input-field" placeholder="Account Holder Name" value="<?php echo e($checkoutData['bank_account_name']); ?>">
                                            <p class="muted">Choose `Failure Test Bank` to simulate a declined netbanking payment.</p>
                                        <?php else: ?>
                                            <p class="muted">No upfront payment is needed. Payment will be collected when the order arrives.</p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <button type="submit" name="place_order" class="btn-primary" style="width:100%; padding:14px 18px;">
                            <span>Pay & Place Order</span>
                            <i class="fas fa-bag-shopping"></i>
                        </button>
                    </form>
                </section>

                <aside class="content-card">
                    <div class="section-copy">
                        <h2>Order Summary</h2>
                        <p class="muted"><?php echo e((string) $cart['total_items']); ?> items in your basket.</p>
                    </div>

                    <?php foreach ($cart['items'] as $item): ?>
                        <div class="checkout-product">
                            <img src="<?php echo e(product_image_src($item['image'])); ?>" alt="<?php echo e($item['name']); ?>">
                            <div>
                                <strong><?php echo e($item['name']); ?></strong>
                                <p class="muted"><?php echo e(product_unit_label($item)); ?> | Qty <?php echo e((string) $item['quantity']); ?></p>
                                <p class="muted">ETA <?php echo e(product_delivery_window($item)); ?></p>
                                <p class="price">Rs <?php echo e((string) $item['subtotal']); ?></p>
                            </div>
                        </div>
                    <?php endforeach; ?>

                    <div class="data-row">
                        <span>MRP Total</span>
                        <strong>Rs <?php echo e((string) $cart['mrp_total']); ?></strong>
                    </div>
                    <div class="data-row">
                        <span>Selling Price</span>
                        <strong>Rs <?php echo e((string) $cart['subtotal_amount']); ?></strong>
                    </div>
                    <div class="data-row">
                        <span>Savings</span>
                        <strong>Rs <?php echo e((string) $cart['discount_amount']); ?></strong>
                    </div>
                    <div class="data-row">
                        <span>Delivery Fee</span>
                        <strong>Rs <?php echo e((string) $cart['delivery_fee']); ?></strong>
                    </div>
                    <div class="data-row">
                        <span>Handling Fee</span>
                        <strong>Rs <?php echo e((string) $cart['handling_fee']); ?></strong>
                    </div>
                    <div class="data-row">
                        <span>Estimated Delivery</span>
                        <strong><?php echo e($cart['estimated_delivery']); ?></strong>
                    </div>
                    <div class="data-row">
                        <span>Total Payable</span>
                        <strong>Rs <?php echo e((string) $cart['total_amount']); ?></strong>
                    </div>
                </aside>
            </div>
        </main>
    </div>

    <script>
        const paymentMethodInputs = document.querySelectorAll('input[name="payment_method"]');
        const paymentGroups = document.querySelectorAll('.payment-detail-group');

        function updatePaymentGroups() {
            const activeValue = document.querySelector('input[name="payment_method"]:checked')?.value || 'card';
            paymentGroups.forEach(group => {
                group.classList.toggle('active', group.dataset.paymentMethod === activeValue);
            });
        }

        paymentMethodInputs.forEach(input => {
            input.addEventListener('change', updatePaymentGroups);
        });

        updatePaymentGroups();
    </script>
</body>

</html>
