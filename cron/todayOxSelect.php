#!/usr/bin/php -q
<?php
/*======================================================================================================================

* 프로그램			: 오늘의 OX퀴즈 이벤트 선택 (매일 00:00분 처리)
* 페이지 설명		: 오늘의 OX퀴즈 이벤트 선택
* 파일명            : todayOxSelect.php

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

$oxQuery = "SELECT idx FROM TB_OX WHERE ox_Status = '1' AND ox_UseBit = '0' ORDER BY RAND() LIMIT 1";
$oxStmt = $DB_con->prepare($oxQuery);
$oxStmt->execute();
$oxRow = $oxStmt->fetch(PDO::FETCH_ASSOC);
$con_TodayOx = $oxRow['idx'];

$insConfigOxQuery = "UPDATE TB_CONFIG
    SET con_TodayOx = :con_TodayOx
    WHERE idx = 1
    LIMIT 1";
$insConfigOxStmt = $DB_con->prepare($insConfigOxQuery);
$insConfigOxStmt->bindparam(":con_TodayOx", $con_TodayOx);
$insConfigOxStmt->execute();


dbClose($DB_con);
$cntDownStmt = null;
$cntStmt = null;
$Stmt = null;
$insStmt = null;
?>