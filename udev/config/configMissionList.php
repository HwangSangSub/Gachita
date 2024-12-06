<?
$menu = "1";
$smenu = "8";

include "../common/inc/inc_header.php";  //헤더 

$base_url = $PHP_SELF;

$sql_search = " WHERE 1";

if ($findword != "") {
    $sql_search .= " AND `{$findType}` LIKE '%{$findword}%' ";
}

$DB_con = db1();

//전체 카운트
$cntQuery = "";
$cntQuery = "SELECT COUNT(idx) AS cntRow ";
$cntQuery .= ",SUM(CASE WHEN m_Type IN ('1', '3') THEN 1 ELSE 0 END) AS A_CNT ";
$cntQuery .= ",SUM(CASE WHEN m_Type = '2' THEN 1 ELSE 0 END) AS T_CNT ";
$cntQuery .= "FROM TB_MISSION  {$sql_search} ";
$cntStmt = $DB_con->prepare($cntQuery);

if ($fr_date != "" || $to_date != "") {
    $cntStmt->bindValue(":fr_date", $fr_date);
    $cntStmt->bindValue(":to_date", $to_date);
}

if ($findword != "") {
    $cntStmt->bindValue(":findType", $findType);
    $cntStmt->bindValue(":findword", $findword);
}

$findType = trim($findType);
$findword = trim($findword);

$cntStmt->execute();
$row = $cntStmt->fetch(PDO::FETCH_ASSOC);
$totalCnt = $row['cntRow'];
$A_CNT = $row['A_CNT'];
$T_CNT = $row['T_CNT'];

$cntStmt = null;

$rows = 10;
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
$query = "SELECT * FROM TB_MISSION {$sql_search} {$sql_order} limit  {$from_record}, {$rows}";
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
            <h1 id="container_title">미션관리</h1>

            <div class="local_ov01 local_ov">
                <span class="btn_ov01"><span class="ov_txt">총 등록 </span><span class="ov_num"><?= number_format($totalCnt); ?>건 </span>&nbsp;
                    <span class="btn_ov01"><span class="ov_txt">총 친해져요 미션 수</span><span class="ov_num"><?= number_format($A_CNT); ?>건 </span>&nbsp;
                        <span class="btn_ov01"><span class="ov_txt">총 오늘의 미션 수 </span><span class="ov_num"><?= number_format($T_CNT); ?>건 </span>
            </div>
            <form class="local_sch03 local_sch" autocomplete="off">
                <div>
                    <strong>분류</strong>
                    <select name="findType" id="findType">
                        <option value="all" <? if ($findType == "all") { ?>selected<? } ?>>전체</option>
                        <option value="m_Name" <? if ($findType == "m_Name") { ?>selected<? } ?>>미션 제목</option>
                    </select>
                    <label for="findword" class="sound_only">검색어<strong class="sound_only"> 필수</strong></label>
                    <input type="text" name="findword" id="findword" value="<?= $findword ?>" size="30" class=" frm_input">

                    <input type="submit" value="검색" class="btn_submit">
                    <a href="<?= $base_url ?>" class="btn btn_06">새로고침</a>
                </div>
            </form>

            <div class="local_desc01 local_desc">
                <p>
                    미션타입은 2가지 종류이며 오늘의 미션(매일), 친해져요 미션(1회한정 + 매달).<br>
                    미션보상지급방법은 3가지 종류이며 즉시(바로지급), 버튼클릭(받기 버튼을 눌러야 가능), 적립예정(익월 1일에 일괄 지급)<br>
                    미션을 추가 등록 및 수정하는건 개발자와 디자인 담당자와 상의 후 진행해야합니다.<br>
                    미션보상포인트는 수정가능합니다.<br>
                    일자는 최근일자부터 역순으로 정렬됩니다.
                </p>
            </div>


            <form name="fmemberlist" id="fmemberlist" method="post" autocomplete="off">

                <div class="tbl_head01 tbl_wrap">
                    <table>
                        <caption>택시호출 목록</caption>
                        <thead>

                            <!-- 아이디, 이름, 등급, 휴대폰번호, 가입일 -->
                            <tr>
                                <th scope="col">순번</th>
                                <th scope="col">미션제목</th>
                                <th scope="col">미션타입</th>
                                <th scope="col">미션상태</a></th>
                                <th scope="col">미션보상포인트<br>(오답지급)</th>
                                <th scope="col">미션보상지급방법</th>
                                <th scope="col">미션등록일</th>
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
                                    $m_Name = $row['m_Name'];
                                    if ($row['m_Status'] == "0") {
                                        $m_Status = "기획";
                                    } else if ($row['m_Status'] == "1") {
                                        $m_Status = "디자인/개발";
                                    } else if ($row['m_Status'] == "2") {
                                        $m_Status = "진행";
                                    } else  if ($row['m_Status'] == "3") {
                                        $m_Status = "종료";
                                    } else {
                                        $m_Status = "반려";
                                    }
                                    if ($row['m_Type'] == '2') {
                                        $m_Type = "오늘의 미션";
                                    } else {
                                        $m_Type = "친해져요 미션";
                                    }
                                    $m_SPoint = $row['m_SPoint'];
                                    $m_FPoint = $row['m_FPoint'];
                                    if ($m_FPoint == 0) {
                                        $point = number_format($m_SPoint) . "원";
                                    } else {
                                        $point = number_format($m_SPoint) . "원 (" . number_format($m_FPoint) . "원)";
                                    }
                                    if ($row['m_GiveType'] == "0") {
                                        $m_GiveType = "즉시";
                                    } else if ($row['m_GiveType'] == "1") {
                                        $m_GiveType = "받기클릭";
                                    } else {
                                        $m_GiveType = "적립예정";
                                    }
                            ?>
                                    <tr class="<?= $bg ?>">
                                        <td><?= $from_record ?></td>
                                        <td><?= $m_Name ?></td>
                                        <td><?= $m_Type ?></td>
                                        <td><?= $m_Status ?></td>
                                        <td><?= $point ?></td>
                                        <td><?= $m_GiveType ?></td>
                                        <td><?= $row['reg_Date'] ?></td>
                                        <td headers="mb_list_mng" class="td_mng td_mng_s">
                                            <a href="configMissionReg.php?mode=mod&idx=<?= $row['idx'] ?>&<?= $qstr ?>&page=<?= $page ?>" class="btn btn_02">수정</a>
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
                        <a href="configMissionReg.php" id="configMissionReg_Add" class="btn btn_01">미션 추가</a>
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