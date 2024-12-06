<?
include "../lib/common.php";
include "../lib/card_password.php"; //카드정보 암호화
include "../lib/functionDB.php";  //공통 db함수

$mem_Id = trim($memId);                //아이디
$mem_Idx = memIdxInfo($mem_Id);   //회원 주아이디 (상점고유아이디로 사용)

if ($mem_Id != "") {  //아이디가 있을 경우

    $DB_con = db1();
    $cardQuery = "SELECT  idx, card_Name, card_NickName, card_Number, card_Number4, card_Bit from TB_PAYMENT_CARD WHERE card_Mem_Idx = :card_Mem_Idx AND card_Mem_Id = :mem_Id ";
    $cardStmt = $DB_con->prepare($cardQuery);
    $cardStmt->bindparam(":mem_Id", $mem_Id);
    $cardStmt->bindparam(":card_Mem_Idx", $mem_Idx);
    $cardStmt->execute();
    $cardNum = $cardStmt->rowCount();

    if ($cardNum < 1) { //아닐경우
        $mresult = array("result" => true, "totCnt" => (int)$cardNum);
        $chkResult = "0";
    } else {

        $chkResult = "1";
        $data  = [];
        while ($cardRow = $cardStmt->fetch(PDO::FETCH_ASSOC)) {

            $idx = $cardRow['idx'];                                                // 고유번호
            $card_Name = $cardRow['card_Name'];                    // 카드명

            $cardInfoQuery = "SELECT card_Color, card_NameColor, card_Img, card_Select_Img FROM TB_CARD_CODE WHERE card_Name = :card_Name";
            $cardInfoStmt = $DB_con->prepare($cardInfoQuery);
            $cardInfoStmt->bindparam(":card_Name", $card_Name);
            $cardInfoStmt->execute();
            $cardInfoRow = $cardInfoStmt->fetch(PDO::FETCH_ASSOC);

            $card_Color = $cardInfoRow['card_Color'];            // 카드배경색상
            $card_NameColor = $cardInfoRow['card_NameColor'];            // 카드배경색상
            $card_Img = $cardInfoRow['card_Img'];            // 카드로고이미지
            $cardImg = "/data/config/card/" . $card_Img;
            $card_Select_Img = $cardInfoRow['card_Select_Img'];            // 카드로고이미지
            $cardSelectImg = "/data/config/card/" . $card_Select_Img;

            $card_Number = $cardRow['card_Number'];            // 카드번호
            $card_Bit = $cardRow['card_Bit'];                            // 카드 재등록여부
            if($card_Bit == "1") {
                $cardBit = true;
            }else{
                $cardBit = false;
            }
            $card_NickName = $cardRow['card_NickName'];                            // 카드별칭
            $cardNumber4 = $cardRow['card_Number4'];                            // 카드번호 끝 4자리

            $cardNumber = openssl_decrypt(base64_decode($card_Number), 'aes-256-cbc', $password, OPENSSL_RAW_DATA, $iv);
            $result = array("idx" => (int)$idx, "cardName" => (string)$card_Name, "cardColor" => (string)$card_Color, "cardNameColor" => (string)$card_NameColor, "cardImg" => (string)$cardImg, "cardSelectImg" => (string)$cardSelectImg, "cardNumber" => (string)$cardNumber, "chkCardNum4" => (string)$cardNumber4, "cardBit" => $cardBit, "cardNickName" => (string)$card_NickName);
            array_push($data, $result);
        }

        $chkData["result"] = true;
        $chkData["totCnt"] = $cardNum;
        if ($cardNum > 0) {
            $chkData['data'] = $data;
        }
    }

    if ($chkResult  == "1") {
        $output = str_replace('\\\/', '/', json_encode($chkData, JSON_UNESCAPED_UNICODE));
        echo  urldecode($output);
    } else {
        $result = array("result" => true, "totCnt" => (int)0, "data" => []);
        echo json_encode($result, JSON_UNESCAPED_UNICODE);
    }

    dbClose($DB_con);
    $stmt = null;
} else {
    $result = array("result" => false, "errorMsg" => "회원아이디가 없습니다. 확인 후 다시 시도해주세요.");
    echo json_encode($result, JSON_UNESCAPED_UNICODE);
}
