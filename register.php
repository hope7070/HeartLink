<?php
require "config.php";
session_start();

$error = "";
$districts = [
    'Thyolo','Mangochi','Blantyre','Chiradzulu','Machinga','Liwonde','Mulanje','Phalombe','Kasungu','Karonga','Rumphi','Mzimba','Chitipa','Mzuzu','Balaka','Salima','Nkhotakota','Zomba','Chikwawa','Nsanje','Dowa','Dedza','Mwanza','Neno','Lilongwe'
];
$step = isset($_GET['step']) ? $_GET['step'] : 'form';
$registration_fee = 1500.00;

if ($step === 'form' && $_SERVER["REQUEST_METHOD"] !== "POST") {
    // show form

} elseif ($_SERVER["REQUEST_METHOD"] === "POST" && $step === 'form') {
    $name = trim($_POST["name"] ?? '');
    $email = trim($_POST["email"] ?? '');
    $pass = $_POST["password"] ?? '';
    $gender = $_POST["gender"] ?? '';
    $interest = $_POST["interested_in"] ?? '';
    $dob = $_POST["dob"] ?? '';
    $location = trim($_POST["location"] ?? '');
    $bio = trim($_POST["bio"] ?? '');

    // Validate district
    $allowedLower = array_map('strtolower', $districts);
    if (!in_array(strtolower($location), $allowedLower)) {
        $error = "Please select a valid district from the list.";
    } else {
        $idx = array_search(strtolower($location), $allowedLower);
        $location = $districts[$idx];
    }

    if (!$error) {
        if (strlen($pass) < 8) {
            $error = "Password must be at least 8 characters.";
        } else {
            $check = $pdo->prepare("SELECT id FROM users WHERE email = ?");
            $check->execute([$email]);
            if ($check->fetch()) {
                $error = "Email already registered.";
            } else {
                $verification_code = strtoupper(bin2hex(random_bytes(8)));
                $_SESSION['pending_registration'] = [
                    'name' => $name,
                    'email' => $email,
                    'password' => password_hash($pass, PASSWORD_DEFAULT),
                    'gender' => $gender,
                    'interested_in' => $interest,
                    'dob' => $dob,
                    'location' => $location,
                    'bio' => $bio,
                    'verification_code' => $verification_code
                ];
                header("Location: register.php?step=payment&code=" . urlencode($verification_code));
                exit;
            }
        }
    }

} elseif ($step === 'payment') {
    if (!isset($_SESSION['pending_registration'])) {
        $error = "Session expired. Please register again.";
        $step = 'form';
    }

} elseif ($step === 'complete') {
    if (!isset($_SESSION['pending_registration'])) {
        $error = "Session expired. Please register again.";
        $step = 'form';
    } else {
        $reg = $_SESSION['pending_registration'];
        try {
            $s = $pdo->prepare("INSERT INTO users(name,email,password,gender,interested_in,dob,location,bio,verification_code) VALUES(?,?,?,?,?,?,?,?,?)");
            $s->execute([$reg['name'], $reg['email'], $reg['password'], $reg['gender'], $reg['interested_in'], $reg['dob'], $reg['location'], $reg['bio'], $reg['verification_code']]);
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
    <title>Register</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container py-5" style="max-width:650px"><div class="card p-4">
<?php if ($step === 'form'): ?>
    <h2>Create account</h2>
    <p>Join HeartLink</p>
    <?php if($error):?><div class="alert alert-danger"><?=htmlspecialchars($error)?></div><?php endif;?>
    <form method="post" action="register.php?step=form">
        <input class="form-control mb-3" name="name" placeholder="Full name" required>
        <input class="form-control mb-3" type="email" name="email" placeholder="Email" required>
        <input class="form-control mb-3" type="password" name="password" placeholder="Password (8+ characters)" required>
        <div class="row">
            <div class="col">
                <label>Gender</label>
                <select class="form-select mb-3" name="gender" required>
                    <option value="male">Male</option>
                    <option value="female">Female</option>
                </select>
            </div>
            <div class="col">
                <label>Interested in</label>
                <select class="form-select mb-3" name="interested_in" required>
                    <option value="female">Female</option>
                    <option value="male">Male</option>
                </select>
            </div>
        </div>
        <label>Date of birth</label>
        <input class="form-control mb-3" type="date" name="dob" required>
        <label>District</label>
        <select class="form-select mb-3" name="location" required>
            <option value="">Choose your district...</option>
            <?php foreach($districts as $d): ?>
                <option value="<?=htmlspecialchars($d)?>" <?= (isset($_POST['location']) && $_POST['location']===$d) ? 'selected' : '' ?>><?=htmlspecialchars($d)?></option>
            <?php endforeach; ?>
        </select>
        <textarea class="form-control mb-3" name="bio" placeholder="Tell people about yourself"></textarea>
        <button class="btn btn-danger w-100">Continue to Payment</button>
    </form>
    <p class="mt-3">Already registered? <a href="login.php">Login</a></p>

<?php elseif ($step === 'payment'): ?>
    <h2>Complete Registration</h2>
    <p>Step 2 of 2: Payment Required</p>
    <?php if($error):?><div class="alert alert-danger"><?=htmlspecialchars($error)?></div><?php endif;?>
    <div class="alert alert-info"><strong>Registration Fee:</strong> MWK <?=number_format($registration_fee, 2)?></div>
    <?php if (isset($_SESSION['pending_registration'])): ?>
        <div class="card mb-3 bg-light"><div class="card-body">
            <p><strong>Name:</strong> <?=htmlspecialchars($_SESSION['pending_registration']['name'])?></p>
            <p><strong>Email:</strong> <?=htmlspecialchars($_SESSION['pending_registration']['email'])?></p>
            <p><strong>Verification Code:</strong> <code><?=$_SESSION['pending_registration']['verification_code']?></code></p>
        </div></div>
        <form method="post" action="initiate_payment.php">
            <input type="hidden" name="amount" value="<?=$registration_fee?>">
            <input type="hidden" name="verification_code" value="<?=$_SESSION['pending_registration']['verification_code']?>">
            <input type="hidden" name="email" value="<?=$_SESSION['pending_registration']['email']?>">
            <label>Select Payment Provider</label>
            <select class="form-select mb-3" name="provider" required>
                <option value="">Choose provider...</option>
                <option value="Mpamba">Mpamba</option>
                <option value="Airtel Money">Airtel Money</option>
                <option value="NBS">NBS Bank</option>
            </select>
            <label>Phone Number</label>
            <input class="form-control mb-3" type="tel" name="phone" placeholder="e.g., 0883836831" required>
            <button class="btn btn-primary w-100">Initiate Payment</button>
        </form>
        <a href="register.php?step=form" class="btn btn-secondary w-100 mt-2">Go Back</a>
    <?php else: ?>
        <div class="alert alert-danger">Session expired. <a href="register.php">Start over</a></div>
    <?php endif; ?>

<?php endif; ?>

</div></div>
</body>
</html>