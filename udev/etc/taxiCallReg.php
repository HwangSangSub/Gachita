<?
$menu = "3";
$smenu = "8";

include "../common/inc/inc_header.php";  //헤더 


if ($mode == "mod") {
    $titNm = "택시호출관리 수정";

    $DB_con = db1();

    $query = "";
    $query = "SELECT taxi_Name, taxi_Type, taxi_locat, taxi_Tel, taxi_And_Install, taxi_Ios_Install, taxi_Ios, taxi_Img, taxi_Memo, taxi_UseBit FROM TB_TAXICALL WHERE idx = :idx";
    $stmt = $DB_con->prepare($query);
    $stmt->bindparam(":idx", $idx);
    //$idx = trim($idx);
    $stmt->execute();

    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    $taxi_Name =  trim($row['taxi_Name']);
    $taxi_Type = trim($row['taxi_Type']);
    $taxi_locat = trim($row['taxi_locat']);
    $taxi_Tel = trim($row['taxi_Tel']);
    $taxi_And_Install = trim($row['taxi_And_Install']);
    $taxi_Ios_Install = trim($row['taxi_Ios_Install']);
    $taxi_Ios = trim($row['taxi_Ios']);
    $taxi_ImgFile = trim($row['taxi_Img']);
    $taxi_Memo = trim($row['taxi_Memo']);
    $taxi_UseBit = trim($row['taxi_UseBit']);
} else {
    $mode = "reg";
    $titNm = "택시호출관리 등록";
}

$qstr = "findType=" . urlencode($findType) . "&amp;findword=" . urlencode($findword);

include "../common/inc/inc_gnb.php";  //헤더 
include "../common/inc/inc_menu.php";  //메뉴 

?>

<div id="wrapper">

    <div id="container" class="">
        <h1 id="container_title"><?= $titNm ?></h1>
        <div class="container_wr">
            <form name="fmember" id="fmember" action="taxiCallProc.php" onsubmit="return fubmit(this);" method="post" enctype="multipart/form-data" autocomplete="off">
                <input type="hidden" name="mode" id="mode" value="<?= $mode ?>">
                <input type="hidden" name="idx" id="idx" value="<?= $idx ?>">
                <input type="hidden" name="qstr" id="qstr" value="<?= $qstr ?>">
                <input type="hidden" name="page" id="page" value="<?= $page ?>">

                <div class="tbl_frm01 tbl_wrap">
                    <table>
                        <caption>택시호출관리
                        </caption>
                        <colgroup>
                            <col class="grid_4">
                            <col>
                        </colgroup>
                        <tbody>
                            <tr>
                                <th scope="row"><label for="taxilocat">호출지역</label></th>
                                <td colspan="3">
                                    <select name="taxilocat" id="taxilocat">
                                        <option value="전국" <?= ($taxi_locat == "전국") ? "selected" : ""; ?>>전국</option>
                                        <option value="">-----특/광역시</option>
                                        <option value="서울특별시" <?= ($taxi_locat == "서울특별시") ? "selected" : ""; ?>>서울특별시</option>
                                        <option value="부산광역시" <?= ($taxi_locat == "부산광역시") ? "selected" : ""; ?>>부산광역시</option>
                                        <option value="대구광역시" <?= ($taxi_locat == "대구광역시") ? "selected" : ""; ?>>대구광역시</option>
                                        <option value="인천광역시" <?= ($taxi_locat == "인천광역시") ? "selected" : ""; ?>>인천광역시</option>
                                        <option value="광주광역시" <?= ($taxi_locat == "광주광역시") ? "selected" : ""; ?>>광주광역시</option>
                                        <option value="대전광역시" <?= ($taxi_locat == "대전광역시") ? "selected" : ""; ?>>대전광역시</option>
                                        <option value="울산광역시" <?= ($taxi_locat == "울산광역시") ? "selected" : ""; ?>>울산광역시</option>
                                        <option value="세종특별자치시" <?= ($taxi_locat == "세종특별자치시") ? "selected" : ""; ?>>세종특별자치시</option>
                                        <option value="">-----경기도</option>
                                        <option value="경기도" <?= ($taxi_locat == "경기도") ? "selected" : ""; ?>>경기도</option>
                                        <option value="">-----강원도</option>
                                        <option value="춘천시" <?= ($taxi_locat == "춘천시") ? "selected" : ""; ?>>강원도 춘천시</option>
                                        <option value="원주시" <?= ($taxi_locat == "원주시") ? "selected" : ""; ?>>강원도 원주시</option>
                                        <option value="강릉시" <?= ($taxi_locat == "강릉시") ? "selected" : ""; ?>>강원도 강릉시</option>
                                        <option value="동해시" <?= ($taxi_locat == "동해시") ? "selected" : ""; ?>>강원도 동해시</option>
                                        <option value="태백시" <?= ($taxi_locat == "태백시") ? "selected" : ""; ?>>강원도 태백시</option>
                                        <option value="속초시" <?= ($taxi_locat == "속초시") ? "selected" : ""; ?>>강원도 속초시</option>
                                        <option value="삼척시" <?= ($taxi_locat == "삼척시") ? "selected" : ""; ?>>강원도 삼척시</option>
                                        <option value="">-----충청북도</option>
                                        <option value="청주시" <?= ($taxi_locat == "청주시") ? "selected" : ""; ?>>충청북도 청주시</option>
                                        <option value="충주시" <?= ($taxi_locat == "충주시") ? "selected" : ""; ?>>충청북도 충주시</option>
                                        <option value="제천시" <?= ($taxi_locat == "제천시") ? "selected" : ""; ?>>충청북도 제천시</option>
                                        <option value="">-----충청남도</option>
                                        <option value="천안시" <?= ($taxi_locat == "천안시") ? "selected" : ""; ?>>충청남도 천안시</option>
                                        <option value="공주시" <?= ($taxi_locat == "공주시") ? "selected" : ""; ?>>충청남도 공주시</option>
                                        <option value="보령시" <?= ($taxi_locat == "보령시") ? "selected" : ""; ?>>충청남도 보령시</option>
                                        <option value="아산시" <?= ($taxi_locat == "아산시") ? "selected" : ""; ?>>충청남도 아산시</option>
                                        <option value="서산시" <?= ($taxi_locat == "서산시") ? "selected" : ""; ?>>충청남도 서산시</option>
                                        <option value="논산시" <?= ($taxi_locat == "논산시") ? "selected" : ""; ?>>충청남도 논산시</option>
                                        <option value="계룡시" <?= ($taxi_locat == "계룡시") ? "selected" : ""; ?>>충청남도 계룡시</option>
                                        <option value="당진시" <?= ($taxi_locat == "당진시") ? "selected" : ""; ?>>충청남도 당진시</option>
                                        <option value="">-----전라북도</option>
                                        <option value="전주시" <?= ($taxi_locat == "전주시") ? "selected" : ""; ?>>전라북도 전주시</option>
                                        <option value="군산시" <?= ($taxi_locat == "군산시") ? "selected" : ""; ?>>전라북도 군산시</option>
                                        <option value="익산시" <?= ($taxi_locat == "익산시") ? "selected" : ""; ?>>전라북도 익산시</option>
                                        <option value="정읍시" <?= ($taxi_locat == "정읍시") ? "selected" : ""; ?>>전라북도 정읍시</option>
                                        <option value="남원시" <?= ($taxi_locat == "남원시") ? "selected" : ""; ?>>전라북도 남원시</option>
                                        <option value="김제시" <?= ($taxi_locat == "김제시") ? "selected" : ""; ?>>전라북도 김제시</option>
                                        <option value="">-----전라남도</option>
                                        <option value="목포시" <?= ($taxi_locat == "목포시") ? "selected" : ""; ?>>전라남도 목포시</option>
                                        <option value="여수시" <?= ($taxi_locat == "여수시") ? "selected" : ""; ?>>전라남도 여수시</option>
                                        <option value="순천시" <?= ($taxi_locat == "순천시") ? "selected" : ""; ?>>전라남도 순천시</option>
                                        <option value="나주시" <?= ($taxi_locat == "나주시") ? "selected" : ""; ?>>전라남도 나주시</option>
                                        <option value="광양시" <?= ($taxi_locat == "광양시") ? "selected" : ""; ?>>전라남도 광양시</option>
                                        <option value="">-----경상북도</option>
                                        <option value="포항시" <?= ($taxi_locat == "포항시") ? "selected" : ""; ?>>경상북도 포항시</option>
                                        <option value="경주시" <?= ($taxi_locat == "경주시") ? "selected" : ""; ?>>경상북도 경주시</option>
                                        <option value="김천시" <?= ($taxi_locat == "김천시") ? "selected" : ""; ?>>경상북도 김천시</option>
                                        <option value="안동시" <?= ($taxi_locat == "안동시") ? "selected" : ""; ?>>경상북도 안동시</option>
                                        <option value="구미시" <?= ($taxi_locat == "구미시") ? "selected" : ""; ?>>경상북도 구미시</option>
                                        <option value="영주시" <?= ($taxi_locat == "영주시") ? "selected" : ""; ?>>경상북도 영주시</option>
                                        <option value="영천시" <?= ($taxi_locat == "영천시") ? "selected" : ""; ?>>경상북도 영천시</option>
                                        <option value="상주시" <?= ($taxi_locat == "상주시") ? "selected" : ""; ?>>경상북도 상주시</option>
                                        <option value="문경시" <?= ($taxi_locat == "문경시") ? "selected" : ""; ?>>경상북도 문경시</option>
                                        <option value="경산시" <?= ($taxi_locat == "경산시") ? "selected" : ""; ?>>경상북도 경산시</option>
                                        <option value="">-----경상남도</option>
                                        <option value="창원시" <?= ($taxi_locat == "창원시") ? "selected" : ""; ?>>경상남도 창원시</option>
                                        <option value="진주시" <?= ($taxi_locat == "진주시") ? "selected" : ""; ?>>경상남도 진주시</option>
                                        <option value="통영시" <?= ($taxi_locat == "통영시") ? "selected" : ""; ?>>경상남도 통영시</option>
                                        <option value="사천시" <?= ($taxi_locat == "사천시") ? "selected" : ""; ?>>경상남도 사천시</option>
                                        <option value="김해시" <?= ($taxi_locat == "김해시") ? "selected" : ""; ?>>경상남도 김해시</option>
                                        <option value="밀양시" <?= ($taxi_locat == "밀양시") ? "selected" : ""; ?>>경상남도 밀양시</option>
                                        <option value="거제시" <?= ($taxi_locat == "거제시") ? "selected" : ""; ?>>경상남도 거제시</option>
                                        <option value="양산시" <?= ($taxi_locat == "양산시") ? "selected" : ""; ?>>경상남도 양산시</option>
                                        <option value="">-----제주특별자치도</option>
                                        <option value="제주시" <?= ($taxi_locat == "제주시") ? "selected" : ""; ?>>제주특별자치도 제주시</option>
                                        <option value="서귀포시" <?= ($taxi_locat == "서귀포시") ? "selected" : ""; ?>>제주특별자치도 서귀포시</option>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><label for="taxiName">호출명</label></th>
                                <td>
                                    <input type="text" name="taxiName" value="<?= $taxi_Name ?>" id="taxiName" required class="required frm_input" size="50" />
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><label for="taxiType">호출타입</label></th>
                                <td colspan="3">
                                    <input type="radio" name="taxiType" value="1" id="taxi_Type1" <?= ($taxi_Type == "1") ? "checked" : ""; ?> required class="required" />
                                    <label for="taxi_Type1">앱</label>
                                    <input type="radio" name="taxiType" value="0" id="taxi_Type0" <?= ($taxi_Type == "0") ? "checked" : ""; ?> required class="required" />
                                    <label for="taxi_Type0">전화</label>
                                    <input type="radio" name="taxiType" value="2" id="taxi_Type2" <?= ($taxi_Type == "2") ? "checked" : ""; ?> required class="required" />
                                    <label for="taxi_Type2">둘다</label>
                                </td>
                            </tr>
                            <tr class="taxi0" <?= ($taxi_Type == "0" || $taxi_Type == "2") ? "style='display:;'" : "style='display:none;'"; ?>>
                                <th scope="row"><label for="taxiTel">호출전화번호</label></th>
                                <td>
                                    <input type="text" name="taxiTel" value="<?= $taxi_Tel ?>" id="taxiTel" class="frm_input" />
                                </td>
                            </tr>
                            <tr class="taxi1" <?= ($taxi_Type == "1" || $taxi_Type == "2") ? "style='display:;'" : "style='display:none;'"; ?>>
                                <th scope="row"><label for="taxiAppAddr">앱 주소</label></th>
                                <td colspan="3">
                                    <span class="frm_info">안드로이드 설치 주소</span>
                                    <input type="text" name="taxiAndInstall" value="<?= $taxi_And_Install ?>" id="taxi_And_Install" class="frm_input" size="50" />
                                    <span class="frm_info">애플 설치 주소</span>
                                    <input type="text" name="taxiIosInstall" value="<?= $taxi_Ios_Install ?>" id="taxi_Ios_Install" class="frm_input" size="50" />
                                    <span class="frm_info">애플 스키마</span>
                                    <input type="text" name="taxiIos" value="<?= $taxi_Ios ?>" id="taxi_Ios" class="frm_input" size="50" />
                                </td>
                            </tr>
                            <tr class="taxi1" <?= ($taxi_Type == "1" || $taxi_Type == "2") ? "style='display:;'" : "style='display:none;'"; ?>>
                                <th scope="row"><label for="taxiImg">앱 이미지</label></th>
                                <td>
                                    <span class="frm_info">이미지 크기는 <strong>넓이 720픽셀 높이 300픽셀</strong>로 해주세요.</span>
                                    <input type="file" name="taxi_Img" id="taxi_Img">
                                    <?
                                    //BLOB 파일 형태로 저장된 이미지 파일 출력되도록 ------------------- 2019.02.15
                                    if ($taxi_ImgFile) {
                                    ?>
                                        <img src="/data/taxicall/photo.php?id=<? echo $taxi_ImgFile ?>" style="height:200px">
                                        <input type="checkbox" id="del_taxiImg" name="del_taxiImg" value="1">삭제
                                    <?
                                    }

                                    ?>

                                    <? if ($mode == "mod") { ?>
                                        <input type="hidden" name="taxi_ImgFile" value="<?= $taxi_ImgFile ?>">
                                    <? } ?>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><label for="taxiMemo">관리자메모</label></th>
                                <td>
                                    <textarea name="taxiMemo" id="taxiMemo"><?= $taxi_Memo ?></textarea>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><label for="taxi_UseBit">사용여부</label></th>
                                <td colspan="3">
                                    <input type="radio" name="taxiUseBit" value="0" id="taxi_UseBit0" <?= ($taxi_UseBit == "0") ? "checked" : ""; ?> checked required class="required" />
                                    <label for="taxi_UseBit0">사용</label>
                                    <input type="radio" name="taxiUseBit" value="1" id="taxi_UseBit1" <?= ($taxi_UseBit == "1") ? "checked" : ""; ?> required class="required" />
                                    <label for="taxi_UseBit1">사용안함</label>
                                </td>
                            </tr>

                        </tbody>
                    </table>
                </div>

                <div class="btn_fixed_top">
                    <a href="taxiCallList.php?<?= $qstr ?>&page=<?= $page ?>" class="btn btn_02">목록</a>
                    <?
                    if ($mode == "mod") {
                    ?>
                        <a href="taxiCallProc.php?<?= $qstr ?>&page=<?= $page ?>&idx=<?= $idx ?>&mode=del" class="btn btn_01">삭제</a>
                    <? } ?>
                    <input type="submit" value="확인" class="btn_submit btn" accesskey='s'>
                </div>
            </form>


            <script>
                $(function() {
                    $("input[name=taxiType]").change(function() {

                        var radioValue = $(this).val();

                        if (radioValue == "0") { //정액할인(원)
                            $(".taxi0").css("display", "");
                            $(".taxi1").css("display", "none");
                        } else if (radioValue == "1"){
                            $(".taxi0").css("display", "none");
                            $(".taxi1").css("display", "");
                        } else {
                            $(".taxi0").css("display", "");
                            $(".taxi1").css("display", "");
                        }
                    });
                });

                function fubmit(f) {
                    if ($.trim($('#taxiName').val()) == '') {
                        message = "호출명를 입력해 주세요!";
                        alert(message);
                        chk = "#taxiName";
                        $(chk).focus();
                        return false;
                    }

                    if ($.trim($(':radio[name="taxiType"]:checked').val()) == '') {
                        message = "호출타입을 선택해주세요.";
                        alert(message);
                        chk = "#taxiType";
                        $(chk).focus();
                        return false;
                    }

                    if ($.trim($(':radio[name="taxiUseBit"]:checked').val()) == '') {
                        message = "사용여부를 선택해주세요.";
                        alert(message);
                        chk = ':radio[name="taxiUseBit"]';
                        $(chk).focus();
                        return false;
                    }
                    return true;
                }
            </script>

        </div>

        <? include "../common/inc/inc_footer.php";  //푸터 
        ?>