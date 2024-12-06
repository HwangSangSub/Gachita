<?

/*======================================================================================================================

* 프로그램			: 투게더 매칭요청, 예약요청 취소 건
* 페이지 설명		: 투게더 매칭요청, 예약요청 취소 건
* 파일명                 : taxiSharingCancle.php

========================================================================================================================*/

include "../lib/common.php";
include "../lib/functionDB.php";  //공통 db함수

$mem_Id = trim($memId);           //아이디
$chkSIdx  = trim($idx);           // 매칭생성 고유번호 idx
$chkRIdx = trim($taxiRidx);       // 투게더 고유번호 idx


if ($mem_Id != "" && $chkSIdx != "" && $chkRIdx != "") {  //아이디가 있을 경우

    $DB_con = db1();

    $chkCntQuery = "SELECT count(taxi_RMemId) AS num from TB_RTAXISHARING WHERE taxi_RMemId = :taxi_RMemId  AND taxi_SIdx = :taxi_SIdx AND idx = :idx AND taxi_RState IN ('1', '2')";  //매칭요청, 예약요청
    //echo $chkCntQuery."<BR>";
    //exit;
    $chkStmt = $DB_con->prepare($chkCntQuery);
    $chkStmt->bindparam(":taxi_RMemId", $mem_Id);
    $chkStmt->bindparam(":taxi_SIdx", $chkSIdx);
    $chkStmt->bindparam(":idx", $chkRIdx);
    $chkStmt->execute();
    $chkRow = $chkStmt->fetch(PDO::FETCH_ASSOC);
    $num = $chkRow['num'];

    //$num =1;
    if ($num < 1) { //아닐 경우
        $result = array("result" => false, "errorMsg" => "현재 진행중인 매칭 요청 노선이 없습니다. 취소가 불가능합니다.");
    } else {  // 매칭신청 대기중 일 경우 취소 가능

        //매칭생성정보
        $infoQuery = "";
        $infoQuery = "SELECT DATE_ADD(A.taxi_SDate, INTERVAL -30 MINUTE) AS chkDate, DATE_ADD(A.taxi_SDate, INTERVAL 30 MINUTE) AS chkDate2, B.taxi_Type ";
        $infoQuery .= " FROM TB_STAXISHARING A LEFT OUTER JOIN TB_STAXISHARING_INFO B ON B.taxi_Idx = A.idx ";
        $infoQuery .= " WHERE 1 = 1 AND A.idx = :idx ";

        $infoStmt = $DB_con->prepare($infoQuery);
        $infoStmt->bindparam(":idx", $chkSIdx);
        $infoStmt->execute();
        $infoNum = $infoStmt->rowCount();

        if ($infoNum < 1) { //아닐경우
        } else {


            while ($infoRow = $infoStmt->fetch(PDO::FETCH_ASSOC)) {
                $chkDate = trim($infoRow['chkDate']);            //예약전 30분
                $chkDate2 = trim($infoRow['chkDate2']);            //예약후 30분
                $taxiType = trim($infoRow['taxi_Type']);        //출발타입 ( 0: 바로출발, 1: 예약출발 )
            }

            $chkNum = "1";

            if ($chkNum == "0") { //시간
                $result = array("result" => false, "errorMsg" => "현재 생성된 노선은 매칭 시간이 지난 노선입니다.");
            } else { // 매칭 신청 취소

                // 메이커 상태값
                $matchQuery = "SELECT taxi_MemId, taxi_MemIdx, taxi_RMemIdx, taxi_RState from TB_RTAXISHARING WHERE taxi_RMemId = :taxi_RMemId AND taxi_SIdx = :taxi_SIdx AND idx = :idx AND taxi_RState IN ('1', '2') ";
                $matStmt = $DB_con->prepare($matchQuery);
                $matStmt->bindparam(":taxi_RMemId", $mem_Id);
                $matStmt->bindparam(":taxi_SIdx", $chkSIdx);
                $matStmt->bindparam(":idx", $chkRIdx);
                $matStmt->execute();
                $matNum = $matStmt->rowCount();


                if ($matNum < 1) { //요청한 신청한 건수가 없음
                } else {  // 요청한 신청건수 가 있을 경우 삭제

                    while ($matRow = $matStmt->fetch(PDO::FETCH_ASSOC)) {
                        $taxiMemId = trim($matRow['taxi_MemId']);    // 생성자 아이디
                        $taxiMemIdx = trim($matRow['taxi_MemIdx']);    // 생성자 고유 아이디
                        $taxiRMemIdx = trim($matRow['taxi_RMemIdx']);   // 투게더 고유 아이디
                        $taxiRState = trim($matRow['taxi_RState']);     // 요청자 상태값

                        //매칭요청 기본 삭제
                        $delQquery = "DELETE FROM TB_RTAXISHARING WHERE taxi_RMemIdx = :taxi_RMemIdx AND taxi_RMemId = :taxi_RMemId AND taxi_SIdx = :taxi_SIdx AND idx = :idx AND taxi_RState IN ('1', '2') LIMIT 1";
                        $delStmt = $DB_con->prepare($delQquery);
                        $delStmt->bindparam(":taxi_RMemIdx", $taxiRMemIdx);
                        $delStmt->bindparam(":taxi_RMemId", $mem_Id);
                        $delStmt->bindparam(":taxi_SIdx", $chkSIdx);
                        $delStmt->bindparam(":idx", $chkRIdx);
                        $delStmt->execute();

                        //매칭요청 정보 삭제
                        $delQquery2 = "DELETE FROM TB_RTAXISHARING_INFO WHERE taxi_RMemIdx = :taxi_RMemIdx AND taxi_RMemId = :taxi_RMemId  AND taxi_SIdx = :taxi_SIdx AND taxi_RIdx = :taxi_RIdx LIMIT 1";
                        $delStmt2 = $DB_con->prepare($delQquery2);
                        $delStmt2->bindparam(":taxi_RMemIdx", $taxiRMemIdx);
                        $delStmt2->bindparam(":taxi_RMemId", $mem_Id);
                        $delStmt2->bindparam(":taxi_SIdx", $chkSIdx);
                        $delStmt2->bindparam(":taxi_RIdx", $chkRIdx);
                        $delStmt2->execute();

                        //매칭요청 지도 삭제
                        $delQquery3 = "DELETE FROM TB_RTAXISHARING_MAP WHERE taxi_RMemIdx = :taxi_RMemIdx AND taxi_RMemId = :taxi_RMemId  AND taxi_SIdx = :taxi_SIdx AND taxi_RIdx = :taxi_RIdx LIMIT 1";
                        $delStmt3 = $DB_con->prepare($delQquery3);
                        $delStmt3->bindparam(":taxi_RMemIdx", $taxiRMemIdx);
                        $delStmt3->bindparam(":taxi_RMemId", $mem_Id);
                        $delStmt3->bindparam(":taxi_SIdx", $chkSIdx);
                        $delStmt3->bindparam(":taxi_RIdx", $chkRIdx);
                        $delStmt3->execute();

                        if ($taxiRState == "1") {
                            $statNm = "생성노선에";
                        } else if ($taxiRState == "2") {
                            $statNm = "예약노선에";
                        }


                        $taxi_Type = "0";
                        $reg_Date = DU_TIME_YMDHIS;        //푸시등록일

                        //푸시 전송 등록 여부 체크
                        $cntPushQuery = "";
                        $cntPushQuery = "SELECT count(idx) AS num FROM TB_SHARING_PUSH WHERE taxi_Idx = :taxi_Idx AND taxi_Type = :taxi_Type AND taxi_MemIdx = :taxi_MemIdx AND taxi_MemId = :taxi_MemId ";
                        $cntPushStmt = $DB_con->prepare($cntPushQuery);
                        $cntPushStmt->bindParam("taxi_Idx", $chkSIdx);
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
                            $stmtPush->bindParam("taxi_Idx", $chkSIdx);
                            $stmtPush->bindParam("taxi_Type", $taxi_Type);
                            $stmtPush->bindParam("taxi_MemIdx", $taxiMemIdx);
                            $stmtPush->bindParam("taxi_MemId", $taxiMemId);
                            $stmtPush->bindParam("reg_Date", $reg_Date);
                            $stmtPush->execute();

                            /*푸시 관련 시작*/
                            $mem_Token = memMatchTokenInfo($taxiMemIdx);


                            $title = "";
                            $msg = $statNm . " 매칭요청이 취소되었습니다.";
                            foreach ($mem_Token as $k => $v) {
                                $tokens = $mem_Token[$k];

                                //알림할 내용들을 취합해서 $data에 모두 담는다. 프로젝트 의도에 따라 다른게 더 있을 수 있다.
                                $inputData = array("title" => $title, "msg" => $msg, "state" => "0");

                                //마지막에 알림을 보내는 함수를 실행하고 그 결과를 화면에 출력해 준다.
                                $presult = send_Push($tokens, $inputData);
                                // echo $presult;
                            }
                            /*푸시 관련 끝*/
                        }
                    }
                }

                //투게더 건수 조회
                $cntQuery = "SELECT count(taxi_MemId) AS num from TB_RTAXISHARING WHERE idx = :idx AND taxi_RState = '1'";
                $cntStmt = $DB_con->prepare($cntQuery);
                $cntStmt->bindparam(":idx", $chkRIdx);
                $cntStmt->execute();
                $cntRow = $cntStmt->fetch(PDO::FETCH_ASSOC);
                $cntNum = $cntRow['num'];

                if ($cntNum > 1) { //아닐 경우
                } else {  // 매칭신청건이 없을 경우

                    $upPQquery = "UPDATE TB_STAXISHARING SET taxi_State = '1' WHERE idx = :idx  LIMIT 1";
                    $upPStmt = $DB_con->prepare($upPQquery);
                    $upPStmt->bindparam(":idx", $chkSIdx);
                    $upPStmt->execute();
                    //, "msg" => " 신청하신 매칭 요청 취소가 정상적으로 처리 되었습니다."
                    $result = array("result" => true);
                }
            }
        }


        dbClose($DB_con);
        $chkStmt = null;
        $infoStmt = null;
        $matStmt = null;
        $cntPushStmt = null;
        $stmtPush = null;
        $delStmt = null;
        $delStmt2 = null;
        $delStmt3 = null;
        $cntStmt = null;
        $upPStmt = null;
    }
} else {
    $result = array("result" => false);
}

echo str_replace('\\/', '/', json_encode($result, JSON_UNESCAPED_UNICODE));
