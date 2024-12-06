<?
/*======================================================================================================================

* 프로그램				:  환전 관련 설정 값
* 페이지 설명			:  환전 관련 설정 값
* 파일명              :  configExc.php

========================================================================================================================*/
include "../lib/common.php";
include "../lib/functionDB.php";  //공통 db함수

//$mem_Id = "shut7720@hanmail.net";
$mem_Id = trim($memId);				//회원 아이디
$mem_Idx = memIdxInfo($mem_Id);		//회원 고유아이디
$DB_con = db1();

// 은행조회
$cardCQuery = "SELECT card_Name from TB_CARD_CODE WHERE c_Disply = 'Y' AND card_Type = '2' ORDER BY idx ";	//은행만 불러옴
$cardCStmt = $DB_con->prepare($cardCQuery);
$cardCStmt->execute();
$cardNum = $cardCStmt->rowCount();

if ($cardNum < 1) { //아닐경우
} else {
	$card_Name = [];
	while ($cardCRow = $cardCStmt->fetch(PDO::FETCH_ASSOC)) {
		$card_Name[] = $cardCRow['card_Name'];				// 카드사이름
	}
}

// 환전가능금액조회
$Query = "SELECT idx, con_Price1, con_Price2, con_Price3, con_Tax from TB_CONFIG_EXC ";
$Stmt = $DB_con->prepare($Query);
$Stmt->execute();
$Num = $Stmt->rowCount();

if ($Num < 1) { //아닐경우
} else {
	$conPrice = [];
	while ($row = $Stmt->fetch(PDO::FETCH_ASSOC)) {
		$con_Price1 = $row['con_Price1'];		//1단계 금액
		$con_Price2 = $row['con_Price2'];		//2단계 금액
		$con_Price3 = $row['con_Price3'];		//3단계 금액
		$con_Tax = $row['con_Tax'];				//환전 수수료
		$conPrice1 = (int)$con_Price1 + (int)$con_Tax;
		$conPrice2 = (int)$con_Price2 + (int)$con_Tax;
		$conPrice3 = (int)$con_Price3 + (int)$con_Tax;
		$conPrice[] = (string)$conPrice1;
		$conPrice[] = (string)$conPrice2;
		$conPrice[] = (string)$conPrice3;
	}
}

//회원환전비밀번호 등록여부 조회
$mQuery = "SELECT idx, mem_ExcPwd from TB_MEMBERS_ETC WHERE mem_Idx = :mem_Idx AND mem_Id = :mem_Id ";
$mStmt = $DB_con->prepare($mQuery);
$mStmt->bindParam(":mem_Idx", $mem_Idx);
$mStmt->bindParam(":mem_Id", $mem_Id);
$mStmt->execute();
$mNum = $mStmt->rowCount();

if ($mNum < 1) { //아닐경우
	$result = array("result" => false, "errorMsg" => (string)"등록된 회원이 아닙니다.");
} else {
	while ($mrow = $mStmt->fetch(PDO::FETCH_ASSOC)) {
		$mem_ExcPwd = $mrow['mem_ExcPwd'];		//환전비밀번호 등록
		if ($mem_ExcPwd != "") {
			$chk_ExcPwd = "1";
		} else {
			$chk_ExcPwd = "0";
		}
	}
}

$result = array("result" => true, "card_Name" => (string)$card_Name, "con_Price" => (int)$conPrice, "chk_ExcPwd" => (string)$chk_ExcPwd);

dbClose($DB_con);
$Stmt = null;

echo json_encode($result, JSON_UNESCAPED_UNICODE);
