<?
$menu = "4";
$smenu = "1";

include "../common/inc/inc_header.php";  //헤더 

if ($ridx == "") {
	$msg = "잘못된 접근 방식입니다. 정확한 경로를 통해서 접근 하시길 바랍니다.";
	proc_msg2($msg);
}

$titNm = "투게더 노선 상세";

$DB_con = db1();

// 투게더 닉네임
$mnSql = "  , ( SELECT mem_NickNm FROM TB_MEMBERS WHERE TB_MEMBERS.mem_Id = TB_RTAXISHARING.taxi_RMemId limit 1 ) AS memNickNm  ";

$query = "";
$query = "SELECT taxi_SIdx, taxi_MemId, taxi_RMemId, taxi_RTPrice, taxi_RState {$mnSql}  FROM TB_RTAXISHARING WHERE idx = :idx LIMIT 1 ";
//echo $query."<BR>";
$stmt = $DB_con->prepare($query);
$stmt->bindparam(":idx", $ridx);
$stmt->execute();

$row = $stmt->fetch(PDO::FETCH_ASSOC);

$taxi_SIdx =  trim($row['taxi_SIdx']);
$taxi_MemId =  trim($row['taxi_MemId']);
$taxi_RMemId =  trim($row['taxi_RMemId']);
$taxi_RTPrice = trim($row['taxi_RTPrice']);
$taxi_State = trim($row['taxi_State']);

//생성자 기타 정보보
$minfoeQuery = "";
$minfoeQuery = "SELECT taxi_Type, taxi_Route, taxi_Distance FROM TB_STAXISHARING_INFO WHERE taxi_Idx = :taxi_Idx LIMIT 1 ";
//echo $minfoeQuery."<BR>";
//exit;
$minfoetmt = $DB_con->prepare($minfoeQuery);
$minfoetmt->bindparam(":taxi_Idx", $taxi_SIdx);
$minfoetmt->execute();
$minfoeNum = $minfoetmt->rowCount();
//echo $minfoeNum."<BR>";

if ($minfoeNum < 1) { //아닐경우
} else {
	while ($minfoeRow = $minfoetmt->fetch(PDO::FETCH_ASSOC)) {
		$taxiType = trim($minfoeRow['taxi_Type']);						//출발타입 ( 0: 바로출발, 1: 예약출발)
		$taxiRoute = trim($minfoeRow['taxi_Route']);					// 경유가능여부 ( 0: 경유가능, 1: 경유불가)
		$taxiDistance = trim($minfoeRow['taxi_Distance']);			   // 예상거리
	}
}

//생성자 신청 정보 가져오기
$minfoQuery = "";
$minfoQuery = "SELECT taxi_TPrice, taxi_Price, taxi_ATime, taxi_Per FROM TB_STAXISHARING WHERE idx = :idx LIMIT 1 ";
//echo $minfoQuery."<BR>";
//exit;
$minfoStmt = $DB_con->prepare($minfoQuery);
$minfoStmt->bindparam(":idx", $taxi_SIdx);
$minfoStmt->execute();
$minfoNum = $minfoStmt->rowCount();
//echo $minfoNum."<BR>";

if ($minfoNum < 1) { //아닐경우
} else {
	while ($minfoRow = $minfoStmt->fetch(PDO::FETCH_ASSOC)) {
		$taxiTPrice =  trim($minfoRow['taxi_TPrice']);	    // 총택시요금
		$taxiPrice =  (int)trim($minfoRow['taxi_Price']);	    // 희망쉐어링요금
		$taxiATime =  trim($minfoRow['taxi_ATime']);		 //총 예상시간
		$taxiPer =  trim($minfoRow['taxi_Per']);		// 희망쉐어링 %
		$taxiPer = (int)$taxiPer;
	}
}


//요청자 신청 정보 가져오기
$infoRQuery = "SELECT taxi_RMcnt, taxi_RSeat, taxi_RSex, taxi_RMemo from TB_RTAXISHARING_INFO WHERE taxi_SIdx = :taxi_SIdx AND taxi_RMemId = :taxi_RMemId AND taxi_RIdx = :taxi_RIdx  ";
$infoRStmt = $DB_con->prepare($infoRQuery);
$infoRStmt->bindparam(":taxi_SIdx", $taxi_SIdx);
$infoRStmt->bindparam(":taxi_RMemId", $taxi_RMemId);
$infoRStmt->bindparam(":taxi_RIdx", $ridx);
$infoRStmt->execute();
$infoRNum = $infoRStmt->rowCount();

if ($infoRNum < 1) { //아닐경우
} else {
	while ($infoRRow = $infoRStmt->fetch(PDO::FETCH_ASSOC)) {
		$taxiRMcnt = trim($infoRRow['taxi_RMcnt']);			//요청자 인원수
		$taxi_RSeat = trim($infoRRow['taxi_RSeat']);		//요청자 좌석
		$taxi_RSex = trim($infoRRow['taxi_RSex']);			//요청자 성별
		$taxi_RMemo = trim($infoRRow['taxi_RMemo']);		//메모
	}
}

if ($taxi_RSex == 0) {
	$taxiRSex = "남자";
} else if ($taxi_RSex == 1) {
	$taxiRSex = "여자";
}

if ($taxi_RSeat == 0) {
	$taxiRSeat = "앞좌석";
} else if ($taxi_RSeat == 1) {
	$taxiRSeat = "뒷좌석";
}


if ($taxiRoute == "0") { //경유가능

	//요청자 지도 정보 가져오기
	$mapRQuery = "SELECT taxi_RSaddr, taxi_RSdong FROM TB_RTAXISHARING_MAP WHERE taxi_SIdx = :taxi_SIdx AND taxi_RMemId = :taxi_RMemId AND taxi_RIdx = :taxi_RIdx  ";
	$mapRStmt = $DB_con->prepare($mapRQuery);
	$mapRStmt->bindparam(":taxi_SIdx", $taxi_SIdx);
	$mapRStmt->bindparam(":taxi_RMemId", $taxi_RMemId);
	$mapRStmt->bindparam(":taxi_RIdx", $ridx);
	$mapRStmt->execute();
	$mapRNum = $mapRStmt->rowCount();

	if ($mapRNum < 1) { //아닐경우
	} else {
		while ($mapRRow = $mapRStmt->fetch(PDO::FETCH_ASSOC)) {
			$taxiRSaddr = trim($mapRRow['taxi_RSaddr']);			//경유지 주소
			$taxiRdong  = trim($mapRRow['taxi_RSdong']);			//경유지 동명
		}
	}
}

//생성 지도정보
$mapQuery = "SELECT taxi_Saddr, taxi_Sdong, taxi_Eaddr, taxi_Edong FROM TB_STAXISHARING_MAP WHERE taxi_Idx = :taxi_Idx LIMIT 1 ";
//echo $mapQuery."<BR>";
//exit;
$mapStmt = $DB_con->prepare($mapQuery);
$mapStmt->bindparam(":taxi_Idx", $taxi_SIdx);
$mapStmt->execute();
$mapNum = $mapStmt->rowCount();
//echo $mapNum."<BR>";

if ($mapNum < 1) { //아닐경우
} else {
	while ($mapRow = $mapStmt->fetch(PDO::FETCH_ASSOC)) {
		$taxiSaddr = trim($mapRow['taxi_Saddr']);			//출발지 주소
		$taxiSdong = trim($mapRow['taxi_Sdong']);			//출발지 동명
		$taxiEaddr = trim($mapRow['taxi_Eaddr']);		  //목적지 주소
		$taxiEdong = trim($mapRow['taxi_Edong']);		  //목적지 동명
	}
}
$taxi_RMcnt = trim($row['taxi_RMcnt']);
$memNickNm =  trim($row['memNickNm']);

if ($memNickNm == "") {
	$memNickNm = "탈퇴회원";
} else {
	$memNickNm = $memNickNm;
}

$chkQuery = "SELECT idx, reg_Date from TB_RTAXISHARING WHERE idx = :idx AND taxi_RState IN ('6', '7', '8', '9', '10' ) LIMIT 1 ";
$chkstmt = $DB_con->prepare($chkQuery);
$chkstmt->bindparam(":idx", $ridx);
$chkstmt->execute();
$num = $chkstmt->rowCount();
if ($num < 1) { //아닐경우
	$chkResult = "0";
} else {
	while ($Row = $chkstmt->fetch(PDO::FETCH_ASSOC)) {
		$reg_Date = $Row['reg_Date'];		// 푸시발송수
	}
	$regDate = date("Ymd", strtotime($reg_Date));
	$TableName = "TB_SHARING_GPS_" . $regDate;


	// 테이블이 존재하는지 체크
	// 테이블 존재하지 않을 경우, 페이지 빈페이지 출력되는 것을 방지하기 위함.__20190416
	$query = "SHOW tables LIKE '" . $TableName . "'";
	$chktb = $DB_con->prepare($query);
	$chktb->execute();
	$chktb_num = $chktb->rowCount();

	//테이블이 존재하는지 체크 후, 값 구하기
	if ($chktb_num > 0) {

		/* 전체 카운트 */
		$Query = "SELECT taxi_Lng, taxi_Lat, reg_Date ";
		$Query .= " FROM " . $TableName . " ";
		$Query .= " WHERE taxi_Idx = :taxi_Idx AND taxi_MemType = 'c' ORDER BY reg_Date ASC ; ";
		//echo $cntQuery."<BR>";
		//exit;
		$Stmt = $DB_con->prepare($Query);
		$Stmt->bindparam(":taxi_Idx", $ridx);
		$Stmt->execute();
		$num = $Stmt->rowCount();
		if ($num < 1) {
			$chkResult = "0";
		} else {
			$chkResult = "1";
		}
	}
}

$qstr = "taxiSIdx=" . urlencode($taxiSIdx) . "&amp;od_rstatus=" . urlencode($od_rstatus) . "&amp;fr_rdate=" . urlencode($fr_rdate) . "&amp;to_rdate=" . urlencode($to_rdate) . "&amp;findrType=" . urlencode($findrType) . "&amp;findrWord=" . urlencode($findrWord);
//echo $qstr."<BR>";
//exit;

include "../common/inc/inc_gnb.php";  //헤더 
include "../common/inc/inc_menu.php";  //메뉴 

?>

<div id="wrapper">

	<div id="container" class="">
		<h1 id="container_title"><?= $titNm ?></h1>
		<div class="container_wr">
			<form name="fmember" id="fmember" action="taxiSharingSProc.php" onsubmit="return fmember_submit(this);" method="post" enctype="multipart/form-data" autocomplete="off">
				<input type="hidden" name="mode" id="mode" value="<?= $mode ?>">
				<input type="hidden" name="ridx" id="ridx" value="<?= $ridx ?>">
				<input type="hidden" name="qstr" id="qstr" value="<?= $qstr ?>">
				<input type="hidden" name="page" id="page" value="<?= $page ?>">

				<div class="tbl_frm01 tbl_wrap">
					<table>
						<caption><?= $titNm ?></caption>
						<colgroup>
							<col class="grid_4">
							<col>
							<col class="grid_4">
							<col>
						</colgroup>
						<tbody>

							<tr>
								<th scope="row"><label for="id">신청노선번호</label></th>
								<td colspan="3"><a href="/udev/taxiSharing/taxiSharingReg.php?mode=mod&idx=<?= $taxi_SIdx ?>"><?= $taxi_SIdx ?></a></td>
							</tr>
							<tr>
								<th scope="row"><label for="id">투게더</label></th>
								<td colspan="3"><?= $taxi_RMemId ?> ( <?= $memNickNm ?> )</td>
							</tr>
							<tr>
								<th scope="row"><label for="mem_Name">경유지</label></th>
								<td colspan="3">
									출발지 : <?= $taxiSaddr ?> (<?= $taxiSdong ?>) </br>
									<? if ($taxiRSaddr != "") { ?> 경유지 : <?= $taxiRSaddr ?> (<?= $taxiRdong ?>) </br> <? } ?>
									도착지 : <?= $taxiEaddr ?> (<?= $taxiEdong ?>) </br>
								</td>
							</tr>
							<tr>
								<th scope="row"><label for="taxi_Info">탑승정보</label></th>
								<td colspan="3">인원 : <?= $taxiRMcnt ?> 명 &nbsp; 성별 : <?= $taxiRSex ?></td>
							</tr>
							<tr>
								<th scope="row"><label for="taxi_RMemo">메모</label></th>
								<td colspan="3"><textarea name="taxi_RMemo" id="taxi_RMemo"><?= stripslashes($taxi_RMemo); ?></textarea></td>
							</tr>
							<? if ($chkResult == "1") { ?>
								<tr>
									<th scope="row"><label for="taxi_Map">경로</label></th>
									<td colspan="3">
										<div id="map"><iframe style="width:100%;height:750px;" src="/udev/taxiSharing/taxiSharingGpsRoute.php?idx=<?= $ridx ?>&mode=c"></iframe></div>
									</td>
								</tr>
							<? } ?>
						</tbody>
					</table>
				</div>

				<div class="btn_fixed_top">
					<a href="taxiSharingSList.php?<?= $qstr ?>&page=<?= $page ?>" class="btn btn_02">목록</a>
					<input type="submit" value="확인" class="btn_submit btn" accesskey='s'>
				</div>
			</form>

			<script>
				function fmember_submit(f) {
					return true;
				}
			</script>

		</div>

		<?
		dbClose($DB_con);
		$stmt = null;
		$minfoetmt = null;
		$infoRStmt = null;
		$mapRStmt = null;

		$mapStmt = null;

		include "../common/inc/inc_footer.php";  //푸터 

		?>