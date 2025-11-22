<!DOCTYPE html>
<html lang="vi">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<!-- <meta http-equiv="X-UA-Compatible" content="ie=edge"> -->
	<meta http-equiv="Content-Type" content="text/html;charset=UTF-8">

	<title>Thế giới điện thoại</title>
	<link rel="shortcut icon" href="img/favicon.ico" />

	<!-- Load font awesome icons -->
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css" crossorigin="anonymous">

	<!-- Jquery -->
	<script src="lib/Jquery/Jquery.min.js"></script>

	<!-- owl carousel libraries -->
	<link rel="stylesheet" href="lib/owlcarousel/owl.carousel.min.css">
	<link rel="stylesheet" href="lib/owlcarousel/owl.theme.default.min.css">
	<script src="lib/owlcarousel/owl.carousel.min.js"></script>

	<!-- Sweet Alert -->
	<script src="https://cdn.jsdelivr.net/npm/sweetalert2@8"></script>

	<!-- Slider -->
	<link rel="stylesheet" href="lib/ion.rangeSlider-2.2.0/css/ion.rangeSlider.css">
	<link rel="stylesheet" href="lib/ion.rangeSlider-2.2.0/css/ion.rangeSlider.skinHTML5.css">
	<script src="lib/ion.rangeSlider-2.2.0/js/ion-rangeSlider/ion.rangeSlider.min.js"></script>

    <!-- Swiper (hero slider) -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
    <!-- AOS (scroll animations) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.css" />

	<!-- tidio - live chat -->
	<!-- <script src="//code.tidio.co/bfiiplaaohclhqwes5xivoizqkq56guu.js"></script> -->

	<!-- our files -->
	<!-- css -->
	<link rel="stylesheet" href="css/style.css">
	<link rel="stylesheet" href="css/topnav.css">
	<link rel="stylesheet" href="css/header.css">
	<link rel="stylesheet" href="css/banner.css">
	<link rel="stylesheet" href="css/taikhoan.css">
	<link rel="stylesheet" href="css/trangchu.css">
	<link rel="stylesheet" href="css/home_products.css">
	<link rel="stylesheet" href="css/pagination_phantrang.css">
	<link rel="stylesheet" href="css/footer.css">
	<!-- js -->
	<link rel="stylesheet" href="css/chatbot.css">
	
	<script src="js/dungchung.js"></script>
	<script src="js/trangchu.js"></script>

	<script src="js/chatbot.js"></script>

	<!-- Lightweight JS libs: Alpine (UI state), Swiper, AOS, lazysizes -->
	<script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
	<script defer src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
	<script defer src="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.js"></script>
	<script defer src="https://cdnjs.cloudflare.com/ajax/libs/lazysizes/5.3.2/lazysizes.min.js" async></script>

	<?php require_once "php/echoHTML.php"; ?>
</head>

<body>
	<?php addTopNav(); ?>

	<section>
		<?php 
			addHeader(); 
			addHome();
		?>
	</section>

	<?php
		addContainTaiKhoan();
		addPlc();
	?>

	<div class="footer">
		<?php addFooter(); ?>
	</div>

	<?php require_once "php/chatbot_widget.php"; renderChatbotWidget(); ?>
	
	<i class="fa fa-arrow-up" id="goto-top-page" onclick="gotoTop()"></i>
	<i class="fa fa-arrow-down" id="goto-bot-page" onclick="gotoBot()"></i>

	<script>
		// Initialize AOS when DOM is ready
		document.addEventListener('DOMContentLoaded', function() {
			if (window.AOS) AOS.init({ duration: 700, once: true });
		});
	</script>

</body>

</html>
