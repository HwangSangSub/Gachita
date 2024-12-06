<?
$menu = "2";
$smenu = "6";

include "../common/inc/inc_header.php";  //헤더 

$base_url = $PHP_SELF;

$sql_search = " WHERE mem_Lv ='2' AND b_Disply = 'N' ";
$findType = "";
$findword = "";
$page = "";
$sort1 = "";

if ($findword != "") {
	if ($findType == "mem_NickNm") {
		$sql_search .= " AND mem_NickNm LIKE :findword ";
	} else if ($findType == "mem_Id") {
		$sql_search .= " AND mem_Id LIKE :findword ";
	} else if ($findType == "mem_Tel") {
		$sql_search .= " AND mem_Tel LIKE :findword ";
	}
}

$DB_con = db1();

//전체 카운트
$cntQuery = "";
$cntQuery = "SELECT COUNT(idx) AS cntRow FROM TB_MEMBERS {$sql_search} ";
$cntStmt = $DB_con->prepare($cntQuery);

if ($findword != "") {
	$cntStmt->bindValue(':findword', '%' . trim($findword) . '%');
}

$cntStmt->execute();
$row = $cntStmt->fetch(PDO::FETCH_ASSOC);
$totalCnt = $row['cntRow'];


$rows = 10;
$total_page  = ceil($totalCnt / $rows);  // 전체 페이지 계산
if ($page == "") {
	$page = 1;
} // 페이지가 없으면 첫 페이지 (1 페이지)
$from_record = ($page - 1) * $rows; // 시작 열을 구함


if (!$sort1) {
	$sort1  = "reg_Date";
	$sort2 = "DESC";
}

$sql_order = "ORDER BY $sort1 $sort2";

//목록
$query = "SELECT idx, mem_Id, mem_NickNm, mem_Tel, reg_Date FROM TB_MEMBERS {$sql_search} {$sql_order} LIMIT  {$from_record}, {$rows} ";
//echo $query."<BR>";
//exit;

$stmt = $DB_con->prepare($query);

if ($findword != "") {
	$stmt->bindValue(':findword', '%' . trim($findword) . '%');
}

$stmt->execute();
$numCnt = $stmt->rowCount();

include "../common/inc/inc_gnb.php";  //헤더 
include "../common/inc/inc_menu.php";  //메뉴 

/*
	// mem_NPush : 회원일 경우, 이벤트 공지 알림유무 ( 0 : ON, 1 : OFF )
	// mem_NPush : 관리자일 경우, 중요처리건 알림 ( 0 : ON, 1 : OFF )
	// (중요처리건 : 취소처리필요건, 완료확인필요건, 신규문의, 환전신청)
	*/

?>
<script type="text/javascript" src="<?= DU_UDEV_DIR ?>/member/js/member.js"></script>

<div id="wrapper">
	<div id="container" class="">
		<div class="container_wr">
			<h1 id="container_title">단체 리스트</h1>

			<div class="local_ov01 local_ov">
				<span class="btn_ov01"><span class="ov_txt">단체 수 </span><span class="ov_num"><?= number_format($totalCnt); ?>명 </span>&nbsp;
			</div>
			<form class="local_sch03 local_sch" autocomplete="off">
				<div>
					<strong>분류</strong>
					<select name="findType" id="findType">
						<option value="mem_NickNm" <? if ($findType == "mem_NickNm") { ?>selected<? } ?>>닉네임</option>
						<option value="mem_Id" <? if ($findType == "mem_Id") { ?>selected<? } ?>>아이디</option>
						<option value="mem_Tel" <? if ($findType == "mem_Tel") { ?>selected<? } ?>>연락처</option>
					</select>
					<label for="findword" class="sound_only">검색어<strong class="sound_only"> 필수</strong></label>
					<input type="text" name="findword" id="findword" value="<?= $findword ?>" class=" frm_input">
				</div>
			</form>
			<nav class="pg_wrap">
				<?= get_apaging($rows, $page, $total_page, "$_SERVER[PHP_SELF]?$qstr"); ?>
			</nav>
			<form name="fmemberlist" id="fmemberlist" method="post" autocomplete="off">
				<div class="tbl_head01 tbl_wrap">
					<table>
						<caption>회원관리 목록</caption>
						<thead>
							<!-- 아이디, 이름, 등급, 휴대폰번호, 가입일 -->
							<tr>
								<th scope="col" id="mb_list_idx">순번</th>
								<th scope="col" id="mb_list_id">아이디</th>
								<th scope="col" id="mb_list_id">단체명</th>
								<th scope="col" id="mb_list_open">연락처</th>
								<th scope="col" id="mb_list_member">가입자수</th>
								<th scope="col" id="mb_list_mng" class="last_cell">관리</th>
							</tr>
						</thead>
						<tbody>
							<?
							if ($numCnt > 0) {

								$stmt->setFetchMode(PDO::FETCH_ASSOC);

								while ($row = $stmt->fetch()) {
									// $bg = 'bg'.($stmt->fetch()%2);

									$from_record++;
									$memIdx = $row['idx'];
									$memId = $row['mem_Id'];
									$memTel = $row['mem_Tel'];
									$memNickNm = $row['mem_NickNm'];

									// 가입자 수
									$mCntQuery = "SELECT COUNT(A.idx) AS mem_Cnt FROM TB_MEMBERS A INNER JOIN TB_MEMBERS_ETC B ON A.idx = B.mem_Idx WHERE B.mem_ChCode = :idx AND A.b_Disply = 'N' ";
									$mCntStmt = $DB_con->prepare($mCntQuery);
									$mCntStmt->bindparam(":idx", $idx);
									$mCntStmt->execute();
									$mCntNum = $mCntStmt->rowCount();
									if ($mCntNum < 1) { //아닐경우
									} else {
										while ($mCntRow = $mCntStmt->fetch(PDO::FETCH_ASSOC)) {
											$mem_Cnt = trim($mCntRow['mem_Cnt']);							
										}
									}

							?>

									<tr class="<?= $bg ?>">
										<td headers="mb_list_idx" class="td_idx"><?= $from_record ?></td>
										<td headers="mb_list_id"><?= $memId ?></td>
										<td headers="mb_list_id"><?= $memNickNm ?></td>
										<td headers="mb_list_open" class="td_name td_mng_s"><?= $memTel ?></td>
										<td headers="mb_list_open" class="td_name td_mng_s"><?= number_format($mem_Cnt) ?>명</td>
										<td headers="mb_list_mng" class="td_mng td_mng_s">
											<a href="memberAdminReg.php?mode=mod&idx=<?= $memIdx ?>&id=<?= $memId ?>&<?= $qstr ?>&page=<?= $page ?>" class="btn btn_03">상세</a>
										</td>
									</tr>
								<?

								}
								?>
							<? } else { ?>
								<tr>
									<td colspan="13" class="empty_table">자료가 없습니다.</td>
								</tr>
							<? } ?>
						</tbody>
					</table>
				</div>

				<div class="btn_fixed_top">
					<a href="memberAdminReg.php?mode=reg" id="bt_m_a_add" class="btn btn_01">단체 추가</a>
				</div>

			</form>
			<nav class="pg_wrap">
				<?= get_apaging($rows, $page, $total_page, "$_SERVER[PHP_SELF]?$qstr"); ?>
			</nav>

			<script>
				$(function() {
					$("#fr_date, #to_date").datepicker({
						changeMonth: true,
						changeYear: true,
						dateFormat: "yy-mm-dd",
						showButtonPanel: true,
						yearRange: "c-99:c+99",
						maxDate: "+0d"
					});
				});
			</script>

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