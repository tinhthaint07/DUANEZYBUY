<?php
$title = 'Trang chủ';
require 'header.php';
$q = trim($_GET['q'] ?? '');
$home = $q === '' && $curCat === '' && !array_intersect_key($_GET, ['sort' => 1, 'page' => 1, 'all' => 1]);

if ($home):
    ob_start();
    $promos = [
        ['Tuần lễ', 'Black Friday', '🛍️🎁', ['sort' => 'hot']],
        ['Giảm đến 40%', 'đồ điện tử', '💻🎧', ['cat' => 'Điện tử']],
        ['Giảm đến 35%', 'đồ chơi & trò chơi', '🧸🧩', ['cat' => 'Đồ chơi']],
        ['Giảm đến 40%', 'thời trang', '👗👟', ['cat' => 'Thời trang']],
        ['Giảm đến 35%', 'đồ gia dụng', '☕🍳', ['cat' => 'Gia dụng']],
        ['Giảm đến 30%', 'làm đẹp', '💄🌸', ['cat' => 'Làm đẹp']],
    ];
    $cardsData = [
        ['Đồ điện tử nổi bật', 'Điện tử', [['🎧', 'Tai nghe'], ['🔊', 'Loa'], ['🖱️', 'Chuột'], ['🔋', 'Sạc']]],
        ['Máy tính & phụ kiện', 'Máy tính', [['💻', 'Laptop'], ['🖥️', 'Màn hình'], ['💾', 'Ổ cứng'], ['📷', 'Webcam']]],
        ['Khỏe để rèn luyện', 'Thể thao', [['🧘', 'Thảm'], ['🏋️', 'Tạ'], ['🚲', 'Xe đạp'], ['⚽', 'Bóng']]],
        ['Thời trang dưới 300k', 'Thời trang', [['👕', 'Áo'], ['🧢', 'Mũ'], ['🕶️', 'Kính'], ['🎒', 'Balo']]],
        ['Khám phá căn bếp', 'Gia dụng', [['🍳', 'Nồi chiên'], ['☕', 'Máy pha'], ['🔪', 'Bộ dao'], ['🍶', 'Bình giữ nhiệt']]],
        ['Làm đẹp rạng ngời', 'Làm đẹp', [['☀️', 'Kem chống nắng'], ['💄', 'Son'], ['🌸', 'Nước hoa'], ['💨', 'Máy sấy']]],
        ['Bạn bốn chân', 'Thú cưng', [['🐕', 'Thức ăn cho chó'], ['🐈', 'Cát vệ sinh'], ['🏠', 'Nhà cho mèo'], ['🐠', 'Bể cá']]],
        ['Thực phẩm ngon', 'Thực phẩm', [['🫘', 'Cà phê rang'], ['🍵', 'Trà xanh'], ['🍯', 'Mật ong'], ['🍫', 'Socola']]],
    ];
    $row = function ($t, $sql, $args, $link) use ($db) {
        $st = $db->prepare($sql . ' LIMIT 12'); $st->execute($args);
        echo '<section class="band rv"><h2><a href="' . e($link) . '">' . e($t) . ' ›</a></h2><div class="rowwrap"><div class="row">';
        foreach ($st->fetchAll() as $p) mini($p);
        echo '</div><button class="nx" aria-label="Tiếp">›</button></div></section>';
    };
    $cards = function ($list) use ($db) {
        echo '<div class="cc">';
        foreach ($list as $i => [$t, $c, $tiles]) {
            $u = '?cat=' . urlencode($c);
            echo '<section class="ccard rv" style="--i:' . $i . '"><h3><a href="' . $u . '">' . e($t) . '</a><a href="' . $u . '">›</a></h3><div class="tiles">';
            foreach ($tiles as [$ic, $k]) {
                $find = $db->prepare('SELECT * FROM products WHERE category = ? AND name LIKE ? ORDER BY sold DESC LIMIT 1');
                $find->execute([$c, '%' . $k . '%']);
                $product = $find->fetch();
                // Không gắn ảnh của sản phẩm khác nếu không tìm thấy mặt hàng tương ứng.
                echo '<a class="tile" href="?q=' . urlencode($k) . '"><div class="pic">'
                    . ($product ? pic($product) : '<span class="no-photo">' . e($ic) . '<small>Ảnh đang cập nhật</small></span>') . '</div>' . e($k) . '</a>';
            }
            echo '</div></section>';
        }
        echo '</div>';
    };
?>
<div class="rowwrap"><div class="promos">
  <?php foreach ($promos as $p): ?>
  <?php $pc = $p[3]['cat'] ?? ''; $ps = $pc !== '' ? $db->prepare('SELECT * FROM products WHERE category = ? ORDER BY sold DESC LIMIT 1') : $db->prepare('SELECT * FROM products ORDER BY sold DESC LIMIT 1'); $ps->execute($pc !== '' ? [$pc] : []); $promoProduct = $ps->fetch(); ?>
  <a class="promo" href="?<?= e(http_build_query($p[3])) ?>"><span>Săn deal sớm ngay</span><h3><?= e($p[0]) ?><br><?= e($p[1]) ?></h3><div class="art"><?php if ($promoProduct) echo pic($promoProduct); else echo e($p[2]); ?></div></a>
  <?php endforeach; ?>
</div><button class="nx" aria-label="Tiếp">›</button></div>
<?php
    $cn = $db->query("SELECT category, COUNT(*) FROM products GROUP BY category")->fetchAll(PDO::FETCH_KEY_PAIR);
    echo '<section class="allcats rv"><h2>Mua sắm theo danh mục</h2><div class="ctiles">';
    foreach ($CATS as $n => $ic) {
        $cs = $db->prepare('SELECT * FROM products WHERE category = ? ORDER BY sold DESC LIMIT 1'); $cs->execute([$n]); $cp = $cs->fetch();
        echo '<a class="ctile" href="?cat=' . urlencode($n) . '"><span class="catphoto">' . ($cp ? pic($cp) : e($ic)) . '</span><b>' . e($n) . '</b><small>' . ($cn[$n] ?? 0) . ' sản phẩm</small></a>';
    }
    echo '</div></section>';
    $row('Ưu đãi hàng mới về', 'SELECT * FROM products ORDER BY id DESC', [], '?all=1');
    $cards(array_slice($cardsData, 0, 4));
    $row('Ưu đãi đồ chơi & trò chơi', 'SELECT * FROM products WHERE category = ? ORDER BY old_price DESC', ['Đồ chơi'], '?cat=' . urlencode('Đồ chơi'));
    $row('Quà tặng sớm cho mùa lễ hội', 'SELECT * FROM products WHERE old_price > 0 ORDER BY (old_price - price) * 100.0 / old_price DESC', [], '?all=1');
    $cards(array_slice($cardsData, 4));
    $row('Sản phẩm bán chạy nhất', 'SELECT * FROM products ORDER BY sold DESC', [], '?sort=hot');
    foreach (['Thể thao' => 'Thể thao & vận động', 'Du lịch & Dã ngoại' => 'Du lịch & dã ngoại', 'Mẹ & Bé' => 'Mẹ & bé', 'Game & Giải trí' => 'Game & giải trí', 'Nhạc cụ' => 'Nhạc cụ', 'Nội thất' => 'Nội thất & trang trí', 'Sức khỏe' => 'Sức khỏe', 'Ô tô & Xe máy' => 'Ô tô & xe máy'] as $c => $t)
        $row($CATS[$c] . ' ' . $t, 'SELECT * FROM products WHERE category = ? ORDER BY sold DESC', [$c], '?cat=' . urlencode($c));
?>
<p class="more"><a class="btn" href="?all=1">Xem tất cả sản phẩm</a></p>
<?php
    $h = ob_get_clean(); echo $h;
else:
    $sort = $_GET['sort'] ?? 'new';
    $orders = ['new' => 'id DESC', 'hot' => 'sold DESC', 'asc' => 'price ASC', 'desc' => 'price DESC'];
    $w = ' WHERE 1'; $a = [];
    if ($q !== '') { $w .= ' AND name LIKE ?'; $a[] = "%$q%"; }
    if ($curCat !== '') { $w .= ' AND category = ?'; $a[] = $curCat; }
    $per = 20;
    $st = $db->prepare("SELECT COUNT(*) FROM products$w"); $st->execute($a);
    $total = (int)$st->fetchColumn();
    $pages = max(1, (int)ceil($total / $per));
    $page = min($pages, max(1, (int)($_GET['page'] ?? 1)));
    $st = $db->prepare("SELECT * FROM products$w ORDER BY " . ($orders[$sort] ?? $orders['new']) . " LIMIT $per OFFSET " . (($page - 1) * $per));
    $st->execute($a);
    $items = $st->fetchAll();
    $link = fn($n) => '?' . http_build_query(array_merge($_GET, ['page' => $n]));
?>
<?php if ($curCat !== '' && $q === ''): ?>
<section class="cathero"><span><?= $CATS[$curCat] ?? '🛍️' ?></span><div><h1><?= e($curCat) ?></h1><p>Tất cả sản phẩm trong danh mục · <?= $total ?> sản phẩm</p></div></section>
<?php else: ?><h1 class="ttl"><?= $q !== '' ? 'Kết quả cho "' . e($q) . '"' : ($curCat !== '' ? e($curCat) : 'Tất cả sản phẩm') ?></h1>
<?php endif; ?>
<form class="bar" method="get">
  <input type="hidden" name="q" value="<?= e($q) ?>"><input type="hidden" name="cat" value="<?= e($curCat) ?>"><input type="hidden" name="all" value="1">
  <span><?= $total ?> sản phẩm</span>
  <select name="sort" onchange="this.form.submit()">
    <option value="new" <?= $sort === 'new' ? 'selected' : '' ?>>Mới nhất</option>
    <option value="hot" <?= $sort === 'hot' ? 'selected' : '' ?>>Bán chạy</option>
    <option value="asc" <?= $sort === 'asc' ? 'selected' : '' ?>>Giá thấp đến cao</option>
    <option value="desc" <?= $sort === 'desc' ? 'selected' : '' ?>>Giá cao đến thấp</option>
  </select>
</form>
<?php if (!$items): ?><p class="empty">Không tìm thấy sản phẩm nào. Hãy thử từ khóa khác.</p><?php endif; ?>
<div class="grid"><?php foreach ($items as $i => $p) card($p, $i); ?></div>
<?php if ($pages > 1): ?>
<nav class="pager"><?php for ($n = 1; $n <= $pages; $n++): ?><a href="<?= e($link($n)) ?>" class="<?= $n === $page ? 'on' : '' ?>"><?= $n ?></a><?php endfor; ?></nav>
<?php endif; endif; require 'footer.php'; ?>
