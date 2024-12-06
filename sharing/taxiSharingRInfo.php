<?
include "../lib/common.php";

//$idx = "10";

$idx = trim($idx);                //매칭요청 고유번호

if ($idx != "") {  //매칭요청고유번호 여부가 경우

    $DB_con = db1();

    $viewQuery = "";
    $viewQuery = "SELECT taxi_SIdx, taxi_RTPrice, taxi_RUPoint, taxi_RMemo, taxi_CardIdx FROM TB_RTAXISHARING WHERE idx = :idx LIMIT 1  ";
    //echo $viewQuery."<BR>";

    $viewStmt = $DB_con->prepare($viewQuery);
    $viewStmt->bindparam(":idx", $idx);
    $viewStmt->execute();
    $num = $viewStmt->rowCount();

    if ($num < 1) { //아닐경우
        $result = array("result" => false, "errorMsg" => "매칭요청된 노선이 아닙니다. 잠시후 다시 시도해주세요.");
    } else {

        while ($row = $viewStmt->fetch(PDO::FETCH_ASSOC)) {
            $taxiSIdx =  trim($row['taxi_SIdx']);                  // 투게더 신청한 노선
            $taxiRTPrice =  trim($row['taxi_RTPrice']);            // 투게더 지불해야할 금액
            $taxiRUPoint =  trim($row['taxi_RUPoint']);            // 투게더 사용한 포인트
            $taxiRMemo =  trim($row['taxi_RMemo']);                // 투게더 하고싶은말
            $taxiCardIdx =  trim($row['taxi_CardIdx']);            // 선택한 카드
        }

        $cardInfoQuery = "SELECT card_Name, card_NickName, card_Number4, (SELECT card_Select_Img FROM TB_CARD_CODE WHERE TB_PAYMENT_CARD.card_Name = TB_CARD_CODE.card_Name) AS card_Img FROM TB_PAYMENT_CARD WHERE idx = :idx";
        $cardInfoStmt = $DB_con->prepare($cardInfoQuery);
        $cardInfoStmt->bindparam(":idx", $taxiCardIdx);
        $cardInfoStmt->execute();
        $cardInfoRow = $cardInfoStmt->fetch(PDO::FETCH_ASSOC);
        $cardName =  trim($cardInfoRow['card_Name']);                  // 카드사명
        $cardNickName =  trim($cardInfoRow['card_NickName']);          // 카드별칭
        $cardNumber4 =  trim($cardInfoRow['card_Number4']);            // 카드끝4자리
        $card_Img =  trim($cardInfoRow['card_Img']);                    // 카드이미지
        $cardImg = "/data/config/card/".$card_Img;
        $sharingMQuery = "SELECT taxi_Price FROM TB_STAXISHARING WHERE idx = :idx";
        $sharingMStmt = $DB_con->prepare($sharingMQuery);
        $sharingMStmt->bindparam(":idx", $taxiSIdx);
        $sharingMStmt->execute();
        $sharingMRow = $sharingMStmt->fetch(PDO::FETCH_ASSOC);
        $taxiPrice =  trim($sharingMRow['taxi_Price']);                  // 투게더 신청한 노선

        $taxiTotalPrice = (int)$taxiPrice - (int)$taxiRUPoint;

        $result = array("result" => true, "taxiMemo" => (string)$taxiRMemo, "cardImg" => (string)$cardImg, "cardName" => (string)$cardName, "cardNickName" => (string)$cardNickName, "cardNumber4" => (string)$cardNumber4, "taxiPrice" => (int)$taxiPrice, "taxiPoint" => (int)$taxiRUPoint, "taxiTotalPrice" => (int)$taxiTotalPrice);
    }
    echo str_replace('\\/', '/', json_encode($result, JSON_UNESCAPED_UNICODE));

    dbClose($DB_con);
    $viewStmt = null;
    $minfoetmt = null;
    $minfoStmt = null;
    $infoRStmt = null;
    $mapRStmt = null;
    $mapStmt = null;
    $memStmt = null;
} else {
    $result = array("result" => false, "errorMsg" => "조회 정보값이 없습니다. 관리자에게 문의바랍니다.");
    echo json_encode($result, JSON_UNESCAPED_UNICODE);
}
