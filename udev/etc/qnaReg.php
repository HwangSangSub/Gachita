<?
	$menu = "3";
	$smenu = "4";

	include "../common/inc/inc_header.php";  //헤더 


	if($mode=="mod") {
		$titNm = "QNA관리 수정";
		
		$DB_con = db1();

		$query = "";
		$query = "SELECT idx, qna_Id, qna_Question, qna_Answer, q_Disply, reg_Date, update_Date FROM TB_TAXI_QNA WHERE idx = :idx" ;
		$stmt = $DB_con->prepare($query);
		$stmt->bindparam(":idx",$idx);
		$stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $qna_Id =  trim($row['qna_Id']);
        $qna_Question   =  trim($row['qna_Question']);
        $qna_Answer = trim($row['qna_Answer']);
        $q_Disply = trim($row['q_Disply']);
        $reg_Date =  trim($row['reg_Date']);
        $update_DATE = trim($row['update_DATE']);

		dbClose($DB_con);
		$stmt = null;
		
	} else {
		$mode = "reg";
		$titNm = "QNA관리 등록";

	}

	$qstr = "findType=".urlencode($findType)."&amp;findword=".urlencode($findword);

	include "../common/inc/inc_gnb.php";  //헤더 
	include "../common/inc/inc_menu.php";  //메뉴 

?>

<div id="wrapper">

    <div id="container" class="">
        <h1 id="container_title"><?=$titNm?></h1>
        <div class="container_wr">
		<form name="fmember" id="fmember" action="qnaProc.php" onsubmit="return fubmit(this);" method="post" enctype="multipart/form-data" autocomplete="off">
		<input type="hidden" name="mode" id="mode" value="<?=$mode?>">	
		<input type="hidden" name="idx" id="idx" value="<?=$idx?>">
		<input type="hidden" name="qstr" id="qstr"  value="<?=$qstr?>">
		<input type="hidden" name="page"  id="page"  value="<?=$page?>">

		<div class="tbl_frm01 tbl_wrap">
			<table>
			<caption>QNA관리</caption>
			<colgroup>
				<col class="grid_4">
				<col>
			</colgroup>
			<tbody>
				<tr>
					<th scope="row"><label for="qna_Id">메뉴명</label></th>
					<td>
						<select id="qna_Id" name="qna_Id" style="width:140px;">
							<option value="">위치선택</option>
							<option value="1" <?=($qna_Id == "1" ? "selected":"")?>>결제·포인트·환전</option>
							<option value="2" <?=($qna_Id == "2" ? "selected":"")?>>이용내역</option>
							<option value="3" <?=($qna_Id == "3" ? "selected":"")?>>이용수칙</option>
							<option value="4" <?=($qna_Id == "4" ? "selected":"")?>>비상신고</option>
							<option value="5" <?=($qna_Id == "5" ? "selected":"")?>>기타</option>
						</select>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="qna_Question">질문</label></th>
    				<td>
						<input type="text" name="qna_Question" value="<?=$qna_Question?>" id="qna_Question" required class="required frm_input" size="150"> 
					</td>
				</tr>
				
    			<tr>
    				<th scope="row"><label for="qna_Answer">답변</label></th>
    				<td>
    					<input type="text" name="qna_Answer" value="<?=$qna_Answer?>" id="qna_Answer" required class="required frm_input" class="frm_input" size="150">
    				</td>
    			</tr>				
				
    			<tr>
    				<th scope="row"><label for="q_Disply">사용여부</label></th>
    				<td>
    					<input type="radio" name="q_Disply" value="Y" id="q_Disply" <?=($q_Disply == "Y" )?"checked":"";?> checked/>
    					<label for="q_Disply">사용</label>
    					<input type="radio" name="q_Disply" value="N" id="q_Disply" <?=($q_Disply == "N")?"checked":"";?> />
    					<label for="q_Disply">사용안함</label>			
    				</td>
    			</tr>
				

				</tbody>
			</table>
		</div>

		<div class="btn_fixed_top">
			<a href="qnaList.php?<?=$qstr?>&page=<?=$page?>" class="btn btn_02">목록</a>
			<input type="submit" value="확인" class="btn_submit btn" accesskey='s'>
		</div>
		</form>


	<script>


		function fubmit(f) {

			if($.trim($('#qna_Id').val()) == ''){
				message = "메뉴를 선택해 주세요!";
				alert(message);
				chk = "#qna_Id";
				$(chk).focus();
				return false;
			} 

		    if ($.trim($('#qna_Question').val()) == ''){
			  message = "질문사항을 입력해 주세요!";
			  alert(message);
			  chk = "#qna_Question";
			  $(chk).focus();
			  return false;
		    } 

		    if ($.trim($('#qna_Answer').val()) == ''){
			  message = "답변을 입력해 주세요!";
			  alert(message);
			  chk = "#qna_Answer";
			  $(chk).focus();
			  return false;
		    } 

		    if ($.trim($(':radio[name="q_Disply"]:checked').val()) == ''){
			  message = "사용여부를 선택해 주세요!";
			  alert(message);
			  chk = "#q_Disply";
			  $(chk).focus();
			  return false;
		    } 

			return true;

		}


		</script>

	</div>    

<? include "../common/inc/inc_footer.php";  //푸터 ?>