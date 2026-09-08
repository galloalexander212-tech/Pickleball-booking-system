<?php
require "config.php";
require "function.php";
require "validation.php";

requireAdmin();

 $errors = [];
 $editCourt = null;

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $action = $_POST["action"] ?? "";

    /* ================= CREATE COURT ================= */
    if ($action === "create") {

        $name     = trim($_POST["name"] ?? "");
        $location = trim($_POST["location"] ?? "");
        $rating   = (float)($_POST["rating"] ?? 0);

        $validator = new Validator();

        $validator->checkEmpty($name, "name", "Court name");
        $validator->checkEmpty($location, "location", "Location");

        if ($rating < 0 || $rating > 5) {
            $validator->errors["rating"] = "Rating must be between 0 and 5.";
        }

        /* Duplicate name check */
        if (!$validator->hasErrors()) {

            $stmt = $conn->prepare("SELECT id FROM courts WHERE name = ? LIMIT 1");
            $stmt->bind_param("s", $name);
            $stmt->execute();
            $dup = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            if ($dup) {
                $validator->errors["name"] = "A court with that name already exists.";
            }
        }

        /* Photo (optional) */
        $imagePath = null;

        if (!$validator->hasErrors() && !empty($_FILES["image"]["name"])) {

            $imagePath = handleImageUpload($_FILES["image"], "courts");

            if ($imagePath === null) {
                $validator->errors["image"] = "Photo upload failed — use JPG, PNG, WEBP or GIF under 5MB.";
            }
        }

        if (!$validator->hasErrors()) {

            $stmt = $conn->prepare("
                INSERT INTO courts (name, location, rating, image)
                VALUES (?, ?, ?, ?)
            ");
            $stmt->bind_param("ssds", $name, $location, $rating, $imagePath);
            $stmt->execute();
            $stmt->close();

            setFlash("Court added!");
            redirect("manage-courts.php");
        }

        $errors = $validator->errors;
    }

    /* ================= UPDATE COURT ================= */
    if ($action === "update") {

        $courtId  = (int)($_POST["court_id"] ?? 0);
        $name     = trim($_POST["name"] ?? "");
        $location = trim($_POST["location"] ?? "");
        $rating   = (float)($_POST["rating"] ?? 0);

        $validator = new Validator();

        $validator->checkEmpty($name, "name", "Court name");
        $validator->checkEmpty($location, "location", "Location");

        if ($rating < 0 || $rating > 5) {
            $validator->errors["rating"] = "Rating must be between 0 and 5.";
        }

        /* Duplicate name check — excluding THIS court */
        if (!$validator->hasErrors()) {

            $stmt = $conn->prepare("SELECT id FROM courts WHERE name = ? AND id != ? LIMIT 1");
            $stmt->bind_param("si", $name, $courtId);
            $stmt->execute();
            $dup = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            if ($dup) {
                $validator->errors["name"] = "A court with that name already exists.";
            }
        }

        /* New photo (optional) */
        $imagePath = null;

        if (!$validator->hasErrors() && !empty($_FILES["image"]["name"])) {

            $imagePath = handleImageUpload($_FILES["image"], "courts");

            if ($imagePath === null) {
                $validator->errors["image"] = "Photo upload failed — use JPG, PNG, WEBP or GIF under 5MB.";
            }
        }

        if (!$validator->hasErrors()) {

            /* If a new photo arrived, delete the old file */
            if ($imagePath !== null) {

                $stmt = $conn->prepare("SELECT image FROM courts WHERE id = ? LIMIT 1");
                $stmt->bind_param("i", $courtId);
                $stmt->execute();
                $old = $stmt->get_result()->fetch_assoc();
                $stmt->close();

                if ($old && !empty($old["image"]) && file_exists(__DIR__ . "/../" . $old["image"])) {
                    unlink(__DIR__ . "/../" . $old["image"]);
                }
            }

            /* Update — photo only if a new one was uploaded */
            if ($imagePath !== null) {

                $stmt = $conn->prepare("
                    UPDATE courts SET name = ?, location = ?, rating = ?, image = ? WHERE id = ?
                ");
                $stmt->bind_param("ssdsi", $name, $location, $rating, $imagePath, $courtId);

            } else {

                $stmt = $conn->prepare("
                    UPDATE courts SET name = ?, location = ?, rating = ? WHERE id = ?
                ");
                $stmt->bind_param("ssdi", $name, $location, $rating, $courtId);
            }

            $stmt->execute();
            $stmt->close();

            setFlash("Court updated!");
            redirect("manage-courts.php");
        }

        $errors = $validator->errors;
        $editCourt = ["id" => $courtId];   /* stay in edit mode after a failed save */
    }

    /* ================= DELETE COURT ================= */
    if ($action === "delete") {

        $courtId = (int)($_POST["court_id"] ?? 0);

        if ($courtId > 0) {

            $stmt = $conn->prepare("SELECT image FROM courts WHERE id = ? LIMIT 1");
            $stmt->bind_param("i", $courtId);
            $stmt->execute();
            $court = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            $stmt = $conn->prepare("DELETE FROM courts WHERE id = ?");
            $stmt->bind_param("i", $courtId);
            $stmt->execute();
            $stmt->close();

            if ($court && !empty($court["image"]) && file_exists(__DIR__ . "/../" . $court["image"])) {
                unlink(__DIR__ . "/../" . $court["image"]);
            }

            setFlash("Court deleted.");
        }

        redirect("manage-courts.php");
    }
}

/* ================= EDIT MODE (via ?edit=ID) ================= */

if (isset($_GET["edit"])) {

    $editId = (int)$_GET["edit"];

    if ($editId > 0) {

        $stmt = $conn->prepare("SELECT * FROM courts WHERE id = ? LIMIT 1");
        $stmt->bind_param("i", $editId);
        $stmt->execute();
        $editCourt = $stmt->get_result()->fetch_assoc();
        $stmt->close();
    }
}

/* ================= FETCH COURTS ================= */

 $result = $conn->query("SELECT * FROM courts ORDER BY created_at DESC");
 $courts = $result->fetch_all(MYSQLI_ASSOC);

 $isEdit = $editCourt !== null;
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>PICKLE | Manage Courts</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <link href="https://fonts.googleapis.com/css2?family=Alef:wght@400;700&family=Alike+Angular&display=swap"
          rel="stylesheet">

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


    <div class="auth-card wide">

        <div class="admin-head">

            <div>

                <a href="../index.php" class="auth-logo">
                    <img src="../images/logo.png" alt="PICKLE" class="auth-logo-img">
                </a>

                <h2 class="auth-title">
                    Manage Courts
                </h2>

                <p class="auth-sub">
                    Add courts players can book — with photos.
                </p>

            </div>


            <div class="admin-top-actions">

                <a href="student.php" class="small-btn">
                    ADMIN PANEL
                </a>

                <a href="manage-events.php" class="small-btn">
                    MANAGE EVENTS
                </a>

                <a href="info.php" class="small-btn">
                    MY ACCOUNT
                </a>

                <a href="logout.php" class="small-btn danger">
                    LOG OUT
                </a>

            </div>

        </div>


        <!-- CREATE / EDIT COURT FORM -->

        <h3 class="admin-section-title">
            <?= $isEdit ? "EDIT COURT — " . e($editCourt["name"]) : "ADD A COURT" ?>
        </h3>

        <form method="POST" action="manage-courts.php"
              enctype="multipart/form-data" class="admin-form">

            <input type="hidden" name="action" value="<?= $isEdit ? "update" : "create" ?>">

            <?php if ($isEdit): ?>
                <input type="hidden" name="court_id" value="<?= (int)$editCourt["id"] ?>">
            <?php endif; ?>


            <div class="form-group">

                <label>COURT NAME</label>

                <input type="text"
                       name="name"
                       value="<?= $isEdit ? e($editCourt["name"]) : old("name") ?>"
                       placeholder="e.g. Skyline Pickleball Club">

                <?php if (isset($errors["name"])): ?>
                    <p class="field-error"><?= e($errors["name"]) ?></p>
                <?php endif; ?>

            </div>


            <div class="form-group">

                <label>LOCATION</label>

                <input type="text"
                       name="location"
                       value="<?= $isEdit ? e($editCourt["location"]) : old("location") ?>"
                       placeholder="e.g. Dumaguete City">

                <?php if (isset($errors["location"])): ?>
                    <p class="field-error"><?= e($errors["location"]) ?></p>
                <?php endif; ?>

            </div>


            <div class="form-group">

                <label>RATING (0–5)</label>

                <input type="number"
                       name="rating"
                       step="0.1"
                       min="0"
                       max="5"
                       value="<?= $isEdit ? e($editCourt["rating"]) : (old("rating") ?: "4.5") ?>">

                <?php if (isset($errors["rating"])): ?>
                    <p class="field-error"><?= e($errors["rating"]) ?></p>
                <?php endif; ?>

            </div>


            <div class="form-group">

                <label>PHOTO <?= $isEdit ? "(LEAVE EMPTY TO KEEP CURRENT)" : "(OPTIONAL)" ?></label>

                <input type="file"
                       name="image"
                       accept="image/jpeg,image/png,image/webp,image/gif">

                <?php if (isset($errors["image"])): ?>
                    <p class="field-error"><?= e($errors["image"]) ?></p>
                <?php endif; ?>

            </div>


            <div class="form-group">

                <button type="submit" class="auth-btn">
                    <?= $isEdit ? "SAVE CHANGES" : "ADD COURT" ?>
                </button>

            </div>

        </form>

        <?php if ($isEdit): ?>

            <p class="edit-note">
                Editing <strong><?= e($editCourt["name"]) ?></strong> —
                <a href="manage-courts.php">cancel</a>
            </p>

        <?php endif; ?>


        <!-- COURTS TABLE -->

        <h3 class="admin-section-title">
            ALL COURTS
        </h3>

        <?php if (empty($courts)): ?>

            <p class="empty-note">
                No courts yet — add your first one above!
            </p>

        <?php else: ?>

            <div class="table-wrap">

                <table class="admin-table">

                    <thead>

                        <tr>
                            <th>ID</th>
                            <th>PHOTO</th>
                            <th>NAME</th>
                            <th>LOCATION</th>
                            <th>RATING</th>
                            <th>ACTIONS</th>
                        </tr>

                    </thead>

                    <tbody>

                        <?php foreach ($courts as $c): ?>

                            <tr>

                                <td><?= (int)$c["id"] ?></td>

                                <td>

                                    <?php if (!empty($c["image"])): ?>

                                        <img src="../<?= e($c["image"]) ?>"
                                             alt="<?= e($c["name"]) ?>"
                                             class="admin-thumb">

                                    <?php else: ?>

                                        <div class="admin-thumb-fallback">🏓</div>

                                    <?php endif; ?>

                                </td>

                                <td><?= e($c["name"]) ?></td>

                                <td><?= e($c["location"]) ?></td>

                                <td>★ <?= e(number_format((float)$c["rating"], 1)) ?></td>

                                <td>

                                    <a href="manage-courts.php?edit=<?= (int)$c["id"] ?>" class="small-btn">
                                        EDIT
                                    </a>

                                    <form method="POST" action="manage-courts.php" class="row-form"
                                          onsubmit="return confirm('Delete this court?')">

                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="court_id" value="<?= (int)$c["id"] ?>">

                                        <button type="submit" class="small-btn danger">
                                            DELETE
                                        </button>

                                    </form>

                                </td>

                            </tr>

                        <?php endforeach; ?>

                    </tbody>

                </table>

            </div>

        <?php endif; ?>


        <a class="auth-back" href="../index.php">
            ← BACK TO HOMEPAGE
        </a>

    </div>

</div>

</body>
</html>