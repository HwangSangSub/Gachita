<?
$menu = "1";
$smenu = "8";

include "../common/inc/inc_header.php";  //헤더 


if ($mode == "mod") {
    $titNm = "미션 수정";

    $DB_con = db1();

    $query = "SELECT * FROM TB_MISSION WHERE idx = :idx";
    $stmt = $DB_con->prepare($query);
    $stmt->bindparam(":idx", $idx);
    //$idx = trim($idx);
    $stmt->execute();

    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    $m_Group =  trim($row['m_Group']);
    $m_Type = trim($row['m_Type']);
    $m_Name = trim($row['m_Name']);
    $m_Status = trim($row['m_Status']);
    $m_SPoint = trim($row['m_SPoint']);
    $m_FPoint = trim($row['m_FPoint']);
    $m_GiveType = trim($row['m_GiveType']);
    $m_Img = trim($row['m_Img']);
    $m_DCnt = trim($row['m_DCnt']);
    $m_SCnt = trim($row['m_SCnt']);
    $m_Time = trim($row['m_Time']);
    $m_Link = trim($row['m_Link']);
    $m_Locat = trim($row['m_Locat']);
    $reg_Date = trim($row['reg_Date']);
    $end_Date = trim($row['end_Date']);
} else {
    $mode = "reg";
    $titNm = "미션 등록";
}

$qstr = "findType=" . urlencode($findType) . "&amp;findword=" . urlencode($findword);

include "../common/inc/inc_gnb.php";  //헤더 
include "../common/inc/inc_menu.php";  //메뉴 

?>

<div id="wrapper">

    <div id="container" class="">
        <h1 id="container_title"><?= $titNm ?></h1>
        <div class="container_wr">
            <form name="fmember" id="fmember" action="configMissionProc.php" onsubmit="return fubmit(this);" method="post" enctype="multipart/form-data" autocomplete="off">
                <input type="hidden" name="mode" id="mode" value="<?= $mode ?>">
                <input type="hidden" name="idx" id="idx" value="<?= $idx ?>">
                <input type="hidden" name="qstr" id="qstr" value="<?= $qstr ?>">
                <input type="hidden" name="page" id="page" value="<?= $page ?>">

                <div class="tbl_frm01 tbl_wrap">
                    <table>
                        <caption>미션관리
                        </caption>
                        <colgroup>
                            <col class="grid_5">
                        </colgroup>
                        <tbody>
                            <tr>
                                <th scope="row"><label for="mGroup">미션구분</label></th>
                                <td colspan="3">
                                    <input type="radio" name="mGroup" value="1" id="m_Group1" <?= ($m_Group == "1") ? "checked" : ""; ?> required class="required" />
                                    <label for="m_Group1">링크</label>
                                    <input type="radio" name="mGroup" value="2" id="m_Group2" <?= ($m_Group == "2") ? "checked" : ""; ?> required class="required" />
                                    <label for="m_Group2">등급달성</label>
                                    <input type="radio" name="mGroup" value="3" id="m_Group3" <?= ($m_Group == "3") ? "checked" : ""; ?> required class="required" />
                                    <label for="m_Group3">횟수</label>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><label for="mType">미션타입</label></th>
                                <td colspan="3">
                                    <input type="radio" name="mType" value="1" id="m_Type1" <?= ($m_Type == "1") ? "checked" : ""; ?> required class="required" />
                                    <label for="m_Type1">친해져요 미션</label>
                                    <input type="radio" name="mType" value="2" id="m_Type2" <?= ($m_Type == "2") ? "checked" : ""; ?> required class="required" />
                                    <label for="m_Type2">오늘의 미션</label>
                                    <input type="radio" name="mType" value="3" id="m_Type3" <?= ($m_Type == "3") ? "checked" : ""; ?> required class="required" />
                                    <label for="m_Type3">친해져요 미션(매달)</label>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><label for="mName">미션제목</label></th>
                                <td colspan="3">
                                    <input type="text" name="mName" value="<?= $m_Name ?>" id="mName" required class="required frm_input" size="50" />
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><label for="mStatus">미션상태</label></th>
                                <td colspan="3">
                                    <input type="radio" name="mStatus" value="0" id="m_Status0" <?= ($m_Status == "0") ? "checked" : ""; ?> required class="required" />
                                    <label for="m_Status0">기획</label>
                                    <input type="radio" name="mStatus" value="1" id="m_Status1" <?= ($m_Status == "1") ? "checked" : ""; ?> required class="required" />
                                    <label for="m_Status1">디자인/개발</label>
                                    <input type="radio" name="mStatus" value="2" id="m_Status2" <?= ($m_Status == "2") ? "checked" : ""; ?> required class="required" />
                                    <label for="m_Status2">진행</label>
                                    <input type="radio" name="mStatus" value="3" id="m_Status3" <?= ($m_Status == "3") ? "checked" : ""; ?> required class="required" />
                                    <label for="m_Status3">종료</label>
                                    <input type="radio" name="mStatus" value="4" id="m_Status4" <?= ($m_Status == "4") ? "checked" : ""; ?> required class="required" />
                                    <label for="m_Status4">반려</label>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><label for="mGiveType">미션보상지급방법</label></th>
                                <td colspan="3">
                                    <input type="radio" name="mGiveType" value="0" id="m_GiveType0" <?= ($m_GiveType == "0") ? "checked" : ""; ?> required class="required" />
                                    <label for="m_GiveType0">즉시</label>
                                    <input type="radio" name="mGiveType" value="1" id="m_GiveType1" <?= ($m_GiveType == "1") ? "checked" : ""; ?> required class="required" />
                                    <label for="m_GiveType1">받기클릭</label>
                                    <input type="radio" name="mGiveType" value="2" id="m_GiveType2" <?= ($m_GiveType == "2") ? "checked" : ""; ?> required class="required" />
                                    <label for="m_GiveType2">적립예정</label>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><label for="mSPoint">미션보상포인트(성공, 정답)</label></th>
                                <td>
                                    <input type="text" name="mSPoint" value="<?= number_format($m_SPoint) ?>" id="mSPoint" class="frm_input" />
                                </td>
                                <th scope="row"><label for="mFPoint">미션보상포인트(오답)</label></th>
                                <td>
                                    <input type="text" name="mFPoint" value="<?= number_format($m_FPoint) ?>" id="mFPoint" class="frm_input" />
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><label for="mImg">미션 아이콘 이미지</label></th>
                                <td colspan="3">
                                    <span class="frm_info">이미지 크기는 <strong>넓이 720픽셀 높이 300픽셀</strong>로 해주세요.</span>
                                    <input type="file" name="m_Img" id="m_Img">
                                    <?
                                    if ($m_Img) {
                                    ?>
                                        <img src="/data/mission/photo.php?id=<? echo $m_Img ?>" style="height:200px">
                                        <input type="checkbox" id="del_m_Img" name="del_m_Img" value="1">삭제
                                    <?
                                    }

                                    ?>

                                    <? if ($mode == "mod") { ?>
                                        <input type="hidden" name="m_Img" value="<?= $m_Img ?>">
                                    <? } ?>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><label for="mDCnt">하루 최대 가능 수</label></th>
                                <td>
                                    <input type="number" name="mDCnt" id="mDCnt" value="<?= $m_DCnt ?>"  class="frm_input"/>
                                </td>
                                <th scope="row"><label for="mSCnt">최대 가능 수</label></th>
                                <td>
                                    <input type="number" name="mSCnt" id="mSCnt" value="<?= $m_SCnt ?>"  class="frm_input"/>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><label for="mLocat">링크위치</label></th>
                                <td colspan="3">
                                    <input type="radio" name="mLocat" value="0" id="m_Locat0" <?= ($m_Locat == "0") ? "checked" : ""; ?> checked required class="required" />
                                    <label for="m_Locat0">앱 내부화면이동</label>
                                    <input type="radio" name="mLocat" value="1" id="m_Locat1" <?= ($m_Locat == "1") ? "checked" : ""; ?> required class="required" />
                                    <label for="m_Locat1">앱 외부링크</label>
                                    <input type="radio" name="mLocat" value="1" id="m_Locat2" <?= ($m_Locat == "2") ? "checked" : ""; ?> required class="required" />
                                    <label for="m_Locat2">앱 웹뷰</label>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><label for="mLink">링크페이지</label></th>
                                <td colspan="3">
                                    <input type="text" name="mLink" id="mLink" value="<?= $m_Link ?>"  class="frm_input"/>
                                </td>
                            </tr>

                        </tbody>
                    </table>
                </div>

                <div class="btn_fixed_top">
                    <a href="configMissionList.php?<?= $qstr ?>&page=<?= $page ?>" class="btn btn_02">목록</a>
                    <?
                    if ($mode == "mod") {
                    ?>
                        <a href="configMissionProc.php?<?= $qstr ?>&page=<?= $page ?>&idx=<?= $idx ?>&mode=del" class="btn btn_01">삭제</a>
                    <? } ?>
                    <input type="submit" value="확인" class="btn_submit btn" accesskey='s'>
                </div>
            </form>


            <script>
                function fubmit(f) {
                    if ($.trim($('#mName').val()) == '') {
                        message = "미션제목을 입력해 주세요!";
                        alert(message);
                        chk = "#mName";
                        $(chk).focus();
                        return false;
                    }

                    if ($.trim($(':radio[name="mGroup"]:checked').val()) == '') {
                        message = "미션구분을 선택해주세요.";
                        alert(message);
                        chk = ':radio[name="mGroup"]';
                        $(chk).focus();
                        return false;
                    }

                    if ($.trim($(':radio[name="mType"]:checked').val()) == '') {
                        message = "미션타입을 선택해주세요.";
                        alert(message);
                        chk = ':radio[name="mType"]';
                        $(chk).focus();
                        return false;
                    }
                    return true;
                }
            </script>

        </div>

        <? include "../common/inc/inc_footer.php";  //푸터 
        ?>