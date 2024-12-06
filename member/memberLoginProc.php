<?
include "../lib/common.php";
include "../lib/functionDB.php";  //공통 db함수


$mem_Id = trim($memId);				//아이디
$mem_Idx = memIdxInfo($mem_Id);   //회원 주아이디

if ($mem_Id != "") {  //아이디가 있을 경우

	$DB_con = db1();

	//로그인횟수
	$memSql = "  , ( SELECT login_Cnt FROM TB_MEMBERS_ETC WHERE TB_MEMBERS_ETC.mem_Id = TB_MEMBERS.mem_Id limit 1 ) AS login_Cnt  ";
	$memQuery = "SELECT idx, mem_NickNm, mem_Lv {$memSql} from TB_MEMBERS WHERE idx = :mem_Idx AND mem_Id = :mem_Id AND b_Disply = 'N' ";
	$stmt = $DB_con->prepare($memQuery);
	$stmt->bindparam(":mem_Idx", $mem_Idx);
	$stmt->bindparam(":mem_Id", $mem_Id);
	$stmt->execute();
	$num = $stmt->rowCount();

	if ($num < 1) { //아닐경우
		$result = array("result" => false, "errorMsg" => "등록되지 않은 아이디입니다. 확인 후 다시 시도해주세요.");
	} else {

		while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {

			$login_Cnt = $row['login_Cnt'];      // 로그인 횟수


			//디바이스 아이디 업데이트 
			$upMQquery = "UPDATE TB_MEMBERS SET mem_DeviceId = :mem_DeviceId WHERE mem_Id = :mem_Id AND idx = :mem_Idx LIMIT 1";
			$upMStmt = $DB_con->prepare($upMQquery);
			$upMStmt->bindparam(":mem_DeviceId", $mDeviceId);
			$upMStmt->bindparam(":mem_Id", $mem_Id);
			$upMStmt->bindparam(":mem_Idx", $mem_Idx);
			$upMStmt->execute();


			# 마지막 로그인 시간을 업데이트 한다.
			$upQquery = "UPDATE TB_MEMBERS_INFO SET login_Date = now() WHERE  mem_Id = :mem_Id AND mem_Idx = :mem_Idx LIMIT 1";
			$upStmt = $DB_con->prepare($upQquery);
			$upStmt->bindparam(":mem_Id", $mem_Id);
			$upStmt->bindparam(":mem_Idx", $mem_Idx);
			$mem_Id = $mem_Id;
			$upStmt->execute();

			# 로그인 횟수 증가.
			$upQquery2 = "UPDATE TB_MEMBERS_ETC SET login_Cnt = :login_Cnt WHERE mem_Id = :mem_Id AND mem_Idx = :mem_Idx LIMIT 1";
			$upStmt2 = $DB_con->prepare($upQquery2);
			$upStmt2->bindparam(":mem_Id", $mem_Id);
			$upStmt2->bindparam(":login_Cnt", $login_Cnt);
			$upStmt2->bindparam(":mem_Idx", $mem_Idx);
			$login_Cnt = $login_Cnt + 1;
			$upStmt2->execute();
		}

		$result = array("result" => true);
	}

	dbClose($DB_con);
	$stmt = null;
	$upMStmt = null;
	$upStmt = null;
	$upStmt2 = null;
	$chktmt = null;
	$upStmt3 = null;
} else {
	$result = array("result" => false, "errorMsg" => "아이디 정보가 없습니다. 확인 후 다시 시도해주세요.");
}

echo json_encode($result);
