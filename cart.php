<?php
require_once 'config.php';
$cart = $_SESSION['cart'] ?? [];
$done = null;

function load_items($db, $cart) {
    $items = []; $total = 0;
    $st = $db->prepare("SELECT * FROM products WHERE id = ?");
    foreach ($cart as $id => $qty) {
        $st->execute([$id]);
        if ($p = $st->fetch()) { $p['qty'] = $qty; $items[] = $p; $total += $p['price'] * $qty; }
    }
    return [$items, $total];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $a = $_POST['a'] ?? '';
    $id = (int)($_POST['id'] ?? 0);
    $qty = (int)($_POST['qty'] ?? 1);
    if ($a === 'add') $cart[$id] = ($cart[$id] ?? 0) + max(1, $qty);
    if ($a === 'set') { if ($qty > 0) $cart[$id] = $qty; else unset($cart[$id]); }
    if ($a === 'del') unset($cart[$id]);
    if ($a === 'checkout' && $cart) {
        if (!isset($_SESSION['user'])) { header('Location: auth.php'); exit; }
        [$items, $total] = load_items($db, $cart);
        $db->beginTransaction();
        $db->prepare("INSERT INTO orders(user_id,total,address) VALUES(?,?,?)")
           ->execute([$_SESSION['user']['id'], $total, trim($_POST['address'] ?? '')]);
        $done = $db->lastInsertId();
        $ins = $db->prepare("INSERT INTO order_items VALUES(?,?,?,?)");
        foreach ($items as $i) $ins->execute([$done, $i['id'], $i['qty'], $i['price']]);
        $db->commit();
        $cart = [];
    }
    $_SESSION['cart'] = $cart;
    if (($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'fetch') {
        header('Content-Type: application/json');
        echo json_encode(['count' => array_sum($cart)]); exit;
    }
    if ($a !== 'checkout') { header('Location: cart.php'); exit; }
}

[$items, $total] = load_items($db, $cart);
$title = 'Giỏ hàng';
require 'header.php';
?>
<h1>Giỏ hàng</h1>
<?php if ($done): ?>
  <p class="ok">Đặt hàng thành công! Mã đơn #<?= $done ?>. Cảm ơn bạn đã mua sắm tại EzyBuy.</p>
<?php elseif (!$items): ?>
  <p class="empty">Giỏ hàng đang trống. <a href="index.php">Chọn sản phẩm</a></p>
<?php else: ?>
  <table>
    <?php foreach ($items as $i): ?>
    <tr>
      <td class="ic"><?= pic($i) ?></td>
      <td><a href="product.php?id=<?= $i['id'] ?>"><?= e($i['name']) ?></a><br><small><?= money($i['price']) ?></small></td>
      <td>
        <form method="post">
          <input type="hidden" name="a" value="set"><input type="hidden" name="id" value="<?= $i['id'] ?>">
          <input type="number" name="qty" value="<?= $i['qty'] ?>" min="0" max="99">
          <button class="link">Cập nhật</button>
        </form>
      </td>
      <td><b><?= money($i['price'] * $i['qty']) ?></b></td>
      <td>
        <form method="post"><input type="hidden" name="a" value="del"><input type="hidden" name="id" value="<?= $i['id'] ?>"><button class="link">Xóa</button></form>
      </td>
    </tr>
    <?php endforeach; ?>
  </table>
  <form method="post" class="checkout">
    <input type="hidden" name="a" value="checkout">
    <div class="price lg">Tổng cộng: <?= money($total) ?></div>
    <textarea name="address" placeholder="Địa chỉ giao hàng" required></textarea>
    <button class="btn">Đặt hàng</button>
    <?php if (!isset($_SESSION['user'])): ?><small>Bạn cần <a href="auth.php">đăng nhập</a> để đặt hàng.</small><?php endif; ?>
  </form>
<?php endif; require 'footer.php'; ?>
