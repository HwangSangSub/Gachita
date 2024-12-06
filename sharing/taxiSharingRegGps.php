<?
/*======================================================================================================================

	 * 프로그램		: GPS 위치 기록
	 * 페이지 설명		: GPS 위치 기록

========================================================================================================================*/

include "../lib/common.php";
include "../lib/functionDB.php";  //공통 db함수

$idx = trim($idx);						// 매칭생성,요청 고유번호
$lng = trim($lng);						// 구글 경도
$lat = trim($lat);						// 구글 위도
$mode = trim($mode);					// 구분( p : 생성자, c : 요청자 ) ==> 소문자

$nowDate = DU_TIME_YMDHIS;				// 등록일

$DB_con = db1();
if ($idx != "" && $lng != "" && $lat != "") {		// 고유번호, 경도, 위도 필수
	if ($mode != "") {
		if ($mode == "c") {		//요청자 인 경우
			$chkQuery = "SELECT idx, reg_Date from TB_RTAXISHARING WHERE idx = :idx AND taxi_RState ='6' LIMIT 1 ";
			$stmt = $DB_con->prepare($chkQuery);
			$stmt->bindparam(":idx", $idx);
			$stmt->execute();
			$num = $stmt->rowCount();
		} else if ($mode == "p") {
			$chkQuery = "SELECT idx, reg_Date from TB_RTAXISHARING WHERE taxi_SIdx = :taxi_SIdx AND taxi_RState ='6' LIMIT 1 ";
			$stmt = $DB_con->prepare($chkQuery);
			$stmt->bindparam(":taxi_SIdx", $idx);
			$stmt->execute();
			$num = $stmt->rowCount();
		}
		if ($num < 1) { //이동중 노선이 없을 경우
			$result = array("result" => false, "errorMsg" => "이동중인 노선이 없습니다.");
		} else {
			while ($Row = $stmt->fetch(PDO::FETCH_ASSOC)) {
				$reg_Date = $Row['reg_Date'];		// 푸시발송수
			}
			$regDate = date("Ymd", strtotime($reg_Date));
			$TableName = "TB_SHARING_GPS_" . $regDate;
			$showTableQuery = "SHOW TABLES LIKE '" . $TableName . "';";
			$showstmt = $DB_con->prepare($showTableQuery);
			$showstmt->execute();
			$shownum = $showstmt->rowCount();
			if ($shownum < 1) {	//TABLE 가 없는 경우
				$createQuery = "CREATE TABLE " . $TableName . " LIKE TB_SHARING_GPS_BASIC ;";
				$createstmt = $DB_con->prepare($createQuery);
				$createstmt->bindparam(":TableName", $TableName);
				$createstmt->execute();
				$createnum = $createstmt->rowCount();
			}
			$chkGQuery = "SELECT taxi_Idx, taxi_Lng, taxi_Lat, taxi_MemType FROM " . $TableName . " WHERE taxi_Idx = :taxi_Idx AND taxi_MemType = :taxi_MemType ORDER BY reg_Date DESC LIMIT 1";
			$chkstmt = $DB_con->prepare($chkGQuery);
			$chkstmt->bindparam(":taxi_Idx", $idx);
			$chkstmt->bindparam(":taxi_MemType", $mode);
			$chkstmt->execute();
			$chknum = $chkstmt->rowCount();
			if ($chknum < 1) {	//최근등록기록이 없으면 바로 INSERT
				$insQuery = " INSERT INTO " . $TableName . "(taxi_Idx, taxi_Lng, taxi_Lat, taxi_MemType, reg_Date) VALUES(:taxi_Idx, :taxi_Lng, :taxi_Lat, :taxi_MemType, :reg_Date)";
				$insstmt = $DB_con->prepare($insQuery);
				$insstmt->bindparam(":taxi_Idx", $idx);
				$insstmt->bindparam(":taxi_Lng", $lng);
				$insstmt->bindparam(":taxi_Lat", $lat);
				$insstmt->bindparam(":taxi_MemType", $mode);
				$insstmt->bindparam(":reg_Date", $nowDate);
				$insstmt->execute();

				$DB_con->lastInsertId();

				$mIdx = $DB_con->lastInsertId();

				$result = array("result" => true);
			} else {				//최근등록기록이 있으면 최근 등록기록과 비교하여 동일한 값이면 INSERT 안함
				while ($chkRow = $chkstmt->fetch(PDO::FETCH_ASSOC)) {
					$taxi_Idx = trim($chkRow['taxi_Idx']);			// 노선번호
					$taxi_Lng = trim($chkRow['taxi_Lng']);			// 경도
					$taxi_Lat = trim($chkRow['taxi_Lat']);			// 위도
					$taxi_MemType = trim($chkRow['taxi_MemType']);	// 회원구분

					if ($taxi_Idx == $idx) {
						$chk_Idx = 1;
					} else {
						$chk_Idx = 0;
					}
					if ((double)$taxi_Lng == (double)$lng) {
						$chk_Lng = 1;
					} else {
						$chk_Lng = 0;
					}
					if ((double)$taxi_Lat == (double)$lat) {
						$chk_Lat = 1;
					} else {
						$chk_Lat = 0;
					}
					if ($taxi_MemType == $mode) {
						$chk_MemType = 1;
					} else {
						$chk_MemType = 0;
					}
				}
				if ($chk_Idx == 1 && $chk_Lng == 1 && $chk_Lat == 1 && $chk_MemType == 1) {	
					// 이전 위치와 동일한 위치로 등록하지 않음
					// DB에만 등록안함.
					// $result = array("result" => false, "errorMsg" => "이전 위치와 동일한 위치입니다.");
					$result = array("result" => true);
				} else {
					$insQuery = " INSERT INTO " . $TableName . "(taxi_Idx, taxi_Lng, taxi_Lat, taxi_MemType, reg_Date) VALUES(:taxi_Idx, :taxi_Lng, :taxi_Lat, :taxi_MemType, :reg_Date)";
					$insstmt = $DB_con->prepare($insQuery);
					$insstmt->bindparam(":taxi_Idx", $idx);
					$insstmt->bindparam(":taxi_Lng", $lng);
					$insstmt->bindparam(":taxi_Lat", $lat);
					$insstmt->bindparam(":taxi_MemType", $mode);
					$insstmt->bindparam(":reg_Date", $nowDate);
					$insstmt->execute();

					$DB_con->lastInsertId();

					$mIdx = $DB_con->lastInsertId();

					$result = array("result" => true);
				}
			}
			dbClose($DB_con);
			$stmt = null;
			$chkstmt = null;
			$showstmt = null;
			$createstmt = null;
			$insstmt = null;
		}
	} else {
		$result = array("result" => false, "errorMsg" => "회원구분이 없습니다.");
	}
} else {
	$result = array("result" => false, "errorMsg" => "ERROR #1 : 조회 정보값이 없습니다. 관리자에게 문의바랍니다.");
}
echo str_replace('\\/', '/', json_encode($result, JSON_UNESCAPED_UNICODE));
