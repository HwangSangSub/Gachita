<?
include "../../udev/lib/common.php";
include DU_COM . "/functionDB.php";

$base_url = $PHP_SELF;

$type = trim($type);    // 조회타입 (notice : 공지사항, event : 이벤트)
$sort = trim($sort);    // 고정순서 1, 2, 3

$DB_con = db1();
if ($type == 'notice') {
    $tagIdName = "conTopNotice";
    $titleName = "공지사항";
    $searchTitle = "b_Title";
    $sql_search = " WHERE b_Not = 'Y' AND b_Idx = 1 AND b_Disply = 'Y'";

    if ($findword != "") {
        $sql_search .= " AND `{$findType}` LIKE '%{$findword}%' ";
    }
    $findType = trim($findType);
    $findword = trim($findword);

    $query = "
        SELECT idx, b_Title AS title, t_Disply AS tDisply, t_Sort AS tSort
        FROM TB_BOARD
        {$sql_search}
        ORDER BY t_Sort;
    ";
    $stmt = $DB_con->prepare($query);
    if ($findword != "") {
        $stmt->bindValue(":findType", $findType);
        $stmt->bindValue(":findword", $findword);
    }
    $stmt->execute();
    $numCnt = $stmt->rowCount();
    $from_record = 0;
} else if ($type == 'event') {
    $tagIdName = "conTopEvent";
    $titleName = "이벤트";
    $searchTitle = "event_Title";
    $sql_search = " WHERE event_EndBit = 'N'";

    if ($findword != "") {
        $sql_search .= " AND `{$findType}` LIKE '%{$findword}%' ";
    }
    $findType = trim($findType);
    $findword = trim($findword);

    $query = "
        SELECT idx, event_Title AS title, event_Tdisply AS tDisply, event_Tsort AS tSort
        FROM TB_EVENT
        {$sql_search}
        ORDER BY event_Tsort;
    ";
    $stmt = $DB_con->prepare($query);
    if ($findword != "") {
        $stmt->bindValue(":findType", $findType);
        $stmt->bindValue(":findword", $findword);
    }
    $stmt->execute();
    $numCnt = $stmt->rowCount();
    $from_record = 0;
} else {
}
?>
<!doctype html>
<html lang="ko">

<head>
    <meta charset="utf-8">
    <meta http-equiv="imagetoolbar" content="no">
    <meta http-equiv="X-UA-Compatible" content="IE=Edge">
    <title><?= $titleName ?>검색</title>
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
        function selIdx(idx, num) {
            var title = $("#title_" + idx).val(); // 태그이름
            var tagIdName = $("#tagIdName").val(); // 태그이름
            var sort = $("#sort").val(); // 태그번호
            var selTagName = "#" + tagIdName + sort; // 적용할 태그 아이디
            console.log(title, tagIdName, sort, selTagName);
            $(selTagName + "Name", opener.document).val(title); // jquery 이용
            $(selTagName, opener.document).val(idx); // jquery 이용
            self.close();
        }
    </script>
</head>

<body>
    <form id="fsearch" name="fsearch" class="local_sch01 local_sch" target="<? $_SERVER['PHP_SELF'] ?>" method="get" autocomplete="off" style="margin-left:10px;">
        <label for="findType" class="sound_only">검색대상</label>
        <select name="findType" id="findType">
            <option value="<?= $searchTitle ?>" <? if ($findType == $searchTitle) { ?>selected<? } ?>>제목</option>
        </select>
        <label for="findword" class="sound_only">검색어<strong class="sound_only"> 필수</strong></label>
        <input type="text" name="findword" id="findword" value="<?= $findword ?>" class=" frm_input">
        <input type="submit" class="btn_submit" value="검색">
        <a href="<?= $base_url ?>" class="btn btn_06">새로고침</a>
    </form>
    <div class="tbl_head01 tbl_wrap">
        <input type="hidden" name="tagIdName" id="tagIdName" value="<?= $tagIdName ?>" />
        <input type="hidden" name="sort" id="sort" value="<?= $sort ?>" />
        <table>
            <thead>
                <tr>
                    <th class="grid_1" id="bIdx">순번</th>
                    <th class="grid_8" id="bTitle">제목</th>
                    <th class="grid_2" id="tDisply">상단고정여부</th>
                    <th class="grid_1" id="tSort">상단고정순서</th>
                    <th class="grid_2" id="admin">관리</th>
                </tr>
            </thead>
            <tbody>
                <?
                if ($numCnt > 0) {
                    $stmt->setFetchMode(PDO::FETCH_ASSOC);
                    while ($row = $stmt->fetch()) {
                        $from_record++;
                        $idx = $row['idx'];
                        $title = $row['title'];
                        $tDisply = $row['tDisply'];
                        if ($tDisply == "Y") {
                            $tDisplyName = "고정중";
                        } else {
                            $tDisplyName = "-";
                        }
                        $tSort = $row['tSort'];
                        if ($tSort != "") {
                            $tSortName = $tSort;
                        } else {
                            $tSortName = "-";
                        }
                ?>
                        <tr class="<?= $bg ?>">
                            <input type="hidden" name="sel_<?= $idx ?>" id="sel_<?= $idx ?>" value="<?= $idx ?>" />
                            <input type="hidden" name="title_<?= $idx ?>" id="title_<?= $idx ?>" value="<?= $title ?>" />
                            <td headers="bIdx"><?= $from_record ?></td>
                            <td headers="bTitle"><?= $title ?></td>
                            <td headers="tDisply"><?= $tDisplyName ?></td>
                            <td headers="tSort"><?= $tSortName ?></td>
                            <td headers="admin">
                                <?
                                if($tDisply == 'Y'){
                                ?>
                                    삭제 후 등록 가능
                                <?
                                }else{
                                ?>
                                    <a href="javascript:selIdx('<?= $idx ?>');" class="btn btn_03">선택</a>
                                <?
                                }
                                ?>
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