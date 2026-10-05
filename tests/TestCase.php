<?php

namespace Tests;

use App\Models\TaiKhoan;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

/**
 * Test chạy trên database riêng ql_banmt_test (dữ liệu mẫu từ qlbanmt.sql);
 * mọi thay đổi trong một test được rollback khi test kết thúc.
 */
abstract class TestCase extends BaseTestCase
{
    use DatabaseTransactions;

    protected const MAT_KHAU_TEST = 'matkhau-test-123';

    /**
     * Lấy tài khoản theo mã và đặt mật khẩu test (dữ liệu mẫu không có mật khẩu dùng được).
     */
    protected function taiKhoan(string $maTaiKhoan): TaiKhoan
    {
        $taiKhoan = TaiKhoan::query()->findOrFail($maTaiKhoan);
        $taiKhoan->update(['MATKHAU' => self::MAT_KHAU_TEST, 'TRANGTHAI' => 'HoatDong']);

        return $taiKhoan->refresh();
    }
}
