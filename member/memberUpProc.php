<?
include "../lib/common.php";
include "../lib/functionDB.php";  //공통 db함수

$mem_Id = trim($memId);				//아이디
$mem_Idx = memIdxInfo($mem_Id);   //회원 주아이디

$mem_NickNm = trim($nickName);	//닉네임
if ($ie) { //익슬플로러일경우
	$mem_NickNm = iconv('euc-kr', 'utf-8', $mem_NickNm);
}

$mem_Seat = trim($memSeat);			//좌석 ( 0: 앞자리, 1: 뒷자리)
$mem_Code = trim($code);					//단체코드

if ($mem_Id != "") {  //아이디가 있을 경우

	$DB_con = db1();

	$memQuery = "SELECT mem_NickNm, mem_Code from TB_MEMBERS WHERE idx = :mem_Idx AND mem_Id = :mem_Id AND b_Disply = 'N' ";
	$stmt = $DB_con->prepare($memQuery);
	$stmt->bindparam(":mem_Idx", $mem_Idx);
	$stmt->bindparam(":mem_Id", $mem_Id);
	$stmt->execute();
	$num = $stmt->rowCount();

	if ($num < 1) { //아닐경우
		$result = array("result" => false, "errorMsg" => "등록되지 않은 회원입니다. 확인 후 다시 시도해주세요.");
	} else {
		if ($mem_Code == "") {
			while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
				$mNickNm = $row['mem_NickNm'];			//회원 닉네임
				$memCode = $row['mem_Code'];				//단체코드
			}

			if ($mem_NickNm == "") {
				$mem_NickNm = $mNickNm;
			} else {
				$mem_NickNm = $mem_NickNm;
			}

			$memInfoQuery = "SELECT mem_Seat from TB_MEMBERS_INFO WHERE mem_Idx = :mem_Idx ";
			$mInfoStmt = $DB_con->prepare($memInfoQuery);
			$mInfoStmt->bindparam(":mem_Idx", $mem_Idx);
			$mInfoStmt->execute();
			$mInfoNum = $mInfoStmt->rowCount();

			if ($mInfoNum < 1) { //아닐경우
			} else {

				while ($mInfoRow = $mInfoStmt->fetch(PDO::FETCH_ASSOC)) {
					$mSeat = $mInfoRow['mem_Seat'];    //좌석
				}
			}

			if ($mem_Seat == "") {
				$mem_Seat = $mSeat;
			} else {
				$mem_Seat = $mem_Seat;
			}

			//회원기본 테이블
			$upQquery = "UPDATE TB_MEMBERS SET  mem_NickNm = :mem_NickNm, mem_Code = :mem_Code WHERE  mem_Id = :mem_Id AND idx = :mem_Idx  LIMIT 1";
			$upStmt = $DB_con->prepare($upQquery);
			$upStmt->bindparam(":mem_NickNm", $mem_NickNm);
			$upStmt->bindparam(":mem_Code", $mem_Code);
			$upStmt->bindparam(":mem_Id", $mem_Id);
			$upStmt->bindparam(":mem_Idx", $mem_Idx);
			$upStmt->execute();

			//회원 정보테이블 업데이트
			$upQquery2 = "UPDATE TB_MEMBERS_INFO SET mem_Seat = :mem_Seat  WHERE mem_Id = :mem_Id AND mem_Idx = :mem_Idx LIMIT 1";
			$upStmt2 = $DB_con->prepare($upQquery2);
			$upStmt2->bindparam(":mem_Seat", $mem_Seat);
			$upStmt2->bindparam(":mem_Id", $mem_Id);
			$upStmt2->bindparam(":mem_Idx", $mem_Idx);
			$upStmt2->execute();

			$result = array("result" => true);
		} else {
			$codeQuery = "
						SELECT idx
						FROM TB_FRD_CODE
						WHERE group_Code = :group_Code
					";
			$codeStmt = $DB_con->prepare($codeQuery);
			$codeStmt->bindparam(":group_Code", $mem_Code);
			$codeStmt->execute();
			$codenum = $codeStmt->rowCount();
			if ($codenum < 1) {
				$result = array("result" => "error", "errorMsg" => "잘못된 단체코드입니다.");
			} else {
				$codeRow = $codeStmt->fetch(PDO::FETCH_ASSOC);
				$chk_Code = $codeRow['idx'];    //그룹코드
				while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
					$mNickNm = $row['mem_NickNm'];			//회원 닉네임
					$memCode = $row['mem_Code'];				//단체코드
				}

				if ($mem_NickNm == "") {
					$mem_NickNm = $mNickNm;
				} else {
					$mem_NickNm = $mem_NickNm;
				}
				if ($mem_Code == "") {
					$mem_Code = $memCode;
				} else {
					$mem_Code = $chk_Code;
				}

				$memInfoQuery = "SELECT mem_Seat from TB_MEMBERS_INFO WHERE mem_Idx = :mem_Idx ";
				$mInfoStmt = $DB_con->prepare($memInfoQuery);
				$mInfoStmt->bindparam(":mem_Idx", $mem_Idx);
				$mInfoStmt->execute();
				$mInfoNum = $mInfoStmt->rowCount();

				if ($mInfoNum < 1) { //아닐경우
				} else {

					while ($mInfoRow = $mInfoStmt->fetch(PDO::FETCH_ASSOC)) {
						$mSeat = $mInfoRow['mem_Seat'];    //좌석
					}
				}

				if ($mem_Seat == "") {
					$mem_Seat = $mSeat;
				} else {
					$mem_Seat = $mem_Seat;
				}

				//회원기본 테이블
				$upQquery = "UPDATE TB_MEMBERS SET  mem_NickNm = :mem_NickNm, mem_Code = :mem_Code WHERE  mem_Id = :mem_Id AND mem_Idx = :mem_Idx  LIMIT 1";
				$upStmt = $DB_con->prepare($upQquery);
				$upStmt->bindparam(":mem_NickNm", $mem_NickNm);
				$upStmt->bindparam(":mem_Code", $mem_Code);
				$upStmt->bindparam(":mem_Id", $mem_Id);
				$upStmt->bindparam(":mem_Idx", $mem_Idx);
				$upStmt->execute();

				//회원 정보테이블 업데이트
				$upQquery2 = "UPDATE TB_MEMBERS_INFO SET mem_Seat = :mem_Seat  WHERE mem_Id = :mem_Id AND mem_Idx = :mem_Idx LIMIT 1";
				$upStmt2 = $DB_con->prepare($upQquery2);
				$upStmt2->bindparam(":mem_Seat", $mem_Seat);
				$upStmt2->bindparam(":mem_Id", $mem_Id);
				$upStmt2->bindparam(":mem_Idx", $mem_Idx);
				$upStmt2->execute();

				$result = array("result" => true);
			}
		}
	}

	dbClose($DB_con);
	$stmt = null;
	$mInfoStmt = null;
	$upStmt = null;
	$upStmt2 = null;
} else {
	$result = array("result" => false, "errorMsg" => "회원 아이디가 없습니다. 확인 후 다시 시도해주세요.");
}
echo json_encode($result);
