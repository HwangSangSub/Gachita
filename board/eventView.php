<?
include "../udev/lib/common.php";

$DB_con = db1();

$idx = trim($idx);

//페이지 추가하기.
$nquery = " SELECT idx, b_Title, b_Content, reg_Date FROM TB_BOARD WHERE idx = :idx AND b_Not = 'Y' AND b_Idx = 1 AND  b_Disply = 'Y' ORDER BY idx DESC";
$nqStmt = $DB_con->prepare($nquery);
$nqStmt->bindparam(":idx", $idx);
$nqStmt->execute();
$Ncounts = $nqStmt->rowCount();

?>
<!DOCTYPE html>
<html>

<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, target-densitydpi=medium-dpi" />
	<title>공지사항</title>
	<link rel="StyleSheet" HREF="css/common.css" type="text/css" title="Global CSS">
	<link rel="StyleSheet" HREF="css/board-style.css" type="text/css" title="Global CSS">
	<link rel="StyleSheet" HREF="css/jquery-ui-1.11.1.css" type="text/css" title="Global CSS">
	<script language='javascript' src="js/jquery-1.11.0.min.js" type="text/javascript"></script>
	<script language='javascript' src="js/jquery-ui-1.11.1.js" type="text/javascript"></script>
	<script language='javascript' src="js/jquery.animate-enhanced.js"></script>
	<script language='javascript' src="js/jquery.form.js" type="text/javascript"></script>
	<script language='javascript' src="js/common.js" type="text/javascript"></script>
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/meyer-reset/2.0/reset.min.css">
	<style>
		@import url('https://cdn.jsdelivr.net/gh/orioncactus/pretendard/dist/web/static/pretendard.css');

		@font-face {
			font-family: "Pretendard-SemiBold";
			font-style: normal;
			font-weight: 600;
			src: url("https://fonts.animaapp.com/Pretendard-SemiBold") format("opentype");
		}

		@font-face {
			font-family: "Pretendard-Medium";
			font-style: normal;
			font-weight: 500;
			src: url("https://fonts.animaapp.com/Pretendard-Medium") format("opentype");
		}

		@font-face {
			font-family: "Pretendard-Regular";
			font-style: normal;
			font-weight: 400;
			src: url("https://fonts.animaapp.com/Pretendard-Regular") format("opentype");
		}

		@font-face {
			font-family: "Pretendard-Medium";
			font-style: normal;
			font-weight: 500;
			src: url("https://fonts.animaapp.com/Pretendard-Medium") format("opentype");
		}



		:root {
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
			--font-size-xxs: 15px;
			--font-size-xxxs: 13px;

			--font-family-pretendard-medium: "Pretendard-Medium", Helvetica;
			--font-family-pretendard-semibold: "Pretendard-SemiBold", Helvetica;
			--font-family-pretendard-regular: "Pretendard-Regular", Helvetica;
			--font-family-pretendard-medium: "Pretendard-Medium", Helvetica;
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

		.title {
			margin-bottom: 21px;
		}

		.text {
			font-family: var(--font-family-pretendard-semibold);
			font-size: var(--font-size-xs);
			color: var(--title);
			padding-top: 12px;
			padding-bottom: 12px;
		}

		.date {
			font-family: var(--font-family-pretendard-medium);
			font-size: var(--font-size-xxs);
			color: var(--date);
		}

		.content {
			border-top: 1px solid var(--line);
			padding-top: 30px;
		}

		.content>pre {
			font-family: var(--font-family-pretendard-medium);
			font-size: var(--font-size-xxs);
			color: var(--content);
			line-height: 25px;
			text-wrap: balance;
		}

		.footer {
			margin-top: 22px;
		}

		.footer>div {
			font-family: var(--font-family-pretendard-regular);
			font-size: var(--font-size-xxs);
			color: var(--footer);
		}
	</style>
</head>

<body>
	<div class="du01">
		<?
		if ($Ncounts < 1) { //없을 경우
		} else {
			while ($v = $nqStmt->fetch(PDO::FETCH_ASSOC)) {
				$title = $v['b_Title'];
				$content = $v['b_Content'];
				$reg_Date = $v['reg_Date'];
				$regDate = date("y.m.d", strtotime($reg_Date));
		?>
				<div class="title">
					<div class="text"><?= $title ?></div>
					<div class="date"><?= $regDate ?></div>
				</div>
				<div class="line"></div>
				<div class="content">
					<pre><?= $content ?></pre>
				</div>
				<div class="footer">
					<div>당신이 몰랐던 새로운 이동, 가치타</div>
				</div>
		<?
			}
		}
		?>
	</div>
</body>

</html>