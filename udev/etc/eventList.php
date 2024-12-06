<?
$menu = "3";
$smenu = "2";

include "../common/inc/inc_header.php";  //헤더 

$base_url = $PHP_SELF;

$sql_search = " WHERE 1 = 1 ";

if ($findword != "") {
    $sql_search .= " AND `{$findType}` LIKE '%{$findword}%' ";
}

$DB_con = db1();

//전체 카운트
$cntQuery = "";
$cntQuery = "SELECT COUNT(idx) AS cntRow FROM TB_EVENT  {$sql_search} ";
$cntStmt = $DB_con->prepare($cntQuery);

if ($findword != "") {
    $cntStmt->bindValue(":findType", $findType);
    $cntStmt->bindValue(":findword", $findword);
}

$findType = trim($findType);
$findword = trim($findword);

$cntStmt->execute();
$row = $cntStmt->fetch(PDO::FETCH_ASSOC);
$totalCnt = $row['cntRow'];

$cntStmt = null;

$rows = 10;
$total_page  = ceil($totalCnt / $rows);  // 전체 페이지 계산
if ($page == "") {
    $page = 1;
} // 페이지가 없으면 첫 페이지 (1 페이지)
$from_record = ($page - 1) * $rows; // 시작 열을 구함


if (!$sort1) {
    $sort1  = "idx";
    $sort2 = "DESC";
}

$sql_order = "ORDER BY CASE WHEN event_EndBit = 'N' THEN 1 ELSE 999 END ASC, $sort1 $sort2";

//목록
$query = "";
$query = "SELECT idx, event_Title, event_Url, DATE_FORMAT(reg_Date,'%Y-%m-%d') AS reg_Date, DATE_FORMAT(end_Date,'%Y-%m-%d') AS end_Date, event_EndBit FROM TB_EVENT {$sql_search} {$sql_order} LIMIT  {$from_record}, {$rows}";
$stmt = $DB_con->prepare($query);

if ($findword != "") {
    $stmt->bindValue(":findType", $findType);
    $stmt->bindValue(":findword", $findword);
}

$findType = trim($findType);
$findword = trim($findword);

$stmt->execute();
$numCnt = $stmt->rowCount();

$qstr = "findType=" . urlencode($findType) . "&amp;findword=" . urlencode($findword);

include "../common/inc/inc_gnb.php";  //헤더 
include "../common/inc/inc_menu.php";  //메뉴 

?>
<script type="text/javascript" src="<?= DU_UDEV_DIR ?>/etc/js/event.js"></script>

<div id="wrapper">
    <div id="container" class="">
        <div class="container_wr">
            <h1 id="container_title">이벤트 관리</h1>

            <div class="local_ov01 local_ov">
                <span class="btn_ov01"><span class="ov_txt">총 수 </span><span class="ov_num"><?= number_format($totalCnt); ?>건 </span>
            </div>



            <form class="local_sch03 local_sch" autocomplete="off">

                <div>
                    <strong>분류</strong>
                    <select name="findType" id="findType">
                        <option value="event_Title" <? if ($findType == "event_Title") { ?>selected<? } ?>>제목</option>
                    </select>
                    <label for="findword" class="sound_only">검색어<strong class="sound_only"> 필수</strong></label>
                    <input type="text" name="findword" id="findword" value="<?= $findword ?>" size="30" class=" frm_input">

                    <input type="submit" value="검색" class="btn_submit">
                    <a href="<?= $base_url ?>" class="btn btn_06">새로고침</a>
                </div>
            </form>



            <form name="fmemberlist" id="fmemberlist" method="post" autocomplete="off">

                <div class="tbl_head01 tbl_wrap">
                    <table>
                        <caption>이벤트 목록</caption>
                        <thead>
                            <tr>
                                <th scope="col">제목</th>
                                <th scope="col">이벤트페이지</th>
                                <th scope="col">종료여부</th>
                                <th scope="col">종료일</th>
                                <th scope="col">등록일</th>
                                <th scope="col">관리</th>
                            </tr>
                        </thead>
                        <tbody>

                            <?

                            if ($numCnt > 0) {

                                $stmt->setFetchMode(PDO::FETCH_ASSOC);

                                while ($row = $stmt->fetch()) {
                                    $idx = $row['idx'];
                                    $event_Title = $row['event_Title'];
                                    $event_Url = $row['event_Url'];
                                    $reg_Date = $row['reg_Date'];
                                    $end_Date = $row['end_Date'];
                                    if ($end_Date == "") {
                                        $endDate = "미정";
                                    } else {
                                        $endDate = $end_Date;
                                    }
                                    if ($row['event_EndBit'] == "Y") {
                                        $event_EndBit = "종료";
                                    } else {
                                        $event_EndBit = "진행중";
                                    }

                            ?>
                                    <tr class="<?= $bg ?>">
                                        <td><?= $event_Title ?></td>
                                        <td><a href="<?= $event_Url ?>">이벤트 페이지 바로가기</a></td>
                                        <td><?= $event_EndBit ?></td>
                                        <td><?= $endDate ?></td>
                                        <td><?= $reg_Date ?></td>
                                        <td headers="mb_list_mng" class="td_mng td_mng_s">
                                            <a href="eventReg.php?mode=mod&idx=<?= $idx ?>&<?= $qstr ?>&page=<?= $page ?>" class="btn btn_03">수정</a>
                                            <a href="javascript:chkDel_Event('<?= $idx ?>')" class="btn btn_02">삭제</a>
                                        </td>
                                    </tr>
                                <?

                                }
                                ?>
                            <? } else { ?>
                                <tr>
                                    <td colspan="7" class="empty_table">자료가 없습니다.</td>
                                </tr>
                            <? } ?>
                        </tbody>
                    </table>
                </div>

                <div class="btn_fixed_top">
                    <a href="eventReg.php" id="event_add" class="btn btn_01">이벤트 추가</a>
                </div>

            </form>
            <nav class="pg_wrap">
                <?= get_apaging($rows, $page, $total_page, "$_SERVER[PHP_SELF]?$qstr"); ?>
            </nav>

            <script>
                function fvisit_submit(act) {
                    var f = document.fvisit;
                    f.action = act;
                    f.submit();
                }
            </script>

        </div>

        <?
        dbClose($DB_con);
        $cntStmt = null;
        $stmt = null;

        include "../common/inc/inc_footer.php";  //푸터 

        ?>