<?
$menu = "4";
$smenu = "1";

include "../common/inc/inc_header.php";  //헤더

$base_url = $PHP_SELF;

$sql_search=" WHERE taxi_SIdx = :taxi_SIdx ";

if ($od_rstatus != "" ) {
    $sql_search .= " AND taxi_RState = :od_rstatus ";
}

if ($fr_rdate != "" || $to_rdate != "" ) {
    $sql_search.=" AND (DATE_FORMAT(reg_Date,'%Y-%m-%d') >= :fr_rdate AND DATE_FORMAT(reg_Date,'%Y-%m-%d') <= :to_rdate)";
}

if($findrWord != "")  {
    $sql_search .= " AND `{$findrType}` LIKE '%{$findrWord}%' ";
}


if($findword != "")  {
    if ($findType == "taxi_RSaddNm") {
        $sql_search .= " AND taxi_RSaddNm LIKE :findword ";
    } else if ($findType == "taxi_REaddr") {
        $sql_search .= " AND taxi_REaddr LIKE :findword ";
    } else if ($findType == "taxi_RMemId") {
        $sql_search .= " AND taxi_RMemId LIKE :findword ";
    }
}


$DB_con = db1();

//전체 카운트
$cntQuery = "";
$cntQuery = "SELECT COUNT(idx) AS cntRow FROM TB_RTAXISHARING  {$sql_search} " ;
//echo $cntQuery."<BR>";
//exit;
$cntStmt = $DB_con->prepare($cntQuery);
$cntStmt->bindValue(":taxi_SIdx",$taxiSIdx);

if ($od_status != "" ) {
    $cntStmt->bindValue(":taxi_State",$od_status);
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


$cntStmt->execute();
$row = $cntStmt->fetch(PDO::FETCH_ASSOC);
$totalCnt = $row['cntRow'];


$cntStmt = null;

$rows = 10;
$total_page  = ceil($totalCnt / $rows);  // 전체 페이지 계산
if ($rpage == "") { $rpage = 1; } // 페이지가 없으면 첫 페이지 (1 페이지)
$from_record = ($rpage - 1) * $rows; // 시작 열을 구함


if (!$sort1)	{
    $sort1  = "reg_Date";
    $sort2 = "DESC";
}

$sql_order = "order by $sort1 $sort2";

// 투게더 닉네임
$mnSql = "  , ( SELECT mem_NickNm FROM TB_MEMBERS WHERE TB_MEMBERS.mem_Id = TB_RTAXISHARING.taxi_RMemId AND TB_MEMBERS.b_Disply = 'N' limit 1 ) AS memNickNm  ";

$mnSql2 = "  , ( SELECT mem_NickNm FROM TB_MEMBERS WHERE TB_MEMBERS.mem_Id = TB_RTAXISHARING.taxi_RMemId AND TB_MEMBERS.b_Disply = 'Y' limit 1 ) AS memNickNm2  "; //탈퇴회원

// 쉐어링요금
$priceSql = "  , ( SELECT taxi_Price FROM TB_STAXISHARING WHERE TB_STAXISHARING.idx = TB_RTAXISHARING.taxi_RIdx AND TB_RTAXISHARING.taxi_MemId = TB_STAXISHARING.taxi_MemId  limit 1 ) AS taxiPrice  ";

// 결제상태
$stmSql = "  , ( SELECT taxi_OrdState FROM TB_ORDER WHERE TB_ORDER.taxi_RIdx = TB_RTAXISHARING.taxi_RIdx AND TB_ORDER.taxi_Idx = TB_RTAXISHARING.idx limit 1 ) AS taxi_OrdState  ";

//목록
$query = "";
/*$query = "SELECT idx, taxi_MemId, taxi_RMemId, taxi_RSaddNm, taxi_REaddr, taxi_RTPrice, taxi_RState, reg_Date {$mnSql} {$mnSql} {$priceSql} {$stmSql} FROM TB_RTAXISHARING {$sql_search} {$sql_order} limit  {$from_record}, {$rows}" ;*/
$query = "SELECT idx, taxi_MemId, taxi_RMemId, taxi_RTPrice, taxi_RState, reg_Date {$mnSql} {$mnSql} FROM TB_RTAXISHARING {$sql_search} {$sql_order} limit  {$from_record}, {$rows}" ;
//echo $query."<BR>";
//exit;
$stmt = $DB_con->prepare($query);
$stmt->bindValue(":taxi_SIdx",$taxiSIdx);

if ($od_rstatus != "" ) {
    $stmt->bindValue(":od_rstatus",$od_rstatus);
}

if ($fr_rdate != "" || $to_rdate != "" ) {
    $stmt->bindValue(":fr_rdate",$fr_rdate);
    $stmt->bindValue(":to_rdate",$to_rdate);
}

if($findrWord != "")  {
    $stmt->bindValue(':findword','%'.$findword.'%');
}


$stmt->execute();
$numCnt = $stmt->rowCount();


$qstr = "fr_date=".urlencode($fr_date)."&amp;to_date=".urlencode($to_date)."&amp;findType=".urlencode($findType)."&amp;findword=".urlencode($findword);

include "../common/inc/inc_gnb.php";  //헤더
include "../common/inc/inc_menu.php";  //메뉴

?>
<script type="text/javascript" src="<?=DU_UDEV_DIR?>/member/js/memberManager.js"></script>

<div id="wrapper">
    <div id="container" class="">
        <div class="container_wr">
        <h1 id="container_title">매칭신청 관리</h1>

		<div class="local_ov01 local_ov">
			<span class="btn_ov01"><span class="ov_txt">총 건수 </span><span class="ov_num"><?=number_format($totalCnt);?>건 </span>
		</div>

        <form class="local_sch03 local_sch"  autocomplete="off">
        <div>
            <strong>분류</strong>
        	<label for="findType" class="sound_only">검색대상</label>
        	<select name="findType" id="findType">
        		<option value="taxi_Saddr" <?if($findType=="taxi_Saddr"){?>selected<?}?>>출발지</option>
        		<option value="taxi_Eaddr" <?if($findType=="taxi_Eaddr"){?>selected<?}?>>목적지</option>
        		<option value="mem_Id" <?if($findType=="mem_Id"){?>selected<?}?>>아이디</option>
        	</select>
        	<label for="findword" class="sound_only">검색어<strong class="sound_only"> 필수</strong></label>
        	<input type="text" name="findword" id="findword" value="<?=$findword?>" class=" frm_input">
        </div>

        <div>
            <strong>상태</strong>
        	<span class="bg <? if ($od_status == "") { ?>all_on<? } ?>">
            <input type="radio" name="od_status" value="" id="od_status_all" <?php echo get_checked($od_status, ''); ?>>
            <label for="od_status_all">전체</label>
        	</span>
        	<span class="bg <? if ($od_status == "1") { ?>c01_on<? } ?>">
            <input type="radio" name="od_status" value="1" id="od_status_matchS" <?php echo get_checked($od_status, '1'); ?>>
            <label for="od_status_matchS">매칭요청</label>
        	</span>
        	<span class="bg <? if ($od_status == "2") { ?>c02_on<? } ?>">
            <input type="radio" name="od_status" value="2" id="od_status_matchR" <?php echo get_checked($od_status, '2'); ?>>
            <label for="od_status_matchR">예약요청</label>
        	</span>
        	<span class="bg <? if ($od_status == "3") { ?>c03_on<? } ?>">
            <input type="radio" name="od_status" value="3" id="od_status_meetS" <?php echo get_checked($od_status, '3'); ?>>
            <label for="od_status_meetS">거절</label>
        	</span>
        	<span class="bg <? if ($od_status == "4") { ?>c04_on<? } ?>">
            <input type="radio" name="od_status" value="4" id="od_status_meetC" <?php echo get_checked($od_status, '4'); ?>>
            <label for="od_status_meetC">예약요청완료</label>
        	</span>
        	<span class="bg <? if ($od_status == "5") { ?>c05_on<? } ?>">
            <input type="radio" name="od_status" value="5" id="od_status_move" <?php echo get_checked($od_status, '5'); ?>>
            <label for="od_status_move">만남중</label>
        	</span>
        	<span class="bg <? if ($od_status == "6") { ?>c06_on<? } ?>">
            <input type="radio" name="od_status" value="6" id="od_status_complte" <?php echo get_checked($od_status, '6'); ?>>
            <label for="od_status_complte">이동중</label>
        	</span>
        	<span class="bg <? if ($od_status == "7") { ?>c07_on<? } ?>">
            <input type="radio" name="od_status" value="7" id="od_status_cancel" <?php echo get_checked($od_status, '7'); ?>>
            <label for="od_status_cancel">완료</label>
        	</span>
        	<span class="bg <? if ($od_status == "8") { ?>c08_on<? } ?>">
            <input type="radio" name="od_status" value="8" id="od_status_cancel" <?php echo get_checked($od_status, '8'); ?>>
            <label for="od_status_cancel">취소</label>
        	</span>
        	<span class="bg <? if ($od_status == "9") { ?>c09_on<? } ?>">
            <input type="radio" name="od_status" value="9" id="od_status_cancel" <?php echo get_checked($od_status, '9'); ?>>
            <label for="od_status_cancel">취소사유확인</label>  
        	</span>
        	<span class="bg <? if ($od_status == "10") { ?>c10_on<? } ?>">  
            <input type="radio" name="od_status" value="10" id="od_status_cancel" <?php echo get_checked($od_status, '10'); ?>>
            <label for="od_status_cancel">거래완료확인</label>  
        	</span> 
        	<span class="bg <? if ($od_status == "11") { ?>c11_on<? } ?>">  
            <input type="radio" name="od_status" value="11" id="od_status_cancel" <?php echo get_checked($od_status, '11'); ?>>
            <label for="od_status_cancel">취소처리(본사)</label>  
        	</span> 
        	<span class="bg <? if ($od_status == "12") { ?>c12_on<? } ?>">  
            <input type="radio" name="od_status" value="12" id="od_status_cancel" <?php echo get_checked($od_status, '12'); ?>>
            <label for="od_status_cancel">완료처리(본사)</label>  
        	</span> 
        </div>
        
        <div class="sch_last">
            <strong>생성일자</strong>
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
        	<?=get_apaging($rows, $rpage, $total_page, "$_SERVER[PHP_SELF]?$qstr"); ?>
        </nav>
        
        <form name="fmlist" id="fmlist"  method="post" autocomplete="off">
        
        <div class="tbl_head01 tbl_wrap">
            <table>
            <caption>쉐어링 매칭 목록</caption>
            <thead>
            <tr>
                <th scope="col" id="mb_list_chk" >
                    <label for="chkall" class="sound_only">전체</label>
                    <input type="checkbox" name="chkall" class="chkc" id="chkAll">
                </th>
                <th scope="col" id="mb_list_idx">매칭요청번호</th>
                <th scope="col" id="mb_list_id">투게더</th>
                <th scope="col" id="mb_list_mailc">경유지</th>
                <th scope="col" id="mb_list_mailc">쉐어링요금</th>
                <th scope="col" id="mb_list_mailc">신청일</th>   
                <th scope="col" id="mb_list_mailc">OS</th>   
                <th scope="col" id="mb_list_mailc">상태</th>
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
	        
	        $memNickNm1 = $row['memNickNm'];
	        $memNickNm2 = $row['memNickNm2'];
	        
	        if ($memNickNm1 != "" ) {
	            $memNickNm = $memNickNm1;
	        } else if ($memNickNm2 != "" ) {
	            $memNickNm = $memNickNm2;
	        } else {
	            $memNickNm = "비회원";
	        }
	        
	        
	        $taxiMemId =  trim($row['taxi_MemId']);			// 생성자 아이디
	        
	        $taxiRTPrice =  trim($row['taxi_RTPrice']);			// 투게더 추가 택시요금
	        
	        //생성자 기타 정보보
	        $minfoeQuery = "";
	        $minfoeQuery = "SELECT taxi_Type, taxi_Route, taxi_Distance FROM TB_STAXISHARING_INFO WHERE taxi_Idx = :taxi_Idx LIMIT 1 ";
	        //echo $minfoeQuery."<BR>";
	        //exit;
	        $minfoetmt = $DB_con->prepare($minfoeQuery);
	        $minfoetmt->bindparam(":taxi_Idx",$taxiSIdx);
	        $minfoetmt->execute();
	        $minfoeNum = $minfoetmt->rowCount();
	        //echo $minfoeNum."<BR>";
	        
	        if($minfoeNum < 1)  { //아닐경우
	        } else {
	            while($minfoeRow=$minfoetmt->fetch(PDO::FETCH_ASSOC)) {
	                $taxiType = trim($minfoeRow['taxi_Type']);						//출발타입 ( 0: 바로출발, 1: 예약출발)
	                $taxiRoute = trim($minfoeRow['taxi_Route']);					// 경유가능여부 ( 0: 경유가능, 1: 경유불가)
	                $taxiDistance = trim($minfoeRow['taxi_Distance']);			   // 예상거리
	            }
	        }
	        
	        
	        //생성자 신청 정보 가져오기
	        $minfoQuery = "";
	        $minfoQuery = "SELECT taxi_TPrice, taxi_Price, taxi_Per FROM TB_STAXISHARING WHERE idx = :idx LIMIT 1 ";
	        //echo $minfoQuery."<BR>";
	        //exit;
	        $minfoStmt = $DB_con->prepare($minfoQuery);
	        $minfoStmt->bindparam(":idx",$taxiSIdx);
	        $minfoStmt->execute();
	        $minfoNum = $minfoStmt->rowCount();
	        //echo $minfoNum."<BR>";
	        
	        if($minfoNum < 1)  { //아닐경우
	        } else {
	            while($minfoRow=$minfoStmt->fetch(PDO::FETCH_ASSOC)) {
	                $taxiTPrice =  trim($minfoRow['taxi_TPrice']);	    // 총택시요금
	                $taxiPrice =  (int)trim($minfoRow['taxi_Price']);	    // 희망쉐어링요금
	                $taxiPer =  trim($minfoRow['taxi_Per']);		// 희망쉐어링 %
	            }
	        }
	        
	        
	        if ($taxiRoute == "0") {//경유가능
	            
	            //요청자 지도 정보 가져오기
	            $mapRQuery = "SELECT taxi_RSaddr, taxi_RSdong from TB_RTAXISHARING_MAP WHERE taxi_SIdx = :taxi_SIdx AND taxi_MemId = :taxi_MemId AND taxi_RIdx = :taxi_RIdx  " ;
	            $mapRStmt = $DB_con->prepare($mapRQuery);
	            $mapRStmt->bindparam(":taxi_SIdx",$taxiSIdx);
	            $mapRStmt->bindparam(":taxi_MemId",$taxiMemId);
	            $mapRStmt->bindparam(":taxi_RIdx",$row['idx']);
	            $mapRStmt->execute();
	            $mapRNum = $mapRStmt->rowCount();
	            
	            if($mapRNum < 1)  { //아닐경우
	            } else {
	                while($mapRRow=$mapRStmt->fetch(PDO::FETCH_ASSOC)) {
	                    $taxiRSaddr = trim($mapRRow['taxi_RSaddr']);			//경유지 주소
	                    $taxiRdong  = trim($mapRRow['taxi_RSdong']);			//경유지 동명
	                }
	            }
	            
	        } 
	        
            //생성 지도정보
            $mapQuery = "";
            $mapQuery = "SELECT taxi_Saddr, taxi_Sdong, taxi_Eaddr, taxi_Edong, taxi_ELat, taxi_ELng FROM TB_STAXISHARING_MAP WHERE taxi_Idx = :taxi_Idx LIMIT 1 ";
            //echo $mapQuery."<BR>";
            //exit;
            $mapStmt = $DB_con->prepare($mapQuery);
            $mapStmt->bindparam(":taxi_Idx",$taxiSIdx);
            $mapStmt->execute();
            $mapNum = $mapStmt->rowCount();
            //echo $mapNum."<BR>";
            
            if($mapNum < 1)  { //아닐경우
            } else {
                while($mapRow=$mapStmt->fetch(PDO::FETCH_ASSOC)) {
                    $taxiSaddr = trim($mapRow['taxi_Saddr']);			//출발지 주소
                    $taxiSdong = trim($mapRow['taxi_Sdong']);			//출발지 동명
                    $taxiEaddr = trim($mapRow['taxi_Eaddr']);		  //목적지 주소
                    $taxiEdong = trim($mapRow['taxi_Edong']);		  //목적지 동명
                }
            }
            
            
            if ($taxiRoute == "0") { //경유 가능일 경우
                
                $taxiRPrice = $taxiRTPrice * ($taxiPer / 100);   //요청자 요금
                //echo $taxiRPrice."<BR>";
                if ($taxiRPrice > $taxiPrice) { // 요청자금액이 클 경우
                    $taxiPrice = $taxiRPrice;	     // 요청자(결제금액)
                } else { // 요청자금액이 같을 경우
                    $taxiPrice = $taxiPrice;	     // 희망쉐어금액(결제금액)
                }
                
            } else {
                $taxiPrice = $taxiPrice;					  // 택시 희망 쉐어링 비용
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
	        
	        
	        $taxiOs = $row['taxi_Os'];
	        if($taxiOs == "") {
	            $taxiOsNm = "-";
	        } else if($taxiOs == 0) {
	            $taxiOsNm = "안드로이드";
	        } else if($taxiOs == 1) {
	            $taxiOsNm = "아이폰";
	        }
	        
	        
	        $taxi_State = $row['taxi_RState'] ;
	        
	        if($taxi_State == 1) {
	            $taxiState = "매칭요청";
	        } else if($taxi_State == 2) {
	            $taxiState = "예약요청";
	        } else if($taxi_State == 3) {
	            $taxiState = "거절";
	        } else if($taxi_State == 4) {
	            $taxiState = "예약요청완료";
	        } else if($taxi_State == 5) {
	            $taxiState = "만남중";
	        } else if($taxi_State == 6) {
	            $taxiState = "이동중";
	        } else if($taxi_State == 7) {
	            $taxiState = "완료";
	        } else if($taxi_State == 8) {
	            $taxiState = "취소";
	        } else if($taxi_State == 9) {
	            $taxiState = "취소사유확인";
	        } else if($taxi_State == 10) {
	            $taxiState = "거래완료확인";
	        } else if($taxi_State == 11) {
	            $taxiState = "취소처리(본사)";
	        } else if($taxi_State == 12) {
	            $taxiState = "완료처리(본사)";
	        }

    ?>


    <tr class="<?=$bg?>">
        <td headers="mb_list_chk" class="td_chk" >
            <input type="hidden" name="mb_id[<?=$row['idx']?>]" id="mb_id_<?=$row['idx']?>" value="<?=$row['mem_Id'] ?>" >
            <? if($taxi_State != "7" && $taxi_State != "8" ) { ?>
              <input type="checkbox"  id="chk" class="chk" name="chk" value="<?=$row['idx']?>">
            <? } else { ?>
           	  -
            <? } ?>
        </td>
        <td headers="mb_list_id"><a href="/udev/taxiSharing/taxiSharingSReg.php?mode=mod&taxiSIdx=<?=$taxiSIdx?>&ridx=<?=$row['idx']?>&<?=$qstr?>&rpage=<?=$rpage?>"><?=$row['idx']?></a></td>
        <td headers="mb_list_id"><a href="/udev/member/memberReg.php?mode=mod&id=<?=$row['taxi_RMemId']?>"><?=$row['taxi_RMemId']?> </br> (<?=$memNickNm?>)</a></td>
        <td headers="mb_list_id">출발지: <?=$taxiSaddr?> </br> <? if ($taxiRSaddr != "") {?> 경유지 : <?=$taxiRSaddr?> </br> <? } ?>도착지: <?=$taxiEaddr?></td>
        <td headers="mb_list_id"><?=number_format($taxiPrice)?></td>
        <td headers="mb_list_id"><?=$row['reg_Date']?></td>
        <td headers="mb_list_id"><?=$taxiOsNm?></td>
        <td headers="mb_list_id"><a href="#" class="btn btn_a<?=$taxi_State?>"><?=$taxiState?></a></td>
		<td headers="mb_list_mng" class="td_mng td_mng_s">
		  <a href="taxiSharingSReg.php?mode=mod&taxiSIdx=<?=$taxiSIdx?>&ridx=<?=$row['idx']?>&<?=$qstr?>&rpage=<?=$rpage?>" class="btn btn_03">상세</a>
		  
		  <? if($taxi_State != "7" && $taxi_State != "8" ) { ?>
<? if($_COOKIE['du_udev']['id'] != 'admin2'){?>
		  <a href="javascript:chkDel('<?=$row['idx']?>')" class="btn btn_02">삭제</a>
<? } ?>
		  <? } ?>
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

<div class="btn_fixed_top">
<? if($_COOKIE['du_udev']['id'] != 'admin2'){ ?>
	<a href="#ALDel" id="bt_m_a_del" class="btn btn_02">선택삭제</a>
<? } ?>
	<a href="taxiSharingList.php" class="btn btn_04">쉐어링 매칭 목록</a>
</div>

</form>
<nav class="pg_wrap">
	<?=get_apaging($rows, $rpage, $total_page, "$_SERVER[PHP_SELF]?$qstr"); ?>
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

