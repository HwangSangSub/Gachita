<?

/*======================================================================================================================

* 프로그램			: 투게더 예약요청완료, 만남중, 만남완료, 이동중 취소 건
* 페이지 설명		: 투게더 예약요청완료, 만남중, 만남완료, 이동중 취소 건
* 파일명            : taxiSharingMPCancle.php

========================================================================================================================*/

include "../lib/common.php";
include "../lib/functionDB.php";  //공통 db함수


$mem_Id = trim($memId);        //아이디
$mem_Idx = memIdxInfo($mem_Id);   //회원 주아이디    
$idx = trim($idx);            //투게더 고유번호

if ($mem_Idx != "" && $idx != "") {  //아이디, 투게더고유번호

    $DB_con = db1();

    $mnameSql = " , ( SELECT mem_NickNm FROM TB_MEMBERS WHERE TB_MEMBERS.mem_Id = TB_RTAXISHARING.taxi_RMemId AND TB_MEMBERS.b_Disply = 'N' limit 1 ) AS mNickNm  ";
    $chkCntQuery = "SELECT taxi_SIdx, taxi_MemIdx, taxi_MemId, taxi_RMemIdx, taxi_RState {$mnameSql}  FROM TB_RTAXISHARING WHERE taxi_RMemIdx = :taxi_RMemIdx  AND idx = :idx AND taxi_RState IN ('4', '5')   LIMIT 1 "; //예약요청완료, 만남중
    $stmt = $DB_con->prepare($chkCntQuery);
    $stmt->bindparam(":taxi_RMemIdx", $mem_Idx);
    $stmt->bindparam(":idx", $idx);
    $stmt->execute();
    $num = $stmt->rowCount();
    // echo $num."<BR>";
    // exit;

    if ($num < 1) { //매칭값이 맞지 않을 경우
        $result = array("result" => false, "errorMsg" => "만남 중(예약 요청 완료)인 노선이 없습니다. 취소가 불가능합니다.");
    } else {  // 취소가능
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $taxiSIdx = trim($row['taxi_SIdx']);                    // 매칭생성 고유번호
            $taxiMemIdx = trim($row['taxi_MemIdx']);                // 메이커 고유 아이디
            $taxiMemId = trim($row['taxi_MemId']);                    // 메이커 아이디
            $taxiRMemIdx = trim($row['taxi_RMemIdx']);                // 투게더 고유아이디
            $taxiRState = trim($row['taxi_RState']);                // 신청 상태값
            $mNickNm = trim($row['mNickNm']);                 //생성자 닉네임

            if ($taxiRState == "4") {  //예약완료중 취소
                $statNm = "예약노선을 ";
                $canNm = "예약요청완료중인 노선 취소";
            } else if ($taxiRState == "5") {
                $statNm = "만남중 ";
                $canNm = "만남중인 노선 취소";
            }


            //생성자 기타 정보
            $minfoeQuery = "SELECT taxi_Type, taxi_Route FROM TB_STAXISHARING_INFO WHERE taxi_Idx = :taxi_Idx LIMIT 1 ";
            //echo $minfoeQuery."<BR>";
            //exit;
            $minfoetmt = $DB_con->prepare($minfoeQuery);
            $minfoetmt->bindparam(":taxi_Idx", $taxiSIdx);
            $minfoetmt->execute();
            $minfoeNum = $minfoetmt->rowCount();
            //echo $minfoeNum."<BR>";

            if ($minfoeNum < 1) { //아닐경우
            } else {
                while ($minfoeRow = $minfoetmt->fetch(PDO::FETCH_ASSOC)) {
                    $taxiType = trim($minfoeRow['taxi_Type']);                        //출발타입 ( 0: 바로출발, 1: 예약출발)
                    $taxiRoute = trim($minfoeRow['taxi_Route']);                    // 경유가능여부 ( 0: 경유가능, 1: 경유불가)
                }
            }

            $taxi_Type = "0";
            $reg_Date = DU_TIME_YMDHIS;        //푸시등록일

            /*푸시 관련 시작*/
            $mem_Token = memMatchTokenInfo($taxiMemIdx);

            $title = "";
            $msg = $statNm . $mNickNm . "님이 취소하였습니다.";

            foreach ($mem_Token as $k => $v) {
                $tokens = $mem_Token[$k];
                $inputData = array("title" => $title, "msg" => $msg, "state" => "8");
                $presult = send_Push($tokens, $inputData);
            }

            /*푸시 관련 끝*/
        }

        // 투게더가 취소 시켰을 때  ( 0:투게더 취소, 1 : 본인취소)
        $reg_CDate = DU_TIME_YMDHIS;           //취소일

        //투게더 취소 상태 변경
        $upMQquery = "UPDATE TB_RTAXISHARING SET taxi_RState = '8' WHERE idx = :idx AND taxi_RMemId = :taxi_RMemId LIMIT 1";
        $upMStmt = $DB_con->prepare($upMQquery);
        $upMStmt->bindparam(":idx", $idx);
        $upMStmt->bindparam(":taxi_RMemId", $mem_Id);
        $upMStmt->execute();

        //투게더 취소 기타 변경 (본인 : 0, 아닐 경우 : 1)
        $upMQquery2 = "UPDATE TB_RTAXISHARING_INFO SET taxi_MCancle = '0', taxi_MState = :taxi_MState, reg_CDate = :reg_CDate WHERE taxi_RIdx = :taxi_RIdx AND taxi_RMemId = :taxi_RMemId LIMIT 1";
        $upMStmt2 = $DB_con->prepare($upMQquery2);
        $upMStmt2->bindparam(":taxi_MState", $taxiRState);
        $upMStmt2->bindparam(":reg_CDate", $reg_CDate);
        $upMStmt2->bindparam(":taxi_RIdx", $idx);
        $upMStmt2->bindparam(":taxi_RMemId", $mem_Id);
        $upMStmt2->execute();


        //메이커 취소 상태로 변경 (본인 : 0, 아닐 경우 : 1)
        $upPQquery = "UPDATE TB_STAXISHARING SET taxi_State = '8', taxi_MCancle = '1', taxi_MState = :taxi_MState, reg_CDate = :reg_CDate WHERE idx = :idx  AND taxi_MemId = :taxi_MemId LIMIT 1";
        $upPStmt = $DB_con->prepare($upPQquery);
        $upPStmt->bindparam(":taxi_MState", $taxiRState);
        $upPStmt->bindparam(":reg_CDate", $reg_CDate);
        $upPStmt->bindparam(":idx", $taxiSIdx);
        $upPStmt->bindparam(":taxi_MemId", $taxiMemId);
        $upPStmt->execute();

        //취소 신청자 회원정보
        $mnSql = "  , ( SELECT mem_NickNm FROM TB_MEMBERS WHERE TB_MEMBERS.idx = TB_MEMBERS_ETC.mem_Idx AND TB_MEMBERS.b_Disply = 'N' limit 1 ) AS memNickNm  ";
        $matCQuery = "SELECT mem_McCnt {$mnSql} FROM TB_MEMBERS_ETC WHERE mem_Idx = :mem_Idx  LIMIT 1 ";
        $matCStmt = $DB_con->prepare($matCQuery);
        $matCStmt->bindparam(":mem_Idx", $mem_Idx);
        $matCStmt->execute();
        $memCMatCnt = $matCStmt->rowCount();

        if ($memCMatCnt < 1) { //아닐경우
        } else {
            while ($matCRow = $matCStmt->fetch(PDO::FETCH_ASSOC)) {
                $memNickNm = trim($matCRow['memNickNm']);        // 취소신청자 닉네임

                if ($memNickNm == "") {
                    $memNickNm = "탈퇴회원";        // 취소신청자 닉네임
                } else {
                    $memNickNm = $memNickNm;        // 취소신청자 닉네임
                }

                $memMcCnt = trim($matCRow['mem_McCnt']);             // 회원 매칭 취소 횟수

                if ($memMcCnt == "") {
                    $memMcCnt = "0";
                } else {
                    $memMcCnt =  $memMcCnt;
                }
            }
        }

        $taxiMType = "c";  //생성자 (p) 요청자 (c)
        $taxi_Memo = DU_TIME_YMDHIS . ' 투게더(' . $memNickNm . ') 본인이 ' . $canNm . "함.";

        //패널티 내역 등록 여부 체크
        $cntQuery = "SELECT count(taxi_SIdx) AS num FROM TB_PENALTY_HISTORY WHERE taxi_SIdx = :taxi_SIdx AND taxi_RIdx = :taxi_RIdx AND taxi_MemIdx = :taxi_MemIdx AND taxi_MemId = :taxi_MemId ";
        $cntStmt = $DB_con->prepare($cntQuery);
        $cntStmt->bindparam(":taxi_SIdx", $taxiSIdx);
        $cntStmt->bindparam(":taxi_RIdx", $idx);
        $cntStmt->bindparam(":taxi_MemIdx", $taxiRMemIdx);   //주문취소 고유아이디
        $cntStmt->bindparam(":taxi_MemId", $mem_Id);         //주문취소 아이디
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
            $insQuery = "INSERT INTO TB_PENALTY_HISTORY (taxi_Mtype, taxi_SIdx, taxi_RIdx, taxi_MemIdx, taxi_MemId, taxi_Memo, reg_Date)
                            VALUES (:taxi_Mtype, :taxi_SIdx, :taxi_RIdx, :taxi_MemIdx, :taxi_MemId, :taxi_Memo, :reg_Date)";
            // echo $insQuery."<BR>";
            //exit;
            $stmt = $DB_con->prepare($insQuery);
            $stmt->bindParam("taxi_Mtype", $taxiMType);
            $stmt->bindParam("taxi_SIdx", $taxiSIdx);
            $stmt->bindParam("taxi_RIdx", $idx);
            $stmt->bindParam("taxi_MemIdx", $taxiRMemIdx); //주문취소 고유아이디
            $stmt->bindParam("taxi_MemId", $mem_Id);       //주문 취소 아이디
            $stmt->bindParam("taxi_Memo", $taxi_Memo);
            $stmt->bindParam("reg_Date", $reg_CDate);
            $stmt->execute();
            $DB_con->lastInsertId();
        }

        //매칭 취소 횟수
        $totMatCCnt = $memMcCnt + 1;

        //매칭 거절 횟수 변경
        $upmatCSQquery = "UPDATE TB_MEMBERS_ETC SET mem_McCnt = :mem_McCnt WHERE mem_Idx = :mem_Idx AND mem_Id = :mem_Id  LIMIT 1";
        // echo $upmatCSQquery."<BR>";
        //exit;
        $upmatCStmt = $DB_con->prepare($upmatCSQquery);
        $upmatCStmt->bindparam(":mem_McCnt", $totMatCCnt);
        $upmatCStmt->bindparam(":mem_Idx", $mem_Idx);
        $upmatCStmt->bindparam(":mem_Id", $mem_Id);
        $upmatCStmt->execute();

        // echo "여기들어옴";
        // exit;

        $result = array("result" => true);
    }

    dbClose($DB_con);
    $stmt = null;
    $viewStmt = null;
    $minfoetmt = null;
    $mSidStmt = null;
    $cntPushStmt = null;
    $stmtPush = null;
    $upMStmt = null;
    $upMStmt2 = null;
    $upPStmt = null;
    $matCStmt = null;
    $upLvStmt = null;
    $upmatCStmt = null;
    $upmatCSStmt = null;
} else {
    $result = array("result" => false);
}

echo str_replace('\\/', '/', json_encode($result, JSON_UNESCAPED_UNICODE));
