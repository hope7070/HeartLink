<?php
require "auth.php";
$adminEmail = 'hopenambuta@gmail.com';
$me_stmt = $pdo->prepare('SELECT email FROM users WHERE id = ?'); $me_stmt->execute([$me]); $me_row = $me_stmt->fetch();
if (!$me_row || $me_row['email'] !== $adminEmail) { die('Access denied. Admin only.'); }

$error=''; $success='';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = intval($_POST['user_id'] ?? 0);
    $new_pass = $_POST['new_password'] ?? '';
    if ($user_id && strlen($new_pass) >= 8) {
        $h = password_hash($new_pass, PASSWORD_DEFAULT);
        $upd = $pdo->prepare('UPDATE users SET password = ? WHERE id = ?');
        $upd->execute([$h, $user_id]);
        $success = 'Password updated.';
    } else {
        $error = 'Invalid input or password too short.';
    }
}
$users = $pdo->query('SELECT id,name,email FROM users ORDER BY created_at DESC')->fetchAll();
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Admin - Users</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="style.css">
</head>
<body>
<nav class="navbar bg-white"><div class="container"><a class="navbar-brand text-danger" href="dashboard.php">♥ HeartLink</a><div><a href="admin_payments.php" class="btn btn-outline-secondary btn-sm me-2">Payments</a><a href="logout.php" class="btn btn-outline-secondary btn-sm">Logout</a></div></div></nav>
<main class="container py-4">
<h2>Manage Users</h2>
<?php if($error):?><div class="alert alert-danger"><?=htmlspecialchars($error)?></div><?php endif; ?>
<?php if($success):?><div class="alert alert-success"><?=htmlspecialchars($success)?></div><?php endif; ?>
<table class="table"><thead><tr><th>ID</th><th>Name</th><th>Email</th><th>Action</th></tr></thead><tbody>
<?php foreach($users as $u): ?>
<tr>
<td><?= $u['id'] ?></td>
<td><?= htmlspecialchars($u['name']) ?></td>
<td><?= htmlspecialchars($u['email']) ?></td>
<td>
<form method="post" class="d-flex">
<input type="hidden" name="user_id" value="<?= $u['id'] ?>">
<input type="password" name="new_password" placeholder="New password" class="form-control form-control-sm me-2" required>
<button class="btn btn-sm btn-warning">Set Password</button>
</form>
</td>
</tr>
<?php endforeach; ?>
</tbody></table>
</main>
</body>
</html>
