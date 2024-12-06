<?
$menu = "7";
$smenu = "3";

include "../common/inc/inc_header.php";  //헤더 

$base_url = $PHP_SELF;

$sql_search = " WHERE 1=1 ";

if ($fr_date != "" || $to_date != "") {
	$sql_search .= " AND (DATE_FORMAT(reg_Date,'%Y-%m-%d') >= :fr_date AND DATE_FORMAT(reg_Date,'%Y-%m-%d') <= :to_date)";
}

$DB_con = db1();

//전체 카운트
$cntQuery = "SELECT idx FROM TB_PROFIT_POINT";
$cntQuery .= " {$sql_search} GROUP BY left(reg_Date,10) ";
$cntStmt = $DB_con->prepare($cntQuery);
//echo $cntQuery;
//exit;
if ($fr_date != "" || $to_date != "") {
	$cntStmt->bindValue(":fr_date", $fr_date);
	$cntStmt->bindValue(":to_date", $to_date);
}
$cntStmt->execute();
$totalCnt = $cntStmt->rowCount();

//전체 카운트
$sumQuery = "SELECT SUM(taxi_OrdMPoint) AS plus_Money, SUM(taxi_OrdTPoint) AS subt_Money, SUM(taxi_OrdSPoint) AS profit_Money FROM TB_PROFIT_POINT";
$sumQuery .= " {$sql_search}";
$sumStmt = $DB_con->prepare($sumQuery);
//echo $cntQuery;
//exit;
if ($fr_date != "" || $to_date != "") {
	$sumStmt->bindValue(":fr_date", $fr_date);
	$sumStmt->bindValue(":to_date", $to_date);
}
$sumStmt->execute();
while ($sumRow = $sumStmt->fetch()) {
	$plus_Money = $sumRow['plus_Money'];
	$subt_Money = $sumRow['subt_Money'];
	$profit_Money = $sumRow['profit_Money'];
}

$rows = 10;
$total_page  = ceil($totalCnt / $rows);  // 전체 페이지 계산
if ($page == "") {
	$page = 1;
} // 페이지가 없으면 첫 페이지 (1 페이지)
$from_record = ($page - 1) * $rows; // 시작 열을 구함

$sql_group = "GROUP BY left(reg_Date,10)";
$sql_order = "ORDER BY left(reg_Date,10) DESC";

//목록
$query = " SELECT left(reg_Date,10) AS DATE, SUM(taxi_OrdMPoint) AS plus_Money, SUM(taxi_OrdTPoint) AS subt_Money, SUM(taxi_OrdSPoint) AS profit_Money FROM TB_PROFIT_POINT";
$query .= " {$sql_search} {$sql_group} {$sql_order} limit  {$from_record}, {$rows} ";
//exit;

$stmt = $DB_con->prepare($query);

if ($fr_date != "" || $to_date != "") {
	$stmt->bindValue(":fr_date", $fr_date);
	$stmt->bindValue(":to_date", $to_date);
}

if ($findword != "") {
	$stmt->bindValue(':findword', '%' . trim($findword) . '%');
}

$stmt->execute();
$numCnt = $stmt->rowCount();

$qstr = "fr_date=" . urlencode($fr_date) . "&amp;to_date=" . urlencode($to_date) . "&amp;findType=" . urlencode($findType) . "&amp;findOs=" . urlencode($findOs) . "&amp;findword=" . urlencode($findword);

include "../common/inc/inc_gnb.php";  //헤더 
include "../common/inc/inc_menu.php";  //메뉴 

?>
<script type="text/javascript" src="<?= DU_UDEV_DIR ?>/member/js/member.js"></script>

<div id="wrapper">
	<div id="container" class="">
		<div class="container_wr">
			<h1 id="container_title">수익통계</h1>

			<div class="local_ov01 local_ov">
				<span class="btn_ov01"><span class="ov_txt">기간 내 총 투게더 나눠내기 포인트 </span><span class="ov_num"><?= number_format($subt_Money); ?> 원</span>&nbsp;
				<span class="btn_ov01"><span class="ov_txt">기간 내 총 메이커 적립 포인트 </span><span class="ov_num"><?= number_format($plus_Money); ?> 원</span>&nbsp;
				<span class="btn_ov01"><span class="ov_txt">기간 내 총 수수료 </span><span class="ov_num"><?= number_format($profit_Money); ?> 원</span>&nbsp;
			</div>
			<form class="local_sch03 local_sch" autocomplete="off">
				<div class="sch_last">
					<strong>검색일자</strong>
					<input type="text" id="fr_date" name="fr_date" value="<?= $fr_date ?>" class="frm_input" size="10" maxlength="10"> ~
					<input type="text" id="to_date" name="to_date" value="<?= $to_date ?>" class="frm_input" size="10" maxlength="10">
					<button type="button" onclick="javascript:set_date('오늘');">오늘</button>
					<button type="button" onclick="javascript:set_date('어제');">어제</button>
					<button type="button" onclick="javascript:set_date('이번주');">이번주</button>
					<button type="button" onclick="javascript:set_date('이번달');">이번달</button>
					<button type="button" onclick="javascript:set_date('지난주');">지난주</button>
					<button type="button" onclick="javascript:set_date('지난달');">지난달</button>
					<button type="button" onclick="javascript:set_date('전체');">전체</button>
					<input type="submit" value="검색" class="btn_submit">
					<a href="<?= $base_url ?>" class="btn btn_06">새로고침</a>
				</div>
			</form>



			<div class="local_desc01 local_desc">
				<p>
					기간선택이 없을 경우 기본적으로 전체 일자의 데이터를 통계처리합니다.<br>
					해당 기간의 일별 데이터는 아래에 테이블로 표시되며 해당기간의 총합은 검색일자 위에 표시됩니다.<br>
					최종완료 거래완료된 노선에 한해서 통계처리합니다.<br>
					적립포인트, 양도포인트, 총수익금별로 확인가능합니다.<br>
					일자는 최근일자부터 역순으로 정렬됩니다.
				</p>
			</div>

			<nav class="pg_wrap">
				<?= get_apaging($rows, $page, $total_page, "$_SERVER[PHP_SELF]?$qstr"); ?>
			</nav>

			<form name="fmemberlist" id="fmemberlist" method="post" autocomplete="off">

				<div class="tbl_head01 tbl_wrap">
					<table>
						<caption>수익통계</caption>
						<thead>

							<!-- 아이디, 이름, 등급, 휴대폰번호, 가입일 -->
							<tr>
								<th scope="col" id="mb_list_idx">순번</th>
								<th scope="col" id="mb_list_data">일자</th>
								<th scope="col" id="mb_list_subtMoney">투게더 나눠내기 포인트</th>
								<th scope="col" id="mb_list_plusMoney">메이커 적립 포인트</th>
								<th scope="col" id="mb_list_profitMoney">수수료</th>
							</tr>
						</thead>
						<tbody>

							<?

							if ($numCnt > 0) {

								$stmt->setFetchMode(PDO::FETCH_ASSOC);

								while ($row = $stmt->fetch()) {
									// $bg = 'bg'.($stmt->fetch()%2);

									$from_record++;
									$date = $row['DATE'];
									$plus_Money = $row['plus_Money'];
									$subt_Money = $row['subt_Money'];
									$profit_Money = $row['profit_Money'];
							?>

									<tr class="<?= $bg ?>">
										<td headers="mb_list_idx" class="td_idx"><?= $from_record ?></td>
										<td headers="mb_list_date" class="td_date"><?= $date ?></td>
										<td headers="mb_list_subtMoney"><?= number_format($subt_Money) ?> 원</td>
										<td headers="mb_list_plusMoney"><?= number_format($plus_Money) ?> 원</td>
										<td headers="mb_list_profitMoney"><?= number_format($profit_Money) ?> 원</td>
									</tr>
								<?

								}
								?>
							<? } else { ?>
								<tr>
									<td colspan="9" class="empty_table">자료가 없습니다.</td>
								</tr>
							<? } ?>
						</tbody>
					</table>
				</div>

				<div class="btn_fixed_top">
					<a href="#ALDel" id="bt_m_a_del" class="btn btn_02">선택삭제</a>
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
						showButtonPanel: true
					});
				});


				function set_date(today) {
					<?
					$date_term = date('w', DU_SERVER_TIME);
					$week_term = $date_term + 7;
					$last_term = strtotime(date('Y-m-01', DU_SERVER_TIME));
					?>
					if (today == "오늘") {
						document.getElementById("fr_date").value = "<?php echo DU_TIME_YMD; ?>";
						document.getElementById("to_date").value = "<?php echo DU_TIME_YMD; ?>";
					} else if (today == "어제") {
						document.getElementById("fr_date").value = "<?php echo date('Y-m-d', DU_SERVER_TIME - 86400); ?>";
						document.getElementById("to_date").value = "<?php echo date('Y-m-d', DU_SERVER_TIME - 86400); ?>";
					} else if (today == "이번주") {
						document.getElementById("fr_date").value = "<?php echo date('Y-m-d', strtotime('-' . $date_term . ' days', DU_SERVER_TIME)); ?>";
						document.getElementById("to_date").value = "<?php echo date('Y-m-d', DU_SERVER_TIME); ?>";
					} else if (today == "이번달") {
						document.getElementById("fr_date").value = "<?php echo date('Y-m-01', DU_SERVER_TIME); ?>";
						document.getElementById("to_date").value = "<?php echo date('Y-m-d', DU_SERVER_TIME); ?>";
					} else if (today == "지난주") {
						document.getElementById("fr_date").value = "<?php echo date('Y-m-d', strtotime('-' . $week_term . ' days', DU_SERVER_TIME)); ?>";
						document.getElementById("to_date").value = "<?php echo date('Y-m-d', strtotime('-' . ($week_term - 6) . ' days', DU_SERVER_TIME)); ?>";
					} else if (today == "지난달") {
						document.getElementById("fr_date").value = "<?php echo date('Y-m-01', strtotime('-1 Month', $last_term)); ?>";
						document.getElementById("to_date").value = "<?php echo date('Y-m-t', strtotime('-1 Month', $last_term)); ?>";
					} else if (today == "전체") {
						document.getElementById("fr_date").value = "";
						document.getElementById("to_date").value = "";
					}
				}


				/*
    var seconds = 5;
    var id = setInterval(function()
    {
       	location.reload();
    }, 1000*seconds);
	*/
			</script>

		</div>

		<?
		dbClose($DB_con);
		$cntStmt = null;
		$stmt = null;
		$mcntStmt = null;
		$mstmt = null;

		include "../common/inc/inc_footer.php";  //푸터 

		?>