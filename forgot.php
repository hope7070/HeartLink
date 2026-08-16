<?php
require "config.php";
$error = '';
$info = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email.';
    } else {
        // generate token
        $token = bin2hex(random_bytes(16));
        $expires = date('Y-m-d H:i:s', time() + 60*60); // 1 hour
        // insert into password_resets
        $stmt = $pdo->prepare("INSERT INTO password_resets (email, token, expires_at) VALUES (?, ?, ?)");
        $stmt->execute([$email, $token, $expires]);
        // In production you would send email. For local testing we show the reset link.
        $resetLink = sprintf('http://%s/HeartLink_Dating_Website/reset_password.php?token=%s', 
            $_SERVER['HTTP_HOST'], urlencode($token));
        $info = 'If the email exists, a reset link will be sent. For testing, use this link: <a href="' . htmlspecialchars($resetLink) . '">Reset password</a>';
    }
}
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Forgot Password</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container py-5" style="max-width:500px">
<div class="card p-4">
<h2>Forgot Password</h2>
<p>Enter your account email and we will send a reset link.</p>
<?php if ($error): ?><div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>
<?php if ($info): ?><div class="alert alert-info"><?php echo $info; ?></div><?php endif; ?>
<form method="post">
<input class="form-control mb-3" type="email" name="email" placeholder="Your email" required>
<button class="btn btn-primary w-100">Send Reset Link</button>
</form>
<p class="mt-3"><a href="login.php">Back to login</a></p>
</div>
</div>
</body>
</html>