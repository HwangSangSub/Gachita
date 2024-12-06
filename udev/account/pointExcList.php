<?
$menu = "7";
$smenu = "2";

include "../common/inc/inc_header.php";  //헤더
include "../../lib/card_password.php"; //카드정보 암호화

$base_url = $PHP_SELF;

$sql_search = " WHERE 1=1 ";
if ($fr_date != "" || $to_date != "") {
	//$sql_search.=" AND (reg_CDate between ':fr_date' AND ':to_date')";
	$sql_search .= " AND (DATE_FORMAT(A.reg_Date,'%Y-%m-%d') >= :fr_date AND DATE_FORMAT(A.reg_Date,'%Y-%m-%d') <= :to_date)";
}

if ($findword != "") {
	if ($findType == "mem_Tel") {
		$sql_search .= " AND C.mem_Tel LIKE :findword ";
	}
	if ($findType == "idx") {
		$sql_search .= " AND A.idx LIKE :findword ";
	}
}




$DB_con = db1();

//전체 카운트
$cntQuery = "";
$cntQuery = "SELECT COUNT(A.idx) AS cntRow FROM TB_POINT_EXC A INNER JOIN TB_MEMBERS C ON A.mem_Id = C.mem_Id {$sql_search}  ";
//echo $cntQuery."<BR>";
//exit;
$cntStmt = $DB_con->prepare($cntQuery);

if ($taxi_MState != "") {
	$cntStmt->bindValue(":taxi_MState", $taxi_MState);
}

if ($taxiSMemId != "") {  //고유회원 아이디
	$cntStmt->bindValue(":taxi_SMemId", $taxiSMemId);
}

if ($fr_date != "" || $to_date != "") {
	$cntStmt->bindValue(":fr_date", $fr_date);
	$cntStmt->bindValue(":to_date", $to_date);
}

if ($findword != "") {
	$cntStmt->bindValue(':findword', '%' . $findword . '%');
}

$fr_date = trim($fr_date);
$to_date = trim($to_date);
$findword = trim($findword);

$cntStmt->execute();

$row = $cntStmt->fetch(PDO::FETCH_ASSOC);
$totalCnt = $row['cntRow'];

//echo $totalCnt."<BR>";
//$cntStmt = null;

$rows = 10;
$total_page  = ceil($totalCnt / $rows);  // 전체 페이지 계산
if ($page == "") {
	$page = 1;
} // 페이지가 없으면 첫 페이지 (1 페이지)
$from_record = ($page - 1) * $rows; // 시작 열을 구함

if (!$sort1) {
	$sort1  = "A.idx";
	$sort2 = "DESC";
}

$sql_order = "order by $sort1 $sort2";

// 투게더 닉네임
$mnSql = "  , ( SELECT mem_NickNm FROM TB_MEMBERS C WHERE C.mem_Id = A.mem_Id AND C.b_Disply = 'N' limit 1 ) AS memNickNm  ";
$mnSql2 = "  , ( SELECT mem_NickNm FROM TB_MEMBERS C WHERE C.mem_Id = A.mem_Id AND C.b_Disply = 'Y' limit 1 ) AS memNickNm2  "; //탈퇴회원
$query = "";
$query .= "SELECT A.idx, A.mem_Id, A.exc_Idx, A.exc_Price, A.e_Disply, A.reg_Date, A.reg_ExcDate, C.mem_Tel {$mnSql} {$mnSql2} ";
$query .= " FROM TB_POINT_EXC A ";
$query .= " INNER JOIN TB_MEMBERS C ON A.mem_Id = C.mem_Id ";
$query .= " {$sql_search} {$sql_order} limit  {$from_record}, {$rows}";
//echo $query."<BR>";
//exit;
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
$findword = trim($findword);

$stmt->execute();
$numCnt = $stmt->rowCount();


$qstr = "fr_date=" . urlencode($fr_date) . "&amp;to_date=" . urlencode($to_date) . "&amp;findType=" . urlencode($findType) . "&amp;findword=" . urlencode($findword) . "&amp;taxi_MState=" . urlencode($taxi_MState);

//수수료 조회
$taxQuery = "";
$taxQuery = "SELECT con_Tax FROM TB_CONFIG_EXC ";
$taxStmt = $DB_con->prepare($taxQuery);
$taxStmt->execute();
$taxRow = $taxStmt->fetch(PDO::FETCH_ASSOC);
$con_Tax = $taxRow['con_Tax'];

include "../common/inc/inc_gnb.php";  //헤더
include "../common/inc/inc_menu.php";  //메뉴

?>
<script type="text/javascript" src="<?= DU_UDEV_DIR ?>/member/js/memberManager.js"></script>
<script type="text/javascript">
	function point_ExcChk(idx, memId, Etype) {
		if (Etype == 'Y') {
			var allData = {
				"idx": idx,
				"memId": memId,
				"e_Disply": "Y"
			};
			$.ajax({
				url: "/udev/account/pointExcProc.php",
				type: 'POST',
				dataType: 'json',
				data: allData,
				success: function(data) {
					alert(data.Msg);
					location.reload();
				},
				error: function(jqXHR, textStatus, errorThrown) {
					alert("에러 발생~~ \n" + textStatus + " : " + errorThrown);
					location.reload();
				}
			});
		} else if (Etype == 'N') {
			var allData = {
				"idx": idx,
				"memId": memId,
				"e_Disply": "N"
			};
			$.ajax({
				url: "/udev/account/pointExcProc.php",
				type: 'POST',
				dataType: 'json',
				data: allData,
				success: function(data) {
					alert(data.Msg);
					location.reload();
				},
				error: function(jqXHR, textStatus, errorThrown) {
					alert("에러 발생~~ \n" + textStatus + " : " + errorThrown);
					location.reload();
				}
			});
		}
	}
</script>

<div id="wrapper">
	<div id="container" class="">
		<div class="container_wr">
			<h1 id="container_title">출금요청 관리</h1>
			<div class="local_ov01 local_ov">
				<span class="btn_ov01"><span class="ov_txt">총 건수 </span><span class="ov_num"><?= number_format($totalCnt); ?>건 </span>
			</div>

			<form class="local_sch03 local_sch" autocomplete="off">
				<div>
					<strong>분류</strong>
					<label for="findType" class="sound_only">검색대상</label>
					<select name="findType" id="findType">
						<option value="mem_Tel" <? if ($findType == "mem_Tel") { ?>selected<? } ?>>연락처</option>
					</select>
					<label for="findword" class="sound_only">검색어<strong class="sound_only"> 필수</strong></label>
					<input type="text" name="findword" id="findword" value="<?= $findword ?>" class=" frm_input">
				</div>
				<div class="sch_last">
					<strong>취소일자</strong>
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

				<div class="local_desc01 local_desc">
					<p>
						기간선택이 없을 경우 기본적으로 전체 일자의 데이터를 통계처리합니다.<br>
						<B>출금요청금액은 실제 사용자가 요청한 금액입니다.</B><br>
						일자는 최근일자부터 역순으로 정렬됩니다.<br><br>
						현재 설정된 수수료는 <a href="<?= DU_UDEV_DIR ?>/config/configExcReg.php"><span style="font-weight:bold; color:red;"><?= $con_Tax ?></span></a> % 입니다.<br>
					</p>
				</div>
			</form>

			<nav class="pg_wrap">
				<?= get_apaging($rows, $page, $total_page, "$_SERVER[PHP_SELF]?$qstr"); ?>
			</nav>

			<form name="fmlist" id="fmlist" method="post" autocomplete="off">

				<div class="tbl_head01 tbl_wrap">
					<table>
						<caption>출금 요청 목록</caption>
						<thead>
							<tr>
								<th scope="col" id="mb_list_idx">순번</th>
								<th scope="col" id="mb_list_Sid">출금요청자</th>
								<th scope="col" id="mb_list_Sid">연락처</th>
								<th scope="col" id="mb_list_mailc">계좌명</th>
								<th scope="col" id="mb_list_mailc">은행명</th>
								<th scope="col" id="mb_list_mailc">계좌번호</th>
								<th scope="col" id="mb_list_mailc">출금요청금액</th>
								<th scope="col" id="mb_list_mailc">출금수수료</th>
								<th scope="col" id="mb_list_mailc">입금액</th>
								<th scope="col" id="mb_list_mailc">출금여부</th>
								<th scope="col" id="mb_list_date">요청일</th>
								<th scope="col" id="mb_list_mng">관리</th>
							</tr>
						</thead>
						<tbody>

							<?

							if ($numCnt > 0) {

								$stmt->setFetchMode(PDO::FETCH_ASSOC);

								$i = 0;

								while ($row = $stmt->fetch()) {
									$i = $i + 1;
									$bg = 'bg' . ($i % 2);
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
									$idx = $row['idx'];
									$mem_Id = $row['mem_Id'];
									$mem_Tel = $row['mem_Tel'];
									$exc_Idx = $row['exc_Idx'];

									$bankQuery = "SELECT bank_OName, bank_Name, bank_Number FROM TB_PAYMENT_BANK WHERE idx = :exc_Idx";
									$bankStmt = $DB_con->prepare($bankQuery);
									$bankStmt->bindValue(":exc_Idx", $exc_Idx);
									$bankStmt->execute();
									$bankCnt = $bankStmt->rowCount();
									if ($bankCnt > 0) {
										$bankRow = $bankStmt->fetch(PDO::FETCH_ASSOC);
										$bankOName = $bankRow['bank_OName'];
										$bankName = $bankRow['bank_Name'];
										$bank_Number = $bankRow['bank_Number'];
										$bankNumber = openssl_decrypt(base64_decode($bank_Number), 'aes-256-cbc', $password, OPENSSL_RAW_DATA, $iv);
									} else {
										$bankOName = "";
										$bankName = "";
										$bankNumber = "";
									}

									$exc_Price = $row['exc_Price'];
									$conTax = $con_Tax / 100;
									$tax = $exc_Price * $conTax;
									$excPrice = (int)$exc_Price - (int)$tax;
									$e_Disply = $row['e_Disply'];
									$taxi_Memo = $row['taxi_Memo'];
									$reg_Date = $row['reg_Date'];
									$reg_ExcDate = $row['reg_ExcDate'];


									if ($e_Disply == 'Y') {
										$eDisply = '입금완료';
									} else if ($e_Disply == 'N') {
										$eDisply = '입금대기중';
									} else if ($e_Disply == 'C') {
										$eDisply = '출금거절처리';
									}

							?>


									<tr class="<?= $bg ?>">
										<td headers="mb_list_id"><?= $from_record ?></td>
										<td headers="mb_list_id"><a href="/udev/member/memberReg.php?mode=mod&id=<?= $mem_Id ?>"><?= $mem_Id ?> </br> (<?= $memNickNm ?>)</a></td>
										<td headers="mb_list_id"><?= $mem_Tel ?></td>
										<td headers="mb_list_id"><?= $bankOName ?></td>
										<td headers="mb_list_id"><?= $bankName ?></td>
										<td headers="mb_list_id"><?= $bankNumber ?></td>
										<td headers="mb_list_id"><?= number_format($exc_Price) . " 원" ?></td>
										<td headers="mb_list_id"><?= number_format($tax) . "원" ?></td>
										<td headers="mb_list_id"><?= number_format($excPrice) . " 원" ?></td>
										<td headers="mb_list_id"><?= $eDisply ?></td>
										<td headers="mb_list_id"><?= ($reg_ExcDate != '' ? $reg_ExcDate : $reg_Date) ?></td>
										<td headers="mb_list_mng" class="td_mng td_mng_s">
											<?
											if ($e_Disply == 'N') {
											?>
												<a href="javascript:;" onclick="point_ExcChk(<?= $idx ?>,'<?= $mem_Id ?>','Y');" class="btn btn_01">완료</a>
												<a href="javascript:;" onclick="point_ExcChk(<?= $idx ?>,'<?= $mem_Id ?>','N');" class="btn btn_01">취소</a>
											<?
											} else {
											?>
												<span>-</span>
											<?
											}
											?>
										</td>
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
			</script>
		</div>

		<?
		dbClose($DB_con);
		$cntStmt = null;
		$stmt = null;

		include "../common/inc/inc_footer.php";  //푸터 

		?>