#!/usr/bin/php -q
<?php
/*======================================================================================================================

* 프로그램			: 매달 1일에 전체 유저를 확인하여 각 완료건수에 따라서 등급 처리하기.
* 페이지 설명		: 등급 자동 변경 매달 1일
* 파일명            : taxiSharingLvUpProc.php

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

$now_Date = date('Y-m-d H:i:s', time());     //등록일
// echo "now_Date(현재날짜) : ".$now_Date."\n";
$prev_Date = date('Y-m-d H:i:s',strtotime($now_Date."-1 month")); // 1달전
// echo "prev_Date(1달전날짜) : ".$prev_Date."\n";
$prev_Month = date('Y-m', strtotime($prev_Date));
// echo "prev_Month(지난달) : ".$prev_Month."\n";

// 회원조회
$memChkQuery = "SELECT idx, mem_Lv FROM TB_MEMBERS WHERE b_Disply = 'N' AND mem_Lv IN (14, 13, 12, 11) ";
$memChkStmt = $DB_con->prepare($memChkQuery);
$memChkStmt->execute();
$memChkNum = $memChkStmt->rowCount();
// echo "memChkNum(회원수) : ".$memChkNum."\n";
if ($memChkNum < 1) {
    $result = array("result" => false, "errorMsg" => "회원이 없습니다.");
} else {
    $modCnt = 0;    // 변경된 회원 수
    while ($memChkRow = $memChkStmt->fetch(PDO::FETCH_ASSOC)) {
        $mem_Idx = $memChkRow['idx']; // 회원 고유번호
        $mem_Lv = $memChkRow['mem_Lv']; // 현재 회원 등급
        // echo "mem_Idx(회원고유번호) : ".$mem_Idx."\n";
        // echo "mem_Lv(현재 회원 등급) : ".$mem_Lv."\n";
        //매칭생성 진행 건수
        $memSCntQuery = "SELECT count(idx) AS num FROM TB_STAXISHARING WHERE taxi_MemIdx = :taxi_MemIdx AND DATE_FORMAT(taxi_SDate, '%Y-%m') = :searchDate AND taxi_State IN ( '7', '10' ) ";
        $chkCntStmt = $DB_con->prepare($memSCntQuery);
        $chkCntStmt->bindparam(":taxi_MemIdx", $mem_Idx);
        $chkCntStmt->bindparam(":searchDate", $prev_Month);
        $chkCntStmt->execute();
        $chkCntRow = $chkCntStmt->fetch(PDO::FETCH_ASSOC);
        $chkCntNum = $chkCntRow['num'];

        if ($chkCntNum <> "") {
            $chkCntNum = $chkCntNum;
        } else {
            $chkCntNum = 0;
        }

        // echo "chkCntNum(메이커 완료 건수) : ".$chkCntNum."\n";
        //매칭요청 진행 건수
        $memRCntQuery = "SELECT count(idx) AS num FROM TB_RTAXISHARING WHERE taxi_RMemIdx = :taxi_RMemIdx AND DATE_FORMAT(reg_Date, '%Y-%m') = :searchDate AND taxi_RState IN ( '7', '10' ) "; //완료, 취소를 제외한 경우
        $chkCntRStmt = $DB_con->prepare($memRCntQuery);
        $chkCntRStmt->bindparam(":taxi_RMemIdx", $mem_Idx);
        $chkCntRStmt->bindparam(":searchDate", $prev_Month);
        $chkCntRStmt->execute();
        $chkCntRrow = $chkCntRStmt->fetch(PDO::FETCH_ASSOC);
        $chkCntRNum = $chkCntRrow['num'];

        if ($chkCntRNum <> "") {
            $chkCntRNum = $chkCntRNum;
        } else {
            $chkCntRNum = 0;
        }

        // echo "chkCntRNum(투게더 완료 건수) : ".$chkCntRNum."\n";
        $matCnt = (int)$chkCntNum + (int)$chkCntRNum;
        // echo "matCnt(전체 완료 건수) : ".$matCnt."\n";

        // 등급 정보 조회
        $memMatChkQuery = "SELECT memLv FROM TB_MEMBER_LEVEL WHERE memLv = ((SELECT memLv FROM TB_MEMBER_LEVEL WHERE memLv NOT IN ('1', '2') AND memMatCnt <= :matCnt ORDER BY idx ASC LIMIT 1))";
        $memMatChkStmt = $DB_con->prepare($memMatChkQuery);
        $memMatChkStmt->bindparam(":matCnt", $matCnt);
        $memMatChkStmt->execute();
        $memMatChkRow = $memMatChkStmt->fetch(PDO::FETCH_ASSOC);
        $memLv = $memMatChkRow['memLv'];
        
        // echo "memLv(변경될 회원 등급) : ".$memLv."\n";
        if($mem_Lv != $memLv){
            // echo "회원 등급 변경되어야 하는 회원\n";
            // 회원 등급 변경
            $memEtcUpQuery = "UPDATE TB_MEMBERS SET mem_Lv = :mem_Lv WHERE idx = :mem_Idx";        
            $memEtcUpStmt = $DB_con->prepare($memEtcUpQuery);
            $memEtcUpStmt->bindparam(":mem_Lv", $memLv);
            $memEtcUpStmt->bindparam(":mem_Idx", $mem_Idx);
            $memEtcUpStmt->execute();

            // 수정일 등록
            $memInfoUpQuery = "UPDATE TB_MEMBERS_INFO SET mod_Date = :mod_Date WHERE idx = :mem_Idx";        
            $memInfoUpStmt = $DB_con->prepare($memInfoUpQuery);
            $memInfoUpStmt->bindparam(":mod_Date", $now_Date);
            $memInfoUpStmt->bindparam(":mem_Idx", $mem_Idx);
            $memInfoUpStmt->execute();

            $modCnt++;
        }else{
            // echo "회원 등급 변경안되는 회원\n";
        }
        // echo "modCnt(등급이 변경된 총 건 수) : ".$modCnt."\n";
        // echo "------------\n";
        // echo "\n";
        // // exit;
    }
    $result = array("result" => true, "modCnt" => $modCnt);
}


dbClose($DB_con);
$memChkStmt = null;
$memMatChkStmt = null;
$chkCntStmt = null;
$chkCntRStmt = null;
echo "
" . str_replace('\\/', '/', json_encode($result, JSON_UNESCAPED_UNICODE));
