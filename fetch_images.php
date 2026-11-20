<?php
// Tự động tìm và tải ảnh thật cho sản phẩm từ Pixabay (ảnh miễn phí, dùng được cho mục đích thương mại).
// Cách dùng (chạy trong thư mục web của bạn):  php fetch_images.php PIXABAY_API_KEY [số_ảnh_tối_đa]
// Hoặc đặt biến PIXABAY_API_KEY trong phiên PowerShell rồi chạy: php fetch_images.php
// Có thể chạy lại nhiều lần: chỉ xử lý sản phẩm chưa có ảnh.
// Từ khóa chính xác cho danh mục có sẵn trong keywords.php.
// Tùy chọn: tạo file keywords.txt, mỗi dòng "Tên sản phẩm|từ khóa tiếng Anh" để ghi đè từ khóa đó.
if (PHP_SAPI !== 'cli') exit('Chạy bằng dòng lệnh: php fetch_images.php PIXABAY_API_KEY');
require_once __DIR__ . '/config.php';
set_time_limit(0);
$key = $argv[1] ?? (getenv('PIXABAY_API_KEY') ?: '');
if ($key === '') exit("Thiếu API key. Đăng nhập Pixabay, mở pixabay.com/api/docs/ để xem key, rồi đặt biến PIXABAY_API_KEY hoặc chạy: php fetch_images.php KEY_CUA_BAN\n");
$max = (int)($argv[2] ?? 203);
$get = fn($url) => @file_get_contents($url, false, stream_context_create(['http' => ['timeout' => 20, 'header' => "User-Agent: EzyBuy/1.0\r\n"]]));
$keywords = is_file(__DIR__ . '/keywords.php') ? require __DIR__ . '/keywords.php' : [];
$ov = [];
if (is_file(__DIR__ . '/keywords.txt'))
    foreach (file(__DIR__ . '/keywords.txt', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $l)
        if (count($x = explode('|', $l, 2)) == 2) $ov[trim($x[0])] = trim($x[1]);

$rows = $db->query("SELECT id, name FROM products WHERE image IS NULL OR image = '' ORDER BY id")->fetchAll();
$up = $db->prepare("UPDATE products SET image = ? WHERE id = ?");
$ok = $n = 0;
foreach ($rows as $p) {
    if ($n++ >= $max) break;
    // Ưu tiên từ khóa tiếng Anh được viết riêng cho từng mặt hàng; chỉ rơi về tên tiếng Việt nếu thiếu.
    // keywords.txt (nếu có) được ưu tiên cao nhất để quản trị viên sửa những kết quả cần tinh chỉnh.
    $term = $ov[$p['name']] ?? $keywords[$p['name']] ?? '';
    $tries = $term !== ''
        ? [[$term, 'en'], [$p['name'], 'vi']]
        : [[$p['name'], 'vi'], [implode(' ', array_slice(explode(' ', $p['name']), 0, 2)), 'vi']];
    $src = null;
    foreach ($tries as [$q, $lg]) {
        $js = json_decode($get('https://pixabay.com/api/?image_type=photo&safesearch=true&per_page=3&lang=' . $lg . '&key=' . urlencode($key) . '&q=' . urlencode($q)), true);
        if ($js === null) exit("Không gọi được Pixabay: hãy kiểm tra API key, kết nối mạng, hoặc chạy lại sau ít phút.\n");
        if ($src = $js['hits'][0]['webformatURL'] ?? null) break;
        usleep(300000);
    }
    $f = $src ? save_photo($get($src), $p['id']) : null;   // tải về máy chủ của bạn, không dùng link trực tiếp
    if ($f) { $up->execute([$f, $p['id']]); $ok++; }
    echo ($f ? '✔' : '✖') . " #{$p['id']} {$p['name']} → $q\n";
    // Giữ dưới giới hạn mặc định 100 yêu cầu/phút của Pixabay, kể cả khi phải thử từ khóa thứ hai.
    usleep(500000);
}
@unlink(sys_get_temp_dir() . '/ezybuy_home_v4.html');
echo "Xong: $ok/" . min($n, count($rows)) . " sản phẩm đã có ảnh. Hãy tải lại trang web (Ctrl + F5).\n";
