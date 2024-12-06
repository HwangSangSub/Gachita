#!/usr/bin/php -q
<?php
/*======================================================================================================================

* 프로그램		:  가치타 2.0 DB 리셋
* 페이지 설명	:  가치타 2.0 DB 리셋
* 파일명        :  dbReset.php

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

include 'inc/dbcon.php';

$gachita2 = db1();  // 가치타 2.0

// 초기화 실패한 TABLE 명 출력
$failTable = '';
// 전체 초기화 가능한 TABLE
$allResetDbArr = array('TB_CANCLE_REASON', 'TB_DEVELOP_LOG', 'TB_MEMBERS_MAP', 'TB_MEMBER_PHOTO', 'TB_MEMWITHDRAWL', 'TB_MISSION_HISTORY', 'TB_ONLINE', 'TB_ORDER', 'TB_PAYMENT_BANK', 'TB_PAYMENT_CARD', 'TB_PENALTY_HISTORY', 'TB_POINT_EXC', 'TB_POINT_HISTORY', 'TB_PROFIT_POINT', 'TB_PUSH_HISTORY', 'TB_RTAXISHARING', 'TB_RTAXISHARING_INFO', 'TB_RTAXISHARING_MAP', 'TB_SHARING_PUSH', 'TB_SMATCH_STATE', 'TB_STAXISHARING', 'TB_STAXISHARING_INFO', 'TB_STAXISHARING_MAP', 'TB_TOTAL_STAT');
for ($i = 0; $i < count($allResetDbArr); $i++) {
    // 테이블 명
    $tableName = $allResetDbArr[$i];

    $delChkNum = 0;
    $delTableQuery = "DELETE FROM " . $tableName;
    $delTableStmt = $gachita2->prepare($delTableQuery);
    $delTableStmt->execute();

    $delChkQuery = "SELECT * FROM " . $tableName;
    $delChkStmt = $gachita2->prepare($delChkQuery);
    $delChkStmt->execute();
    $delChkNum = $delChkStmt->rowCount();
    if ($delChkNum < 1) {
        $resetIdxQuery = "ALTER TABLE " . $tableName . " AUTO_INCREMENT = 1";
        $resetIdxStmt = $gachita2->prepare($resetIdxQuery);
        $resetIdxStmt->execute();
    } else {
        if ($failTable == "") {
            $failTable .= $tableName;
        } else {
            $failTable .= ", ".$tableName;
        }
    }
}

//예외 처리로 삭제할 TABLE
// 1. 회원 테이블
$memDelQuery = "DELETE FROM TB_MEMBERS WHERE idx > 2";
$memDelStmt = $gachita2->prepare($memDelQuery);
$memDelStmt->execute();

$memEtcDelQuery = "DELETE FROM TB_MEMBERS_ETC WHERE mem_Idx > 2";
$memEtcDelStmt = $gachita2->prepare($memEtcDelQuery);
$memEtcDelStmt->execute();

$memInfoDelQuery = "DELETE FROM TB_MEMBERS_INFO WHERE mem_Idx > 2";
$memInfoDelStmt = $gachita2->prepare($memInfoDelQuery);
$memInfoDelStmt->execute();

// 2. 주소 통계 테이블
$addrResetQuery = "UPDATE TB_ADDR_STAT SET addr_Cnt = 0, stat_Date = NOW()";
$addrResetStmt = $gachita2->prepare($addrResetQuery);
$addrResetStmt->execute();

dbClose($gachita2);
$delTableStmt = null;
$delChkStmt = null;
$resetIdxStmt = null;
$memDelStmt = null;
$memEtcDelStmt = null;
$memInfoDelStmt = null;
$addrResetStmt = null;
?>