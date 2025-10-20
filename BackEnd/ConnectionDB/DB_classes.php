<?php
require_once("DB_business.php");

// ================== HIỂN THỊ DỮ LIỆU ==================
function show_DataBUS_as_Table($bus)
{
    echo "<table cellspacing='15'>";
    foreach ($bus->select_all() as $row) {
        echo "<tr>";
        foreach ($row as $col) {
            echo "<td>" . $col . "</td>";
        }
        echo "</tr>";
    }
    echo "</table>";
}

// ================== SẢN PHẨM ==================
class SanPhamBUS extends DB_business
{
    function __construct()
    {
        $this->setTable("SanPham", "MaSP");
    }

    function add_new($data)
    {
        // tránh lỗi Auto Increment
        if (isset($data["MaSP"])) unset($data["MaSP"]);

        // thêm sản phẩm mới
        return parent::add_new($data);
    }

    function capNhapTrangThai($trangthai, $id)
    {
        $sanpham = $this->select_by_id("*", $id);
        $sanpham["TrangThai"] = $trangthai;
        return $this->update_by_id($sanpham, $id);
    }

    function themDanhGia($id)
    {
        $sanpham = $this->select_by_id("*", $id);
        $sanpham["SoDanhGia"] = $sanpham["SoDanhGia"] + 1;

        $dsbl = (new DB_driver())->get_list("SELECT * FROM danhgia WHERE MaSP=$id");
        $tongSoSao = 0;
        for ($i = 0; $i < sizeof($dsbl); $i++) {
            $tongSoSao += $dsbl[$i]["SoSao"];
        }
        $sanpham["SoSao"] = $tongSoSao / sizeof($dsbl);

        return $this->update_by_id($sanpham, $id);
    }

    // Xóa sản phẩm và TẤT CẢ dữ liệu liên quan (đánh giá, chi tiết hóa đơn)
    function xoa_san_pham_va_lien_quan($maSP)
    {
        $db = new DB_driver();
        
        // 1. Xóa tất cả đánh giá của sản phẩm này
        $db->remove("danhgia", "MaSP='$maSP'");
        
        // 2. Xóa tất cả chi tiết hóa đơn chứa sản phẩm này
        $db->remove("chitiethoadon", "MaSP='$maSP'");
        
        // 3. Cuối cùng mới xóa sản phẩm
        return $this->delete_by_id($maSP);
    }
}

// ================== LOẠI SẢN PHẨM ==================
class LoaiSanPhamBUS extends DB_business
{
    function __construct()
    {
        $this->setTable("LoaiSanPham", "MaLSP");
    }
}

// ================== CHI TIẾT SẢN PHẨM ==================
class ChiTietSanPhamBUS extends DB_business
{
    function __construct()
    {
        $this->setTable("ChiTietSanPham", "MaSP");
    }
}

// ================== NGƯỜI DÙNG ==================
class NguoiDungBUS extends DB_business
{
    function __construct()
    {
        $this->setTable("NguoiDung", "MaND");
    }

    function add_new($data)
    {
        // tránh lỗi Auto Increment
        if (isset($data["MaND"])) unset($data["MaND"]);

        // thêm user mới vào bảng NguoiDung
        return parent::add_new($data);
    }
}

// ================== HÓA ĐƠN ==================
class HoaDonBUS extends DB_business
{
    function __construct()
    {
        $this->setTable("HoaDon", "MaHD");
    }

    function add_new($data)
    {
        // tránh lỗi Auto Increment
        if (isset($data["MaHD"])) unset($data["MaHD"]);

        // thêm hóa đơn mới
        return parent::add_new($data);
    }

    function getHoaDonCuaNguoiDung($mand)
    {
        $sql = "SELECT * FROM hoadon WHERE MaND=$mand";
        return $this->get_list($sql);
    }
}

// ================== TÀI KHOẢN ==================
class TaiKhoanBUS extends DB_business
{
    function __construct()
    {
        $this->setTable("TaiKhoan", "TenTaiKhoan");
    }
}

// ================== PHÂN QUYỀN ==================
class PhanQuyenBUS extends DB_business
{
    function __construct()
    {
        $this->setTable("PhanQuyen", "MaQuyen");
    }
}

// ================== KHUYẾN MÃI ==================
class KhuyenMaiBUS extends DB_business
{
    function __construct()
    {
        $this->setTable("KhuyenMai", "MaKM");
    }
}

// ================== CHI TIẾT HÓA ĐƠN ==================
class ChiTietHoaDonBUS extends DB_business
{
    protected $_key2;

    function __construct()
    {
        $this->setTable("ChiTietHoaDon", "MaHD");
        $this->_key2 = "MaSP";
    }

    function delete_by_2id($id, $id2)
    {
        return $this->remove($this->_table_name, "{$this->_key}='$id' AND {$this->_key2}='$id2'");
    }

    function update_by_2id($data, $id, $id2)
    {
        return $this->update($this->_table_name, $data, "{$this->_key}='$id' AND {$this->_key2}='$id2'");
    }

    function select_by_2id($select, $id, $id2)
    {
        $sql = "SELECT $select FROM {$this->_table_name} WHERE {$this->_key}='$id' AND {$this->_key2}='$id2'";
        return $this->get_row($sql);
    }

    function select_all_in_hoadon($id)
    {
        $sql = "SELECT * FROM {$this->_table_name} WHERE {$this->_key}='$id'";
        return $this->get_list($sql);
    }
}
?>
