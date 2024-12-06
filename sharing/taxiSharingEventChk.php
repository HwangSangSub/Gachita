<?
/*======================================================================================================================

	 * 프로그램		: 가치있는 가치타기 인증 여부 파악 
	 * 페이지 설명	: 가치있는 가치타기 인증 여부 파악
     * 파일명       : taxiSharingEventChk.php   

========================================================================================================================*/

include "../lib/common.php";
include "../lib/functionDB.php";  //공통 db함수

$idx = trim($idx);                        // 메이커고유번호

$DB_con = db1();
if ($idx != "") {

    // 택시확인하기.
    $sharingChkQuery = "SELECT idx, taxi_MemId, taxi_MemIdx, taxi_Price, taxi_Img FROM TB_STAXISHARING WHERE idx = :idx AND taxi_State = '6'";
    $sharingChkStmt = $DB_con->prepare($sharingChkQuery);
    $sharingChkStmt->bindparam(":idx", $idx);
    $sharingChkStmt->execute();
    $sharingChkNum = $sharingChkStmt->rowCount();
    if ($sharingChkNum < 1) {
        $result = array("result" => false, "errorMsg" => "노선상태가 이동중이 아닙니다. 확인 후 다시 시도해주세요.");
    } else {
        $eventChkQuery = "SELECT idx FROM TB_POINT_HISTORY WHERE taxi_SIdx = :taxi_SIdx AND taxi_SubTitle = '가치타기 인증'";
        $eventChkStmt = $DB_con->prepare($eventChkQuery);
        $eventChkStmt->bindparam(":taxi_SIdx", $idx);
        $eventChkStmt->execute();
        $eventChkNum = $eventChkStmt->rowCount();
        if ($eventChkNum > 0) {
            $result = array("result" => true, "eventBit" => true);
        } else {
            $result = array("result" => true, "eventBit" => false);
        }
    }
    dbClose($DB_con);
    $sharingChkStmt = null;
    $insStmt = null;
} else {
    $result = array("result" => false, "errorMsg" => "ERROR #1 : 조회 정보값이 없습니다. 관리자에 문의바랍니다.");
}
echo str_replace('\\/', '/', json_encode($result, JSON_UNESCAPED_UNICODE));
