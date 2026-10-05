<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\LuuDanhMucRequest;
use App\Support\MaTuDong;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * Danh mục dùng chung: loại sản phẩm, nhà sản xuất, nhà cung cấp, chức vụ.
 */
class DanhMucController extends Controller
{
    /**
     * Cấu hình từng bảng: tên bảng, khoá, tiền tố mã, các trường của form và bảng tham chiếu tới nó.
     *
     * @var array<string, array{ten: string, bang: string, khoa: string, tien_to: string, truong: array<string, array{nhan: string, luat: list<string>}>, tham_chieu: array<string, string>}>
     */
    public const BANG = [
        'loai' => [
            'ten' => 'Loại sản phẩm',
            'bang' => 'LOAISANPHAM',
            'khoa' => 'MALOAI',
            'tien_to' => 'LSP',
            'truong' => [
                'TENLOAI' => ['nhan' => 'Tên loại', 'luat' => ['required', 'string', 'max:100']],
            ],
            'tham_chieu' => ['SANPHAM' => 'MALOAI'],
        ],
        'nsx' => [
            'ten' => 'Nhà sản xuất',
            'bang' => 'NHASANXUA',
            'khoa' => 'MANSX',
            'tien_to' => 'NSX',
            'truong' => [
                'TENNSX' => ['nhan' => 'Tên hãng', 'luat' => ['required', 'string', 'max:100']],
                'QUOCGIA' => ['nhan' => 'Quốc gia', 'luat' => ['nullable', 'string', 'max:100']],
            ],
            'tham_chieu' => ['SANPHAM' => 'MANSX'],
        ],
        'ncc' => [
            'ten' => 'Nhà cung cấp',
            'bang' => 'NHACUNGCAP',
            'khoa' => 'MANCC',
            'tien_to' => 'NCC',
            'truong' => [
                'TENNCC' => ['nhan' => 'Tên nhà cung cấp', 'luat' => ['required', 'string', 'max:100']],
                'SDT_NCC' => ['nhan' => 'Điện thoại', 'luat' => ['nullable', 'string', 'regex:/^[0-9 +.]{8,20}$/']],
                'EMAIL_NCC' => ['nhan' => 'Email', 'luat' => ['nullable', 'email', 'max:100']],
                'DIACHI_NCC' => ['nhan' => 'Địa chỉ', 'luat' => ['nullable', 'string', 'max:200']],
            ],
            'tham_chieu' => ['SANPHAM' => 'MANCC', 'PHIEUNHAPHANG' => 'MANCC'],
        ],
        'chucvu' => [
            'ten' => 'Chức vụ',
            'bang' => 'CHUCVU',
            'khoa' => 'MACV',
            'tien_to' => 'CV',
            'truong' => [
                'TENCV' => ['nhan' => 'Tên chức vụ', 'luat' => ['required', 'string', 'max:100']],
                'MOTA_CV' => ['nhan' => 'Mô tả', 'luat' => ['nullable', 'string', 'max:500']],
            ],
            'tham_chieu' => ['NHANVIEN' => 'MACV'],
        ],
    ];

    /**
     * Một trang, bốn thẻ; ?sua=MA thì điền dòng đó lên form.
     */
    public function index(Request $request): View
    {
        $bang = array_key_exists((string) $request->query('bang'), self::BANG) ? (string) $request->query('bang') : 'loai';
        $cauHinh = self::BANG[$bang];

        $soLan = collect($cauHinh['tham_chieu'])
            ->map(fn (string $cot, string $bangCon): string => "(SELECT COUNT(*) FROM {$bangCon} WHERE {$bangCon}.{$cot} = dm.{$cauHinh['khoa']})")
            ->implode(' + ');

        return view('admin.danhmuc', [
            'bang' => $bang,
            'cauHinh' => $cauHinh,
            'tatCa' => self::BANG,
            'dong' => DB::table($cauHinh['bang'].' as dm')->select('dm.*')->selectRaw("{$soLan} AS so_dung")->orderBy('dm.'.$cauHinh['khoa'])->get(),
            'dangSua' => $request->query('sua') ? DB::table($cauHinh['bang'])->where($cauHinh['khoa'], $request->query('sua'))->first() : null,
        ]);
    }

    /**
     * Thêm một dòng danh mục, mã tự sinh.
     */
    public function store(LuuDanhMucRequest $request, string $bang): RedirectResponse
    {
        $cauHinh = $this->cauHinh($bang);

        $ma = DB::transaction(function () use ($request, $cauHinh): string {
            $ma = MaTuDong::tiepTheo($cauHinh['bang'], $cauHinh['khoa'], $cauHinh['tien_to']);
            DB::table($cauHinh['bang'])->insert([$cauHinh['khoa'] => $ma, ...$request->validated()]);

            return $ma;
        });

        return redirect()->route('admin.danhmuc', ['bang' => $bang])->with('thong_bao', "Đã thêm {$cauHinh['ten']} {$ma}.");
    }

    /**
     * Sửa một dòng danh mục.
     */
    public function update(LuuDanhMucRequest $request, string $bang, string $ma): RedirectResponse
    {
        $cauHinh = $this->cauHinh($bang);
        abort_unless(DB::table($cauHinh['bang'])->where($cauHinh['khoa'], $ma)->exists(), 404);

        DB::table($cauHinh['bang'])->where($cauHinh['khoa'], $ma)->update($request->validated());

        return redirect()->route('admin.danhmuc', ['bang' => $bang])->with('thong_bao', "Đã cập nhật {$cauHinh['ten']} {$ma}.");
    }

    /**
     * Xoá dòng danh mục chưa được dùng ở đâu.
     */
    public function destroy(string $bang, string $ma): RedirectResponse
    {
        $cauHinh = $this->cauHinh($bang);
        abort_unless(DB::table($cauHinh['bang'])->where($cauHinh['khoa'], $ma)->exists(), 404);

        foreach ($cauHinh['tham_chieu'] as $bangCon => $cot) {
            if (DB::table($bangCon)->where($cot, $ma)->exists()) {
                return back()->with('thong_bao', "{$cauHinh['ten']} {$ma} đang được dùng, không xoá được.")->with('loai', 'danger');
            }
        }

        DB::table($cauHinh['bang'])->where($cauHinh['khoa'], $ma)->delete();

        return redirect()->route('admin.danhmuc', ['bang' => $bang])->with('thong_bao', "Đã xoá {$cauHinh['ten']} {$ma}.");
    }

    /**
     * @return array{ten: string, bang: string, khoa: string, tien_to: string, truong: array<string, array{nhan: string, luat: list<string>}>, tham_chieu: array<string, string>}
     */
    private function cauHinh(string $bang): array
    {
        abort_unless(array_key_exists($bang, self::BANG), 404);

        return self::BANG[$bang];
    }
}
