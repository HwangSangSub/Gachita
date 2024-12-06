<?
$menu = "3";
$smenu = "3";

include "../common/inc/inc_header.php";  //헤더 

$base_url = $PHP_SELF;

$sql_search = " WHERE 1";

if ($findword != "") {
	$sql_search .= " AND `{$findType}` LIKE '%{$findword}%' ";
}

$DB_con = db1();

//전체 카운트
$cntQuery = "";
$cntQuery = "SELECT COUNT(idx) AS cntRow FROM TB_BANNER  {$sql_search} ";
$cntStmt = $DB_con->prepare($cntQuery);

if ($findword != "") {
	$cntStmt->bindValue(":findType", $findType);
	$cntStmt->bindValue(":findword", $findword);
}

$findType = trim($findType);
$findword = trim($findword);

$cntStmt->execute();
$row = $cntStmt->fetch(PDO::FETCH_ASSOC);
$totalCnt = $row['cntRow'];

$cntStmt = null;

$rows = 10;
$total_page  = ceil($totalCnt / $rows);  // 전체 페이지 계산
if ($page == "") {
	$page = 1;
} // 페이지가 없으면 첫 페이지 (1 페이지)
$from_record = ($page - 1) * $rows; // 시작 열을 구함


if (!$sort1) {
	$sort1  = "idx";
	$sort2 = "DESC";
}

$sql_order = "order by $sort1 $sort2";

//목록
$query = "";
$query = "SELECT idx, ban_Title, ban_ImgFile, b_Disply FROM TB_BANNER {$sql_search} {$sql_order} limit  {$from_record}, {$rows}";
$stmt = $DB_con->prepare($query);

if ($findword != "") {
	$stmt->bindValue(":findType", $findType);
	$stmt->bindValue(":findword", $findword);
}

$findType = trim($findType);
$findword = trim($findword);

$stmt->execute();
$numCnt = $stmt->rowCount();

$qstr = "findType=" . urlencode($findType) . "&amp;findword=" . urlencode($findword);

include "../common/inc/inc_gnb.php";  //헤더 
include "../common/inc/inc_menu.php";  //메뉴 

?>
<script type="text/javascript" src="<?= DU_UDEV_DIR ?>/etc/js/event.js"></script>

<div id="wrapper">
	<div id="container" class="">
		<div class="container_wr">
			<h1 id="container_title">배너 관리</h1>

			<div class="local_ov01 local_ov">
				<span class="btn_ov01"><span class="ov_txt">총 수 </span><span class="ov_num"><?= number_format($totalCnt); ?>건 </span>
			</div>



			<form class="local_sch03 local_sch" autocomplete="off">

				<div>
					<strong>분류</strong>
					<select name="findType" id="findType">
						<option value="ban_Title" <? if ($findType == "ban_Title") { ?>selected<? } ?>>제목</option>
					</select>
					<label for="findword" class="sound_only">검색어<strong class="sound_only"> 필수</strong></label>
					<input type="text" name="findword" id="findword" value="<?= $findword ?>" size="30" class=" frm_input">

					<input type="submit" value="검색" class="btn_submit">
					<a href="<?= $base_url ?>" class="btn btn_06">새로고침</a>
				</div>
			</form>



			<form name="fmemberlist" id="fmemberlist" method="post" autocomplete="off">

				<div class="tbl_head01 tbl_wrap">
					<table>
						<caption>배너 목록</caption>
						<thead>

							<!-- 아이디, 이름, 등급, 휴대폰번호, 가입일 -->
							<tr>
								<th scope="col" id="mb_list_chk">
									<label for="chkall" class="sound_only">배너 전체</label>
									<input type="checkbox" name="chkall" class="chkc" id="chkAll">
								</th>
								<th scope="col">제목</th>
								<th scope="col">사용여부</th>
								<th scope="col">관리</th>
							</tr>
						</thead>
						<tbody>

							<?

							if ($numCnt > 0) {

								$stmt->setFetchMode(PDO::FETCH_ASSOC);

								while ($row = $stmt->fetch()) {
									// $bg = 'bg'.($stmt->fetch()%2);
									$idx = $row['idx'];
									$ban_Title = $row['ban_Title'];
									if ($row['b_Disply'] == "Y") {
										$b_Disply = "노출";
									} else {
										$b_Disply = "미노출";
									}

							?>
									<tr class="<?= $bg ?>">
										<td headers="mb_list_chk" class="td_chk">
											<? if ($idx != 1) { ?>
												<input type="checkbox" id="chk" class="chk" name="chk" value="<?= $idx ?>">
											<? } ?>
										</td>
										<td><?= $ban_Title ?></td>
										<td><?= $b_Disply ?></td>
										<td headers="mb_list_mng" class="td_mng td_mng_s">
											<a href="eventBannerReg.php?mode=mod&idx=<?= $idx ?>&<?= $qstr ?>&page=<?= $page ?>" class="btn btn_03">수정</a>
											<? if ($idx != 1) { ?>
												<a href="javascript:chkDel('<?= $idx ?>')" class="btn btn_02">삭제</a>
											<? } ?>
										</td>
									</tr>
								<?

								}
								?>
							<? } else { ?>
								<tr>
									<td colspan="7" class="empty_table">자료가 없습니다.</td>
								</tr>
							<? } ?>
						</tbody>
					</table>
				</div>

				<div class="btn_fixed_top">
					<a href="#ALDel" id="bt_m_a_del" class="btn btn_02">선택삭제</a>
					<a href="eventBannerReg.php" id="event_add" class="btn btn_01">배너 추가</a>
				</div>

			</form>
			<nav class="pg_wrap">
				<?= get_apaging($rows, $page, $total_page, "$_SERVER[PHP_SELF]?$qstr"); ?>
			</nav>

			<script>
				function fvisit_submit(act) {
					var f = document.fvisit;
					f.action = act;
					f.submit();
				}
			</script>

		</div>

		<?
		dbClose($DB_con);
		$cntStmt = null;
		$stmt = null;

		include "../common/inc/inc_footer.php";  //푸터 

		?>