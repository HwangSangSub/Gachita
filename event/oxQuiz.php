<?
/*======================================================================================================================

* 프로그램			: OX퀴즈 풀기 (웹뷰)
* 페이지 설명		: OX퀴즈 이벤트 상세페이지에서 오늘의 퀴즈풀고 50원 받기 이벤트버튼 클릭 할 경우 문제 페이지로 넘어감.
* 파일명            : oxQuiz.php

========================================================================================================================*/
include "../udev/lib/common.php";
include DU_COM . "/functionDB.php";

$DB_con = db1();
$mission_Idx = trim($idx);          // 미션 고유번호
$ox_Idx = trim($oxIdx);             // 오늘의 OX 미션 고유번호
$mem_Idx = memIdxInfo($memIdx);     // 회원 고유번호 

// 미션 정보 조회
$mission = missionInfoChk($mission_Idx);            // 미션 정보 조회
$m_Name = $mission['mName'];                        // 미션 명

//페이지 추가하기.
$oxQuery = " SELECT idx, ox_Cate, ox_Question, ox_Answer, ox_Explanation FROM TB_OX WHERE idx = :idx ";
$oxStmt = $DB_con->prepare($oxQuery);
$oxStmt->bindparam(":idx", $ox_Idx);
$oxStmt->execute();
$oxCount = $oxStmt->rowCount();

?>
<!DOCTYPE html>
<html>

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, target-densitydpi=medium-dpi" />
    <title><?= $m_Name ?></title>
    <link rel="StyleSheet" HREF="../board/css/common.css" type="text/css" title="Global CSS">
    <link rel="StyleSheet" HREF="../../common/css/pretendard/pretendard.css" type="text/css" title="Global CSS">
    <link rel="StyleSheet" HREF="../board/css/board-style.css" type="text/css" title="Global CSS">
    <link rel="StyleSheet" HREF="../board/css/jquery-ui-1.11.1.css" type="text/css" title="Global CSS">
    <script language='javascript' src="../board/js/jquery-1.11.0.min.js" type="text/javascript"></script>
    <script language='javascript' src="../board/js/jquery-ui-1.11.1.js" type="text/javascript"></script>
    <script language='javascript' src="../board/js/jquery.animate-enhanced.js"></script>
    <script language='javascript' src="../board/js/jquery.form.js" type="text/javascript"></script>
    <script language='javascript' src="../board/js/common.js" type="text/javascript"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/meyer-reset/2.0/reset.min.css">
    <style>
        :root {

            --title: #7F98D5;
            --date: #D1D3D5;
            --line: #E1E1E1;
            --content: #5A5C5E;
            --font-white: #FFFFFF;
            --button-blue: #326CF9;
            --button-red: #F04452;
            --footer: #5C6F9E;
            --footer-title: #18272FD1;
            --footer-background: #F1F1F1;
            --footer-font-color: #747474;
            --answer-prev: #D0D3DA;
            --button-background-prev: #EBEEF3;

            --font-size-l: 40px;
            --font-size-m: 20px;
            --font-size-s: 18px;
            --font-size-xl: 48px;
            --font-size-xs: 19px;
            --font-size-xxs: 18.5px;
            --font-size-xxxs: 25px;
            --font-size-xxxxs: 25px;
            --font-size-xxxxxs: 20px;
        }


        body {
            width: 99.5%;
            height: 100%;
            display: flex;
            flex-wrap: wrap;
            background: #FFFFFF 0% 0% no-repeat padding-box;
            align-content: space-between;
            overflow: hidden;
        }

        .du01 {
            width: 100%;
            padding: 8%;
            display: flex;
            flex-wrap: wrap;
            justify-content: flex-start;
            flex-direction: column;
        }

        .title {
            margin-bottom: 21px;
            width: 250px;
        }

        .text {
            font-family: var(--font-family-pretendard-semibold);
            font-size: 17px;
            color: var(--title);
            line-height: 24px;
            letter-spacing: -0.34px;
            padding-top: 7%;
        }

        .date {
            font-family: var(--font-family-pretendard-semibold);
            font-size: 20px;
            color: var(--date);
            line-height: 24px;
        }

        .line {
            border-top: 1px solid var(--line);
        }

        .content {
            padding-top: 30%;
            display: flex;
            flex-direction: column;
            flex-wrap: nowrap;
        }

        .content>.content_title {
            font-family: var(--font-family-pretendard-semibold);
            font-size: 20px;
            color: var(--date);
            line-height: 24px;
            text-align: center;
            margin-bottom: 10%;
        }

        .content>.content_answer {
            display: flex;
            flex-direction: row;
            justify-content: center;
            align-items: center;
        }

        /**
         *  yes
         */
        .content>.content_answer>.content_yes {
            display: flex;
            flex-direction: column;
            justify-content: flex-start;
        }

        .content>.content_answer>.content_yes>.content_img {
            margin-bottom: 20%;
            width: 100px;
            height: 100px;
        }

        .content>.content_answer>.content_yes>.content_img>img {
            width: 100px;
            height: 100px;
        }

        .content>.content_answer>.content_yes>.content_chk {
            display: flex;
            flex-direction: row;
            justify-content: center;
            align-items: center;
        }

        .content>.content_answer>.content_yes>.content_chk>.content_ChkImg {
            width: 22px;
            height: 22px;
            margin-right: 5%;
            background-image: url("./img/non_Chk.png");
            background-size: cover;
        }

        .content>.content_answer>.content_yes>.active>.content_ChkImg {
            width: 22px;
            height: 22px;
            margin-right: 5%;
            background-image: url("./img/yes_Chk.png") !important;
            background-size: cover;
        }

        .content>.content_answer>.content_yes>.content_chk>.content_ChkText {
            font-family: var(--font-family-pretendard-medium);
            font-size: var(--font-size-xxxxxs);
            color: var(--date);
            line-height: 22px;
        }

        .content>.content_answer>.content_yes>.active>.content_ChkText {
            font-family: var(--font-family-pretendard-medium);
            font-size: var(--font-size-xxxxxs);
            color: var(--button-blue) !important;
            line-height: 22px;
        }

        /**
         *  no
         */
        .content>.content_answer>.content_no>.content_img {
            margin-bottom: 20%;
            width: 100px;
            height: 100px;
        }

        .content>.content_answer>.content_no>.content_img>img {
            width: 100px;
            height: 100px;
        }

        .content>.content_answer>.content_no>.content_chk {
            display: flex;
            flex-direction: row;
            justify-content: center;
            align-items: center;
        }

        .content>.content_answer>.content_no>.content_chk>.content_ChkImg {
            width: 22px;
            height: 22px;
            margin-right: 5%;
            background-image: url("./img/non_Chk.png");
            background-size: cover;
        }

        .content>.content_answer>.content_no>.active>.content_ChkImg {
            width: 22px;
            height: 22px;
            margin-right: 5%;
            background-image: url("./img/no_Chk.png") !important;
            background-size: cover;
        }

        .content>.content_answer>.content_no>.content_chk>.content_ChkText {
            font-family: var(--font-family-pretendard-medium);
            font-size: var(--font-size-xxxxxs);
            color: var(--date);
            line-height: 22px;
        }

        .content>.content_answer>.content_no>.active>.content_ChkText {
            font-family: var(--font-family-pretendard-medium);
            font-size: var(--font-size-xxxxxs);
            color: var(--button-red) !important;
            line-height: 22px;
        }

        .content>.content_answer>.content_face {
            display: flex;
            flex-direction: column;
            width: 100px;
            height: 100px;
            margin: 5%;
            flex-wrap: wrap;
            justify-content: flex-start;
            align-content: center;
            padding-bottom: 5%;
        }

        .content>.content_answer>.content_face>img {
            width: 75px;
            height: 75px;
        }

        .footer {
            width: 100%;
            margin: 0px 10%;
            padding-bottom: 5%;
        }

        .footer>div {
            font-family: var(--font-family-pretendard-regular);
            font-size: var(--font-size-xxs);
            color: var(--footer);
        }

        .footer>.answer_Prev>a {
            pointer-events: none;
        }

        .footer>.answer_Prev>a>button {
            font-family: var(--font-family-pretendard-semibold);
            font-size: 17px;
            background-color: var(--button-background-prev);
            color: var(--answer-prev);
            line-height: 19px;
            border: 0px;
            margin: 15% 13% 0% 0%;
            padding: 5% 0%;
            opacity: 1;
            border-radius: 4px;
            width: 100%;
            pointer-events: none;
        }

        .footer>.answer_Comp>a>button {
            font-family: var(--font-family-pretendard-semibold);
            font-size: 17px;
            background-color: var(--button-blue);
            color: var(--font-white);
            line-height: 19px;
            border: 0px;
            margin: 15% 13% 0% 0%;
            padding: 5% 0%;
            opacity: 1;
            border-radius: 4px;
            width: 100%;
        }
    </style>
    <script type="text/javascript">
        $(document).ready(function() {
            // 예시: 모바일 여부를 출력하는 코드
            var isMobile = isMobileDevice();
            // if (isMobile) {
            //     var elements = document.getElementsByTagName('*');
            //     // 각 요소의 스타일 수정하기
            //     var widthRate = window.innerWidth / 375;
            //     var heightRate = window.innerHeight / 667;
            //     if (widthRate > heightRate) {
            //         var rate = heightRate;
            //     } else {
            //         var rate = widthRate;
            //     }

            //     // var newFontSize = currentFontSize * (rate); // 휴대폰 기준 폭을 기준으로 폰트 크기를 조정
            //     for (var i = 0; i < elements.length; i++) {
            //         var element = elements[i];
            //         var styles = window.getComputedStyle(element);
            //         // fontsize 속성이 존재하는 경우에만 처리
            //         if (styles.fontSize) {
            //             var currentFontSize = parseFloat(styles.fontSize);
            //             var newFontSize = currentFontSize * rate; // 폰트 비율에 따라 동적으로 값을 가져옴
            //             element.style.fontSize = newFontSize + 'px';
            //         }
            //         if (styles.lineHeight) {
            //             var currentHeight = parseFloat(styles.lineHeight);
            //             var newHeight = currentHeight * rate; // 폰트 비율에 따라 동적으로 값을 가져옴
            //             element.style.lineHeight = newHeight + 'px';
            //         }
            //     }
            // }
            // OX 퀴즈 선택 CSS
            $('.content_button').click(function() {
                // 클릭한 태그의 클래스명을 가져오기.
                var getClass = $(this).attr("class");
                // 체크값이 뭔지 확인하기.
                var whatChk = "content_yes";
                // 체크값의 선택된것인지 확인하기.
                var activeChk = "active";
                // ox_Val 값 가져오기.  정답여부(1: 그렇다, 2: 아니다)
                $('#oxVal').val('');

                $('.footer').find("div").attr('class', 'answer_Prev');
                if (getClass.indexOf(whatChk) != -1) {
                    $('.content_no').find(".content_chk").attr('class', 'content_chk');
                    var getChkClass = $('.content_yes').find(".content_chk").attr('class');
                    if (getChkClass.indexOf(activeChk) != -1) {
                        // 이미 체크된 상태라면 체크 해제하기.
                        $('.content_yes').find(".content_chk").attr('class', 'content_chk');
                    } else {
                        $('.content_yes').find(".content_chk").attr('class', 'content_chk active');
                        $('#oxVal').val('1');
                        $('.footer').find("div").attr('class', 'answer_Comp');
                    }
                } else {
                    $('.content_yes').find(".content_chk").attr('class', 'content_chk');
                    var getChkClass = $('.content_no').find(".content_chk").attr('class');
                    if (getChkClass.indexOf(activeChk) != -1) {
                        // 이미 체크된 상태라면 체크 해제하기.
                        $('.content_no').find(".content_chk").attr('class', 'content_chk');
                    } else {
                        $('.content_no').find(".content_chk").attr('class', 'content_chk active');
                        $('#oxVal').val('2');
                        $('.footer').find("div").attr('class', 'answer_Comp');
                    }
                }
            });
        });

        function isMobileDevice() {
            var userAgent = navigator.userAgent.toLowerCase();
            var mobileKeywords = ['mobile', 'iphone', 'ipod', 'android', 'blackberry', 'windows phone'];

            for (var i = 0; i < mobileKeywords.length; i++) {
                if (userAgent.indexOf(mobileKeywords[i]) !== -1) {
                    return true;
                }
            }

            return false;
        }

        // 미션 풀기 버튼 클릭 이벤트
        function oxQuizProc() {
            // 미션고유번호
            var idx = $("#idx").val();
            // 회원고유번호
            var memIdx = $("#memIdx").val();
            // 오늘의 OX 퀴즈고유번호
            var oxIdx = $("#oxIdx").val();
            // 오늘의 OX 퀴즈 회원이 선택한 답
            var oxVal = $("#oxVal").val();

            // 0: 미션 수행 이력이 없는 경우 OX 미션 진행
            if (oxVal == "") {
                var title = "정답을 선택하지 않았습니다.\n정답을 선택해주세요.";
                var contents = '';
                window.flutter_inappwebview.callHandler('popup', title, contents);
                return false;
            } else {
                //ajax로 미션 수행 이력 저장하기.
                $.ajax({
                    type: "POST",
                    url: "oxQuizProc.php",
                    data: {
                        idx: idx,
                        memIdx: memIdx,
                        oxIdx: oxIdx,
                        oxVal: oxVal
                    },
                    dataType: "json",
                    success: function(data) {
                        if (data.result == true) {
                            if (data.oxBit == true) {
                                location.href = "oxQuizYes.php?oxIdx=" + oxIdx;
                            } else {
                                location.href = "oxQuizNo.php?oxIdx=" + oxIdx;
                            }
                        } else {
                            var title = "오류가 발생하였습니다.\n관리자에게 문의바랍니다.";
                            var contents = '';
                            window.flutter_inappwebview.callHandler('popup', title, contents);
                            return false;
                        }
                    },
                    error: function(xhr, status, error) {
                        // alert("error : " + error);
                        var title = "오류가 발생하였습니다.\n관리자에게 문의바랍니다.";
                        var contents = '';
                        window.flutter_inappwebview.callHandler('popup', title, contents);
                        return false;
                    }
                });
            }
        }
    </script>
</head>

<body>
    <div class="du01">
        <?
        if ($oxCount < 1) { //없을 경우
        } else {
            while ($oxRow = $oxStmt->fetch(PDO::FETCH_ASSOC)) {

                if ($oxRow['ox_Cate'] == "1") {
                    $ox_Cate = "같이타기";
                } else if ($oxRow['ox_Cate'] == "2") {
                    $ox_Cate = "포인트(출금)";
                } else if ($oxRow['ox_Cate'] == "3") {
                    $ox_Cate = "공지 및 이벤트";
                } else  if ($oxRow['ox_Cate'] == "4") {
                    $ox_Cate = "아재 테스트";
                } else  if ($oxRow['ox_Cate'] == "5") {
                    $ox_Cate = "등급";
                } else {
                    $ox_Cate = "미분류";
                }
                $ox_Question = html_Decode($oxRow['ox_Question']);
                $ox_Answer = trim($oxRow['ox_Answer']);
                $ox_Explanation = html_Decode($oxRow['ox_Explanation']);
        ?>

                <div class="title">
                    <div class="date"><?= $ox_Cate ?></div>
                    <div class="text"><?= $ox_Question ?></div>
                </div>
                <div class="content">
                    <div class="content_title">정답을 선택해주세요.</div>
                    <div class="content_answer">
                        <input type="hidden" name="idx" id="idx" value="<?= $idx ?>" />
                        <input type="hidden" name="memIdx" id="memIdx" value="<?= $memIdx ?>" />
                        <input type="hidden" name="oxIdx" id="oxIdx" value="<?= $oxIdx ?>" />
                        <input type="hidden" name="oxVal" id="oxVal" value="" />
                        <div class="content_yes content_button">
                            <div class="content_img">
                                <img src="./img/yes.png" />
                            </div>
                            <div class="content_chk">
                                <span class="content_ChkImg"></span>
                                <span class="content_ChkText">그렇다</span>
                            </div>
                        </div>
                        <div class="content_face"><img src="./img/thinking_face.gif" /></div>
                        <div class="content_no content_button">
                            <div class="content_img">
                                <img src="./img/no.png" />
                            </div>
                            <div class="content_chk">
                                <span class="content_ChkImg"></span>
                                <span class="content_ChkText">아니다</span>
                            </div>
                        </div>
                    </div>
                </div>
        <?
            }
        }
        ?>
    </div>
    <div class="footer">
        <div class="answer_Prev"><a href="javascript:oxQuizProc();"><button>선택 완료</button></a></div>
    </div>
</body>

</html>