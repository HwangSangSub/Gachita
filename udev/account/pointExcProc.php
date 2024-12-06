<?php
/*======================================================================================================================

* 프로그램				:  환전요청 (완료-입금완료, 취소-환전거절)
* 페이지 설명			:  환전요청 (완료-입금완료, 취소-환전거절)
* 파일명              :  pointExcProc.php

========================================================================================================================*/
include "../../udev/lib/common.php";
include "../../lib/functionDB.php";  //공통 db함수

$idx = trim($idx);					// 고유번호 (환전신청번호)
$mem_Id  = trim($memId);			// 회원 아이디
$mem_Idx = memIdxInfo($mem_Id);		// 회원 고유아이디    
$MemNm = memNickInfo($mem_Id);		// 회원 닉네임
$e_Disply = trim($e_Disply);		// 승인여부 (승인시 완료처리 / 미승인시 포인트 반납)

$reg_Date = DU_TIME_YMDHIS;

$DB_con = db1();
//회원 고유 아이디
$Query = "SELECT count(idx) FROM TB_POINT_EXC WHERE idx = :idx AND mem_Id = :mem_Id AND mem_Idx = :mem_Idx";
$Stmt = $DB_con->prepare($Query);
$Stmt->bindparam(":idx", $idx);
$Stmt->bindparam(":mem_Id", $mem_Id);
$Stmt->bindparam(":mem_Idx", $mem_Idx);
$Stmt->execute();
$Num = $Stmt->rowCount();

if ($Num < 1) {			//내역이 없을 경우
	$result = array("result" => "error", "errorMsg" => "요청내역이 없습니다.");
} else {
	if ($e_Disply == "Y") { /////출금요청승인 시작 - 푸시알림
		//입금완료일
		$upQquery1 = "UPDATE TB_POINT_EXC SET e_Disply = 'Y', reg_ExcDate = NOW() WHERE idx = :idx AND mem_Id = :mem_Id LIMIT 1";
		$upStmt1 = $DB_con->prepare($upQquery1);
		$upStmt1->bindparam(":idx", $idx);
		$upStmt1->bindparam(":mem_Id", $mem_Id);
		$upStmt1->execute();

		//신청자 푸시
		$mem_NToken = memMatchTokenInfo($mem_Idx);

		$chkState = "10";  //거래완료
		$ntitle = "";
		$nmsg = "출금요청이 승인 및 입금되었습니다.";
		foreach ($mem_NToken as $k => $v) {
			$ntokens = $mem_NToken[$k];
			$ninputData = array("title" => $ntitle, "msg" => $nmsg, "state" => $chkState);
			$nresult = send_Push($ntokens, $ninputData);
		}

		$result['success']	= true;
		$result['Msg']	= "입금완료처리 되었습니다.";
		//////////////////////////////출금요청승인 끝
	} else { ////////////////////출금요청거절 시작 - 포인트환불, 푸시알림
		//회원 고유 아이디
		$Query = "SELECT idx, mem_Id, exc_Price FROM TB_POINT_EXC WHERE idx = :idx AND mem_Id = :mem_Id AND mem_Idx = :mem_Idx ";
		$Stmt = $DB_con->prepare($Query);
		$Stmt->bindparam(":idx", $idx);
		$Stmt->bindparam(":mem_Id", $mem_Id);
		$Stmt->bindparam(":mem_Idx", $mem_Idx);
		$Stmt->execute();

		while ($Row = $Stmt->fetch(PDO::FETCH_ASSOC)) {
			$mem_Id = $Row['mem_Id'];            // 신청자 아이디
			$exc_Price = $Row['exc_Price'];		 // 출금요청금액
		}
		//포인트금액 조회
		$memQuery = "SELECT idx, mem_Point FROM TB_MEMBERS_ETC WHERE mem_Idx = :mem_Idx ";
		$stmt2 = $DB_con->prepare($memQuery);
		$stmt2->bindParam("mem_Idx", $mem_Idx);
		$stmt2->execute();
		while ($Row2 = $stmt2->fetch(PDO::FETCH_ASSOC)) {
			$mem_Point = $Row2['mem_Point'];				// 회원 보유 포인트
		}
		if ($mem_Point == 0) {
			$mem_Point = 0;
		} else {
			$memPoint = $mem_Point;
		}

		$taxi_Memo = $reg_Date . "
" . $MemNm . " 님! 요청 출금건이 거절되어 " . number_format($exc_Price) . "포인트가 재적립되었습니다.";
		$taxi_Sign = '0';		// 포인트 구분 (0: +, 1: -)
		$taxi_PState = '2';		// 구분 (0: 매칭, 1: 적립, 2: 환전)
		//회원기타정보에 카드등록여부 수정
		$UpCardQuery = "INSERT INTO TB_POINT_HISTORY (taxi_MemId, taxi_MemIdx, taxi_OrdNo, taxi_OrdPoint, taxi_OrgPoint, taxi_Memo, taxi_Sign, taxi_PState, reg_Date) VALUES (:taxi_MemId, :taxi_MemIdx, :taxi_OrdNo, :taxi_OrdPoint, :taxi_OrgPoint, :taxi_Memo, :taxi_Sign, :taxi_PState, :reg_Date)";
		$upMstmt = $DB_con->prepare($UpCardQuery);
		$upMstmt->bindParam(":taxi_MemId", $mem_Id);
		$upMstmt->bindParam(":taxi_MemIdx", $mem_Idx);
		$upMstmt->bindParam(":taxi_OrdNo", $mem_Idx);
		$upMstmt->bindParam(":taxi_OrdPoint", $exc_Price);
		$upMstmt->bindParam(":taxi_OrgPoint", $memPoint);
		$upMstmt->bindParam(":taxi_Memo", $taxi_Memo);
		$upMstmt->bindParam(":taxi_Sign", $taxi_Sign);
		$upMstmt->bindParam(":taxi_PState", $taxi_PState);
		$upMstmt->bindParam(":reg_Date", $reg_Date);
		$upMstmt->execute();

		//양도금액 포함 포인트(요청자의 경우 차감 으로 -)
		(int)$totPoint = (int)$memPoint + (int)$exc_Price; // 현재포인트 = 보유포인트 + 환전요청포인트

		//포인트 변경
		$upmsPQquery = "UPDATE TB_MEMBERS_ETC SET mem_Point = :mem_Point WHERE mem_Idx = :mem_Idx  LIMIT 1";
		//$upmsPQquery = "UPDATE TB_MEMBERS_ETC SET mem_MatCnt = $mtotMatCnt WHERE mem_Id = $taxiOrdMemId  LIMIT 1";
		//echo $upmsPQquery."<BR>";
		//exit;
		$upmsPStmt = $DB_con->prepare($upmsPQquery);
		$upmsPStmt->bindparam(":mem_Point", $totPoint);
		$upmsPStmt->bindparam(":mem_Idx", $mem_Idx);
		$upmsPStmt->execute();

		//입금완료일
		$upQquery1 = "UPDATE TB_POINT_EXC SET e_Disply = 'C', reg_ExcDate = NOW() WHERE idx = :idx AND mem_Id = :mem_Id LIMIT 1";
		$upStmt1 = $DB_con->prepare($upQquery1);
		$upStmt1->bindparam(":idx", $idx);
		$upStmt1->bindparam(":mem_Id", $mem_Id);
		$upStmt1->execute();

		//푸시
		$mem_NToken = memMatchTokenInfo($mem_Idx);

		$chkState = "10";  //거래완료
		$ntitle = "";
		$nmsg = "출금요청이 거절되었습니다. 출금요청 포인트가 재적립되었습니다.";


		foreach ($mem_NToken as $k => $v) {
			$ntokens = $mem_NToken[$k];
			$ninputData = array("title" => $ntitle, "msg" => $nmsg, "state" => $chkState);
			$nresult = send_Push($ntokens, $ninputData);
		}

		$result['success']	= true;
		$result['Msg']	= "환전요청을 거절하였습니다.";
	} /////////////////////////환전요청거절 끝
}

dbClose($DB_con);
$Stmt = null;
$stmt2 = null;
$upMstmt = null;
$upStmt1 = null;
$upmsPStmt = null;
$nSidStmt = null;
$delstmt = null;

echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
