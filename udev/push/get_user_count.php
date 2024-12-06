<?
//- - + - - + - - + - - + - - + - - + - - + - - + - - + - - + - - + - - + - - + - -
// Include
//- - + - - + - - + - - + - - + - - + - - + - - + - - + - - + - - + - - + - - + - -
include "../../udev/lib/common.php";
include "../../lib/alertLib.php";
include "../../lib/thumbnail.lib.php";   //썸네일

$DB_con = db1();
$prev_conn = $_POST["prev_conn"];
//$prev_conn = $_GET["prev_conn"];

if ($prev_conn != "ALL") {
	$update_time = mktime(0, 0, 0, date("m"), date("d"), date("Y")) - ($prev_conn * (60 * 60 * 24));
	$update_date = date("Y-m-d", $update_time) . " 00:00:00";
	$sql_search = "AND login_Date > '" . $update_date . "' ";
}

$query = "select * from TB_MEMBERS_INFO where isnull(leaved_Date)  {$sql_search}  ";
$stmt = $DB_con->prepare($query);
$stmt->execute();
$num = $stmt->rowCount();

/*
$db->que = "select count(*) AS count from user AS u JOIN device AS d ON u.uid=d.user_uid and d.os_type = 'Android' ". $db->getWhere(). "";

$db->query();
$jArray->setObject("user_count", $num);
echo json_encode($jArray->getResult(), JSON_UNESCAPED_UNICODE);
exit;
*/
echo $num;
