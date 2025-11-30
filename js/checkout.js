// ======================= CHECKOUT PAGE =======================

var listProduct = [];
var currentUser = null;

// Initialize on page load
window.onload = function() {
    console.log("Checkout page loaded");
    khoiTao();
}

function khoiTao() {
    console.log("khoiTao() called");
    khoiTaoTinh();
    khoiTaoGioHang();
    loadUserInfo();
}

// Load cart from localStorage
function khoiTaoGioHang() {
    var cart = localStorage.getItem('giohang');
    console.log('Raw cart from localStorage:', cart);
    if (!cart) {
        displayOrderItems();
        calculateTotal();
        return;
    }
    
    var listGioHang = JSON.parse(cart);
    if (!listGioHang || listGioHang.length === 0) {
        displayOrderItems();
        calculateTotal();
        return;
    }
    
    // Get product IDs
    var listID = [];
    for (var i = 0; i < listGioHang.length; i++) {
        listID.push(listGioHang[i].masp);
    }
    
    // Load full product info from database
    $.ajax({
        type: "POST",
        url: "php/xulysanpham.php",
        dataType: "json",
        data: {
            request: "getlistbyids",
            listID: listID
        },
        success: function(data) {
            // Merge with quantity from cart
            for (var i = 0; i < data.length; i++) {
                var p = data[i];
                for (var j = 0; j < listGioHang.length; j++) {
                    if (p.MaSP == listGioHang[j].masp) {
                        p.SoLuongTrongGio = listGioHang[j].soLuong;
                        break;
                    }
                }
            }
            listProduct = data;
            displayOrderItems();
            calculateTotal();
        },
        error: function(e) {
            console.log("Error loading products:", e);
            displayOrderItems();
            calculateTotal();
        }
    });
}

// Load user info
function loadUserInfo() {
    getCurrentUser(function(user) {
        console.log('User data:', user);
        currentUser = user;
        if (user) {
            // Hide login modal if user is logged in
            var modal = document.querySelector('.containTaikhoan');
            if (modal) {
                modal.style.display = 'none';
            }
            
            var email = user.Email || user.email || '';
            var hovaten = ((user.Ho || user.ho || '') + ' ' + (user.Ten || user.ten || '')).trim();
            var sdt = user.SDT || user.sdt || '';
            
            document.getElementById('email').value = email;
            document.getElementById('hovaten').value = hovaten;
            document.getElementById('sdt').value = sdt;
            document.getElementById('userEmail').textContent = hovaten || email || 'Guest';
        } else {
            // Show login modal if not logged in
            console.log('No user logged in');
        }
    }, function(error) {
        console.log('Error loading user:', error);
    });
}

// Display order items
function displayOrderItems() {
    var container = document.getElementById('orderItems');
    var cartCount = document.querySelector('.cart-count');
    
    console.log('displayOrderItems called, listProduct:', listProduct);
    
    if (!listProduct || listProduct.length === 0) {
        container.innerHTML = '<div style="text-align:center; padding:20px; color:#999;">Giỏ hàng trống</div>';
        if (cartCount) cartCount.textContent = '0';
        return;
    }
    
    if (cartCount) cartCount.textContent = listProduct.length;
    
    var html = '';
    for (var i = 0; i < listProduct.length; i++) {
        var p = listProduct[i];
        console.log('Product:', p);
        
        var price = Number(p.DonGia || 0);
        var discount = 0;
        
        // Handle KM object safely
        if (p.KM && p.KM.GiaTriKM) {
            discount = Number(p.KM.GiaTriKM);
        } else if (p.km && p.km.giatrikm) {
            discount = Number(p.km.giatrikm);
        }
        
        var finalPrice = price - discount;
        var quantity = Number(p.SoLuongTrongGio || 1);
        var total = finalPrice * quantity;
        
        html += `
            <div class="order-item">
                <img src="${p.HinhAnh || ''}" alt="${p.TenSP || ''}" class="order-item-image">
                <div class="order-item-info">
                    <div class="order-item-name">${p.TenSP || 'Sản phẩm'}</div>
                    <div class="order-item-details">Kèm ${quantity}</div>
                </div>
                <div class="order-item-price">
                    ${discount > 0 ? '<div class="order-item-original-price">' + numToString(price) + ' VNĐ</div>' : ''}
                    <div class="order-item-final-price">${numToString(total)} VNĐ</div>
                </div>
            </div>
        `;
    }
    
    container.innerHTML = html;
    console.log('Order items rendered');
}

// Calculate totals
function calculateTotal() {
    var subtotal = 0;
    
    console.log('calculateTotal called');
    
    for (var i = 0; i < listProduct.length; i++) {
        var p = listProduct[i];
        var price = Number(p.DonGia || 0);
        var discount = 0;
        
        if (p.KM && p.KM.GiaTriKM) {
            discount = Number(p.KM.GiaTriKM);
        } else if (p.km && p.km.giatrikm) {
            discount = Number(p.km.giatrikm);
        }
        
        var finalPrice = price - discount;
        var quantity = Number(p.SoLuongTrongGio || 1);
        subtotal += finalPrice * quantity;
    }
    
    var total = subtotal; // No VAT
    
    var subtotalEl = document.getElementById('subtotal');
    var totalEl = document.getElementById('totalPrice');
    
    if (subtotalEl) subtotalEl.textContent = numToString(subtotal) + ' VNĐ';
    if (totalEl) totalEl.textContent = numToString(total) + ' VNĐ';
    
    console.log('Totals calculated - Subtotal:', subtotal, 'Total:', total);
}

// Toggle summary details
function toggleSummary() {
    var summaryExpand = document.querySelector('.summary-expand');
    summaryExpand.classList.toggle('active');
}

// Initialize provinces
function khoiTaoTinh() {
    var tinhSelect = document.getElementById('tinh');
    var provinces = [
        'Hà Nội', 'TP. Hồ Chí Minh', 'Đà Nẵng', 'Hải Phòng', 'Cần Thơ',
        'An Giang', 'Bà Rịa - Vũng Tàu', 'Bắc Giang', 'Bắc Kạn', 'Bạc Liêu',
        'Bắc Ninh', 'Bến Tre', 'Bình Định', 'Bình Dương', 'Bình Phước',
        'Bình Thuận', 'Cà Mau', 'Cao Bằng', 'Đắk Lắk', 'Đắk Nông',
        'Điện Biên', 'Đồng Nai', 'Đồng Tháp', 'Gia Lai', 'Hà Giang',
        'Hà Nam', 'Hà Tĩnh', 'Hải Dương', 'Hậu Giang', 'Hòa Bình',
        'Hưng Yên', 'Khánh Hòa', 'Kiên Giang', 'Kon Tum', 'Lai Châu',
        'Lâm Đồng', 'Lạng Sơn', 'Lào Cai', 'Long An', 'Nam Định',
        'Nghệ An', 'Ninh Bình', 'Ninh Thuận', 'Phú Thọ', 'Phú Yên',
        'Quảng Bình', 'Quảng Nam', 'Quảng Ngãi', 'Quảng Ninh', 'Quảng Trị',
        'Sóc Trăng', 'Sơn La', 'Tây Ninh', 'Thái Bình', 'Thái Nguyên',
        'Thanh Hóa', 'Thừa Thiên Huế', 'Tiền Giang', 'Trà Vinh', 'Tuyên Quang',
        'Vĩnh Long', 'Vĩnh Phúc', 'Yên Bái'
    ];
    
    provinces.forEach(function(province) {
        var option = document.createElement('option');
        option.value = province;
        option.textContent = province;
        tinhSelect.appendChild(option);
    });
}

// Confirm order
function xacNhanDatHang() {
    // Validate form
    var email = document.getElementById('email').value.trim();
    var hovaten = document.getElementById('hovaten').value.trim();
    var sdt = document.getElementById('sdt').value.trim();
    var sonha = document.getElementById('sonha').value.trim();
    var duongpho = document.getElementById('duongpho').value.trim();
    var tinh = document.getElementById('tinh').value;
    var huyen = document.getElementById('huyen').value;
    var phuong = document.getElementById('phuong').value;
    
    if (!email || !hovaten || !sdt) {
        Swal.fire({
            type: 'warning',
            title: 'Thiếu thông tin',
            text: 'Vui lòng điền đầy đủ thông tin khách hàng'
        });
        return;
    }
    
    if (!sonha || !duongpho || !tinh || !huyen || !phuong) {
        Swal.fire({
            type: 'warning',
            title: 'Thiếu thông tin',
            text: 'Vui lòng điền đầy đủ địa chỉ giao hàng'
        });
        return;
    }
    
    if (listProduct.length === 0) {
        Swal.fire({
            type: 'warning',
            title: 'Giỏ hàng trống',
            text: 'Không có sản phẩm để thanh toán'
        });
        return;
    }
    
    // Get payment method
    var paymentMethod = document.querySelector('input[name="payment"]:checked').value;
    
    // Get address
    var lang = document.getElementById('lang').value.trim();
    var ghichu = document.getElementById('ghichu').value.trim();
    var diachi = sonha + ', ' + duongpho + ', ' + phuong + ', ' + huyen + ', ' + tinh;
    if (lang) diachi = lang + ', ' + diachi;
    
    // Prepare order data
    var orderData = {
        email: email,
        hovaten: hovaten,
        sdt: sdt,
        diachi: diachi,
        ghichu: ghichu,
        paymentMethod: paymentMethod,
        products: listProduct
    };
    
    // Show confirmation
    Swal.fire({
        type: 'question',
        title: 'Xác nhận đặt hàng',
        text: 'Bạn có chắc chắn muốn đặt hàng?',
        showCancelButton: true,
        confirmButtonText: 'Đặt hàng',
        cancelButtonText: 'Hủy'
    }).then((result) => {
        if (result.value) {
            // Submit order
            submitOrder(orderData);
        }
    });
}

// Submit order to server
function submitOrder(orderData) {
    // Show loading
    Swal.fire({
        title: 'Đang xử lý...',
        text: 'Vui lòng đợi',
        allowOutsideClick: false,
        onBeforeOpen: () => {
            Swal.showLoading();
        }
    });
    
    // Send to server (using existing xulythanhtoan.php)
    $.ajax({
        url: 'php/xulythanhtoan.php',
        method: 'POST',
        data: {
            email: orderData.email,
            hovaten: orderData.hovaten,
            sdt: orderData.sdt,
            diachi: orderData.diachi,
            ghichu: orderData.ghichu,
            paymentMethod: orderData.paymentMethod,
            listProduct: JSON.stringify(orderData.products)
        },
        success: function(response) {
            Swal.fire({
                type: 'success',
                title: 'Đặt hàng thành công!',
                text: 'Cảm ơn bạn đã mua hàng. Đơn hàng của bạn đang được xử lý.',
                confirmButtonText: 'OK'
            }).then(() => {
                // Clear cart
                localStorage.removeItem('listSanPham');
                // Redirect to home
                window.location.href = 'index.php';
            });
        },
        error: function() {
            Swal.fire({
                type: 'error',
                title: 'Lỗi',
                text: 'Không thể đặt hàng. Vui lòng thử lại sau.'
            });
        }
    });
}
