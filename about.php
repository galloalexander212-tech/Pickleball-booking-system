<?php
require "database/config.php";
require "database/function.php";

 $page = "about";

/* Real numbers for the stats row */
 $result = $conn->query("SELECT COUNT(*) AS total FROM users");
 $playerCount = (int)$result->fetch_assoc()["total"];

 $result = $conn->query("SELECT COUNT(*) AS total FROM events");
 $eventCount = (int)$result->fetch_assoc()["total"];

 $result = $conn->query("SELECT COUNT(*) AS total FROM bookings");
 $bookingCount = (int)$result->fetch_assoc()["total"];

 $result = $conn->query("SELECT COUNT(*) AS total FROM courts");
 $courtCount = (int)$result->fetch_assoc()["total"];
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>PICKLE | About Us</title>

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
            OUR STORY
        </p>

        <h1>
            ABOUT PICKLE
        </h1>

        <p class="op-desc">
            Book. Compete. Repeat. — built by players,
            for players.
        </p>

    </div>

</section>


<section class="about-section">

    <div class="section-container">

        <p class="about-mission">
            PICKLE was born in <strong>Dumaguete City</strong> with one
            goal — make pickleball <strong>accessible to everyone</strong>.
            No group chats, no lost reservations, no "who's playing
            this weekend?" — just book a court, join an open play,
            and compete in tournaments, <strong>all in one place</strong>.
        </p>


        <div class="stats-row">

            <div class="stat-box">
                <strong><?= $playerCount ?></strong>
                <span>ACTIVE PLAYERS</span>
            </div>

            <div class="stat-box">
                <strong><?= $courtCount ?></strong>
                <span>PARTNER COURTS</span>
            </div>

            <div class="stat-box">
                <strong><?= $eventCount ?></strong>
                <span>EVENTS HOSTED</span>
            </div>

            <div class="stat-box">
                <strong><?= $bookingCount ?></strong>
                <span>COURTS BOOKED</span>
            </div>

        </div>

    </div>

</section>


<section class="how-section">

    <div class="section-container">

        <div class="center-title">

            <p>
                WHAT WE STAND FOR
            </p>

            <h2>
                OUR VALUES
            </h2>

        </div>


        <div class="values-grid">


            <div class="value-card">

                <div class="value-icon">

                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path>
                        <circle cx="9" cy="7" r="4"></circle>
                        <path d="M22 21v-2a4 4 0 0 0-3-3.87"></path>
                        <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                    </svg>

                </div>

                <h3>
                    COMMUNITY FIRST
                </h3>

                <p>
                    Open plays and tournaments built to connect
                    players of every level — nobody plays alone.
                </p>

            </div>


            <div class="value-card">

                <div class="value-icon">

                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
                    </svg>

                </div>

                <h3>
                    FAIR PLAY
                </h3>

                <p>
                    Transparent bookings, real slot counts,
                    and honest listings — what you see is
                    what you get.
                </p>

            </div>


            <div class="value-card">

                <div class="value-icon">

                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="23 6 13.5 15.5 8.5 10.5 1 18"></polyline>
                        <polyline points="17 6 23 6 23 12"></polyline>
                    </svg>

                </div>

                <h3>
                    GROW THE GAME
                </h3>

                <p>
                    From first-timers to champions — every match,
                    every event, grows pickleball in our city.
                </p>

            </div>


        </div>

    </div>

</section>


<section class="open-play">

    <div class="section-container">

        <div class="cta-banner">

            <h2>
                READY TO PLAY?
            </h2>

            <p>
                Join the community — your court,
                your time, your game.
            </p>


            <div class="cta-buttons">

                <a href="courts.php" class="primary-btn">
                    BOOK A COURT
                </a>

                <a href="openplays.php" class="secondary-btn">
                    FIND AN OPEN PLAY
                </a>

            </div>

        </div>


        <div class="contact-strip">

            <div class="contact-box">

                <span>
                    EMAIL US
                </span>

                <a href="mailto:galloalexander212@gmail.com">
                    galloalexander212@gmail.com
                </a>

            </div>


            <div class="contact-box">

                <span>
                    CALL US
                </span>

                <a href="tel:+639123456789">
                    +63 912 345 6789
                </a>

            </div>

        </div>

    </div>

</section>


<?php include "includes/footer.php"; ?>


<script>
    const IS_LOGGED_IN = <?= isLoggedIn() ? "true" : "false" ?>;
</script>

<script src="javascript.js?v=6"></script>

</body>
</html>