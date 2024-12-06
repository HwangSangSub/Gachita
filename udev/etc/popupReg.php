<?
$menu = "3";
$smenu = "4";

include "../common/inc/inc_header.php";  //헤더 


if ($mode == "mod") {
    $titNm = "팝업 관리 수정";

    $DB_con = db1();

    $query = "";
    $query = "SELECT popup_Title, popup_Img, popup_Url, popup_Bit, reg_Date, end_Date FROM TB_CONFIG_POPUP WHERE idx = :idx";
    $stmt = $DB_con->prepare($query);
    $stmt->bindparam(":idx", $idx);
    $stmt->execute();

    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $popup_Title =  trim($row['popup_Title']);
    $popup_Img = trim($row['popup_Img']);
    $popup_Url =  trim($row['popup_Url']);
    $popup_Bit = trim($row['popup_Bit']);
    $reg_Date = trim($row['reg_Date']);
    $end_Date = trim($row['end_Date']);

    dbClose($DB_con);
    $stmt = null;
} else {
    $mode = "reg";
    $titNm = "팝업 관리 등록";
}

$qstr = "findType=" . urlencode($findType) . "&amp;findword=" . urlencode($findword);

include "../common/inc/inc_gnb.php";  //헤더 
include "../common/inc/inc_menu.php";  //메뉴 

?>

<div id="wrapper">

    <div id="container" class="">
        <h1 id="container_title"><?= $titNm ?></h1>
        <div class="container_wr">
            <form name="fmember" id="fmember" action="popupProc.php" onsubmit="return fubmit(this);" method="post" enctype="multipart/form-data" autocomplete="off">
                <input type="hidden" name="mode" id="mode" value="<?= $mode ?>">
                <input type="hidden" name="idx" id="idx" value="<?= $idx ?>">
                <input type="hidden" name="qstr" id="qstr" value="<?= $qstr ?>">
                <input type="hidden" name="page" id="page" value="<?= $page ?>">

                <div class="tbl_frm01 tbl_wrap">
                    <table>
                        <caption>팝업 관리</caption>
                        <colgroup>
                            <col class="grid_4">
                            <col>
                        </colgroup>
                        <tbody>

                            <tr>
                                <th scope="row"><label for="popupTitle">제목</label></th>
                                <td>
                                    <input type="text" name="popupTitle" value="<?= $popup_Title ?>" id="popupTitle" required class="required frm_input" size="50">
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><label for="popImg">이미지</label></th>
                                <td>
                                    <span class="frm_info">이미지 크기는 <strong>넓이 1387픽셀 높이 1000픽셀</strong>로 해주세요.</span>
                                    <input type="file" name="popImg" id="popImg">
                                    <?
                                    //BLOB 파일 형태로 저장된 이미지 파일 출력되도록 ------------------- 2019.02.15
                                    if ($popup_Img) {
                                    ?>
                                        <img src="/data/popup/photo.php?id=<? echo $popup_Img ?>" style="height:60px">
                                        <input type="checkbox" id="del_popImg" name="del_popImg" value="1">삭제
                                    <?
                                    }

                                    ?>

                                    <? if ($mode == "mod") { ?>
                                        <input type="hidden" name="popupImg" value="<?= $popup_Img ?>">
                                    <? } ?>
                                </td>
                            </tr>

                            <tr>
                                <th scope="row"><label for="popupUrl">URL</label></th>
                                <td colspan="3">

                                    <input type="text" name="popupUrl" value="<?= $popup_Url ?>" id="popupUrl" required class="required frm_input" class="frm_input" size="150">
                                    <span class="frm_info"> <strong> url 주소를 입력해주세요. ex) https://www.naver.com/</strong></span>
                                </td>
                            </tr>

                            <tr>
                                <th scope="row"><label for="popupBit">사용여부</label></th>
                                <td colspan="3">
                                    <input type="radio" name="popupBit" value="Y" id="popupBit" <?= ($popup_Bit == "Y") ? "checked" : ""; ?> />
                                    <label for="popup_Bit">사용</label>
                                    <input type="radio" name="popupBit" value="N" id="popupBit" <?= ($popup_Bit == "N") ? "checked" : ""; ?> />
                                    <label for="popup_Bit">사용안함</label>
                                </td>
                            </tr>
							<tr>
								<th scope="row"><label for="endDate">팝업 종료일</label></th>
								<td colspan="3">
									<input type="date" class="frm_input" id="endDate" name="endDate" size="15" value="<?= $end_Date ?>" />
								</td>
							</tr>
                        </tbody>
                    </table>
                </div>

                <div class="btn_fixed_top">
                    <a href="popupList.php?<?= $qstr ?>&page=<?= $page ?>" class="btn btn_02">목록</a>
                    <? if ($_COOKIE['du_udev']['id'] != 'admin2') { ?>
                        <input type="submit" value="확인" class="btn_submit btn" accesskey='s'>
                    <? } ?>
                </div>
            </form>


            <script>
                function fubmit(f) {
                    if ($.trim($(':radio[name="popupBit"]:checked').val()) == '') {
                        message = "사용여부를 선택해 주세요!";
                        alert(message);
                        chk = "#popupBit";
                        $(chk).focus();
                        return false;
                    }

                    return true;

                }
            </script>

        </div>

        <? include "../common/inc/inc_footer.php";  //푸터 
        ?>