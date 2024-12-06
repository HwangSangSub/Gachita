<?
$menu = "4";
$smenu = "5";

include "../common/inc/inc_header.php";  //헤더

$base_url = $PHP_SELF;

$sql_search = " WHERE 1 = 1 AND A.taxi_state = 10";
if ($taxi_MState != "" ) {
    $sql_search .= " AND A.taxi_MState = :taxi_MState ";
}

if ($taxiSMemId != "" ) {  //고유회원 아이디
    $sql_search .= " AND C.taxi_SMemId = :taxi_SMemId ";
}

if ($fr_date != "" || $to_date != "" ) {
    //$sql_search.=" AND (reg_CDate between ':fr_date' AND ':to_date')";
    $sql_search .= " AND (DATE_FORMAT(A.reg_CDate,'%Y-%m-%d') >= :fr_date AND DATE_FORMAT(A.reg_CDate,'%Y-%m-%d') <= :to_date)";
}

if($findword != "")  {
    if ($findType == "taxi_idx") {
       $sql_search .= " AND A.idx LIKE :findword "; 
    } else if ($findType == "taxi_RMemId") {
        $sql_search .= " AND B.taxi_RMemId LIKE :findword "; 
    }
} 




$DB_con = db1();

//전체 카운트
$cntQuery = "";
$cntQuery = "SELECT COUNT(A.idx) AS cntRow FROM TB_STAXISHARING A INNER JOIN TB_SMATCH_STATE B ON A.Idx = B.taxi_SIdx INNER JOIN TB_RTAXISHARING C ON A.idx = C.taxi_SIdx {$sql_search}  " ;
//echo $cntQuery."<BR>";
//exit;
$cntStmt = $DB_con->prepare($cntQuery);

if ($taxi_MState != "" ) {
    $cntStmt->bindValue(":taxi_MState",$taxi_MState);
}

if ($taxiSMemId != "" ) {  //고유회원 아이디
    $cntStmt->bindValue(":taxi_SMemId",$taxiSMemId);
}

if ($fr_date != "" || $to_date != "" ) {
    $cntStmt->bindValue(":fr_date",$fr_date);
    $cntStmt->bindValue(":to_date",$to_date);
}

if($findword != "")  {
    $cntStmt->bindValue(':findword','%'.$findword.'%');
}

$fr_date = trim($fr_date);
$to_date = trim($to_date);
$findword = trim($findword);

$cntStmt->execute();

$row = $cntStmt->fetch(PDO::FETCH_ASSOC);
$totalCnt = $row['cntRow'];

//echo $totalCnt."<BR>";
//$cntStmt = null;

$rows = 10;
$total_page  = ceil($totalCnt / $rows);  // 전체 페이지 계산
if ($page == "") { $page = 1; } // 페이지가 없으면 첫 페이지 (1 페이지)
$from_record = ($page - 1) * $rows; // 시작 열을 구함

if (!$sort1)	{
    $sort1  = "A.idx";
    $sort2 = "DESC";
}

$sql_order = "order by $sort1 $sort2";

// 투게더 닉네임
$mnSql = "  , ( SELECT mem_NickNm FROM TB_MEMBERS WHERE TB_MEMBERS.mem_Id = C.taxi_MemId AND TB_MEMBERS.b_Disply = 'N' limit 1 ) AS memNickNm  ";
$mnSql2 = "  , ( SELECT mem_NickNm FROM TB_MEMBERS WHERE TB_MEMBERS.mem_Id = C.taxi_MemId AND TB_MEMBERS.b_Disply = 'Y' limit 1 ) AS memNickNm2  "; //탈퇴회원
$mnSql3 = "  , ( SELECT mem_NickNm FROM TB_MEMBERS WHERE TB_MEMBERS.mem_Id = C.taxi_RMemId AND TB_MEMBERS.b_Disply = 'N' limit 1 ) AS memNickNm3  ";
$mnSql4 = "  , ( SELECT mem_NickNm FROM TB_MEMBERS WHERE TB_MEMBERS.mem_Id = C.taxi_RMemId AND TB_MEMBERS.b_Disply = 'Y' limit 1 ) AS memNickNm4  "; //탈퇴회원
$query = "";
$query = "SELECT A.idx, B.taxi_SIdx, C.taxi_MemId, C.taxi_RMemId, C.taxi_MChk, B.taxi_CMemo, B.reg_CRDate, A.reg_CDate, A.taxi_MState, B.taxi_Disply, B.taxi_PDisply, D.taxi_OrdNo {$mnSql} {$mnSql2} {$mnSql3} {$mnSql4}  " ;
$query .= " FROM TB_STAXISHARING A ";
$query .= " INNER JOIN TB_SMATCH_STATE B ON A.idx = B.taxi_SIdx ";
$query .= " INNER JOIN TB_RTAXISHARING C ON A.idx = C.taxi_SIdx ";
$query .= " INNER JOIN TB_ORDER D ON A.idx = D.taxi_SIdx ";
$query .= " {$sql_search} {$sql_order} limit  {$from_record}, {$rows}";
//echo $query."<BR>";
//exit;
$stmt = $DB_con->prepare($query);

if ($taxi_MState != "" ) {
    $stmt->bindValue(":taxi_MState",$taxi_MState);
}

if ($taxiSMemId != "" ) {  //고유회원 아이디
    $stmt->bindValue(":taxi_SMemId",$taxiSMemId);
}

if ($fr_date != "" || $to_date != "" ) {
    $stmt->bindValue(":fr_date",$fr_date);
    $stmt->bindValue(":to_date",$to_date);
}

if($findword != "")  {
    $stmt->bindValue(':findword','%'.$findword.'%');
}

$fr_date = trim($fr_date);
$to_date = trim($to_date);
$findword = trim($findword);

$stmt->execute();
$numCnt = $stmt->rowCount();

$DB_con = null;

$qstr = "fr_date=".urlencode($fr_date)."&amp;to_date=".urlencode($to_date)."&amp;findType=".urlencode($findType)."&amp;findword=".urlencode($findword)."&amp;taxi_MState=".urlencode($taxi_MState);


include "../common/inc/inc_gnb.php";  //헤더
include "../common/inc/inc_menu.php";  //메뉴

?>
<script type="text/javascript" src="<?=DU_UDEV_DIR?>/member/js/memberManager.js"></script>
<script type="text/javascript">
	function taxi_PointChk(idx, taxi_OrdNo){
		var allData = {"idx": idx, "taxiOrdNo": taxi_OrdNo, "chkState": 2};
		$.ajax({
		url:"/udev/taxiSharing/taxiSharingComProc.php",
			type:'POST',
			dataType : 'json',
			data: allData,
			success:function(data){
				alert(data.Msg);
				location.reload();
			},
			error:function(jqXHR, textStatus, errorThrown){
				alert("에러 발생~~ \n" + textStatus + " : " + errorThrown);
				location.reload();
			}
		});
	}
</script>

<div id="wrapper">
    <div id="container" class="">
        <div class="container_wr">
        <h1 id="container_title">완료처리 내역</h1>
		<div class="local_ov01 local_ov">
			<span class="btn_ov01"><span class="ov_txt">총 건수 </span><span class="ov_num"><?=number_format($totalCnt);?>건 </span>
		</div>

        <form class="local_sch03 local_sch"  autocomplete="off">
        <div>
            <strong>분류</strong>
        	<label for="findType" class="sound_only">검색대상</label>
        	<select name="findType" id="findType">
        		<option value="taxi_idx" <?if($findType=="taxi_idx"){?>selected<?}?>>노선번호</option>
        	</select>
        	<label for="findword" class="sound_only">검색어<strong class="sound_only"> 필수</strong></label>
        	<input type="text" name="findword" id="findword" value="<?=$findword?>" class=" frm_input">
        </div>
        <div class="sch_last">
            <strong>취소일자</strong>
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

        <nav class="pg_wrap">
        	<?=get_apaging($rows, $page, $total_page, "$_SERVER[PHP_SELF]?$qstr"); ?>
        </nav>
        
        <form name="fmlist" id="fmlist"  method="post" autocomplete="off">
        
        <div class="tbl_head01 tbl_wrap">
            <table>
            <caption>쉐어링 매칭 목록</caption>
            <thead>
            <tr>
				<!--	단순내역보는  부분임으로 관리가 필요없을것 같아 주석처리 작업일 : 2019-01-07 작업자 : 황상섭 대리	
                <th scope="col" id="mb_list_chk" >
                    <label for="chkall" class="sound_only">전체</label>
                    <input type="checkbox" name="chkall" class="chkc" id="chkAll">
                </th>
				-->
                <th scope="col" id="mb_list_idx">순번</th>
                <th scope="col" id="mb_list_sIdx">노선번호</th>
                <th scope="col" id="mb_list_Sid">메이커</th>
                <th scope="col" id="mb_list_Rid">투게더</th>
                <th scope="col" id="mb_list_mailc">사유</th>
                <th scope="col" id="mb_list_mailc">사유메모</th>  
                <th scope="col" id="mb_list_mailc">포인트양도상태</th> 
                <th scope="col" id="mb_list_mailc">처리완료일</th>   
                <th scope="col" id="mb_list_mng">포인트관리</th>  
            </tr>
            </thead>
            <tbody>

   <?

	if($numCnt > 0)   {

	    $stmt->setFetchMode(PDO::FETCH_ASSOC);
	    
	    $i = 0;
	    
	    while($row =$stmt->fetch()) {
	        $i = $i + 1;
	        $bg = 'bg'.($i%2);
			$from_record++;
	        $memNickNm1 = $row['memNickNm'];
	        $memNickNm2 = $row['memNickNm2'];
	        
	        if ($memNickNm1 != "" ) {
	            $memNickNm = $memNickNm1;
	        } else if ($memNickNm2 != "" ) {
	            $memNickNm = $memNickNm2;
	        } else {
	            $memNickNm = "비회원";
	        }
		        
	        $memNickNm3 = $row['memNickNm3'];
	        $memNickNm4 = $row['memNickNm4'];
	        
	        if ($memNickNm3 != "" ) {
	            $memNickNm_1 = $memNickNm3;
	        } else if ($memNickNm4 != "" ) {
	            $memNickNm_1 = $memNickNm4;
	        } else {
	            $memNickNm_1 = "비회원";
	        }        
			$taxi_SIdx = $row['taxi_SIdx'];
			$taxi_MemId = $row['taxi_MemId']; 
			$taxi_RMemId = $row['taxi_RMemId']; 
			$taxi_MChk = $row['taxi_MChk']; 
			$taxi_CMemo = $row['taxi_CMemo']; 
			$reg_CRDate = $row['reg_CRDate']; 
			$reg_CDate = $row['reg_CDate']; 
			$taxi_MState = $row['taxi_MState']; 
			$taxi_Disply = $row['taxi_Disply'];
			$taxi_PDisply = $row['taxi_PDisply']; 
			$taxi_OrdNo = $row['taxi_OrdNo'];
			if($taxi_MChk == '1'){
				$taxiMChk = '완료조건 1 (시스템)';
			}else if($taxi_MChk == '2'){
				$taxiMChk = '완료조건 2 (시스템)';
			}else if($taxi_MChk == '3'){
				$taxiMChk = '완료조건 3 (시스템)';
			}else if($taxi_MChk == '4'){
				$taxiMChk = '완료조건 4 (시스템)';
			}else{
				$taxiMChk = '사용자의 미동의로 인한 취소';
			}
			if($taxi_PDisply == 'Y'){
				$taxiPDisply = '양도완료';
			}else{
				$taxiPDisply = '양도대기중';
			}
    ?>


    <tr class="<?=$bg?>">
        <td headers="mb_list_id"><?=$from_record?></td>
        <td headers="mb_list_id"><a href="taxiSharingReg.php?mode=mod&idx=<?=$taxi_SIdx?>&<?=$qstr?>&page=<?=$page?>" ><?=$taxi_SIdx?></a></td>
        <td headers="mb_list_id"><a href="/udev/member/memberReg.php?mode=mod&id=<?=$taxi_MemId?>"><?=$taxi_MemId?> </br> (<?=$memNickNm?>)</a></td>
        <td headers="mb_list_id"><a href="/udev/member/memberReg.php?mode=mod&id=<?=$taxi_RMemId?>"><?=$taxi_RMemId?> </br> (<?=$memNickNm_1?>)</a></td>
        <td headers="mb_list_id"><?=$taxiMChk?></td>
        <td headers="mb_list_id"><?=$taxi_CMemo?></td>
        <td headers="mb_list_id"><?=$taxiPDisply?></td>
        <td headers="mb_list_id"><?=($reg_CRDate == '' ? $reg_CDate : $reg_CRDate)?></td>
		<td headers="mb_list_mng" class="td_mng td_mng_s">
		<?
			if($taxi_PDisply == 'N'){
		?>
<? if($_COOKIE['du_udev']['id'] != 'admin2'){ ?>
				<a href="javascript:;" onclick="taxi_PointChk(<?=$taxi_SIdx?>,'<?=$taxi_OrdNo?>');" class="btn btn_01">양도</a>
<? } ?>
		<?
			}else{
		?>
				<span>-</span>
		<?
			}
		?>
		</td>
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

	 include "../common/inc/inc_footer.php";  //푸터 
	 
?>

