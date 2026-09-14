<?php
/* Shared navbar — expects $page to be set by the including file */

if (!isset($page)) {
    $page = "";
}
?>

<header class="navbar">

    <div class="nav-container">

        <a href="index.php" class="logo">
            <img src="images/logo.png" alt="PICKLE" class="logo-img">
        </a>


        <nav class="nav-links">

            <a href="index.php" class="<?= $page === "home" ? "active" : "" ?>">
                HOME
            </a>

            <a href="courts.php" class="<?= $page === "courts" ? "active" : "" ?>">
                COURTS
            </a>

            <a href="openplays.php" class="<?= $page === "openplays" ? "active" : "" ?>">
                OPEN PLAYS
            </a>

            <a href="tournaments.php" class="<?= $page === "tournaments" ? "active" : "" ?>">
                TOURNAMENTS
            </a>

            <a href="about.php" class="<?= $page === "about" ? "active" : "" ?>">
                ABOUT US
            </a>

            <a href="support.php" class="<?= $page === "support" ? "active" : "" ?>">
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


<div class="mobile-menu" id="mobileMenu">

    <a href="index.php">HOME</a>

    <a href="courts.php">COURTS</a>

    <a href="openplays.php">OPEN PLAYS</a>

    <a href="tournaments.php">TOURNAMENTS</a>

    <a href="about.php">ABOUT US</a>

    <a href="support.php">CONTACT US</a>

</div>