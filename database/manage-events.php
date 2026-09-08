<?php
require "config.php";
require "function.php";
require "validation.php";

requireAdmin();

 $errors = [];
 $editEvent = null;

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $action = $_POST["action"] ?? "";

    /* ================= CREATE EVENT ================= */
    if ($action === "create") {

        $type        = $_POST["type"] ?? "";
        $title       = trim($_POST["title"] ?? "");
        $description = trim($_POST["description"] ?? "");
        $location    = trim($_POST["location"] ?? "");
        $date        = trim($_POST["date"] ?? "");
        $time        = trim($_POST["time"] ?? "");
        $maxPlayers  = (int)($_POST["max_players"] ?? 0);
        $prize       = trim($_POST["prize"] ?? "");

        $validator = new Validator();

        $validator->checkEmpty($title, "title", "Title");
        $validator->checkEmpty($location, "location", "Location");
        $validator->checkEmpty($date, "date", "Date");
        $validator->checkEmpty($time, "time", "Time");

        if (!in_array($type, ["open_play", "tournament"], true)) {
            $validator->errors["type"] = "Pick a valid event type.";
        }

        if ($maxPlayers < 2 || $maxPlayers > 64) {
            $validator->errors["max_players"] = "Max players must be between 2 and 64.";
        }

        if ($date !== "" && $date < date("Y-m-d")) {
            $validator->errors["date"] = "Date can't be in the past.";
        }

        /* Photo (optional) */
        $imagePath = null;

        if (!$validator->hasErrors() && !empty($_FILES["image"]["name"])) {

            $imagePath = handleImageUpload($_FILES["image"], "events");

            if ($imagePath === null) {
                $validator->errors["image"] = "Photo upload failed — use JPG, PNG, WEBP or GIF under 5MB.";
            }
        }

        if (!$validator->hasErrors()) {

            $stmt = $conn->prepare("
                INSERT INTO events (type, title, description, location, event_date, event_time, max_players, prize, image)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->bind_param("ssssssiss", $type, $title, $description, $location, $date, $time, $maxPlayers, $prize, $imagePath);
            $stmt->execute();
            $stmt->close();

            setFlash(ucfirst(str_replace("_", " ", $type)) . " created!");
            redirect("manage-events.php");
        }

        $errors = $validator->errors;
    }

    /* ================= UPDATE EVENT ================= */
    if ($action === "update") {

        $eventId     = (int)($_POST["event_id"] ?? 0);
        $type        = $_POST["type"] ?? "";
        $title       = trim($_POST["title"] ?? "");
        $description = trim($_POST["description"] ?? "");
        $location    = trim($_POST["location"] ?? "");
        $date        = trim($_POST["date"] ?? "");
        $time        = trim($_POST["time"] ?? "");
        $maxPlayers  = (int)($_POST["max_players"] ?? 0);
        $prize       = trim($_POST["prize"] ?? "");

        $validator = new Validator();

        $validator->checkEmpty($title, "title", "Title");
        $validator->checkEmpty($location, "location", "Location");
        $validator->checkEmpty($date, "date", "Date");
        $validator->checkEmpty($time, "time", "Time");

        if (!in_array($type, ["open_play", "tournament"], true)) {
            $validator->errors["type"] = "Pick a valid event type.";
        }

        if ($maxPlayers < 2 || $maxPlayers > 64) {
            $validator->errors["max_players"] = "Max players must be between 2 and 64.";
        }

        /* New photo (optional) */
        $imagePath = null;

        if (!$validator->hasErrors() && !empty($_FILES["image"]["name"])) {

            $imagePath = handleImageUpload($_FILES["image"], "events");

            if ($imagePath === null) {
                $validator->errors["image"] = "Photo upload failed — use JPG, PNG, WEBP or GIF under 5MB.";
            }
        }

        if (!$validator->hasErrors()) {

            /* If a new photo arrived, delete the old file */
            if ($imagePath !== null) {

                $stmt = $conn->prepare("SELECT image FROM events WHERE id = ? LIMIT 1");
                $stmt->bind_param("i", $eventId);
                $stmt->execute();
                $old = $stmt->get_result()->fetch_assoc();
                $stmt->close();

                if ($old && !empty($old["image"]) && file_exists(__DIR__ . "/../" . $old["image"])) {
                    unlink(__DIR__ . "/../" . $old["image"]);
                }
            }

            if ($imagePath !== null) {

                $stmt = $conn->prepare("
                    UPDATE events
                    SET type = ?, title = ?, description = ?, location = ?,
                        event_date = ?, event_time = ?, max_players = ?, prize = ?, image = ?
                    WHERE id = ?
                ");
                $stmt->bind_param("ssssssissi", $type, $title, $description, $location, $date, $time, $maxPlayers, $prize, $imagePath, $eventId);

            } else {

                $stmt = $conn->prepare("
                    UPDATE events
                    SET type = ?, title = ?, description = ?, location = ?,
                        event_date = ?, event_time = ?, max_players = ?, prize = ?
                    WHERE id = ?
                ");
                $stmt->bind_param("ssssssisi", $type, $title, $description, $location, $date, $time, $maxPlayers, $prize, $eventId);
            }

            $stmt->execute();
            $stmt->close();

            setFlash("Event updated!");
            redirect("manage-events.php");
        }

          $errors = $validator->errors;

 $editEvent = [
    "id"          => $eventId,
    "type"        => $type,
    "title"       => $title,
    "description" => $description,
    "location"    => $location,
    "event_date"  => $date,
    "event_time"  => $time,
    "max_players" => $maxPlayers,
    "prize"       => $prize,
];
    }

    /* ================= DELETE EVENT ================= */
    if ($action === "delete") {

        $eventId = (int)($_POST["event_id"] ?? 0);

        if ($eventId > 0) {

            $stmt = $conn->prepare("SELECT image FROM events WHERE id = ? LIMIT 1");
            $stmt->bind_param("i", $eventId);
            $stmt->execute();
            $event = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            /* Joins disappear automatically (ON DELETE CASCADE) */
            $stmt = $conn->prepare("DELETE FROM events WHERE id = ?");
            $stmt->bind_param("i", $eventId);
            $stmt->execute();
            $stmt->close();

            if ($event && !empty($event["image"]) && file_exists(__DIR__ . "/../" . $event["image"])) {
                unlink(__DIR__ . "/../" . $event["image"]);
            }

            setFlash("Event deleted.");
        }

        redirect("manage-events.php");
    }
}

/* ================= EDIT MODE (via ?edit=ID) ================= */

if (isset($_GET["edit"])) {

    $editId = (int)$_GET["edit"];

    if ($editId > 0) {

        $stmt = $conn->prepare("SELECT * FROM events WHERE id = ? LIMIT 1");
        $stmt->bind_param("i", $editId);
        $stmt->execute();
        $editEvent = $stmt->get_result()->fetch_assoc();
        $stmt->close();
    }
}

/* ================= FETCH ALL EVENTS ================= */

 $result = $conn->query("
    SELECT events.*, COUNT(event_joins.id) AS joined_count
    FROM events
    LEFT JOIN event_joins ON event_joins.event_id = events.id
    GROUP BY events.id
    ORDER BY events.event_date ASC, events.event_time ASC
");

 $events = $result->fetch_all(MYSQLI_ASSOC);

 $isEdit = $editEvent !== null;
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>PICKLE | Manage Events</title>

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
                    Manage Events
                </h2>

                <p class="auth-sub">
                    Create open plays and tournaments for the community.
                </p>

            </div>


            <div class="admin-top-actions">

                <a href="manage-courts.php" class="small-btn">
                    MANAGE COURTS
                </a>

                <a href="student.php" class="small-btn">
                    ADMIN PANEL
                </a>

                <a href="info.php" class="small-btn">
                    MY ACCOUNT
                </a>

                <a href="logout.php" class="small-btn danger">
                    LOG OUT
                </a>

            </div>

        </div>


        <!-- CREATE / EDIT EVENT FORM -->

        <h3 class="admin-section-title">
            <?= $isEdit ? "EDIT EVENT — " . e($editEvent["title"]) : "CREATE AN EVENT" ?>
        </h3>

        <form method="POST" action="manage-events.php"
              enctype="multipart/form-data" class="admin-form">

            <input type="hidden" name="action" value="<?= $isEdit ? "update" : "create" ?>">

            <?php if ($isEdit): ?>
                <input type="hidden" name="event_id" value="<?= (int)$editEvent["id"] ?>">
            <?php endif; ?>


            <div class="form-group">

                <label>EVENT TYPE</label>

                <select name="type">

                    <option value="open_play" <?= $isEdit && $editEvent["type"] === "open_play" ? "selected" : "" ?>>
                        Open Play
                    </option>

                    <option value="tournament" <?= $isEdit && $editEvent["type"] === "tournament" ? "selected" : "" ?>>
                        Tournament
                    </option>

                </select>

                <?php if (isset($errors["type"])): ?>
                    <p class="field-error"><?= e($errors["type"]) ?></p>
                <?php endif; ?>

            </div>


            <div class="form-group">

                <label>TITLE</label>

                <input type="text"
                       name="title"
                       value="<?= $isEdit ? e($editEvent["title"]) : old("title") ?>"
                       placeholder="Saturday Morning Smash">

                <?php if (isset($errors["title"])): ?>
                    <p class="field-error"><?= e($errors["title"]) ?></p>
                <?php endif; ?>

            </div>


            <div class="form-group">

                <label>LOCATION</label>

                <input type="text"
                       name="location"
                       value="<?= $isEdit ? e($editEvent["location"]) : old("location") ?>"
                       placeholder="Dumaguete City">

                <?php if (isset($errors["location"])): ?>
                    <p class="field-error"><?= e($errors["location"]) ?></p>
                <?php endif; ?>

            </div>


            <div class="form-group">

                <label>DATE</label>

                <input type="date"
                       name="date"
                       value="<?= $isEdit ? e($editEvent["event_date"]) : old("date") ?>">

                <?php if (isset($errors["date"])): ?>
                    <p class="field-error"><?= e($errors["date"]) ?></p>
                <?php endif; ?>

            </div>


            <div class="form-group">

                <label>TIME</label>

                <input type="time"
                       name="time"
                       value="<?= $isEdit ? e(substr($editEvent["event_time"], 0, 5)) : old("time") ?>">

                <?php if (isset($errors["time"])): ?>
                    <p class="field-error"><?= e($errors["time"]) ?></p>
                <?php endif; ?>

            </div>


            <div class="form-group">

                <label>MAX PLAYERS</label>

                <input type="number"
                       name="max_players"
                       value="<?= $isEdit ? (int)$editEvent["max_players"] : (old("max_players") ?: "8") ?>"
                       min="2"
                       max="64">

                <?php if (isset($errors["max_players"])): ?>
                    <p class="field-error"><?= e($errors["max_players"]) ?></p>
                <?php endif; ?>

            </div>


            <div class="form-group">

                <label>PRIZE (TOURNAMENTS — OPTIONAL)</label>

                <input type="text"
                       name="prize"
                       value="<?= $isEdit ? e($editEvent["prize"]) : old("prize") ?>"
                       placeholder="₱5,000 + bragging rights">

                <?php if (isset($errors["prize"])): ?>
                    <p class="field-error"><?= e($errors["prize"]) ?></p>
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


            <div class="form-group span-2">

                <label>DESCRIPTION (OPTIONAL)</label>

                <input type="text"
                       name="description"
                       value="<?= $isEdit ? e($editEvent["description"]) : old("description") ?>"
                       placeholder="All levels welcome — paddles provided!">

            </div>


            <div class="form-group">

                <button type="submit" class="auth-btn">
                    <?= $isEdit ? "SAVE CHANGES" : "CREATE EVENT" ?>
                </button>

            </div>

        </form>

        <?php if ($isEdit): ?>

            <p class="edit-note">
                Editing <strong><?= e($editEvent["title"]) ?></strong> —
                <a href="manage-events.php">cancel</a>
            </p>

        <?php endif; ?>


        <!-- ALL EVENTS TABLE -->

        <h3 class="admin-section-title">
            ALL EVENTS
        </h3>

        <?php if (empty($events)): ?>

            <p class="empty-note">
                No events yet — create your first one above!
            </p>

        <?php else: ?>

            <div class="table-wrap">

                <table class="admin-table">

                    <thead>

                        <tr>
                            <th>ID</th>
                            <th>PHOTO</th>
                            <th>TYPE</th>
                            <th>TITLE</th>
                            <th>DATE</th>
                            <th>TIME</th>
                            <th>LOCATION</th>
                            <th>JOINED</th>
                            <th>PRIZE</th>
                            <th>ACTIONS</th>
                        </tr>

                    </thead>

                    <tbody>

                        <?php foreach ($events as $ev): ?>

                            <?php $isPast = $ev["event_date"] < date("Y-m-d"); ?>

                            <tr>

                                <td><?= (int)$ev["id"] ?></td>

                                <td>

                                    <?php if (!empty($ev["image"])): ?>

                                        <img src="../<?= e($ev["image"]) ?>"
                                             alt="<?= e($ev["title"]) ?>"
                                             class="admin-thumb">

                                    <?php else: ?>

                                        <div class="admin-thumb-fallback">🏓</div>

                                    <?php endif; ?>

                                </td>

                                <td>
                                    <span class="badge <?= e($ev["type"]) ?><?= $isPast ? " past" : "" ?>">
                                        <?= strtoupper(str_replace("_", " ", e($ev["type"]))) ?>
                                    </span>
                                </td>

                                <td><?= e($ev["title"]) ?></td>

                                <td><?= e(date("M j, Y", strtotime($ev["event_date"]))) ?></td>

                                <td><?= e(date("g:i A", strtotime($ev["event_time"]))) ?></td>

                                <td><?= e($ev["location"]) ?></td>

                                <td>
                                    <?= (int)$ev["joined_count"] ?> / <?= (int)$ev["max_players"] ?>
                                </td>

                                <td>
                                    <?= $ev["prize"] ? e($ev["prize"]) : "—" ?>
                                </td>

                                <td>

                                    <a href="manage-events.php?edit=<?= (int)$ev["id"] ?>" class="small-btn">
                                        EDIT
                                    </a>

                                    <form method="POST" action="manage-events.php" class="row-form"
                                          onsubmit="return confirm('Delete this event? All joins will be removed too.')">

                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="event_id" value="<?= (int)$ev["id"] ?>">

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