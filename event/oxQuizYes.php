<?
/*======================================================================================================================

* 프로그램			: 오늘의 OX 정답인 경우 페이지 (웹뷰)
* 페이지 설명		: 오늘의 OX 정답인 경우 페이지 (웹뷰)
* 파일명            : oxQuizYes.php

========================================================================================================================*/
include "../udev/lib/common.php";
include DU_COM . "/functionDB.php";

$DB_con = db1();
$ox_Idx = trim($oxIdx);             // 오늘의 OX 미션 고유번호

//페이지 추가하기.
$oxQuery = " SELECT idx, ox_Cate, ox_Question, ox_Answer, ox_Explanation FROM TB_OX WHERE idx = :idx ";
$oxStmt = $DB_con->prepare($oxQuery);
$oxStmt->bindparam(":idx", $ox_Idx);
$oxStmt->execute();
$oxCount = $oxStmt->rowCount();

$oxRow = $oxStmt->fetch(PDO::FETCH_ASSOC);

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
<!DOCTYPE html>
<html>

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, target-densitydpi=medium-dpi" />
    <title>OX퀴즈(<?= $ox_Cate ?>) 정답</title>
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
            --line: #F8F8F8;
            --line-shadow: #B5B5B51C;
            --content: #5A5C5E;
            --ans-content: #656A6D;
            --font-white: #FFFFFF;
            --button-blue: #326CF9;
            --button-red: #F04452;
            --footer: #5C6F9E;
            --footer-title: #18272FD1;
            --footer-background: #F1F1F1;
            --footer-font-color: #747474;

            --font-size-l: 40px;
            --font-size-m: 20px;
            --font-size-s: 18px;
            --font-size-xl: 48px;
            --font-size-xs: 19px;
            --font-size-xxs: 18.5px;
            --font-size-xxxs: 28px;
            --font-size-xxxxs: 22px;
            --font-size-xxxxxs: 20px;
            --font-size-xxxxxxs: 15px;
        }


        body {
            width: 99.5%;
            height: 100%;
            display: flex;
            flex-wrap: wrap;
            background: #FFFFFF 0% 0% no-repeat padding-box;
            align-content: space-between;
            overflow-x: hidden;
        }

        .du01 {
            width: 100%;
            display: flex;
            flex-wrap: wrap;
            justify-content: flex-start;
            flex-direction: column;
        }

        .title {
            padding: 8%;
            margin-bottom: 10px;
            width: 250px;
        }

        .title>.text {
            font-family: var(--font-family-pretendard-semibold);
            font-size: 17px;
            color: var(--title);
            line-height: 24px;
            letter-spacing: -0.32px;
            padding-top: 7%;
        }

        .ans_title {
            padding: 8%;
            margin-bottom: 21px;
            text-align: center;
        }

        .ans_title>.ans_text {
            margin-top: 10%;
            width: 100%;
            font-family: var(--font-family-pretendard-semibold);
            font-size: var(--font-size-xxxxs);
            color: var(--button-blue);
            padding-bottom: 2%;
            line-height: 24px;
        }

        .ans_title>.ans_memo {
            width: 100%;
            font-family: var(--font-family-pretendard-semibold);
            font-size: var(--font-size-xxxxs);
            color: var(--footer-title);
            padding-bottom: 10%;
            line-height: 24px;
        }

        .ans_title>.ans_face {
            padding-top: 2%;
        }

        .ans_title>.ans_exp {
            width: 100%;
            font-family: var(--font-family-pretendard-regular);
            font-size: var(--font-size-xxxxxxs);
            color: var(--ans-content);
            line-height: 24px;
            white-space: pre;
            /* margin-top: -2%; */
        }

        .date {
            font-family: var(--font-family-pretendard-semibold);
            font-size: 20px;
            color: var(--date);
            line-height: 24px;
        }

        .line {
            width: 100%;
            height: 13px;
            background-color: var(--line);
            box-shadow: inset 0px 3px 3px var(--line-shadow);
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

            // 확인 클릭 이벤트
            $('.answer_Comp').click(function() {
                window.flutter_inappwebview.callHandler('noticeRefresh');
                window.flutter_inappwebview.callHandler('close');
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
    </script>
</head>

<body>
    <div class="du01">
        <div class="title">
            <div class="date"><?= $ox_Cate ?></div>
            <div class="text"><?= $ox_Question ?></div>
        </div>
        <div class="line"></div>
        <div class="ans_title">
            <div class="ans_text">대단해요! 정답</div>
            <div class="ans_memo">50원을 지급해드렸어요!</div>
            <div class="ans_face"><img src="./img/face_Yes.gif" /></div>
            <div class="ans_exp">
                <div class="content_pre"><?= $ox_Explanation ?></div>
            </div>
        </div>
    </div>
    <div class="footer">
        <div class="answer_Comp"><a href="javascript:;"><button>확인</button></a></div>
    </div>
</body>

</html>