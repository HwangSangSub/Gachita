<?php
/*======================================================================================================================

* 프로그램				:  매칭 요청자 만남완료 확인 이후 카드결제 (만남완료 확인 이후 결제방식을 카드결제 선택시 카드결제 진행[즉시])
* 페이지 설명			:  매칭 요청자 만남완료 확인 이후 카드결제 (만남완료 확인 이후 결제방식을 카드결제 선택시 카드결제 진행[즉시])
* 파일명              :  taxiSharingRPayProc.php

========================================================================================================================*/
include "../lib/common.php";
include "../lib/functionDB.php";  //공통 db함수
include "./lib/TPAY.LIB.php";  //공통 db함수
include "./lib/tpay_proc.php"; // 아임포트 함수
//require_once dirname(__FILE__).'/TPAY.LIB.php';  //tpay lib


$idx  = trim($idx);                // 투게더 고유번호 idx
$mem_Id  = trim($memId);        // 투게더 아이디

$DB_con = db1();

$mem_Idx = memIdxInfo($mem_Id);   //회원 주아이디    
$sMemNm = memNickInfo($mem_Id);   //요청자 닉네임
$ordTitNm = $sMemNm . "님! 합승 노선 요금";


$cFquery = "SELECT idx, con_OrdFCnt FROM TB_CONFIG LIMIT 1";
$cFstmt = $DB_con->prepare($cFquery);
$cFstmt->execute();

$cFrow = $cFstmt->fetch(PDO::FETCH_ASSOC);

$con_OrdFCnt = (int)$cFrow['con_OrdFCnt'] + 1;    //관리자가 설정한 재시도 가능 횟수

$viewQuery = "SELECT taxi_SIdx, taxi_MemId, taxi_MemIdx, taxi_RMemId, taxi_RMemIdx, taxi_RTPrice, taxi_RUPoint, taxi_RChk FROM TB_RTAXISHARING WHERE idx = :idx AND taxi_RMemIdx = :taxi_RMemIdx AND taxi_RMemId = :taxi_RMemId LIMIT 1  ";
$viewStmt = $DB_con->prepare($viewQuery);
$viewStmt->bindparam(":idx", $idx);
$viewStmt->bindparam(":taxi_RMemIdx", $mem_Idx);
$viewStmt->bindparam(":taxi_RMemId", $mem_Id);
$viewStmt->execute();
$num = $viewStmt->rowCount();
//echo $num."<BR>";
//exit;

if ($num < 1) { //아닐경우
    $result = array("result" => false, "errorMsg" => (string)"만남중인 노선이 없습니다.");
} else {
    while ($row = $viewStmt->fetch(PDO::FETCH_ASSOC)) {
        $taxiSIdx = trim($row['taxi_SIdx']);                                // 메이커 고유번호
        $taxiMemId = trim($row['taxi_MemId']);                                // 메이커 아이디
        $taxiMemIdx = trim($row['taxi_MemIdx']);                            // 메이커 고유 아이디
        $taxiRMemId = trim($row['taxi_RMemId']);                            // 메이커 아이디
        $taxiRMemIdx = trim($row['taxi_RMemIdx']);                            // 메이커 고유 아이디
        $taxiRTPrice = trim($row['taxi_RTPrice']);                         // 총예상요금
        $taxiRUPoint = trim($row['taxi_RUPoint']);                         // 매칭요청사 사용포인트금액
        $taxiRChk = trim($row['taxi_RChk']);                               // 만남완료 확인 여부


        if ($taxiRChk <> "") {
            $taxiRChk = $taxiRChk;
        } else {
            $taxiRChk = "N";
        }

        $taxiRChk = (string)$taxiRChk;
    }

    /* 매칭생성 기본정보 */
    $matchQuery = "";
    $matchQuery = "SELECT taxi_Price, taxi_Per, taxi_SChk FROM TB_STAXISHARING WHERE idx = :idx AND taxi_MemIdx = :taxi_MemIdx AND taxi_MemId = :taxi_MemId ";
    //echo $matchQuery."<BR>";
    //exit;
    $matchStmt = $DB_con->prepare($matchQuery);
    $matchStmt->bindparam(":idx", $taxiSIdx);
    $matchStmt->bindparam(":taxi_MemIdx", $taxiMemIdx);
    $matchStmt->bindparam(":taxi_MemId", $taxiMemId);
    $matchStmt->execute();
    $mNum = $matchStmt->rowCount();

    if ($mNum < 1) { //아닐경우
    } else {
        while ($mrow = $matchStmt->fetch(PDO::FETCH_ASSOC)) {
            $taxiPrice = trim($mrow['taxi_Price']);              // 희망쉐어금액(결제금액)
            $taxiPer = trim($mrow['taxi_Per']);              // 생성요금 %
            $taxiSChk = trim($mrow['taxi_SChk']);         // 만남완료 확인 여부

            if ($taxiSChk <> "") {
                $taxiSChk = $taxiSChk;
            } else {
                $taxiSChk = "N";
            }

            $taxiSChk = (string)$taxiSChk;
        }
    }

    $taxi_OrdMemId = $mem_Id;                                                        // 투게더 아이디

    //투게더 회원정보
    $memQuery = "";
    $memQuery = "SELECT mem_NickNm, mem_Tel FROM TB_MEMBERS WHERE mem_Id = :mem_Id  LIMIT 1 ";
    $memStmt = $DB_con->prepare($memQuery);
    $memStmt->bindparam(":mem_Id", $mem_Id);
    $memStmt->execute();
    $memNum = $memStmt->rowCount();

    if ($memNum < 1) { //아닐경우
    } else {
        while ($memRow = $memStmt->fetch(PDO::FETCH_ASSOC)) {
            $taxi_OrdNickNm = trim($memRow['mem_NickNm']);              // 닉네임
            $taxi_OrdTel = trim($memRow['mem_Tel']);                                 // 연락처
        }
    }


    //매칭생성 기타정보
    $infoQuery = "";
    $infoQuery = "SELECT taxi_Type, taxi_Route FROM TB_STAXISHARING_INFO WHERE taxi_Idx = :taxi_Idx AND taxi_MemIdx = :taxi_MemIdx AND taxi_MemId = :taxi_MemId LIMIT 1 ";
    //echo $infoQuery."<BR>";
    //exit;
    $infoStmt = $DB_con->prepare($infoQuery);
    $infoStmt->bindparam(":taxi_Idx", $taxiSIdx);
    $infoStmt->bindparam(":taxi_MemIdx", $taxiMemIdx);
    $infoStmt->bindparam(":taxi_MemId", $taxiMemId);
    $infoStmt->execute();
    $infoNum = $infoStmt->rowCount();
    //echo $infoNum."<BR>";

    if ($infoNum < 1) { //아닐경우
    } else {
        while ($infoRow = $infoStmt->fetch(PDO::FETCH_ASSOC)) {
            $taxi_OrdMType =  trim($infoRow['taxi_Type']);        //출발타입 ( 0: 바로출발, 1: 예약출발)
            $taxiRoute =  trim($infoRow['taxi_Route']);                    // 경유가능여부 ( 0: 경유가능, 1: 경유불가)
        }
    }
    $taxi_OrdPrice = $taxiPrice;                         // 희망쉐어금액				

    /*
    *	결제 부분 
    *
    */

    if ((int)$taxiRUPoint < (int)$taxi_OrdPrice) { //포인트사용금액이 희망 쉐어금액보다 낮으면 포인트 + 카드결제
        $card_Price = (int)$taxi_OrdPrice - (int)$taxiRUPoint;  // 카드결제금액
        //생성자 지도정보
        $mapQuery = "";
        $mapQuery = "SELECT taxi_Sdong, taxi_Edong, taxi_Saddr, taxi_Eaddr FROM TB_STAXISHARING_MAP WHERE taxi_Idx = :taxi_Idx AND taxi_MemIdx = :taxi_MemIdx AND taxi_MemId = :taxi_MemId  LIMIT 1 ";
        //echo $mapQuery."<BR>";
        //exit;
        $mapStmt = $DB_con->prepare($mapQuery);
        $mapStmt->bindparam(":taxi_Idx", $taxiSIdx);
        $mapStmt->bindparam(":taxi_MemIdx", $taxiMemIdx);
        $mapStmt->bindparam(":taxi_MemId", $taxiMemId);
        $mapStmt->execute();
        $mapNum = $mapStmt->rowCount();
        //echo $mapNum."<BR>";

        if ($mapNum < 1) { //아닐경우
        } else {
            while ($mapRow = $mapStmt->fetch(PDO::FETCH_ASSOC)) {
                $taxiSdong = trim($mapRow['taxi_Sdong']);                      //  출발지 동명
                $taxiEdong = trim($mapRow['taxi_Edong']);                      //  도착지 동명
                $taxi_OrdSaddr  = trim($mapRow['taxi_Saddr']);              //  출발지 주소
                $taxi_OrdEaddr = trim($mapRow['taxi_Eaddr']);              //  도착지 주소
            }
        }
        $taxi_OrdTit = $taxiSdong . "▶" . $taxiEdong;
        $taxi_OrdRaddr = "";


        //주문 정보 저장 여부
        $chkOrdQuery = "SELECT idx, taxi_OrdNo, taxi_OrdState, taxi_OrdFCnt from TB_ORDER WHERE taxi_SIdx = :taxi_SIdx AND taxi_RIdx = :taxi_RIdx";
        $chkOrdStmt = $DB_con->prepare($chkOrdQuery);
        $chkOrdStmt->bindparam("taxi_SIdx", $taxiSIdx);
        $chkOrdStmt->bindParam("taxi_RIdx", $idx);
        $chkOrdStmt->execute();
        $chkOrdNum = $chkOrdStmt->rowCount();

        if ($chkOrdNum  < 1) {  //대기모드
            $taxi_OrdNo = date('YmdHis', time()) . str_pad((int)(microtime() * 100), 2, "0", STR_PAD_LEFT);  //주문번호

            if ((int)$taxiRUPoint == 0) {
                $taxi_OrdType = '1';    // 사용 포인트가 0 인경우 순수 카드결제
            } else {
                $taxi_OrdType = '0';    // 사용 포인트가 0원 이상인 경우 포인트 + 카드 결제
            }

            //카드결제 추후 카드결제 또는 보유캐쉬 결제 선택하여 값 받아 들이게 설정해야함 C:\Users\USER\Desktop\택시킹업무파일택시킹 결제단계에 작업해야 할 부분.txt파일 확인해볼것 에를들어 (1: 카드결제, 2: 보유캐쉬결제 // 1의 값은 변경하지 말 것)

            $taxi_OrdState = "0"; //결제완료 (추후 결제 할 경우 "0"으로 수정 후 진행 해야함 // '결제 상태 여부 (0: 접수, 1: 결제완료, 2: 양도완료, 3: 취소, 4: 거래취소확인, 5:거래완료확인)' 작업일 : 2019-01-08 

            //주문정보 저장
            $reg_Date = DU_TIME_YMDHIS;           //등록일


            $insQuery = "INSERT INTO TB_ORDER (taxi_SIdx, taxi_RIdx, taxi_OrdNo, taxi_OrdTit, taxi_OSMemIdx, taxi_OrdSMemId, taxi_OMemIdx, taxi_OrdMemId, taxi_OrdSaddr, taxi_OrdEaddr, taxi_OrdRaddr, taxi_OrdNickNm, taxi_OrdTel, taxi_OrdPrice, taxi_OrdPoint, taxi_OrdMType, taxi_OrdType, taxi_OrdState, taxi_OrdFCnt, reg_Date)
				SELECT :taxi_SIdx, :taxi_RIdx, :taxi_OrdNo, :taxi_OrdTit, :taxi_OSMemIdx, :taxi_OrdSMemId, :taxi_OMemIdx, :taxi_OrdMemId, :taxi_OrdSaddr, :taxi_OrdEaddr, :taxi_OrdRaddr, :taxi_OrdNickNm, :taxi_OrdTel, :taxi_OrdPrice, :taxi_OrdPoint, :taxi_OrdMType, :taxi_OrdType, :taxi_OrdState, 0, :reg_Date FROM DUAL";
            $insQuery .= " WHERE NOT EXISTS (SELECT * FROM TB_ORDER WHERE taxi_OrdNo = :taxi_OrdNo);";
            //echo $insQuery."<BR>";
            //exit;
            $stmt = $DB_con->prepare($insQuery);
            $stmt->bindParam("taxi_SIdx", $taxiSIdx);
            $stmt->bindParam("taxi_RIdx", $idx);
            $stmt->bindParam("taxi_OrdNo", $taxi_OrdNo);
            $stmt->bindParam("taxi_OrdTit", $taxi_OrdTit);
            $stmt->bindParam("taxi_OSMemIdx", $taxiMemIdx);
            $stmt->bindParam("taxi_OrdSMemId", $taxiMemId);
            $stmt->bindParam("taxi_OMemIdx", $taxiRMemIdx);
            $stmt->bindParam("taxi_OrdMemId", $taxiRMemId);
            $stmt->bindParam("taxi_OrdSaddr", $taxi_OrdSaddr);
            $stmt->bindParam("taxi_OrdEaddr", $taxi_OrdEaddr);
            $stmt->bindParam("taxi_OrdRaddr", $taxi_OrdRaddr);
            $stmt->bindParam("taxi_OrdNickNm", $taxi_OrdNickNm);
            $stmt->bindParam("taxi_OrdTel", $taxi_OrdTel);
            $stmt->bindParam("taxi_OrdPrice", $card_Price);
            $stmt->bindParam("taxi_OrdPoint", $taxiRUPoint);
            $stmt->bindParam("taxi_OrdMType", $taxi_OrdMType);
            $stmt->bindParam("taxi_OrdType", $taxi_OrdType);
            $stmt->bindParam("taxi_OrdState", $taxi_OrdState);
            $stmt->bindParam("reg_Date", $reg_Date);
            $stmt->execute();
            $DB_con->lastInsertId();
            //결제정보만 입력 하였으므로 이동중상태로 변경 하면 안됨(결제 완료 후 처리 해야함)

            // AND taxi_OrdState = 0 준비중상태 추가할 것
            $viewQuery2 = "";
            $viewQuery2 = "SELECT taxi_OrdNo, taxi_OrdPrice, taxi_OrdFCnt FROM TB_ORDER WHERE taxi_RIdx = :taxi_RIdx AND taxi_OrdMemId = :taxi_OrdMemId AND taxi_OrdState = 0 LIMIT 1; ";
            $viewStmt2 = $DB_con->prepare($viewQuery2);
            $viewStmt2->bindparam(":taxi_RIdx", $idx);
            $viewStmt2->bindparam(":taxi_OrdMemId", $memId);
            $viewStmt2->execute();
            $num2 = $viewStmt2->rowCount();

            if ($num2 < 1) { //아닐경우
                $result = array("result" => false, "errorMsg" => "#1 : 요청 주문이 없습니다.");
            } else {

                while ($row2 = $viewStmt2->fetch(PDO::FETCH_ASSOC)) {
                    $taxi_OrdNo = trim($row2['taxi_OrdNo']);                                // 주문번호
                    $taxi_OrdPrice = trim($row2['taxi_OrdPrice']);                          // 실제카드결제요금
                    $taxi_OrdFCnt = trim($row2['taxi_OrdFCnt']);                            // 결제 실패 횟수
                }
                // 토큰값 생성
                $access_token = get_Token_PayForm('https://api.iamport.kr/users/getToken', array("imp_key" => $imp_key, "imp_secret" => $imp_secret));
                //메세지
                if ($access_token == '') {
                    $result = array("result" => false, "errorMsg" => "#2. " . $accesstoken_message);
                } else if ($access_token != '') {
                    //카드결제의 경우에는 요청때 등록한 카드로 결제 진행
                    $card_Sel_Query = "SELECT pc.idx AS cardIdx, pc.card_Number4 FROM TB_RTAXISHARING AS rs INNER JOIN TB_PAYMENT_CARD AS pc ON rs.taxi_CardIdx = pc.idx WHERE rs.idx = :idx";
                    $card_Sel_Stmt = $DB_con->prepare($card_Sel_Query);
                    $card_Sel_Stmt->bindparam(":idx", $idx);
                    $card_Sel_Stmt->execute();
                    $card_Sel_Row = $card_Sel_Stmt->fetch(PDO::FETCH_ASSOC);
                    $cardIdx = trim($card_Sel_Row['cardIdx']);                                // 카드 고유번호
                    $card_Number4 = trim($card_Sel_Row['card_Number4']);                                // 카드 끝 4자리

                    $res = pay_Order_PayForm('https://api.iamport.kr/subscribe/payments/again', array("customer_uid" => $mem_Idx . $card_Number4, "merchant_uid" => $taxi_OrdNo, "amount" => $taxi_OrdPrice, "name" => $ordTitNm), $access_token);

                    $code = $res['code'];                            //성공여부
                    $message = $res['message'];                        //메세지
                    $status = $res['response']['status'];            //결제상태
                    $fail_reason = $res['response']['fail_reason'];    //실패시 메세지
                    if ($code == 0 && $status != 'paid') {
                        $taxiOrdFCnt = (int)$taxi_OrdFCnt + 1;
                        //주문 재시도 처리
                        $upMQquery2 = "UPDATE TB_ORDER SET taxi_OrdFCnt = :taxi_OrdFCnt WHERE taxi_RIdx = :taxi_RIdx AND taxi_OrdMemId = :taxi_OrdMemId LIMIT 1";
                        $upMStmt2 = $DB_con->prepare($upMQquery2);
                        $upMStmt2->bindparam(":taxi_RIdx", $idx);
                        $upMStmt2->bindparam(":taxi_OrdMemId", $mem_Id);
                        $upMStmt2->bindparam(":taxi_OrdFCnt", $taxiOrdFCnt);
                        $upMStmt2->execute();

                        $OrdOCnt = (int)$con_OrdFCnt - (int)$taxiOrdFCnt;

                        $result = array("result" => false, "OrdOCnt" => (int)$OrdOCnt);
                    } else if ($code == 0 && $status == 'paid') { //if ($taxi_OrdState == "1") { //결제완료
                        //주문
                        $upMQquery2 = "UPDATE TB_ORDER SET taxi_OrdState = 1 WHERE taxi_RIdx = :taxi_RIdx AND taxi_OrdMemId = :taxi_OrdMemId LIMIT 1";
                        $upMStmt2 = $DB_con->prepare($upMQquery2);
                        $upMStmt2->bindparam(":taxi_RIdx", $idx);
                        $upMStmt2->bindparam(":taxi_OrdMemId", $mem_Id);
                        $upMStmt2->execute();

                        //투게더 이동중 날짜 업데이트
                        $upMQquery3 = "UPDATE TB_RTAXISHARING_INFO SET reg_EDate = :reg_EDate WHERE taxi_RIdx = :taxi_RIdx AND taxi_RMemId = :taxi_RMemId LIMIT 1";
                        $upMStmt3 = $DB_con->prepare($upMQquery3);
                        $upMStmt3->bindparam(":reg_EDate", $reg_Date);
                        $upMStmt3->bindparam(":taxi_RIdx", $idx);
                        $upMStmt3->bindparam(":taxi_RMemId", $mem_Id);
                        $upMStmt3->execute();

                        //투게더 이동중 상태로 변경
                        $upPQquery = "UPDATE TB_RTAXISHARING SET taxi_RState = '6', taxi_RChk = 'Y' WHERE idx = :idx AND taxi_RMemId = :taxi_RMemId LIMIT 1";
                        $upPStmt = $DB_con->prepare($upPQquery);
                        $upPStmt->bindparam(":idx", $idx);
                        $upPStmt->bindparam(":taxi_RMemId", $mem_Id);
                        $upPStmt->execute();

                        //메이커 이동중 상태로 변경
                        $upSQquery = "UPDATE TB_STAXISHARING SET taxi_State = '6', taxi_SChk = 'Y' WHERE idx = :idx AND taxi_MemId = :taxi_MemId LIMIT 1";
                        $upSStmt = $DB_con->prepare($upSQquery);
                        $upSStmt->bindparam(":idx", $taxiSIdx);
                        $upSStmt->bindparam(":taxi_MemId", $taxiMemId);
                        $upSStmt->execute();

                        $taxi_Type = "6";

                        //푸시 전송 등록 여부 체크
                        $cntPushQuery = "";
                        $cntPushQuery = "SELECT count(idx) AS num FROM TB_SHARING_PUSH WHERE taxi_Idx = :taxi_Idx AND taxi_Type = :taxi_Type AND taxi_MemIdx = :taxi_MemIdx AND taxi_MemId = :taxi_MemId ";
                        $cntPushStmt = $DB_con->prepare($cntPushQuery);
                        $cntPushStmt->bindParam("taxi_Idx", $taxiSIdx);
                        $cntPushStmt->bindParam("taxi_Type", $taxi_Type);
                        $cntPushStmt->bindParam("taxi_MemIdx", $taxiMemIdx);
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
                            $insPushQuery = "INSERT INTO TB_SHARING_PUSH (taxi_Idx, taxi_Type, taxi_MemIdx, taxi_MemId, reg_Date)
									 VALUES (:taxi_Idx, :taxi_Type, :taxi_MemIdx, :taxi_MemId, :reg_Date)";
                            $stmtPush = $DB_con->prepare($insPushQuery);
                            $stmtPush->bindParam("taxi_Idx", $taxiSIdx);
                            $stmtPush->bindParam("taxi_Type", $taxi_Type);
                            $stmtPush->bindParam("taxi_MemIdx", $taxiMemIdx);
                            $stmtPush->bindParam("taxi_MemId", $taxiMemId);
                            $stmtPush->bindParam("reg_Date", $reg_Date);
                            $stmtPush->execute();
                            /*푸시 관련 시작*/

                            //매칭 생성자 이동중 푸시
                            $mem_MDToken = memMatchTokenInfo($taxiMemIdx);

                            $mDtitle = "";
                            $mDmsg = "노선이 이동중으로 진행됩니다.";
                            $mState = "6";
                            foreach ($mem_MDToken as $k => $v) {
                                $mDtokens = $mem_MDToken[$k];
                                $mDinputData = array("title" => $mDtitle, "msg" => $mDmsg, "state" => $mState);
                                $mDpresult = send_Push($mDtokens, $mDinputData);
                            }

                            /*푸시 관련 끝*/
                        }


                        //푸시 전송 등록 여부 체크
                        $cntPushQuery2 = "";
                        $cntPushQuery2 = "SELECT count(idx) AS num FROM TB_SHARING_PUSH WHERE taxi_Idx = :taxi_Idx AND taxi_Type = :taxi_Type AND taxi_MemIdx = :taxi_MemIdx AND taxi_MemId = :taxi_MemId ";
                        $cntPushStmt2 = $DB_con->prepare($cntPushQuery2);
                        $cntPushStmt2->bindParam("taxi_Idx", $idx);
                        $cntPushStmt2->bindParam("taxi_Type", $taxi_Type);
                        $cntPushStmt2->bindParam("taxi_MemIdx", $taxiRMemIdx);
                        $cntPushStmt2->bindParam("taxi_MemId", $mem_Id);
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
                            $insPushQuery2 = "INSERT INTO TB_SHARING_PUSH (taxi_Idx, taxi_Type, taxi_MemIdx, taxi_MemId, reg_Date)
									 VALUES (:taxi_Idx, :taxi_Type, :taxi_MemIdx, :taxi_MemId, :reg_Date)";
                            $stmtPush2 = $DB_con->prepare($insPushQuery2);
                            $stmtPush2->bindParam("taxi_Idx", $idx);
                            $stmtPush2->bindParam("taxi_Type", $taxi_Type);
                            $stmtPush2->bindParam("taxi_MemIdx", $taxiRMemIdx);
                            $stmtPush2->bindParam("taxi_MemId", $mem_Id);
                            $stmtPush2->bindParam("reg_Date", $reg_Date);
                            $stmtPush2->execute();

                            /*푸시 관련 시작*/

                            //투게더에게 푸시
                            $mem_CToken = memMatchTokenInfo($taxiRMemIdx);

                            $ctitle = "";
                            $cmsg = "노선이 이동중으로 진행됩니다.";
                            $cState = "6";
                            foreach ($mem_CToken as $k => $v) {
                                $ctokens = $mem_CToken[$k];
                                $cinputData = array("title" => $ctitle, "msg" => $cmsg, "state" => $cState);
                                $cpresult = send_Push($ctokens, $cinputData);
                            }
                            /*푸시 관련 끝*/
                        }
                        $result = array("result" => true, "taxiOrdNo" => (string)$taxi_OrdNo);
                    } else {

                        $result = array("result" => false, "errorMsg" => "#4" . $message);
                    }
                } else {
                    $result = array("result" => false, "errorMsg" => "#2 : 인증토큰 발급오류가 있습니다.");
                }
            }
        } else { //대기모드 END
            while ($row = $chkOrdStmt->fetch(PDO::FETCH_ASSOC)) {
                $taxi_OrdNo = $row['taxi_OrdNo'];                                // 주문번호
                $taxi_OrdState = $row['taxi_OrdState'];                            // 결제상태
                $taxi_OrdFCnt = $row['taxi_OrdFCnt'];                            // 결제 실패 횟수
            }
            if ($taxi_OrdFCnt != "") {
                $viewQuery2 = "SELECT taxi_OrdNo, taxi_OrdPrice, taxi_OrdFCnt FROM TB_ORDER WHERE taxi_RIdx = :taxi_RIdx AND taxi_OrdMemId = :taxi_OrdMemId AND taxi_OrdState = 0 LIMIT 1; ";
                $viewStmt2 = $DB_con->prepare($viewQuery2);
                $viewStmt2->bindparam(":taxi_RIdx", $idx);
                $viewStmt2->bindparam(":taxi_OrdMemId", $memId);
                $viewStmt2->execute();
                $viewnum2 = $viewStmt2->rowCount();
                if ($viewnum2 < 1) { //아닐경우
                    $result = array("result" => false, "errorMsg" => "#1 : 요청 주문이 없습니다.");
                } else {
                    while ($row2 = $viewStmt2->fetch(PDO::FETCH_ASSOC)) {
                        $taxi_OrdNo = trim($row2['taxi_OrdNo']);                                // 주문번호
                        $taxi_OrdPrice = trim($row2['taxi_OrdPrice']);                        // 쉐어링요금
                        $taxi_OrdFCnt = trim($row2['taxi_OrdFCnt']);                            // 결제 실패 횟수
                    }
                    //카드결제의 경우에는 요청때 등록한 카드로 결제 진행
                    $card_Sel_Query = "SELECT pc.idx AS cardIdx, pc.card_Number4 FROM TB_RTAXISHARING AS rs INNER JOIN TB_PAYMENT_CARD AS pc ON rs.taxi_CardIdx = pc.idx WHERE rs.idx = :taxi_RIdx";
                    $card_Sel_Stmt = $DB_con->prepare($card_Sel_Query);
                    $card_Sel_Stmt->bindparam(":taxi_RIdx", $idx);
                    $card_Sel_Stmt->execute();
                    $card_Sel_Row = $card_Sel_Stmt->fetch(PDO::FETCH_ASSOC);
                    $cardIdx = trim($card_Sel_Row['cardIdx']);                                // 카드 고유번호
                    $card_Number4 = trim($card_Sel_Row['card_Number4']);                                // 카드 끝 4자리
                    // 토큰값 생성
                    $access_token = get_Token_PayForm('https://api.iamport.kr/users/getToken', array("imp_key" => $imp_key, "imp_secret" => $imp_secret));
                    //메세지
                    if ($access_token == '') {
                        $result = array("result" => false, "errorMsg" => "#2. " . $accesstoken_message);
                    } else if ($access_token != '') {
                        $res = pay_Order_PayForm('https://api.iamport.kr/subscribe/payments/again', array("customer_uid" => $mem_Idx . $card_Number4, "merchant_uid" => $taxi_OrdNo, "amount" => $taxi_OrdPrice, "name" => $ordTitNm), $access_token);
                        $code = $res['code'];                            //성공여부
                        $message = $res['message'];                        //메세지
                        $status = $res['response']['status'];            //결제상태
                        $fail_reason = $res['response']['fail_reason'];    //실패시 메세지
                        if ($code == 0 && $status != 'paid') {
                            if ($con_OrdFCnt > $taxi_OrdFCnt) { //결제 재시도
                                $taxiOrdFCnt = (int)$taxi_OrdFCnt + 1;
                                //주문 재시도 처리
                                $upMQquery2 = "UPDATE TB_ORDER SET taxi_OrdFCnt = :taxi_OrdFCnt WHERE taxi_RIdx = :taxi_RIdx AND taxi_OrdMemId = :taxi_OrdMemId LIMIT 1";
                                $upMStmt2 = $DB_con->prepare($upMQquery2);
                                $upMStmt2->bindparam(":taxi_RIdx", $idx);
                                $upMStmt2->bindparam(":taxi_OrdMemId", $mem_Id);
                                $upMStmt2->bindparam(":taxi_OrdFCnt", $taxiOrdFCnt);
                                $upMStmt2->execute();

                                $OrdOCnt = (int)$con_OrdFCnt - (int)$taxiOrdFCnt;

                                if ($OrdOCnt == 0) {
                                    //결제에 시도한 카드에 대해서 재등록 하게 처리
                                    $card_Up_Query = "UPDATE TB_PAYMENT_CARD SET card_Bit = 1 WHERE idx = :cardIdx LIMIT 1";
                                    $card_Up_Stmt = $DB_con->prepare($card_Up_Query);
                                    $card_Up_Stmt->bindparam(":cardIdx", $cardIdx);
                                    $card_Up_Stmt->execute();

                                    //등록된 카드 수량 확인
                                    $card_Cnt_Query = "SELECT COUNT(idx) AS card_Cnt FROM TB_PAYMENT_CARD WHERE card_Mem_Idx = :mem_Idx AND card_Bit <> 1";
                                    $card_Cnt_Stmt = $DB_con->prepare($card_Cnt_Query);
                                    $card_Cnt_Stmt->bindparam(":mem_Idx", $mem_Idx);
                                    $card_Cnt_Stmt->execute();
                                    $card_Cnt_Row = $card_Cnt_Stmt->fetch(PDO::FETCH_ASSOC);
                                    $card_Cnt = trim($card_Cnt_Row['card_Cnt']);                                // 카드 고유번호

                                    if ((int)$card_Cnt < 1) {
                                        //카드 재등록 필요 등록
                                        $upMQquery3 = "UPDATE TB_MEMBERS_ETC SET mem_CardBit = 1 WHERE mem_Idx = :mem_Idx LIMIT 1";
                                        $upMStmt3 = $DB_con->prepare($upMQquery3);
                                        $upMStmt3->bindparam(":mem_Idx", $mem_Idx);
                                        $upMStmt3->execute();
                                    }
                                    $result = array("result" => false, "OrdOCnt" => (string)$OrdOCnt, "errorMsg" => "재시도 횟수를 모두 사용하였습니다.");
                                } else {
                                    $result = array("result" => false, "OrdOCnt" => (string)$OrdOCnt);
                                }
                            } else if ($con_OrdFCnt == $taxi_OrdFCnt) {
                                $result = array("result" => false, "OrdOCnt" => (int)"0", "errorMsg" => "횟수가 초과되었습니다.");
                            }
                        } else if ($code == 0 && $status == 'paid') { //if ($taxi_OrdState == "1") { //결제완료
                            //주문
                            $upMQquery2 = "UPDATE TB_ORDER SET taxi_OrdState = 1 WHERE taxi_RIdx = :taxi_RIdx AND taxi_OrdMemId = :taxi_OrdMemId LIMIT 1";
                            $upMStmt2 = $DB_con->prepare($upMQquery2);
                            $upMStmt2->bindparam(":taxi_RIdx", $idx);
                            $upMStmt2->bindparam(":taxi_OrdMemId", $mem_Id);
                            $upMStmt2->execute();

                            //투게더 이동중 날짜 업데이트
                            $upMQquery3 = "UPDATE TB_RTAXISHARING_INFO SET reg_EDate = :reg_EDate WHERE taxi_RIdx = :taxi_RIdx AND taxi_RMemId = :taxi_RMemId LIMIT 1";
                            $upMStmt3 = $DB_con->prepare($upMQquery3);
                            $upMStmt3->bindparam(":reg_EDate", $reg_Date);
                            $upMStmt3->bindparam(":taxi_RIdx", $idx);
                            $upMStmt3->bindparam(":taxi_RMemId", $mem_Id);
                            $upMStmt3->execute();

                            //투게더 이동중 상태로 변경
                            $upPQquery = "UPDATE TB_RTAXISHARING SET taxi_RState = '6', taxi_RChk = 'Y' WHERE idx = :idx AND taxi_RMemId = :taxi_RMemId LIMIT 1";
                            $upPStmt = $DB_con->prepare($upPQquery);
                            $upPStmt->bindparam(":idx", $idx);
                            $upPStmt->bindparam(":taxi_RMemId", $mem_Id);
                            $upPStmt->execute();

                            //메이커 이동중 상태로 변경
                            $upSQquery = "UPDATE TB_STAXISHARING SET taxi_State = '6', taxi_SChk = 'Y' WHERE idx = :idx AND taxi_MemId = :taxi_MemId LIMIT 1";
                            $upSStmt = $DB_con->prepare($upSQquery);
                            $upSStmt->bindparam(":idx", $taxiSIdx);
                            $upSStmt->bindparam(":taxi_MemId", $taxiMemId);
                            $upSStmt->execute();

                            $taxi_Type = "6";

                            //매칭 생성자 이동중 푸시
                            $mem_MDToken = memMatchTokenInfo($taxiMemIdx);

                            $mDtitle = "";
                            $mDmsg = "노선이 이동중으로 진행됩니다.";
                            $mState = "6";

                            foreach ($mem_MDToken as $k => $v) {
                                $mDtokens = $mem_MDToken[$k];
                                $mDinputData = array("title" => $mDtitle, "msg" => $mDmsg, "state" => $mState);
                                $mDpresult = send_Push($mDtokens, $mDinputData);
                            }
                            /*푸시 관련 끝*/

                            //투게더에게 푸시
                            /*푸시 관련 시작*/
                            $mem_CToken = memMatchTokenInfo($taxiRMemIdx);

                            $ctitle = "";
                            $cmsg = "노선이 이동중으로 진행됩니다.";
                            $cState = "6";
                            foreach ($mem_CToken as $k => $v) {
                                $ctokens = $mem_CToken[$k];
                                $cinputData = array("title" => $ctitle, "msg" => $cmsg, "state" => $cState);
                                $cpresult = send_Push($ctokens, $cinputData);
                            }
                            /*푸시 관련 끝*/
                            $result = array("result" => true, "taxiOrdNo" => (string)$taxi_OrdNo);
                        } else {
                            $result = array("result" => false, "errorMsg" => "#4" . $message);
                        }
                    } else {
                        $result = array("result" => false, "errorMsg" => "#2 : 인증토큰 발급오류가 있습니다.");
                    }
                }
            } else {
                if ($taxi_OrdState == '5' || $taxi_OrdState == '4') {
                    $result = array("result" => false, "errorMsg" => "#4 : 본사 확인이 필요한 주문입니다.");
                } else if ($taxi_OrdState == '3') {
                    $result = array("result" => false, "errorMsg" => "#3 : 이미 결제가 취소된 주문입니다.");
                } else if ($taxi_OrdState == '2') {
                    $result = array("result" => false, "errorMsg" => "#2 : 이미 포인트양도가 완료된 주문입니다.");
                } else {
                    $result = array("result" => true, "taxiOrdNo" => (string)$taxi_OrdNo);
                }
            }
        }
    } else { //투게더 사용포인트가 쉐어링요금과 동일한 경우 포인트 결제 start

        //메이커 정보
        $mapQuery = "SELECT taxi_Sdong, taxi_Edong, taxi_Saddr, taxi_Eaddr FROM TB_STAXISHARING_MAP WHERE taxi_Idx = :taxi_Idx AND taxi_MemIdx = :taxi_MemIdx AND taxi_MemId = :taxi_MemId  LIMIT 1 ";
        //echo $mapQuery."<BR>";
        //exit;
        $mapStmt = $DB_con->prepare($mapQuery);
        $mapStmt->bindparam(":taxi_Idx", $taxiSIdx);
        $mapStmt->bindparam(":taxi_MemIdx", $taxiMemIdx);
        $mapStmt->bindparam(":taxi_MemId", $taxiMemId);
        $mapStmt->execute();
        $mapNum = $mapStmt->rowCount();
        //echo $mapNum."<BR>";

        if ($mapNum < 1) { //아닐경우
        } else {
            while ($mapRow = $mapStmt->fetch(PDO::FETCH_ASSOC)) {
                $taxiSdong = trim($mapRow['taxi_Sdong']);                      //  출발지 동명
                $taxiEdong = trim($mapRow['taxi_Edong']);                      //  도착지 동명
                $taxi_OrdSaddr  = trim($mapRow['taxi_Saddr']);              //  출발지 주소
                $taxi_OrdEaddr = trim($mapRow['taxi_Eaddr']);              //  도착지 주소
            }
        }

        $taxi_OrdTit = $taxiSdong . "▶" . $taxiEdong;
        $taxi_OrdRaddr = "";

        //주문 정보 저장 여부
        $chkOrdQuery = "SELECT idx, taxi_OrdNo, taxi_OrdState FROM TB_ORDER WHERE taxi_SIdx = :taxi_SIdx AND taxi_RIdx = :taxi_RIdx ";
        $chkOrdStmt = $DB_con->prepare($chkOrdQuery);
        $chkOrdStmt->bindparam(":taxi_SIdx", $taxiSIdx);
        $chkOrdStmt->bindParam("taxi_RIdx", $idx);
        $chkOrdStmt->execute();
        $chkOrdNum = $chkOrdStmt->rowCount();
        if ($chkOrdNum  < 1) {  //대기모드

            $taxi_OrdNo = date('YmdHis', time()) . str_pad((int)(microtime() * 100), 2, "0", STR_PAD_LEFT);  //주문번호

            $taxi_OrdType = "2";
            $taxi_OrdState = "1"; //결제완료 (추후 결제 할 경우 "0"으로 수정 후 진행 해야함 // '결제 상태 여부 (0: 접수, 1: 결제완료, 2: 양도완료, 3: 취소, 4: 거래취소확인, 5:거래완료확인)' 작업일 : 2019-01-08 

            //주문정보 저장
            $reg_Date = DU_TIME_YMDHIS;           //등록일

            $insQuery = "INSERT INTO TB_ORDER (taxi_SIdx, taxi_RIdx, taxi_OrdNo, taxi_OrdTit, taxi_OSMemIdx, taxi_OrdSMemId, taxi_OMemIdx, taxi_OrdMemId, taxi_OrdSaddr, taxi_OrdEaddr, taxi_OrdRaddr, taxi_OrdNickNm, taxi_OrdTel, taxi_OrdPrice, taxi_OrdPoint, taxi_OrdMType, taxi_OrdType, taxi_OrdState, reg_Date)
			SELECT :taxi_SIdx, :taxi_RIdx, :taxi_OrdNo, :taxi_OrdTit, :taxi_OSMemIdx, :taxi_OrdSMemId, :taxi_OMemIdx, :taxi_OrdMemId, :taxi_OrdSaddr, :taxi_OrdEaddr, :taxi_OrdRaddr, :taxi_OrdNickNm, :taxi_OrdTel, 0, :taxi_OrdPoint, :taxi_OrdMType, :taxi_OrdType, :taxi_OrdState, :reg_Date FROM DUAL";
            $insQuery .= " WHERE NOT EXISTS (SELECT * FROM TB_ORDER WHERE taxi_OrdNo = :taxi_OrdNo);";
            //echo $insQuery."<BR>";
            //exit;
            $stmt = $DB_con->prepare($insQuery);
            $stmt->bindParam("taxi_SIdx", $taxiSIdx);
            $stmt->bindParam("taxi_RIdx", $idx);
            $stmt->bindParam("taxi_OrdNo", $taxi_OrdNo);
            $stmt->bindParam("taxi_OrdTit", $taxi_OrdTit);
            $stmt->bindParam("taxi_OSMemIdx", $taxiMemIdx);
            $stmt->bindParam("taxi_OrdSMemId", $taxiMemId);
            $stmt->bindParam("taxi_OMemIdx", $taxiRMemIdx);
            $stmt->bindParam("taxi_OrdMemId", $taxiRMemId);
            $stmt->bindParam("taxi_OrdSaddr", $taxi_OrdSaddr);
            $stmt->bindParam("taxi_OrdEaddr", $taxi_OrdEaddr);
            $stmt->bindParam("taxi_OrdRaddr", $taxi_OrdRaddr);
            $stmt->bindParam("taxi_OrdNickNm", $taxi_OrdNickNm);
            $stmt->bindParam("taxi_OrdTel", $taxi_OrdTel);
            $stmt->bindParam("taxi_OrdPoint", $taxi_OrdPrice);
            $stmt->bindParam("taxi_OrdMType", $taxi_OrdMType);
            $stmt->bindParam("taxi_OrdType", $taxi_OrdType);
            $stmt->bindParam("taxi_OrdState", $taxi_OrdState);
            $stmt->bindParam("reg_Date", $reg_Date);
            $stmt->execute();
            $DB_con->lastInsertId();

            //결제정보만 입력 하였으므로 이동중상태로 변경 하면 안됨(결제 완료 후 처리 해야함)

            // AND taxi_OrdState = 0 준비중상태 추가할 것
            $viewQuery2 = "";
            $viewQuery2 = "SELECT taxi_OrdNo, taxi_OrdPoint FROM TB_ORDER WHERE taxi_RIdx = :taxi_RIdx AND taxi_OrdMemId = :taxi_OrdMemId AND taxi_OrdState = 1 LIMIT 1; ";
            $viewStmt2 = $DB_con->prepare($viewQuery2);
            $viewStmt2->bindparam(":taxi_RIdx", $idx);
            $viewStmt2->bindparam(":taxi_OrdMemId", $memId);
            $viewStmt2->execute();
            $num = $viewStmt2->rowCount();

            if ($num < 1) { //아닐경우
                $result = array("result" => false, "errorMsg" => "#1 : 요청 주문이 없습니다.");
            } else {

                while ($row = $viewStmt->fetch(PDO::FETCH_ASSOC)) {
                    $taxi_OrdNo = trim($row['taxi_OrdNo']);                                // 주문번호
                    $taxi_OrdPoint = trim($row['taxi_OrdPoint']);                        // 쉐어링요금
                }
                //주문
                $upMQquery2 = "UPDATE TB_ORDER SET taxi_OrdState = 1 WHERE taxi_RIdx = :taxi_RIdx AND taxi_OrdMemId = :taxi_OrdMemId LIMIT 1";
                $upMStmt2 = $DB_con->prepare($upMQquery2);
                $upMStmt2->bindparam(":taxi_RIdx", $idx);
                $upMStmt2->bindparam(":taxi_OrdMemId", $mem_Id);
                $upMStmt2->execute();

                //투게더 이동중 날짜 업데이트
                $upMQquery3 = "UPDATE TB_RTAXISHARING_INFO SET reg_EDate = :reg_EDate WHERE taxi_RIdx = :taxi_RIdx AND taxi_RMemId = :taxi_RMemId LIMIT 1";
                $upMStmt3 = $DB_con->prepare($upMQquery3);
                $upMStmt3->bindparam(":reg_EDate", $reg_Date);
                $upMStmt3->bindparam(":taxi_RIdx", $idx);
                $upMStmt3->bindparam(":taxi_RMemId", $mem_Id);
                $upMStmt3->execute();

                //투게더 이동중 상태로 변경
                $upPQquery = "UPDATE TB_RTAXISHARING SET taxi_RState = '6', taxi_RChk = 'Y' WHERE idx = :idx AND taxi_RMemId = :taxi_RMemId LIMIT 1";
                $upPStmt = $DB_con->prepare($upPQquery);
                $upPStmt->bindparam(":idx", $idx);
                $upPStmt->bindparam(":taxi_RMemId", $mem_Id);
                $upPStmt->execute();

                //메이커 이동중 상태로 변경
                $upSQquery = "UPDATE TB_STAXISHARING SET taxi_State = '6', taxi_SChk = 'Y' WHERE idx = :idx AND taxi_MemId = :taxi_MemId LIMIT 1";
                $upSStmt = $DB_con->prepare($upSQquery);
                $upSStmt->bindparam(":idx", $taxiSIdx);
                $upSStmt->bindparam(":taxi_MemId", $taxiMemId);
                $upSStmt->execute();

                // 이동 중 상태 값으로 변경
                $taxi_Type = "6";

                //푸시 전송 등록 여부 체크
                $cntPushQuery = "";
                $cntPushQuery = "SELECT count(idx) AS num FROM TB_SHARING_PUSH WHERE taxi_Idx = :taxi_Idx AND taxi_Type = :taxi_Type AND taxi_MemIdx = :taxi_MemIdx AND taxi_MemId = :taxi_MemId ";
                $cntPushStmt = $DB_con->prepare($cntPushQuery);
                $cntPushStmt->bindParam("taxi_Idx", $taxiSIdx);
                $cntPushStmt->bindParam("taxi_Type", $taxi_Type);
                $cntPushStmt->bindParam("taxi_MemIdx", $taxiMemIdx);
                $cntPushStmt->bindParam("taxi_MemId", $taxiMemId);
                $cntPushStmt->execute();
                $cntPushRow = $cntPushStmt->fetch(PDO::FETCH_ASSOC);
                $totalPushCnt = $cntPushRow['num'];

                if ($totalPushCnt == "") {
                    $totalPushCnt = "0";
                } else {
                    $totalPushCnt =  $totalPushCnt;
                }


                /*푸시 관련 시작(메이커)*/
                //메이커에게 이동중 푸시
                $mem_MDToken = memMatchTokenInfo($taxiMemIdx);

                $mDtitle = "";
                $mDmsg = "노선이 이동중으로 진행됩니다.";
                $mState = "6";

                foreach ($mem_MDToken as $k => $v) {
                    $mDtokens = $mem_MDToken[$k];
                    $mDinputData = array("title" => $mDtitle, "msg" => $mDmsg, "state" => $mState);
                    $mDpresult = send_Push($mDtokens, $mDinputData);
                }
                /*푸시 관련 끝(메이커)*/

                /*푸시 관련 시작(투게더)*/
                //투게더에게 이동중 푸시
                $mem_CToken = memMatchTokenInfo($taxiRMemIdx);

                $ctitle = "";
                $cmsg = "노선이 이동중으로 진행됩니다.";
                $cState = "6";
                foreach ($mem_CToken as $k => $v) {
                    $ctokens = $mem_CToken[$k];
                    $cinputData = array("title" => $ctitle, "msg" => $cmsg, "state" => $cState);
                    $cpresult = send_Push($ctokens, $cinputData);
                }
                /*푸시 관련 끝(투게더)*/
                $result = array("result" => true, "taxiOrdNo" => (string)$taxi_OrdNo);
            }
        } else { //대기모드 END
            while ($row = $chkOrdStmt->fetch(PDO::FETCH_ASSOC)) {
                $taxi_OrdNo = $row['taxi_OrdNo'];                                // 주문번호
                $taxi_OrdState = $row['taxi_OrdState'];                            // 결제상태
            }
            if ($taxi_OrdState == '5' || $taxi_OrdState == '4') {
                $result = array("result" => false, "errorMsg" => "#4 : 본사 확인이 필요한 주문입니다.");
            } else if ($taxi_OrdState == '3') {
                $result = array("result" => false, "errorMsg" => "#3 : 이미 결제가 취소된 주문입니다.");
            } else if ($taxi_OrdState == '2') {
                $result = array("result" => false, "errorMsg" => "#2 : 이미 포인트양도가 완료된 주문입니다.");
            } else {
                $result = array("result" => true, "taxiOrdNo" => (string)$taxi_OrdNo);
            }
        } //투게더 사용포인트가 쉐어링요금과 동일한 경우 포인트 결제 end
    }
}
dbClose($DB_con);
$viewStmt = null;
$viewStmt2 = null;
$memStmt = null;
$matchStmt = null;
$infoStmt = null;
$mapStmt = null;
$MapRStmt = null;
$upMStmt = null;
$upMStmt2 = null;
$upMStmt3 = null;
$upPStmt = null;
$upSStmt = null;
$smtmt2 = null;
$cntPushStmt = null;
$stmtPush = null;
$cntPushStmt2 = null;
$stmtPush2 = null;
$upLvStmt2 = null;

echo str_replace('\\/', '/', json_encode($result, JSON_UNESCAPED_UNICODE));
