<?
$menu = "4";
$smenu = "1";

include "../common/inc/inc_header.php";  //헤더 

if ($idx == "") {
	$msg = "잘못된 접근 방식입니다. 정확한 경로를 통해서 접근 하시길 바랍니다.";
	proc_msg2($msg);
}

$titNm = "매칭관리 상세";

$DB_con = db1();

// 투게더 닉네임
$query = "";
/*
	$query = "SELECT idx, taxi_MemId, taxi_SaddNm, taxi_Eaddr, taxi_Distance, taxi_Type, taxi_SDay, taxi_STime, taxi_SDate, taxi_Tprice, taxi_Price,
     taxi_Mcnt, taxi_Sex, taxi_Seat, taxi_Etc, taxi_Etc2, taxi_Etc3, taxi_State, taxi_Memo, taxi_Route  {$mnSql }  FROM TB_STAXISHARING WHERE idx = :idx" ;*/

$query = "SELECT idx, taxi_MemId, taxi_MemIdx, taxi_Per, taxi_SDate, taxi_Price, taxi_State FROM TB_STAXISHARING WHERE idx = :idx";
$stmt = $DB_con->prepare($query);
$stmt->bindparam(":idx", $idx);
$stmt->execute();

$row = $stmt->fetch(PDO::FETCH_ASSOC);

$taxiIdx =  trim($row['idx']);     			// 노선번호
$taxiMemId =  trim($row['taxi_MemId']);     // 메이커
$taxiMemIdx =  trim($row['taxi_MemIdx']);     // 메이커
$taxiPrice =  trim($row['taxi_Price']);		// 요청포인트
$taxiPer =  trim($row['taxi_Per']);	    	// 요청포인트비율 %
$taxi_State =  trim($row['taxi_State']);	// 노선상태값

if ($taxi_State == 1) {
	$taxiState = "매칭중";
} else if ($taxi_State == 2) {
	$taxiState = "매칭요청";
} else if ($taxi_State == 3) {
	$taxiState = "예약요청";
} else if ($taxi_State == 4) {
	$taxiState = "예약요청완료";
} else if ($taxi_State == 5) {
	$taxiState = "만남중";
} else if ($taxi_State == 6) {
	$taxiState = "이동중";
} else if ($taxi_State == 7) {
	$taxiState = "완료";
} else if ($taxi_State == 8) {
	$taxiState = "취소";
} else if ($taxi_State == 9) {
	$taxiState = "취소사유확인";
} else if ($taxi_State == 10) {
	$taxiState = "거래완료확인";
}

//생성 정보
$infoQuery = "SELECT taxi_Type, taxi_Mcnt, taxi_Distance, taxi_Route, taxi_Sex, taxi_Seat FROM TB_STAXISHARING_INFO WHERE taxi_Idx = :taxi_Idx LIMIT 1 ";
//echo $infoQuery."<BR>";
//exit;
$infoStmt = $DB_con->prepare($infoQuery);
$infoStmt->bindparam(":taxi_Idx", $idx);
$infoStmt->execute();
$infoNum = $infoStmt->rowCount();
//echo $infoNum."<BR>";

if ($infoNum < 1) { //아닐경우
} else {
	while ($infoRow = $infoStmt->fetch(PDO::FETCH_ASSOC)) {
		$taxiType =  $infoRow['taxi_Type'];				//출발타입 ( 0: 바로출발, 1: 예약출발 )
		$taxiMcnt =  $infoRow['taxi_Mcnt'];				// 인원수
		$lineDistance =  $infoRow['taxi_Distance'];		// 예상거리
		$taxi_Sex =  $infoRow['taxi_Sex'];				// 성별 ( 0: 남자, 1: 여자)
	}
}

//생성 지도정보
$mapQuery = "SELECT taxi_Saddr, taxi_Sdong, taxi_Eaddr, taxi_Edong, taxi_SLat, taxi_SLng, taxi_ELat, taxi_ELng FROM TB_STAXISHARING_MAP WHERE taxi_Idx = :taxi_Idx LIMIT 1 ";
//echo $mapQuery."<BR>";
//exit;
$mapStmt = $DB_con->prepare($mapQuery);
$mapStmt->bindparam(":taxi_Idx", $idx);
$mapStmt->execute();
$mapNum = $mapStmt->rowCount();
//echo $mapNum."<BR>";

if ($mapNum < 1) { //아닐경우
} else {
	while ($mapRow = $mapStmt->fetch(PDO::FETCH_ASSOC)) {
		$taxiSaddr = $mapRow['taxi_Saddr'];					  //  출발지 주소
		$taxiSdong = $mapRow['taxi_Sdong'];					  //  출발지 동명
		$taxiEaddr = $mapRow['taxi_Eaddr'];					  //  목적지 주소
		$taxiEdong = $mapRow['taxi_Edong'];					  //  목적지 동명
	}
}

$taxiSaddr = str_replace("null", "", $taxiSaddr);
$taxiSdong = str_replace("null", "", $taxiSdong);
$taxiEaddr = str_replace("null", "", $taxiEaddr);
$taxiEdong = str_replace("null", "", $taxiEdong);

$taxi_Distance = $row['taxi_Distance'];

if ($lineDistance <= "1000") {
	$lineTDistance = $lineDistance . "m";    // 미터
} else {
	$taxiDistance = $lineDistance / 1000.0;
	$lineTDistance = round($taxiDistance, 2) . "km";    // 미터를 km로 변환
}

$taxi_Type = trim($taxi_Type);

if ($taxi_Type == "0") {
	$taxiSDate = "바로출발";
} else {
	$taxiSDate =  $row['taxi_SDate'];
}

if ($taxi_Sex == 0) {
	$taxiSex = "남자";
} else if ($taxi_Sex == 1) {
	$taxiSex = "여자";
}
$memNickNm = memIdxNickInfo($taxiMemIdx);					// 메이커 회원 닉네임

if ($memNickNm == "") {
	$memNickNm = "탈퇴회원";
} else {
	$memNickNm = $memNickNm;
}

$taxi_Memo =  $row['taxi_Memo'];

echo "ASfadsfasf";
//노선 주문
$orderQuery = "SELECT taxi_RIdx, taxi_OrdMemId, taxi_OMemIdx, taxi_OrdNo, taxi_OrdType, taxi_OrdPrice, taxi_OrdPoint, reg_Date FROM TB_ORDER WHERE taxi_SIdx = :taxi_SIdx LIMIT 1 ";
//echo $mapQuery."<BR>";
//exit;
$orderStmt = $DB_con->prepare($orderQuery);
$orderStmt->bindparam(":taxi_SIdx", $idx);
$orderStmt->execute();
$orderNum = $orderStmt->rowCount();
//echo $mapNum."<BR>";

if ($orderNum < 1) { //아닐경우
	$taxiRIdx = "";
	$taxiRMemId = "";
	$taxiRMemIdx = "";
	$memRNickNm = "";
	$taxiOrdNo = "-";
	$taxiOrdType = "";
	$orderDate = "-";
	$taxiOrdPrice = 0;
	$taxiOrdPoint = 0;
} else {
	while ($orderRow = $orderStmt->fetch(PDO::FETCH_ASSOC)) {
		$taxiRIdx = $orderRow['taxi_RIdx'];							// 투게더 노선 번호
		$taxiRMemId = $orderRow['taxi_OrdMemId'];						// 투게더 회원 아이디
		$taxiRMemIdx = $orderRow['taxi_OMemIdx'];					// 투게더 회원 고유번호
		$memRNickNm = memIdxNickInfo($taxiRMemIdx);					// 투게더 회원 닉네임
		$taxiOrdNo = $orderRow['taxi_OrdNo'];					  	// 투게더 결제 번호
		$taxiOrdType = $orderRow['taxi_OrdType'];				  	// 투게더 결제 수단
		$taxiOrdPrice = $orderRow['taxi_OrdPrice'];			  		// 투게더 카드 결제 금액
		$taxiOrdPoint = $orderRow['taxi_OrdPoint'];			  		// 투게더 사용 포인트
		$orderDate = $orderRow['reg_Date'];						  	// 투게더 결제일
	}
}

if ($taxiOrdType == '0') {
	$taxiOrdType = "카드+포인트결제";
} else if ($taxiOrdType == '1') {
	$taxiOrdType = "카드";
} else if ($taxiOrdType == '2') {
	$taxiOrdType = "포인트";
} else {
	$taxiOrdType = "-";
}

//본사 수익 조회
$pointQuery = "SELECT taxi_OrdSPoint, taxi_OrdTPoint, taxi_OrdMPoint FROM TB_PROFIT_POINT WHERE taxi_OrdNo = :taxi_OrdNo LIMIT 1 ";
//echo $mapQuery."<BR>";
//exit;
$pointStmt = $DB_con->prepare($pointQuery);
$pointStmt->bindparam(":taxi_OrdNo", $taxiOrdNo);
$pointStmt->execute();
$pointNum = $pointStmt->rowCount();
//echo $mapNum."<BR>";

if ($pointNum < 1) { //아닐경우
	$taxiOrdSPoint = 0;
	$taxiOrdTPoint = 0;
	$taxiOrdMPoint = 0;
} else {
	while ($pointRow = $pointStmt->fetch(PDO::FETCH_ASSOC)) {
		$taxiOrdSPoint = trim($pointRow['taxi_OrdSPoint']);		  //  수수료
		$taxiOrdTPoint = trim($pointRow['taxi_OrdTPoint']);		  //  요청 총 금액
		$taxiOrdMPoint = trim($pointRow['taxi_OrdMPoint']);		  //  메이커 적립 금액
	}
}

$chkQuery = "SELECT idx, reg_Date from TB_RTAXISHARING WHERE taxi_SIdx = :taxi_SIdx AND taxi_RState IN ('6', '7', '8', '9', '10' ) LIMIT 1 ";
$chkstmt = $DB_con->prepare($chkQuery);
$chkstmt->bindparam(":taxi_SIdx", $idx);
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
		$Query .= " WHERE taxi_Idx = :taxi_Idx AND taxi_MemType = 'p' ORDER BY reg_Date ASC ; ";
		//echo $cntQuery."<BR>";
		//exit;
		$Stmt = $DB_con->prepare($Query);
		$Stmt->bindparam(":taxi_Idx", $idx);
		$Stmt->execute();
		$num = $Stmt->rowCount();
		if ($num < 1) {
			$chkResult = "0";
		} else {
			$chkResult = "1";
		}
	}
}

$qstr = "fr_date=" . urlencode($fr_date) . "&amp;o_date=" . urlencode($to_date) . "&amp;findType=" . urlencode($findType) . "&amp;findword=" . urlencode($findword);

include "../common/inc/inc_gnb.php";  //헤더 
include "../common/inc/inc_menu.php";  //메뉴 

?>

<div id="wrapper">

	<div id="container" class="">
		<h1 id="container_title"><?= $titNm ?></h1>
		<div class="container_wr">
			<form name="fmember" id="fmember" action="taxiSharingProc.php" onsubmit="return fmember_submit(this);" method="post" enctype="multipart/form-data" autocomplete="off">
				<input type="hidden" name="mode" id="mode" value="<?= $mode ?>">
				<input type="hidden" name="idx" id="idx" value="<?= $idx ?>">
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
								<th scope="row"><label for="id">메이커</label></th>
								<td><?= $taxiMemId ?> ( <?= $memNickNm ?> )</td>
								<th scope="row"><label for="taxi_Idx">메이커 노선 번호</label></th>
								<td><?= $taxiIdx ?></td>
							</tr>
							<? if ($taxiRIdx != "") { ?>
								<tr>
									<th scope="row"><label for="Rid">투게더</label></th>
									<td><?= $taxiRMemId ?> ( <?= $memRNickNm ?> )</td>
									<th scope="row"><label for="taxi_RIdx">투게더 노선 번호</label></th>
									<td><?= $taxiRIdx ?> <a href="taxiSharingSReg.php?mode=mod&taxiSIdx=<?=$taxiIdx?>&ridx=<?=$taxiRIdx?>"> [상세보기]</a></td>
								</tr>
							<? } ?>
							<tr>
								<th scope="row"><label for="taxi_State">상태</label></th>
								<td colspan="3"><?= $taxiState ?></td>
								<!-- <th scope="row"><label for="taxi_Route">경유여부</label></th>
								<td><?= $taxiRoute ?></td> -->
							</tr>
							<tr>
								<th scope="row"><label for="mem_Name">출발지</label></th>
								<td><?= $taxiSaddr ?> (<?= $taxiSdong ?>)</td>
								<th scope="row"><label for="mem_NickNm">도착지 주소</label></th>
								<td><?= $taxiEaddr ?> (<?= $taxiEdong ?>)</td>
							</tr>
							<tr>
								<th scope="row"><label for="mem_Lv">예상거리</label></th>
								<td><?= $lineTDistance ?> </td>
								<th scope="row"><label for="mem_Tel">출발일/시간</label></th>
								<td><?= $taxiSDate ?></td>
							</tr>
							<tr>
								<th scope="row"><label for="taxi_Price">희망 요청 포인트</label></th>
								<td><?= number_format($taxiPrice) ?>ⓟ</td>
								<th scope="row"><label for="taxi_TPrice">탑승정보</label></th>
								<td>인원 : <?= $taxiMcnt ?> 명 &nbsp; 성별 : <?= $taxiSex ?></td>
							</tr>
							<tr>
								<th scope="row"><label for="taxi_Price">거래번호</label></th>
								<td><?= $taxiOrdNo ?></td>
								<th scope="row"><label for="taxi_TPrice">거래등록일</label></th>
								<td><?= $orderDate ?></td>
							</tr>
							<tr>
								<th scope="row"><label for="taxi_Price">결제수단</label></th>
								<td><?= $taxiOrdType ?></td>
								<th scope="row"><label for="taxi_RPrice">투게더 결제금액<br>(카드 결제 금액 / 투게더 사용 포인트)</label></th>
								<td><?= number_format($taxiOrdPrice) ?>원 / <?= number_format($taxiOrdPoint) ?>ⓟ</td>
							</tr>
							<tr>
								<th scope="row"><label for="taxi_Price">실제 적립포인트</label></th>
								<td><?= number_format($taxiOrdMPoint) ?>ⓟ</td>
								<th scope="row"><label for="taxi_TPrice">메이커 차감 수수료</label></th>
								<td><?= number_format($taxiOrdSPoint) ?>ⓟ</td>
							</tr>
							<tr>
								<th scope="row"><label for="taxi_Memo">메모</label></th>
								<td colspan="3"><textarea name="taxi_Memo" id="taxi_Memo"><?= stripslashes($taxi_Memo); ?></textarea></td>
							</tr>
							<? if ($chkResult == "1") { ?>
								<tr>
									<th scope="row"><label for="taxi_Map">경로</label></th>
									<td colspan="3">
										<div id="map"><iframe style="width:100%;height:750px;" src="/udev/taxiSharing/taxiSharingGpsRoute.php?idx=<?= $taxiIdx ?>&mode=p"></iframe></div>
									</td>
								</tr>
							<? } else { ?>
								<tr>
									<th scope="row"><label for="taxi_Map">경로</label></th>
									<td colspan="3">
										<p>위치기록은 7일간 저장됩니다.</p>
									</td>
								</tr>
							<? } ?>
						</tbody>
					</table>
				</div>

				<div class="btn_fixed_top">
					<a href="taxiSharingList.php?<?= $qstr ?>&page=<?= $page ?>" class="btn btn_02">목록</a>
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
		$infoStmt = null;
		$mapStmt = null;
		$orderStmt = null;
		$pointStmt = null;

		include "../common/inc/inc_footer.php";  //푸터 

		?>