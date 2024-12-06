<?
/*======================================================================================================================

	 * 프로그램		: 매칭중 채팅창에서 상대방 닉네임 및 프로필이미지 확인
	 * 페이지 설명	: 매칭중 채팅창에서 상대방 닉네임 및 프로필이미지 확인

========================================================================================================================*/

include "../lib/common.php";
include "../lib/functionDB.php";  //공통 db함수

$idx = trim($idx);						// 매칭생성,요청 고유번호
$mode = trim($mode);					// 구분( p : 생성자, c : 요청자 ) ==> 소문자

if ($idx != "") {

	$DB_con = db1();

	if ($mode == "c") {		//요청자 인 경우
		$query = "SELECT taxi_SIdx, taxi_MemId AS mem_Id, taxi_MemIdx AS mem_Idx FROM TB_RTAXISHARING WHERE idx = :idx ";
		$stmt = $DB_con->prepare($query);
		$stmt->bindparam(":idx", $idx);
		$stmt->execute();
		$num = $stmt->rowCount();
	} else if ($mode == "p") {
		$query = "SELECT taxi_SIdx, taxi_RMemId AS mem_Id, taxi_RMemIdx AS mem_Idx FROM TB_RTAXISHARING WHERE taxi_SIdx = :taxi_SIdx";
		$stmt = $DB_con->prepare($query);
		$stmt->bindparam(":taxi_SIdx", $idx);
		$stmt->execute();
		$num = $stmt->rowCount();
	} else {
		$result = array("result" => false, "errorMsg" => "회원구분이 없습니다.");
	}

	if ($num < 1) { //이동중 노선이 없을 경우
		$result = array("result" => false, "errorMsg" => "이용중인 노선이 없습니다.");
	} else {
		while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
			$mem_Id = $row['mem_Id'];		// 회원아이디
			$mem_Idx = $row['mem_Idx'];		// 회원아이디
		}

		$memChkQuery = "SELECT A.mem_NickNm, A.mem_CharBit, A.mem_CharIdx, B.mem_profile_update FROM TB_MEMBERS AS A LEFT OUTER JOIN TB_MEMBER_PHOTO AS B ON A.idx = B.mem_Idx WHERE A.idx = :taxi_MemIdx";
		$memChkStmt = $DB_con->prepare($memChkQuery);
		$memChkStmt->bindparam(":taxi_MemIdx", $mem_Idx);
		$memChkStmt->execute();
		$memChkNum = $memChkStmt->rowCount();
		if ($memChkNum < 1) { //아닐경우
		} else {
			while ($memChkRow = $memChkStmt->fetch(PDO::FETCH_ASSOC)) {

				$mem_NickNm = $memChkRow['mem_NickNm'];            // 캐릭터프로필 선택 여부( 0: 미선택, 1: 선택)
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
			$result = array("result" => true, "memNickNm" => (string)$mem_NickNm, "memImgFile" => (string)$memImgFile);
		}


		dbClose($DB_con);
		$stmt = null;
		$chkstmt = null;
	}
} else {
	$result = array("result" => false, "errorMsg" => "ERROR #1 : 조회 정보값이 없습니다. 관리자에 문의바랍니다.");
}
echo str_replace('\\/', '/', json_encode($result, JSON_UNESCAPED_UNICODE));
