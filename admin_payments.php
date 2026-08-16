<?php 
require "auth.php"; 

$adminEmail = "hopenambuta@gmail.com";

$me_result = $pdo->prepare("SELECT email FROM users WHERE id = ?");
$me_result->execute([$me]);
$me_user = $me_result->fetch();

if (!$me_user || $me_user['email'] !== $adminEmail) {
    die("Access denied. Admin only.");
}

$error = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (isset($_POST["payment_id"]) && isset($_POST["status"])) {
        $payment_id = intval($_POST["payment_id"]);
        $new_status = $_POST["status"];
        
        if (in_array($new_status, ['pending', 'success', 'failed'])) {
            try {
                $update = $pdo->prepare("UPDATE payments SET status = ? WHERE id = ?");
                $update->execute([$new_status, $payment_id]);
                $success = "Payment updated successfully.";
            } catch (PDOException $e) {
                $error = "Failed to update payment: " . $e->getMessage();
            }
        } else {
            $error = "Invalid status.";
        }
    }
}

$filter_status = $_GET['filter'] ?? '';
$query = "SELECT p.*, u.email as user_email FROM payments p LEFT JOIN users u ON p.user_id = u.id";
if ($filter_status && in_array($filter_status, ['pending', 'success', 'failed'])) {
    $query .= " WHERE p.status = ?";
    $stmt = $pdo->prepare($query . " ORDER BY p.created_at DESC");
    $stmt->execute([$filter_status]);
} else {
    $stmt = $pdo->prepare($query . " ORDER BY p.created_at DESC");
    $stmt->execute();
}
$payments = $stmt->fetchAll();
?>
<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Admin - Payments</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <nav class="navbar bg-white">
        <div class="container">
            <b class="text-danger">♥ HeartLink Admin</b>
            <div>
                <span class="me-3">Admin: <?php echo htmlspecialchars($me_user['email']); ?></span>
                <a href="dashboard.php" class="btn btn-outline-secondary btn-sm">Dashboard</a>
                <a href="logout.php" class="btn btn-outline-secondary btn-sm">Logout</a>
            </div>
        </div>
    </nav>

    <main class="container py-4">
        <h2>Payment Management</h2>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>

        <div class="mb-3">
            <a href="?filter=" class="btn btn-secondary <?php echo !$filter_status ? 'active' : ''; ?>">All</a>
            <a href="?filter=pending" class="btn btn-warning <?php echo $filter_status === 'pending' ? 'active' : ''; ?>">Pending</a>
            <a href="?filter=success" class="btn btn-success <?php echo $filter_status === 'success' ? 'active' : ''; ?>">Success</a>
            <a href="?filter=failed" class="btn btn-danger <?php echo $filter_status === 'failed' ? 'active' : ''; ?>">Failed</a>
        </div>

        <table class="table table-striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>User Email</th>
                    <th>Phone</th>
                    <th>Provider</th>
                    <th>Amount</th>
                    <th>Status</th>
                    <th>Date</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($payments as $p): ?>
                    <tr>
                        <td><?php echo $p['id']; ?></td>
                        <td><?php echo $p['user_email'] ? htmlspecialchars($p['user_email']) : '(unknown)'; ?></td>
                        <td><?php echo htmlspecialchars($p['phone']); ?></td>
                        <td><?php echo htmlspecialchars($p['provider']); ?></td>
                        <td><?php echo number_format($p['amount'], 2); ?></td>
                        <td>
                            <span class="badge bg-<?php echo $p['status'] === 'success' ? 'success' : ($p['status'] === 'pending' ? 'warning' : 'danger'); ?>">
                                <?php echo ucfirst($p['status']); ?>
                            </span>
                        </td>
                        <td><?php echo $p['created_at']; ?></td>
                        <td>
                            <form method="post" style="display:inline;">
                                <input type="hidden" name="payment_id" value="<?php echo $p['id']; ?>">
                                <select name="status" class="form-select form-select-sm" onchange="this.form.submit();">
                                    <option value="">Set Status...</option>
                                    <option value="pending" <?php echo $p['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                    <option value="success" <?php echo $p['status'] === 'success' ? 'selected' : ''; ?>>Success</option>
                                    <option value="failed" <?php echo $p['status'] === 'failed' ? 'selected' : ''; ?>>Failed</option>
                                </select>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <?php if (empty($payments)): ?>
            <p class="alert alert-info">No payments found.</p>
        <?php endif; ?>

        <hr>
        <p><strong>Total payments:</strong> <?php echo count($payments); ?></p>

    </main>

    <footer class="text-center py-4 mt-5">
        <p class="text-muted">HeartLink Admin Dashboard</p>
    </footer>

</body>
</html>
