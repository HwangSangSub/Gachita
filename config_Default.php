<?
include "./lib/common.php";

//$mem_Id = "shut7720@hanmail.net";

$DB_con = db1();

$reg_Date = DU_TIME_YMDHIS;         // 등록일
$now_Day = date('Y-m-d', strtotime($reg_Date));   // 오늘

/*	필요시 사용
			, con_Sec, con_mTxt, conComp1_Min_D, conComp1_Max_D, conComp1_H, conComp2_Min_D, conComp2_Max_D, conComp2_H, conComp3_Min_D, conComp3_Max_D, conComp3_H, conComp4_Min_D, conComp4_H, conRecom_RC, conRecom_RP, conRecom_BRC, conRecom_BRP
		*/
$Query = "SELECT idx, con_Distance, con_SharingD, con_SharingS, con_ETime, con_MaxDc, con_GpsTime, con_GpsPTime, con_GpsYTime, con_GpsRegTime, con_BtnTime, con_Version, con_UpBit, con_GuideUrl, con_MinPriceRate, con_MaxPriceRate, con_IntervalPriceRate FROM TB_CONFIG ";
$Stmt = $DB_con->prepare($Query);
$Stmt->execute();
$Num = $Stmt->rowCount();

if ($Num < 1) { //아닐경우
	$result = array("result" => "error");
} else {
	while ($row = $Stmt->fetch(PDO::FETCH_ASSOC)) {
		$con_SharingD = $row['con_SharingD'];						//만남완료변수설정(거리_m)
		$con_SharingS = $row['con_SharingS'];						//만남완료변수설정(시간_초)
		$con_ETime = $row['con_ETime'];								//노선유효시간(분)
		$con_Distance = $row['con_Distance'];						//매칭가능거리(m)
		$con_MaxDc = $row['con_MaxDc'];								//매칭증가요금(%)
		$con_GpsTime = $row['con_GpsTime'];							//GPS신호 재 탐색(분단위)
		$con_GpsPTime = $row['con_GpsPTime'];						//GPS 동일 위치 푸시발송시간 (분단위)
		$con_GpsYTime = $row['con_GpsYTime'];						//GPS 동일 위치 2회 이후 상대방 알림 시간 (분단위)
		$con_GpsRegTime = $row['con_GpsRegTime'];					//GPS 위치기록시간 (분단위)
		$con_BtnTime = $row['con_BtnTime'];							//바로양도 버튼클릭시간 => 50%설정 시 도착예상시간이 20분인 경우 50%인 10분 (% 단위)
		$con_GuideUrl = $row['con_GuideUrl'];						//가이드 웹뷰 경로
		$con_Version = $row['con_Version'];							//현재버전
		$con_MinPriceRate = $row['con_MinPriceRate'];				//메이커 생성 최소 요금(%)
		$conMinPriceRate = $con_MinPriceRate / 100;				
		$con_MaxPriceRate = $row['con_MaxPriceRate'];				//메이커 생성 최대 요금(%)
		$conMaxPriceRate = $con_MaxPriceRate / 100;				
		$con_IntervalPriceRate = $row['con_IntervalPriceRate'];		//메이커 생성 간격 요금(%)
		$conIntervalPriceRate = $con_IntervalPriceRate / 100;				
		$con_UpBit = $row['con_UpBit'];								//업데이트 필요여부
		if ($con_UpBit == 'Y') {
			$con_UpBit = true;
		} else {
			$con_UpBit = false;
		}
		/*$con_Sec = $row['con_Sec'];				//매칭현황시간(초)
					$con_mTxt = $row['con_mTxt'];				//취소안내문구
					$conComp1_Max_D = $row['conComp1_Max_D'];	//조건 1 : 최대거리
					$conComp1_H = $row['conComp1_H'];			//조건 1 : 제한시간
					$conComp2_Min_D = $row['conComp2_Min_D'];	//조건 2 : 최소거리
					$conComp2_Max_D = $row['conComp2_Max_D'];	//조건 2 : 최대거리
					$conComp2_H = $row['conComp2_H'];			//조건 2 : 제한시간
					$conComp3_Min_D = $row['conComp3_Min_D'];	//조건 3 : 최소거리
					$conComp3_Max_D = $row['conComp3_Max_D'];	//조건 3 : 최대거리
					$conComp3_H = $row['conComp3_H'];			//조건 3 : 제한시간
					$conComp4_Min_D = $row['conComp4_Min_D'];	//조건 4 : 최소거리
					$conComp4_H = $row['conComp4_H'];			//조건 4 : 제한시간
					$conRecom_RC = $row['conRecom_RC'];			//추천 받을 시 포인트적립
					$conRecom_RP = $row['conRecom_RP'];			//추천 받을 시 회원등급점수
					$conRecom_BRC = $row['conRecom_BRC'];		//추천 할 시 포인트적립
					$conRecom_BRP = $row['conRecom_BRP'];		//추천 받을 시 회원등급점수*/

		// 프로필 이미지
		$profileQuery = "SELECT con_ProfileNo, con_ProfileImg FROM TB_CONFIG_PROFILE WHERE con_ProfileBit = 'Y' ORDER BY con_ProfileSort ASC";
		$profileStmt = $DB_con->prepare($profileQuery);
		$profileStmt->execute();
		$profileNum = $profileStmt->rowCount();
		$profileList = [];
		if ($profileNum > 0) {
			while ($profileRow = $profileStmt->fetch(PDO::FETCH_ASSOC)) {
				$profile_Img = $profileRow['con_ProfileImg'];

				$imgUrl = "/data/config/profile/";

				$profileImg = $imgUrl . $profile_Img;
				array_push($profileList, $profileImg);
			}
		}

		// 햄버거메뉴 배너
		// 상시 노출이 아닌 배너가 있다면 기본 배너는 제외
		$bannerChkQuery = "SELECT COUNT(idx) AS bannerCnt FROM TB_BANNER WHERE b_Disply = 'Y' AND idx <> 1 ORDER BY ban_Sort ASC, reg_Date DESC";
		$bannerChkStmt = $DB_con->prepare($bannerChkQuery);
		$bannerChkStmt->execute();
		$bannerChkRow = $bannerChkStmt->fetch(PDO::FETCH_ASSOC);
		$bannerCnt = $bannerChkRow['bannerCnt'];

		if($bannerCnt > 0){
			$notIdx = "AND idx <> 1";
		}else{
			$notIdx = "";
		}

		$bannerQuery = "SELECT ban_ImgFile, ban_Url FROM TB_BANNER WHERE b_Disply = 'Y' {$notIdx} ORDER BY ban_Sort ASC, reg_Date DESC";
		$bannerStmt = $DB_con->prepare($bannerQuery);
		$bannerStmt->execute();
		$bannerNum = $bannerStmt->rowCount();
		$bannerList = [];
		if ($bannerNum > 0) {
			while ($bannerRow = $bannerStmt->fetch(PDO::FETCH_ASSOC)) {
				$ban_ImgFile = $bannerRow['ban_ImgFile'];
				$ban_Url = $bannerRow['ban_Url'];
				$imgUrl = "/data/banner/photo.php?id=";
				$banImgFile = $imgUrl . $ban_ImgFile;
				$bannerResult = array("img" => (string)$banImgFile, "link" => (string)$ban_Url);
				array_push($bannerList, $bannerResult);
			}
		}

		// 메인 하단에 공지사항 (최대 3개)
		$noticeQuery = "SELECT idx, b_Title FROM TB_BOARD WHERE b_Idx = 1 ORDER BY reg_Date DESC LIMIT 3";
		$noticeStmt = $DB_con->prepare($noticeQuery);
		$noticeStmt->execute();
		$noticeNum = $noticeStmt->rowCount();
		$noticeList = [];
		if ($noticeNum > 0) {
			while ($noticeRow = $noticeStmt->fetch(PDO::FETCH_ASSOC)) {
				$idx = $noticeRow['idx'];
				$title = $noticeRow['b_Title'];
				$link = "https://" . $_SERVER['HTTP_HOST'] . "/board/noticeView.php?idx=" . $idx;
				$noticeResult = array("title" => $title, "link" => $link);
				array_push($noticeList, $noticeResult);
			}
		}

		// 즐겨찾는 장소 상단 이미지
		$bookmarkQuery = "SELECT bm_Img_1, bm_Img_2, bm_Img_3 FROM TB_CONFIG_BOOKMARK";
		$bookmarkStmt = $DB_con->prepare($bookmarkQuery);
		$bookmarkStmt->execute();
		$bookmarkNum = $bookmarkStmt->rowCount();
		$bookmarkList = [];
		if ($bookmarkNum > 0) {
			while ($bookmarkRow = $bookmarkStmt->fetch(PDO::FETCH_ASSOC)) {
				$bm_Img_1 = $bookmarkRow['bm_Img_1'];
				if ($bm_Img_1 != "") {
					$imgUrl = "/data/bookmark/photo.php?id=";
					$bookmarkImg = $imgUrl . $bm_Img_1;
					array_push($bookmarkList, $bookmarkImg);
				}
				$bm_Img_2 = $bookmarkRow['bm_Img_2'];
				if ($bm_Img_2 != "") {
					$imgUrl = "/data/bookmark/photo.php?id=";
					$bookmarkImg = $imgUrl . $bm_Img_2;
					array_push($bookmarkList, $bookmarkImg);
				}
				$bm_Img_3 = $bookmarkRow['bm_Img_3'];
				if ($bm_Img_3 != "") {
					$imgUrl = "/data/bookmark/photo.php?id=";
					$bookmarkImg = $imgUrl . $bm_Img_3;
					array_push($bookmarkList, $bookmarkImg);
				}
			}
		}
		
		// 팝업이미지 
		$popupQuery = "SELECT popup_Img, popup_Url FROM TB_CONFIG_POPUP WHERE (popup_Bit = 'Y' AND end_Date IS NULL) OR (popup_Bit = 'Y' AND DATE_FORMAT(end_Date, '%Y-%m-%d') >= :now_Day) ORDER BY popup_Sort ASC, reg_Date DESC LIMIT 3";
		$popupStmt = $DB_con->prepare($popupQuery);
		$popupStmt->bindparam(":now_Day", $now_Day);
		$popupStmt->execute();
		$popupNum = $popupStmt->rowCount();
		$popupList = [];
		if ($popupNum > 0) {
			while ($popupRow = $popupStmt->fetch(PDO::FETCH_ASSOC)) {
				$popup_Img = $popupRow['popup_Img'];
				$popup_Url = $popupRow['popup_Url'];
				$imgUrl = "/data/popup/photo.php?id=";
				$popupImg = $imgUrl . $popup_Img;
				$popupResult = array("img" => $popupImg, "link" => $popup_Url);
				array_push($popupList, $popupResult);
			}
		}


	}
	/* 필요시 사용
				,  "con_Sec" => $con_Sec,  "con_mTxt" => $con_mTxt,  "conComp1_Max_D" => $conComp1_Max_D,  "conComp1_H" => $conComp1_H,  "conComp2_Min_D" => $conComp2_Min_D,  "conComp2_Max_D" => $conComp2_Max_D,  "conComp2_H" => $conComp2_H,  "conComp3_Min_D" => $conComp3_Min_D,  "conComp3_Max_D" => $conComp3_Max_D,  "conComp3_H" => $conComp3_H,  "conComp4_Min_D" => $conComp4_Min_D,  "conComp4_H" => $conComp4_H,  "conRecom_RC" => $conRecom_RC,  "conRecom_RP" => $conRecom_RP,  "conRecom_BRC" => $conRecom_BRC,  "conRecom_BRP" => $conRecom_BRP 
				*/
	$result = array(
		"result" => true,
		"conSharingD" => (int)$con_SharingD,  "conSharingS" => (int)$con_SharingS,  "conETime" => (int)$con_ETime,  "conDistance" => (float)$con_Distance,
		"conMaxDc" => (int)$con_MaxDc, "conGpsTime" => (int)$con_GpsTime, "conGpsPTime" => (int)$con_GpsPTime, "conGpsYTime" => (int)$con_GpsYTime,
		"conGpsRegTime" => (int)$con_GpsRegTime, "conBtnTime" => (int)$con_BtnTime,
		"conVersion" => (int)$con_Version, "conUpBit" => $con_UpBit, "profile" => $profileList,
		"bannerList" => $bannerList, "noticeList" => $noticeList, "bookmarkList" => $bookmarkList, "popupList" => $popupList, "conGuideUrl" => (string)$con_GuideUrl,
		"minPriceRate" => (double)$conMinPriceRate, "maxPriceRate" => (double)$conMaxPriceRate, "intervalPriceRate" => (double)$conIntervalPriceRate

	);
}

dbClose($DB_con);
$Stmt = null;

echo json_encode($result, JSON_UNESCAPED_UNICODE);
