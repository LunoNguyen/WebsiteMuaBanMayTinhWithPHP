<?php

namespace App\Http\Controllers;

use App\Services\MinioStorage;
use Illuminate\Http\Response;

/**
 * Trả ảnh sản phẩm từ MinIO qua website, để trình duyệt ở máy khác (Cloudflare Tunnel) vẫn xem được
 * mà MinIO không phải mở ra ngoài.
 */
class AnhController extends Controller
{
    /**
     * Tên file ảnh có dấu thời gian và chuỗi ngẫu nhiên, không bao giờ bị ghi đè, nên cho trình duyệt giữ lâu.
     */
    public function __invoke(MinioStorage $storage, string $duongDan): Response
    {
        abort_if(str_contains($duongDan, '..'), 404);

        $ketQua = $storage->get($duongDan);

        abort_unless($ketQua?->successful() === true, 404);

        $loai = (string) $ketQua->header('Content-Type');
        abort_unless(str_starts_with($loai, 'image/'), 404);

        return response($ketQua->body(), 200, [
            'Content-Type' => $loai,
            'Cache-Control' => 'public, max-age=31536000, immutable',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}
