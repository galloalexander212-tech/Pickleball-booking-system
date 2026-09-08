<?php
require "database/config.php";
require "database/function.php";

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

    <link href="https://fonts.googleapis.com/css2?family=Alef:wght@400;700&family=Alike+Angular&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="style.css">

</head>

<body>

<!-- FLASH MESSAGE -->

<?php $flash = getFlash(); ?>

<?php if ($flash): ?>

    <div class="flash <?= e($flash["type"]) ?>" id="flashMsg">
        <?= e($flash["message"]) ?>
    </div>

<?php endif; ?>


<!-- NAVBAR -->

<header class="navbar">

    <div class="nav-container">

        <a href="index.php" class="logo">
            <img src="images/logo.png" alt="PICKLE" class="logo-img">
        </a>


        <nav class="nav-links">

            <a href="index.php">
                HOME
            </a>

            <a href="courts.php">
                COURTS
            </a>

            <a href="openplays.php">
                OPEN PLAYS
            </a>

            <a href="tournaments.php">
                TOURNAMENTS
            </a>

            <a href="about.php" class="active">
                ABOUT US
            </a>

            <a href="index.php#contact">
                CONTACT US
            </a>

        </nav>


        <?php if (isLoggedIn()): ?>

            <a href="database/info.php" class="login-btn">
                HI, <?= e(strtoupper($_SESSION["username"])) ?>
            </a>

        <?php else: ?>

            <a href="database/index.php" class="login-btn">
                LOG IN / SIGN UP
            </a>

        <?php endif; ?>


        <button class="menu-btn" id="menuBtn">
            ☰
        </button>

    </div>

</header>


<!-- MOBILE NAV -->

<div class="mobile-menu" id="mobileMenu">

    <a href="index.php">HOME</a>

    <a href="courts.php">COURTS</a>

    <a href="openplays.php">OPEN PLAYS</a>

    <a href="tournaments.php">TOURNAMENTS</a>

    <a href="about.php">ABOUT US</a>

    <a href="index.php#contact">CONTACT US</a>

</div>


<!-- PAGE HERO STRIP -->

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


<!-- MISSION + STATS -->

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


<!-- VALUES -->

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


            <!-- VALUE 1 -->

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


            <!-- VALUE 2 -->

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


            <!-- VALUE 3 -->

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


<!-- CTA + CONTACT -->

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

                <a href="tel:0912345678">
                    0912345678
                </a>

            </div>

        </div>

    </div>

</section>


<!-- FOOTER -->

<!-- =========================
     FOOTER
========================= -->

<footer>

    <div class="footer-container">


        <!-- BRAND COLUMN -->

        <div class="footer-brand">

            <img src="images/logo.png" alt="PICKLE" class="footer-logo-img">

            <p class="footer-tagline">
                Your ultimate pickleball companion.
                Book, compete, and repeat.
            </p>


            <div class="footer-socials">

                <a href="#" aria-label="Facebook">

                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"></path>
                    </svg>

                </a>


                <a href="#" aria-label="Instagram">

                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <rect width="20" height="20" x="2" y="2" rx="5" ry="5"></rect>
                        <path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"></path>
                        <line x1="17.5" x2="17.51" y1="6.5" y2="6.5"></line>
                    </svg>

                </a>


                <a href="#" aria-label="TikTok">

                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M9 12a4 4 0 1 0 4 4V4a5 5 0 0 0 5 5"></path>
                    </svg>

                </a>

            </div>

        </div>


        <!-- QUICK LINKS -->

        <div class="footer-col">

            <h4>
                QUICK LINKS
            </h4>

            <a href="index.php">Home</a>
            <a href="courts.php">Courts</a>
            <a href="openplays.php">Open Plays</a>
            <a href="tournaments.php">Tournaments</a>
            <a href="about.php">About Us</a>
            <a href="index.php#contact">Contact Us</a>

        </div>


        <!-- SUPPORT -->

        <div class="footer-col">

            <h4>
                SUPPORT
            </h4>

            <a href="#">FAQs</a>
            <a href="index.php#about">How It Works</a>
            <a href="#">Terms &amp; Conditions</a>
            <a href="#">Privacy Policy</a>

        </div>


        <!-- CONTACT US -->

        <div class="footer-col footer-contact">

            <h4>
                CONTACT US
            </h4>


            <div class="footer-contact-row">

                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"></path>
                    <circle cx="12" cy="10" r="3"></circle>
                </svg>

                <span>
                    Dumaguete City, Philippines
                </span>

            </div>


            <div class="footer-contact-row">

                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <rect width="20" height="16" x="2" y="4" rx="2"></rect>
                    <path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"></path>
                </svg>

                <a href="mailto:galloalexander212@gmail.com">
                    galloalexander212@gmail.com
                </a>

            </div>


            <div class="footer-contact-row">

                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path>
                </svg>

                <a href="tel:+639123456789">
                    +63 912 345 6789
                </a>

            </div>

        </div>

    </div>


    <!-- BOTTOM BAR -->

    <div class="footer-bottom">

        <div class="footer-bottom-inner">

            <p>
                © 2026 PICKLE. All rights reserved.
            </p>


            <p class="footer-motto">

                Book. Compete. Repeat.

                <svg viewBox="0 0 24 24" fill="currentColor">
                    <circle cx="12" cy="12" r="10"></circle>
                    <circle cx="12" cy="7.5" r="1.6" fill="#171916"></circle>
                    <circle cx="16.5" cy="10.5" r="1.6" fill="#171916"></circle>
                    <circle cx="16.5" cy="15" r="1.6" fill="#171916"></circle>
                    <circle cx="12" cy="17.5" r="1.6" fill="#171916"></circle>
                    <circle cx="7.5" cy="15" r="1.6" fill="#171916"></circle>
                    <circle cx="7.5" cy="10.5" r="1.6" fill="#171916"></circle>
                    <circle cx="12" cy="12.5" r="1.6" fill="#171916"></circle>
                </svg>

            </p>

        </div>

    </div>

</footer>


<script>
    const IS_LOGGED_IN = <?= isLoggedIn() ? "true" : "false" ?>;
</script>

<script src="javascript.js?v=5"></script>

</body>
</html>