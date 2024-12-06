<?
/*======================================================================================================================

* 프로그램			: 문의사항 조회
* 페이지 설명		: 문의사항 조회
* 파일명           : onLineList.php

========================================================================================================================*/

include "../lib/common.php";
$b_MemId  = trim($memId);

$DB_con = db1();

//include "boardSetting.php";  //게시판 환경설정

//전체 카운트
$cntQuery = "";
$cntQuery = "SELECT COUNT(idx)  AS cntRow FROM TB_ONLINE WHERE b_MemId = :b_MemId";
$cntStmt = $DB_con->prepare($cntQuery);
$cntStmt->bindparam(":b_MemId", $b_MemId);

$cntStmt->execute();
$row = $cntStmt->fetch(PDO::FETCH_ASSOC);
$totalCnt = $row['cntRow'];
$totalCnt = (int)$totalCnt;

$rows = 10;
$total_page  = ceil($totalCnt / $rows);  // 전체 페이지 계산
if ($page == "") {
	$page = 1;
} // 페이지가 없으면 첫 페이지 (1 페이지)
$page = (int)$page;
$from_record = ($page - 1) * $rows; // 시작 열을 구함

$page = (int)$page;
if ($totalCnt < 1) {
	$listInfoResult = array("totCnt" => (int)$totalCnt, "page" => (int)$page);
	$result = array("result" => true, "listInfo" => $listInfoResult,  "lists" => []);
} else {
	$listInfoResult = array("totCnt" => (int)$totalCnt, "page" => (int)$page);
	$query = "";
	$query .= " SELECT idx, b_MemId, b_Title, b_Content, b_RContent, b_State, b_RDate, reg_Date  ";
	$query .= " FROM TB_ONLINE WHERE b_MemId = :b_MemId ";
	$query .= " ORDER BY idx DESC limit  {$from_record}, {$rows}";
	$Stmt = $DB_con->prepare($query);
	$Stmt->bindparam(":b_MemId", $b_MemId);
	$Stmt->execute();

	$data  = [];
	while ($Row = $Stmt->fetch(PDO::FETCH_ASSOC)) {
		$idx = $Row['idx'];						// 고유번호
		$b_MemId = $Row['b_MemId'];				// 문의자ID
		$b_Title = $Row['b_Title'];				// 문의제목
		$b_Content = str_replace("?", "", $Row['b_Content']);			// 문의내용
		$b_RContent = $Row['b_RContent'];		// 답변내용
		$b_State = $Row['b_State'];				// 답변여부	==>	0: 답변대기 (답변안함), 1: 답변완료 (답변함)
		if($b_State == 1){
			$b_State = true;
		}else{
			$b_State = false;
		}
		$reg_Date = $Row['reg_Date'];				// 답변등록일
        $link = "https://".$_SERVER['HTTP_HOST']."/board/onLineView.php?idx=".$idx;

		$result = ["idx" => (int)$idx, "regDate" => (string)$reg_Date, "bTitle" => (string)$b_Content, "bState" => $b_State, "link" => (string)$link];
		array_push($data, $result);
	}
	$result = array("result" => true, "listInfo" => $listInfoResult,  "lists" => $data);
}

echo json_encode($result, JSON_UNESCAPED_UNICODE);

dbClose($DB_con);
$cntStmt = null;
$Stmt = null;
