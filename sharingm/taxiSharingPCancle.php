<?

/*======================================================================================================================

* 프로그램			: 메이커 예약요청완료, 만남중, 만남완료, 이동중 취소 건
* 페이지 설명		: 메이커 예약요청완료, 만남중, 만남완료, 이동중 취소 건
* 파일명            : taxiSharingPcancle.php

========================================================================================================================*/

include "../lib/common.php";
include "../lib/functionDB.php";  //공통 db함수

$mem_Id = trim($memId);        //아이디
$idx = trim($idx);                            //매칭생성 고유번호

if ($mem_Id != "" && $idx != "") {  //아이디, 매칭생성고유번호

    $DB_con = db1();


    $mem_Idx = memIdxInfo($mem_Id);   //회원 주아이디

    $mnameSql = " , ( SELECT mem_NickNm FROM TB_MEMBERS WHERE TB_MEMBERS.mem_Id = TB_STAXISHARING.taxi_MemId AND TB_MEMBERS.b_Disply = 'N' limit 1 ) AS mNickNm  ";
    $chkCntQuery = "SELECT idx, taxi_State {$mnameSql} from TB_STAXISHARING WHERE taxi_MemIdx = :taxi_MemIdx AND taxi_MemId = :taxi_MemId  AND idx = :idx AND taxi_State IN ('4', '5', '6')  LIMIT 1   "; //예약요청완료, 만남중, 이동중
    //echo $chkCntQuery."<BR>";
    //exit;
    $stmt = $DB_con->prepare($chkCntQuery);
    $stmt->bindparam(":taxi_MemIdx", $mem_Idx);
    $stmt->bindparam(":taxi_MemId", $mem_Id);
    $stmt->bindparam(":idx", $idx);
    $stmt->execute();
    $num = $stmt->rowCount();

    if ($num < 1) { //매칭값이 맞지 않을 경우
        $result = array("result" => false, "errorMsg" => "매칭 상태값이  맞지 않습니다. 다시 확인해 주세요.");
    } else {  // 취소가능

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $taxiSIdx = trim($row['idx']);            // 생성자 고유번호
            $taxiState = trim($row['taxi_State']);            // 매칭신청 상태값
            $mNickNm = trim($row['mNickNm']);               // 생성자 닉네임

            //생성자 기타 정보
            $minfoeQuery = "";
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
        }

        //투게더 정보 가져옴
        $viewQuery = "SELECT idx, taxi_RMemIdx, taxi_RMemId FROM TB_RTAXISHARING WHERE taxi_SIdx = :taxi_SIdx AND taxi_MemIdx = :taxi_MemIdx AND taxi_MemId = :taxi_MemId AND taxi_MemId = :taxi_MemId AND taxi_RState IN ('4', '5','6','7')  LIMIT 1  ";
        $viewStmt = $DB_con->prepare($viewQuery);
        $viewStmt->bindparam(":taxi_SIdx", $idx);
        $viewStmt->bindparam(":taxi_MemIdx", $mem_Idx);
        $viewStmt->bindparam(":taxi_MemId", $mem_Id);
        $viewStmt->execute();
        $vNum = $viewStmt->rowCount();

        //$vNum =1;
        if ($vNum < 1) { //아닐 경우
            $result = array("result" => false, "errorMsg" => "취소하신 정보에 대한 요청자 정보가 없습니다. 다시 확인해 주세요.");
        } else {  // 결과값이 있을 경우 취소 가능
            while ($vrow = $viewStmt->fetch(PDO::FETCH_ASSOC)) {
                $taxiRIdx = trim($vrow['idx']);                     // 매칭요청 고유번호
                $taxiRMemIdx = trim($vrow['taxi_RMemIdx']);        // 매칭요청 고유 아이디
                $taxiRMemId = trim($vrow['taxi_RMemId']);        // 매칭요청 아이디
            }
        }

        // 메이커가 취소 시켰을 때  ( 0:메이커 취소, 1 : 본인취소)
        $reg_CDate = DU_TIME_YMDHIS;           //취소일

        //투게더 취소 상태 변경
        $upMQquery = "UPDATE TB_RTAXISHARING SET taxi_RState = '8' WHERE idx = :idx AND taxi_RMemIdx = :taxi_RMemIdx AND taxi_RMemId = :taxi_RMemId LIMIT 1";
        //$upMQquery = "UPDATE TB_RTAXISHARING SET taxi_RState = '8' WHERE idx = $taxiRIdx AND taxi_RMemId = $taxiRMemId LIMIT 1";
        $upMStmt = $DB_con->prepare($upMQquery);
        $upMStmt->bindparam(":idx", $taxiRIdx);
        $upMStmt->bindparam(":taxi_RMemIdx", $taxiRMemIdx);
        $upMStmt->bindparam(":taxi_RMemId", $taxiRMemId);
        $upMStmt->execute();

        //투게더 취소 기타 변경 (본인 : 0, 아닐 경우 : 1)
        $upMQquery2 = "UPDATE TB_RTAXISHARING_INFO SET taxi_MCancle = '1', taxi_MState = :taxi_MState, reg_CDate = :reg_CDate WHERE taxi_RIdx = :taxi_RIdx AND taxi_RMemIdx = :taxi_RMemIdx AND taxi_RMemId = :taxi_RMemId LIMIT 1";
        //echo $upMQquery2."<BR>";
        //exit;
        $upMStmt2 = $DB_con->prepare($upMQquery2);
        $upMStmt2->bindparam(":taxi_MState", $taxiState);
        $upMStmt2->bindparam(":reg_CDate", $reg_CDate);
        $upMStmt2->bindparam(":taxi_RIdx", $taxiRIdx);
        $upMStmt2->bindparam(":taxi_RMemIdx", $taxiRMemIdx);
        $upMStmt2->bindparam(":taxi_RMemId", $taxiRMemId);
        $upMStmt2->execute();

        //메이커 취소 상태로 변경 (본인 : 0, 아닐 경우 : 1)
        $upPQquery = "UPDATE TB_STAXISHARING SET taxi_State = '8', taxi_MCancle = '0', taxi_MState = :taxi_MState, reg_CDate = :reg_CDate WHERE idx = :idx AND taxi_MemIdx = :taxi_MemIdx AND taxi_MemId = :taxi_MemId LIMIT 1";
        //echo $upPQquery."<BR>";
        $upPStmt = $DB_con->prepare($upPQquery);
        $upPStmt->bindparam(":taxi_MState", $taxiState);
        $upPStmt->bindparam(":reg_CDate", $reg_CDate);
        $upPStmt->bindparam(":idx", $idx);
        $upPStmt->bindparam(":taxi_MemIdx", $mem_Idx);
        $upPStmt->bindparam(":taxi_MemId", $mem_Id);
        $upPStmt->execute();

        if ($taxiState == "4") {  //예약완료중 취소
            $statNm = "예약노선을 ";
            $canNm = "예약요청완료중인 노선 취소";
        } else if ($taxiState == "5") {
            $statNm = "만남중 ";
            $canNm = "만남중인 노선 취소";
        }

        $taxi_Type = "0";

        //푸시 전송 등록 여부 체크
        $cntPushQuery = "";
        $cntPushQuery = "SELECT count(idx) AS num FROM TB_SHARING_PUSH WHERE taxi_Idx = :taxi_Idx AND taxi_Type = :taxi_Type AND taxi_MemIdx = :taxi_MemIdx AND taxi_MemId = :taxi_MemId ";
        $cntPushStmt = $DB_con->prepare($cntPushQuery);
        $cntPushStmt->bindParam("taxi_Idx", $taxiRIdx);
        $cntPushStmt->bindParam("taxi_Type", $taxi_Type);
        $cntPushStmt->bindParam("taxi_MemIdx", $taxiRMemIdx);
        $cntPushStmt->bindParam("taxi_MemId", $taxiRMemId);
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
            $stmtPush->bindParam("taxi_Idx", $taxiRIdx);
            $stmtPush->bindParam("taxi_Type", $taxi_Type);
            $stmtPush->bindParam("taxi_MemIdx", $taxiRMemIdx);
            $stmtPush->bindParam("taxi_MemId", $taxiRMemId);
            $stmtPush->bindParam("reg_Date", $reg_CDate);
            $stmtPush->execute();

            /*푸시 관련 시작*/
            $mem_Token = memMatchTokenInfo($taxiRMemIdx);

            $title = "";
            $msg = $statNm . $mNickNm . "님이 취소하였습니다.";

            foreach ($mem_Token as $k => $v) {
                $tokens = $mem_Token[$k];
                $inputData = array("title" => $title, "msg" => $msg, "state" => "8");
                $presult = send_Push($tokens, $inputData);
            }
            /*푸시 끝*/
        }

        //취소 신청자 회원정보
        $mnSql = "  , ( SELECT mem_NickNm FROM TB_MEMBERS WHERE TB_MEMBERS.mem_Id = TB_MEMBERS_ETC.mem_Id AND TB_MEMBERS.b_Disply = 'N' limit 1 ) AS memNickNm  ";
        $matCQuery = "SELECT mem_McCnt {$mnSql} FROM TB_MEMBERS_ETC WHERE mem_Id = :mem_Id  LIMIT 1 ";
        $matCStmt = $DB_con->prepare($matCQuery);
        $matCStmt->bindparam(":mem_Id", $mem_Id);
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

        $taxiMType = "p";  //생성자 (p) 요청자 (c)
        $taxi_Memo = DU_TIME_YMDHIS . ' 메이커(' . $memNickNm . ') 본인이 ' . $canNm . "함.";

        //패널티 내역 등록 여부 체크
        $cntQuery = "SELECT count(taxi_SIdx) AS num FROM TB_PENALTY_HISTORY WHERE taxi_SIdx = :taxi_SIdx AND taxi_RIdx = :taxi_RIdx AND taxi_MemIdx = :taxi_MemIdx AND taxi_MemId = :taxi_MemId ";
        $cntStmt = $DB_con->prepare($cntQuery);
        $cntStmt->bindparam(":taxi_SIdx", $idx);
        $cntStmt->bindparam(":taxi_RIdx", $taxiRIdx);
        $cntStmt->bindparam(":taxi_MemIdx", $mem_Idx);
        $cntStmt->bindparam(":taxi_MemId", $mem_Id);
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
            $stmt->bindParam("taxi_SIdx", $idx);
            $stmt->bindParam("taxi_RIdx", $taxiRIdx);
            $stmt->bindParam("taxi_MemIdx", $mem_Idx);
            $stmt->bindParam("taxi_MemId", $mem_Id);
            $stmt->bindParam("taxi_Memo", $taxi_Memo);
            $stmt->bindParam("reg_Date", $reg_CDate);
            $stmt->execute();
            $DB_con->lastInsertId();
        }

        //매칭 취소 횟수
        $totMatCCnt = (int)$memMcCnt + 1;

        //매칭 거절 횟수 변경
        $upmatCQquery = "UPDATE TB_MEMBERS_ETC SET mem_McCnt = :mem_McCnt WHERE mem_Idx = :mem_Idx AND mem_Id = :mem_Id  LIMIT 1";
        //echo $upMemQuery."<BR>";
        //exit;
        $upmatCStmt = $DB_con->prepare($upmatCQquery);
        $upmatCStmt->bindparam(":mem_McCnt", $totMatCCnt);
        $upmatCStmt->bindparam(":mem_Idx", $mem_Idx);
        $upmatCStmt->bindparam(":mem_Id", $mem_Id);
        $upmatCStmt->execute();

        $result = array("result" => true);
    }

    dbClose($DB_con);
    $stmt = null;
    $minfoetmt = null;
    $viewStmt = null;
    $upMStmt = null;
    $upMStmt2 = null;
    $upPStmt = null;
    $cPointStmt = null;
    $cntStmt = null;
    $cntPushStmt = null;
    $stmtPush = null;
    $matCStmt = null;
    $upmatCStmt = null;
    $upLvStmt = null;
} else {
    $result = array("result" => false);
}

echo str_replace('\\/', '/', json_encode($result, JSON_UNESCAPED_UNICODE));
