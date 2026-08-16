<?php
require "config.php";
session_start();

$verification_code = $_GET['verification_code'] ?? '';

if (!$verification_code) {
    die("Invalid verification code.");
}

// Check payment status
$stmt = $pdo->prepare("SELECT * FROM payments WHERE verification_code = ?");
$stmt->execute([$verification_code]);
$payment = $stmt->fetch();

if (!$payment) {
    die("Payment not found.");
}

// If payment is successful, complete registration
if ($payment['status'] === 'success') {
    if (isset($_SESSION['pending_registration'])) {
        $reg = $_SESSION['pending_registration'];
        try {
            $insert = $pdo->prepare("INSERT INTO users(name,email,password,gender,interested_in,dob,location,bio,verification_code) VALUES(?,?,?,?,?,?,?,?,?)");
            $insert->execute([$reg['name'], $reg['email'], $reg['password'], $reg['gender'], $reg['interested_in'], $reg['dob'], $reg['location'], $reg['bio'], $verification_code]);
            
            // Update payment record with user_id
            $user_id = $pdo->lastInsertId();
            $update = $pdo->prepare("UPDATE payments SET user_id = ? WHERE verification_code = ?");
            $update->execute([$user_id, $verification_code]);
            
            unset($_SESSION['pending_registration']);
            header("Location: login.php?registered=1");
            exit;
        } catch (PDOException $e) {
            $error = "Registration failed: " . $e->getMessage();
        }
    }
}
?>
<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Payment Processing</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <script>
        function checkPaymentStatus() {
            fetch('check_payment.php?verification_code=<?php echo urlencode($verification_code); ?>')
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        document.getElementById('status-text').innerHTML = '<div class="alert alert-success">Payment successful! Completing registration...</div>';
                        setTimeout(() => {
                            window.location.href = 'payment_wait.php?verification_code=<?php echo urlencode($verification_code); ?>';
                        }, 2000);
                    } else if (data.status === 'failed') {
                        document.getElementById('status-text').innerHTML = '<div class="alert alert-danger">Payment failed. <a href="register.php?step=payment">Try again</a></div>';
                    } else {
                        // Still pending, check again
                        setTimeout(checkPaymentStatus, 3000);
                    }
                })
                .catch(err => {
                    console.error('Error:', err);
                    setTimeout(checkPaymentStatus, 3000);
                });
        }
        window.onload = function() {
            checkPaymentStatus();
        };
    </script>
</head>
<body>
    <div class="container py-5" style="max-width:600px">
        <div class="card p-4 text-center">
            <h2>Processing Payment</h2>
            <p class="text-muted">MWK <?php echo number_format($payment['amount'], 2); ?></p>
            
            <div id="status-text">
                <div class="alert alert-info">
                    <div class="spinner-border" role="status" style="display:inline-block; margin-right:10px;">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    Waiting for payment confirmation from <?php echo htmlspecialchars($payment['provider']); ?>...
                </div>
            </div>

            <div class="mt-4">
                <p><strong>Provider:</strong> <?php echo htmlspecialchars($payment['provider']); ?></p>
                <p><strong>Phone:</strong> <?php echo htmlspecialchars($payment['phone']); ?></p>
                <p><strong>Verification Code:</strong> <code><?php echo $verification_code; ?></code></p>
            </div>

            <p class="text-muted small">If payment is not confirmed within 5 minutes, <a href="register.php">click here</a> to start over.</p>
        </div>
    </div>
</body>
</html>
