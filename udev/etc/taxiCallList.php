<?
$menu = "3";
$smenu = "8";

include "../common/inc/inc_header.php";  //헤더 

$base_url = $PHP_SELF;

$sql_search = " WHERE taxi_DelBit = '0' ";

if ($findword != "") {
    $sql_search .= " AND `{$findType}` LIKE '%{$findword}%' ";
}

$DB_con = db1();

//전체 카운트
$cntQuery = "";
$cntQuery = "SELECT COUNT(idx) AS cntRow ";
$cntQuery .= ",SUM(CASE WHEN taxi_Type = '0' THEN 1 ELSE 0 END) AS A_CNT ";
$cntQuery .= ",SUM(CASE WHEN taxi_Type = '1' THEN 1 ELSE 0 END) AS T_CNT ";
$cntQuery .= "FROM TB_TAXICALL  {$sql_search} ";
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
$query = "SELECT * FROM TB_TAXICALL {$sql_search} {$sql_order} limit  {$from_record}, {$rows}";
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
            <h1 id="container_title">택시호출관리</h1>

            <div class="local_ov01 local_ov">
                <span class="btn_ov01"><span class="ov_txt">총 등록 </span><span class="ov_num"><?= number_format($totalCnt); ?>건 </span>&nbsp;
                    <span class="btn_ov01"><span class="ov_txt">총 택시앱 </span><span class="ov_num"><?= number_format($A_CNT); ?>건 </span>&nbsp;
                        <span class="btn_ov01"><span class="ov_txt">총 콜택시 </span><span class="ov_num"><?= number_format($T_CNT); ?>건 </span>
            </div>
            <form class="local_sch03 local_sch" autocomplete="off">
                <div>
                    <strong>분류</strong>
                    <select name="findType" id="findType">
                        <option value="all" <? if ($findType == "all") { ?>selected<? } ?>>전체</option>
                        <option value="taxi_Name" <? if ($findType == "taxi_Name") { ?>selected<? } ?>>호출명</option>
                        <option value="taxi_locat" <? if ($findType == "taxi_locat") { ?>selected<? } ?>>호출가능지역</option>
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
                        <caption>택시호출 목록</caption>
                        <thead>

                            <!-- 아이디, 이름, 등급, 휴대폰번호, 가입일 -->
                            <tr>
                                <th scope="col">순번</th>
                                <th scope="col">호출명</th>
                                <th scope="col">호출타입</th>
                                <th scope="col">호출가능지역</a></th>
                                <th scope="col">호출수</th>
                                <th scope="col">관리자메모</th>
                                <th scope="col">호출사용여부</th>
                                <th scope="col">등록일</th>
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
                                    if ($row['taxi_UseBit'] == "0") {
                                        $taxi_UseBit = "사용";
                                    } else {
                                        $taxi_UseBit = "미사용";
                                    }
                                    $taxi_Type = $row['taxi_Type'];
                                    if ($taxi_Type == 0) {
                                        $taxiType = "전화";
                                    } else {
                                        $taxiType = "앱";
                                    }
                            ?>
                                    <tr class="<?= $bg ?>">
                                        <td><?= $from_record ?></td>
                                        <td><?= $row['taxi_Name'] ?></td>
                                        <td><?= $taxiType ?></td>
                                        <td><?= $row['taxi_locat'] ?></td>
                                        <td><?= number_format($row['taxi_CallCnt']) ?></td>
                                        <td><?= $row['taxi_Memo'] ?></td>
                                        <td><?= $taxi_UseBit ?></td>
                                        <td><?= $row['reg_Date'] ?></td>
                                        <td headers="mb_list_mng" class="td_mng td_mng_s">
                                            <a href="taxiCallReg.php?mode=mod&idx=<?= $row['idx'] ?>&<?= $qstr ?>&page=<?= $page ?>" class="btn btn_02">수정</a>
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
                        <a href="taxiCallReg.php" id="taxicall_add" class="btn btn_01">호출 추가</a>
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