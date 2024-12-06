#!/usr/bin/php -q
<?php
/*======================================================================================================================

* 프로그램			: 사무실 메인 입구 통계현황정리 (매일 00:00분 처리)
* 페이지 설명		: 사무실 메인 입구 통계현황정리
* 파일명          : gachitaTotalStat.php

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

$prev_Date = date('Y-m-d', strtotime('-1 day'));     //등록일

$cntDownQuery = "";
$cntDownQuery = "SELECT COUNT(idx)  AS cntRow FROM TB_MEMBERS A";
$cntDownStmt = $DB_con->prepare($cntDownQuery);
$cntDownStmt->execute();
$cntDownRow = $cntDownStmt->fetch(PDO::FETCH_ASSOC);
$totalDownCnt = $cntDownRow['cntRow'];

$cntQuery = "SELECT COUNT(idx)  AS cntRow FROM TB_MEMBERS A WHERE A.mem_Lv NOT IN ('0') AND A.b_Disply = 'N'";
$cntStmt = $DB_con->prepare($cntQuery);
$cntStmt->execute();
$cntRow = $cntStmt->fetch(PDO::FETCH_ASSOC);
$totalCnt = $cntRow['cntRow'];

$query = " SELECT (SELECT COUNT(idx) FROM TB_ORDER WHERE taxi_OrdState = 2) AS sharing_Cnt, SUM(taxi_OrdMPoint) AS plus_Money, SUM(taxi_OrdTPoint) AS subt_Money, SUM(taxi_OrdSPoint) AS profit_Money FROM TB_PROFIT_POINT ";
$stmt = $DB_con->prepare($query);
$stmt->execute();
while ($row = $stmt->fetch()) {
    $sharing_Cnt = $row['sharing_Cnt'];
    $plus_Money = $row['plus_Money'];
    $subt_Money = $row['subt_Money'];
    $profit_Money = $row['profit_Money'];
}

$insQuery = "INSERT INTO TB_TOTAL_STAT SET down_Cnt = :down_Cnt, reg_Cnt = :reg_Cnt, mat_Cnt = :mat_Cnt, subt_Money = :subt_Money, profit_Money = :profit_Money, stat_Date = :stat_Date";
$insStmt = $DB_con->prepare($insQuery);
$insStmt->bindparam(":down_Cnt", $totalDownCnt);
$insStmt->bindparam(":reg_Cnt", $totalCnt);
$insStmt->bindparam(":mat_Cnt", $sharing_Cnt);
$insStmt->bindparam(":subt_Money", $subt_Money);
$insStmt->bindparam(":profit_Money", $profit_Money);
$insStmt->bindparam(":stat_Date", $prev_Date);
$insStmt->execute();


dbClose($DB_con);
$cntDownStmt = null;
$cntStmt = null;
$Stmt = null;
$insStmt = null;
?>