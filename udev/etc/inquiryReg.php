<?
$menu = "3";
$smenu = "5";

include "../common/inc/inc_header.php";  //헤더 

$DB_con = db1();

$cateQuery = "SELECT b_CateChk, b_CateName FROM TB_BOARD_SET WHERE b_Idx = 2 ORDER BY idx DESC";
$cateStmt = $DB_con->prepare($cateQuery);
$cateStmt->execute();
$cateNum = $cateStmt->rowCount();

if ($cateNum < 1) { //아닐경우
} else {
	$category = [];
	while ($cateRow = $cateStmt->fetch(PDO::FETCH_ASSOC)) {

		$b_CateChk = $cateRow['b_CateChk'];
		if ($b_CateChk == 'N') {
		} else {
			$b_CateName = $cateRow['b_CateName'];
			$chk = explode("&", $b_CateName);
			for ($i = 0; $i < count($chk); $i++) {
				$cateNo = $i + 1;
				$cate = array("cateNo" => (int)$cateNo, "cateName" => (string)$chk[$i]);
				array_push($category, $cate);
			}
		}
	}
}

if ($mode == "mod") {
	$titNm = "문의리스트 답변등록";

	$query = "SELECT idx, b_Part, b_SIdx, b_RIdx, b_Cate, b_MemIdx, b_MemId, b_Title, b_Name, b_Content, b_RMemIdx, b_RMemId, b_RName, b_RContent FROM TB_ONLINE WHERE idx = :idx;";
	$stmt = $DB_con->prepare($query);
	$stmt->bindparam(":idx", $idx);
	$stmt->execute();
	$row = $stmt->fetch(PDO::FETCH_ASSOC);
	$idx = trim($row['idx']);
	$b_Part =  trim($row['b_Part']);
	$b_SIdx =  trim($row['b_SIdx']);
	$b_RIdx =  trim($row['b_RIdx']);
	$b_MemIdx   =  trim($row['b_MemIdx']);
	$b_MemId   =  trim($row['b_MemId']);
	$b_Title = trim($row['b_Title']);
	$b_Name = trim($row['b_Name']);
	$b_Content = str_replace("?", "", trim($row['b_Content']));			// 문의내용
	$b_RMemIdx = trim($row['b_RMemIdx']);
	$b_RMemId = trim($row['b_RMemId']);
	$b_RName = trim($row['b_RName']);
	$b_RContent = trim($row['b_RContent']);
	$b_Cate = trim($row['b_Cate']);
	$bCate = $category[$b_Cate]['cateName'];
	if ($b_Part == 1) {
		$bPart = "매칭생성";
	} else if ($b_Part == 2) {
		$bPart = "매칭신청";
	} else if ($b_Part == 3) {
		$bPart = "문의유형";
	}

	dbClose($DB_con);
	$stmt = null;
} else {
	$DB_con = db1();

	$query = "";
	$query = "SELECT idx, b_Part, b_SIdx, b_RIdx, b_Cate, b_MemIdx, b_MemId, b_Title, b_Name, b_Content, b_RMemIdx, b_RMemId, b_RName, b_RContent FROM TB_ONLINE WHERE idx = :idx;";
	$stmt = $DB_con->prepare($query);
	$stmt->bindparam(":idx", $idx);
	$stmt->execute();
	$row = $stmt->fetch(PDO::FETCH_ASSOC);
	$idx = trim($row['idx']);
	$b_Part =  trim($row['b_Part']);
	$b_SIdx =  trim($row['b_SIdx']);
	$b_RIdx =  trim($row['b_RIdx']);
	$b_MemIdx   =  trim($row['b_MemIdx']);
	$b_MemId   =  trim($row['b_MemId']);
	$b_Title = trim($row['b_Title']);
	$b_Name = trim($row['b_Name']);
	$b_Content = str_replace("?", "", trim($row['b_Content']));			// 문의내용
	$b_RMemIdx = trim($row['b_RMemIdx']);
	$b_RMemId = trim($row['b_RMemId']);
	$b_RName = trim($row['b_RName']);
	$b_RContent = trim($row['b_RContent']);
	$b_Cate = trim($row['b_Cate']);
	$bCate = $category[$b_Cate]['cateName'];
	if ($b_Part == 1) {
		$bPart = "매칭생성";
	} else if ($b_Part == 2) {
		$bPart = "매칭신청";
	} else if ($b_Part == 3) {
		$bPart = "문의유형";
	}

	dbClose($DB_con);
	$stmt = null;
	$mode = "reg";
	$titNm = "문의리스트 답변등록";
}

$qstr = "findType=" . urlencode($findType) . "&amp;findword=" . urlencode($findword);

include "../common/inc/inc_gnb.php";  //헤더 
include "../common/inc/inc_menu.php";  //메뉴 

?>

<div id="wrapper">

	<div id="container" class="">
		<h1 id="container_title"><?= $titNm ?></h1>
		<div class="container_wr">
			<form name="fmember" id="fmember" action="inquiryProc.php" onsubmit="return fubmit(this);" method="post" enctype="multipart/form-data" autocomplete="off">
				<input type="hidden" name="mode" id="mode" value="<?= $mode ?>">
				<input type="hidden" name="idx" id="idx" value="<?= $idx ?>">
				<input type="hidden" name="qstr" id="qstr" value="<?= $qstr ?>">
				<input type="hidden" name="page" id="page" value="<?= $page ?>">

				<div class="tbl_frm01 tbl_wrap">
					<table>
						<caption>문의리스트 답변등록</caption>
						<colgroup>
							<col class="grid_4">
							<col>
						</colgroup>
						<tbody>
							<tr>
								<th scope="row"><label for="b_Part">문의분류</label></th>
								<td>
									<input type="text" name="b_Part" value="<?= $bPart ?>" id="b_Part" readonly disabled class="frm_input">
								</td>
								<? if ($b_Part == 3) { ?>
									<th scope="row"><label for="b_NIdx">문의유형</label></th>
									<td>
										<input type="text" name="b_Cate" value="<?= $bCate ?>" id="b_Cate" readonly disabled class="frm_input">

									</td>
								<? } else if ($b_Part == 2) { ?>
									<th scope="row"><label for="b_RIdx">노선번호</label></th>
									<td>
										<a href="/udev/taxiSharing/taxiSharingReg.php?mode=mod&idx=<?= $b_RIdx ?>"><input type="text" name="b_RIdx" value="<?= $b_RIdx ?>" id="b_RIdx" readonly disabled class="frm_input"></a>
									</td>
								<? } else if ($b_Part == 1) { ?>
									<th scope="row"><label for="b_SIdx">노선번호</label></th>
									<td>
										<a href="/udev/taxiSharing/taxiSharingReg.php?mode=mod&idx=<?= $b_SIdx ?>"><input type="text" name="b_SIdx" value="<?= $b_SIdx ?>" id="b_SIdx" readonly disabled class="frm_input"></a>
									</td>
								<? } ?>
							</tr>
							<tr>
								<th scope="row"><label for="b_MemId">문의자ID(이름)</label></th>
								<td>
									<a href="/udev/member/memberReg.php?mode=mod&idx=<?= $b_MemIdx ?>"><input type="text" name="b_MemId" value="<?= $b_MemId . " (" . $b_Name . ")" ?>" id="b_MemId" readonly disabled class="frm_input" size="50"></a>
								</td>
								<th scope="row"><label for="b_MemId">문의번호</label></th>
								<td>
									<input type="text" name="b_MemId" value="<?= $idx ?>" id="b_MemId" readonly disabled class="frm_input">
								</td>
							</tr>

							<tr>
								<th scope="row"><label for="b_Title">제목</label></th>
								<td colspan='3'>
									<input type="text" name="b_Title" value="<?= $b_Title ?>" id="b_Title" readonly disabled class="frm_input" size="50">
								</td>
							</tr>

							<tr>
								<th scope="row"><label for="b_Content">문의내용</label></th>
								<td colspan='3'>
									<textarea name="b_Content" id="b_Content" cols="20" rows="8" readonly disabled><?= $b_Content ?></textarea>
								</td>
							</tr>

							<tr>
								<th scope="row"><label for="b_RContent">답변내용</label></th>
								<td colspan='3'>
									<textarea name="b_RContent" id="b_RContent" cols="20" rows="8"><?= $b_RContent ?></textarea>
								</td>
							</tr>
						</tbody>
					</table>
				</div>

				<div class="btn_fixed_top">
					<a href="inquiryList.php?<?= $qstr ?>&page=<?= $page ?>" class="btn btn_02">목록</a>
					<input type="submit" value="확인" class="btn_submit btn" accesskey='s'>
				</div>
			</form>


			<script>
				function fubmit(f) {

					if ($.trim($('#b_RContent').val()) == '') {
						message = "답변을 입력해 주세요!";
						alert(message);
						chk = "#b_RContent";
						$(chk).focus();
						return false;
					}

					return true;

				}
			</script>

		</div>

		<? include "../common/inc/inc_footer.php";  //푸터 
		?>