<?
$menu = "2";
$smenu = "2";

include "../common/inc/inc_header.php";  //헤더 

$base_url = $PHP_SELF;


$sql_search = " WHERE A.mem_Lv NOT IN ('0', '1', '2') AND A.b_Disply = 'N' ";

if ($fr_date != "" || $to_date != "") {
	//$sql_search.=" AND (reg_Date between ':fr_date' AND ':to_date')";
	$sql_search .= " AND (DATE_FORMAT(A.reg_Date,'%Y-%m-%d') >= :fr_date AND DATE_FORMAT(A.reg_Date,'%Y-%m-%d') <= :to_date)";
}

if ($mem_Lv != "") {
	$sql_search .= " AND A.mem_Lv = :mem_Lv";
}

if ($mem_ChCode != "") {
	$sql_search .= " AND B.mem_ChCode = :mem_ChCode";
}


if ($findMir == "") {
	$findMir = "N";
}

if ($findword != "") {
	if ($findType == "mem_NickNm") {
		$sql_search .= " AND A.mem_NickNm LIKE :findword ";
	} else if ($findType == "mem_Id") {
		$sql_search .= " AND A.mem_Id LIKE :findword ";
	} else if ($findType == "mem_Tel") {
		$sql_search .= " AND A.mem_Tel LIKE :findword ";
	} else if ($findType == "mem_Nm") {
		$sql_search .= " AND A.mem_Nm LIKE :findword ";
	}
}


$DB_con = db1();

//전체 카운트
$cntQuery = "";
$cntQuery = "SELECT COUNT(A.mem_Id)  AS cntRow FROM TB_MEMBERS A INNER JOIN TB_MEMBERS_ETC B ON B.mem_Idx = A.idx  {$sql_search}  ";
$cntStmt = $DB_con->prepare($cntQuery);

if ($fr_date != "" || $to_date != "") {
	$cntStmt->bindValue(":fr_date", $fr_date);
	$cntStmt->bindValue(":to_date", $to_date);
}

if ($mem_Lv != "") {
	$cntStmt->bindValue(":mem_Lv", $mem_Lv);
}

if ($mem_ChCode != "") {
	$cntStmt->bindValue(":mem_ChCode", $mem_ChCode);
}

if ($findOs != "") {
	$cntStmt->bindValue(":mem_Os", $findOs);
}

if ($findword != "") {
	$cntStmt->bindValue(':findword', '%' . trim($findword) . '%');
}

$cntStmt->execute();
$row = $cntStmt->fetch(PDO::FETCH_ASSOC);
$totalCnt = $row['cntRow'];


if ($rows == '') {
	$rows = '10';
}
$total_page  = ceil($totalCnt / $rows);  // 전체 페이지 계산
if ($page == "") {
	$page = 1;
} // 페이지가 없으면 첫 페이지 (1 페이지)
$from_record = ($page - 1) * $rows; // 시작 열을 구함


if (!$sort1) {
	$sort1  = "A.reg_Date";
	$sort2 = "DESC";
}

$sql_order = "order by $sort1 $sort2";

// 회원등급명
$memLvSql = "  , ( SELECT memLv_Name FROM TB_MEMBER_LEVEL C WHERE C.memLv = A.mem_Lv limit 1 ) AS memLvNm  ";

//목록
$query = "";
$query = "SELECT A.idx, A.mem_Id, A.mem_NickNm, A.mem_CertBit, A.mem_Tel, A.reg_Date, A.mem_Os, A.b_Disply, A.mem_Code, B.mem_Point, B.mem_MatCnt, ";
$query .= "  B.mem_McCnt, B.mem_Card, B.mem_ChCode";
$query .= " {$memLvSql} FROM TB_MEMBERS A ";
$query .= " INNER JOIN TB_MEMBERS_ETC B ON B.mem_Idx = A.idx  {$sql_search} {$sql_order} limit  {$from_record}, {$rows} ";
// echo $query."<BR>";
// exit;

$stmt = $DB_con->prepare($query);

if ($fr_date != "" || $to_date != "") {
	$stmt->bindValue(":fr_date", $fr_date);
	$stmt->bindValue(":to_date", $to_date);
}

if ($mem_Lv != "") {
	$stmt->bindValue(":mem_Lv", $mem_Lv);
}

if ($mem_ChCode != "") {
	$stmt->bindValue(":mem_ChCode", $mem_ChCode);
}

if ($findOs != "") {
	$stmt->bindValue(":mem_Os", $findOs);
}

if ($findword != "") {
	$stmt->bindValue(':findword', '%' . trim($findword) . '%');
}

$stmt->execute();
$numCnt = $stmt->rowCount();

//탈퇴회원수
$mcntQuery = "";
$mcntQuery = "SELECT COUNT(idx) AS mCnt FROM TB_MEMBERS  WHERE b_Disply = 'Y' ";
$mcntStmt = $DB_con->prepare($mcntQuery);
$mcntStmt->execute();
$mcRow = $mcntStmt->fetch(PDO::FETCH_ASSOC);
$leave_count = $mcRow['mCnt'];

//회원등급
$mquery = "";
$mquery = "SELECT memLv, memLv_Name FROM TB_MEMBER_LEVEL WHERE 1 = 1 ORDER BY memLv ASC";
$mstmt = $DB_con->prepare($mquery);
$mstmt->execute();

//단체구분
$mGroupquery = "SELECT idx, mem_NickNm FROM TB_MEMBERS WHERE mem_Lv = 2 AND b_Disply = 'N'";
$mGroupstmt = $DB_con->prepare($mGroupquery);
$mGroupstmt->execute();

$qstr = "fr_date=" . urlencode($fr_date) . "&amp;to_date=" . urlencode($to_date) . "&amp;findType=" . urlencode($findType) . "&amp;findOs=" . urlencode($findOs) . "&amp;findMir=" . urlencode($findMir) . "&amp;rows=" . urlencode($rows) . "&amp;findword=" . urlencode($findword);

include "../common/inc/inc_gnb.php";		//헤더 
include "../common/inc/inc_menu.php";		//메뉴 
?>
<script type="text/javascript" src="<?= DU_UDEV_DIR ?>/member/js/member.js"></script>

<div id="wrapper">
	<div id="container" class="">
		<div class="container_wr">
			<h1 id="container_title">회원관리</h1>

			<div class="local_ov01 local_ov">
				<span class="btn_ov01"><span class="ov_txt">총회원수 </span><span class="ov_num"><?= number_format($totalCnt); ?>명 </span>&nbsp;
					<span class="btn_ov01"> <span class="ov_txt">탈퇴 </span><span class="ov_num"><?= number_format($leave_count); ?>명</span>
			</div>


			<form class="local_sch03 local_sch" autocomplete="off">

				<div>
					<strong>리스트출력</strong>
					<select id="rows" name="rows" onchange="$('.local_sch').submit();">
						<option value="10" <? if ($rows == "10") { ?>selected="selected" <? } ?>>10개 씩 보기</option>
						<option value="15" <? if ($rows == "15") { ?>selected="selected" <? } ?>>15개 씩 보기</option>
						<option value="20" <? if ($rows == "20") { ?>selected="selected" <? } ?>>20개 씩 보기</option>
					</select>
				</div>
				<div>
					<strong>회원등급</strong>
					<select id="mem_Lv" name="mem_Lv">
						<option value="">회원등급선택</option>
						<?
						$mstmt->setFetchMode(PDO::FETCH_ASSOC);
						while ($v = $mstmt->fetch()) {
						?>
							<option value="<?= $v['memLv']; ?>" <? if ($v['memLv'] == $mem_Lv) { ?>selected="selected" <? } ?>><? echo $v['memLv_Name'] ?></option>
						<? } ?>
					</select>
				</div>
				<div>
					<strong>단체구분</strong>
					<select id="mem_ChCode" name="mem_ChCode">
						<option value="">단체구분선택</option>
						<?
						$mGroupstmt->setFetchMode(PDO::FETCH_ASSOC);
						while ($g = $mGroupstmt->fetch()) {
						?>
							<option value="<?= $g['idx']; ?>" <? if ($g['idx'] == $mem_ChCode) { ?>selected="selected" <? } ?>><? echo $g['mem_NickNm'] ?></option>
						<? } ?>
					</select>
				</div>

				<div>
					<strong>OS구분</strong>
					<select name="findOs" id="findOs">
						<option value="">선택</option>
						<option value="0" <? if ($findOs == "0") { ?>selected<? } ?>>안드로이드</option>
						<option value="1" <? if ($findOs == "1") { ?>selected<? } ?>>아이폰</option>
					</select>
				</div>

				<div>
					<strong>분류</strong>
					<select name="findType" id="findType">
						<option value="mem_Nm" <? if ($findType == "mem_Nm") { ?>selected<? } ?>>이름</option>
						<option value="mem_NickNm" <? if ($findType == "mem_NickNm") { ?>selected<? } ?>>닉네임</option>
						<option value="mem_Id" <? if ($findType == "mem_Id") { ?>selected<? } ?>>아이디</option>
						<option value="mem_Tel" <? if ($findType == "mem_Tel") { ?>selected<? } ?>>연락처</option>
					</select>
					<label for="findword" class="sound_only">검색어<strong class="sound_only"> 필수</strong></label>
					<input type="text" name="findword" id="findword" value="<?= $findword ?>" class=" frm_input">
				</div>

				<div class="sch_last">
					<strong>가입일</strong>
					<input type="text" name="fr_date" id="fr_date" value="<?= $fr_date ?>" class="frm_input" size="11" maxlength="10">
					<label for="fr_date" class="sound_only">시작일</label>
					~
					<input type="text" name="to_date" id="to_date" value="<?= $to_date ?>" class="frm_input" size="11" maxlength="10">
					<label for="to_date" class="sound_only">종료일</label>
					<input type="submit" value="검색" class="btn_submit">

					<a href="<?= $base_url ?>" class="btn btn_06">새로고침</a>
				</div>
			</form>



			<div class="local_desc01 local_desc">
				<p>
					회원자료 삭제 시 다른 회원이 기존 회원아이디를 사용하지 못하도록 회원아이디, 이름은 삭제하지 않고 영구 보관합니다.<!--<br>
		보유포인트는 가치타 보유 포인트입니다. (상세보기시 보유미르페이를 확인 할 수 있습니다.)-->
				</p>
			</div>

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
								<th scope="col" id="mb_list_chk">
									<label for="chkall" class="sound_only">회원 전체</label>
									<input type="checkbox" name="chkall" class="chkc" id="chkAll" onclick="check_all(this.form)">
								</th>
								<th scope="col" id="mb_list_idx">순번</th>
								<th scope="col" id="mb_list_mailr">가입일</th>
								<th scope="col" id="mb_list_mailr">최종접속일</th>
								<th scope="col" id="mb_list_id">아이디(닉네임)</th>
								<th scope="col" id="mb_list_open">휴대폰</th>
								<th scope="col" id="mb_list_card">카드등록여부</th>
								<th scope="col" id="mb_list_mailc">성별(선호좌석)</th>
								<th scope="col" id="mb_list_mailc">sns구분(OS)</th>
								<th scope="col" id="mb_list_mailc">등급(점수)</th>
								<!--<th scope="col" id="mb_list_auth">보유포인트</th>-->
								<th scope="col" id="mb_list_auth">추천인</th>
								<th scope="col" id="mb_list_auth">추천받은 수</th>
								<th scope="col" id="mb_list_mng" class="last_cell">관리</th>
							</tr>
						</thead>
						<tbody>

							<?

							if ($numCnt > 0) {

								$stmt->setFetchMode(PDO::FETCH_ASSOC);

								while ($row = $stmt->fetch()) {
									$from_record++;
									$memIdx = $row['idx'];
									$memId = $row['mem_Id'];

									$memOs = $row['mem_Os'];
									if (isset($memOs)) {
										if ($memOs == 0) {
											$memOs = "안드로이드";
										} else if ($memOs == 1) {
											$memOs = "아이폰";
										} else if ($memOs == 2) {
											$memOs = "기타(운영진)";
										}
									} else {
										$memOs = "-";
									}
									$memCard = $row['mem_Card'];
									if (isset($memCard)) {
										if ($memCard == 1) {
											$memCard = "등록";
										} else if ($memCard == 0) {
											$memCard = "미등록";
										}
									} else {
										$memCard = "-";
									}

									//회원 정보 
									$mInfoQuery = "";
									$mInfoQuery = "SELECT mem_Sex, mem_Seat, mem_SnsChk, login_Date from TB_MEMBERS_INFO  WHERE mem_Id = :mem_Id  LIMIT 1";
									$meInfoStmt = $DB_con->prepare($mInfoQuery);
									$meInfoStmt->bindparam(":mem_Id", $memId);
									$meInfoStmt->execute();
									$infoNum = $meInfoStmt->rowCount();
									//echo $infoNum."<BR>";

									if ($infoNum < 1) { //아닐경우
									} else {
										while ($ifnoRow = $meInfoStmt->fetch(PDO::FETCH_ASSOC)) {
											$mem_Sex = trim($ifnoRow['mem_Sex']);							// 성별 (0:남자 , 1:여자)
											$mem_Seat = trim($ifnoRow['mem_Seat']);						// 좌석 (0:앞자리 , 1:뒷자리)	
											$mem_SnsChk = trim($ifnoRow['mem_SnsChk']);				// sns구분 ( kakao, google )
											$login_Date = trim($ifnoRow['login_Date']);				// 최종접속일
										}
									}

									if ($row['b_Disply'] == "N") {
										$b_Disply = "정상";
									} else {
										$b_Disply = "탈퇴";
									}


									if (isset($mem_Sex)) {
										if ($mem_Sex == 0) {
											$mem_Sex = "남자";
										} else if ($mem_Sex == 1) {
											$mem_Sex = "여자";
										}
									} else {
										$mem_Sex = "-";
									}

									if (isset($mem_Seat)) {
										if ($mem_Seat == 0) {
											$mem_Seat = "앞자리";
										} else if ($mem_Seat == 1) {
											$mem_Seat = "뒷자리";
										}
									} else {
										$mem_Seat = "-";
									}

									if ($login_Date != "") {
										$last_Login = substr($login_Date, 2, 8) . "<br>(" . substr($login_Date, 11, 5) . ")";
									} else {
										$last_Login = "-";
									}

									if (strtoupper($mem_SnsChk) == 'KAKAO') {
										$memSnsChk = '카카오톡';
									} else if (strtoupper($mem_SnsChk) == 'GOOGLE') {
										$memSnsChk = '구글';
									} else {
										$memSnsChk = '-';
									}

									$mem_ChCode = $row['mem_ChCode'];
									if ($mem_ChCode != "") {
										$memChInfoQuery = "SELECT mem_Id, mem_NickNm FROM TB_MEMBERS WHERE idx = :mem_ChIdx";
										$memChInfoStmt = $DB_con->prepare($memChInfoQuery);
										$memChInfoStmt->bindparam(":mem_ChIdx", $mem_ChCode);
										$memChInfoStmt->execute();
										$memChInfoNum = $memChInfoStmt->rowCount();
										//echo $infoNum."<BR>";

										if ($memChInfoNum < 1) { //아닐경우
										} else {
											while ($memChInfoRow = $memChInfoStmt->fetch(PDO::FETCH_ASSOC)) {
												$mem_ChNickNm = trim($memChInfoRow['mem_NickNm']);							// 성별 (0:남자 , 1:여자)
												$mem_ChId = trim($memChInfoRow['mem_Id']);						// 좌석 (0:앞자리 , 1:뒷자리)	
											}
										}
									}

									$idx = $row['idx'];
									$memChCntNum = 0;
									$memChCntQuery = "SELECT idx FROM TB_MEMBERS_ETC WHERE mem_ChCode = :idx";
									$memChCntStmt = $DB_con->prepare($memChCntQuery);
									$memChCntStmt->bindparam(":idx", $idx);
									$memChCntStmt->execute();
									$memChCntNum = $memChCntStmt->rowCount();
									//echo $infoNum."<BR>";

							?>

									<tr class="<?= $bg ?>">
										<td headers="mb_list_chk" class="td_chk">
											<input type="hidden" name="mb_id[<?= $row['idx'] ?>]" id="mb_id_<?= $row['idx'] ?>" value="<?= $memId ?>">
											<input type="hidden" name="mir_chk[<?= $row['idx'] ?>]" id="mir_chk_<?= $row['idx'] ?>" value="<?= $mir_chk ?>">
											<input type="checkbox" id="chk_<?= $row['idx'] ?>" class="chk" name="chk[]" value="<?= $row['idx'] ?>">
										</td>
										<td headers="mb_list_idx" class="td_idx"><?= $from_record ?></td>
										<td headers="mb_list_lastcall" class="td_date"><?= substr($row['reg_Date'], 2, 8) ?></td>
										<td headers="mb_list_lastcall" class="td_date"><?= $last_Login ?></td>
										<td headers="mb_list_id"><a href="memberDetailView.php?idx=<?= $memIdx ?>"><?= $memId . "<br>(" . $row['mem_NickNm'] . ")" ?></a></td>
										<td headers="mb_list_open" class="td_name td_mng_s"><?= $row['mem_Tel'] ?></td>
										<td headers="mb_list_open" class="td_name td_mng_s"><?= $memCard ?></td>
										<td headers="mb_list_open" class="td_mbstat td_mng_s"><?= $mem_Sex . " (" . $mem_Seat . ")" ?></td>
										<td headers="mb_list_open" class="td_mbstat td_mng_s"><?= $memSnsChk . "<br>(" . $memOs . ")" ?></td>
										<td headers="mb_list_open" class="td_mbstat td_mng_s"><?= $row['memLvNm'] ?></td>
										<td headers="mb_list_auth" class="td_mbstat td_mng_s">
											<? if ($mem_ChCode != '') { ?>
												<a href="memberReg.php?mode=mod&id=<?= $mem_ChId ?>" class="btn btn_01"><?= $mem_ChNickNm ?></a>
											<? } else { ?>
												-
											<? } ?>
										</td>
										<td headers="mb_list_auth" class="td_mbstat td_mng_s"><?= $memChCntNum ?></td>
										<td headers="mb_list_mng" class="td_mng td_mng_s">
											<a href="../taxiSharing/taxiSharingList.php?findType=mem_Idx&findword=<?= $memIdx ?>" class="btn btn_04">생성매칭</a>
											<a href="memberDetailView.php?idx=<?= $memIdx ?>" class="btn btn_01">상세</a>
											<a href="memberReg.php?mode=mod&idx=<?= $memIdx ?>&<?= $qstr ?>&page=<?= $page ?>" class="btn btn_03">수정</a>
											<? if ($_COOKIE['du_udev']['id'] != 'admin2') { ?>
												<a href="javascript:chkDel('<?= $row['idx'] ?>')" class="btn btn_02">삭제</a>
											<? } ?>
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
					<? if ($_COOKIE['du_udev']['id'] != 'admin2') { ?>
						<a href="#ALDel" id="bt_m_a_del" class="btn btn_02">선택삭제</a>
					<? } ?>
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
		$mstmt = null;

		include "../common/inc/inc_footer.php";  //푸터 

		?>