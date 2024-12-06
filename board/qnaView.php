<?
/*======================================================================================================================

* 프로그램			: 자주 묻는 질문 인기질문 상세페이지
* 페이지 설명		: 자주 묻는 질문 인기질문 상세페이지
* 파일명          : qnaView.php

========================================================================================================================*/

include "../udev/lib/common.php";
include DU_COM . "/functionDB.php";

$DB_con = db1();

$idx = trim($idx);

//페이지 추가하기.
$nquery = " SELECT idx, b_Title, b_Cate, b_Content, reg_Date FROM TB_BOARD WHERE idx = :idx AND b_Idx = 2 AND b_Disply = 'Y' ORDER BY idx DESC";
$nqStmt = $DB_con->prepare($nquery);
$nqStmt->bindparam(":idx", $idx);
$nqStmt->execute();
$Ncounts = $nqStmt->rowCount();

//카테고리 확인
$query = "SELECT b_CateChk, b_CateName FROM TB_BOARD_SET WHERE b_Idx = 2 ORDER BY idx DESC";
$stmt = $DB_con->prepare($query);
$stmt->execute();
$num = $stmt->rowCount();

if ($num < 1) { //아닐경우
} else {
	$data = [];
	while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {

		$b_CateChk = $row['b_CateChk'];
		if ($b_CateChk == 'N') {
			$result = array("result" => false, "errorMsg" => "등록된 카테고리가 없습니다. 확인 후 다시 시도해주세요.");
		} else {
			$b_CateName = $row['b_CateName'];
			$chk = explode("&", $b_CateName);
			for ($i = 0; $i < count($chk); $i++) {
				$cateNo = $i + 1;
				$cate = array("cateNo" => (int)$cateNo, "cateName" => (string)$chk[$i]);
				array_push($data, $cate);
			}
		}
	}
}
?>
<!DOCTYPE html>
<html>

<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, target-densitydpi=medium-dpi" />
	<title>자주 묻는 질문</title>
	<link rel="StyleSheet" HREF="css/common.css" type="text/css" title="Global CSS">
	<link rel="StyleSheet" HREF="../common/css/pretendard/pretendard.css" type="text/css" title="Global CSS">
	<link rel="StyleSheet" HREF="css/board-style.css" type="text/css" title="Global CSS">
	<link rel="StyleSheet" HREF="css/jquery-ui-1.11.1.css" type="text/css" title="Global CSS">
	<script language='javascript' src="js/jquery-1.11.0.min.js" type="text/javascript"></script>
	<script language='javascript' src="js/jquery-ui-1.11.1.js" type="text/javascript"></script>
	<script language='javascript' src="js/jquery.animate-enhanced.js"></script>
	<script language='javascript' src="js/jquery.form.js" type="text/javascript"></script>
	<script language='javascript' src="js/common.js" type="text/javascript"></script>
	<style>
		:root {
			--category: #F0F8FD;
			--title: #5C6F9E;
			--date: #8A91A1;
			--line: #E1E1E1;
			--content: #5A5C5E;
			--footer: #5C6F9E;

			--font-size-l: 40px;
			--font-size-m: 20px;
			--font-size-s: 18px;
			--font-size-xl: 48px;
			--font-size-xs: 17px;
			--font-size-xxs: 15.5px;
			--font-size-xxxs: 15px;
		}

		body {
			top: 0px;
			left: 0px;
			width: 100%;
			height: 100vh;
			display: flex;
			flex-wrap: wrap;
			background: #FFFFFF 0% 0% no-repeat padding-box;
			opacity: 1;
		}

		.du01 {
			padding: 30px;
			display: flex;
			flex-wrap: wrap;
			justify-content: flex-start;
			flex-direction: column;
		}

		.cate_title {
			display: flex;
			justify-content: center;
			align-content: flex-start;
			flex-direction: column;
			flex-wrap: wrap;
			margin-bottom: 21px;
		}

		.cate_title>.category {
			padding: 7px 12px;
			border-radius: 5px;
			background-color: var(--category);
			font-family: var(--font-family-pretendard-semibold) !important;
			font-size: var(--font-size-xxxs);
			color: var(--title);
			line-height: 18px;
		}

		.text {
			font-family: var(--font-family-pretendard-semibold) !important;
			font-size: var(--font-size-xs);
			color: var(--title);
			line-height: 24px;
			padding-top: 12px;
			padding-bottom: 26px;
		}

		.content>.content_pre {
			width: 100%;
			font-family: var(--font-family-pretendard-medium) !important;
			font-size: var(--font-size-xxs);
			color: var(--content);
			line-height: 25px;
			/* white-space: pre-wrap; */
		}

		.content>.content_pre>p>b {
			font-family: var(--font-family-pretendard-semibold) !important;
		}
	</style>
	<script type="text/javascript">
		$(document).ready(function() {
			// 예시: 모바일 여부를 출력하는 코드
			function isMobileDevice() {
				var userAgent = navigator.userAgent.toLowerCase();
				var mobileKeywords = ['mobile', 'iphone', 'ipod', 'android', 'blackberry', 'windows phone'];

				for (var i = 0; i < mobileKeywords.length; i++) {
					if (userAgent.indexOf(mobileKeywords[i]) !== -1) {
						return true;
					}
				}

				return false;
			}
			var isMobile = isMobileDevice();
			// if (isMobile) {
			// 	var elements = document.getElementsByTagName('*');
			// 	// 각 요소의 스타일 수정하기
			// 	var widthRate = window.innerWidth / 375;
			// 	var heightRate = window.innerHeight / 667;
			// 	if (widthRate > heightRate) {
			// 		var rate = heightRate;
			// 	} else {
			// 		var rate = widthRate;
			// 	}

			// 	// var newFontSize = currentFontSize * (rate); // 휴대폰 기준 폭을 기준으로 폰트 크기를 조정
			// 	for (var i = 0; i < elements.length; i++) {
			// 		var element = elements[i];
			// 		var styles = window.getComputedStyle(element);
			// 		// fontsize 속성이 존재하는 경우에만 처리
			// 		if (styles.fontSize) {
			// 			var currentFontSize = parseFloat(styles.fontSize);
			// 			var newFontSize = currentFontSize * rate; // 폰트 비율에 따라 동적으로 값을 가져옴
			// 			element.style.fontSize = newFontSize + 'px';
			// 		}
			// 		if (styles.lineHeight) {
			// 			var currentHeight = parseFloat(styles.lineHeight);
			// 			var newHeight = currentHeight * rate; // 폰트 비율에 따라 동적으로 값을 가져옴
			// 			element.style.lineHeight = newHeight + 'px';
			// 		}
			// 	}
			// }
		});
	</script>
</head>

<body>
	<div class="du01">
		<?
		if ($Ncounts < 1) { //없을 경우
		} else {
			while ($v = $nqStmt->fetch(PDO::FETCH_ASSOC)) {
				$title = $v['b_Title'];
				$b_Cate = $v['b_Cate'];
				$cate = (int)$b_Cate - 1;
				// $content = $v['b_Content'];

				// $b_Content = html_entity_decode($v["b_Content"]);
				// $content = str_replace(
				// 	'\"',
				// 	'',
				// 	$b_Content
				// );
				$b_Content = html_Decode($v['b_Content']);
				$reg_Date = $v['reg_Date'];
				$regDate = date("y.m.d", strtotime($reg_Date));
		?>
				<div class="cate_title">
					<div class="category"><?= $data[$cate]['cateName'] ?></div>
				</div>
				<div class="title">
					<div class="text"><?= $title ?></div>
				</div>
				<div class="content">
					<div class="content_pre"><?= $b_Content ?></div>
				</div>
		<?
			}
		}
		?>
	</div>
</body>

</html>