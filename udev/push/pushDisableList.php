<?
	$menu = "10";
	$smenu = "2";

	include "../common/inc/inc_header.php";  //헤더 

	$base_url = $PHP_SELF;
	
	/* 검색조건 확인 */
	$sql_search = "where 1=1 ";

	if(empty($findType) == false)
	{
		$sql_search .= "AND ".$findType." ='1' ";
	}
	else
	{
		$sql_search .= " and ( mem.mem_NPush='1' or mem.mem_MPush = '1') ";
	}

	if(empty($mem_Tel) == false)
	{
		$sql_search .= " and  mem.mem_Tel like '%".$mem_Tel."% ";
	}


	$DB_con = db1();

	//전체 카운트
	$cntQuery = "";
	$cntQuery = "SELECT COUNT(mem.idx)  AS cntRow FROM TB_MEMBERS as mem left outer join TB_MEMBERS_INFO as mem_info on mem.idx = mem_info.mem_Idx  {$sql_search} " ;
	$cntStmt = $DB_con->prepare($cntQuery);

	$cntStmt->execute();
	$row = $cntStmt->fetch(PDO::FETCH_ASSOC);
	$totalCnt = $row['cntRow'];


	$rows = 10;
	$total_page  = ceil($totalCnt / $rows);  // 전체 페이지 계산
	if ($page == "") { $page = 1; } // 페이지가 없으면 첫 페이지 (1 페이지)
	$from_record = ($page - 1) * $rows; // 시작 열을 구함


	if (!$sort1)	{
		$sort1  = " mem.idx ";
		$sort2 = "DESC";
	}

	$sql_order = "order by $sort1 $sort2";

	//목록
	$query ="select mem.mem_Id, mem.mem_NickNm, mem.mem_Tel, mem.mem_NPush, mem.mem_MPush, mem.reg_Date, mem.b_Disply, mem_info.login_Date, mem_info.leaved_Date from TB_MEMBERS as mem left outer join TB_MEMBERS_INFO as mem_info on mem.idx = mem_info.mem_Idx  {$sql_search} {$sql_order} limit  {$from_record}, {$rows}";
	$stmt = $DB_con->prepare($query);

	$stmt->execute();
	$numCnt = $stmt->rowCount();

	$qstr = "fr_date=".urlencode($fr_date)."&amp;to_date=".urlencode($to_date)."&amp;findType=".urlencode($findType)."&amp;findOs=".urlencode($findOs)."&amp;findword=".urlencode($findword);
	
	include "../common/inc/inc_gnb.php";  //헤더 
	include "../common/inc/inc_menu.php";  //메뉴 

	/*
	// mem_NPush : 회원일 경우, 이벤트 공지 알림유무 ( 0 : ON, 1 : OFF )
	// mem_NPush : 관리자일 경우, 중요처리건 알림 ( 0 : ON, 1 : OFF )
	// (중요처리건 : 취소처리필요건, 완료확인필요건, 신규문의, 환전신청)
	*/

?>
<script type="text/javascript" src="<?=DU_UDEV_DIR?>/member/js/member.js"></script>

<div id="wrapper">
    <div id="container" class="">
        <div class="container_wr">
        <h1 id="container_title">푸시(SMS)수신거부 리스트</h1>

		<div class="local_ov01 local_ov">
			<span class="btn_ov01"><span class="ov_txt">수신거부 </span><span class="ov_num"><?=number_format($totalCnt);?>건 </span>
		</div>

		<!--
		검색구분 : 이벤트 공지 알림 ( 0 : ON, 1 : OFF )
		검색구분 : 매칭 및 쪽지 알림 ( 0 : ON, 1 : OFF )
		-->
		<form class="local_sch03 local_sch"  autocomplete="off">
		<div>
    		<strong>검&nbsp;색&nbsp;&nbsp;구&nbsp;분&nbsp;</strong>
    		<select name="findType" id="findType">
    			<option value="" <?if($findType==""){?>selected<?}?>>전체</option>
				<option value="mem_NPush" <?if($findType=="mem_NPush"){?>selected<?}?>>이벤트 공지</option>
    			<option value="mem_MPush" <?if($findType=="mem_MPush"){?>selected<?}?>>매칭 및 쪽지</option>
    		</select>
		</div>
		
		<div>
    		<strong>수신거부번호</strong>    		
    		<label for="findword" class="sound_only">검색어<strong class="sound_only"> 필수</strong></label>
    		<input type="text" name="mem_Tel" id="mem_Tel" value="<?=$mem_Tel?>" class=" frm_input">		
			<input type="submit" value="검색" class="btn_submit">

			<a href="<?=$base_url?>" class="btn btn_06">새로고침</a>
		</div>
		</form>


<!--
<div class="local_desc01 local_desc">
    <p>
        회원자료 삭제 시 다른 회원이 기존 회원아이디를 사용하지 못하도록 회원아이디, 이름은 삭제하지 않고 영구 보관합니다.
    </p>
</div>
-->

<nav class="pg_wrap">
	<?=get_apaging($rows, $page, $total_page, "$_SERVER[PHP_SELF]?$qstr"); ?>
</nav>

<form name="fmemberlist" id="fmemberlist"  method="post" autocomplete="off">

<div class="tbl_head01 tbl_wrap">
    <table>
    <caption>푸시관리 목록</caption>
    <thead>

	<!-- 아이디, 이름, 등급, 휴대폰번호, 가입일 -->
    <tr>
		<th scope="col" id="mb_list_idx" style="width:5%">순번</th>
		<th scope="col" id="mb_list_mailr" style="width:15%">수신거부구분</th>
		<th scope="col" id="mb_list_mailr" style="width:15%">가입일자</th>
		<th scope="col" id="mb_list_mailr" style="width:15%">최근접속</th>
        <th scope="col" id="mb_list_mailr" style="width:20%">휴대폰번호</th>
        <th scope="col" id="mb_list_id" style="width:15%">닉네임</th>
		<th scope="col" id="mb_list_idx" style="width:20%">회원상태</th>
    </tr>
    </thead>
    <tbody>

    <?

	if($numCnt > 0)   {

		$stmt->setFetchMode(PDO::FETCH_ASSOC);

		while($row =$stmt->fetch()) {
       // $bg = 'bg'.($stmt->fetch()%2);

		$from_record++;
		
		$deny_text = "";
		if($row['mem_NPush'] == '1') 
		{
			$deny_text .= "이벤트 공지 : 수신거부 ";
		}
		if($row['mem_MPush'] == '1') 
		{
			if(empty($deny_text) == false)
			{
				$deny_text .= "<br> ";
			}

			$deny_text .= "매칭 및 쪽지 : 수신거부 ";
		}

		switch($row['b_Disply'])
		{
			case "N":
				$status = "가입";
			break;
			case "Y":
				$status = "탈퇴<br>(".$row['leaved_Date'].")";				
			break;
			case "D":
				$status = "휴먼회원";
			break;
		}
		
    ?>

    <tr class="<?=$bg?>">
		<td headers="mb_list_idx" class="td_idx"><?=$from_record?></td>
		<td headers="mb_list_idx" class="td_idx"><?=$deny_text?></td>
		<td headers="mb_list_idx" class="td_idx"><?=$row["reg_Date"]?></td>
		<td headers="mb_list_idx" class="td_idx"><?=$row["login_Date"]?></td>		
		<td headers="mb_list_lastcall" class="td_date"><a href="/udev/member/memberReg.php?mode=mod&id=<?=$row["mem_Id"]?>"><?=$row["mem_Tel"]?></a></td>
		<td headers="mb_list_idx" class="td_idx"><a href="/udev/member/memberReg.php?mode=mod&id=<?=$row["mem_Id"]?>"><?=$row["mem_NickNm"]?></a></td>
		<td headers="mb_list_open" class="td_name td_mng_s"><?=$status?></td>
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

</form>
<nav class="pg_wrap">
	<?=get_apaging($rows, $page, $total_page, "$_SERVER[PHP_SELF]?$qstr"); ?>
</nav>

<script>
	$(function(){
		$("#fr_date, #to_date").datepicker({ changeMonth: true, changeYear: true, dateFormat: "yy-mm-dd", showButtonPanel: true, yearRange: "c-99:c+99", maxDate: "+0d" });
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
