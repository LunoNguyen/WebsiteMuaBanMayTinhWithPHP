<?php
// ================================================================
// API tải ảnh sản phẩm lên MinIO - admin/api/upload_anh.php
// POST multipart: masp, anh (file), chinh=1 (tuỳ chọn: đặt làm ảnh đại diện)
// Lưu khoá object vào DANHSACHANH.URL_ANH
// ================================================================
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../config/storage.php';
header('Content-Type: application/json; charset=utf-8');

function jsonOut(int $code, array $data): void {
    http_response_code($code);
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

if (empty($_SESSION['matk']) || ($_SESSION['loai'] ?? '') !== 'Admin') {
    jsonOut(403, ['success' => false, 'error' => 'Bạn không có quyền tải ảnh lên.']);
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonOut(405, ['success' => false, 'error' => 'Chỉ nhận POST.']);
}

$masp = trim($_POST['masp'] ?? '');
$sp   = $masp !== '' ? dbFetchOne("SELECT MASP FROM SANPHAM WHERE MASP=?", [$masp], 's') : null;
if (!$sp) {
    jsonOut(404, ['success' => false, 'error' => 'Không tìm thấy sản phẩm.']);
}
if (empty($_FILES['anh'])) {
    jsonOut(400, ['success' => false, 'error' => 'Chưa chọn ảnh.']);
}

$up = storageUploadFile($_FILES['anh'], 'sanpham/' . $masp);
if (!$up['ok']) {
    jsonOut(400, ['success' => false, 'error' => $up['error']]);
}

// Sản phẩm chưa có ảnh đại diện thì ảnh đầu tiên tự thành ảnh đại diện
$coAnhChinh = dbFetchOne("SELECT COUNT(*) AS c FROM DANHSACHANH WHERE MASP=? AND LA_ANH_CHINH=1", [$masp], 's');
$laChinh    = (!empty($_POST['chinh']) || (int)($coAnhChinh['c'] ?? 0) === 0) ? 1 : 0;
$thuTu      = (int)(dbFetchOne("SELECT COALESCE(MAX(THU_TU),0)+1 AS t FROM DANHSACHANH WHERE MASP=?", [$masp], 's')['t'] ?? 1);

try {
    if ($laChinh) {
        dbExecute("UPDATE DANHSACHANH SET LA_ANH_CHINH=0 WHERE MASP=?", [$masp], 's');
    }
    $ok = dbExecute(
        "INSERT INTO DANHSACHANH (MASP, URL_ANH, LA_ANH_CHINH, THU_TU) VALUES (?,?,?,?)",
        [$masp, $up['key'], $laChinh, $thuTu], 'ssii'
    );
    if (!$ok) throw new Exception('INSERT DANHSACHANH thất bại');
} catch (Throwable $e) {
    minioDelete($up['key']);   // không để file mồ côi trên MinIO
    jsonOut(500, ['success' => false, 'error' => 'Lưu thông tin ảnh thất bại.']);
}

jsonOut(200, [
    'success'  => true,
    'key'      => $up['key'],
    'url'      => storageUrl($up['key']),
    'la_chinh' => (bool)$laChinh,
]);
