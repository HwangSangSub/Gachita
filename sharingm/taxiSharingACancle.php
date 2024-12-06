<?

/*======================================================================================================================

* 프로그램			: 메이커 매칭중, 매칭요청, 예약요청 취소 건
* 페이지 설명		: 메이커 매칭중, 매칭요청, 예약요청 취소 건
* 파일명                 : taxiSharingAcancle.php

========================================================================================================================*/

include "../lib/common.php";
include "../lib/functionDB.php";  //공통 db함수
include "../lib/sharing_send.php";  //현황확인을 위한 함수


$mem_Id = trim($memId);        //아이디
$idx = trim($idx);                            //매칭생성 고유번호
$res_bit = 0; // 성공여부 (0: 실패, 1: 성공)

if ($mem_Id != "" && $idx != "") {  //아이디, 매칭생성고유번호

    $DB_con = db1();

    $mem_Idx = memIdxInfo($mem_Id);   //회원 주아이디

    $chkCntQuery = "SELECT count(taxi_MemId) AS num, taxi_State FROM TB_STAXISHARING WHERE taxi_MemIdx = :taxi_MemIdx AND taxi_MemId = :taxi_MemId AND idx = :idx AND taxi_State IN ('1', '2', '3')  LIMIT 1   "; //매칭요청, 예약요청
    // echo $chkCntQuery."<BR>";
    // exit;
    $stmt = $DB_con->prepare($chkCntQuery);
    $stmt->bindparam(":taxi_MemIdx", $mem_Idx);
    $stmt->bindparam(":taxi_MemId", $mem_Id);
    $stmt->bindparam(":idx", $idx);
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $num = $row['num'];
    $taxi_State = $row['taxi_State'];

    if ($num < 1) { //매칭값이 맞지 않을 경우
        $result = array("result" => false, "errorMsg" => "현재 진행 중인 매칭 노선이 없습니다. 취소가 불가능합니다.");
    } else {  // 매칭생성,매칭중 일 경우 수정 가능

        //해당 생성노선에 따른 요청자 정보 값 조회

        // 메이커 상태값
        $mStaeSql = "  , ( SELECT taxi_State FROM TB_STAXISHARING WHERE TB_STAXISHARING.taxi_MemIdx = TB_RTAXISHARING.taxi_MemIdx AND TB_STAXISHARING.taxi_MemId = TB_RTAXISHARING.taxi_MemId limit 1 ) AS taxiState  ";

        $matchQuery = "SELECT idx, taxi_RMemIdx, taxi_RMemId {$mStaeSql} FROM TB_RTAXISHARING WHERE taxi_SIdx = :taxi_SIdx AND taxi_MemIdx = :taxi_MemIdx AND taxi_MemId = :taxi_MemId ";
        $matStmt = $DB_con->prepare($matchQuery);
        $matStmt->bindparam(":taxi_SIdx", $idx);
        $matStmt->bindparam(":taxi_MemIdx", $mem_Idx);
        $matStmt->bindparam(":taxi_MemId", $mem_Id);
        $matStmt->execute();
        $matNum = $matStmt->rowCount();

        if ($matNum < 1) { //요청한 신청한 건수가 없음
        } else {  // 요청한 신청건수 가 있을 경우 삭제

            while ($matRow = $matStmt->fetch(PDO::FETCH_ASSOC)) {
                $taxiMRIdx = trim($matRow['idx']);               // 투게더 고유번호
                $taxiRMemIdx = trim($matRow['taxi_RMemIdx']);   // 투게더 고유 아이디
                $taxiRMemId = trim($matRow['taxi_RMemId']);    // 투게더 아이디
                $taxiState = trim($matRow['taxiState']);       // 메이커 상태값

                //매칭요청 기본 삭제
                $delRQquery = "UPDATE TB_RTAXISHARING SET taxi_RState = '8' WHERE taxi_SIdx = :taxi_SIdx LIMIT 1";
                $delRStmt = $DB_con->prepare($delRQquery);
                $delRStmt->bindparam(":taxi_SIdx", $idx);
                $delRStmt->execute();

                //매칭요청 정보 삭제
                $delRQquery2 = "UPDATE TB_RTAXISHARING_INFO SET reg_CDate = now(), taxi_RMemo = '메이커의 인한 취소' WHERE taxi_SIdx = :taxi_SIdx LIMIT 1";
                $delRStmt2 = $DB_con->prepare($delRQquery2);
                $delRStmt2->bindparam(":taxi_SIdx", $idx);
                $delRStmt2->execute();

                if ($taxiState == "1") {
                    $statNm = "매칭중인";
                } else if ($taxiState == "2") {
                    $statNm = "매칭요청중인";
                } else if ($taxiState == "3") {
                    $statNm = "예약요청중인";
                }

                if ($title == "") {
                    $title = "";
                } else {
                    $title = $title;
                }


                $msg = "현재 " . $statNm . " 노선이 메이커의 취소로 인하여 취소 되었습니다.";

                $mem_Token = memMatchTokenInfo($taxiRMemIdx);

                if ($mem_Token != "") { //토큰값이 있을 경우

                    //알림할 내용들을 취합해서 $data에 모두 담는다. 프로젝트 의도에 따라 다른게 더 있을 수 있다.
                    $inputData = array("title" => $title, "msg" => $msg, "state" => $taxiState);

                    //마지막에 알림을 보내는 함수를 실행하고 그 결과를 화면에 출력해 준다.
                    $presult = send_Push($mem_Token, $inputData);
                    //echo $presult;
                }
            }
        }
        //메이커 취소처리
        $upMQquery11 = "UPDATE TB_STAXISHARING SET taxi_State = '8', taxi_MState = :taxi_MState, reg_CDate = NOW() WHERE idx = :idx LIMIT 1";
        $upMStmt11 = $DB_con->prepare($upMQquery11);
        $upMStmt11->bindparam(":idx", $idx);
        $upMStmt11->bindparam(":taxi_MState", $taxi_State);
        $upMStmt11->execute();

        $chkLocQuery1 = "SELECT taxi_SLng, taxi_SLat FROM TB_STAXISHARING_MAP WHERE taxi_Idx = :taxi_SIdx LIMIT 1;";
        $chkLocStmt1 = $DB_con->prepare($chkLocQuery1);
        $chkLocStmt1->bindparam(":taxi_SIdx", $idx);
        $chkLocStmt1->execute();
        while ($chkLocrow1 = $chkLocStmt1->fetch(PDO::FETCH_ASSOC)) {
            $res_lat =  $chkLocrow1['taxi_SLat'];                // 쉐어링 위치(Lat)
            $res_lon =  $chkLocrow1['taxi_SLng'];                // 쉐어링 위치(Lng)
        }

        $result = array("result" => true);
        $res_bit = 1; // 성공여부 (0: 실패, 1: 성공)

    }

    dbClose($DB_con);
    $stmt = null;
    $matStmt = null;
    $delRStmt = null;
    $delRStmt2 = null;
    $delRStmt3 = null;
    $delStmt = null;
    $delStmt2 = null;
    $delStmt3 = null;
    $chkLocStmt1 = null;
} else {
    $result = array("result" => false);
}

echo str_replace('\\/', '/', json_encode($result, JSON_UNESCAPED_UNICODE));


// 성공할 경우 curl로 현황 동기화
if ($res_bit == 1) {
    common_Form(array("lat" => (float)$res_lat, "lon" => (float)$res_lon));
}
