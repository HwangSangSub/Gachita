<?php
/*======================================================================================================================

* 프로그램				:  노선취소처리
* 페이지 설명			:  노선취소처리
* 파일명              :  taxiSharingCancleProc.php

========================================================================================================================*/
include "../../udev/lib/common.php";
include "../../lib/functionDB.php";  //공통 db함수
include "../../order/lib/TPAY.LIB.php";  //공통 db함수
include "../../order/lib/tpay_proc.php"; // 아임포트 함수
//require_once dirname(__FILE__).'/TPAY.LIB.php';  //tpay lib

$idx = trim($idx);					//고유번호 (노선번호)

$DB_con = db1();

$regDate = DU_TIME_YMDHIS;  //시간등록

//노선확인
$chkQuery = "SELECT idx, taxi_State FROM TB_STAXISHARING WHERE idx = :idx LIMIT 1 ";
//echo $chkQuery."<BR>";
//exit;
$chkStmt = $DB_con->prepare($chkQuery);
$chkStmt->bindparam(":idx", $idx);
$chkStmt->execute();
$chkNum = $chkStmt->rowCount();
//echo $mapNum."<BR>";

if ($chkNum < 1) { //아닐경우
	$result['success']	= false;
	$result['Msg']	= "해당노선은 존재하지 않습니다. 다시 확인 후 진행해주세요.";
	//$result = array("result" => "error","errorMsg" => "해당노선의 주문건이 없습니다." );
} else {
	while ($chkRow = $chkStmt->fetch(PDO::FETCH_ASSOC)) {
		$taxiSIdx = $chkRow['idx'];					// 생성노선번호
		$taxiState = $chkRow['taxi_State'];			// 생성노선상태값
	}
	//이동중인 경우 카드결제 확인 후 카드결제취소하기위해 개별처리
	if ($taxiState == "6") {
		//거래취소를 위한 주문조회
		$orderQuery = "";
		$orderQuery = "SELECT taxi_SIdx, taxi_RIdx, taxi_OrdNo, taxi_OrdType, taxi_OrdSMemId, taxi_OrdMemId, taxi_OSMemId, taxi_OMemId FROM TB_ORDER WHERE taxi_SIdx = :taxi_SIdx ; ";
		echo $orderQuery . "<BR>";
		exit;
		$orderStmt = $DB_con->prepare($orderQuery);
		$orderStmt->bindparam(":taxi_SIdx", $idx);
		$orderStmt->execute();
		$orderNum = $orderStmt->rowCount();
		//echo $mapNum."<BR>";

		if ($orderNum < 1) { //아닐경우
			$result['success']	= false;
			$result['Msg']	= "해당노선의 주문건이 없습니다.";
			//$result = array("result" => "error","errorMsg" => "해당노선의 주문건이 없습니다." );
		} else {
			while ($orderRow = $orderStmt->fetch(PDO::FETCH_ASSOC)) {
				$taxiSIdx = trim($orderRow['taxi_SIdx']);					// 메이커 고유번호
				$taxiRIdx = trim($orderRow['taxi_RIdx']);					// 투게더 고유번호
				$taxiOrdNo = trim($orderRow['taxi_OrdNo']);					// 노선주문번호
				$taxi_OrdPoint = trim($orderRow['taxi_OrdPrice']);			// 양도포인트
				$taxiOrdSMemId = trim($orderRow['taxi_OrdSMemId']);			// 메이커 아이디
				$taxiOrdMemId = trim($orderRow['taxi_OrdMemId']);			// 투게더 아이디
				$taxiOSMemId = trim($orderRow['taxi_OSMemId']);			    // 메이커 고유아이디
				$taxiOMemId = trim($orderRow['taxi_OMemId']);		    	// 투게더 고유아이디
				$taxi_OrdType = trim($orderRow['taxi_OrdType']);			// 결제타입
			}
		}
		if ($taxi_OrdType == "1") {
			$access_token = get_Token_PayForm('https://api.iamport.kr/users/getToken', array("imp_key" => $imp_key, "imp_secret" => $imp_secret));

			if ($access_token == '') {
				$result['success']	= false;
				$result['Msg']	= "#2. " . $accesstoken_message;
				//$result = array("result" => "error","errorMsg" => "#2. ".$accesstoken_message );
			} else if ($access_token != '') {
				$order_cancle = common_Form('https://api.iamport.kr/payments/cancel', array("merchant_uid" => $taxiOrdNo, "reason" => "관리자페이지 내 취소처리"), $access_token);
				$code = $order_cancle['code'];										//성공여부
				$message = $order_cancle['message'];								//메세지
				$status = $order_cancle['response']['status'];						//결제상태
				if ($code == 1) {
					$result['success']	= false;
					$result['Msg']	= "#3. " . $message;
					//$result = array("result" => "error", "errorMsg" => "#3. ".$message);
				} else if ($code == 0 && $status != 'cancelled') {
					$result['success']	= false;
					$result['Msg']	= "#4. " . $fail_reason;
					//$result = array("result" => "error", "errorMsg" => "#4. ".$fail_reason);
				} else if ($code == 0 && $status == 'cancelled') {		//if ($taxi_OrdState == "1") { //결제완료
					//메이커 취소처리
					$upMQquery11 = "UPDATE TB_STAXISHARING SET taxi_State = '8', taxi_MState = :taxiState, reg_CDate = NOW() WHERE idx = :idx;";
					$upMStmt11 = $DB_con->prepare($upMQquery11);
					$upMStmt11->bindparam(":idx", $taxiSIdx);
					$upMStmt11->bindparam(":taxiState", $taxiState);
					$upMStmt11->execute();

					//투게더 취소처리
					$upMQquery22 = "UPDATE TB_RTAXISHARING SET taxi_RState = '8' WHERE taxi_SIdx = :taxi_SIdx;";
					$upMStmt22 = $DB_con->prepare($upMQquery22);
					$upMStmt22->bindparam(":taxi_SIdx", $taxiSIdx);
					$upMStmt22->execute();

					//취소사유메모 기록
					$upMQquery33 = "UPDATE TB_ORDER SET taix_OrdCancle = '관리자로 인한 취소', taxi_OrdState ='3' WHERE taxi_OrdNo = :taxi_OrdNo;";
					$upMStmt33 = $DB_con->prepare($upMQquery33);
					$upMStmt33->bindparam(":taxi_OrdNo", $taxi_OrdNo);
					$upMStmt33->execute();

					$upMQquery44 = "UPDATE TB_RTAXISHARING_INFO SET reg_CYDate = now(), taxi_RMemo = '관리자로 인한 취소', taxi_MState = :taxi_MState WHERE taxi_SIdx = :taxi_SIdx;";
					$upMStmt44 = $DB_con->prepare($upMQquery44);
					$upMStmt44->bindparam(":taxi_SIdx", $taxiSIdx);
					$upMStmt44->bindparam(":taxi_MState", $taxiState);
					$upMStmt44->execute();


					//푸시 전송 등록 여부 체크(생성자)
					$cntPushQuery = "";
					$cntPushQuery = "SELECT count(idx) AS num FROM TB_SHARING_PUSH WHERE taxi_Idx = :taxi_Idx AND taxi_Type = '998' AND taxi_SMemId = :taxi_SMemId AND taxi_MemId = :taxi_MemId ";
					$cntPushStmt = $DB_con->prepare($cntPushQuery);
					$cntPushStmt->bindParam("taxi_Idx", $taxiSIdx);
					$cntPushStmt->bindParam("taxi_SMemId", $taxiOSMemId);
					$cntPushStmt->bindParam("taxi_MemId", $taxiOrdSMemId);
					$cntPushStmt->execute();
					$cntPushRow = $cntPushStmt->fetch(PDO::FETCH_ASSOC);
					$totalPushCnt = $cntPushRow['num'];

					if ($totalPushCnt == "") {
						$totalPushCnt = "0";
					} else {
						$totalPushCnt =  $totalPushCnt;
					}

					//푸시 전송 내역 저장
					if ($totalPushCnt < 1) {

						//푸시 저장
						$insPushQuery = "INSERT INTO TB_SHARING_PUSH (taxi_Idx, taxi_Type, taxi_SMemId, taxi_MemId, reg_Date)
										 VALUES (:taxi_Idx, '998', :taxi_SMemId, :taxi_MemId, :reg_Date)";
						$stmtPush = $DB_con->prepare($insPushQuery);
						$stmtPush->bindParam("taxi_Idx", $taxiSIdx);
						$stmtPush->bindParam("taxi_SMemId", $taxiOSMemId);
						$stmtPush->bindParam("taxi_MemId", $taxiOrdSMemId);
						$stmtPush->bindParam("reg_Date", $regDate);
						$stmtPush->execute();


						//메이커 푸시
						$mem_NToken = memMatchTokenInfo($taxiOSMemId);
						$ntitle = "";
						$nmsg = "관리자로 인해 노선이 취소되었습니다.";
						$chkState = "998";  //거래취소

						foreach ($mem_NToken as $k => $v) {
							$ntokens = $mem_NToken[$k];

							//알림할 내용들을 취합해서 $data에 모두 담는다. 프로젝트 의도에 따라 다른게 더 있을 수 있다.
							$ninputData = array("title" => $ntitle, "msg" => $nmsg, "state" => $chkState);

							//마지막에 알림을 보내는 함수를 실행하고 그 결과를 화면에 출력해 준다.
							$nresult = send_Push($ntokens, $ninputData);
						}
					}
					//푸시 전송 등록 여부 체크(요청자)
					$cntPushQuery2 = "";
					$cntPushQuery2 = "SELECT count(idx) AS num FROM TB_SHARING_PUSH WHERE taxi_Idx = :taxi_Idx AND taxi_Type = '998' AND taxi_SMemId = :taxi_SMemId AND taxi_MemId = :taxi_MemId ";
					$cntPushStmt2 = $DB_con->prepare($cntPushQuery2);
					$cntPushStmt2->bindParam("taxi_Idx", $taxiRIdx);
					$cntPushStmt2->bindParam("taxi_SMemId", $taxiOMemId);
					$cntPushStmt2->bindParam("taxi_MemId", $taxiOrdMemId);
					$cntPushStmt2->execute();
					$cntPushRow2 = $cntPushStmt2->fetch(PDO::FETCH_ASSOC);
					$totalPushCnt2 = $cntPushRow2['num'];

					if ($totalPushCnt2 == "") {
						$totalPushCnt2 = "0";
					} else {
						$totalPushCnt2 =  $totalPushCnt2;
					}

					//푸시 전송 내역 저장
					if ($totalPushCnt2 < 1) {

						//푸시 저장
						$insPushQuery2 = "INSERT INTO TB_SHARING_PUSH (taxi_Idx, taxi_Type, taxi_SMemId, taxi_MemId, reg_Date)
										 VALUES (:taxi_Idx, '998', :taxi_SMemId, :taxi_MemId, :reg_Date)";
						$stmtPush2 = $DB_con->prepare($insPushQuery2);
						$stmtPush2->bindParam("taxi_Idx", $taxiRIdx);
						$stmtPush2->bindParam("taxi_SMemId", $taxiOMemId);
						$stmtPush2->bindParam("taxi_MemId", $taxiOrdMemId);
						$stmtPush2->bindParam("reg_Date", $regDate);
						$stmtPush2->execute();

						//투게더 푸시
						$mem_RToken = memMatchTokenInfo($taxiOMemId);
						$rtitle = "함께타고 비용을 나누는 똑똑한 합승앱, 가치타!";
						$rmsg = "관리자로 인해 노선이 취소되었습니다.";
						$rchkState = "998";  //거래취소

						foreach ($mem_RToken as $k2 => $v2) {
							$rtokens = $mem_RToken[$k2];

							//알림할 내용들을 취합해서 $data에 모두 담는다. 프로젝트 의도에 따라 다른게 더 있을 수 있다.
							$rinputData = array("title" => $rtitle, "msg" => $rmsg, "state" => $rchkState);

							//마지막에 알림을 보내는 함수를 실행하고 그 결과를 화면에 출력해 준다.
							$rResult = send_Push($rtokens, $ninputData);
						}
					}
					$result['success']	= true;
					$result['Msg']	= "해당노선을 취소처리 완료하였습니다.";
				}
			}
		} else {
			//메이커 취소처리
			$upMQquery11 = "UPDATE TB_STAXISHARING SET taxi_State = '8', taxi_MState = :taxiState, reg_CDate = NOW() WHERE idx = :idx;";
			$upMStmt11 = $DB_con->prepare($upMQquery11);
			$upMStmt11->bindparam(":idx", $taxiSIdx);
			$upMStmt11->bindparam(":taxiState", $taxiState);
			$upMStmt11->execute();

			//투게더 취소처리
			$upMQquery22 = "UPDATE TB_RTAXISHARING SET taxi_RState = '8' WHERE taxi_SIdx = :taxi_SIdx;";
			$upMStmt22 = $DB_con->prepare($upMQquery22);
			$upMStmt22->bindparam(":taxi_SIdx", $taxiSIdx);
			$upMStmt22->execute();

			//취소사유메모 기록(주문)
			$upMQquery33 = "UPDATE TB_ORDER SET taix_OrdCancle = '관리자로 인한 취소', taxi_OrdState ='3' WHERE taxi_OrdNo = :taxi_OrdNo;";
			$upMStmt33 = $DB_con->prepare($upMQquery33);
			$upMStmt33->bindparam(":taxi_OrdNo", $taxi_OrdNo);
			$upMStmt33->execute();

			$upMQquery44 = "UPDATE TB_RTAXISHARING_INFO SET reg_CDate = now(), taxi_RMemo = '관리자로 인한 취소', taxi_MState = :taxi_MState WHERE taxi_SIdx = :taxi_SIdx;";
			$upMStmt44 = $DB_con->prepare($upMQquery44);
			$upMStmt44->bindparam(":taxi_SIdx", $taxiSIdx);
			$upMStmt44->bindparam(":taxi_MState", $taxiState);
			$upMStmt44->execute();

			//푸시 전송 등록 여부 체크(생성자)
			$cntPushQuery = "";
			$cntPushQuery = "SELECT count(idx) AS num FROM TB_SHARING_PUSH WHERE taxi_Idx = :taxi_Idx AND taxi_Type = '998' AND taxi_SMemId = :taxi_SMemId AND taxi_MemId = :taxi_MemId;";
			$cntPushStmt = $DB_con->prepare($cntPushQuery);
			$cntPushStmt->bindParam("taxi_Idx", $taxiSIdx);
			$cntPushStmt->bindParam("taxi_SMemId", $taxiOSMemId);
			$cntPushStmt->bindParam("taxi_MemId", $taxiOrdSMemId);
			$cntPushStmt->execute();
			$cntPushRow = $cntPushStmt->fetch(PDO::FETCH_ASSOC);
			$totalPushCnt = $cntPushRow['num'];

			if ($totalPushCnt == "") {
				$totalPushCnt = "0";
			} else {
				$totalPushCnt =  $totalPushCnt;
			}

			//푸시 전송 내역 저장
			if ($totalPushCnt < 1) {

				//푸시 저장
				$insPushQuery = "INSERT INTO TB_SHARING_PUSH (taxi_Idx, taxi_Type, taxi_SMemId, taxi_MemId, reg_Date)
								 VALUES (:taxi_Idx, '998', :taxi_SMemId, :taxi_MemId, :reg_Date);";
				$stmtPush = $DB_con->prepare($insPushQuery);
				$stmtPush->bindParam("taxi_Idx", $taxiSIdx);
				$stmtPush->bindParam("taxi_SMemId", $taxiOSMemId);
				$stmtPush->bindParam("taxi_MemId", $taxiOrdSMemId);
				$stmtPush->bindParam("reg_Date", $regDate);
				$stmtPush->execute();

				//투게더 푸시
				$mem_NToken = memMatchTokenInfo($taxiOSMemId);
				$ntitle = "";
				$nmsg = "관리자로 인해 노선이 취소되었습니다.";
				$chkState = "998";  //거래취소

				foreach ($mem_NToken as $k => $v) {
					$ntokens = $mem_NToken[$k];

					//알림할 내용들을 취합해서 $data에 모두 담는다. 프로젝트 의도에 따라 다른게 더 있을 수 있다.
					$ninputData = array("title" => $ntitle, "msg" => $nmsg, "state" => $chkState);

					//마지막에 알림을 보내는 함수를 실행하고 그 결과를 화면에 출력해 준다.
					$nresult = send_Push($ntokens, $ninputData);
				}
			}

			//푸시 전송 등록 여부 체크(요청자)
			$cntPushQuery2 = "";
			$cntPushQuery2 = "SELECT count(idx) AS num FROM TB_SHARING_PUSH WHERE taxi_Idx = :taxi_Idx AND taxi_Type = '998' AND taxi_SMemId = :taxi_SMemId AND taxi_MemId = :taxi_MemId;";
			$cntPushStmt2 = $DB_con->prepare($cntPushQuery2);
			$cntPushStmt2->bindParam("taxi_Idx", $taxiRIdx);
			$cntPushStmt2->bindParam("taxi_SMemId", $taxiOMemId);
			$cntPushStmt2->bindParam("taxi_MemId", $taxiOrdMemId);
			$cntPushStmt2->execute();
			$cntPushRow2 = $cntPushStmt2->fetch(PDO::FETCH_ASSOC);
			$totalPushCnt2 = $cntPushRow2['num'];

			if ($totalPushCnt2 == "") {
				$totalPushCnt2 = "0";
			} else {
				$totalPushCnt2 =  $totalPushCnt2;
			}

			//푸시 전송 내역 저장
			if ($totalPushCnt2 < 1) {

				//푸시 저장
				$insPushQuery2 = "INSERT INTO TB_SHARING_PUSH (taxi_Idx, taxi_Type, taxi_SMemId, taxi_MemId, reg_Date)
								 VALUES (:taxi_Idx, '998', :taxi_SMemId, :taxi_MemId, :reg_Date);";
				$stmtPush2 = $DB_con->prepare($insPushQuery2);
				$stmtPush2->bindParam("taxi_Idx", $taxiRIdx);
				$stmtPush2->bindParam("taxi_SMemId", $taxiOMemId);
				$stmtPush2->bindParam("taxi_MemId", $taxiOrdMemId);
				$stmtPush2->bindParam("reg_Date", $regDate);
				$stmtPush2->execute();

				//투게더 푸시
				$mem_RToken = memMatchTokenInfo($taxiOMemId);
				$rtitle = "";
				$rmsg = "관리자로 인해 노선이 취소되었습니다.";
				$rchkState = "998";  //거래완료

				foreach ($mem_RToken as $k2 => $v2) {
					$rtokens = $mem_RToken[$k2];

					//알림할 내용들을 취합해서 $data에 모두 담는다. 프로젝트 의도에 따라 다른게 더 있을 수 있다.
					$rinputData = array("title" => $rtitle, "msg" => $rmsg, "state" => $rchkState);

					//마지막에 알림을 보내는 함수를 실행하고 그 결과를 화면에 출력해 준다.
					$rResult = send_Push($rtokens, $ninputData);
				}
			}
			$result['success']	= true;
			$result['Msg']	= "해당노선을 취소처리 완료하였습니다.";
		}
		//그외 상태에서는 그냥 취소
	} else {
		//해당노선의 요청건을 확인하여 처리
		$rtaxiQuery = "";
		$rtaxiQuery = "SELECT idx, taxi_SMemId, taxi_RSMemId, taxi_MemId, taxi_RMemId FROM TB_RTAXISHARING WHERE taxi_SIdx = :taxi_SIdx;";
		//echo $orderQuery."<BR>";
		//exit;
		$rtaxiStmt = $DB_con->prepare($rtaxiQuery);
		$rtaxiStmt->bindparam(":taxi_SIdx", $idx);
		$rtaxiStmt->execute();
		$rtaxiNum = $rtaxiStmt->rowCount();
		//echo $mapNum."<BR>";
		if ($rtaxiNum < 1) { //아닐경우
			$staxiQuery = "";
			$staxiQuery = "SELECT taxi_SMemId, taxi_MemId, taxi_State FROM TB_STAXISHARING WHERE idx = :idx;";
			//echo $orderQuery."<BR>";
			//exit;
			$staxiStmt = $DB_con->prepare($staxiQuery);
			$staxiStmt->bindparam(":idx", $idx);
			$staxiStmt->execute();
			while ($staxiRow = $staxiStmt->fetch(PDO::FETCH_ASSOC)) {
				$taxiSMemId = $staxiRow['taxi_SMemId'];		// 생성자고유아이디
				$taxiMemId = $staxiRow['taxi_MemId'];		// 생성자아이디
				$taxiState = $staxiRow['taxi_State'];		// 상태
				//메이커 취소처리
				$upMQquery111 = "UPDATE TB_STAXISHARING SET taxi_State = '8', taxi_MState = :taxiState, reg_CDate = NOW() WHERE idx = :idx;";
				$upMStmt111 = $DB_con->prepare($upMQquery111);
				$upMStmt111->bindparam(":idx", $idx);
				$upMStmt111->bindparam(":taxiState", $taxiState);
				$upMStmt111->execute();

				//푸시 전송 등록 여부 체크(생성자)
				$cntPushQuery = "";
				$cntPushQuery = "SELECT count(idx) AS num FROM TB_SHARING_PUSH WHERE taxi_Idx = :taxi_Idx AND taxi_Type = '998' AND taxi_SMemId = :taxi_SMemId AND taxi_MemId = :taxi_MemId;";
				$cntPushStmt = $DB_con->prepare($cntPushQuery);
				$cntPushStmt->bindParam("taxi_Idx", $idx);
				$cntPushStmt->bindParam("taxi_SMemId", $taxiSMemId);
				$cntPushStmt->bindParam("taxi_MemId", $taxiMemId);
				$cntPushStmt->execute();
				$cntPushRow = $cntPushStmt->fetch(PDO::FETCH_ASSOC);
				$totalPushCnt = $cntPushRow['num'];

				if ($totalPushCnt == "") {
					$totalPushCnt = "0";
				} else {
					$totalPushCnt =  $totalPushCnt;
				}

				//푸시 전송 내역 저장
				if ($totalPushCnt < 1) {

					//푸시 저장
					$insPushQuery = "INSERT INTO TB_SHARING_PUSH (taxi_Idx, taxi_Type, taxi_SMemId, taxi_MemId, reg_Date)
									 VALUES (:taxi_Idx, '998', :taxi_SMemId, :taxi_MemId, :reg_Date);";
					$stmtPush = $DB_con->prepare($insPushQuery);
					$stmtPush->bindParam("taxi_Idx", $idx);
					$stmtPush->bindParam("taxi_SMemId", $taxiSMemId);
					$stmtPush->bindParam("taxi_MemId", $taxiMemId);
					$stmtPush->bindParam("reg_Date", $regDate);
					$stmtPush->execute();

					//메이커 푸시
					$mem_NToken = memMatchTokenInfo($taxiSMemId);
					$ntitle = "";
					$nmsg = "관리자로 인해 노선이 취소되었습니다.";
					$chkState = "998";  //거래취소

					foreach ($mem_NToken as $k => $v) {
						$ntokens = $mem_NToken[$k];

						//알림할 내용들을 취합해서 $data에 모두 담는다. 프로젝트 의도에 따라 다른게 더 있을 수 있다.
						$ninputData = array("title" => $ntitle, "msg" => $nmsg, "state" => $chkState);

						//마지막에 알림을 보내는 함수를 실행하고 그 결과를 화면에 출력해 준다.
						$nresult = send_Push($ntokens, $ninputData);
					}
				}
			}
			$result['success']	= true;
			$result['Msg']	= "해당노선을 취소처리 완료하였습니다.";
		} else {
			while ($rtaxiRow = $rtaxiStmt->fetch(PDO::FETCH_ASSOC)) {
				$taxiRIdx = $rtaxiRow['idx'];		// 요청노선번호
				$taxiSMemId = $rtaxiRow['taxi_SMemId'];		// 생성자고유아이디
				$taxiMemId = $rtaxiRow['taxi_MemId'];		// 생성자아이디
				$taxiRSMemId = $rtaxiRow['taxi_RSMemId'];	// 요청자고유아이디
				$taxiRMemId = $rtaxiRow['taxi_RMemId'];		// 요청자아이디

				//메이커 취소처리
				$upMQquery111 = "UPDATE TB_STAXISHARING SET taxi_State = '8', taxi_MState = :taxiState, reg_CDate = NOW() WHERE idx = :idx;";
				$upMStmt111 = $DB_con->prepare($upMQquery111);
				$upMStmt111->bindparam(":idx", $idx);
				$upMStmt111->bindparam(":taxiState", $taxiState);
				$upMStmt111->execute();

				//투게더 취소처리
				$upMQquery222 = "UPDATE TB_RTAXISHARING SET taxi_RState = '8' WHERE taxi_SIdx = :taxi_SIdx;";
				$upMStmt222 = $DB_con->prepare($upMQquery222);
				$upMStmt222->bindparam(":taxi_SIdx", $idx);
				$upMStmt222->execute();

				$upMQquery444 = "UPDATE TB_RTAXISHARING_INFO SET reg_CDate = now(), taxi_RMemo = '관리자로 인한 취소', taxi_MState = :taxi_MState WHERE taxi_SIdx = :taxi_SIdx;";
				$upMStmt444 = $DB_con->prepare($upMQquery444);
				$upMStmt444->bindparam(":taxi_SIdx", $idx);
				$upMStmt444->bindparam(":taxi_MState", $taxiState);
				$upMStmt444->execute();

				//푸시 전송 등록 여부 체크(생성자)
				$cntPushQuery = "";
				$cntPushQuery = "SELECT count(idx) AS num FROM TB_SHARING_PUSH WHERE taxi_Idx = :taxi_Idx AND taxi_Type = '998' AND taxi_SMemId = :taxi_SMemId AND taxi_MemId = :taxi_MemId;";
				$cntPushStmt = $DB_con->prepare($cntPushQuery);
				$cntPushStmt->bindParam("taxi_Idx", $idx);
				$cntPushStmt->bindParam("taxi_SMemId", $taxiSMemId);
				$cntPushStmt->bindParam("taxi_MemId", $taxiMemId);
				$cntPushStmt->execute();
				$cntPushRow = $cntPushStmt->fetch(PDO::FETCH_ASSOC);
				$totalPushCnt = $cntPushRow['num'];

				if ($totalPushCnt == "") {
					$totalPushCnt = "0";
				} else {
					$totalPushCnt =  $totalPushCnt;
				}

				//푸시 전송 내역 저장
				if ($totalPushCnt < 1) {

					//푸시 저장
					$insPushQuery = "INSERT INTO TB_SHARING_PUSH (taxi_Idx, taxi_Type, taxi_SMemId, taxi_MemId, reg_Date)
									 VALUES (:taxi_Idx, '998', :taxi_SMemId, :taxi_MemId, :reg_Date);";
					$stmtPush = $DB_con->prepare($insPushQuery);
					$stmtPush->bindParam("taxi_Idx", $idx);
					$stmtPush->bindParam("taxi_SMemId", $taxiSMemId);
					$stmtPush->bindParam("taxi_MemId", $taxiMemId);
					$stmtPush->bindParam("reg_Date", $regDate);
					$stmtPush->execute();

					//메이커 푸시
					$mem_NToken = memMatchTokenInfo($taxiSMemId);
					$ntitle = "";
					$nmsg = "관리자로 인해 노선이 취소되었습니다.";
					$chkState = "998";  //거래취소

					foreach ($mem_NToken as $k => $v) {
						$ntokens = $mem_NToken[$k];
						//알림할 내용들을 취합해서 $data에 모두 담는다. 프로젝트 의도에 따라 다른게 더 있을 수 있다.
						$ninputData = array("title" => $ntitle, "msg" => $nmsg, "state" => $chkState);
						//마지막에 알림을 보내는 함수를 실행하고 그 결과를 화면에 출력해 준다.
						$nresult = send_Push($ntokens, $ninputData);
					}
				}
				//푸시 전송 등록 여부 체크(요청자)
				$cntPushQuery2 = "";
				$cntPushQuery2 = "SELECT count(idx) AS num FROM TB_SHARING_PUSH WHERE taxi_Idx = :taxi_Idx AND taxi_Type = '998' AND taxi_SMemId = :taxi_SMemId AND taxi_MemId = :taxi_MemId;";
				$cntPushStmt2 = $DB_con->prepare($cntPushQuery2);
				$cntPushStmt2->bindParam("taxi_Idx", $taxiRIdx);
				$cntPushStmt2->bindParam("taxi_SMemId", $taxiRSMemId);
				$cntPushStmt2->bindParam("taxi_MemId", $taxiRMemId);
				$cntPushStmt2->execute();
				$cntPushRow2 = $cntPushStmt2->fetch(PDO::FETCH_ASSOC);
				$totalPushCnt2 = $cntPushRow2['num'];

				if ($totalPushCnt2 == "") {
					$totalPushCnt2 = "0";
				} else {
					$totalPushCnt2 =  $totalPushCnt2;
				}

				//푸시 전송 내역 저장
				if ($totalPushCnt2 < 1) {

					//푸시 저장
					$insPushQuery2 = "INSERT INTO TB_SHARING_PUSH (taxi_Idx, taxi_Type, taxi_SMemId, taxi_MemId, reg_Date)
									 VALUES (:taxi_Idx, '998', :taxi_SMemId, :taxi_MemId, :reg_Date);";
					$stmtPush2 = $DB_con->prepare($insPushQuery2);
					$stmtPush2->bindParam("taxi_Idx", $taxiRIdx);
					$stmtPush2->bindParam("taxi_SMemId", $taxiRSMemId);
					$stmtPush2->bindParam("taxi_MemId", $taxiRMemId);
					$stmtPush2->bindParam("reg_Date", $regDate);
					$stmtPush2->execute();

					//투게더 푸시
					$mem_RToken = memMatchTokenInfo($taxiRSMemId);
					$rtitle = "";
					$rmsg = "관리자로 인해 노선이 취소되었습니다.";
					$rchkState = "998";  //거래취소

					foreach ($mem_RToken as $k2 => $v2) {
						$rtokens = $mem_RToken[$k2];

						//알림할 내용들을 취합해서 $data에 모두 담는다. 프로젝트 의도에 따라 다른게 더 있을 수 있다.
						$rinputData = array("title" => $rtitle, "msg" => $rmsg, "state" => $rchkState);

						//마지막에 알림을 보내는 함수를 실행하고 그 결과를 화면에 출력해 준다.
						$rResult = send_Push($rtokens, $ninputData);
					}
				}
			}
			$result['success']	= true;
			$result['Msg']	= "해당노선을 취소처리 완료하였습니다.";
		}
	}
}



dbClose($DB_con);
$chkStmt = null;
$orderStmt = null;
$upMStmt11 = null;
$upMStmt22 = null;
$upMStmt33 = null;
$upMStmt44 = null;
$upMStmt111 = null;
$upMStmt222 = null;
$upMStmt444 = null;
$nSidStmt = null;
$rSidStmt = null;
$rtaxiStmt = null;
$staxiStmt = null;
$cntPushStmt = null;
$stmtPush = null;
$cntPushStmt2 = null;
$stmtPush2 = null;

//echo str_replace('\\/', '/', json_encode($result, JSON_UNESCAPED_UNICODE));
echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
