<?
include "../../udev/lib/common.php";
include DU_COM . "/functionDB.php";

$base_url = $PHP_SELF;

$DB_con = db1();

$sql_search = " WHERE ox_UseBit = '0' AND ox_Status = '1'";

if ($findword != "") {
    $sql_search .= " AND `{$findType}` LIKE '%{$findword}%' ";
}
$findType = trim($findType);
$findword = trim($findword);

$oxQuery = "
	SELECT *
	FROM TB_OX
	{$sql_search}
	ORDER BY idx DESC;
";
$oxStmt = $DB_con->prepare($oxQuery);
if ($findword != "") {
    $oxStmt->bindValue(":findType", $findType);
    $oxStmt->bindValue(":findword", $findword);
}
$oxStmt->execute();
$oxNumCnt = $oxStmt->rowCount();
$from_record = 0;
?>
<!doctype html>
<html lang="ko">

<head>
    <meta charset="utf-8">
    <meta http-equiv="imagetoolbar" content="no">
    <meta http-equiv="X-UA-Compatible" content="IE=Edge">
    <title>가치타_OX퀴즈검색</title>
    <link rel="stylesheet" href="<?= DU_UDEV_DIR ?>/common/css/admin.css">
    <link type="text/css" href="//ajax.googleapis.com/ajax/libs/jqueryui/1.9.2/themes/base/jquery-ui.css" rel="stylesheet" />
    <!--[if lte IE 8]>
		<script src="<?= DU_UDEV_DIR ?>/common/js/html5.js"></script>
		<![endif]-->
    <script src="<?= DU_UDEV_DIR ?>/common/js/jquery-1.8.3.min.js"></script>
    <script src="<?= DU_UDEV_DIR ?>/common/js/jquery.menu.js?ver=<?= rand(); ?>"></script>
    <script src="<?= DU_UDEV_DIR ?>/common/js/common.js?ver=<?= rand(); ?>"></script>
    <script src="<?= DU_UDEV_DIR ?>/common/js/wrest.js?ver=<?= rand(); ?>"></script>
    <script src="<?= DU_UDEV_DIR ?>/common/js/placeholders.min.js"></script>
    <link rel="stylesheet" href="<?= DU_UDEV_DIR ?>/common/js/font-awesome/css/font-awesome.min.css">
    <link type="text/css" href="//ajax.googleapis.com/ajax/libs/jqueryui/1.9.2/themes/base/jquery-ui.css" rel="stylesheet" />
    <script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jqueryui/1.9.2/jquery-ui.min.js"></script>
    <script>
        function idSelect(oxIdx) {
            $("#conTodayOx", opener.document).val(oxIdx); //jquery 이용
            self.close();
        }
    </script>
</head>

<body>
    <form id="fsearch" name="fsearch" class="local_sch01 local_sch" target="<? $_SERVER['PHP_SELF'] ?>" method="get" autocomplete="off" style="margin-left:10px;">
        <label for="findType" class="sound_only">검색대상</label>
        <select name="findType" id="findType">
            <option value="ox_Cate" <? if ($findType == "ox_Cate") { ?>selected<? } ?>>카테고리</option>
            <option value="ox_Question" <? if ($findType == "ox_Question") { ?>selected<? } ?>>문제</option>
        </select>
        <label for="findword" class="sound_only">검색어<strong class="sound_only"> 필수</strong></label>
        <input type="text" name="findword" id="findword" value="<?= $findword ?>" class=" frm_input">
        <input type="submit" class="btn_submit" value="검색">
        <a href="<?= $base_url ?>" class="btn btn_06">새로고침</a>
    </form>
    <div class="tbl_head01 tbl_wrap">
        <table>
            <thead>
                <tr>
                    <th class="grid_1" id="idx">순번</th>
                    <th class="grid_2" id="ox_Cate">OX 카테고리</th>
                    <th class="grid_8" id="ox_Question">OX 문제</th>
                    <th class="grid_1" id="ox_Answer">OX 답</th>
                    <!-- <th scope="col" id="ox_Explanation">OX 설명</th> -->
                    <th class="grid_2" id="ox_Admin">관리</th>
                </tr>
            </thead>
            <tbody>
                <?
                if ($oxNumCnt > 0) {
                    $oxStmt->setFetchMode(PDO::FETCH_ASSOC);
                    while ($oxRow = $oxStmt->fetch()) {
                        $from_record++;
                        $ox_Idx = $oxRow['idx'];
                        if ($oxRow['ox_Cate'] == "1") {
                            $ox_Cate = "같이타기 이용";
                        } else if ($oxRow['ox_Cate'] == "2") {
                            $ox_Cate = "포인트(출금)";
                        } else if ($oxRow['ox_Cate'] == "3") {
                            $ox_Cate = "공지 및 이벤트";
                        } else  if ($oxRow['ox_Cate'] == "4") {
                            $ox_Cate = "아재 테스트";
                        } else  if ($oxRow['ox_Cate'] == "%") {
                            $ox_Cate = "등급";
                        } else {
                            $ox_Cate = "미분류";
                        }
                        $ox_Question = html_Decode(trim($oxRow['ox_Question']));
                        if ($oxRow['ox_Answer'] == "1") {
                            $ox_Answer = "그렇다";
                        } else if ($oxRow['ox_Answer'] == "2") {
                            $ox_Answer = "아니다";
                        } else {
                            $ox_Answer = "미분류";
                        }
                        $ox_Explanation = html_Decode(trim($oxRow['ox_Explanation']));
                ?>
                        <tr class="<?= $bg ?>">
                            <input type="hidden" name="ox_<?= $ox_Idx ?>" id="ox_<?= $ox_Idx ?>" value="<?= $ox_Idx ?>" />
                            <td headers="idx"><?= $from_record ?></td>
                            <td headers="ox_Cate"><?= $ox_Cate ?></td>
                            <td headers="ox_Question"><?= $ox_Question ?></td>
                            <td headers="ox_Answer"><?= $ox_Answer ?></td>
                            <!-- <td headers="ox_Explanation"><?= $ox_Explanation ?></td> -->
                            <td headers="ox_Admin">
                                <a href="javascript:idSelect('<?= $ox_Idx ?>');" class="btn btn_03">선택</a>
                            </td>
                        </tr>
                    <?
                    }
                    ?>
                <? } else { ?>
                    <tr>
                        <td colspan="5" class="empty_table">자료가 없습니다.</td>
                        <!-- <td colspan="6" class="empty_table">자료가 없습니다.</td> -->
                    </tr>
                <? } ?>
            </tbody>
        </table>
    </div>
</body>

</html>
<?
dbClose($DB_con);
?>