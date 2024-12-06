<?
include "../lib/common.php";
include "../lib/functionDB.php";  //공통 db함수

$mem_Id = trim($memId);            //아이디
$mem_Idx = memIdxInfo($mem_Id);   //회원 주아이디

if ($mem_Id != "") {  //아이디,  최근 개월 있을 경우

    $DB_con = db1();
    /* 포인트 현황 */
    $hisQuery = "SELECT idx, mission_Idx, taxi_MemId, taxi_OrdNo, taxi_OrdPoint, taxi_Memo, taxi_Sign, taxi_PState, taxi_SubTitle, taxi_OrdType, reg_Date, res_Date FROM TB_POINT_HISTORY WHERE taxi_MemIdx = :taxi_MemIdx AND taxi_PState = 6 ORDER BY res_Date DESC";
    //echo $hisQuery."<BR>";
    //exit;
    $hisStmt = $DB_con->prepare($hisQuery);
    $hisStmt->bindparam(':taxi_MemIdx', $mem_Idx, PDO::PARAM_STR);
    $hisStmt->execute();
    $mNum = $hisStmt->rowCount();

    if ($mNum < 1) { //아닐경우
        $chkResult = "0";
    } else {
        $chkResult = "1";
        $data  = [];
        while ($hrow = $hisStmt->fetch(PDO::FETCH_ASSOC)) {

            $idx = $hrow['idx'];                              // 포인트내역 고유번호
            $mission_Idx = $hrow['mission_Idx'];              // 미션고유번호
            $taxi_MemId = $hrow['taxi_MemId'];                // 회원아이디
            $taxi_OrdNo = $hrow['taxi_OrdNo'];                // 주문번호
            $taxiOrdPoint = $hrow['taxi_OrdPoint'];           // 포인트금액
            $taxiSign = $hrow['taxi_Sign'];                   // 포인트구분 (0: +, 1: -)
            $taxi_PState = $hrow['taxi_PState'];              // 구분 (0: 매칭, 1: 적립, 2: 환전, 3: 추천인 적립, 4: 포인트적립(카드), 5: 신규가입 이벤트, 6.적립예정, 7:미션적립)
            $taxi_SubTitle = $hrow['taxi_SubTitle'];          // 포인트 내역메모
            $taxi_OrdType = $hrow['taxi_OrdType'];            // 결제타입 (1: 카드, 2: 보유포인트결제)
            $res_Date = $hrow['res_Date'];                    // 적립예정일
            $resDate = substr($res_Date, 0, 10);
            if ($taxi_SubTitle == "가치타기 인증") {
                $taxiPState = '이벤트 적립';
                $taxi_Memo = "가치있는 가치타기 인증";
            } else {
                $taxiPState = '미션 적립';
                $mission = missionInfoChk($mission_Idx);
                $taxi_Memo = trim(preg_replace("/\r|\n/", " ", $mission['mName']));
            }


            // : 내역상세
            $mresult = ["idx" => (int)$idx, "taxiOrdPoint" => (int)$taxiOrdPoint, "taxiPState" => (string)$taxiPState, "taxiMemo" => (string)$taxi_Memo, "resDate" => (string)$resDate];
            array_push($data, $mresult);
        }

        $chkData = [];
        $chkData["result"] = true;
        $chkData['lists'] = $data;
    }

    if ($chkResult  == "1") {
        $output = str_replace('\\\/', '/', json_encode($chkData, JSON_UNESCAPED_UNICODE));
    } else if ($chkResult  == "0") {
        $chkData2["result"] = true;
        $chkData['lists'] = [];
        $output = str_replace('\\\/', '/', json_encode($chkData2, JSON_UNESCAPED_UNICODE));
    }

    echo  urldecode($output);

    dbClose($DB_con);
    $cntStmt = null;
    $hisStmt = null;
    $chkStmt = null;
} else {
    $result = array("result" => false, "errorMsg" => "조회가능한 정보가 없습니다. 관리자에게 문의바랍니다.");
    echo json_encode($result, JSON_UNESCAPED_UNICODE);
}
