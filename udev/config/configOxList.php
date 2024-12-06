<?
$menu = "1";
$smenu = "9";

include "../common/inc/inc_header.php";  //헤더 

$base_url = $PHP_SELF;

$sql_search = " WHERE ox_UseBit = '0' ";

if ($findword != "") {
    $sql_search .= " AND `{$findType}` LIKE '%{$findword}%' ";
}

$DB_con = db1();

//전체 카운트
$cntQuery = "";
$cntQuery = "SELECT COUNT(idx) AS cntRow ";
$cntQuery .= "FROM TB_OX {$sql_search} ";
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

$rows = 30;
$total_page  = ceil($totalCnt / $rows);  // 전체 페이지 계산
if ($page == "") {
    $page = 1;
} // 페이지가 없으면 첫 페이지 (1 페이지)
$from_record = ($page - 1) * $rows; // 시작 열을 구함


if (!$sort1) {
    $sort1  = "reg_Date";
    $sort2 = "DESC";
}

$sql_order = "order by $sort1 $sort2";

//목록
$query = "";
$query = "SELECT * FROM TB_OX {$sql_search} {$sql_order} limit  {$from_record}, {$rows}";
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
            <h1 id="container_title">OX퀴즈관리</h1>

            <div class="local_ov01 local_ov">
                <span class="btn_ov01"><span class="ov_txt">총 등록 </span><span class="ov_num"><?= number_format($totalCnt); ?>건 </span>&nbsp;
            </div>
            <form class="local_sch03 local_sch" autocomplete="off">
                <div>
                    <strong>분류</strong>
                    <select name="findType" id="findType">
                        <option value="all" <? if ($findType == "all") { ?>selected<? } ?>>전체</option>
                        <option value="m_Name" <? if ($findType == "m_Name") { ?>selected<? } ?>>OX 문제</option>
                    </select>
                    <label for="findword" class="sound_only">검색어<strong class="sound_only"> 필수</strong></label>
                    <input type="text" name="findword" id="findword" value="<?= $findword ?>" size="30" class=" frm_input">

                    <input type="submit" value="검색" class="btn_submit">
                    <a href="<?= $base_url ?>" class="btn btn_06">새로고침</a>
                </div>
            </form>

            <div class="local_desc01 local_desc">
                <p>
                    OX퀴즈는 카테고리별 문제와 답을 지정하며 오답일 경우에는 설명을 적으셔야 합니다.<br>
                    일자는 최근일자부터 역순으로 정렬됩니다.
                </p>
            </div>
            <form name="fmemberlist" id="fmemberlist" method="post" autocomplete="off">
                <div class="tbl_head01 tbl_wrap">
                    <table>
                        <caption>OX퀴즈 목록</caption>
                        <thead>
                            <tr>
                                <th scope="col">순번</th>
                                <th scope="col">OX 카테고리</th>
                                <th scope="col">OX 문제</th>
                                <th scope="col">OX 답</a></th>
                                <th scope="col">OX 상태</a></th>
                                <th scope="col">OX 등록일</a></th>
                                <th scope="col">관리</th>
                            </tr>
                        </thead>
                        <tbody>

                            <?

                            if ($numCnt > 0) {

                                $stmt->setFetchMode(PDO::FETCH_ASSOC);

                                while ($row = $stmt->fetch()) {
                                    // $bg = 'bg'.($stmt->fetch()%2);
                                    $from_record++;
                                    $ox_Idx = $row['idx'];
                                    //OX 카테고리(1: 같이타기, 2:포인트(출금), 3:공지 및 이벤트, 4:아재 테스트)
                                    if ($row['ox_Cate'] == "1") {
                                        $ox_Cate = "같이타기";
                                    } else if ($row['ox_Cate'] == "2") {
                                        $ox_Cate = "포인트(출금)";
                                    } else if ($row['ox_Cate'] == "3") {
                                        $ox_Cate = "공지 및 이벤트";
                                    } else  if ($row['ox_Cate'] == "4") {
                                        $ox_Cate = "아재 테스트";
                                    } else  if ($row['ox_Cate'] == "5") {
                                        $ox_Cate = "등급";
                                    } else {
                                        $ox_Cate = "미분류";
                                    }
                                    $ox_Question = html_Decode(trim($row['ox_Question']));
                                    if ($row['ox_Answer'] == "1") {
                                        $ox_Answer = "그렇다";
                                    } else if ($row['ox_Answer'] == "2") {
                                        $ox_Answer = "아니다";
                                    } else {
                                        $ox_Answer = "미분류";
                                    }
                                    if ($row['ox_Status'] == "1") {
                                        $ox_Status = "사용";
                                    } else if ($row['ox_Status'] == "2") {
                                        $ox_Status = "미사용";
                                    } else {
                                        $ox_Status = "미분류";
                                    }
                                    $reg_Date = $row['reg_Date'];
                            ?>
                                    <tr class="<?= $bg ?>">
                                        <td><?= $from_record ?></td>
                                        <td><?= $ox_Cate ?></td>
                                        <td><?= $ox_Question ?></td>
                                        <td><?= $ox_Answer ?></td>
                                        <td><?= $ox_Status ?></td>
                                        <td><?= $reg_Date ?></td>
                                        <td headers="mb_list_mng" class="td_mng td_mng_s">
                                            <a href="configOxReg.php?mode=mod&idx=<?= $ox_Idx ?>&<?= $qstr ?>&page=<?= $page ?>" class="btn btn_02">수정</a>
                                        </td>
                                    </tr>
                                <?

                                }
                                ?>
                            <? } else { ?>
                                <tr>
                                    <td colspan="9" class="empty_table">자료가 없습니다.</td>
                                </tr>
                            <? } ?>
                        </tbody>
                    </table>
                </div>

                <div class="btn_fixed_top">
                    <? if ($_COOKIE['du_udev']['id'] != 'admin2') { ?>
                        <a href="configOxReg.php" id="configOxReg_Add" class="btn btn_01">OX퀴즈 추가</a>
                    <? } ?>
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