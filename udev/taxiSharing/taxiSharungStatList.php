<?
	$menu = "4";
	$smenu = "6";

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
	$cntQuery .= " SELECT left(A.reg_Date,10) as DATE" ;
	$cntQuery .= " , COUNT(A.idx) as tot_Cnt " ;
	$cntQuery .= " , SUM(CASE WHEN A.taxi_State = '7' THEN 1 ELSE 0 END) AS com_CNT " ;
	$cntQuery .= " , SUM(CASE WHEN A.taxi_State = '8' THEN 1 ELSE 0 END) AS cal_CNT " ;
	$cntQuery .= " , SUM(CASE WHEN A.taxi_State = '9' THEN 1 ELSE 0 END) AS cal_RCNT " ;
	$cntQuery .= " , SUM(CASE WHEN A.taxi_State = '10' THEN 1 ELSE 0 END) AS com_RCNT " ;
	$cntQuery .= " , SUM(CASE WHEN A.taxi_State NOT IN ('7', '8', '9','10') THEN 1 ELSE 0 END) AS com_NCNT " ;
	$cntQuery .= " , SUM(CASE WHEN A.taxi_State IN ('7','10') AND B.idx = D.taxi_RIdx AND D.taxi_OrdNo = E.taxi_OrdNo THEN E.taxi_OrdTPoint  ELSE 0 END) AS tot_Money " ;
	$cntQuery .= " , SUM(CASE WHEN A.taxi_State IN ('7','10') AND B.idx = D.taxi_RIdx AND D.taxi_OrdNo = E.taxi_OrdNo THEN E.taxi_OrdSPoint ELSE 0 END) AS tot_PMoney " ;
	$cntQuery .= " FROM TB_STAXISHARING A ";
	$cntQuery .= " LEFT OUTER JOIN TB_RTAXISHARING B ON A.idx = B.taxi_SIdx ";
	$cntQuery .= " LEFT OUTER JOIN TB_SMATCH_STATE C ON A.idx = C.taxi_SIdx ";
	$cntQuery .= " LEFT OUTER JOIN TB_ORDER D ON A.idx = D.taxi_SIdx ";
	$cntQuery .= " LEFT OUTER JOIN TB_PROFIT_POINT E ON A.idx = E.taxi_SIdx ";
	$cntQuery .= " {$sql_search} GROUP BY left(A.reg_Date,10) ";
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
	$cntQuery .= " SELECT ";
	$cntQuery .= " COUNT(A.idx) as tot_Cnt " ;
	$cntQuery .= " , SUM(CASE WHEN A.taxi_State = '7' THEN 1 ELSE 0 END) AS com_CNT " ;
	$cntQuery .= " , SUM(CASE WHEN A.taxi_State = '8' THEN 1 ELSE 0 END) AS cal_CNT " ;
	$cntQuery .= " , SUM(CASE WHEN A.taxi_State = '9' THEN 1 ELSE 0 END) AS cal_RCNT " ;
	$cntQuery .= " , SUM(CASE WHEN A.taxi_State = '10' THEN 1 ELSE 0 END) AS com_RCNT " ;
	$cntQuery .= " , SUM(CASE WHEN A.taxi_State NOT IN ('7', '8', '9','10') THEN 1 ELSE 0 END) AS com_NCNT " ;
	$cntQuery .= " , SUM(CASE WHEN A.taxi_State IN ('7','10') AND B.idx = D.taxi_RIdx AND D.taxi_OrdNo = E.taxi_OrdNo THEN E.taxi_OrdTPoint  ELSE 0 END) AS tot_Money " ;
	$cntQuery .= " , SUM(CASE WHEN A.taxi_State IN ('7','10') AND B.idx = D.taxi_RIdx AND D.taxi_OrdNo = E.taxi_OrdNo THEN E.taxi_OrdSPoint ELSE 0 END) AS tot_PMoney " ;
	$cntQuery .= " FROM TB_STAXISHARING A ";
	$cntQuery .= " LEFT OUTER JOIN TB_RTAXISHARING B ON A.idx = B.taxi_SIdx ";
	$cntQuery .= " LEFT OUTER JOIN TB_SMATCH_STATE C ON A.idx = C.taxi_SIdx ";
	$cntQuery .= " LEFT OUTER JOIN TB_ORDER D ON A.idx = D.taxi_SIdx ";
	$cntQuery .= " LEFT OUTER JOIN TB_PROFIT_POINT E ON A.idx = E.taxi_SIdx ";
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
		$com_CNT = $row['com_CNT'];
		$cal_CNT = $row['cal_CNT'];
		$tot_Money = $row['tot_Money'];
		$tot_PMoney = $row['tot_PMoney'];
	}

	$rows = 10;
	$total_page  = ceil($totalCnt / $rows);  // 전체 페이지 계산
	if ($page == "") { $page = 1; } // 페이지가 없으면 첫 페이지 (1 페이지)
	$from_record = ($page - 1) * $rows; // 시작 열을 구함

	$sql_group = "GROUP BY left(A.reg_Date,10)";
	$sql_order = "ORDER BY left(A.reg_Date,10) DESC";

	// 회원등급명
	$memLvSql = "  , ( SELECT memLv_Name FROM TB_MEMBER_LEVEL C WHERE C.memLv = A.mem_Lv limit 1 ) AS memLvNm  ";

	//목록
	$query = "";
	$query .= " SELECT left(A.reg_Date,10) as DATE" ;
	$query .= " , COUNT(A.idx) as tot_Cnt " ;
	$query .= " , SUM(CASE WHEN A.taxi_State = '7' THEN 1 ELSE 0 END) AS com_CNT " ;
	$query .= " , SUM(CASE WHEN A.taxi_State = '8' THEN 1 ELSE 0 END) AS cal_CNT " ;
	$query .= " , SUM(CASE WHEN A.taxi_State = '9' THEN 1 ELSE 0 END) AS cal_RCNT " ;
	$query .= " , SUM(CASE WHEN A.taxi_State = '10' THEN 1 ELSE 0 END) AS com_RCNT " ;
	$query .= " , SUM(CASE WHEN A.taxi_State NOT IN ('7', '8', '9','10') THEN 1 ELSE 0 END) AS com_NCNT " ;
	$query .= " , SUM(CASE WHEN A.taxi_State IN ('7','10') AND B.idx = D.taxi_RIdx AND D.taxi_OrdNo = E.taxi_OrdNo THEN E.taxi_OrdTPoint  ELSE 0 END) AS tot_Money " ;
	$query .= " , SUM(CASE WHEN A.taxi_State IN ('7','10') AND B.idx = D.taxi_RIdx AND D.taxi_OrdNo = E.taxi_OrdNo THEN E.taxi_OrdSPoint ELSE 0 END) AS tot_PMoney " ;
	$query .= " FROM TB_STAXISHARING A ";
	$query .= " LEFT OUTER JOIN TB_RTAXISHARING B ON A.idx = B.taxi_SIdx ";
	$query .= " LEFT OUTER JOIN TB_SMATCH_STATE C ON A.idx = C.taxi_SIdx ";
	$query .= " LEFT OUTER JOIN TB_ORDER D ON A.idx = D.taxi_SIdx ";
	$query .= " LEFT OUTER JOIN TB_PROFIT_POINT E ON A.idx = E.taxi_SIdx ";
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
        <h1 id="container_title">매칭통계</h1>
		
		<div class="local_ov01 local_ov">
			<span class="btn_ov01"><span class="ov_txt">기간 내 총 매칭생성수 </span><span class="ov_num"><?=number_format($tot_Cnt);?>회 </span>&nbsp;
			<span class="btn_ov01"><span class="ov_txt">기간 내 총 매칭완료수 </span><span class="ov_num"><?=number_format($com_CNT);?>회 </span>&nbsp;
			<span class="btn_ov01"><span class="ov_txt">기간 내 총 매칭취소수 </span><span class="ov_num"><?=number_format($cal_CNT);?>회 </span>&nbsp;
			<span class="btn_ov01"><span class="ov_txt">기간 내 총 매칭금액 </span><span class="ov_num"><?=number_format($tot_Money);?>원 </span>&nbsp;
			<span class="btn_ov01"><span class="ov_txt">기간 내 총 수익금 </span><span class="ov_num"><?=number_format($tot_PMoney);?>원 </span>&nbsp;
		</div>
		<form class="local_sch03 local_sch"  autocomplete="off">
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



<div class="local_desc01 local_desc">
    <p>
        기간선택이 없을 경우 기본적으로 전체 일자의 데이터를 통계처리합니다.<br>
		해당 기간의 일별 데이터는 아래에 테이블로 표시되며 해당기간의 총합은 검색일자 위에 표시됩니다.<br>
		매칭 수는 해당 일자에 생성된 총 노선수 입니다.<br>
        총결제액과 총수익금은 완료, 완료확인단계의 해당하는 노선의 합계액입니다.<br>
		일자는 최근일자부터 역순으로 정렬됩니다.
    </p>
</div>

<nav class="pg_wrap">
	<?=get_apaging($rows, $page, $total_page, "$_SERVER[PHP_SELF]?$qstr"); ?>
</nav>

<form name="fmemberlist" id="fmemberlist"  method="post" autocomplete="off">

<div class="tbl_head01 tbl_wrap">
    <table>
    <caption>회원관리 목록</caption>
    <thead>

	<!-- 아이디, 이름, 등급, 휴대폰번호, 가입일 -->
    <tr>
		<th scope="col" id="mb_list_idx">순번</th>
        <th scope="col" id="mb_list_data">일자</th>
        <th scope="col" id="mb_list_totcnt">매칭수</th>
        <th scope="col" id="mb_list_comcnt">완료</th>
		<th scope="col" id="mb_list_calcnt">취소</th>		 
		<th scope="col" id="mb_list_comrcnt">완료확인</th>	
		<th scope="col" id="mb_list_calrcnt">취소확인</th>	
		<th scope="col" id="mb_list_totmoney">총결제액</th>
		<th scope="col" id="mb_list_totpmoney">총수익금</th>
    </tr>
    </thead>
    <tbody>

    <?

	if($numCnt > 0)   {

		$stmt->setFetchMode(PDO::FETCH_ASSOC);

		while($row = $stmt->fetch()) {
       // $bg = 'bg'.($stmt->fetch()%2);

		$from_record++;
		$date = $row['DATE'];
		$totcnt = $row['tot_Cnt'];
		$comcnt = $row['com_CNT'];
		$calcnt = $row['cal_CNT'];
		$calrcnt = $row['cal_RCNT'];
		$comrcnt = $row['com_RCNT'];
		$comncnt = $row['com_NCNT'];
		$totmoney = $row['tot_Money'];
		$totpmoney = $row['tot_PMoney'];
    ?>

    <tr class="<?=$bg?>">
        <td headers="mb_list_idx" class="td_idx"><?=$from_record?></td>
        <td headers="mb_list_date" class="td_date"><?=$date?></td>
        <td headers="mb_list_totcnt"><?=$totcnt?></td>
		<td headers="mb_list_comcnt"><?=$comcnt?></td>
		<td headers="mb_list_calcnt"><?=$calcnt?></td>
		<td headers="mb_list_comrcnt"><?=$comrcnt?></td>
		<td headers="mb_list_calrcnt"><?=$calrcnt?></td>
        <td headers="mb_list_totmoney"><?=number_format($totmoney)?> 원</td>
        <td headers="mb_list_totpmoney"><?=number_format($totpmoney)?> 원</td>
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
