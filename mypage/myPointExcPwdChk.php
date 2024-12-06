<?
/*======================================================================================================================

* 프로그램				:  환전 비밀번호
* 페이지 설명			:  환전 비밀번호 기존비밀번호 확인하기.
* 파일명              :  myPointExcPwdChk.php

========================================================================================================================*/
include "../lib/common.php";
include "../lib/functionDB.php";  //공통 db함수
include "../lib/card_password.php"; //카드정보 암호화

$mem_Id = trim($memId);                //회원 아이디
$mem_Idx = memIdxInfo($mem_Id);        //회원 고유아이디
$exc_Cpwd = trim($pwd);            //환전 현재 사용중 비밀번호
$DB_con = db1();

if ($mem_Id != "" && $exc_Cpwd != "") {  //아이디가 있고 비밀번호입력 한것이 동일한 경우
    //등록된 비밀번호 조회
    $memQuery = "SELECT idx, mem_ExcPwd from TB_MEMBERS_ETC WHERE mem_Idx = :mem_Idx ";
    $stmt = $DB_con->prepare($memQuery);
    $stmt->bindParam("mem_Idx", $mem_Idx);
    $stmt->execute();
    while ($Row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $mem_ExcPwd = $Row['mem_ExcPwd'];                // 환전비밀번호
        $memExcPwd = openssl_decrypt(base64_decode($mem_ExcPwd), 'aes-256-cbc', $password, OPENSSL_RAW_DATA, $iv); //환전비밀번호 복호화
    }
    //등록된 비밀번호와 사용자가 입력한 현재 비밀번호가 일치 한지 확인
    if ($exc_Cpwd == $memExcPwd) {
        $result = array("result" => true);
    } else {
        $result = array("result" => false, "errorMsg" => "현재 비밀번호가 틀립니다. 다시 입력해주세요.");
    }
} else {
    $result = array("result" => false, "errorMsg" => "아이디 또는 비밀번호가 입력되지 않았습니다. 확인해주세요.");
}

dbClose($DB_con);
$stmt = null;
$upmsPStmt = null;

echo str_replace('\\/', '/', json_encode($result, JSON_UNESCAPED_UNICODE));
