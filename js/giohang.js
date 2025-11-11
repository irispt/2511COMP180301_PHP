var TotalPrice = 0;
window.onload = function() {
    khoiTao();

    // thêm tags (từ khóa) vào khung tìm kiếm
    var tags = ["Samsung", "iPhone", "Huawei", "Oppo", "Mobi"];
    for (var t of tags) addTags(t, "index.php?search=" + t)

    var listGioHang = getListGioHang();
    getListFromDB(listGioHang);
    
    console.log("=== ĐANG GẮN EVENT HANDLER ===");
    
    // Thêm event handler cho button xác nhận thanh toán
    $(document).on('click', '#btnXacNhan', function(e) {
        console.log("=== BUTTON CLICKED ===");
        e.preventDefault();
        e.stopPropagation();
        xacNhanThanhToan();
    });
    
    console.log("=== ĐÃ GẮN EVENT HANDLER XONG ===");
}

function getListFromDB(list) {
    if (!list || !list.length) {
        addProductToTable(list);
        return;
    };

    var listID = [];
    for (var p of list) {
        listID.push(p.masp);
    }

    $.ajax({
        type: "POST",
        url: "php/xulysanpham.php",
        dataType: "json",
        timeout: 1500, // sau 1.5 giây mà không phản hồi thì dừng => hiện lỗi
        data: {
            request: "getlistbyids",
            listID: listID
        },
        success: function(data, status, xhr) {
            // addSanPhamToTable(data);
            for (var p of data) {
                for (var g of list) {
                    if (p.MaSP == g.masp) {
                    	if(p.SoLuong >= g.soLuong) { // check đủ hàng
                        	p.SoLuongTrongGio = g.soLuong;
                    	} else {
                    		p.SoLuongTrongGio = p.SoLuong;

                    		g.soLuong = Number(p.SoLuong); // thay dổi trong localstorage luôn 
                    		setListGioHang(list); // cập nhật localstorage
            				animateCartNumber();

                    		Swal.fire({
                    			title: "Không đủ sản phẩm",
                    			type: "error",
                    			text: "Số lượng sản phẩm " + p.TenSP + " trong kho không đủ(" + p.SoLuong + ")"
                    		})
                    	}
                    }
                }
            }
            addProductToTable(data);
        },
        error: function(e) {
            console.log(e.responseText);
        }
    })
}

function addProductToTable(listProduct) {
    var table = document.getElementsByClassName('listSanPham')[0];

    var s = `
		<tbody>
			<tr>
				<th>Sản phẩm</th>
				<th>Giá</th>
				<th>Số lượng</th>
				<th>Thành tiền</th>
				<th>Xóa</th>
			</tr>`;

    if (!listProduct || listProduct.length == 0) {
        s += `
			<tr>
				<td colspan="7"> 
					<h1 style="color:green; background-color:white; font-weight:bold; text-align:center; padding: 15px 0;">
						Giỏ hàng trống !!
					</h1> 
				</td>
			</tr>
		`;
        table.innerHTML = s;
        return;
    }

    var totalPrice = 0;
    for (var i = 0; i < listProduct.length; i++) {
        var p = listProduct[i];
        var masp = p.MaSP;
        var soluongSp = p.SoLuongTrongGio;
        var price = Number(p.DonGia) - Number(p.KM.GiaTriKM);
        var thanhtien = price * soluongSp;

        s += `
			<tr>
				<td class="noPadding">
					<a target="_blank" href="chitietsanpham.html?` + p.MaSP + `" title="Xem chi tiết">
						<img class="smallImg" src="` + p.HinhAnh + `">
						<br>
						` + p.TenSP + `
					</a>
				</td>
				<td class="alignRight">` + numToString(price) + ` ₫</td>
				<td class="soluong" >
					<button onclick="giamSoLuong('` + masp + `')"><i class="fa fa-minus"></i></button>
					<input size="1" onchange="capNhatSoLuongFromInput(this, '` + masp + `')" value=` + soluongSp + `>
					<button onclick="tangSoLuong('` + masp + `')"><i class="fa fa-plus"></i></button>
				</td>
				<td class="alignRight">` + numToString(thanhtien) + ` ₫</td>
				<td class="noPadding"> 
					<i class="fa fa-trash" onclick="xoaSanPhamTrongGioHang(` + masp + ",'" + p.TenSP + `')"></i> 
				</td>
			</tr>
		`;
        // Chú ý nháy cho đúng ở giamsoluong, tangsoluong
        totalPrice += thanhtien;
    }

    TotalPrice = totalPrice;

    s += `
			<tr style="font-weight:bold; text-align:center">
				<td colspan="3">TỔNG TIỀN: </td>
				<td class="alignRight" style="color:red">` + numToString(totalPrice) + ` ₫</td>
				<td></td>
			</tr>
			<tr>
				<td colspan="5">
					<button class="btn btn-primary" data-toggle="modal" data-target="#exampleModal" onclick="thanhToan()">
						<i class="fa fa-usd"></i> Thanh Toán 
					</button> 
					<button class="btn btn-danger" onclick="xoaHet()">
						<i class="fa fa-trash-o"></i> Xóa hết 
					</button>
				</td>
			</tr>
		</tbody>
	`;

    table.innerHTML = s;
}

function xoaSanPhamTrongGioHang(masp, tensp) {

    Swal.fire({
        type: "question",
        title: "Xác nhận?",
        html: "Bạn có chắc muốn xóa sản phẩm <b style='color:red'>" + tensp + "</b> ?",
        grow: "row",
        cancelButtonText: 'Hủy',
        showCancelButton: true

    }).then((result) => {
        if (result.value) {
            var listProduct = getListGioHang();

            for (var i = 0; i < listProduct.length; i++) {
                if (listProduct[i].masp == masp) {
                    listProduct.splice(i, 1);
                    break;
                }
            }

            capNhatMoiThu(listProduct);
        }
    });
}

function thanhToan() {
    var listProduct = getListGioHang();
    if (!listProduct.length) {
        Swal.fire({
            type: 'info',
            title: "Rỗng",
            grow: 'row',
            text: 'Không có mặt hàng nào để thanh toán.'
        });
        return;
    }

    getCurrentUser((user) => {
        if (user == null) {
            Swal.fire({
                title: 'Xin chào!',
                text: 'Bạn cần đăng nhập để mua hàng',
                type: 'info',
                grow: 'row',
                confirmButtonText: 'Đăng nhập',
                cancelButtonText: 'Trở về',
                showCancelButton: true
            }).then((result) => {
                if (result.value) {
                    showTaiKhoan(true);
                }
            })

        } else if (user.TrangThai == 0) {
            Swal.fire({
                title: 'Tài Khoản Bị Khóa!',
                text: 'Tài khoản của bạn hiện đang bị khóa nên không thể mua hàng!',
                type: 'error',
                grow: 'row',
                confirmButtonText: 'Trở về',
                footer: '<a href>Liên hệ với Admin</a>'
            });
        } else {
        	UserHienTai = user;  // biến toàn cục
        	htmlThanhToan(user);
        }

    }, (error) => {
        console.log(error.responseText);
    });
}

function htmlThanhToan(userHienTai) {
	console.log('abc')

	$("#thongtinthanhtoan").html(`
		<div class="form-group">
		    <p>Tổng tiền : <h2>` + TotalPrice.toLocaleString() + `đ </h2></p>
		</div>
		<div class="form-group">
		    <label for="inputTen">Tên người nhận</label>
		    <input class="form-control input-sm" id="inputTen" required type="text" value="` + (userHienTai.Ho + " " + userHienTai.Ten) + `">
		</div>
		<div class="form-group">
		    <label for="inputSDT">SDT người nhận</label>
		    <input class="form-control input-sm" id="inputSDT" required type="text" pattern="\\d*" minlength="10" maxlength="12" value="` + userHienTai.SDT + `">
		</div>
		<div class="form-group">
		    <label for="inputDiaChi">Địa chỉ giao hàng</label>
		    <input class="form-control input-sm" id="inputDiaChi" required type="text" value="` + userHienTai.DiaChi + `">
		</div>
		<div class="form-group">
		    <select class="browser-default custom-select" id="selectHinhThucTT">
		      <option value="" disabled selected>Hình thức thanh toán</option>
			  <option value="Trực tiếp khi nhận hàng">💵 Trực tiếp khi nhận hàng (COD)</option>
			  <option value="MoMo">📱 Ví điện tử MoMo</option>
			  <option value="MoMo ATM">💳 Thẻ ATM/Ngân hàng qua MoMo</option>
			  <option value="MoMo VISA">💳 Thẻ VISA/MasterCard qua MoMo</option>
			</select>
		</div>
	`);
}

function xacNhanThanhToan() {
	console.log("=== BẮT ĐẦU xacNhanThanhToan ===");
	console.log("UserHienTai:", UserHienTai);
	
	// Kiểm tra đăng nhập
	if (!UserHienTai || !UserHienTai.MaND) {
		Swal.fire({
			type: 'warning',
			title: 'Chưa đăng nhập',
			text: 'Vui lòng đăng nhập để đặt hàng!'
		});
		return false;
	}
	
	// Kiểm tra xem đang xử lý không
	if (window.isProcessingPayment) {
		console.log("Đang xử lý thanh toán, vui lòng chờ...");
		return false;
	}
	
	// Validate form
	var ten = $("#inputTen").val();
	var sdt = $("#inputSDT").val();
	var diaChi = $("#inputDiaChi").val();
	var phuongThucTT = $("#selectHinhThucTT").val();
	
	console.log("Form values:", {ten, sdt, diaChi, phuongThucTT});
	
	if (!ten || !sdt || !diaChi || !phuongThucTT) {
		Swal.fire({
			type: 'warning',
			title: 'Thiếu thông tin',
			text: 'Vui lòng điền đầy đủ thông tin!'
		});
		return false;
	}
	
	// Set flag đang xử lý
	window.isProcessingPayment = true;
	
	// Disable button submit
	$('#btnXacNhan').prop('disabled', true).text('Đang xử lý...');
	
	var dulieu = {
		maNguoiDung: UserHienTai.MaND,
		tenNguoiNhan: ten,
		sdtNguoiNhan: sdt,
		diaChiNguoiNhan: diaChi,
		phuongThucTT: phuongThucTT,
		dssp: getListGioHang(),
		tongTien: TotalPrice,
		ngayLap: new Date().toMysqlFormat()
	}
	
	console.log("Gửi đơn hàng:", dulieu);
	
	// Nếu chọn thanh toán MoMo, gọi API riêng
	if (phuongThucTT === "MoMo") {
		xuLyThanhToanMoMo(dulieu, "create_momo_payment");
	} else if (phuongThucTT === "MoMo ATM") {
		xuLyThanhToanMoMo(dulieu, "create_momo_atm_payment");
	} else if (phuongThucTT === "MoMo VISA") {
		xuLyThanhToanMoMo(dulieu, "create_momo_visa_payment");
	} else {
		xuLyThanhToanTrucTiep(dulieu);
	}
	
	return false;
}

// Xử lý thanh toán trực tiếp khi nhận hàng
function xuLyThanhToanTrucTiep(dulieu) {
	// Hiển thị loading
	Swal.fire({
		title: 'Đang xử lý...',
		text: 'Vui lòng chờ trong giây lát',
		allowOutsideClick: false,
		allowEscapeKey: false,
		showConfirmButton: false,
		onBeforeOpen: () => {
			Swal.showLoading();
		}
	});

	$.ajax({
		type: "POST",
		url: "php/xulythanhtoan.php",
		dataType: "json",
		data: {
			request: "themdonhang",
			dulieu: dulieu
		},
		success: function(data) {
			console.log("Đặt hàng thành công:", data);
			
			// Reset flag
			window.isProcessingPayment = false;
			
			// Enable button lại
			$('#btnXacNhan').prop('disabled', false).text('Xác nhận');
			
			// Xóa giỏ hàng NGAY LẬP TỨC
			capNhatMoiThu([]);
			
			// Đóng modal thanh toán trước
			$('#exampleModal').modal('hide');
			
			// Delay 500ms rồi mới hiện thông báo để thấy giỏ hàng clear
			setTimeout(function() {
				Swal.fire({
					type: 'success',
					title: 'Đặt hàng thành công!',
					text: 'Cảm ơn bạn đã mua hàng. Chúng tôi sẽ liên hệ với bạn sớm nhất.',
					confirmButtonText: 'OK'
				});
			}, 500);
		},
		error: function(e) {
			console.error("Lỗi đặt hàng:", e.responseText);
			
			// Reset flag
			window.isProcessingPayment = false;
			
			// Enable button lại
			$('#btnXacNhan').prop('disabled', false).text('Xác nhận');
			
			Swal.fire({
				type: 'error',
				title: 'Lỗi đặt hàng!',
				text: 'Đã có lỗi xảy ra. Vui lòng thử lại.',
				html: e.responseText
			});
		}
	});
}

// Xử lý thanh toán qua MoMo
function xuLyThanhToanMoMo(dulieu, requestType) {
	requestType = requestType || "create_momo_payment"; // Default là ví MoMo
	
	var paymentName = "MoMo";
	if (requestType === "create_momo_atm_payment") {
		paymentName = "thẻ ATM/Ngân hàng";
	} else if (requestType === "create_momo_visa_payment") {
		paymentName = "thẻ VISA/MasterCard";
	}
	
	// Hiển thị loading
	Swal.fire({
		title: 'Đang tạo thanh toán ' + paymentName + '...',
		text: 'Vui lòng chờ trong giây lát',
		allowOutsideClick: false,
		allowEscapeKey: false,
		showConfirmButton: false,
		onBeforeOpen: () => {
			Swal.showLoading();
		}
	});

	$.ajax({
		type: "POST",
		url: "php/momo_payment.php",
		dataType: "json",
		data: {
			request: requestType,
			dulieu: dulieu
		},
		success: function(response) {
			console.log("MoMo response:", response);
			
			if (response.success && response.payUrl) {
				// Xóa giỏ hàng trước khi chuyển sang MoMo
				capNhatMoiThu([]);
				
				// Đóng modal
				$('#exampleModal').modal('hide');
				
				// Hiển thị thông báo và chuyển hướng
				var messageHtml = 'Bạn sẽ được chuyển đến trang thanh toán <b>' + paymentName + '</b>';
				
				// Nếu là thanh toán VISA, thêm hướng dẫn
				if (requestType === "create_momo_visa_payment") {
					messageHtml += '<br><br><div style="background:#fff3cd;padding:10px;border-radius:5px;margin-top:10px;font-size:14px;">💡 <b>Lưu ý:</b> Trên trang MoMo, hãy click vào tab <b>"Thẻ"</b> hoặc <b>"Card"</b> để nhập thông tin thẻ VISA/MasterCard</div>';
				}
				
				Swal.fire({
					type: 'success',
					title: 'Chuyển đến trang thanh toán...',
					html: messageHtml,
					timer: 4000,
					showConfirmButton: true,
					confirmButtonText: 'OK, đã hiểu'
				}).then(() => {
					// Chuyển hướng đến trang thanh toán MoMo
					window.location.href = response.payUrl;
				});
			} else {
				throw new Error(response.message || 'Không thể tạo thanh toán ' + paymentName);
			}
		},
		error: function(e) {
			console.error("Lỗi MoMo:", e.responseText);
			
			// Reset flag
			window.isProcessingPayment = false;
			
			// Enable button lại
			$('#btnXacNhan').prop('disabled', false).text('Xác nhận');
			
			var errorMsg = 'Đã có lỗi xảy ra khi tạo thanh toán ' + paymentName;
			try {
				var errorData = JSON.parse(e.responseText);
				if (errorData.message) errorMsg = errorData.message;
			} catch(ex) {}
			
			Swal.fire({
				type: 'error',
				title: 'Lỗi thanh toán!',
				text: errorMsg
			});
		}
	});
}

function xoaHet() {
    var listProduct = getListGioHang();

    if (listProduct.length) {
        Swal.fire({
            title: 'Xóa Hết?',
            text: 'Bạn có chắc muốn xóa hết sản phẩm trong giỏ! Việc này không thể được hoàn lại.',
            type: 'warning',
            grow: 'row',
            confirmButtonText: 'Tôi đồng ý',
            cancelButtonText: 'Hủy',
            showCancelButton: true

        }).then((result) => {
            if (result.value) {
                listProduct = [];
                capNhatMoiThu(listProduct);
            }
        })
    }
}

// Cập nhật số lượng lúc nhập số lượng vào input
function capNhatSoLuongFromInput(inp, masp) {
    var soLuongMoi = Number(inp.value);
    if (!soLuongMoi || soLuongMoi <= 0) soLuongMoi = 1;

    var listProduct = getListGioHang();

    for (var p of listProduct) {
        if (p.masp == masp && p.soLuong > 0) {
            p.soLuong = soLuongMoi;
        }
    }

    capNhatMoiThu(listProduct);
}

function tangSoLuong(masp) {
    var listProduct = getListGioHang();

    for (var p of listProduct) {
        if (p.masp == masp) {
            p.soLuong++;
        }
    }

    capNhatMoiThu(listProduct);
}

function giamSoLuong(masp) {
    var listProduct = getListGioHang();

    for (var p of listProduct) {
        if (p.masp == masp && p.soLuong > 1) {
            p.soLuong--;
        }
    }

    capNhatMoiThu(listProduct);
}

function capNhatMoiThu(list) { // Mọi thứ
    console.log("=== CẬP NHẬT GIỎ HÀNG ===");
    console.log("Danh sách mới:", list);
    console.log("Số lượng:", list.length);
    
    // Lưu vào localStorage TRƯỚC
    setListGioHang(list);
    
    // Sau đó mới animate (để getSoLuongGioHang() lấy số mới)
    animateCartNumber();

    // cập nhật danh sách sản phẩm ở table
    getListFromDB(list);
}