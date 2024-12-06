<?
include "../lib/common.php";
include "../lib/functionDB.php";        //공통 db함수


$idx = trim($idx);                                    //매칭생성 고유번호

if ($idx != "") {  //매칭생성 고유번호가 있을 경우

    $DB_con = db1();

    $viewQuery = "";
    $viewQuery = "SELECT s.taxi_TPrice, s.taxi_Price, sm.taxi_SaddrNm, sm.taxi_EaddrNm, s.taxi_ATime, sm.taxi_SLat, sm.taxi_SLng, sm.taxi_ELat, sm.taxi_ELng FROM TB_STAXISHARING AS s INNER JOIN TB_STAXISHARING_MAP AS sm ON s.idx = sm.taxi_Idx WHERE s.idx = :idx LIMIT 1 ";
    //echo $viewQuery."<BR>";
    //exit;
    $viewStmt = $DB_con->prepare($viewQuery);
    $viewStmt->bindparam(":idx", $idx);
    $viewStmt->execute();
    $num = $viewStmt->rowCount();
    // echo $num."<BR>";

    if ($num < 1) { //아닐경우
        $result = array("result" => false, "errorMsg" => "생성된 노선이 아닙니다. 확인 후 다시 시도해주세요.");
    } else {

        while ($row = $viewStmt->fetch(PDO::FETCH_ASSOC)) {
            $taxiTPrice =  trim($row['taxi_TPrice']);        // 총택시요금
            $taxiPrice =  trim($row['taxi_Price']);                // 희망쉐어링요금
            $taxiSaddrNm =  trim($row['taxi_SaddrNm']);                    // 출발지주소
            $taxiEaddrNm =  trim($row['taxi_EaddrNm']);    // 목적지주소
            $taxiATime =  trim($row['taxi_ATime']);    // 도착예상시간
            $taxiSLat =  trim($row['taxi_SLat']);    // 목적지경도
            $taxiSLng =  trim($row['taxi_SLng']);    // 목적지위도
            $taxiELat =  trim($row['taxi_ELat']);    // 목적지경도
            $taxiELng =  trim($row['taxi_ELng']);    // 목적지위도

            $orderChkQuery = "SELECT taxi_OrdNo FROM TB_ORDER WHERE taxi_SIdx = :idx";
            $orderChkStmt = $DB_con->prepare($orderChkQuery);
            $orderChkStmt->bindparam(":idx", $idx);
            $orderChkStmt->execute();
            $orderChkNum = $orderChkStmt->rowCount();
            if ($orderChkNum < 1) { //없을경우
                $taxiOrdNo = "";
            } else {
                $orderChkRow = $orderChkStmt->fetch(PDO::FETCH_ASSOC);
                $taxiOrdNo =  trim($orderChkRow['taxi_OrdNo']);    // 주문번호
            }

            $configQuery = "SELECT con_TaxiRate, con_TaxiEventRate, con_TaxiEventBit, con_TaxiEventStartDate, con_TaxiEventEndDate FROM TB_CONFIG";
            $configStmt = $DB_con->prepare($configQuery);
            $configStmt->execute();
            $configNum = $configStmt->rowCount();
            if ($configNum < 1) { //없을경우
                $conTaxiRate = 1;
                $conTaxiEventEndDate = "";
                $conTaxiEventBit = false;
            } else {
                $configRow = $configStmt->fetch(PDO::FETCH_ASSOC);
                $con_TaxiRate =  trim($configRow['con_TaxiRate']);                      // 택시이미지 포인트 더 받기 포인트 비율
                $con_TaxiEventRate =  trim($configRow['con_TaxiEventRate']);            // 택시이미지 포인트 더 받기 이벤트시 포인트 비율
                $con_TaxiEventBit =  trim($configRow['con_TaxiEventBit']);              // 택시이미지 포인트 더 받기 이벤트 진행 여부 (진행 : Y, 종료 : N)
                $con_TaxiEventStartDate =  trim($configRow['con_TaxiEventStartDate']);  // 택시이미지 포인트 더 받기 이벤트 시작일
                $con_TaxiEventEndDate =  trim($configRow['con_TaxiEventEndDate']);      // 택시이미지 포인트 더 받기 이벤트 종료일
                if ($con_TaxiEventBit == 'Y') {

                    $nowDate = date("Y-m-d");                                            // 오늘
                    $conTaxiEventStartDate = date('Y-m-d', strtotime($con_TaxiEventStartDate));     // 이벤트시작일 
                    $conTaxiEventEndDate = date('Y-m-d', strtotime($con_TaxiEventEndDate));         // 이벤트종료일
                    if ($conTaxiEventStartDate <= $nowDate && $conTaxiEventEndDate >= $nowDate) {
                        $conTaxiRate = $con_TaxiEventRate;
                        $conTaxiEventEndDate = $conTaxiEventEndDate;
                        $conTaxiEventBit = true ;
                    }else{
                        $conTaxiRate = $con_TaxiRate;
                        $conTaxiEventEndDate = "";
                        $conTaxiEventBit = false;
                    }
                } else {
                    $conTaxiRate = $con_TaxiRate;
                    $conTaxiEventEndDate = "";
                    $conTaxiEventBit = false;
                }
            }
        }

        $result = array("result" => true, "taxiATime" => (int)$taxiATime, "taxiTPrice" => (int)$taxiTPrice, "taxiPrice" => (int)$taxiPrice, "taxiSaddrNm" => (string)$taxiSaddrNm, "taxiEaddrNm" => (string)$taxiEaddrNm, "taxiSLat" => (float)$taxiSLat, "taxiSLng" => (float)$taxiSLng, "taxiELat" => (float)$taxiELat, "taxiELng" => (float)$taxiELng, "taxiOrdNo" => (string)$taxiOrdNo, "conTaxiEventBit" => $conTaxiEventBit, "conTaxiRate" => (int)$conTaxiRate, "conTaxiEventEndDate" => (string)$conTaxiEventEndDate);
    }

    dbClose($DB_con);
    $viewStmt = null;
    $infoStmt = null;
    $mapStmt = null;
    $minfoRStmt = null;
    $memStmt = null;
    $memRStmt = null;
    $mpStmt = null;
} else {
    $result = array("result" => false, "errorMsg" => "매칭정보가 없습니다. 관리자에게 문의바랍니다.");
}
echo json_encode($result, JSON_UNESCAPED_UNICODE);
