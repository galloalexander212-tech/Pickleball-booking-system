<?php
require "function.php";

 $name = $_GET["name"] ?? "player";
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>PICKLE | Account Created</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <link href="https://fonts.googleapis.com/css2?family=Alef:wght@400;700&family=Alike+Angular&display=swap"
          rel="stylesheet">

    <link rel="stylesheet" href="auth.css">

</head>

<body>

<div class="auth-wrap">

    <div class="auth-card center">

        <div class="success-icon">🏓</div>

        <h2 class="auth-title">
            You're in, <?= e($name) ?>!
        </h2>

        <p class="auth-sub">
            Your PICKLE account has been created.<br>
            Log in to book courts, join open plays and compete.
        </p>


        <div class="auth-actions">

            <a href="index.php" class="auth-btn">
                LOG IN NOW
            </a>

        </div>


        <a class="auth-back" href="../index.php">
            ← BACK TO HOMEPAGE
        </a>

    </div>

</div>

</body>
</html>