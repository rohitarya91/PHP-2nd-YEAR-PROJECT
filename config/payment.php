<?php
function get_checkout_payment_options(): array
{
    return [
        'card' => ['label' => 'Debit / Credit Card', 'description' => 'Use test card details to simulate a payment.'],
        'upi' => ['label' => 'UPI', 'description' => 'Pay using a test UPI ID and verify success or failure states.'],
        'netbanking' => ['label' => 'Netbanking', 'description' => 'Simulate a bank redirect flow using a test bank choice.'],
        'cod' => ['label' => 'Cash on Delivery', 'description' => 'Collect payment at doorstep after order confirmation.'],
    ];
}

function get_payment_method_label(string $paymentMethod): string
{
    $options = get_checkout_payment_options();

    return $options[$paymentMethod]['label'] ?? 'Cash on Delivery';
}

function get_netbanking_bank_options(): array
{
    return [
        'HDFC' => 'HDFC Bank',
        'ICICI' => 'ICICI Bank',
        'SBI' => 'State Bank of India',
        'AXIS' => 'Axis Bank',
        'FAILBANK' => 'Failure Test Bank',
    ];
}

function validate_checkout_address_data(array $input): array
{
    $shippingName = trim((string) ($input['shipping_name'] ?? ''));
    $shippingPhone = preg_replace('/\s+/', '', trim((string) ($input['shipping_phone'] ?? '')));
    $addressLine1 = trim((string) ($input['address_line1'] ?? ''));
    $addressLine2 = trim((string) ($input['address_line2'] ?? ''));
    $city = trim((string) ($input['city'] ?? ''));
    $state = trim((string) ($input['state'] ?? ''));
    $postalCode = trim((string) ($input['postal_code'] ?? ''));
    $saveToProfile = isset($input['save_to_profile']) ? 1 : 0;
    $errors = [];

    if ($shippingName === '') {
        $errors[] = 'Shipping name is required.';
    }

    if ($shippingPhone === '' || !preg_match('/^[0-9+\-]{7,15}$/', $shippingPhone)) {
        $errors[] = 'Enter a valid shipping phone number.';
    }

    if ($addressLine1 === '') {
        $errors[] = 'Address line 1 is required.';
    }

    if ($city === '') {
        $errors[] = 'City is required.';
    }

    if ($state === '') {
        $errors[] = 'State is required.';
    }

    if ($postalCode === '' || !preg_match('/^[0-9A-Za-z -]{4,10}$/', $postalCode)) {
        $errors[] = 'Enter a valid postal code.';
    }

    return [
        'errors' => $errors,
        'shipping_name' => $shippingName,
        'shipping_phone' => $shippingPhone,
        'address_line1' => $addressLine1,
        'address_line2' => $addressLine2,
        'city' => $city,
        'state' => $state,
        'postal_code' => $postalCode,
        'shipping_address' => trim(implode(', ', array_filter([
            $addressLine1,
            $addressLine2,
            $city,
            $state,
            $postalCode,
        ]))),
        'save_to_profile' => $saveToProfile,
    ];
}

function validate_checkout_payment_data(array $input): array
{
    $paymentMethod = strtolower(trim((string) ($input['payment_method'] ?? 'cod')));
    $errors = [];
    $paymentData = [
        'payment_method' => $paymentMethod,
        'card_name' => trim((string) ($input['card_name'] ?? '')),
        'card_number' => preg_replace('/\D+/', '', (string) ($input['card_number'] ?? '')),
        'card_expiry' => trim((string) ($input['card_expiry'] ?? '')),
        'card_cvv' => preg_replace('/\D+/', '', (string) ($input['card_cvv'] ?? '')),
        'upi_id' => trim((string) ($input['upi_id'] ?? '')),
        'bank_code' => trim((string) ($input['bank_code'] ?? '')),
        'bank_account_name' => trim((string) ($input['bank_account_name'] ?? '')),
    ];

    if (!array_key_exists($paymentMethod, get_checkout_payment_options())) {
        $errors[] = 'Please choose a valid payment method.';
    }

    if ($paymentMethod === 'card') {
        if ($paymentData['card_name'] === '') {
            $errors[] = 'Cardholder name is required.';
        }

        if (!preg_match('/^[0-9]{13,19}$/', $paymentData['card_number'])) {
            $errors[] = 'Enter a valid test card number.';
        }

        if (!preg_match('/^(0[1-9]|1[0-2])\/[0-9]{2}$/', $paymentData['card_expiry'])) {
            $errors[] = 'Enter card expiry in MM/YY format.';
        }

        if (!preg_match('/^[0-9]{3,4}$/', $paymentData['card_cvv'])) {
            $errors[] = 'Enter a valid CVV.';
        }
    } elseif ($paymentMethod === 'upi') {
        if (!preg_match('/^[A-Za-z0-9.\-_]{2,256}@[A-Za-z]{2,64}$/', $paymentData['upi_id'])) {
            $errors[] = 'Enter a valid UPI ID.';
        }
    } elseif ($paymentMethod === 'netbanking') {
        if ($paymentData['bank_code'] === '') {
            $errors[] = 'Please select a bank for netbanking.';
        }

        if ($paymentData['bank_account_name'] === '') {
            $errors[] = 'Account holder name is required for netbanking.';
        }
    }

    return [
        'errors' => $errors,
        'payment' => $paymentData,
    ];
}

function process_test_payment_gateway(array $paymentData, float $amount): array
{
    $paymentMethod = $paymentData['payment_method'] ?? 'cod';
    $reference = 'PAY-' . strtoupper(substr(app_random_token(8), 0, 12));
    $gatewayName = PAYMENT_GATEWAY_NAME;

    if ($paymentMethod === 'card') {
        $cardNumber = $paymentData['card_number'] ?? '';
        if ($cardNumber === '4000000000000002') {
            return [
                'success' => false,
                'status' => 'Failed',
                'message' => 'Test card was declined by the gateway.',
                'gateway_name' => $gatewayName,
                'payment_reference' => $reference,
            ];
        }

        return [
            'success' => true,
            'status' => 'Paid',
            'message' => 'Card payment authorized successfully in test mode.',
            'gateway_name' => $gatewayName,
            'payment_reference' => $reference,
        ];
    }

    if ($paymentMethod === 'upi') {
        $upiId = strtolower((string) ($paymentData['upi_id'] ?? ''));
        if (str_ends_with($upiId, '@fail')) {
            return [
                'success' => false,
                'status' => 'Failed',
                'message' => 'UPI payment failed in test mode. Try a different test ID.',
                'gateway_name' => $gatewayName,
                'payment_reference' => $reference,
            ];
        }

        return [
            'success' => true,
            'status' => 'Paid',
            'message' => 'UPI payment completed successfully in test mode.',
            'gateway_name' => $gatewayName,
            'payment_reference' => $reference,
        ];
    }

    if ($paymentMethod === 'netbanking') {
        if (strtoupper((string) ($paymentData['bank_code'] ?? '')) === 'FAILBANK') {
            return [
                'success' => false,
                'status' => 'Failed',
                'message' => 'Netbanking test flow returned a failure response.',
                'gateway_name' => $gatewayName,
                'payment_reference' => $reference,
            ];
        }

        return [
            'success' => true,
            'status' => 'Paid',
            'message' => 'Netbanking payment completed successfully in test mode.',
            'gateway_name' => $gatewayName,
            'payment_reference' => $reference,
        ];
    }

    return [
        'success' => true,
        'status' => 'Pending',
        'message' => 'Cash on Delivery selected. Payment will be collected at delivery.',
        'gateway_name' => $gatewayName,
        'payment_reference' => $reference,
    ];
}
