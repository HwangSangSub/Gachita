<?
/*======================================================================================================================

* 프로그램				:  환전 요청 (등록)
* 페이지 설명			:  환전 요청 (등록)
* 파일명              :  myPointExc.php

========================================================================================================================*/
include "../lib/common.php";
include "../lib/functionDB.php";  //공통 db함수
include "../lib/card_password.php"; //카드정보 암호화

$mem_Id = trim($memId);				//회원 아이디
$mem_Idx = memIdxInfo($mem_Id);		//회원 고유아이디
$MemNm = memNickInfo($mem_Id);		//회원 닉네임
$excPrice = trim($excPrice);		//환전금액
$excPwd = trim($excPwd);			//환전비밀번호
$excIdx = trim($excIdx);			//환전비밀번호
$reg_Date = DU_TIME_YMDHIS;			//등록일

if ($mem_Id != "") {  //아이디가 있는 경우

	$DB_con = db1();
	//비밀번호, 포인트금액 조회
	$memQuery = "SELECT idx, mem_Point, mem_ExcPwd from TB_MEMBERS_ETC WHERE mem_Idx = :mem_Idx ";
	$stmt = $DB_con->prepare($memQuery);
	$stmt->bindParam("mem_Idx", $mem_Idx);
	$stmt->execute();
	while ($Row = $stmt->fetch(PDO::FETCH_ASSOC)) {
		$mem_Point = $Row['mem_Point'];				// 회원 보유 포인트
		$mem_ExcPwd = $Row['mem_ExcPwd'];				// 환전비밀번호
		$memExcPwd = openssl_decrypt(base64_decode($mem_ExcPwd), 'aes-256-cbc', $password, OPENSSL_RAW_DATA, $iv); //환전비밀번호 복호화
	}
	//등록된 비밀번호와 사용자가 입력한 현재 비밀번호가 일치 한지 확인
	if ($excPwd != $memExcPwd) {
		$result = array("result" => false, "errorMsg" => (string)"입력하신 비밀번호가 틀립니다. 확인 후 다시 시도해주세요.");
	} else {
		if ((int)$mem_Point == 0) {
			$memPoint = 0;
		} else {
			$memPoint = $mem_Point;
		}
		if ($excPrice > $memPoint) { // 환전요청금액이 보유포인트보다 많을 경우
			$result = array("result" => false, "errorMsg" => (string)"#1 요청한 출금포인트가 보유 포인트보다 많습니다. 확인 후 다시 시도해주세요.");
		} else {
			$insBilQuery = "INSERT INTO TB_POINT_EXC (mem_Id, mem_Idx, exc_Idx, exc_Price, e_Disply, reg_Date) VALUES (:mem_Id, :mem_Idx, :exc_Idx, :exc_Price, 'N', :reg_Date)";
			$upMstmt1 = $DB_con->prepare($insBilQuery);
			$upMstmt1->bindParam(":mem_Id", $mem_Id);
			$upMstmt1->bindParam(":mem_Idx", $mem_Idx);
			$upMstmt1->bindParam(":exc_Idx", $excIdx);
			$upMstmt1->bindParam(":exc_Price", $excPrice);
			$upMstmt1->bindParam(":reg_Date", $reg_Date);
			$upMstmt1->execute();
			$DB_con->lastInsertId();

			$mIdx = $DB_con->lastInsertId();  //저장된 idx 값 
			$taxi_Memo = $reg_Date . "
" . $MemNm . " 님! 포인트 출금요청으로 " . number_format($excPrice) . "원이 차감되었습니다.";
			$taxi_Sign = '1';		// 포인트 구분 (0: +, 1: -)
			$taxi_PState = '2';		// 구분 (0: 매칭, 1: 적립, 2: 환전)
			//회원기타정보에 카드등록여부 수정
			$UpCardQuery = "INSERT INTO TB_POINT_HISTORY (taxi_MemId, taxi_MemIdx, taxi_OrdNo, taxi_OrdPoint, taxi_OrgPoint, taxi_Memo, taxi_Sign, taxi_PState, reg_Date) VALUES (:taxi_MemId, :taxi_MemIdx, :taxi_OrdNo, :taxi_OrdPoint, :taxi_OrgPoint, :taxi_Memo, :taxi_Sign, :taxi_PState, :reg_Date)";
			$upMstmt2 = $DB_con->prepare($UpCardQuery);
			$upMstmt2->bindParam(":taxi_MemId", $mem_Id);
			$upMstmt2->bindParam(":taxi_MemIdx", $mem_Idx);
			$upMstmt2->bindParam(":taxi_OrdNo", $mIdx);
			$upMstmt2->bindParam(":taxi_OrdPoint", $excPrice);
			$upMstmt2->bindParam(":taxi_OrgPoint", $memPoint);
			$upMstmt2->bindParam(":taxi_Memo", $taxi_Memo);
			$upMstmt2->bindParam(":taxi_Sign", $taxi_Sign);
			$upMstmt2->bindParam(":taxi_PState", $taxi_PState);
			$upMstmt2->bindParam(":reg_Date", $reg_Date);
			$upMstmt2->execute();

			//양도금액 포함 포인트(요청자의 경우 차감 으로 -)
			(int)$totPoint = (int)$memPoint - (int)$excPrice; // 현재포인트 = 보유포인트 - 환전요청포인트

			//포인트 변경
			$upmsPQquery = "UPDATE TB_MEMBERS_ETC SET mem_Point = :mem_Point WHERE mem_Idx = :mem_Idx  LIMIT 1";
			//echo $upmsPQquery."<BR>";
			//exit;
			$upmsPStmt = $DB_con->prepare($upmsPQquery);
			$upmsPStmt->bindparam(":mem_Point", $totPoint);
			$upmsPStmt->bindparam(":mem_Idx", $mem_Idx);
			$upmsPStmt->execute();

			$result = array("result" => true, "idx" => (int)$mIdx); // 각 API 성공값 부분
		}
	}
	dbClose($DB_con);
	$stmt = null;
	$upStmt1 = null;
	$upStmt2 = null;
	$upmsPStmt = null;
} else {
	$result = array("result" => false, "errorMsg" => "조회정보값이 없습니다. 관리자에게 문의바랍니다.");
}

echo str_replace('\\/', '/', json_encode($result, JSON_UNESCAPED_UNICODE));
