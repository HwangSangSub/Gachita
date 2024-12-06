#!/usr/bin/php -q
<?php
/*======================================================================================================================

* 프로그램			: 위치기록보관 (7일) 전 테이블삭제하기
* 페이지 설명		: 위치기록보관 (7일) 전 테이블삭제하기
* 파일명          : taxiGpsDBDel.php

========================================================================================================================*/

// register_globals off 처리
if (isset($_GET)) {
    @extract($_GET);
}
if (isset($_POST)) {
    @extract($_POST);
}
if (isset($_SERVER)) {
    @extract($_SERVER);
}
if (isset($_ENV)) {
    @extract($_ENV);
}
if (isset($_SESSION)) {
    @extract($_SESSION);
}
if (isset($_COOKIE)) {
    @extract($_COOKIE);
}
if (isset($_REQUEST)) {
    @extract($_REQUEST);
}
if (isset($_FILES)) {
    @extract($_FILES);
}
exit;
ob_start();

header('Content-Type: text/html; charset=utf-8');
$gmnow = gmdate('D, d M Y H:i:s') . ' GMT';
header('Expires: 0'); // rfc2616 - Section 14.21
header('Last-Modified: ' . $gmnow);
header('Cache-Control: no-store, no-cache, must-revalidate'); // HTTP/1.1
header('Cache-Control: pre-check=0, post-check=0, max-age=0'); // HTTP/1.1
header('Pragma: no-cache'); // HTTP/1.0

//구글 fcm키
define("GOOGLE_API_KEY", "AAAAQ5PRua4:APA91bHIqpvIHy5sm_Av5GYw1o3qO3gZxorKjfHnbXN_G17YiEf_qnaH-5n34dsbUJ1YmqBNjAaGAAY6hrJ4VmL2ntidTTMF_FXOYh_xcH4X-od_bdHVmj5iyqmAeYnLXqprP_FWA1mD");
include 'inc/dbcon.php';


$DB_con = db1();

$reg_Date = date('Y-m-d H:i:s', time());

$delDate = date("Ymd", strtotime($reg_Date . " -1 week"));

$TableName = "TB_SHARING_GPS_" . $delDate;

$showTableQuery = "SHOW TABLES LIKE '" . $TableName . "';";
$showstmt = $DB_con->prepare($showTableQuery);
$showstmt->execute();
$shownum = $showstmt->rowCount();

if ($shownum < 1) { //아닐경우
	$result = array("result" => false, "errorMsg" => "해당테이블이 없습니다.");
} else {
	//삭제
	$delQquery = "DROP TABLE " . $TableName . ";";
	$delStmt = $DB_con->prepare($delQquery);
	$delStmt->execute();
	$result = array("result" => true, "Msg" => "삭제성공", "DBName" => $TableName);
}

dbClose($DB_con);
$showstmt = null;
$delStmt = null;
echo "
" . str_replace('\\/', '/', json_encode($result, JSON_UNESCAPED_UNICODE));
?>