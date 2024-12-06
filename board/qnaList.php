<?
/*======================================================================================================================

* 프로그램			: 자주 묻는 질문 조회
* 페이지 설명		: 자주 묻는 질문 전체 조회(페이징처리)
* 파일명          : qnaList.php

========================================================================================================================*/

include "../lib/common.php";

$DB_con = db1();

$b_Cate = trim($cateNo);

//전체 카운트
$cntQuery = "SELECT COUNT(idx)  AS cntRow FROM TB_BOARD WHERE b_Idx = 2 AND b_Cate = :b_Cate AND b_Disply = 'Y' ";
$cntStmt = $DB_con->prepare($cntQuery);
$cntStmt->bindparam(":b_Cate", $b_Cate);
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

//페이지 추가하기.
$query = " SELECT idx, b_Title FROM TB_BOARD WHERE b_Idx = 2 AND b_Cate = :b_Cate AND b_Disply = 'Y' ORDER BY idx DESC LIMIT {$from_record}, {$rows}";
$stmt = $DB_con->prepare($query);
$stmt->bindparam(":b_Cate", $b_Cate);
$stmt->execute();
$count = $stmt->rowCount();
if ($count < 1) { //없을 경우
    $chkResult = "0";
    $listInfoResult = array("totCnt" => (int)$totalCnt, "page" => (int)$page);
} else {
    $chkResult = "1";
    $listInfoResult = array("totCnt" => (int)$totalCnt, "page" => (int)$page);

    $qna = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $idx = $row['idx'];
        $title = $row['b_Title'];
        $link = "https://".$_SERVER['HTTP_HOST']."/board/qnaView.php?idx=".$idx;
        $result = array("title" => $title, "link" => $link);
        array_push($qna, $result);
    }
    $chkData["result"] = true;
    $chkData["listInfo"] = $listInfoResult;  //카운트 관련
    $chkData["lists"] = $qna;  //카운트 관련
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
$cardCStmt = null;