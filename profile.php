<?php
require "auth.php";
$error=''; $success='';
$u=$pdo->prepare("SELECT * FROM users WHERE id=?"); $u->execute([$me]); $meu=$u->fetch();

$districts = [
    'Thyolo','Mangochi','Blantyre','Chiradzulu','Machinga','Liwonde','Mulanje','Phalombe','Kasungu','Karonga','Rumphi','Mzimba','Chitipa','Mzuzu','Balaka','Salima','Nkhotakota','Zomba','Chikwawa','Nsanje','Dowa','Dedza','Mwanza','Neno','Lilongwe'
];
if (!$meu) { header('Location: login.php'); exit; }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Profile update (district, name, bio)
    if (isset($_POST['update_profile'])) {
        $new_name = trim($_POST['name'] ?? '');
        $new_bio = trim($_POST['bio'] ?? '');
        $new_location = trim($_POST['location'] ?? '');
        $allowedLower = array_map('strtolower', $districts);
        if (!in_array(strtolower($new_location), $allowedLower)) {
            $error = 'Please select a valid district.';
        } else {
            $idx = array_search(strtolower($new_location), $allowedLower);
            $normalized = $districts[$idx];
            $upd = $pdo->prepare("UPDATE users SET name = ?, bio = ?, location = ? WHERE id = ?");
            $upd->execute([$new_name, $new_bio, $normalized, $me]);
            $success = 'Profile updated.';
            $u->execute([$me]); $meu = $u->fetch();
        }
    }

    // Photo upload
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
        $allowed = ['image/jpeg','image/png','image/gif'];
        if (!in_array($_FILES['photo']['type'], $allowed)) { $error='Only JPG, PNG and GIF allowed.'; }
        else {
            $ext = pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION);
            $filename = 'user_'.$me . '_' . time() . '.' . $ext;
            $dest = __DIR__ . '/uploads/' . $filename;
            if (move_uploaded_file($_FILES['photo']['tmp_name'], $dest)) {
                $path = 'uploads/' . $filename;
                $upd = $pdo->prepare("UPDATE users SET photo = ? WHERE id = ?");
                $upd->execute([$path, $me]);
                $success = 'Profile photo updated.';
                // refresh user
                $u->execute([$me]); $meu = $u->fetch();
            } else { $error='Failed to move uploaded file.'; }
        }
    }
}
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Edit Profile</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="style.css">
</head>
<body>
<nav class="navbar bg-white"><div class="container"><a class="navbar-brand text-danger" href="dashboard.php">♥ HeartLink</a><div><a href="dashboard.php" class="btn btn-outline-secondary btn-sm">Back</a></div></div></nav>
<main class="container py-4" style="max-width:700px">
<h2>Edit Profile</h2>
<?php if($error):?><div class="alert alert-danger"><?=htmlspecialchars($error)?></div><?php endif; ?>
<?php if($success):?><div class="alert alert-success"><?=htmlspecialchars($success)?></div><?php endif; ?>
<div class="card p-3 mb-3">
    <div class="d-flex align-items-center">
        <?php if($meu['photo']): ?>
            <img src="<?=htmlspecialchars($meu['photo'])?>" style="width:96px;height:96px;object-fit:cover;border-radius:8px;margin-right:16px;">
        <?php else: ?>
            <div style="width:96px;height:96px;background:#f1f1f1;border-radius:8px;margin-right:16px;display:flex;align-items:center;justify-content:center;">No Photo</div>
        <?php endif; ?>
        <div>
            <h5><?=htmlspecialchars($meu['name'])?></h5>
            <p class="text-muted"><?=htmlspecialchars($meu['email'])?></p>
            <p class="text-muted">District: <?=htmlspecialchars($meu['location'] ?? '')?></p>
        </div>
    </div>
</div>
<!-- Profile update form -->
<form method="post" class="mb-4">
    <div class="mb-3">
        <label class="form-label">Full name</label>
        <input class="form-control" name="name" value="<?=htmlspecialchars($meu['name'])?>" required>
    </div>
    <div class="mb-3">
        <label class="form-label">District</label>
        <select class="form-select" name="location" required>
            <?php foreach($districts as $d): ?>
                <option value="<?=htmlspecialchars($d)?>" <?=($meu['location']===$d)?'selected':''?>><?=htmlspecialchars($d)?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="mb-3">
        <label class="form-label">About / Bio</label>
        <textarea class="form-control" name="bio"><?=htmlspecialchars($meu['bio'] ?? '')?></textarea>
    </div>
    <button name="update_profile" class="btn btn-success mb-3">Save Profile</button>
</form>

<!-- Photo upload form -->
<form method="post" enctype="multipart/form-data">
    <label class="form-label">Upload profile photo (JPG, PNG, GIF)</label>
    <input type="file" name="photo" class="form-control mb-3" accept="image/*">
    <button class="btn btn-primary">Upload</button>
</form>
</main>
</body>
</html>
