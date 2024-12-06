<?
include "../lib/common.php";
include "../lib/functionDB.php";  //공통 db함수

$mem_Id = trim($memId);			//아이디
$mem_Idx = memIdxInfo($mem_Id);   //회원 주아이디
$chkMonth = trim($chkMonth);	//개월체크 (최근 1개월 1, 최근 3개월, 최근 6개월)
$chkType = trim($chkType);		//포인트내역구분 (1: 전체, 2: 적립, 3: 사용, 4: 환전)
//$chkPType = trim($chkPType);	//포인트구분 (0: 매칭, 1: 적립, 2: 환전)

if ($chkMonth == "") {
	$chkMonth = "1";							//최근 1개월 1
} else {
	$chkMonth = trim($chkMonth);				//구분 (최근 1개월 : 1, 최근 3개월 : 3, 최근 6개월 : 6)
}

if ($chkType == "" || $chkType == "1") {
	$chkAndType = "";								//내역보기 (전체)
} else if ($chkType == "2") {
	$chkAndType = "AND taxi_Sign = 0 AND taxi_PState <> '6'";
} else if ($chkType == "3") {
	$chkAndType = "AND taxi_Sign = 1";
}


if ($mem_Id != "" && $chkMonth != "") {  //아이디,  최근 개월 있을 경우

	$DB_con = db1();

	/* 전체 카운트 */
	$cntQuery = "";
	$cntQuery = "SELECT idx FROM TB_POINT_HISTORY WHERE taxi_MemIdx = :taxi_MemIdx AND taxi_PState <> 6";
	$cntQuery .= " AND  reg_Date > SUBDATE(NOW(), INTERVAL :chkMonth MONTH) {$chkAndType}  ";

	$cntStmt = $DB_con->prepare($cntQuery);
	$cntStmt->bindparam(":taxi_MemIdx", $mem_Idx);
	$cntStmt->bindparam(":chkMonth", $chkMonth);
	$cntStmt->execute();
	$totalCnt = $cntStmt->rowCount();

	if ($totalCnt == "") {
		$totalCnt = "0";
	} else {
		$totalCnt =  $totalCnt;
	}

	$totalCnt = (int)$totalCnt;

	$rows = 10;  //페이지 갯수
	$total_page  = ceil($totalCnt / $rows);  // 전체 페이지 계산
	if ($page == "") {
		$page = 1;
	} // 페이지가 없으면 첫 페이지 (1 페이지)
	$page = (int)$page;

	$from_record = ($page - 1) * $rows; // 시작 열을 구함

	/* 포인트 현황 */
	$hisQuery = "";
	$hisQuery = "SELECT idx, mission_Idx, taxi_MemId, taxi_OrdNo, taxi_OrdPoint, taxi_Memo, taxi_Sign, taxi_PState, taxi_SubTitle, taxi_OrdType, reg_Date FROM TB_POINT_HISTORY WHERE taxi_MemIdx = :taxi_MemIdx AND taxi_PState <> 6";
	$hisQuery .= "  AND  reg_Date > SUBDATE(NOW(), INTERVAL :chkMonth MONTH) {$chkAndType}  ORDER BY reg_Date DESC limit  {$from_record}, {$rows}  ";
	//echo $hisQuery."<BR>";
	//exit;
	$hisStmt = $DB_con->prepare($hisQuery);
	$hisStmt->bindparam(':taxi_MemIdx', $mem_Idx, PDO::PARAM_STR);
	$hisStmt->bindparam(":chkMonth", $chkMonth);
	$hisStmt->execute();
	$mNum = $hisStmt->rowCount();


	if ($mNum < 1) { //아닐경우
		$chkResult = "0";
		$listInfoResult = array("totCnt" => (int)$totalCnt, "page" => (int)$page);
	} else {
		$chkResult = "1";
		$listInfoResult = array("totCnt" => (int)$totalCnt, "page" => (int)$page);

		$data  = [];
		while ($hrow = $hisStmt->fetch(PDO::FETCH_ASSOC)) {

			$idx = $hrow['idx'];							// 포인트내역 고유번호
			$mission_Idx = $hrow['mission_Idx'];			// 미션고유번호
			$taxi_MemId = $hrow['taxi_MemId'];				// 회원아이디
			$taxi_OrdNo = $hrow['taxi_OrdNo'];				// 주문번호
			$taxiOrdPoint = $hrow['taxi_OrdPoint'];			// 포인트금액
			$taxiSign = $hrow['taxi_Sign'];					// 포인트구분 (0: +, 1: -)
			$taxi_PState = $hrow['taxi_PState'];	        // 구분 (0: 매칭, 1: 적립, 2: 환전, 3: 추천인 적립, 4: 포인트적립(카드), 5: 신규가입 이벤트, 6.적립예정, 7:미션적립)
			$taxi_SubTitle = $hrow['taxi_SubTitle'];		// 포인트 내역메모
			$taxi_OrdType = $hrow['taxi_OrdType'];	        // 결제타입 (1: 카드, 2: 보유포인트결제)
			$reg_Date = $hrow['reg_Date'];					// 등록일
			$regDate = substr($reg_Date, 0, 10);
			if ($taxi_PState == '0' && $taxiSign == '0') {
				$taxiPState = '같이타기 메이커 적립';
				$taxi_Memo = $taxi_SubTitle;				
			} else if ($taxi_PState == '0' && $taxiSign == '1') {
				$taxiPState = '같이타기 투게더 사용';
				$taxi_Memo = $taxi_SubTitle;		
			} else if ($taxi_PState == '4' && $taxiSign == '0') {
				$taxiPState = '같이타기 투게더 결제(카드)';
				$taxi_Memo = $taxi_SubTitle;		
			} else if ($taxi_PState == '1') {
				$taxiPState = '이벤트 적립';
				$taxi_Memo = $taxi_SubTitle;		
			} else if ($taxi_PState == '2' && $taxiSign == '0') {
				$taxiPState = '출금하기 신청(반환)';
				$taxi_Memo = $taxi_SubTitle;		
			} else if ($taxi_PState == '2' && $taxiSign == '1') {
				$taxiPState = '출금하기 신청';
				$taxi_Memo = $taxi_SubTitle;		
			} else if ($taxi_PState == '3' && $taxiSign == '0') {
				$taxiPState = '추천인 적립';
				$taxi_Memo = $taxi_SubTitle;		
			} else if ($taxi_PState == '5' && $taxiSign == '0') {
				$taxiPState = '환영해요! 웰컴 포인트';
				$taxi_Memo = $taxi_SubTitle;		
			} else if ($taxi_PState == '7' && $taxiSign == '0') {
				$taxiPState = '미션 적립';
                $mission = missionInfoChk($mission_Idx);
                $taxi_Memo = trim(preg_replace("/\r|\n/", " ", $mission['mName']));
			} else if ($taxi_PState == '8' && $taxiSign == '0') {
				$taxiPState = '이벤트 적립';
                if($taxi_SubTitle == "가치타기 인증"){
					$taxi_Memo = "가치있는 가치타기 인증";		
				}
			} else if ($taxi_PState == '9' && $taxiSign == '0') {
				$taxiPState = '관리자 적립';
				$taxi_Memo = $taxi_SubTitle;
			} else if ($taxi_PState == '9' && $taxiSign == '1') {
				$taxiPState = '관리자 차감';
				$taxi_Memo = $taxi_SubTitle;
			}


			// : 내역상세
			$mresult = ["idx" => (int)$idx, "taxiOrdPoint" => (int)$taxiOrdPoint, "taxiSign" => (string)$taxiSign, "taxiPState" => (string)$taxiPState, "taxiMemo" => (string)$taxi_Memo, "regDate" => (string)$regDate];
			array_push($data, $mresult);
		}

		$chkData = [];
		$chkData["result"] = true;
		$chkData["listInfo"] = $listInfoResult;  //카운트 관련
		$chkData['lists'] = $data;
	}

	if ($chkResult  == "1") {
		$output = str_replace('\\\/', '/', json_encode($chkData, JSON_UNESCAPED_UNICODE));
	} else if ($chkResult  == "0") {
		$chkData2["result"] = true;
		$chkData2["listInfo"] = $listInfoResult;  //카운트 관련
		$output = str_replace('\\\/', '/', json_encode($chkData2, JSON_UNESCAPED_UNICODE));
	}

	echo  urldecode($output);

	dbClose($DB_con);
	$cntStmt = null;
	$hisStmt = null;
	$chkStmt = null;
} else {
	$result = array("result" => false, "errorMsg" => "조회가능한 정보가 없습니다. 관리자에게 문의바랍니다.");
	echo json_encode($result, JSON_UNESCAPED_UNICODE);
}
