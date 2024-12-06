<?
	$menu = "10";
	$smenu = "1";

	include "../common/inc/inc_header.php";  //헤더 
	
	$DB_con = db1();
	
	if($mode == "regAll")
	{
		$titNm = "푸시일괄발송";
	}
	else if($mode == "reg")
	{
		$titNm = "푸시발송";
	}


	$qstr = "fr_date=".urlencode($fr_date)."&amp;to_date=".urlencode($to_date)."&amp;findType=".urlencode($findType)."&amp;findword=".urlencode($findword);

	include "../common/inc/inc_gnb.php";  //헤더 
	include "../common/inc/inc_menu.php";  //메뉴 

?>
<script>
//접속일자 기준 회원수 구하기
function getUserCount() {
	$.ajax({
			url: "./get_user_count.php",
			data:"prev_conn="+$("#prev_conn").val(),
			type: "post",
			dataType : "json",
			success: function( data ) {
				$("#user_count").text(data);
			},
			error: function( xhr, status ) { 
				$("#progressbar").hide(); alert("웹서버의 응답이 없습니다. 다시 시도하여 주십시오."); 
			},
			complete: function( xhr, status ) { }
	});
}


function delayCheck() {
	if($("#setDelay").is(":checked")) {
		$(".delay").show();
	} else {
		$(".delay").hide();
		$("#send_date").val("");
	}
}

// loading시 실행
window.onload=function(){ 
	getUserCount();
}

</script>
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
		<input type="hidden" name="mode" id="mode" value="<?=$mode?>">	
		<input type="hidden" name="idx" id="idx" value="<?=$idx?>">
		<input type="hidden" name="mem_Id" id="mem_Id" value="<?=$mem_Id?>">
		<input type="hidden" name="qstr" id="qstr"  value="<?=$qstr?>">
		<input type="hidden" name="page"  id="page"  value="<?=$page?>">

		<div class="tbl_frm01 tbl_wrap">
			<table>
			<caption><?=$titNm?></caption>
			<colgroup>
				<col class="grid_4">
				<col>
			</colgroup>
			<tbody>
			<tr height="45">
				<th align="center">푸시방식</th>
				<td class="objects">
					<label><input type="radio" name="push_type" value="NOTI" checked> <span>노티 &nbsp; &nbsp; </span></label>
					<label><input type="radio" name="push_type" value="POPUP"> <span>팝업 &nbsp; &nbsp; </span></label>
					<label><input type="radio" name="push_type" value="SMS"> <span>SMS &nbsp; &nbsp; </span></label>
				</td>
			</tr>
			<tr height="45">
				<th align="center">알람설정</th>
				<td class="objects">
					<label><input type="radio" name="alarm_type" value="SILENT"> <span>무음 &nbsp; &nbsp; </span></label>
					<label><input type="radio" name="alarm_type" value="BELL"> <span>벨 &nbsp; &nbsp; </span></label>
					<label><input type="radio" name="alarm_type" value="VIBRATE" checked> <span>진동 &nbsp; &nbsp; </span></label>
					<label><input type="radio" name="alarm_type" value="BELL+VIBRATE"> <span>벨+진동</span></label>
				</td>
			</tr>		
			<tr height="45">
				<th align="center">푸시바로가기</th>
				<td class="objects">
					<select name="push_shortcut" id="push_shortcut" class="selectbox">
						<option value="0">메인</option>
						<!--
						<option value="1">실시간제보정보</option>
						<option value="4">cctv-지도보기</option>
						<option value="5">교통정보-지도보기</option>
						<option value="6">car-서비스센터</option>
						<option value="10">CAST</option>
						<option value="11">매거진</option>
						<option value="12">공동구매</option>
						<option value="13" selected>게시판</option>
						<option value="recom">추천정보</option>
						<option value="FUNC001">기능->대리기사보기</option>
						<option value="FUNC002">기능->교통현황</option>
						<option value="FUNC003">기능->CCTV</option>
						<option value="FUNC004">기능->날씨</option>
						<option value="FUNC005">기능->운세</option>
						<option value="FUNC006">기능->게임</option>
						<option value="FUNC007">기능->지점등록</option>
						<option value="FUNC008">기능->과거이력</option>
						<option value="FUNC009">기능->반경추가</option>
						<option value="FUNC010">기능->관심지역</option>
						<option value="FUNC011">기능->콜서비스</option>
						<option value="FUNC012">기능->채팅</option>
						<option value="FUNC013">기능->탁송</option>
						<option value="FUNC014">기능->퀵서비스</option>
						<option value="FUNC015">기능->긴급견인</option>
						<option value="FUNC016">기능->만화</option>
						-->
					</select>
				</td>
			</tr>
			<style>
			
			</style>
			<tr height="45">
				<th align="center">URL 링크</th>
				<td class="objects">
					<input type="text" class="frm_input" name="link_url" id="link_url" style="width:95%; color:blue;" placeholder="http://  또는 market://  등">
				</td>
			</tr>
			<tr height="45">
				<th align="center">이미지<br>( 1080px / 500px )</th>
				<td class="objects"><input type="file" name="att_file" id="att_file" class="input" style="width:95%"></td>
			</tr>
			<tr height="45">
				<th align="center">예약하기</th>
				<td class="objects">
					<div class="PT5"><input type="checkbox" name="setDelay" id="setDelay" value="Y" onclick="delayCheck()" checked></div>
					<input type="text" name="send_date" id="send_date" class="frm_input input date delay" style="width:100px" readonly>



					<span class="delay">&nbsp;</span>
					<select name="send_hour" id="send_hour" class="selectbox delay">
					<?
						for($i=0; $i<24; $i++) {
							$h = str_pad($i, 2, "0", STR_PAD_LEFT);
							echo "<option value='". $h. "'>". $h. "시</option>\n";
						}
					?>
					</select>
					<span class="PT5 delay">:</span>
					
					<select name="send_minute" id="send_minute" class="selectbox delay">
					<?
						for($i=0; $i<59; $i=$i+5) {
							$m = str_pad($i, 2, "0", STR_PAD_LEFT);
							echo "<option value='". $m. "'>". $m. "분</option>\n";
						}
					?>
					</select>
				</td>
			</tr>
			<tr height="45">
				<th align="center">접속일자</th>
				<td class="objects">
					<select name="prev_conn" id="prev_conn" class="selectbox" onchange="getUserCount()">
						<option value="ALL">전체</option>
						<option value="1">당일</option>
						<option value="3">3일</option>
						<option value="7">일주일</option>
						<option value="30">한달</option>
					</select>

					<span class="PT5" >&nbsp; &nbsp; &nbsp; 회원수 &nbsp;:</span><span id="user_count" class="PT5"></span>
				</td>
			</tr>
			<tr height="45">
				<th align="center">테스트 선택</th>
				<td class="objects">
					<label><input type="radio" name="test" value="Y"> <span>테스트 발송 &nbsp; &nbsp; </span></label>
					<label><input type="radio" name="test" value="N" checked> <span>일반 발송 &nbsp; &nbsp; </span></label>
				</td>
			</tr>
			<tr height="100">
				<th align="center">알림내용</th>
				<td class="objects">
					<textarea class="textarea placeholder" name="contents" id="contents" maxlength="1000" style="height:70px" placeholder="내용을 입력하세요"></textarea>
				</td>
			</tr>
		</table>

		<div class="btn_fixed_top">
			<a href="pushList.php?<?=$qstr?>&page=<?=$page?>" class="btn btn_02">목록</a>
			<input type="submit" value="확인" class="btn_submit btn" accesskey='s'>
		</div>
		</form>


		<script>
		function fmember_submit(f)
		{
			if (!f.mb_img.value.match(/\.(gif|jpe?g|png)$/i) && f.mb_img.value) {
				alert('회원이미지는 이미지 파일만 가능합니다.');
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
