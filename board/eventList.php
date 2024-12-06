<?
/*======================================================================================================================

* 프로그램			: 이벤트 조회
* 페이지 설명		: 이벤트 전체 조회(페이징처리)
* 파일명          : eventList.php

========================================================================================================================*/

include "../lib/common.php";

$DB_con = db1();

$page = trim($page);

//전체 카운트
$cntQuery = "SELECT COUNT(idx)  AS cntRow FROM TB_EVENT";
$cntStmt = $DB_con->prepare($cntQuery);
$cntStmt->execute();
$row = $cntStmt->fetch(PDO::FETCH_ASSOC);
$totalCnt = $row['cntRow'];

$rows = 10;
$total_page  = ceil($totalCnt / $rows);  // 전체 페이지 계산
if ($page == "") {
    $page = 1;
} // 페이지가 없으면 첫 페이지 (1 페이지)

$page = (int)$page;
$from_record = ($page - 1) * $rows; // 시작 열을 구함

$eventQuery = "SELECT idx, event_Title, event_Url, event_EndBit FROM TB_EVENT ORDER BY idx DESC LIMIT {$from_record}, {$rows}";
$stmt = $DB_con->prepare($eventQuery);
$stmt->execute();
$num = $stmt->rowCount();

if ($num < 1) { //아닐경우
    $chkResult = "0";
    $listInfoResult = array("totCnt" => (int)$totalCnt, "page" => (int)$page);
} else {
    $chkResult = "1";
    $listInfoResult = array("totCnt" => (int)$totalCnt, "page" => (int)$page);

    $event = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $event_Title = $row['event_Title'];                 // 이벤트명
        $event_EndBit = $row['event_EndBit'];               // 이벤트 종료여부(N: 미종료, Y: 종료)
        if($event_EndBit == 'Y'){
            $eventTitle = "[종료] ".$event_Title;
        }else{
            $eventTitle = $event_Title;
        }
        $event_Url = $row['event_Url'];                     // 배너 url
        $result = ["title" => (string)$eventTitle, "link" => (string)$event_Url];
        array_push($event, $result);
    }
    $chkData["result"] = true;
    $chkData["listInfo"] = $listInfoResult;  //카운트 관련
    $chkData["lists"] = $event;  //카운트 관련
}

if ($chkResult  == "1") {
    $output = str_replace('\\\/', '/', json_encode($chkData, JSON_UNESCAPED_UNICODE));
} else if ($chkResult  == "0") {
    $chkData2["result"] = true;
    $chkData2["listInfo"] = $listInfoResult;  //카운트 관련
    $chkData2['lists'] = [];
    $output = str_replace('\\\/', '/', json_encode($chkData2, JSON_UNESCAPED_UNICODE));
}
echo  urldecode($output);

dbClose($DB_con);
$stmt = null;