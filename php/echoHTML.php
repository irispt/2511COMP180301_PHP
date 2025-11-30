<?php
session_start();

// Thêm topnav vào trang (Samsung style - minimalist)
function addTopNav()
{
    // Top nav removed - clean minimal design
}

// Thêm header Samsung style
function addHeader()
{
    echo '        
	<div class="header-samsung">
        <div class="header-container">
            <!-- Logo bên trái -->
            <div class="header-logo">
                <a href="index.php">
                    <img src="img/logo.jpg" alt="Smartphone Store" title="Smartphone Store">
                </a>
            </div>

            <!-- Menu chính ở giữa -->
            <nav class="header-nav">
                <ul class="nav-menu">
                    <li><a href="index.php" class="nav-link">Trang chủ</a></li>
                    <li><a href="?category=new" class="nav-link">Sản phẩm mới</a></li>
                    <li><a href="?featured=1" class="nav-link">Nổi bật nhất</a></li>
                    <li><a href="?promotion=1" class="nav-link">Khuyến mãi</a></li>
                    <li><a href="?installment=1" class="nav-link">Trả góp</a></li>
                    <li><a href="?category=accessories" class="nav-link">Phụ kiện</a></li>
                    <li><a href="#support" class="nav-link">Hỗ trợ</a></li>
                </ul>
            </nav>

            <!-- Tools bên phải -->
            <div class="header-tools">
                <!-- Search -->
                <div class="tool-search">
                    <button class="search-toggle" onclick="toggleSearch()">
                        <i class="fa fa-search"></i>
                    </button>
                    <div class="search-dropdown" id="searchDropdown" style="display: none;">
                        <form method="get" action="index.php">
                            <input type="text" name="search" placeholder="Tìm kiếm..." autocomplete="off">
                            <button type="submit"><i class="fa fa-search"></i></button>
                        </form>
                    </div>
                </div>

                <!-- Cart -->
                <div class="tool-cart">
                    <a href="giohang.php" class="cart-link">
                        <i class="fa fa-shopping-cart"></i>
                        <span class="cart-badge cart-number" x-data="{ count: 0 }" x-text="count"></span>
                    </a>
                </div>

                <!-- User Account -->
                <div class="tool-account">
                    <button class="account-toggle" onclick="checkTaiKhoan()" id="btnTaiKhoan">
                        <i class="fa fa-user-circle-o"></i>
                    </button>
                    <div class="account-dropdown menuMember">
                        <div class="account-header" id="accountUserName">
                            <i class="fa fa-user"></i>
                            <span>Tài khoản</span>
                        </div>
                        <a href="nguoidung.php">Tài khoản của tôi</a>
                        <a href="nguoidung.php?tab=orders">Sản phẩm đã mua</a>
                        <a onclick="checkDangXuat();" style="cursor: pointer;">Đăng xuất</a>
                    </div>
                </div>
            </div>
        </div>
    </div>';
}

// thêm home
function addHome()
{
    echo '
    <div class="banner">
        <!-- Swiper hero slider -->
        <div class="swiper hero-swiper">
            <div class="swiper-wrapper"></div>
            <div class="swiper-pagination"></div>
            <div class="swiper-button-prev"></div>
            <div class="swiper-button-next"></div>
        </div>
    </div> <!-- End Banner -->
    
    <div class="smallbanner" style="width: 100%;"></div>

    <div class="companysFilter">
        <button class="companysButton" onclick="setCompanysMenu()">
            <p>Hãng</p>
            <div id="iconOpenMenu">▷</div>
            <div id="iconCloseMenu" style="display: none;">▽</div>
        </button>
    </div>
    <div class="companyMenu group flexContain"></div>

    <div class="timNangCao">
        <div class="flexContain">
            <div class="pricesRangeFilter dropdown">
                <button class="dropbtn">Khoảng Giá</button>
                <div class="dropdown-content"></div>
            </div>

            <div class="promosFilter dropdown">
                <button class="dropbtn">Khuyến mãi</button>
                <div class="dropdown-content"></div>
            </div>

            <div class="starFilter dropdown">
                <button class="dropbtn">Số lượng sao</button>
                <div class="dropdown-content"></div>
            </div>

            <div class="sortFilter dropdown">
                <button class="dropbtn">Sắp xếp</button>
                <div class="dropdown-content"></div>
            </div>            
        </div>
        
        <!-- Active filters display -->
        <div class="filter-tags" id="activeFilters" style="display:none;">
            <!-- Active filter tags will be inserted here by JS -->
        </div>

        <!-- Result count -->
        <div class="result-count" id="resultCount" style="display:none;">
            Tìm thấy <strong>0</strong> sản phẩm
        </div>
    </div>

    </div> <!-- End khung chọn bộ lọc -->

    <div class="choosedFilter flexContain"></div> <!-- Những bộ lọc đã chọn -->
    <hr>

    <!-- Mặc định mới vào trang sẽ ẩn đi, nế có filter thì mới hiện lên -->
    <div class="contain-products" style="display:none">
    <!-- Removed filterName div with product count -->
    <div class="loader" style="display: none"></div>

    <div id="khongCoSanPham">
        <i class="fa fa-times-circle"></i>
        Không có sản phẩm nào
    </div> <!-- End Khong co san pham -->

    <ul id="products" class="products-grid">
    </ul><!-- End products -->

    <div class="pagination"></div>
    </div>

    <!-- Div hiển thị khung sp hot, khuyến mãi, mới ra mắt ... -->
    <div class="contain-khungSanPham"></div>';
}

// Thêm chi tiết sản phẩm
function addChiTietSanPham()
{
    echo '
    <div class="chitietSanpham" style="min-height: 85vh">
        <h1>Điện thoại </h1>
        <div class="rowdetail group">
            <div class="picture">
                <img src="">
            </div>
            <div class="price_sale">
                <div class="area_price"> </div>
                <div class="ship" style="display: none;">
                    <i class="fa fa-clock-o"></i>
                    <div>NHẬN HÀNG TRONG 1 GIỜ</div>
                </div>
                <div class="area_promo">
                    <strong>khuyến mãi</strong>
                    <div class="promo">
                        <i class="fa fa-check-circle"></i>
                        <div id="detailPromo"> </div>
                    </div>
                </div>
                <div class="policy">
                    <div>
                        <i class="fa fa-archive"></i>
                        <p>Trong hộp có: Sạc, Tai nghe, Sách hướng dẫn, Cây lấy sim, Ốp lưng </p>
                    </div>
                    <div>
                        <i class="fa fa-star"></i>
                        <p>Bảo hành chính hãng 12 tháng.</p>
                    </div>
                    <div class="last">
                        <i class="fa fa-retweet"></i>
                        <p>1 đổi 1 trong 1 tháng nếu lỗi, đổi sản phẩm tại nhà trong 1 ngày.</p>
                    </div>
                </div>
                <div class="area_order">
                    <!-- nameProduct là biến toàn cục được khởi tạo giá trị trong phanTich_URL_chiTietSanPham -->
                    <a class="buy_now" onclick="themVaoGioHang(maProduct, nameProduct);">
                        <h3><i class="fa fa-plus"></i> Thêm vào giỏ hàng</h3>
                    </a>
                </div>
            </div>
            <div class="info_product">
                <h2>Thông số kỹ thuật</h2>
                <ul class="info">

                </ul>
            </div>
        </div>
        <hr>
        <div class="comment-area">
            <div class="guiBinhLuan">
                <div class="stars">
                    <form action="">
                        <input class="star star-5" id="star-5" value="5" type="radio" name="star"/>
                        <label class="star star-5" for="star-5" title="Tuyệt vời"></label>

                        <input class="star star-4" id="star-4" value="4" type="radio" name="star"/>
                        <label class="star star-4" for="star-4" title="Tốt"></label>

                        <input class="star star-3" id="star-3" value="3" type="radio" name="star"/>
                        <label class="star star-3" for="star-3" title="Tạm"></label>

                        <input class="star star-2" id="star-2" value="2" type="radio" name="star"/>
                        <label class="star star-2" for="star-2" title="Khá"></label>

                        <input class="star star-1" id="star-1" value="1" type="radio" name="star"/>
                        <label class="star star-1" for="star-1" title="Tệ"></label>
                    </form>
                </div>
                <textarea maxlength="250" id="inpBinhLuan" placeholder="Viết suy nghĩ của bạn vào đây..."></textarea>
                <input id="btnBinhLuan" type="button" onclick="checkGuiBinhLuan()" value="GỬI BÌNH LUẬN">
            </div>
            <!-- <h2>Bình luận</h2> -->
            <div class="container-comment">
                <div class="rating"></div>
                <div class="comment-content">
                </div>
            </div>
        </div>
    </div>';
}

// Thêm footer
function addFooter()
{
    echo '
    <!-- ============== Alert Box ============= -->
    <div id="alert">
        <span id="closebtn">&otimes;</span>
    </div>

    <!-- ============== Footer ============= -->
    <div class="copy-right">
        <p>
            All rights reserved © 2018-' . date("Y") . ' - Designed by
            <span style="color: #eee; font-weight: bold">H-group</span>
        </p>
    </div>';
}

// Thêm contain Taikhoan
function addContainTaiKhoan()
{
    echo '
	<div class="containTaikhoan">
    <span class="close" onclick="showTaiKhoan(false);">&times;</span> 
        <div class=" taikhoan">
            <ul class="tab-group">
                <li class="tab active"><a href="#login">Đăng nhập</a></li>
                <li class="tab"><a href="#signup">Đăng kí</a></li>
            </ul> <!-- /tab group -->
            <div class="tab-content">
                <div id="login">
                    <h1>Chào mừng bạn trở lại!</h1>
                    <!-- <form onsubmit="return logIn(this);"> -->
                    <form action="" method="post" name="formDangNhap" onsubmit="return checkDangNhap();">
                        <div class="field-wrap">
                            <label>
                                Tên đăng nhập<span class="req">*</span>
                            </label>
                            <input name="username" type="text" id="username" required autocomplete="off" />
                        </div> <!-- /user name -->
                        <div class="field-wrap">
                            <label>
                                Mật khẩu<span class="req">*</span>
                            </label>
                            <input name="pass" type="password" id="pass" required autocomplete="off" />
                        </div> <!-- pass -->
                        <p class="forgot"><a href="#">Quên mật khẩu?</a></p>
                        <button type="submit" class="button button-block" />Tiếp tục</button>
                    </form> <!-- /form -->
                </div> <!-- /log in -->
                <div id="signup">
                    <h1>Đăng kí miễn phí</h1>
                    <!-- <form onsubmit="return signUp(this);"> -->
                    <form action="" method="post" name="formDangKy" onsubmit="return checkDangKy();">
                        <div class="top-row">
                            <div class="field-wrap">
                                <label>
                                    Họ<span class="req">*</span>
                                </label>
                                <input name="ho" type="text" id="ho" required autocomplete="off" />
                            </div>
                            <div class="field-wrap">
                                <label>
                                    Tên<span class="req">*</span>
                                </label>
                                <input name="ten" id="ten" type="text" required autocomplete="off" />
                            </div>
                        </div> <!-- / ho ten -->
                        <div class="top-row">
                            <div class="field-wrap">
                                <label>
                                    Điện thoại<span class="req">*</span>
                                </label>
                                <input name="sdt" id="sdt" type="text" pattern="\d*" minlength="10" maxlength="12" required autocomplete="off" />
                            </div> <!-- /sdt -->
                            <div class="field-wrap">
                                <label>
                                    Email<span class="req">*</span>
                                </label>
                                <input name="email" id="email" type="email" required autocomplete="off" />
                            </div> <!-- /email -->
                        </div>
                        <div class="field-wrap">
                            <label>
                                Địa chỉ<span class="req">*</span>
                            </label>
                            <input name="diachi" id="diachi" type="text" required autocomplete="off" />
                        </div> <!-- /user name -->
                        <div class="field-wrap">
                            <label>
                                Tên đăng nhập<span class="req">*</span>
                            </label>
                            <input name="newUser" id="newUser" type="text" required autocomplete="off" />
                        </div> <!-- /user name -->
                        <div class="field-wrap">
                            <label>
                                Mật khẩu<span class="req">*</span>
                            </label>
                            <input name="newPass" id="newPass" type="password" required autocomplete="off" />
                        </div> <!-- /pass -->
                        <button type="submit" class="button button-block" />Tạo tài khoản</button>
                    </form> <!-- /form -->
                </div> <!-- /sign up -->
            </div><!-- tab-content -->
        </div> <!-- /taikhoan -->
    </div>
';
}

// Thêm plc (phần giới thiệu trước footer)
function addPlc()
{
    echo '
    <div class="plc">
        <section>
            <ul class="flexContain">
                <li>Giao hàng hỏa tốc trong 1 giờ</li>
                <li>Thanh toán linh hoạt: tiền mặt, visa / master, trả góp</li>
                <li>Trải nghiệm sản phẩm tại nhà</li>
                <li>Lỗi đổi tại nhà trong 1 ngày</li>
                <li>Hỗ trợ suốt thời gian sử dụng.
                    <br>Hotline:
                    <a href="tel:12345678" style="color: #288ad6;">1234.5678</a>
                </li>
            </ul>
        </section>
    </div>';
}