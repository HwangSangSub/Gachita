<?php
/*======================================================================================================================

* 프로그램				:  매칭 요청자 만남완료 확인 이후 카드결제 (만남완료 확인 이후 결제방식을 카드결제 선택시 카드결제 진행[즉시])
* 페이지 설명			:  매칭 요청자 만남완료 확인 이후 카드결제 (만남완료 확인 이후 결제방식을 카드결제 선택시 카드결제 진행[즉시])
* 파일명              :  taxiSharingRPayProc.php

========================================================================================================================*/
include "../lib/common.php";
include "../lib/functionDB.php";  //공통 db함수
include "../order/lib/TPAY.LIB.php";  //공통 db함수
include "../order/lib/tpay_proc.php"; // 아임포트 함수

$mem_Id  = trim($memId);				// 재인증요청회원아이디
$mem_Idx  = memIdxInfo($mem_Id);				// 회원고유번호
$imp_id  = trim($imp_uid);				// 아임포트본인인증 고유아이디

$access_token = get_Token_PayForm('https://api.iamport.kr/users/getToken', array("imp_key" => $imp_key, "imp_secret" => $imp_secret));
//메세지
$DB_con = db1();
if ($access_token == '') {
	$result = array("result" => false, "errorMsg" => "#1. " . $accesstoken_message);
} else if ($access_token != '') {
	$res = certifi_Chk('https://api.iamport.kr/certifications/' . $imp_id, $access_token);
    // print_r($res);
    // exit;
	$code = $res['code'];								//성공여부
	$message = $res['message'];							//메세지
	$certified = $res['response']['certified'];			//인증상태
	$memNm = $res['response']['name'];					//이름
	$memIdx = $res['response']['merchant_uid'];		//회원고유아이디
	$memgender = $res['response']['gender'];				//회원성별
	$mem_Birth = $res['response']['birth'];				//회원생일
	$memTel = $res['response']['phone'];				//회원연락처
	$memBirth = date("Ymd", $mem_Birth);				//생일

	if ($mem_gender == 'male') {
		$mem_Sex = "0";
	} else {
		$mem_Sex = "1";
	}
	if ($certified) {
        $chkQuery = "SELECT mem_Nm, mem_Birth, mem_Tel FROM TB_MEMBERS WHERE idx = :mem_Idx AND b_Disply = 'N'";
		$chkStmt = $DB_con->prepare($chkQuery);
		$chkStmt->bindparam(":mem_Idx", $mem_Idx);
		$chkStmt->execute();
        $chkCnt = $chkStmt->rowCount();

        if ($chkCnt < 1) {
            $result = array("result" => false, "errorMsg" => "가입되지 않은 회원이거나 탈퇴된 회원입니다. 확인 후 다시 시도해주세요.");
        } else {
            $chkRow = $chkStmt->fetch(PDO::FETCH_ASSOC);
            $mem_ChkNm = $chkRow['mem_Nm'];                // 회원명
            $mem_ChkBirth = $chkRow['mem_Birth'];          // 생년월일
            $mem_ChkTel = $chkRow['mem_Tel'];              // 연락처

            if($memNm == $mem_ChkNm && $memTel == $mem_ChkTel && $memBirth == $mem_ChkBirth){
                $result = array("result" => true);
            }else{
                $result = array("result" => false, "errorMsg" => "회원정보가 일치하지 않습니다. 확인 후 다시 시도해주세요.");
            }
        }
	} else {
		$result = array("result" => false, "errorMsg" => "#3 : 미인증");
	}
} else {
	$result = array("result" => false, "errorMsg" => "#2 : 인증토큰 발급오류가 있습니다.");
}
echo str_replace('\\/', '/', json_encode($result, JSON_UNESCAPED_UNICODE));
