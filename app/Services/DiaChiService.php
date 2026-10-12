<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Tỉnh / thành phố và phường / xã từ provinces.open-api.vn (API v2, hai cấp sau sáp nhập).
 * Kết quả được cache; API lỗi thì trả mảng rỗng và không cache, lần sau gọi lại.
 */
class DiaChiService
{
    /**
     * @return list<array{ma: int, ten: string}>
     */
    public function tinhThanh(): array
    {
        return $this->layCoCache('dia-chi:tinh', '/p/', fn (array $json): array => $this->rutGon($json));
    }

    /**
     * @return list<array{ma: int, ten: string}>
     */
    public function phuongXa(int $maTinh): array
    {
        return $this->layCoCache("dia-chi:phuong:{$maTinh}", "/p/{$maTinh}?depth=2", fn (array $json): array => $this->rutGon($json['wards'] ?? []));
    }

    /**
     * @param  callable(array<mixed>): list<array{ma: int, ten: string}>  $chuyen
     * @return list<array{ma: int, ten: string}>
     */
    private function layCoCache(string $khoa, string $duongDan, callable $chuyen): array
    {
        $daCo = Cache::get($khoa);

        if (is_array($daCo)) {
            return $daCo;
        }

        try {
            $phanHoi = Http::timeout(10)->retry(2, 300, throw: false)
                ->withOptions(['verify' => $this->chungChi()])
                ->acceptJson()
                ->get(rtrim((string) config('cuahang.dia_chi.api'), '/').$duongDan);

            $ketQua = $phanHoi->successful() && is_array($phanHoi->json()) ? $chuyen($phanHoi->json()) : [];
        } catch (Throwable $exception) {
            Log::warning('Không lấy được danh mục địa chỉ', ['duong_dan' => $duongDan, 'loi' => $exception->getMessage()]);
            $ketQua = [];
        }

        if ($ketQua !== []) {
            Cache::put($khoa, $ketQua, (int) config('cuahang.dia_chi.cache_giay'));
        }

        return $ketQua;
    }

    /**
     * @param  array<mixed>  $dong
     * @return list<array{ma: int, ten: string}>
     */
    private function rutGon(array $dong): array
    {
        return collect($dong)
            ->filter(fn ($muc): bool => is_array($muc) && isset($muc['code'], $muc['name']))
            ->map(fn (array $muc): array => ['ma' => (int) $muc['code'], 'ten' => (string) $muc['name']])
            ->values()
            ->all();
    }

    /**
     * PHP của Laragon nhiều máy chưa trỏ tới bộ chứng chỉ gốc: dùng file cacert.pem của Laragon nếu có.
     */
    private function chungChi(): string|bool
    {
        if (filled(ini_get('curl.cainfo')) || filled(ini_get('openssl.cafile'))) {
            return true;
        }

        $laragon = dirname(PHP_BINARY, 4).'/etc/ssl/cacert.pem';

        return is_file($laragon) ? $laragon : true;
    }
}
