<?php
require "database/config.php";
require "database/function.php";

 $page = "home";

/* =========================
   DATA LOADING
========================= */

/* Upcoming open plays */
 $result = $conn->query("
    SELECT events.*, COUNT(event_joins.id) AS joined_count
    FROM events
    LEFT JOIN event_joins ON event_joins.event_id = events.id
    WHERE events.type = 'open_play'
      AND events.event_date >= CURDATE()
    GROUP BY events.id
    ORDER BY events.event_date ASC, events.event_time ASC
    LIMIT 6
");
 $openPlays = $result->fetch_all(MYSQLI_ASSOC);

/* Upcoming tournaments */
 $result = $conn->query("
    SELECT events.*, COUNT(event_joins.id) AS joined_count
    FROM events
    LEFT JOIN event_joins ON event_joins.event_id = events.id
    WHERE events.type = 'tournament'
      AND events.event_date >= CURDATE()
    GROUP BY events.id
    ORDER BY events.event_date ASC, events.event_time ASC
    LIMIT 6
");
 $tournaments = $result->fetch_all(MYSQLI_ASSOC);

/* Which events has the logged-in user joined? */
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

/* Real active player count */
 $result = $conn->query("SELECT COUNT(*) AS total FROM users");
 $playerCount = (int)$result->fetch_assoc()["total"];

/* Courts from the database */
 $result = $conn->query("SELECT * FROM courts ORDER BY name ASC");
 $courts = $result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>PICKLE | Book. Compete. Repeat.</title>

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


<section class="hero" id="home">

    <div class="hero-image"></div>

    <div class="hero-content">

        <p class="hero-small">
            YOUR GAME. YOUR COURT. YOUR TIME.
        </p>

        <h1>
            BOOK.<br>
            COMPETE.<br>
            <span>REPEAT.</span>
        </h1>

        <p class="hero-description">

            Book pickleball courts, join open plays,
            and compete in tournaments — all in one place.

        </p>


        <div class="hero-buttons">

            <a href="#courts"
               class="primary-btn"
               id="heroBookBtn">

                BOOK A COURT

            </a>

            <a href="#openplays"
               class="secondary-btn">

                BROWSE OPEN PLAYS

            </a>

        </div>

    </div>

</section>


<section class="quick-options">

    <div class="quick-container">


        <div class="quick-item">

            <div class="quick-icon">

                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M8 2v4"></path>
                    <path d="M16 2v4"></path>
                    <rect width="18" height="18" x="3" y="4" rx="3"></rect>
                    <path d="M3 10h18"></path>
                </svg>

            </div>

            <div>

                <h3>
                    BOOK COURTS
                </h3>

                <p>
                    Reserve your preferred court
                    anytime, anywhere.
                </p>

            </div>

        </div>


        <div class="quick-item">

            <div class="quick-icon">

                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path>
                    <circle cx="9" cy="7" r="4"></circle>
                    <path d="M22 21v-2a4 4 0 0 0-3-3.87"></path>
                    <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                </svg>

            </div>

            <div>

                <h3>
                    OPEN PLAYS
                </h3>

                <p>
                    Join open plays and meet
                    other pickleball players.
                </p>

            </div>

        </div>


        <div class="quick-item">

            <div class="quick-icon">

                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M6 9H4.5a2.5 2.5 0 0 1 0-5H6"></path>
                    <path d="M18 9h1.5a2.5 2.5 0 0 0 0-5H18"></path>
                    <path d="M4 22h16"></path>
                    <path d="M10 14.66V17c0 .55-.47.98-.97 1.21C7.85 18.75 7 20.24 7 22"></path>
                    <path d="M14 14.66V17c0 .55.47.98.97 1.21C16.15 18.75 17 20.24 17 22"></path>
                    <path d="M18 2H6v7a6 6 0 0 0 12 0V2Z"></path>
                </svg>

            </div>

            <div>

                <h3>
                    TOURNAMENTS
                </h3>

                <p>
                    Compete, win and be
                    part of the community.
                </p>

            </div>

        </div>

    </div>

</section>


<section class="booking-section"
         id="booking">

    <div class="section-container">

        <div class="section-title">

            <p>
                FIND YOUR COURT
            </p>

            <h2>
                Where do you want to play?
            </h2>

        </div>


        <div class="search-box">


            <div class="search-field">

                <label>
                    LOCATION
                </label>

                <input
                    type="text"
                    id="locationInput"
                    placeholder="Dumaguete City"
                >

            </div>


            <div class="search-field">

                <label>
                    DATE
                </label>

                <input
                    type="date"
                    id="dateInput"
                >

            </div>


            <div class="search-field">

                <label>
                    PLAYERS
                </label>

                <select id="playersInput">

                    <option>
                        2 Players
                    </option>

                    <option>
                        4 Players
                    </option>

                    <option>
                        6 Players
                    </option>

                    <option>
                        8 Players
                    </option>

                </select>

            </div>


            <button
                class="search-btn"
                id="searchBtn">

                SEARCH

            </button>

        </div>


        <p id="searchMessage"
           class="search-message">
        </p>

    </div>

</section>


<section class="courts-section"
         id="courts">

    <div class="section-container">


        <div class="section-header">

            <div>

                <p>
                    EXPLORE
                </p>

                <h2>
                    Popular Courts
                </h2>

            </div>

            <a href="courts.php">
                VIEW ALL COURTS →
            </a>

        </div>


        <div class="court-grid">

            <?php if (empty($courts)): ?>

                <p class="event-empty">
                    No courts yet — check back soon! 🏓
                </p>

            <?php else: ?>

                <?php foreach ($courts as $c): ?>

                    <article class="court-card">

                        <div class="court-image">

                            <?php if (!empty($c["image"])): ?>

                                <img src="<?= e($c["image"]) ?>" alt="<?= e($c["name"]) ?>">

                            <?php else: ?>

                                <div class="court-image-fallback">

                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                        <circle cx="12" cy="12" r="10"></circle>
                                        <circle cx="12" cy="7.5" r="1.6" fill="currentColor" stroke="none"></circle>
                                        <circle cx="16.5" cy="10.5" r="1.6" fill="currentColor" stroke="none"></circle>
                                        <circle cx="16.5" cy="15" r="1.6" fill="currentColor" stroke="none"></circle>
                                        <circle cx="12" cy="17.5" r="1.6" fill="currentColor" stroke="none"></circle>
                                        <circle cx="7.5" cy="15" r="1.6" fill="currentColor" stroke="none"></circle>
                                        <circle cx="7.5" cy="10.5" r="1.6" fill="currentColor" stroke="none"></circle>
                                        <circle cx="12" cy="12.5" r="1.6" fill="currentColor" stroke="none"></circle>
                                    </svg>

                                </div>

                            <?php endif; ?>

                            <span class="rating">★ <?= e(number_format((float)$c["rating"], 1)) ?></span>

                        </div>

                        <div class="court-details">

                            <h3><?= e($c["name"]) ?></h3>

                            <p class="court-location">◉ <?= e($c["location"]) ?></p>

                            <button class="book-btn" data-court="<?= e($c["name"]) ?>">
                                BOOK COURT
                            </button>

                        </div>

                    </article>

                <?php endforeach; ?>

            <?php endif; ?>

        </div>

    </div>

</section>


<section class="how-section"
         id="about">

    <div class="section-container">

        <div class="center-title">

            <p>
                SIMPLE FROM START TO FINISH
            </p>

            <h2>
                HOW IT WORKS
            </h2>

        </div>


        <div class="steps">


            <div class="step">

                <div class="step-icon">

                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="11" cy="11" r="8"></circle>
                        <path d="m21 21-4.3-4.3"></path>
                    </svg>

                </div>

                <h3>
                    CHOOSE
                </h3>

                <p>
                    Pick a court, open play,
                    or tournament.
                </p>

            </div>


            <div class="step">

                <div class="step-icon">

                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M8 2v4"></path>
                        <path d="M16 2v4"></path>
                        <rect width="18" height="18" x="3" y="4" rx="3"></rect>
                        <path d="M3 10h18"></path>
                    </svg>

                </div>

                <h3>
                    BOOK
                </h3>

                <p>
                    Select your time
                    and confirm.
                </p>

            </div>


            <div class="step">

                <div class="step-icon">

                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="8.5"></circle>
                        <path d="M5.8 6.8c3.8 1.8 8.6 1.8 12.4 0"></path>
                        <path d="M5.8 17.2c3.8-1.8 8.6-1.8 12.4 0"></path>
                    </svg>

                </div>

                <h3>
                    PLAY
                </h3>

                <p>
                    Show up, play hard,
                    and have fun.
                </p>

            </div>


            <div class="step">

                <div class="step-icon">

                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M6 9H4.5a2.5 2.5 0 0 1 0-5H6"></path>
                        <path d="M18 9h1.5a2.5 2.5 0 0 0 0-5H18"></path>
                        <path d="M4 22h16"></path>
                        <path d="M10 14.66V17c0 .55-.47.98-.97 1.21C7.85 18.75 7 20.24 7 22"></path>
                        <path d="M14 14.66V17c0 .55.47.98.97 1.21C16.15 18.75 17 20.24 17 22"></path>
                        <path d="M18 2H6v7a6 6 0 0 0 12 0V2Z"></path>
                    </svg>

                </div>

                <h3>
                    COMPETE
                </h3>

                <p>
                    Climb the ranks
                    and win rewards.
                </p>

            </div>


        </div>

    </div>

</section>


<section class="open-play"
         id="openplays">

    <div class="section-container">

        <div class="op-banner">

            <div class="op-text">

                <p class="op-eyebrow">
                    MEET THE COMMUNITY
                </p>

                <h2>
                    FIND AN<br>
                    OPEN PLAY
                </h2>

                <p class="op-desc">
                    Don't have a group yet? Join an
                    open play and meet players in
                    your area.
                </p>

                <a href="#openPlayList" class="primary-btn">

                    FIND OPEN PLAYS

                </a>

            </div>


            <div class="op-photo">

                <img src="images/openplay-photo.jpg"
                     alt="Open play session"
                     onerror="this.style.display='none'">

            </div>


            <div class="op-card">

                <p class="op-card-label">
                    NEXT OPEN PLAY
                </p>

                <?php if (!empty($openPlays)): ?>

                    <?php $nextPlay = $openPlays[0]; ?>

                    <h3>
                        <?= e($nextPlay["title"]) ?>
                    </h3>

                    <p class="op-card-line">
                        📅 <?= e(date("M j, Y", strtotime($nextPlay["event_date"]))) ?>
                        &nbsp;•&nbsp;
                        <?= e(date("g:i A", strtotime($nextPlay["event_time"]))) ?>
                    </p>

                    <p class="op-card-line">
                        ◉ <?= e($nextPlay["location"]) ?>
                    </p>

                    <p class="op-card-line">
                        👥 <?= (int)$nextPlay["joined_count"] ?> / <?= (int)$nextPlay["max_players"] ?> slots filled
                    </p>


                    <?php
                        $nextIsFull    = (int)$nextPlay["joined_count"] >= (int)$nextPlay["max_players"];
                        $nextHasJoined = in_array((int)$nextPlay["id"], $joinedIds);
                    ?>

                    <?php if ($nextHasJoined): ?>

                        <span class="op-card-btn op-card-btn-state">
                            YOU'RE IN ✓
                        </span>

                    <?php elseif ($nextIsFull): ?>

                        <span class="op-card-btn op-card-btn-state full">
                            FULL
                        </span>

                    <?php elseif (!isLoggedIn()): ?>

                        <a href="database/index.php?notice=login-event" class="op-card-btn">
                            JOIN NOW
                        </a>

                    <?php else: ?>

                        <form method="POST" action="database/events.php">

                            <input type="hidden" name="action" value="join">
                            <input type="hidden" name="event_id" value="<?= (int)$nextPlay["id"] ?>">

                            <button type="submit" class="op-card-btn">
                                JOIN NOW
                            </button>

                        </form>

                    <?php endif; ?>


                <?php else: ?>

                    <h3>
                        No open plays yet
                    </h3>

                    <p class="op-card-line">
                        New sessions drop soon — check back!
                    </p>

                    <a href="#openPlayList" class="op-card-btn">
                        SEE LISTINGS
                    </a>

                <?php endif; ?>


                <div class="op-photo-stat">

                    <strong>
                        <?= $playerCount ?>
                    </strong>

                    <span>
                        ACTIVE PLAYERS
                    </span>

                </div>

            </div>

        </div>

    </div>


    <div class="section-container event-list" id="openPlayList">

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


<section class="tournament"
         id="tournaments">

    <div class="section-container">

        <div class="tour-banner">

            <div class="tour-photo">

                <img src="images/tournament-photo.jpg"
                     alt="Tournament play"
                     onerror="this.style.display='none'">

            </div>


            <div class="tour-text">

                <p class="op-eyebrow">
                    COMPETE AND WIN
                </p>

                <h2>
                    JOIN TOURNAMENTS<br>
                    NEAR YOU
                </h2>

                <p class="op-desc">
                    Show your skills, earn points,
                    and be the champion.
                </p>

                <a href="#tournamentList" class="primary-btn">

                    VIEW TOURNAMENTS

                </a>

            </div>


            <div class="op-card">

                <p class="op-card-label">
                    NEXT TOURNAMENT
                </p>

                <?php if (!empty($tournaments)): ?>

                    <?php $nextT = $tournaments[0]; ?>

                    <h3>
                        <?= e($nextT["title"]) ?>
                    </h3>

                    <p class="op-card-line">
                        📅 <?= e(date("M j, Y", strtotime($nextT["event_date"]))) ?>
                        &nbsp;•&nbsp;
                        <?= e(date("g:i A", strtotime($nextT["event_time"]))) ?>
                    </p>

                    <p class="op-card-line">
                        ◉ <?= e($nextT["location"]) ?>
                    </p>

                    <p class="op-card-line">
                        👥 <?= (int)$nextT["joined_count"] ?> / <?= (int)$nextT["max_players"] ?> slots filled
                    </p>

                    <?php if ($nextT["prize"]): ?>

                        <p class="op-card-line">
                            🏆 <?= e($nextT["prize"]) ?>
                        </p>

                    <?php endif; ?>


                    <?php
                        $nextTIsFull    = (int)$nextT["joined_count"] >= (int)$nextT["max_players"];
                        $nextTHasJoined = in_array((int)$nextT["id"], $joinedIds);
                    ?>

                    <?php if ($nextTHasJoined): ?>

                        <span class="op-card-btn op-card-btn-state">
                            YOU'RE IN ✓
                        </span>

                    <?php elseif ($nextTIsFull): ?>

                        <span class="op-card-btn op-card-btn-state full">
                            FULL
                        </span>

                    <?php elseif (!isLoggedIn()): ?>

                        <a href="database/index.php?notice=login-event" class="op-card-btn">
                            REGISTER NOW
                        </a>

                    <?php else: ?>

                        <form method="POST" action="database/events.php">

                            <input type="hidden" name="action" value="join">
                            <input type="hidden" name="event_id" value="<?= (int)$nextT["id"] ?>">

                            <button type="submit" class="op-card-btn">
                                REGISTER NOW
                            </button>

                        </form>

                    <?php endif; ?>


                <?php else: ?>

                    <h3>
                        DUMA SMASH CUP
                    </h3>

                    <p class="op-card-line">
                        📅 Coming soon — dates dropping!
                    </p>

                    <p class="op-card-line">
                        Show your skills, earn points,
                        and be the champion.
                    </p>

                    <a href="#tournamentList" class="op-card-btn">
                        STAY TUNED
                    </a>

                <?php endif; ?>

            </div>

        </div>

    </div>


    <div class="section-container event-list" id="tournamentList">

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

<div class="modal"
     id="bookingModal">

    <div class="modal-box">

        <button
            class="close-btn"
            id="closeModal">

            ×

        </button>


        <p>
            RESERVE YOUR COURT
        </p>

        <h2>
            Book a Court
        </h2>


        <form id="bookingForm" method="POST" action="database/book.php">

            <input type="hidden" name="action" value="book">


            <label>
                COURT
            </label>

            <select
                id="courtSelect"
                name="court"
                required>

                <option value="">
                    — Choose a court —
                </option>

                <?php foreach ($courts as $c): ?>

                    <option value="<?= e($c["name"]) ?>">
                        <?= e($c["name"]) ?>
                    </option>

                <?php endforeach; ?>

            </select>


            <label>
                DATE
            </label>

            <input
                type="date"
                id="bookingDate"
                name="date"
                required
            >


            <label>
                TIME
            </label>

            <input
                type="hidden"
                name="time"
                id="bookingTime"
            >

            <div class="slot-grid" id="slotGrid"></div>

            <p class="slot-hint" id="slotHint">
                Choose a court and date to see available times.
            </p>


            <button
                type="submit"
                class="primary-btn confirm-btn">

                CONFIRM BOOKING

            </button>

        </form>


        <p
            id="bookingMessage"
            class="booking-message">
        </p>

    </div>

</div>


<?php include "includes/footer.php"; ?>


<script>
    const IS_LOGGED_IN = <?= isLoggedIn() ? "true" : "false" ?>;
</script>

<script src="javascript.js?v=6"></script>

</body>
</html>