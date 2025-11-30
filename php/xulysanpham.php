<?php
    require_once('../BackEnd/ConnectionDB/DB_classes.php');

    if(!isset($_POST['request']) && !isset($_GET['request'])) die(null);

    switch ($_POST['request']) {
    	// lấy tất cả sản phẩm
    	case 'getall':
				$dssp = (new SanPhamBUS())->select_all();
                for($i = 0; $i < sizeof($dssp); $i++) {
                    // thêm thông tin khuyến mãi
                    $dssp[$i]["KM"] = (new KhuyenMaiBUS())->select_by_id('*', $dssp[$i]['MaKM']);
                    // thêm thông tin hãng
                    $dssp[$i]["LSP"] = (new LoaiSanPhamBUS())->select_by_id('*', $dssp[$i]['MaLSP']);
                }
		    	die (json_encode($dssp));
    		break;

        case 'getbyid':
            $sp = (new SanPhamBUS())->select_by_id("*", $_POST['id']);
            // thêm thông tin khuyến mãi và hãng
            $sp["KM"] = (new KhuyenMaiBUS())->select_by_id('*', $sp['MaKM']);
            $sp["LSP"] = (new LoaiSanPhamBUS())->select_by_id('*', $sp['MaLSP']);

            die (json_encode($sp));
            break;

        case 'getlistbyids':
            $listID = $_POST['listID'];
            $sql = "SELECT * FROM SanPham WHERE ";

            forEach($listID as $id) {
                $sql .= "MaSP=".$id." OR ";
            }
            $sql.=" 1=0";

            $result = (new DB_driver())->get_list($sql);
            
            for($i = 0; $i < sizeof($result); $i++) {
                // thêm thông tin khuyến mãi
                $result[$i]["KM"] = (new KhuyenMaiBUS())->select_by_id('*', $result[$i]['MaKM']);
                // thêm thông tin hãng
                $result[$i]["LSP"] = (new LoaiSanPhamBUS())->select_by_id('*', $result[$i]['MaLSP']);
            }

            die (json_encode($result));
            break;

        case 'phanTich_Filters':
            phanTich_Filters();
            break;

        case 'addFromWeb1':
            addFromWeb1();
            break;

        //thêm
        case 'add':
                // Xử lý upload ảnh
                $uploadDir = '../img/products/';
                $imagePath = '';
                
                if (isset($_FILES['hinhanh']) && $_FILES['hinhanh']['error'] == 0) {
                    $fileName = $_FILES['hinhanh']['name'];
                    $tmpName = $_FILES['hinhanh']['tmp_name'];
                    $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
                    
                    // Kiểm tra định dạng file
                    $allowedExts = ['jpg', 'jpeg', 'png', 'gif'];
                    if (!in_array($fileExt, $allowedExts)) {
                        die(json_encode(['success' => false, 'message' => 'Chỉ chấp nhận file ảnh (jpg, jpeg, png, gif)']));
                    }
                    
                    // Tạo tên file mới với mã sản phẩm
                    $maSP = $_POST['masp'];
                    $newFileName = $maSP . '.' . $fileExt;
                    $targetPath = $uploadDir . $newFileName;
                    
                    // Upload file
                    if (move_uploaded_file($tmpName, $targetPath)) {
                        $imagePath = 'img/products/' . $newFileName;
                    } else {
                        die(json_encode(['success' => false, 'message' => 'Không thể upload ảnh']));
                    }
                } else {
                    die(json_encode(['success' => false, 'message' => 'Vui lòng chọn ảnh']));
                }
                
                // Tạo mảng dữ liệu sản phẩm
                $spAddArr = array(
                    'MaSP' => $_POST['masp'],
                    'MaLSP' => $_POST['company'],
                    'TenSP' => $_POST['name'],
                    'DonGia' => $_POST['price'],
                    'SoLuong' => $_POST['amount'],
                    'HinhAnh' => $imagePath,
                    'MaKM' => isset($_POST['promo']) ? $_POST['promo'] : 'null',
                    'ManHinh' => isset($_POST['screen']) ? $_POST['screen'] : '',
                    'HDH' => isset($_POST['os']) ? $_POST['os'] : '',
                    'CamSau' => isset($_POST['camara']) ? $_POST['camara'] : '',
                    'CamTruoc' => isset($_POST['camaraFront']) ? $_POST['camaraFront'] : '',
                    'CPU' => isset($_POST['cpu']) ? $_POST['cpu'] : '',
                    'Ram' => isset($_POST['ram']) ? $_POST['ram'] : '',
                    'Rom' => isset($_POST['rom']) ? $_POST['rom'] : '',
                    'SDCard' => isset($_POST['microUSB']) ? $_POST['microUSB'] : '',
                    'Pin' => isset($_POST['battery']) ? $_POST['battery'] : '',
                    'SoSao' => 0,
                    'SoDanhGia' => 0,
                    'TrangThai' => 1
                );

                $spBUS = new SanPhamBUS();
                $result = $spBUS->add_new($spAddArr);
                
                if ($result) {
                    die(json_encode(['success' => true, 'message' => 'Thêm sản phẩm thành công']));
                } else {
                    die(json_encode(['success' => false, 'message' => 'Không thể thêm sản phẩm vào database']));
                }
            break;

        // cập nhật sản phẩm
        case 'update':
                $data = $_POST['data'];
                // đảm bảo MaSP tồn tại
                if (!isset($data['MaSP'])) die (json_encode(false));

                $ma = $data['MaSP'];
                // loại bỏ MaSP khỏi dữ liệu để update
                unset($data['MaSP']);

                $spBUS = new SanPhamBUS();
                $res = $spBUS->update_by_id($data, $ma);
                die (json_encode($res));
            break;

        // xóa
        case 'delete':
                $spBUS = new SanPhamBUS();
                $maSPDel = $_POST['maspdelete'];
                
                // Xóa sản phẩm và tất cả dữ liệu liên quan (đánh giá, chi tiết hóa đơn)
                $result = $spBUS->xoa_san_pham_va_lien_quan($maSPDel);
                
                die (json_encode($result));
            break;

        case 'hide' :
            $id = $_POST["id"];
            $trangthai = $_POST["trangthai"];
            die (json_encode((new SanPhamBUS())->capNhapTrangThai($trangthai, $id)));
            break;
    	
    	default:
    		# code...
    		break;
    }

    function phanTich_Filters() {
        $filters = isset($_POST['filters']) ? $_POST['filters'] : [];
        $ori = "SELECT * FROM SanPham WHERE TrangThai=1 AND SoLuong>0";
        $sql = $ori;
        $db = new DB_driver();
        $db->connect();

        // $page = null;
        $tenThanhPhanCanSort = null;
        $typeSort = null;

        forEach($filters as $filter) {
            $dauBang = explode("=", $filter);
            switch ($dauBang[0]) {
                case 'search':
                    $dauBang[1] = explode("+", $dauBang[1]);
                    $dauBang[1] = join(" ", $dauBang[1]);
                    $dauBang[1] = mysqli_escape_string($db->__conn, $dauBang[1]);
                    $sql .= " AND TenSP LIKE '%$dauBang[1]%' ";
                    break;

                case 'price':
                    $prices = explode("-", $dauBang[1]);
                    $giaTu = (int)$prices[0];
                    $giaDen = (int)$prices[1];

                    // nếu giá đến = 0 thì cho giá đến = 100 triệu
                    if($giaDen == 0) $giaDen = 1000000000;

                    $sql .= " AND DonGia >= $giaTu AND DonGia <= $giaDen";
                    break;

                case 'company':
                    $companyID = $dauBang[1];
                    $sql .= " AND MaLSP='$companyID'";
                    break;

                case 'star':
                    $soSao = (int)$dauBang[1];
                    $sql .= " AND SoSao >= $soSao";
                    break;

                case 'promo':
                    // lấy id khuyến mãi
                    $loaikm = $dauBang[1];
                    $khuyenmai = (new DB_driver())->get_row("SELECT * FROM KhuyenMai WHERE LoaiKM='$loaikm'");
                    $khuyenmaiID = $khuyenmai["MaKM"];
                    
                    $sql .= " AND MaKM='$khuyenmaiID'";
                    break;

                case 'sort':
                    $s = explode("-", $dauBang[1]);
                    $tenThanhPhanCanSort = $s[0];
                    $typeSort = ($s[1]=="asc"?"ASC":"DESC");
                    break;

                // case 'page':
                //     $page = $dauBang[1];
                //     break;
                
                default:
                    # code...
                    break;
            }
        }

        // sort phải để cuối
        if($tenThanhPhanCanSort != null && $typeSort != null) {
            $sql .= " ORDER BY $tenThanhPhanCanSort $typeSort";
        }

        // Phân trang
        // if($page != 0 || $page == null) { // nếu == 0 thì trả về hết
        //     if($page == null) $page = 1; // mặc định là trang 1 (nếu không ghi gì hết)
        //     $productsPerPage = 10; // số lượng sản phẩm trong 1 trang
        //     $startIndex = ($page-1)*$productsPerPage;
        //     $sql .= ($sql==$ori?" 1=1 ":""); // fix lỗi dư chữ where
        //     $sql .= " LIMIT $startIndex,$productsPerPage";
        // }

        // chạy sql
        $result = $db->get_list($sql);
        $db->dis_connect();

        for($i = 0; $i < sizeof($result); $i++) {
            // thêm thông tin khuyến mãi
            $result[$i]["KM"] = (new KhuyenMaiBUS())->select_by_id('*', $result[$i]['MaKM']);
            // thêm thông tin hãng
            $result[$i]["LSP"] = (new LoaiSanPhamBUS())->select_by_id('*', $result[$i]['MaLSP']);
        }
        die (json_encode($result));
    }

    function addFromWeb1() {
        $spBUS = new SanPhamBUS();

        $sp = $_POST['sanpham'];
        $loaisanpham = (new DB_driver())->get_row("SELECT * FROM LoaiSanPham WHERE TenLSP='".$sp["company"]."'");

        $sanphamArr = array(
            'MaLSP' => $loaisanpham['MaLSP'],
            'TenSP' => $sp['name'],
            'DonGia' => $sp['price'],
            'SoLuong' => 10,
            'HinhAnh' => $sp['img'],
            'MaKM' => $sp['MaKM'],
            'ManHinh' => $sp['detail']['screen'],
            'HDH' => $sp['detail']['os'],
            'CamSau' => $sp['detail']['camara'],
            'CamTruoc' => $sp['detail']['camaraFront'],
            'CPU' => $sp['detail']['cpu'],
            'Ram' => $sp['detail']['ram'],
            'Rom' => $sp['detail']['rom'],
            'SDCard' => $sp['detail']['microUSB'],
            'Pin' => $sp['detail']['battery'],
            'SoSao' => 0,
            'SoDanhGia' => 0,
            'TrangThai' => 1
        ); 
        
        die (json_encode($spBUS->add_new($sanphamArr)));
    }
?>
