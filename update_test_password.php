<?php
require __DIR__ . '/../config.php';
$h = password_hash('password123', PASSWORD_DEFAULT);
$s = $pdo->prepare('UPDATE users SET password=? WHERE email=?');
$s->execute([$h, 'autotest@example.com']);
echo "UPDATED\n";
