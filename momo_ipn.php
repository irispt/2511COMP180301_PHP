<?php
/**
 * MoMo IPN (Instant Payment Notification)
 * Nhận callback từ MoMo server khi có giao dịch
 * File này chạy background, user không thấy
 */

require_once ("BackEnd/ConnectionDB/DB_classes.php");
date_default_timezone_set('Asia/Ho_Chi_Minh');

// Đọc dữ liệu POST từ MoMo
$data = json_decode(file_get_contents('php://input'), true);

// Log toàn bộ request để debug
$logFile = 'momo_ipn_log.txt';
$logData = [
	'time' => date('Y-m-d H:i:s'),
	'data' => $data,
	'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
];
file_put_contents($logFile, json_encode($logData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n\n", FILE_APPEND);

// Xử lý nếu có dữ liệu
if ($data && isset($data['resultCode'])) {
	$resultCode = $data['resultCode'];
	$orderId = $data['orderId'] ?? '';
	$extraData = $data['extraData'] ?? '';
	
	// Giải mã extraData để lấy MaHD
	$extraDataDecoded = json_decode(base64_decode($extraData), true);
	$maHD = null;
	
	if (isset($extraDataDecoded['maHD'])) {
		$maHD = $extraDataDecoded['maHD'];
	} else {
		// Fallback: lấy từ orderId
		$orderParts = explode('_', $orderId);
		if (count($orderParts) >= 2) {
			$maHD = $orderParts[0];
		}
	}
	
	if ($maHD) {
		try {
			$hoadonBUS = new HoaDonBUS();
			$hoadon = $hoadonBUS->select_by_id("*", $maHD);
			
			if ($hoadon) {
				if ($resultCode == 0) {
					// Thanh toán thành công
					$hoadonBUS->update_by_id(array(
						"TrangThai" => 1
					), $maHD);
					
					file_put_contents($logFile, "SUCCESS: Đơn hàng #$maHD đã được cập nhật thành công\n\n", FILE_APPEND);
				} else {
					// Thanh toán thất bại - hủy đơn
					$hoadonBUS->update_by_id(array(
						"TrangThai" => 0
					), $maHD);
					
					file_put_contents($logFile, "FAILED: Đơn hàng #$maHD đã bị hủy do thanh toán thất bại\n\n", FILE_APPEND);
				}
			}
		} catch (Exception $e) {
			file_put_contents($logFile, "ERROR: " . $e->getMessage() . "\n\n", FILE_APPEND);
		}
	}
}

// Trả về response 204 No Content cho MoMo
http_response_code(204);
?>
