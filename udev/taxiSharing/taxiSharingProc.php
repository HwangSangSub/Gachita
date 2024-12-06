<?
include "../../udev/lib/common.php";
include "../../lib/alertLib.php";


if ($mode == "mod") { //수정일경우

	$upQquery = "UPDATE TB_STAXISHARING SET taxi_Memo = :taxi_Memo WHERE idx =  :idx LIMIT 1";
	$upStmt = $DB_con->prepare($upQquery);
	$upStmt->bindparam(":taxi_Memo", $taxi_Memo);
	$upStmt->bindParam(":idx", $idx);
	$upStmt->execute();

	$preUrl = "taxiSharingSList.php?page=$page&$qstr";
	$message = "mod";
	proc_msg($message, $preUrl);
} else {  //삭제일경우

	$check = trim($chk);
	$array = explode('/', $check);

	foreach ($array as $k => $v) {
		$idx = $v;
		$delQquery = "DELETE FROM TB_MEMBER_LEVEL WHERE idx =  :idx LIMIT 1";

		$delStmt = $DB_con->prepare($delQquery);
		$delStmt->bindParam(":idx", $idx);
		$delStmt->execute();
	}

	echo "success";
}
