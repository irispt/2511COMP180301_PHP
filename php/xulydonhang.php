<?php
    require_once('../BackEnd/ConnectionDB/DB_classes.php');

    if(!isset($_POST['request']) && !isset($_GET['request'])) die(null);

    switch ($_POST['request']) {
    	case 'getall':
				$donhang = (new HoaDonBUS())->select_all();
                $ctdonhang = (new ChiTietHoaDonBUS())->select_all();
		    	die (json_encode($donhang));
    		break;

		case 'getdetail':
				$mahd = $_POST['mahd'];
				// Lấy chi tiết đơn hàng và join với tên sản phẩm
				$sql = "SELECT ct.*, sp.TenSP 
						FROM chitiethoadon ct 
						JOIN sanpham sp ON ct.MaSP = sp.MaSP 
						WHERE ct.MaHD = $mahd";
				$result = (new DB_driver())->get_list($sql);
				die (json_encode($result));
			break;

		case 'updatestatus':
				$mahd = $_POST['mahd'];
				$trangthai = $_POST['trangthai'];
				
				$hd = (new HoaDonBUS())->select_by_id('*', $mahd);
				$hd['TrangThai'] = $trangthai;
				
				$result = (new HoaDonBUS())->update_by_id($hd, $mahd);
				die (json_encode($result));
			break;

		default:
	    		# code...
	    		break;
    }
?>