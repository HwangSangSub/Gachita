<?
	$menu = "3";
	$smenu = "4";

	include "../common/inc/inc_header.php";  //헤더 

	$base_url = $PHP_SELF;

	$sql_search=" WHERE 1=1";

	if($findword != "")  {
		$sql_search .= " AND `{$findType}` LIKE '%{$findword}%' ";
	}
	if($qna_Id != "") {
		$sql_search .= " AND qna_Id = {$qna_Id}";
	}

	$DB_con = db1();
	
	//전체 카운트
	$cntQuery = "";
	$cntQuery = "SELECT COUNT(idx) AS cntRow FROM TB_TAXI_QNA  {$sql_search} " ;
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
	$query = "SELECT idx, qna_Id, qna_Question, qna_Answer, reg_Date, update_Date FROM TB_TAXI_QNA {$sql_search} {$sql_order} limit  {$from_record}, {$rows}" ;
	$stmt = $DB_con->prepare($query);
	if($findword != "")  {
	    $stmt->bindValue(":findType",$findType);		
	    $stmt->bindValue(":findword",$findword );
	}
	if($qna_Id != "") {
		$stmt->bindValue(":qna_Id",$qna_Id);
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
        <h1 id="container_title">QNA 관리</h1>

		<div class="local_ov01 local_ov">
			<span class="btn_ov01"><span class="ov_txt">총 수 </span><span class="ov_num"><?=number_format($totalCnt);?>건 </span>
		</div>



		<form class="local_sch03 local_sch"  autocomplete="off">

		<div>
			<strong>메뉴명</strong>
			<select id="qna_Id" name="qna_Id" style="width:140px;">
				<option value="">메뉴명선택</option>
				<option value="1" <?=($qna_Id == "1" ? "selected":"")?>>결제·포인트·환전</option>
				<option value="2" <?=($qna_Id == "2" ? "selected":"")?>>이용내역</option>
				<option value="3" <?=($qna_Id == "3" ? "selected":"")?>>이용수칙</option>
				<option value="4" <?=($qna_Id == "4" ? "selected":"")?>>비상신고</option>
				<option value="5" <?=($qna_Id == "5" ? "selected":"")?>>기타</option>
			</select>
		</div>
		<div>
			<strong>분류</strong>
			<select name="findType" id="findType">
				<option value="qna_Question" <?if($findType=="qna_Question"){?>selected<?}?>>질문</option>
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
        <th scope="col" id="mb_list_chk" >
            <label for="chkall" class="sound_only">QNA 전체</label>
            <input type="checkbox" name="chkall" class="chkc" id="chkAll">
        </th>
        <th scope="col">순번</th>
        <th scope="col">메뉴명</th>
		<th scope="col">질문</a></th>		 
		<th scope="col">답변</th>
		<th scope="col">등록일</th>
		<th scope="col">최종 수정일</th>
		<th scope="col">노출여부</th>
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
		$qna_Id = $row['qna_Id'];
		if($qna_Id == '1'){
			$qnaId = '결제·포인트·환전';
		}else if($qna_Id == '2'){
			$qnaId = '이용내역';
		}else if($qna_Id == '3'){
			$qnaId = '이용수칙';
		}else if($qna_Id == '4'){
			$qnaId = '비상신고';
		}else if($qna_Id == '5'){
			$qnaId = '기타';
		}
    ?>
    <tr class="<?=$bg?>">
        <td headers="mb_list_chk" class="td_chk" >
            <input type="checkbox"  id="chk" class="chk" name="chk" value="<?=$row['idx']?>">
        </td>
		<td><?=$from_record?></td>
		<td><?=$qnaId?></td>
		<td><?=$row['qna_Question']?></td>
		<td><?=$row['qna_Answer']?></td>
		<td><?=$row['reg_Date']?></td>
		<td><?=$row['update_Date']?></td>
		<td headers="mb_list_mng" class="td_mng td_mng_s"><a href="qnaReg.php?mode=mod&idx=<?=$row['idx']?>&<?=$qstr?>&page=<?=$page?>" class="btn btn_03">수정</a><a href="javascript:chkDel_qna('<?=$row['idx']?>')" class="btn btn_02">삭제</a>
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

<div class="btn_fixed_top">
	<a href="qnaReg.php" id="qna_add" class="btn btn_01">QNA 추가</a>
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
