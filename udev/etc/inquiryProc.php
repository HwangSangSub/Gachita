<?

include "../../udev/lib/common.php";
include "../../lib/alertLib.php";
include "../../lib/thumbnail.lib.php";   //썸네일

$DB_con = db1();
if ($mode == "reg") { //등록일경우
	$Qquery = "UPDATE TB_ONLINE SET b_RContent = :b_RContent, b_State = 1, b_RDate = now() WHERE idx = :idx  LIMIT 1";
	$Stmt = $DB_con->prepare($Qquery);
	$Stmt->bindparam(":b_RContent", $b_RContent);
	$Stmt->bindParam(":idx", $idx);
	$Stmt->execute();

	$preUrl = "inquiryList.php?page=$page&$qstr";
	$message = "reg";
	proc_msg($message, $preUrl);
} else if ($mode == "mod") { //수정일경우

	$upQquery = "UPDATE TB_ONLINE SET b_RContent = :b_RContent, b_State = 1, b_RDate = now() WHERE idx = :idx  LIMIT 1";
	$upStmt = $DB_con->prepare($upQquery);
	$upStmt->bindparam(":b_RContent", $b_RContent);
	$upStmt->bindParam(":idx", $idx);
	$upStmt->execute();

	$preUrl = "inquiryList.php?page=$page&$qstr";
	$message = "mod";
	proc_msg($message, $preUrl);
}



dbClose($DB_con);
$Stmt = null;
$upStmt = null;
