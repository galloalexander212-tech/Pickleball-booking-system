<?php
require "database/config.php";
require "database/function.php";

 $page = "support";
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>PICKLE | Support</title>

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


<!-- =========================
     PAGE HERO
========================= -->

<section class="page-hero">

    <div class="section-container">

        <p class="op-eyebrow">
            HELP CENTER
        </p>

        <h1>
            SUPPORT
        </h1>

        <p class="op-desc">
            FAQs, terms and privacy — everything
            you need to know.
        </p>

    </div>

</section>


<!-- =========================
     FAQs
========================= -->

<section class="support-section" id="faqs">

    <div class="section-container">

        <h2 class="support-title">
            FREQUENTLY ASKED QUESTIONS
        </h2>


        <div class="faq-item">

            <h3>
                How do I book a court?
            </h3>

            <p>
                Log in, hit <strong>BOOK A COURT</strong> (or browse the
                Courts page), pick your court and date, then tap a free
                time slot and confirm. Your booking appears on your
                My Account page right away.
            </p>

        </div>


        <div class="faq-item">

            <h3>
                Why does my booking say PENDING?
            </h3>

            <p>
                New bookings are reviewed by our admins. Once approved,
                the status flips to <strong>CONFIRMED</strong> — you'll
                see it change on My Account.
            </p>

        </div>


        <div class="faq-item">

            <h3>
                Can I cancel a booking?
            </h3>

            <p>
                Yes — go to My Account → MY BOOKINGS → CANCEL.
                Cancelled slots are freed up immediately for other
                players.
            </p>

        </div>


        <div class="faq-item">

            <h3>
                How do open plays and tournaments work?
            </h3>

            <p>
                Hit <strong>JOIN</strong> on any event card. Slots are
                first come, first served — full events show FULL.
                You can leave an event anytime from My Account before
                it starts.
            </p>

        </div>


        <div class="faq-item">

            <h3>
                What are the booking hours?
            </h3>

            <p>
                Courts run hourly slots from <strong>6:00 AM to 9:00 PM</strong>.
                Already-booked slots and past times are shown as
                unavailable.
            </p>

        </div>


        <div class="faq-item">

            <h3>
                I forgot my password!
            </h3>

            <p>
                Reach out to us through the email or phone number in
                the footer and we'll get you back in the game.
            </p>

        </div>

    </div>

</section>


<!-- =========================
     TERMS
========================= -->

<section class="support-section" id="terms">

    <div class="section-container">

        <h2 class="support-title">
            TERMS &amp; CONDITIONS
        </h2>


        <div class="legal-block">

            <h3>
                1. Bookings
            </h3>

            <p>
                Court slots are reserved on a first come, first served
                basis. A booking is pending until confirmed by an admin.
                Only one active reservation may hold a court slot at a
                time.
            </p>

        </div>


        <div class="legal-block">

            <h3>
                2. Cancellations
            </h3>

            <p>
                You may cancel a booking anytime before your slot.
                Repeated no-shows on confirmed bookings may lead to
                booking restrictions.
            </p>

        </div>


        <div class="legal-block">

            <h3>
                3. Events
            </h3>

            <p>
                Open plays and tournaments have limited slots. Joining
                is binding — leave an event before it starts if you
                can't make it. Organizers may cancel events; joined
                players are notified through the platform.
            </p>

        </div>


        <div class="legal-block">

            <h3>
                4. Conduct
            </h3>

            <p>
                Respect the courts, the staff and fellow players.
                Damage to court property is the responsibility of the
                booking player. Accounts violating fair-play rules may
                be suspended.
            </p>

        </div>


        <div class="legal-block">

            <h3>
                5. Accounts
            </h3>

            <p>
                Provide accurate information when registering. You are
                responsible for activity under your account. Do not
                share your credentials with anyone.
            </p>

        </div>

    </div>

</section>


<!-- =========================
     PRIVACY
========================= -->

<section class="support-section" id="privacy">

    <div class="section-container">

        <h2 class="support-title">
            PRIVACY POLICY
        </h2>


        <div class="legal-block">

            <h3>
                What we collect
            </h3>

            <p>
                Your username, email address, a securely hashed password,
                your court bookings and the events you join. That's it.
            </p>

        </div>


        <div class="legal-block">

            <h3>
                How we use it
            </h3>

            <p>
                To run the platform — showing your bookings, managing
                event slots and letting admins keep the community fair.
                Your password is never stored in readable form.
            </p>

        </div>


        <div class="legal-block">

            <h3>
                What we never do
            </h3>

            <p>
                We never sell or share your personal data with third
                parties, and we never store plain-text passwords.
            </p>

        </div>


        <div class="legal-block">

            <h3>
                Sessions &amp; cookies
            </h3>

            <p>
                We use a single session cookie to keep you logged in.
                Sessions expire automatically after 30 minutes of
                inactivity.
            </p>

        </div>


        <div class="legal-block">

            <h3>
                Questions about your data?
            </h3>

            <p>
                Contact us through the email or phone number in the
                footer and we'll help you out.
            </p>

        </div>

    </div>

</section>


<!-- =========================
     FOOTER
========================= -->

<footer>

    <div class="footer-container">


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


        <div class="footer-col">

            <h4>
                QUICK LINKS
            </h4>

            <a href="index.php">Home</a>
            <a href="courts.php">Courts</a>
            <a href="openplays.php">Open Plays</a>
            <a href="tournaments.php">Tournaments</a>
            <a href="about.php">About Us</a>
            <a href="support.php">Contact Us</a>

        </div>


        <div class="footer-col">

            <h4>
                SUPPORT
            </h4>

            <a href="support.php#faqs">FAQs</a>
            <a href="index.php#about">How It Works</a>
            <a href="support.php#terms">Terms &amp; Conditions</a>
            <a href="support.php#privacy">Privacy Policy</a>

        </div>


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