<?php
require "config.php";

header('Content-Type: application/json');

$verification_code = $_GET['verification_code'] ?? '';

if (!$verification_code) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid verification code']);
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT status FROM payments WHERE verification_code = ?");
    $stmt->execute([$verification_code]);
    $payment = $stmt->fetch();
    
    if ($payment) {
        echo json_encode(['status' => $payment['status']]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Payment not found']);
    }
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
