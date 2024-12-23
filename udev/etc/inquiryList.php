<?
	$menu = "3";
	$smenu = "5";

	include "../common/inc/inc_header.php";  //헤더 

	$base_url = $PHP_SELF;

	$sql_search=" WHERE 1";

	if($findword != "")  {
		$sql_search .= " AND `{$findType}` LIKE '%{$findword}%' ";
	}

	$DB_con = db1();
	
	//전체 카운트
	$cntQuery = "";
	$cntQuery = "SELECT COUNT(idx) AS cntRow ";
	$cntQuery .= ",SUM(CASE WHEN b_State = '0' THEN 1 ELSE 0 END) AS I_CNT ";
	$cntQuery .= ",SUM(CASE WHEN b_State = '1' THEN 1 ELSE 0 END) AS F_CNT ";
	$cntQuery .= "FROM TB_ONLINE  {$sql_search} " ;
	$cntStmt = $DB_con->prepare($cntQuery);

	if ($fr_date != "" || $to_date != "" ) {
	    $cntStmt->bindValue(":fr_date",$fr_date);
	    $cntStmt->bindValue(":to_date",$to_date);
	}

	if($findword != "")  {
	    $cntStmt->bindValue(":findType",$findType);		
	    $cntStmt->bindValue(":findword",$findword );
	}

	$findType = trim($findType);
	$findword = trim($findword);

	$cntStmt->execute();
	$row = $cntStmt->fetch(PDO::FETCH_ASSOC);
	$totalCnt = $row['cntRow'];
	$I_CNT = $row['I_CNT'];
	$F_CNT = $row['F_CNT'];

	$cntStmt = null;

	$rows = 10;
	$total_page  = ceil($totalCnt / $rows);  // 전체 페이지 계산
	if ($page == "") { $page = 1; } // 페이지가 없으면 첫 페이지 (1 페이지)
	$from_record = ($page - 1) * $rows; // 시작 열을 구함


	if (!$sort1)	{
		$sort1  = "reg_Date";
		$sort2 = "DESC";
	}

	$sql_order = "order by $sort1 $sort2";

	//목록
	$query = "";
	$query = "SELECT idx, b_Part, b_MemId, b_Title, b_Name, b_Content, b_RMemId, b_RName, b_RContent, reg_Date, b_State FROM TB_ONLINE {$sql_search} {$sql_order} limit  {$from_record}, {$rows}" ;
	$stmt = $DB_con->prepare($query);

	if($findword != "")  {
	    $stmt->bindValue(":findType",$findType);		
	    $stmt->bindValue(":findword",$findword );
	}

	$findType = trim($findType);
	$findword = trim($findword);

	$stmt->execute();
	$numCnt = $stmt->rowCount();

	$qstr = "findType=".urlencode($findType)."&amp;findword=".urlencode($findword);

	include "../common/inc/inc_gnb.php";  //헤더 
	include "../common/inc/inc_menu.php";  //메뉴 

?>
<script type="text/javascript" src="<?=DU_UDEV_DIR?>/etc/js/event.js"></script>

<div id="wrapper">
    <div id="container" class="">
        <div class="container_wr">
        <h1 id="container_title">문의리스트 관리</h1>

		<div class="local_ov01 local_ov">
			<span class="btn_ov01"><span class="ov_txt">총 등록 </span><span class="ov_num"><?=number_format($totalCnt);?>건 </span>&nbsp;
			<span class="btn_ov01"><span class="ov_txt">총 답변대기 </span><span class="ov_num"><?=number_format($I_CNT);?>건 </span>&nbsp;
			<span class="btn_ov01"><span class="ov_txt">총 답변완료 </span><span class="ov_num"><?=number_format($F_CNT);?>건 </span>
		</div>
		<form class="local_sch03 local_sch"  autocomplete="off">
		<div>
			<strong>분류</strong>
			<select name="findType" id="findType">
				<option value="b_MemId" <?if($findType=="b_MemId"){?>selected<?}?>>제목</option>
			</select>
			<label for="findword" class="sound_only">검색어<strong class="sound_only"> 필수</strong></label>
			<input type="text" name="findword" id="findword" value="<?=$findword?>" size="30"  class=" frm_input">

			<input type="submit" value="검색" class="btn_submit">
			<a href="<?=$base_url?>" class="btn btn_06">새로고침</a>
		</div>
		</form>



<form name="fmemberlist" id="fmemberlist"  method="post" autocomplete="off">

<div class="tbl_head01 tbl_wrap">
    <table>
    <caption>이벤트 배너 목록</caption>
    <thead>

	<!-- 아이디, 이름, 등급, 휴대폰번호, 가입일 -->
    <tr>
        <th scope="col">순번</th>
        <th scope="col">문의분류</th>
        <th scope="col">문의자ID</th>
		<th scope="col">제목</a></th>		 
		<th scope="col">문의내용</th>
		<th scope="col">문의일자</th>
		<th scope="col">답변여부</th>
		<th scope="col">관리</th>
    </tr>
    </thead>
    <tbody>

    <?

	if($numCnt > 0)   {

		$stmt->setFetchMode(PDO::FETCH_ASSOC);

		while($row =$stmt->fetch()) {
       // $bg = 'bg'.($stmt->fetch()%2);
		    $from_record++;
        if($row['b_Disply'] == "Y") {
            $b_Disply = "노출";
        } else {
            $b_Disply = "미노출";
        }		
		$b_Part = $row['b_Part'];
		if($b_Part == 1){
			$bPart = "매칭생성";
		}else if($b_Part == 2){
			$bPart = "매칭신청";
		}else if($b_Part == 3){
			$bPart = "게시판";
		}
		$b_State = $row['b_State'];
		if($b_State == '0'){
			$bState = '-';
		}else if($b_State == '1'){
			$bState = '답변완료';
		}
    ?>
    <tr class="<?=$bg?>">
		<td><?=$from_record?></td>
		<td><?=$bPart?></td>
		<td><?=$row['b_MemId']?></td>
		<td><?=$row['b_Title']?></td>
		<td><div style="overflow:hidden;text-overflow:ellipsis;white-space:nowrap; ">
			<?if($b_State == '0'){?>
				<a href="inquiryReg.php?mode=reg&idx=<?=$row['idx']?>&<?=$qstr?>&page=<?=$page?>"><span><?=	str_replace("?","",trim($row['b_Content'])) ?></span></a>
			<?}else{?>
				<a href="inquiryReg.php?mode=mod&idx=<?=$row['idx']?>&<?=$qstr?>&page=<?=$page?>"><span><?=str_replace("?","",trim($row['b_Content'])) ?></span></a>
			<?}?></div></td>
		<td><?=$row['reg_Date']?></td>
		<td><?=$bState?></td>
		<td headers="mb_list_mng" class="td_mng td_mng_s">
<? if($_COOKIE['du_udev']['id'] != 'admin2'){?>
				<?if($b_State == '0'){?>
					<a href="inquiryReg.php?mode=reg&idx=<?=$row['idx']?>&<?=$qstr?>&page=<?=$page?>" class="btn btn_03">답변등록</a>
				<?}else{?>
					<a href="inquiryReg.php?mode=mod&idx=<?=$row['idx']?>&<?=$qstr?>&page=<?=$page?>" class="btn btn_02">답변수정</a>
				<?}?>
<? } ?>
		</td>
    </tr>
    <? 

		}
	?>   
	<? } else { ?>
	<tr>
		<td colspan="8" class="empty_table">자료가 없습니다.</td>
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

	function fvisit_submit(act)
	{
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
