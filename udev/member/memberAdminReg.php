<?
$menu = "2";
$smenu = "6";

include "../common/inc/inc_header.php";  //헤더 

$DB_con = db1();

if($mode == ''){
	$mode = 'reg';
}
if ($mode == "mod") {
	$titNm = "단체정보 수정";

	$query = "
			SELECT 
				member.idx, 
				member.mem_Id, 
				member.mem_Pwd, 
				member.mem_Lv, 
				member.mem_NickNm, 
				member.mem_Tel 
			FROM 
				TB_MEMBERS as member 
			WHERE member.mem_id = :id";
	//echo $query."<BR>";
	//exit;

	$stmt = $DB_con->prepare($query);
	$stmt->bindparam(":id", $id);
	$stmt->execute();
	$num = $stmt->rowCount();

	if ($num < 1) { //아닐경우
	} else {

		while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {

			$mem_Idx = trim($row['idx']);
			$mem_Id =  trim($row['mem_Id']);
			$mem_Pwd = trim($row['mem_Pwd']);
			$mem_Lv = trim($row['mem_Lv']);
			$mem_NickNm = trim($row['mem_NickNm']);
			$mem_Tel = trim($row['mem_Tel']);

			//회원 기타 정보
			$mEtcQuery = "SELECT mem_GroupDownUrl FROM TB_MEMBERS_ETC WHERE mem_Idx = :mem_Idx LIMIT 1";
			$mEtcStmt = $DB_con->prepare($mEtcQuery);
			$mEtcStmt->bindparam(":mem_Idx", $mem_Idx);
			$mEtcStmt->execute();
			$etcNum = $mEtcStmt->rowCount();
			//echo $etcNum."<BR>";
			//exit;

			if ($etcNum < 1) { //아닐경우
				$mem_GroupDownUrl = "";			//다운로드주소
			} else {
				while ($etcRow = $mEtcStmt->fetch(PDO::FETCH_ASSOC)) {
					$mem_GroupDownUrl = trim($etcRow['mem_GroupDownUrl']);			//다운로드주소
				}
			}
			//회원 정보
			$mInfoQuery = "SELECT mem_Memo FROM TB_MEMBERS_INFO WHERE mem_Idx = :mem_Idx LIMIT 1";
			$mInfoStmt = $DB_con->prepare($mInfoQuery);
			$mInfoStmt->bindparam(":mem_Idx", $mem_Idx);
			$mInfoStmt->execute();
			$infoNum = $mInfoStmt->rowCount();
			//echo $etcNum."<BR>";
			//exit;

			if ($infoNum < 1) { //아닐경우
			} else {
				while ($infoRow = $mInfoStmt->fetch(PDO::FETCH_ASSOC)) {
					$mem_Memo = trim($infoRow['mem_Memo']);			//다운로드주소
				}
			}
		}
	}
} else {
	$titNm = "단체정보 등록";
}

include "../common/inc/inc_gnb.php";  //헤더 
include "../common/inc/inc_menu.php";  //메뉴 

?>

<div id="wrapper">

	<div id="container" class="">
		<h1 id="container_title"><?= $titNm ?></h1>
		<div class="container_wr">
			<form name="fmember" id="fmember" action="memberAdminProc.php" onsubmit="return fmember_submit(this);" method="post" enctype="multipart/form-data" autocomplete="off">
				<input type="hidden" name="mode" id="mode" value="<?= $mode ?>">
				<input type="hidden" name="memIdx" id="memIdx" value="<?= $mem_Idx ?>">

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
									<th scope="row"><label for="id">아이디(고유번호)</label></th>
									<td>
										<?= $mem_Id ?> (<?= $idx ?>)
										<input type="hidden" name="memId" id="memId" value="<?= $mem_Id ?>">
									</td>
									<th scope="row"><label for="memPwd">비밀번호</label></th>
									<td>
										<input type="password" name="memPwd" id="memPwd" class="frm_input" size="50" maxlength="20">
										<input type="hidden" name="mem_Pwd" id="mem_Pwd" value="<?= $mem_Pwd ?>">

									</td>
								<? } else if ($mode == "reg") { ?>
									<th scope="row"><label for="id">아이디<strong class="sound_only">필수</strong></label></th>
									<td>
										<input type="text" name="memId" value="" id="memId" required class="frm_input required" size="50" maxlength="20">
									</td>
									<th scope="row"><label for="memPwd">비밀번호<strong class="sound_only">필수</strong></label></th>
									<td><input type="password" name="memPwd" id="memPwd" required class="frm_input required" size="50" maxlength="20"></td>
								<? } ?>
							</tr>
							<tr>
								<? if ($mode == "mod") { ?>
									<th scope="row"><label for="memNickNm">닉네임<strong class="sound_only">필수</strong></label></th>
									<td><input type="text" name="memNickNm" value="<?= $mem_NickNm ?>" id="memNickNm" required class="required frm_input" size="50" maxlength="20"></td>
									<th scope="row"><label for="memTel">연락처<strong class="sound_only">필수</strong></label></th>
									<td colspan="3"><input type="text" name="memTel" value="<?= $mem_Tel ?>" id="memTel" required class="required frm_input" size="50" maxlength="20"></td>
								<? } else if ($mode == "reg") { ?>
									<th scope="row"><label for="memNickNm">닉네임<strong class="sound_only">필수</strong></label></th>
									<td><input type="text" name="memNickNm" value="" id="memNickNm" required class="required frm_input" size="50" maxlength="20"></td>
									<th scope="row"><label for="memTel">연락처<strong class="sound_only">필수</strong></label></th>
									<td colspan="3"><input type="text" name="memTel" value="" id="memTel" required class="required frm_input" size="50" maxlength="20"></td>
								<? } ?>
							</tr>
							<tr>
								<? if ($mode == "mod") { ?>
									<th scope="row"><label for="memGroupDownUrl">다운로드주소</label></th>
									<td colspan="3"><input type="text" name="memGroupDownUrl" value="<?= $mem_GroupDownUrl ?>" id="memGroupDownUrl" required class="required frm_input" size="100" maxlength="150"> (무조건 모바일에서 실행해야 합니다.)</td>
								<? } else if ($mode == "reg") { ?>
									<th scope="row"><label for="memGroupDownUrl">다운로드주소</label></th>
									<td colspan="3"><input type="text" name="memGroupDownUrl" value="" id="memGroupDownUrl" required class="required frm_input" size="100" maxlength="150"> * 무조건 모바일에서 실행해야 합니다.</td>
								<? } ?>
							</tr>
							<tr>
								<? if ($mode == "mod") { ?>
									<th scope="row"><label for="memMemo">메모</label></th>
									<td colspan="3"><textarea name="memMemo" id="memMemo"><?= stripslashes($mem_Memo); ?></textarea></td>
								<? } else if ($mode == "reg") { ?>
									<th scope="row"><label for="memMemo">메모</label></th>
									<td colspan="3"><textarea name="memMemo" id="memMemo"></textarea></td>
								<? } ?>
							</tr>
						</tbody>
					</table>
				</div>

				<div class="btn_fixed_top">
					<a href="memberAdminList.php?<?= $qstr ?>&page=<?= $page ?>" class="btn btn_02">목록</a>
					<input type="submit" value="확인" class="btn_submit btn" accesskey='s'>
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