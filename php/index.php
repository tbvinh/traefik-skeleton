<?php
// Thiết lập múi giờ (Ví dụ: Việt Nam)
date_default_timezone_set('Asia/Ho_Chi_Minh'); 

// Lấy tên Container (hostname) từ biến môi trường
$container_name = gethostname();

echo "## 🚀 Ứng dụng PHP-FPM đang hoạt động";
echo "<br>"; 
echo "---";
echo "<br>";

echo "Hello từ PHP-FPM!";
echo "<br>"; 

// Hiển thị giờ hiện tại trên Server (đã đặt múi giờ)
echo "⏰ Giờ hiện tại trên server là: **" . date('H:i:s Y-m-d') . "**"; 
echo "<br>";

// Hiển thị tên Container đang xử lý (sẽ luân phiên thay đổi)
echo "🐳 Yêu cầu này đang được xử lý bởi Container: **" . htmlspecialchars($container_name) . "**";

?>
