<?
/*======================================================================================================================

* 프로그램				:  이용기록 (삭제)
* 페이지 설명			:  이용기록 (삭제)
* 파일명              :  myOrdProc.php

========================================================================================================================*/
include "../lib/common.php";
include "../lib/functionDB.php";  // 공통 db함수

$DB_con = db1();

$idx = trim($idx);                // 노선생성(요청)번호
$part = trim($part);              // 구분  (p: 메이커, c: 투게더)
$mode = "";

if ($mode == "") {
    $mode = "del";      //삭제가 기본
} else {
    $mode = trim($mode);
}

if ($idx != "" && $part != "") {
    if ($mode == "reg") {
    } else if ($mode == "del") {
        if ($part == "p") {  // 메이커인경우
            $chkQuery = "SELECT * FROM TB_STAXISHARING WHERE idx = :idx";
            $chkStmt = $DB_con->prepare($chkQuery);
            $chkStmt->bindparam(":idx", $idx);
            $chkStmt->execute();
            $chkCnt = $chkStmt->rowCount();

            if ($chkCnt < 1) {
                $result = array("result" => false, "errorMsg" => "조회되지 않는 노선입니다. 확인 후 다시 시도해주세요.");
            } else {
                $query = "UPDATE TB_STAXISHARING SET taxi_DelBit = 'Y' WHERE idx = :idx LIMIT 1";
                $stmt = $DB_con->prepare($query);
                $stmt->bindparam(":idx", $idx);
                $stmt->execute();

                $result = array("result" => true);
            }
        } else if ($part == "c") {  // 투게더인 경우
            $chkQuery = "SELECT * FROM TB_RTAXISHARING WHERE idx = :idx";
            $chkStmt = $DB_con->prepare($chkQuery);
            $chkStmt->bindparam(":idx", $idx);
            $chkStmt->execute();
            $chkCnt = $chkStmt->rowCount();

            if ($chkCnt < 1) {
                $result = array("result" => false, "errorMsg" => "조회되지 않는 노선입니다. 확인 후 다시 시도해주세요.");
            } else {
                $query = "UPDATE TB_RTAXISHARING SET taxi_DelBit = 'Y' WHERE idx = :idx LIMIT 1";
                $stmt = $DB_con->prepare($query);
                $stmt->bindparam(":idx", $idx);
                $stmt->execute();

                $result = array("result" => true);
            }
        } else {
            $result = array("result" => false, "errorMsg" => "구분값이 없습니다. 확인 후 다시 시도해주세요.");
        }
    }
} else {
    $result = array("result" => false, "errorMsg" => "조회정보값이 없습니다. 확인 후 다시 시도해주세요.");
}
echo str_replace('\\/', '/', json_encode($result, JSON_UNESCAPED_UNICODE));
dbClose($DB_con);
$chkStmt = null;
$stmt = null;
