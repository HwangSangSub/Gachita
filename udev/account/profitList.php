<?	
	$menu = "7";
	$smenu = "1";

	include "../common/inc/inc_header.php";  //헤더 

	$base_url = $PHP_SELF;

	$sql_search=" WHERE 1";

	if ($ordNo != "" ) {
		$findType = "taxi_OrdNo";
		$findword = $ordNo;
	}

	if ($fr_date != "" || $to_date != "" ) {
		//$sql_search.=" AND (taxi_SDate between ':fr_date' AND ':to_date')";
		$sql_search.=" AND (DATE_FORMAT(reg_Date,'%Y-%m-%d') >= :fr_date AND DATE_FORMAT(reg_Date,'%Y-%m-%d') <= :to_date)";
	}

	if($findword != "")  {
		$sql_search .= " AND `{$findType}` LIKE '%{$findword}%' ";
	}
	
	$DB_con = db1();
	
	//전체 총수익
	$sumQuery = "";
	$sumQuery = "SELECT SUM(taxi_OrdSPoint) AS sumRow FROM TB_PROFIT_POINT {$sql_search}" ;

	$sumStmt = $DB_con->prepare($sumQuery);
	if ($fr_date != "" || $to_date != "" ) {
		$sumStmt->bindparam(":fr_date",$fr_date);
		$sumStmt->bindparam(":to_date",$to_date);
	}

	$fr_date = trim($fr_date);
	$to_date = trim($to_date);
	$sumStmt->execute();
	$srow = $sumStmt->fetch(PDO::FETCH_ASSOC);
	$totalPrice = $srow['sumRow'];
	$sumStmt = null;


	//전체 카운트
	$cntQuery = "";
	$cntQuery = "SELECT COUNT(idx)  AS cntRow FROM TB_PROFIT_POINT  {$sql_search} " ;

	$cntStmt = $DB_con->prepare($cntQuery);

	if ($fr_date != "" || $to_date != "" ) {
		$cntStmt->bindparam(":fr_date",$fr_date);
		$cntStmt->bindparam(":to_date",$to_date);
	}

	$fr_date = trim($fr_date);
	$to_date = trim($to_date);

	$cntStmt->execute();
	$row = $cntStmt->fetch(PDO::FETCH_ASSOC);
	$totalCnt = $row['cntRow'];

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

	// 회원명
	$memNmSql = "  , ( SELECT mem_NickNm FROM TB_MEMBERS WHERE TB_MEMBERS.mem_Id = TB_PROFIT_POINT.taxi_MemId AND TB_MEMBERS.b_Disply = 'N' limit 1 ) AS memNickNm  ";

	// 탈퇴회원명
	$memNmSql2 = "  , ( SELECT mem_NickNm FROM TB_MEMBERS WHERE TB_MEMBERS.mem_Id = TB_PROFIT_POINT.taxi_MemId AND TB_MEMBERS.b_Disply = 'Y' limit 1 ) AS memNickNm2  ";

	//목록

	/* 
		idx 와 taxi_Idx의 차이는 무엇인지? 
		taxi_Idx는 현재 없는 컬럼명으로 일단 삭제
		이후 확인 해 볼 것 2019-01-02
	*/
	$query = "";
	$query = "SELECT idx, taxi_SIdx, taxi_RIdx, taxi_OrdNo, taxi_RMemId, taxi_OrdSPoint, taxi_OrdTPoint, taxi_OrdMPoint, taxi_Memo, reg_Date {$memNmSql } {$memNmSql2 } FROM TB_PROFIT_POINT {$sql_search} {$sql_order} limit  {$from_record}, {$rows}" ;
	//echo $query."<BR>";
	//exit;
	$stmt = $DB_con->prepare($query);

	if ($fr_date != "" || $to_date != "" ) {
		$stmt->bindparam(":fr_date",$fr_date);
		$stmt->bindparam(":to_date",$to_date);
	}

	$fr_date = trim($fr_date);
	$to_date = trim($to_date);

	$stmt->execute();
	$numCnt = $stmt->rowCount();

	$DB_con = null;

	$qstr = "od_status=".urlencode($od_status)."&amp;fr_date=".urlencode($fr_date)."&amp;to_date=".urlencode($to_date)."&amp;findType=".urlencode($findType)."&amp;findword=".urlencode($findword);

	include "../common/inc/inc_gnb.php";  //헤더 
	include "../common/inc/inc_menu.php";  //메뉴 

?>

<div id="wrapper">
    <div id="container" class="">
        <div class="container_wr">
        <h1 id="container_title">수익관리</h1>

		<div class="local_ov01 local_ov">
			<span class="btn_ov01"><span class="ov_txt">총 건수 </span><span class="ov_num"><?=number_format($totalCnt);?>건 </span>
			<span class="btn_ov01"> <span class="ov_txt">총 수익금 </span><span class="ov_num"><?=number_format($totalPrice);?>원</span>
		</div>

		<form class="local_sch03 local_sch"  autocomplete="off">
		<div>
			<strong>분류</strong>
			<select name="findType" id="findType">
				<option value="taxi_SIdx" <?if($findType=="taxi_SIdx"){?>selected<?}?>>노선번호</option>
				<option value="taxi_OrdNickNm" <?if($findType=="taxi_OrdNickNm"){?>selected<?}?>>투게더아이디</option>
				<option value="taxi_OrdNo" <?if($findType=="taxi_OrdNo"){?>selected<?}?>>주문번호</option>
			</select>
			<label for="findword" class="sound_only">검색어<strong class="sound_only"> 필수</strong></label>
			<input type="text" name="findword" id="findword" value="<?=$findword?>" class=" frm_input">
		</div>

		<div class="sch_last">

            <strong>검색일자</strong>
            <input type="text" id="fr_date"  name="fr_date" value="<?=$fr_date?>" class="frm_input" size="10" maxlength="10"> ~
            <input type="text" id="to_date"  name="to_date" value="<?=$to_date?>" class="frm_input" size="10" maxlength="10">
            <button type="button" onclick="javascript:set_date('오늘');">오늘</button>
            <button type="button" onclick="javascript:set_date('어제');">어제</button>
            <button type="button" onclick="javascript:set_date('이번주');">이번주</button>
            <button type="button" onclick="javascript:set_date('이번달');">이번달</button>
            <button type="button" onclick="javascript:set_date('지난주');">지난주</button>
            <button type="button" onclick="javascript:set_date('지난달');">지난달</button>
            <button type="button" onclick="javascript:set_date('전체');">전체</button>
            <input type="submit" value="검색" class="btn_submit">
			<a href="<?=$base_url?>" class="btn btn_06">새로고침</a>
		</div>
		</form>


<form name="fmemberlist" id="fmemberlist"  method="post" autocomplete="off">

<div class="tbl_head01 tbl_wrap">
    <table>
    <caption>포인트내역 목록</caption>
    <thead>

	<!-- 아이디, 이름, 등급, 휴대폰번호, 가입일 -->
    <tr>
        <th scope="col" id="mb_list_idx">순번</th>
        <th scope="col" id="mb_list_sidx">노선번호</th>
        <th scope="col" id="mb_list_id"><a href="<?=title_sort("taxi_memId", 1)."&amp;$sqstr"; ?>">투게더아이디</a></th>
        <th scope="col" id="mb_list_mailc"><a href="<?=title_sort("taxi_OrdNo", 1)."&amp;$sqstr"; ?>">주문번호</a></th>
        <th scope="col" id="mb_list_mailc"><a href="<?=title_sort("taxi_OrdTPoint", 1)."&amp;$sqstr"; ?>">양도포인트</a></th>
        <th scope="col" id="mb_list_auth"><a href="<?=title_sort("taxi_OrdSPoint", 1)."&amp;$sqstr"; ?>">수익금</a></th> 
        <th scope="col" id="mb_list_mailr"><a href="<?=title_sort("reg_Date", 1)."&amp;$sqstr"; ?>">등록일</a></th>
    </tr>
    </thead>
    <tbody>

    <?
	if($numCnt > 0)   {

		$stmt->setFetchMode(PDO::FETCH_ASSOC);

		while($row =$stmt->fetch()) {
       // $bg = 'bg'.($stmt->fetch()%2);
		$from_record++;
		$memNickNm1 = $row['memNickNm'] ;
		$memNickNm2 = $row['memNickNm2'] ;

		if ($memNickNm1 != "" ) {
			$memNickNm = $memNickNm1;
		} else if ($memNickNm2 != "" ) {
			$memNickNm = $memNickNm2;
		} else {
			$memNickNm = "비회원";
		}

    ?>

    <tr class="<?=$bg?>" title="<?=$row['taxi_Memo']?>">
        <td headers="mb_list_idx"><?=$from_record?></td>
        <td headers="mb_list_idx"><a href="<?=DU_UDEV_DIR?>/taxiSharing/taxiSharingReg.php?mode=mod&idx=<?=$row['taxi_SIdx']?>&<?=$qstr?>&page=<?=$page?>" ><?=$row['taxi_SIdx']?></a></td>
        <td headers="mb_list_id"><?=$row['taxi_RMemId']?> (<?=$memNickNm?>) </td>
        <td headers="mb_list_id" ><a href="<?=DU_UDEV_DIR?>/order/orderList.php?findType=taxi_OrdNo&findword=<?=$row['taxi_OrdNo']?>"><?=$row['taxi_OrdNo']?></a></td>
        <td headers="mb_list_point" class="td_numPoint td_num"><?=number_format($row['taxi_OrdTPoint'])?></td> 
        <td headers="mb_list_point" class="td_numcancel td_num"><?=number_format($row['taxi_OrdSPoint'])?></td> 
        <td headers="mb_list_lastcall" class="td_date"><?=$row['reg_Date']?></td>
    </tr>
    <? 

		}
		$stmt = null;
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
	<?=get_apaging($rows, $page, $total_page, "$_SERVER[PHP_SELF]?$sqstr"); ?>
</nav>

<script>
	$(function(){
		$("#fr_date, #to_date").datepicker({ changeMonth: true, changeYear: true, dateFormat: "yy-mm-dd", showButtonPanel: true });
	});


	function set_date(today)
	{
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
			document.getElementById("fr_date").value = "<?php echo date('Y-m-d', strtotime('-'.$date_term.' days', DU_SERVER_TIME)); ?>";
			document.getElementById("to_date").value = "<?php echo date('Y-m-d', DU_SERVER_TIME); ?>";
		} else if (today == "이번달") {
			document.getElementById("fr_date").value = "<?php echo date('Y-m-01', DU_SERVER_TIME); ?>";
			document.getElementById("to_date").value = "<?php echo date('Y-m-d', DU_SERVER_TIME); ?>";
		} else if (today == "지난주") {
			document.getElementById("fr_date").value = "<?php echo date('Y-m-d', strtotime('-'.$week_term.' days', DU_SERVER_TIME)); ?>";
			document.getElementById("to_date").value = "<?php echo date('Y-m-d', strtotime('-'.($week_term - 6).' days', DU_SERVER_TIME)); ?>";
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

<? include "../common/inc/inc_footer.php";  //푸터 ?>
