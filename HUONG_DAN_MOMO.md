# HƯỚNG DẪN TEST THANH TOÁN MOMO

## 🎯 MỤC TIÊU
Tích hợp thanh toán MoMo vào giỏ hàng PHP, thay thế phương thức "Qua thẻ ngân hàng"

## 📦 CÁC FILE ĐÃ TẠO

### 1. Backend - Xử lý MoMo API
- **php/momo_payment.php** - Tạo yêu cầu thanh toán và gọi MoMo API
- **momo_return.php** - Trang hiển thị kết quả thanh toán (user thấy)
- **momo_ipn.php** - Nhận callback từ MoMo server (background)

### 2. Frontend - Giỏ hàng
- **js/giohang.js** - Đã cập nhật:
  - Thay "Qua thẻ ngân hàng" → "Ví điện tử MoMo"
  - Thêm function `xuLyThanhToanMoMo()`
  - Thêm function `xuLyThanhToanTrucTiep()`
  - Tự động phân luồng theo phương thức thanh toán

### 3. Database
- Không cần thay đổi cấu trúc
- Trường `PhuongThucTT` sẽ lưu "MoMo" hoặc "Trực tiếp khi nhận hàng"
- Trường `TrangThai`:
  - 0 = Đã hủy / Chờ thanh toán MoMo
  - 1 = Chờ xác nhận (đã thanh toán)
  - 2-5 = Các trạng thái khác

## 🔧 CẤU HÌNH MOMO SANDBOX

```php
MOMO_ENDPOINT: https://test-payment.momo.vn/v2/gateway/api/create
MOMO_PARTNER_CODE: MOMO
MOMO_ACCESS_KEY: F8BBA842ECF85
MOMO_SECRET_KEY: K951B6PE1waDMi640xX08PD3vg6EkVlz
MOMO_RETURN_URL: http://localhost/Project-PHP/momo_return.php
MOMO_NOTIFY_URL: http://localhost/Project-PHP/momo_ipn.php
```

## 🚀 HƯỚNG DẪN TEST

### Bước 1: Khởi động XAMPP
```powershell
# Mở XAMPP Control Panel
# Start Apache
# Start MySQL
```

### Bước 2: Truy cập giỏ hàng
```
http://localhost/Project-PHP/giohang.php
```

### Bước 3: Thêm sản phẩm vào giỏ (nếu chưa có)
- Vào trang chủ: http://localhost/Project-PHP/index.php
- Click "Thêm vào giỏ" trên bất kỳ sản phẩm nào

### Bước 4: Thanh toán
1. Vào giỏ hàng
2. Click nút **"Thanh Toán"**
3. Điền thông tin:
   - Tên người nhận
   - SDT
   - Địa chỉ
   - **Hình thức thanh toán: chọn "Ví điện tử MoMo"**
4. Click **"Xác nhận"**

### Bước 5: Trang thanh toán MoMo
- Tự động redirect đến trang test MoMo
- Sẽ thấy QR code và thông tin đơn hàng
- **LƯU Ý**: Đây là môi trường TEST, không cần thanh toán thật

### Bước 6: Mô phỏng thanh toán
**Cách 1: Sử dụng URL test (khuyến nghị)**
- MoMo sẽ cung cấp nút "Complete Payment" trên trang test
- Click vào để hoàn thành giao dịch test

**Cách 2: Truy cập URL thủ công**
Thêm các tham số vào URL momo_return.php:
```
http://localhost/Project-PHP/momo_return.php?resultCode=0&orderId=[OrderID]&amount=[Amount]&transId=123456&message=Success&extraData=[ExtraData]
```

### Bước 7: Kiểm tra kết quả
- **Thành công (resultCode=0)**:
  - Trang hiển thị icon check màu xanh
  - Mã đơn hàng, mã giao dịch MoMo
  - Đơn hàng trong DB có TrangThai = 1
  
- **Thất bại (resultCode != 0)**:
  - Trang hiển thị icon X màu đỏ
  - Đơn hàng trong DB có TrangThai = 0 (Đã hủy)

### Bước 8: Kiểm tra database
```sql
-- Xem đơn hàng vừa tạo
SELECT * FROM hoadon ORDER BY MaHD DESC LIMIT 1;

-- Xem chi tiết đơn hàng
SELECT * FROM chitiethoadon WHERE MaHD = [MaHD vừa tạo];
```

### Bước 9: Kiểm tra log MoMo IPN
```
C:\xampp\htdocs\Project-PHP\momo_ipn_log.txt
```
File này sẽ ghi lại mọi callback từ MoMo server.

## 🧪 TEST CASES

### Test Case 1: Thanh toán MoMo thành công
```
1. Đăng nhập vào hệ thống
2. Thêm 2-3 sản phẩm vào giỏ
3. Thanh toán → Chọn "Ví điện tử MoMo"
4. Điền đầy đủ thông tin → Xác nhận
5. Trang MoMo xuất hiện → Click "Complete Payment"
6. Redirect về momo_return.php
✅ KẾT QUẢ: 
   - Icon check màu xanh
   - Giỏ hàng đã clear
   - DB: TrangThai = 1
```

### Test Case 2: Thanh toán trực tiếp (COD)
```
1. Thêm sản phẩm vào giỏ
2. Thanh toán → Chọn "Trực tiếp khi nhận hàng"
3. Điền thông tin → Xác nhận
✅ KẾT QUẢ:
   - Thông báo "Đặt hàng thành công"
   - Không redirect sang MoMo
   - Giỏ hàng clear
   - DB: TrangThai = 1, PhuongThucTT = "Trực tiếp khi nhận hàng"
```

### Test Case 3: Thanh toán MoMo thất bại
```
1. Thanh toán bình thường với MoMo
2. Tại trang MoMo test, click "Cancel" hoặc timeout
3. Redirect về momo_return.php với resultCode != 0
✅ KẾT QUẢ:
   - Icon X màu đỏ
   - Thông báo lỗi
   - DB: TrangThai = 0 (Đã hủy)
```

### Test Case 4: Kiểm tra security
```
1. Thử truy cập momo_return.php với tham số giả mạo
2. Thử gửi POST đến momo_payment.php không có session
✅ KẾT QUẢ:
   - Phải có validate đầy đủ
   - Không được phép tạo đơn hàng nếu chưa đăng nhập
```

## 📊 FLOW HOẠT ĐỘNG

```
USER                    FRONTEND              BACKEND              MOMO API           DATABASE
  |                        |                      |                     |                 |
  |--Click "Thanh toán"--> |                      |                     |                 |
  |                        |                      |                     |                 |
  |--Chọn "MoMo"---------> |                      |                     |                 |
  |                        |                      |                     |                 |
  |                        |--POST dulieu-------->|                     |                 |
  |                        |   (giohang.js)       |                     |                 |
  |                        |                      |                     |                 |
  |                        |                      |--INSERT hoadon----->|---------------->|
  |                        |                      |                     |                 |
  |                        |                      |--POST create------->|                 |
  |                        |                      |   (signature)       |                 |
  |                        |                      |                     |                 |
  |                        |<--JSON {payUrl}------|<----Response--------|                 |
  |                        |                      |                     |                 |
  |<--Redirect to payUrl---|                      |                     |                 |
  |                        |                      |                     |                 |
  |======QR Code MoMo==============================|                     |                 |
  |                        |                      |                     |                 |
  |--Scan QR / Click------------------------------>|                     |                 |
  |                        |                      |                     |                 |
  |                        |                      |<----IPN callback----|                 |
  |                        |                      | (momo_ipn.php)      |                 |
  |                        |                      |                     |                 |
  |                        |                      |--UPDATE TrangThai-->|---------------->|
  |                        |                      |                     |                 |
  |<--Redirect to momo_return.php---------------->|                     |                 |
  |                        |                      |                     |                 |
  |--Xem kết quả---------->|                      |                     |                 |
```

## 🐛 TROUBLESHOOTING

### Lỗi 1: "Không thể tạo thanh toán MoMo"
**Nguyên nhân**:
- CURL không hoạt động
- Sai cấu hình endpoint/keys
- Firewall chặn

**Giải pháp**:
```powershell
# Kiểm tra CURL trong PHP
php -m | Select-String curl

# Nếu không có, enable trong php.ini:
# extension=curl
```

### Lỗi 2: "Signature không hợp lệ"
**Nguyên nhân**: Sai thứ tự tham số trong rawHash

**Giải pháp**: Kiểm tra lại `php/momo_payment.php` dòng tạo rawHash

### Lỗi 3: Không redirect về momo_return.php
**Nguyên nhân**: Sai MOMO_RETURN_URL

**Giải pháp**:
```php
// Đảm bảo URL đúng với local setup
define('MOMO_RETURN_URL', 'http://localhost/Project-PHP/momo_return.php');
```

### Lỗi 4: IPN không chạy
**Nguyên nhân**: MoMo không thể gọi localhost

**Giải pháp**: 
- Trong test sandbox, IPN có thể không chạy (bình thường)
- Có thể test bằng cách gọi thủ công:
```powershell
Invoke-WebRequest -Uri "http://localhost/Project-PHP/momo_ipn.php" -Method POST -Body '{"resultCode":0,"orderId":"123_456"}' -ContentType "application/json"
```

### Lỗi 5: Giỏ hàng không clear sau thanh toán
**Nguyên nhân**: localStorage không xóa

**Giải pháp**: Kiểm tra function `capNhatMoiThu([])` trong giohang.js

## 📝 LƯU Ý QUAN TRỌNG

1. **Môi trường Sandbox**: Không cần thanh toán thật, chỉ test flow
2. **extraData**: Lưu thông tin MaHD để track đơn hàng
3. **Security**: 
   - Luôn validate signature từ MoMo
   - Kiểm tra user đã đăng nhập
   - Sanitize input
4. **IPN vs Return URL**:
   - **Return URL**: User thấy (có thể bị tắt trước khi load)
   - **IPN**: Server-to-server (tin cậy hơn)
5. **Database Transaction**: Nên dùng transaction khi tạo hóa đơn + chi tiết

## 🎯 CHECKLIST HOÀN THÀNH

- [x] Tạo php/momo_payment.php
- [x] Tạo momo_return.php
- [x] Tạo momo_ipn.php
- [x] Cập nhật js/giohang.js
- [x] Thay đổi option thanh toán
- [x] Test thanh toán MoMo thành công
- [ ] Test thanh toán MoMo thất bại
- [ ] Test thanh toán COD
- [ ] Kiểm tra log IPN
- [ ] Kiểm tra database records

## 🚀 DEPLOY LÊN PRODUCTION

Khi deploy lên server thật:

1. **Thay đổi cấu hình**:
```php
// Đăng ký tài khoản MoMo Business
// Lấy thông tin production:
define('MOMO_ENDPOINT', 'https://payment.momo.vn/v2/gateway/api/create');
define('MOMO_PARTNER_CODE', 'YOUR_PARTNER_CODE');
define('MOMO_ACCESS_KEY', 'YOUR_ACCESS_KEY');
define('MOMO_SECRET_KEY', 'YOUR_SECRET_KEY');
define('MOMO_RETURN_URL', 'https://yourdomain.com/momo_return.php');
define('MOMO_NOTIFY_URL', 'https://yourdomain.com/momo_ipn.php');
```

2. **SSL Certificate**: Bắt buộc HTTPS cho production

3. **Webhook**: Đảm bảo IPN URL có thể truy cập từ internet

4. **Logging**: Tắt debug log, chỉ log lỗi

---

**Tác giả**: AI Assistant  
**Ngày tạo**: <?php echo date('Y-m-d'); ?>  
**Version**: 1.0  
**MoMo API**: v2.0 (Sandbox)
