<?
/*======================================================================================================================

* 프로그램				:  카드 정보 (별칭수정)
* 페이지 설명			:  카드 정보 (별칭수정)
* 파일명              :  cardNickProc.php

========================================================================================================================*/
include "../lib/common.php";
include "../lib/functionDB.php";  //공통 db함수

$idx = trim($idx);                // 카드삭제 시 필요한 카드고유번호
$card_NickName = trim($cardNickName);       //카드별칭

if ($idx != "" && $card_NickName != "") {
    $DB_con = db1();

    //카드여부확인하기
    $cardChkQuery = "SELECT idx FROM TB_PAYMENT_CARD WHERE idx = :idx";
    $cardChkStmt = $DB_con->prepare($cardChkQuery);
    $cardChkStmt->bindparam(":idx", $idx);
    $cardChkStmt->execute();
    $cardChkNum = $cardChkStmt->rowCount();

    if ($cardChkNum < 1) { //아닐경우
        $result = array("result" => false, "errorMsg" => "등록된 카드가 아닙니다. 확인 후 다시 시도해주세요.");
    } else {
        //카드닉네임변경하기
        $cardUpQuery = "UPDATE TB_PAYMENT_CARD SET card_NickName = :card_NickName WHERE idx = :idx LIMIT 1";
        $cardUpStmt = $DB_con->prepare($cardUpQuery);
        $cardUpStmt->bindparam(":card_NickName", $card_NickName);
        $cardUpStmt->bindparam(":idx", $idx);
        $cardUpStmt->execute();
        $result = array("result" => true);
    }
} else {
    $result = array("result" => false, "errorMsg" => "조회가능한 정보가 없습니다. 관리자에게 문의바랍니다.");
}

echo json_encode($result, JSON_UNESCAPED_UNICODE);