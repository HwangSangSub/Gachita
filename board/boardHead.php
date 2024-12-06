<!DOCTYPE HTML>
<html>

<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<!-- <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, target-densitydpi=medium-dpi" /> -->
	<title>가치타 <?= $subNm ?> 게시판</title>
	<script type="text/javascript" src="editor/js/HuskyEZCreator.js" charset="utf-8"></script>
	<link rel="StyleSheet" HREF="css/common.css" type="text/css" title="Global CSS">
	<link rel="StyleSheet" HREF="css/board-style.css" type="text/css" title="Global CSS">
	<link rel="StyleSheet" HREF="css/jquery-ui-1.11.1.css" type="text/css" title="Global CSS">
	<script language='javascript' src="js/jquery-1.11.0.min.js" type="text/javascript"></script>
	<script language='javascript' src="js/jquery-ui-1.11.1.js" type="text/javascript"></script>
	<script language='javascript' src="js/jquery.animate-enhanced.js"></script>
	<script language='javascript' src="js/jquery.form.js" type="text/javascript"></script>
	<script language='javascript' src="js/common.js" type="text/javascript"></script>
</head>
<script>

</script>

<body>
	<content>
		<nav id="nav" class="nav">
			<ul class="nav">
				<li <? if ($board_id == "1") { ?>class="on" <? } ?>>
					<a href="boardList.php?board_id=1">공지사항</a>
				</li>
				<li <? if ($board_id == "2") { ?>class="on" <? } ?>>
					<a href="boardList.php?board_id=2">자주 묻는 질문</a>
				</li>
				<!-- 
				<li>
					<a href="#">게시판1</a>
				</li>
				<li>
					<a href="#">게시판2</a>
				</li>
				<li>
					<a href="#">게시판3</a>
				</li>
				-->
			</ul>
		</nav>


		<div class="contents">