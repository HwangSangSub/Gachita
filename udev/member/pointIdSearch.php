<?
	include "../common/inc/inc_header.php";  //헤더 
	
	$DB_con = db1();
	$titNm = "회원검색";


	
	$view_id="등록된 회원이 없습니다.";

	if($_POST['mode'] == "search")
	{
		if($_POST['search_NickNm'])
		{
			$listQuery = "SELECT mem_Id, mem_NickNm FROM TB_MEMBERS WHERE mem_NickNm LIKE '%".$_POST['search_NickNm']."%' AND mem_Lv > 3 AND b_Disply = 'N'";
			$listStmt = $DB_con->prepare($listQuery);
			$listStmt->execute();
			$numCnt = $listStmt->rowCount();

			$view_id = "";

			if($numCnt > 0)
			{
				while($row = $listStmt->fetch()) {
					$mem_Id = $row['mem_Id'];
					$mem_NickNm = $row['mem_NickNm'];
					$view_id .= "<span onclick=\"javascript:partents_data('".$mem_Id."');\">".$mem_NickNm.'('.$mem_Id.')</span><br>';
				}
			}
			else
			{
				$view_id="등록된 회원이 없습니다.";
			}
		}
	}
?>
<script>
function partents_data(mem_Id)
{
	opener.setMemId(mem_Id);
	window.close(); 
}
</script>
<style>
#container {

    padding: 0 0 0 220px;
    margin-top: 50px;
    height: 100%;
    background: #fff;
    min-width: 400px;

}
</style>

<div id="wrapper">

    <div id="container" class="container-small" style="width:100%;padding:0px;min-width:400px;">

        <h1 id="container_title" style="padding-left:10px;top:0px;"><?=$titNm?></h1>
        <div class="container_wr">
			<form name="fmember" id="fmember" action="./pointIdSearch.php" onsubmit="return fmember_submit(this);" method="post" enctype="multipart/form-data" autocomplete="off">
			<input type="hidden" name="mode" id="mode" value="search">
			<div class="tbl_frm01 tbl_wrap">
				<table>
				<caption><?=$titNm?></caption>
				<tbody>
				<tr height="45">
					<th align="center">회원닉네임</th>
					<td class="objects">
						<input type="text" class="frm_input" name="search_NickNm" id="search_NickNm" style="width:60%;" placeholder="검색할 회원닉네임">&nbsp;<input type="submit" value="검색" class="btn_submit btn" accesskey='s'>
					</td>
				</tr>		
				<tr height="45">
					<th align="center">검색된 회원</th>
					<td class="objects">
						<div id="view_ids"><?= $view_id ?></div>
					</td>
				</tr>		
				<tr>
					<td colspan="2"></td>
				</tr>
				</table>
				
			

			
			</form>


			<script>
			function fmember_submit(f)
			{
				if (!f.search_NickNm.value) {
					alert('검색할 회원닉네임을 입력해주세요..');
					return false;
				}

				return true;
			}
			</script>
		</div>
	</div>   
</div>   	



<?
	dbClose($DB_con);
	$stmt = null;
	$meInfoStmt = null;
	$mEtcStmt = null;
	$mstmt = null;

	 
?>
