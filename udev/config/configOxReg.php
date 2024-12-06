<?
$menu = "1";
$smenu = "9";

include "../common/inc/inc_header.php";  //헤더 


if ($mode == "mod") {
    $titNm = "OX 퀴즈 수정";

    $DB_con = db1();

    $query = "SELECT * FROM TB_OX WHERE idx = :idx";
    $stmt = $DB_con->prepare($query);
    $stmt->bindparam(":idx", $idx);
    //$idx = trim($idx);
    $stmt->execute();

    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    $ox_Cate =  trim($row['ox_Cate']);
    // $ox_Question = trim($row['ox_Question']);
    $ox_Question = html_Decode(trim($row['ox_Question']));
    $ox_Answer = trim($row['ox_Answer']);
    // $ox_Explanation = trim($row['ox_Explanation']);
    $ox_Explanation = html_Decode(trim($row['ox_Explanation']));
    $reg_Date = trim($row['reg_Date']);
} else {
    $mode = "reg";
    $titNm = "OX 퀴즈 등록";
}

$qstr = "findType=" . urlencode($findType) . "&amp;findword=" . urlencode($findword);

include "../common/inc/inc_gnb.php";  //헤더 
include "../common/inc/inc_menu.php";  //메뉴 

?>
<div id="wrapper">
    <div id="container" class="">
        <h1 id="container_title"><?= $titNm ?></h1>
        <div class="container_wr">
            <form name="fmember" id="fmember" action="configOxProc.php" onsubmit="return fubmit(this);" method="post" enctype="multipart/form-data" autocomplete="off">
                <input type="hidden" name="mode" id="mode" value="<?= $mode ?>">
                <input type="hidden" name="idx" id="idx" value="<?= $idx ?>">
                <input type="hidden" name="qstr" id="qstr" value="<?= $qstr ?>">
                <input type="hidden" name="page" id="page" value="<?= $page ?>">

                <div class="tbl_frm01 tbl_wrap">
                    <table>
                        <caption>OX 퀴즈 관리
                        </caption>
                        <colgroup>
                            <col class="grid_5">
                            <col>
                        </colgroup>
                        <tbody>
                            <tr>
                                <th scope="row"><label for="oxCate">OX 카테고리</label></th>
                                <td colspan="3">
                                    <div class="radio_Option1">
                                        <span class="bg <? if ($ox_Cate == "1") { ?>all_on<? } ?>">
                                            <input type="radio" name="oxCate" value="1" id="oxCate1" required <?= get_checked($ox_Cate, 1) ?>>
                                            <label for="oxCate1">같이타기</label>
                                        </span>
                                        <span class="bg <? if ($ox_Cate == "2") { ?>c01_on<? } ?>">
                                            <input type="radio" name="oxCate" value="2" id="oxCate2" required <?= get_checked($ox_Cate, 2) ?>>
                                            <label for="oxCate2">포인트(출금)</label>
                                        </span>
                                        <span class="bg <? if ($ox_Cate == "3") { ?>c02_on<? } ?>">
                                            <input type="radio" name="oxCate" value="3" id="oxCate3" required <?= get_checked($ox_Cate, 3) ?>>
                                            <label for="oxCate3">공지 및 이벤트</label>
                                        </span>
                                        <span class="bg <? if ($ox_Cate == "4") { ?>c08_on<? } ?>">
                                            <input type="radio" name="oxCate" value="4" id="oxCate4" required <?= get_checked($ox_Cate, 4) ?>>
                                            <label for="oxCate4">아재 테스트</label>
                                        </span>
                                        <span class="bg <? if ($ox_Cate == "5") { ?>c03_on<? } ?>">
                                            <input type="radio" name="oxCate" value="5" id="oxCate5" required <?= get_checked($ox_Cate, 5) ?>>
                                            <label for="oxCate5">등급</label>
                                        </span>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><label for="oxQuestion">OX 문제</label></th>
                                <td colspan="3">
                                    <textarea name="oxQuestion" id="oxQuestion" cols="30" rows="10" required><?= $ox_Question ?></textarea>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><label for="ox_Answer">OX 답</label></th>
                                <td colspan="3">
                                    <div class="radio_Option2">
                                        <span class="bg <? if ($ox_Answer == "1") { ?>all_on<? } ?>">
                                            <input type="radio" name="oxAnswer" value="1" id="oxAnswer1" required <?= get_checked($ox_Answer, 1) ?>>
                                            <label for="oxAnswer1">그렇다</label>
                                        </span>
                                        <span class="bg <? if ($ox_Answer == "2") { ?>c12_on<? } ?>">
                                            <input type="radio" name="oxAnswer" value="2" id="oxAnswer2" required <?= get_checked($ox_Answer, 2) ?>>
                                            <label for="oxAnswer2">아니다</label>
                                        </span>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><label for="oxExplanation">OX 문제 설명</label></th>
                                <td colspan="3">
                                    <textarea name="oxExplanation" id="oxExplanation" cols="30" rows="10"><?= $ox_Explanation ?></textarea>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="btn_fixed_top">
                    <a href="configOxList.php?<?= $qstr ?>&page=<?= $page ?>" class="btn btn_02">목록</a>
                    <?
                    if ($mode == "mod") {
                    ?>
                        <a href="configOxProc.php?<?= $qstr ?>&page=<?= $page ?>&idx=<?= $idx ?>&mode=del" class="btn btn_01">삭제</a>
                    <? } ?>
                    <input type="submit" value="확인" class="btn_submit btn" accesskey='s'>
                </div>
            </form>


            <script>
                $(document).ready(function() {
                    $('input:radio[name="oxCate"]').click(function() {
                        var chkVal = $('input:radio[name="oxCate"]:checked').val();
                        var addClass = '';
                        if(chkVal == '1'){
                            addClass = 'all_on';
                        }else if(chkVal == '2'){
                            addClass = 'c01_on';
                        }else if(chkVal == '3'){
                            addClass = 'c02_on';
                        }else if(chkVal == '4'){
                            addClass = 'c08_on';
                        }else if(chkVal == '5'){
                            addClass = 'c03_on';
                        }
                        $('.radio_Option1').find('span').attr('class','bg');

                        $(this).parent().attr('class','bg '+addClass+'');
                    });
                    $('input:radio[name="oxAnswer"]').click(function() {
                        var chkVal2 = $('input:radio[name="oxAnswer"]:checked').val();
                        var addClass2 = '';
                        if(chkVal2 == '1'){
                            addClass2 = 'all_on';
                        }else if(chkVal2 == '2'){
                            addClass2 = 'c12_on';
                        }
                        $('.radio_Option2').find('span').attr('class','bg');

                        $(this).parent().attr('class','bg '+addClass2+'');
                    });
                });
                // function fubmit(f) {
                //     return true;
                // }
            </script>
        </div>
        <? include "../common/inc/inc_footer.php";  //푸터 
        ?>