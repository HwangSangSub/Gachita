#!/usr/bin/php -q
<?php
/*======================================================================================================================

* 프로그램		:  가치타 1.0 DB 마이그레이션
* 페이지 설명	:  가치타 1.0 회원 조회 후 가입중인 회원에 대해서 일괄적으로 2.0 DB에 추가하기.
* 파일명        :  dbMigration.php

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

$gachita1 = db2();  // 가치타 1.0
$gachita2 = db1();  // 가치타 2.0

$now_Time = date('Y-m-d H:i:s', time());     //등록일
// 가치타 1.0 회원 조회 > 본사 직원 제외
$gachita1Query = "SELECT * FROM TB_MEMBERS WHERE b_Disply = 'N' AND mem_CertBit = '1' AND mem_Id <> 'NULL' AND mem_Tel NOT IN ('01071291105', '01075320156', '01049421907', '01088434516', '01051327245', '01068475900', '01067778383', '01090957526', '01055499171', '01055970410')";
$gachita1Stmt = $gachita1->prepare($gachita1Query);
$gachita1Stmt->execute();
$gachita1Num = $gachita1Stmt->rowCount();

if ($gachita1Num < 1) {
    //회원이 없는 경우
} else {
    while ($gachita1Row = $gachita1Stmt->fetch(PDO::FETCH_ASSOC)) {
        $mem_Idx = $gachita1Row['idx'];                             // 회원 고유 번호
        $mem_Id = $gachita1Row['mem_Id'];                           // 회원 아이디
        $mem_Nm = $gachita1Row['mem_Nm'];                           // 회원 이름
        $mem_NickNm = $gachita1Row['mem_NickNm'];                   // 회원 닉네임
        $mem_Tel = $gachita1Row['mem_Tel'];                         // 회원 전화번호
        $mem_CertBit = $gachita1Row['mem_CertBit'];                 // 회원 본인인증여부
        $mem_CertId = $gachita1Row['mem_CertId'];                   // 회원 본인인증고유아이디
        $mem_Birth = $gachita1Row['mem_Birth'];                     // 회원 생년월일
        $mem_Lv = $gachita1Row['mem_Lv'];                           // 회원 등급
        $mem_Os = $gachita1Row['mem_Os'];                           // 회원 디바이스 OS
        $mem_Token = $gachita1Row['mem_Token'];                     // 회원 푸시토큰
        $mem_NPush = $gachita1Row['mem_NPush'];                     // 회원 공지 및 이벤트, 광고성 알림 허용 여부
        $mem_MPush = $gachita1Row['mem_MPush'];                     // 회원 가치타 필수 알림 허용
        $reg_Date = $gachita1Row['reg_Date'];                       // 회원 가입일
        $b_Disply = $gachita1Row['b_Disply'];                       // 회원 상태 (가입 중 회원만 처리)

        //회원 기타 정보 조회
        $gachita1EtcQuery = "SELECT * FROM TB_MEMBERS_ETC WHERE mem_Idx = :mem_Idx AND mem_Id = :mem_Id";
        $gachita1EtcStmt = $gachita1->prepare($gachita1EtcQuery);
        $gachita1EtcStmt->bindparam(":mem_Idx", $mem_Idx);
        $gachita1EtcStmt->bindparam(":mem_Id", $mem_Id);
        $gachita1EtcStmt->execute();
        $gachita1EtcNum = $gachita1EtcStmt->rowCount();

        if ($gachita1EtcNum < 1) {
            //회원이 없는 경우
            $mem_Point = 0;
        } else {

            while ($gachita1EtcRow = $gachita1EtcStmt->fetch(PDO::FETCH_ASSOC)) {
                $mem_Point = $gachita1EtcRow['mem_Point'];          // 회원 보유 포인트
            }
        }

        //회원 추가 정보 조회
        $gachita1InfoQuery = "SELECT * FROM TB_MEMBERS_INFO WHERE mem_Idx = :mem_Idx AND mem_Id = :mem_Id";
        $gachita1InfoStmt = $gachita1->prepare($gachita1InfoQuery);
        $gachita1InfoStmt->bindparam(":mem_Idx", $mem_Idx);
        $gachita1InfoStmt->bindparam(":mem_Id", $mem_Id);
        $gachita1InfoStmt->execute();
        $gachita1InfoNum = $gachita1InfoStmt->rowCount();

        if ($gachita1InfoNum < 1) {
            //회원이 없는 경우
            $mem_Email = "";
            $mem_Sex = "1";
        } else {
            while ($gachita1InfoRow = $gachita1InfoStmt->fetch(PDO::FETCH_ASSOC)) {
                $mem_Email = $gachita1InfoRow['mem_Email'];          // 회원 이메일
                $mem_Sex = $gachita1InfoRow['mem_Sex'];              // 회원 성별
            }
        }

        // 가치타 2.0 DB에 회원 입력하기
        // 회원 확인하기
        $mem2ChkQuery = "SELECT * FROM TB_MEMBERS WHERE mem_Id = :mem_Id AND mem_Tel = :mem_Tel AND mem_Birth = :mem_Birth AND mem_CertId = :mem_CertId";
        $mem2ChkStmt = $gachita2->prepare($mem2ChkQuery);
        $mem2ChkStmt->bindparam(":mem_Id", $mem_Id);
        $mem2ChkStmt->bindparam(":mem_Tel", $mem_Tel);
        $mem2ChkStmt->bindparam(":mem_Birth", $mem_Birth);
        $mem2ChkStmt->bindparam(":mem_CertId", $mem_CertId);
        $mem2ChkStmt->execute();
        $mem2ChkNum = $mem2ChkStmt->rowCount();
        // 등록된 정보로 회원이 없다면 회원 등록
        if ($mem2ChkNum < 1) {
            // TB_MEMBERS 입력
            $memInsQuery = "INSERT INTO TB_MEMBERS 
                SET mem_Id = :mem_Id
                , mem_Nm = :mem_Nm
                , mem_NickNm = :mem_NickNm
                , mem_Tel = :mem_Tel
                , mem_CertBit = :mem_CertBit
                , mem_CertId = :mem_CertId
                , mem_Birth = :mem_Birth
                , mem_CharBit = '1'
                , mem_CharIdx = '1'
                , mem_Lv = 14
                , mem_Os = :mem_Os
                , mem_Token = :mem_Token
                , mem_NPush = :mem_NPush
                , mem_MPush = '0'
                , reg_Date = :reg_Date
                , b_Disply = 'N'
            ";
            $memInsStmt = $gachita2->prepare($memInsQuery);
            $memInsStmt->bindparam(":mem_Id", $mem_Id);
            $memInsStmt->bindparam(":mem_Nm", $mem_Nm);
            $memInsStmt->bindparam(":mem_NickNm", $mem_NickNm);
            $memInsStmt->bindparam(":mem_Tel", $mem_Tel);
            $memInsStmt->bindparam(":mem_CertBit", $mem_CertBit);
            $memInsStmt->bindparam(":mem_CertId", $mem_CertId);
            $memInsStmt->bindparam(":mem_Birth", $mem_Birth);
            $memInsStmt->bindparam(":mem_Os", $mem_Os);
            $memInsStmt->bindparam(":mem_Token", $mem_Token);
            $memInsStmt->bindparam(":mem_NPush", $mem_NPush);
            $memInsStmt->bindparam(":reg_Date", $reg_Date);
            $memInsStmt->execute();
            $mem_Idx = $gachita2->lastInsertId();  //저장된 idx 값

            // TB_MEMBERS_ETC 입력
            $memEtcInsQuery = "INSERT INTO TB_MEMBERS_ETC
                SET mem_Idx = :mem_Idx
                , mem_Id = :mem_Id
                , mem_Point = :mem_Point
            ";
            $memEtcInsStmt = $gachita2->prepare($memEtcInsQuery);
            $memEtcInsStmt->bindparam(":mem_Idx", $mem_Idx);
            $memEtcInsStmt->bindparam(":mem_Id", $mem_Id);
            $memEtcInsStmt->bindparam(":mem_Point", $mem_Point);
            $memEtcInsStmt->execute();
            $mem_EtcIdx = $gachita2->lastInsertId();  //저장된 idx 값

            if ($mem_EtcIdx > 0 && (int)$mem_Point > 0) {
                // 적립한 포인트 푸시내역 남기기.
                $insPointHistory_Sign = "0"; // +기호
                $insPointHistory_State = "1"; // 적립
                $insPointHistory_Memo = $now_Time . '
2.0 업그레이드로 인한 포인트 전환';
                $insPointHistoryQuery = "INSERT INTO TB_POINT_HISTORY (taxi_MemId, taxi_MemIdx, taxi_OrdPoint, taxi_OrgPoint, taxi_Memo, taxi_Sign, taxi_PState, reg_Date) VALUES (:taxi_MemId, :taxi_MemIdx, :taxi_OrdPoint, 0, :taxi_Memo, :taxi_Sign, :taxi_PState, NOW())";
                //echo $insQuery."<BR>";
                //exit;
                $insPointHistoryStmt = $gachita2->prepare($insPointHistoryQuery);
                $insPointHistoryStmt->bindParam("taxi_MemId", $mem_Id);
                $insPointHistoryStmt->bindParam("taxi_MemIdx", $mem_Idx);
                $insPointHistoryStmt->bindParam("taxi_OrdPoint", $mem_Point);
                $insPointHistoryStmt->bindParam("taxi_Memo", $insPointHistory_Memo);
                $insPointHistoryStmt->bindParam("taxi_Sign", $insPointHistory_Sign);
                $insPointHistoryStmt->bindParam("taxi_PState", $insPointHistory_State);
                $insPointHistoryStmt->execute();
            }

            // TB_MEMBERS_INFO 입력
            $memInfoInsQuery = "INSERT INTO TB_MEMBERS_INFO 
                SET mem_Idx = :mem_Idx
                , mem_Id = :mem_Id
                , mem_Email = :mem_Email
                , mem_Sex = :mem_Sex
            ";
            $memInfoInsStmt = $gachita2->prepare($memInfoInsQuery);
            $memInfoInsStmt->bindparam(":mem_Idx", $mem_Idx);
            $memInfoInsStmt->bindparam(":mem_Id", $mem_Id);
            $memInfoInsStmt->bindparam(":mem_Email", $mem_Email);
            $memInfoInsStmt->bindparam(":mem_Sex", $mem_Sex);
            $memInfoInsStmt->execute();
            $mem_InfoIdx = $gachita2->lastInsertId();  //저장된 idx 값
        }
    }
}

dbClose($gachita1);
dbClose($gachita2);
$gachita1Stmt = null;
$gachita1EtcStmt = null;
$gachita1InfoStmt = null;
$mem2ChkStmt = null;
$memInsStmt = null;
$memEtcInsStmt = null;
$insPointHistoryStmt = null;
$memInfoInsStmt = null;
?>