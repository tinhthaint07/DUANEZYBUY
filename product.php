<?php
require_once 'config.php';
$st = $db->prepare("SELECT * FROM products WHERE id = ?");
$st->execute([(int)($_GET['id'] ?? 0)]);
$p = $st->fetch();
if (!$p) http_response_code(404);
$title = $p['name'] ?? 'Không tìm thấy';
require 'header.php';
if (!$p): ?>
  <p class="empty">Sản phẩm không tồn tại. <a href="index.php">Về trang chủ</a></p>
<?php else: ?>
<div class="detail">
  <div class="pic big"><?= pic($p) ?></div>
  <div>
    <h1><?= e($p['name']) ?></h1>
    <div class="meta">★ <?= $p['rating'] ?> · Đã bán <?= number_format($p['sold'], 0, ',', '.') ?> · <?= e($p['category']) ?></div>
    <p><?= e($p['description']) ?></p>
    <div class="price lg"><?= money($p['price']) ?><?php if ($p['old_price']): ?><s><?= money($p['old_price']) ?></s><?php endif; ?></div>
    <form method="post" action="cart.php" data-ajax>
      <input type="hidden" name="a" value="add">
      <input type="hidden" name="id" value="<?= $p['id'] ?>">
      <label>Số lượng <input type="number" name="qty" value="1" min="1" max="99"></label>
      <button class="btn">Thêm vào giỏ hàng</button>
    </form>
  </div>
</div>
<?php
$r = $db->prepare("SELECT * FROM products WHERE category = ? AND id != ? ORDER BY sold DESC LIMIT 4");
$r->execute([$p['category'], $p['id']]);
if ($rel = $r->fetchAll()): ?>
<h2>Sản phẩm liên quan</h2>
<div class="grid"><?php foreach ($rel as $i => $x) card($x, $i); ?></div>
<?php endif; endif; require 'footer.php'; ?>
