<?php
/**
 * MoMo Return URL - Trang xử lý kết quả thanh toán MoMo
 * User sẽ được redirect về đây sau khi thanh toán
 */

require_once ("BackEnd/ConnectionDB/DB_classes.php");
date_default_timezone_set('Asia/Ho_Chi_Minh');

?>
<!DOCTYPE html>
<html lang="vi">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Kết quả thanh toán MoMo</title>
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
	<script src="https://cdn.jsdelivr.net/npm/sweetalert2@8"></script>
	<style>
		body {
			font-family: Arial, sans-serif;
			background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
			min-height: 100vh;
			display: flex;
			align-items: center;
			justify-content: center;
			margin: 0;
			padding: 20px;
		}
		.result-container {
			background: white;
			border-radius: 15px;
			padding: 40px;
			max-width: 600px;
			width: 100%;
			box-shadow: 0 10px 40px rgba(0,0,0,0.2);
			text-align: center;
		}
		.success-icon {
			color: #28a745;
			font-size: 80px;
			margin-bottom: 20px;
			animation: scaleIn 0.5s ease-out;
		}
		.error-icon {
			color: #dc3545;
			font-size: 80px;
			margin-bottom: 20px;
			animation: shake 0.5s;
		}
		h1 {
			color: #333;
			margin-bottom: 20px;
		}
		.info-box {
			background: #f8f9fa;
			border-radius: 8px;
			padding: 20px;
			margin: 20px 0;
			text-align: left;
		}
		.info-row {
			display: flex;
			justify-content: space-between;
			padding: 10px 0;
			border-bottom: 1px solid #dee2e6;
		}
		.info-row:last-child {
			border-bottom: none;
		}
		.info-label {
			font-weight: bold;
			color: #666;
		}
		.info-value {
			color: #333;
			text-align: right;
		}
		.btn-home {
			display: inline-block;
			background: #667eea;
			color: white;
			padding: 12px 30px;
			border-radius: 25px;
			text-decoration: none;
			margin-top: 20px;
			transition: all 0.3s;
		}
		.btn-home:hover {
			background: #5568d3;
			transform: translateY(-2px);
		}
		@keyframes scaleIn {
			0% { transform: scale(0); }
			50% { transform: scale(1.1); }
			100% { transform: scale(1); }
		}
		@keyframes shake {
			0%, 100% { transform: translateX(0); }
			10%, 30%, 50%, 70%, 90% { transform: translateX(-10px); }
			20%, 40%, 60%, 80% { transform: translateX(10px); }
		}
	</style>
</head>
<body>
	<div class="result-container">
		<?php
		// Lấy tham số từ URL
		$resultCode = $_GET['resultCode'] ?? '';
		$message = $_GET['message'] ?? '';
		$orderId = $_GET['orderId'] ?? '';
		$amount = $_GET['amount'] ?? '';
		$orderInfo = $_GET['orderInfo'] ?? '';
		$transId = $_GET['transId'] ?? '';
		$extraData = $_GET['extraData'] ?? '';

		// Giải mã extraData để lấy MaHD
		$extraDataDecoded = json_decode(base64_decode($extraData), true);
		$maHD = null;
		if (isset($extraDataDecoded['maHD'])) {
			$maHD = $extraDataDecoded['maHD'];
		} else {
			// Fallback: lấy từ orderId (format: MaHD_timestamp)
			$orderParts = explode('_', $orderId);
			if (count($orderParts) >= 2) {
				$maHD = $orderParts[0];
			}
		}

		if ($resultCode == '0') {
			// Thanh toán thành công
			if ($maHD) {
				// Cập nhật trạng thái đơn hàng
				$hoadonBUS = new HoaDonBUS();
				$hoadon = $hoadonBUS->select_by_id("*", $maHD);
				
				if ($hoadon) {
					$hoadonBUS->update_by_id(array(
						"TrangThai" => 1 // 1 = Chờ xác nhận (đã thanh toán)
					), $maHD);
				}
			}
			
			echo '
			<i class="fa fa-check-circle success-icon"></i>
			<h1>Thanh toán thành công!</h1>
			<p style="color: #28a745; font-size: 18px;">Cảm ơn bạn đã mua hàng</p>
			
			<div class="info-box">
				<div class="info-row">
					<span class="info-label">Mã đơn hàng:</span>
					<span class="info-value">#' . htmlspecialchars($maHD ?? 'N/A') . '</span>
				</div>
				<div class="info-row">
					<span class="info-label">Mã giao dịch MoMo:</span>
					<span class="info-value">' . htmlspecialchars($transId) . '</span>
				</div>
				<div class="info-row">
					<span class="info-label">Số tiền:</span>
					<span class="info-value" style="color: #28a745; font-weight: bold;">' . number_format($amount) . ' ₫</span>
				</div>
				<div class="info-row">
					<span class="info-label">Thông tin:</span>
					<span class="info-value">' . htmlspecialchars($orderInfo) . '</span>
				</div>
			</div>
			
			<p style="color: #666; margin-top: 20px;">
				<i class="fa fa-info-circle"></i> 
				Chúng tôi sẽ xử lý đơn hàng và giao đến bạn sớm nhất có thể.
			</p>
			';
		} else {
			// Thanh toán thất bại
			if ($maHD) {
				// Cập nhật trạng thái đơn hàng thành "Đã hủy"
				$hoadonBUS = new HoaDonBUS();
				$hoadon = $hoadonBUS->select_by_id("*", $maHD);
				
				if ($hoadon) {
					$hoadonBUS->update_by_id(array(
						"TrangThai" => 0 // 0 = Đã hủy
					), $maHD);
				}
			}
			
			echo '
			<i class="fa fa-times-circle error-icon"></i>
			<h1>Thanh toán thất bại</h1>
			<p style="color: #dc3545; font-size: 18px;">' . htmlspecialchars($message) . '</p>
			
			<div class="info-box">
				<div class="info-row">
					<span class="info-label">Mã lỗi:</span>
					<span class="info-value">' . htmlspecialchars($resultCode) . '</span>
				</div>
				<div class="info-row">
					<span class="info-label">Mã đơn hàng:</span>
					<span class="info-value">#' . htmlspecialchars($maHD ?? 'N/A') . '</span>
				</div>
			</div>
			
			<p style="color: #666; margin-top: 20px;">
				<i class="fa fa-exclamation-triangle"></i> 
				Vui lòng thử lại hoặc chọn phương thức thanh toán khác.
			</p>
			';
		}
		?>
		
		<a href="index.php" class="btn-home">
			<i class="fa fa-home"></i> Về trang chủ
		</a>
		
		<?php if ($resultCode == '0'): ?>
		<a href="nguoidung.php" class="btn-home" style="background: #28a745; margin-left: 10px;">
			<i class="fa fa-list"></i> Xem đơn hàng
		</a>
		<?php endif; ?>
	</div>
</body>
</html>
