<?
require_once dirname(__FILE__) . '/TPAY.LIB.php';
?>
<!DOCTYPE html>
<html>

<head>
	<meta http-equiv="Content-Type" content="text/html; charset=euc-kr">
	<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0, user-scalable=no" />
	<link rel="stylesheet" href="css/sample.css" type="text/css" media="screen" />
	<title>tPay 인터넷결제</title>
</head>

<body>
	<?
	//상점(회원가) 페이지가 EUC-KR일 경우 한글깨짐방지
	encoding("UTF-8", "EUC-KR", $_POST);

	print_r($_POST);

	$payMethod = $_POST['payMethod'];
	$ediDate = $_POST['ediDate'];
	$returnUrl = $_POST['returnUrl'];
	$resultMsg = $_POST['resultMsg'];
	$cancelDate = $_POST['cancelDate'];
	$cancelTime = $_POST['cancelTime'];
	$resultCd = $_POST['resultCd'];
	$cancelNum = $_POST['cancelNum'];
	$cancelAmt = $_POST['cancelAmt'];
	$moid = $_POST['moid'];

	// $mid = "tpaytest0m";	//상점id
	// $merchantKey = "VXFVMIZGqUJx29I/k52vMM8XG4hizkNfiapAkHHFxq0RwFzPit55D3J3sAeFSrLuOnLNVCIsXXkcBfYK1wv8kQ==";	//상점키
	$mid = "taxiking1m";	//상점id
	$merchantKey = "NWedcIVvVwwATYHSpuZIdUw8KoM9oNcqaHs6xq3CP+JHo+Nu6uel0rhK6yLqiihwsIFkdabxmfsBHBWXn7Or7g==";	//상점키

	$encryptor = new Encryptor($merchantKey, $ediDate);
	$decAmt = $encryptor->decData($cancelAmt);
	$decMoid = $encryptor->decData($moid);

	?>
</body>

</html>