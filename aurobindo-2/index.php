<?php
require './shared-components/db.php';
require './admin/shared-components/error_reporting.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <?php require './shared-components/head.php'; ?>
</head>

<body>
    <?php require './shared-components/header.php'; ?>
    <main class="main">
        <?php
        require './components/home/slider.php';
        require './components/flash-news.php';
        require './components/about/about.php';
        require './components/counter.php';
        require './components/board-of-management.php';
        require './components/contact/contact.php';
        ?>
    </main>
    <?php require './shared-components/footer.php'; ?>

    <?php require './shared-components/back-to-top.php'; ?>
    <?php require './shared-components/scripts.php'; ?>
</body>

</html>