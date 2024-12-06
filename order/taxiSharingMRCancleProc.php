<?

/*======================================================================================================================

* 프로그램			: 매칭 생성자, 요청자 이동 중 취소 상태 접수 진행 후 상대방 상태 접수 처리 화면
* 페이지 설명		: 매칭 생성자, 요청자 이동 중 취소 상태 접수 진행 후 상대방 상태 접수 처리 화면
* 파일명                 : taxiSharingMRCancleProc.php

========================================================================================================================*/

include "../lib/common.php";
include "../lib/functionDB.php";  //공통 db함수
include "./lib/tpay_proc.php"; // 아임포트 함수

$chkIdx = trim($chkIdx);	   // 취소신청 고유번호
$part = trim($part);           // 구분 (2 : 거래 취소는 동일하나 다른 사유입니다, 4: 동의합니다)
$bContent = trim($bContent);   //취소 사유 내용
if ($chkIdx != "" && $part != "") {  //취소신청 고유번호, 구분값이  있을 경우

	$DB_con = db1();

	$regDate = DU_TIME_YMDHIS;  //시간등록

	$cancleCQuery = "SELECT idx FROM TB_CANCLE_REASON WHERE cancle_Idx = :cancle_Idx ORDER BY idx DESC LIMIT 1;";
	$cancleCStmt = $DB_con->prepare($cancleCQuery);
	$cancleCStmt->bindparam(":cancle_Idx", $chkIdx);
	$cancleCStmt->execute();
	while ($cancleCRow = $cancleCStmt->fetch(PDO::FETCH_ASSOC)) {
		$last_CancleIdx = $cancleCRow['idx'];     // 해당노선의 취소사유 테이블 고유번호 조회 (최근등록) ==> 한개의 노선에서 취소요청은 동시에 진행되지 않아 처리함
	}

	/*사유기록*/
	$cancleUQuery = "
		UPDATE TB_CANCLE_REASON SET cancle_CanRChk = 'N', cancle_CRPart = :cancle_CRPart, cancle_CMemo = :cancle_CMemo WHERE idx = :idx;";
	$cancleUStmt = $DB_con->prepare($cancleUQuery);
	$cancleUStmt->bindparam(":idx", $last_CancleIdx);
	$cancleUStmt->bindparam(":cancle_CRPart", $part);
	$cancleUStmt->bindparam(":cancle_CMemo", $bContent);
	$cancleUStmt->execute();

	//회원 정보 보여줌
	$viewQuery = "SELECT taxi_SIdx, taxi_MemId, taxi_MemIdx, taxi_RIdx, taxi_RMemId, taxi_RMemIdx, taxi_MType, taxi_CPart FROM TB_SMATCH_STATE WHERE idx = :idx AND taxi_CanChk = 'Y' LIMIT 1 ";
	// echo $viewQuery."<BR>";
	// exit;
	$viewStmt = $DB_con->prepare($viewQuery);
	$viewStmt->bindparam(":idx", $chkIdx);
	$viewStmt->execute();
	$num = $viewStmt->rowCount();
	//echo $num."<BR>";
	if ($num < 1) { //아닐경우
		$result = array("result" => false, "errorMsg" => "취소요청건이 없습니다. 확인 후 다시 시도해주세요.");
		echo str_replace('\\/', '/', json_encode($result, JSON_UNESCAPED_UNICODE));
		exit;
	} else {
		while ($row = $viewStmt->fetch(PDO::FETCH_ASSOC)) {
			$taxiSIdx =  trim($row['taxi_SIdx']);          // 생성자 고유번호
			$taxiMemId =  trim($row['taxi_MemId']);        // 생성자 아이디
			$taxiMemIdx =  trim($row['taxi_MemIdx']);        // 생성자 아이디
			$taxiRIdx =  trim($row['taxi_RIdx']);          // 요청자 고유번호
			$taxiRMemId =  trim($row['taxi_RMemId']);      // 요청자 아이디
			$taxiRMemIdx =  trim($row['taxi_RMemIdx']);      // 요청자 아이디
			$taxiMType = trim($row['taxi_MType']);         // 생성자 :p, 요청자 : c
			$taxiCPart = trim($row['taxi_CPart']);         // 취소 사유
		}

		if ($taxiMType == "p") { //취소 요청자가 생성자일 경우

			if ($taxiCPart == "3") { //상대방 사유로 인한 취소
				$taxiPTit = "투게더";
				$taxiPhkNm = "본인의 불가피한 사유에 의한 취소";
				$taxiCSIdx = $taxiRIdx;       //매칭요청 고유번호
				$taxiCMemId = $taxiRMemId;     //매칭요청 아이디
				$taxiCMemIdx = $taxiRMemIdx;     //매칭요청 아이디
				$taxiRPTit = "메이커";
				$taxiRPChkNm = "상대방의 불가피한 사유에 의한 취소";
				$taxiCRIdx = $taxiSIdx;       //매칭생성 고유번호
				$taxiCRMemId = $taxiMemId;   //매칭생성 아이디
				$taxiCRMemIdx = $taxiMemIdx;   //매칭생성 아이디
			} else {
				$taxiPTit = "메이커";
				$taxiPhkNm = "본인의 불가피한 사유에 의한 취소";
				$taxiCSIdx = $taxiSIdx;       //매칭생성 고유번호
				$taxiCMemId = $taxiMemId;     //매칭생성 아이디
				$taxiCMemIdx = $taxiMemIdx;     //매칭생성 아이디
				$taxiRPTit = "투게더";
				$taxiRPChkNm = "상대방의 불가피한 사유에 의한 취소";
				$taxiCRIdx = $taxiRIdx;       //매칭요청 고유번호
				$taxiCRMemId = $taxiRMemId;   //매칭요청 아이디
				$taxiCRMemIdx = $taxiRMemIdx;   //매칭요청 아이디
			}
		} else { //취소 요청자가 요청자일 경우

			if ($taxiCPart == "3") { //상대방 사유로 인한 취소
				$taxiPTit = "메이커";
				$taxiPhkNm = "본인의 불가피한 사유에 의한 취소";
				$taxiCSIdx = $taxiSIdx;       //매칭생성 고유번호
				$taxiCMemId = $taxiMemId;     //매칭생성 아이디
				$taxiCMemIdx = $taxiMemIdx;     //매칭생성 아이디
				$taxiRPTit = "투게더";
				$taxiRPChkNm = "상대방의 불가피한 사유에 의한 취소";
				$taxiCRIdx = $taxiRIdx;       //매칭요청 고유번호
				$taxiCRMemId = $taxiRMemId;   //매칭요청 아이디
				$taxiCRMemIdx = $taxiRMemIdx;   //매칭요청 아이디
			} else {
				$taxiPTit = "투게더";
				$taxiPhkNm = "본인의 불가피한 사유에 의한 취소";
				$taxiCSIdx = $taxiRIdx;      //매칭요청 고유번호
				$taxiCMemId = $taxiRMemId;   //매칭요청 아이디
				$taxiCMemIdx = $taxiRMemIdx;   //매칭요청 아이디
				$taxiRPTit = "메이커";
				$taxiRPChkNm = "상대방의 불가피한 사유에 의한 취소";
				$taxiCRIdx = $taxiSIdx;      //매칭생성 고유번호
				$taxiCRMemId = $taxiMemId;   //매칭생성 아이디
				$taxiCRMemIdx = $taxiMemIdx;   //매칭생성 아이디
			}
		}

		if ($ie) { //익슬플로러일경우
			$taxi_CMemo = iconv('euc-kr', 'utf-8', $bContent);
		} else {
			$taxi_CMemo = $bContent;
		}

		//주문정보 가져옴
		$chkQuery = "SELECT idx FROM TB_RTAXISHARING WHERE idx = :idx AND taxi_RState = '6'  LIMIT 1  ";
		//echo $chkQuery."<BR>";
		//exit;
		$chkStmt = $DB_con->prepare($chkQuery);
		$chkStmt->bindparam(":idx", $taxiRIdx);
		$chkStmt->execute();
		$chkNum = $chkStmt->rowCount();
		//echo $chkNum."<BR>";
		//exit;

		if ($chkNum < 1) { //매칭값이 맞지 않을 경우
			$result = array("result" => false, "errorMsg" => "잘못된 접근입니다. 현재 매칭 중인 이동 노선이 없습니다.");
			echo str_replace('\\/', '/', json_encode($result, JSON_UNESCAPED_UNICODE));
			exit;
		} else {  // 취소가능
			//거래취소를 위한 주문조회
			$orderQuery = "SELECT taxi_SIdx, taxi_RIdx, taxi_OrdNo, taxi_OrdType, taxi_OrdSMemId, taxi_OrdMemId, taxi_OSMemIdx, taxi_OMemIdx FROM TB_ORDER WHERE taxi_SIdx = :taxi_SIdx LIMIT 1 ";
			//echo $orderQuery."<BR>";
			//exit;
			$orderStmt = $DB_con->prepare($orderQuery);
			$orderStmt->bindparam(":taxi_SIdx", $taxiSIdx);
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
					$taxiOrdNo = trim($orderRow['taxi_OrdNo']);					//  노선주문번호
					$taxi_OrdPoint = trim($orderRow['taxi_OrdPrice']);			// 양도포인트
					$taxiOrdSMemId = trim($orderRow['taxi_OrdSMemId']);			// 메이커 아이디
					$taxiOrdMemId = trim($orderRow['taxi_OrdMemId']);			// 투게더 아이디
					$taxiOSMemIdx = trim($orderRow['taxi_OSMemIdx']);			// 메이커 고유아이디
					$taxiOMemIdx = trim($orderRow['taxi_OMemIdx']);		    	// 투게더 고유아이디
					$taxi_OrdType = trim($orderRow['taxi_OrdType']);			// 결제타입(1: 카드, 2: 휴대폰)
				}
			}
			if ($taxi_OrdType == '0' || $taxi_OrdType == '1') {
				$access_token = get_Token_PayForm('https://api.iamport.kr/users/getToken', array("imp_key" => $imp_key, "imp_secret" => $imp_secret));

				if ($access_token == '') {
					$result['success']	= false;
					$result['errorMsg']	= "#2. " . $accesstoken_message;
				} else if ($access_token != '') {
					$order_cancle = common_Form('https://api.iamport.kr/payments/cancel', array("merchant_uid" => $taxiOrdNo, "reason" => "사용자 요청 취소처리"), $access_token);

					$code = $order_cancle['code'];										//성공여부
					$message = $order_cancle['message'];								//메세지
					$status = $order_cancle['response']['status'];						//결제상태
					
					if ($code == 1) {
						$result['success']	= false;
						$result['errorMsg']	= "#3. " . $message;
					} else if ($code == 0 && $status != 'cancelled') {
						$result['success']	= false;
						$result['errorMsg']	= "#4. " . $fail_reason;
					} else if ($code == 0 && $status == 'cancelled') { //if ($taxi_OrdState == "1") { //취소완료            
						//취소 사유 업데이트
						$upCQquery = "UPDATE TB_SMATCH_STATE SET taxi_CanRChk ='Y', taxi_CRPart = :taxi_CRPart, taxi_CMemo = :taxi_CMemo, reg_CRDate = :reg_CRDate WHERE idx = :idx  LIMIT 1";
						$upCStmt = $DB_con->prepare($upCQquery);
						$upCStmt->bindparam(":taxi_CRPart", $part);
						$upCStmt->bindparam(":taxi_CMemo", $taxi_CMemo);
						$upCStmt->bindparam(":reg_CRDate", $regDate);
						$upCStmt->bindparam(":idx", $chkIdx);
						$upCStmt->execute();

						//취소 신청자 회원정보
						$mnSql = "  , ( SELECT mem_NickNm FROM TB_MEMBERS WHERE TB_MEMBERS.idx = TB_MEMBERS_ETC.mem_Idx AND TB_MEMBERS.b_Disply = 'N' limit 1 ) AS memNickNm  ";
						$memQuery = "";
						$memQuery = "SELECT mem_McCnt {$mnSql} FROM TB_MEMBERS_ETC WHERE mem_Idx = :mem_Idx  LIMIT 1 ";
						$memStmt = $DB_con->prepare($memQuery);
						$memStmt->bindparam(":mem_Idx", $taxiCMemIdx);
						$memStmt->execute();
						$memNum = $memStmt->rowCount();

						if ($memNum < 1) { //아닐경우
						} else {
							while ($memRow = $memStmt->fetch(PDO::FETCH_ASSOC)) {
								$memNickNm = trim($memRow['memNickNm']);        // 취소신청자 닉네임

								if ($memNickNm == "") {
									$memNickNm = "탈퇴회원";        // 취소신청자 닉네임
								} else {
									$memNickNm = $memNickNm;        // 취소신청자 닉네임
								}

								$memMcCnt = trim($memRow['mem_McCnt']);			 // 회원 매칭 취소 횟수

								if ($memMcCnt == "") {
									$memMcCnt = "0";
								} else {
									$memMcCnt =  $memMcCnt;
								}
							}
						}

						//취소 확인자 회원정보
						$mnRSql = "  , ( SELECT mem_NickNm FROM TB_MEMBERS WHERE TB_MEMBERS.idx = TB_MEMBERS_ETC.mem_Idx AND TB_MEMBERS.b_Disply = 'N' limit 1 ) AS memNickNm  ";
						$memQuery2 = "";
						$memQuery2 = "SELECT mem_McCnt {$mnRSql} FROM TB_MEMBERS_ETC WHERE mem_Idx = :mem_Idx  LIMIT 1 ";
						$memStmt2 = $DB_con->prepare($memQuery2);
						$memStmt2->bindparam(":mem_Idx", $taxiCRMemIdx);
						$memStmt2->execute();
						$memNum2 = $memStmt2->rowCount();

						if ($memNum2 < 1) { //아닐경우
						} else {
							while ($memRow2 = $memStmt2->fetch(PDO::FETCH_ASSOC)) {
								$memRNickNm = trim($memRow2['memNickNm']);	     // 취소 확인자 닉네임

								if ($memRNickNm == "") {
									$memRNickNm = "탈퇴회원";        // 취소신청자 닉네임
								} else {
									$memRNickNm = $memRNickNm;        // 취소신청자 닉네임
								}

								$memRMcCnt = trim($memRow2['mem_McCnt']);		 // 회원 매칭 취소 횟수

								if ($memRMcCnt == "") {
									$memRMcCnt = "0";
								} else {
									$memRMcCnt =  $memRMcCnt;
								}
							}
						}

						//택시가 잡히지 않을 경우를 제외한 사유
						if ($taxiCPart != "1") {

							//주문정보 가져옴
							$ordQuery = "";
							$ordQuery = "SELECT taxi_OrdNo FROM TB_ORDER WHERE taxi_SIdx = :taxi_SIdx AND taxi_RIdx = :taxi_RIdx AND taxi_OrdSMemId = :taxi_OrdSMemId AND taxi_OrdMemId = :taxi_OrdMemId  AND taxi_OrdState = '1'  LIMIT 1  ";
							$ordStmt = $DB_con->prepare($ordQuery);
							$ordStmt->bindparam(":taxi_SIdx", $taxiSIdx);
							$ordStmt->bindparam(":taxi_RIdx", $taxiRIdx);
							$ordStmt->bindparam(":taxi_OrdSMemId", $taxiMemId);
							$ordStmt->bindparam(":taxi_OrdMemId", $taxiRMemId);
							$ordStmt->execute();
							$ordNum = $ordStmt->rowCount();
							//echo $ordNum."<BR>";
							//exit;

							if ($ordNum < 1) { //아닐경우
								$result = array("result" => false, "errorMsg" => (string)"잘못된 접근입니다. 현재 주문 상태가 맞지 않습니다.");
								echo str_replace('\\/', '/', json_encode($result, JSON_UNESCAPED_UNICODE));
								exit;
							} else {
								while ($ordRow = $ordStmt->fetch(PDO::FETCH_ASSOC)) {
									$taxiOrdNo = trim($ordRow['taxi_OrdNo']);		// 메이커 고유번호
								}
							}

							//취소 요청자 패널티 히스토리 내역
							if ($taxiCMemId <> "") {

								$taxi_Memo = DU_TIME_YMDHIS . ' 투게더(' . $memNickNm . ') ' . $taxiPhkNm . "함 ";
								//echo $taxi_Memo."<BR>";
								//exit;

								//패널티 내역 등록 여부 체크
								$cntQuery = "";
								$cntQuery = "SELECT count(idx) AS num FROM TB_PENALTY_HISTORY WHERE taxi_CIdx = :taxi_CIdx AND taxi_SIdx = :taxi_SIdx AND taxi_RIdx = :taxi_RIdx AND taxi_OrdNo = :taxi_OrdNo AND taxi_MemId = :taxi_MemId AND taxi_MemIdx = :taxi_MemIdx";
								$cntStmt = $DB_con->prepare($cntQuery);
								$cntStmt->bindparam(":taxi_CIdx", $chkIdx);
								$cntStmt->bindparam(":taxi_SIdx", $taxiCSIdx);
								$cntStmt->bindparam(":taxi_RIdx", $taxiCRIdx);
								$cntStmt->bindparam(":taxi_OrdNo", $taxiOrdNo);
								$cntStmt->bindparam(":taxi_MemId", $taxiCMemId);
								$cntStmt->bindparam(":taxi_MemIdx", $taxiCMemIdx);
								$cntStmt->execute();
								$cntRow = $cntStmt->fetch(PDO::FETCH_ASSOC);
								$totalCnt = $cntRow['num'];

								if ($totalCnt == "") {
									$totalCnt = "0";
								} else {
									$totalCnt =  $totalCnt;
								}


								//패널티 내역 중복 등록을 맞기 위해서 체크 함
								if ($totalCnt < 1) {

									$insQuery = "INSERT INTO TB_PENALTY_HISTORY (taxi_Mtype, taxi_CIdx, taxi_SIdx, taxi_RIdx, taxi_OrdNo, taxi_MemId, taxi_MemIdx, taxi_Cpart, taxi_Memo, reg_Date)
										VALUES (:taxi_Mtype, :taxi_CIdx, :taxi_SIdx, :taxi_RIdx, :taxi_OrdNo, :taxi_MemId, :taxi_MemIdx, :taxi_Cpart, :taxi_Memo, :reg_Date)";
									// echo $insQuery."<BR>";
									//exit;
									$stmt = $DB_con->prepare($insQuery);
									$stmt->bindParam("taxi_Mtype", $taxiMType);
									$stmt->bindParam("taxi_CIdx", $chkIdx);
									$stmt->bindParam("taxi_SIdx", $taxiCSIdx);
									$stmt->bindParam("taxi_RIdx", $taxiCRIdx);
									$stmt->bindParam("taxi_OrdNo", $taxiOrdNo);
									$stmt->bindParam("taxi_MemId", $taxiCMemId);
									$stmt->bindParam("taxi_MemIdx", $taxiCMemIdx);
									$stmt->bindParam("taxi_Cpart", $taxiCPart);
									$stmt->bindParam("taxi_Memo", $taxi_Memo);
									$stmt->bindParam("reg_Date", $regDate);
									$stmt->execute();
									$DB_con->lastInsertId();

									//매칭거절횟수
									$totMatCCnt = $memMcCnt + 1;

									//매칭 거절 횟수 변경
									$upMemQuery = "UPDATE TB_MEMBERS_ETC SET mem_McCnt = :mem_McCnt WHERE mem_Id = :mem_Id AND mem_Idx = :mem_Idx  LIMIT 1";
									//echo $upMemQuery."<BR>";
									//exit;
									$upMemPStmt = $DB_con->prepare($upMemQuery);
									$upMemPStmt->bindparam(":mem_McCnt", $totMatCCnt);
									$upMemPStmt->bindparam(":mem_Idx", $taxiCMemIdx);
									$upMemPStmt->bindparam(":mem_Id", $taxiCMemId);
									$upMemPStmt->execute();

									//푸시시작
									$mem_Token = memMatchTokenInfo($taxiCMemIdx);

									$title = "";
									$msg = "취소처리가 되었습니다.(이용내역에서 확인가능)";

									foreach ($mem_Token as $k => $v) {
										$tokens = $mem_Token[$k];
										$inputData = array("title" => $title, "msg" => $msg, "state" => "0");
										$presult = send_Push($tokens, $inputData);
									}
									//푸시종료
								}
							}

							//취소 확인자 패널티 히스토리 내역
							if ($taxiCRMemId <> "") {

								$taxi_RMemo = DU_TIME_YMDHIS . " " . $taxiRPTit . "(" . $memRNickNm . ") " . $taxiRPChkNm . "함.";
								//echo $taxi_RMemo."<BR>";
								//exit;
								//패널티 내역 등록 여부 체크
								$cntMQuery = "";
								$cntMQuery = "SELECT count(idx) AS num FROM TB_PENALTY_HISTORY  WHERE taxi_CIdx = :taxi_CIdx AND taxi_SIdx = :taxi_SIdx AND taxi_RIdx = :taxi_RIdx AND taxi_OrdNo = :taxi_OrdNo AND taxi_MemId = :taxi_MemId AND taxi_MemIdx = :taxi_MemIdx";
								$cntMStmt = $DB_con->prepare($cntMQuery);
								$cntMStmt->bindparam(":taxi_CIdx", $chkIdx);
								$cntMStmt->bindparam(":taxi_SIdx", $taxiCSIdx);
								$cntMStmt->bindparam(":taxi_RIdx", $taxiCRIdx);
								$cntMStmt->bindparam(":taxi_OrdNo", $taxiOrdNo);
								$cntMStmt->bindparam(":taxi_MemId", $taxiCRMemId);
								$cntMStmt->bindparam(":taxi_MemIdx", $taxiCRMemIdx);
								$cntMStmt->execute();
								$cntMRow = $cntMStmt->fetch(PDO::FETCH_ASSOC);
								$totalMCnt = $cntMRow['num'];

								if ($totalMCnt == "") {
									$totalMCnt = "0";
								} else {
									$totalMCnt =  $totalMCnt;
								}


								//패널티 내역 중복 등록을 맞기 위해서 체크 함
								if ($totalMCnt < 1) {

									$insMQuery = "INSERT INTO TB_PENALTY_HISTORY (taxi_Mtype, taxi_CIdx, taxi_SIdx, taxi_RIdx, taxi_OrdNo, taxi_MemId, taxi_MemIdx, taxi_CRpart, taxi_Memo, taxi_CMemo, reg_Date)
									VALUES (:taxi_Mtype, :taxi_CIdx, :taxi_SIdx, :taxi_RIdx, :taxi_OrdNo, :taxi_MemId, :taxi_MemIdx, :taxi_CRpart, :taxi_Memo, :taxi_CMemo, :reg_Date)";
									//echo $insQuery."<BR>";
									//exit;
									$mstmt = $DB_con->prepare($insMQuery);
									$mstmt->bindParam("taxi_Mtype", $taxiMType);
									$mstmt->bindParam("taxi_CIdx", $chkIdx);
									$mstmt->bindParam("taxi_SIdx", $taxiCSIdx);
									$mstmt->bindParam("taxi_RIdx", $taxiCRIdx);
									$mstmt->bindParam("taxi_OrdNo", $taxiOrdNo);
									$mstmt->bindParam("taxi_MemId", $taxiCRMemId);
									$mstmt->bindParam("taxi_MemIdx", $taxiCRMemIdx);
									$mstmt->bindParam("taxi_CRpart", $part);
									$mstmt->bindParam("taxi_Memo", $taxi_RMemo);
									$mstmt->bindParam("taxi_CMemo", $taxi_CMemo);
									$mstmt->bindParam("reg_Date", $regDate);
									$mstmt->execute();
									$DB_con->lastInsertId();


									//매칭 취소 횟수
									$totRMatCCnt = $memRMcCnt + 1;

									//매칭 취소 횟수 변경
									$upRMemQuery = "UPDATE TB_MEMBERS_ETC SET mem_McCnt = :mem_McCnt WHERE mem_Id = :mem_Id AND mem_Idx = :mem_Idx LIMIT 1";
									//echo $upRMemQuery."<BR>";
									//exit;
									$upRMemPStmt = $DB_con->prepare($upRMemQuery);
									$upRMemPStmt->bindparam(":mem_McCnt", $totRMatCCnt);
									$upRMemPStmt->bindparam(":mem_Idx", $taxiCRMemIdx);
									$upRMemPStmt->bindparam(":mem_Id", $taxiCRMemId);
									$upRMemPStmt->execute();

									//주문서 상태 취소 변경
									$upOrdQuery = "UPDATE TB_ORDER SET taxi_OrdState = '3' WHERE taxi_OrdNo = :taxi_OrdNo  LIMIT 1";
									//echo $upOrdQuery."<BR>";
									//exit;
									$upOrdStmt = $DB_con->prepare($upOrdQuery);
									$upOrdStmt->bindparam(":taxi_OrdNo", $taxiOrdNo);
									$upOrdStmt->execute();

									//푸시시작
									$mem_MDToken = memMatchTokenInfo($taxiCRMemIdx);
									$mDtitle = "";
									$mDmsg = "취소처리가 되었습니다.(이용내역에서 확인가능)";

									foreach ($mem_MDToken as $k => $v) {
										$mDtokens = $mem_MDToken[$k];
										$mDinputData = array("title" => $mDtitle, "msg" => $mDmsg, "state" => "0");
										$mDpresult = send_Push($mDtokens, $mDinputData);
									}
									//푸시종료

								}
							}
						}


						// 투게더 취소
						// 투게더가 취소 시켰을 때  ( 0:투게더 취소, 1 : 본인취소)

						//투게더 취소 상태 변경
						$upMQquery = "UPDATE TB_RTAXISHARING SET taxi_RState = '8' WHERE idx = :idx AND taxi_RMemId = :taxi_RMemId AND taxi_RState = '6' LIMIT 1";   //이동중 취소 상태 변경";
						$upMStmt = $DB_con->prepare($upMQquery);
						$upMStmt->bindparam(":idx", $taxiRIdx);
						$upMStmt->bindparam(":taxi_RMemId", $taxiRMemId);
						$upMStmt->execute();

						//투게더 취소 기타 변경
						$upMQquery2 = "UPDATE TB_RTAXISHARING_INFO SET taxi_MCancle = :taxi_MCancle, taxi_MState = '6', reg_CDate = :reg_CDate WHERE taxi_RIdx = :taxi_RIdx AND taxi_RMemId = :taxi_RMemId LIMIT 1";
						//echo $upMQquery2."<BR>";
						//exit;
						$upMStmt2 = $DB_con->prepare($upMQquery2);
						$upMStmt2->bindparam(":taxi_MCancle", $taxiRMCancle);
						$upMStmt2->bindparam(":reg_CDate", $regDate);
						$upMStmt2->bindparam(":taxi_RIdx", $taxiRIdx);
						$upMStmt2->bindparam(":taxi_RMemId", $taxiRMemId);
						$upMStmt2->execute();


						//메이커 이동중 취소 상태로 변경
						$upPQquery = "UPDATE TB_STAXISHARING SET taxi_State = '8', taxi_MCancle = :taxi_MCancle, taxi_MState = '6', reg_CDate = :reg_CDate WHERE idx = :idx  AND taxi_MemId = :taxi_MemId AND taxi_State = '6' LIMIT 1";
						//echo $upPQquery."<BR>";
						$upPStmt = $DB_con->prepare($upPQquery);
						$upPStmt->bindparam(":taxi_MCancle", $taxiMcancle);
						$upPStmt->bindparam(":reg_CDate", $regDate);
						$upPStmt->bindparam(":idx", $taxiSIdx);
						$upPStmt->bindparam(":taxi_MemId", $taxiMemId);
						$upPStmt->execute();

						$result = array("result" => true);
					}else{
						$result['success']	= false;
						$result['errorMsg']	= "#5. " . $message;
					}
				}
			} else {	// 보유포인트결제 시작
				//취소 사유 업데이트
				$upCQquery = "UPDATE TB_SMATCH_STATE SET taxi_CanRChk ='Y', taxi_CRPart = :taxi_CRPart, taxi_CMemo = :taxi_CMemo, reg_CRDate = :reg_CRDate WHERE idx = :idx  LIMIT 1";
				$upCStmt = $DB_con->prepare($upCQquery);
				$upCStmt->bindparam(":taxi_CRPart", $part);
				$upCStmt->bindparam(":taxi_CMemo", $taxi_CMemo);
				$upCStmt->bindparam(":reg_CRDate", $regDate);
				$upCStmt->bindparam(":idx", $chkIdx);
				$upCStmt->execute();

				//취소 신청자 회원정보
				$mnSql = "  , ( SELECT mem_NickNm FROM TB_MEMBERS WHERE TB_MEMBERS.mem_Id = TB_MEMBERS_ETC.mem_Id AND TB_MEMBERS.b_Disply = 'N' limit 1 ) AS memNickNm  ";
				$memQuery = "";
				$memQuery = "SELECT mem_McCnt {$mnSql} FROM TB_MEMBERS_ETC WHERE mem_Id = :mem_Id  LIMIT 1 ";
				$memStmt = $DB_con->prepare($memQuery);
				$memStmt->bindparam(":mem_Id", $taxiCMemId);
				$memStmt->execute();
				$memNum = $memStmt->rowCount();

				if ($memNum < 1) { //아닐경우
				} else {
					while ($memRow = $memStmt->fetch(PDO::FETCH_ASSOC)) {
						$memNickNm = trim($memRow['memNickNm']);        // 취소신청자 닉네임

						if ($memNickNm == "") {
							$memNickNm = "탈퇴회원";        // 취소신청자 닉네임
						} else {
							$memNickNm = $memNickNm;        // 취소신청자 닉네임
						}
						$memMcCnt = trim($memRow['mem_McCnt']);			 // 회원 매칭 취소 횟수

						if ($memMcCnt == "") {
							$memMcCnt = "0";
						} else {
							$memMcCnt =  $memMcCnt;
						}
					}
				}

				//취소 확인자 회원정보
				$mnRSql = "  , ( SELECT mem_NickNm FROM TB_MEMBERS WHERE TB_MEMBERS.mem_Id = TB_MEMBERS_ETC.mem_Id AND TB_MEMBERS.b_Disply = 'N' limit 1 ) AS memNickNm  ";
				$memQuery2 = "";
				$memQuery2 = "SELECT mem_McCnt {$mnRSql} FROM TB_MEMBERS_ETC WHERE mem_Id = :mem_Id  LIMIT 1 ";
				$memStmt2 = $DB_con->prepare($memQuery2);
				$memStmt2->bindparam(":mem_Id", $taxiCRMemId);
				$memStmt2->execute();
				$memNum2 = $memStmt2->rowCount();

				if ($memNum2 < 1) { //아닐경우
				} else {
					while ($memRow2 = $memStmt2->fetch(PDO::FETCH_ASSOC)) {
						$memRNickNm = trim($memRow2['memNickNm']);	     // 취소 확인자 닉네임

						if ($memRNickNm == "") {
							$memRNickNm = "탈퇴회원";        // 취소신청자 닉네임
						} else {
							$memRNickNm = $memRNickNm;        // 취소신청자 닉네임
						}

						$memRMcCnt = trim($memRow2['mem_McCnt']);		 // 회원 매칭 취소 횟수

						if ($memRMcCnt == "") {
							$memRMcCnt = "0";
						} else {
							$memRMcCnt =  $memRMcCnt;
						}
					}
				}

				//택시가 잡히지 않을 경우를 제외한 사유
				if ($taxiCPart != "1") {

					//주문정보 가져옴
					$ordQuery = "";
					$ordQuery = "SELECT taxi_OrdNo FROM TB_ORDER WHERE taxi_SIdx = :taxi_SIdx AND taxi_RIdx = :taxi_RIdx AND taxi_OrdSMemId = :taxi_OrdSMemId AND taxi_OrdMemId = :taxi_OrdMemId  AND taxi_OrdState = '1'  LIMIT 1  ";
					$ordStmt = $DB_con->prepare($ordQuery);
					$ordStmt->bindparam(":taxi_SIdx", $taxiSIdx);
					$ordStmt->bindparam(":taxi_RIdx", $taxiRIdx);
					$ordStmt->bindparam(":taxi_OrdSMemId", $taxiMemId);
					$ordStmt->bindparam(":taxi_OrdMemId", $taxiRMemId);
					$ordStmt->execute();
					$ordNum = $ordStmt->rowCount();
					//echo $ordNum."<BR>";
					//exit;

					if ($ordNum < 1) { //아닐경우
						$result = array("result" => false, "errorMsg" => (string)"잘못된 접근입니다. 현재 주문 상태가 맞지 않습니다.");
						echo str_replace('\\/', '/', json_encode($result, JSON_UNESCAPED_UNICODE));
						exit;
					} else {
						while ($ordRow = $ordStmt->fetch(PDO::FETCH_ASSOC)) {
							$taxiOrdNo = trim($ordRow['taxi_OrdNo']);		// 메이커 고유번호
						}
					}

					//취소 요청자 패널티 히스토리 내역
					if ($taxiCMemId <> "") {

						$taxi_Memo = DU_TIME_YMDHIS . ' 투게더(' . $memNickNm . ') ' . $taxiPhkNm . '함.';
						//echo $taxi_Memo."<BR>";
						//exit;

						//패널티 내역 등록 여부 체크
						$cntQuery = "";
						$cntQuery = "SELECT count(idx) AS num FROM TB_PENALTY_HISTORY WHERE taxi_CIdx = :taxi_CIdx AND taxi_SIdx = :taxi_SIdx AND taxi_RIdx = :taxi_RIdx AND taxi_OrdNo = :taxi_OrdNo AND taxi_MemId = :taxi_MemId ";
						$cntStmt = $DB_con->prepare($cntQuery);
						$cntStmt->bindparam(":taxi_CIdx", $chkIdx);
						$cntStmt->bindparam(":taxi_SIdx", $taxiCSIdx);
						$cntStmt->bindparam(":taxi_RIdx", $taxiCRIdx);
						$cntStmt->bindparam(":taxi_OrdNo", $taxiOrdNo);
						$cntStmt->bindparam(":taxi_MemId", $taxiCMemId);
						$cntStmt->execute();
						$cntRow = $cntStmt->fetch(PDO::FETCH_ASSOC);
						$totalCnt = $cntRow['num'];

						if ($totalCnt == "") {
							$totalCnt = "0";
						} else {
							$totalCnt =  $totalCnt;
						}


						//패널티 내역 중복 등록을 맞기 위해서 체크 함
						if ($totalCnt < 1) {

							$insQuery = "INSERT INTO TB_PENALTY_HISTORY (taxi_Mtype, taxi_CIdx, taxi_SIdx, taxi_RIdx, taxi_OrdNo, taxi_MemId, taxi_Cpart, taxi_Memo, reg_Date)
										VALUES (:taxi_Mtype, :taxi_CIdx, :taxi_SIdx, :taxi_RIdx, :taxi_OrdNo, :taxi_MemId, :taxi_Cpart, :taxi_Memo, :reg_Date)";
							// echo $insQuery."<BR>";
							//exit;
							$stmt = $DB_con->prepare($insQuery);
							$stmt->bindParam("taxi_Mtype", $taxiMType);
							$stmt->bindParam("taxi_CIdx", $chkIdx);
							$stmt->bindParam("taxi_SIdx", $taxiCSIdx);
							$stmt->bindParam("taxi_RIdx", $taxiCRIdx);
							$stmt->bindParam("taxi_OrdNo", $taxiOrdNo);
							$stmt->bindParam("taxi_MemId", $taxiCMemId);
							$stmt->bindParam("taxi_Cpart", $taxiCPart);
							$stmt->bindParam("taxi_Memo", $taxi_Memo);
							$stmt->bindParam("reg_Date", $regDate);
							$stmt->execute();
							$DB_con->lastInsertId();

							//매칭거절횟수
							$totMatCCnt = $memMcCnt + 1;

							//매칭 거절 횟수 변경
							$upMemQuery = "UPDATE TB_MEMBERS_ETC SET mem_McCnt = :mem_McCnt WHERE mem_Id = :mem_Id AND mem_Idx = :mem_Idx LIMIT 1";
							//echo $upMemQuery."<BR>";
							//exit;
							$upMemPStmt = $DB_con->prepare($upMemQuery);
							$upMemPStmt->bindparam(":mem_McCnt", $totMatCCnt);
							$upMemPStmt->bindparam(":mem_Id", $taxiCMemId);
							$upMemPStmt->bindparam(":mem_Idx", $taxiCMemIdx);
							$upMemPStmt->execute();

							//푸시시작
							$mem_Token = memMatchTokenInfo($taxiCMemIdx);

							$title = "";
							$msg = "취소처리가 되었습니다.(이용내역에서 확인가능)";

							foreach ($mem_Token as $k => $v) {
								$tokens = $mem_Token[$k];
								$inputData = array("title" => $title, "msg" => $msg, "state" => "0");
								$presult = send_Push($tokens, $inputData);
							}
							//푸시종료
						}
					}

					//취소 확인자 패널티 히스토리 내역
					if ($taxiCRMemId <> "") {

						$taxi_RMemo = DU_TIME_YMDHIS . " " . $taxiRPTit . "(" . $memRNickNm . ") " . $taxiRPChkNm . '함.';
						//echo $taxi_RMemo."<BR>";
						//exit;
						//패널티 내역 등록 여부 체크
						$cntMQuery = "";
						$cntMQuery = "SELECT count(idx) AS num FROM TB_PENALTY_HISTORY  WHERE taxi_CIdx = :taxi_CIdx AND taxi_SIdx = :taxi_SIdx AND taxi_RIdx = :taxi_RIdx AND taxi_OrdNo = :taxi_OrdNo AND taxi_MemId = :taxi_MemId AND taxi_MemIdx = :taxi_MemIdx";
						$cntMStmt = $DB_con->prepare($cntMQuery);
						$cntMStmt->bindparam(":taxi_CIdx", $chkIdx);
						$cntMStmt->bindparam(":taxi_SIdx", $taxiCSIdx);
						$cntMStmt->bindparam(":taxi_RIdx", $taxiCRIdx);
						$cntMStmt->bindparam(":taxi_OrdNo", $taxiOrdNo);
						$cntMStmt->bindparam(":taxi_MemId", $taxiCRMemId);
						$cntMStmt->bindparam(":taxi_MemIdx", $taxiCRMemIdx);
						$cntMStmt->execute();
						$cntMRow = $cntMStmt->fetch(PDO::FETCH_ASSOC);
						$totalMCnt = $cntMRow['num'];

						if ($totalMCnt == "") {
							$totalMCnt = "0";
						} else {
							$totalMCnt =  $totalMCnt;
						}


						//패널티 내역 중복 등록을 맞기 위해서 체크 함
						if ($totalMCnt < 1) {

							$insMQuery = "INSERT INTO TB_PENALTY_HISTORY (taxi_Mtype, taxi_CIdx, taxi_SIdx, taxi_RIdx, taxi_OrdNo, taxi_MemId, taxi_MemIdx, taxi_CRpart, taxi_Memo, taxi_CMemo, reg_Date)
									VALUES (:taxi_Mtype, :taxi_CIdx, :taxi_SIdx, :taxi_RIdx, :taxi_OrdNo, :taxi_MemId, :taxi_MemIdx, :taxi_CRpart, :taxi_Memo, :taxi_CMemo, :reg_Date)";
							//echo $insQuery."<BR>";
							//exit;
							$mstmt = $DB_con->prepare($insMQuery);
							$mstmt->bindParam("taxi_Mtype", $taxiMType);
							$mstmt->bindParam("taxi_CIdx", $chkIdx);
							$mstmt->bindParam("taxi_SIdx", $taxiCSIdx);
							$mstmt->bindParam("taxi_RIdx", $taxiCRIdx);
							$mstmt->bindParam("taxi_OrdNo", $taxiOrdNo);
							$mstmt->bindParam("taxi_MemId", $taxiCRMemId);
							$mstmt->bindParam("taxi_MemIdx", $taxiCRMemIdx);
							$mstmt->bindParam("taxi_CRpart", $part);
							$mstmt->bindParam("taxi_Memo", $taxi_RMemo);
							$mstmt->bindParam("taxi_CMemo", $taxi_CMemo);
							$mstmt->bindParam("reg_Date", $regDate);
							$mstmt->execute();
							$DB_con->lastInsertId();


							//매칭 취소 횟수
							$totRMatCCnt = $memRMcCnt + 1;

							//매칭 취소 횟수 변경
							$upRMemQuery = "UPDATE TB_MEMBERS_ETC SET mem_McCnt = :mem_McCnt WHERE mem_Id = :mem_Id AND mem_Idx = :mem_Idx LIMIT 1";
							//echo $upRMemQuery."<BR>";
							//exit;
							$upRMemPStmt = $DB_con->prepare($upRMemQuery);
							$upRMemPStmt->bindparam(":mem_McCnt", $totRMatCCnt);
							$upRMemPStmt->bindparam(":mem_Id", $taxiCRMemId);
							$upRMemPStmt->bindparam(":mem_Idx", $taxiCRMemIdx);
							$upRMemPStmt->execute();

							//주문서 상태 취소 변경
							$upOrdQuery = "UPDATE TB_ORDER SET taxi_OrdState = '3' WHERE taxi_OrdNo = :taxi_OrdNo  LIMIT 1";
							//echo $upOrdQuery."<BR>";
							//exit;
							$upOrdStmt = $DB_con->prepare($upOrdQuery);
							$upOrdStmt->bindparam(":taxi_OrdNo", $taxiOrdNo);
							$upOrdStmt->execute();

							//푸시시작
							$mem_MDToken = memMatchTokenInfo($taxiCRMemIdx);

							$mDtitle = "";
							$mDmsg = "취소처리가 되었습니다.(이용내역에서 확인가능)";

							foreach ($mem_MDToken as $k => $v) {
								$mDtokens = $mem_MDToken[$k];
								$mDinputData = array("title" => $mDtitle, "msg" => $mDmsg, "state" => "0");
								$mDpresult = send_Push($mDtokens, $mDinputData);
							}
							//푸시종료

						}
					}
				}

				// 투게더 취소
				// 투게더가 취소 시켰을 때  ( 0:투게더 취소, 1 : 본인취소)

				//투게더 취소 상태 변경
				$upMQquery = "UPDATE TB_RTAXISHARING SET taxi_RState = '8' WHERE idx = :idx AND taxi_RMemId = :taxi_RMemId AND taxi_RState = '6' LIMIT 1";   //이동중 취소 상태 변경";
				$upMStmt = $DB_con->prepare($upMQquery);
				$upMStmt->bindparam(":idx", $taxiRIdx);
				$upMStmt->bindparam(":taxi_RMemId", $taxiRMemId);
				$upMStmt->execute();

				//투게더 취소 기타 변경
				$upMQquery2 = "UPDATE TB_RTAXISHARING_INFO SET taxi_MCancle = :taxi_MCancle, taxi_MState = '6', reg_CDate = :reg_CDate WHERE taxi_RIdx = :taxi_RIdx AND taxi_RMemId = :taxi_RMemId LIMIT 1";
				//echo $upMQquery2."<BR>";
				//exit;
				$upMStmt2 = $DB_con->prepare($upMQquery2);
				$upMStmt2->bindparam(":taxi_MCancle", $taxiRMCancle);
				$upMStmt2->bindparam(":reg_CDate", $regDate);
				$upMStmt2->bindparam(":taxi_RIdx", $taxiRIdx);
				$upMStmt2->bindparam(":taxi_RMemId", $taxiRMemId);
				$upMStmt2->execute();


				//메이커 이동중 취소 상태로 변경
				$upPQquery = "UPDATE TB_STAXISHARING SET taxi_State = '8', taxi_MCancle = :taxi_MCancle, taxi_MState = '6', reg_CDate = :reg_CDate WHERE idx = :idx  AND taxi_MemId = :taxi_MemId AND taxi_State = '6' LIMIT 1";
				//echo $upPQquery."<BR>";
				$upPStmt = $DB_con->prepare($upPQquery);
				$upPStmt->bindparam(":taxi_MCancle", $taxiMcancle);
				$upPStmt->bindparam(":reg_CDate", $regDate);
				$upPStmt->bindparam(":idx", $taxiSIdx);
				$upPStmt->bindparam(":taxi_MemId", $taxiMemId);
				$upPStmt->execute();

				$result = array("result" => true);
			}	// 보유포인트결제 끝
		}
	}

	dbClose($DB_con);
	$viewStmt = null;
	$upCStmt = null;
	$cPointStmt = null;
	$memStmt = null;
	$memStmt2 = null;
	$ordStmt = null;
	$cntStmt = null;
	$stmt = null;
	$upLvStmt = null;
	$upMemPStmt = null;
	$cntMStmt = null;
	$mstmt = null;
	$upLvRStmt = null;
	$upRMemPStmt = null;
	$upOrdStmt = null;
	$upMStmt = null;
	$upMStmt2 = null;
	$upPStmt = null;
	$orderStmt = null;
	$cancleCStmt = null;
	$cancleUStmt = null;

	echo str_replace('\\/', '/', json_encode($result, JSON_UNESCAPED_UNICODE));
} else {
	$result = array("result" => false, "errorMsg" => "조회정보값이 없습니다. 관리자에게 문의바랍니다.");
	echo json_encode($result, JSON_UNESCAPED_UNICODE);
}
