<?

/*======================================================================================================================

* 프로그램			: 이용완료페이지 
* 페이지 설명		: 이용완료페이지 
* 파일명            : taxiSharingComp.php
*
========================================================================================================================*/

include "../lib/common.php";
include "../lib/functionDB.php";  //공통 db함수

$sidx = trim($sidx);       // 노선생성번호
$ridx = trim($ridx);       // 노선요청번호
$data = [];
if ($sidx != "" || $ridx != "") {  //노선 생성 또는 요청번호 둘다 없는 경우

    $DB_con = db1();

    if ($sidx != "" && $ridx == "") {    // 투게더요청시
        $query = "SELECT s.taxi_MemIdx, s.taxi_Price, sm.taxi_SaddrNm, sm.taxi_EaddrNm
        FROM TB_STAXISHARING AS s 
            INNER JOIN TB_STAXISHARING_MAP AS sm ON s.idx = sm.taxi_Idx
        WHERE s.idx = :idx";
        $stmt = $DB_con->prepare($query);
        $stmt->bindparam(":idx", $sidx);
        $stmt->execute();
        $num = $stmt->rowCount();

        if ($num < 1) { //아닐경우
            $taxiMemIdx = "";
            $taxiSaddrNm = "";
            $taxiEaddrNm = "";
            $taxiPrice = 0;
        } else {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $taxiMemIdx = $row['taxi_MemIdx'];       // 회원고유번호
            $taxiPrice = $row['taxi_Price'];         // 요청금액
            $taxiSaddrNm = $row['taxi_SaddrNm'];     // 출발지주소
            $taxiEaddrNm = $row['taxi_EaddrNm'];     // 도착지주소
        }
        $memLv = memLvGet($taxiMemIdx);             // 회원등급
        $memLvDc = memLvDcGet($memLv);              // 등급수수료율
        $memDc = $memLvDc / 100;                    // 회원수수료율 (등급수수료율 / 100)
        $memDcPrice = $taxiPrice * $memDc;          // 수수료 (요청금액 / 회원 수수료율)

        $memTotalPrice = $taxiPrice - $memDcPrice;  // 실제 적립 포인트 (요청금액 - 수수료)

        $memPoint = memPointGet($taxiMemIdx);       // 회원보유포인트
        $memOrgPoint = $memPoint - $memTotalPrice;  // 잔여 포인트(현재 보유 포인트 - 적립 포인트)

        $data['result'] = true;
        $data['taxiSaddrNm'] = (string)$taxiSaddrNm;
        $data['taxiEaddrNm'] = (string)$taxiEaddrNm;
        $data['memOrgPoint'] = (int)$memOrgPoint;
        $data['taxiPrice'] = (int)$taxiPrice;
        $data['memTexPrice'] = (int)$memDcPrice;
        $data['memPoint'] = (int)$memPoint;
        $result = $data;
    } else if ($sidx == "" && $ridx != "") {   // 메이커요청시
        $query = "SELECT s.taxi_Price, sm.taxi_SaddrNm, sm.taxi_EaddrNm 
        FROM TB_RTAXISHARING AS r 
            INNER JOIN TB_STAXISHARING_MAP AS sm ON r.taxi_SIdx = sm.taxi_Idx 
            INNER JOIN TB_STAXISHARING AS s ON r.taxi_SIdx = s.idx
        WHERE r.idx = :idx ";
        $stmt = $DB_con->prepare($query);
        $stmt->bindparam(":idx", $ridx);
        $stmt->execute();
        $num = $stmt->rowCount();

        if ($num < 1) { //아닐경우
            $taxiSaddrNm = "";
            $taxiEaddrNm = "";
            $taxiPrice = 0;
        } else {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $taxiPrice = $row['taxi_Price'];         // 요청금액
            $taxiSaddrNm = $row['taxi_SaddrNm'];     // 출발지주소
            $taxiEaddrNm = $row['taxi_EaddrNm'];     // 도착지주소
        }

        $orderQuery = "SELECT taxi_OrdPrice, taxi_OrdPoint 
        FROM TB_ORDER 
        WHERE taxi_RIdx = :idx";
        $orderStmt = $DB_con->prepare($orderQuery);
        $orderStmt->bindparam(":idx", $ridx);
        $orderStmt->execute();
        $orderNum = $orderStmt->rowCount();

        if ($orderNum < 1) { //아닐경우
            $taxiOrdPrice = 0;
            $taxiOrdPoint = 0;
        } else {
            $orderRow = $orderStmt->fetch(PDO::FETCH_ASSOC);
            $taxiOrdPrice = $orderRow['taxi_OrdPrice'];         // 카드결제금액
            $taxiOrdPoint = $orderRow['taxi_OrdPoint'];         // 사용포인트
        }

        $data['result'] = true;
        $data['taxiSaddrNm'] = (string)$taxiSaddrNm;
        $data['taxiEaddrNm'] = (string)$taxiEaddrNm;
        $data['taxiPrice'] = (int)$taxiPrice;
        $data['taxiOrdPrice'] = (int)$taxiOrdPrice;
        $data['taxiOrdPoint'] = (int)$taxiOrdPoint;
        $result = $data;
    } else {
        $result = array("result" => false, "errorMsg" => "조회정보값이 없습니다. 확인 후 다시 시도해주세요.");
    }
    echo str_replace('\\/', '/', json_encode($result, JSON_UNESCAPED_UNICODE));

    dbClose($DB_con);
    $stmt = null;
    $orderStmt = null;
} else {
    $result = array("result" => false, "errorMsg" => "조회정보값이 없습니다. 확인 후 다시 시도해주세요.");
    echo json_encode($result, JSON_UNESCAPED_UNICODE);
}
