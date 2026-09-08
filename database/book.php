<?php
require "config.php";
require "function.php";

/* =====================================
   BOOK A COURT (from homepage modal)
===================================== */

if ($_SERVER["REQUEST_METHOD"] === "POST" && ($_POST["action"] ?? "") === "book") {

    /* Must be logged in */
    if (!isLoggedIn()) {
        setFlash("Please log in to book a court.", "error");
        redirect("index.php");
    }

    $court = trim($_POST["court"] ?? "");
    $date  = trim($_POST["date"] ?? "");
    $time  = trim($_POST["time"] ?? "");

    /* Validation */
    $errors = [];

    if ($court === "") { $errors[] = "Court is required."; }
    if ($date  === "") { $errors[] = "Date is required."; }
    if ($time  === "") { $errors[] = "Time slot is required."; }

    if ($date !== "" && $date < date("Y-m-d")) {
        $errors[] = "You can't book a court in the past.";
    }

    if ($court !== "" && strlen($court) > 100) {
        $errors[] = "Invalid court.";
    }

    /* Must be one of our real slots (6:00 AM – 9:00 PM) */
    $validSlots = [];

    for ($h = 6; $h <= 21; $h++) {
        $validSlots[] = sprintf("%02d:00", $h);
    }

    if ($time !== "" && !in_array($time, $validSlots, true)) {
        $errors[] = "Invalid time slot. Slots run from 6:00 AM to 9:00 PM.";
    }

    /* Can't book a slot that already passed today */
    if ($date === date("Y-m-d") && $time !== "" && in_array($time, $validSlots, true)) {

        $slotMinutes = (int)substr($time, 0, 2) * 60;

        $nowMinutes  = (int)date("H") * 60 + (int)date("i");

        if ($slotMinutes <= $nowMinutes) {
            $errors[] = "That time slot has already passed today.";
        }
    }

    if (empty($errors)) {

        /* Is this slot already taken? (cancelled slots are free again) */
        $stmt = $conn->prepare("
            SELECT id FROM bookings
            WHERE court = ? AND booking_date = ? AND booking_time = ?
              AND status != 'cancelled'
            LIMIT 1
        ");
        $stmt->bind_param("sss", $court, $date, $time);
        $stmt->execute();
        $taken = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($taken) {
            setFlash("Sorry, " . $court . " is already booked on " . $date . " at " . $time . ". Pick another slot.", "error");
            redirect("../index.php#booking");
        }

        /* Save the booking */
        $stmt = $conn->prepare("
            INSERT INTO bookings (user_id, court, booking_date, booking_time)
            VALUES (?, ?, ?, ?)
        ");
        $stmt->bind_param("isss", $_SESSION["user_id"], $court, $date, $time);
        $stmt->execute();
        $stmt->close();

        setFlash("Court booked! " . $court . " on " . $date . " at " . $time . ". Waiting for confirmation.");
        redirect("info.php");
    }

    setFlash(implode(" ", $errors), "error");
    redirect("../index.php#booking");
}


/* =====================================
   CANCEL MY OWN BOOKING (from info.php)
===================================== */

if ($_SERVER["REQUEST_METHOD"] === "POST" && ($_POST["action"] ?? "") === "cancel") {

    if (!isLoggedIn()) {
        redirect("index.php");
    }

    $bookingId = (int)($_POST["booking_id"] ?? 0);

    if ($bookingId > 0) {

        /* user_id = ? guarantees you can only cancel YOUR booking */
        $stmt = $conn->prepare("
            UPDATE bookings
            SET status = 'cancelled'
            WHERE id = ? AND user_id = ? AND status != 'cancelled'
        ");
        $stmt->bind_param("ii", $bookingId, $_SESSION["user_id"]);
        $stmt->execute();
        $stmt->close();

        setFlash("Booking cancelled.");
    }

    redirect("info.php");
}


/* Weird direct visit → back to homepage */
redirect("../index.php");