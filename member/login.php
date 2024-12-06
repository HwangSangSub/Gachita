<?
include "../lib/common.php";
include "../lib/alertLib.php";

// 로그인 확인.
if ($du_udev['id'] != "") {
	header("location:/board/boardList.php?board_id=1");
}

?>

<!doctype html>
<html lang="ko">

<head>
	<meta charset="utf-8">
	<meta http-equiv="imagetoolbar" content="no">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<title>로그인</title>
	<link rel="stylesheet" href="../common/css/default.css">
	<link rel="stylesheet" href="../common/css/style.css">
	<!--[if lte IE 8]>
<script src="https://demo.gnuboard.com/js/html5.js"></script>
<![endif]-->

	<script src="//cdnjs.cloudflare.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
	<script src="//code.jquery.com/jquery-3.2.1.min.js"></script>
	<script type="text/javascript" src="https://code.jquery.com/jquery-migrate-1.2.1.min.js"></script>

</head>

<body class="lang_ko_KR">

	<!-- Start Login { -->
	<div id="mb_login" class="mbskin">
		<h1>로그인</h1>

		<form id="loginform" name="loginform" method="post" autocomplete="off">

			<fieldset id="login_fs">
				<legend>회원로그인</legend>
				<div class="login_btn_inner">
					<label for="login_id" class="sound_only">회원로그인<strong class="sound_only"> 필수</strong></label>
					<input type="text" name="mb_id" id="login_id" required class="frm_input required" size="20" maxLength="20" placeholder="아이디">
					<label for="login_pw" class="sound_only">패스워드<strong class="sound_only"> 필수</strong></label>
					<input type="password" name="mb_password" id="login_pw" required class="frm_input required" size="20" maxLength="20" placeholder="패스워드">
				</div>
				<span class="login_auto">
				</span>
				<button type="submit" id="login" class="btn_submit">로그인</button>
			</fieldset>

			<aside id="login_info">
				<span>
				</span>
			</aside>


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
					var action = "loginProc.php";
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

							var chkValue = $.trim(response);

							if (chkValue == 'success') {
								$('#loginform').attr('action', '/board/boardList.php?board_id=1').submit();
							} else if (chkValue == 'error') {
								alert("아이디와 비밀번호를 다시 확인해 주세요!");
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

	<!-- } End Login -->


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