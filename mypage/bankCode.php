<?
include "../lib/common.php";
include "../lib/card_password.php"; //카드정보 암호화

$DB_con = db1();
//카드코드 조회시 c_Disply : 사용여부 Y 인경우 조회
$cardCQuery = "SELECT card_Name, card_Img FROM TB_CARD_CODE WHERE c_Disply = 'Y' AND card_Type = '2' ORDER BY idx ";
$cardCStmt = $DB_con->prepare($cardCQuery);
$cardCStmt->execute();
$cardNum = $cardCStmt->rowCount();

if ($cardNum < 1) { //아닐경우
	$result = array("result" => false, "errorMsg" => "사용가능한 카드사가 없습니다. 관리자에게 문의 바랍니다.");
} else {
	//이후 카드코드 사용시 ==>card_Code,:라인 9 ||||| $card_Code = []; : 라인 19 ||||| $card_Code[] = $cardCRow['card_Code'];	 // 카드사코드(JTNet사 기준) : 라인 23 |||||||| "card_Code" => $card_Code, : 라인 25 추가
	$bank = [];
	while ($cardCRow = $cardCStmt->fetch(PDO::FETCH_ASSOC)) {
		// $card_Name[] = $cardCRow['card_Name'];				// 카드사이름
		$card_Name = $cardCRow['card_Name'];				// 카드사이름
        $card_Img = $cardCRow ['card_Img'];
        $cardImg = "/data/config/bank/".$card_Img;
        $result = array("bankName" => (string)$card_Name, "bankImg" => (string)$cardImg);
        array_push($bank, $result);
	}
	$result = array("result" => true,  "data" => $bank);
}

dbClose($DB_con);
$cardCStmt = null;

echo json_encode($result, JSON_UNESCAPED_UNICODE);