<?
include "../lib/common.php";
include "../lib/card_password.php"; //카드정보 암호화
include "../lib/functionDB.php";  //공통 db함수

$mem_Id = trim($memId);                //아이디
$mem_Idx = memIdxInfo($mem_Id);   //회원 주아이디 (상점고유아이디로 사용)

if ($mem_Id != "") {  //아이디가 있을 경우

    $DB_con = db1();
    $bankQuery = "SELECT  idx, bank_OName, bank_Name, bank_Number FROM TB_PAYMENT_BANK WHERE bank_Mem_Idx = :bank_Mem_Idx AND bank_Mem_Id = :bank_Mem_Id ";
    $bankStmt = $DB_con->prepare($bankQuery);
    $bankStmt->bindparam(":bank_Mem_Idx", $mem_Idx);
    $bankStmt->bindparam(":bank_Mem_Id", $mem_Id);
    $bankStmt->execute();
    $bankNum = $bankStmt->rowCount();

    if ($bankNum < 1) { //아닐경우
        $mresult = array("result" => true, "totCnt" => (int)$bankNum);
        $chkResult = "0";
    } else {

        $chkResult = "1";
        $data  = [];
        while ($bankRow = $bankStmt->fetch(PDO::FETCH_ASSOC)) {

            $idx = $bankRow['idx'];                                                // 고유번호
            $bankOName = $bankRow['bank_OName'];                                     // 은행명
            $bankName = $bankRow['bank_Name'];                                     // 은행명

            $bankInfoQuery = "SELECT card_Select_Img FROM TB_CARD_CODE WHERE card_Name = :card_Name AND card_Type = 2 AND c_Disply = 'Y'";
            $bankInfoStmt = $DB_con->prepare($bankInfoQuery);
            $bankInfoStmt->bindparam(":card_Name", $bankName);
            $bankInfoStmt->execute();
            $bankInfoRow = $bankInfoStmt->fetch(PDO::FETCH_ASSOC);
            $bank_Img = $bankInfoRow['card_Select_Img'];            // 카드로고이미지
            $bankImg = "/data/config/bank/" . $bank_Img;

            $bank_Number = $bankRow['bank_Number'];            // 계좌번호

            $bankNumber = openssl_decrypt(base64_decode($bank_Number), 'aes-256-cbc', $password, OPENSSL_RAW_DATA, $iv);
            $bankNumber = substr_replace($bankNumber,"****",3,4);
            $result = array("idx" => (int)$idx, "bankName" => (string)$bankName, "bankOName" => (string)$bankOName, "bankImg" => (string)$bankImg, "bankNumber" => (string)$bankNumber);
            array_push($data, $result);
        }

        $chkData["result"] = true;
        $chkData["totCnt"] = $bankNum;
        if ($bankNum > 0) {
            $chkData['data'] = $data;
        }else{
            $chkData['data'] = [];
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
