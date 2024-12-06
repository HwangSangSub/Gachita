<?
include "../lib/common.php";
include "../lib/functionDB.php";

$idx  = trim($idx);								// 매칭생성고유번호
$mem_Id  = trim($memId);				//매칭생성 아이디
// $page = trim($page);						//페이지 페이징 처리 제거 2023-01-10 권대리요청 황상섭처리

if ($idx != "" && $mem_Id != "") {  // 고유번호, 매칭생성아이디

	$DB_con = db1();

	/* 매칭요청중 목록 */
	$matchQuery = "";
	$matchQuery = "SELECT taxi_TPrice, taxi_Price, taxi_ATime, taxi_SDate, taxi_Memo, taxi_State FROM TB_STAXISHARING WHERE idx = :idx AND taxi_MemId = :taxi_MemId AND taxi_State IN ('1', '2', '3') ";
	//echo $matchQuery."<BR>";
	//exit;
	$matchStmt = $DB_con->prepare($matchQuery);
	$matchStmt->bindparam(":idx", $idx);
	$matchStmt->bindparam(":taxi_MemId", $mem_Id);
	$matchStmt->execute();
	$mNum = $matchStmt->rowCount();

	if ($mNum < 1) { //아닐경우
		$chkMResult = "0";
		$mresult = array("result" => false, "errorMsg" => "매칭 대기 노선이 없습니다.");
		//$mresult = array("result" => true, "totCnt" => $mNum);
		echo json_encode($mresult, JSON_UNESCAPED_UNICODE);
	} else {

		$chkMResult = "1";

		while ($mrow = $matchStmt->fetch(PDO::FETCH_ASSOC)) {
			$taxiTPrice = trim($mrow['taxi_TPrice']);	       // 택시총요금
			$taxiPrice = trim($mrow['taxi_Price']);	          // 희망쉐어금액
			$taxiATime = trim($mrow['taxi_ATime']);	  // 예상 시간
			$taxiSDate = trim($mrow['taxi_SDate']);		  // 생성 시간
			$taxiMemo = trim($mrow['taxi_Memo']);		  // 생성 시간
			$taxiState = trim($mrow['taxi_State']);			 // 상태값
		}

		//생성 정보
		$infoQuery = "";
		$infoQuery = "SELECT taxi_Type, taxi_Distance, taxi_Route FROM TB_STAXISHARING_INFO WHERE taxi_Idx = :taxi_Idx AND taxi_MemId = :taxi_MemId LIMIT 1 ";
		//echo $infoQuery."<BR>";
		//exit;
		$infoStmt = $DB_con->prepare($infoQuery);
		$infoStmt->bindparam(":taxi_Idx", $idx);
		$infoStmt->bindparam(":taxi_MemId", $mem_Id);
		$infoStmt->execute();
		$infoNum = $infoStmt->rowCount();
		//echo $infoNum."<BR>";

		if ($infoNum < 1) { //아닐경우
		} else {
			while ($infoRow = $infoStmt->fetch(PDO::FETCH_ASSOC)) {
				$taxiType =  trim($infoRow['taxi_Type']);						//출발타입 ( 0: 바로출발, 1: 예약출발)
				$lineTDistance =  trim($infoRow['taxi_Distance']);		// 예상거리
				$taxi_Route =  trim($infoRow['taxi_Route']);				// 경유가능여부 ( 0: 경유가능, 1: 경유불가)

				if ($taxi_Route == "0") {
					$taxiRoute = true;
				} else {
					$taxiRoute = false;
				}
			}
		}

		//생성 지도정보
		$mapQuery = "";
		$mapQuery = "SELECT taxi_Sdong, taxi_Edong, taxi_EaddrNm FROM TB_STAXISHARING_MAP WHERE taxi_Idx = :taxi_Idx AND taxi_MemId = :taxi_MemId  LIMIT 1 ";
		//echo $mapQuery."<BR>";
		//exit;
		$mapStmt = $DB_con->prepare($mapQuery);
		$mapStmt->bindparam(":taxi_Idx", $idx);
		$mapStmt->bindparam(":taxi_MemId", $mem_Id);
		$mapStmt->execute();
		$mapNum = $mapStmt->rowCount();
		//echo $mapNum."<BR>";

		if ($mapNum < 1) { //아닐경우
		} else {
			while ($mapRow = $mapStmt->fetch(PDO::FETCH_ASSOC)) {
				$taxiSdong = trim($mapRow['taxi_Sdong']);					  //  출발지 동명
				$taxiEaddrNm = trim($mapRow['taxi_EaddrNm']);
				if ($taxiEaddrNm != "") {
					$taxiEdong = $taxiEaddrNm;					  //  도착지 동명
				} else {
					$taxiEdong = trim($mapRow['taxi_Edong']);					  //  도착지 동명
				}
			}
		}

		//매칭 요청 대기 건수
		$chkCntQuery = "SELECT count(idx) AS num from TB_RTAXISHARING WHERE taxi_SIdx = :taxi_SIdx AND taxi_MemId = :taxi_MemId AND taxi_RState IN ( '1', '2' ) "; //매칭요청, 예약요청
		$chkStmt = $DB_con->prepare($chkCntQuery);
		$chkStmt->bindparam(":taxi_SIdx", $idx);
		$chkStmt->bindparam(":taxi_MemId", $mem_Id);
		$chkStmt->execute();
		$chkRow = $chkStmt->fetch(PDO::FETCH_ASSOC);
		$chkNum = $chkRow['num'];

		$mresult = array("idx" => (int)$idx, "taxiMemId" => (string)$mem_Id, "taxiRoute" => $taxiRoute, "taxiType" => (string)$taxiType, "taxiSDate" => (string)$taxiSDate, "taxiSdong" => (string)$taxiSdong, "taxiEdong" => (string)$taxiEdong, "lineDistance" => (float)$lineTDistance, "taxiATime" => (int)$taxiATime, "taxiState" => (string)$taxiState, "taxiTPrice" => (int)$taxiTPrice, "taxiPrice" => (int)$taxiPrice, "taxiMemo" => (string)$taxiMemo);

		/* 전체 카운트 */
		$cntQuery = "";
		$cntQuery = "SELECT idx, taxi_SIdx, taxi_RTPrice, taxi_RDistance, taxi_RState FROM TB_RTAXISHARING WHERE taxi_SIdx = :taxi_SIdx  AND taxi_RState IN ( '1', '2' ) ORDER BY reg_Date ASC   ";
		//echo $cntQuery."<BR>";
		//exit;
		$cntStmt = $DB_con->prepare($cntQuery);
		$cntStmt->bindparam(":taxi_SIdx", $idx);
		$cntStmt->execute();
		$totalCnt = $cntStmt->rowCount();

		if ($totalCnt == "") {
			$totalCnt = 0;
		} else {
			$totalCnt =  $totalCnt;
		}

		$rows = 10;  //페이지 갯수
		$total_page  = ceil($totalCnt / $rows);  // 전체 페이지 계산
		if ($page == "") {
			$page = 1;
		} // 페이지가 없으면 첫 페이지 (1 페이지)
		$page = (int)$page;

		$from_record = ($page - 1) * $rows; // 시작 열을 구함

		/* 매칭 신청 요청 대기 목록 */
		$viewQuery = "";
		$viewQuery = "SELECT idx, taxi_SIdx, taxi_RMemIdx, taxi_RTPrice, taxi_RMemo, taxi_RDistance, taxi_RState FROM TB_RTAXISHARING WHERE taxi_SIdx = :taxi_SIdx  AND taxi_RState IN ( '1', '2' ) ORDER BY reg_Date ASC ";
		//exit;
		$viewStmt = $DB_con->prepare($viewQuery);
		$viewStmt->bindparam(":taxi_SIdx", $idx);
		$viewStmt->execute();
		$num = $viewStmt->rowCount();


		if ($num < 1) { //아닐경우
			$chkResult = "0";
			$listInfoResult = array("totCnt" => (int)$totalCnt);
		} else {
			$chkResult = "1";
			$listInfoResult = array("totCnt" => (int)$totalCnt);

			$data  = [];
			while ($row = $viewStmt->fetch(PDO::FETCH_ASSOC)) {
				$taxiRIdx = trim($row['idx']);									 // 매칭요청 고유번호
				$taxiSIdx = trim($row['taxi_SIdx']);						 // 매칭생성 고유번호
				$taxiRMemIdx = trim($row['taxi_RMemIdx']);						 // 매칭생성 고유번호
				$taxiRTPrice = trim($row['taxi_RTPrice']);			 // 추가택시요금
				$taxiRMemo = trim($row['taxi_RMemo']);			 // 추가택시요금
				$taxiRDistance = trim($row['taxi_RDistance']);	 // 나와의거리
				$taxiRState = trim($row['taxi_RState']);	      // 요청상태값

				//요청자 신청 정보 가져오기
				$infoRQuery = "SELECT taxi_RMcnt, taxi_RSeat, taxi_RSex from TB_RTAXISHARING_INFO  WHERE taxi_SIdx = :taxi_SIdx AND taxi_MemId = :taxi_MemId AND taxi_RIdx = :taxi_RIdx  ";

				$infoRStmt = $DB_con->prepare($infoRQuery);
				$infoRStmt->bindparam(":taxi_SIdx", $idx);
				$infoRStmt->bindparam(":taxi_MemId", $mem_Id);
				$infoRStmt->bindparam(":taxi_RIdx", $taxiRIdx);
				$infoRStmt->execute();
				$infoRNum = $infoRStmt->rowCount();

				if ($infoRNum < 1) { //아닐경우
				} else {
					while ($infoRRow = $infoRStmt->fetch(PDO::FETCH_ASSOC)) {
						$taxiRMcnt = trim($infoRRow['taxi_RMcnt']);			//요청자 인원수
						$taxiRSeat = trim($infoRRow['taxi_RSeat']);			//요청자 좌석
						$taxiRSex = trim($infoRRow['taxi_RSex']);				//요청자 성별
					}
				}


				if ($taxi_Route == "0") { //경유가능
					//요청자 지도 정보 가져오기
					$mapRQuery = "SELECT taxi_RSdong, taxi_RSLat,taxi_RSLng from TB_RTAXISHARING_MAP  WHERE taxi_SIdx = :taxi_SIdx AND taxi_MemId = :taxi_MemId AND taxi_RIdx = :taxi_RIdx  ";
					$mapRStmt = $DB_con->prepare($mapRQuery);
					$mapRStmt->bindparam(":taxi_SIdx", $idx);
					$mapRStmt->bindparam(":taxi_MemId", $mem_Id);
					$mapRStmt->bindparam(":taxi_RIdx", $taxiRIdx);
					$mapRStmt->execute();
					$mapRNum = $mapRStmt->rowCount();

					if ($mapRNum < 1) { //아닐경우
					} else {
						while ($mapRRow = $mapRStmt->fetch(PDO::FETCH_ASSOC)) {
							$taxiRSdong = trim($mapRRow['taxi_RSdong']);			//경유지 동명
							$taxiRSLat = trim($mapRRow['taxi_RSLat']);			//경유지
							$taxiRSLng = trim($mapRRow['taxi_RSLng']);			//경유지
						}
					}
				}


				if ($taxi_Route == "0") { //경유가능
					$taxiRSdong = $taxiRSdong;
					$taxiRTPrice = $taxiRTPrice;  //추가요금 계산은 앱에서 처리
				} else {
					$taxiRTPrice = "0";
					$taxiRSdong = "없음";
				}
				$memImgFile = getMemberImg($taxiRMemIdx);
				//, "taxiRSdong" => (string)$taxiRSdong, "taxiRTPrice" => (int)$taxiRTPrice, "taxiRSLat" => (float)$taxiRSLat, "taxiRSLng" => (float)$taxiRSLng, "taxiRSeat" => (string)$taxiRSeat, "taxiRSex" => (string)$taxiRSex
				$result = ["idx" => (int)$taxiRIdx, "memImgFile" => (string)$memImgFile, "taxiSIdx" => (int)$taxiSIdx, "lineTDistance" => (float)$taxiRDistance, "taxiRMcnt" => (int)$taxiRMcnt, "taxiRMemo" => (string)$taxiRMemo];
				array_push($data, $result);
			}

			$chkData = [];
			$chkData["result"] = true;
			// $chkData["info"] = $mresult;  //매칭요청정보
			$chkData["listInfo"] = $listInfoResult;  //카운트 관련
			$chkData['lists'] = $data;
		}


		if ($chkMResult  == "1" && $chkResult  == "1") {
			$output = str_replace('\\\/', '/', json_encode($chkData, JSON_UNESCAPED_UNICODE + JSON_PRETTY_PRINT));
		} else if ($chkMResult  == "1" && $chkResult  == "0") {
			$chkData2["result"] = true;
			// $chkData2["info"] = $mresult;  //매칭요청정보
			$chkData2["listInfo"] = $listInfoResult;  //카운트 관련
			$chkData2['lists'] = [];
			$output = str_replace('\\\/', '/', json_encode($chkData2, JSON_UNESCAPED_UNICODE + JSON_PRETTY_PRINT));
		}

		echo  urldecode($output);
	}

	dbClose($DB_con);
	$matchStmt = null;
	$infoStmt = null;
	$mapStmt = null;
	$chkStmt = null;
	$cntStmt = null;
	$viewStmt = null;
	$infoRStmt = null;
	$MapRStmt = null;
} else {
	$result = array("result" => false);
	echo json_encode($result, JSON_UNESCAPED_UNICODE);
}
