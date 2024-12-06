<?
	$menu = "4";
	$smenu = "2";

	include "../common/inc/inc_header.php";  //헤더 

	if($idx == "") {
		$msg = "잘못된 접근 방식입니다. 정확한 경로를 통해서 접근 하시길 바랍니다.";
		proc_msg2($msg);
	}

	$titNm = "취소내역 상세";

	$DB_con = db1();
	
	$query = "SELECT A.idx, B.taxi_SIdx, B.taxi_RIdx, A.cancle_MemId, A.cancle_MType, A.cancle_CanChk, A.cancle_CanRChk, A.cancle_CPart, A.cancle_CRPart, A.cancle_CMemo, A.reg_Date, C.mem_NickNm AS cancle_NickNm FROM TB_CANCLE_REASON A INNER JOIN TB_SMATCH_STATE B ON A.cancle_Idx = B.idx INNER JOIN TB_MEMBERS C ON A.cancle_MemId = C.mem_Id AND C.b_Disply = 'N' WHERE A.cancle_Idx = :idx GROUP BY A.idx, B.taxi_SIdx, B.taxi_RIdx, A.cancle_MemId, A.cancle_MType, A.cancle_CanChk, A.cancle_CanRChk, A.cancle_CPart, A.cancle_CRPart, A.cancle_CMemo, A.reg_Date, C.mem_NickNm ;" ;

	//echo $query;
	//exit;
	$stmt = $DB_con->prepare($query);
	$stmt->bindparam(":idx",$idx);
	$stmt->execute();
	$numCnt = $stmt->rowCount();


	$qstr = "fr_date=".urlencode($fr_date)."&amp;o_date=".urlencode($to_date)."&amp;findType=".urlencode($findType)."&amp;findword=".urlencode($findword);

	include "../common/inc/inc_gnb.php";  //헤더 
	include "../common/inc/inc_menu.php";  //메뉴 

?>
<script type="text/javascript" src="<?=DU_UDEV_DIR?>/member/js/memberManager.js"></script>

<div id="wrapper">

    <div id="container" class="">
        <h1 id="container_title"><?=$titNm?></h1>
        <div class="container_wr">
		<div class="tbl_head01 tbl_wrap">
			<table>
			<caption><?=$titNm?></caption>
			<colgroup>
				<col class="grid_4">
				<col>
				<col class="grid_4">
				<col>
			</colgroup> 
			<thead>
            <tr>
				<!--	단순내역보는  부분임으로 관리가 필요없을것 같아 주석처리 작업일 : 2019-01-07 작업자 : 황상섭 대리	
                <th scope="col" id="mb_list_chk" >
                    <label for="chkall" class="sound_only">전체</label>
                    <input type="checkbox" name="chkall" class="chkc" id="chkAll">
                </th>
				-->
                <th scope="col" id="mb_list_idx">순번</th>
                <th scope="col" id="mb_list_cidx">취소고유번호</th>
                <th scope="col" id="mb_list_sIdx">생성노선번호</th>
                <th scope="col" id="mb_list_rIdx">요청노선번호</th>
                <th scope="col" id="mb_list_id">취소요청자아이디</th>
                <th scope="col" id="mb_list_mailc">취소요청회원구분</th>
                <th scope="col" id="mb_list_mailc">취소사유</th>  
                <th scope="col" id="mb_list_mailc">취소동의여부</th> 
                <th scope="col" id="mb_list_mailc">취소동의사유</th>   
                <th scope="col" id="mb_list_mailc">기타취소사유</th>  
                <th scope="col" id="mb_list_mailc">취소일</th>
            </tr>
            </thead>
			<tbody>
			<? 
			$idx = 1;
			if($numCnt > 0){ 	   
				$stmt->setFetchMode(PDO::FETCH_ASSOC);
				$i = 0;
				while($row =$stmt->fetch()) {
					$i = $i + 1;
					$bg = 'bg'.($i%2);
					$cancle_idx = $row['idx'];
					$taxi_SIdx =  $row['taxi_SIdx'];				// 생성노선번호
					$taxi_RIdx =  $row['taxi_RIdx'];				// 요청노선번호
					$cancle_MemId =  $row['cancle_MemId'];			// 취소요청자아이디
					$cancle_NickNm =  $row['cancle_NickNm'];		// 취소요청자닉네임
					$cancle_MType =  $row['cancle_MType'];			// 취소요청회원구분 (p : 생성자, c : 요청자)
					$cancle_CanChk =  $row['cancle_CanChk'];		// 취소여부( Y,N )
					$cancle_CanRChk =  $row['cancle_CanRChk'];		// 취소동의여부( Y,N )
					$cancle_CPart =  $row['cancle_CPart'];			// 취소사유(1,2,3,4)
					$cancle_CRPart =  $row['cancle_CRPart'];		// 취소동의사유(1,2)
					$cancle_CMemo =  $row['cancle_CMemo'];			// 기타 취소 사유 메모
					$reg_Date =  $row['reg_Date'];					// 등록일

					if($cancle_MType == "p"){
						$cancleMType = "메이커";
					}else{
						$cancleMType = "투게더";
					}
						
					if($cancle_CanChk == "Y"){
						$cancleCanChk = "동의";
					}else{
						$cancleCanChk = "미동의";
					}
					if($cancle_CanRChk == "Y"){
						$cancleCanRChk = "동의";
					}else{
						$cancleCanRChk = "미동의";
					}


					if($cancle_CPart == 1) {
						$cancleCPart = "택시가 잡히지 않습니다.";
					} else if($cancle_CPart == 2) {
						$cancleCPart = "나의 사유로 인해 취소가 불가피 합니다.";
					} else if($cancle_CPart == 3) {
						$cancleCPart = "상대방의 사유로 인해 취소가 불가피 합니다.";
					}
					
					if($cancle_CRPart == 1) {
						$cancleCRPart = "거래취소를 원하지 않습니다.";
					} else if($cancle_CRPart == 2) {
						$cancleCRPart = "거래 취소는 동일하나 다른 사유입니다.";
					} else if($cancle_CRPart == 3) {
						$cancleCRPart = "기타 (5분 초과 미응답)";
					} else if($cancle_CRPart == 4) {
						$cancleCRPart = "동의합니다.";
					}
				?>
				<tr class="<?=$bg?>">
					<!--	단순내역보는  부분임으로 관리가 필요없을것 같아 주석처리 작업일 : 2019-01-07 작업자 : 황상섭 대리
					<td headers="mb_list_chk" class="td_chk" >
						<input type="hidden" name="mb_id[<?=$row['idx']?>]" id="mb_id_<?=$row['idx']?>" value="<?=$row['mem_Id'] ?>" >
					</td>
					-->
					<td headers="mb_list_idx"><?=$idx?></td>
					<td headers="mb_list_cidx"><?=$cancle_idx?></td>
					<td headers="mb_list_id"><a href="taxiSharingReg.php?mode=mod&idx=<?=$taxi_SIdx?>" ><?=$taxi_SIdx?></a></td>
					<td headers="mb_list_id"><a href="taxiSharingSReg.php?mode=mod&ridx=<?=$taxi_RIdx?>" ><?=$taxi_RIdx?></a></td>
					<td headers="mb_list_id"><a href="/udev/member/memberReg.php?mode=mod&id=<?=$cancle_MemId?>"><?=$cancle_MemId?> </br> (<?=$cancle_NickNm?>)</a></td>
					<td headers="mb_list_id"><?=$cancleMType?></td>
					<td headers="mb_list_id"><?=$cancleCPart?></td>
					<td headers="mb_list_id"><?=$cancleCanRChk?></td>
					<td headers="mb_list_id"><?=$cancleCRPart?></td>
					<td headers="mb_list_id"><?=$cancle_CMemo?></td>
					<td headers="mb_list_id"><?=$reg_Date?></td>
				</tr>
				<?
					$idx++;
					} ?>
			<? }else{ ?>
				<tr>
					<td colspan="11" class="empty_table">자료가 없습니다.</td>
				</tr>
			<? } ?>
			</tbody>
			</table>
		</div>
		<script>
			function fmember_submit(f) 	{
				return true;
			}
		</script>

	</div>    

<?
	dbClose($DB_con);
	$stmt = null;
	$infoStmt = null;
	$mapStmt = null;
	$orderStmt = null;
	$pointStmt = null;

	 include "../common/inc/inc_footer.php";  //푸터 
	 
?>