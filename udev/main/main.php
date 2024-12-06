<?
// ini_set("display_errors", 1);
// ini_set("track_errors", 1);
// ini_set("html_errors", 1);
// error_reporting(E_ALL&~E_NOTICE);
//$menu = "2";
//$smenu = "2";
//메인화면으로 좌측메뉴 숨김처리

include "../common/inc/inc_header.php";  //헤더 
include "../../lib/card_password.php"; //카드정보 암호화

$DB_con = db1();
$base_url = $PHP_SELF;

// 통계값 기준
$sql_search = " WHERE A.mem_Lv NOT IN ('0', '1', '2') AND A.b_Disply = 'N' ";
// 통계 기준 - 현재날짜 기준 7일
if ($search_fr_date != "") {
	//between으로 인한 하루 전날 기준으로 값을 가져오기
	$fr_date = date('Y-m-d', strtotime($search_fr_date . ' -1 day'));
} else {
	//기본적으로 1주일(7일)전인데 between으로 인하여 하루 더 전날로 표현
	$fr_date = date("Y-m-d", strtotime("-8 day"));
	$view_data_date = $fr_date . " ~ " . $to_date;
}
if ($search_to_date != "") {
	$to_date = $search_to_date;
} else {
	$to_date = date("Y-m-d");
}

//단체장일경우 회원리스트 확인하기.
if ($du_udev['lv'] == 2) {
	$group_Member_Query = "SELECT COUNT(idx) AS group_Count FROM TB_MEMBERS_ETC WHERE mem_ChCode = '" . $du_udev['idx'] . "'";
	$group_Member_Stmt = $DB_con->prepare($group_Member_Query);
	$group_Member_Stmt->execute();
	$group_Member_Row = $group_Member_Stmt->fetch(PDO::FETCH_ASSOC);
	$group_Count = $group_Member_Row['group_Count'];
	if ($group_Count == "") {
		$group_Count = 0;
	}
}


$sql_search .= " AND DATE_FORMAT(A.reg_Date, '%Y-%m-%d') BETWEEN :fr_date AND :to_date";
if ($group_Count > 0) {
	$sql_search .= "AND idx IN (SELECT mem_Idx FROM TB_MEMBERS_ETC WHERE mem_ChCode = '" . $du_udev['idx'] . "')";
} else if ($group_Count == 0 && $du_udev['lv'] == 2) {
	$sql_search .= " AND  idx = '" . $du_udev['idx'] . "'";
}

if ($search_fr_date != "") {
	$view_data_date = $search_fr_date . " ~ " . $to_date;
} else {
	$view_data_date = $fr_date . " ~ " . $to_date;
}


// 회원가입수
$cntMemberQuery = "SELECT COUNT(mem_Id)  AS cntRow FROM TB_MEMBERS A {$sql_search} ";
$cntMemberStmt = $DB_con->prepare($cntMemberQuery);
$cntMemberStmt->bindValue(":fr_date", $fr_date);
$cntMemberStmt->bindValue(":to_date", $to_date);
$cntMemberStmt->execute();
$memberRow = $cntMemberStmt->fetch(PDO::FETCH_ASSOC);
$totalMemCnt = $memberRow['cntRow'];

// echo $cntQuery;

//수익통계
$sql_search_point .= " WHERE DATE_FORMAT(reg_Date, '%Y-%m-%d') BETWEEN :fr_date AND :to_date";
if ($group_Count > 0) {
	$sql_search_point .= " AND mem_Id IN (SELECT mem_Id FROM TB_MEMBERS_ETC WHERE mem_ChCode = '" . $du_udev['idx'] . "')";
} else if ($group_Count == 0 && $du_udev['lv'] == 2) {
	$sql_search_point .= " AND  idx = '" . $du_udev['idx'] . "'";
}

$sumProfitQuery = "SELECT SUM(taxi_OrdSPoint) AS sumRow FROM TB_PROFIT_POINT {$sql_search_point}";
$sumProfitStmt = $DB_con->prepare($sumProfitQuery);
$sumProfitStmt->bindparam(":fr_date", $fr_date);
$sumProfitStmt->bindparam(":to_date", $to_date);
$sumProfitStmt->execute();
$profitRow = $sumProfitStmt->fetch(PDO::FETCH_ASSOC);
$totalPrice = $profitRow['sumRow'];

// echo $sumQuery;
// exit;

// 주문합계 -  결제/양도완료된 주문 합계
$sql_search_order = " WHERE taxi_OrdState IN ('1', '2') ";
$sql_search_order .= " AND DATE_FORMAT(reg_Date, '%Y-%m-%d') BETWEEN :fr_date AND :to_date";
if ($group_Count > 0) {
	$sql_search_order .= "AND ( taxi_OrdSMemId IN (SELECT mem_Id FROM TB_MEMBERS_ETC WHERE mem_ChCode = '" . $du_udev['idx'] . "') OR taxi_OrdMemId IN (SELECT mem_Id FROM TB_MEMBERS_ETC WHERE mem_ChCode = '" . $du_udev['idx'] . "') )";
} else if ($group_Count == 0 && $du_udev['lv'] == 2) {
	$sql_search_order .= " AND (  taxi_OrdSMemId = (SELECT mem_Id FROM TB_MEMBERS WHERE idx = '" . $du_udev['idx'] . "') OR  taxi_OrdMemId = (SELECT mem_Id FROM TB_MEMBERS WHERE idx = '" . $du_udev['idx'] . "') )";
}

$cntOrderQuery = "SELECT COUNT(idx) AS cntRow FROM TB_ORDER  {$sql_search_order} ";
$cntOrderStmt = $DB_con->prepare($cntOrderQuery);
$cntOrderStmt->bindValue(":fr_date", $fr_date);
$cntOrderStmt->bindValue(":to_date", $to_date);
$cntOrderStmt->execute();
$orderRow = $cntOrderStmt->fetch(PDO::FETCH_ASSOC);
$totalOrderCnt = $orderRow['cntRow'];


//매칭합계
$sql_search_taxi .= " WHERE DATE_FORMAT(taxi_SDate, '%Y-%m-%d') BETWEEN :fr_date AND :to_date";
if ($group_Count > 0) {
	$sql_search_taxi .= "AND taxi_MemId IN (SELECT mem_Id FROM TB_MEMBERS_ETC WHERE mem_ChCode = '" . $du_udev['idx'] . "')";
} else if ($group_Count == 0 && $du_udev['lv'] == 2) {
	$sql_search_taxi .= " AND taxi_MemId IN (SELECT mem_Id FROM TB_MEMBERS WHERE idx = '" . $du_udev['idx'] . "')";
}
$cntTaxiQuery = "SELECT COUNT(A.idx) AS cntRow FROM TB_STAXISHARING A LEFT OUTER JOIN TB_ORDER C ON A.idx = C.taxi_SIdx {$sql_search_taxi} ";
$cntTaxiStmt = $DB_con->prepare($cntTaxiQuery);
$cntTaxiStmt->bindValue(":fr_date", $fr_date);
$cntTaxiStmt->bindValue(":to_date", $to_date);
$cntTaxiStmt->execute();
$taxiRow = $cntTaxiStmt->fetch(PDO::FETCH_ASSOC);
$totalTaxiCnt = $taxiRow['cntRow'];

// //수익통계 - 그래프
// $sql_search_point = " WHERE (DATE_FORMAT(A.reg_Date,'%Y-%m-%d') >= :fr_date AND DATE_FORMAT(A.reg_Date,'%Y-%m-%d') <= :to_date)";
// if ($group_Count > 0) {
// 	$sql_search_point .= "AND A.taxi_MemId IN (SELECT mem_Id FROM TB_MEMBERS_ETC WHERE mem_ChCode = '" . $du_udev['idx'] . "')";
// } else if ($group_Count == 0 && $du_udev['lv'] == 2) {
// 	$sql_search_point .= " AND  A.taxi_MemId IN (SELECT mem_Id FROM TB_MEMBERS WHERE idx = '" . $du_udev['idx'] . "')";
// }
// $sql_group_point = " GROUP BY left(A.reg_Date,10)";
// $sql_order_point = " ORDER BY left(A.reg_Date,10)";

// // 양도포인트				SUM(CASE WHEN A.taxi_Sign = '1' THEN A.taxi_OrdPoint ELSE 0 END) AS subt_Money,
// $sumPointQuery = "
// 			SELECT 
// 				left(A.reg_Date,10) as DATE,
// 				SUM(CASE WHEN A.taxi_Sign = '0' THEN A.taxi_OrdPoint ELSE 0 END) AS plus_Money,
// 				SUM(CASE WHEN A.taxi_Sign = '0' THEN B.taxi_OrdSPoint ELSE 0 END) AS profit_Money 
// 			FROM TB_POINT_HISTORY A 
// 				INNER JOIN TB_PROFIT_POINT B ON A.taxi_SIdx = B.taxi_SIdx {$sql_search_point} {$sql_group_point} {$sql_order_point}";
// 				// echo $sumPointQuery;
// $sumPointStmt = $DB_con->prepare($sumPointQuery);
// $sumPointStmt->bindparam(":fr_date", $fr_date);
// $sumPointStmt->bindparam(":to_date", $to_date);
// $sumPointStmt->execute();


// $numPointCnt = $sumPointStmt->rowCount();
// if ($numPointCnt > 0) {

// 	$sumPointStmt->setFetchMode(PDO::FETCH_ASSOC);
// 	$label = [];
// 	$data1 = [];
// 	$data2 = [];
// 	while ($pointRow = $sumPointStmt->fetch()) {

// 		$date = $pointRow['DATE'];
// 		$plus_Money = $pointRow['plus_Money'];
// 		$profit_Money = $pointRow['profit_Money'];

// 		array_push($label, $date);
// 		array_push($data1, $plus_Money);
// 		array_push($data2, $profit_Money);
// 	}
// }
//매칭건수 일별 그래프

// $sql_search_mat = " WHERE (DATE_FORMAT(reg_Date,'%Y-%m-%d') >= :fr_date AND DATE_FORMAT(reg_Date,'%Y-%m-%d') <= :to_date)";
$sql_search_mat = " WHERE DATE_ADD(:fr_date, INTERVAL idx DAY) BETWEEN :fr_date AND :to_date";
$sql_group_mat = " GROUP BY sel_Date";
$sql_order_mat = " ORDER BY sel_Date";
$sumMatQuery = "
	SELECT DATE_ADD(:fr_date, INTERVAL idx DAY) AS sel_Date
	,(SELECT COUNT(s.idx) FROM TB_STAXISHARING AS s WHERE DATE_FORMAT(s.reg_Date,'%Y-%m-%d') = sel_Date) AS mat_Cnt
	FROM TB_STAXISHARING {$sql_search_mat} {$sql_group_mat} {$sql_order_mat}";
// echo $sumMatQuery;
// exit;
$sumMatStmt = $DB_con->prepare($sumMatQuery);
$sumMatStmt->bindparam(":fr_date", $fr_date);
$sumMatStmt->bindparam(":to_date", $to_date);
$sumMatStmt->execute();
$numMatCnt = $sumMatStmt->rowCount();
if ($numMatCnt > 0) {
	$sumMatStmt->setFetchMode(PDO::FETCH_ASSOC);
	$label = [];
	$data1 = [];
	while ($matRow = $sumMatStmt->fetch()) {

		$sel_Date = $matRow['sel_Date'];
		$mat_Cnt = $matRow['mat_Cnt'];

		array_push($label, $sel_Date);
		array_push($data1, $mat_Cnt);
	}
}
/*
	echo $sumQuery;
	exit;
*/

//매칭통계 - 그래프
$sql_search_sharing = " WHERE DATE_FORMAT(A.reg_Date,'%Y-%m-%d') BETWEEN :fr_date AND :to_date";
if ($group_Count > 0) {
	$sql_search_sharing .= "AND taxi_MemId IN (SELECT mem_Id FROM TB_MEMBERS_ETC WHERE mem_ChCode = '" . $du_udev['idx'] . "')";
} else if ($group_Count == 0 && $du_udev['lv'] == 2) {
	$sql_search_sharing .= " AND taxi_MemId IN (SELECT mem_Id FROM TB_MEMBERS WHERE idx = '" . $du_udev['idx'] . "')";
}
$sumSharingQuery = "
			SELECT 
				COALESCE(SUM(CASE WHEN A.taxi_State = '1' THEN 1 ELSE 0 END), 0) AS 'CNT1'
				,COALESCE(SUM(CASE WHEN A.taxi_State = '2' THEN 1 ELSE 0 END), 0) AS 'CNT2'
				,COALESCE(SUM(CASE WHEN A.taxi_State = '3' THEN 1 ELSE 0 END), 0) AS 'CNT3'
				,COALESCE(SUM(CASE WHEN A.taxi_State = '5' THEN 1 ELSE 0 END), 0) AS 'CNT4'
				,COALESCE(SUM(CASE WHEN A.taxi_State = '6' THEN 1 ELSE 0 END), 0) AS 'CNT5'
				,COALESCE(SUM(CASE WHEN A.taxi_State = '7' THEN 1 ELSE 0 END), 0) AS 'CNT6'
				,COALESCE(SUM(CASE WHEN A.taxi_State = '8' THEN 1 ELSE 0 END), 0) AS 'CNT7'
			FROM TB_STAXISHARING A	{$sql_search_sharing}";
$sumSharingStmt = $DB_con->prepare($sumSharingQuery);
$sumSharingStmt->bindparam(":fr_date", $fr_date);
$sumSharingStmt->bindparam(":to_date", $to_date);
$sumSharingStmt->execute();

$numSharingCnt = $sumSharingStmt->rowCount();
if ($numSharingCnt > 0) {

	$sumSharingStmt->setFetchMode(PDO::FETCH_ASSOC);
	while ($sharingRow = $sumSharingStmt->fetch()) {

		$CNT1 = $sharingRow['CNT1'];
		$CNT2 = $sharingRow['CNT2'];
		$CNT3 = $sharingRow['CNT3'];
		$CNT4 = $sharingRow['CNT4'];
		$CNT5 = $sharingRow['CNT5'];
		$CNT6 = $sharingRow['CNT6'];
		$CNT7 = $sharingRow['CNT7'];

		$tot_CNT = (int)$CNT1 + (int)$CNT2 + (int)$CNT3 + (int)$CNT4 + (int)$CNT5 + (int)$CNT6 + (int)$CNT7;

		if ($tot_CNT > 0) {
			$P_CNT1 = round((int)$CNT1 / (int)$tot_CNT * 100);
			$P_CNT2 = round((int)$CNT2 / (int)$tot_CNT * 100);
			$P_CNT3 = round((int)$CNT3 / (int)$tot_CNT * 100);
			$P_CNT4 = round((int)$CNT4 / (int)$tot_CNT * 100);
			$P_CNT5 = round((int)$CNT5 / (int)$tot_CNT * 100);
			$P_CNT6 = round((int)$CNT6 / (int)$tot_CNT * 100);
			$P_CNT7 = round((int)$CNT7 / (int)$tot_CNT * 100);
		} else {
			$P_CNT1 = 0;
			$P_CNT2 = 0;
			$P_CNT3 = 0;
			$P_CNT4 = 0;
			$P_CNT5 = 0;
			$P_CNT6 = 0;
			$P_CNT7 = 0;
		}
	}
}

//주문통계 - 그래프
$sql_search_order = " WHERE DATE_FORMAT(A.reg_Date,'%Y-%m-%d') BETWEEN :fr_date AND :to_date";
if ($group_Count > 0) {
	$sql_search_order .= "AND ( taxi_OrdSMemId IN (SELECT mem_Id FROM TB_MEMBERS_ETC WHERE mem_ChCode = '" . $du_udev['idx'] . "') OR taxi_OrdMemId IN (SELECT mem_Id FROM TB_MEMBERS_ETC WHERE mem_ChCode = '" . $du_udev['idx'] . "') )";
} else if ($group_Count == 0 && $du_udev['lv'] == 2) {
	$sql_search_order .= " AND (  taxi_OrdSMemId = (SELECT mem_Id FROM TB_MEMBERS WHERE idx = '" . $du_udev['idx'] . "') OR  taxi_OrdMemId = (SELECT mem_Id FROM TB_MEMBERS WHERE idx = '" . $du_udev['idx'] . "') )";
}

$sumOrderQuery = "
			SELECT 
				COALESCE(SUM(CASE WHEN A.taxi_OrdState = '0' THEN 1 ELSE 0 END), 0) AS '0_CNT'
				,COALESCE(SUM(CASE WHEN A.taxi_OrdState in ('1', '2') THEN 1 ELSE 0 END), 0) AS '12_CNT'
				,COALESCE(SUM(CASE WHEN A.taxi_OrdState = '3' THEN 1 ELSE 0 END), 0) AS '3_CNT'
			FROM TB_ORDER A	{$sql_search_order}";
$sumOrderStmt = $DB_con->prepare($sumOrderQuery);
$sumOrderStmt->bindparam(":fr_date", $fr_date);
$sumOrderStmt->bindparam(":to_date", $to_date);
$sumOrderStmt->execute();

$numOrderCnt = $sumOrderStmt->rowCount();
if ($numOrderCnt > 0) {

	$sumOrderStmt->setFetchMode(PDO::FETCH_ASSOC);
	while ($orderRow = $sumOrderStmt->fetch()) {

		$orderCNT1 = $orderRow['0_CNT'];
		$orderCNT2 = $orderRow['12_CNT'];
		$orderCNT3 = $orderRow['3_CNT'];

		$ordertot_CNT = (int)$orderCNT1 + (int)$orderCNT2 + (int)$orderCNT3;
		if ($ordertot_CNT) {
			$orderP_CNT1 = round((int)$orderCNT1 / (int)$ordertot_CNT * 100);
			$orderP_CNT2 = round((int)$orderCNT2 / (int)$ordertot_CNT * 100);
			$orderP_CNT3 = round((int)$orderCNT3 / (int)$ordertot_CNT * 100);
		} else {
			$orderP_CNT1 = 0;
			$orderP_CNT2 = 0;
			$orderP_CNT3 = 0;
		}

		$orderp_cnt = [$orderP_CNT1, $orderP_CNT2, $orderP_CNT3];
	}
}
//문의리스트
$sql_search_online = " WHERE DATE_FORMAT(A.reg_Date,'%Y-%m-%d') BETWEEN :fr_date AND :to_date";
if ($group_Count > 0) {
	$sql_search_online .= "AND b_MemId IN (SELECT mem_Id FROM TB_MEMBERS_ETC WHERE mem_ChCode = '" . $du_udev['idx'] . "')";
} else if ($group_Count == 0 && $du_udev['lv'] == 2) {
	$sql_search_online .= " AND b_MemId IN (SELECT mem_Id FROM TB_MEMBERS WHERE idx = '" . $du_udev['idx'] . "')";
}
$sql_order_online = " ORDER BY A.reg_Date DESC limit 5;";

$sumOnlineQuery = "
			SELECT 
				A.idx
				,A.b_Part
				,A.b_Name
				,A.b_MemId
				,A.b_Title
				,A.b_State
				,A.reg_Date
			FROM TB_ONLINE A {$sql_search_online} {$sql_order_online}";
$sumOnlineStmt = $DB_con->prepare($sumOnlineQuery);
$sumOnlineStmt->bindparam(":fr_date", $fr_date);
$sumOnlineStmt->bindparam(":to_date", $to_date);
$sumOnlineStmt->execute();

$numOnlineCnt = $sumOnlineStmt->rowCount();

if ($du_udev['lv'] != 2) {
	//환전요청리스트
	$sql_search_exc = " WHERE (DATE_FORMAT(A.reg_Date,'%Y-%m-%d') >= :fr_date AND DATE_FORMAT(A.reg_Date,'%Y-%m-%d') <= :to_date)";
	$sql_order_exc = " ORDER BY A.reg_Date DESC LIMIT 5";

	$sumExcQuery = "
			SELECT 
				A.idx
				,A.mem_Id
				,(SELECT mem_Tel FROM TB_MEMBERS WHERE mem_Id = A.mem_Id ORDER BY idx DESC LIMIT 1) AS mem_Tel
				,(SELECT mem_NickNm FROM TB_MEMBERS WHERE mem_Id = A.mem_Id ORDER BY idx DESC LIMIT 1) AS mem_Nm
				,(SELECT bank_OName FROM TB_PAYMENT_BANK WHERE idx = A.exc_Idx) AS exc_Name
				,(SELECT bank_Name FROM TB_PAYMENT_BANK WHERE idx = A.exc_Idx) AS exc_BName
				,(SELECT bank_Number FROM TB_PAYMENT_BANK WHERE idx = A.exc_Idx) AS exc_Number
				,A.exc_Price
				,A.e_Disply
				,A.reg_Date
			FROM TB_POINT_EXC A {$sql_search_exc} {$sql_order_exc}";
	$sumExcStmt = $DB_con->prepare($sumExcQuery);
	$sumExcStmt->bindparam(":fr_date", $fr_date);
	$sumExcStmt->bindparam(":to_date", $to_date);
	$sumExcStmt->execute();

	$numExcCnt = $sumExcStmt->rowCount();
}


/*
	//탈퇴회원수
	$mcntQuery = "";
	$mcntQuery = "SELECT COUNT(idx) AS mCnt FROM TB_MEMBERS  WHERE b_Disply = 'Y' " ;
	$mcntStmt = $DB_con->prepare($mcntQuery);
	$mcntStmt->execute();
	$mcRow = $mcntStmt->fetch(PDO::FETCH_ASSOC);
	$leave_count = $mcRow['mCnt'];

	//회원등급
	$mquery = "";
	$mquery = "SELECT memLv, memLv_Name FROM TB_MEMBER_LEVEL WHERE 1 = 1 ORDER BY memLv ASC" ;
	$mstmt = $DB_con->prepare($mquery);
	$mstmt->execute();

	$qstr = "fr_date=".urlencode($fr_date)."&amp;to_date=".urlencode($to_date)."&amp;findType=".urlencode($findType)."&amp;findOs=".urlencode($findOs)."&amp;findword=".urlencode($findword);
	*/

include "../common/inc/inc_gnb.php";  //헤더 
include "../common/inc/inc_menu.php";  //메뉴 

?>
<script type="text/javascript" src="<?= DU_UDEV_DIR ?>/member/js/member.js"></script>
<!-- 
// jQuery UI CSS파일 
<link rel="stylesheet" href="http://code.jquery.com/ui/1.8.18/themes/base/jquery-ui.css" type="text/css" />
// jQuery 기본 js파일
<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js"></script>
// jQuery UI 라이브러리 js파일
<script src="http://code.jquery.com/ui/1.8.18/jquery-ui.min.js"></script> -->

<!-- Custom fonts for this template-->
<link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
<link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">

<!-- Custom styles for this template-->
<link href="css/sb-admin-2.min.css" rel="stylesheet">

<!-- Page level plugins -->
<script src="vendor/chart.js/Chart.min.js"></script>





<!-- Bootstrap core JavaScript-->
<!-- <script src="vendor/jquery/jquery.min.js"></script> -->
<script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>

<!-- Core plugin JavaScript-->
<script src="vendor/jquery-easing/jquery.easing.min.js"></script>

<!-- Custom scripts for all pages-->
<script src="js/sb-admin-2.min.js"></script>


<!-- Page level custom scripts -->
<!--<script src="js/demo/chart-pie-demo.js"></script>-->
<!-- <script src="js/demo/chart-area-demo.js"></script>
<script src="js/demo/chart-bar-demo.js"></script> -->
<div id="wrapper">
	<div id="container" class="container-small" style="width:100%;">
		<div class="container_wr">
			<h1 id="container_title">Dashboard</h1>

			<form class="local_sch03 local_sch" autocomplete="off">
				<div class="sch_last">
					<strong>가입일</strong>
					<input type="text" name="search_fr_date" id="search_fr_date" value="<?= $search_fr_date ?>" class="frm_input" size="11" maxlength="10">
					<label for="search_fr_date" class="sound_only">시작일</label>
					~
					<input type="text" name="search_to_date" id="search_to_date" value="<?= $search_to_date ?>" class="frm_input" size="11" maxlength="10">
					<label for="search_to_date" class="sound_only">종료일</label>
					<input type="submit" style="height:40px !important;" value="검색" class="btn_submit">

					<a href="<?= $base_url ?>" class="btn btn_06">새로고침</a>
				</div>
			</form>
			<script>
				$(function() {
					$("#search_fr_date, #search_to_date").datepicker({
						changeMonth: true,
						changeYear: true,
						dateFormat: "yy-mm-dd",
						showButtonPanel: true,
						yearRange: "c-99:c+99",
						maxDate: "+0d"
					});
				});
			</script>
			</script>
		</div>

		<div>
			<!-- Main Content -->
			<div id="content">


				<!-- Begin Page Content -->
				<div class="container-fluid">

					<!-- Content Row -->
					<div class="row">

						<!-- 회원가입수  -->
						<div class="col-xl-3 col-md-6 mb-4">
							<div class="card border-left-primary shadow h-100 py-2">
								<div class="card-body">
									<div class="row no-gutters align-items-center">
										<div class="col mr-2">
											<div class="text-xs font-weight-bold text-primary text-uppercase mb-1">회원가입수 (<?php echo $view_data_date ?>)</div>
											<div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo number_format($totalMemCnt) ?></div>
										</div>
										<div class="col-auto">
											<i class="fas fa-calendar fa-2x text-gray-300"></i>
										</div>
									</div>
								</div>
							</div>
						</div>

						<!-- 수입합계  -->
						<div class="col-xl-3 col-md-6 mb-4">
							<div class="card border-left-success shadow h-100 py-2">
								<div class="card-body">
									<div class="row no-gutters align-items-center">
										<div class="col mr-2">
											<div class="text-xs font-weight-bold text-success text-uppercase mb-1">수입합계 (<?php echo $view_data_date ?>)</div>
											<div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo number_format($totalPrice) ?></div>
										</div>
										<div class="col-auto">
											<i class="fas fa-dollar-sign fa-2x text-gray-300"></i>
										</div>
									</div>
								</div>
							</div>
						</div>

						<!-- 주문합계-결제/양도완료 -->
						<div class="col-xl-3 col-md-6 mb-4">
							<div class="card border-left-info shadow h-100 py-2">
								<div class="card-body">
									<div class="row no-gutters align-items-center">
										<div class="col mr-2">
											<div class="text-xs font-weight-bold text-info text-uppercase mb-1">주문합계-결제/양도완료 (<?php echo $view_data_date ?>)</div>
											<div class="row no-gutters align-items-center">
												<div class="col-auto">
													<div class="h5 mb-0 mr-3 font-weight-bold text-gray-800"><?php echo number_format($totalOrderCnt) ?></div>
												</div>
												<!--
							<div class="col">
							  <div class="progress progress-sm mr-2">
								<div class="progress-bar bg-info" role="progressbar" style="width: 50%" aria-valuenow="50" aria-valuemin="0" aria-valuemax="100"></div>
							  </div>
							</div>
							-->
											</div>
										</div>
										<div class="col-auto">
											<i class="fas fa-clipboard-list fa-2x text-gray-300"></i>
										</div>
									</div>
								</div>
							</div>
						</div>

						<!-- 매칭합계 -->
						<div class="col-xl-3 col-md-6 mb-4">
							<div class="card border-left-warning shadow h-100 py-2">
								<div class="card-body">
									<div class="row no-gutters align-items-center">
										<div class="col mr-2">
											<div class="text-xs font-weight-bold text-warning text-uppercase mb-1">매칭합계 (<?php echo $view_data_date ?>)</div>
											<div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo number_format($totalTaxiCnt); ?></div>
										</div>
										<div class="col-auto">
											<i class="fas fa-comments fa-2x text-gray-300"></i>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>



					<!--   // -->
					<div class="row">
						<?
						// $implabel = implode("\",\"", $label); 
						// $impdata1 = implode("\",\"", $data1); 
						// $impdata2 = implode("\",\"", $data2); 
						?>
						<!-- 수익통계 -->
						<!-- <div class="col-xl-6 col-lg-7">
							<div class="card shadow mb-4">
								<div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
									<h6 class="m-0 font-weight-bold text-primary">수익통계</h6>
								</div>
								<div class="card-body">
									<div class="chart-area" style="height:100%;">
										<script type="text/javascript" src="js/demo/chart-bar-stack-demo.js"></script>
										<canvas id="stack_bar"></canvas>
										<script>
											var arrlabel = new Array("<?= $implabel ?>");
											var arrdata1 = new Array("<?= $impdata1 ?>");
											var arrdata2 = new Array("<?= $impdata2 ?>");
											var label = '';
											var data1 = '';
											var data2 = '';

											for (var i = 0; i < arrlabel.length; i++) {
												if (i == 0) {
													label += arrlabel[i];
												} else {
													label += ',' + arrlabel[i];
												}
											}
											var split_label = label.split(",");
											for (var i = 0; i < arrdata1.length; i++) {
												if (i == 0) {
													data1 += parseInt(arrdata1[i]);
												} else {
													data1 += ',' + parseInt(arrdata1[i]);
												}
											}
											var split_data1 = data1.split(",");
											for (var i = 0; i < arrdata2.length; i++) {
												if (i == 0) {
													data2 += parseInt(arrdata2[i]);
												} else {
													data2 += ',' + parseInt(arrdata2[i]);
												}
											}
											var split_data2 = data2.split(",");
											var barChartData = {
												labels: split_label,
												datasets: [{
													label: '적립포인트',
													backgroundColor: window.chartColors.red,
													data: split_data1
													/*}, {
														label: '양도포인트',
														backgroundColor: window.chartColors.blue,
														data: [
															randomScalingFactor(),
															randomScalingFactor(),
															randomScalingFactor(),
															randomScalingFactor(),
															randomScalingFactor(),
															randomScalingFactor(),
															randomScalingFactor()
														]*/
												}, {
													label: '총수익금',
													backgroundColor: window.chartColors.green,
													data: split_data2
												}]

											};
											window.onload = function() {
												var ctx = document.getElementById('stack_bar').getContext('2d');
												window.myBar = new Chart(ctx, {
													type: 'bar',
													data: barChartData,
													options: {
														title: {
															display: false,
															text: '적립포인트 + 총수익금 = 양도포인트'
														},
														tooltips: {
															mode: 'index',
															intersect: false
														},
														responsive: true,
														scales: {
															xAxes: [{
																stacked: true,
															}],
															yAxes: [{
																stacked: true
															}]
														}
													}
												});
											};

											document.getElementById('randomizeData').addEventListener('click', function() {
												barChartData.datasets.forEach(function(dataset) {
													dataset.data = dataset.data.map(function() {
														return randomScalingFactor();
													});
												});
												window.myBar.update();
											});
										</script>
									</div>
								</div>
							</div>
						</div> -->
						<div class="col-xl-6 col-lg-7">
							<div class="card shadow mb-4">
								<div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
									<h6 class="m-0 font-weight-bold text-primary">매칭통계 - 일별</h6>
								</div>
								<div class="card-body">
									<div class="chart-area" style="height:100%;">
										<script type="text/javascript" src="js/demo/chart-bar-stack-demo.js"></script>
										<canvas id="stack_bar"></canvas>
										<script>
											var arrlabel = new Array("<?= implode("\",\"", $label); ?>");
											var arrdata1 = new Array("<?= implode("\",\"", $data1); ?>");
											var label = '';
											var data1 = '';

											for (var i = 0; i < arrlabel.length; i++) {
												if (i == 0) {
													label += arrlabel[i];
												} else {
													label += ',' + arrlabel[i];
												}
											}
											var split_label = label.split(",");
											for (var i = 0; i < arrdata1.length; i++) {
												if (i == 0) {
													data1 += parseInt(arrdata1[i]);
												} else {
													data1 += ',' + parseInt(arrdata1[i]);
												}
											}
											var split_data1 = data1.split(",");
											var barChartData = {
												labels: split_label,
												datasets: [{
													label: '매칭수',
													backgroundColor: window.chartColors.red,
													data: split_data1
												}]

											};
											window.onload = function() {
												var ctx = document.getElementById('stack_bar').getContext('2d');
												window.myBar = new Chart(ctx, {
													type: 'bar',
													data: barChartData,
													options: {
														title: {
															display: false,
															text: '총 매칭 수'
														},
														tooltips: {
															mode: 'index',
															intersect: false
														},
														responsive: true,
														scales: {
															xAxes: [{
																stacked: true
															}],
															yAxes: [{
																stacked: true,
																ticks: {
																	beginAtZero: true // y축을 0부터 시작하도록 설정
																	// stepSize: 100 // y축 간격을 1로 설정
																}
															}]
														}
													}
												});
											};
										</script>
									</div>
								</div>
							</div>
						</div>

						<!--매칭통계 -->
						<div class="col-xl-6 col-lg-7">
							<div class="card shadow mb-4">
								<div class="card-header py-3">
									<h6 class="m-0 font-weight-bold text-primary">매칭통계</h6>
								</div>
								<div class="card-body">
									<h4 class="small font-weight-bold">매칭중(<?= $CNT1 ?> 건) <span class="float-right"><?= $P_CNT1; ?>%</span></h4>
									<div class="progress mb-4">
										<div class="progress-bar bg-danger" role="progressbar" style="width: <?= $P_CNT1; ?>%" aria-valuenow="<?= $P_CNT1; ?>" aria-valuemin="0" aria-valuemax="100"></div>
									</div>
									<h4 class="small font-weight-bold">매칭요청(<?= $CNT2 ?> 건) <span class="float-right"><?= $P_CNT2; ?>%</span></h4>
									<div class="progress mb-4">
										<div class="progress-bar bg-warning" role="progressbar" style="width: <?= $P_CNT2; ?>%" aria-valuenow="<?= $P_CNT2; ?>" aria-valuemin="0" aria-valuemax="100"></div>
									</div>
									<h4 class="small font-weight-bold">예약요청(<?= $CNT3 ?> 건) <span class="float-right"><?= $P_CNT3; ?>%</span></h4>
									<div class="progress mb-4">
										<div class="progress-bar" role="progressbar" style="width: <?= $P_CNT3; ?>%" aria-valuenow="<?= $P_CNT3; ?>" aria-valuemin="0" aria-valuemax="100"></div>
									</div>
									<h4 class="small font-weight-bold">만남중(<?= $CNT4 ?> 건) <span class="float-right"><?= $P_CNT4; ?>%</span></h4>
									<div class="progress mb-4">
										<div class="progress-bar bg-info" role="progressbar" style="width: <?= $P_CNT4; ?>%" aria-valuenow="<?= $P_CNT4; ?>" aria-valuemin="0" aria-valuemax="100"></div>
									</div>
									<h4 class="small font-weight-bold">이동중(<?= $CNT5 ?> 건) <span class="float-right"><?= $P_CNT5; ?>%</span></h4>
									<div class="progress mb-4">
										<div class="progress-bar bg-success" role="progressbar" style="width: <?= $P_CNT5; ?>%" aria-valuenow="<?= $P_CNT5; ?>" aria-valuemin="0" aria-valuemax="100"></div>
									</div>
									<h4 class="small font-weight-bold">완료(<?= $CNT6 ?> 건) <span class="float-right"><?= $P_CNT6; ?>%</span></h4>
									<div class="progress mb-4">
										<div class="progress-bar bg-danger" role="progressbar" style="width: <?= $P_CNT6; ?>%" aria-valuenow="<?= $P_CNT6; ?>" aria-valuemin="0" aria-valuemax="100"></div>
									</div>
									<h4 class="small font-weight-bold">취소(<?= $CNT7 ?> 건) <span class="float-right"><?= $P_CNT7; ?>%</span></h4>
									<div class="progress mb-4">
										<div class="progress-bar bg-warning" role="progressbar" style="width: <?= $P_CNT7; ?>%" aria-valuenow="<?= $P_CNT7; ?>" aria-valuemin="0" aria-valuemax="100"></div>
									</div>
								</div>
							</div>
						</div>


					</div>



					<!-- Content Row -->
					<div class="row">

						<!-- 주문통계 -->
						<div class="col-xl-4 col-lg-5">
							<div class="card shadow mb-4">
								<!-- Card Header - Dropdown -->
								<div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
									<h6 class="m-0 font-weight-bold text-primary">주문통계</h6>
								</div>
								<!-- Card Body -->
								<div class="card-body">
									<div class="chart-pie pt-4 pb-2">
										<? if ($numOrderCnt > 0) { ?>
											<canvas id="myPieChart"></canvas>
										<? } else { ?>
											<div id="noDataText" style="display: block; position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); width: 100%; text-align: center;">
												기간 내 주문이 없습니다.
											</div>
										<? } ?>
									</div>
									<div class="mt-4 text-center small">
										<span class="mr-2">
											<i class="fas fa-circle text-primary"></i> 접수
										</span>
										<span class="mr-2">
											<i class="fas fa-circle text-success"></i> 결제/양도완료
										</span>
										<span class="mr-2">
											<i class="fas fa-circle text-info"></i> 취소
										</span>
									</div>
									<script>
										var arrp_data = new Array("<?= implode("\",\"", $orderp_cnt); ?>");
										var p_data = '';
										for (var i = 0; i < arrp_data.length; i++) {
											if (i == 0) {
												p_data += arrp_data[i];
											} else {
												p_data += ',' + arrp_data[i];
											}
										}
										var split_p_data = p_data.split(",");
										var totalSum = split_p_data.reduce((acc, val) => acc + parseInt(val), 0);
										// Set new default font family and font color to mimic Bootstrap's default styling
										Chart.defaults.global.defaultFontFamily = 'Nunito', '-apple-system,system-ui,BlinkMacSystemFont,"Segoe UI",Roboto,"Helvetica Neue",Arial,sans-serif';
										Chart.defaults.global.defaultFontColor = '#858796';

										// Pie Chart Example
										var ctx = document.getElementById("myPieChart");
										var myPieChart = new Chart(ctx, {
											type: 'doughnut',
											data: {
												labels: ["접수", "결제/양도완료", "취소"],
												datasets: [{
													data: split_p_data,
													backgroundColor: ['#4e73df', '#1cc88a', '#36b9cc'],
													hoverBackgroundColor: ['#2e59d9', '#17a673', '#2c9faf'],
													hoverBorderColor: "rgba(234, 236, 244, 1)",
												}],
											},
											options: {
												maintainAspectRatio: false,
												tooltips: {
													backgroundColor: "rgb(255,255,255)",
													bodyFontColor: "#858796",
													borderColor: '#dddfeb',
													borderWidth: 1,
													xPadding: 15,
													yPadding: 15,
													displayColors: false,
													caretPadding: 10,
												},
												legend: {
													display: false
												},
												cutoutPercentage: 80,
											},
										});
									</script>
								</div>
							</div>
						</div>

						<div class="col-xl-8 col-lg-5" style="width:100%;">
							<!-- 문의리스트  -->
							<div class="card shadow mb-4">
								<div class="card-header py-3">
									<h6 class="m-0 font-weight-bold text-primary">문의리스트</h6>
								</div>
								<div class="card-body">
									<div class="table-responsive">
										<table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
											<thead>
												<tr>
													<th>NO</th>
													<th>문의분류</th>
													<th>문의자ID</th>
													<th>제목</th>
													<th>문의일자</th>
													<th>답변여부</th>
												</tr>
											</thead>
											<?
											$onlineNum = 1;
											if ($numOnlineCnt > 0) {
											?>
												<tbody>
													<?

													$sumOnlineStmt->setFetchMode(PDO::FETCH_ASSOC);
													while ($onlineRow = $sumOnlineStmt->fetch()) {
														$idx = $onlineRow['idx'];
														$part = $onlineRow['b_Part'];
														if ($part == '1') {
															$part_name = '매칭생성';
														} else if ($part == '2') {
															$part_name = '매칭신청';
														} else if ($part == '3') {
															$part_name = '게시판';
														}
														$name = $onlineRow['b_Name'];
														$memid = $onlineRow['b_MemId'];
														$title = $onlineRow['b_Title'];
														$state = $onlineRow['b_State'];
														if ($state == '0') {
															$state_name = '답변대기';
														} else {
															$state_name = '답변완료';
														}
														$regdate = $onlineRow['reg_Date'];
													?>
														<tr>
															<td><?= $onlineNum; ?></td>
															<td><?= $part_name; ?></td>
															<td><?= $memid . "<br>(" . $name . ")"; ?></td>
															<td><a href="/udev/etc/inquiryReg.php?mode=mod&idx=<?= $onlineRow['idx'] ?>"><?= $title; ?></a></td>
															<td><?= $regdate; ?></td>
															<td><?= $state_name; ?></td>
														</tr>
													<?
														$onlineNum++;
													}
													?>
												</tbody>
											<?
											} else {
											?>
												<tbody>
													<tr>
														<td colspan='6'>내역이 없습니다.</td>
													</tr>
												</tbody>
											<?
											}
											?>
										</table>
									</div>
								</div>
							</div>
						</div>

					</div>

					<? if ($du_udev['lv'] != 2) { ?>
						<!-- Content Row -->
						<div class="row">

							<div class="col-xl-12 col-lg-5" style="width:100%;">
								<!-- 환전신청리스트  -->
								<div class="card shadow mb-4">
									<div class="card-header py-3">
										<h6 class="m-0 font-weight-bold text-primary">환전신청리스트</h6>
									</div>
									<div class="card-body">
										<div class="table-responsive">
											<table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
												<thead>
													<tr>
														<th>NO</th>
														<th>신청인ID</th>
														<th>연락처</th>
														<th>예금주명</th>
														<th>은행명</th>
														<th>계좌번호</th>
														<th>환전요청금액</th>
														<th>요청일</th>
														<th>환전여부</th>
													</tr>
												</thead>
												<?
												$excNum = 1;
												if ($numExcCnt > 0) {
												?>
													<tbody>
														<?

														$sumExcStmt->setFetchMode(PDO::FETCH_ASSOC);
														while ($excRow = $sumExcStmt->fetch()) {
															$idx = $excRow['idx'];
															$memId = $excRow['mem_Id'];
															$memNm = $excRow['mem_Nm'];
															$memTel = $excRow['mem_Tel'];
															$excName = $excRow['exc_Name'];
															$excBName = $excRow['exc_BName'];
															$exc_Number = $excRow['exc_Number'];

															$excNumber = openssl_decrypt(base64_decode($exc_Number), 'aes-256-cbc', $password, OPENSSL_RAW_DATA, $iv);  //계좌번호 복호화

															$excPrice = $excRow['exc_Price'];
															$eDisply = $excRow['e_Disply'];
															if ($eDisply == 'Y') {
																$eDisply_Name = '입금완료';
															} else if ($eDisply == 'N') {
																$eDisply_Name = '입금대기중';
															} else {
																$eDisply_Name = '환전요청취소';
															}
															$regdate = $excRow['reg_Date'];
														?>
															<tr>
																<td><?= $excNum; ?></td>
																<td><?= $memId . "<br>(" . $memNm . ")"; ?></td>
																<td><?= $memTel; ?></td>
																<td><?= $excName; ?></td>
																<td><?= $excBName; ?></td>
																<td><?= $excNumber; ?></td>
																<td><?= NUMBER_FORMAT($excPrice); ?>원</td>
																<td><?= $regdate; ?></td>
																<td><?= $eDisply_Name; ?></td>
															</tr>
														<?
															$excNum++;
														}
														?>
													</tbody>
												<?
												} else {
												?>
													<tbody>
														<tr>
															<td colspan='9'>내역이 없습니다.</td>
														</tr>
													</tbody>
												<?
												}
												?>
											</table>
										</div>
									</div>
								</div>
							</div>
						</div>
					<? } ?>
				</div>
				<!-- /.container-fluid -->
			</div>
		</div>
	</div>
</div>