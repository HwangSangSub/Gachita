<?
include "../lib/common.php";
include "../lib/functionDB.php";  //공통 db함수
include "../lib/functionWithdrawal.php";  //회원탈퇴 관련

$mem_Id = trim($memId);					// 아이디
$mem_Idx = memIdxInfo($mem_Id);   		// 회원 고유번호
$startLng = trim($startLng);			// 경도
$startLat = trim($startLat);			// 위도
$chkDistance = trim($chkDistance);	 	// 거리 (1, 3, 5 )    // 거리 (1, 3, 5 ) ex) 0.5 => 500m 동일
$sort = trim($sort);	 				// 정렬방식(신규 순 : 0, 출발지 가까운 순 : 1, 동성끼리 탑승 : 2)
$addrIdx = trim($addrIdx);	 			// 즐겨찾기주소고유번호
//거리 조건
if ($chkDistance == "1") {  //500m
	$chkDistance = 0.5;
} else if ($chkDistance == "3") {  //700m
	$chkDistance = 0.7;
} else if ($chkDistance == "5") {  //1000m
	$chkDistance = 1;
} else if ($chkDistance == "7") {  //2Km
	$chkDistance = 2.0;
} else if ($chkDistance == "9") {  //3Km
	$chkDistance = 3.0;
} else if ($chkDistance == "") {
	$chkDistance = 0.5;
}
//

$DB_con = db1();

if ($startLng != "" && $startLat != "" && $chkDistance != "") {  // 위도, 경도, km
	if ($addrIdx != "") {
		$addrSelQuery = "SELECT mem_Dong FROM TB_MEMBERS_MAP WHERE idx = :addrIdx";
		$addrSelStmt = $DB_con->prepare($addrSelQuery);
		$addrSelStmt->bindparam(":addrIdx", $addrIdx);
		$addrSelStmt->execute();
		$addrSelRow = $addrSelStmt->fetch(PDO::FETCH_ASSOC);
		$memDong = trim($addrSelRow['mem_Dong']);	      // 즐겨찾기 주소 동명1
	}
	$sql_search = "  WHERE 1 ";
	$sql_search_my = "	AND A.taxi_MemId <> :mem_Id ";

	if ($sort == "0" ||  $sort == "") {
		$sort1  = "B.reg_Date DESC";
	} else if ($sort == "1") {
		$sort1  = "distance ASC";
	} else if ($sort == "2") {
		$sort1  = "B.reg_Date DESC";
		$sql_search  .= " AND C.taxi_SexBit = 1 AND C.taxi_Sex = (SELECT mem_Sex FROM TB_MEMBERS_INFO WHERE mem_Idx = :mem_Idx) ";
	}
	if ($memDong != "") {
		$sql_search  .= " AND (A.taxi_Eaddr LIKE '%" . $memDong . "%' OR A.taxi_Edong LIKE '%" . $memDong . "%')";
	}

	$sql_order = "ORDER BY $sort1";

	$chkData["result"] = true;


	$taxi_SIdx = sharingRWaitCnt($mem_Idx);
	// 내 노선 조회
	$viewMyQuery = "SELECT A.taxi_Idx, A.taxi_MemId, A.taxi_MemIdx, A.taxi_SLat, A.taxi_SLng, A.taxi_SaddrNm, A.taxi_EaddrNm, A.taxi_Sdong, A.taxi_Edong, B.taxi_State, A.taxi_EaddrNm, ";
	$viewMyQuery .= "  ( 6371 * acos( cos( radians(:startLat) ) * cos( radians( A.taxi_SLat ) ) * cos( radians( A.taxi_SLng ) - radians(:startLng) ) + sin( radians(:startLat) ) * sin( radians( A.taxi_SLat ) ) ) ) AS  distance ";
	$viewMyQuery .= ",B.reg_Date, B.taxi_TPrice, C.taxi_SexBit , B.taxi_SDate, B.taxi_Memo, DATE_ADD(B.taxi_SDate, INTERVAL -30 MINUTE) AS chkDate, DATE_ADD(B.taxi_SDate, INTERVAL 30 MINUTE) AS chkDate2 ,B.taxi_Price ";
	$viewMyQuery .= ",A.taxi_Point, A.taxi_Line, C.taxi_Sex";
	$viewMyQuery .= " FROM TB_STAXISHARING_MAP A ";
	$viewMyQuery .= " INNER JOIN TB_STAXISHARING B ON B.idx = A.taxi_Idx ";
	$viewMyQuery .= " INNER JOIN TB_STAXISHARING_INFO C ON A.taxi_Idx = C.taxi_Idx";
	if ($taxi_SIdx != "") {
		$viewMyQuery .= " AND A.taxi_Idx = :taxi_SIdx ";
	} else {
		$viewMyQuery .= " {$sql_search} AND B.taxi_State IN ('1', '2', '3')";
		$viewMyQuery .= " AND A.taxi_MemId = :mem_Id ";
	}
	$viewMyQuery .= " AND DATE_ADD(B.taxi_SDate, INTERVAL 30 MINUTE) > NOW()";
	$viewMyQuery .= " {$sql_order}";

	$viewMyStmt = $DB_con->prepare($viewMyQuery);
	$viewMyStmt->bindparam(":startLat", $startLat);
	$viewMyStmt->bindparam(":startLng", $startLng);
	if ($sort == "2") {
		$viewMyStmt->bindparam(":mem_Idx", $mem_Idx);
	}
	if ($taxi_SIdx != "") {
		$viewMyStmt->bindparam(":taxi_SIdx", $taxi_SIdx);
	} else {
		$viewMyStmt->bindparam(":mem_Id", $mem_Id);
	}
	$viewMyStmt->execute();
	$myNum = $viewMyStmt->rowCount();
	if ($myNum < 1) {
		$chkData['mydata'] = [];
	} else {
		$mydata  = [];
		while ($myRow = $viewMyStmt->fetch(PDO::FETCH_ASSOC)) {

			$idx = trim($myRow['taxi_Idx']);	      // 고유번호
			$taxiMemIdx = trim($myRow['taxi_MemIdx']);	      // 매칭생성고유번호
			$taxiMemId = trim($myRow['taxi_MemId']);	      // 매칭생성아이디
			$state = trim($myRow['taxi_State']);	      // 고유번호

			$taxiSLat = trim($myRow['taxi_SLat']);						  // 현재위치 구글 위도
			$taxiSLng = trim($myRow['taxi_SLng']);						  // 현재위치 구글 경도

			$taxiSaddrNm = trim($myRow['taxi_SaddrNm']);	      // 출발지동명 ==> //20181024 도착지동으로 수정(부장님 요청)
			$taxiEaddrNm = trim($myRow['taxi_EaddrNm']);

			$taxiSdong = trim($myRow['taxi_Sdong']);
			$taxiEdong = trim($myRow['taxi_Edong']);

			$taxiTPrice = trim($myRow['taxi_TPrice']);
			$taxiPrice = trim($myRow['taxi_Price']);

			$taxiPoint = trim($myRow['taxi_Point']);
			$taxiLine = trim($myRow['taxi_Line']);

			$taxiDPrice = (int)$taxiTPrice - (int)$taxiPrice;	// 절약금액 계산
			$taxiTotalDPrice = floor($taxiDPrice / 1000) * 1000;

			$taxiDPriceMemo = "약 " . number2hangul($taxiTotalDPrice) . "원 절약";

			$taxiSex = trim($myRow['taxi_Sex']);
			$taxiSexBit = trim($myRow['taxi_SexBit']);
			if ($taxiSexBit == "0") {
				$taxiSexBit = false;
			} else {
				$taxiSexBit = true;
			}

			//시간정보 가져오기
			$shaQuery = "SELECT taxi_Type , taxi_Route from TB_STAXISHARING_INFO  WHERE taxi_Idx = :taxi_Idx AND taxi_MemId = :taxi_MemId  ";
			$stmt = $DB_con->prepare($shaQuery);
			$stmt->bindparam(":taxi_Idx", $idx);
			$stmt->bindparam(":taxi_MemId", $taxiMemId);
			$stmt->execute();
			$shrNum = $stmt->rowCount();

			if ($shrNum < 1) { //아닐경우
			} else {
				while ($shrRow = $stmt->fetch(PDO::FETCH_ASSOC)) {
					$taxiType = trim($shrRow['taxi_Type']);
					$taxiRoute = trim($shrRow['taxi_Route']);
				}
			}
			if ($taxiRoute == "0") {
				$taxiRoute = true;
			} else {
				$taxiRoute = false;
			}
			$taxiRDate = trim($myRow['reg_Date']); //생성시간
			$taxiSDate = trim($myRow['taxi_SDate']); //출발시간
			$chkNDate = trim($myRow['chkDate']);	 //생성후 30분
			$chkNDate2 = trim($myRow['chkDate2']);	 //생성후 30분후

			if ($taxiType == "0") { //바로출발 일 경우
				$chkDate = $chkNDate2;			 //30분후 시간
			} else {
				$chkDate = $chkNDate2;     //30분전 시간
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
			///자기정보인지 여부를 내려줍니다.
			if ($mem_Id == $taxiMemId) {
				$isOwner = true;
			} else {
				$isOwner = false;
			}
			//경유가능 여부를 내려줍니다.
			//요청금액도 내려줍니다.
			$taxiMemo = trim($myRow['taxi_Memo']);

			// 미션참가한 노선인지 여부를 내려줍니다.(팝업용)
			$makerRoomChk = makerRoomChk($mem_Idx, $idx);

			// 권대리요청으로 제거., "taxiPoint" => json_decode($taxiPoint), "taxiLine" => json_decode($taxiLine) 
			$myresult = ["idx" => (int)$idx, "taxiRoute" => $taxiRoute, "taxiTPrice" => (int)$taxiTPrice, "taxiPrice" => (int)$taxiPrice, "isOwner" => $isOwner,  "taxiMemId" => (string)$taxiMemId, "taxiSLat" => (float)$taxiSLat, "taxiSLng" => (float)$taxiSLng, "taxiSaddrNm" => (string)$taxiSaddrNm, "taxiEaddrNm" => (string)$taxiEaddrNm, "taxiType" => (string)$taxiType, "taxiRDate" => (string)$taxiRDate, "taxiSDate" => (string)$taxiSDate, "chkDate" => (string)$chkDate, "state" => (string)$state, "taxiMemo" => (string)$taxiMemo, "taxiSexBit" => $taxiSexBit, "taxiDPriceMemo" => (string)$taxiDPriceMemo, "taxiSex" => (string)$taxiSex, "memImgFile" => (string)$memImgFile, "taxiSdong" => (string)$taxiSdong, "taxiEdong" => (string)$taxiEdong, "eventPopupChk" => $makerRoomChk];
			array_push($mydata, $myresult);
		}
		$chkData['mydata'] = $mydata;
	}

	/* 매칭대기 목록 */
	$viewQuery = "";
	$viewQuery = "SELECT A.taxi_Idx, A.taxi_MemId, A.taxi_MemIdx, A.taxi_SLat, A.taxi_SLng, A.taxi_SaddrNm, A.taxi_EaddrNm, A.taxi_Sdong, A.taxi_Edong, B.taxi_State, A.taxi_EaddrNm, ";
	//$viewQuery .= " ROUND ( 6371 * acos( cos( radians(:startLat) ) * cos( radians( A.taxi_SLat ) ) * cos( radians( A.taxi_SLng ) - radians(:startLng) ) + sin( radians(:startLat) ) * sin( radians( A.taxi_SLat ) ) ) ) AS  distance ";
	$viewQuery .= "  ( 6371 * acos( cos( radians(:startLat) ) * cos( radians( A.taxi_SLat ) ) * cos( radians( A.taxi_SLng ) - radians(:startLng) ) + sin( radians(:startLat) ) * sin( radians( A.taxi_SLat ) ) ) ) AS  distance ";
	$viewQuery .= ",B.reg_Date, B.taxi_TPrice, C.taxi_SexBit , B.taxi_SDate, B.taxi_Memo, DATE_ADD(B.taxi_SDate, INTERVAL -30 MINUTE) AS chkDate, DATE_ADD(B.taxi_SDate, INTERVAL 30 MINUTE) AS chkDate2 ,B.taxi_Price ";
	$viewQuery .= ",A.taxi_Point, A.taxi_Line, C.taxi_Sex";
	$viewQuery .= " FROM TB_STAXISHARING_MAP A ";
	$viewQuery .= " INNER JOIN TB_STAXISHARING B ON B.idx = A.taxi_Idx ";
	$viewQuery .= " INNER JOIN TB_STAXISHARING_INFO C ON A.taxi_Idx = C.taxi_Idx";
	if ($taxi_SIdx != "") {
		$viewQuery .= " {$sql_search} {$sql_search_my} AND B.taxi_State IN ('1', '2', '3')";
		$viewQuery .= " AND A.taxi_Idx <> :taxi_SIdx ";
	} else {
		$viewQuery .= " {$sql_search} {$sql_search_my} AND B.taxi_State IN ('1', '2', '3')";
		$viewQuery .= " AND A.taxi_MemId <> :mem_Id ";
	}
	$viewQuery .= " AND DATE_ADD(B.taxi_SDate, INTERVAL 30 MINUTE) > NOW()";
	$viewQuery .= " HAVING distance <= :chkDistance {$sql_order}";
	//echo $viewQuery."<BR>";
	//exit;

	$viewStmt = $DB_con->prepare($viewQuery);
	$viewStmt->bindparam(":startLat", $startLat);
	$viewStmt->bindparam(":startLng", $startLng);
	if ($sort == "2") {
		$viewStmt->bindparam(":mem_Idx", $mem_Idx);
	}
	if ($taxi_SIdx != "") {
		$viewStmt->bindparam(":taxi_SIdx", $taxi_SIdx);
		$viewStmt->bindparam(":mem_Id", $mem_Id);
	} else {
		$viewStmt->bindparam(":mem_Id", $mem_Id);
	}
	$viewStmt->bindparam(":chkDistance", $chkDistance);
	$viewStmt->execute();
	$num = $viewStmt->rowCount();
	$data  = [];
	while ($row = $viewStmt->fetch(PDO::FETCH_ASSOC)) {

		$idx = trim($row['taxi_Idx']);	      // 고유번호
		$taxiMemIdx = trim($row['taxi_MemIdx']);	      // 매칭생성고유번호
		$taxiMemId = trim($row['taxi_MemId']);	      // 매칭생성아이디
		$state = trim($row['taxi_State']);	      // 고유번호

		$taxiSLat = trim($row['taxi_SLat']);						  // 현재위치 구글 위도
		$taxiSLng = trim($row['taxi_SLng']);						  // 현재위치 구글 경도

		$taxiSaddrNm = trim($row['taxi_SaddrNm']);	      // 출발지동명 ==> //20181024 도착지동으로 수정(부장님 요청)
		$taxiEaddrNm = trim($row['taxi_EaddrNm']);

		$taxiSdong = trim($row['taxi_Sdong']);
		$taxiEdong = trim($row['taxi_Edong']);

		$taxiTPrice = trim($row['taxi_TPrice']);
		$taxiPrice = trim($row['taxi_Price']);

		$taxiPoint = trim($row['taxi_Point']);
		$taxiLine = trim($row['taxi_Line']);

		$taxiDPrice = (int)$taxiTPrice - (int)$taxiPrice;	// 절약금액 계산
		$taxiTotalDPrice = floor($taxiDPrice / 1000) * 1000;

		$taxiDPriceMemo = "약 " . number2hangul($taxiTotalDPrice) . "원 절약";

		$taxiSex = trim($row['taxi_Sex']);
		$taxiSexBit = trim($row['taxi_SexBit']);
		if ($taxiSexBit == "0") {
			$taxiSexBit = false;
		} else {
			$taxiSexBit = true;
		}

		//시간정보 가져오기
		$shaQuery = "SELECT taxi_Type , taxi_Route from TB_STAXISHARING_INFO  WHERE taxi_Idx = :taxi_Idx AND taxi_MemId = :taxi_MemId  ";
		$stmt = $DB_con->prepare($shaQuery);
		$stmt->bindparam(":taxi_Idx", $idx);
		$stmt->bindparam(":taxi_MemId", $taxiMemId);
		$stmt->execute();
		$shrNum = $stmt->rowCount();

		if ($shrNum < 1) { //아닐경우
		} else {
			while ($shrRow = $stmt->fetch(PDO::FETCH_ASSOC)) {
				$taxiType = trim($shrRow['taxi_Type']);
				$taxiRoute = trim($shrRow['taxi_Route']);
			}
		}
		if ($taxiRoute == "0") {
			$taxiRoute = true;
		} else {
			$taxiRoute = false;
		}
		$taxiRDate = trim($row['reg_Date']); //생성시간
		$taxiSDate = trim($row['taxi_SDate']); //출발시간
		$chkNDate = trim($row['chkDate']);	 //생성후 30분
		$chkNDate2 = trim($row['chkDate2']);	 //생성후 30분후

		if ($taxiType == "0") { //바로출발 일 경우
			$chkDate = $chkNDate2;			 //30분후 시간
		} else {
			$chkDate = $chkNDate2;     //30분전 시간
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
		///자기정보인지 여부를 내려줍니다.
		if ($mem_Id == $taxiMemId) {
			$isOwner = true;
		} else {
			$isOwner = false;
		}
		//경유가능 여부를 내려줍니다.
		//요청금액도 내려줍니다.
		$taxiMemo = trim($row['taxi_Memo']);

		// 권대리 요청으로 MInfo API로 이동, "taxiPoint" => json_decode($taxiPoint), "taxiLine" => json_decode($taxiLine)
		$result = ["idx" => (int)$idx, "taxiRoute" => $taxiRoute, "taxiTPrice" => (int)$taxiTPrice, "taxiPrice" => (int)$taxiPrice, "isOwner" => $isOwner,  "taxiMemId" => (string)$taxiMemId, "taxiSLat" => (float)$taxiSLat, "taxiSLng" => (float)$taxiSLng, "taxiSaddrNm" => (string)$taxiSaddrNm, "taxiEaddrNm" => (string)$taxiEaddrNm, "taxiType" => (string)$taxiType, "taxiRDate" => (string)$taxiRDate, "taxiSDate" => (string)$taxiSDate, "chkDate" => (string)$chkDate, "state" => (string)$state, "taxiMemo" => (string)$taxiMemo, "taxiSexBit" => $taxiSexBit, "taxiDPriceMemo" => (string)$taxiDPriceMemo, "taxiSex" => (string)$taxiSex, "memImgFile" => (string)$memImgFile, "taxiSdong" => (string)$taxiSdong, "taxiEdong" => (string)$taxiEdong];
		array_push($data, $result);
	}
	$chkData['data'] = $data;
	$output = str_replace('\\\/', '/', json_encode($chkData, JSON_UNESCAPED_UNICODE));


	dbClose($DB_con);
	$cntStmt = null;
	$viewStmt = null;
	$stmt = null;

	echo  urldecode($output);
} else {
	$result = array("result" => false, "errorMsg" => "조회 정보값이 없습니다. 관리자에게 문의바랍니다.");
	echo json_encode($result, JSON_UNESCAPED_UNICODE);
}
