<?php
require "database/config.php";
require "database/function.php";

 $page = "tournaments";

/* ALL upcoming tournaments — no LIMIT */
 $result = $conn->query("
    SELECT events.*, COUNT(event_joins.id) AS joined_count
    FROM events
    LEFT JOIN event_joins ON event_joins.event_id = events.id
    WHERE events.type = 'tournament'
      AND events.event_date >= CURDATE()
    GROUP BY events.id
    ORDER BY events.event_date ASC, events.event_time ASC
");
 $tournaments = $result->fetch_all(MYSQLI_ASSOC);

 $joinedIds = [];

if (isLoggedIn()) {

    $stmt = $conn->prepare("SELECT event_id FROM event_joins WHERE user_id = ?");
    $stmt->bind_param("i", $_SESSION["user_id"]);
    $stmt->execute();
    $joinResult = $stmt->get_result();

    while ($row = $joinResult->fetch_assoc()) {
        $joinedIds[] = (int)$row["event_id"];
    }

    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>PICKLE | Tournaments</title>

    <link rel="icon" type="image/png" href="images/logo.png">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <link href="https://fonts.googleapis.com/css2?family=Alef:wght@400;700&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="style.css">

</head>

<body>

<?php $flash = getFlash(); ?>

<?php if ($flash): ?>

    <div class="flash <?= e($flash["type"]) ?>" id="flashMsg">
        <?= e($flash["message"]) ?>
    </div>

<?php endif; ?>


<?php include "includes/navbar.php"; ?>


<section class="page-hero">

    <div class="section-container">

        <p class="op-eyebrow">
            COMPETE AND WIN
        </p>

        <h1>
            TOURNAMENTS
        </h1>

        <p class="op-desc">
            Every upcoming tournament — register
            and fight for the crown.
        </p>

    </div>

</section>


<section class="tournament tournament-page">

    <div class="section-container">

        <?php if (empty($tournaments)): ?>

            <p class="event-empty">
                No upcoming tournaments right now — check back soon! 🏆
            </p>

        <?php else: ?>

            <div class="event-grid">

                <?php foreach ($tournaments as $ev): ?>

                    <?php include "includes/event-card.php"; ?>

                <?php endforeach; ?>

            </div>

        <?php endif; ?>

    </div>

</section>


<?php include "includes/footer.php"; ?>


<script>
    const IS_LOGGED_IN = <?= isLoggedIn() ? "true" : "false" ?>;
</script>

<script src="javascript.js?v=6"></script>

</body>
</html>