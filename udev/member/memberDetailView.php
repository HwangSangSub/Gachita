<?
$menu = "2";
$smenu = "2";

include "../common/inc/inc_header.php";  //헤더 

$base_url = $PHP_SELF;

$DB_con = db1();

// 회원 기본정보
$viewQuery = "SELECT * 
	FROM TB_MEMBERS AS A 
		INNER JOIN TB_MEMBERS_INFO AS B ON A.idx = B.mem_Idx 
		INNER JOIN TB_MEMBERS_ETC AS D ON A.idx = D.mem_Idx 
		LEFT OUTER JOIN TB_MEMBER_PHOTO AS C ON A.idx = C.mem_Idx
	WHERE A.idx = " . $idx . " ";
$viewStmt = $DB_con->prepare($viewQuery);
$viewStmt->execute();
$row = $viewStmt->fetch(PDO::FETCH_ASSOC);
$mem_Id = $row['mem_Id'];							// sns 아이디
$mem_Nm = $row['mem_Nm'];							// 이름
$mem_NickNm = $row['mem_NickNm'];					// 닉네임
$mem_Birth = $row['mem_Birth'];						// 생년월일
$mem_Email = $row['mem_Email'];						// 이메일
$mem_SnsChk = $row['mem_SnsChk'];					// sns 로그인체크 
$mem_Sex = $row['mem_Sex'];							// 성별 0: 남자, 1:여자
$mem_Tel = $row['mem_Tel'];							// 연락처
$mem_Point = $row['mem_Point'];						// 포인트
$mem_MatCnt = $row['mem_MatCnt'];					// 매칭카운트 성공 횟수
$mem_McCnt = $row['mem_McCnt'];						// 매칭카운트 취소 횟수
$mem_Memo = $row['mem_Memo'];						// 관리자메모

$mem_CharBit = $row['mem_CharBit'];					// 캐릭터프로필 선택 여부( 0: 미선택, 1: 선택)
$mem_CharIdx = $row['mem_CharIdx'];					// 캐릭터프로필 고유번호
$mem_ImgFile = $row['mem_profile_update'];			// 이미지파일명

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

$qstr = "fr_date=" . urlencode($fr_date) . "&amp;to_date=" . urlencode($to_date) . "&amp;findType=" . urlencode($findType) . "&amp;findOs=" . urlencode($findOs) . "&amp;findword=" . urlencode($findword);

include "../common/inc/inc_gnb.php";  //헤더 
include "../common/inc/inc_menu.php";  //메뉴 

?>
<script type="text/javascript" src="<?= DU_UDEV_DIR ?>/member/js/member.js"></script>

<div id="wrapper">
	<div id="container" class="">
		<div class="container_wr">
			<h1 id="container_title"><?= $mem_Id ?>(<?= $mem_NickNm ?>)&nbsp;회원상세보기</h1>


			<style>
				.ov_num {
					border-right: 1px solid #fff;
				}

				.ov_txt a {
					color: #fff;
				}
			</style>
			<div class="local_ov01 local_ov">
				<span class="btn_ov01">
					<span class="ov_txt"><a href="memberDetailView.php?idx=<?= $idx ?>">기본정보</a> </span>
					<span class="ov_num"><a href="memberDetailView_point.php?idx=<?= $idx ?>">포인트내역</a></span>
					<span class="ov_num"><a href="memberDetailView_order.php?idx=<?= $idx ?>">주문내역</a></span>
					<span class="ov_num"><a href="memberDetailView_taxiSharingList.php?idx=<?= $idx ?>">매칭내역</a></span>
					<span class="ov_num"><a href="memberDetailView_inquiryList.php?idx=<?= $idx ?>">문의리스트</a></span>
				</span>
			</div>

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
							<th scope="row"><label for="id">아이디</label></th>
							<td>
								<?= $mem_Id ?>
							</td>
							<th scope="row"><label for="memPwd">비밀번호</label></th>
							<td> ***</td>
						</tr>
						<tr>
							<th scope="row"><label for="mem_Nm">이름<strong class="sound_only">필수</strong></label></th>
							<td><?= $mem_Nm ?></td>
							<th scope="row"><label for="mem_NickNm">닉네임<strong class="sound_only">필수</strong></label></th>
							<td><?= $mem_NickNm ?></td>
							<!-- <th scope="row"><label for="mem_Lv">회원 권한</label></th>
				<td>
					<input type="hidden" name="oldlev"  id="oldlev" value="<?= $mem_Lv ?>">
					<select id="mem_Lv" name="mem_Lv">
						<option value="">회원등급선택</option>
					</select>
				</td> -->

						</tr>
						<tr>
							<th scope="row"><label for="mem_Birth">생년월일</label></th>
							<td><?= $mem_Birth ?></td>
							<th scope="row"><label for="mem_Tel">휴대폰번호<strong class="sound_only">필수</strong></label></th>
							<td><?= $mem_Tel ?></td>
						</tr>

						<tr>
							<th scope="row"><label for="mem_Sex">성별</label></th>
							<td scope="row"><?
											if ($mem_Sex == "1")  echo "여자";
											else  echo "남자";
											?>
							</td>
						</tr>
						<tr>
							<th scope="row"><label for="mem_Email">이메일</label></th>
							<td scope="row"><?= $mem_Email ?></td>
							<th scope="row"><label for="mem_SnsChk">sns 가입여부</label></th>
							<td scope="row"><?= $mem_SnsChk ?></td>
						</tr>

						<tr>
							<th scope="row"><label for="mem_Point">사용가능 포인트</label></th>
							<td scope="row"><?= number_format((int)$mem_Point) ?> ⓟ</td>
						</tr>

						<tr>
							<th scope="row"><label for="mem_MatCnt">매칭성공횟수</label></th>
							<td scope="row"><?= $mem_MatCnt ?> 회</td>
							<th scope="row"><label for="mem_McCnt">매칭취소횟수</label></th>
							<td scope="row"><?= $mem_McCnt ?> 회</td>
						</tr>

						<tr>
							<th scope="row"><label for="mb_img">회원이미지</label></th>
							<td colspan="3">
								<?
								if ($mem_Img) {
								?>
									<img src="<?= $mem_Img ?>" width="150px" height="150px">
								<?
								}
								?>
							</td>
						</tr>
						<tr>
							<th scope="row"><label for="mem_Memo">메모</label></th>
							<td colspan="3"><textarea name="mem_Memo" id="mem_Memo" disabled><?= stripslashes($mem_Memo); ?></textarea></td>
						</tr>
					</tbody>
				</table>
			</div>




		</div>
		<div class="btn_fixed_top">
			<a href="memberList.php" id="bt_m_a_add" class="btn btn_01">회원목록</a>
		</div>

		<?
		dbClose($DB_con);
		$cntStmt = null;
		$stmt = null;
		$mcntStmt = null;
		$mcntStmt2 = null;
		$mcntStmt3 = null;
		$mstmt = null;

		include "../common/inc/inc_footer.php";  //푸터 

		?>