<?php
	require_once ("../BackEnd/ConnectionDB/DB_classes.php");
	session_start();

	// Function chuyển số trạng thái thành text và màu
	function getTrangThaiText($trangThai) {
		switch($trangThai) {
			case 1:
				return '<span style="color: orange; font-weight: bold;">Đang xử lý</span>';
			case 2:
				return '<span style="color: blue; font-weight: bold;">Đã xác nhận</span>';
			case 3:
				return '<span style="color: purple; font-weight: bold;">Đã lên đơn</span>';
			case 4:
				return '<span style="color: teal; font-weight: bold;">Đang giao</span>';
			case 5:
				return '<span style="color: green; font-weight: bold;">Đã giao thành công</span>';
			case 0:
				return '<span style="color: red; font-weight: bold;">Đã hủy</span>';
			default:
				return '<span style="color: gray;">Không xác định</span>';
		}
	}

	if (isset($_SESSION['currentUser'])) {
		$manguoidung = $_SESSION['currentUser']['MaND'];
	
		$sql="SELECT * FROM hoadon WHERE MaND=$manguoidung ORDER BY NgayLap DESC";
		$dsdh=(new DB_driver())->get_list($sql);

		if(sizeof($dsdh) > 0) {
			echo '<table class="table table-striped" >
				<tr style="text-align:center;vertical-align:middle;font-size:20px;background-color:coral;color:black!important">
				<th  style="font-weight:600">Mã đơn hàng</th>
				<th  style="font-weight:600">Ngày lập</th>
				<th  style="font-weight:600">Người nhận</th>
				<th  style="font-weight:600">SDT</th>
				<th  style="font-weight:600">Địa chỉ</th>
				<th  style="font-weight:600">Phương thức TT</th>
				<th  style="font-weight:600">Tổng tiền</th>
				<th  style="font-weight:600">Trạng thái</th>
				<th  style="font-weight:600">Xem chi tiết</th>
			</tr>';

			forEach($dsdh as $row) {
					echo '<tr>
						<td  style="text-align:center;vertical-align:middle;">'.$row["MaHD"].'</td>
						<td  style="text-align:center;vertical-align:middle;">'.$row["NgayLap"].'</td>
						<td  style="text-align:center;vertical-align:middle;">'.$row["NguoiNhan"].'</td>
						<td  style="text-align:center;vertical-align:middle;">'.$row["SDT"].'</td>
						<td  style="text-align:center;vertical-align:middle;">'.$row["DiaChi"].'</td>
						<td  style="text-align:center;vertical-align:middle;">'.$row["PhuongThucTT"].'</td>
						<td  style="text-align:center;vertical-align:middle;">'.number_format($row["TongTien"], 0, ',', '.').' đ</td>
						<td  style="text-align:center;vertical-align:middle;">'.getTrangThaiText($row["TrangThai"]).'</td>
						<td  style="text-align:center;vertical-align:middle;">
							<button class="btn btn-info btn-sm" data-toggle="modal" data-target="#exampleModal" onclick="xemChiTiet(\''.$row["MaHD"].'\')">Xem</button>
						</td>
					</tr>'	;	
			}
			echo '</table>';

		} else {
			echo '<h2 style="color:green; text-align:center;">
						Hiện chưa có đơn hàng nào, 
						<a href="index.php" style="color:blue">Mua ngay</a>
					</h2>';
		}
	}
?>