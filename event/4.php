<?
/*======================================================================================================================

* 프로그램			: 미션 상세 페이지 (웹뷰)
* 페이지 설명		: 매일매일 OX 퀴즈 이벤트 (TB_MISSION 테이블에 idx 번호)
* 파일명            : 4.php

========================================================================================================================*/
include "../udev/lib/common.php";
include DU_COM . "/functionDB.php";

$DB_con = db1();
$mission_Idx = trim($idx);                  // 미션 고유번호
$mem_Id = trim($memId);             // 회원 아이디
$mem_Idx = memIdxInfo($mem_Id);     // 회원 고유번호 

// 오늘의 OX 퀴즈 문제 조회
$configQuery = "SELECT con_TodayOx FROM TB_CONFIG WHERE idx = 1";
$configStmt = $DB_con->prepare($configQuery);
$configStmt->execute();
$configRow = $configStmt->fetch(PDO::FETCH_ASSOC);
$ox_Idx = $configRow['con_TodayOx'];

// 미션 정보 조회
$mission = missionInfoChk($mission_Idx);            // 미션 정보 조회

// 미션 수행 이력 조회
$mCnt = missionHistoryChk($mission_Idx, $mem_Idx);  // mCnt = 0 : 미션 수행 이력 없음, mCnt = 1 : 미션 수행 이력 있음

?>
<!DOCTYPE html>
<html>

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, target-densitydpi=medium-dpi" />
    <title><?= $mission['mName'] ?></title>
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
            --title: #5C6F9E;
            --date: #8A91A1;
            --line: #E1E1E1;
            --content: #5A5C5E;
            --font-white: #FFFFFF;
            --button-blue: #326CF9;
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
            --font-size-xxxs: 17px;
            --font-size-xxxxs: 14.5px;
        }

        body {
            top: 0px;
            left: 0px;
            width: 100%;
            height: 100%;
            display: flex;
            flex-wrap: wrap;
            background: #FFFFFF 0% 0% no-repeat padding-box;
        }

        .du01 {
            width: 100%;
            padding: 30px;
            display: flex;
            flex-wrap: wrap;
            justify-content: flex-start;
            flex-direction: column;
        }

        .title {
            margin-bottom: 21px;
        }

        .text {
            font-family: var(--font-family-pretendard-semibold);
            font-size: var(--font-size-xs);
            color: var(--title);
            line-height: 24px;
            letter-spacing: -0.34px;
            padding-top: 12px;
            padding-bottom: 12px;
        }

        .date {
            font-family: var(--font-family-pretendard-medium);
            font-size: var(--font-size-xxs);
            color: var(--date);
            line-height: 19px;
        }

        .line {
            border-top: 1px solid var(--line);
        }

        .content {
            padding-top: 30px;
        }

        .content>.content_pre>.content_Img {
            width: 100%;
            margin-bottom: 25px;
            display: flex;
            flex-direction: column;
        }

        .content>.content_pre>.content_Button {
            width: 100%;
        }


        .content>.content_pre>.content_Button>a>button {
            width: 100%;
            font-family: var(--font-family-pretendard-semibold);
            font-size: var(--font-size-xs);
            background-color: var(--button-blue);
            color: var(--font-white);
            line-height: 19px;
            border: 0px;
            margin: 0px 14px 0px 0px;
            padding: 20px 0px 20px 0px;
            opacity: 1;
            border-radius: 4px;
        }

        .footer {
            width: 100%;
            background-color: var(--footer-background);
            width: 100%;
            padding: 50px;
            display: flex;
            flex-wrap: wrap;
            justify-content: flex-start;
            flex-direction: column;
        }

        .footer>div:first-child {
            font-family: var(--font-family-pretendard-bold);
            font-size: var(--font-size-xxxs);
            color: var(--footer-title);
            margin-bottom: 10px;
            line-height: 19px;
        }

        .footer>div:nth-child(2) {
            font-family: var(--font-family-pretendard-light);
            font-size: var(--font-size-xxxxs);
            color: var(--footer-font-color);
            line-height: 20px;
        }

        .footer>div:nth-child(2)>div {
            padding-bottom: 5px;
            display: flex;
            justify-content: flex-start;
            flex-direction: row;
            line-height: 20px;
        }

        .footer>div:nth-child(2)>div>div:first-child {
            width: 10px;
            padding-right: 5px;
        }

        .footer>div:nth-child(2)>div>div:first-child>img {
            width: 4px;
            height: 4px;
            padding-top: 8px;
        }

        .footer>div:nth-child(2)>div>div:nth-child(2) {
            width: 100%;
            font-size: 10px;
        }
    </style>
    <script type="text/javascript">
        // 미션 풀기 버튼 클릭 이벤트
        function oxQuiz() {
            var idx = $("#idx").val(); // 미션 고유번호
            var ox_Idx = $("#ox_Idx").val(); // 오늘의 OX 퀴즈미션
            var mem_Idx = $("#mem_Idx").val(); // 회원고유번호
            var mCnt = $("#mCnt").val(); // 미션 수행 이력 조회 (0: 미션 수행 이력 없음, 1: 미션 수행 이력 있음)

            // 0: 미션 수행 이력이 없는 경우 OX 미션 진행
            if (mCnt == 0) {
                location.href = "/event/oxQuiz.php?oxIdx=" + ox_Idx + "&memIdx=" + mem_Idx + "&idx=" + idx;
            } else {
                var title = "미션 수행 이력이 있습니다.";
                var contents = "";
                window.flutter_inappwebview.callHandler('popup', title, contents);
            }
        }
    </script>
</head>

<body>
    <div class="du01">
        <input type="hidden" id="mCnt" name="mCnt" value="<?= $mCnt ?>" />
        <input type="hidden" id="idx" name="idx" value="<?= $idx ?>" />
        <input type="hidden" id="ox_Idx" name="ox_Idx" value="<?= $ox_Idx ?>" />
        <input type="hidden" id="mem_Idx" name="mem_Idx" value="<?= $mem_Idx ?>" />
        <?
        if (COUNT($mission) < 1) { //없을 경우
        } else {
            $m_Name = $mission['mName'];
            $b_Content = html_Decode($v['b_Content']);
            $reg_Date = $mission['regDate'];
            $regDate = date("y.m.d", strtotime($reg_Date));
        ?>
            <div class="title">
                <div class="text">매일매일 OX 퀴즈 풀기<BR>한 달 짠테크로 1,550원 받자!</div>
                <div class="date"><?= $regDate ?></div>
            </div>
            <div class="line"></div>
            <div class="content">
                <div class="content_pre">
                    <div class="content_Img">
                        <img src="img/4_1.gif" alt="4_1.gif" style="width: 100%;">
                        <img src="img/4_2.jpg" alt="4_2.jpg" style="width: 100%;">
                    </div>
                    <div class="content_Button">
                        <a href="javascript:oxQuiz();"><button>오늘의 퀴즈풀고 50원 받기</button></a>
                    </div>
                </div>
            </div>
        <?
        }
        ?>
    </div>
    <div class="footer">
        <div>유의사항</div>
        <div>
            <div>
                <div><img src="img/disc.png" alt="disc.png"></div>
                <div>해당 미션은 1일 1회 참여로 진행되며 일일 중복 참여는 불가합니다.</div>
            </div>
            <div>
                <div><img src="img/disc.png" alt="disc.png"></div>
                <div>본 이벤트는 한 달간 매일 참여가 가능하며, 한 ID 당 출제되는 퀴즈의 내용은 랜덤으로 출제됩니다.</div>
            </div>
            <div>
                <div><img src="img/disc.png" alt="disc.png"></div>
                <div>당일 OX 퀴즈 문제를 맞추지 못했더라도 다음 날 새로운 퀴즈에 참여 가능합니다.</div>
            </div>
            <div>
                <div><img src="img/disc.png" alt="disc.png"></div>
                <div>퀴즈 결과가 정답일 경우 <?= number_format($mission['mSPoint']) ?>원이 지급되며, 오답일 경우 참여 보상으로 <?= number_format($mission['mFPoint']) ?>원이 지급됩니다.</div>
            </div>
            <div>
                <div><img src="img/disc.png" alt="disc.png"></div>
                <div>참여 종료 후 결과 공개와 포인트는 즉시 지급되며, 지급된 포인트는 [메뉴]→[내 포인트]에서 확인할 수 있습니다.</div>
            </div>
            <div>
                <div><img src="img/disc.png" alt="disc.png"></div>
                <div>부적절한 방법으로 미션에 참여했다고 판단되는 경우, 포인트 지급을 거부 및 회수하거나 서비스 이용을 제한할 수 있습니다.</div>
            </div>
            <div>
                <div><img src="img/disc.png" alt="disc.png"></div>
                <div>본 이벤트는 사전 공지 없이 변경되거나 종료될 수 있습니다.</div>
            </div>
        </div>
    </div>
</body>

</html>