<?
$menu = "3";
$smenu = "3";

include "../common/inc/inc_header.php";  //헤더 


if ($mode == "mod") {
	$titNm = "배너 관리 수정";

	$DB_con = db1();

	$query = "";
	$query = "SELECT ban_Title, ban_ImgFile, ban_Url, b_Disply FROM TB_BANNER WHERE idx = :idx";
	$stmt = $DB_con->prepare($query);
	$stmt->bindparam(":idx", $idx);
	$stmt->execute();

	$row = $stmt->fetch(PDO::FETCH_ASSOC);
	$ban_Title =  trim($row['ban_Title']);
	$ban_ImgFile = trim($row['ban_ImgFile']);
	$ban_Url =  trim($row['ban_Url']);
	$b_Disply = trim($row['b_Disply']);

	dbClose($DB_con);
	$stmt = null;
} else {
	$mode = "reg";
	$titNm = "배너 관리 등록";
}

$qstr = "findType=" . urlencode($findType) . "&amp;findword=" . urlencode($findword);

include "../common/inc/inc_gnb.php";  //헤더 
include "../common/inc/inc_menu.php";  //메뉴 

?>

<div id="wrapper">

	<div id="container" class="">
		<h1 id="container_title"><?= $titNm ?></h1>
		<div class="container_wr">
			<form name="fmember" id="fmember" action="eventBannerProc.php" onsubmit="return fubmit(this);" method="post" enctype="multipart/form-data" autocomplete="off">
				<input type="hidden" name="mode" id="mode" value="<?= $mode ?>">
				<input type="hidden" name="idx" id="idx" value="<?= $idx ?>">
				<input type="hidden" name="qstr" id="qstr" value="<?= $qstr ?>">
				<input type="hidden" name="page" id="page" value="<?= $page ?>">

				<div class="tbl_frm01 tbl_wrap">
					<table>
						<caption>배너관리</caption>
						<colgroup>
							<col class="grid_4">
							<col>
						</colgroup>
						<tbody>

							<tr>
								<th scope="row"><label for="banTitle">제목</label></th>
								<td>
									<? if ($idx != 1) { ?>
										<input type="text" name="banTitle" value="<?= $ban_Title ?>" id="banTitle" required class="required frm_input" size="50">
									<? } else {
										echo $ban_Title;
									} ?>
								</td>
							</tr>
							<tr>
								<th scope="row"><label for="ban_Img">이미지</label></th>
								<td>
									<? if ($idx != 1) { ?>
										<span class="frm_info">이미지 크기는 <strong>넓이 800픽셀 높이 235픽셀</strong>로 해주세요.</span>
										<input type="file" name="ban_Img" id="ban_Img">
									<? } ?>
									<?
									//BLOB 파일 형태로 저장된 이미지 파일 출력되도록 ------------------- 2019.02.15
									if ($ban_ImgFile) {
									?>
										<img src="/data/banner/photo.php?id=<? echo $ban_ImgFile ?>" style="height:60px">
										<? if ($idx != 1) { ?>
											<input type="checkbox" id="del_cou_Img" name="del_cou_Img" value="1">삭제
										<? } ?>
									<?
									}

									?>

									<? if ($mode == "mod") { ?>
										<input type="hidden" name="ban_ImgFile" value="<?= $ban_ImgFile ?>">
									<? } ?>
								</td>
							</tr>

							<? if ($idx != 1) { ?>
								<tr>
									<th scope="row"><label for="ban_Url">URL</label></th>
									<td colspan="3">

										<input type="text" name="ban_Url" value="<?= $ban_Url ?>" id="ban_Url" required class="required frm_input" class="frm_input" size="150">
										<span class="frm_info"> <strong> url 주소를 입력해주세요. ex) https://www.naver.com/</strong></span>
									</td>
								</tr>
								<tr>
									<th scope="row"><label for="b_Disply">사용여부</label></th>
									<td colspan="3">
										<input type="radio" name="b_Disply" value="Y" id="b_Disply" <?= ($b_Disply == "Y") ? "checked" : ""; ?> />
										<label for="b_Disply">사용</label>
										<input type="radio" name="b_Disply" value="N" id="b_Disply" <?= ($b_Disply == "N") ? "checked" : ""; ?> />
										<label for="b_Disply">사용안함</label>
									</td>
								</tr>
							<? } ?>


						</tbody>
					</table>
				</div>

				<div class="btn_fixed_top">
					<a href="eventBannerList.php?<?= $qstr ?>&page=<?= $page ?>" class="btn btn_02">목록</a>
					<? if ($_COOKIE['du_udev']['id'] != 'admin2') { ?>
						<input type="submit" value="확인" class="btn_submit btn" accesskey='s'>
					<? } ?>
				</div>
			</form>


			<script>
				function fubmit(f) {

					var ban_Content_data = oEditors.getById['ban_Content'].getIR();
					oEditors.getById["ban_Content"].exec("UPDATE_CONTENTS_FIELD", []);
					if (jQuery.inArray(document.getElementById('ban_Content').value.toLowerCase().replace(/^\s*|\s*$/g, ''), ['&nbsp;', '<p>&nbsp;</p>', '<p><br></p>', '<div><br></div>', '<p></p>', '<br>', '']) != -1) {
						document.getElementById('ban_Content').value = '';
					}

					if ($.trim($(':radio[name="b_Disply"]:checked').val()) == '') {
						message = "사용여부를 선택해 주세요!";
						alert(message);
						chk = "#b_Disply";
						$(chk).focus();
						return false;
					}

					return true;

				}
			</script>

		</div>

		<? include "../common/inc/inc_footer.php";  //푸터 
		?>