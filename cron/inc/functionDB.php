<?
/*======================================================================================================================

* 프로그램			: DB 내용 불러올 함수
* 페이지 설명		: DB 내용 불러올 함수

========================================================================================================================*/
/*회원 고유 번호 가져오기 */
function memIdxInfo($mem_Id)
{

    $fDB_con = db1();

    $memTQuery = "SELECT idx FROM TB_MEMBERS WHERE mem_Id = :mem_Id AND b_Disply = 'N' LIMIT 1";
    $memTStmt = $fDB_con->prepare($memTQuery);
    $memTStmt->bindparam(":mem_Id", $mem_Id);
    $memTStmt->execute();
    $memTNum = $memTStmt->rowCount();

    if ($memTNum < 1) { //주 ID가 없을 경우 회원가입 시작
    } else {  //등록된 회원이 있을 경우
        while ($memTRow = $memTStmt->fetch(PDO::FETCH_ASSOC)) {
            $idx = $memTRow['idx'];           //체크 랜덤아이디
        }
        return $idx;
    }

    dbClose($fDB_con);
    $memTStmt = null;
}



/*회원 디바이스 아이디 가져오기 */
function memDeviceIdInfo($mem_Id)
{

    $fDB_con = db1();

    $memDeQuery = "SELECT mem_DeviceId FROM TB_MEMBERS WHERE mem_Id = :mem_Id AND b_Disply = 'N' LIMIT 1";
    $memDeStmt = $fDB_con->prepare($memDeQuery);
    $memDeStmt->bindparam(":mem_Id", $mem_Id);
    $memDeStmt->execute();
    $memDeNum = $memDeStmt->rowCount();

    if ($memDeNum < 1) { //없을 경우
    } else {  //등록된 회원이 있을 경우
        while ($memDeRow = $memDeStmt->fetch(PDO::FETCH_ASSOC)) {
            $memDeviceId = $memDeRow['mem_DeviceId'];           //체크 랜덤아이디
        }
        return $memDeviceId;
    }

    dbClose($fDB_con);
    $memDeStmt = null;
}




/* 매칭 회원 토큰 값 가져오기 */
function memMatchTokenInfo($mem_Idx)
{

    $fDB_con = db1();

    $memTokQuery = "SELECT mem_Token FROM TB_MEMBERS WHERE idx = :mem_Idx AND b_Disply = 'N'";
    $memTokStmt = $fDB_con->prepare($memTokQuery);
    $memTokStmt->bindparam(":mem_Idx", $mem_Idx);
    $memTokStmt->execute();
    $memTokNum = $memTokStmt->rowCount();

    $tokens = array();
    if ($memTokNum < 1) { //주 ID가 없을 경우 회원가입 시작
    } else {  //등록된 회원이 있을 경우
        while ($memTokRow = $memTokStmt->fetch(PDO::FETCH_ASSOC)) {
            $tokens[] = $memTokRow["mem_Token"]; //토큰값
        }
        return $tokens;
    }


    dbClose($fDB_con);
    $memTokStmt = null;
}