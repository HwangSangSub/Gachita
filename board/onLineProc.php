<?
/*======================================================================================================================

* 프로그램			: 문의 내역 삭제
* 페이지 설명		: 문의 내역 삭제
* 파일명            : onLineProc.php

========================================================================================================================*/

include "../udev/lib/common.php";
include "../lib/alertLib.php";
include DU_COM . "/functionDB.php";

$onLineIdx = trim($idx);                  // 문의고유번호
$mode = trim($mode);                  // mode(del : 삭제)

$DB_con = db1();

if($onLineIdx == ""){
    $data['result'] = false;
}else{
    if($mode == "del"){
        //문의 조회
        $onLineChkQuery = "SELECT * FROM TB_ONLINE WHERE idx = :onLineIdx ";        
        $onLineChkStmt = $DB_con->prepare($onLineChkQuery);
        $onLineChkStmt->bindparam(":onLineIdx", $onLineIdx);
        $onLineChkStmt->execute();
        $onLineChkNum = $onLineChkStmt->rowCount();
        if($onLineChkNum > 0){
            //문의 삭제
            $onLineDelQuery = "DELETE FROM TB_ONLINE WHERE idx = :onLineIdx LIMIT 1";      
            $onLineDelStmt = $DB_con->prepare($onLineDelQuery);
            $onLineDelStmt->bindparam(":onLineIdx", $onLineIdx);
            $onLineDelStmt->execute();
            $data['result'] = true;
        }else{
            // 문의번호 오류
            $data['result'] = false;
        }
    }else{
        $data['result'] = false;
    }
}

$output = str_replace('\\\/', '/', json_encode($data, JSON_UNESCAPED_UNICODE));
echo  urldecode($output);

dbClose($DB_con);
$stmt = null;
