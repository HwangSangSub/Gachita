<?

include "../../udev/lib/common.php";
include "../../lib/alertLib.php";
include "../../lib/thumbnail.lib.php";   //썸네일

$DB_con = db1();

if ($mode == "reg") {

	$reg_Date = DU_TIME_YMDHIS;		   //등록일

	$insQuery = "INSERT INTO TB_TAXI_QNA ( qna_Id, qna_Question, qna_Answer, q_Disply, reg_Date ) VALUES ( :qna_Id, :qna_Question, :qna_Answer, :q_Disply, :reg_Date )";
	//echo $insQuery."<BR>";
	//exit;
	$stmt = $DB_con->prepare($insQuery);
	$stmt->bindParam(":qna_Id", $qna_Id);
	$stmt->bindParam(":qna_Question", $qna_Question);
	$stmt->bindParam(":qna_Answer", $qna_Answer);
	$stmt->bindParam(":q_Disply", $q_Disply);
	$stmt->bindParam(":reg_Date", $reg_Date);
	$stmt->execute();
	$DB_con->lastInsertId();

	$preUrl = "qnaList.php?page=$page&$qstr";
	$message = "reg";
	proc_msg($message, $preUrl);
} else if ($mode == "mod") { //수정일경우

	$upQquery = "UPDATE TB_TAXI_QNA SET qna_Id = :qna_Id, qna_Question = :qna_Question, qna_Answer = :qna_Answer, q_Disply = :q_Disply, update_Date = now() WHERE idx = :idx  LIMIT 1";
	$upStmt = $DB_con->prepare($upQquery);
	$upStmt->bindparam(":qna_Id", $qna_Id);
	$upStmt->bindparam(":qna_Question", $qna_Question);
	$upStmt->bindparam(":qna_Answer", $qna_Answer);
	$upStmt->bindparam(":q_Disply", $q_Disply);
	$upStmt->bindParam(":idx", $idx);
	$upStmt->execute();

	$preUrl = "qnaList.php?page=$page&$qstr";
	$message = "mod";
	proc_msg($message, $preUrl);
} else {  //삭제일경우

	//이벤트 배너 삭제
	$delQuery = "DELETE FROM TB_TAXI_QNA WHERE idx = :idx";
	$delStmt = $DB_con->prepare($delQuery);
	$delStmt->bindparam(":idx", $idx);
	$delStmt->execute();
	echo "success";
}




dbClose($DB_con);
$stmt = null;
$upStmt = null;
$delStmt = null;
