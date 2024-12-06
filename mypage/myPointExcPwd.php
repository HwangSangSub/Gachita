<?
/*======================================================================================================================

* 프로그램				:  환전 비밀번호
* 페이지 설명			:  환전 비밀번호 등록 및 변경, 찾기 기능
* 파일명              :  myPointExcPwd.php

========================================================================================================================*/
include "../lib/common.php";
include "../lib/functionDB.php";  //공통 db함수
include "../lib/card_password.php"; //카드정보 암호화

$mem_Id = trim($memId);                //회원 아이디
$mem_Idx = memIdxInfo($mem_Id);        //회원 고유아이디
$MemNm = memNickInfo($mem_Id);        //회원 닉네임
$mode = trim($mode);                //구분 (r: 초기 비밀번호 등록, m: 비밀번호 변경, s: 비밀번호 찾기)	==> 소문자 필수
$exc_Cpwd = trim($Cpwd);            //환전 현재 사용중 비밀번호
$exc_pwd = trim($pwd);                //환전 비밀번호1
$exc_Rpwd = trim($Rpwd);            //환전 비밀번호2
$DB_con = db1();

if ($mode == "r") {        // 비밀번호 등록
	if ($mem_Id != "" && $exc_pwd != "") {  //아이디가 있고 비밀번호입력 한것이 동일한 경우
		// 입력한 비밀번호 1, 2가 동일한지 확인
		if ($exc_pwd == $exc_Rpwd) {
			$chkpwd = 1;
		} else {
			$chkpwd = 0;
		}
		//동일한 경우 비밀번호 암호화 하여 DB 등록
		if ($chkpwd == 1) {
			$excpwd = base64_encode(openssl_encrypt($exc_pwd, 'aes-256-cbc', $password, OPENSSL_RAW_DATA, $iv)); //환전비밀번호 암호화
			$upmsPQquery = "UPDATE TB_MEMBERS_ETC SET mem_ExcPwd = :mem_ExcPwd WHERE mem_Idx = :mem_Idx  LIMIT 1";
			$upmsPStmt = $DB_con->prepare($upmsPQquery);
			$upmsPStmt->bindparam(":mem_ExcPwd", $excpwd);
			$upmsPStmt->bindparam(":mem_Idx", $mem_Idx);
			$upmsPStmt->execute();
			$result = array("result" => true);
		} else {
			$result = array("result" => false, "errorMsg" => "비밀번호가 동일하지 않습니다. 다시 입력해주세요.");
		}
	} else {
		$result = array("result" => false, "errorMsg" => "아이디 또는 비밀번호가 입력되지 않았습니다. 확인해주세요.");
	}
} else if ($mode == "m") {        //비밀번호 변경
	if ($mem_Id != "") {  //아이디가 있고 비밀번호입력 한것이 동일한 경우
		//일치 할 경우 변경할 비밀번호 1,2이 동일한지 확인
		if ($exc_pwd == $exc_Rpwd) {
			$chkpwd = 1;
		} else {
			$chkpwd = 0;
		}
		//동일 한 경우 비밀번호 암호화 하여 변경하기
		if ($chkpwd == 1) {
			$excpwd = base64_encode(openssl_encrypt($exc_pwd, 'aes-256-cbc', $password, OPENSSL_RAW_DATA, $iv)); //환전비밀번호 암호화
			$upmsPQquery = "UPDATE TB_MEMBERS_ETC SET mem_ExcPwd = :mem_ExcPwd WHERE mem_Idx = :mem_Idx  LIMIT 1";
			$upmsPStmt = $DB_con->prepare($upmsPQquery);
			$upmsPStmt->bindparam(":mem_ExcPwd", $excpwd);
			$upmsPStmt->bindparam(":mem_Idx", $mem_Idx);
			$upmsPStmt->execute();
			$result = array("result" => true);
		} else {
			$result = array("result" => false, "errorMsg" => "변경할 비밀번호가 동일하지 않습니다. 다시 입력해주세요.");
		}
	} else {
		$result = array("result" => false, "errorMsg" => "아이디 또는 비밀번호가 입력되지 않았습니다. 확인해주세요.");
	}
} else {
	$result = array("result" => false, "errorMsg" => "잘못된 구분값이 지정되었습니다. 확인해주세요.");
}

dbClose($DB_con);
$stmt = null;
$upmsPStmt = null;

echo str_replace('\\/', '/', json_encode($result, JSON_UNESCAPED_UNICODE));
