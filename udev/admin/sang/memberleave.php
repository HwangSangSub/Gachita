<?
	$menu = "1";
	$smenu = "1";

	include "../common/inc/inc_header.php";  //헤더 

	$base_url = $PHP_SELF;

	$qstr = "fr_date=".urlencode($fr_date)."&amp;to_date=".urlencode($to_date)."&amp;findType=".urlencode($findType)."&amp;findword=".urlencode($findword);
	$sqstr = "id=".urlencode($id)."&amp;fr_date=".urlencode($fr_date)."&amp;to_date=".urlencode($to_date)."&amp;findType=".urlencode($findType)."&amp;findword=".urlencode($findword);
	
	

	include "../common/inc/inc_gnb.php";  //헤더 
	include "../common/inc/inc_menu.php";  //메뉴 
	$loginId =  $_COOKIE['du_udev']['id'];

?>
<script type="text/javascript" src="<?=DU_UDEV_DIR?>/member/js/member.js"></script>
<script type="text/javascript">
	function memberdel(){
		var memId = $('#memId').val();
		var login_Id = $('#loginId').val();
		if(memId != ''){
			$.ajax({
				url:"/udev/admin/lib/memDelete.php",
				type:'POST',
				dataType : 'json',
				data: {
					memId : memId,
					loginId : login_Id
				},
				success:function(rs){
					if(rs.result == "error"){
						alert(rs.errorMsg);
						location.reload();
					}else{
						alert("회원정보삭제(탈퇴) 성공");
						location.reload();
					}
				},
				error:function(jqXHR, textStatus, errorThrown){
					alert("에러 발생~~ \n" + textStatus + " : " + errorThrown);
				}
			});
		}else{
			alert("입력한 아이디가 없습니다.");
			location.reload();
		}
	}
</script>
<div id="wrapper">
    <div id="container" class="">
        <div class="container_wr">
        <h1 id="container_title">회원정보삭제 (운영서버)</h1>
		<span style="font-weight:bold;">해당메뉴는 운영서버에서 신규회원(가입부터 시작) 테스트를 위한 메뉴입니다. 그외 용도로는 사용하지 말아 주세요.</span><br><br>
		<!--접속아이디 : <?echo $_COOKIE['du_udev']['id'];?><br>
		접속정보 : <?print_r($_COOKIE['du_udev']);?>-->
		<input type="hidden" name="loginId" id="loginId"  value="<?=$loginId?>"/>
		<span>회원아이디 : </span><input type="text" id="memId" name="memId" />&nbsp;&nbsp;&nbsp;&nbsp;<input type="button" onclick="memberdel();" value="삭제" />
<script>
	$(function(){
		$("#fr_date, #to_date").datepicker({ changeMonth: true, changeYear: true, dateFormat: "yy-mm-dd", showButtonPanel: true, yearRange: "c-99:c+99", maxDate: "+0d" });
	});

</script>

</div>    

<? include "../common/inc/inc_footer.php";  //푸터 ?>
