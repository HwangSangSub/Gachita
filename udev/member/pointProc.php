<?

include "../../udev/lib/common.php";
include "../../lib/alertLib.php";
include "../../lib/thumbnail.lib.php";   //썸네일
include DU_COM . "/functionDB.php";

$DB_con = db1();

if ($mode == "reg") {		// 추가일 경우

	$DATA["taxi_OrdPoint"]					= $taxi_OrdPoint;
	$DATA["taxi_Memo"]						= date("Y-m-d H:i:s") . " " . addslashes($taxi_Memo);
	$DATA["taxi_Sign"]						= $taxi_Sign;
	$DATA["taxi_PState"]					= $taxi_PState;			//관리자직접 입력
	$DATA["reg_Date"]						= date("Y-m-d H:i:s");
	$DATA["taxi_SubTitle"]					= "관리자처리";


	if ($taxi_MemTeype == "ALL") {

		//회원상태 b_Disply=>'N'(가입) 인 상태의 회원만 포인트 추가
		$listQuery = "SELECT idx, mem_Id FROM TB_MEMBERS WHERE b_Disply='N' AND mem_Lv > 3";
		$listStmt = $DB_con->prepare($listQuery);
		$listStmt->execute();

		while ($row = $listStmt->fetch()) {
			//회원아이디
			$DATA["taxi_MemIdx"]				= $row['idx'];
			$DATA["taxi_MemId"]					= $row['mem_Id'];

			// 회원 포인트 정보
			$cashQuery = "SELECT mem_Point FROM TB_MEMBERS_ETC WHERE mem_Id = '" . $row['mem_Id'] . "' AND mem_Idx = " . $row['idx'];
			$cashStmt = $DB_con->prepare($cashQuery);
			$cashStmt->execute();
			$cashRow = $cashStmt->fetch();
			$mem_Point = $cashRow['mem_Point'];
			$DATA["taxi_OrgPoint"]				= $mem_Point;

			if (!$mem_Point) $mem_Point = 0;
			if ($taxi_Sign == "0") {
				$mem_points = (int)$mem_Point + (int)$taxi_OrdPoint;
			} else {
				$mem_points = (int)$mem_Point - (int)$taxi_OrdPoint;
			}

			if ($mem_points < 0) {
				$mem_points = 0;
			}
			//포인트입력 정보 입력
			$insQuery = "INSERT INTO TB_POINT_HISTORY SET ";
			$i = 0;
			foreach ($DATA as $key => $val) {
				if ($i > 0) $insQuery .= " , ";
				$insQuery .= $key . " = '" . $val . "' ";

				$i++;
			}
			$DB_con->exec($insQuery);

			// 회원정보 포인트 update
			$updateQuery = "UPDATE TB_MEMBERS_ETC SET mem_Point = " . $mem_points . " WHERE mem_Id='" . $row['mem_Id'] . "' AND mem_Idx = " . $row['idx'];
			$DB_con->exec($updateQuery);

			$insQuery = "";
			$DATA["taxi_MemId"] = "";
		}
	} else if ($taxi_MemTeype == "level") {
		//레벨별 회원리스트구하기
		//$taxi_MemLevel
		//회원상태 b_Disply=>'N'(가입) 인 상태의 회원만 포인트 추가
		$listQuery = "SELECT idx, mem_Id FROM TB_MEMBERS WHERE b_Disply = 'N' AND mem_Lv = '" . $taxi_MemLevel . "' ";
		$listStmt = $DB_con->prepare($listQuery);
		$listStmt->execute();

		while ($row = $listStmt->fetch()) {
			//회원아이디
			$DATA["taxi_MemIdx"]				= $row['idx'];
			$DATA["taxi_MemId"]					= $row['mem_Id'];

			// 회원 포인트 정보
			$cashQuery = "SELECT mem_Point FROM TB_MEMBERS_ETC WHERE mem_Id = '" . $row['mem_Id'] . "' AND mem_Idx = " . $row['idx'];
			$cashStmt = $DB_con->prepare($cashQuery);
			$cashStmt->execute();
			$cashRow = $cashStmt->fetch();
			$mem_Point = $cashRow['mem_Point'];
			$DATA["taxi_OrgPoint"]				= $mem_Point;

			if (!$mem_Point) $mem_Point = 0;
			if ($taxi_Sign == "0") {
				$mem_points = (int)$mem_Point + (int)$taxi_OrdPoint;
			} else {
				$mem_points = (int)$mem_Point - (int)$taxi_OrdPoint;
			}

			if ($mem_points < 0) {
				$mem_points = 0;
			}

			//포인트입력 정보 입력
			$insQuery = "INSERT INTO TB_POINT_HISTORY SET ";
			$i = 0;
			foreach ($DATA as $key => $val) {
				if ($i > 0) $insQuery .= " , ";
				$insQuery .= $key . " = '" . $val . "' ";

				$i++;
			}
			$DB_con->exec($insQuery);

			// 회원정보 포인트 update
			$updateQuery = "UPDATE TB_MEMBERS_ETC SET mem_Point = " . $mem_points . " WHERE mem_Id='" . $row['mem_Id'] . "' AND mem_Idx = " . $row['idx'];
			$DB_con->exec($updateQuery);

			$insQuery = "";
			$DATA["taxi_MemId"] = "";
		}
	} else if ($taxi_MemTeype == "pub") {
		//개별회원
		$DATA["taxi_MemId"]					= $taxi_MemId;
		$DATA["taxi_MemIdx"]				= memIdxInfo($taxi_MemId);


		// 회원 포인트 정보
		$cashQuery = "SELECT mem_Point FROM TB_MEMBERS_ETC WHERE mem_Id = '" . $taxi_MemId . "' AND mem_Idx = " . memIdxInfo($taxi_MemId);
		$cashStmt = $DB_con->prepare($cashQuery);
		$cashStmt->execute();
		$row = $cashStmt->fetch();
		$mem_Point = $row['mem_Point'];
		$DATA["taxi_OrgPoint"]				= $mem_Point;

		if (!$mem_Point) $mem_Point = 0;
		if ($taxi_Sign == "0") {
			$mem_points = (int)$mem_Point + (int)$taxi_OrdPoint;
		} else {
			$mem_points = (int)$mem_Point - (int)$taxi_OrdPoint;
		}

		if ($mem_points < 0) {
			$mem_points = 0;
		}

		//포인트입력 정보 입력
		$insQuery = " INSERT INTO TB_POINT_HISTORY SET ";
		$i = 0;
		foreach ($DATA as $key => $val) {
			if ($i > 0) $insQuery .= " , ";
			$insQuery .= $key . " = '" . $val . "' ";

			$i++;
		}
		//echo $insQuery;
		$DB_con->exec($insQuery);
		// 회원정보 포인트 update
		$updateQuery = "UPDATE TB_MEMBERS_ETC SET mem_Point = " . $mem_points . " WHERE mem_Id='" . $taxi_MemId . "' AND mem_Idx = " . memIdxInfo($taxi_MemId);
		$DB_con->exec($updateQuery);
	}

	// 입력정보
	$preUrl = "pointList.php?page=$page&$qstr";
	$message = "reg";
	proc_msg($message, $preUrl);
}

dbClose($DB_con);
