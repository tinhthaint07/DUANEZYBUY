<?php require_once 'config.php';
$cats = array_keys($CATS);
$curCat = $_GET['cat'] ?? ''; ?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($title ?? 'EzyBuy') ?> | EzyBuy</title>
<script>document.documentElement.classList.add('js')</script>
<link rel="stylesheet" href="<?= asset('style.css') ?>">
<script src="<?= asset('app.js') ?>" defer></script>
</head>
<body>
<header class="top">
  <a class="logo" href="index.php">Ezy<b>Buy</b></a>
  <form class="search" action="index.php">
    <select name="cat">
      <option value="">Tất cả</option>
      <?php foreach ($cats as $c): ?><option <?= $c === $curCat ? 'selected' : '' ?>><?= e($c) ?></option><?php endforeach; ?>
    </select>
    <input name="q" placeholder="Tìm sản phẩm trên EzyBuy" value="<?= e($_GET['q'] ?? '') ?>">
    <button>Tìm</button>
  </form>
  <nav>
    <?php if (isset($_SESSION['user'])): ?>
      <span>Chào, <?= e($_SESSION['user']['name']) ?></span>
      <a href="auth.php?m=logout">Đăng xuất</a>
    <?php else: ?>
      <a href="auth.php">Đăng nhập</a>
    <?php endif; ?>
    <a class="cart" href="cart.php">🛒 Giỏ hàng <span><?= cart_count() ?></span></a>
  </nav>
  <div class="subnav">
    <a href="index.php" class="<?= !$curCat && !isset($_GET['all']) ? 'on' : '' ?>">Trang chủ</a>
    <a href="?all=1" class="<?= isset($_GET['all']) ? 'on' : '' ?>">Tất cả sản phẩm</a>
    <?php foreach ($cats as $c): ?><a href="?cat=<?= urlencode($c) ?>" class="<?= $c === $curCat ? 'on' : '' ?>"><?= $CATS[$c] ?> <?= e($c) ?></a><?php endforeach; ?>
  </div>
</header>
<main>
