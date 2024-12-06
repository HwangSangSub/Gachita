<?
include "../lib/common.php";
include "../lib/functionDB.php";  //공통 db함수

$mem_Id = trim($memId);                //아이디
$mem_Idx = memIdxInfo($mem_Id);   //회원 주아이디

$DB_con = db1();

if ($mem_Id != "") {  //아이디가 있을 경우
    //회원 정보
    $query = "SELECT idx FROM TB_MEMBERS WHERE mem_Id = :mem_Id AND idx = :mem_Idx LIMIT 1";
    $stmt = $DB_con->prepare($query);
    $stmt->bindparam(":mem_Id", $mem_Id);
    $stmt->bindparam(":mem_Idx", $mem_Idx);
    $stmt->execute();
    $num = $stmt->rowCount();
    //echo $etcNum."<BR>";
    //exit;

    if ($num < 1) { //아닐경우
        $result = array("result" => false, "errorMsg" => "등록되지 않은 회원이거나 이미 탈퇴된 회원입니다. 확인 후 다시 시도해주세요.");
    } else {
        //회원 기타 정보
        $etcQuery = "SELECT mem_Point FROM TB_MEMBERS_ETC  WHERE mem_Id = :mem_Id AND mem_Idx = :mem_Idx LIMIT 1";
        $etcStmt = $DB_con->prepare($etcQuery);
        $etcStmt->bindparam(":mem_Id", $mem_Id);
        $etcStmt->bindparam(":mem_Idx", $mem_Idx);
        $etcStmt->execute();
        $etcNum = $etcStmt->rowCount();
        //echo $etcNum."<BR>";
        //exit;

        if ($etcNum < 1) { //아닐경우
            $memPoint = 0;
        } else {
            while ($etcRow = $etcStmt->fetch(PDO::FETCH_ASSOC)) {
                $mem_Point = trim($etcRow['mem_Point']);            // 포인트
                if ($mem_Point  == "") {
                    $memPoint     = 0;
                } else {
                    $memPoint     = (int)$mem_Point;
                }
            }
        }

        //회원 적립예정포인트 확인하기.
        $resPointQuery = "SELECT SUM(taxi_OrdPoint) AS memResPoint FROM TB_POINT_HISTORY WHERE taxi_MemId = :mem_Id AND taxi_MemIdx = :mem_Idx AND taxi_PState = '6'";
        $resPointStmt = $DB_con->prepare($resPointQuery);
        $resPointStmt->bindparam(":mem_Id", $mem_Id);
        $resPointStmt->bindparam(":mem_Idx", $mem_Idx);
        $resPointStmt->execute();
        $resPointRow = $resPointStmt->fetch(PDO::FETCH_ASSOC);
        //echo $etcNum."<BR>";
        //exit;
        $memResPoint = $resPointRow['memResPoint'];

        $result = array("result" => true, "memPoint" => (int)$memPoint, "memResPoint" => (int)$memResPoint);
    }


    dbClose($DB_con);
    $stmt = null;
    $etcStmt = null;
    $missionStmt = null;
} else {
    $result = array("result" => false, "errorMsg" => "조회 정보값이 없습니다. 관리자에게 문의바랍니다.");
}

echo json_encode($result, JSON_UNESCAPED_UNICODE);
