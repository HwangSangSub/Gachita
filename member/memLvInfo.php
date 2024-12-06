<?
//회원등급정보
include "../lib/common.php";
include "../lib/functionDB.php";  //공통 db함수

$mem_Id = trim($memId);
$mem_Idx = memIdxInfo($mem_Id);   		// 회원 고유번호

$DB_con = db1();

if ($mem_Id == "") {
	$result = array("result" => false, "errorMsg" => "회원아이디가 없습니다. 확인 후 다시시도해주세요.");
	echo json_encode($result, JSON_UNESCAPED_UNICODE);
} else {
	$chkData["result"] = true;
	$nowdate = date('Y-m');
	
	//매칭생성 진행 건수
	$memSCntQuery = "SELECT count(idx) AS num FROM TB_STAXISHARING WHERE taxi_MemIdx = :taxi_MemIdx AND DATE_FORMAT(taxi_SDate, '%Y-%m') = :searchDate AND taxi_State IN ( '7', '10' ) ";
	$chkCntStmt = $DB_con->prepare($memSCntQuery);
	$chkCntStmt->bindparam(":taxi_MemIdx", $mem_Idx);
	$chkCntStmt->bindparam(":searchDate", $nowdate);
	$chkCntStmt->execute();
	$chkCntRow = $chkCntStmt->fetch(PDO::FETCH_ASSOC);
	$chkCntNum = $chkCntRow['num'];

	if ($chkCntNum <> "") {
		$chkCntNum = $chkCntNum;
	} else {
		$chkCntNum = 0;
	}

	//매칭요청 진행 건수
	$memRCntQuery = "SELECT count(idx) AS num FROM TB_RTAXISHARING WHERE taxi_RMemIdx = :taxi_RMemIdx AND DATE_FORMAT(reg_Date, '%Y-%m') = :searchDate AND taxi_RState IN ( '7', '10' ) "; //완료, 취소를 제외한 경우
	$chkCntRStmt = $DB_con->prepare($memRCntQuery);
	$chkCntRStmt->bindparam(":taxi_RMemIdx", $mem_Idx);
	$chkCntRStmt->bindparam(":searchDate", $nowdate);
	$chkCntRStmt->execute();
	$chkCntRrow = $chkCntRStmt->fetch(PDO::FETCH_ASSOC);
	$chkCntRNum = $chkCntRrow['num'];

	if ($chkCntRNum <> "") {
		$chkCntRNum = $chkCntRNum;
	} else {
		$chkCntRNum = 0;
	}

	$matCnt = (int)$chkCntNum + (int)$chkCntRNum;

	//나의 현재 등급 조회
	$memberMatQuery = "SELECT memIconFile, memDc FROM TB_MEMBER_LEVEL WHERE memLv = (SELECT mem_Lv FROM TB_MEMBERS WHERE idx = :idx) ORDER BY idx ASC LIMIT 1";
	$memMatStmt = $DB_con->prepare($memberMatQuery);
	$memMatStmt->bindparam(":idx", $mem_Idx);
	$memMatStmt->execute();
	$memMatRow = $memMatStmt->fetch(PDO::FETCH_ASSOC);
	$memIconFile = $memMatRow['memIconFile'];
	$memDc = $memMatRow['memDc'];
	$memDcSubName = "같이타기 메이커 이용시";				 // 혜택조건서브
	$memDcName = "수수료 " . $memDc . "%";						// 혜택조건 
	
	$chkData["levImg"] = "/data/levIcon/photo.php?id=" . $memIconFile;	// 등급아이콘
	$chkData["memDcSubName"] = (string)$memDcSubName;		// 고정멘트 "같이타기 메이커 이용시"
	$chkData["memDcName"] = (string)$memDcName;				// 현재등급 수수료

	$memMatChkQuery = "SELECT (memMatCnt - :matCnt) AS more_Cnt, memLv_Nick FROM TB_MEMBER_LEVEL WHERE memLv = ((SELECT memLv FROM TB_MEMBER_LEVEL WHERE memLv NOT IN ('1', '2') AND memMatCnt <= :matCnt ORDER BY idx ASC LIMIT 1) - 1)";	
	$memMatChkStmt = $DB_con->prepare($memMatChkQuery);
	$memMatChkStmt->bindparam(":matCnt", $matCnt);
	$memMatChkStmt->execute();
	$memMatChkRow = $memMatChkStmt->fetch(PDO::FETCH_ASSOC);
	$more_Cnt = $memMatChkRow['more_Cnt'];
	$memLv_Nick = $memMatChkRow['memLv_Nick'];

	if($matCnt > 4){
		$moreMemo = "축하드려요! 다음달 가치타 VIP가 됩니다.";
	}else{
		$moreMemo = $more_Cnt."회 더 이용시 다음달 가치타 VIP가 됩니다.";
	}
	$chkData["moreMemo"] = $moreMemo;

	$month = date('n');
	$matMemo = $month . "월 누적 이용횟수 : " . $matCnt . "회";
	$chkData["matMemo"] = (string)$matMemo;


	$memQuery = "SELECT idx, memLv, memLv_Name, memLv_Nick, memLv_MatName, memIconFile, memLv_Color, memMatCnt, memDc FROM TB_MEMBER_LEVEL WHERE memLv NOT IN ('1', '2') ORDER BY memLv DESC";
	$stmt = $DB_con->prepare($memQuery);
	$stmt->execute();
	$num = $stmt->rowCount();

	if ($num < 1) { //아닐경우
		$chkResult = "1";
		$chkData["totCnt"] = (int)$num;
		$chkData["data"] = [];
	} else {

		$chkResult = "1";

		$data  = [];
		while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {

			$idx = $row['idx'];										// 고유번호
			$memLv = $row['memLv'];									// 레벨
			$memLvNm = $row['memLv_Name'];							// 등급명
			$memLvNick = $row['memLv_Nick'];						// 등급명
			$memLvMatName = $row['memLv_MatName'];					// 달성조건
			$memLvColor = $row['memLv_Color'];						// 등급색상
			$memMatCnt = $row['memMatCnt'];							// 달성조건
			$memDc = $row['memDc'];									// 혜택 
			$memDcSubName = "같이타기 메이커 이용시";				 // 혜택조건서브
			$memDcName = "수수료 " . $memDc . "%";						// 혜택조건 
			$memIconFile = $row['memIconFile'];
			// 이미지 경로 (/data/levIcon)
			$levImg = "/data/levIcon/photo.php?id=" . $memIconFile;

			$result = array("memLv" => (string)$memLv, "memLvColor" => (string)$memLvColor, "memLvNm" => (string)$memLvNm, "memLvNick" => (string)$memLvNick, "memLvMatName" => (string)$memLvMatName, "memDcSubName" => (string)$memDcSubName, "memDcName" => (string)$memDcName, "levImg" => (string)$levImg);
			array_push($data, $result);
		}

		$chkData["result"] = true;
		$chkData['data'] = $data;
	}

	dbClose($DB_con);
	$stmt = null;

	if ($chkResult  == "1") {
		$output = str_replace('\\\/', '/', json_encode($chkData, JSON_UNESCAPED_UNICODE + JSON_PRETTY_PRINT));
		echo  urldecode($output);
	} else {
		$result = array("result" => false);
		echo json_encode($result, JSON_UNESCAPED_UNICODE);
	}
}
