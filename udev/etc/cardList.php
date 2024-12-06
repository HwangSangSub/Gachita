<?
$menu = "3";
$smenu = "1";

include "../common/inc/inc_header.php";  //헤더 

$base_url = $PHP_SELF;

$sql_search = " WHERE 1";

if ($fr_date != "" || $to_date != "") {
	//$sql_search.=" AND (reg_Date between ':fr_date' AND ':to_date')";
	$sql_search .= " AND (DATE_FORMAT(A.reg_Date,'%Y-%m-%d') >= :fr_date AND DATE_FORMAT(A.reg_Date,'%Y-%m-%d') <= :to_date)";
}

if ($findword != "") {
	if ($findType == "card_Mem_Id") {
		$sql_search .= " AND A.card_Mem_Id LIKE :findword ";
	} else if ($findType == "mem_Tel") {
		$sql_search .= " AND B.mem_Tel LIKE :findword ";
	} else if ($findType == "card_Number") {
		$sql_search .= " AND A.card_Number LIKE :findword ";
	}
}

$DB_con = db1();

//전체 카운트
$cntQuery = "";
$cntQuery = "SELECT COUNT(A.idx)  AS cntRow FROM TB_PAYMENT_CARD A INNER JOIN TB_MEMBERS B ON B.idx = A.card_Mem_Idx {$sql_search} ";
$cntStmt = $DB_con->prepare($cntQuery);
if ($fr_date != "" || $to_date != "") {
	$cntStmt->bindValue(":fr_date", $fr_date);
	$cntStmt->bindValue(":to_date", $to_date);
}

if ($findword != "") {
	$cntStmt->bindValue(':findword', "'%" . $findword . "%'");
}

$fr_date = trim($fr_date);
$to_date = trim($to_date);
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
	$sort1  = "reg_Date";
	$sort2 = "DESC";
}

$sql_order = "order by $sort1 $sort2";

//목록
$mnSql = "  , ( SELECT mem_NickNm FROM TB_MEMBERS B WHERE B.idx = A.card_Mem_Idx AND B.b_Disply = 'N' limit 1 ) AS memNickNm  ";
$mnSql2 = "  , ( SELECT mem_NickNm FROM TB_MEMBERS B WHERE B.idx = A.card_Mem_Idx AND B.b_Disply = 'Y' limit 1 ) AS memNickNm2  "; //탈퇴회원
$query = "";
$query = "SELECT A.idx, A.card_Mem_Idx, A.card_Mem_Id, A.card_Name, A.reg_Date, B.mem_Tel as memTel {$mnSql} {$mnSql2} FROM TB_PAYMENT_CARD A INNER JOIN TB_MEMBERS B ON B.idx = A.card_Mem_Idx {$sql_search} {$sql_order} limit  {$from_record}, {$rows}";
$stmt = $DB_con->prepare($query);
if ($fr_date != "" || $to_date != "") {
	$stmt->bindValue(":fr_date", $fr_date);
	$stmt->bindValue(":to_date", $to_date);
}

if ($findword != "") {
	$stmt->bindValue(':findword', '%' . $findword . '%');
}

$fr_date = trim($fr_date);
$to_date = trim($to_date);
$findType = trim($findType);
$findword = trim($findword);

$stmt->execute();
$numCnt = $stmt->rowCount();

$qstr = "fr_date=" . urlencode($fr_date) . "&amp;o_date=" . urlencode($to_date) . "&amp;findType=" . urlencode($findType) . "&amp;findword=" . urlencode($findword);

include "../common/inc/inc_gnb.php";  //헤더 
include "../common/inc/inc_menu.php";  //메뉴 

?>
<script type="text/javascript" src="<?= DU_UDEV_DIR ?>/etc/js/card.js"></script>

<div id="wrapper">
	<div id="container" class="">
		<div class="container_wr">
			<h1 id="container_title">결제카드관리</h1>

			<div class="local_ov01 local_ov">
				<span class="btn_ov01"><span class="ov_txt">총 수 </span><span class="ov_num"><?= number_format($totalCnt); ?>명 </span>
			</div>

			<form class="local_sch03 local_sch" autocomplete="off">

				<div>
					<strong>분류</strong>
					<select name="findType" id="findType">
						<option value="card_Mem_Id" <? if ($findType == "card_Mem_Id") { ?>selected<? } ?>>아이디</option>
						<option value="mem_Tel" <? if ($findType == "mem_Tel") { ?>selected<? } ?>>연락처</option>
					</select>
					<label for="findword" class="sound_only">검색어<strong class="sound_only"> 필수</strong></label>
					<input type="text" name="findword" id="findword" value="<?= $findword ?>" class=" frm_input">
				</div>

				<div class="sch_last">
					<strong>등록일검색</strong>
					<input type="text" name="fr_date" id="fr_date" value="<?= $fr_date ?>" class="frm_input" size="11" maxlength="10">
					<label for="fr_date" class="sound_only">시작일</label>
					~
					<input type="text" name="to_date" id="to_date" value="<?= $to_date ?>" class="frm_input" size="11" maxlength="10">
					<label for="to_date" class="sound_only">종료일</label>
					<input type="submit" value="검색" class="btn_submit">

					<a href="<?= $base_url ?>" class="btn btn_06">새로고침</a>
				</div>
			</form>


			<form name="fmemberlist" id="fmemberlist" method="post" autocomplete="off">

				<div class="tbl_head01 tbl_wrap">
					<table>
						<caption>회원관리 목록</caption>
						<thead>

							<!-- 아이디, 이름, 등급, 휴대폰번호, 가입일 -->
							<tr>
								<th scope="col" id="mb_list_idx">순번</th>
								<th scope="col" id="mb_list_id">아이디</th>
								<th scope="col" id="mb_list_name">닉네임</th>
								<th scope="col" id="mb_list_tel">연락처</th>
								<th scope="col" id="mb_list_mailc">카드명</th>
								<th scope="col" id="mb_list_mailr">등록일</th>
								<!--<th scope="col" id="mb_list_mng">관리</th>-->
							</tr>
						</thead>
						<tbody>

							<?

							if ($numCnt > 0) {

								$stmt->setFetchMode(PDO::FETCH_ASSOC);

								while ($row = $stmt->fetch()) {
									// $bg = 'bg'.($stmt->fetch()%2);
									$from_record++;
									$memNickNm1 = $row['memNickNm'];
									$memNickNm2 = $row['memNickNm2'];

									if ($memNickNm1 != "") {
										$memNickNm = $memNickNm1;
									} else if ($memNickNm2 != "") {
										$memNickNm = $memNickNm2;
									} else {
										$memNickNm = "비회원";
									}
							?>
									<tr class="<?= $bg ?>">
										<td headers="mb_list_idx"><?= $from_record ?></td>
										<td headers="mb_list_id"><a href="/udev/member/memberReg.php?mode=mod&id=<?= $row['card_Mem_Id'] ?>"><?= $row['card_Mem_Id'] ?></a></td>
										<td headers="mb_list_nname"><?= $memNickNm ?></td>
										<td headers="mb_list_tel"><?= $row['memTel'] ?></td>
										<td headers="mb_list_id"><?= $row['card_Name'] ?></td>
										<td headers="mb_list_lastcall" class="td_date"><?= substr($row['reg_Date'], 2, 8) ?></td>
										<!--<td headers="mb_list_mng" class="td_mng td_mng_s"><a href="javascript:chkDel('<?= $row['idx'] ?>')" class="btn btn_02">삭제</a>-->
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