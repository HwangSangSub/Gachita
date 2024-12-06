<?php
/*======================================================================================================================

* 프로그램				:  취소 승인, 거절 처리
* 페이지 설명			:  취소 승인, 거절 처리
* 파일명              :  taxiSharingCRProc.php

========================================================================================================================*/
include "../../udev/lib/common.php";
include "../../lib/functionDB.php";  //공통 db함수
include "../../order/lib/TPAY.LIB.php";  //공통 db함수
include "../../order/lib/tpay_proc.php"; // 아임포트 함수
//require_once dirname(__FILE__).'/TPAY.LIB.php';  //tpay lib

$idx = trim($idx);                    //고유번호 (노선번호)
$type = trim($type);                //승인여부
$push = trim($push);                //푸시발송여부
$taxiOrdNo  = trim($taxiOrdNo);            // 주문번호
$chkState  = trim($chkState);    // 바로양도 확인 2

$DB_con = db1();

//거래취소를 위한 주문조회
$orderQuery = "";
$orderQuery = "SELECT taxi_SIdx, taxi_RIdx, taxi_OrdNo, taxi_OrdType, taxi_OrdSMemId, taxi_OrdMemId, taxi_OSMemIdx, taxi_OMemIdx FROM TB_ORDER WHERE taxi_SIdx = :taxi_SIdx LIMIT 1 ";
//echo $orderQuery."<BR>";
//exit;
$orderStmt = $DB_con->prepare($orderQuery);
$orderStmt->bindparam(":taxi_SIdx", $idx);
$orderStmt->execute();
$orderNum = $orderStmt->rowCount();
//echo $mapNum."<BR>";

if ($orderNum < 1) { //아닐경우
    $result['success']    = false;
    $result['Msg']    = "해당노선의 주문건이 없습니다.";
    //$result = array("result" => "error","errorMsg" => "해당노선의 주문건이 없습니다." );
} else {
    while ($orderRow = $orderStmt->fetch(PDO::FETCH_ASSOC)) {
        $taxiSIdx = trim($orderRow['taxi_SIdx']);                    // 메이커 고유번호
        $taxiRIdx = trim($orderRow['taxi_RIdx']);                    // 투게더 고유번호
        $taxiOrdNo = trim($orderRow['taxi_OrdNo']);                    //  노선주문번호
        $taxi_OrdPoint = trim($orderRow['taxi_OrdPrice']);            // 양도포인트
        $taxiOrdSMemId = trim($orderRow['taxi_OrdSMemId']);            // 메이커 아이디
        $taxiOrdMemId = trim($orderRow['taxi_OrdMemId']);            // 투게더 아이디
        $taxiOSMemIdx = trim($orderRow['taxi_OSMemIdx']);                // 메이커 고유아이디
        $taxiOMemIdx = trim($orderRow['taxi_OMemIdx']);                // 투게더 고유아이디
        $taxi_OrdType = trim($orderRow['taxi_OrdType']);            // 결제타입
    }
}

if ($type == 'Y') {
    if ($taxi_OrdType == "1") {
        $access_token = get_Token_PayForm('https://api.iamport.kr/users/getToken', array("imp_key" => $imp_key, "imp_secret" => $imp_secret));
        /*

		echo$access_token;	
		$Billing_Key_Chk = common_Form('https://api.iamport.kr/subscribe/customers',array("customer_uid" => "2019473131650107248"),$access_token);
		$BillingKey_code = $Billing_Key_Chk['code'];							//성공여부
		$BillingKey_message = $Billing_Key_Chk['message'];						//메세지
		$BillingKey_uid = $Billing_Key_Chk['customer_uid'];						//메세지

		print_r($Billing_Key_Chk);
		echo "<BR>";
		echo $BillingKey_code."<BR>";
		echo $BillingKey_message."<BR>";
		echo $BillingKey_uid."<BR>";
		exit;
		*/
        if ($access_token == '') {
            $result['success']    = false;
            $result['Msg']    = "#2. " . $accesstoken_message;
            //$result = array("result" => "error","errorMsg" => "#2. ".$accesstoken_message );
        } else if ($access_token != '') {
            $order_cancle = common_Form('https://api.iamport.kr/payments/cancel', array("merchant_uid" => $taxiOrdNo, "reason" => "사용자 요청으로 본사에서 확인 후 취소처리"), $access_token);
            $code = $order_cancle['code'];                                        //성공여부
            $message = $order_cancle['message'];                                //메세지
            $status = $order_cancle['response']['status'];                        //결제상태
            if ($code == 1) {
                $result['success']    = false;
                $result['Msg']    = "#3. " . $message;
                //$result = array("result" => "error", "errorMsg" => "#3. ".$message);
            } else if ($code == 0 && $status != 'cancelled') {
                $result['success']    = false;
                $result['Msg']    = "#4. " . $fail_reason;
                //$result = array("result" => "error", "errorMsg" => "#4. ".$fail_reason);
            } else if ($code == 0 && $status == 'cancelled') {        //if ($taxi_OrdState == "1") { //결제완료
                //메이커 취소처리
                $upMQquery11 = "UPDATE TB_STAXISHARING SET taxi_State = '10', taxi_MState = '9', reg_CDate = NOW() WHERE idx = :idx LIMIT 1";
                $upMStmt11 = $DB_con->prepare($upMQquery11);
                $upMStmt11->bindparam(":idx", $idx);
                $upMStmt11->execute();

                //투게더 취소처리
                $upMQquery22 = "UPDATE TB_RTAXISHARING SET taxi_RState = '10' WHERE taxi_SIdx = :taxi_SIdx LIMIT 1";
                $upMStmt22 = $DB_con->prepare($upMQquery22);
                $upMStmt22->bindparam(":taxi_SIdx", $idx);
                $upMStmt22->execute();

                //취소사유메모 기록
                $upMQquery33 = "UPDATE TB_SMATCH_STATE SET taxi_CMemo = '본사확인 후 취소 처리', taxi_Disply ='Y' WHERE taxi_SIdx = :taxi_SIdx LIMIT 1";
                $upMStmt33 = $DB_con->prepare($upMQquery33);
                $upMStmt33->bindparam(":taxi_SIdx", $idx);
                $upMStmt33->execute();

                $upMQquery44 = "UPDATE TB_RTAXISHARING_INFO SET reg_CYDate = now(), taxi_RMemo = '본사확인 후 취소 처리' WHERE taxi_SIdx = :taxi_SIdx LIMIT 1";
                $upMStmt44 = $DB_con->prepare($upMQquery44);
                $upMStmt44->bindparam(":taxi_SIdx", $idx);
                $upMStmt44->execute();


                //생성자 푸시
                $mem_NToken = memMatchTokenInfo($taxiOSMemIdx);

                $chkState = "10";  //거래완료
                $ntitle = "";
                $nmsg = "거래취소 요청이 승인되었습니다.";
                foreach ($mem_NToken as $k => $v) {
                    $ntokens = $mem_NToken[$k];
                    $ninputData = array("title" => $ntitle, "msg" => $nmsg, "state" => $chkState);
                    $nresult = send_Push($ntokens, $ninputData);
                }

                //요청자 푸시
                $mem_RToken = memMatchTokenInfo($taxiOMemIdx);
                $rchkState = "10";  //거래완료
                $rtitle = "";
                $rmsg = "거래취소 요청이 승인되었습니다.";
                foreach ($mem_RToken as $k2 => $v2) {
                    $rtokens = $mem_RToken[$k2];
                    $rinputData = array("title" => $rtitle, "msg" => $rmsg, "state" => $rchkState);
                    $rResult = send_Push($rtokens, $ninputData);
                }

                $result['success']    = true;
                $result['Msg']    = "요청된 취소건을 승인완료 처리되었습니다.";
            }
        }
    } else {
        //메이커 취소처리
        $upMQquery11 = "UPDATE TB_STAXISHARING SET taxi_State = '10', taxi_MState = '9', reg_CDate = NOW() WHERE idx = :idx LIMIT 1";
        $upMStmt11 = $DB_con->prepare($upMQquery11);
        $upMStmt11->bindparam(":idx", $idx);
        $upMStmt11->execute();

        //투게더 취소처리
        $upMQquery22 = "UPDATE TB_RTAXISHARING SET taxi_RState = '10' WHERE taxi_SIdx = :taxi_SIdx LIMIT 1";
        $upMStmt22 = $DB_con->prepare($upMQquery22);
        $upMStmt22->bindparam(":taxi_SIdx", $idx);
        $upMStmt22->execute();

        //취소사유메모 기록
        $upMQquery33 = "UPDATE TB_SMATCH_STATE SET taxi_CMemo = '본사확인 후 취소 처리', taxi_Disply ='Y' WHERE taxi_SIdx = :taxi_SIdx LIMIT 1";
        $upMStmt33 = $DB_con->prepare($upMQquery33);
        $upMStmt33->bindparam(":taxi_SIdx", $idx);
        $upMStmt33->execute();

        $upMQquery44 = "UPDATE TB_RTAXISHARING_INFO SET reg_CYDate = now(), taxi_RMemo = '본사확인 후 취소 처리' WHERE taxi_SIdx = :taxi_SIdx LIMIT 1";
        $upMStmt44 = $DB_con->prepare($upMQquery44);
        $upMStmt44->bindparam(":taxi_SIdx", $idx);
        $upMStmt44->execute();


        //생성자 푸시
        $mem_NToken = memMatchTokenInfo($taxiOSMemIdx);
        $chkState = "10";  //거래완료
        $ntitle = "";
        $nmsg = "거래취소 요청이 승인되었습니다.";
        foreach ($mem_NToken as $k => $v) {
            $ntokens = $mem_NToken[$k];
            $ninputData = array("title" => $ntitle, "msg" => $nmsg, "state" => $chkState);
            $nresult = send_Push($ntokens, $ninputData);
        }

        //요청자 푸시
        $mem_RToken = memMatchTokenInfo($taxiOMemIdx);
        $rchkState = "10";  //거래완료
        $rtitle = "";
        $rmsg = "거래취소 요청이 승인되었습니다.";
        foreach ($mem_RToken as $k2 => $v2) {
            $rtokens = $mem_RToken[$k2];
            $rinputData = array("title" => $rtitle, "msg" => $rmsg, "state" => $rchkState);
            $rResult = send_Push($rtokens, $ninputData);
        }

        $result['success']    = true;
        $result['Msg']    = "요청된 취소건을 승인완료 처리되었습니다.";
        //$result = array("result" => "success","Msg" => "요청된 취소건을 승인완료 처리되었습니다." );
    }
} else if ($type == 'N') { //
    $taxi_PDisply = trim($pDisplay);
    if ($taxi_PDisply == 'N') {
        //메이커 취소처리
        $upMQquery11 = "UPDATE TB_STAXISHARING SET taxi_State = '10', taxi_MState = '9', reg_CDate = NOW() WHERE idx = :idx LIMIT 1";
        $upMStmt11 = $DB_con->prepare($upMQquery11);
        $upMStmt11->bindparam(":idx", $idx);
        $upMStmt11->execute();

        //투게더 취소처리
        $upMQquery22 = "UPDATE TB_RTAXISHARING SET taxi_RState = '10' WHERE taxi_SIdx = :taxi_SIdx LIMIT 1";
        $upMStmt22 = $DB_con->prepare($upMQquery22);
        $upMStmt22->bindparam(":taxi_SIdx", $idx);
        $upMStmt22->execute();

        //취소사유메모 기록
        $upMQquery33 = "UPDATE TB_SMATCH_STATE SET taxi_CMemo = '본사확인 후 취소 거절 처리', taxi_Disply ='Y', taxi_PDisply = :taxi_PDisply WHERE taxi_SIdx = :taxi_SIdx LIMIT 1";
        $upMStmt33 = $DB_con->prepare($upMQquery33);
        $upMStmt33->bindparam(":taxi_SIdx", $idx);
        $upMStmt33->bindparam(":taxi_PDisply", $taxi_PDisply);
        $upMStmt33->execute();
    } else {
        //주문정보 가져옴
        $viewQuery = "";
        $viewQuery = "SELECT taxi_SIdx, taxi_RIdx, taxi_OrdPrice, taxi_SOrdPoint, taxi_OrdSMemId, taxi_OrdMemId, taxi_OSMemIdx, taxi_OMemIdx FROM TB_ORDER WHERE taxi_OrdNo = :taxi_OrdNo AND taxi_OrdState = '1'  LIMIT 1  ";
        //$viewQuery = "SELECT  taxi_SIdx, taxi_RIdx, taxi_OrdPrice, taxi_OrdSMemId, taxi_OrdMemId FROM TB_ORDER WHERE  taxi_OrdNo = $taxiOrdNo AND taxi_OrdState = '1'  LIMIT 1  ";
        //echo $viewQuery."<BR>";
        //exit;
        $viewStmt = $DB_con->prepare($viewQuery);
        $viewStmt->bindparam(":taxi_OrdNo", $taxiOrdNo);
        $viewStmt->execute();
        $num = $viewStmt->rowCount();

        if ($num < 1) { //아닐경우
            $result = array("result" => "error", "errorMsg" => "이동중인 매칭 건이 없습니다.");
        } else {

            while ($row = $viewStmt->fetch(PDO::FETCH_ASSOC)) {
                $taxiSIdx = trim($row['taxi_SIdx']);                    // 메이커 고유번호
                $taxiRIdx = trim($row['taxi_RIdx']);                    // 투게더 고유번호
                $taxi_OrdPoint = trim($row['taxi_OrdPrice']);            // 쉐어링요금
                $taxi_SOrdPoint = trim($row['taxi_SOrdPoint']);            // 사용포인트 DB상 포인트롤 차감하기 위함 (예 : 보유포인트 0원 으로 카드결제를 할 경우 -4000원이 되기때문에 입력한 포인트로 실제 차감을 위해 처리
                $taxiOrdSMemId = trim($row['taxi_OrdSMemId']);            // 메이커 아이디
                $taxiOrdMemId = trim($row['taxi_OrdMemId']);            // 투게더 아이디
                $taxiOSMemIdx = trim($row['taxi_OSMemIdx']);                // 메이커 고유아이디
                $taxiOMemIdx = trim($row['taxi_OMemIdx']);                // 투게더 고유아이디
            }
            if ($taxi_SOrdPoint == '') {
                $taxiSOrdPoint = $taxi_OrdPoint;
            } else {
                $taxiSOrdPoint = $taxi_SOrdPoint;
            }

            //메이커 회원정보
            $memQuery = "";
            $memQuery = "SELECT mem_NickNm, mem_LV FROM TB_MEMBERS WHERE mem_Id = :mem_Id  LIMIT 1 ";
            $memStmt = $DB_con->prepare($memQuery);
            $memStmt->bindparam(":mem_Id", $taxiOrdSMemId);
            $memStmt->execute();
            $memNum = $memStmt->rowCount();

            if ($memNum < 1) { //아닐경우
            } else {
                while ($memRow = $memStmt->fetch(PDO::FETCH_ASSOC)) {
                    $memNickNm = trim($memRow['mem_NickNm']);            // 메이커 닉네임
                    $memSLv = trim($memRow['mem_LV']);                               // 메이커 등급
                }
            }

            //투게더 회원정보
            //탈퇴 후 재가입 시 이전 탈퇴한 계정이 조회가 되어 맴버요청자 등급 조회 시 0 으로 나옴.. 가입한 상태의 맴버아이디만 조회하게 수정 - (라인72 : AND b_Disply = 'N' 추가) 작업일 : 2019-01-08 작업자 : 황상섭 대리
            $memQuery2 = "";
            $memQuery2 = "SELECT mem_NickNm, mem_LV FROM TB_MEMBERS WHERE mem_Id = :mem_Id AND b_Disply = 'N' LIMIT 1 ";
            $memStmt2 = $DB_con->prepare($memQuery2);
            $memStmt2->bindparam(":mem_Id", $taxiOrdMemId);
            $memStmt2->execute();
            $memNum2 = $memStmt2->rowCount();

            if ($memNum2 < 1) { //아닐경우
            } else {
                while ($memRow2 = $memStmt2->fetch(PDO::FETCH_ASSOC)) {
                    $memRNickNm = trim($memRow2['mem_NickNm']);              // 투게더 닉네임
                    $memRLV = trim($memRow2['mem_LV']);                                 // 투게더 등급
                }
            }

            //회원등급 포인트
            if ($memRLV != "") {
                $mpQuery = "";
                $mpQuery = "SELECT memDc FROM TB_MEMBER_LEVEL WHERE memLv = :memLv  LIMIT 1 ";
                $mpStmt = $DB_con->prepare($mpQuery);
                $mpStmt->bindparam(":memLv", $memRLV);
                $mpStmt->execute();
                $mpNum = $mpStmt->rowCount();

                if ($mpNum < 1) { //아닐경우
                } else {
                    while ($mpRow = $mpStmt->fetch(PDO::FETCH_ASSOC)) {
                        $levDc = trim($mpRow['memDc']);             // 포인트
                    }
                }
            } else {  //관리자 기준
                $levDc = "10";  //10% 차감
            }

            $totalDc = $levDc;
            $taxiOrdPoint = $taxi_OrdPoint - floor($taxi_OrdPoint * ($totalDc / 100));
            $taxiPoint = $taxi_OrdPoint - $taxiOrdPoint;

            //histoy 저장
            $reg_Date = DU_TIME_YMDHIS;           //등록일

            //양도처리 내역 저장
            //메이커 포인트내역
            if ($taxiOrdSMemId <> "") {

                $taxi_Sign = "0"; // +기호
                $taxi_PState = "0"; //매칭

                $taxi_Memo = DU_TIME_YMDHIS . '
 투게더(' . $memRNickNm . ') 님이  요청 포인트 총 ' . number_format($taxi_OrdPoint) . '원에서 수수료 ' . $totalDc . '%를 차감한 요청 포인트 ' . number_format($taxiOrdPoint) . '원를 적립' . "";
                //echo $taxi_Memo."<BR>";
                //exit;

                //메이커 포인트내역 등록 여부 체크
                $cntQuery = "";
                $cntQuery = "SELECT count(idx)  AS num FROM TB_POINT_HISTORY WHERE taxi_SIdx = :taxi_SIdx AND taxi_RIdx = :taxi_RIdx AND taxi_OrdNo = :taxi_OrdNo AND taxi_MemId = :taxi_MemId ";
                //$cntQuery = "SELECT count(idx)  AS num FROM TB_POINT_HISTORY WHERE taxi_SIdx = $taxiSIdx AND taxi_RIdx = $taxiRIdx AND taxi_OrdNo = $taxiOrdNo AND taxi_MemId = $taxiOrdSMemId ";
                $cntStmt = $DB_con->prepare($cntQuery);
                $cntStmt->bindparam(":taxi_SIdx", $taxiSIdx);
                $cntStmt->bindparam(":taxi_RIdx", $taxiRIdx);
                $cntStmt->bindparam(":taxi_OrdNo", $taxiOrdNo);
                $cntStmt->bindparam(":taxi_MemId", $taxiOrdSMemId);
                $cntStmt->execute();
                $cntRow = $cntStmt->fetch(PDO::FETCH_ASSOC);
                $totalCnt = $cntRow['num'];

                if ($totalCnt == "") {
                    $totalCnt = "0";
                } else {
                    $totalCnt =  $totalCnt;
                }

                //포인트 내역 중복 등록을 맞기 위해서 체크 함
                if ($totalCnt < 1) {
                    $insQuery = "INSERT INTO TB_POINT_HISTORY (taxi_SIdx, taxi_RIdx, taxi_OrdNo, taxi_MemId, taxi_OrdPoint, taxi_Memo, taxi_Sign, taxi_PState, reg_Date) VALUES (:taxi_SIdx, :taxi_RIdx, :taxi_OrdNo, :taxi_MemId, :taxi_OrdPoint, :taxi_Memo, :taxi_Sign, :taxi_PState, :reg_Date)";
                    //echo $insQuery."<BR>";
                    //exit;
                    $stmt = $DB_con->prepare($insQuery);
                    $stmt->bindParam("taxi_SIdx", $taxiSIdx);
                    $stmt->bindParam("taxi_RIdx", $taxiRIdx);
                    $stmt->bindParam("taxi_OrdNo", $taxiOrdNo);
                    $stmt->bindParam("taxi_MemId", $taxiOrdSMemId);
                    $stmt->bindParam("taxi_OrdPoint", $taxiOrdPoint);
                    $stmt->bindParam("taxi_Memo", $taxi_Memo);
                    $stmt->bindParam("taxi_Sign", $taxi_Sign);
                    $stmt->bindParam("taxi_PState", $taxi_PState);
                    $stmt->bindParam("reg_Date", $reg_Date);
                    $stmt->execute();
                    $DB_con->lastInsertId();

                    //메이커 포인트, 매칭성공횟수 내역 조회
                    $pointQuery = "";
                    $pointQuery = "SELECT mem_Point, mem_MatCnt FROM TB_MEMBERS_ETC WHERE mem_Id = :mem_Id  LIMIT 1 ";
                    $pointStmt = $DB_con->prepare($pointQuery);
                    $pointStmt->bindparam(":mem_Id", $taxiOrdSMemId);
                    $pointStmt->execute();
                    $pointNum = $pointStmt->rowCount();

                    if ($pointNum < 1) { //아닐경우
                    } else {
                        while ($pointRow = $pointStmt->fetch(PDO::FETCH_ASSOC)) {
                            $sumPoint = trim($pointRow['mem_Point']);    //포인트
                            $memMatCnt = trim($pointRow['mem_MatCnt']);  //매칭성공횟수
                        }
                    }

                    //총포인트 조회
                    if (!$sumPoint > 0) {
                        $sumPoint = "0";
                    } else { //포인트가 있을 경우
                        $sumPoint =  $sumPoint;
                    }

                    //매칭성공횟수
                    $memMatCnt = $memMatCnt + 1;

                    //양도금액 포함 포인트 (생성장의 경우는 적립
                    $totPoint = $sumPoint + $taxiOrdPoint;        //현재포인트 = 보유포인트 + 쉐어링요금에서 수수료를 차감한 금액을 더해줌 

                    //매칭횟수
                    $totMatCnt = $memMatCnt;


                    //포인트 금액 변경
                    $upmPQquery = "UPDATE TB_MEMBERS_ETC SET mem_MatCnt = :mem_MatCnt, mem_Point = :mem_Point WHERE mem_Id = :mem_Id  LIMIT 1";
                    //$upmPQquery = "UPDATE TB_MEMBERS_ETC SET mem_MatCnt = $totMatCnt, mem_Point = $totPoint WHERE mem_Id = $taxiOrdSMemId  LIMIT 1";
                    //echo $upmPQquery."<BR>";
                    //exit;
                    $upmPStmt = $DB_con->prepare($upmPQquery);
                    $upmPStmt->bindparam(":mem_MatCnt", $totMatCnt);
                    $upmPStmt->bindparam(":mem_Point", $totPoint);
                    $upmPStmt->bindparam(":mem_Id", $taxiOrdSMemId);
                    $upmPStmt->execute();
                }
            }


            //투게더 포인트내역
            if ($taxiOrdMemId <> "") {

                $taxi_Sign = "1"; // -기호
                $taxi_PState = "0"; //매칭

                $taxi_CMemo = DU_TIME_YMDHIS . '
 메이커(' . $memNickNm . ')님에게 요청 포인트  ' . number_format($taxi_OrdPoint) . '원를 양도 처리함' . "";

                //투게더 포인트내역 등록 여부 체크
                $cntMQuery = "";
                $cntMQuery = "SELECT count(idx)  AS num FROM TB_POINT_HISTORY WHERE taxi_SIdx = :taxi_SIdx AND taxi_RIdx = :taxi_RIdx AND taxi_OrdNo = :taxi_OrdNo AND taxi_MemId = :taxi_MemId ";
                $cntMStmt = $DB_con->prepare($cntMQuery);
                $cntMStmt->bindparam(":taxi_SIdx", $taxiSIdx);
                $cntMStmt->bindparam(":taxi_RIdx", $taxiRIdx);
                $cntMStmt->bindparam(":taxi_OrdNo", $taxiOrdNo);
                $cntMStmt->bindparam(":taxi_MemId", $taxiOrdMemId);
                $cntMStmt->execute();
                $cntRow = $cntMStmt->fetch(PDO::FETCH_ASSOC);
                $totalCnt = $cntRow['num'];

                if ($totalCnt == "") {
                    $totalCnt = "0";
                } else {
                    $totalCnt =  $totalCnt;
                }

                //포인트 내역 중복 등록을 맞기 위해서 체크 함
                if ($totalCnt < 1) {
                    $insQuery = "INSERT INTO TB_POINT_HISTORY (taxi_SIdx, taxi_RIdx, taxi_OrdNo, taxi_MemId, taxi_OrdPoint, taxi_Memo, taxi_Sign, taxi_PState, reg_Date) VALUES (:taxi_SIdx, :taxi_RIdx, :taxi_OrdNo, :taxi_MemId, :taxi_OrdPoint, :taxi_Memo, :taxi_Sign, :taxi_PState, :reg_Date)";
                    //echo $insQuery."<BR>";
                    //exit;
                    $mstmt = $DB_con->prepare($insQuery);
                    $mstmt->bindParam("taxi_SIdx", $taxiSIdx);
                    $mstmt->bindParam("taxi_RIdx", $taxiRIdx);
                    $mstmt->bindParam("taxi_OrdNo", $taxiOrdNo);
                    $mstmt->bindParam("taxi_MemId", $taxiOrdMemId);
                    $mstmt->bindParam("taxi_OrdPoint", $taxi_OrdPoint);
                    $mstmt->bindParam("taxi_Memo", $taxi_CMemo);
                    $mstmt->bindParam("taxi_Sign", $taxi_Sign);
                    $mstmt->bindParam("taxi_PState", $taxi_PState);
                    $mstmt->bindParam("reg_Date", $reg_Date);
                    $mstmt->execute();
                    $DB_con->lastInsertId();

                    //투게더 포인트, 매칭성공횟수 내역 조회
                    $pointmQuery = "";
                    $pointmQuery = "SELECT mem_Point, mem_MatCnt FROM TB_MEMBERS_ETC WHERE mem_Id = :mem_Id  LIMIT 1 ";
                    $pointmStmt = $DB_con->prepare($pointmQuery);
                    $pointmStmt->bindparam(":mem_Id", $taxiOrdMemId);
                    $pointmStmt->execute();
                    $pointmNum = $pointmStmt->rowCount();

                    if ($pointmNum < 1) { //아닐경우
                    } else {
                        while ($pointmRow = $pointmStmt->fetch(PDO::FETCH_ASSOC)) {
                            $sumPoint = trim($pointmRow['mem_Point']);    //포인트
                            $membMatCnt = trim($pointmRow['mem_MatCnt']);  //매칭성공횟수
                        }
                    }

                    //총포인트 조회
                    if (!$sumPoint > 0) {
                        $sumPoint = "0";
                    } else { //포인트가 있을 경우
                        $sumPoint =  $sumPoint;
                    }

                    //매칭성공횟수
                    if (!$membMatCnt > 0) {
                        $membMatCnt = "0";
                    } else { //포인트가 있을 경우
                        $membMatCnt =  $membMatCnt;
                    }

                    //매칭횟수
                    $mtotMatCnt = $membMatCnt + 1;

                    //양도금액 포함 포인트(요청자의 경우 차감 으로 -)
                    $totPoint = $sumPoint - $taxiSOrdPoint; // 현재포인트 = 보유포인트 - 사용포인트

                    //매칭 횟수, 포인트 변경
                    $upmsPQquery = "UPDATE TB_MEMBERS_ETC SET mem_MatCnt = :mem_MatCnt, mem_Point = :mem_Point WHERE mem_Id = :mem_Id  LIMIT 1";
                    //$upmsPQquery = "UPDATE TB_MEMBERS_ETC SET mem_MatCnt = $mtotMatCnt WHERE mem_Id = $taxiOrdMemId  LIMIT 1";
                    //echo $upmsPQquery."<BR>";
                    //exit;
                    $upmsPStmt = $DB_con->prepare($upmsPQquery);
                    $upmsPStmt->bindparam(":mem_MatCnt", $mtotMatCnt);
                    $upmsPStmt->bindparam(":mem_Point", $totPoint);
                    $upmsPStmt->bindparam(":mem_Id", $taxiOrdMemId);
                    $upmsPStmt->execute();
                }
            }

            $taxi_SMemo = DU_TIME_YMDHIS . '
 투게더(' . $memRNickNm . ') 님이 메이커(' . $memNickNm . ')님에게 요청 포인트 총 ' . number_format($taxi_OrdPoint) . '원에서 수수료 ' . $totalDc . '%를 차감한 요청 포인트 ' . number_format($taxiOrdPoint) . '원에서 수수료 ' . $totalDc . '%인 수익 ' . number_format($taxiPoint) . '원를 적립' . "";

            //본사 수익 내역 등록 여부 체크
            $cntPQuery = "";
            $cntPQuery = "SELECT count(idx)  AS num FROM TB_PROFIT_POINT WHERE taxi_SIdx = :taxi_SIdx AND taxi_RIdx = :taxi_RIdx AND taxi_OrdNo = :taxi_OrdNo AND taxi_MemId = :taxi_MemId  AND taxi_OMemId = :taxi_OMemId ";
            $cntPStmt = $DB_con->prepare($cntPQuery);
            $cntPStmt->bindparam(":taxi_SIdx", $taxiSIdx);
            $cntPStmt->bindparam(":taxi_RIdx", $taxiRIdx);
            $cntPStmt->bindparam(":taxi_OrdNo", $taxiOrdNo);
            $cntPStmt->bindparam(":taxi_MemId", $taxiOrdSMemId);
            $cntPStmt->bindparam(":taxi_OMemId", $taxiOrdMemId);
            $cntPStmt->execute();
            $cntPRow = $cntPStmt->fetch(PDO::FETCH_ASSOC);
            $totalCnt = $cntPRow['num'];

            if ($totalCnt == "") {
                $totalCnt = "0";
            } else {
                $totalCnt =  $totalCnt;
            }

            //본사 수익 내역 중복 등록을 맞기 위해서 체크 함
            if ($totalCnt < 1) {

                //본사 수익 내역 저장
                $insQuery = "INSERT INTO TB_PROFIT_POINT (taxi_SIdx, taxi_RIdx, taxi_OrdNo, taxi_MemId, taxi_OMemId, taxi_OrdSPoint, taxi_OrdTPoint, taxi_OrdMPoint, taxi_Memo, reg_Date)
                 VALUES (:taxi_SIdx, :taxi_RIdx, :taxi_OrdNo, :taxi_MemId, :taxi_OMemId, :taxi_OrdSPoint, :taxi_OrdTPoint, :taxi_OrdMPoint, :taxi_Memo, :reg_Date)";
                //echo $insQuery."<BR>";
                //exit;
                $pstmt = $DB_con->prepare($insQuery);
                $pstmt->bindParam("taxi_SIdx", $taxiSIdx);
                $pstmt->bindParam("taxi_RIdx", $taxiRIdx);
                $pstmt->bindParam("taxi_OrdNo", $taxiOrdNo);
                $pstmt->bindParam("taxi_MemId", $taxiOrdSMemId);
                $pstmt->bindParam("taxi_OMemId", $taxiOrdMemId);
                $pstmt->bindParam("taxi_OrdSPoint", $taxiPoint);
                $pstmt->bindParam("taxi_OrdTPoint", $taxi_OrdPoint);
                $pstmt->bindParam("taxi_OrdMPoint", $taxiOrdPoint);
                $pstmt->bindParam("taxi_Memo", $taxi_SMemo);
                $pstmt->bindParam("reg_Date", $reg_Date);
                $pstmt->execute();
                $DB_con->lastInsertId();

                //매칭생성 완료 상태로 변경
                $upPQquery = "UPDATE TB_STAXISHARING SET taxi_State = '10' WHERE idx = :idx  LIMIT 1";
                $upPStmt = $DB_con->prepare($upPQquery);
                $upPStmt->bindparam(":idx", $taxiSIdx);
                $upPStmt->execute();

                //투게더 완료 날짜 업데이트
                $upMQquery2 = "UPDATE TB_RTAXISHARING_INFO SET reg_YDate = :reg_YDate WHERE taxi_RIdx = :taxi_RIdx AND taxi_RMemId = :taxi_RMemId LIMIT 1";
                $upMStmt2 = $DB_con->prepare($upMQquery2);
                $upMStmt2->bindparam(":reg_YDate", $reg_Date);
                $upMStmt2->bindparam(":taxi_RIdx", $taxiRIdx);
                $upMStmt2->bindparam(":taxi_RMemId", $taxiOrdMemId);
                $upMStmt2->execute();

                //투게더 완료 상태로 변경
                $upMQquery = "UPDATE TB_RTAXISHARING SET taxi_RState = '10' WHERE idx = :idx AND taxi_RMemId = :taxi_RMemId LIMIT 1";
                $upMStmt = $DB_con->prepare($upMQquery);
                $upMStmt->bindparam(":idx", $taxiRIdx);
                $upMStmt->bindparam(":taxi_RMemId", $taxiOrdMemId);
                $upMStmt->execute();

                //주문서 신청 완료 상태 변경
                $upOquery = "UPDATE TB_ORDER SET taxi_OrdState = '2'  WHERE taxi_OrdNo = :taxi_OrdNo  LIMIT 1";
                $upOStmt = $DB_con->prepare($upOquery);
                $upOStmt->bindparam(":taxi_OrdNo", $taxiOrdNo);
                $upOStmt->execute();
            }
        }

        $result = array("result" => "success");
    }

    //생성자 푸시
    $mem_NToken = memMatchTokenInfo($taxiOSMemIdx);

    $chkState = "10";  //거래완료
    if ($taxi_PDisply == 'Y') {
        $nmsg = "거래취소 요청이 거절되었습니다. 포인트가 양도되었습니다.";
    } else if ($taxi_PDisply == 'N') {
        $nmsg = "거래취소 요청이 거절되었습니다. 포인트는 양도대기 중 입니다.";
    }
    $ntitle = "";
    $nmsg = $nmsg;

    foreach ($mem_NToken as $k => $v) {
        $ntokens = $mem_NToken[$k];
        $ninputData = array("title" => $ntitle, "msg" => $nmsg, "state" => $chkState);
        $nresult = send_Push($ntokens, $ninputData);
    }

    //요청자 푸시
    $mem_RToken = memMatchTokenInfo($taxiOMemIdx);

    $rchkState = "10";  //거래완료
    if ($taxi_PDisply == 'Y') {
        $rmsg = "거래취소 요청이 거절되었습니다. 포인트가 양도되었습니다.";
    } else if ($taxi_PDisply == 'N') {
        $rmsg = "거래취소 요청이 거절되었습니다. 포인트는 양도대기 중 입니다.";
    }
    $rtitle = "";
    $rmsg = $rmsg;

    foreach ($mem_RToken as $k2 => $v2) {
        $rtokens = $mem_RToken[$k2];
        $rinputData = array("title" => $rtitle, "msg" => $rmsg, "state" => $rchkState);
        $rResult = send_Push($rtokens, $ninputData);
    }

    $result['success']    = true;
    $result['Msg']    = "요청된 취소건을 거절처리 하였습니다.";
} else {
    $result['success']    = false;
    $result['Msg']    = "#1. 승인여부값이 없습니다. 확인 후 다시 시도해주세요.";
}
dbClose($DB_con);
$orderStmt = null;
$upMStmt11 = null;
$upMStmt22 = null;
$upMStmt33 = null;
$upMStmt44 = null;
$viewStmt = null;
$memStmt = null;
$memStmt2 = null;
$minfoetmt = null;
$mpStmt = null;
$cntStmt = null;
$stmt = null;
$pointStmt = null;
$cntmStmt = null;
$smtmt = null;
$cntmStmt2 = null;
$smtmt2 = null;
$upmPStmt = null;
$upLvStmt = null;
$cntMStmt = null;
$mstmt = null;
$pointmStmt = null;
$cntrStmt = null;
$srtmt = null;
$cntrStmt2 = null;
$srtmt2 = null;
$upmsPStmt = null;
$cntPStmt = null;
$pstmt = null;
$upLvRStmt = null;
$upPStmt = null;
$upMStmt2 = null;
$upMStmt = null;
$upOStmt = null;
$nSidStmt = null;
$rSidStmt = null;

//echo str_replace('\\/', '/', json_encode($result, JSON_UNESCAPED_UNICODE));
echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
