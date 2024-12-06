<?
/*======================================================================================================================

* 프로그램			: 미션별 서버에서 처리
* 페이지 설명		: 미션별 서버에서 처리
* 파일명          : eventChk.php

========================================================================================================================*/

include "../lib/common.php";
include "../lib/functionDB.php";  //공통 db함수

$DB_con = db1();
$mem_Id = trim($memId);
$mem_Idx = memIdxInfo($mem_Id);        // 회원 고유아이디
$m_Idx = trim($idx);                   // 미션 고유번호
$reg_Date = DU_TIME_YMDHIS;         // 등록일

if ($mem_Idx != "" && $m_Idx != "") {
    // 미션처리 내역 조회
    $missionQuery = "SELECT idx, m_Group, m_Type, m_Name, m_SPoint, m_FPoint, m_Img, m_SCnt, m_DCnt, m_Link, m_Status, m_Locat, m_Time FROM TB_MISSION WHERE idx = :m_Idx";
    $missionStmt = $DB_con->prepare($missionQuery);
    $missionStmt->bindparam(":m_Idx", $m_Idx);
    $missionStmt->execute();
    $missionCount = $missionStmt->rowCount();
    if ($missionCount < 1) { //없을 경우
        $result = array("result" => false, "errorMsg" => "등록된 미션이 없습니다. 확인 후 다시 시도해주세요.");
    } else {
        while ($missionRow = $missionStmt->fetch(PDO::FETCH_ASSOC)) {
            $mission_Idx = $missionRow['idx'];              // 미션고유번호
            $m_Group = $missionRow['m_Group'];              // 미션구분(1: 링크, 2: 등급달성, 3: 횟수)
            $m_Type = $missionRow['m_Type'];                // 미션타입(1:1회 한정미션, 2: 한달 미션)
            $m_Name = $missionRow['m_Name'];                // 미션제목
            $m_SPoint = $missionRow['m_SPoint'];            // 미션보상포인트(성공, 정답)
            $m_FPoint = $missionRow['m_FPoint'];            // 미션보상포인트(오답)
            $m_Img = $missionRow['m_Img'];                  // 미션이미지   
            $m_SCnt = $missionRow['m_SCnt'];                // 미션수행횟수
            $m_DCnt = $missionRow['m_DCnt'];                // 하루최대가능 수(0 이면 제한없이 가능)
            $m_Time = $missionRow['m_Time'];                // 미션 보상 간 지급 간격 (분단위) ==> 0이면 제한없이 가능
            $m_Link = $missionRow['m_Link'];                // 미션링크페이지
            $m_Locat = $missionRow['m_Locat'];              // 위치(0: 내부, 1: 외부, 2: 웹뷰)

            // 회원 미션 실행횟수 확인
            $mChkQuery = "SELECT idx FROM TB_MISSION_HISTORY WHERE mission_Idx = :mission_Idx AND mem_Idx = :mem_Idx";
            $mChkStmt = $DB_con->prepare($mChkQuery);
            $mChkStmt->bindparam(":mission_Idx", $mission_Idx);
            $mChkStmt->bindparam(":mem_Idx", $mem_Idx);
            $mChkStmt->execute();
            $mChkCount = $mChkStmt->rowCount();

            if ($mChkCount < 1) { //없을 경우
                $mInsQuery = "INSERT INTO TB_MISSION_HISTORY SET mem_Idx = :mem_Idx, mem_Id = :mem_Id, mission_Idx = :mission_Idx, reg_Date = :reg_Date";
                $mInsStmt = $DB_con->prepare($mInsQuery);
                $mInsStmt->bindparam(":mem_Idx", $mem_Idx);
                $mInsStmt->bindparam(":mem_Id", $mem_Id);
                $mInsStmt->bindparam(":mission_Idx", $mission_Idx);
                $mInsStmt->bindparam(":reg_Date", $reg_Date);
                $mInsStmt->execute();
                $mhIdx = $DB_con->lastInsertId();  //저장된 idx 값

                if ($mhIdx > 0) {
                    $result = array("result" => true, "link" => (string)$m_Link, "locat" => (string)$m_Locat);
                } else {
                    $result = array("result" => false, "errorMsg" => "미션기록 등록 실패하였습니다. 확인 후 다시 시도해주세요.");
                }
            } else {
                $result = array("result" => false, "errorMsg" => "이미 완료된 미션입니다. 확인 후 다시 시도해주세요.");
            }
        }
    }
    // 미션정보 조회하기
    dbClose($DB_con);
    $noticeStmt = null;
    $eventStmt = null;
} else {
    $result = array("result" => false, "errorMsg" => "조회가능한 정보가 없습니다. 관리자에게 문의바랍니다.");
}
echo json_encode($result, JSON_UNESCAPED_UNICODE);
