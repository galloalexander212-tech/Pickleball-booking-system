<?php
require "config.php";
require "function.php";

/* Must be logged in to see this page */
requireLogin();

/* Fetch fresh user info from the database */
 $stmt = $conn->prepare("SELECT username, email, role, created_at FROM users WHERE id = ? LIMIT 1");
 $stmt->bind_param("i", $_SESSION["user_id"]);
 $stmt->execute();
 $user = $stmt->get_result()->fetch_assoc();
 $stmt->close();

/* Account deleted while logged in? Kick them out */
if (!$user) {
    session_destroy();
    redirect("index.php");
}

/* Fetch this user's bookings (newest first) */
 $stmt = $conn->prepare("
    SELECT id, court, booking_date, booking_time, status
    FROM bookings
    WHERE user_id = ?
    ORDER BY booking_date DESC, booking_time DESC
");
 $stmt->bind_param("i", $_SESSION["user_id"]);
 $stmt->execute();
 $bookings = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
 $stmt->close();

/* Fetch the events this user joined (upcoming first, past last) */
 $stmt = $conn->prepare("
    SELECT events.id, events.type, events.title, events.location,
           events.event_date, events.event_time, events.prize
    FROM event_joins
    JOIN events ON events.id = event_joins.event_id
    WHERE event_joins.user_id = ?
    ORDER BY (events.event_date < CURDATE()) ASC, events.event_date ASC, events.event_time ASC
");
 $stmt->bind_param("i", $_SESSION["user_id"]);
 $stmt->execute();
 $myEvents = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
 $stmt->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>PICKLE | My Account</title>

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
            My Account
        </h2>

        <p class="auth-sub">
            Your PICKLE profile details.
        </p>


        <div class="profile-row">
            <span>USERNAME</span>
            <strong><?= e($user["username"]) ?></strong>
        </div>

        <div class="profile-row">
            <span>EMAIL</span>
            <strong><?= e($user["email"]) ?></strong>
        </div>

        <div class="profile-row">
            <span>ROLE</span>
            <span class="badge <?= e($user["role"]) ?>">
                <?= strtoupper(e($user["role"])) ?>
            </span>
        </div>

        <div class="profile-row">
            <span>MEMBER SINCE</span>
            <strong><?= e(date("M j, Y", strtotime($user["created_at"]))) ?></strong>
        </div>

        <div class="profile-row">
            <span>BOOKINGS</span>
            <strong><?= count($bookings) ?></strong>
        </div>

        <div class="profile-row">
            <span>EVENTS JOINED</span>
            <strong><?= count($myEvents) ?></strong>
        </div>


        <!-- ================= MY BOOKINGS ================= -->

        <h3 class="section-sub">
            MY BOOKINGS
        </h3>

        <?php if (empty($bookings)): ?>

            <p class="empty-note">
                No bookings yet — hit BOOK COURT on the homepage! 🏓
            </p>

        <?php else: ?>

            <?php foreach ($bookings as $b): ?>

                <div class="booking-item">

                    <div>
                        <strong><?= e($b["court"]) ?></strong>
                        <span class="meta">
                            <?= e(date("D, M j, Y", strtotime($b["booking_date"]))) ?>
                            •
                            <?= e(date("g:i A", strtotime($b["booking_time"]))) ?>
                        </span>
                    </div>

                    <div class="booking-side">

                        <span class="status <?= e($b["status"]) ?>">
                            <?= strtoupper(e($b["status"])) ?>
                        </span>

                        <?php if ($b["status"] !== "cancelled"): ?>

                            <form method="POST" action="book.php"
                                  onsubmit="return confirm('Cancel this booking?')">

                                <input type="hidden" name="action" value="cancel">
                                <input type="hidden" name="booking_id" value="<?= (int)$b["id"] ?>">

                                <button type="submit" class="small-btn danger">
                                    CANCEL
                                </button>

                            </form>

                        <?php endif; ?>

                    </div>

                </div>

            <?php endforeach; ?>

        <?php endif; ?>


        <!-- ================= MY EVENTS ================= -->

        <h3 class="section-sub">
            MY EVENTS
        </h3>

        <?php if (empty($myEvents)): ?>

            <p class="empty-note">
                You haven't joined any open plays or tournaments yet! 🏆
            </p>

        <?php else: ?>

            <?php foreach ($myEvents as $ev): ?>

                <div class="booking-item">

                    <div>
                        <strong><?= e($ev["title"]) ?></strong>
                        <span class="meta">
                            <?= strtoupper(str_replace("_", " ", $ev["type"])) ?>
                            • <?= e(date("D, M j, Y", strtotime($ev["event_date"]))) ?>
                            • <?= e(date("g:i A", strtotime($ev["event_time"]))) ?>
                            • <?= e($ev["location"]) ?>
                            <?php if ($ev["prize"]): ?>
                                • 🏆 <?= e($ev["prize"]) ?>
                            <?php endif; ?>
                        </span>
                    </div>

                    <div class="booking-side">

                        <?php if ($ev["event_date"] < date("Y-m-d")): ?>

                            <span class="badge past">
                                EVENT OVER
                            </span>

                        <?php else: ?>

                            <form method="POST" action="events.php"
                                  onsubmit="return confirm('Leave this event?')">

                                <input type="hidden" name="action" value="leave">
                                <input type="hidden" name="event_id" value="<?= (int)$ev["id"] ?>">

                                <button type="submit" class="small-btn danger">
                                    LEAVE
                                </button>

                            </form>

                        <?php endif; ?>

                    </div>

                </div>

            <?php endforeach; ?>

        <?php endif; ?>


        <div class="auth-actions">

            <?php if (isAdmin()): ?>

                <a href="student.php" class="auth-btn">
                    OPEN ADMIN PANEL
                </a>

            <?php endif; ?>

            <a href="edit-profile.php" class="auth-btn outline">
                EDIT PROFILE
            </a>

            <a href="logout.php" class="auth-btn danger">
                LOG OUT
            </a>

        </div>


        <a class="auth-back" href="../index.php">
            ← BACK TO HOMEPAGE
        </a>

    </div>

</div>

</body>
</html>