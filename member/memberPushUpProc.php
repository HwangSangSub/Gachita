<?
/*======================================================================================================================

* 프로그램			: 이벤트 공지 알림 및  매칭 쪽지 알림 설정
* 페이지 설명		: 이벤트 공지 알림 및  매칭 쪽지 알림 설정
* 파일명                 : memberPushUpProc.php

========================================================================================================================*/


include "../lib/common.php";
include "../lib/functionDB.php";  //공통 db함수

$mem_Id = trim($memId);				//아이디
$mem_Idx = memIdxInfo($mem_Id);   //회원 주아이디
$mem_NPush = trim($npush);			//이벤트 공지 알림 ( 0 : ON, 1: OFF)
$mem_MPush = trim($mpush);			//매칭 및 쪽지 알림 ( 0 : ON, 1: OFF)


if ($mem_Id != "") {  //아이디가 있을 경우

	$DB_con = db1();

	$memQuery = "SELECT idx, mem_NPush, mem_MPush from TB_MEMBERS WHERE idx = :mem_Idx AND mem_Id = :mem_Id AND b_Disply = 'N'";
	$stmt = $DB_con->prepare($memQuery);
	$stmt->bindparam(":mem_Idx", $mem_Idx);
	$stmt->bindparam(":mem_Id", $mem_Id);
	$stmt->execute();
	$num = $stmt->rowCount();


	if ($num < 1) { //아닐경우
		$result = array("result" => false, "errorMsg" => "등록된 회원이 아닙니다. 다시 시도해주세요.");
	} else {

		while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
			$memNPush = $row['mem_NPush'];
			$memMPush = $row['mem_MPush'];
		}

		if ($mem_NPush != "") {
			$mem_NPush = $mem_NPush;
		} else {
			$mem_NPush = $memNPush;
		}


		if ($mem_MPush != "") {
			$mem_MPush = $mem_MPush;
		} else {
			$mem_MPush = $memMPush;
		}


		$upQquery = "UPDATE TB_MEMBERS SET mem_NPush = :mem_NPush, mem_MPush = :mem_MPush WHERE mem_Id = :mem_Id AND idx = :mem_Idx LIMIT 1";
		$upStmt = $DB_con->prepare($upQquery);
		$upStmt->bindparam(":mem_NPush", $mem_NPush);
		$upStmt->bindparam(":mem_MPush", $mem_MPush);
		$upStmt->bindparam(":mem_Id", $mem_Id);
		$upStmt->bindparam(":mem_Idx", $mem_Idx);
		$upStmt->execute();

		$result = array("result" => true);
	}

	dbClose($DB_con);
	$stmt = null;
	$upStmt = null;
} else {
	$result = array("result" => false, "errorMsg" => "조회정보값이 없습니다. 관리자에게 문의바랍니다.");
}

echo json_encode($result);
