<?
	$menu = "3";
	$smenu = "7";

	include "../common/inc/inc_header.php";  //헤더 

	$base_url = $PHP_SELF;
	

	$sql_search=" WHERE 1=1 ";

	if ($fr_date != "" || $to_date != "" ) {
		//$sql_search.=" AND (reg_Date between ':fr_date' AND ':to_date')";
		$sql_search.=" AND (DATE_FORMAT(A.reg_Date,'%Y-%m-%d') >= :fr_date AND DATE_FORMAT(A.reg_Date,'%Y-%m-%d') <= :to_date)";
	}
	
	$DB_con = db1();

	//전체 카운트
	$cntQuery = "";
	$cntQuery .= " SELECT " ;
	$cntQuery .= " COUNT(A.idx) AS tot_Cnt " ;
	$cntQuery .= " FROM TB_PAYMENT_CARD A ";
	$cntQuery .= " LEFT OUTER JOIN TB_CARD_CODE B ON A.card_Name = B.card_Name ";
	$cntQuery .= " {$sql_search} GROUP BY A.card_Name ";
	$cntStmt = $DB_con->prepare($cntQuery);
//echo $cntQuery;
//exit;
	if ($fr_date != "" || $to_date != "" ) {
	    $cntStmt->bindValue(":fr_date",$fr_date);
	    $cntStmt->bindValue(":to_date",$to_date);
	}
	$cntStmt->execute();
	$row = $cntStmt->fetch(PDO::FETCH_ASSOC);
//	$totalCnt = $row['cntRow'];	//기존방식
	$totalCnt = $cntStmt->rowCount();

//전체 카운트
	$cntQuery = "";
	$cntQuery .= " SELECT " ;
	$cntQuery .= " COUNT(A.idx) AS tot_Cnt " ;
	$cntQuery .= " FROM TB_PAYMENT_CARD A ";
	$cntQuery .= " LEFT OUTER JOIN TB_CARD_CODE B ON A.card_Name = B.card_Name ";
	$cntQuery .= " {$sql_search}";
	$cntStmt = $DB_con->prepare($cntQuery);
//echo $cntQuery;
//exit;
	if ($fr_date != "" || $to_date != "" ) {
	    $cntStmt->bindValue(":fr_date",$fr_date);
	    $cntStmt->bindValue(":to_date",$to_date);
	}
	$cntStmt->execute();
	while($row = $cntStmt->fetch()) {
		$tot_Cnt = $row['tot_Cnt'];
	}

	$rows = 10;
	$total_page  = ceil($totalCnt / $rows);  // 전체 페이지 계산
	if ($page == "") { $page = 1; } // 페이지가 없으면 첫 페이지 (1 페이지)
	$from_record = ($page - 1) * $rows; // 시작 열을 구함

	$sql_group = "GROUP BY A.card_Name";
	$sql_order = "ORDER BY A.card_Name";

	// 회원등급명
	$memLvSql = "  , ( SELECT memLv_Name FROM TB_MEMBER_LEVEL C WHERE C.memLv = A.mem_Lv limit 1 ) AS memLvNm  ";

	//목록
	$query = "";
	$query .= " SELECT " ;
	$query .= " COUNT(A.idx) AS tot_Cnt " ;
	$query .= " , A.card_Name " ;
	$query .= " FROM TB_PAYMENT_CARD A ";
	$query .= " LEFT OUTER JOIN TB_CARD_CODE B ON A.card_Name = B.card_Name ";
	$query .= " {$sql_search} {$sql_group} {$sql_order} limit  {$from_record}, {$rows} ";
	//echo $query."<BR>";
	//exit;

	$stmt = $DB_con->prepare($query);

	if ($fr_date != "" || $to_date != "" ) {
	    $stmt->bindValue(":fr_date",$fr_date);
	    $stmt->bindValue(":to_date",$to_date);
	}

	if($mem_Lv != "")  {
	    $stmt->bindValue(":mem_Lv",$mem_Lv);
	}

	if($findOs != "")  {
	    $stmt->bindValue(":mem_Os",$findOs);
	}

	if($findword != "")  {
	    $stmt->bindValue(':findword','%'.trim($findword).'%');
	}
	
	$stmt->execute();
	$numCnt = $stmt->rowCount();

	$qstr = "fr_date=".urlencode($fr_date)."&amp;to_date=".urlencode($to_date)."&amp;findType=".urlencode($findType)."&amp;findOs=".urlencode($findOs)."&amp;findword=".urlencode($findword);
	
	include "../common/inc/inc_gnb.php";  //헤더 
	include "../common/inc/inc_menu.php";  //메뉴 

?>
<script type="text/javascript" src="<?=DU_UDEV_DIR?>/member/js/member.js"></script>

<div id="wrapper">
    <div id="container" class="">
        <div class="container_wr">
        <h1 id="container_title">카드등록통계</h1>
		
		<div class="local_ov01 local_ov">
			<span class="btn_ov01"><span class="ov_txt">총 카드등록회원 </span><span class="ov_num"><?=number_format($tot_Cnt);?> 명</span>&nbsp;
		</div>

<div class="local_desc01 local_desc">
    <p>
		등록된 카드사 별로 통계처리합니다.<br>
    </p>
</div>

<nav class="pg_wrap">
	<?=get_apaging($rows, $page, $total_page, "$_SERVER[PHP_SELF]?$qstr"); ?>
</nav>

<form name="fmemberlist" id="fmemberlist"  method="post" autocomplete="off">

<div class="tbl_head01 tbl_wrap">
    <table>
    <caption>카드등록통계</caption>
    <thead>

	<!-- 아이디, 이름, 등급, 휴대폰번호, 가입일 -->
    <tr>
		<th scope="col" id="mb_list_idx">순번</th>
        <th scope="col" id="mb_list_cardName">카드사명</th>
		<th scope="col" id="mb_list_cnt">등록수</th>
    </tr>
    </thead>
    <tbody>

    <?

	if($numCnt > 0)   {

		$stmt->setFetchMode(PDO::FETCH_ASSOC);

		while($row = $stmt->fetch()) {
       // $bg = 'bg'.($stmt->fetch()%2);

		$from_record++;
		$tot_Cnt = $row['tot_Cnt'];
		$card_Name = $row['card_Name'];
    ?>

    <tr class="<?=$bg?>">
        <td headers="mb_list_idx" class="td_idx"><?=$from_record?></td>
        <td headers="mb_list_cardName"><?=$card_Name?></td>
        <td headers="mb_list_cnt"><?=number_format($tot_Cnt)?> 명</td>
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

</form>
<nav class="pg_wrap">
	<?=get_apaging($rows, $page, $total_page, "$_SERVER[PHP_SELF]?$qstr"); ?>
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
