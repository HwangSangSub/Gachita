<?
$menu = "1";
$smenu = "2";

include "../common/inc/inc_header.php";  //헤더 

$titNm = "기타 환경 설정";

$DB_con = db1();

// 기타 설정 조회
$query = "SELECT idx, con_ImgUp, con_TxtFilter, con_Agree, con_Privacy FROM TB_CONFIG_ETC LIMIT 1";
$stmt = $DB_con->prepare($query);
$stmt->execute();

$row = $stmt->fetch(PDO::FETCH_ASSOC);

$idx = trim($row['idx']);
$con_ImgUp = trim($row['con_ImgUp']);
$con_TxtFilter = trim($row['con_TxtFilter']);
$con_Agree = trim($row['con_Agree']);
$con_Privacy = trim($row['con_Privacy']);

// 즐겨찾는 장소 상단 이미지 조회
$bmQuery = "SELECT bm_Img_1, bm_Img_2, bm_Img_3 FROM TB_CONFIG_BOOKMARK LIMIT 1";
$bmStmt = $DB_con->prepare($bmQuery);
$bmStmt->execute();

$bmRow = $bmStmt->fetch(PDO::FETCH_ASSOC);

$bm_Img_1 = trim($bmRow['bm_Img_1']);
$bm_Img_2 = trim($bmRow['bm_Img_2']);
$bm_Img_3 = trim($bmRow['bm_Img_3']);


// 공지사항 상단 고정 게시판 조회
$selTopNoticeQuery = "SELECT idx, b_Title, t_Sort FROM TB_BOARD WHERE b_Idx = '1' AND t_Disply = 'Y' AND t_Sort IN (1,2,3) ORDER BY t_Sort";
$selTopNoticeStmt = $DB_con->prepare($selTopNoticeQuery);
$selTopNoticeStmt->execute();
while ($selTopNoticeRow = $selTopNoticeStmt->fetch(PDO::FETCH_ASSOC)) {
	$noticeIdx = $selTopNoticeRow['idx'];
	$noticeTitle = $selTopNoticeRow['b_Title'];
	$noticeSort = $selTopNoticeRow['t_Sort'];
	if ($noticeSort == 1) {
		if ($noticeIdx == "") {
			$conTopNotice1 = "";
			$conTopNotice1Name = "";
		} else {
			$conTopNotice1 = $noticeIdx;
			$conTopNotice1Name = $noticeTitle;
		}
	} else if ($noticeSort == 2) {
		if ($noticeIdx == "") {
			$conTopNotice2 = "";
			$conTopNotice2Name = "";
		} else {
			$conTopNotice2 = $noticeIdx;
			$conTopNotice2Name = $noticeTitle;
		}
	} else if ($noticeSort == 3) {
		if ($noticeIdx == "") {
			$conTopNotice3 = "";
			$conTopNotice3Name = "";
		} else {
			$conTopNotice3 = $noticeIdx;
			$conTopNotice3Name = $noticeTitle;
		}
	}
}
// 이벤트 상단 고정 게시판 조회
$selTopEventQuery = "SELECT idx, event_Title, event_Tsort FROM TB_EVENT WHERE event_Tdisply = 'Y' AND event_Tsort IN (1,2,3)  ORDER BY event_Tsort";
$selTopEventStmt = $DB_con->prepare($selTopEventQuery);
$selTopEventStmt->execute();
while ($selTopEventRow = $selTopEventStmt->fetch(PDO::FETCH_ASSOC)) {
	$eventIdx = $selTopEventRow['idx'];
	$eventTitle = $selTopEventRow['event_Title'];
	$eventSort = $selTopEventRow['event_Tsort'];
	if ($eventSort == 1) {
		if ($eventIdx == "") {
			$conTopEvent1 = "";
			$conTopEvent1Name = "";
		} else {
			$conTopEvent1 = $eventIdx;
			$conTopEvent1Name = $eventTitle;
		}
	} else if ($eventSort == 2) {
		if ($eventIdx == "") {
			$conTopEvent2 = "";
			$conTopEvent2Name = "";
		} else {
			$conTopEvent2 = $eventIdx;
			$conTopEvent2Name = $eventTitle;
		}
	} else if ($eventSort == 3) {
		if ($eventIdx == "") {
			$conTopEvent3 = "";
			$conTopEvent3Name = "";
		} else {
			$conTopEvent3 = $eventIdx;
			$conTopEvent3Name = $eventTitle;
		}
	}
	$eventNum++;
}

if ($idx == "") {
	$mode = "reg";
} else {
	$mode = "mod";
}

dbClose($DB_con);
$stmt = null;

include "../common/inc/inc_gnb.php";  //헤더 
include "../common/inc/inc_menu.php";  //메뉴 

?>

<div id="wrapper">

	<div id="container" class="">
		<h1 id="container_title"><?= $titNm ?></h1>
		<div class="container_wr">
			<form name="fmember" id="fmember" action="configEtcProc.php" onsubmit="return f_submit(this);" method="post" enctype="multipart/form-data" autocomplete="off">
				<input type="hidden" name="mode" id="mode" value="<?= $mode ?>">
				<input type="hidden" name="idx" id="idx" value="<?= $idx ?>">

				<div class="tbl_frm01 tbl_wrap">
					<table>
						<caption><?= $titNm ?></caption>
						<colgroup>
							<col>
							<col>
							<col>
						</colgroup>
						<tbody>
							<tr>
								<th scope="row" colspan="2"><label for="bmImg1">즐겨찾는 상단 배너 1번 이미지</label></th>
								<td>
									<span class="frm_info">이미지 크기는 <strong>넓이 1000픽셀 높이 790픽셀</strong>로 해주세요.</span>
									<input type="file" name="bmImg1" id="bmImg1">
									<?
									//BLOB 파일 형태로 저장된 이미지 파일 출력되도록 ------------------- 2019.02.15
									if ($bm_Img_1) {
									?>
										<img src="/data/bookmark/photo.php?id=<? echo $bm_Img_1 ?>" style="height:60px">
										<input type="checkbox" id="del_bmImg1" name="del_bmImg1" value="1">삭제
									<?
									}

									?>

									<? if ($mode == "mod") { ?>
										<input type="hidden" name="bookmarkImg1" value="<?= $bm_Img_1 ?>">
									<? } ?>
								</td>
							</tr>
							<tr>
								<th scope="row" colspan="2"><label for="bmImg2">즐겨찾는 상단 배너 2번 이미지</label></th>
								<td>
									<span class="frm_info">이미지 크기는 <strong>넓이 1000픽셀 높이 790픽셀</strong>로 해주세요.</span>
									<input type="file" name="bmImg2" id="bmImg2">
									<?
									//BLOB 파일 형태로 저장된 이미지 파일 출력되도록 ------------------- 2019.02.15
									if ($bm_Img_2) {
									?>
										<img src="/data/bookmark/photo.php?id=<? echo $bm_Img_2 ?>" style="height:60px">
										<input type="checkbox" id="del_bmImg2" name="del_bmImg2" value="1">삭제
									<?
									}

									?>

									<? if ($mode == "mod") { ?>
										<input type="hidden" name="bookmarkImg2" value="<?= $bm_Img_2 ?>">
									<? } ?>
								</td>
							</tr>
							<tr>
								<th scope="row" colspan="2"><label for="bmImg3">즐겨찾는 상단 배너 3번 이미지</label></th>
								<td>
									<span class="frm_info">이미지 크기는 <strong>넓이 1000픽셀 높이 790픽셀</strong>로 해주세요.</span>
									<input type="file" name="bmImg3" id="bmImg3">
									<?
									//BLOB 파일 형태로 저장된 이미지 파일 출력되도록 ------------------- 2019.02.15
									if ($bm_Img_3) {
									?>
										<img src="/data/bookmark/photo.php?id=<? echo $bm_Img_3 ?>" style="height:60px">
										<input type="checkbox" id="del_bmImg3" name="del_bmImg3" value="1">삭제
									<?
									}

									?>

									<? if ($mode == "mod") { ?>
										<input type="hidden" name="bookmarkImg3" value="<?= $bm_Img_3 ?>">
									<? } ?>
								</td>
							</tr>
							<tr>
								<th scope="row" rowspan="3"><label for="conTopNotice">공지사항 상단고정</label></th>
								<th style="width:160px;"><span>1번 고정 공지사항</span></th>
								<td>
									<input type="hidden" name="conTopNotice1" id="conTopNotice1" value="<?= $conTopNotice1 ?>" />
									<input type="text" name="conTopNotice1Name" id="conTopNotice1Name" class="frm_input" size="100" onclick="window.open('selectBoard.php?type=notice&sort=1','공지사항 선택','width=800,height=800,top=100,left=100');" value="<?= $conTopNotice1Name ?>" readonly>
									<?
									if ($conTopNotice1 != "") {
									?>
										<a id="del_conTopNotice1" href="configEtcProc.php?noticeIdx=<?= $conTopNotice1 ?>&mode=del&type=notice" class="btn btn_04">삭제</a>
									<?
									}
									?>
								</td>
							</tr>
							<tr>
								<th style="width:160px;"><span>2번 고정 공지사항</span></th>
								<td>
									<input type="hidden" name="conTopNotice2" id="conTopNotice2" value="<?= $conTopNotice2 ?>" />
									<input type="text" name="conTopNotice2Name" id="conTopNotice2Name" class="frm_input" size="100" onclick="window.open('selectBoard.php?type=notice&sort=2','공지사항 선택','width=800,height=800,top=100,left=100');" value="<?= $conTopNotice2Name ?>" readonly>
									<?
									if ($conTopNotice2 != "") {
									?>
										<a id="del_conTopNotice2" href="configEtcProc.php?noticeIdx=<?= $conTopNotice2 ?>&mode=del&type=notice" class="btn btn_04">삭제</a>
									<?
									}
									?>
								</td>
							</tr>
							<tr>
								<th style="width:160px;"><span>3번 고정 공지사항</span></th>
								<td>
									<input type="hidden" name="conTopNotice3" id="conTopNotice3" value="<?= $conTopNotice3 ?>" />
									<input type="text" name="conTopNotice3Name" id="conTopNotice3Name" class="frm_input" size="100" onclick="window.open('selectBoard.php?type=notice&sort=3','공지사항 선택','width=800,height=800,top=100,left=100');" value="<?= $conTopNotice3Name ?>" readonly>
									<?
									if ($conTopNotice3 != "") {
									?>
										<a id="del_conTopNotice3" href="configEtcProc.php?noticeIdx=<?= $conTopNotice3 ?>&mode=del&type=notice" class="btn btn_04">삭제</a>
									<?
									}
									?>
								</td>
							</tr>
							<tr>
								<th scope="row" rowspan="3"><label for="conTopEvent">이벤트 상단고정</label></th>
								<th style="width:160px;"><span>1번 고정 이벤트</span></th>
								<td>
									<input type="hidden" name="conTopEvent1" id="conTopEvent1" value="<?= $conTopEvent1 ?>" />
									<input type="text" name="conTopEvent1Name" id="conTopEvent1Name" class="frm_input" size="100" onclick="window.open('selectBoard.php?type=event&sort=1','이벤트 선택','width=800,height=800,top=100,left=100');" value="<?= $conTopEvent1Name ?>" readonly>
									<?
									if ($conTopEvent1 != "") {
									?>
										<a id="del_conTopEvent1" href="configEtcProc.php?eventIdx=<?= $conTopEvent1 ?>&mode=del&type=event" class="btn btn_04">삭제</a>
									<?
									}
									?>
								</td>
							</tr>
							<tr>
								<th style="width:160px;"><span>2번 고정 이벤트</span></th>
								<td>
									<input type="hidden" name="conTopEvent2" id="conTopEvent2" value="<?= $conTopEvent2 ?>" />
									<input type="text" name="conTopEvent2Name" id="conTopEvent2Name" class="frm_input" size="100" onclick="window.open('selectBoard.php?type=event&sort=2','이벤트 선택','width=800,height=800,top=100,left=100');" value="<?= $conTopEvent2Name ?>" readonly>
									<?
									if ($conTopEvent2 != "") {
									?>
										<a id="del_conTopEvent2" href="configEtcProc.php?eventIdx=<?= $conTopEvent2 ?>&mode=del&type=event" class="btn btn_04">삭제</a>
									<?
									}
									?>
								</td>
							</tr>
							<tr>
								<th style="width:160px;"><span>3번 고정 이벤트</span></th>
								<td>
									<input type="hidden" name="conTopEvent3" id="conTopEvent3" value="<?= $conTopEvent3 ?>" />
									<input type="text" name="conTopEvent3Name" id="conTopEvent3Name" class="frm_input" size="100" onclick="window.open('selectBoard.php?type=event&sort=3','이벤트 선택','width=800,height=800,top=100,left=100');" value="<?= $conTopEvent3Name ?>" readonly>
									<?
									if ($conTopEvent3 != "") {
									?>
										<a id="del_conTopEvent3" href="configEtcProc.php?eventIdx=<?= $conTopEvent3 ?>&mode=del&type=event" class="btn btn_04">삭제</a>
									<?
									}
									?>
								</td>
							</tr>
							<!-- <tr>
								<th scope="row" colspan="2"><label for="conImgUp">이미지 업로드 확장자</label></th>
								<td> <input type="text" name="conImgUp" id="conImgUp" class="frm_input" size="50" maxlength="20" value="<?= $con_ImgUp ?>"></td>
							</tr>
							<tr>
								<th scope="row" colspan="2"><label for="conTxtFilter">단어 필터링</label></th>
								<td><textarea name="conTxtFilter" id="conTxtFilter"><?= $con_TxtFilter ?></textarea></td>
							</tr>
							<tr>
								<th scope="row" colspan="2"><label for="conAgree">회원가입약관</label></th>
								<td><textarea name="conAgree" id="conAgree"><?= $con_Agree ?></textarea></td>
							</tr>
							<tr>
								<th scope="row" colspan="2"><label for="conPrivacy">개인정보취급방침</label></th>
								<td><textarea name="conPrivacy" id="conPrivacy"><?= $con_Privacy ?></textarea></td>
							</tr> -->

						</tbody>
					</table>
				</div>

				<div class="btn_fixed_top">
					<input type="submit" value="확인" class="btn_submit btn" accesskey='s'>
				</div>
			</form>


			<script>
				function conTopNoticeDel(num) {
					$('#del_conTopNotice' + num).css("display", "none");
					$('#conTopNotice' + num).val('');
					$('#conTopNotice' + num + 'Name').val('');
				}

				function conTopEventDel(num) {
					$('#del_conTopEvent' + num).css("display", "none");
					$('#conTopEvent' + num).val('');
					$('#conTopEvent' + num + 'Name').val('');
				}

				function f_submit(f) {
					return true;
				}
			</script>

		</div>

		<? include "../common/inc/inc_footer.php";  //푸터 
		?>