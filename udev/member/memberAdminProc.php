<?
include "../../udev/lib/common.php";
include "../../lib/alertLib.php";
include "../../lib/thumbnail.lib.php";   //썸네일

$mode = trim($mode);										// 구분(reg : 등록, mod : 수정)
$mem_Id = trim($memId);										// 아이디
$mem_Idx = trim($memIdx);										// 아이디
$memPwd = trim($memPwd);									// 변경할 비밀번호

if ($memPwd == "") {
	$mem_Pwd = trim($mem_Pwd);								// 기존 비밀번호
} else {
	$mem_Pwd = password_hash($memPwd, PASSWORD_DEFAULT);  	// 비밀번호 암호화 
}

$mem_NickNm = trim($memNickNm);								// 닉네임
$mem_Tel = trim($memTel);									// 연락처
$mem_GroupDownUrl = trim($memGroupDownUrl);					// 다운로드 주소
$mem_Memo = trim($memMemo);									// 메모

$DB_con = db1();

if ($mode == "reg") {		// 추가일 경우
	//회원코드
	$cntQuery = "SELECT count(idx) AS num FROM TB_MEMBERS WHERE mem_Id = :mem_Id ";
	$cntStmt = $DB_con->prepare($cntQuery);
	$cntStmt->bindparam(":mem_Id", $mem_Id);
	$cntStmt->execute();
	$cntRow = $cntStmt->fetch(PDO::FETCH_ASSOC);
	$vnum = $cntRow['num'];
	if ($vnum > 1) { //있을 경우
	} else {

		$mem_Lv = 2;													 // 등급 - 관리자추가로 level 1
		$b_Disply = "N";												 //탈퇴여부(N:가입/Y:탈퇴)
		$reg_Date = date("Y-m-d H:i:s");										 //등록일

		//회원 기본테이블 저장
		$insQuery = "
				INSERT INTO TB_MEMBERS (mem_Id, mem_NickNm, mem_Tel, mem_Lv, b_Disply, reg_date ) 
				VALUES ('" . $mem_Id . "', '" . $mem_NickNm . "', '" . $mem_Tel . "', '" . $mem_Lv . "', '" . $b_Disply . "', '" . $reg_Date . "' )";
		$DB_con->exec($insQuery);
		$mIdx = $DB_con->lastInsertId();  //저장된 idx 값

		if ($mIdx > 0) { //삽입 성공
			//회원 정보테이블 저장
			$insInFoQuery = "
					INSERT INTO TB_MEMBERS_INFO (mem_Idx, mem_Id, mem_Memo ) 
					VALUES ('" . $mIdx . "', '" . $mem_Id . "', '" . $mem_Memo . "' )";
			$DB_con->exec($insInFoQuery);

			//회원 기타테이블 저장
			$insEtcQuery = "
					INSERT INTO TB_MEMBERS_ETC (mem_Idx, mem_Id, mem_GroupDownUrl) 
					VALUES ('" . $mIdx . "', '" . $mem_Id . "' , '" . $mem_GroupDownUrl . "')";
			$DB_con->exec($insEtcQuery);
		}
	}
	$preUrl = "memberAdminList.php?page=$page&$qstr";
	$message = "reg";
	proc_msg($message, $preUrl);
} else if ($mode == "mod") { //수정일경우

	//회원 기본 수정
	$upQquery = "UPDATE TB_MEMBERS SET mem_NickNm = :mem_NickNm, mem_Pwd = :mem_Pwd, mem_Tel = :mem_Tel WHERE idx = :idx LIMIT 1";
	$upStmt = $DB_con->prepare($upQquery);
	$upStmt->bindparam(":mem_NickNm", $mem_NickNm);
	$upStmt->bindparam(":mem_Pwd", $mem_Pwd);
	$upStmt->bindParam(":mem_Tel", $mem_Tel);
	$upStmt->bindParam(":idx", $mem_Idx);
	$upStmt->execute();

	//회원 기타 정보 수정
	$upQquery2 = "UPDATE TB_MEMBERS_INFO SET mem_Memo = :mem_Memo WHERE mem_Idx = :mem_Idx LIMIT 1";
	$upStmt2 = $DB_con->prepare($upQquery2);
	$upStmt2->bindParam(":mem_Memo", $mem_Memo);
	$upStmt2->bindParam(":mem_Idx", $mem_Idx);
	$upStmt2->execute();

	//회원 기타 정보 수정
	$upQquery3 = "UPDATE TB_MEMBERS_ETC SET mem_GroupDownUrl = :mem_GroupDownUrl WHERE mem_Idx = :mem_Idx LIMIT 1";
	$upStmt3 = $DB_con->prepare($upQquery3);
	$upStmt3->bindParam(":mem_GroupDownUrl", $mem_GroupDownUrl);
	$upStmt3->bindParam(":mem_Idx", $mem_Idx);
	$upStmt3->execute();


	$preUrl = "memberAdminList.php?page=$page&$qstr";
	$message = "mod";
	proc_msg($message, $preUrl);
} else {  //삭제일경우
}



dbClose($DB_con);
$stmt = null;
$upStmt = null;
$upStmt2 = null;
$chkStmt = null;
$cntStmt = null;
$upStmt = null;
