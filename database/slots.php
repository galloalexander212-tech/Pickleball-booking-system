<?php
require "config.php";
require "function.php";

/* =====================================
   RETURNS TAKEN SLOTS AS JSON
   Called by the homepage slot picker
===================================== */

header("Content-Type: application/json");

 $court = trim($_GET["court"] ?? "");
 $date  = trim($_GET["date"] ?? "");

 $taken = [];

if ($court !== "" && $date !== "") {

    $stmt = $conn->prepare("
        SELECT DATE_FORMAT(booking_time, '%H:%i') AS t
        FROM bookings
        WHERE court = ? AND booking_date = ? AND status != 'cancelled'
    ");
    $stmt->bind_param("ss", $court, $date);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $taken[] = $row["t"];
    }

    $stmt->close();
}

echo json_encode(["taken" => $taken]);