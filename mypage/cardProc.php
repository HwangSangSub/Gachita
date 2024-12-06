<?
/*======================================================================================================================

* 프로그램				:  카드 정보 (등록, 수정, 삭제)
* 페이지 설명			:  카드 정보 (등록, 수정, 삭제)
* 파일명              :  cardProc.php

========================================================================================================================*/
include "../lib/common.php";
include "../lib/functionDB.php";  //공통 db함수
include "../order/lib/TPAY.LIB.php";  //공통 db함수
include "../order/lib/tpay_proc.php"; // 아임포트 함수
include "../lib/card_password.php"; //카드정보 암호화
$mem_Id = trim($memId);
$mem_Idx = memIdxInfo($mem_Id);   //회원 주아이디 (상점고유아이디로 사용)

$idx = trim($idx);				// 카드삭제 시 필요한 카드고유번호

//등록 일 경우 : reg, 삭제일 경우 : del 수정은 없어야 함. 사내DB에서는 수정가능하나 빌링키 발급을 위해서는 삭제 후 재 발급 방식으로 처리해야 함.
if ($mode == "") {
	$mode = "reg";	  //등록
} else {
	$mode = trim($mode);
}

$chk_cardNum4 = trim($chk_cardNum4);	// 수정 삭제의 경우 필요한 값

$cardNumber = $cardNum1 . "-" . $cardNum2 . "-" . $cardNum3 . "-" . $cardNum4;
$card_Number = trim($cardNumber);	//카드번호
$card_Number2 = base64_encode(openssl_encrypt($card_Number, 'aes-256-cbc', $password, OPENSSL_RAW_DATA, $iv)); //카드번호 암호화

$card_Month = trim($cardMonth);		//유효기간 월
$card_Month2 = base64_encode(openssl_encrypt($card_Month, 'aes-256-cbc', $password, OPENSSL_RAW_DATA, $iv)); //카드비밀번호 암호화
$card_Year = trim($cardYear);		//유효기간 년도
$card_Year2 = base64_encode(openssl_encrypt($card_Year, 'aes-256-cbc', $password, OPENSSL_RAW_DATA, $iv)); //카드비밀번호 암호화
$expiry = trim($card_Year . "-" . $card_Month);

$reg_Date = DU_TIME_YMDHIS;			//등록일

$card_Birth = trim($cardBirth);		//생년월일
$card_Birth2 = base64_encode(openssl_encrypt($card_Birth, 'aes-256-cbc', $password, OPENSSL_RAW_DATA, $iv)); //생년월일 암호화

$cardPwd = trim($cardPwd2);			//카드비밀번호 앞 2자리
$card_Pwd = base64_encode(openssl_encrypt($cardPwd, 'aes-256-cbc', $password, OPENSSL_RAW_DATA, $iv)); //카드비밀번호 앞 2자리 암호화
$card_Mem_Id = trim($mem_Id);		//사용자아이디
$card_Name = trim($cardName);       //카드명
$card_NickName = trim($cardNickName);       //카드별칭
if ($card_NickName == "") {
	$card_NickName = "";
}
if ($mem_Id != "" && $mode != "") {  //아이디랑 등록,수정 삭제 여부가 경우

	$DB_con = db1();
	if ($mode == "reg") {
		if ($card_Number == '' && $expiry != '' && $card_Birth != '' && $cardPwd != '') {
			$result = array("result" => false, "errorMsg" => "유효하지 않은 카드 번호입니다. 확인 후 다시 입력하시기 바랍니다.");
		} else if ($card_Number != '' && $expiry == '' && $card_Birth != '' && $cardPwd != '') {
			$result = array("result" => false, "errorMsg" => "유효기간이 잘못되었습니다. 다시입력 해주세요.");
		} else if ($card_Number != '' && $expiry != '' && $card_Birth == '' && $cardPwd != '') {
			$result = array("result" => false, "errorMsg" => "생년월일이 잘못되었습니다. 다시입력 해주세요.");
		} else if ($card_Number != '' && $expiry != '' && $card_Birth != '' && $cardPwd == '') {
			$result = array("result" => false, "errorMsg" => "비밀번호 앞 2자리가 잘못되었습니다. 다시입력 해주세요.");
		} else {
			//카드번호로 동일한 카드 등록여부 확인
			$memQuery = "SELECT idx from TB_PAYMENT_CARD WHERE card_Mem_Idx = :card_Mem_Idx ";
			$stmt = $DB_con->prepare($memQuery);
			$stmt->bindParam("card_Mem_Idx", $mem_Idx);
			$stmt->execute();
			$num = $stmt->rowCount();
			$card_use_bit = 0;
			if ($num > 4) { //있는 경우
				$result = array("result" => false, "errorMsg" => "결제카드는 최대 4개까지 등록가능합니다. 등록된 카드를 삭제 후 재등록 해주세요.");
			} else {

				//회원카드가 한개도 없는지 확인하기.
				$cardQuery = "SELECT card_Name, card_Number from TB_PAYMENT_CARD WHERE card_Mem_Idx = :card_Mem_Idx ";
				$cardstmt = $DB_con->prepare($cardQuery);
				$cardstmt->bindParam("card_Mem_Idx", $mem_Idx);
				$cardstmt->execute();
				while ($cardRow = $cardstmt->fetch(PDO::FETCH_ASSOC)) {
					$chk_card_Name = $cardRow['card_Name'];						// 카드명
					$chk_card_Number = $cardRow['card_Number'];						// 카드번호
					$chkcardNumber = openssl_decrypt(base64_decode($chk_card_Number), 'aes-256-cbc', $password, OPENSSL_RAW_DATA, $iv);
					if ($card_Number == $chkcardNumber && $card_Name == $chk_card_Name) {
						$card_use_bit = 1;
					}
				}
				if ($card_use_bit == 1) {
					$result = array("result" => false, "errorMsg" => "이미 동일한 카드가 있습니다. 확인 후 다시 등록해주세요.");
				} else {
					//토큰 발급하기
					$access_token = get_Token_PayForm('https://api.iamport.kr/users/getToken', array("imp_key" => $imp_key, "imp_secret" => $imp_secret));
					//빌링키 발급하기
					$Billing_Key = common_Form('https://api.iamport.kr/subscribe/customers/' . $mem_Idx . $cardNum4, array("card_number" => $card_Number, "expiry" => $expiry, "birth" => $card_Birth, "pwd_2digit" => $cardPwd), $access_token);
					$BillingKey_code = $Billing_Key['code'];							//성공여부
					if ($BillingKey_code != 0) { //빌링키 발급 실패일경우 
						if ($Billing_Key['message'] != "") {
							$error_Msg = $Billing_Key['message'];
						} else {
							$error_Msg = $Billing_Key['message'];
						}
						$result = array("result" => false, "errorMsg" => (string)$error_Msg);
					} else {
						//빌링키 발급 성공일 경우 DB에 해당정보 등록
						$insBilQuery = "INSERT INTO TB_PAYMENT_CARD (card_Mem_Idx, card_Mem_Id, card_NickName, card_Name, card_Number4, card_Number, card_Month, card_Year, card_Birth, card_Pwd2, reg_Date) VALUES (:card_Mem_Idx, :card_Mem_Id, :card_NickName, :card_Name, :card_Number4, :card_Number, :card_Month, :card_Year, :card_Birth, :card_Pwd2, :reg_Date)";
						$stmt = $DB_con->prepare($insBilQuery);
						$stmt->bindParam("card_Mem_Idx", $mem_Idx);
						$stmt->bindParam("card_Mem_Id", $card_Mem_Id);
						$stmt->bindParam("card_NickName", $card_NickName);
						$stmt->bindParam("card_Name", $card_Name);
						$stmt->bindParam("card_Number4", $cardNum4);
						$stmt->bindParam("card_Number", $card_Number2);
						$stmt->bindParam("card_Month", $card_Month2);
						$stmt->bindParam("card_Year", $card_Year2);
						$stmt->bindParam("card_Birth", $card_Birth2);
						$stmt->bindParam("card_Pwd2", $card_Pwd);
						$stmt->bindParam("reg_Date", $reg_Date);
						$stmt->execute();
						$DB_con->lastInsertId();

						$cIdx = $DB_con->lastInsertId();  //저장된 idx 값 

						//회원기타정보에 카드등록여부 수정
						$UpCardQuery = "UPDATE TB_MEMBERS_ETC SET mem_Card = 1 WHERE mem_Idx = :mem_Idx ";
						$upMstmt = $DB_con->prepare($UpCardQuery);
						$upMstmt->bindParam("mem_Idx", $mem_Idx);
						$upMstmt->execute();

						$result = array("result" => true, "idx" => (int)$cIdx);
					}
				}
			}
		}
	} else if ($mode == "del") {
		$chkRQuery = "SELECT idx FROM TB_RTAXISHARING WHERE taxi_RMemIdx = :card_Mem_Idx AND taxi_RState NOT IN ( '7', '8', '9', '10') ORDER BY idx desc";
		$chkRStmt = $DB_con->prepare($chkRQuery);
		$chkRStmt->bindparam(":card_Mem_Idx", $mem_Idx);
		$chkRStmt->execute();
		$chkRNum = $chkRStmt->rowCount();

		if ($chkRNum < 1) {
			//회원카드가 한개도 없는지 확인하기.
			$cardQuery = "SELECT card_Number4 from TB_PAYMENT_CARD WHERE idx = :idx ";
			$cardstmt = $DB_con->prepare($cardQuery);
			$cardstmt->bindParam("idx", $idx);
			$cardstmt->execute();
			$cardRow = $cardstmt->fetch(PDO::FETCH_ASSOC);
			$card_Number4 = $cardRow['card_Number4'];            // 카드번호

			//토큰 발급하기
			$access_token = get_Token_PayForm('https://api.iamport.kr/users/getToken', array("imp_key" => $imp_key, "imp_secret" => $imp_secret));
			//빌링키 삭제하기
			$Del_Billing_Key = Del_Billing_Key('https://api.iamport.kr/subscribe/customers/' . $mem_Idx . $card_Number4, array("customer_uid" => $mem_Idx . $card_Number4), $access_token);
			if ($Del_Billing_Key != 0) { //빌링키 삭제 실패인 경우 
				$result = array("result" => false, "errorMsg" => "카드삭제에 실패하였습니다. 관리자에게 문의해주세요.");
			} else {
				//빌링키 삭제 성공일 경우 - DB에서 삭제
				$delQquery = "DELETE FROM TB_PAYMENT_CARD WHERE  idx = :idx  LIMIT 1";
				$delStmt = $DB_con->prepare($delQquery);
				$delStmt->bindParam("idx", $idx);
				$delStmt->execute();

				//회원카드가 한개도 없는지 확인하기.
				$memQuery = "SELECT idx from TB_PAYMENT_CARD WHERE card_Mem_Idx = :card_Mem_Idx ";
				$stmt = $DB_con->prepare($memQuery);
				$stmt->bindParam("card_Mem_Idx", $mem_Idx);
				$stmt->execute();
				$num = $stmt->rowCount();

				if ((int)$num < 1) {
					//회원기타정보에 카드등록여부 수정
					$UpCardQuery = "UPDATE TB_MEMBERS_ETC SET mem_Card = 0 WHERE mem_Idx = :mem_Idx ";
					$upMstmt = $DB_con->prepare($UpCardQuery);
					$upMstmt->bindParam("mem_Idx", $mem_Idx);
					$upMstmt->execute();
				}
				$result = array("result" => true);
			}
		} else {
			$result = array("result" => false, "errorMsg" => "신청하신 노선을 취소하시고 삭제해주세요.");
		}
	}
	dbClose($DB_con);
	$stmt = null;
	$upStmt = null;
	$delStmt = null;
} else {
	$result = array("result" => false);
}

echo str_replace('\\/', '/', json_encode($result, JSON_UNESCAPED_UNICODE));
