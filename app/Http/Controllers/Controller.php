<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\Database\Query\Builder;

abstract class Controller
{
    /**
     * Phân trang một truy vấn, trả về các dòng dạng mảng cùng thông tin trang cho view.
     *
     * @return array{rows: list<array<string, mixed>>, total: int, pages: int, page: int, offset: int}
     */
    protected function phanTrang(Builder $query, int $perPage): array
    {
        $paginator = $query->paginate($perPage);

        return [
            'rows' => array_map(fn (object $row): array => (array) $row, $paginator->items()),
            'total' => $paginator->total(),
            'pages' => max(1, $paginator->lastPage()),
            'page' => $paginator->currentPage(),
            'offset' => ($paginator->currentPage() - 1) * $perPage,
        ];
    }

    /**
     * Hàng đếm theo trạng thái cho x-qt.dem: mục "Tất cả" rồi từng giá trị của cột, đếm trên truy vấn đã lọc (trừ lọc trạng thái).
     *
     * @param  array<string, string>  $nhan  giá trị cột => nhãn hiển thị
     * @return list<array{0: string, 1: string, 2: int}>
     */
    protected function hangDemTheoCot(Builder $coSo, string $cot, array $nhan): array
    {
        $so = (clone $coSo)->selectRaw("{$cot} as gia_tri, COUNT(*) as so")->groupBy($cot)->pluck('so', 'gia_tri')->all();

        return [
            ['', 'Tất cả', (int) array_sum($so)],
            ...array_map(fn (string $giaTri): array => [$giaTri, $nhan[$giaTri], (int) ($so[$giaTri] ?? 0)], array_keys($nhan)),
        ];
    }

    /**
     * Khoảng ngày có sẵn của bộ lọc (x-qt.chon "khoang"): trả về [từ ngày, đến ngày] dạng Y-m-d, hoặc [null, null].
     *
     * @return array{0: ?string, 1: ?string}
     */
    protected function khoangNgay(string $khoang): array
    {
        return match ($khoang) {
            'hom-nay' => [today()->toDateString(), today()->toDateString()],
            '7-ngay' => [today()->subDays(6)->toDateString(), today()->toDateString()],
            'thang-nay' => [today()->startOfMonth()->toDateString(), today()->toDateString()],
            default => [null, null],
        };
    }

    /**
     * Lựa chọn khoảng ngày cho bộ lọc danh sách.
     *
     * @var array<string, string>
     */
    public const KHOANG_NGAY = ['' => 'Mọi ngày', 'hom-nay' => 'Hôm nay', '7-ngay' => '7 ngày qua', 'thang-nay' => 'Tháng này'];

    /**
     * Chuyển kết quả query builder (stdClass) thành danh sách mảng cho view.
     *
     * @param  iterable<object>  $rows
     * @return list<array<string, mixed>>
     */
    protected function mang(iterable $rows): array
    {
        $out = [];
        foreach ($rows as $row) {
            $out[] = (array) $row;
        }

        return $out;
    }
}
