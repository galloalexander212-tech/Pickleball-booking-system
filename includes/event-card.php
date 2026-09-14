<?php
/* Shared event card — expects $ev (event row with joined_count),
   plus $joinedIds from the including file */

 $isFull    = (int)$ev["joined_count"] >= (int)$ev["max_players"];
 $hasJoined = in_array((int)$ev["id"], $joinedIds ?? []);
?>

<article class="event-card<?= !empty($ev["image"]) ? " event-card-photo" : "" ?>">

    <div class="event-top">

        <span class="event-badge <?= e($ev["type"]) ?>">
            <?= strtoupper(str_replace("_", " ", e($ev["type"]))) ?>
        </span>

        <?php if ($hasJoined): ?>

            <span class="event-badge joined">
                JOINED ✓
            </span>

        <?php endif; ?>

    </div>


    <?php if (!empty($ev["image"])): ?>

        <div class="event-image">
            <img src="<?= e($ev["image"]) ?>" alt="<?= e($ev["title"]) ?>">
        </div>

    <?php endif; ?>


    <div class="event-text-plate">

        <h3><?= e($ev["title"]) ?></h3>

        <p class="event-meta">
            📅 <?= e(date("D, M j, Y", strtotime($ev["event_date"]))) ?>
            &nbsp;•&nbsp;
            🕐 <?= e(date("g:i A", strtotime($ev["event_time"]))) ?>
        </p>

        <p class="event-meta">
            ◉ <?= e($ev["location"]) ?>
        </p>

        <?php if (!empty($ev["description"])): ?>

            <p class="event-desc"><?= e($ev["description"]) ?></p>

        <?php endif; ?>

        <?php if (!empty($ev["prize"])): ?>

            <p class="event-prize">🏆 <?= e($ev["prize"]) ?></p>

        <?php endif; ?>

    </div>


    <div class="event-bottom">

        <span class="event-slots">
            <?= (int)$ev["joined_count"] ?> / <?= (int)$ev["max_players"] ?> PLAYERS
        </span>


        <?php if ($hasJoined): ?>

            <span class="join-state joined">YOU'RE IN ✓</span>

        <?php elseif ($isFull): ?>

            <span class="join-state full">FULL</span>

        <?php elseif (!isLoggedIn()): ?>

            <a href="database/index.php?notice=login-event" class="join-btn">JOIN</a>

        <?php else: ?>

            <form method="POST" action="database/events.php" class="join-form">

                <input type="hidden" name="action" value="join">
                <input type="hidden" name="event_id" value="<?= (int)$ev["id"] ?>">

                <button type="submit" class="join-btn">JOIN</button>

            </form>

        <?php endif; ?>

    </div>

</article>