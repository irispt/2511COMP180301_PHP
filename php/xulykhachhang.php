<?php
    require_once('../BackEnd/ConnectionDB/DB_classes.php');

    if(!isset($_POST['request']) && !isset($_GET['request'])) die(null);

    switch ($_POST['request']) {
    	case 'getall':
				$khachang = (new NguoiDungBUS())->select_all();
                
		    	die (json_encode($khachang));
    		break;

    	case 'changeTT':
				$khachhangBUS = new NguoiDungBUS();
				$key = $_POST['key'];
				$trangthai = $_POST['trangThai'];
				
		    	die (json_encode($khachhangBUS->capNhapTrangThai($trangthai, $key)));
    		break;

	    case 'delete':
				$khachhangBUS = new NguoiDungBUS();
				$mand = $_POST['mand'];
					
			    die (json_encode($khachhangBUS->delete_by_id($mand	)));
	    	break;

	    case 'add':
				$khachhangBUS = new NguoiDungBUS();
				
				// Kiểm tra tài khoản và email đã tồn tại chưa
				$taiKhoan = $_POST['taiKhoan'];
				$email = $_POST['email'];
				$allUsers = $khachhangBUS->select_all();
				
				foreach ($allUsers as $user) {
					if ($user['TaiKhoan'] == $taiKhoan) {
						die(json_encode([
							'success' => false,
							'message' => 'Tài khoản đã tồn tại!'
						]));
					}
					if ($user['Email'] == $email) {
						die(json_encode([
							'success' => false,
							'message' => 'Email đã được sử dụng!'
						]));
					}
				}
				
				// Thêm người dùng mới
				$maQuyen = isset($_POST['maQuyen']) ? intval($_POST['maQuyen']) : 1; // Mặc định là khách hàng
				
				$data = [
					'Ho' => $_POST['ho'],
					'Ten' => $_POST['ten'],
					'GioiTinh' => $_POST['gioiTinh'],
					'Email' => $_POST['email'],
					'SDT' => $_POST['sdt'],
					'DiaChi' => $_POST['diaChi'],
					'TaiKhoan' => $_POST['taiKhoan'],
					'MatKhau' => password_hash($_POST['matKhau'], PASSWORD_DEFAULT),
					'MaQuyen' => $maQuyen,
					'TrangThai' => 1 // Active
				];
				
				$result = $khachhangBUS->add_new($data);
				
				if ($result) {
					die(json_encode([
						'success' => true,
						'message' => 'Thêm khách hàng thành công!'
					]));
				} else {
					die(json_encode([
						'success' => false,
						'message' => 'Không thể thêm khách hàng!'
					]));
				}
	    	break;


	default:
    		# code...
    		break;
    }
?>