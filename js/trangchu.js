// DEPRECATED: Pagination variables - no longer using pagination
// var SoLuongTrangHienThi = 4;
// var ProductsPerPage = 4;
var DanhSachSanPham = [];
var DataCompany = [];
var CurrentFilters = [];

window.onload = function() {
    console.log('Trangchu.js loaded - Starting initialization');
    khoiTao();

    // autocomplete cho khung tim kiem
    // autocomplete(document.getElementById('search-box'), list_products);

    // Tags - disabled for new header
    // var tags = ["Samsung", "iPhone", "Coolpad", "Oppo", "Mobi"];
    // for (var t of tags) addTags(t, "index.php?search=" + t);

    // =================== web 2 tìm nâng cao ================
    // Thêm hình vào banner
    setupBanner();

    // Thêm danh sách hãng điện thoại
    setupCompany();

    // Setup dropdown toggles
    setupDropdowns();

    // ==================== End ===========================

    // Thêm sản phẩm vào trang
    var filters = getFilterFromURL();
    console.log('Filters from URL:', filters);
    if (filters.length) { // có filter
        filtersAjax(filters);
    } else { // ko có filter : hiển thị tất cả sản phẩm
        console.log('No filters - showing all products');
        hienThiTatCaSanPham();
    }

    // Thêm chọn mức giá
    addPricesRange(0, 4000000);
    addPricesRange(4000000, 7000000);
    addPricesRange(7000000, 15000000);
    addPricesRange(15000000, 25000000);
    addPricesRange(25000000, 0);

    // Thêm chọn khuyến mãi
    addPromotion('Nothing');
    addPromotion('giamgia');
    addPromotion('tragop');
    addPromotion('moiramat');
    addPromotion('giareonline');

    // Thêm chọn số sao
    addStarFilter(0);
    addStarFilter(1);
    addStarFilter(2);
    addStarFilter(3);
    addStarFilter(4);
    addStarFilter(5);

    // Thêm chọn sắp xếp
    addSortFilter('asc', 'DonGia', 'Giá tăng dần');
    addSortFilter('des', 'DonGia', 'Giá giảm dần');
    addSortFilter('asc', 'SoSao', 'Sao tăng dần');
    addSortFilter('des', 'SoSao', 'Sao giảm dần');
    addSortFilter('asc', 'SoDanhGia', 'Đánh giá tăng dần');
    addSortFilter('des', 'SoDanhGia', 'Đánh giá giảm dần');
    addSortFilter('asc', 'TenSP', 'Tên A-Z');
    addSortFilter('des', 'TenSP', 'Tên Z-A');
};

// ============================== web2 ===========================
function hienThiTatCaSanPham() {
    $('.contain-khungSanPham').html('');
    
    // Load all products without filters
    filtersAjax([]);
}

function hienThiKhungSanPhamMacDinh() {
    // Deprecated - now showing all products
    hienThiTatCaSanPham();
}

// Setup dropdown toggles for filters
function setupDropdowns() {
    var dropdowns = document.querySelectorAll('.timNangCao .dropdown');
    
    dropdowns.forEach(function(dropdown) {
        var button = dropdown.querySelector('.dropbtn');
        
        button.addEventListener('click', function(e) {
            e.stopPropagation();
            
            // Close all other dropdowns
            dropdowns.forEach(function(otherDropdown) {
                if (otherDropdown !== dropdown) {
                    otherDropdown.classList.remove('show');
                }
            });
            
            // Toggle current dropdown
            dropdown.classList.toggle('show');
        });
    });
    
    // Close dropdowns when clicking outside
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.timNangCao .dropdown')) {
            dropdowns.forEach(function(dropdown) {
                dropdown.classList.remove('show');
            });
        }
    });
}

function setupBanner() {
    $.ajax({
        type: "POST",
        url: "php/xulyhinhanh.php",
        dataType: "json",
        timeout: 1500, // sau 1.5 giây mà không phản hồi thì dừng => hiện lỗi
        data: {
            request: "getallbanners",
        },
        success: function(data, status, xhr) {
            for (var url of data) {
                var realPath = url.split('../').join('');
                addBanner(realPath, realPath);
            }
            // If Swiper markup exists, init Swiper; else fall back to existing owl-carousel
            if (document.querySelector('.hero-swiper .swiper-wrapper')) {
                // ensure images are ready, then initialize
                setTimeout(function() {
                    if (window.heroSwiper) {
                        window.heroSwiper.destroy(true, true);
                    }
                    window.heroSwiper = new Swiper('.hero-swiper', {
                        slidesPerView: 1,
                        spaceBetween: 0,
                        loop: true,
                        centeredSlides: false,
                        autoplay: {
                            delay: 3500,
                            disableOnInteraction: false
                        },
                        pagination: { el: '.swiper-pagination', clickable: true },
                        navigation: { nextEl: '.swiper-button-next', prevEl: '.swiper-button-prev' },
                        breakpoints: {
                            600: { slidesPerView: 1 },
                            1000: { slidesPerView: 1 }
                        }
                    });
                }, 50);
            } else {
                $('.owl-carousel').owlCarousel({
                    items: 1,
                    margin: 0,
                    center: false,
                    loop: true,
                    smartSpeed: 450,
                    nav: false,

                    autoplay: true,
                    autoplayTimeout: 3500,

                    responsive: {
                        0: { items: 1 },
                        600: { items: 1 },
                        1000: { items: 1 }
                    }
                });
            }
        },
        error: function() {
            Swal.fire({
                type: "error",
                title: "Lỗi lấy dữ liệu hình ảnh banners (trangchu.js > setUpBanner)",
                html: e.responseText
            });
        }
    });

    $.ajax({
        type: "POST",
        url: "php/xulyhinhanh.php",
        dataType: "json",
        timeout: 1500, // sau 1.5 giây mà không phản hồi thì dừng => hiện lỗi
        data: {
            request: "getsmallbanner",
        },
        success: function(data, status, xhr) {
            for (var url of data) {
                var realPath = url.split('../').join('');
                addSmallBanner(realPath);
            }
        },
        error: function() {
            Swal.fire({
                type: "error",
                title: "Lỗi lấy dữ liệu hình ảnh small banners (trangchu.js > setUpBanner)",
                html: e.responseText
            });
        }
    });
}

// chọn hãng
function setupCompany() {
    $.ajax({
        type: "POST",
        url: "php/xulyloaisanpham.php",
        dataType: "json",
        timeout: 1500, // sau 1.5 giây mà không phản hồi thì dừng => hiện lỗi
        data: {
            request: "getall",
        },
        success: function(data, status, xhr) {
            DataCompany = data;
            for (var c of data) {
                addCompany("img/company/" + c.HinhAnh, c.MaLSP);
            }
        },
        error: function(e) {
            Swal.fire({
                type: "error",
                title: "Lỗi lấy dữ liệu loại sản phẩm (trangchu.js > setupCompany)",
                html: e.responseText
            });
        }
    });
}

function addProductsFromList(list, filters) {
    DanhSachSanPham = list; // lưu danh sách hiện thời

    if (list.length == 0) {
        alertNotHaveProduct(false); // nếu length = 0 thì hiện ko có sản phẩm
        return;
    } else {
        alertNotHaveProduct(true);
    }

    // Display all products without pagination
    for (var p of list) {
        addToWeb(p);
    }

    // Hide pagination
    $('.pagination').hide();
}

// DEPRECATED: No longer using pagination - showing all products
// function chuyenTrang(vitriTrang) {
//     $("#products li.sanPham").remove();
//     pushState(createFilters('page', vitriTrang));
//     var sanPhamDu = DanhSachSanPham.length % ProductsPerPage;
//     var soTrang = parseInt(DanhSachSanPham.length / ProductsPerPage) + (sanPhamDu ? 1 : 0);
//     var trangHienTai = parseInt(vitriTrang < soTrang ? vitriTrang : soTrang);
//     themNutPhanTrang(soTrang, trangHienTai);
//     var start = ProductsPerPage * (trangHienTai - 1);
//     var temp = copyObject(DanhSachSanPham);
//     temp = temp.splice(start, ProductsPerPage);
//     for (var p of temp) {
//         addToWeb(p);
//     }
// }

function filtersAjax(filters, callback) {
    if (!callback) { // ko có call back -> mặc định là thêm vào contain-products
        // hiển thị list sản phẩm
        $(".contain-products").css("display", "block");
        $(".contain-khungSanPham").css("display", "none");
        $(".contain-products li.sanPham").remove(); // xóa các sản phẩm hiện tại
        $(".loader").css("display", "block");
        
        // Clear filter pills if no filters
        if(filters.length == 0) {
            CurrentFilters = [];
            $(".choosedFilter").html("").css("display", "none");
        }
    }
    $.ajax({
        type: "POST",
        url: "php/xulysanpham.php",
        dataType: "json",
        timeout: 1500, // sau 1.5 giây mà không phản hồi thì dừng => hiện lỗi
        data: {
            request: "phanTich_Filters",
            filters: filters
        },
        success: function(data, status, xhr) {

            if (callback) callback(data);
            else {
                addProductsFromList(data, filters);
                addAllChoosedFilter(filters);
                pushState(filters);
                $(".loader").css("display", "none");
            }
        },
        error: function(e) {
            Swal.fire({
                type: "error",
                title: "Lỗi lấy dữ liệu sản phẩm filters (trangchu.js > filtersAjax)",
                html: e.responseText
            });
        }
    })
}

function ajaxThemSanPham(p, onSuccess, onFail) {
    $.ajax({
        type: "POST",
        url: "php/xulysanpham.php",
        dataType: "json",
        timeout: 1500, // sau 1.5 giây mà không phản hồi thì dừng => hiện lỗi
        data: {
            request: "addFromWeb1",
            sanpham: p
        },
        success: function(data, status, xhr) {
            if (onSuccess) onSuccess(data);
            else {
                console.log('Thêm thành công ');
                console.log(data);
            }
        },
        error: function(e) {
            if (onFail) onFail(e);
            else {
                Swal.fire({
                    type: "error",
                    title: "Lỗi thêm sản phẩm sản phẩm " + p + " (trangchu.js > ajaxThemSanPham)",
                    html: e.responseText
                });
            }
        }
    })
}

function addToWeb(p, ele, returnString) {
    // Chuyển star sang dạng tag html
    var rating = "";
    if (p.SoDanhGia >= 0) {
        for (var i = 1; i <= 5; i++) {
            if (i <= p.SoSao) {
                rating += `<i class="fa fa-star"></i>`
            } else {
                rating += `<i class="fa fa-star-o"></i>`
            }
        }
        rating += `<span>` + p.SoDanhGia + ` đánh giá</span>`;
    }

    // Chuyển giá tiền sang dạng tag html
    var giaTri = parseInt(p.DonGia);
    var giaTrikhuyenMai = parseInt(p.KM.GiaTriKM);
    var giaTriSauKM = giaTri - giaTrikhuyenMai;

    var pricediv, khuyenmaidiv;

    if (p.KM.LoaiKM == "GiaReOnline") {

        khuyenmaidiv = promoToWeb(p.KM.LoaiKM, giaTriSauKM);
        pricediv = `<strong>` + giaTriSauKM.toLocaleString() + `&#8363;</strong>
                <span>` + giaTri.toLocaleString() + `&#8363;</span>`;
    } else {

        khuyenmaidiv = promoToWeb(p.KM.LoaiKM, giaTrikhuyenMai);
        pricediv = `<strong>` + giaTri.toLocaleString() + `&#8363;</strong>`;
    }

    // tách theo dấu ' ' vào gắn lại bằng dấu '-', code này giúp bỏ hết khoảng trắng và thay vào bằng dấu '-'.
    // Tạo link tới chi tiết sản phẩm, chuyển tất cả ' ' thành '-'
    var chitietSp = 'chitietsanpham.php?' + p.MaSP;

    // Samsung-style product card
    var newLi =
        `<li class="sanPham product-card" data-aos="fade-up">
        <a href="` + chitietSp + `">
            <div class="product-card-image">
                <img data-src="` + p.HinhAnh + `" class="lazyload" alt="` + (p.TenSP || '') + `">
            </div>
            <div class="product-card-content">
                <h3 class="product-card-title">` + p.TenSP + `</h3>
                <div class="product-card-price">
                    ` + pricediv + `
                </div>
                <div class="product-card-rating">
                    ` + rating + `
                </div>
            </div>
            <div class="product-card-footer">
                <button class="btn-buy-now" onclick="event.preventDefault(); return themVaoGioHang('` + p.MaSP + `', '` + p.TenSP + `');">Mua ngay</button>
                <a href="` + chitietSp + `" class="btn-learn-more">Tìm hiểu thêm</a>
            </div>
        </a>
    </li>`;

    if (returnString) return newLi;

    // Thêm tag <li> vừa tạo vào <ul> homeproduct (mặc định) , hoặc tag ele truyền vào
    var products = ele || document.getElementById('products');
    products.innerHTML += newLi;
    // If AOS is loaded, refresh to bind animations for dynamically added elements
    try { if (window.AOS) window.AOS.refresh(); } catch (e) { }
}

// =========== Đọc dữ liệu từ url ============
function getFilterFromURL() { // tách và trả về mảng bộ lọc trên url
    var fullLocation = window.location.href;
    fullLocation = decodeURIComponent(fullLocation);
    var dauHoi = fullLocation.split('?'); // tách theo dấu ?

    if (dauHoi[1]) {
        var dauVa = dauHoi[1].split('&');
        return dauVa;
    }

    return [];
}

// Thêm sản phẩm vào các khung sản phẩm
function addKhungSanPham(tenKhung, color, filters, len) {
    // convert color to code
    var gradient = `background-image: linear-gradient(120deg, ` + color[0] + ` 0%, ` + color[1] + ` 50%, ` + color[0] + ` 100%);`
    var borderColor = `border-color: ` + color[0];
    var borderA = ` border-left: 2px solid ` + color[0] + `; border-right: 2px solid ` + color[0] + `;`;

    // mở tag
    var s = `<div class="khungSanPham" style="` + borderColor + `">
                <h3 class="tenKhung" style="` + gradient + `">* ` + tenKhung + ` *</h3>
                <div class="listSpTrongKhung flexContain" data-tenkhung="` + tenKhung + `">
                    <div class="loader"></div>
                </div>
                <a class="xemTatCa" onclick='filtersAjax(`+JSON.stringify(filters)+`)' style="` + borderA + `" data-tenkhung="` + tenKhung + `">
                </a>
              </div> <hr>`;


    // thêm khung vào contain-khung
    document.getElementsByClassName('contain-khungSanPham')[0].innerHTML += s;

    // lấy dữ liệu cho vào khung
    filtersAjax(filters, (data) => {
        // thêm các <li> (sản phẩm) vào tag
        var s1 = "";
        var spResult = data;
        if (spResult.length < len) len = spResult.length;

        for (var i = 0; i < len; i++) {
            s1 += addToWeb(spResult[i], null, true);
            // truyền vào 'true' để trả về chuỗi rồi gán vào s
        }

        $("div.listSpTrongKhung[data-tenkhung='" + tenKhung + "']").html(s1);
        $("a.xemTatCa[data-tenkhung='" + tenKhung + "']").html("Xem tất cả " + spResult.length + " sản phẩm");
    })
}

// Nút phân trang
// DEPRECATED: No longer using pagination buttons
// function themNutPhanTrang(soTrang, trangHienTai) {
//     var divPhanTrang = document.getElementsByClassName('pagination')[0];
//     divPhanTrang.innerHTML = "";
//     if (trangHienTai > 1) {
//         divPhanTrang.innerHTML += `<a onclick="chuyenTrang(1)"><i class="fa fa-angle-double-left"></i></a>`;
//         divPhanTrang.innerHTML += `<a onclick="chuyenTrang(` + (trangHienTai - 1) + `)"><i class="fa fa-angle-left"></i></a>`;
//     }
//     if (soTrang > 1) {
//         for (var i = trangHienTai - (SoLuongTrangHienThi - 2); i <= trangHienTai + (SoLuongTrangHienThi - 2); i++) {
//             if (i == trangHienTai) {
//                 divPhanTrang.innerHTML += `<a href="javascript:;" class="current">` + i + `</a>`
//             } else if (i >= 1 && i <= soTrang) {
//                 divPhanTrang.innerHTML += `<a onclick="chuyenTrang(` + i + `)">` + i + `</a>`
//             }
//         }
//     }
//     if (trangHienTai < soTrang) {
//         divPhanTrang.innerHTML += `<a onclick="chuyenTrang(` + (trangHienTai + 1) + `)"><i class="fa fa-angle-right"></i></a>`;
//         divPhanTrang.innerHTML += `<a onclick="chuyenTrang(` + (soTrang) + `)"><i class="fa fa-angle-double-right"></i></a>`;
//     }
// }

function pushState(filters) {
    var str = "index.php?";
    var fsort = "";
    for(var f of filters) {
        if(f.split('=')[0] != 'sort') {
            str += f + "&";
        } else {
            fsort = f;
        }
    }
    if(fsort != '') {
        str += fsort;
    } else if(str.indexOf("&") >= 0) {
        str = str.slice(0, str.length - 1); // loại bỏ "&" dư thừa
    }

    history.pushState("", "", str);
}

// ========== LỌC ===============
function createFilters(type, value) {
    var newFilters = [];

    var chuaCo = true;
    for (var f of CurrentFilters) {
        var fSplit = f.split('=');
        var fType = fSplit[0];
        var fValue = fSplit[1];

        if (fType == type) {
            newFilters.push(type + "=" + value);
            chuaCo = false;
        } else {
            newFilters.push(f);
        }
    }

    if (chuaCo) {
        newFilters.push(type + "=" + value);
    }

    return newFilters;
}

function craeteRemoveFilters(type) {
    var newFilters = [];

    for (var f of CurrentFilters) {
        var fSplit = f.split('=');
        var fType = fSplit[0];
        var fValue = fSplit[1];

        if (fType == type) {
            // không thêm cái cần remove
        } else {
            newFilters.push(f);
        }
    }

    return newFilters;
}

function removeAllFilters() {
    CurrentFilters = [];
    pushState([]);
    $(".choosedFilter").html("").css("display", "none");
    // Load all products without filters
    filtersAjax([]);
}

// Thêm bộ lọc đã chọn vào html
function addChoosedFilter(type, textInside) {
    var divChoosedFilter = document.getElementsByClassName('choosedFilter')[0];
    divChoosedFilter.innerHTML += (`<a onclick="filtersAjax(craeteRemoveFilters('`+type+`'))">
        <h3>` + textInside + ` <i class="fa fa-close"></i></h3>
        </a>`);
}

// Thêm nhiều bộ lọc cùng lúc 
function addAllChoosedFilter(filters) {
    // Lưu bộ lọc mới
    CurrentFilters = filters;

    if (filters.length) {
        // xóa tất cả bộ lọc cũ và thêm nút "Xóa bộ lọc"
        $(".choosedFilter").html(`<a onclick="removeAllFilters()"><h3>Xóa bộ lọc <i class="fa fa-close"></i></h3></a>`);
        $(".choosedFilter").css("display", "");

        for (var f of filters) {
            var data = f.split('=');
            var type = data[0];
            var value = data[1];

            switch (type) {
                case 'company':
                    var tenHang = "";
                    for (var c of DataCompany) {
                        if (c.MaLSP == value) {
                            tenHang = c.TenLSP;
                        }
                    }
                    addChoosedFilter('company', "Hãng " + tenHang);
                    break;

                case 'search':
                    addChoosedFilter('search', searchToString(value));
                    break;
                case 'price':
                    var prices = value.split('-');
                    addChoosedFilter('price', priceToString(prices[0], prices[1]));
                    break;
                case 'promo':
                    addChoosedFilter('promo', promoToString(value));
                    break;
                case 'star':
                    addChoosedFilter('star', starToString(value));
                    break;
                case 'sort':
                    var sorts = value.split('-');
                    var sortBy = sortToString(sorts[0]);
                    var kieuSapXep = (sorts[1] == 'asc' ? 'tăng dần' : 'giảm dần');
                    addChoosedFilter('sort', sortBy + kieuSapXep);
                    break;
                default:
                    // statements_def
                    break;
            }
        }
    }
}

// Thông báo nếu không có sản phẩm
function alertNotHaveProduct(coSanPham) {
    var thongbao = document.getElementById('khongCoSanPham');
    if (!coSanPham) {
        thongbao.style.width = "auto";
        thongbao.style.opacity = "1";
        thongbao.style.margin = "auto"; // Căn giữa
        thongbao.style.transitionDuration = "1s"; // hiện ra từ từ

    } else {
        thongbao.style.width = "0";
        thongbao.style.opacity = "0";
        thongbao.style.margin = "0";
        thongbao.style.transitionDuration = "0s"; // Ngay lâp tức biến mất
    }
}

// ========== Lọc TRONG TRANG ============
// Hiển thị Sản phẩm
function showLi(li) {
    li.style.opacity = 1;
    li.style.width = "239px";
    li.style.borderWidth = "1px";
}
// Ẩn sản phẩm
function hideLi(li) {
    li.style.width = 0;
    li.style.opacity = 0;
    li.style.borderWidth = "0";
}

// Lấy mảng sản phẩm trong trang hiện tại (ở dạng tag html)
function getLiArray() {
    var ul = document.getElementById('products');
    var listLi = ul.getElementsByTagName('li');
    return listLi;
}

// lọc theo tên
function getNameFromLi(li) {
    var a = li.getElementsByTagName('a')[0];
    var h3 = a.getElementsByTagName('h3')[0];
    var name = h3.innerHTML;
    return name;
}

// DEPRECATED: In-page filtering by name - removed search bar
// function filterProductsName(ele) {
//     var filter = ele.value.toUpperCase();
//     var listLi = getLiArray();
//     var coSanPham = false;
//     var soLuong = 0;
//     for (var i = 0; i < listLi.length; i++) {
//         if (getNameFromLi(listLi[i]).toUpperCase().indexOf(filter) > -1 &&
//             soLuong < ProductsPerPage) {
//             showLi(listLi[i]);
//             coSanPham = true;
//             soLuong++;
//         } else {
//             hideLi(listLi[i]);
//         }
//     }
//     alertNotHaveProduct(coSanPham);
// }

// ================= Hàm khác ==================

// Thêm banner
function addBanner(img, link) {
    // If using Swiper, append slides to .swiper-wrapper; otherwise append to owl-carousel
    var swiperWrapper = document.querySelector('.hero-swiper .swiper-wrapper');
    if (swiperWrapper) {
        var slide = `<div class="swiper-slide"><a href="` + link + `"><img data-src="` + img + `" class="lazyload" alt=""></a></div>`;
        swiperWrapper.innerHTML += slide;
    } else {
        var newDiv = `<div class='item'><img src=` + img + `></div>`;
        var banner = document.getElementsByClassName('owl-carousel')[0];
        if (banner) banner.innerHTML += newDiv;
    }
}

function addSmallBanner(img) {
    var newimg = `<img data-src="` + img + `" class="lazyload" style="width:100%;">`;
    var smallbanner = document.getElementsByClassName('smallbanner')[0];
    if (smallbanner) smallbanner.innerHTML += newimg;
}

// Thêm hãng sản xuất
function addCompany(img, nameCompany) {
    var new_tag = `<button onclick="filtersAjax(['company=` + nameCompany + `'])"><img src=` + img + `></button>`;

    var khung_hangSanXuat = document.getElementsByClassName('companyMenu')[0];
    khung_hangSanXuat.innerHTML += new_tag;
}

// Thêm chọn mức giá
function addPricesRange(min, max) {
    var text = priceToString(min, max);
    var value = min + "-" + max;
    var checkbox = `
        <label>
            <input type="checkbox" value="${value}" onchange="handlePriceFilter(this, '${value}')">
            ${text}
        </label>
    `;

    document.getElementsByClassName('pricesRangeFilter')[0]
        .getElementsByClassName('dropdown-content')[0].innerHTML += checkbox;
}

// Handle price filter checkbox
function handlePriceFilter(checkbox, value) {
    filtersAjax(createFilters('price', value));
}

// Thêm chọn khuyến mãi
function addPromotion(name) {
    var text = promoToString(name);
    var promo = `<a onclick="filtersAjax(createFilters('promo', '` + name + `'))">` + text + `</a>`;

    document.getElementsByClassName('promosFilter')[0]
        .getElementsByClassName('dropdown-content')[0].innerHTML += promo;
}

// Thêm chọn số lượng sao
function addStarFilter(value) {
    var text = starToString(value);
    var star = `<a onclick="filtersAjax(createFilters('star', '` + value + `'))">` + text + `</a>`;

    document.getElementsByClassName('starFilter')[0]
        .getElementsByClassName('dropdown-content')[0].innerHTML += star;
}

// Thêm chọn sắp xếp theo giá
function addSortFilter(type, nameFilter, text) {
    var sortTag = `<a onclick="filtersAjax(createFilters('sort', '` + (nameFilter + "-" + type) + `'))">` + text + `</a>`;

    document.getElementsByClassName('sortFilter')[0]
        .getElementsByClassName('dropdown-content')[0].innerHTML += sortTag;
}

//  =================== ToString ====================
function searchToString(value) {
    return "Tìm '" + value + "'";
}

// Chuyển mức giá về dạng chuỗi tiếng việt
function priceToString(min, max) {
    if (min == 0) return 'Below ' + (max / 1000).toLocaleString() + '.000 ₫';
    if (max == 0) return 'Above ' + (min / 1000).toLocaleString() + '.000 ₫';
    return (min / 1000).toLocaleString() + ' ₫ - ' + (max / 1000).toLocaleString() + '.000 ₫';
}

// Chuyển khuyến mãi vễ dạng chuỗi tiếng việt
function promoToString(name) {
    switch (name) {
        case 'tragop':
            return 'Trả góp';
        case 'giamgia':
            return 'Giảm giá';
        case 'giareonline':
            return 'Giá rẻ online';
        case 'moiramat':
            return 'Mới ra mắt';
        case 'Nothing':
            return 'Không khuyến mãi';
    }
}

// Chuyển số sao về dạng chuỗi tiếng việt
function starToString(star) {
    return 'Từ ' + star + ' sao trở lên';
}

// Chuyển các loại sắp xếp về dạng chuỗi tiếng việt
function sortToString(sortBy) {
    switch (sortBy) {
        case 'DonGia':
            return 'Giá ';
        case 'SoSao':
            return 'Sao ';
        case 'SoDanhGia':
            return 'Đánh giá ';
        case 'TenSP':
            return 'Tên ';
        default:
            return '';
    }
}

// Hàm Test, chưa sử dụng
function hideSanPhamKhongThuoc(list) {
    var allLi = getLiArray();
    for (var i = 0; i < allLi.length; i++) {
        var hide = true;
        for (var j = 0; j < list.length; j++) {
            if (getNameFromLi(allLi[i]) == list[j].name) {
                hide = false;
                break;
            }
        }
        if (hide) hideLi(allLi[i]);
    }
}

//companysMenu responsive
function setCompanysMenu() {
    var content = document.getElementsByClassName("companyMenu")[0];
    if (content.style.maxHeight) {
        content.style.maxHeight = null;
        document.getElementById("iconOpenMenu").style.display = "block";
        document.getElementById("iconCloseMenu").style.display = "none";
    } else {
        content.style.maxHeight = content.scrollHeight + "px";
        document.getElementById("iconOpenMenu").style.display = "none";
        document.getElementById("iconCloseMenu").style.display = "block";
    }
}