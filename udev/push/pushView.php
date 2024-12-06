<?
	$menu = "10";
	$smenu = "1";

	include "../common/inc/inc_header.php";  //헤더 
	
	$DB_con = db1();

	// config
	$prev_conn["ALL"] = "전체";
	$prev_conn["1"] = "당일";
	$prev_conn["3"] = "3일";
	$prev_conn["7"] = "일주일";
	$prev_conn["30"] = "한달";


	$push_type["NOTI"] = "노티";
	$push_type["POPUP"] = "팝업";
	$push_type["SMS"] = "SMS";

	$alarm_type["SILENT"] = "무음";
	$alarm_type["BELL"] = "벨";
	$alarm_type["VIBRATE"] = "진동";
	$alarm_type["BELL+VIBRATE"] = "벨+진동";
	
	
	$titNm = "푸시발송 상세보기";

	// 상세보기 seq check
	if(!$_GET['seq'])
	{
		echo "<script>alert('등록된 내용이 없습니다.');history.back();</script>";
	}
	else
	{
		$query = "select * from TB_PUSH where seq = ".$_GET['seq']." ";
		$stmt = $DB_con->prepare($query);
		$stmt->execute();
		$row =$stmt->fetch();


		if(empty($row["img_url"]) == false)
		{
			$img = "<img src='". $row["img_url"]. "'>";
		}

	}


	$qstr = "fr_date=".urlencode($fr_date)."&amp;to_date=".urlencode($to_date)."&amp;findType=".urlencode($findType)."&amp;findword=".urlencode($findword);

	include "../common/inc/inc_gnb.php";  //헤더 
	include "../common/inc/inc_menu.php";  //메뉴 

?>

<style>
/* 예약하기  layer*/
.objects select {margin-left:10px; float:left;}
.objects input {margin-left:10px; float:left;}
.objects input[type=radio] {margin-left:10px; margin-top:2px; float:left;}
.objects input[type=checkbox] {margin-left:10px; margin-top:2px; float:left;}
.objects textarea {margin-left:10px; float:left;}
.objects span {margin-left:10px; float:left;}
.objects i {margin-left:10px; float:left;}
.objects div {margin-left:10px; float:left;}
.objects img {margin-left:10px; float:left;}
/* 예약라기 기본 선택 */
.delay {display:block;}


.PT5 {line-height:35px;}
</style>

<div id="wrapper">

    <div id="container" class="">
        <h1 id="container_title"><?=$titNm?></h1>
        <div class="container_wr">
		<form name="fmember" id="fmember" action="pushProc.php" onsubmit="return fmember_submit(this);" method="post" enctype="multipart/form-data" autocomplete="off">
		<input type="hidden" name="mode" id="mode" value="del">	
		<input type="hidden" name="idx" id="idx" value="<?=$seq?>">
		<input type="hidden" name="qstr" id="qstr"  value="<?=$qstr?>">
		<input type="hidden" name="page"  id="page"  value="<?=$page?>">

		<div class="tbl_frm01 tbl_wrap">
			<table>
			<caption><?=$titNm?></caption>
			<colgroup>
				<col class="grid_4">
				<col>
				<col class="grid_4">
				<col>
			</colgroup>
			<tbody>
			<tr height="45">
				<th align="center">푸시방식</th>
				<td class="objects">
					<label><span><?= $push_type[trim($row['push_type'])]?></span></label>
				</td>
			</tr>
			<tr height="45">
				<th align="center">알람설정</th>
				<td class="objects">
					<label><span><?= $alarm_type[trim($row['alarm_type'])]?></label>
				</td>
			</tr>		
			<tr height="45">
				<th align="center">푸시바로가기</th>
				<td class="objects">
					<?
					$push_shortcut = trim($row["push_shortcut"]);

					if($push_shortcut == "0"){
						$push_shortcut_txt = "메인";
					}else if($push_shortcut == "1"){
						$push_shortcut_txt = "실시간제보정보";
					}else if($push_shortcut == "4"){
						$push_shortcut_txt = "cctv-지도보기";
					}else if($push_shortcut == "5"){
						$push_shortcut_txt = "교통정보-지도보기";
					}else if($push_shortcut == "6"){
						$push_shortcut_txt = "car-서비스센터";
					}else if($push_shortcut == "10"){
						$push_shortcut_txt = "CAST";
					}else if($push_shortcut == "11"){
						$push_shortcut_txt = "매거진";
					}else if($push_shortcut == "12"){
						$push_shortcut_txt = "공동구매";
					}else if($push_shortcut == "13"){
						$push_shortcut_txt = "게시판";
					}else if($push_shortcut == "recom"){
						$push_shortcut_txt = "추천정보";
					}else if($push_shortcut == "FUNC001"){
						$push_shortcut_txt = "기능->대리기사보기";
					}else if($push_shortcut == "FUNC002"){
						$push_shortcut_txt = "기능->교통현황";
					}else if($push_shortcut == "FUNC003"){
						$push_shortcut_txt = "기능->CCTV";
					}else if($push_shortcut == "FUNC004"){
						$push_shortcut_txt = "기능->날씨";
					}else if($push_shortcut == "FUNC005"){
						$push_shortcut_txt = "기능->운세";
					}else if($push_shortcut == "FUNC006"){
						$push_shortcut_txt = "기능->게임";
					}else if($push_shortcut == "FUNC007"){
						$push_shortcut_txt = "기능->지점등록";
					}else if($push_shortcut == "FUNC008"){
						$push_shortcut_txt = "기능->과거이력";
					}else if($push_shortcut == "FUNC009"){
						$push_shortcut_txt = "기능->반경추가";
					}else if($push_shortcut == "FUNC010"){
						$push_shortcut_txt = "기능->관심지역";
					}else if($push_shortcut == "FUNC011"){
						$push_shortcut_txt = "기능->콜서비스";
					}else if($push_shortcut == "FUNC012"){
						$push_shortcut_txt = "기능->채팅";
					}else if($push_shortcut == "FUNC013"){
						$push_shortcut_txt = "기능->탁송";
					}else if($push_shortcut == "FUNC014"){
						$push_shortcut_txt = "기능->퀵서비스";
					}else if($push_shortcut == "FUNC015"){
						$push_shortcut_txt = "기능->긴급견인";
					}else if($push_shortcut == "FUNC016"){
						$push_shortcut_txt = "기능->만화";
					}
					echo $push_shortcut_txt;
					?>
				</td>
			</tr>
			<style>
			
			</style>
			<tr height="45">
				<th align="center">URL 링크</th>
				<td class="objects"><?= $row["link_url"]?>	</td>
			</tr>
			<tr height="45">
				<th align="center">이미지<br>( 1080px / 500px )</th>
				<td class="objects"><?= $img ?></td>
			</tr>
			<tr height="45">
				<th align="center">예약하기</th>
				<td class="objects">
					<?
					if(trim($row['delay']) == "Y")
					{
						echo $row['delay_send_time'];
					}
					?>
				</td>
			</tr>
			<tr height="45">
				<th align="center">접속일자</th>
				<td class="objects"><?=$prev_conn[trim($row["prev_conn"])]?></td>
			</tr>
			<tr height="45">
				<th align="center">테스트 선택</th>
				<td class="objects">
					<?
					if(trim($row['test']) == "Y") 	echo "테스트 발송";
					else echo "일반발송";
					?>
				</td>
			</tr>
			<tr height="100">
				<th align="center">알림내용</th>
				<td class="objects">
					<?=nl2br(stripslashes($row["contents"]))?>
				</td>
			</tr>
		</table>

		<div class="btn_fixed_top">
			<a href="pushList.php?<?=$qstr?>&page=<?=$page?>" class="btn btn_02">목록</a>
			<input type="submit" value="삭제" class="btn_submit btn" accesskey='s'>
		</div>
		</form>


		<script>
		function fmember_submit(f)
		{
			if (!confirm("해당 내용을 삭제하시겠습니까?")){
				return false;
			}

			return true;
		}
		</script>

	</div>   
</div>   	


<script>
	$(function(){
		$("#send_date").datepicker({ changeMonth: true, changeYear: true, dateFormat: "yy-mm-dd", showButtonPanel: true,  minDate:"-0d"});
	});

</script>



<?
	dbClose($DB_con);
	$stmt = null;
	$meInfoStmt = null;
	$mEtcStmt = null;
	$mstmt = null;

	include "../common/inc/inc_footer.php";  //푸터 
	 
?>
