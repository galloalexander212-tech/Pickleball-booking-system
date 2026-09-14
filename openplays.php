<?php
require "database/config.php";
require "database/function.php";

 $page = "openplays";

/* ALL upcoming open plays — no LIMIT */
 $result = $conn->query("
    SELECT events.*, COUNT(event_joins.id) AS joined_count
    FROM events
    LEFT JOIN event_joins ON event_joins.event_id = events.id
    WHERE events.type = 'open_play'
      AND events.event_date >= CURDATE()
    GROUP BY events.id
    ORDER BY events.event_date ASC, events.event_time ASC
");
 $openPlays = $result->fetch_all(MYSQLI_ASSOC);

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

    <title>PICKLE | Open Plays</title>

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
            MEET THE COMMUNITY
        </p>

        <h1>
            OPEN PLAYS
        </h1>

        <p class="op-desc">
            Every upcoming open play — join one
            and meet players in your area.
        </p>

    </div>

</section>


<section class="open-play">

    <div class="section-container">

        <?php if (empty($openPlays)): ?>

            <p class="event-empty">
                No upcoming open plays right now — check back soon! 🏓
            </p>

        <?php else: ?>

            <div class="event-grid">

                <?php foreach ($openPlays as $ev): ?>

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