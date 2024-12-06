<?
$menu = "2";
$smenu = "1";

include "../common/inc/inc_header.php";  //헤더 

$DB_con = db1();

if ($mode == "mod") {
	$titNm = "회원등급관리 수정";
	$query = "";
	//$query = "SELECT memLv, memLv_Name, memIconFile, memMatCnt, memDc FROM TB_MEMBER_LEVEL WHERE idx = :idx" ;
	$query = "
			SELECT 
				memLv,
				memLv_Name, 
				memLv_Nick, 
				memLv_MatName, 
				memIconFile, 
				memIconInfoFile, 
				memLv_Color, 
				memMatCnt, 
				memDc
			FROM 
				TB_MEMBER_LEVEL
			WHERE 
				idx = :idx";
	$stmt = $DB_con->prepare($query);
	$stmt->bindparam(":idx", $idx);
	//$idx = trim($idx);
	$stmt->execute();

	$row = $stmt->fetch(PDO::FETCH_ASSOC);

	$memLv =  trim($row['memLv']);
	$memLv_Name = trim($row['memLv_Name']);
	$memLv_Nick = trim($row['memLv_Nick']);
	$memLv_MatName = trim($row['memLv_MatName']);
	$memIconFile = trim($row['memIconFile']);
	$memIconInfoFile = trim($row['memIconInfoFile']);
	$memLv_Color = trim($row['memLv_Color']);
	$memMatCnt = trim($row['memMatCnt']);
	$memDc = trim($row['memDc']);
} else {
	$mode = "reg";
	$titNm = "회원등급관리 등록";
	$memLv_Name = "";
	$memIconFile = "";
	$memIconInfoFile = "";
	$memLv_Nick = "";
	$memLv_MatName = "";
	$memMatCnt = "";
	$memDc = "";
	$findType = "";
	$findword = "";
	$memLv_Color = "";
}



$qstr = "findType=" . urlencode($findType) . "&amp;findword=" . urlencode($findword);

include "../common/inc/inc_gnb.php";  //헤더 
include "../common/inc/inc_menu.php";  //메뉴 

?>
<script type="text/javascript">
	function sel_Color(color) {
		$('#code_sel_Color').val(color);
		$('#sel_Color').css("background-color", color);
	}
</script>

<style type="text/css">
	#memLv_Color ul {
		list-style-type: none;
		margin: 0;
		padding: 0;
		width: 200px;
		background-color: #f1f1f1;
	}

	#memLv_Color li ul {
		list-style-type: none;
		margin: 0;
		padding: 0;
		width: 200px;
		display: none;
		z-index: 200;
		left: 0px;
		top: 38px;
	}

	#memLv_Color li:hover ul {
		display: block;
	}

	#memLv_Color li a {
		display: block;
		color: #000;
		padding: 8px 16px;
		text-decoration: none;
		text-align: center;
	}

	#memLv_Color li a.active {
		background-color: #4CAF50;
		color: white;
	}

	#memLv_Color li:hover:not(.active) {
		display: block;
		color: white;
	}

	#memLv_Color li a:hover:not(.active) {
		/*background-color: #555;*/
		color: white;
	}
</style>
<div id="wrapper">

	<div id="container" class="">
		<h1 id="container_title"><?= $titNm ?></h1>
		<div class="container_wr">
			<form name="fmember" id="fmember" action="memManagerProc.php" onsubmit="return fubmit(this);" method="post" enctype="multipart/form-data" autocomplete="off">
				<input type="hidden" name="mode" id="mode" value="<?= $mode ?>">
				<input type="hidden" name="idx" id="idx" value="<?= $idx ?>">
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
								<th scope="row"><label for="memLvName">회원등급명<strong class="sound_only">필수</strong></label></th>
								<td>
									<input type="text" name="memLvName" value="<?= $memLv_Name ?>" id="memLvName" required class="frm_input required" size="50" maxlength="50">
								</td>
								<?
								$select_array = array(
									'5' => '5레벨',
									'6' => '6레벨',
									'7' => '7레벨',
									'8' => '8레벨',
									'9' => '9레벨',
									'10' => '10레벨',
									'11' => '11레벨',
									'12' => '12레벨',
									'13' => '13레벨',
									'14' => '14레벨'
								);
								?>

								<th scope="row"><label for="memLv">레벨선택<strong class="sound_only">필수</strong></label></th>
								<td>
									<select id="memLv" name="memLv" class="selectBox" required class="frm_input required">
										<option value="">레벨선택</option>
										<? foreach ($select_array as $k => $v) : ?>
											<option value="<?= $k; ?>" <? if ($mode == "mod") { ?><? if ($k == $memLv) { ?>selected="selected" <? }
																																		} ?>><? echo $v ?></option>
										<? endforeach; ?>
									</select>
								</td>
							</tr>
							<tr>
								<th scope="row"><label for="memLvNick">등급명<strong class="sound_only">필수</strong></label></th>
								<td colspan="3">
									<input type="text" name="memLvNick" value="<?= $memLv_Nick ?>" id="memLvNick" required class="frm_input required" size="30">
								</td>
							</tr>
							<tr>
								<th scope="row"><label for="memIconFile">나의등급이미지(그림자포함)</label></th>
								<td colspan="3">
									<!-- <span class="frm_info">이미지 크기는 <strong>넓이 132픽셀 높이 132픽셀</strong>로 해주세요.</span> -->
									<input type="file" name="memIconFile" id="memIconFile" <? if ($memIconFile == "") { ?>required class="frm_input required" <? } ?>>
									<?
									//BLOB 파일 형태로 저장된 이미지 파일 출력되도록 ------------------- 2019.02.18
									if ($memIconFile) {
									?>
										<img src="/data/levIcon/photo.php?id=<? echo $memIconFile ?>" height="60">
										<input type="checkbox" id="del_memIconFile" name="del_memIconFile" value="1">삭제
									<?
									}

									?>

									<? if ($mode == "mod") { ?>
										<input type="hidden" name="memIconFile" value="<?= $memIconFile ?>">
									<? } ?>
								</td>
							</tr>
							<tr>
								<th scope="row"><label for="memIconInfoFile">햄버거메뉴등급이미지</label></th>
								<td colspan="3">
									<input type="file" name="memIconInfoFile" id="memIconInfoFile" <? if ($memIconInfoFile == "") { ?>required class="frm_input required" <? } ?>>
									<?
									//BLOB 파일 형태로 저장된 이미지 파일 출력되도록 ------------------- 2019.02.18
									if ($memIconInfoFile) {
									?>
										<img src="/data/levIcon/photo.php?id=<? echo $memIconInfoFile ?>" height="60">
										<input type="checkbox" id="del_memIconInfoFile" name="del_memIconInfoFile" value="1">삭제
									<?
									}

									?>

									<? if ($mode == "mod") { ?>
										<input type="hidden" name="memIconInfoFile" value="<?= $memIconInfoFile ?>">
									<? } ?>
								</td>
							</tr>
							<tr>
								<th scope="row"><label for="memLvMatName">매칭횟수조건명<strong class="sound_only">필수</strong></label></th>
								<td colspan="3">
									<input type="text" name="memLvMatName" value="<?= $memLv_MatName ?>" id="memLvMatName" required class="frm_input required" size="100">
								</td>
							</tr>
							<tr id="memLv_Color">
								<th scope="row"><label for="memLv_Color">코드배경색상</label></th>
								<td id="memLv_Color">
									<div>
										<div id="memLv_Color" style="float:left;">
											<ul>
												<li style="padding: 8px 16px; text-align:center;position: relative;" class="active"><span>색상선택</span>
													<ul style="position: absolute;" id="list_Color">
														<li style="background-color:#44a9ef;"><a href="javascript:;" onclick="sel_Color('#44a9ef');"><span style="color:#fff;">#44a9ef</span></a></li>
														<li style="background-color:#ffa858;"><a href="javascript:;" onclick="sel_Color('#ffa858');"><span style="color:#fff;">#ffa858</span></a></li>
														<li style="background-color:#726262;"><a href="javascript:;" onclick="sel_Color('#726262');"><span style="color:#fff;">#726262</span></a></li>
														<li style="background-color:#e37e5d;"><a href="javascript:;" onclick="sel_Color('#e37e5d');"><span style="color:#fff;">#e37e5d</span></a></li>
													</ul>
												</li>
											</ul>
										</div>
										<div style="float:left;">
											<ul>
												<li style="background-color:<?= ($memLv_Color == "" ? "#a9aab5" : $memLv_Color) ?>;padding: 8px 16px; text-align:center;height:100%;" class="active" id="sel_Color"><?= ($memLv_Color == "" ? "<span style='color:#000;'>색을 선택해주세요.</span></li>" : "<span style='color:#fff;'>선택한 색상</span></li>") ?>
											</ul>
										</div>
									</div>
									<input type="hidden" id="codeselColor" name="codeselColor" value="" />
									<input type="hidden" id="memLvColor" name="memLvColor" value="<?= $memLv_Color ?>" />
									<br><br><span>지점아이콘 인 경우 배경색상을 지정해주세요.</span>
								</td>
							</tr>
							<tr>
								<th scope="row"><label for="memMatCnt">매칭횟수조건<strong class="sound_only">필수</strong></label></th>
								<td>
									<input type="text" name="memMatCnt" value="<?= $memMatCnt ?>" id="memMatCnt" required class="frm_input required" size="20" maxlength="20"> 회 이상
								</td>
								<th scope="row"><label for="memDc">수수료<strong class="sound_only">필수</strong></label></th>
								<td>
									<input type="text" name="memDc" value="<?= $memDc ?>" id="memDc" required class="frm_input required" size="20" maxlength="20"> %
								</td>
							</tr>
						</tbody>
					</table>
				</div>

				<div class="btn_fixed_top">
					<a href="memManagerList.php?<?= $qstr ?>&page=<?= $page ?>" class="btn btn_02">목록</a>
					<input type="submit" value="확인" class="btn_submit btn" accesskey='s'>
				</div>
			</form>


			<script>
				function fubmit(f) {
					if (!f.mb_img.value.match(/\.(gif|jpe?g|png)$/i) && f.mb_img.value) {
						alert('회원등급이미지는 이미지 파일만 가능합니다.');
						return false;
					}

					return true;
				}
			</script>

		</div>


		<?
		dbClose($DB_con);
		$stmt = null;
		$mstmt = null;

		include "../common/inc/inc_footer.php";  //푸터 

		?>