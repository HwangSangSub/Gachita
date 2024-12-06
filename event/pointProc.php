<?
/*======================================================================================================================

* 프로그램			: 미션 포인트 받기
* 페이지 설명		: 미션 포인트 받기
* 파일명            : pointProc.php

========================================================================================================================*/

include "../udev/lib/common.php";
include "../lib/alertLib.php";
include DU_COM . "/functionDB.php";

$mission_Idx = trim($idx);                  // 미션고유번호
$mem_Idx = trim($mem_Idx);                  // 회원고유번호
$nowdate = date('Y-m');                     // 이번달

$DB_con = db1();

// 미션 수행 이력 조회
$mCnt = missionHistoryChk($mission_Idx, $mem_Idx);  // mCnt = 0 : 미션 수행 이력 없음, mCnt = 1 : 미션 수행 이력 있음
if ($mCnt == 0) { // 미션 수행 이력이 없는 경우
    if ($mission_Idx != '5') { // 등급달성은 별도로 처리하기 위함으로 제외
        $missionInsHistory = missionInsHistory($mission_Idx, $mem_Idx);
        if ($missionInsHistory) {
            // 미션 수행이 등록이 된 경우.
            // 미션 성공으로 포인트 지급하기.
            $missionMemberPointGive = missionMemberPointGive($mission_Idx, $mem_Idx, true);
            if ($missionMemberPointGive) {
                //포인트 지급이 정상인 경우
                $data['result'] = true;
                $data['pointBit'] = true;
            } else {
                //포인트 지급에 실패한 경우
                $data['result'] = true;
                $data['pointBit'] = false;
            }
        } else {
            // 미션수행 기록이 등록이 안된 경우
            $data['result'] = false;
        }
    } else {    // 등급달성 미션의 경우에만

        //매칭생성 진행 건수
        $memSCntQuery = "SELECT count(idx) AS num FROM TB_STAXISHARING WHERE taxi_MemIdx = :taxi_MemIdx AND DATE_FORMAT(taxi_SDate, '%Y-%m') = :searchDate AND taxi_State IN ( '7', '10' ) ";
        $chkCntStmt = $DB_con->prepare($memSCntQuery);
        $chkCntStmt->bindparam(":taxi_MemIdx", $mem_Idx);
        $chkCntStmt->bindparam(":searchDate", $nowdate);
        $chkCntStmt->execute();
        $chkCntRow = $chkCntStmt->fetch(PDO::FETCH_ASSOC);
        $chkCntNum = $chkCntRow['num'];

        if ($chkCntNum <> "") {
            $chkCntNum = $chkCntNum;
        } else {
            $chkCntNum = 0;
        }

        //매칭요청 진행 건수
        $memRCntQuery = "SELECT count(idx) AS num FROM TB_RTAXISHARING WHERE taxi_RMemIdx = :taxi_RMemIdx AND DATE_FORMAT(reg_Date, '%Y-%m') = :searchDate AND taxi_RState IN ( '7', '10' ) "; //완료, 취소를 제외한 경우
        $chkCntRStmt = $DB_con->prepare($memRCntQuery);
        $chkCntRStmt->bindparam(":taxi_RMemIdx", $mem_Idx);
        $chkCntRStmt->bindparam(":searchDate", $nowdate);
        $chkCntRStmt->execute();
        $chkCntRrow = $chkCntRStmt->fetch(PDO::FETCH_ASSOC);
        $chkCntRNum = $chkCntRrow['num'];

        if ($chkCntRNum <> "") {
            $chkCntRNum = $chkCntRNum;
        } else {
            $chkCntRNum = 0;
        }

        $matCnt = (int)$chkCntNum + (int)$chkCntRNum;   // 총 완료 건수


        $memMatChkQuery = "SELECT memMatCnt FROM TB_MEMBER_LEVEL WHERE memLv = 13";
        $memMatChkStmt = $DB_con->prepare($memMatChkQuery);
        $memMatChkStmt->bindparam(":matCnt", $matCnt);
        $memMatChkStmt->execute();
        $memMatChkRow = $memMatChkStmt->fetch(PDO::FETCH_ASSOC);
        $config_MatCnt = $memMatChkRow['memMatCnt'];
        $configMatCnt = (int)$config_MatCnt;

        //성공
        if($matCnt >= $configMatCnt){
            $missionInsHistory = missionInsHistory($mission_Idx, $mem_Idx);
            if ($missionInsHistory) {
                // 미션 수행이 등록이 된 경우.
                // 미션 성공으로 포인트 지급하기.
                $missionMemberPointGive = missionMemberPointGive($mission_Idx, $mem_Idx, true);
                if ($missionMemberPointGive) {
                    //포인트 지급이 정상인 경우
                    $data['result'] = true;
                    $data['pointBit'] = true;
                } else {
                    //포인트 지급에 실패한 경우
                    $data['result'] = true;
                    $data['pointBit'] = false;
                }
            } else {
                // 미션수행 기록이 등록이 안된 경우
                $data['result'] = false;
            }
        }else{
            $data['result'] = false;
        }

    }
} else {
    // 미션 수행 이력이 있는 경우
    $data['result'] = false;
}

$output = str_replace('\\\/', '/', json_encode($data, JSON_UNESCAPED_UNICODE));
echo  urldecode($output);

dbClose($DB_con);
$stmt = null;
