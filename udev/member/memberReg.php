<?
$menu = "2";
$smenu = "2";

include "../common/inc/inc_header.php";  //헤더

$DB_con = db1();

if ($mode == "mod") {
	$titNm = "회원 수정";

	$query = "
			SELECT 
				member.idx, 
				member.mem_Id, 
				member.mem_Pwd, 
				member.mem_Lv, 
				member.mem_NickNm, 
				member.mem_Tel, 
				member.mem_Birth,
				member.mem_Code, 
				member.mem_CharBit, 
				member.mem_CharIdx, 
				member.b_Disply,
				member_photo.mem_profile,
				member_photo.mem_profile_update,
				info.mem_Memo
			FROM 
				TB_MEMBERS as member 
				INNER JOIN TB_MEMBERS_INFO AS info ON member.idx = info.mem_Idx
				left outer join TB_MEMBER_PHOTO as member_photo on member.idx = member_photo.mem_Idx
			WHERE member.idx = :idx AND member.b_Disply = 'N' ";
	//echo $query."<BR>";
	//exit;

	$stmt = $DB_con->prepare($query);
	$stmt->bindparam(":idx", $idx);
	$stmt->execute();
	$num = $stmt->rowCount();

	if ($num < 1) { //아닐경우
	} else {

		while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {

			$mem_Idx = trim($row['idx']);
			$mem_Id =  trim($row['mem_Id']);
			$mem_Pwd = $row['mem_Pwd'];
			$mem_Lv = $row['mem_Lv'];
			$mem_NickNm = trim($row['mem_NickNm']);
			$mem_Tel = trim($row['mem_Tel']);
			$mem_Birth = trim($row['mem_Birth']);
			$mem_Code =  trim($row['mem_Code']);
			$mem_Memo =  trim($row['mem_Memo']);

			//blob 첨부파일 확인
			$mem_profile = $row['mem_profile'];
			$mem_CharBit = $row['mem_CharBit'];
			$mem_CharIdx = $row['mem_CharIdx'];
			$mem_ImgFile = $row['mem_profile_update'];

			if ($mem_CharBit == "1") {
				$profileQuery = "SELECT con_ProfileNo, con_ProfileImg FROM TB_CONFIG_PROFILE WHERE con_ProfileBit = 'Y' AND con_ProfileNo = :memCharIdx ORDER BY con_ProfileSort ASC";
				$profileStmt = $DB_con->prepare($profileQuery);
				$profileStmt->bindparam(":memCharIdx", $mem_CharIdx);
				$profileStmt->execute();
				$profileRow = $profileStmt->fetch(PDO::FETCH_ASSOC);
				$profile_Img = $profileRow['con_ProfileImg'];	// 캐릭터이미지명
			
				$imgUrl = "/data/config/profile/";				// 캐릭터이미지경로
				$profileImg = $imgUrl . $profile_Img;
			
				$mem_Img = $profileImg;
			} else {
				if ($mem_ImgFile == '') {
					$mem_Img = '';
				} else {
					$mem_Img = '/data/member/photo.php?id=' . $mem_ImgFile;		// 프로필사진이미지경로
				}
			}

			/*
    		$mem_Sex = trim($row['mem_Sex']);
    		$mem_SnsChk = $row[mem_SnsChk];
    
    		$mem_Seat = $row[mem_Seat];
    
    		$mem_Haddr = $row[mem_Haddr];
    		$mem_Oaddr = $row[mem_Oaddr];
    */
			$b_Disply = $row['b_Disply'];

			//회원 정보
			$mInfoQuery = "";
			$mInfoQuery = "SELECT mem_Sex, mem_Seat, mem_Email, mem_SnsChk from TB_MEMBERS_INFO WHERE mem_Idx = :mem_Idx LIMIT 1";
			$meInfoStmt = $DB_con->prepare($mInfoQuery);
			$meInfoStmt->bindparam(":mem_Idx", $mem_Idx);
			$meInfoStmt->execute();
			$infoNum = $meInfoStmt->rowCount();
			//echo $infoNum."<BR>";

			if ($infoNum < 1) { //아닐경우
			} else {
				while ($ifnoRow = $meInfoStmt->fetch(PDO::FETCH_ASSOC)) {
					$mem_Sex = trim($ifnoRow['mem_Sex']);							// 성별 (0:남자 , 1:여자)
					$mem_Seat = trim($ifnoRow['mem_Seat']);						// 좌석 (0:앞자리 , 1:뒷자리)
					$mem_Email = trim($ifnoRow['mem_Email']);				   // 이메일주소
					$mem_SnsChk = trim($ifnoRow['mem_SnsChk']);				   // sns가입여부

					if ($mem_Email == '') {
						$memEmail = '-';
					} else {
						$memEmail = $mem_Email;
					}

					if ($mem_SnsChk == 'Kakao' || $mem_SnsChk == 'kakao') {
						$memSnsChk = '카카오톡';
					} else if ($mem_SnsChk == 'google') {
						$memSnsChk = '구글';
					} else {
						$memSnsChk = '-';
					}
				}
			}

			//회원 기타 정보
			$mEtcQuery = "";
			$mEtcQuery = "SELECT mem_Point, mem_MatCnt, mem_McCnt FROM TB_MEMBERS_ETC WHERE mem_Idx = :mem_Idx LIMIT 1";
			$mEtcStmt = $DB_con->prepare($mEtcQuery);
			$mEtcStmt->bindparam(":mem_Idx", $mem_Idx);
			$mEtcStmt->execute();
			$etcNum = $mEtcStmt->rowCount();
			//echo $etcNum."<BR>";
			//exit;

			if ($etcNum < 1) { //아닐경우
			} else {
				while ($etcRow = $mEtcStmt->fetch(PDO::FETCH_ASSOC)) {
					$mem_Point = trim($etcRow['mem_Point']);			//포인트
					$mem_MatCnt = trim($etcRow['mem_MatCnt']);			//매칭성공횟수
					$mem_McCnt = trim($etcRow['mem_McCnt']);			//매칭취소횟수

					if ($mem_Point  == "") {
						$memPoint 	= 0;
					} else {
						$memPoint 	= $mem_Point;
					}

					if ($mem_MatCnt  == "") {
						$memMatCnt 	= 0;
					} else {
						$memMatCnt 	= $mem_MatCnt;
					}

					if ($mem_McCnt  == "") {
						$memMcCnt 	= 0;
					} else {
						$memMcCnt 	= $mem_McCnt;
					}
				}
			}


			//회원 주소 정보
			$mMapQuery = "";
			$mMapQuery = "SELECT mem_AddrNickNm, mem_AddrNm, mem_Addr, mem_Dong from TB_MEMBERS_MAP  WHERE mem_Idx = :mem_Idx LIMIT 1";
			$mMapStmt = $DB_con->prepare($mMapQuery);
			$mMapStmt->bindparam(":mem_Idx", $mem_Idx);
			$mMapStmt->execute();
			$etcNum = $mMapStmt->rowCount();
			//echo $etcNum."<BR>";
			//exit;

			if ($etcNum < 1) { //아닐경우
			} else {
				while ($mapRow = $mMapStmt->fetch(PDO::FETCH_ASSOC)) {
					$mem_AddrNickNm = trim($mapRow['mem_AddrNickNm']);			//주소 별칭
					$mem_AddrNm = trim($mapRow['mem_AddrNm']);		   			//주소 명
					$mem_Addr = trim($mapRow['mem_Addr']);						//주소
					$mem_Dong = trim($mapRow['mem_Dong']);		    			//주소 동

				}
			}
		}
	}
} else {
	$titNm = "회원 등록";
}

//회원등급
$mquery = "";
$mquery = "SELECT memLv, memLv_Name FROM TB_MEMBER_LEVEL WHERE idx > 2 ORDER BY memLv ASC";
$mstmt = $DB_con->prepare($mquery);
$mstmt->execute();



$qstr = "fr_date=" . urlencode($fr_date) . "&amp;to_date=" . urlencode($to_date) . "&amp;findType=" . urlencode($findType) . "&amp;findword=" . urlencode($findword);

include "../common/inc/inc_gnb.php";  //헤더 
include "../common/inc/inc_menu.php";  //메뉴 

?>

<div id="wrapper">

	<div id="container" class="">
		<h1 id="container_title"><?= $titNm ?></h1>
		<div class="container_wr">
			<form name="fmember" id="fmember" action="memberProc.php" onsubmit="return fmember_submit(this);" method="post" enctype="multipart/form-data" autocomplete="off">
				<input type="hidden" name="mode" id="mode" value="<?= $mode ?>">
				<input type="hidden" name="idx" id="idx" value="<?= $idx ?>">
				<input type="hidden" name="mem_Id" id="mem_Id" value="<?= $mem_Id ?>">
				<input type="hidden" name="qstr" id="qstr" value="<?= $qstr ?>">
				<input type="hidden" name="page" id="page" value="<?= $page ?>">

				<div class="tbl_frm01 tbl_wrap">
					<table>
						<caption><?= $titNm ?></caption>
						<colgroup>
							<col class="grid_4">
							<col>
							<col class="grid_4">
							<col>
						</colgroup>
						<tbody>
							<tr>

								<? if ($mode == "mod") { ?>
									<th scope="row"><label for="id">아이디</label></th>
									<td>
										<?= $mem_Id ?>
										<input type="hidden" name="mem_Id" id="mem_Id" value="<?= $mem_Id ?>">
									</td>
									<th scope="row"><label for="memPwd">비밀번호</label></th>
									<td>
										<input type="password" name="memPwd" id="memPwd" class="frm_input" size="50" maxlength="20">
										<input type="hidden" name="mem_Pwd" id="mem_Pwd" value="<?= $mem_Pwd ?>">

									</td>
								<? } else if ($mode == "reg") { ?>
									<th scope="row"><label for="id">아이디<strong class="sound_only">필수</strong></label></th>
									<td>
										<input type="text" name="id" value="" id="id" required class="frm_input required" size="50" maxlength="20">
									</td>
									<th scope="row"><label for="memPwd">비밀번호<strong class="sound_only">필수</strong></label></th>
									<td><input type="password" name="memPwd" id="memPwd" required class="frm_input required" size="50" maxlength="20"></td>
								<? } ?>
							</tr>
							<tr>
								<th scope="row"><label for="mem_NickNm">닉네임<strong class="sound_only">필수</strong></label></th>
								<td><input type="text" name="mem_NickNm" value="<?= $mem_NickNm ?>" id="mem_NickNm" required class="required frm_input" size="50" maxlength="20"></td>
								<th scope="row"><label for="mem_Lv">회원 권한</label></th>
								<td>
									<input type="hidden" name="oldlev" id="oldlev" value="<?= $mem_Lv ?>">
									<select id="mem_Lv" name="mem_Lv">
										<option value="">회원등급선택</option>
										<?
										$mstmt->setFetchMode(PDO::FETCH_ASSOC);
										while ($v = $mstmt->fetch()) {
										?>
											<option value="<?= $v['memLv']; ?>" <? if ($mode == "mod") { ?><? if ($v['memLv'] == $mem_Lv) { ?>selected="selected" <? }
																																							} ?>><? echo $v['memLv_Name'] ?></option>
										<? } ?>
									</select>
								</td>

							</tr>
							<tr>
								<th scope="row"><label for="mem_Birth">생년월일</label></th>
								<td><input type="text" name="mem_Birth" value="<?= $mem_Birth ?>" id="mem_Birth" class="frm_input" size="50" maxlength="20"></td>
								<th scope="row"><label for="mem_Tel">휴대폰번호<strong class="sound_only">필수</strong></label></th>
								<td><input type="text" name="mem_Tel" value="<?= $mem_Tel ?>" id="mem_Tel" required class="required frm_input" size="50" maxlength="20"></td>
							</tr>

							<tr>
								<th scope="row"><label for="mem_Sex">성별</label></th>
								<td scope="row" colspan="3">
									<input type="radio" name="mem_Sex" value="0" id="mem_Sex" <?= ($mem_Sex == "0" || !$mem_Sex) ? "checked" : ""; ?>>
									<label for="mem_Sex">남자</label>
									<input type="radio" name="mem_Sex" value="1" id="mem_Sex" <?= ($mem_Sex == "1") ? "checked" : ""; ?>>
									<label for="mem_Sex">여자</label>
								</td>
							</tr>
							<tr>
								<th scope="row"><label for="mem_Email">이메일</label></th>
								<td scope="row"><?= $memEmail ?></td>
								<th scope="row"><label for="mem_SnsChk">sns 가입여부</label></th>
								<td scope="row"><?= $memSnsChk ?></td>
							</tr>
							<tr>
								<th scope="row"><label for="mem_Point">사용가능금액 <br>포인트</label></th>
								<td scope="row" colspan="3"><?= number_format((int)$memPoint) . "원" ?></td>
							</tr>
							<tr>
								<th scope="row"><label for="mem_MatCnt">매칭성공횟수</label></th>
								<td scope="row"><?= $memMatCnt ?> 회</td>
								<th scope="row"><label for="mem_McCnt">매칭취소횟수</label></th>
								<td scope="row"><?= $memMcCnt ?> 회</td>
							</tr>
							<tr>
								<th scope="row"><label for="mb_img">회원이미지</label></th>
								<td colspan="3">
									<span class="frm_info">이미지 크기는 <strong>넓이 100픽셀 높이 100픽셀</strong>로 해주세요.</span>
									<input type="file" name="mb_img" id="mb_img">
									<?
									//BLOB 파일 형태로 저장된 이미지 파일 출력되도록 ------------------- 2019.02.15

									if ($mem_Img) {
									?>
										<img src="<?=$mem_Img?>" width="150px" height="150px">
										<input type="checkbox" id="del_mb_img1" name="del_mb_img1" value="1">삭제
									<?
									}

									?>

									<? if ($mode == "mod") { ?>
										<input type="hidden" name="mem_ImgFile" value="<?= $mem_Img ?>">
									<? } ?>
								</td>
							</tr>
							<tr>
								<th scope="row"><label for="mem_Memo">메모</label></th>
								<td colspan="3"><textarea name="mem_Memo" id="mem_Memo"><?= stripslashes($mem_Memo); ?></textarea></td>
							</tr>
						</tbody>
					</table>
				</div>

				<div class="btn_fixed_top">
					<a href="memberList.php?<?= $qstr ?>&page=<?= $page ?>" class="btn btn_02">목록</a>
					<? if ($_COOKIE['du_udev']['id'] != 'admin2') { ?>
						<input type="submit" value="확인" class="btn_submit btn" accesskey='s'>
					<? } ?>
				</div>
			</form>


			<script>
				function fmember_submit(f) {
					if (!f.mb_img.value.match(/\.(gif|jpe?g|png|webp)$/i) && f.mb_img.value) {
						alert('회원이미지는 이미지 파일만 가능합니다.');
						return false;
					}

					return true;
				}
			</script>

		</div>

		<?
		dbClose($DB_con);
		$stmt = null;
		$meInfoStmt = null;
		$mEtcStmt = null;
		$mstmt = null;

		include "../common/inc/inc_footer.php";  //푸터 

		?>