<?php
// Công cụ gán ảnh thật cho sản phẩm — chỉ chạy trên máy local.
require_once 'config.php';
if (!in_array($_SERVER['REMOTE_ADDR'], ['127.0.0.1', '::1'])) { http_response_code(403); exit('Chỉ dùng trên máy local.'); }
$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)($_POST['id'] ?? 0);
    $data = '';
    if (!empty($_FILES['file']['tmp_name'])) $data = file_get_contents($_FILES['file']['tmp_name']);
    elseif (preg_match('#^https?://#i', $_POST['url'] ?? ''))
        $data = @file_get_contents($_POST['url'], false, stream_context_create(['http' => ['timeout' => 10, 'header' => "User-Agent: Mozilla/5.0\r\n"]]));
    $info = $data ? @getimagesizefromstring($data) : false;
    if ($info && strlen($data) < 8e6) {
        @mkdir(__DIR__ . '/assets/img', 0777, true);
        $f = "assets/img/$id.jpg";
        if (function_exists('imagecreatefromstring') && ($im = @imagecreatefromstring($data))) {   // thu nhỏ còn tối đa 640px cho web nhẹ
            if (imagesx($im) > 640) $im = imagescale($im, 640);
            $bg = imagecreatetruecolor(imagesx($im), imagesy($im));
            imagefill($bg, 0, 0, 0xFFFFFF);
            imagecopy($bg, $im, 0, 0, 0, 0, imagesx($im), imagesy($im));
            imagejpeg($bg, __DIR__ . "/$f", 82);
        } else file_put_contents(__DIR__ . "/$f", $data);
        $db->prepare("UPDATE products SET image = ? WHERE id = ?")->execute([$f, $id]);
        @unlink(sys_get_temp_dir() . '/ezybuy_home_v4.html');
        $msg = "Đã lưu ảnh cho sản phẩm #$id.";
    } else $msg = 'Không đọc được ảnh. Hãy chọn file JPG, PNG hoặc WEBP dưới 8MB, hoặc dán link ảnh trực tiếp.';
}
$title = 'Quản lý ảnh';
require 'header.php';
$st = $db->prepare("SELECT * FROM products" . ($curCat !== '' ? " WHERE category = ?" : "") . " ORDER BY category, id");
$st->execute($curCat !== '' ? [$curCat] : []);
?>
<h1 class="ttl">Quản lý ảnh sản phẩm</h1>
<p>Danh sách này cho phép gán ảnh đúng từng sản phẩm. Ưu tiên tải ảnh chụp đúng mặt hàng (JPG, PNG hoặc WEBP); không dùng ảnh của sản phẩm khác. Ảnh được lưu riêng theo mã sản phẩm và dùng đồng bộ ở trang chủ, danh mục, chi tiết và giỏ hàng.</p>
<?php if ($msg): ?><p class="ok"><?= e($msg) ?></p><?php endif; ?>
<?php foreach ($st->fetchAll() as $p): ?>
<div class="irow">
  <div class="ith"><?= pic($p) ?></div>
  <b>#<?= $p['id'] ?> · <?= e($p['name']) ?></b>
  <form method="post" enctype="multipart/form-data" action="?cat=<?= urlencode($curCat) ?>">
    <input type="hidden" name="id" value="<?= $p['id'] ?>">
    <input type="file" name="file" accept="image/*">
    <input name="url" placeholder="hoặc dán link ảnh https://...">
    <button class="btn sm">Lưu</button>
  </form>
</div>
<?php endforeach; require 'footer.php'; ?>
