<?
/*======================================================================================================================

* 프로그램			: 공지 및 이벤트, 오늘의 미션 조회
* 페이지 설명		: 공지 및 이벤트, 오늘의 미션 조회
* 파일명          : boardTotalList.php

========================================================================================================================*/

include "../lib/common.php";
include "../lib/functionDB.php";  //공통 db함수

$DB_con = db1();
$mem_Id = trim($memId);
$mem_Idx = memIdxInfo($mem_Id);        // 회원 고유아이디
$reg_Date = DU_TIME_YMDHIS;         // 등록일
$now_Month = date('Y-m', strtotime($reg_Date));   // 이번달
$now_Day = date('Y-m-d', strtotime($reg_Date));   // 오늘

$chkData['result'] = true;

// 공지사항 조회하기
$noticeCntQuery = " SELECT idx, b_Title FROM TB_BOARD WHERE b_Not = 'Y' AND b_Idx = 1 AND  b_Disply = 'Y' ORDER BY idx DESC";
$noticeCntStmt = $DB_con->prepare($noticeCntQuery);
$noticeCntStmt->execute();
$noticeCount = $noticeCntStmt->rowCount();
if ($noticeCount < 1) { //없을 경우
    // $chkData['noticeCount'] = 0;
    $chkData['noticeLists'] = [];
} else {
    $noticeQuery = " SELECT idx, b_Title FROM TB_BOARD WHERE b_Not = 'Y' AND b_Idx = 1 AND  b_Disply = 'Y' AND t_Disply = 'Y' ORDER BY t_Sort LIMIT 3";
    $noticeStmt = $DB_con->prepare($noticeQuery);
    $noticeStmt->execute();
    // $chkData['noticeCount'] = $noticeCount;
    $notice = [];
    while ($noticeRow = $noticeStmt->fetch(PDO::FETCH_ASSOC)) {
        $idx = $noticeRow['idx'];                       // 제목
        $title = $noticeRow['b_Title'];                 // 링크
        $link = "https://" . $_SERVER['HTTP_HOST'] . "/board/noticeView.php?idx=" . $idx;
        $result = array("title" => $title, "link" => $link);
        array_push($notice, $result);
    }
    $chkData['noticeLists'] = $notice;
}

// 이벤트 조회하기
$eventCntQuery = "SELECT idx FROM TB_EVENT ORDER BY idx DESC";
$eventCntStmt = $DB_con->prepare($eventCntQuery);
$eventCntStmt->execute();
$eventCount = $eventCntStmt->rowCount();
if ($eventCount < 1) { //없을 경우
    // $chkData['eventCount'] = 0;
    $chkData['eventLists'] = [];
} else {
    $eventQuery = "SELECT idx, event_Title, event_Url, event_EndBit FROM TB_EVENT WHERE event_Tdisply = 'Y' ORDER BY event_Tsort LIMIT 3";
    $eventStmt = $DB_con->prepare($eventQuery);
    $eventStmt->execute();
    // $chkData['eventCount'] = $eventCount;
    $event = [];
    while ($eventRow = $eventStmt->fetch(PDO::FETCH_ASSOC)) {
        $event_Title = $eventRow['event_Title'];           // 제목
        $event_EndBit = $eventRow['event_EndBit'];         // 이벤트 종료여부(N: 미종료, Y: 종료)
        if ($event_EndBit == 'Y') {
            $eventTitle = "[종료] " . $event_Title;
        } else {
            $eventTitle = $event_Title;
        }
        $event_Url = $eventRow['event_Url'];               // 링크
        $result = ["title" => (string)$eventTitle, "link" => (string)$event_Url];
        array_push($event, $result);
    }
    $chkData['eventLists'] = $event;
}

// 미션완료 포인트 조회
$missionSumQuery = "SELECT SUM(m_SPoint * m_SCnt) AS point FROM TB_MISSION WHERE m_Status = '2'";
$missionSumStmt = $DB_con->prepare($missionSumQuery);
$missionSumStmt->execute();
$missionSumCount = $missionSumStmt->rowCount();
if ($missionSumCount < 1) { //없을 경우
    $missionPoint = 0;
} else {
    while ($missionSumRow = $missionSumStmt->fetch(PDO::FETCH_ASSOC)) {
        $missionPoint = $missionSumRow['point'];
    }
}

// 미션처리 내역 조회
//개발이 완료된 건
$missionQuery = "SELECT idx, m_Group, m_Type, m_Name, m_SPoint, m_Img, m_SCnt, m_DCnt, m_Url  FROM TB_MISSION WHERE m_Status = '2'";
$missionStmt = $DB_con->prepare($missionQuery);
$missionStmt->execute();
$missionCount = $missionStmt->rowCount();
if ($missionCount < 1) { //없을 경우
    $chkData['missionPoint'] = 0;
    $chkData['missionOneLists'] = [];
    $chkData['missionMonthLists'] = [];
} else {
    $oneMission = [];
    $monthMission = [];
    while ($missionRow = $missionStmt->fetch(PDO::FETCH_ASSOC)) {
        $mission_Idx = $missionRow['idx'];              // 미션고유번호
        $m_Group = $missionRow['m_Group'];              // 미션구분(1: 링크, 2: 등급달성, 3: 횟수)
        $m_Type = $missionRow['m_Type'];                // 미션타입(1:1회 한정미션, 2: 한달 초기화 미션)
        $m_Name = $missionRow['m_Name'];                // 미션제목
        $m_Point = $missionRow['m_SPoint'];             // 미션보상포인트
        $m_Img = $missionRow['m_Img'];                  // 미션이미지   
        if($m_Img != ""){
            $mImg = "/data/mission/photo.php?id=".$m_Img;
        }else{
            $mImg = "";
        } 
        $m_SCnt = $missionRow['m_SCnt'];                // 미션수행횟수
        $m_DCnt = $missionRow['m_DCnt'];                // 하루최대가능 수(0 이면 제한없이 가능)
        $m_Link = $missionRow['m_Url']."?memId=".$mem_Id."&idx=".$mission_Idx;                 // 링크페이지   
        // $m_Link = "http://".$_SERVER["HTTP_HOST"]."/event/oxQuiz.php?oxIdx=27&memIdx=3&idx=4";                 // 링크페이지   

        $mCnt = missionHistoryChk($mission_Idx, $mem_Idx);

        //미션타입별로 진행하기
        if ($m_Type == "2") { // 한달 초기화 미션

            if ($mCnt < $m_SCnt) {
                $mem_Status = false;
            } else {
                $mem_Status = true;
                $missionPoint = (int)$missionPoint - (int)$m_Point;
            }

            // if ($m_Group == "3") { // 횟수 달성 미션의 경우에는 미션명을 수정해줘야함.
            //     $mName = $m_Name . " " . $mCnt . "/" . $m_SCnt . "회";
            // } else {
            //     $mName = $m_Name;
            // }
            $mName = $m_Name;

            //, "tCnt" => (int)$m_SCnt, "mCnt" => (int)$mCnt, "nCnt" => (int)$nCnt, "mLink" => (string)$link, "mLocat" => (string)$m_Locat
            $monthResult = ["mIdx" => (int)$mission_Idx, "mTitle" => (string)$mName, "mImg" => (string)$mImg, "mPoint" => (int)$m_Point, "mStatus" => $mem_Status, "mLink" => (string)$m_Link];

            //성공한 미션은 맨 뒤로
            if ($mem_Status) {
                array_push($monthMission, $monthResult); // 배열 맨끝에 추가
            } else {
                array_unshift($monthMission, $monthResult); // 배열 맨앞에 추가
            }

        } else { // 1회 한정미션

            if ($mCnt < $m_SCnt) {
                $mem_Status = false;
            } else {
                $mem_Status = true;
                $missionPoint = (int)$missionPoint - (int)$m_Point;
            }
            //, "tCnt" => (int)$m_SCnt, "mCnt" => (int)$mCnt, "nCnt" => (int)$nCnt, "mLink" => (string)$link, "mLocat" => (string)$m_Locat
            $oneResult = ["mIdx" => (int)$mission_Idx, "mTitle" => (string)$m_Name, "mImg" => (string)$mImg, "mPoint" => (int)$m_Point, "mStatus" => $mem_Status, "mLink" => (string)$m_Link];

            if ($mem_Status) {
                array_push($oneMission, $oneResult); // 배열 맨끝에 추가
            } else {
                array_unshift($oneMission, $oneResult); // 배열 맨앞에 추가
            }
        }
    }
    $chkData['missionPoint'] = (int)$missionPoint;
    $chkData['missionOneLists'] = $oneMission;
    $chkData['missionMonthLists'] = $monthMission;
}
// 미션정보 조회하기
dbClose($DB_con);
$noticeStmt = null;
$eventStmt = null;

$output = str_replace('\\\/', '/', json_encode($chkData, JSON_UNESCAPED_UNICODE));
echo  urldecode($output);
