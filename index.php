<?php require "config.php"; ?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>HeartLink</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="style.css">
</head>
<body>
<nav class="navbar navbar-expand-lg bg-white">
  <div class="container">
    <a class="navbar-brand text-danger" href="index.php">♥ HeartLink</a>
    <div>
      <a class="me-3" href="register.php">Sign Up</a> | <a class="ms-3" href="register.php">Join HeartLink</a>
    </div>
  </div>
</nav>

<section class="hero">
  <div class="container text-center">
    <h1 class="display-4 fw-bold">Meet someone who matches your heart.</h1>
    <p class="lead">Where genuine hearts find real connections</p>
    <a href="register.php" class="btn btn-light btn-lg">Create your profile</a>
  </div>
</section>

<div class="container py-5">
  <div class="row g-4 text-center">
    <div class="col-md-4"><h3>♥ Discover</h3><p>Find profiles based on gender, age and location.</p></div>
    <div class="col-md-4"><h3>💬 Connect</h3><p>Like profiles and chat after a mutual match.</p></div>
    <div class="col-md-4"><h3>🛡 Safety</h3><p>Block and report users when necessary.</p></div>
  </div>
</div>

</body>
</html>
