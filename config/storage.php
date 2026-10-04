<?php
// ==============================================================
// Lưu trữ file (ảnh sản phẩm...) trên MinIO qua API tương thích S3.
// Ký request theo AWS Signature V4, dùng cURL có sẵn của PHP, không cần thư viện ngoài.
// ==============================================================

define('MINIO_ENDPOINT',   'http://localhost:9000');
define('MINIO_ACCESS_KEY', 'minioadmin');
define('MINIO_SECRET_KEY', 'minioadmin');
define('MINIO_BUCKET',     'ban-may-tinh');
define('MINIO_REGION',     'us-east-1');   // MinIO mặc định chấp nhận vùng này
define('MINIO_URL_TTL',    3600);          // Thời hạn link xem ảnh (giây)

// Loại file được phép tải lên và dung lượng tối đa
define('STORAGE_MAX_BYTES', 5 * 1024 * 1024);
define('STORAGE_ALLOWED_MIME', [
    'image/jpeg' => 'jpg',
    'image/png'  => 'png',
    'image/webp' => 'webp',
    'image/gif'  => 'gif',
]);

/** Mã hoá từng đoạn của key theo chuẩn S3 (giữ dấu /). */
function minioEncodeKey(string $key): string {
    return implode('/', array_map('rawurlencode', explode('/', ltrim($key, '/'))));
}

/** Ký và gửi một request tới MinIO. Trả về ['status'=>int, 'body'=>string]. */
function minioRequest(string $method, string $key = '', string $body = '', array $headers = [], array $query = []): array {
    $host   = parse_url(MINIO_ENDPOINT, PHP_URL_HOST) . (parse_url(MINIO_ENDPOINT, PHP_URL_PORT) ? ':' . parse_url(MINIO_ENDPOINT, PHP_URL_PORT) : '');
    $path   = '/' . MINIO_BUCKET . ($key !== '' ? '/' . minioEncodeKey($key) : '');
    if ($key === '' && !empty($headers['x-bucket-root'])) { $path = '/'; unset($headers['x-bucket-root']); }

    $now       = gmdate('Ymd\THis\Z');
    $date      = substr($now, 0, 8);
    $payload   = hash('sha256', $body);

    ksort($query);
    $canonQuery = http_build_query($query, '', '&', PHP_QUERY_RFC3986);

    $headers = array_change_key_case($headers, CASE_LOWER) + [
        'host'                 => $host,
        'x-amz-date'           => $now,
        'x-amz-content-sha256' => $payload,
    ];
    ksort($headers);
    $canonHeaders = '';
    foreach ($headers as $k => $v) $canonHeaders .= $k . ':' . trim($v) . "\n";
    $signedHeaders = implode(';', array_keys($headers));

    $canonRequest = implode("\n", [$method, $path, $canonQuery, $canonHeaders, $signedHeaders, $payload]);
    $scope        = "$date/" . MINIO_REGION . '/s3/aws4_request';
    $toSign       = "AWS4-HMAC-SHA256\n$now\n$scope\n" . hash('sha256', $canonRequest);
    $signature    = hash_hmac('sha256', $toSign, minioSigningKey($date));

    $headers['authorization'] = 'AWS4-HMAC-SHA256 Credential=' . MINIO_ACCESS_KEY . "/$scope, SignedHeaders=$signedHeaders, Signature=$signature";

    $ch = curl_init(MINIO_ENDPOINT . $path . ($canonQuery !== '' ? '?' . $canonQuery : ''));
    $httpHeaders = [];
    foreach ($headers as $k => $v) if ($k !== 'host') $httpHeaders[] = "$k: $v";
    curl_setopt_array($ch, [
        CURLOPT_CUSTOMREQUEST  => $method,
        CURLOPT_HTTPHEADER     => $httpHeaders,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CONNECTTIMEOUT => 3,
        CURLOPT_TIMEOUT        => 30,
    ]);
    if ($body !== '') curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
    $resp   = curl_exec($ch);
    $status = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $err    = curl_error($ch);
    curl_close($ch);

    if ($resp === false) {
        error_log('MinIO lỗi kết nối: ' . $err);
        return ['status' => 0, 'body' => $err];
    }
    return ['status' => $status, 'body' => (string)$resp];
}

function minioSigningKey(string $date): string {
    $k = hash_hmac('sha256', $date, 'AWS4' . MINIO_SECRET_KEY, true);
    $k = hash_hmac('sha256', MINIO_REGION, $k, true);
    $k = hash_hmac('sha256', 's3', $k, true);
    return hash_hmac('sha256', 'aws4_request', $k, true);
}

/** Tạo bucket nếu chưa có. Trả về true khi bucket sẵn sàng. */
function minioEnsureBucket(): bool {
    $head = minioRequest('HEAD');
    if ($head['status'] === 200) return true;
    if ($head['status'] !== 404) return false;
    $put = minioRequest('PUT');
    return $put['status'] === 200;
}

/** Tải nội dung lên MinIO. Trả về true nếu thành công. */
function minioPut(string $key, string $content, string $contentType = 'application/octet-stream'): bool {
    $r = minioRequest('PUT', $key, $content, ['content-type' => $contentType]);
    if ($r['status'] === 404 && minioEnsureBucket()) {
        $r = minioRequest('PUT', $key, $content, ['content-type' => $contentType]);
    }
    if ($r['status'] !== 200) error_log("MinIO PUT $key thất bại ({$r['status']}): " . substr($r['body'], 0, 300));
    return $r['status'] === 200;
}

/** Xoá một object. */
function minioDelete(string $key): bool {
    $r = minioRequest('DELETE', $key);
    return in_array($r['status'], [200, 204], true);
}

/** Kiểm tra object có tồn tại không. */
function minioExists(string $key): bool {
    return minioRequest('HEAD', $key)['status'] === 200;
}

/**
 * Link xem file có thời hạn (presigned GET). Bucket để riêng tư, trình duyệt dùng link này để tải ảnh.
 * Trả về chuỗi rỗng nếu key rỗng.
 */
function storageUrl(?string $key, int $ttl = MINIO_URL_TTL): string {
    if ($key === null || $key === '') return '';
    if (preg_match('#^https?://#i', $key)) return $key;   // URL ngoài thì giữ nguyên

    $host  = parse_url(MINIO_ENDPOINT, PHP_URL_HOST) . (parse_url(MINIO_ENDPOINT, PHP_URL_PORT) ? ':' . parse_url(MINIO_ENDPOINT, PHP_URL_PORT) : '');
    $path  = '/' . MINIO_BUCKET . '/' . minioEncodeKey($key);
    $now   = gmdate('Ymd\THis\Z');
    $date  = substr($now, 0, 8);
    $scope = "$date/" . MINIO_REGION . '/s3/aws4_request';

    $query = [
        'X-Amz-Algorithm'     => 'AWS4-HMAC-SHA256',
        'X-Amz-Credential'    => MINIO_ACCESS_KEY . '/' . $scope,
        'X-Amz-Date'          => $now,
        'X-Amz-Expires'       => (string)$ttl,
        'X-Amz-SignedHeaders' => 'host',
    ];
    ksort($query);
    $canonQuery   = http_build_query($query, '', '&', PHP_QUERY_RFC3986);
    $canonRequest = "GET\n$path\n$canonQuery\nhost:$host\n\nhost\nUNSIGNED-PAYLOAD";
    $toSign       = "AWS4-HMAC-SHA256\n$now\n$scope\n" . hash('sha256', $canonRequest);
    $signature    = hash_hmac('sha256', $toSign, minioSigningKey($date));

    return MINIO_ENDPOINT . $path . '?' . $canonQuery . '&X-Amz-Signature=' . $signature;
}

/**
 * Nhận một file từ $_FILES, kiểm tra loại và dung lượng, tải lên MinIO dưới thư mục $folder.
 * Trả về ['ok'=>true,'key'=>...] hoặc ['ok'=>false,'error'=>...].
 */
function storageUploadFile(array $file, string $folder): array {
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        return ['ok' => false, 'error' => 'Không nhận được file tải lên.'];
    }
    if ($file['size'] > STORAGE_MAX_BYTES) {
        return ['ok' => false, 'error' => 'File quá lớn (tối đa ' . (STORAGE_MAX_BYTES / 1024 / 1024) . ' MB).'];
    }
    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
    $ext  = STORAGE_ALLOWED_MIME[$mime] ?? null;
    if (!$ext) {
        return ['ok' => false, 'error' => 'Chỉ nhận ảnh JPG, PNG, WEBP hoặc GIF.'];
    }
    $folder = trim(preg_replace('#[^A-Za-z0-9/_-]#', '', $folder), '/');
    $key    = $folder . '/' . date('Ymd-His') . '-' . bin2hex(random_bytes(4)) . '.' . $ext;

    if (!minioPut($key, file_get_contents($file['tmp_name']), $mime)) {
        return ['ok' => false, 'error' => 'Không lưu được file lên MinIO. Kiểm tra MinIO có đang chạy không.'];
    }
    return ['ok' => true, 'key' => $key];
}
