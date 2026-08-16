<?php
require "config.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    die("Invalid request.");
}

$amount = floatval($_POST['amount'] ?? 0);
$verification_code = $_POST['verification_code'] ?? '';
$email = trim($_POST['email'] ?? '');
$provider = trim($_POST['provider'] ?? '');
$phone = trim($_POST['phone'] ?? '');

if (!$amount || !$verification_code || !$email || !$provider || !$phone) {
    die("Missing required fields.");
}

try {
    // Insert payment record with verification code
    $stmt = $pdo->prepare("INSERT INTO payments (verification_code, email, provider, phone, amount, status, created_at) VALUES (?, ?, ?, ?, ?, 'pending', NOW())");
    $stmt->execute([$verification_code, $email, $provider, $phone, $amount]);
    
    // Store payment info in session
    session_start();
    $_SESSION['payment_info'] = [
        'verification_code' => $verification_code,
        'provider' => $provider,
        'phone' => $phone,
        'amount' => $amount
    ];
    
    // Simulate payment processing (mock USSD flow)
    // In production, this would call the actual provider API
    header("Location: payment_wait.php?verification_code=" . urlencode($verification_code));
    exit;
    
} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}
?>
