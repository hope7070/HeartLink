<?php
require "config.php";
$error = '';
$success = '';
$token = $_GET['token'] ?? ($_POST['token'] ?? '');
if (!$token) {
    die('Invalid token.');
}
// find token
$stmt = $pdo->prepare("SELECT * FROM password_resets WHERE token = ? LIMIT 1");
$stmt->execute([$token]);
$reset = $stmt->fetch();
if (!$reset) {
    die('Token not found.');
}
if (new DateTime() > new DateTime($reset['expires_at'])) {
    die('Token expired.');
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = $_POST['password'] ?? '';
    $password2 = $_POST['password2'] ?? '';
    if (strlen($password) < 8) {
        $error = 'Password must be at least 8 characters.';
    } elseif ($password !== $password2) {
        $error = 'Passwords do not match.';
    } else {
        // update user password
        $hashed = password_hash($password, PASSWORD_DEFAULT);
        $update = $pdo->prepare("UPDATE users SET password = ? WHERE email = ?");
        $update->execute([$hashed, $reset['email']]);
        // delete resets for this email
        $del = $pdo->prepare("DELETE FROM password_resets WHERE email = ?");
        $del->execute([$reset['email']]);
        $success = 'Password updated. You can now <a href="login.php">login</a>.';
    }
}
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Reset Password</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container py-5" style="max-width:500px">
<div class="card p-4">
<h2>Reset Password</h2>
<?php if ($error): ?><div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>
<?php if ($success): ?><div class="alert alert-success"><?php echo $success; ?></div><?php else: ?>
<form method="post">
<input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
<input class="form-control mb-3" type="password" name="password" placeholder="New password" required>
<input class="form-control mb-3" type="password" name="password2" placeholder="Confirm password" required>
<button class="btn btn-primary w-100">Reset Password</button>
</form>
<?php endif; ?>
<p class="mt-3"><a href="login.php">Back to login</a></p>
</div>
</div>
</body>
</html>