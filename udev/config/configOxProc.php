<?
include "../../udev/lib/common.php";
include "../../lib/alertLib.php";
include "../../lib/thumbnail.lib.php";   //썸네일

$mode = $mode;

if ($mode == "") {
    $mode = "reg";
} else {
    $mode = $mode;
}

$DB_con = db1();

$now_Date = DU_TIME_YMDHIS;           //등록일
if ($mode == "reg") {

    $insQuery = "INSERT INTO TB_OX 
    SET ox_Cate = :ox_Cate
        , ox_Question = :ox_Question
        , ox_Answer = :ox_Answer
        , ox_Explanation = :ox_Explanation
        , reg_Date = :reg_Date";

    $stmt = $DB_con->prepare($insQuery);
    $stmt->bindParam(":ox_Cate", $oxCate);
    $stmt->bindParam(":ox_Question", $oxQuestion);
    $stmt->bindParam(":ox_Answer", $oxAnswer);
    $stmt->bindParam(":ox_Explanation", $oxExplanation);
    $stmt->bindParam(":reg_Date", $now_Date);

    $stmt->execute();
    $mIdx = $DB_con->lastInsertId();

    $preUrl = "configOxList.php?page=$page&$qstr";
    $message = "reg";
    proc_msg($message, $preUrl);
} else if ($mode == "mod") { //수정일경우		
    $upQuery = "
				UPDATE 
                    TB_OX 
                SET ox_Cate = :ox_Cate
                    , ox_Question = :ox_Question
                    , ox_Answer = :ox_Answer
                    , ox_Explanation = :ox_Explanation
                    , mod_Date = :mod_Date
				WHERE 
					idx = :idx 
				LIMIT 1";
    $upStmt = $DB_con->prepare($upQuery);
    $upStmt->bindParam(":ox_Cate", $oxCate);
    $upStmt->bindParam(":ox_Question", $oxQuestion);
    $upStmt->bindParam(":ox_Answer", $oxAnswer);
    $upStmt->bindParam(":ox_Explanation", $oxExplanation);
    $upStmt->bindParam(":mod_Date", $now_Date);
    $upStmt->bindParam(":idx", $idx);
    $upStmt->execute();

    $preUrl = "configOxList.php?page=$page&$qstr";
    $message = "mod";
    proc_msg($message, $preUrl);
} else {  //삭제일경우
    //OX퀴즈 삭제 삭제
    $delQuery = "UPDATE TB_OX SET ox_Status = '0', ox_UseBit = '1', del_Date = NOW() WHERE idx = :idx";
    $delStmt = $DB_con->prepare($delQuery);
    $delStmt->bindparam(":idx", $idx);
    $delStmt->execute();    

    $preUrl = "configOxList.php?page=$page&$qstr";
    $message = "del";
    proc_msg($message, $preUrl);
}


dbClose($DB_con);
$stmt = null;
$cntStmt = null;
$upStmt = null;
$conStmt = null;
$fileStmt = null;
$delStmt = null;
