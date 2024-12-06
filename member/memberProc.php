<?
include "../lib/common.php";

$mem_Id = trim($memId);                //아이디
$mem_NickNm = ($nickname);            //닉네임
if($mem_NickNm == ""){
    $mem_NickNm = "";
}
$mem_SnsChk = trim($snsChk);        //SNS체크여부 (Kakao, google)
$mem_Tel = trim($memTel);           //연락처
$mem_Email = trim($memEmail);        //이메일
$mem_Sex = trim($memSex);            //성별 ( 0: 남자, 1:여자)
$mem_Seat = trim($memSeat);            //좌석 ( 0: 앞자리, 1: 뒷자리)
$mem_Os = trim($memOs);                //Os운영체제 (0: 안드로이드,1: 아이폰, 2:기타(웹만사용)  )
$member_Ch_Idx = trim($idx);               // 추천할 회원 고유번호

if ($ie) { //익슬플로러일경우
    $mem_NickNm = iconv('euc-kr', 'utf-8', $mem_NickNm);
} else {
    $mem_NickNm = $mem_NickNm;
}


if ($mem_Id != "" && $mem_SnsChk != "") {

    $DB_con = db1();

    $memQuery = "SELECT idx, b_Disply FROM TB_MEMBERS WHERE mem_Id = :mem_Id";
    $stmt = $DB_con->prepare($memQuery);
    $stmt->bindparam(":mem_Id", $mem_Id);
    $stmt->execute();
    $num = $stmt->rowCount();

    if ($num < 1) { //주 ID가 없을 경우 회원가입 시작
    } else {  //등록된 회원이 있을 경우
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $mem_Idx = $row['idx'];                 //회원고유번호
            $bDisply = $row['b_Disply'];            //탈퇴여부
        }
    }

    if ($bDisply == "N") { //회원 가입이 되어 있을 경우

        //로그인횟수
        $memSql = "  , ( SELECT login_Cnt FROM TB_MEMBERS_ETC WHERE TB_MEMBERS_ETC.mem_Idx = TB_MEMBERS.idx AND TB_MEMBERS_ETC.mem_Id = TB_MEMBERS.mem_Id limit 1 ) AS login_Cnt  ";
        $memQuery = "SELECT mem_NickNm, mem_Lv {$memSql} from TB_MEMBERS  WHERE idx = :mem_Idx AND mem_Id = :mem_Id AND b_Disply = 'N' ";

        $stmt = $DB_con->prepare($memQuery);
        $stmt->bindparam(":mem_Idx", $mem_Idx);
        $stmt->bindparam(":mem_Id", $mem_Id);
        $mem_Id = $mem_Id;
        $stmt->execute();
        $num = $stmt->rowCount();

        if ($num < 1) { //아닐경우
        } else {
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {

                $mem_Id = $mem_Id;                          // 아이디
                $mem_NickNm = $row['mem_NickNm'];           // 닉네임
                $mem_Lv = $row['mem_Lv'];                   // 등급
                $login_Cnt = $row['login_Cnt'];             // 로그인 횟수

                # 마지막 로그인 시간을 업데이트 한다.
                $upQquery = "UPDATE TB_MEMBERS_INFO SET login_Date = now() WHERE mem_Idx = :mem_Idx AND mem_Id = :mem_Id LIMIT 1";
                $upStmt = $DB_con->prepare($upQquery);
                $upStmt->bindparam(":mem_Idx", $mem_Idx);
                $upStmt->bindparam(":mem_Id", $mem_Id);
                $mem_Id = $mem_Id;
                $upStmt->execute();

                # 로그인 횟수 증가.
                $upQquery2 = "UPDATE TB_MEMBERS_ETC SET login_Cnt = :login_Cnt WHERE mem_Idx = :mem_Idx AND mem_Id = :mem_Id LIMIT 1";
                $upStmt2 = $DB_con->prepare($upQquery2);
                $upStmt2->bindparam(":login_Cnt", $login_Cnt);
                $upStmt2->bindparam(":mem_Idx", $mem_Idx);
                $upStmt2->bindparam(":mem_Id", $mem_Id);
                $mem_Id = $mem_Id;
                $login_Cnt = $login_Cnt + 1;
                $upStmt2->execute();
            }
        }
        $result = array("result" => true, "idx" => (int)$mem_Idx);
    } else { //회원탈퇴 및 신규회원 가입

        $chk_member = 0;
        if ($member_Ch_Idx != "") {
            $mem_Query = "SELECT idx FROM TB_MEMBERS WHERE idx = :idx";
            $mem_Stmt = $DB_con->prepare($mem_Query);
            $mem_Stmt->bindParam(":idx", $member_Ch_Idx);
            $mem_Stmt->execute();
            $mem_num = $mem_Stmt->rowCount();
            if ($mem_num > 0) {
                $mem_ch_Query = "SELECT COUNT(idx) AS cnt FROM TB_MEMBERS_ETC WHERE mem_ChCode = :mem_ChCode";
                $mem_ch_Stmt = $DB_con->prepare($mem_ch_Query);
                $mem_ch_Stmt->bindParam(":mem_ChCode", $member_Ch_Idx);
                $mem_ch_Stmt->execute();
                $mem_ch_Row = $mem_ch_Stmt->fetch(PDO::FETCH_ASSOC);
                $mem_Ch_Cnt = $mem_ch_Row['cnt'];      // 해당 회원이 받은 추천 수

                $config_Query = "SELECT con_ChCnt FROM TB_CONFIG";
                $config_Stmt = $DB_con->prepare($config_Query);
                $config_Stmt->execute();
                $config_Row = $config_Stmt->fetch(PDO::FETCH_ASSOC);
                $con_ChCnt = $config_Row['con_ChCnt'];      // 해당 회원이 받은 추천 수

                if ((int)$mem_Ch_Cnt >= (int)$con_ChCnt) {
                    $chk_member = 0;
                } else {
                    $chk_member = 1;
                }
            } else {
                $chk_member = 0;
            }
        }
        $mem_Lv = 14;                                                     // 등급
        $b_Disply = "N";                                                 //탈퇴여부
        $reg_Date = DU_TIME_YMDHIS;                                         //등록일

        //회원 기본테이블 저장
        $insQuery = "INSERT INTO TB_MEMBERS (mem_Id, mem_NickNm, mem_Tel, mem_Lv, b_Disply, mem_Os, reg_date ) VALUES (:mem_Id, :mem_NickNm, :mem_Tel, :mem_Lv, :b_Disply, :mem_Os, :reg_Date)";
        $stmt = $DB_con->prepare($insQuery);
        $stmt->bindParam("mem_Id", $mem_Id);
        $stmt->bindParam("mem_NickNm", $mem_NickNm);
        $stmt->bindParam("mem_Tel", $mem_Tel);
        $stmt->bindParam("mem_Lv", $mem_Lv);
        $stmt->bindParam("b_Disply", $b_Disply);
        $stmt->bindParam("mem_Os", $mem_Os);
        $stmt->bindParam("reg_Date", $reg_Date);
        $stmt->execute();

        $mIdx = $DB_con->lastInsertId();  //저장된 idx 값

        if ($stmt->rowCount() > 0) { //삽입 성공

            //회원 정보테이블 저장
            $insInFoQuery = "INSERT INTO TB_MEMBERS_INFO (mem_Idx, mem_Id, mem_SnsChk, mem_Email, mem_Sex, mem_Seat ) VALUES (:mem_Idx, :mem_Id, :mem_SnsChk, :mem_Email, :mem_Sex, :mem_Seat )";
            $stmtInfo = $DB_con->prepare($insInFoQuery);
            $stmtInfo->bindParam("mem_Idx", $mIdx);
            $stmtInfo->bindParam("mem_Id", $mem_Id);
            $stmtInfo->bindParam("mem_SnsChk", $mem_SnsChk);
            $stmtInfo->bindParam("mem_Email", $mem_Email);
            $stmtInfo->bindparam(":mem_Sex", $mem_Sex);
            $stmtInfo->bindparam(":mem_Seat", $mem_Seat);
            $stmtInfo->execute();

            //회원 기타테이블 저장
            if ($chk_member == 1) {
                $insEtcQuery = "INSERT INTO TB_MEMBERS_ETC (mem_Idx, mem_Id, mem_ChCode) VALUES (:mem_Idx, :mem_Id, :mem_ChCode)";
                $stmtEtc = $DB_con->prepare($insEtcQuery);
                $stmtEtc->bindParam("mem_Idx", $mIdx);
                $stmtEtc->bindParam("mem_ChCode", $member_Ch_Idx);
                $stmtEtc->bindParam("mem_Id", $mem_Id);
                $stmtEtc->execute();
            } else {
                $insEtcQuery = "INSERT INTO TB_MEMBERS_ETC (mem_Idx, mem_Id) VALUES (:mem_Idx, :mem_Id)";
                $stmtEtc = $DB_con->prepare($insEtcQuery);
                $stmtEtc->bindParam("mem_Idx", $mIdx);
                $stmtEtc->bindParam("mem_Id", $mem_Id);
                $stmtEtc->execute();
            }
            
            // 신규가입이벤트 조회
            $newEventQuery = "SELECT con_NewEventBit, con_NewEventDate, con_NewEventPoint FROM TB_CONFIG";
            $newEventStmt = $DB_con->prepare($newEventQuery);
            $newEventStmt->execute();
            $newEventRow = $newEventStmt->fetch(PDO::FETCH_ASSOC);
            $con_NewEventBit = $newEventRow['con_NewEventBit'];      // 신규이벤트 진행 여부 (0: 진행안함, 1: 진행)
            $con_NewEventDate = $newEventRow['con_NewEventDate'];      // 신규이벤트 종료일.
            $con_NewEventPoint = $newEventRow['con_NewEventPoint'];      // 신규이벤트시 지급 포인트.

            $nowDate = strtotime(date("Y-m-d"));
            $conNewEventDate = strtotime($con_NewEventDate);
            //이벤트 진행
            if ($con_NewEventBit == 1) {
                //현재 날짜가 이벤트 종료일 보다 작을 경우에 포인트 지급하기.
                if ($nowDate < $conNewEventDate || $nowDate == $conNewEventDate) {
                    $upEtcQuery = "UPDATE TB_MEMBERS_ETC SET mem_Point = :con_NewEventPoint WHERE mem_Idx = :mem_Idx LIMIT 1";
                    $upEtcStmt = $DB_con->prepare($upEtcQuery);
                    $upEtcStmt->bindParam("con_NewEventPoint", $con_NewEventPoint);
                    $upEtcStmt->bindParam("mem_Idx", $mIdx);
                    $upEtcStmt->execute();

                    $insPointHistoryQuery = "UPDATE TB_MEMBERS_ETC SET mem_Point = :con_NewEventPoint WHERE mem_Idx = :mem_Idx LIMIT 1";
                    $insPointHistoryStmt = $DB_con->prepare($insPointHistoryQuery);
                    $insPointHistoryStmt->bindParam("con_NewEventPoint", $con_NewEventPoint);
                    $insPointHistoryStmt->bindParam("mem_Idx", $mIdx);
                    $insPointHistoryStmt->execute();

                    // 적립한 포인트 푸시내역 남기기.
                    $insPointHistory_Sign = "0"; // +기호
                    $insPointHistory_State = "5"; // 신규가입이벤트 적립
                    $insPointHistory_Memo = DU_TIME_YMDHIS . '
신규가입 이벤트 적립';
                    $insPointHistoryQuery = "INSERT INTO TB_POINT_HISTORY (taxi_MemId, taxi_MemIdx, taxi_OrdPoint, taxi_OrgPoint, taxi_Memo, taxi_Sign, taxi_PState, reg_Date) VALUES (:taxi_MemId, :taxi_MemIdx, :taxi_OrdPoint, 0, :taxi_Memo, :taxi_Sign, :taxi_PState, :reg_Date)";
                    //echo $insQuery."<BR>";
                    //exit;
                    $insPointHistoryStmt = $DB_con->prepare($insPointHistoryQuery);
                    $insPointHistoryStmt->bindParam("taxi_MemId", $mem_Id);
                    $insPointHistoryStmt->bindParam("taxi_MemIdx", $mIdx);
                    $insPointHistoryStmt->bindParam("taxi_OrdPoint", $con_NewEventPoint);
                    $insPointHistoryStmt->bindParam("taxi_Memo", $insPointHistory_Memo);
                    $insPointHistoryStmt->bindParam("taxi_Sign", $insPointHistory_Sign);
                    $insPointHistoryStmt->bindParam("taxi_PState", $insPointHistory_State);
                    $insPointHistoryStmt->bindParam("reg_Date", $reg_Date);
                    $insPointHistoryStmt->execute();
                }
            }
            $result = array("result" => true, "idx" => (int)$mIdx);
        } else { //등록시 에러
            $result = array("result" => false, "errorMsg" => "회원등록에 실패했습니다. 관리자에게 이메일 문의 부탁드립니다.");
        }
    }

    dbClose($DB_con);
    $stmt = null;
    $cntMStmt = null;
    $stmtInfo = null;
    $stmtEtc = null;
    $stmtMap = null;
    $upStmt = null;
    $upStmt2 = null;
} else { //빈값일 경우
    $result = array("result" => false);
}

echo json_encode($result);