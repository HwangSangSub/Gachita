<?

/*======================================================================================================================

* 프로그램			: 메이커 투게더 현재 상태값 확인
* 페이지 설명		: 메이커 투게더 현재 상태값 확인
* 파일명           : taxiSharingState.php

========================================================================================================================*/

include "../lib/common.php";
include "../lib/functionDB.php";  //공통 db함수


$mem_Id = trim($memId);                //아이디

if ($mem_Id != "") {  //아이디 여부가 경우

    $DB_con = db1();

    $deviceId = memDeviceIdInfo($mem_Id);  //디바이스아이디

    if ($deviceId != "") {
        $deviceId = $deviceId;
    } else {
        $deviceId = "";
    }

    //생성자 상태값
    $chkSQuery = "";
    $chkSQuery = "SELECT idx, taxi_State FROM TB_STAXISHARING WHERE taxi_MemId = :taxi_MemId AND taxi_State NOT IN ( '7', '8', '9', '10') ORDER BY idx desc LIMIT 1 ";
    //echo $viewQuery."<BR>";
    //exit;
    $chkSStmt = $DB_con->prepare($chkSQuery);
    $chkSStmt->bindparam(":taxi_MemId", $mem_Id);
    $chkSStmt->execute();
    $chkSNum = $chkSStmt->rowCount();

    if ($chkSNum < 1) { //아닐경우
        $totalCnt = 0;
        $totalCnt = (int)$totalCnt;
    } else {

        $totalCnt = (int)$chkSNum;

        while ($chkSrow = $chkSStmt->fetch(PDO::FETCH_ASSOC)) {
            $idx = toNumber($chkSrow['idx']);          // 고유번호
            $taxi_State = trim($chkSrow['taxi_State']);          // 매칭생성아이디
            $taxi_State = (int)$taxi_State;


            if ($taxi_State >= 2) {

                //요청자 상태값
                $chkRSQuery = "";
                $chkRSQuery = "SELECT idx FROM TB_RTAXISHARING WHERE taxi_MemId = :taxi_MemId AND taxi_SIdx = :taxi_SIdx LIMIT 1";
                $chkRSStmt = $DB_con->prepare($chkRSQuery);
                $chkRSStmt->bindparam(":taxi_MemId", $mem_Id);
                $chkRSStmt->bindparam(":taxi_SIdx", $idx);
                $chkRSStmt->execute();
                $chkRSNum = $chkRSStmt->rowCount();

                if ($chkRSNum < 1) { //아닐경우
                } else {
                    while ($chkRSrow = $chkRSStmt->fetch(PDO::FETCH_ASSOC)) {
                        $ridx = trim($chkRSrow['idx']);          // 고유번호
                        $ridx = (int)$ridx;
                    }
                }
            }
        }

        if ($taxi_State >= 2) { //매칭요청 이후
            $sresult = array("idx" => (int)$idx, "ridx" => (int)$ridx, "state" => (string)$taxi_State);
        } else {
            $sresult = array("idx" => (int)$idx, "state" => (string)$taxi_State);
        }
    }

    //요청자 상태값
    $chkRQuery = "";
    $chkRQuery = "SELECT taxi_SIdx, idx, taxi_RState FROM TB_RTAXISHARING WHERE taxi_RMemId = :taxi_RMemId AND taxi_RState NOT IN ( '7', '8', '9', '10') ORDER BY idx desc LIMIT 1";
    //echo $chkRQuery."<BR>";
    //exit;
    $chkRStmt = $DB_con->prepare($chkRQuery);
    $chkRStmt->bindparam(":taxi_RMemId", $mem_Id);
    $chkRStmt->execute();
    $chkRNum = $chkRStmt->rowCount();

    if ($chkRNum < 1) { //아닐경우
        $totalRCnt = 0;
        $totalRCnt = (int)$totalRCnt;
    } else {
        $totalRCnt = (int)$chkRNum;

        $data  = [];
        while ($chkRrow = $chkRStmt->fetch(PDO::FETCH_ASSOC)) {
            $sidx = trim($chkRrow['taxi_SIdx']);          // 생성자고유번호
            $sidx = (int)$sidx;
            $idx = trim($chkRrow['idx']);          // 고유번호
            $idx = (int)$idx;
            $taxi_RState = trim($chkRrow['taxi_RState']);          // 매칭요청아이디
            $taxi_RState = (int)$taxi_RState;

            $mresult = ["sidx" => (int)$sidx, "idx" => (int)$idx, "state" => (string)$taxi_RState];
            array_push($data, $mresult);
        }
    }

    $chkData["result"] = true;
    $chkData["deviceId"] = $deviceId;  //현재카운트


    if ($totalCnt > 0) { //아닐경우
        $chkData['pdata'] = $sresult; //생성자
    }

    if ($totalRCnt > 0) { //아닐경우
        $chkData['rdata'] = $data; //요청자
    }

    $output = str_replace('\\\/', '/', json_encode($chkData, JSON_NUMERIC_CHECK | JSON_UNESCAPED_UNICODE));

    echo $output;

    dbClose($DB_con);
    $chkSStmt = null;
    $chkRStmt = null;
    $mSidStmt = null;
    $upMStmt = null;
} else {
    $result = array("result" => false, "errorMsg" => "조회정보값이 없습니다. 관리자에게 문의바랍니다.");

    echo str_replace('\\/', '/', json_encode($result, JSON_UNESCAPED_UNICODE));
}
