<?php
date_default_timezone_set("Asia/Manila");

 $host   = "localhost";
 $dbUser = "root";
 $dbPass = "";
 $dbName = "pickleball";

/* @ suppresses the default PHP error so nothing leaks to the page */
 $conn = @new mysqli($host, $dbUser, $dbPass, $dbName);

if ($conn->connect_error) {
    /* Log the real reason privately, show the public a generic message */
    error_log("DB connection failed: " . $conn->connect_error);
    die("Something went wrong. Please try again later.");
}
?>