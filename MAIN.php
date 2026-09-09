<?php
// ALWAYS start session before any HTML output
// session_set_cookie_params([
//     'lifetime' => 0,
//     'path' => '/',
//     'domain' => '',
//     'secure' => false,
//     'httponly' => true,
//     'samesite' => 'Lax'
// ]);
// session_start();
// ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PureSolar Energy - Pure Power Pure Savings</title>
        <link rel="stylesheet" href="CSS/MainUI.css">
        <link rel="stylesheet" href="CSS/MainHeader.css">
        <link rel="stylesheet" href="CSS/Authentication.css">
        <link rel="stylesheet" href="CSS/MainFooter.css">
        <!-- <link rel="stylesheet" href="CSS/SignUp.css">x -->
        <link rel="stylesheet" href="CSS/Login.css">
        
</head>
<body>

<!-- ================================================================ -->

    <?php include 'MainPhp/header.php'; ?>
    <?php include 'MainPhp/Function.php'; ?>
    <?php include 'MainPhp/hero.php'; ?>
    <?php include 'MainPhp/SolarModule.php'; ?>
    <?php include 'MainPhp/BatterySection.php'; ?>
    <?php include 'MainPhp/InverterSection.php'; ?>
    <?php include 'MainPhp/Options.php'; ?>
    <?php include 'MainPhp/footer.php'; ?>
    
    <!-- ===================================================================== -->
    
    <!-- <?php include 'MainPhp/Authentication.php'; ?> -->
    


    <script src="script.js"></script>
</body>
</html>


