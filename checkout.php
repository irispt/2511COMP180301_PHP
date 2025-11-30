<!DOCTYPE html>
<html lang="vi">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta http-equiv="X-UA-Compatible" content="ie=edge">
	<link rel="shortcut icon" href="img/favicon.ico" />

	<title>Thanh toán - Thế giới điện thoại</title>

	<!-- Load font awesome icons -->
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css" crossorigin="anonymous">
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.3.1/css/bootstrap.min.css">

	<!-- Jquery -->
	<script src="lib/Jquery/Jquery.min.js"></script>

	<!-- Bootstrap -->
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.3.1/css/bootstrap.min.css">
	<script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.3.1/js/bootstrap.min.js"></script>

	<!-- Sweet Alert -->
	<script src="https://cdn.jsdelivr.net/npm/sweetalert2@8"></script>

	<!-- our files -->
	<!-- css -->
	<link rel="stylesheet" href="css/style.css">
	<link rel="stylesheet" href="css/header_samsung.css">
	<link rel="stylesheet" href="css/checkout_page.css">
	<link rel="stylesheet" href="css/footer.css">
	<!-- js -->
	<script src="js/dungchung.js?v=<?php echo time(); ?>"></script>
	<script src="js/header_samsung.js?v=<?php echo time(); ?>"></script>
	<script src="js/checkout.js?v=<?php echo time(); ?>"></script>

	<?php require_once "php/echoHTML.php"; ?>

</head>

<body>
	<?php addTopNav(); ?>

	<section style="min-height: 85vh; background: #f8f9fa;">
		<?php addHeader(); ?>

		<style>
			.containTaikhoan {
				display: none !important;
				visibility: hidden !important;
				opacity: 0 !important;
				pointer-events: none !important;
			}
		</style>

		<div class="checkout-container">
			<div class="checkout-content">
				<!-- Left side - Customer info and delivery -->
				<div class="checkout-left">
					<div class="checkout-section">
						<h2 class="section-title">1. Chi tiết khách hàng</h2>
						
						<div class="form-group">
							<label>E-mail *</label>
							<input type="email" id="email" class="form-control" placeholder="email@example.com" required>
						</div>

						<div class="form-row">
							<div class="form-group col-md-6">
								<label>Họ và tên *</label>
								<input type="text" id="hovaten" class="form-control" required>
							</div>
							<div class="form-group col-md-6">
								<label>Số điện thoại *</label>
								<input type="tel" id="sdt" class="form-control" required>
							</div>
						</div>
					</div>

					<div class="checkout-section">
						<h2 class="section-title">Địa chỉ giao hàng</h2>
						<p class="section-description">Chúng tôi đang trong quá trình cập nhật địa chỉ giao hàng theo đơn vị hành chính mới, vui lòng chọn địa chỉ giao hàng theo đơn vị hành chính trong thời gian cập nhật hiện có trong.</p>

						<h3 class="subsection-title">Địa chỉ mới</h3>

						<div class="form-row">
							<div class="form-group col-md-6">
								<label>Số nhà *</label>
								<input type="text" id="sonha" class="form-control" required>
							</div>
							<div class="form-group col-md-6">
								<label>Đường phố *</label>
								<input type="text" id="duongpho" class="form-control" required>
							</div>
						</div>

						<div class="form-row">
							<div class="form-group col-md-4">
								<label>Tỉnh/Thành phố *</label>
								<select id="tinh" class="form-control" required>
									<option value="">Chọn tỉnh/thành</option>
								</select>
							</div>
							<div class="form-group col-md-4">
								<label>Huyện *</label>
								<select id="huyen" class="form-control" required>
									<option value="">Chọn huyện</option>
								</select>
							</div>
							<div class="form-group col-md-4">
								<label>Phường *</label>
								<select id="phuong" class="form-control" required>
									<option value="">Chọn phường</option>
								</select>
							</div>
						</div>

						<div class="form-group">
							<label>Làng/Khu phố (tùy chọn)</label>
							<input type="text" id="lang" class="form-control">
						</div>

						<div class="form-group">
							<label>Phiếu giao hàng (tùy chọn)</label>
							<textarea id="ghichu" class="form-control" rows="3" placeholder="Vui lòng nhập tiếng Việt không dấu. Ghi rõ tòa nhà, tên căn hộ, số tầng,"></textarea>
						</div>
					</div>

					<div class="checkout-section">
						<h2 class="section-title">Phương thức thanh toán</h2>
						<div class="payment-methods">
							<label class="payment-option">
								<input type="radio" name="payment" value="cod" checked>
								<span class="payment-label">Thanh toán khi nhận hàng (COD)</span>
							</label>
							<label class="payment-option">
								<input type="radio" name="payment" value="banking">
								<span class="payment-label">Chuyển khoản ngân hàng</span>
							</label>
							<label class="payment-option">
								<input type="radio" name="payment" value="momo">
								<span class="payment-label">Ví MoMo</span>
							</label>
						</div>
					</div>
				</div>

				<!-- Right side - Order summary -->
				<div class="checkout-right">
					<div class="order-summary">
						<h2 class="summary-title">Xem đơn hàng</h2>
						
						<div class="cart-edit">
							<span>Bạn có <strong class="cart-count">0</strong> mặt hàng trong giỏ hàng của mình</span>
							<a href="giohang.php" class="edit-cart">
								<i class="fa fa-pencil"></i> Chỉnh sửa
							</a>
						</div>

						<div class="order-items" id="orderItems">
							<!-- Items will be loaded here -->
						</div>

						<div class="promo-section">
							<div class="promo-code-title">Mã khuyến mãi</div>
							<div class="promo-code-input">
								<input type="text" id="promoCode" placeholder="Nhập Voucher hoặc Gift Code">
								<button class="btn-apply-promo">Áp dụng</button>
							</div>
							<a href="#" class="promo-link">Xem mã khuyến mãi</a>
						</div>

						<div class="summary-details">
							<div class="summary-row">
								<span>Tạm tính</span>
								<span class="summary-value" id="subtotal">0 ₫</span>
							</div>
							<div class="summary-row">
								<span>Phí vận chuyển</span>
								<span class="summary-value">Miễn phí</span>
							</div>
						</div>

						<div class="total-section">
							<div class="total-row">
								<span class="total-label">Tổng cộng</span>
								<span class="total-value" id="totalPrice">0 ₫</span>
							</div>
							<div class="total-note-blue">Bạn đã đăng nhập với tư cách: <strong id="userEmail">Blue</strong></div>
						</div>

						<button class="btn-place-order" onclick="xacNhanDatHang()">
							Xác nhận
						</button>
					</div>
				</div>
			</div>
		</div>

	</section>

	<div class="footer">
		<?php addFooter(); ?>
	</div>

	<?php addContainTaiKhoan(); ?>

	<i class="fa fa-arrow-up" id="goto-top-page" onclick="gotoTop()"></i>
	<i class="fa fa-arrow-down" id="goto-bot-page" onclick="gotoBot()"></i>
</body>

</html>
