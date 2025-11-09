<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title><?= isset($page_title) ? $page_title : 'Outbound' ?></title>
    <link rel="stylesheet" href="../assets/css/dashboard_admin.css">

</head>
<body>
<header class="topbar">
    <div class="container">
        <h1 class="brand">Outbound LPK CTI </h1>
        <div class="user-info">
            <?= isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : '' ?>
            | <a href="../logout.php">Logout</a>
        </div>
    </div>
</header>
<div class="container main">
