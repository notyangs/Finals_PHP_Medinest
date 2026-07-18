<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/functions.php';
$asset_base = isset($base) ? $base : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="referrer" content="strict-origin-when-cross-origin">
    <title><?php echo h(isset($page_title) ? $page_title . ' | ' . SITE_NAME : SITE_NAME); ?></title>
    <link rel="stylesheet" href="<?php echo h($asset_base); ?>assets/css/style.css">
</head>
<body>
<header class="site-header">
    <a class="brand" href="<?php echo h($asset_base); ?>index.php"><span class="logo">+</span><span>MediNest<small>Community Pharmacy</small></span></a>
    <nav>
        <a href="<?php echo h($asset_base); ?>store.php">Shop</a>
        <a href="<?php echo h($asset_base); ?>about.php">About</a>
        <a href="<?php echo h($asset_base); ?>cart.php">Cart (<?php echo cart_count(); ?>)</a>
        <?php if (is_admin()) { ?><a href="<?php echo h($asset_base); ?>admin/index.php">Admin</a><?php } ?>
        <?php if (signed_in()) { ?><a class="nav-button" href="<?php echo h($asset_base); ?>logout.php">Logout</a><?php } else { ?><a class="nav-button" href="<?php echo h($asset_base); ?>login.php">Sign in</a><?php } ?>
    </nav>
</header>
<main>
