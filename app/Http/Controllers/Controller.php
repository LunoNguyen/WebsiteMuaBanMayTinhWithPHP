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
