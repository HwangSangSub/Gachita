<?
include "../../udev/lib/common.php";
include "../../lib/alertLib.php";
include "../../lib/thumbnail.lib.php";   //썸네일

$DB_con = db1();

if ($mode == "reg") {

	$reg_Date = DU_TIME_YMDHIS;		   //등록일

	$insQuery = "INSERT INTO TB_EVENT (event_Title, event_Url, event_EndBit, reg_Date) VALUES (:event_Title, :event_Url, :event_EndBit, :reg_Date)";
	// exit;
	$stmt = $DB_con->prepare($insQuery);
	$stmt->bindParam(":event_Title", $eventTitle);
	$stmt->bindParam(":event_Url", $eventUrl);
	$stmt->bindParam(":event_EndBit", $eventEndBit);
	$stmt->bindParam("reg_Date", $reg_Date);
	$stmt->execute();
	$DB_con->lastInsertId();

	$preUrl = "eventList.php?page=$page&$qstr";
	$message = "reg";
	proc_msg($message, $preUrl);
} else if ($mode == "mod") { //수정일경우

	$upQquery = "
			UPDATE 
				TB_EVENT 
			SET
				event_Title = :event_Title, 
				event_Url = :event_Url, 
				event_EndBit = :event_EndBit
			WHERE 
				idx = :idx 
			LIMIT 1";
	$upStmt = $DB_con->prepare($upQquery);
	$upStmt->bindparam(":event_Title", $eventTitle);
	$upStmt->bindparam(":event_Url", $eventUrl);
	$upStmt->bindparam(":event_EndBit", $eventEndBit);
	$upStmt->bindParam(":idx", $idx);
	$upStmt->execute();

	$preUrl = "eventList.php?page=$page&$qstr";
	$message = "mod";
	proc_msg($message, $preUrl);
} else {  //삭제일경우

	//이벤트 배너 삭제
	$delQuery = "DELETE FROM TB_EVENT WHERE idx = :idx";
	$delStmt = $DB_con->prepare($delQuery);
	$delStmt->bindparam(":idx", $chkIdx);
	$delStmt->execute();

	echo "success";
}

dbClose($DB_con);
$stmt = null;
$upStmt = null;
$fileStmt = null;
$delStmt = null;
