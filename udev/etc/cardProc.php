<?

include "../../udev/lib/common.php";


$DB_con = db1();
$array = explode('/', $chk);

foreach ($array as $k => $v) {
	$chkIdx = $v;

	$delQquery = "DELETE FROM TB_PAYMENT_CARD WHERE  idx = :idx LIMIT 1";
	$delStmt = $DB_con->prepare($delQquery);
	$delStmt->bindParam(":idx", $chkIdx);
	$delStmt->execute();
}

echo "success";



dbClose($DB_con);
$delStmt = null;
