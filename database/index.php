<?php
require "config.php";
require "function.php";
require "validation.php";

/* Already logged in? Skip the forms */
if (isLoggedIn()) {
    redirect(isAdmin() ? "student.php" : "info.php");
}

 $errors = [];
 $activeTab = "login";   // which tab is open after a failed submit

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $action = $_POST["action"] ?? "";

    /* ================= REGISTER ================= */
    if ($action === "register") {

        $activeTab = "register";

        $username = trim($_POST["username"] ?? "");
        $email    = trim($_POST["email"] ?? "");
        $password = $_POST["password"] ?? "";
        $confirm  = $_POST["confirm"] ?? "";

        $validator = new Validator();

        $validator->checkEmpty($username, "username", "Username");
        $validator->checkEmpty($email, "email", "Email");
        $validator->checkEmpty($password, "password", "Password");
        $validator->checkEmpty($confirm, "confirm", "Password confirmation");

        if (!$validator->hasErrors()) {
            $validator->validUsername($username, "username");
            $validator->validEmail($email, "email");
            $validator->validPassword($password, "password");
            $validator->passwordMatch($password, $confirm, "confirm");
        }

        /* Duplicate username / email check */
        if (!$validator->hasErrors()) {

            $stmt = $conn->prepare("SELECT username, email FROM users WHERE username = ? OR email = ? LIMIT 1");
            $stmt->bind_param("ss", $username, $email);
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

        /* Create the account */
        if (!$validator->hasErrors()) {

            $hashed = password_hash($password, PASSWORD_DEFAULT);

            $stmt = $conn->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $username, $email, $hashed);

            if ($stmt->execute()) {
                $stmt->close();
                redirect("success.php?name=" . urlencode($username));
            }

            $stmt->close();
            $validator->errors["username"] = "Something went wrong. Please try again.";
        }

        $errors = $validator->errors;
    }

    /* ================= LOGIN ================= */
    if ($action === "login") {

        $activeTab = "login";

        $loginId  = trim($_POST["login_id"] ?? "");
        $password = $_POST["password"] ?? "";

        $validator = new Validator();
        $validator->checkEmpty($loginId, "login_id", "Username or email");
        $validator->checkEmpty($password, "login_password", "Password");

        if (!$validator->hasErrors()) {

            /* Oversized input can't be a real account — reject quietly */
            if (strlen($loginId) > 100 || strlen($password) > 100) {

                $validator->errors["login_password"] = "Wrong username/email or password.";

            } else {

                $stmt = $conn->prepare("SELECT id, username, password, role, failed_attempts, locked_until FROM users WHERE username = ? OR email = ? LIMIT 1");
                $stmt->bind_param("ss", $loginId, $loginId);
                $stmt->execute();
                $user = $stmt->get_result()->fetch_assoc();
                $stmt->close();

                /* Account currently locked? */
                if ($user && $user["locked_until"] !== null && strtotime($user["locked_until"]) > time()) {

                    $minutes = ceil((strtotime($user["locked_until"]) - time()) / 60);
                    $validator->errors["login_password"] = "Too many failed attempts. Try again in " . (int)$minutes . " minute(s).";

                } elseif ($user && password_verify($password, $user["password"])) {

                    /* SUCCESS — fresh session ID (anti session-fixation), reset attempts */
                    session_regenerate_id(true);

                    $stmt = $conn->prepare("UPDATE users SET failed_attempts = 0, locked_until = NULL WHERE id = ?");
                    $stmt->bind_param("i", $user["id"]);
                    $stmt->execute();
                    $stmt->close();

                    $_SESSION["user_id"]  = $user["id"];
                    $_SESSION["username"] = $user["username"];
                    $_SESSION["role"]     = $user["role"];

                    redirect(isAdmin() ? "student.php" : "info.php");

                } elseif ($user) {

                    /* Wrong password — count the attempt */
                    $attempts = (int)$user["failed_attempts"] + 1;

                    if ($attempts >= 5) {

                        /* Lock the account for 15 minutes */
                        $lockUntil = date("Y-m-d H:i:s", time() + 15 * 60);

                        $stmt = $conn->prepare("UPDATE users SET failed_attempts = ?, locked_until = ? WHERE id = ?");
                        $stmt->bind_param("isi", $attempts, $lockUntil, $user["id"]);
                        $stmt->execute();
                        $stmt->close();

                        $validator->errors["login_password"] = "Too many failed attempts. Account locked for 15 minutes.";

                    } else {

                        $stmt = $conn->prepare("UPDATE users SET failed_attempts = ? WHERE id = ?");
                        $stmt->bind_param("ii", $attempts, $user["id"]);
                        $stmt->execute();
                        $stmt->close();

                        $remaining = 5 - $attempts;
                        $validator->errors["login_password"] = "Wrong username/email or password. (" . $remaining . " attempt(s) left)";
                    }

                } else {

                    /* Unknown username — identical message, no hints for attackers */
                    $validator->errors["login_password"] = "Wrong username/email or password.";
                }
            }
        }

        $errors = $validator->errors;
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>PICKLE | Log In / Sign Up</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <link href="https://fonts.googleapis.com/css2?family=Alef:wght@400;700&family=Alike+Angular&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="auth.css">

</head>

<body>

<div class="auth-wrap">

    <!-- FLASH MESSAGE -->

    <?php $flash = getFlash(); ?>

    <?php if ($flash): ?>

        <div class="flash <?= e($flash["type"]) ?>">
            <?= e($flash["message"]) ?>
        </div>

    <?php endif; ?>


    <!-- "YOU MUST LOG IN" NOTICE -->

    <?php $notice = $_GET["notice"] ?? ""; ?>

    <?php if ($notice === "login-first"): ?>

        <p class="auth-alert error">
            Please log in first to book a court. 🏓
        </p>

    <?php elseif ($notice === "login-event"): ?>

        <p class="auth-alert error">
            Please log in first to join events. 🏓
        </p>

    <?php endif; ?>


    <div class="auth-card auth-center">

        <a href="../index.php" class="auth-logo">
            <img src="../images/logo.png" alt="PICKLE" class="auth-logo-img">
        </a>


        <!-- TABS -->

        <div class="auth-tabs">

            <button type="button"
                    class="auth-tab<?= $activeTab === "login" ? " active" : "" ?>"
                    data-tab="login">
                LOG IN
            </button>

            <button type="button"
                    class="auth-tab<?= $activeTab === "register" ? " active" : "" ?>"
                    data-tab="register">
                SIGN UP
            </button>

        </div>


        <!-- LOGIN PANEL -->

        <div class="tab-panel<?= $activeTab === "login" ? " active" : "" ?>"
             id="login-panel">

            <form method="POST" action="index.php">

                <input type="hidden" name="action" value="login">

                <div class="form-group">

                    <label>USERNAME OR EMAIL</label>

                    <input type="text"
                           name="login_id"
                           value="<?= old("login_id") ?>"
                           placeholder="e.g. alex or alex@gmail.com">

                    <?php if (isset($errors["login_id"])): ?>
                        <p class="field-error"><?= e($errors["login_id"]) ?></p>
                    <?php endif; ?>

                </div>


                <div class="form-group">

                    <label>PASSWORD</label>

                    <input type="password"
                           name="password"
                           placeholder="Your password">

                    <?php if (isset($errors["login_password"])): ?>
                        <p class="field-error"><?= e($errors["login_password"]) ?></p>
                    <?php endif; ?>

                </div>


                <label class="show-pass">
                    <input type="checkbox"> Show password
                </label>


                <button type="submit" class="auth-btn">
                    LOG IN
                </button>

            </form>


            <p class="auth-switch">
                New to PICKLE?
                <a href="#" class="switch-link" data-tab="register">Create an account</a>
            </p>

        </div>


        <!-- REGISTER PANEL -->

        <div class="tab-panel<?= $activeTab === "register" ? " active" : "" ?>"
             id="register-panel">

            <form method="POST" action="index.php">

                <input type="hidden" name="action" value="register">

                <div class="form-group">

                    <label>USERNAME</label>

                    <input type="text"
                           name="username"
                           value="<?= old("username") ?>"
                           placeholder="3-20 letters, numbers, underscores">

                    <?php if (isset($errors["username"])): ?>
                        <p class="field-error"><?= e($errors["username"]) ?></p>
                    <?php endif; ?>

                </div>


                <div class="form-group">

                    <label>EMAIL</label>

                    <input type="email"
                           name="email"
                           value="<?= old("email") ?>"
                           placeholder="you@example.com">

                    <?php if (isset($errors["email"])): ?>
                        <p class="field-error"><?= e($errors["email"]) ?></p>
                    <?php endif; ?>

                </div>


                <div class="form-group">

                    <label>PASSWORD</label>

                    <input type="password"
                           name="password"
                           placeholder="At least 8 characters">

                    <?php if (isset($errors["password"])): ?>
                        <p class="field-error"><?= e($errors["password"]) ?></p>
                    <?php endif; ?>

                </div>


                <div class="form-group">

                    <label>CONFIRM PASSWORD</label>

                    <input type="password"
                           name="confirm"
                           placeholder="Repeat your password">

                    <?php if (isset($errors["confirm"])): ?>
                        <p class="field-error"><?= e($errors["confirm"]) ?></p>
                    <?php endif; ?>

                </div>


                <label class="show-pass">
                    <input type="checkbox"> Show password
                </label>


                <button type="submit" class="auth-btn">
                    CREATE ACCOUNT
                </button>

            </form>


            <p class="auth-switch">
                Already have an account?
                <a href="#" class="switch-link" data-tab="login">Log in instead</a>
            </p>

        </div>


        <a class="auth-back" href="../index.php">
            ← BACK TO HOMEPAGE
        </a>

    </div>

</div>


<script>

    /* =====================================
       TAB SWITCHING
    ===================================== */

    const tabs = document.querySelectorAll(".auth-tab");
    const panels = document.querySelectorAll(".tab-panel");
    const switchLinks = document.querySelectorAll(".switch-link");

    function openTab(name) {

        tabs.forEach(function (tab) {
            tab.classList.toggle("active", tab.dataset.tab === name);
        });

        panels.forEach(function (panel) {
            panel.classList.toggle("active", panel.id === name + "-panel");
        });

    }

    tabs.forEach(function (tab) {
        tab.addEventListener("click", function () {
            openTab(tab.dataset.tab);
        });
    });

    switchLinks.forEach(function (link) {
        link.addEventListener("click", function (event) {
            event.preventDefault();
            openTab(link.dataset.tab);
        });
    });


    /* =====================================
       SHOW PASSWORD
    ===================================== */

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