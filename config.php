<?php
if (PHP_SAPI !== 'cli' && extension_loaded('zlib') && !ini_get('zlib.output_compression')) ob_start('ob_gzhandler');
if (PHP_SAPI !== 'cli') session_start();
$db = new PDO('sqlite:' . __DIR__ . '/ezybuy.db', null, null, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_PERSISTENT => true,
]);
$db->exec("PRAGMA journal_mode=WAL; PRAGMA synchronous=NORMAL;");

// Chỉ tạo/nâng cấp CSDL khi cần (nhanh hơn mỗi lần tải trang)
if ($db->query("PRAGMA user_version")->fetchColumn() < 2) {
    $db->exec("CREATE TABLE IF NOT EXISTS users(id INTEGER PRIMARY KEY, name TEXT, email TEXT UNIQUE, password TEXT);
CREATE TABLE IF NOT EXISTS orders(id INTEGER PRIMARY KEY, user_id INTEGER, total INTEGER, address TEXT, created TEXT DEFAULT CURRENT_TIMESTAMP);
CREATE TABLE IF NOT EXISTS order_items(order_id INTEGER, product_id INTEGER, qty INTEGER, price INTEGER);
DROP TABLE IF EXISTS products;
CREATE TABLE products(id INTEGER PRIMARY KEY, name TEXT, category TEXT, price INTEGER, old_price INTEGER, icon TEXT, description TEXT, rating REAL, sold INTEGER);
CREATE INDEX idx_cat ON products(category); CREATE INDEX idx_price ON products(price); CREATE INDEX idx_sold ON products(sold);");
    $seed = <<<'S'
Tai nghe không dây|Điện tử|890000|1190000|🎧|Bluetooth 5.3, pin 30 giờ, chống ồn chủ động.|4.7|1820
Bàn phím cơ|Điện tử|1250000|0|⌨️|Switch đỏ, đèn nền RGB, kết nối USB-C.|4.6|940
Đồng hồ thông minh|Điện tử|2490000|2990000|⌚|Đo nhịp tim, GPS, chống nước 5ATM.|4.5|760
Loa Bluetooth mini|Điện tử|590000|0|🔊|Âm thanh 360°, pin 12 giờ.|4.4|1310
Chuột gaming|Điện tử|420000|520000|🖱️|DPI 16000, 7 nút lập trình được.|4.6|2100
Sạc dự phòng 20000mAh|Điện tử|450000|0|🔋|Sạc nhanh 22.5W, 2 cổng USB.|4.8|3400
Áo thun cotton|Thời trang|199000|250000|👕|100% cotton, form rộng, nhiều màu.|4.5|5200
Giày chạy bộ|Thời trang|1090000|0|👟|Đế êm, thoáng khí, nhẹ 260g.|4.6|1450
Balo laptop|Thời trang|450000|590000|🎒|Ngăn laptop 15.6 inch, chống nước.|4.7|2300
Mũ lưỡi trai|Thời trang|120000|0|🧢|Vải kaki, khóa chỉnh kích cỡ.|4.3|980
Kính râm chống UV|Thời trang|260000|350000|🕶️|Tròng phân cực, chống UV400.|4.4|870
Áo khoác gió|Thời trang|520000|0|🧥|Chống nước nhẹ, gấp gọn tiện lợi.|4.5|1100
Nồi chiên không dầu|Gia dụng|1890000|2290000|🍳|Dung tích 5L, 8 chế độ nấu.|4.8|4100
Máy pha cà phê|Gia dụng|3200000|0|☕|Áp suất 15 bar, vòi đánh sữa.|4.6|520
Đèn bàn LED|Gia dụng|260000|0|💡|3 mức sáng, bảo vệ mắt, sạc USB.|4.5|1900
Bình giữ nhiệt 750ml|Gia dụng|230000|290000|🍶|Giữ nóng 12 giờ, inox 304.|4.7|3600
Máy hút bụi mini|Gia dụng|1450000|0|🧹|Không dây, lực hút mạnh.|4.4|690
Bộ dao nhà bếp|Gia dụng|380000|480000|🔪|5 món, thép không gỉ.|4.6|1250
Sách Lập trình PHP|Sách|175000|0|📘|Từ cơ bản đến xây dựng website thực tế.|4.7|1500
Tiểu thuyết bán chạy|Sách|98000|120000|📖|Tác phẩm được yêu thích nhất năm.|4.6|4800
Bộ bút màu 36 cây|Sách|85000|0|🖍️|Màu tươi, an toàn cho trẻ em.|4.5|2700
Sổ tay bìa da|Sách|110000|0|📓|200 trang, giấy chống lem.|4.6|1900
Sách học tiếng Anh|Sách|150000|190000|📚|Giao tiếp từ cơ bản đến nâng cao.|4.5|3100
Truyện tranh thiếu nhi|Sách|45000|0|📕|Tranh màu, nội dung giáo dục.|4.8|6200
Kem chống nắng SPF50|Làm đẹp|220000|280000|☀️|Không nhờn rít, bảo vệ da cả ngày.|4.7|3900
Son dưỡng môi|Làm đẹp|95000|0|💄|Dưỡng ẩm, mềm môi suốt 8 giờ.|4.5|2800
Sữa rửa mặt|Làm đẹp|165000|0|🫧|Dịu nhẹ, phù hợp mọi loại da.|4.6|3300
Nước hoa 50ml|Làm đẹp|690000|850000|🌸|Hương hoa cỏ, lưu hương lâu.|4.4|720
Máy sấy tóc|Làm đẹp|480000|0|💨|Công suất 1800W, 3 chế độ nhiệt.|4.5|1050
Mặt nạ dưỡng da (hộp 10)|Làm đẹp|140000|180000|🎭|Cấp ẩm, sáng da sau 1 lần dùng.|4.6|4400
Thảm tập yoga|Thể thao|210000|0|🧘|Chống trượt, dày 8mm.|4.6|2200
Tạ tay 5kg (cặp)|Thể thao|350000|420000|🏋️|Bọc cao su, cầm chắc tay.|4.5|860
Bóng đá size 5|Thể thao|180000|0|⚽|Da PU, bền, nảy chuẩn.|4.4|1700
Vợt cầu lông|Thể thao|320000|390000|🏸|Khung carbon nhẹ, kèm bao đựng.|4.5|1240
Xe đạp thể thao|Thể thao|4200000|4800000|🚲|Khung nhôm, 21 tốc độ.|4.7|310
Dây nhảy tốc độ|Thể thao|60000|0|🪢|Điều chỉnh độ dài, tay cầm êm.|4.3|5100
Bộ xếp hình 500 mảnh|Đồ chơi|280000|0|🧩|Phát triển tư duy, an toàn cho bé.|4.7|1600
Gấu bông lớn|Đồ chơi|250000|320000|🧸|Bông mềm mịn, cao 80cm.|4.8|2900
Xe điều khiển từ xa|Đồ chơi|520000|0|🏎️|Pin sạc, chạy 30 phút liên tục.|4.5|980
Bộ cờ vua|Đồ chơi|150000|0|♟️|Bàn gỗ gấp gọn, quân cờ nặng.|4.6|1350
Robot thông minh|Đồ chơi|890000|1090000|🤖|Điều khiển giọng nói, biết nhảy múa.|4.6|640
Đàn piano mini|Đồ chơi|430000|0|🎹|37 phím, nhiều âm sắc.|4.4|760
S;
    $db->beginTransaction();
    $st = $db->prepare("INSERT INTO products(name,category,price,old_price,icon,description,rating,sold) VALUES(?,?,?,?,?,?,?,?)");
    foreach (explode("\n", trim($seed)) as $l) $st->execute(explode('|', $l));
    $db->commit();
    $db->exec("PRAGMA user_version=2");
}
if ($db->query("PRAGMA user_version")->fetchColumn() < 3) {
    $more = <<<'S'
Laptop văn phòng 15.6 inch|Máy tính|12900000|14900000|💻|Core i5, RAM 16GB, SSD 512GB.|4.6|420
Màn hình 27 inch 2K|Máy tính|4900000|5900000|🖥️|Tần số quét 100Hz, viền mỏng.|4.7|610
Ổ cứng di động 1TB|Máy tính|1350000|0|💾|USB 3.2, nhỏ gọn, bền bỉ.|4.6|1300
Giá đỡ laptop nhôm|Máy tính|290000|350000|📐|Gấp gọn, tản nhiệt tốt.|4.5|1700
Webcam Full HD|Máy tính|550000|0|📷|1080p, micro khử ồn.|4.4|880
Máy tính bảng 10 inch|Máy tính|4200000|4900000|📱|Pin 7000mAh, màn hình sắc nét.|4.5|540
Thức ăn cho chó 5kg|Thú cưng|320000|0|🐕|Giàu đạm, tốt cho bộ lông.|4.7|2400
Cát vệ sinh cho mèo|Thú cưng|130000|160000|🐈|Vón cục nhanh, khử mùi.|4.6|3100
Đồ chơi cho thú cưng|Thú cưng|75000|0|🦴|Cao su bền, an toàn.|4.5|1900
Nhà cho mèo|Thú cưng|420000|520000|🏠|Êm ái, dễ vệ sinh.|4.6|760
Vòng cổ có chuông|Thú cưng|55000|0|🔔|Điều chỉnh được, nhiều màu.|4.3|2100
Bể cá mini|Thú cưng|380000|0|🐠|Kèm đèn LED và máy lọc.|4.4|430
Cà phê rang xay 500g|Thực phẩm|185000|220000|🫘|Hương thơm đậm, rang mộc.|4.8|4200
Trà xanh hộp 100g|Thực phẩm|95000|0|🍵|Thơm mát, thanh nhiệt.|4.5|2000
Mật ong rừng 500ml|Thực phẩm|240000|290000|🍯|Nguyên chất, không pha.|4.7|1800
Hạt điều rang muối|Thực phẩm|160000|0|🥜|Giòn bùi, hộp 500g.|4.6|3300
Bánh quy bơ|Thực phẩm|70000|0|🍪|Thơm bơ, hộp thiếc đẹp.|4.6|2600
Socola đen 70%|Thực phẩm|120000|150000|🍫|Vị đậm, ít ngọt.|4.5|1500
Ghế công thái học|Văn phòng|2100000|2600000|🪑|Tựa lưng lưới, chỉnh độ cao.|4.6|520
Bàn làm việc gấp|Văn phòng|890000|0|🧰|Gấp gọn, chịu lực 40kg.|4.5|480
Kệ tài liệu 3 tầng|Văn phòng|340000|0|🗄️|Khung thép, lắp nhanh.|4.4|610
Bút bi cao cấp (hộp 10)|Văn phòng|90000|0|🖊️|Mực trơn, nét đều.|4.6|3800
Máy tính Casio|Văn phòng|250000|290000|🧮|Màn hình lớn, tính chính xác.|4.7|1600
Giấy in A4 (ream)|Văn phòng|75000|0|📄|Trắng mịn, định lượng 70gsm.|4.5|5200
S;
    $db->beginTransaction();
    $st = $db->prepare("INSERT INTO products(name,category,price,old_price,icon,description,rating,sold) VALUES(?,?,?,?,?,?,?,?)");
    foreach (explode("\n", trim($more)) as $l) $st->execute(explode('|', $l));
    $db->commit();
    $db->exec("PRAGMA user_version=3");
}
if ($db->query("PRAGMA user_version")->fetchColumn() < 4) {
    // Dữ liệu gọn: Danh mục|icon|tên|giá (nghìn đồng). Giá gốc, đánh giá, lượt bán, mô tả được sinh tự động.
    $gen = <<<'S'
Thể thao|🏀|Bóng rổ size 7|290
Thể thao|🏐|Bóng chuyền thi đấu|190
Thể thao|🎾|Vợt tennis|850
Thể thao|🏓|Bộ bóng bàn|240
Thể thao|🥊|Găng tay đấm bốc|320
Thể thao|🏊|Kính bơi chống hơi nước|130
Thể thao|🩳|Quần short thể thao|160
Thể thao|🧦|Tất thể thao (5 đôi)|90
Thể thao|🥾|Giày leo núi|1250
Thể thao|🛹|Ván trượt|690
Thể thao|🏃|Đai chạy bộ đựng điện thoại|110
Du lịch & Dã ngoại|⛺|Lều cắm trại 4 người|1450
Du lịch & Dã ngoại|🧳|Vali kéo 24 inch|980
Du lịch & Dã ngoại|🎒|Balo du lịch 40L|650
Du lịch & Dã ngoại|🔦|Đèn pin siêu sáng|180
Du lịch & Dã ngoại|🛏️|Túi ngủ gọn nhẹ|420
Du lịch & Dã ngoại|🪑|Ghế xếp dã ngoại|290
Du lịch & Dã ngoại|🔥|Bếp gas mini|260
Du lịch & Dã ngoại|😴|Gối cổ chữ U|120
Mẹ & Bé|🍼|Bình sữa chống đầy hơi|150
Mẹ & Bé|👶|Tã dán (bịch 60 miếng)|280
Mẹ & Bé|🛒|Xe đẩy gấp gọn|1650
Mẹ & Bé|🧼|Sữa tắm gội trẻ em|135
Mẹ & Bé|👗|Bộ quần áo sơ sinh|190
Mẹ & Bé|🛏️|Nôi điện tự động|2300
Mẹ & Bé|🥣|Bộ chén ăn dặm|110
Mẹ & Bé|🌡️|Nhiệt kế điện tử trẻ em|160
Sức khỏe|💊|Vitamin C 1000mg|180
Sức khỏe|🩺|Máy đo huyết áp|650
Sức khỏe|🌡️|Nhiệt kế hồng ngoại|290
Sức khỏe|😷|Khẩu trang y tế (hộp 50)|65
Sức khỏe|🧴|Nước rửa tay khô|45
Sức khỏe|⚖️|Cân sức khỏe điện tử|320
Sức khỏe|💆|Máy massage cầm tay|790
Sức khỏe|🩹|Bộ sơ cứu gia đình|210
Ô tô & Xe máy|🪖|Mũ bảo hiểm 3/4|350
Ô tô & Xe máy|🚗|Camera hành trình|1100
Ô tô & Xe máy|🧽|Bộ dụng cụ rửa xe|280
Ô tô & Xe máy|📱|Giá đỡ điện thoại trên xe|120
Ô tô & Xe máy|🛞|Bơm lốp mini|550
Ô tô & Xe máy|🔋|Kích bình ắc quy|1450
Ô tô & Xe máy|💧|Nước rửa kính ô tô|60
Ô tô & Xe máy|🧥|Áo mưa đi phượt|140
Nhạc cụ|🎸|Đàn guitar acoustic|1250
Nhạc cụ|🎹|Đàn organ 61 phím|2400
Nhạc cụ|🥁|Trống cajon|1190
Nhạc cụ|🎤|Micro karaoke không dây|690
Nhạc cụ|🎻|Đàn violin 4/4|1650
Nhạc cụ|🪈|Sáo trúc|90
Nhạc cụ|🎧|Tai nghe kiểm âm|820
Nhạc cụ|🪕|Đàn ukulele|480
Game & Giải trí|🎮|Tay cầm chơi game|690
Game & Giải trí|🕹️|Máy chơi game cầm tay|1250
Game & Giải trí|🎲|Bộ board game gia đình|320
Game & Giải trí|🃏|Bộ bài Poker|65
Game & Giải trí|🎯|Bộ phi tiêu nam châm|110
Game & Giải trí|🥽|Kính thực tế ảo|2100
Game & Giải trí|🧊|Rubik 3x3|60
Game & Giải trí|📺|Máy chiếu mini|2790
Vườn & Cây cảnh|🪴|Chậu cây trang trí|120
Vườn & Cây cảnh|🌱|Hạt giống rau sạch (bộ 10)|60
Vườn & Cây cảnh|🌵|Cây sen đá mini|45
Vườn & Cây cảnh|🚿|Bình tưới cây|85
Vườn & Cây cảnh|✂️|Kéo cắt tỉa cành|110
Vườn & Cây cảnh|🧤|Găng tay làm vườn|40
Vườn & Cây cảnh|🪱|Phân hữu cơ 5kg|95
Vườn & Cây cảnh|💡|Đèn trồng cây LED|240
Dụng cụ & Sửa chữa|🔧|Bộ cờ lê 40 món|420
Dụng cụ & Sửa chữa|🪛|Bộ tua vít đa năng|180
Dụng cụ & Sửa chữa|🔨|Búa đa năng|110
Dụng cụ & Sửa chữa|🪚|Cưa tay|150
Dụng cụ & Sửa chữa|⚡|Máy khoan pin|1250
Dụng cụ & Sửa chữa|📏|Thước cuộn 5m|55
Dụng cụ & Sửa chữa|🧰|Hộp đồ nghề|290
Dụng cụ & Sửa chữa|🔌|Ổ cắm kéo dài|160
Nội thất|🛋️|Sofa mini 2 chỗ|3900
Nội thất|🛌|Nệm cao su|4800
Nội thất|🪞|Gương trang điểm|420
Nội thất|🚪|Tủ quần áo vải|650
Nội thất|🏮|Đèn thả trần|580
Nội thất|🖼️|Tranh treo tường|250
Nội thất|🪟|Rèm cửa cách nhiệt|380
Nội thất|🧶|Thảm trải sàn|450
Trang sức|💍|Nhẫn bạc|450
Trang sức|📿|Vòng tay đá|280
Trang sức|💎|Bông tai ngọc trai|390
Trang sức|⛓️|Dây chuyền vàng 18K|3200
Trang sức|⌚|Đồng hồ nữ dây da|1250
Trang sức|🧿|Mặt dây chuyền phong thủy|220
Trang sức|👑|Kẹp tóc đính đá|130
Trang sức|🎁|Hộp đựng trang sức|180
Điện tử|📺|Smart TV 43 inch|6900
Điện tử|📸|Máy ảnh mini|3200
Điện tử|🎙️|Micro thu âm|490
Điện tử|🔌|Củ sạc nhanh 65W|350
Điện tử|🏠|Camera an ninh Wi-Fi|520
Máy tính|🖨️|Máy in laser|3400
Máy tính|🛜|Router Wi-Fi 6|890
Máy tính|💽|Ổ SSD 500GB|1050
Máy tính|🔗|Hub USB-C 7 cổng|390
Máy tính|🖱️|Bàn di chuột cỡ lớn|120
Thời trang|👗|Váy hoa dáng dài|390
Thời trang|👖|Quần jean ống đứng|450
Thời trang|👜|Túi xách nữ|620
Thời trang|🧣|Khăn len|150
Thời trang|👔|Sơ mi công sở|320
Làm đẹp|🧴|Dầu gội thảo dược|140
Làm đẹp|💅|Bộ sơn móng tay|110
Làm đẹp|🪒|Máy cạo râu|450
Làm đẹp|🧼|Xà phòng handmade|60
Gia dụng|🍚|Nồi cơm điện 1.8L|890
Gia dụng|🧊|Tủ lạnh mini|3400
Gia dụng|🥤|Máy xay sinh tố|650
Gia dụng|🧺|Giỏ đựng đồ|90
Gia dụng|🌀|Quạt điều hòa|2100
Thực phẩm|🍜|Mì ăn liền (thùng)|120
Thực phẩm|🫒|Dầu ô liu 500ml|210
Thực phẩm|🥫|Cá hộp (lốc 6)|180
Thực phẩm|🍬|Kẹo dẻo trái cây|55
Thực phẩm|🥛|Sữa hạt dinh dưỡng|135
Sách|📗|Sách kỹ năng sống|120
Sách|📙|Truyện trinh thám|110
Sách|🗒️|Giấy ghi chú|35
Sách|✏️|Bộ bút chì phác thảo|90
Sách|🌍|Từ điển Anh - Việt|180
Văn phòng|🖨️|Máy hủy tài liệu|1350
Văn phòng|📎|Kẹp giấy và ghim (set)|40
Văn phòng|🗓️|Lịch để bàn|55
Văn phòng|🧾|Máy in hóa đơn nhiệt|1650
Đồ chơi|🚂|Tàu hỏa mô hình|380
Đồ chơi|🪁|Diều bay|90
Đồ chơi|🎨|Bộ tô màu nước|120
Đồ chơi|🧱|Bộ lắp ráp xe tải|450
Thú cưng|🐦|Lồng chim|240
Thú cưng|🦮|Dây dắt chó tự cuộn|160
Thú cưng|🥣|Bát ăn tự động|210
Thú cưng|🐟|Thức ăn cá cảnh|35
S;
    $ph = ['hàng chính hãng, giao nhanh toàn quốc.', 'chất lượng cao, bền bỉ, giá tốt.', 'thiết kế hiện đại, dễ sử dụng.', 'được nhiều khách hàng đánh giá cao.'];
    $db->beginTransaction();
    $st = $db->prepare("INSERT INTO products(name,category,price,old_price,icon,description,rating,sold) VALUES(?,?,?,?,?,?,?,?)");
    foreach (explode("\n", trim($gen)) as $l) {
        [$c, $ic, $n, $k] = explode('|', $l);
        $h = crc32($n); $p = $k * 1000;
        $st->execute([$n, $c, $p, $h % 3 ? 0 : (int)(round($p * 1.25 / 1000) * 1000), $ic, "$n — " . $ph[$h % 4], round(4.2 + $h % 7 / 10, 1), 200 + $h % 4800]);
    }
    $db->commit();
    $db->exec("PRAGMA user_version=4");
}
if ($db->query("PRAGMA user_version")->fetchColumn() < 5) {
    $db->exec("ALTER TABLE products ADD COLUMN image TEXT");
    $db->exec("PRAGMA user_version=5");
}
$CATS = ['Điện tử'=>'🎧','Máy tính'=>'💻','Thời trang'=>'👕','Làm đẹp'=>'💄','Gia dụng'=>'🍳','Nội thất'=>'🛋️','Thực phẩm'=>'🍯','Sách'=>'📚','Văn phòng'=>'🪑','Thể thao'=>'🏋️','Du lịch & Dã ngoại'=>'⛺','Mẹ & Bé'=>'🍼','Đồ chơi'=>'🧸','Thú cưng'=>'🐕','Sức khỏe'=>'💊','Ô tô & Xe máy'=>'🚗','Nhạc cụ'=>'🎸','Game & Giải trí'=>'🎮','Vườn & Cây cảnh'=>'🪴','Dụng cụ & Sửa chữa'=>'🔧','Trang sức'=>'💍'];

function e($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
function money($n) { return number_format($n, 0, ',', '.') . ' ₫'; }
function cart_count() { return array_sum($_SESSION['cart'] ?? []); }
function asset($f) { return "assets/$f?v=" . filemtime(__DIR__ . "/assets/$f"); }

function card($p, $i = 0) {
    $sale = $p['old_price'] ? '<span class="badge">-' . round(100 - $p['price'] * 100 / $p['old_price']) . '%</span>' : '';
    $old = $p['old_price'] ? '<s>' . money($p['old_price']) . '</s>' : '';
    $u = 'product.php?id=' . $p['id'];
    echo '<article class="card rv" style="--i:' . ($i % 12) . '">' . $sale
        . '<a class="pic" href="' . $u . '">' . pic($p) . '</a>'
        . '<h3><a href="' . $u . '">' . e($p['name']) . '</a></h3>'
        . '<div class="meta">★ ' . $p['rating'] . ' · Đã bán ' . number_format($p['sold'], 0, ',', '.') . '</div>'
        . '<div class="price">' . money($p['price']) . $old . '</div>'
        . '<form method="post" action="cart.php" data-ajax><input type="hidden" name="a" value="add"><input type="hidden" name="id" value="' . $p['id'] . '"><input type="hidden" name="qty" value="1"><button class="btn sm">Thêm vào giỏ</button></form></article>';
}

function mini($p) {
    $off = $p['old_price'] ? round(100 - $p['price'] * 100 / $p['old_price']) : 0;
    echo '<a class="mini" href="product.php?id=' . $p['id'] . '"><div class="pic">' . pic($p) . '</div><h4>' . e($p['name']) . '</h4>'
        . '<div class="tag">' . ($off ? '<b>Giảm ' . $off . '%</b><span>Kết thúc sau <i class="cd">--:--:--</i></span>' : '<span>Đã bán ' . number_format($p['sold'], 0, ',', '.') . '</span>') . '</div>'
        . '<div class="price">' . money($p['price']) . '</div>' . ($off ? '<s>' . money($p['old_price']) . '</s>' : '') . '</a>';
}

// Ảnh sản phẩm: chỉ dùng ảnh riêng tải lên hoặc ảnh khớp tên đầy đủ.
// Không suy đoán ảnh từ danh mục vì một ảnh chung có thể không đúng mặt hàng.
function pic($p) {
    $nameRaw = (string)($p['name'] ?? 'Sản phẩm');
    $name = e($nameRaw);
    $file = (string)($p['image'] ?? '');

    if ($file !== '' && is_file(__DIR__ . '/' . $file)) {
        $src = e($file) . '?v=' . filemtime(__DIR__ . '/' . $file);
        return '<img src="' . $src . '" alt="' . $name . '" loading="lazy" decoding="async">';
    }

    // Danh sách khớp theo tên đầy đủ, không dùng contains/fuzzy matching.
    $exactPhotos = [
        'Tai nghe không dây' => 'headphones.jpg',
        'Tai nghe kiểm âm' => 'headphones.jpg',
        'Laptop văn phòng 15.6 inch' => 'laptop.jpg',
        'Áo thun cotton' => 'tshirt.jpg',
        'Son dưỡng môi' => 'lipstick.jpg',
        'Nồi chiên không dầu' => 'air-fryer.jpg',
        'Sofa mini 2 chỗ' => 'sofa.jpg',
        'Mật ong rừng 500ml' => 'honey.jpg',
        'Sách Lập trình PHP' => 'books.jpg',
        'Tiểu thuyết bán chạy' => 'books.jpg',
        'Sách học tiếng Anh' => 'books.jpg',
        'Truyện tranh thiếu nhi' => 'books.jpg',
        'Ghế công thái học' => 'office-chair.jpg',
        'Giày chạy bộ' => 'running-shoes.jpg',
        'Lều cắm trại 4 người' => 'tent.jpg',
        'Bình sữa chống đầy hơi' => 'baby-bottle.jpg',
        'Gấu bông lớn' => 'teddy-bear.jpg',
        'Thức ăn cho chó 5kg' => 'pet-food.jpg',
        'Máy đo huyết áp' => 'blood-pressure-monitor.jpg',
        'Mũ bảo hiểm 3/4' => 'motorcycle-helmet.jpg',
    ];

    if (isset($exactPhotos[$nameRaw])) {
        $path = 'assets/img/' . $exactPhotos[$nameRaw];
        if (is_file(__DIR__ . '/' . $path)) {
            return '<img src="' . e($path) . '?v=' . filemtime(__DIR__ . '/' . $path) . '" alt="' . $name . '" loading="lazy" decoding="async">';
        }
    }

    // Chưa có ảnh khớp chính xác: hiển thị trạng thái rõ ràng, không thay bằng ảnh sai.
    $icon = e($p['icon'] ?? '📦');
    return '<span class="no-photo" role="img" aria-label="Chưa có ảnh riêng cho ' . $name . '">' . $icon . '<small>Ảnh đang cập nhật</small></span>';
}

// Lưu ảnh (thu nhỏ tối đa 640px, nén JPG) và trả về đường dẫn
function save_photo($data, $id) {
    if (!$data || !@getimagesizefromstring($data) || strlen($data) > 8e6) return null;
    @mkdir(__DIR__ . '/assets/img', 0777, true);
    $f = "assets/img/$id.jpg";
    if (function_exists('imagecreatefromstring') && ($im = @imagecreatefromstring($data))) {
        if (imagesx($im) > 640) $im = imagescale($im, 640);
        $bg = imagecreatetruecolor(imagesx($im), imagesy($im));
        imagefill($bg, 0, 0, 0xFFFFFF);
        imagecopy($bg, $im, 0, 0, 0, 0, imagesx($im), imagesy($im));
        imagejpeg($bg, __DIR__ . "/$f", 82);
    } else file_put_contents(__DIR__ . "/$f", $data);
    return $f;
}
