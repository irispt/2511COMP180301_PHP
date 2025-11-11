# HƯỚNG DẪN TEST THANH TOÁN THẺ ATM QUA MOMO

## 🎯 TỔNG QUAN

Hệ thống hỗ trợ **3 phương thức thanh toán**:

1. **💵 COD** - Trực tiếp khi nhận hàng
2. **📱 Ví MoMo** - Quét QR bằng app MoMo
3. **💳 Thẻ ATM/Ngân hàng** - Nhập thẻ trực tiếp trên trang MoMo (hosted form)

## 🔧 CẤU HÌNH MOMO SANDBOX

### API Endpoints
```
Endpoint: https://test-payment.momo.vn/v2/gateway/api/create
Partner Code: MOMO
Access Key: F8BBA842ECF85
Secret Key: K951B6PE1waDMi640xX08PD3vg6EkVlz
```

### Request Types
- **captureWallet**: Thanh toán qua ví MoMo (QR code)
- **payWithATM**: Thanh toán qua thẻ ATM/Ngân hàng (hosted card form)

## 🚀 HƯỚNG DẪN TEST THANH TOÁN THẺ ATM

### Bước 1: Khởi động XAMPP
```powershell
# Mở XAMPP Control Panel
# Start Apache
# Start MySQL
```

### Bước 2: Truy cập website
```
http://localhost/Project-PHP/index.php
```

### Bước 3: Đăng nhập và thêm sản phẩm
1. Click "Tài khoản" → Đăng nhập
2. Thêm sản phẩm vào giỏ hàng
3. Click icon giỏ hàng

### Bước 4: Thanh toán
1. Click nút **"Thanh Toán"**
2. Điền thông tin:
   - Tên người nhận
   - Số điện thoại
   - Địa chỉ
   - **Hình thức thanh toán: chọn "💳 Thẻ ATM/Ngân hàng qua MoMo"**
3. Click **"Xác nhận"**

### Bước 5: Trên trang MoMo Sandbox

#### A. Giao diện MoMo Test sẽ hiển thị:
- QR Code
- **Form nhập thẻ ATM/Napas/Visa/Master**
- Thông tin đơn hàng

#### B. Chọn phương thức thanh toán:
- Click tab **"Thẻ"** hoặc **"ATM Card"**

#### C. Nhập thông tin thẻ TEST:

**🔴 LƯU Ý**: Đây là môi trường TEST - KHÔNG dùng thẻ thật!

##### Thẻ ATM/Napas Test (khuyến nghị):
```
Số thẻ:      9704 0000 0000 0018
Tên chủ thẻ:  NGUYEN VAN A
Ngày hết hạn: 03/07
CVV/CVC:      (không cần hoặc 123)
```

##### Thẻ Visa/MasterCard Test:
```
Visa:
  Số thẻ:      4111 1111 1111 1111
  Expiry:      12/30
  CVC:         111

MasterCard:
  Số thẻ:      5555 5555 5555 4444
  Expiry:      12/30
  CVC:         123
```

#### D. OTP Test (nếu được yêu cầu):
```
OTP: 111111
hoặc
OTP: 123456
```

#### E. Click **"Thanh toán"** / **"Pay"**

### Bước 6: Kết quả

#### ✅ Thanh toán thành công:
- Redirect về `momo_return.php`
- Hiển thị:
  - Icon check màu xanh ✓
  - Mã đơn hàng
  - Mã giao dịch MoMo
  - Số tiền
- Database: `hoadon.TrangThai = 1` (Chờ xác nhận)
- Log file: `logs/momo_create_{orderId}.json`

#### ❌ Thanh toán thất bại:
- Redirect về `momo_return.php`
- Hiển thị:
  - Icon X màu đỏ ✗
  - Mã lỗi
  - Lý do thất bại
- Database: `hoadon.TrangThai = 0` (Đã hủy)

## 📊 KIỂM TRA DATABASE

```sql
-- Xem đơn hàng mới nhất
SELECT * FROM hoadon 
ORDER BY MaHD DESC 
LIMIT 1;

-- Xem chi tiết đơn hàng
SELECT h.*, c.* 
FROM hoadon h
JOIN chitiethoadon c ON h.MaHD = c.MaHD
ORDER BY h.MaHD DESC
LIMIT 10;

-- Xem đơn hàng theo phương thức
SELECT MaHD, NguoiNhan, TongTien, PhuongThucTT, TrangThai, NgayLap
FROM hoadon
WHERE PhuongThucTT LIKE '%MoMo%'
ORDER BY NgayLap DESC;
```

## 📝 KIỂM TRA LOG FILES

### Xem log tạo thanh toán:
```powershell
# Mở folder logs
cd C:\xampp\htdocs\Project-PHP\logs

# Xem file log mới nhất
Get-Content (Get-ChildItem momo_create_*.json | Sort-Object LastWriteTime -Descending | Select-Object -First 1)
```

### Xem log IPN (callback từ MoMo):
```powershell
Get-Content momo_ipn_log.txt -Tail 50
```

## 🧪 TEST CASES

### Test Case 1: Thanh toán thẻ ATM thành công
```
1. Đăng nhập
2. Thêm sản phẩm vào giỏ
3. Thanh toán → Chọn "Thẻ ATM/Ngân hàng qua MoMo"
4. Điền thông tin → Xác nhận
5. Trên MoMo: Chọn tab "Thẻ"
6. Nhập: 9704 0000 0000 0018
7. OTP: 111111
8. Click "Thanh toán"

✅ KẾT QUẢ:
   - Redirect về success page
   - Icon check xanh
   - Giỏ hàng đã clear
   - DB: TrangThai = 1, PhuongThucTT = "MoMo ATM"
   - Log file tồn tại
```

### Test Case 2: Thanh toán Visa thành công
```
1-4. Giống Test Case 1
5. Trên MoMo: Chọn tab "Thẻ" hoặc "Visa/Master"
6. Nhập: 4111 1111 1111 1111
7. Expiry: 12/30, CVC: 111
8. OTP: 123456
9. Click "Thanh toán"

✅ KẾT QUẢ: Tương tự Test Case 1
```

### Test Case 3: So sánh 3 phương thức
```
Test 3 lần với 3 phương thức khác nhau:
A. COD → TrangThai = 1 ngay lập tức
B. Ví MoMo → Redirect QR code
C. Thẻ ATM → Redirect form nhập thẻ

✅ KẾT QUẢ:
   - 3 đơn hàng trong DB
   - PhuongThucTT khác nhau
   - Tất cả TrangThai = 1 (nếu thành công)
```

### Test Case 4: Hủy thanh toán
```
1-5. Giống Test Case 1
6. Trên MoMo: Click "Hủy" hoặc "Cancel"

✅ KẾT QUẢ:
   - Redirect về với resultCode != 0
   - Icon X đỏ
   - DB: TrangThai = 0 (Đã hủy)
```

## 🔍 DEBUG & TROUBLESHOOTING

### Lỗi 1: "Không thể tạo thanh toán"
**Nguyên nhân**: CURL bị tắt hoặc firewall chặn

**Giải pháp**:
```powershell
# Kiểm tra CURL
php -m | Select-String curl

# Nếu không có, mở php.ini và uncomment:
# extension=curl
```

### Lỗi 2: "Signature không hợp lệ"
**Nguyên nhân**: Sai thứ tự tham số trong rawHash

**Giải pháp**: Kiểm tra `php/momo_payment.php` dòng tạo rawHash:
```php
$rawHash = "accessKey={accessKey}&amount={amount}&extraData={extraData}&ipnUrl={ipnUrl}&orderId={orderId}&orderInfo={orderInfo}&partnerCode={partnerCode}&redirectUrl={redirectUrl}&requestId={requestId}&requestType={requestType}";
```

### Lỗi 3: Không thấy option "Thẻ ATM"
**Nguyên nhân**: Cache browser hoặc JS chưa load

**Giải pháp**:
- Hard refresh: Ctrl + F5
- Xóa cache browser
- Kiểm tra console browser (F12) xem có lỗi JS không

### Lỗi 4: Log file không tạo
**Nguyên nhân**: Folder `logs/` không tồn tại hoặc không có quyền ghi

**Giải pháp**:
```powershell
# Tạo folder và set permission
mkdir C:\xampp\htdocs\Project-PHP\logs
icacls C:\xampp\htdocs\Project-PHP\logs /grant Everyone:F
```

## 📱 SỐ THẺ TEST CHÍNH THỨC TỪ MOMO

### ATM/Napas Cards (Việt Nam):
```
9704 0000 0000 0018 (Vietcombank)
9704 0000 0000 0026 (Techcombank)
9704 1234 5678 9010 (Generic test)
```

### International Cards:
```
Visa:
  4111 1111 1111 1111 (Basic)
  4012 8888 8888 1881 (3DS required)

MasterCard:
  5555 5555 5555 4444 (Basic)
  5200 8282 8282 8210 (Debit)

JCB:
  3530 1113 3330 0000
```

### Expiry Date & CVV:
```
Expiry: Bất kỳ ngày nào trong tương lai (VD: 12/30, 03/27)
CVV/CVC: 111, 123, hoặc 789
Name: NGUYEN VAN A (hoặc bất kỳ)
```

## 🔐 BẢO MẬT & LƯU Ý

⚠️ **QUAN TRỌNG**:
1. Đây là môi trường **SANDBOX TEST** - KHÔNG dùng thẻ thật
2. Không lưu thông tin thẻ vào database
3. Tất cả số thẻ trên chỉ hoạt động trong sandbox
4. Secret Key phải được bảo vệ - không commit lên Git
5. Production phải dùng HTTPS + SSL certificate

## 🚀 CHUYỂN LÊN PRODUCTION

Khi deploy lên server thật:

### 1. Đăng ký tài khoản MoMo Business:
- https://business.momo.vn/
- Hoàn thành KYC
- Nhận Partner Code + Keys production

### 2. Thay đổi cấu hình:
```php
// php/momo_payment.php
define('MOMO_ENDPOINT', 'https://payment.momo.vn/v2/gateway/api/create'); // Bỏ "test-"
define('MOMO_PARTNER_CODE', 'YOUR_PRODUCTION_PARTNER_CODE');
define('MOMO_ACCESS_KEY', 'YOUR_PRODUCTION_ACCESS_KEY');
define('MOMO_SECRET_KEY', 'YOUR_PRODUCTION_SECRET_KEY');
define('MOMO_RETURN_URL', 'https://yourdomain.com/momo_return.php'); // HTTPS
define('MOMO_NOTIFY_URL', 'https://yourdomain.com/momo_ipn.php'); // HTTPS
```

### 3. Bật HTTPS:
- Cài SSL certificate (Let's Encrypt free)
- Redirect HTTP → HTTPS
- Update tất cả URLs

### 4. Security checklist:
- [ ] Move secret keys ra file .env
- [ ] Add .env vào .gitignore
- [ ] Enable rate limiting cho API
- [ ] Validate signature từ MoMo IPN
- [ ] Log mọi transaction
- [ ] Monitor fraud detection
- [ ] Set up webhook retry logic

## 📞 HỖ TRỢ

- MoMo Docs: https://developers.momo.vn/
- MoMo Support: support@momo.vn
- Hotline: 1900 54 54 41

---

**Version**: 1.0  
**Last Updated**: <?php echo date('Y-m-d'); ?>  
**Author**: AI Assistant  
**MoMo API Version**: v2.0 (Sandbox)
