<?php
require "config.php";
require "function.php";

/* Must be logged in */
if (!isLoggedIn()) {
    setFlash("Please log in to join events.", "error");
    redirect("index.php");
}

 $action  = $_POST["action"] ?? "";
 $eventId = (int)($_POST["event_id"] ?? 0);

if ($eventId > 0) {

    /* Fetch the event — its type comes from the DB, never trusted from the form */
    $stmt = $conn->prepare("SELECT id, type, title, event_date FROM events WHERE id = ? LIMIT 1");
    $stmt->bind_param("i", $eventId);
    $stmt->execute();
    $event = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($event) {

        $anchor = $event["type"] === "open_play" ? "openPlayList" : "tournamentList";

        /* ================= JOIN ================= */
        if ($action === "join") {

            /* Event must still be upcoming */
            if ($event["event_date"] < date("Y-m-d")) {
                setFlash("That event has already happened.", "error");
                redirect("../index.php#" . $anchor);
            }

            /* Already joined? */
            $stmt = $conn->prepare("SELECT id FROM event_joins WHERE user_id = ? AND event_id = ? LIMIT 1");
            $stmt->bind_param("ii", $_SESSION["user_id"], $eventId);
            $stmt->execute();
            $already = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            if ($already) {
                setFlash("You already joined " . $event["title"] . ".", "error");
                redirect("../index.php#" . $anchor);
            }

            /* Full? */
            $stmt = $conn->prepare("
                SELECT (SELECT max_players FROM events WHERE id = ?) AS max_players,
                       (SELECT COUNT(*) FROM event_joins WHERE event_id = ?) AS joined
            ");
            $stmt->bind_param("ii", $eventId, $eventId);
            $stmt->execute();
            $row = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            if ((int)$row["joined"] >= (int)$row["max_players"]) {
                setFlash("Sorry, " . $event["title"] . " is full!", "error");
                redirect("../index.php#" . $anchor);
            }

            /* Save the join */
            $stmt = $conn->prepare("INSERT INTO event_joins (user_id, event_id) VALUES (?, ?)");
            $stmt->bind_param("ii", $_SESSION["user_id"], $eventId);
            $stmt->execute();
            $stmt->close();

            setFlash("You're in! See you at " . $event["title"] . ".");
            redirect("info.php");
        }

        /* ================= LEAVE ================= */
        if ($action === "leave") {

            $stmt = $conn->prepare("DELETE FROM event_joins WHERE user_id = ? AND event_id = ?");
            $stmt->bind_param("ii", $_SESSION["user_id"], $eventId);
            $stmt->execute();
            $stmt->close();

            setFlash("You left " . $event["title"] . ".");
            redirect("info.php");
        }
    }
}

/* Weird direct visit → homepage */
redirect("../index.php");