<?
$menu = "4";
$smenu = "3";

include "../common/inc/inc_header.php";  //헤더

$base_url = $PHP_SELF;

$sql_search = " WHERE 1 = 1 AND A.taxi_state = 9  AND C.taxi_MChk NOT IN ('1', '2', '3', '4')";
if ($taxi_MState != "" ) {
    $sql_search .= " AND A.taxi_MState = :taxi_MState ";
}

if ($taxiSMemId != "" ) {  //고유회원 아이디
    $sql_search .= " AND C.taxi_SMemId = :taxi_SMemId ";
}

if ($fr_date != "" || $to_date != "" ) {
    //$sql_search.=" AND (reg_CDate between ':fr_date' AND ':to_date')";
    $sql_search .= " AND (DATE_FORMAT(reg_CDate,'%Y-%m-%d') >= :fr_date AND DATE_FORMAT(reg_CDate,'%Y-%m-%d') <= :to_date)";
}

if($findword != "")  {
    if ($findType == "taxi_idx") {
       $sql_search .= " AND A.idx LIKE :findword "; 
    }
} 




$DB_con = db1();

//전체 카운트
$cntQuery = "";
$cntQuery = "SELECT COUNT(A.idx) AS cntRow FROM TB_STAXISHARING A INNER JOIN TB_SMATCH_STATE B ON A.Idx = B.taxi_SIdx LEFT OUTER JOIN TB_RTAXISHARING C ON A.idx = C.taxi_SIdx {$sql_search}  " ;
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
$query = "SELECT A.idx, B.idx as c_idx, A.taxi_MState, A.reg_CDate, C.taxi_MemId, C.taxi_RMemId, B.taxi_MType, B.taxi_OMType, B.taxi_CPart, B.taxi_CRPart, B.taxi_CMemo, A.reg_Date, D.taxi_OrdNo, D.taxi_OrdPrice, D.reg_Date as order_Date {$mnSql} {$mnSql2} {$mnSql3} {$mnSql4}  " ;
$query .= " FROM TB_STAXISHARING A ";
$query .= " LEFT OUTER JOIN TB_SMATCH_STATE B ON A.idx = B.taxi_SIdx ";
$query .= " LEFT OUTER JOIN TB_RTAXISHARING C ON A.idx = C.taxi_SIdx ";
$query .= " LEFT OUTER JOIN TB_ORDER D ON A.idx = D.taxi_SIdx ";
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
	function taxi_Cancle(idx,  type){
		// 사용자 ID(문자열)와 체크박스 값들(배열)을 name/value 형태로 담는다.		
		/*var push_chk = '';
		if ($('#pushsel').is(":checked")) {
			push_chk = 'Y';
		}else{
			push_chk = 'N';
		}	*/
		var pDisplay = '';
		if ($('#pDisplay').is(":checked")) {
			pDisplay = 'Y';
		}else{
			pDisplay = 'N';
		}

		if(type == 'Y'){
			var allData = { "idx": idx, "type": type, "push": pDisplay};
			$.ajax({
				url:"/udev/taxiSharing/taxiSharingCRProc.php",
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
		}else if (type == 'N'){
			var con_test = confirm("취소요청을 거절하셨습니다. 포인트 양도를 진행하시겠습니까?\n(확인: 포인트양도, 취소: 포인트양도대기)");
			if(con_test == true){
				var taxiOrdNo = $('#taxi_OrdNo'+idx).val();
				var allData = { "idx": idx, "type": type, "pDisplay": pDisplay, "taxiOrdNo": taxiOrdNo, "chkState": 2};
				$.ajax({
				url:"/udev/taxiSharing/taxiSharingCRProc.php",
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
			}else if(con_test == false){
			var allData = { "idx": idx, "type": type, "pDisplay": pDisplay};
			$.ajax({
				url:"/udev/taxiSharing/taxiSharingCRProc.php",
				type:'POST', 
				dataType : 'json',
				data: allData,
				success :function(data){
					alert(data.Msg);
					location.reload();
				},
				error:function(jqXHR, textStatus, errorThrown){
					alert("에러 발생~~ \n" + textStatus + " : " + errorThrown);
					location.reload();
				}
			});
			}
		}
	}
</script>

<div id="wrapper">
    <div id="container" class="">
        <div class="container_wr">
        <h1 id="container_title">취소처리 관리</h1>
		<div class="local_ov01 local_ov">
			<span class="btn_ov01"><span class="ov_txt">총 건수 </span><span class="ov_num"><?=number_format($totalCnt);?>건 </span></span>
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

        <div>
            <strong>취소시상태</strong>
        	<span class="bg <? if ($taxi_MState == "") { ?>all_on<? } ?>">
            <input type="radio" name="taxi_MState" value="" id="taxi_MState_all" <?php echo get_checked($taxi_MState, ''); ?>>
            <label for="taxi_MState_all">전체</label>
        	</span>
        	<span class="bg <? if ($taxi_MState == "4") { ?>c01_on<? } ?>">
            <input type="radio" name="taxi_MState" value="4" id="taxi_MState_4" <?php echo get_checked($taxi_MState, '4'); ?>>
            <label for="taxi_MState_4">예약요청완료</label>
        	</span>
        	<span class="bg <? if ($taxi_MState == "5") { ?>c01_on<? } ?>">
            <input type="radio" name="taxi_MState" value="5" id="taxi_MState_5" <?php echo get_checked($taxi_MState, '5'); ?>>
            <label for="taxi_MState_5">만남중</label>
        	</span>
        	<span class="bg <? if ($taxi_MState == "6") { ?>c02_on<? } ?>">
            <input type="radio" name="taxi_MState" value="6" id="taxi_MState_6" <?php echo get_checked($taxi_MState, '6'); ?>>
            <label for="taxi_MState_6">이동중</label>
        	</span> 
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
                <th scope="col" id="mb_list_CState">취소시상태</th>
                <th scope="col" id="mb_list_id">메이커</th>
                <th scope="col" id="mb_list_Rid">투게더</th>
                <th scope="col" id="mb_list_mailc">취소요청자</th>
                <th scope="col" id="mb_list_mailc">취소사유</th>  
                <th scope="col" id="mb_list_mailc">결제주문번호</th> 
                <th scope="col" id="mb_list_mailc">결제금액</th>   
                <th scope="col" id="mb_list_mailc">결제일</th>  
                <th scope="col" id="mb_list_mailc">노선등록일</th>
                <th scope="col" id="mb_list_mailc">포인트양도유무</th>
        		<th scope="col" id="mb_list_mng">관리</th>
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
	        if($row['taxi_Type'] == "0") {
	            $taxiType = "바로출발";
	        } else {
	            $taxiType = "예약출발";
	        }
	        
	        $regDate =  $row['reg_Date'];
	        
	        $taxi_MType = $row['taxi_MType'] ;
	        
	        if($taxi_MType == "c") {
	            $taxiMType =  $memNickNm_1;
	        } else if($taxi_MType == "p") {
	            $taxiMType = $memNickNm;
	        }
	        
	        $taxi_Seat = $row['taxi_Seat'] ;
	        if($taxi_Seat == 0) {
	            $taxiSeat = "앞좌석";
	        } else if($taxi_Seat == 1) {
	            $taxiSeat = "뒷좌석";
	        }
	        
	        $lineDistance = $row['taxi_Distance'];
	        if ($lineDistance <= "1000") {
	            $lineTDistance = $lineDistance."m";    // 미터
	        } else {
	            $taxiDistance = $lineDistance / 1000.0;
	            $lineTDistance = round($taxiDistance, 2)."km";    // 미터를 km로 변환
	        }
	        
	        
	        $taxiCMemo = $row['taxi_CMemo'];
	        
	        $regCRDate = $row['reg_CRDate'];
	        
	        $taxi_Route = $row['taxi_Route'] ;
	        if($taxi_Route == 0) {
	            $taxiRoute = "경유가능";
	        } else if($taxi_Route == 1) {
	            $taxiRoute = "경유불가";
	        }
	        
	        $taxiCanRChk = $row['taxi_CanRChk'];
	        if($taxiCanRChk == 'Y') {
	            $taxiCanRChkNm = "동의";
	        } else if($taxiCanRChk == 'N') {
	            $taxiCanRChkNm = "미동의";
	        }
	        
	        
	        $taxi_CPart = $row['taxi_CPart'] ;
	        
	        if($taxi_CPart == 1) {
	            $taxiCPart = "택시가 잡히지 않습니다.";
	        } else if($taxi_CPart == 2) {
	            $taxiCPart = "나의 사유로 인해 취소가 불가피 합니다.";
	        } else if($taxi_CPart == 3) {
	            $taxiCPart = "상대방의 사유로 인해 취소가 불가피 합니다.";
	        } else if($taxi_CPart == 5) {
	            $taxiCPart = $taxiCMemo;
	        }


			
	        $taxi_CRPart = $row['taxi_CRPart'] ;
	        
	        if($taxi_CRPart == 1) {
	            $taxiCRPart = "거래취소를 원하지 않습니다.";
	        } else if($taxi_CRPart == 2) {
	            $taxiCRPart = "거래 취소는 동일하나 다른 사유입니다.";
	        } else if($taxi_CRPart == 3) {
	            $taxiCRPart = "기타 (5분 초과 미응답)";
	        } else if($taxi_CRPart == 4) {
	            $taxiCRPart = "동의합니다.";
	        }

			//취소사 상태부분임으로 취소처리(본사), 완료처리(본사)는 없음
			$taxi_MState =$row['taxi_MState'];	        
			if($taxi_MState == 1) {
	            $taxiMState = "매칭중";
	        } else if($taxi_MState == 2) {
	            $taxiMState = "매칭요청";
	        } else if($taxi_MState == 3) {
	            $taxiMState = "예약요청";
	        } else if($taxi_MState == 4) {
	            $taxiMState = "예약요청완료";
	        } else if($taxi_MState == 5) {
	            $taxiMState = "만남중";
	        } else if($taxi_MState == 6) {
	            $taxiMState = "이동중";
	        } else if($taxi_MState == 7) {
	            $taxiMState = "완료";
	        } else if($taxi_MState == 8) {
	            $taxiMState = "취소";
	        } else if($taxi_MState == 9) {
	            $taxiMState = "취소사유확인";
	        } else if($taxi_MState == 10) {
	            $taxiMState = "거래완료확인";
	        }else{
				$taxiMState = "-";
			}

			$taxi_OrdPrice = $row['taxi_OrdPrice'];
			if($taxi_OrdPrice == ''){
				$taxiOrdPrice = 0;
			}else{
				$taxiOrdPrice = $taxi_OrdPrice;
			}
    ?>


    <tr class="<?=$bg?>">
		<!--	단순내역보는  부분임으로 관리가 필요없을것 같아 주석처리 작업일 : 2019-01-07 작업자 : 황상섭 대리
        <td headers="mb_list_chk" class="td_chk" >
            <input type="hidden" name="mb_id[<?=$row['idx']?>]" id="mb_id_<?=$row['idx']?>" value="<?=$row['mem_Id'] ?>" >
        </td>
		-->
        <td headers="mb_list_id"><?=$from_record?></td>
        <td headers="mb_list_id"><a href="taxiSharingReg.php?mode=mod&idx=<?=$row['idx']?>&<?=$qstr?>&page=<?=$page?>" ><?=$row['idx']?></a></td>
        <td headers="mb_list_id"><?=$taxiMState?></td>
        <td headers="mb_list_id"><a href="/udev/member/memberReg.php?mode=mod&id=<?=$row['taxi_MemId']?>"><?=$row['taxi_MemId']?> <br/> (<?=$memNickNm?>)</a></td>
        <td headers="mb_list_id"><a href="/udev/member/memberReg.php?mode=mod&id=<?=$row['taxi_RMemId']?>"><?=$row['taxi_RMemId']?> </br> (<?=$memNickNm_1?>)</a></td>
        <td headers="mb_list_id"><?=$taxiMType?></td>
        <td headers="mb_list_id"><?=$taxiCPart?></td>
        <td headers="mb_list_id"><a href="/udev/order/orderList.php?findType=taxi_OrdNo&findword=<?=$row['taxi_OrdNo']?>"><?=$row['taxi_OrdNo']?><input type="hidden" id="taxi_OrdNo_<?=$row['idx']?>" name="taxi_OrdNo_<?=$row['idx']?>" value="<?=$row['taxi_OrdNo']?>"/></a></td>
        <td headers="mb_list_id"><?=number_format($taxiOrdPrice)."원"?></td>
        <td headers="mb_list_id"><?=$row['order_Date']?></td>
        <td headers="mb_list_id"><?=$regDate?></td>
        <!-- 푸시설정은 폰에서 설정할 수 있으므로 일단 주석처리
			<td headers="mb_list_id"><input type="checkbox" value='Y' id="pushsel" name="pushsel" /></td>
		-->
        <td headers="mb_list_id"><input type="checkbox" value='Y' id="pDisplay" name="pDisplay" /></td>
		<td headers="mb_list_mng" class="td_mng td_mng_s">
		  <a href="javascript:;" onclick="taxi_Cancle('<?=$row['idx']?>','Y');" class="btn btn_01">승인</a>
		  <a href="javascript:;" onclick="taxi_Cancle('<?=$row['idx']?>','N');" class="btn btn_02">거절</a>
		</td>
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

