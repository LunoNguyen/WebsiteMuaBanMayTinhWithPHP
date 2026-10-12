<?php

namespace App\Http\Controllers;

use App\Services\DiaChiService;
use Illuminate\Http\JsonResponse;

/**
 * Danh mục tỉnh / phường cho ô chọn địa chỉ (x-dia-chi). Gọi qua server vì API gốc không cho trình duyệt gọi thẳng (CORS).
 */
class DiaChiController extends Controller
{
    public function __construct(private DiaChiService $diaChi) {}

    public function tinhThanh(): JsonResponse
    {
        return $this->traVe($this->diaChi->tinhThanh());
    }

    public function phuongXa(int $maTinh): JsonResponse
    {
        return $this->traVe($this->diaChi->phuongXa($maTinh));
    }

    /**
     * @param  list<array{ma: int, ten: string}>  $danhSach
     */
    private function traVe(array $danhSach): JsonResponse
    {
        return $danhSach === []
            ? response()->json(['loi' => 'Chưa tải được danh mục địa chỉ.'], 503)
            : response()->json($danhSach)->header('Cache-Control', 'private, max-age=86400');
    }
}
