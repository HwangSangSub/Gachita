<?
/*======================================================================================================================

* 프로그램			: 오늘의 OX 퀴즈 정답 확인하는 페이지
* 페이지 설명		: 오늘의 OX 퀴즈 정답인 경우 미션 수행 기록 및 포인트 적립.
* 파일명            : oxQuizYes.php

========================================================================================================================*/

include "../udev/lib/common.php";
include "../lib/alertLib.php";
include DU_COM . "/functionDB.php";

$mission_Idx = trim($idx);              // 미션고유번호
$mem_Idx = trim($memIdx);               // 회원고유번호
$ox_Idx = trim($oxIdx);                 // 오늘의 OX 퀴즈 고유번호
$ox_Val = trim($oxVal);                 // 오늘의 OX 퀴즈 회원이 선택한 정답 (1: 그렇다, 2: 아니다)

$DB_con = db1();

// 미션 수행 이력 조회
// $mCnt = missionHistoryChk($mission_Idx, $mem_Idx);  // mCnt = 0 : 미션 수행 이력 없음, mCnt = 1 : 미션 수행 이력 있음
$mCnt = 0;
if ($mCnt == 0) { // 미션 수행 이력이 없는 경우

    // OX 퀴즈 정보 확인하기.
    $query = "SELECT ox_Answer, ox_Explanation FROM TB_OX WHERE idx = :ox_Idx";
    $stmt = $DB_con->prepare($query);
    $stmt->bindparam(":ox_Idx", $ox_Idx);
    $stmt->execute();
    $count = $stmt->rowCount();

    if ($count > 0) {

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $mCnt = $row['mCnt']; // 미션 성공 수
        // OX 퀴즈 정보가 있는 경우
        $ox_Answer = $row['ox_Answer']; // 정답
        $ox_Explanation = $row['ox_Explanation']; // 설명
        if ($ox_Answer == $ox_Val) {
            $missionInsHistory = missionInsHistory($mission_Idx, $mem_Idx);
            if ($missionInsHistory) {
                // 정답인 경우
                missionMemberPointGive($mission_Idx, $mem_Idx, true);
                $data['result'] = true;
                $data['oxBit'] = true; // 정답
                $data['exp'] = $ox_Explanation;
            }
        } else {
            $missionInsHistory = missionInsHistory($mission_Idx, $mem_Idx);
            if ($missionInsHistory) {
                // 오답인 경우
                missionMemberPointGive($mission_Idx, $mem_Idx, false);
                $data['result'] = true;
                $data['oxBit'] = false; // 오답
            }
        }
    } else {
        // OX 퀴즈 정보가 없는 경우
        $data['result'] = false;
    }
} else {
    // 미션 수행 이력이 있는 경우
    $data['result'] = false;
}

$output = str_replace('\\\/', '/', json_encode($data, JSON_UNESCAPED_UNICODE));
echo  urldecode($output);

dbClose($DB_con);
$stmt = null;
