<?php
/**
 * setup/user.php — Test nhanh REST API authentication trước khi chạy tool.
 *
 * Xác minh 2 điều cùng lúc:
 *   1. Application Password đúng (username + password khớp).
 *   2. mu-plugin rest-auth-header.php đã hoạt động — tức LiteSpeed/Apache
 *      không còn strip header Authorization trên site này.
 *
 * Không phụ thuộc WordPress hay bất kỳ package nào ngoài cURL (mặc định có
 * sẵn trong PHP) — chạy được ngay cả khi wp-automation/ chưa cài xong.
 *
 * Cách chạy:
 *   php setup/user.php <site_url> <username> "<application_password>"
 *
 * Ví dụ:
 *   php setup/user.php https://dienmaykimbien.com.vn admin "abcd 1234 efgh 5678 ijkl 9012"
 *
 * Kết quả mong đợi:
 *   HTTP 200 + in ra id/username lấy từ GET /wp-json/wp/v2/users/me
 *
 * Nếu lỗi 401: xem phần "Xử lý sự cố" trong setup/README.md.
 */

if ($argc < 4) {
    fwrite(STDERR, "Cách dùng: php user.php <site_url> <username> <application_password>\n");
    fwrite(STDERR, "Ví dụ:    php user.php https://example.com admin \"abcd 1234 efgh 5678\"\n");
    exit(1);
}

[$script, $siteUrl, $username, $appPassword] = $argv;
$siteUrl = rtrim($siteUrl, '/');
$endpoint = $siteUrl . '/wp-json/wp/v2/users/me';
$basic = base64_encode($username . ':' . $appPassword);

$ch = curl_init($endpoint);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => [
        'Authorization: Basic ' . $basic,
        // Header dự phòng — mu-plugin rest-auth-header.php sẽ đọc header này
        // để dựng lại Authorization nếu server (LiteSpeed) đã strip mất nó.
        'X-Authorization: Basic ' . $basic,
    ],
    CURLOPT_TIMEOUT => 15,
]);
$body = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$err = curl_error($ch);
curl_close($ch);

echo "GET $endpoint\n";

if ($err) {
    fwrite(STDERR, "❌ Lỗi kết nối: $err\n");
    exit(1);
}

echo "HTTP $httpCode\n";

if ($httpCode === 200) {
    $data = json_decode($body, true);
    $id = $data['id'] ?? '?';
    $name = $data['name'] ?? '?';
    $slug = $data['slug'] ?? '?';
    echo "✅ Auth OK — id={$id} username={$slug} name={$name}\n";
    exit(0);
}

echo "❌ Auth thất bại. Response:\n$body\n\n";
echo "Gợi ý:\n";
echo "  - Kiểm tra lại username (thường KHÔNG phải tên hiển thị trên Application\n";
echo "    Password mà là username đăng nhập wp-admin thật, xem CLAUDE.md mục 2.3).\n";
echo "  - Kiểm tra Application Password còn đúng 16 ký tự (6 nhóm cách nhau space).\n";
echo "  - Nếu HTTP là 0 hoặc lỗi kết nối: kiểm tra lại site_url.\n";
echo "  - Nếu vẫn 401 dù chắc chắn đúng credential: kiểm tra mu-plugin\n";
echo "    wp-content/mu-plugins/rest-auth-header.php đã upload lên đúng server\n";
echo "    chưa (public_html/wp-content/mu-plugins/), xem setup/README.md.\n";
exit(1);
