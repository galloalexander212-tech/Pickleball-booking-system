<?php
require "config.php";
require "function.php";

/* Admins only */
requireAdmin();

/* ================= HANDLE ACTIONS ================= */
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $action = $_POST["action"] ?? "";
    $userId = (int)($_POST["user_id"] ?? 0);

    /* Delete a normal user (you can't delete admins or yourself) */
    if ($action === "delete" && $userId > 0 && $userId !== (int)$_SESSION["user_id"]) {

        $stmt = $conn->prepare("DELETE FROM users WHERE id = ? AND role = 'user'");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $stmt->close();
    }

    /* Promote user → admin, or demote admin → user */
    if ($action === "toggle" && $userId > 0 && $userId !== (int)$_SESSION["user_id"]) {

        $stmt = $conn->prepare("UPDATE users SET role = IF(role = 'admin', 'user', 'admin') WHERE id = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $stmt->close();
    }

    /* ================= BOOKING ACTIONS (6a) ================= */

    $bookingId = (int)($_POST["booking_id"] ?? 0);

    if ($bookingId > 0) {

        if ($action === "confirm") {

            $stmt = $conn->prepare("UPDATE bookings SET status = 'confirmed' WHERE id = ?");
            $stmt->bind_param("i", $bookingId);
            $stmt->execute();
            $stmt->close();
        }

        if ($action === "bcancel") {

            $stmt = $conn->prepare("UPDATE bookings SET status = 'cancelled' WHERE id = ?");
            $stmt->bind_param("i", $bookingId);
            $stmt->execute();
            $stmt->close();
        }

        if ($action === "bdelete") {

            $stmt = $conn->prepare("DELETE FROM bookings WHERE id = ?");
            $stmt->bind_param("i", $bookingId);
            $stmt->execute();
            $stmt->close();
        }
    }

    /* Redirect so refreshing doesn't repeat the action */
    redirect("student.php");
}

/* ================= FETCH ALL USERS ================= */

 $result = $conn->query("
    SELECT users.id, users.username, users.email, users.role, users.created_at,
           COUNT(bookings.id) AS total_bookings
    FROM users
    LEFT JOIN bookings ON bookings.user_id = users.id
    GROUP BY users.id
    ORDER BY users.created_at DESC
");

 $users = $result->fetch_all(MYSQLI_ASSOC);

/* ================= FETCH ALL BOOKINGS (6b) ================= */

 $result = $conn->query("
    SELECT bookings.id, bookings.court, bookings.booking_date,
           bookings.booking_time, bookings.status, users.username
    FROM bookings
    JOIN users ON users.id = bookings.user_id
    ORDER BY bookings.created_at DESC
");

 $bookings = $result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>PICKLE | Admin Panel</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <link href="https://fonts.googleapis.com/css2?family=Alef:wght@400;700&family=Alike+Angular&display=swap"
          rel="stylesheet">

    <link rel="stylesheet" href="auth.css">

</head>

<body>

<div class="auth-wrap">

    <!-- ================= FLASH MESSAGE (6d) ================= -->

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
                    Admin Panel
                </h2>

                <p class="auth-sub">
                    Logged in as <?= e($_SESSION["username"]) ?> — manage all PICKLE players.
                </p>

            </div>


            <div class="admin-top-actions">
    <a href="manage-courts.php" class="small-btn">
    MANAGE COURTS
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


        <!-- =========================
             ALL USERS
        ========================= -->

        <div class="table-wrap">

            <table class="admin-table">

                <thead>

                    <tr>
                        <th>ID</th>
                        <th>USERNAME</th>
                        <th>EMAIL</th>
                        <th>ROLE</th>
                        <th>BOOKINGS</th>
                        <th>JOINED</th>
                        <th>ACTIONS</th>
                    </tr>

                </thead>

                <tbody>

                    <?php foreach ($users as $u): ?>

                        <tr>

                            <td><?= (int)$u["id"] ?></td>

                            <td>
                                <?= e($u["username"]) ?>
                                <?= (int)$u["id"] === (int)$_SESSION["user_id"] ? " (you)" : "" ?>
                            </td>

                            <td><?= e($u["email"]) ?></td>

                            <td>
                                <span class="badge <?= e($u["role"]) ?>">
                                    <?= strtoupper(e($u["role"])) ?>
                                </span>
                            </td>

                            <td><?= (int)$u["total_bookings"] ?></td>

                            <td><?= e(date("M j, Y", strtotime($u["created_at"]))) ?></td>

                            <td>

                                <?php if ((int)$u["id"] === (int)$_SESSION["user_id"]): ?>

                                    —

                                <?php else: ?>

                                    <form method="POST" action="student.php" class="row-form">
                                        <input type="hidden" name="action" value="toggle">
                                        <input type="hidden" name="user_id" value="<?= (int)$u["id"] ?>">
                                        <button type="submit" class="small-btn">
                                            <?= $u["role"] === "admin" ? "MAKE USER" : "MAKE ADMIN" ?>
                                        </button>
                                    </form>

                                    <?php if ($u["role"] === "user"): ?>

                                        <form method="POST" action="student.php" class="row-form"
                                              onsubmit="return confirm('Delete this user permanently?')">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="user_id" value="<?= (int)$u["id"] ?>">
                                            <button type="submit" class="small-btn danger">
                                                DELETE
                                            </button>
                                        </form>

                                    <?php endif; ?>

                                <?php endif; ?>

                            </td>

                        </tr>

                    <?php endforeach; ?>

                </tbody>

            </table>

        </div>


        <!-- =========================
             ALL BOOKINGS (6c)
        ========================= -->

        <h3 class="admin-section-title">
            ALL BOOKINGS
        </h3>

        <?php if (empty($bookings)): ?>

            <p class="empty-note">
                No bookings yet.
            </p>

        <?php else: ?>

            <div class="table-wrap">

                <table class="admin-table">

                    <thead>

                        <tr>
                            <th>ID</th>
                            <th>PLAYER</th>
                            <th>COURT</th>
                            <th>DATE</th>
                            <th>TIME</th>
                            <th>STATUS</th>
                            <th>ACTIONS</th>
                        </tr>

                    </thead>

                    <tbody>

                        <?php foreach ($bookings as $b): ?>

                            <tr>

                                <td><?= (int)$b["id"] ?></td>

                                <td><?= e($b["username"]) ?></td>

                                <td><?= e($b["court"]) ?></td>

                                <td><?= e(date("M j, Y", strtotime($b["booking_date"]))) ?></td>

                                <td><?= e(date("g:i A", strtotime($b["booking_time"]))) ?></td>

                                <td>
                                    <span class="status <?= e($b["status"]) ?>">
                                        <?= strtoupper(e($b["status"])) ?>
                                    </span>
                                </td>

                                <td>

                                    <?php if ($b["status"] !== "cancelled"): ?>

                                        <form method="POST" action="student.php" class="row-form">
                                            <input type="hidden" name="action" value="<?= $b["status"] === "pending" ? "confirm" : "bcancel" ?>">
                                            <input type="hidden" name="booking_id" value="<?= (int)$b["id"] ?>">
                                            <button type="submit" class="small-btn">
                                                <?= $b["status"] === "pending" ? "CONFIRM" : "CANCEL" ?>
                                            </button>
                                        </form>

                                    <?php endif; ?>

                                    <form method="POST" action="student.php" class="row-form"
                                          onsubmit="return confirm('Delete this booking permanently?')">
                                        <input type="hidden" name="action" value="bdelete">
                                        <input type="hidden" name="booking_id" value="<?= (int)$b["id"] ?>">
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