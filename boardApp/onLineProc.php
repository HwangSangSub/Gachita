<?
/*======================================================================================================================

* 프로그램			: 문의사항 등록
* 페이지 설명		: 문의사항 등록
* 파일명           : onLineProc.php

========================================================================================================================*/
include "../lib/common.php";
include "../lib/functionDB.php";  //공통 db함수

$mem_Id  = trim($memId);	    // 아이디(회원)
$mem_Idx = memIdxInfo($mem_Id);
$part = trim($part);            // 문의구분(메이커문의 : 1, 투게더문의 : 2, 문의유형 : 3)
$b_Cate = trim($cate);			// 문의카테고리
$idx = trim($idx);              // 노선고유번호 (메이커노선, 투게더노선 각 고유번호)
$bContent = trim($content);  	// 문의상담 내용

if ($mem_Id != "" && $part != "" && $bContent != "") {  //아이디, 구분, 고유번호 있을 경우
	$DB_con = db1();

	if ($part == "1") { // 메이커문의
		$b_SIdx = trim($idx);
		$b_RIdx = "";
	} else if ($part == "2") { // 투게더문의
		$b_SIdx = "";
		$b_RIdx = trim($idx);
	} else if ($part == "3") { // 문의유형
		$b_SIdx = "";
		$b_RIdx = "";
	}


	$mm = date('m');   //월
	$dd = date('d');   //일

	//회원정보
	$memQuery = "SELECT mem_NickNm FROM TB_MEMBERS WHERE idx = :mem_Idx AND b_Disply = 'N' LIMIT 1";
	$memStmt = $DB_con->prepare($memQuery);
	$memStmt->bindparam(":mem_Idx", $mem_Idx);
	$memStmt->execute();
	$vnum = $memStmt->rowCount();
	//echo $vnum."<BR>";
	//exit;

	if ($vnum < 1) { //아닐경우
	} else {
		while ($vrow = $memStmt->fetch(PDO::FETCH_ASSOC)) {
			$memNickNm = trim($vrow['mem_NickNm']);
		}
	}

	$title = $mm . "월 " . $dd . "일에 문의하신 내용입니다.";

	$b_Hide = "Y";		   //비공개체크유무

	if ($b_Cate != "") {
		$b_Cate = $b_Cate;
	} else {
		$b_Cate = "0";
	}

	if ($b_SIdx != "") {
		$b_SIdx = $b_SIdx;
	} else {
		$b_SIdx = 0;
	}

	if ($b_RIdx != "") {
		$b_RIdx = $b_RIdx;
	} else {
		$b_RIdx = 0;
	}

	$regDate = DU_TIME_YMDHIS;  //시간등록

	$b_Content = $bContent;

	if ($ie) { //익슬플로러일경우
		$b_Content = iconv('euc-kr', 'utf-8', $bContent);
	}

	$b_Content = str_replace("'", "`", $b_Content);

	$b_Ip = escape_trim($_SERVER['REMOTE_ADDR']);

	//같은 카테고리 내에 문의사항이 이미 없는 경우 - 정상등록
	//문의내용이 있을경우
	$insQuery = "INSERT INTO TB_ONLINE (b_Part, b_SIdx, b_RIdx, b_Cate, b_MemIdx, b_MemId, b_Title, b_Name, b_Content, b_Hide, b_Ip, reg_Date) VALUES (:b_Part, :b_SIdx, :b_RIdx, :b_Cate, :b_MemIdx, :b_MemId, :b_Title, :b_Name, :b_Content, :b_Hide, :b_Ip, :reg_Date)";
	//echo $insQuery."<BR>";
	//exit;
	$stmt = $DB_con->prepare($insQuery);
	$stmt->bindParam("b_Part", $part);
	$stmt->bindParam("b_SIdx", $b_SIdx);
	$stmt->bindParam("b_RIdx", $b_RIdx);
	$stmt->bindParam("b_Cate", $b_Cate);
	$stmt->bindParam("b_MemIdx", $mem_Idx);
	$stmt->bindParam("b_MemId", $mem_Id);
	$stmt->bindParam("b_Title", $title);
	$stmt->bindParam("b_Name", $memNickNm);
	$stmt->bindParam("b_Content", $b_Content);
	$stmt->bindParam("b_Hide", $b_Hide);
	$stmt->bindParam("b_Ip", $b_Ip);
	$stmt->bindParam("reg_Date", $regDate);
	$stmt->execute();
	$DB_con->lastInsertId();

	$mIdx = $DB_con->lastInsertId();  //저장된 idx 값

	$result = array("result" => true, "idx" => (int)$mIdx); // 각 API 성공값 부분
} else {
	$result = array("result" => false);
}

echo json_encode($result, JSON_UNESCAPED_UNICODE);

dbClose($DB_con);
$memStmt = null;
$bStmt = null;
$stmt = null;
