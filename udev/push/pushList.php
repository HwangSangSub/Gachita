<?
	$menu = "10";
	$smenu = "1";

	include "../common/inc/inc_header.php";  //헤더 

	$base_url = $PHP_SELF;
	
	/* 검색조건 확인 */
	$sql_search = "WHERE 1=1 ";

	if(empty($findType) == false)
	{
		$sql_search .= "AND :findType LIKE :findword ";
	}

	if(empty($fr_date) == false)
	{
		$sql_search .= "AND reg_Date >= :fr_date ";
	}

	if(empty($to_date) == false)
	{
		$sql_search .= "AND reg_Date <= :to_date ";
	}


	$DB_con = db1();

	//전체 카운트
	$cntQuery = "";
	$cntQuery = "SELECT COUNT(idx)  AS cntRow FROM TB_PUSH_HISTORY A {$sql_search} " ;
	$cntStmt = $DB_con->prepare($cntQuery);

	if ($fr_date != "" || $to_date != "" ) {
	    $cntStmt->bindValue(":fr_date",$fr_date);
	    $cntStmt->bindValue(":to_date",$to_date." 23:59:59");
	}
	
	if($findType != "" && $findword != "" )  {
	    $cntStmt->bindValue(':findType',trim($findType));
		$cntStmt->bindValue(':findword','%'.trim($findword).'%');
	}

	$cntStmt->execute();
	$row = $cntStmt->fetch(PDO::FETCH_ASSOC);
	$totalCnt = $row['cntRow'];


	$rows = 10;
	$total_page  = ceil($totalCnt / $rows);  // 전체 페이지 계산
	if ($page == "") { $page = 1; } // 페이지가 없으면 첫 페이지 (1 페이지)
	$from_record = ($page - 1) * $rows; // 시작 열을 구함


	if (!$sort1)	{
		$sort1  = "idx";
		$sort2 = "DESC";
	}

	$sql_order = "ORDER BY $sort1 $sort2";

	//목록
	$query ="SELECT * FROM TB_PUSH_HISTORY {$sql_search} {$sql_order} LIMIT  {$from_record}, {$rows}";
	$stmt = $DB_con->prepare($query);

	if ($fr_date != "" || $to_date != "" ) {
	    $stmt->bindValue(":fr_date",$fr_date);
	    $stmt->bindValue(":to_date",$to_date." 23:59:59");
	}
	
	if($findType != "" && $findword != "" )  {
	    $stmt->bindValue(':findType',trim($findType));
		$stmt->bindValue(':findword','%'.trim($findword).'%');
	}
	
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
        <h1 id="container_title">푸시 관리</h1>

		<div class="local_ov01 local_ov">
			<span class="btn_ov01"><span class="ov_txt">푸시발송건 </span><span class="ov_num"><?=number_format($totalCnt);?>건 </span>
		</div>


		<form class="local_sch03 local_sch"  autocomplete="off">
		<div>
    		<strong>검색조건</strong>
    		<select name="findType" id="findType">
    			<option value="contents" <?if($findType=="contents"){?>selected<?}?>>알림내용</option>
    			<option value="link_url" <?if($findType=="link_url"){?>selected<?}?>>URL링크</option>
    		</select>
    		<label for="findword" class="sound_only">검색어<strong class="sound_only"> 필수</strong></label>
    		<input type="text" name="findword" id="findword" value="<?=$findword?>" class=" frm_input">
		</div>
		
		<div class="sch_last">
			<strong>등록일자</strong>
			<input type="text" name="fr_date" id="fr_date" value="<?=$fr_date?>" class="frm_input" size="11" maxlength="10">
			<label for="fr_date" class="sound_only">시작일</label>
			~
			<input type="text" name="to_date" id="to_date" value="<?=$to_date?>"  class="frm_input" size="11" maxlength="10">
			<label for="to_date" class="sound_only">종료일</label>
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
        <th scope="col" id="mb_list_chk"  style="width:3%">
            <label for="chkall" class="sound_only">푸시내역 전체</label>
            <input type="checkbox" name="chkall" class="chkc" id="chkAll" onclick="check_all(this.form)">
        </th>
		<th scope="col" id="mb_list_idx" style="width:5%">순번</th>
		<th scope="col" id="mb_list_mailr" style="width:*%">알림내용</th>
		<th scope="col" id="mb_list_mailr" style="width:10%">등록일자</th>		 
    </tr>
    </thead>
    <tbody>

    <?

	if($numCnt > 0)   {

		$stmt->setFetchMode(PDO::FETCH_ASSOC);

		while($row =$stmt->fetch()) {
       // $bg = 'bg'.($stmt->fetch()%2);

		$from_record++;
		$idx = $row["idx"];
		$push_Msg = $row["push_Msg"];
		$reg_Date = $row["reg_Date"];
    ?>

    <tr class="<?=$bg?>">
        <td headers="mb_list_chk" class="td_chk" >
            <input type="hidden" name="mb_id[<?=$idx?>]" id="mb_id_<?=$idx?>" value="<?=$idx?>" >
            <input type="checkbox" id="chk_<?=$idx?>" class="chk" name="chk[]" value="<?=$idx?>">
        </td>
		<td headers="mb_list_idx" class="td_idx"><?=$from_record?></td>
		<td headers="mb_list_idx" class="td_idx"><a href="./pushView.php?seq=<?=$idx?>&page=<?=$page?>&qstr=<?=$qstr?>"><?=$push_Msg?></a></td>
		<td headers="mb_list_open" class="td_name td_mng_s"><?=$reg_Date?></td>
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
	<a href="#ALDel" id="bt_m_a_del" class="btn btn_02">선택삭제</a>
	<!--<a href="pushReg.php?mode=regAll" id="bt_m_a_add" class="btn btn_01">푸시일괄 발송</a>-->
	<a href="pushReg.php?mode=reg" id="bt_m_a_add" class="btn btn_01">푸시발송</a>
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
