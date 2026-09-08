<?php
require "config.php";
require "function.php";
require "validation.php";

requireLogin();

 $userId = $_SESSION["user_id"];

/* Fetch current account data */
 $stmt = $conn->prepare("SELECT username, email, password FROM users WHERE id = ? LIMIT 1");
 $stmt->bind_param("i", $userId);
 $stmt->execute();
 $user = $stmt->get_result()->fetch_assoc();
 $stmt->close();

/* Account gone? Kick out */
if (!$user) {
    session_unset();
    session_destroy();
    redirect("index.php");
}

 $errors = [];
 $activeForm = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $action = $_POST["action"] ?? "";

    /* ================= UPDATE USERNAME / EMAIL ================= */
    if ($action === "profile") {

        $activeForm = "profile";

        $username = trim($_POST["username"] ?? "");
        $email    = trim($_POST["email"] ?? "");

        $validator = new Validator();

        $validator->checkEmpty($username, "username", "Username");
        $validator->checkEmpty($email, "email", "Email");

        if (!$validator->hasErrors()) {
            $validator->validUsername($username, "username");
            $validator->validEmail($email, "email");
        }

        /* Duplicate check — excluding YOUR own account */
        if (!$validator->hasErrors()) {

            $stmt = $conn->prepare("
                SELECT id, username, email FROM users
                WHERE (username = ? OR email = ?) AND id != ?
                LIMIT 1
            ");
            $stmt->bind_param("ssi", $username, $email, $userId);
            $stmt->execute();
            $existing = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            if ($existing) {
                if (strcasecmp($existing["username"], $username) === 0) {
                    $validator->errors["username"] = "That username is already taken.";
                } else {
                    $validator->errors["email"] = "That email is already registered.";
                }
            }
        }

        if (!$validator->hasErrors()) {

            $stmt = $conn->prepare("UPDATE users SET username = ?, email = ? WHERE id = ?");
            $stmt->bind_param("ssi", $username, $email, $userId);
            $stmt->execute();
            $stmt->close();

            /* Keep the session in sync — navbar shows the new name instantly */
            $_SESSION["username"] = $username;

            setFlash("Profile updated!");
            redirect("edit-profile.php");
        }

        $errors = $validator->errors;
    }

    /* ================= CHANGE PASSWORD ================= */
    if ($action === "password") {

        $activeForm = "password";

        $current = $_POST["current_password"] ?? "";
        $new     = $_POST["new_password"] ?? "";
        $confirm = $_POST["confirm_password"] ?? "";

        $validator = new Validator();

        $validator->checkEmpty($current, "current_password", "Current password");
        $validator->checkEmpty($new, "new_password", "New password");
        $validator->checkEmpty($confirm, "confirm_password", "Password confirmation");

        if (!$validator->hasErrors()) {
            $validator->validPassword($new, "new_password");
            $validator->passwordMatch($new, $confirm, "confirm_password");
        }

        /* Current password must be correct */
        if (!$validator->hasErrors() && !password_verify($current, $user["password"])) {
            $validator->errors["current_password"] = "Your current password is incorrect.";
        }

        /* New password must actually be new */
        if (!$validator->hasErrors() && password_verify($new, $user["password"])) {
            $validator->errors["new_password"] = "New password must be different from the current one.";
        }

        if (!$validator->hasErrors()) {

            $hashed = password_hash($new, PASSWORD_DEFAULT);

            $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
            $stmt->bind_param("si", $hashed, $userId);
            $stmt->execute();
            $stmt->close();

            setFlash("Password changed successfully!");
            redirect("edit-profile.php");
        }

        $errors = $validator->errors;
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>PICKLE | Edit Profile</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <link href="https://fonts.googleapis.com/css2?family=Alef:wght@400;700&family=Alike+Angular&display=swap"
          rel="stylesheet">

    <link rel="stylesheet" href="auth.css">

</head>

<body>

<div class="auth-wrap">

    <!-- ================= FLASH MESSAGE ================= -->

    <?php $flash = getFlash(); ?>

    <?php if ($flash): ?>

        <div class="flash <?= e($flash["type"]) ?>">
            <?= e($flash["message"]) ?>
        </div>

    <?php endif; ?>


    <div class="auth-card">

        <a href="../index.php" class="auth-logo">
            <img src="../images/logo.png" alt="PICKLE" class="auth-logo-img">
        </a>


        <h2 class="auth-title">
            Edit Profile
        </h2>

        <p class="auth-sub">
            Update your account details or change your password.
        </p>


        <!-- ================= PROFILE FORM ================= -->

        <h3 class="section-sub">
            ACCOUNT DETAILS
        </h3>

        <form method="POST" action="edit-profile.php">

            <input type="hidden" name="action" value="profile">

            <div class="form-group">

                <label>USERNAME</label>

                <input type="text"
                       name="username"
                       value="<?= old("username") ?: e($user["username"]) ?>">

                <?php if (isset($errors["username"])): ?>
                    <p class="field-error"><?= e($errors["username"]) ?></p>
                <?php endif; ?>

            </div>


            <div class="form-group">

                <label>EMAIL</label>

                <input type="email"
                       name="email"
                       value="<?= old("email") ?: e($user["email"]) ?>">

                <?php if (isset($errors["email"])): ?>
                    <p class="field-error"><?= e($errors["email"]) ?></p>
                <?php endif; ?>

            </div>


            <button type="submit" class="auth-btn">
                SAVE CHANGES
            </button>

        </form>


        <div class="auth-divider"></div>


        <!-- ================= PASSWORD FORM ================= -->

        <h3 class="section-sub">
            CHANGE PASSWORD
        </h3>

        <form method="POST" action="edit-profile.php">

            <input type="hidden" name="action" value="password">

            <div class="form-group">

                <label>CURRENT PASSWORD</label>

                <input type="password"
                       name="current_password"
                       placeholder="Your current password">

                <?php if (isset($errors["current_password"])): ?>
                    <p class="field-error"><?= e($errors["current_password"]) ?></p>
                <?php endif; ?>

            </div>


            <div class="form-group">

                <label>NEW PASSWORD</label>

                <input type="password"
                       name="new_password"
                       placeholder="At least 8 characters">

                <?php if (isset($errors["new_password"])): ?>
                    <p class="field-error"><?= e($errors["new_password"]) ?></p>
                <?php endif; ?>

            </div>


            <div class="form-group">

                <label>CONFIRM NEW PASSWORD</label>

                <input type="password"
                       name="confirm_password"
                       placeholder="Repeat your new password">

                <?php if (isset($errors["confirm_password"])): ?>
                    <p class="field-error"><?= e($errors["confirm_password"]) ?></p>
                <?php endif; ?>

            </div>


            <label class="show-pass">
                <input type="checkbox"> Show password
            </label>


            <button type="submit" class="auth-btn outline">
                CHANGE PASSWORD
            </button>

        </form>


        <div class="auth-actions">

    <a href="info.php" class="auth-btn">
        ← MY ACCOUNT
    </a>

</div>


<a class="auth-back" href="../index.php">
    ← BACK TO HOMEPAGE
</a>

    </div>

</div>


<script>

    /* Show password toggle */
    document.querySelectorAll(".show-pass input").forEach(function (box) {

        box.addEventListener("change", function () {

            const form = box.closest("form");

            form.querySelectorAll('input[type="password"]').forEach(function (input) {
                input.type = box.checked ? "text" : "password";
            });

        });

    });

</script>

</body>
</html>