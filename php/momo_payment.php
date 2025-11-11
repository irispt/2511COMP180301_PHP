<?php
/**
 * MoMo Payment Gateway Integration
 * Xử lý thanh toán qua ví điện tử MoMo và thẻ ATM (Sandbox)
 */

require_once ("../BackEnd/ConnectionDB/DB_classes.php");
date_default_timezone_set('Asia/Ho_Chi_Minh');

if(!isset($_POST['request'])) die(json_encode(['success' => false, 'message' => 'Invalid request']));

// Cấu hình MoMo Sandbox
define('MOMO_ENDPOINT', 'https://test-payment.momo.vn/v2/gateway/api/create');
define('MOMO_PARTNER_CODE', 'MOMO');
define('MOMO_ACCESS_KEY', 'F8BBA842ECF85');
define('MOMO_SECRET_KEY', 'K951B6PE1waDMi640xX08PD3vg6EkVlz');
define('MOMO_RETURN_URL', 'http://localhost/Project-PHP/momo_return.php');
define('MOMO_NOTIFY_URL', 'http://localhost/Project-PHP/momo_ipn.php');

// Router
switch ($_POST['request']) {
	case 'create_momo_payment':
		createMoMoPayment($_POST["dulieu"], "captureWallet", "MoMo");
		break;
		
	case 'create_momo_atm_payment':
		createMoMoPayment($_POST["dulieu"], "payWithATM", "MoMo ATM");
		break;
	
	case 'create_momo_visa_payment':
		createMoMoPayment($_POST["dulieu"], "captureWallet", "MoMo VISA");
		break;

	default:
		die(json_encode(['success' => false, 'message' => 'Unknown request']));
}

/**
 * Hàm tạo thanh toán MoMo chung
 * @param array $dulieu - Thông tin đơn hàng
 * @param string $requestType - Loại thanh toán: "captureWallet" hoặc "payWithATM"
 * @param string $paymentMethod - Tên phương thức hiển thị
 */
function createMoMoPayment($dulieu, $requestType, $paymentMethod) {
	try {
		// Tạo đơn hàng trước trong database
		$hoadonBUS = new HoaDonBUS();
		$chitiethdBUS = new ChiTietHoaDonBUS();

		$hoadonBUS->add_new(array(
			"MaND" => $dulieu["maNguoiDung"],
			"NgayLap" => $dulieu["ngayLap"],
			"NguoiNhan" => $dulieu["tenNguoiNhan"],
			"SDT" => $dulieu["sdtNguoiNhan"],
			"DiaChi" => $dulieu["diaChiNguoiNhan"],
			"PhuongThucTT" => $paymentMethod,
			"TongTien" => $dulieu["tongTien"],
			"TrangThai" => 0 // 0 = Chờ thanh toán MoMo
		));
		
		// Lấy mã hóa đơn vừa tạo
		$hoadonMaxID = $hoadonBUS->get_list("SELECT * FROM hoadon ORDER BY MaHD DESC LIMIT 0, 1");
		$mahd = $hoadonMaxID[0]["MaHD"];

		// Thêm chi tiết hóa đơn
		forEach($dulieu["dssp"] as $sp) {
			$dataSp = (new SanPhamBUS())->select_by_id("*", $sp["masp"]);
			$donGia = $dataSp["DonGia"];

			$chitiethdBUS->add_new(array(
				"MaHD" => $mahd,
				"MaSP" => $sp["masp"],
				"SoLuong" => $sp["soLuong"],
				"DonGia" => $donGia
			));
		}

		// Tạo yêu cầu thanh toán MoMo
		$orderId = $mahd . "_" . time(); // Gắn MaHD vào orderId
		$requestId = time() . "";
		$amount = (string)$dulieu["tongTien"];
		$orderInfo = "Thanh toan don hang #" . $mahd . " - " . $dulieu["tenNguoiNhan"];
		$extraData = base64_encode(json_encode([
			'maHD' => $mahd,
			'maNguoiDung' => $dulieu["maNguoiDung"],
			'paymentType' => $requestType
		]));

		// Tạo signature theo docs MoMo
		$rawHash = "accessKey=" . MOMO_ACCESS_KEY . 
				   "&amount=" . $amount . 
				   "&extraData=" . $extraData . 
				   "&ipnUrl=" . MOMO_NOTIFY_URL . 
				   "&orderId=" . $orderId . 
				   "&orderInfo=" . $orderInfo . 
				   "&partnerCode=" . MOMO_PARTNER_CODE . 
				   "&redirectUrl=" . MOMO_RETURN_URL . 
				   "&requestId=" . $requestId . 
				   "&requestType=" . $requestType;

		$signature = hash_hmac("sha256", $rawHash, MOMO_SECRET_KEY);

		// Data gửi đến MoMo
		$data = array(
			'partnerCode' => MOMO_PARTNER_CODE,
			'accessKey' => MOMO_ACCESS_KEY,
			'requestId' => $requestId,
			'amount' => $amount,
			'orderId' => $orderId,
			'orderInfo' => $orderInfo,
			'redirectUrl' => MOMO_RETURN_URL,
			'ipnUrl' => MOMO_NOTIFY_URL,
			'extraData' => $extraData,
			'requestType' => $requestType,
			'signature' => $signature,
			'lang' => 'vi'
		);
		
		// Nếu là thanh toán VISA, thêm userInfo để hiện form card
		if ($paymentMethod === "MoMo VISA") {
			$data['userInfo'] = json_encode([
				'name' => $dulieu["tenNguoiNhan"],
				'phoneNumber' => $dulieu["sdtNguoiNhan"]
			]);
		}

		// Gửi request đến MoMo API
		$ch = curl_init(MOMO_ENDPOINT);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
		curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
			'Content-Type: application/json',
			'Content-Length: ' . strlen(json_encode($data))
		));
		curl_setopt($ch, CURLOPT_TIMEOUT, 10);

		$result = curl_exec($ch);
		$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		
		if (curl_errno($ch)) {
			throw new Exception('Curl error: ' . curl_error($ch));
		}
		
		curl_close($ch);

		$jsonResult = json_decode($result, true);

		// Log để debug
		$logDir = '../logs';
		if (!file_exists($logDir)) {
			mkdir($logDir, 0777, true);
		}
		file_put_contents($logDir . '/momo_create_' . $orderId . '.json', json_encode([
			'request' => $data,
			'response' => $jsonResult,
			'timestamp' => date('Y-m-d H:i:s')
		], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

		if (isset($jsonResult['resultCode']) && $jsonResult['resultCode'] == 0) {
			// Thành công - trả về payUrl gốc từ MoMo
			die(json_encode([
				'success' => true,
				'payUrl' => $jsonResult['payUrl'],
				'maHD' => $mahd,
				'requestType' => $requestType,
				'paymentMethod' => $paymentMethod,
				'message' => 'Tạo link thanh toán ' . $paymentMethod . ' thành công'
			]));
		} else {
			// Cập nhật trạng thái đơn hàng thành hủy nếu tạo payment thất bại
			$hoadonBUS->update_by_id(array("TrangThai" => 0), $mahd);
			
			throw new Exception($jsonResult['message'] ?? 'Lỗi kết nối MoMo');
		}

	} catch (Exception $e) {
		die(json_encode([
			'success' => false,
			'message' => $e->getMessage()
		]));
	}
}
?>

