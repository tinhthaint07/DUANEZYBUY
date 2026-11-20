# EzyBuy — website thương mại điện tử (PHP + SQLite)

Chạy: mở thư mục trong VS Code → Terminal:
    php -S localhost:8000
Truy cập http://localhost:8000
Yêu cầu PHP 8+ có bật extension pdo_sqlite. CSDL ezybuy.db tự tạo ở lần chạy đầu.

## Ảnh sản phẩm
- Ảnh riêng theo mã sản phẩm được ưu tiên, sau đó là ảnh khớp chính xác theo tên; website không dùng ảnh chung theo danh mục để tránh lệch mặt hàng.
- Nếu chưa có ảnh đúng, website hiển thị nhãn “Ảnh đang cập nhật” thay vì dùng ảnh của một sản phẩm khác.
- Trang chủ, danh mục, chi tiết sản phẩm và giỏ hàng dùng chung hàm `pic()` để bảo đảm ảnh đồng nhất.
- Quản trị viên có thể tải ảnh đúng từng mặt hàng tại `images.php` (chỉ mở trên localhost). Ảnh riêng theo ID được ưu tiên và hiển thị đồng bộ ở mọi trang.
- Để lấy ảnh riêng theo từng sản phẩm hàng loạt, tạo API key Pixabay rồi chạy `php fetch_images.php PIXABAY_API_KEY 203`. Tập lệnh dùng các từ khóa riêng trong `keywords.php`; có thể ghi đè từng từ khóa bằng `keywords.txt` trước khi chạy.
