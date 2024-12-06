<?

/*======================================================================================================================

* 프로그램			: 수락, 거절 처리 페이지
* 페이지 설명		: 수락, 거절 처리 페이지
* 파일명                 : taxiSharingSProc.php

========================================================================================================================*/

include "../lib/common.php";
include "../lib/functionDB.php";  //공통 db함수
include "../lib/sharing_send.php";  //현황확인을 위한 함수

$idx  = trim($idx);                        // 투게더 고유번호 idx
$taxiRChk = trim($chkState);            // 매칭 상태 여부 ( 1: 수락, 2: 거절)
$taxiRCancle = trim($chkCancle);        //취소여부 (1: 경로 맞지 않음, 2: 거리가 너무 멀음, 3: 다른 용무)

$res_bit = 0; // 성공여부 (0: 실패, 1: 성공)

if ($idx != "") {  //고유번호가 있을 경우
    $DB_con = db1();

    $reg_Date = DU_TIME_YMDHIS;        //푸시등록일


    //매칭생성 기본 정보 가져옴
    $viewQuery = "";
    $viewQuery = "SELECT taxi_SIdx, taxi_MemIdx, taxi_MemId, taxi_RMemIdx, taxi_RMemId, taxi_RState FROM TB_RTAXISHARING WHERE idx = :idx LIMIT 1  ";
    //echo $viewQuery."<BR>";
    //exit;
    $viewStmt = $DB_con->prepare($viewQuery);
    $viewStmt->bindparam(":idx", $idx);
    $viewStmt->execute();
    $num = $viewStmt->rowCount();
    //echo $num."<BR>";
    //exit;

    if ($num < 1) { //아닐경우
        $result = array("result" => false, "errorMsg" => "요청하지 않은 노선입니다. 확인 후 다시 시도해주세요.");
    } else {
        while ($row = $viewStmt->fetch(PDO::FETCH_ASSOC)) {
            $taxiSIdx = trim($row['taxi_SIdx']);            // 매칭생성 고유번호
            $taxiMemIdx = trim($row['taxi_MemIdx']);        // 매칭생성 고유 아이디
            $taxiMemId = trim($row['taxi_MemId']);            // 매칭생성 아이디
            $taxiRMemIdx = trim($row['taxi_RMemIdx']);        // 투게더 고유 아이디
            $taxiRMemId = trim($row['taxi_RMemId']);        // 매칭요청 아이디
            $taxiState = trim($row['taxi_RState']);            // 매칭 상태
        }
        if ($taxiRChk  == "1") { //매칭수락일경우


            //매칭자 수락한 자가 있는 지 체크 상태값이 2가 맞음
            $cntQuery = "SELECT count(idx) AS num from TB_RTAXISHARING WHERE taxi_SIdx = :taxi_SIdx AND taxi_MemId = :taxi_MemId AND taxi_RMemId = :taxi_RMemId AND taxi_RState IN ('4', '5') ";
            $cntStmt = $DB_con->prepare($cntQuery);
            $cntStmt->bindparam(":taxi_SIdx", $taxiSIdx);
            $cntStmt->bindparam(":taxi_MemId", $taxiMemId);
            $cntStmt->bindparam(":taxi_RMemId", $taxiRMemId);
            $cntStmt->execute();
            $cntRow = $cntStmt->fetch(PDO::FETCH_ASSOC);
            $cntNum = $cntRow['num'];

            if ($cntNum < 1) { // 매칭된 수락건 없을 경우

                //2018-10-24 추가
                //요청 수락한 요청자가 다른 노선 신청 정보가 있을 경우 매칭건 조회
                $matchQuery = "SELECT idx, taxi_SIdx, taxi_MemIdx, taxi_MemId, taxi_RState from TB_RTAXISHARING WHERE taxi_SIdx != :taxi_SIdx AND taxi_RMemId = :taxi_RMemId AND taxi_RState IN ('1', '2', '3') ";
                $matStmt = $DB_con->prepare($matchQuery);
                $matStmt->bindparam(":taxi_SIdx", $taxiSIdx);
                $matStmt->bindparam(":taxi_RMemId", $taxiRMemId);
                $matStmt->execute();
                $matNum = $matStmt->rowCount();

                if ($matNum < 1) { //요청한 신청한 건수가 없음
                } else {  // 요청한 신청건수 가 있을 경우 삭제

                    while ($matRow = $matStmt->fetch(PDO::FETCH_ASSOC)) {
                        $taxiMRIdx = trim($matRow['idx']);                // 투게더 고유번호
                        $taxiMSIdx = trim($matRow['taxi_SIdx']);        // 매칭생성 고유번호
                        $taxiMMemIdx = trim($matRow['taxi_MemIdx']);    // 메이커 고유아이디
                        $taxiMMemId = trim($matRow['taxi_MemId']);      // 메이커 아이디
                        $taxiMRState = trim($matRow['taxi_RState']);    // 상태

                        if ($taxiMRState != "3") { //거절이 아닌경우 만 제외

                            $taxi_Type = "0";

                            //푸시 전송 등록 여부 체크
                            $cntPushQuery = "";
                            $cntPushQuery = "SELECT count(idx) AS num FROM TB_SHARING_PUSH WHERE taxi_Idx = :taxi_Idx AND taxi_Type = :taxi_Type AND taxi_MemIdx = :taxi_MemIdx AND taxi_MemId = :taxi_MemId ";
                            $cntPushStmt = $DB_con->prepare($cntPushQuery);
                            $cntPushStmt->bindParam("taxi_Idx", $taxiMSIdx);
                            $cntPushStmt->bindParam("taxi_Type", $taxi_Type);
                            $cntPushStmt->bindParam("taxi_MemIdx", $taxiMMemIdx);
                            $cntPushStmt->bindParam("taxi_MemId", $taxiMMemId);
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
                                $stmtPush->bindParam("taxi_Idx", $taxiMSIdx);
                                $stmtPush->bindParam("taxi_Type", $taxi_Type);
                                $stmtPush->bindParam("taxi_MemIdx", $taxiMMemIdx);
                                $stmtPush->bindParam("taxi_MemId", $taxiMMemId);
                                $stmtPush->bindParam("reg_Date", $reg_Date);
                                $stmtPush->execute();

                                /*푸시 관련 시작*/

                                //요청 수락한자가 다른 생성방에 요청했을 경우 취소 푸시
                                $mem_MDToken = memMatchTokenInfo($taxiMMemIdx);

                                if ($taxiMRState == "1") { //예약출발일 경우
                                    $statMNm = "예약요청완료로";
                                } else if ($taxiMRState == "2") { //바로출발, 시간선택일 경우
                                    $statMNm = "만남중으로";
                                }

                                $MDmsg = " 요청취소됨 (타인매칭)";

                                $mDtitle = "";
                                $mDmsg = $MDmsg;

                                foreach ($mem_MDToken as $k => $v) {
                                    $mDtokens = $mem_MDToken[$k];

                                    //알림할 내용들을 취합해서 $data에 모두 담는다. 프로젝트 의도에 따라 다른게 더 있을 수 있다.
                                    $mDinputData = array("title" => $mDtitle, "msg" => $mDmsg, "state" => "0");

                                    //마지막에 알림을 보내는 함수를 실행하고 그 결과를 화면에 출력해 준다.
                                    $mDpresult = send_Push($mDtokens, $mDinputData);
                                    // echo $mDpresult;
                                }

                                /*푸시 관련 끝*/
                            }
                        }

                        //매칭요청 기본 삭제
                        $mdelQquery = "DELETE FROM TB_RTAXISHARING WHERE idx = :idx AND taxi_RMemId = :taxi_RMemId ";
                        $mdelStmt = $DB_con->prepare($mdelQquery);
                        $mdelStmt->bindparam(":idx", $taxiMRIdx);
                        $mdelStmt->bindparam(":taxi_RMemId", $taxiRMemId);
                        $mdelStmt->execute();

                        //매칭요청 정보 삭제
                        $mdelQquery2 = "DELETE FROM TB_RTAXISHARING_INFO WHERE taxi_RIdx = :taxi_RIdx AND taxi_RMemId = :taxi_RMemId ";
                        $mdelStmt2 = $DB_con->prepare($mdelQquery2);
                        $mdelStmt2->bindparam(":taxi_RIdx", $taxiMRIdx);
                        $mdelStmt2->bindparam(":taxi_RMemId", $taxiRMemId);
                        $mdelStmt2->execute();

                        //매칭요청 지도 삭제
                        $mdelQquery3 = "DELETE FROM TB_RTAXISHARING_MAP WHERE taxi_RIdx = :taxi_RIdx AND taxi_RMemId = :taxi_RMemId";
                        $mdelStmt3 = $DB_con->prepare($mdelQquery3);
                        $mdelStmt3->bindparam(":taxi_RIdx", $taxiMRIdx);
                        $mdelStmt3->bindparam(":taxi_RMemId", $taxiRMemId);
                        $mdelStmt3->execute();

                        //투게더 카운트 체크
                        $mcntQuery = "SELECT count(taxi_MemId) AS num from TB_RTAXISHARING WHERE taxi_SIdx = :taxi_SIdx AND taxi_RState IN ('1', '2')  ";  //매칭요청, 예약요청
                        $mcntStmt = $DB_con->prepare($mcntQuery);
                        $mcntStmt->bindparam(":taxi_SIdx", $taxiMSIdx);
                        $mcntStmt->execute();
                        $mcntMRow = $mcntStmt->fetch(PDO::FETCH_ASSOC);
                        $mcntMNum = $mcntMRow['num'];

                        if ($mcntMNum == "0") { // 취소일경우
                            //매칭 취소 신청이 하나도 없을 경우 매칭중 상태로 변경
                            $mupQquery = "UPDATE TB_STAXISHARING SET taxi_State = '1' WHERE idx = :idx  LIMIT 1";
                            $mupStmt = $DB_con->prepare($mupQquery);
                            $mupStmt->bindparam(":idx", $taxiMSIdx);
                            $mupStmt->execute();
                        }
                    }
                }


                //2018-09-21 추가
                //기타 매칭요청, 거절 중인 회원(요청 수락 한 회원 말고 다른 요청자들 처리)
                $mnDSql = "  , ( SELECT mem_NickNm FROM TB_MEMBERS WHERE TB_MEMBERS.mem_Id = TB_RTAXISHARING.taxi_RMemId AND TB_MEMBERS.b_Disply = 'N' limit 1 ) AS memNickNm  ";
                $chkQuery = "";
                $chkQuery = "SELECT idx, taxi_SIdx, taxi_MemIdx, taxi_MemId, taxi_RMemIdx, taxi_RMemId, taxi_RState {$mnDSql} from TB_RTAXISHARING WHERE taxi_SIdx = :taxi_SIdx AND idx != :idx AND taxi_RState IN ('1', '2', '3') ";
                $chkStmt = $DB_con->prepare($chkQuery);
                $chkStmt->bindparam(":taxi_SIdx", $taxiSIdx);
                $chkStmt->bindparam(":idx", $idx);
                $chkStmt->execute();
                $chkNum = $chkStmt->rowCount();

                if ($chkNum < 1) { //대기 회원이 없음
                } else {  // 요청 수락한 요청자를 제외한 노선 취소 회원

                    while ($chkRow = $chkStmt->fetch(PDO::FETCH_ASSOC)) {
                        $memDNickNm = trim($chkRow['memNickNm']);               // 생성자 닉네임
                        $taxiDRMemIdx = trim($chkRow['taxi_RMemIdx']);            // 투게더 고유 아이디
                        $taxiDIdx = trim($chkRow['idx']);                       // 요청자 고유번호
                        $taxiDRMemId = trim($chkRow['taxi_RMemId']);            // 투게더 아이디
                        $taxiDSIdx = trim($chkRow['taxi_SIdx']);                // 매칭생성 고유번호 (메이커 상태 변경 할때 만 사용)
                        $taxiDMemIdx = trim($chkRow['taxi_MemIdx']);            // 메이커 아이디
                        $taxiDMemId = trim($chkRow['taxi_MemId']);                // 메이커 아이디
                        $taxiRState = trim($chkRow['taxi_RState']);                // 매칭요청 상태

                        if ($taxiRState != "3") { //기타 요청한 요청자들

                            $taxi_Type2 = "0";

                            //푸시 전송 등록 여부 체크
                            $cntPushQuery2 = "";
                            $cntPushQuery2 = "SELECT count(idx) AS num FROM TB_SHARING_PUSH WHERE taxi_Idx = :taxi_Idx AND taxi_Type = :taxi_Type AND taxi_MemIdx = :taxi_MemIdx AND taxi_MemId = :taxi_MemId ";
                            $cntPushStmt2 = $DB_con->prepare($cntPushQuery2);
                            $cntPushStmt2->bindParam("taxi_Idx", $taxiDIdx);
                            $cntPushStmt2->bindParam("taxi_Type", $taxi_Type2);
                            $cntPushStmt2->bindParam("taxi_MemIdx", $taxiDRMemIdx);
                            $cntPushStmt2->bindParam("taxi_MemId", $taxiDRMemId);
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
                                $stmtPush2->bindParam("taxi_Idx", $taxiDIdx);
                                $stmtPush2->bindParam("taxi_Type", $taxi_Type2);
                                $stmtPush2->bindParam("taxi_MemIdx", $taxiDRMemIdx);
                                $stmtPush2->bindParam("taxi_MemId", $taxiDRMemId);
                                $stmtPush2->bindParam("reg_Date", $reg_Date);
                                $stmtPush2->execute();

                                /*푸시 관련 시작*/

                                //요청 수락한자가 다른 생성방에 요청했을 경우 취소 푸시
                                $mem_DToken = memMatchTokenInfo($taxiDRMemIdx);

                                $dtitle = "";
                                $dmsg = "요청취소됨 (타인매칭), 다른 노선을 선택해주세요.";

                                foreach ($mem_DToken as $k2 => $v2) {
                                    $dtokens = $mem_DToken[$k2];

                                    //알림할 내용들을 취합해서 $data에 모두 담는다. 프로젝트 의도에 따라 다른게 더 있을 수 있다.
                                    $dinputData = array("title" => $dtitle, "msg" => $dmsg, "state" => "0");

                                    //마지막에 알림을 보내는 함수를 실행하고 그 결과를 화면에 출력해 준다.
                                    $dpresult = send_Push($dtokens, $dinputData);
                                    // echo $dpresult;
                                }
                            }
                            /*푸시 관련 끝*/
                        }


                        //매칭요청 기본 삭제
                        $delQquery = "DELETE FROM TB_RTAXISHARING WHERE taxi_SIdx = :taxi_SIdx  AND taxi_RMemId = :taxi_RMemId ";
                        $delStmt = $DB_con->prepare($delQquery);
                        $delStmt->bindparam(":taxi_SIdx", $taxiSIdx);
                        $delStmt->bindparam(":taxi_RMemId", $taxiDRMemId);
                        $delStmt->execute();

                        //매칭요청 정보 삭제
                        $delQquery2 = "DELETE FROM TB_RTAXISHARING_INFO WHERE taxi_SIdx = :taxi_SIdx  AND taxi_RMemId = :taxi_RMemId ";
                        $delStmt2 = $DB_con->prepare($delQquery2);
                        $delStmt2->bindparam(":taxi_SIdx", $taxiSIdx);
                        $delStmt2->bindparam(":taxi_RMemId", $taxiDRMemId);
                        $delStmt2->execute();

                        //매칭요청 지도 삭제
                        $delQquery3 = "DELETE FROM TB_RTAXISHARING_MAP WHERE taxi_SIdx = :taxi_SIdx  AND taxi_RMemId = :taxi_RMemId";
                        $delStmt3 = $DB_con->prepare($delQquery3);
                        $delStmt3->bindparam(":taxi_SIdx", $taxiSIdx);
                        $delStmt3->bindparam(":taxi_RMemId", $taxiDRMemId);
                        $delStmt3->execute();
                    }
                }


                if ($taxiState == "2") { //예약출발일 경우
                    $taxiChkState = "4";  //예약요청완료
                    $statNm = "예약요청이 완료되었습니다.";
                } else if ($taxiState == "1") { //바로출발, 시간선택일 경우
                    $taxiChkState = "5";  //만남중
                    $statNm = "노선이 만남중으로 진행됩니다.";
                }

                //투게더 상태값 변경
                $upPQquery = "UPDATE TB_RTAXISHARING SET taxi_RState = :taxi_RState WHERE idx = :idx  LIMIT 1";
                $upPStmt = $DB_con->prepare($upPQquery);
                $upPStmt->bindparam(":taxi_RState", $taxiChkState);
                $upPStmt->bindparam(":idx", $idx);
                $upPStmt->execute();

                $regChkDate = DU_TIME_YMDHIS;           //상태 변경일

                //투게더 날짜 변경
                if ($taxiState == "2") { //예약출발일 경우
                    //얘약요청완료일 변경
                    $upMQquery = "UPDATE TB_RTAXISHARING_INFO SET reg_RDate = :reg_RDate WHERE taxi_RIdx = :taxi_RIdx  LIMIT 1";
                    $upMStmt = $DB_con->prepare($upMQquery);
                    $upMStmt->bindparam(":reg_RDate", $regChkDate);
                    $upMStmt->bindparam(":taxi_RIdx", $idx);
                    $upMStmt->execute();
                } else if ($taxiState == "1") { //바로출발, 시간선택일 경우
                    //만남중 변경일
                    $upMQquery = "UPDATE TB_RTAXISHARING_INFO SET reg_MDate = :reg_MDate WHERE taxi_RIdx = :taxi_RIdx  LIMIT 1";
                    $upMStmt = $DB_con->prepare($upMQquery);
                    $upMStmt->bindparam(":reg_MDate", $regChkDate);
                    $upMStmt->bindparam(":taxi_RIdx", $idx);
                    $upMStmt->execute();
                }

                //메이커 상태값 변경
                $upPQquery = "UPDATE TB_STAXISHARING SET taxi_State = :taxi_State WHERE idx = :idx  LIMIT 1";
                $upPStmt = $DB_con->prepare($upPQquery);
                $upPStmt->bindparam(":taxi_State", $taxiChkState);
                $upPStmt->bindparam(":idx", $taxiSIdx);
                $upPStmt->execute();


                $chkLocQuery1 = "SELECT taxi_SLng, taxi_SLat FROM TB_STAXISHARING_MAP WHERE taxi_Idx = :taxi_SIdx LIMIT 1;";
                $chkLocStmt1 = $DB_con->prepare($chkLocQuery1);
                $chkLocStmt1->bindparam(":taxi_SIdx", $taxiSIdx);
                $chkLocStmt1->execute();
                while ($chkLocrow1 = $chkLocStmt1->fetch(PDO::FETCH_ASSOC)) {
                    $res_lat =  $chkLocrow1['taxi_SLat'];                // 쉐어링 위치(Lat)
                    $res_lon =  $chkLocrow1['taxi_SLng'];                // 쉐어링 위치(Lng)
                }
                //푸시 전송 등록 여부 체크
                $cntPushQuery3 = "";
                $cntPushQuery3 = "SELECT count(idx) AS num FROM TB_SHARING_PUSH WHERE taxi_Idx = :taxi_Idx AND taxi_Type = :taxi_Type AND taxi_MemIdx = :taxi_MemIdx AND taxi_MemId = :taxi_MemId ";
                $cntPushStmt3 = $DB_con->prepare($cntPushQuery3);
                $cntPushStmt3->bindParam("taxi_Idx", $idx);
                $cntPushStmt3->bindParam("taxi_Type", $taxiChkState);
                $cntPushStmt3->bindParam("taxi_MemIdx", $taxiRMemIdx);
                $cntPushStmt3->bindParam("taxi_MemId", $taxiRMemId);
                $cntPushStmt3->execute();
                $cntPushRow3 = $cntPushStmt3->fetch(PDO::FETCH_ASSOC);
                $totalPushCnt3 = $cntPushRow3['num'];

                if ($totalPushCnt3 == "") {
                    $totalPushCnt3 = "0";
                } else {
                    $totalPushCnt3 =  $totalPushCnt3;
                }

                //푸시 전송 내역 저장
                if ($totalPushCnt3 < 1) {

                    //푸시 저장
                    $insPushQuery3 = "INSERT INTO TB_SHARING_PUSH (taxi_Idx, taxi_Type, taxi_MemIdx, taxi_MemId, reg_Date)
                             VALUES (:taxi_Idx, :taxi_Type, :taxi_MemIdx, :taxi_MemId, :reg_Date)";
                    $stmtPush3 = $DB_con->prepare($insPushQuery3);
                    $stmtPush3->bindParam("taxi_Idx", $idx);
                    $stmtPush3->bindParam("taxi_Type", $taxiChkState);
                    $stmtPush3->bindParam("taxi_MemIdx", $taxiRMemIdx);
                    $stmtPush3->bindParam("taxi_MemId", $taxiRMemId);
                    $stmtPush3->bindParam("reg_Date", $reg_Date);
                    $stmtPush3->execute();

                    /*푸시 관련 시작*/
                    //투게더에게 푸시
                    $mem_CToken = memMatchTokenInfo($taxiRMemIdx);

                    $ctitle = "";
                    $cmsg = $statNm;

                    foreach ($mem_CToken as $k3 => $v3) {
                        $ctokens = $mem_CToken[$k3];

                        //알림할 내용들을 취합해서 $data에 모두 담는다. 프로젝트 의도에 따라 다른게 더 있을 수 있다.
                        $cinputData = array("title" => $ctitle, "msg" => $cmsg, "state" => $taxiChkState);

                        //마지막에 알림을 보내는 함수를 실행하고 그 결과를 화면에 출력해 준다.
                        $cpresult = send_Push($ctokens, $cinputData);
                        // echo $cpresult;
                    }
                    /*푸시 관련 끝*/
                }

                //푸시 전송 등록 여부 체크
                $cntPushQuery4 = "";
                $cntPushQuery4 = "SELECT count(idx) AS num FROM TB_SHARING_PUSH WHERE taxi_Idx = :taxi_Idx AND taxi_Type = :taxi_Type AND taxi_MemIdx = :taxi_MemIdx AND taxi_MemId = :taxi_MemId ";
                $cntPushStmt4 = $DB_con->prepare($cntPushQuery4);
                $cntPushStmt4->bindParam("taxi_Idx", $taxiSIdx);
                $cntPushStmt4->bindParam("taxi_Type", $taxiChkState);
                $cntPushStmt4->bindParam("taxi_MemIdx", $taxiMemIdx);
                $cntPushStmt4->bindParam("taxi_MemId", $taxiMemId);
                $cntPushStmt4->execute();
                $cntPushRow4 = $cntPushStmt4->fetch(PDO::FETCH_ASSOC);
                $totalPushCnt4 = $cntPushRow4['num'];

                if ($totalPushCnt4 == "") {
                    $totalPushCnt4 = "0";
                } else {
                    $totalPushCnt4 =  $totalPushCnt4;
                }

                //푸시 전송 내역 저장
                if ($totalPushCnt4 < 1) {

                    //푸시 저장
                    $insPushQuery4 = "INSERT INTO TB_SHARING_PUSH (taxi_Idx, taxi_Type, taxi_MemIdx, taxi_MemId, reg_Date)
                             VALUES (:taxi_Idx, :taxi_Type, :taxi_MemIdx, :taxi_MemId, :reg_Date)";
                    $stmtPush4 = $DB_con->prepare($insPushQuery4);
                    $stmtPush4->bindParam("taxi_Idx", $taxiSIdx);
                    $stmtPush4->bindParam("taxi_Type", $taxiChkState);
                    $stmtPush4->bindParam("taxi_MemIdx", $taxiMemIdx);
                    $stmtPush4->bindParam("taxi_MemId", $taxiMemId);
                    $stmtPush4->bindParam("reg_Date", $reg_Date);
                    $stmtPush4->execute();

                    /*푸시 관련 시작*/

                    //메이커에게 푸시
                    $mem_RToken = memMatchTokenInfo($taxiMemIdx);

                    $ptitle = "";
                    $pmsg = $statNm;

                    foreach ($mem_RToken as $k4 => $v4) {
                        $ptokens = $mem_RToken[$k4];

                        //알림할 내용들을 취합해서 $data에 모두 담는다. 프로젝트 의도에 따라 다른게 더 있을 수 있다.
                        $pinputData = array("title" => $ptitle, "msg" => $pmsg, "state" => $taxiChkState);

                        //마지막에 알림을 보내는 함수를 실행하고 그 결과를 화면에 출력해 준다.
                        $presult = send_Push($ptokens, $pinputData);
                    }

                    /*푸시 관련 끝*/
                }

                $result = array("result" => true);
                $res_bit = 1;
            } else {

                if ($taxiState == "4") { //예약출발
                    $errorMsg = "현재 매칭 예약 완료된 신청자가 있습니다.";
                } else if ($taxiState == "5") { //기타
                    $errorMsg = "현재 매칭 승인된 신청자가 있습니다.";
                }

                $result = array("result" => false, "errorMsg" => $errorMsg);
            }
        } else if ($taxiRChk  == "2") { //매칭거절일 경우

            //투게더 상태값 변경
            $upPQquery = "UPDATE TB_RTAXISHARING SET taxi_RState = 3 WHERE idx = :idx  LIMIT 1";
            $upPStmt = $DB_con->prepare($upPQquery);
            $upPStmt->bindparam(":idx", $idx);
            $upPStmt->execute();

            //매칭 거절 사유 변경
            $upPQquery2 = "UPDATE TB_RTAXISHARING_INFO SET taxi_RCType = :taxi_RCType, taxi_RCancle = :taxi_RCancle WHERE taxi_RIdx = :taxi_RIdx  LIMIT 1";
            $upPStmt2 = $DB_con->prepare($upPQquery2);
            $upPStmt2->bindparam(":taxi_RCType", $taxiState);   //매칭타입
            $upPStmt2->bindparam(":taxi_RCancle", $taxiRCancle);
            $upPStmt2->bindparam(":taxi_RIdx", $idx);
            $upPStmt2->execute();

            $taxi_Type5 = "3";

            //푸시 전송 등록 여부 체크
            $cntPushQuery5 = "";
            $cntPushQuery5 = "SELECT count(idx) AS num FROM TB_SHARING_PUSH WHERE taxi_Idx = :taxi_Idx AND taxi_Type = :taxi_Type AND taxi_MemIdx = :taxi_MemIdx AND taxi_MemId = :taxi_MemId ";
            $cntPushStmt5 = $DB_con->prepare($cntPushQuery5);
            $cntPushStmt5->bindParam("taxi_Idx", $idx);
            $cntPushStmt5->bindParam("taxi_Type", $taxi_Type5);
            $cntPushStmt5->bindParam("taxi_MemIdx", $taxiRMemIdx);
            $cntPushStmt5->bindParam("taxi_MemId", $taxiRMemId);
            $cntPushStmt5->execute();
            $cntPushRow5 = $cntPushStmt5->fetch(PDO::FETCH_ASSOC);
            $totalPushCnt5 = $cntPushRow5['num'];

            if ($totalPushCnt5 == "") {
                $totalPushCnt5 = "0";
            } else {
                $totalPushCnt5 =  $totalPushCnt5;
            }

            //푸시 전송 내역 저장
            if ($totalPushCnt5 < 1) {

                //푸시 저장
                $insPushQuery5 = "INSERT INTO TB_SHARING_PUSH (taxi_Idx, taxi_Type, taxi_SMemId, taxi_MemId, reg_Date)
                             VALUES (:taxi_Idx, :taxi_Type, :taxi_SMemId, :taxi_MemId, :reg_Date)";
                $stmtPush5 = $DB_con->prepare($insPushQuery5);
                $stmtPush5->bindParam("taxi_Idx", $idx);
                $stmtPush5->bindParam("taxi_Type", $taxi_Type5);
                $stmtPush5->bindParam("taxi_SMemId", $taxiRSMemId);
                $stmtPush5->bindParam("taxi_MemId", $taxiRMemId);
                $stmtPush5->bindParam("reg_Date", $reg_Date);
                $stmtPush5->execute();

                /*푸시 관련 시작*/
                //요청 거절일경우 푸시
                $mem_NToken = memMatchTokenInfo($taxiRMemIdx);

                //회원 고유 아이디
                $nSidQuery = "SELECT mem_Os, mem_MPush, mem_Token from TB_MEMBERS WHERE mem_Id = :mem_Id AND b_Disply = 'N' ";
                $nSidStmt = $DB_con->prepare($nSidQuery);
                $nSidStmt->bindparam(":mem_Id", $taxiRMemId);
                $nSidStmt->execute();
                $nSidNum = $nSidStmt->rowCount();

                if ($nSidNum < 1) { //아닐경우
                } else {

                    while ($nSidRow = $nSidStmt->fetch(PDO::FETCH_ASSOC)) {

                        $nmemOs = $nSidRow['mem_Os'];         //os구분  (0 : 안드로이드, 1: 아이폰)
                        $nmemMPush = $nSidRow['mem_MPush'];     //푸시발송여부  (0 : 발송, 1: 발송불가)

                        $chkState = "3";  //거절
                        $nmsg = "요청이 취소되었습니다. 다른 노선을 선택해주세요.";

                        if ($nmemOs != "") { //os가 있을 경우
                            if ($nmemMPush == "0") { //푸시 수신 가능
                                $ntitle = "";
                                $nmsg = $nmsg;
                            } else {
                                $ntitle = "";
                                $nmsg = "";
                            }

                            foreach ($mem_NToken as $k => $v) {
                                $ntokens = $mem_NToken[$k];

                                //알림할 내용들을 취합해서 $data에 모두 담는다. 프로젝트 의도에 따라 다른게 더 있을 수 있다.
                                $ninputData = array("title" => $ntitle, "msg" => $nmsg, "state" => $chkState);

                                //마지막에 알림을 보내는 함수를 실행하고 그 결과를 화면에 출력해 준다.
                                $nresult = send_Push($ntokens, $ninputData);
                            }
                        }
                    }
                }

                /*푸시 관련 끝*/
            }


            //투게더 카운트 체크
            $cntMQuery = "SELECT count(taxi_MemId) AS num from TB_RTAXISHARING WHERE  taxi_SIdx = :taxi_SIdx AND taxi_RState = :taxi_RState ";
            $cntMtmt = $DB_con->prepare($cntMQuery);
            $cntMtmt->bindparam(":taxi_SIdx", $taxiSIdx);
            $cntMtmt->bindparam(":taxi_RState", $taxiRChk);
            $cntMtmt->execute();
            $cntMRow = $cntMtmt->fetch(PDO::FETCH_ASSOC);
            $cntMNum = $cntMRow['num'];

            if ($cntMNum == "0") { // 취소일경우
                //매칭 취소 신청이 하나도 없을 경우 매칭중 상태로 변경
                $upQquery = "UPDATE TB_STAXISHARING SET taxi_State = '1' WHERE idx = :idx  LIMIT 1";
                $upStmt = $DB_con->prepare($upQquery);
                $upStmt->bindparam(":idx", $taxiSIdx);
                $upStmt->execute();
            }

            $result = array("result" => true);
        } else { //매칭수락, 거절 둘다 아닐 경우
            $result = array("result" => false);
        }
    }


    dbClose($DB_con);
    $viewStmt = null;
    $cntStmt = null;
    $upMStmt = null;
    $matStmt = null;
    $chkStmt = null;
    $mDSidStmt = null;
    $cntPushStmt = null;
    $stmtPush = null;
    $mdelStmt = null;
    $mdelStmt2 = null;
    $mdelStmt3 = null;
    $dSidStmt = null;
    $mDSidStmt2 = null;
    $cntPushStmt2 = null;
    $delStmt = null;
    $delStmt2 = null;
    $delStmt3 = null;
    $dmcntStmt = null;
    $dmupStmt = null;
    $mcntStmt = null;
    $mupStmt = null;
    $cSidStmt = null;
    $pSidStmt = null;
    $mDSidStmt3 = null;
    $cntPushStmt3 = null;
    $mDSidStmt4 = null;
    $cntPushStmt4 = null;
    $upPStmt = null;
    $upPStmt2 = null;
    $nSidStmt = null;
    $mDSidStmt5 = null;
    $cntPushStmt5 = null;
    $cntMtmt = null;
    $upStmt = null;
    $chkLocStmt1 = null;
} else {
    $result = array("result" => false);
}

echo str_replace('\\/', '/', json_encode($result, JSON_UNESCAPED_UNICODE));

// 성공할 경우 curl로 현황 동기화
if ($res_bit == 1) {
    common_Form(array("lat" => (float)$res_lat, "lon" => (float)$res_lon));
}
