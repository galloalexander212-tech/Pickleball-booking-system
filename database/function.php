<?php
/* =====================================
   HELPER FUNCTIONS
===================================== */

// Start the session (only if not started yet)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/* =====================================
   SESSION SECURITY
===================================== */

/* Auto-logout after 30 minutes of inactivity */
define("SESSION_TIMEOUT", 30 * 60);


function checkSessionTimeout() {

    /* Only applies to logged-in users */
    if (isset($_SESSION["user_id"]) && isset($_SESSION["last_activity"])) {

        if (time() - $_SESSION["last_activity"] > SESSION_TIMEOUT) {

            session_unset();
            session_destroy();

            session_start();

            setFlash("You were logged out after 30 minutes of inactivity. Please log in again.", "error");

            redirect("index.php");
        }
    }

    /* Every page load counts as activity */
    $_SESSION["last_activity"] = time();
}


/* Send the user to another page */
function redirect($url) {
    header("Location: " . $url);
    exit();
}

/* Is someone logged in? */
function isLoggedIn() {
    return isset($_SESSION["user_id"]);
}

/* Is the logged-in user an admin? */
function isAdmin() {
    return isset($_SESSION["role"]) && $_SESSION["role"] === "admin";
}

/* Block a page unless logged in */
function requireLogin() {
    if (!isLoggedIn()) {
        redirect("index.php");
    }
}

/* Block a page unless admin */
function requireAdmin() {
    if (!isAdmin()) {
        redirect("info.php");
    }
}

/* Make output safe to print (stops XSS attacks) */
function e($value) {
    return htmlspecialchars($value ?? "", ENT_QUOTES, "UTF-8");
}

/* Keep old form values after a failed submit */
function old($field) {
    return isset($_POST[$field]) ? e($_POST[$field]) : "";
}

/* =====================================
   FLASH MESSAGES (one-time notices)
===================================== */

function setFlash($message, $type = "success") {

    $_SESSION["flash"] = [
        "message" => $message,
        "type"    => $type
    ];
}


function getFlash() {

    if (isset($_SESSION["flash"])) {

        $flash = $_SESSION["flash"];
        unset($_SESSION["flash"]);

        return $flash;
    }

    return null;
}


/* Run the timeout check on every page that loads this file */
checkSessionTimeout();

/* =====================================
   IMAGE UPLOAD HELPER
   Returns the stored path, or null on
   failure / no file chosen
===================================== */

function handleImageUpload($file, $folder) {

    /* No file chosen — not an error, just skip */
    if (!isset($file) || $file["error"] === UPLOAD_ERR_NO_FILE) {
        return null;
    }

    if ($file["error"] !== UPLOAD_ERR_OK) {
        return null;
    }

    /* 5 MB limit */
    if ($file["size"] > 5 * 1024 * 1024) {
        return null;
    }

    /* Real image check — extension can lie, MIME can't */
    $allowed = [
        "image/jpeg" => "jpg",
        "image/png"  => "png",
        "image/webp" => "webp",
        "image/gif"  => "gif"
    ];

    $info = getimagesize($file["tmp_name"]);

    if ($info === false || !isset($allowed[$info["mime"]])) {
        return null;
    }

    /* Create the folder if it doesn't exist */
    $dir = __DIR__ . "/../uploads/" . $folder;

    if (!is_dir($dir)) {
        mkdir($dir, 0777, true);
    }

    /* Unique safe filename */
    $name = $folder . "_" . uniqid() . "." . $allowed[$info["mime"]];

    if (move_uploaded_file($file["tmp_name"], $dir . "/" . $name)) {
        return "uploads/" . $folder . "/" . $name;
    }

    return null;
}
?>