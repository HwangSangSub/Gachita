<?
$menu = "3";
$smenu = "2";

include "../common/inc/inc_header.php";  //헤더 


if ($mode == "mod") {
    $titNm = "이벤트 수정";

    $DB_con = db1();

    $query = "SELECT event_Title, event_Url, end_Date, event_EndBit FROM TB_EVENT WHERE idx = :idx";
    $stmt = $DB_con->prepare($query);
    $stmt->bindparam(":idx", $idx);
    $stmt->execute();

    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $event_Title =  trim($row['event_Title']);
    $event_Url =  trim($row['event_Url']);
    $end_Date = trim($row['end_Date']);
    $event_EndBit = trim($row['event_EndBit']);

    dbClose($DB_con);
    $stmt = null;
} else {
    $mode = "reg";
    $titNm = "이벤트 등록";
}

$qstr = "findType=" . urlencode($findType) . "&amp;findword=" . urlencode($findword);

include "../common/inc/inc_gnb.php";  //헤더 
include "../common/inc/inc_menu.php";  //메뉴 

?>

<div id="wrapper">

    <div id="container" class="">
        <h1 id="container_title"><?= $titNm ?></h1>
        <div class="container_wr">
            <form name="fmember" id="fmember" action="eventProc.php" onsubmit="return fubmit(this);" method="post" enctype="multipart/form-data" autocomplete="off">
                <input type="hidden" name="mode" id="mode" value="<?= $mode ?>">
                <input type="hidden" name="idx" id="idx" value="<?= $idx ?>">
                <input type="hidden" name="qstr" id="qstr" value="<?= $qstr ?>">
                <input type="hidden" name="page" id="page" value="<?= $page ?>">

                <div class="tbl_frm01 tbl_wrap">
                    <table>
                        <caption>이벤트관리</caption>
                        <colgroup>
                            <col class="grid_4">
                            <col>
                        </colgroup>
                        <tbody>

                            <tr>
                                <th scope="row"><label for="eventTitle">제목</label></th>
                                <td>
                                    <input type="text" name="eventTitle" value="<?= $event_Title ?>" id="eventTitle" required class="required frm_input" size="50">
                                </td>
                            </tr>

                            <tr>
                                <th scope="row"><label for="eventUrl">URL</label></th>
                                <td colspan="3">

                                    <input type="text" name="eventUrl" value="<?= $event_Url ?>" id="eventUrl" required class="required frm_input" class="frm_input" size="150">
                                    <span class="frm_info"> <strong> url 주소를 입력해주세요. ex) https://www.naver.com/</strong></span>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><label for="eventEndBit">진행여부</label></th>
                                <td colspan="3">
                                    <input type="radio" name="eventEndBit" value="N" id="eventEndBit2" <?= ($event_EndBit == "N") ? "checked" : ""; ?> />
                                    <label for="eventEndBit2">진행</label>
                                    <input type="radio" name="eventEndBit" value="Y" id="eventEndBit1" <?= ($event_EndBit == "Y") ? "checked" : ""; ?> />
                                    <label for="eventEndBit1">종료</label>
                                </td>
                            </tr>


                        </tbody>
                    </table>
                </div>

                <div class="btn_fixed_top">
                    <a href="eventList.php?<?= $qstr ?>&page=<?= $page ?>" class="btn btn_02">목록</a>
                    <input type="submit" value="확인" class="btn_submit btn" accesskey='s'>
            </form>

            <script>
                function fubmit(f) {

                    if ($.trim($(':radio[name="eventEndBit"]:checked').val()) == '') {
                        message = "진행여부를 선택해 주세요!";
                        alert(message);
                        chk = "#eventEndBit";
                        $(chk).focus();
                        return false;
                    }

                    return true;

                }
            </script>

        </div>

        <? include "../common/inc/inc_footer.php";  //푸터 
        ?>