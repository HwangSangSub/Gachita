<?
include "../lib/common.php";

$DB_con = db1();
$call_Idx = trim($idx);

if ($call_Idx == "") {
	$result = array("result" => false, "errorMsg" => "선택한 택시호출이 없습니다. 확인 후 다시 시도해주세요.");
} else {
	//추천콜 전화번호 확인하기
	$query = "SELECT  taxi_CallCnt FROM TB_TAXICALL WHERE idx = :idx";
	$stmt = $DB_con->prepare($query);
	$stmt->bindparam(":idx", $call_Idx);
	$stmt->execute();
	$num = $stmt->rowCount();
	if ($num > 0) {
		$row = $stmt->fetch(PDO::FETCH_ASSOC);
		$idx = $row['idx'];															// 고유번호
		$taxi_CallCnt = $row['taxi_CallCnt'];							// 택시 호출 수 

		$taxiCallCnt = (int)$taxi_CallCnt + 1;							// 택히 호출 수 증가

		$upQuery = "UPDATE TB_TAXICALL SET taxi_CallCnt = :taxi_CallCnt WHERE idx = :idx LIMIT 1";
		$upStmt = $DB_con->prepare($upQuery);
		$upStmt->bindparam(":taxi_CallCnt", $taxiCallCnt);
		$upStmt->bindparam(":idx", $call_Idx);
		$upStmt->execute();

		$result = array("result" => true);
	} else {
		$result = array("result" => false, "errorMsg" => "등록되지 않은 호출입니다. 확인 후 다시 시도해주세요.");
	}
}

echo str_replace('\\/', '/', json_encode($result, JSON_UNESCAPED_UNICODE));

dbClose($DB_con);
$stmt = null;
