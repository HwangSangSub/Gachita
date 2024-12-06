<?php
/*======================================================================================================================

* 프로그램				:  본인인증 결과값 확인하기.
* 페이지 설명			:  본인인증 결과값 확인하기.
* 파일명              :  certifi_proc.php

========================================================================================================================*/
include "../lib/common.php";
include "../lib/functionDB.php";  //공통 db함수
include "../order/lib/TPAY.LIB.php";  //공통 db함수
include "../order/lib/tpay_proc.php"; // 아임포트 함수

$imp_id  = trim($imp_uid);				// 아임포트본인인증 고유아이디

$access_token = get_Token_PayForm('https://api.iamport.kr/users/getToken', array("imp_key" => $imp_key, "imp_secret" => $imp_secret));
//메세지
$DB_con = db1();
if ($access_token == '') {
	$result = array("result" => false, "errorMsg" => "#1. " . $accesstoken_message);
} else if ($access_token != '') {
	$res = certifi_Chk('https://api.iamport.kr/certifications/' . $imp_id, $access_token);
	$code = $res['code'];								//성공여부
	$message = $res['message'];							//메세지
	$certified = $res['response']['certified'];			//인증상태
	$mem_Nm = $res['response']['name'];					//이름
	$mem_Idx = $res['response']['merchant_uid'];		//회원고유아이디
	$mem_gender = $res['response']['gender'];				//회원성별
	$mem_Birth = $res['response']['birth'];				//회원생일
	$mem_Tel = $res['response']['phone'];				//회원연락처
	$memBirth = date("Ymd", $mem_Birth);				//생일

	if ($mem_gender == 'male') {
		$mem_Sex = "0";
	} else {
		$mem_Sex = "1";
	}
	if ($certified) {
		$upQuery = "UPDATE TB_MEMBERS SET mem_Nm = :mem_Nm, mem_CertBit = '1', mem_CertId = :mem_CertId, mem_Birth = :mem_Birth, mem_Tel = :mem_Tel  WHERE idx = :mem_Idx";
		// echo $upQuery;
		// exit;
		$upStmt = $DB_con->prepare($upQuery);
		$upStmt->bindparam(":mem_Nm", $mem_Nm);
		$upStmt->bindparam(":mem_CertId", $imp_id);
		$upStmt->bindparam(":mem_Birth", $memBirth);
		$upStmt->bindparam(":mem_Tel", $mem_Tel);
		$upStmt->bindparam(":mem_Idx", $mem_Idx);
		$upStmt->execute();

		$upQuery2 = "UPDATE TB_MEMBERS_INFO SET mem_Sex = :mem_Sex WHERE mem_Idx = :mem_Idx";
		$upStmt2 = $DB_con->prepare($upQuery2);
		$upStmt2->bindparam(":mem_Sex", $mem_Sex);
		$upStmt2->bindparam(":mem_Idx", $mem_Idx);
		$upStmt2->execute();
		$result = array("result" => true, "memBirth" => (string)$memBirth, "memSex" => (string)$mem_Sex);
	} else {
		$result = array("result" => false, "errorMsg" => "#3 : 미인증");
	}
} else {
	$result = array("result" => false, "errorMsg" => "#2 : 인증토큰 발급오류가 있습니다.");
}
echo str_replace('\\/', '/', json_encode($result, JSON_UNESCAPED_UNICODE));
