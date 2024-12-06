<?
/*======================================================================================================================

* 프로그램			: 자주 묻는 질문 인기질문
* 페이지 설명		: 자주 묻는 질문 인기질문
* 파일명          : qnaTopList.php

========================================================================================================================*/

include "../lib/common.php";

$DB_con = db1();

$query = " SELECT idx, b_Title FROM TB_BOARD WHERE b_Idx = 2 AND b_Disply = 'Y' AND t_Disply = 'Y' ORDER BY t_Sort ASC";
$stmt = $DB_con->prepare($query);
$stmt->execute();
$count = $stmt->rowCount();
if ($count < 1) { //없을 경우
    $result = array("result" => false, "errorMsg" => "등록된 인기 질문이이 없습니다.");
} else {
    $notice = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $idx = $row['idx'];
        $title = $row['b_Title'];
        $link = "https://".$_SERVER['HTTP_HOST']."/board/qnaView.php?idx=".$idx;
        $result = array("title" => $title, "link" => $link);
        array_push($notice, $result);
    }
    $result = array("result" => true, "lists" => $notice);
}

dbClose($DB_con);
$cardCStmt = null;

echo json_encode($result, JSON_UNESCAPED_UNICODE);
