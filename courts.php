<?php
require "database/config.php";
require "database/function.php";

 $page = "courts";

/* Courts from the database */
 $result = $conn->query("SELECT * FROM courts ORDER BY name ASC");
 $courts = $result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>PICKLE | Courts</title>

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
            EXPLORE
        </p>

        <h1>
            ALL COURTS
        </h1>

        <p class="op-desc">
            Every court you can book — pick one
            and reserve your slot.
        </p>

    </div>

</section>


<section class="courts-section">

    <div class="section-container">

        <?php if (empty($courts)): ?>

            <p class="event-empty">
                No courts yet — check back soon! 🏓
            </p>

        <?php else: ?>

            <div class="court-grid court-grid-page">

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

            </div>

        <?php endif; ?>

    </div>

</section>


<!-- BOOKING MODAL -->

<div class="modal" id="bookingModal">

    <div class="modal-box">

        <button class="close-btn" id="closeModal">×</button>

        <p>RESERVE YOUR COURT</p>

        <h2>Book a Court</h2>


        <form id="bookingForm" method="POST" action="database/book.php">

            <input type="hidden" name="action" value="book">

            <label>COURT</label>

            <select id="courtSelect" name="court" required>

                <option value="">— Choose a court —</option>

                <?php foreach ($courts as $c): ?>

                    <option value="<?= e($c["name"]) ?>">
                        <?= e($c["name"]) ?>
                    </option>

                <?php endforeach; ?>

            </select>


            <label>DATE</label>

            <input type="date" id="bookingDate" name="date" required>


            <label>TIME</label>

            <input type="hidden" name="time" id="bookingTime">

            <div class="slot-grid" id="slotGrid"></div>

            <p class="slot-hint" id="slotHint">
                Choose a court and date to see available times.
            </p>


            <button type="submit" class="primary-btn confirm-btn">
                CONFIRM BOOKING
            </button>

        </form>


        <p id="bookingMessage" class="booking-message"></p>

    </div>

</div>


<?php include "includes/footer.php"; ?>


<script>
    const IS_LOGGED_IN = <?= isLoggedIn() ? "true" : "false" ?>;
</script>

<script src="javascript.js?v=6"></script>

</body>
</html>