<?

/*======================================================================================================================

* 프로그램		: 매칭 양도 처리
* 페이지 설명	: 매칭 양도 처리
* 파일명          : taxiSharingPointProc.php

========================================================================================================================*/
include "../lib/common.php";
include "../lib/functionDB.php";  //공통 db함수
include "../lib/functionFB.php";  //공통 firebase함수
include "./lib/TPAY.LIB.php";  //공통 db함수
include "./lib/tpay_proc.php"; // 아임포트 함수

$taxiOrdNo  = trim($taxiOrdNo);            // 주문번호
$chkState  = trim($chkState);    // 바로양도 확인 2

//histoy 저장
$reg_Date = DU_TIME_YMDHIS;           //등록일
if ($taxiOrdNo != "" && $chkState != "") {  //주문번호랑 양도확인 여부가 있을 경우

    if ($chkState == "2") { //바로양도

        $DB_con = db1();

        //주문정보 가져옴
        $viewQuery = "";
        $viewQuery = "SELECT taxi_SIdx, taxi_RIdx, taxi_OrdPrice, taxi_OrdPoint, taxi_OrdSMemId, taxi_OrdMemId, taxi_OSMemIdx, taxi_OMemIdx, taxi_OrdType FROM TB_ORDER WHERE taxi_OrdNo = :taxi_OrdNo AND taxi_OrdState = '1' ORDER BY idx DESC LIMIT 1  ";
        //echo $viewQuery."<BR>";
        //exit;
        $viewStmt = $DB_con->prepare($viewQuery);
        $viewStmt->bindparam(":taxi_OrdNo", $taxiOrdNo);
        $viewStmt->execute();
        $num = $viewStmt->rowCount();

        if ($num < 1) { //아닐경우
            $result = array("result" => false, "errorMsg" => "이동중인 매칭 건이 없습니다.");
        } else {
            while ($row = $viewStmt->fetch(PDO::FETCH_ASSOC)) {
                $taxiSIdx = trim($row['taxi_SIdx']);                    // 메이커 고유번호
                $taxiRIdx = trim($row['taxi_RIdx']);                    // 투게더 고유번호
                $taxi_OrdPrice = trim($row['taxi_OrdPrice']);            // 카드결제금액
                $taxi_OrdPoint = trim($row['taxi_OrdPoint']);            // 사용한포인트
                $taxiOrdSMemId = trim($row['taxi_OrdSMemId']);            // 메이커 아이디
                $taxiOrdMemId = trim($row['taxi_OrdMemId']);            // 투게더 아이디
                $taxiOSMemIdx = trim($row['taxi_OSMemIdx']);                // 메이커 고유아이디
                $taxiOMemIdx = trim($row['taxi_OMemIdx']);                // 투게더 고유아이디
                $taxi_OrdType = trim($row['taxi_OrdType']);                // 결제방식
            }
            $taxiSOrdPoint = (int)$taxi_OrdPrice + (int)$taxi_OrdPoint;

            $sMemNm = memNickInfo($taxiOrdMemId);   //요청자 닉네임

            //메이커 회원정보
            $memQuery = "SELECT mem_NickNm, mem_LV FROM TB_MEMBERS WHERE idx = :mem_Idx AND b_Disply ='N' LIMIT 1 ";
            $memStmt = $DB_con->prepare($memQuery);
            $memStmt->bindparam(":mem_Idx", $taxiOSMemIdx);
            $memStmt->execute();
            $memNum = $memStmt->rowCount();

            if ($memNum < 1) { //아닐경우
            } else {
                while ($memRow = $memStmt->fetch(PDO::FETCH_ASSOC)) {
                    $memNickNm = trim($memRow['mem_NickNm']);           // 메이커 닉네임
                    $memSLv = trim($memRow['mem_LV']);                    // 메이커 등급
                }
            }

            $memQuery1 = "SELECT mem_Point FROM TB_MEMBERS_ETC WHERE mem_Idx = :mem_Idx  ORDER BY idx DESC LIMIT 1 ";
            $memStmt1 = $DB_con->prepare($memQuery1);
            $memStmt1->bindparam(":mem_Idx", $taxiOSMemIdx);
            $memStmt1->execute();
            $memNum1 = $memStmt1->rowCount();

            if ($memNum1 < 1) { //아닐경우
            } else {
                while ($memRow1 = $memStmt1->fetch(PDO::FETCH_ASSOC)) {
                    $memPoint = trim($memRow1['mem_Point']);              // 메이커 포인트
                }
            }
            //투게더 회원정보
            $memQuery2 = "SELECT mem_NickNm, mem_Lv FROM TB_MEMBERS WHERE idx = :mem_Idx AND b_Disply = 'N' LIMIT 1 ";
            $memStmt2 = $DB_con->prepare($memQuery2);
            $memStmt2->bindparam(":mem_Idx", $taxiOMemIdx);
            $memStmt2->execute();
            $memNum2 = $memStmt2->rowCount();

            if ($memNum2 < 1) { //아닐경우
            } else {
                while ($memRow2 = $memStmt2->fetch(PDO::FETCH_ASSOC)) {
                    $memRNickNm = trim($memRow2['mem_NickNm']);              // 투게더 닉네임
                }
            }

            $memQuery3 = "SELECT mem_Point FROM TB_MEMBERS_ETC WHERE mem_Idx = :mem_Idx  ORDER BY idx DESC LIMIT 1 ";
            $memStmt3 = $DB_con->prepare($memQuery3);
            $memStmt3->bindparam(":mem_Idx", $taxiOMemIdx);
            $memStmt3->execute();
            $memNum3 = $memStmt3->rowCount();

            if ($memNum3 < 1) { //아닐경우
            } else {
                while ($memRow3 = $memStmt3->fetch(PDO::FETCH_ASSOC)) {
                    $memRPoint = trim($memRow3['mem_Point']);              // 투게더 포인트
                }
            }

            // 메이커 등급 포인트
            if ($memSLv != "") {
                $mpQuery = "";
                $mpQuery = "SELECT memDc FROM TB_MEMBER_LEVEL WHERE memLv = :memLv  LIMIT 1 ";
                $mpStmt = $DB_con->prepare($mpQuery);
                $mpStmt->bindparam(":memLv", $memSLv);
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
            $taxiPoint = $taxiSOrdPoint - floor($taxiSOrdPoint * ($levDc / 100));  // 요청요금 = 택시요금 - 택시요금의 %요금 - 사용 미르페이포인트 ==> 퍼센트 요금이란 택시요금에서 생성자가 입력한 요청비율(%)를 구한 요금
            //양도처리 내역 저장
            //메이커 포인트내역
            if ($taxiOrdSMemId <> "") {
                $taxi_Sign = "0"; // +기호
                $taxi_PState = "0"; //매칭
                //1400 요청 포인트 1400 수수료 금액
                $taxi_Memo = DU_TIME_YMDHIS . '
투게더(' . $memRNickNm . ') 님이 나눠내기한 ' . number_format($taxiSOrdPoint) . '포인트에서 수수료 ' . $levDc . '%를 차감한 ' . number_format($taxiPoint) . '포인트를 적립' . "";
                //echo $taxi_Memo."<BR>";
                //exit;

                //메이커 포인트내역 등록 여부 체크
                $cntQuery = "";
                $cntQuery = "SELECT count(idx)  AS num FROM TB_POINT_HISTORY WHERE taxi_SIdx = :taxi_SIdx AND taxi_RIdx = :taxi_RIdx AND taxi_OrdNo = :taxi_OrdNo AND taxi_MemId = :taxi_MemId AND taxi_MemIdx = :taxi_MemIdx";
                //$cntQuery = "SELECT count(idx)  AS num FROM TB_POINT_HISTORY WHERE taxi_SIdx = $taxiSIdx AND taxi_RIdx = $taxiRIdx AND taxi_OrdNo = $taxiOrdNo AND taxi_MemId = $taxiOrdSMemId ";
                $cntStmt = $DB_con->prepare($cntQuery);
                $cntStmt->bindparam(":taxi_SIdx", $taxiSIdx);
                $cntStmt->bindparam(":taxi_RIdx", $taxiRIdx);
                $cntStmt->bindparam(":taxi_OrdNo", $taxiOrdNo);
                $cntStmt->bindparam(":taxi_MemId", $taxiOrdSMemId);
                $cntStmt->bindparam(":taxi_MemIdx", $taxiOSMemIdx);
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
                    $insQuery = "INSERT INTO TB_POINT_HISTORY (taxi_SIdx, taxi_RIdx, taxi_OrdNo, taxi_MemId, taxi_MemIdx, taxi_OrdPoint, taxi_OrgPoint, taxi_Memo, taxi_Sign, taxi_PState, taxi_OrdType, reg_Date) VALUES (:taxi_SIdx, :taxi_RIdx, :taxi_OrdNo, :taxi_MemId, :taxi_MemIdx, :taxi_OrdPoint, :taxi_OrgPoint, :taxi_Memo, :taxi_Sign, :taxi_PState, :taxi_OrdType, :reg_Date)";
                    $stmt = $DB_con->prepare($insQuery);
                    $stmt->bindParam("taxi_SIdx", $taxiSIdx);
                    $stmt->bindParam("taxi_RIdx", $taxiRIdx);
                    $stmt->bindParam("taxi_OrdNo", $taxiOrdNo);
                    $stmt->bindParam("taxi_MemId", $taxiOrdSMemId);
                    $stmt->bindParam("taxi_MemIdx", $taxiOSMemIdx);
                    $stmt->bindParam("taxi_OrdPoint", $taxiPoint);
                    $stmt->bindParam("taxi_OrgPoint", $memPoint);
                    $stmt->bindParam("taxi_Memo", $taxi_Memo);
                    $stmt->bindParam("taxi_Sign", $taxi_Sign);
                    $stmt->bindParam("taxi_PState", $taxi_PState);
                    $stmt->bindParam("taxi_OrdType", $taxi_OrdType);
                    $stmt->bindParam("reg_Date", $reg_Date);
                    $stmt->execute();
                    // try {
                    //     $stmt->execute();
                    //     echo "Data inserted successfully.";
                    // } catch (PDOException $e) {
                    //     echo "PDO Exception: " . $e->getMessage();
                    // }
                    $DB_con->lastInsertId();

                    //메이커 포인트, 매칭성공횟수 내역 조회
                    $pointQuery = "SELECT mem_MatCnt FROM TB_MEMBERS_ETC WHERE mem_Id = :mem_Id  ORDER BY idx DESC  LIMIT 1 ";
                    $pointStmt = $DB_con->prepare($pointQuery);
                    $pointStmt->bindparam(":mem_Id", $taxiOrdSMemId);
                    $pointStmt->execute();
                    $pointNum = $pointStmt->rowCount();

                    if ($pointNum < 1) { //아닐경우
                    } else {
                        while ($pointRow = $pointStmt->fetch(PDO::FETCH_ASSOC)) {
                            $memMatCnt = trim($pointRow['mem_MatCnt']);  //매칭성공횟수
                        }
                    }

                    //양도금액 포함 포인트 (생성장의 경우는 적립
                    $memTotalPoint = (int)$memPoint + (int)$taxiPoint;        //현재포인트 = 보유포인트 + 쉐어링요금에서 수수료를 차감한 금액을 더해줌 

                    //매칭횟수
                    $totMatCnt = (int)$memMatCnt + 1;


                    //포인트 금액 변경
                    $upmPQquery = "UPDATE TB_MEMBERS_ETC SET mem_MatCnt = :mem_MatCnt, mem_Point = :mem_Point WHERE mem_Idx = :mem_Idx  ORDER BY idx DESC  LIMIT 1";
                    //$upmPQquery = "UPDATE TB_MEMBERS_ETC SET mem_MatCnt = $totMatCnt, mem_Point = $totPoint WHERE mem_Id = $taxiOrdSMemId  LIMIT 1";
                    //echo $upmPQquery."<BR>";
                    //exit;
                    $upmPStmt = $DB_con->prepare($upmPQquery);
                    $upmPStmt->bindparam(":mem_MatCnt", $totMatCnt);
                    $upmPStmt->bindparam(":mem_Point", $memTotalPoint);
                    $upmPStmt->bindparam(":mem_Idx", $taxiOSMemIdx);
                    $upmPStmt->execute();
                }
            }


            //투게더 포인트내역
            if ($taxiOrdMemId <> "") {
                if ((int)$taxi_OrdPrice > 0) {
                    $taxi_Sign = "0"; // +기호
                    $taxi_PState = "4"; //매칭

                    $taxi_CMemo = DU_TIME_YMDHIS . '
카드결제로 인하여 ' . number_format($taxi_OrdPrice) . '포인트 적립' . "";

                    //투게더 포인트내역 등록 여부 체크
                    $cntMQuery = "SELECT count(idx)  AS num FROM TB_POINT_HISTORY WHERE taxi_SIdx = :taxi_SIdx AND taxi_RIdx = :taxi_RIdx AND taxi_OrdNo = :taxi_OrdNo AND taxi_MemId = :taxi_MemId AND taxi_MemIdx = :taxi_MemIdx AND taxi_OrdType = :taxi_OrdType ";
                    $cntMStmt = $DB_con->prepare($cntMQuery);
                    $cntMStmt->bindparam(":taxi_SIdx", $taxiSIdx);
                    $cntMStmt->bindparam(":taxi_RIdx", $taxiRIdx);
                    $cntMStmt->bindparam(":taxi_OrdNo", $taxiOrdNo);
                    $cntMStmt->bindparam(":taxi_MemId", $taxiOrdMemId);
                    $cntMStmt->bindparam(":taxi_MemIdx", $taxiOMemIdx);
                    $cntMStmt->bindparam(":taxi_OrdType", $taxi_OrdType);
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
                        $insQuery = "INSERT INTO TB_POINT_HISTORY (taxi_SIdx, taxi_RIdx, taxi_OrdNo, taxi_MemId, taxi_MemIdx, taxi_OrdPoint, taxi_OrgPoint, taxi_Memo, taxi_Sign, taxi_PState, taxi_OrdType, reg_Date) VALUES (:taxi_SIdx, :taxi_RIdx, :taxi_OrdNo, :taxi_MemId, :taxi_MemIdx, :taxi_OrdPoint, :taxi_OrgPoint, :taxi_Memo, :taxi_Sign, :taxi_PState, :taxi_OrdType, :reg_Date)";
                        //echo $insQuery."<BR>";
                        //exit;
                        $mstmt = $DB_con->prepare($insQuery);
                        $mstmt->bindParam("taxi_SIdx", $taxiSIdx);
                        $mstmt->bindParam("taxi_RIdx", $taxiRIdx);
                        $mstmt->bindParam("taxi_OrdNo", $taxiOrdNo);
                        $mstmt->bindParam("taxi_MemId", $taxiOrdMemId);
                        $mstmt->bindParam("taxi_MemIdx", $taxiOMemIdx);
                        $mstmt->bindParam("taxi_OrdPoint", $taxi_OrdPrice);
                        $mstmt->bindParam("taxi_OrgPoint", $memRPoint);
                        $mstmt->bindParam("taxi_Memo", $taxi_CMemo);
                        $mstmt->bindParam("taxi_Sign", $taxi_Sign);
                        $mstmt->bindParam("taxi_PState", $taxi_PState);
                        $mstmt->bindParam("taxi_OrdType", $taxi_OrdType);
                        $mstmt->bindParam("reg_Date", $reg_Date);
                        $mstmt->execute();
                        // try {
                        //     $mstmt->execute();
                        //     echo "Data inserted successfully.";
                        // } catch (PDOException $e) {
                        //     echo "PDO Exception: " . $e->getMessage();
                        // }
                        $DB_con->lastInsertId();


                        //양도금액 포함 포인트(요청자의 경우 +	)
                        $totRPoint = $memRPoint + $taxi_OrdPrice; // 현재포인트 = 보유포인트 + 카드결제포인트

                        //포인트 변경
                        $upmsPQquery = "UPDATE TB_MEMBERS_ETC SET mem_Point = :mem_Point WHERE mem_Idx = :mem_Idx ORDER BY idx DESC LIMIT 1";
                        //echo $upmsPQquery."<BR>";
                        //exit;
                        $upmsPStmt = $DB_con->prepare($upmsPQquery);
                        $upmsPStmt->bindparam(":mem_Point", $totRPoint);
                        $upmsPStmt->bindparam(":mem_Idx", $taxiOMemIdx);
                        $upmsPStmt->execute();
                    }
                }
                $taxi_Sign = "1"; // -기호
                $taxi_PState = "0"; //매칭
                $taxi_CMemo = DU_TIME_YMDHIS . '
메이커(' . $memNickNm . ')님이 요청한  ' . number_format($taxiSOrdPoint) . '포인트를 나눠 내기.';


                //투게더 포인트내역 등록 여부 체크
                $cntMQuery = "";
                $cntMQuery = "SELECT count(idx)  AS num FROM TB_POINT_HISTORY WHERE taxi_SIdx = :taxi_SIdx AND taxi_RIdx = :taxi_RIdx AND taxi_OrdNo = :taxi_OrdNo AND taxi_MemId = :taxi_MemId AND taxi_MemIdx = :taxi_MemIdx AND taxi_OrdType = :taxi_OrdType AND taxi_Sign = 1 AND taxi_PState <> '5'";
                $cntMStmt = $DB_con->prepare($cntMQuery);
                $cntMStmt->bindparam(":taxi_SIdx", $taxiSIdx);
                $cntMStmt->bindparam(":taxi_RIdx", $taxiRIdx);
                $cntMStmt->bindparam(":taxi_OrdNo", $taxiOrdNo);
                $cntMStmt->bindparam(":taxi_MemId", $taxiOrdMemId);
                $cntMStmt->bindparam(":taxi_MemIdx", $taxiOMemIdx);
                $cntMStmt->bindparam(":taxi_OrdType", $taxi_OrdType);
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
                    if($totRPoint == "" || (int)$totRPoint == 0){
                        $totRPoint = $memRPoint;
                    }
                    $insQuery = "INSERT INTO TB_POINT_HISTORY (taxi_SIdx, taxi_RIdx, taxi_OrdNo, taxi_MemId, taxi_MemIdx, taxi_OrdPoint, taxi_OrgPoint, taxi_Memo, taxi_Sign, taxi_PState, taxi_OrdType, reg_Date) VALUES (:taxi_SIdx, :taxi_RIdx, :taxi_OrdNo, :taxi_MemId, :taxi_MemIdx, :taxi_OrdPoint, :taxi_OrgPoint, :taxi_Memo, :taxi_Sign, :taxi_PState, :taxi_OrdType, :reg_Date)";
                    //echo $insQuery."<BR>";
                    //exit;
                    $mstmt = $DB_con->prepare($insQuery);
                    $mstmt->bindParam("taxi_SIdx", $taxiSIdx);
                    $mstmt->bindParam("taxi_RIdx", $taxiRIdx);
                    $mstmt->bindParam("taxi_OrdNo", $taxiOrdNo);
                    $mstmt->bindParam("taxi_MemId", $taxiOrdMemId);
                    $mstmt->bindParam("taxi_MemIdx", $taxiOMemIdx);
                    $mstmt->bindParam("taxi_OrdPoint", $taxiSOrdPoint);
                    $mstmt->bindParam("taxi_OrgPoint", $totRPoint);
                    $mstmt->bindParam("taxi_Memo", $taxi_CMemo);
                    $mstmt->bindParam("taxi_Sign", $taxi_Sign);
                    $mstmt->bindParam("taxi_PState", $taxi_PState);
                    $mstmt->bindParam("taxi_OrdType", $taxi_OrdType);
                    $mstmt->bindParam("reg_Date", $reg_Date);
                    $mstmt->execute();
                    $DB_con->lastInsertId();

                    //투게더 포인트, 매칭성공횟수 내역 조회
                    $pointmQuery = "SELECT mem_MatCnt, mem_Point FROM TB_MEMBERS_ETC WHERE mem_Idx = :mem_Idx  ORDER BY idx DESC  LIMIT 1 ";
                    $pointmStmt = $DB_con->prepare($pointmQuery);
                    $pointmStmt->bindparam(":mem_Idx", $taxiOMemIdx);
                    $pointmStmt->execute();
                    $pointmNum = $pointmStmt->rowCount();

                    if ($pointmNum < 1) { //아닐경우
                    } else {
                        while ($pointmRow = $pointmStmt->fetch(PDO::FETCH_ASSOC)) {
                            $membMatCnt = trim($pointmRow['mem_MatCnt']);  //매칭성공횟수
                        }
                    }

                    //매칭횟수
                    $mtotMatCnt = $membMatCnt + 1;

                    //양도금액 포함 포인트(요청자의 경우 차감 으로 -)
                    $memTotalRPoint = (int)$totRPoint - (int)$taxiSOrdPoint;  // 현재포인트 = 보유포인트 - 사용포인트

                    //매칭 횟수, 포인트 변경
                    $upmsPQquery = "UPDATE TB_MEMBERS_ETC SET mem_MatCnt = :mem_MatCnt, mem_Point = :mem_Point WHERE mem_Idx = :mem_Idx ORDER BY idx DESC  LIMIT 1";
                    //echo $upmsPQquery."<BR>";
                    //exit;
                    $upmsPStmt = $DB_con->prepare($upmsPQquery);
                    $upmsPStmt->bindparam(":mem_MatCnt", $mtotMatCnt);
                    $upmsPStmt->bindparam(":mem_Point", $memTotalRPoint);
                    $upmsPStmt->bindparam(":mem_Idx", $taxiOMemIdx);
                    $upmsPStmt->execute();
                }
            }
            $profitMoney = floor($taxiSOrdPoint * ($levDc / 100));
            $taxi_SMemo = DU_TIME_YMDHIS . '
투게더(' . $memRNickNm . ') 님이 메이커(' . $memNickNm . ')님에게 요청한 ' . number_format($taxiSOrdPoint) . '포인트에서 수수료 ' . $levDc . '%인 수익 ' . number_format($profitMoney) . '포인트를 적립' . "";

            //본사 수익 내역 등록 여부 체크
            $cntPQuery = "SELECT count(idx)  AS num FROM TB_PROFIT_POINT WHERE taxi_SIdx = :taxi_SIdx AND taxi_RIdx = :taxi_RIdx AND taxi_OrdNo = :taxi_OrdNo AND taxi_MemId = :taxi_MemId AND taxi_MemIdx = :taxi_MemIdx AND taxi_RMemId = :taxi_RMemId AND taxi_RMemIdx = :taxi_RMemIdx ";
            $cntPStmt = $DB_con->prepare($cntPQuery);
            $cntPStmt->bindparam(":taxi_SIdx", $taxiSIdx);
            $cntPStmt->bindparam(":taxi_RIdx", $taxiRIdx);
            $cntPStmt->bindparam(":taxi_OrdNo", $taxiOrdNo);
            $cntPStmt->bindparam(":taxi_MemId", $taxiOrdSMemId);
            $cntPStmt->bindparam(":taxi_MemIdx", $taxiOSMemIdx);
            $cntPStmt->bindparam(":taxi_RMemId", $taxiOrdMemId);
            $cntPStmt->bindparam(":taxi_RMemIdx", $taxiOMemIdx);
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
                $insQuery = "INSERT INTO TB_PROFIT_POINT (taxi_SIdx, taxi_RIdx, taxi_OrdNo, taxi_MemId, taxi_MemIdx, taxi_RMemId, taxi_RMemIdx, taxi_OrdSPoint, taxi_OrdTPoint, taxi_OrdMPoint, taxi_Memo, reg_Date)
					 VALUES (:taxi_SIdx, :taxi_RIdx, :taxi_OrdNo, :taxi_MemId, :taxi_MemIdx, :taxi_RMemId, :taxi_RMemIdx, :taxi_OrdSPoint, :taxi_OrdTPoint, :taxi_OrdMPoint, :taxi_Memo, :reg_Date)";
                //echo $insQuery."<BR>";
                //exit;
                $pstmt = $DB_con->prepare($insQuery);
                $pstmt->bindParam("taxi_SIdx", $taxiSIdx);
                $pstmt->bindParam("taxi_RIdx", $taxiRIdx);
                $pstmt->bindParam("taxi_OrdNo", $taxiOrdNo);
                $pstmt->bindparam("taxi_MemId", $taxiOrdSMemId);
                $pstmt->bindparam("taxi_MemIdx", $taxiOSMemIdx);
                $pstmt->bindparam("taxi_RMemId", $taxiOrdMemId);
                $pstmt->bindparam("taxi_RMemIdx", $taxiOMemIdx);
                $pstmt->bindParam("taxi_OrdSPoint", $profitMoney);
                $pstmt->bindParam("taxi_OrdTPoint", $taxiSOrdPoint);
                $pstmt->bindParam("taxi_OrdMPoint", $taxiPoint);
                $pstmt->bindParam("taxi_Memo", $taxi_SMemo);
                $pstmt->bindParam("reg_Date", $reg_Date);
                $pstmt->execute();
                $DB_con->lastInsertId();
            }

            //매칭생성 완료 상태로 변경
            $upPQquery = "UPDATE TB_STAXISHARING SET taxi_State = '7' WHERE idx = :idx  LIMIT 1";
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
            $upMQquery = "UPDATE TB_RTAXISHARING SET taxi_RState = '7' WHERE idx = :idx AND taxi_RMemId = :taxi_RMemId LIMIT 1";
            $upMStmt = $DB_con->prepare($upMQquery);
            $upMStmt->bindparam(":idx", $taxiRIdx);
            $upMStmt->bindparam(":taxi_RMemId", $taxiOrdMemId);
            $upMStmt->execute();

            //주문서 신청 완료 상태 변경
            $upOquery = "UPDATE TB_ORDER SET taxi_OrdState = '2'  WHERE taxi_OrdNo = :taxi_OrdNo  LIMIT 1";
            $upOStmt = $DB_con->prepare($upOquery);
            $upOStmt->bindparam(":taxi_OrdNo", $taxiOrdNo);
            $upOStmt->execute();


            //생성자 푸시
            $mem_NToken = memMatchTokenInfo($taxiOSMemIdx);

            $chkState = "7";  //거래완료
            $ntitle = "";
            $nmsg = "거래가 성공적으로 완료되었습니다.";
            foreach ($mem_NToken as $k => $v) {
                $ntokens = $mem_NToken[$k];
                $ninputData = array("title" => $ntitle, "msg" => $nmsg, "state" => $chkState);
                $nresult = send_Push($ntokens, $ninputData);
            }

            //요청자 푸시
            $mem_RToken = memMatchTokenInfo($taxiOMemIdx);

            $rchkState = "7";  //거래완료
            $rtitle = "";
            $rmsg = "거래가 성공적으로 완료되었습니다.";
            foreach ($mem_RToken as $k2 => $v2) {
                $rtokens = $mem_RToken[$k2];
                $rinputData = array("title" => $rtitle, "msg" => $rmsg, "state" => $rchkState);
                $rResult = send_Push($rtokens, $rinputData);
            }

            if ($taxi_OrdType == "0" || $taxi_OrdType == "1") {
                // 추천인이 있는지 확인
                $member_Ch_Query = "SELECT mem_ChCode FROM TB_MEMBERS_ETC WHERE mem_Idx = :mem_Idx AND mem_ChCode IS NOT NULL";
                $member_Ch_Stmt = $DB_con->prepare($member_Ch_Query);
                $member_Ch_Stmt->bindparam(":mem_Idx", $taxiOMemIdx);
                $member_Ch_Stmt->execute();
                $member_Ch_Num = $member_Ch_Stmt->rowCount();
                if ($member_Ch_Num > 0) {
                    $member_Ch_Row = $member_Ch_Stmt->fetch(PDO::FETCH_ASSOC);
                    $mem_Ch_Idx = $member_Ch_Row['mem_ChCode'];         //추천한 회원 고유번호
                    // 추천인이 있다면 적립% 확인
                    $config_Query = "SELECT con_ChPoint FROM TB_CONFIG";
                    $config_Stmt = $DB_con->prepare($config_Query);
                    $config_Stmt->execute();
                    $config_Num = $config_Stmt->rowCount();
                    if ($config_Num > 0) {
                        $config_Row = $config_Stmt->fetch(PDO::FETCH_ASSOC);
                        $con_ChPoint = $config_Row['con_ChPoint'];         //공통 추천 적립율
                    }
                    $mem_Ch_Rate_Query = "SELECT mem_ChRate FROM TB_MEMBERS_ETC WHERE mem_Idx = :mem_Ch_Idx";
                    $mem_Ch_Rate_Stmt = $DB_con->prepare($mem_Ch_Rate_Query);
                    $mem_Ch_Rate_Stmt->bindparam(":mem_Ch_Idx", $mem_Ch_Idx);
                    $mem_Ch_Rate_Stmt->execute();
                    $mem_Ch_Rate_Num = $mem_Ch_Rate_Stmt->rowCount();
                    if ($mem_Ch_Rate_Num > 0) {
                        $mem_Ch_Rate_Row = $mem_Ch_Rate_Stmt->fetch(PDO::FETCH_ASSOC);
                        $mem_ChRate = $mem_Ch_Rate_Row['mem_ChRate'];         //개인 추천 적립율
                    }
                    if ($mem_ChRate == 0) {
                        $chPointRate = $con_ChPoint;
                    } else {
                        $chPointRate = $mem_ChRate;
                    }
                    $pointRate = $chPointRate / 100;
                    $chPoint = $pointRate * $taxi_OrdPrice;// 카드 결제 금액에 대해서만 초대한 사람에게 적립
                    //회원아이디 조회하기.
                    $member_Sel_Query = "SELECT m.mem_Id, me.mem_Point FROM TB_MEMBERS AS m INNER JOIN TB_MEMBERS_ETC AS me ON m.idx = me.mem_Idx WHERE m.idx = :mem_Ch_Idx";
                    $member_Sel_Stmt = $DB_con->prepare($member_Sel_Query);
                    $member_Sel_Stmt->bindparam(":mem_Ch_Idx", $mem_Ch_Idx);
                    $member_Sel_Stmt->execute();
                    $member_Sel_Num = $member_Sel_Stmt->rowCount();
                    if ($member_Sel_Num > 0) {
                        $member_Sel_Row = $member_Sel_Stmt->fetch(PDO::FETCH_ASSOC);
                        $mem_Ch_Id = $member_Sel_Row['mem_Id'];         //추천한 회원 고유번호
                        $mem_Ch_Point = $member_Sel_Row['mem_Point'];         //추천한 회원 고유번호
                        $memChPoint = $mem_Ch_Point + $chPoint;

                        // 적립%만큼 추천인에게 포인트 적립하기
                        $mem_Etc_Ch_Query = "UPDATE TB_MEMBERS_ETC SET mem_Point = :mem_Point WHERE mem_Idx = :mem_Ch_Idx LIMIT 1";
                        $mem_Etc_Ch_Stmt = $DB_con->prepare($mem_Etc_Ch_Query);
                        $mem_Etc_Ch_Stmt->bindparam(":mem_Point", $memChPoint);
                        $mem_Etc_Ch_Stmt->bindparam(":mem_Ch_Idx", $mem_Ch_Idx);
                        $mem_Etc_Ch_Stmt->execute();
                    }

                    // 적립한 포인트 푸시내역 남기기.
                    $taxi_Ch_Sign = "0"; // +기호
                    $taxi_Ch_State = "3"; //추천인적립
                    $taxi_Ch_Memo = DU_TIME_YMDHIS . '
추천인의 매칭성공으로 인한 포인트 적립';
                    $m_Ch_Query = "INSERT INTO TB_POINT_HISTORY (taxi_SIdx, taxi_RIdx, taxi_OrdNo, taxi_MemId, taxi_MemIdx, taxi_OrdPoint, taxi_OrgPoint, taxi_Memo, taxi_Sign, taxi_PState, taxi_OrdType, reg_Date) VALUES (:taxi_SIdx, :taxi_RIdx, :taxi_OrdNo, :taxi_MemId, :taxi_MemIdx, :taxi_OrdPoint, :taxi_OrgPoint, :taxi_Memo, :taxi_Sign, :taxi_PState, :taxi_OrdType, :reg_Date)";
                    //echo $insQuery."<BR>";
                    //exit;
                    $m_Ch_Stmt = $DB_con->prepare($m_Ch_Query);
                    $m_Ch_Stmt->bindParam("taxi_SIdx", $taxiSIdx);
                    $m_Ch_Stmt->bindParam("taxi_RIdx", $taxiRIdx);
                    $m_Ch_Stmt->bindParam("taxi_OrdNo", $taxiOrdNo);
                    $m_Ch_Stmt->bindParam("taxi_MemId", $mem_Ch_Id);
                    $m_Ch_Stmt->bindParam("taxi_MemIdx", $mem_Ch_Idx);
                    $m_Ch_Stmt->bindParam("taxi_OrdPoint", $chPoint);
                    $m_Ch_Stmt->bindParam("taxi_OrgPoint", $mem_Ch_Point);
                    $m_Ch_Stmt->bindParam("taxi_Memo", $taxi_Ch_Memo);
                    $m_Ch_Stmt->bindParam("taxi_Sign", $taxi_Ch_Sign);
                    $m_Ch_Stmt->bindParam("taxi_PState", $taxi_Ch_State);
                    $m_Ch_Stmt->bindParam("taxi_OrdType", $taxi_OrdType);
                    $m_Ch_Stmt->bindParam("reg_Date", $reg_Date);
                    $m_Ch_Stmt->execute();
                }
            }
            //, "totaxiPoint" => (int)$totaxiPoint, "taxiMPoint" => (int)$taxiMPoint
            $result = array("result" => true);
            if ($result['result'] == true) {
                fire_Complete_Set($taxiSIdx);
            }
        }    // 이동중인 매칭 건이 없습니다. 끝
    } // 바로양도 끝


    dbClose($DB_con);
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
    $cntMStmt_mir = null;
    $mstmt_mir = null;
} else {
    $result = array("result" => false);
}

echo str_replace('\\/', '/', json_encode($result, JSON_UNESCAPED_UNICODE));
