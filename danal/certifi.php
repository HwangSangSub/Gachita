<?php
/*======================================================================================================================

* 프로그램				:  본인인증웹뷰
* 페이지 설명			:  본인인증웹뷰
* 파일명                :  certifi.php

========================================================================================================================*/
	include "../udev/lib/common.php"; 
	include "../lib/functionDB.php";  //공통 db함수

	$mem_Id  = trim($memId);		// 투게더 아이디

	$DB_con = db1();

	$mem_Idx = memIdxInfo($mem_Id);   //회원 주아이디
?>
<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0, user-scalable=no" />
<meta name="apple-mobile-web-app-capable" content="yes" />
<meta name="apple-mobile-web-app-status-bar-style" content="black" />
<link rel="apple-touch-icon" href=""/>
<link rel="apple-touch-startup-image" href="" />

<!-- jQuery -->
<script type="text/javascript" src="https://code.jquery.com/jquery-1.12.4.min.js" ></script>
<!-- iamport.payment.js -->
<script type="text/javascript" src="https://cdn.iamport.kr/js/iamport.payment-1.1.5.js"></script>
<script type="text/javascript" src="https://service.iamport.kr/js/iamport.payment-1.1.5.js"></script>
<script type="text/javascript">
	$( document ).ready(function() {
		submitForm();
	});
	function submitForm(){
		IMP.init('imp65751719');
		IMP.certification({
			merchant_uid : '<?=$mem_Idx?>' //본인인증과 연관된 가맹점 내부 주문번호가 있다면 넘겨주세요
		}, function(rsp) {
			if ( rsp.success ) {
					if( /Android/i.test(navigator.userAgent)) {
						// 안드로이드
						console.log("imp_uid="+rsp.imp_uid);
						Android.certDone(rsp.imp_uid);
						// callDoneIPhone(rsp.imp_uid);
					} else if (/iPhone|iPad|iPod/i.test(navigator.userAgent)) {
						// 아이폰
						callDoneIPhone(rsp.imp_uid);
					}else{
						alert(rsp.imp_uid);
						console.log("imp_uid="+rsp.imp_uid);
					}
			} else {
				// 인증취소 또는 인증실패
				if( /Android/i.test(navigator.userAgent)) {
					// 안드로이드
					Android.certFail();
					// callFailIPhone();
				} else if (/iPhone|iPad|iPod/i.test(navigator.userAgent)) {
					callFailIPhone();
				}else{
					var msg = '인증에 실패하였습니다.';
					msg += '에러내용 : ' + rsp.error_msg;
					alert(msg);
				}
			}
		});
	}

	function callDoneIPhone(uid) {
	  try {
	    window.webkit.messageHandlers.callIPhoneNative.postMessage( uid );
	  }catch(err) {
	      alert(err);
	  }
	}
	function callFailIPhone() {
	  try {
	    window.webkit.messageHandlers.callIPhoneNative.postMessage("fail");
	  }catch(err) {
	      alert(err);
	  }
	}

	function web_href(){

	}
</script>
<style>
	.imp-header>.imp-close{
		display:none;
	}
</style>
<title>가치타 본인인증</title>
</head>
<body>
</body>
</html>
<?

	$DB_con = db1();
?>
