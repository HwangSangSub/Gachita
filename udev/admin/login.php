<?
include "../../udev/lib/common.php";
include "../../lib/alertLib.php";
$DB_con = db1();

//관리자아이디 조회
$query = "";
$query = "SELECT A.idx, A.mem_Id FROM TB_MEMBERS A WHERE A.mem_Lv IN (0, 1) AND A.b_Disply = 'N'; ";;
$stmt = $DB_con->prepare($query);
$stmt->execute();
$chkAdminNum = $stmt->rowCount();
if ($chkAdminNum < 1) { //등록된 관리자가 없는 경우
	$chknum = 0;
} else { // 관리자 일 경우 푸시 발송 시작
	$chknum = 1;
	$admin_list = array();				//관리자 아이디를 저장하기 위한 배열 선언
	while ($chkAdminRow = $stmt->fetch(PDO::FETCH_ASSOC)) {
		array_push($admin_list, $chkAdminRow['mem_Id']);			//관리자 아이디를 배열에 저장
		//$amem_Id = $chkAdminRow['mem_Id'];
	}
}
// 로그인 확인.
if ($du_udev['id'] != "") {
	if (in_array($du_udev['id'], $admin_list)) {
		if ($du_udev['lv'] == 0 ||  $du_udev['lv'] == 1) { //최고권한관리자 	
			header("location:/udev/admin/sang/mirpay.php");
			exit;
		} else {
			$message = "잘못된 접근 방식입니다.";
			proc_msg2($message);
		}
	} else {
		$message = "개발자만 로그인이 가능합니다.";
		proc_msg2($message);
	}
}

?>



<!doctype html>
<html lang="ko">

<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width,initial-scale=1.0,minimum-scale=0,maximum-scale=10,user-scalable=yes">
	<meta name="HandheldFriendly" content="true">
	<meta name="format-detection" content="telephone=no">
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
	<title>개발자 로그인</title>
	<link rel="stylesheet" href="/udev/common/css/mobile.css">
	<link rel="stylesheet" href="/udev/common/css/style.css">

	<script src="//cdnjs.cloudflare.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
	<script src="//code.jquery.com/jquery-3.2.1.min.js"></script>
	<script type="text/javascript" src="//code.jquery.com/jquery-migrate-1.2.1.min.js"></script>

<body>

	<div id="mb_login" class="mbskin">
		<h1>로그인</h1>
		<form id="loginform" name="loginform" method="post" autocomplete="off">

			<div id="login_frm">
				<label for="login_id" class="sound_only">아이디<strong class="sound_only"> 필수</strong></label>
				<input type="text" name="login_id" id="login_id" placeholder="아이디(필수)" required class="frm_input required" maxLength="20">
				<label for="login_pw" class="sound_only">비밀번호<strong class="sound_only"> 필수</strong></label>
				<input type="password" name="login_pw" id="login_pw" placeholder="비밀번호(필수)" required class="frm_input required" maxLength="20">
				<div>
					<label for="login_auto_login"></label>
				</div>
				<input type="submit" value="로그인" id="login" class="btn_submit">
			</div>

		</form>

	</div>


	<script type="text/javascript">
		//<![CDATA[
		$(document).ready(function(e) {

			$(".sound_only").live("focus", function() {
				$("label[for=" + $(this).attr("id") + "]").hide();
			}).live("blur", function() {
				if (!$.trim($(this).val())) $("label[for=" + $(this).attr("id") + "]").show();
				else $("label[for=" + $(this).attr("id") + "]").hide();
			});

			if (!$("#login_id").val()) {
				$("#login_id").focus();
			} else {
				$("#login_id").blur();
				$("#login_pw").focus();
			}

			// 입력폼에서  엔터 입력시 처리
			$('#loginform input').keypress(function(e) {
				if (e.which == 13) {
					$('#login').click();
				}
			});

			$("#login").click(function() {
				var message, chk;


				if ($.trim($('#login_id').val()) != '' && $.trim($('#login_pw').val()) != '') {

					var action = "/udev/admin/loginProc.php";
					var form_data = {
						user_id: $("#login_id").val(),
						user_pw: $("#login_pw").val(),
						i_wk: "login",
						mode: "adm",
						is_ajax: 1
					};

					//로그인 로직 타게 적용
					$.ajax({
						type: "POST",
						url: action,
						data: form_data,
						success: function(response) {

							if ($.trim(response) == 'success') {
								$('#loginform').attr('action', '/udev/admin/sang/mirpay.php').submit();
							} else if ($.trim(response) == 'error') {
								alert("아이디와 비밀번호를 다시 확인해 주세요!");
								$("form:first").submit();
							} else if ($.trim(response) == 'error2') {
								alert("개발자만 로그인이 가능합니다!");
								$("form:first").submit();
							}
						}
					});
					return false;

				}

			});
		});

		//-->
	</script>


	<!-- ie6,7에서 사이드뷰가 게시판 목록에서 아래 사이드뷰에 가려지는 현상 수정 -->
	<!--[if lte IE 7]>
<script>
$(function() {
    var $sv_use = $(".sv_use");
    var count = $sv_use.length;

    $sv_use.each(function() {
        $(this).css("z-index", count);
        $(this).css("position", "relative");
        count = count - 1;
    });
});
</script>
<![endif]-->

</body>

</html>
<?
dbClose($DB_con);
$stmt = null;
?>