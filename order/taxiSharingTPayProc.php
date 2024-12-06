<?php
/*======================================================================================================================

* 프로그램				:  매칭 요청자 만남완료 확인 이후 카드결제 (만남완료 확인 이후 결제방식을 카드결제 선택시 카드결제 진행[즉시])
* 페이지 설명			:  매칭 요청자 만남완료 확인 이후 카드결제 (만남완료 확인 이후 결제방식을 카드결제 선택시 카드결제 진행[즉시])
* 파일명              :  taxiSharingTPayProc.php (사용안함 RPayProc.php 에 추가함 2019-01-15)

========================================================================================================================*/
include "../lib/common.php";
include "../lib/functionDB.php";  //공통 db함수
include "./lib/TPAY.LIB.php";  //공통 db함수
include "./lib/tpay_proc.php"; // 아임포트 함수
//require_once dirname(__FILE__).'/TPAY.LIB.php';  //tpay lib


$idx  = trim($idx);				// 투게더 고유번호 idx
$mem_Id  = trim($memId);		// 투게더 아이디

$DB_con = db1();

$mem_Idx = memIdxInfo($mem_Id);   //회원 주아이디    
$sMemNm = memNickInfo($mem_Id);   //요청자 닉네임
$ordTitNm = $sMemNm . "님! 합승 노선 요금";
// AND taxi_OrdState = 0 준비중상태 추가할 것
$viewQuery = "";
$viewQuery = "SELECT taxi_OrdNo, taxi_OrdPrice FROM TB_ORDER WHERE taxi_RIdx = :taxi_RIdx AND taxi_OrdMemId = :taxi_OrdMemId LIMIT 1; ";
$viewStmt = $DB_con->prepare($viewQuery);
$viewStmt->bindparam(":taxi_RIdx", $idx);
$viewStmt->bindparam(":taxi_OrdMemId", $memId);
$viewStmt->execute();
$num = $viewStmt->rowCount();

if ($num < 1) { //아닐경우
	$result = array("result" => false, "errorMsg" => "#1 : 요청 주문이 없습니다.");
} else {

	while ($row = $viewStmt->fetch(PDO::FETCH_ASSOC)) {
		$taxi_OrdNo = trim($row['taxi_OrdNo']);								// 주문번호
		$taxi_OrdPrice = trim($row['taxi_OrdPrice']);						// 쉐어링요금
	}
	// 토큰값 생성
	$access_token = get_Token_PayForm('https://api.iamport.kr/users/getToken', array("imp_key" => $imp_key, "imp_secret" => $imp_secret));

	if ($access_token != '') {
		//빌링키 발급 조회
		$Billing_Key_Chk = common_Form('https://api.iamport.kr/subscribe/customers', array("customer_uid" => $mem_Idx), $access_token);
		if ($Billing_Key_Chk != 0) {
			//빌링키가 발급된 경우 결제
			$res = common_Form('https://api.iamport.kr/subscribe/payments/again', array("customer_uid" => $mem_Idx, "merchant_uid" => $taxi_OrdNo, "amount" => $taxi_OrdPrice, "name" => $ordTitNm), $access_token);
			if ($res != 0) {
				$result = array("result" => false, "errorMsg" => "#4 : 결재오류가 있습니다.");
			} else {
				$result = array("result" => true);
			}
		} else {
			$result = array("result" => false, "errorMsg" => "#3 : 등록된 카드정보가 없습니다.");
		}
	} else {
		$result = array("result" => false, "errorMsg" => "#2 : 인증토큰 발급오류가 있습니다.");
	}
}


dbClose($DB_con);
$viewStmt = null;

echo str_replace('\\/', '/', json_encode($result, JSON_UNESCAPED_UNICODE));
