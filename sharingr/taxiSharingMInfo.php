<?
include "../lib/common.php";
include "../lib/functionDB.php";  //공통 db함수

//$memId = "dududu";
//$idx = "2";

$mem_Id = trim($memId);				//아이디
$idx = trim($idx);									//고유번호
$memSex = memSexInfo($mem_Id);	//성별값조회하기
if ($mem_Id != "" && $idx != "") {  //아이디, 매칭정보 여부가 있을 경우

	$DB_con = db1();

	$viewQuery = "SELECT st.idx, st.taxi_MemId, st.reg_Date, st.taxi_Eaddr, st.taxi_TPrice, st.taxi_Price, st.taxi_Memo, st.taxi_Per, st.taxi_ATime, st.taxi_SDate, st.taxi_State, info.taxi_SexBit, info.taxi_SexBit, DATE_ADD(st.taxi_SDate, INTERVAL 30 MINUTE) AS chkDate FROM TB_STAXISHARING AS st INNER JOIN TB_STAXISHARING_INFO AS info ON st.idx = info.taxi_Idx WHERE st.idx = :idx AND taxi_State IN ('1', '2', '3', '4') LIMIT 1 ";
	// echo $viewQuery."<BR>";
	// exit;
	$viewStmt = $DB_con->prepare($viewQuery);
	$viewStmt->bindparam(":idx", $idx);
	$viewStmt->execute();
	$num = $viewStmt->rowCount();
	//echo $num."<BR>";

	if ($num < 1) { //아닐경우
		$result = array("result" => false, "errorMsg" => "신청불가능한 노선입니다. 확인 후 다시 시도해주세요.");
	} else {

		while ($row = $viewStmt->fetch(PDO::FETCH_ASSOC)) {

			$idx =  trim($row['idx']);    // 생성자아이디
			$taxiMemId =  trim($row['taxi_MemId']);    // 생성자아이디
			$taxiEaddr =  trim($row['taxi_Eaddr']);		  // 목적지주소
			$taxiMemo =  trim($row['taxi_Memo']);	    // 하고싶은말
			$taxiTPrice =  trim($row['taxi_TPrice']);	    // 총택시요금
			$taxiPrice =  trim($row['taxi_Price']);			    // 희망쉐어링요금
			$taxiPer =  trim($row['taxi_Per']);					// 희망쉐어링 %
			$taxiATime =  trim($row['taxi_ATime']);		 //총 예상시간
			$taxiSDate =  trim($row['taxi_SDate']);		 //매칭요청시간
			$chkDate =  trim($row['chkDate']);		 //매칭요청시간
			$taxiRDate = trim($row['reg_Date']); //생성시간
			$taxiState =  trim($row['taxi_State']);			 //상태값
			$taxiState = (int)$taxiState;
			$taxiSex = trim($row['taxi_Sex']);
			$taxiSexBit = trim($row['taxi_SexBit']);
			if ($taxiSexBit == "0") {
				$taxiSexBit = false;
			} else {
				$taxiSexBit = true;
			}

			$taxiDPrice = (int)$taxiTPrice - (int)$taxiPrice;	// 절약금액 계산
			$taxiTotalDPrice = floor($taxiDPrice / 1000) * 1000;

			$taxiDPriceMemo = "약 " . number2hangul($taxiTotalDPrice) . "원 절약";

			///자기정보인지 여부를 내려줍니다.
			if ($mem_Id == $taxiMemId) {
				$isOwner = true;
			} else {
				$isOwner = false;
			}
		}
		// 여자 인경우 성별 확인 주석처리 //성별이 남자이면서, 노선이 여자만인 경우에는 신청불가.
		// 여자 인경우 성별 확인 주석처리 if ($memSex == "0" && $taxiSexBit == "1") {
		// 여자 인경우 성별 확인 주석처리 	$result = array("result" => false, "errorMsg" => "이 노선은 여자만 신청 가능한 노선입니다.");
		// 여자 인경우 성별 확인 주석처리 } else {
		//생성 정보
		$infoQuery = "";
		$infoQuery = "SELECT taxi_MemIdx, taxi_Type, taxi_Mcnt, taxi_Distance, taxi_Route, taxi_Sex, taxi_SexBit, taxi_Seat FROM TB_STAXISHARING_INFO WHERE taxi_Idx = :taxi_Idx LIMIT 1 ";
		//echo $infoQuery."<BR>";
		//exit;
		$infoStmt = $DB_con->prepare($infoQuery);
		$infoStmt->bindparam(":taxi_Idx", $idx);
		$infoStmt->execute();
		$infoNum = $infoStmt->rowCount();
		//echo $infoNum."<BR>";

		if ($infoNum < 1) { //아닐경우
		} else {
			while ($infoRow = $infoStmt->fetch(PDO::FETCH_ASSOC)) {
				$taxiMemIdx =  trim($infoRow['taxi_MemIdx']);						//출발타입 ( 0: 바로출발, 1: 예약출발 )
				$taxiType =  trim($infoRow['taxi_Type']);						//출발타입 ( 0: 바로출발, 1: 예약출발 )
				$taxiMcnt =  trim($infoRow['taxi_Mcnt']);					// 인원수
				$lineDistance =  trim($infoRow['taxi_Distance']);		// 예상거리

				// if ($lineDistance <= "1000") {
				// 	$lineTDistance = $lineDistance . "m";    // 미터
				// } else {
				// 	$taxiDistance = $lineDistance / 1000.0;
				// 	$lineTDistance = round($taxiDistance, 2) . "km";    // 미터를 km로 변환
				// }
				$lineTDistance = $lineDistance;    // 미터

				$taxi_Route =  trim($infoRow['taxi_Route']);				// 경유가능여부 ( 0: 경유가능, 1: 경유불가)

				if ($taxi_Route == "0") {
					$taxiRoute = true;
				} else {
					$taxiRoute = false;
				}

				$taxiSex =  trim($infoRow['taxi_Sex']);						 //성별 ( 0: 남자, 1: 여자)
				$taxiSexBit =  trim($infoRow['taxi_SexBit']);				 //선호하는매칭 ( 0: 성별무관, 1: 여자만)
				if ($taxiSexBit == "0") {
					$taxiSexBit = false;
				} else {
					$taxiSexBit = true;
				}

				$taxiSeat =  trim($infoRow['taxi_Seat']);					 //좌석 ( 0: 앞좌석, 1: 뒷좌석)


			}
		}

		//생성 지도정보
		$mapQuery = "";
		$mapQuery = "SELECT taxi_SaddrNm, taxi_EaddrNm, taxi_Sdong, taxi_Edong, taxi_SLat, taxi_SLng, taxi_Point, taxi_Line FROM TB_STAXISHARING_MAP WHERE taxi_Idx = :taxi_Idx LIMIT 1 ";
		//echo $mapQuery."<BR>";
		//exit;
		$mapStmt = $DB_con->prepare($mapQuery);
		$mapStmt->bindparam(":taxi_Idx", $idx);
		$mapStmt->execute();
		$mapNum = $mapStmt->rowCount();
		//echo $mapNum."<BR>";

		if ($mapNum < 1) { //아닐경우
		} else {
			while ($mapRow = $mapStmt->fetch(PDO::FETCH_ASSOC)) {

				$taxiSaddrNm = trim($mapRow['taxi_SaddrNm']);					  //  출발지주소
				$taxiEaddrNm = trim($mapRow['taxi_EaddrNm']);					  //  목적지주소
				$taxiSdong = trim($mapRow['taxi_Sdong']);					  //  목적지주소
				$taxiEdong = trim($mapRow['taxi_Edong']);					  //  목적지주소
				$taxiSLat = trim($mapRow['taxi_SLat']);						  // 출발지 구글 위도
				$taxiSLng = trim($mapRow['taxi_SLng']);					  // 출발지 구글 경도
				$taxiPoint = trim($mapRow['taxi_Point']);				  // 경로포인트
				$taxiLine = trim($mapRow['taxi_Line']);					  // 경로라인
			}
		}

		//생성자정보

		// 매칭 성공횟수
		$mnSql = "  , ( SELECT mem_Matcnt  FROM TB_MEMBERS_ETC WHERE TB_MEMBERS_ETC.mem_Id = TB_MEMBERS.mem_Id limit 1 ) AS mem_Matcnt  ";
		$memQuery = "";
		$memQuery = "SELECT mem_NickNm, mem_Lv {$mnSql} from TB_MEMBERS  WHERE mem_Id = :idx AND b_Disply = 'N' LIMIT 1"; //메이커 정보
		$memStmt = $DB_con->prepare($memQuery);
		$memStmt->bindparam(":idx", $taxiMemIdx);

		$memStmt->execute();
		$vnum = $memStmt->rowCount();
		//echo $vnum."<BR>";

		if ($vnum < 1) { //아닐경우
		} else {

			while ($vrow = $memStmt->fetch(PDO::FETCH_ASSOC)) {
				$memNickNm = trim($vrow['mem_NickNm']);							// 회원아이디
				$memMatcnt = trim($vrow['mem_Matcnt']);						// 매칭성공횟수

				if ($memMatcnt  == "") {
					$memMatcnt = "0";
				} else {
					$memMatcnt = $memMatcnt;
				}

				$memLv = trim($vrow['mem_Lv']);										// 회원레벨

			}
		}

		$memChkQuery = "SELECT A.mem_CharBit, A.mem_CharIdx, B.mem_profile_update FROM TB_MEMBERS AS A LEFT OUTER JOIN TB_MEMBER_PHOTO AS B ON A.idx = B.mem_Idx WHERE A.idx = :taxi_MemIdx";
		$memChkStmt = $DB_con->prepare($memChkQuery);
		$memChkStmt->bindparam(":taxi_MemIdx", $taxiMemIdx);
		$memChkStmt->execute();
		$memChkNum = $memChkStmt->rowCount();
		if ($memChkNum < 1) { //아닐경우
		} else {
			while ($memChkRow = $memChkStmt->fetch(PDO::FETCH_ASSOC)) {

				$mem_CharBit = $memChkRow['mem_CharBit'];            // 캐릭터프로필 선택 여부( 0: 미선택, 1: 선택)
				$mem_CharIdx = $memChkRow['mem_CharIdx'];            // 캐릭터프로필 고유번호
				$mem_profile_update = trim($memChkRow['mem_profile_update']);

				if ($mem_CharIdx == "") {
					$memCharIdx = "";
				} else {
					$memCharIdx = $mem_CharIdx;
				}
				if ($mem_CharBit == "1") {
					$profileQuery = "SELECT con_ProfileNo, con_ProfileImg FROM TB_CONFIG_PROFILE WHERE con_ProfileBit = 'Y' AND con_ProfileNo = :memCharIdx ORDER BY con_ProfileSort ASC";
					$profileStmt = $DB_con->prepare($profileQuery);
					$profileStmt->bindparam(":memCharIdx", $memCharIdx);
					$profileStmt->execute();
					$profileRow = $profileStmt->fetch(PDO::FETCH_ASSOC);
					$profile_Img = $profileRow['con_ProfileImg'];

					$imgUrl = "/data/config/profile/";
					$profileImg = $imgUrl . $profile_Img;

					$memImgFile = $profileImg;
				} else {
					if ($mem_profile_update == '') {
						$memImgFile = '';
					} else {
						$memImgFile = '/data/member/photo.php?id=' . $mem_profile_update;
					}
				}
			}
		}
		//, "taxiELat" => (float)$taxiELat, "taxiELng" => (float)$taxiELng, "taxiSeat" => (string)$taxiSeat
		$result = array("result" => true, "idx" => (int)$idx, "state" => (string)$taxiState, "isOwner" => $isOwner, "taxiSLat" => (float)$taxiSLat, "taxiSLng" => (float)$taxiSLng, "taxiSaddrNm" => (string)$taxiSaddrNm, "taxiEaddrNm" => (string)$taxiEaddrNm, "taxiSdong" => (string)$taxiSdong, "taxiEdong" => (string)$taxiEdong, "taxiATime" => (int)$taxiATime, "lineTDistance" => (string)$lineTDistance, "taxiTPrice" => (int)$taxiTPrice, "taxiPrice" => (int)$taxiPrice, "taxiRoute" => $taxiRoute, "taxiMcnt" => (int)$taxiMcnt, "taxiSex" => (string)$taxiSex, "taxiType" => (string)$taxiType, "taxiSDate" => (string)$taxiSDate, "chkDate" => (string)$chkDate, "taxiRDate" => (string)$taxiRDate, "taxiMemId" => (string)$taxiMemId, "memImgFile" => (string)$memImgFile, "memNickNm" => (string)$memNickNm, "memMatcnt" => (int)$memMatcnt, "taxiPer" => (float)$taxiPer, "taxiSexBit" => $taxiSexBit, "taxiMemo" => (string)$taxiMemo, "taxiDPriceMemo" => (string)$taxiDPriceMemo, "taxiPoint" => json_decode($taxiPoint), "taxiLine" => json_decode($taxiLine));
		// 여자 인경우 성별 확인 주석처리 }
	}
	dbClose($DB_con);
	$viewStmt = null;
	$infoStmt = null;
	$mapStmt = null;
	$memStmt = null;

	echo str_replace('\\/', '/', json_encode($result, JSON_UNESCAPED_UNICODE));
} else {
	$result = array("result" => false);
	echo json_encode($result, JSON_UNESCAPED_UNICODE);
}
