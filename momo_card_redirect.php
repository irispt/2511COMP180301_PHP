<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chuyển đến thanh toán thẻ MoMo</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .container {
            background: white;
            border-radius: 20px;
            padding: 40px;
            max-width: 500px;
            width: 100%;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            text-align: center;
        }
        
        .logo {
            width: 80px;
            height: 80px;
            margin: 0 auto 20px;
            background: linear-gradient(135deg, #a626d3 0%, #d626b1 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 40px;
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }
        
        h1 {
            color: #333;
            font-size: 24px;
            margin-bottom: 10px;
        }
        
        p {
            color: #666;
            font-size: 16px;
            margin-bottom: 30px;
        }
        
        .loading {
            display: inline-block;
            width: 50px;
            height: 50px;
            border: 4px solid #f3f3f3;
            border-top: 4px solid #a626d3;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin: 20px 0;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        .card-form {
            background: #f8f9fa;
            border-radius: 15px;
            padding: 30px;
            margin-top: 30px;
            display: none;
        }
        
        .card-form.active {
            display: block;
        }
        
        iframe {
            width: 100%;
            min-height: 600px;
            border: none;
            border-radius: 10px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        }
        
        .btn {
            display: inline-block;
            padding: 15px 40px;
            background: linear-gradient(135deg, #a626d3 0%, #d626b1 100%);
            color: white;
            text-decoration: none;
            border-radius: 10px;
            font-weight: 600;
            margin-top: 20px;
            transition: transform 0.2s;
        }
        
        .btn:hover {
            transform: translateY(-2px);
        }
        
        .info {
            background: #fff3cd;
            border: 1px solid #ffc107;
            border-radius: 10px;
            padding: 15px;
            margin-top: 20px;
            font-size: 14px;
            color: #856404;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="logo">💳</div>
        <h1>Thanh toán bằng thẻ VISA/MasterCard</h1>
        <p>Đang chuyển đến trang thanh toán an toàn của MoMo...</p>
        <div class="loading"></div>
        
        <div class="info">
            <strong>💡 Lưu ý:</strong> Nếu không tự động chuyển hướng, vui lòng click vào nút bên dưới
        </div>
        
        <a href="<?php echo htmlspecialchars($_GET['payUrl'] ?? '#'); ?>" class="btn" id="manualBtn">
            Mở trang thanh toán
        </a>
    </div>

    <script>
        // Lấy payUrl từ query string
        const urlParams = new URLSearchParams(window.location.search);
        const payUrl = urlParams.get('payUrl');
        
        console.log('PayUrl:', payUrl);
        
        if (payUrl) {
            // Tự động chuyển hướng sau 2 giây
            setTimeout(() => {
                window.location.href = payUrl;
            }, 2000);
        } else {
            document.querySelector('.container').innerHTML = `
                <div class="logo">❌</div>
                <h1>Lỗi</h1>
                <p>Không tìm thấy link thanh toán</p>
                <a href="giohang.php" class="btn">Quay lại giỏ hàng</a>
            `;
        }
    </script>
</body>
</html>
