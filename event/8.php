<?
/*======================================================================================================================

* 프로그램			: 미션 상세 페이지 (웹뷰)
* 페이지 설명		: 메이커 같이타기 요청받기 (TB_MISSION 테이블에 idx 번호)
* 파일명            : 8.php

========================================================================================================================*/
include "../udev/lib/common.php";
include DU_COM . "/functionDB.php";

$DB_con = db1();
$idx = 6;
$mission_Idx = trim($idx);                  // 미션 고유번호
$mem_Id = trim($memId);             // 회원 아이디
$mem_Idx = memIdxInfo($mem_Id);     // 회원 고유번호 

// 가이드 URL 조회
$guideQuery = "SELECT con_GuideUrl FROM TB_CONFIG WHERE idx = 1";
$guideStmt = $DB_con->prepare($guideQuery);
$guideStmt->execute();
$guideRow = $guideStmt->fetch(PDO::FETCH_ASSOC);
$guideUrl = $guideRow['con_GuideUrl'];              // 웹뷰 가이드 URL

// 미션 정보 조회
$mission = missionInfoChk($mission_Idx);            // 미션 정보 조회

// 미션 수행 이력 조회
$mCnt = missionHistoryChk($mission_Idx, $mem_Idx);  // mCnt = 0 : 미션 수행 이력 없음, mCnt = 1 : 미션 수행 이력 있음

// 노선 생성 여부
$makerChk = makerChk($mem_Idx);              // 노선 생성 여부 (1: 노선 생성, 0: 노선 미생성)

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
            --font-black: #18272F;
            --button-white: #FFFFFF;
            --button-white-border: #E5E5E5E6;
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
            --font-size-xxxs: 16px;
            --font-size-xxxxs: 14.5px;
        }

        body {
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
            margin-bottom: 70px;
            display: flex;
            flex-direction: column;
        }

        .content>.content_pre>.content_Button {
            width: 100%;
            display: flex;
            flex-direction: row;
            justify-content: center;
            gap: 4px;
        }

        .content>.content_pre>.content_Button>a {
            width: 50%;
        }

        .content>.content_pre>.content_Button>a>.button_Give {
            width: 100%;
            font-family: var(--font-family-pretendard-semibold);
            font-size: var(--font-size-xxxs);
            background-color: var(--button-white);
            color: var(--font-black);
            line-height: 19px;
            border: 0px;
            padding: 11px 35px;
            border-radius: 4px;
            border: 1px solid var(--button-white-border);
        }

        .content>.content_pre>.content_Button>a>.button_Do {
            width: 100%;
            font-family: var(--font-family-pretendard-semibold);
            font-size: var(--font-size-xxxs);
            background-color: var(--button-blue);
            color: var(--font-white);
            line-height: 19px;
            border: 0px;
            padding: 11px 27px;
            border-radius: 4px;
        }

        .footer {
            width: 100%;
            background-color: var(--footer-background);
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
            margin-bottom: 18px;
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
            padding-bottom: 3px;
        }

        .footer>div:nth-child(2)>div>div:nth-child(2) {
            width: 100%;
        }
    </style>
    <script type="text/javascript">
        $(document).ready(function() {
            // 예시: 모바일 여부를 출력하는 코드
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
            // 메이커가이드 클릭
            $('.button_Give').click(function() {
                var guideUrl = $("#guideUrl").val();
                location.href = guideUrl + "?id=content_Maker";
            });

            // 500원 받기 클릭
            $('.button_Do').click(function() {
                var idx = $("#idx").val(); // 미션 고유번호
                var mem_Idx = $("#mem_Idx").val(); // 회원고유번호
                var makerChk = $("#makerChk").val(); // 메이커노선생성여부확인 (1:생성, 0:미생성)
                var title = ''; // 플러터 팝업 굵은 글씨
                var contents = ''; // 플러터 팝업 그외 텍스트
                var confirmText = '확인'; // 확인 버튼 텍스트 
                var cancelText = '취소'; //취소 버튼 텍스트
                var confirmColor = '#326CF9'; // 확인 버튼 색깔 ex) #326CF9(파랑), #EE5055(빨강) 생략시 파랑으로 기본 적용
                if (makerChk == true) {
                    title = "요청을 대기 중입니다.";
                    contents = "투게더의 요청을 받으면\n포인트를 즉시 지급해드려요!";
                    cancelText = "확인";
                    confirmText = "내 노선 보기";
                    window.flutter_inappwebview.callHandler('checkPopup', title, contents, confirmText, cancelText, confirmColor).then(function(result) {
                        if (result) { // 내 노선 보기 누를시
                            // 웹뷰 닫기
                            window.flutter_inappwebview.callHandler('close');
                            // 메이커 대기 화면으로 이동
                            window.flutter_inappwebview.callHandler('push', true, '/maker/wait/mode');
                        } else {
                            return false;
                        }
                    });
                } else {
                    // 웹뷰 닫기
                    window.flutter_inappwebview.callHandler('close');
                    // 공지사항&이벤트&미션 새로고침
                    window.flutter_inappwebview.callHandler('noticeRefresh');
                    // 만들기 페이지로 이동
                    window.flutter_inappwebview.callHandler('push', true, '/create/route');
                }
            });
        });
    </script>
</head>

<body>
    <div class="du01">
        <input type="hidden" id="mCnt" name="mCnt" value="<?= $mCnt ?>" />
        <input type="hidden" id="idx" name="idx" value="<?= $idx ?>" />
        <input type="hidden" id="mem_Idx" name="mem_Idx" value="<?= $mem_Idx ?>" />
        <input type="hidden" id="mission_Bit" name="mission_Bit" value="N" />
        <input type="hidden" id="url" name="url" value="<?= $mission['mLink'] ?>" />
        <input type="hidden" id="guideUrl" name="guideUrl" value="<?= $guideUrl ?>" />
        <input type="hidden" id="makerChk" name="makerChk" value="<?= $makerChk ?>" />
        <?
        if (COUNT($mission) < 1) { //없을 경우
        } else {
            $m_Name = $mission['mName'];
            $m_SPoint = $mission['mSPoint'];
            $b_Content = html_Decode($v['b_Content']);
            $reg_Date = $mission['regDate'];
            $regDate = date("y.m.d", strtotime($reg_Date));
        ?>
            <div class="title">
                <div class="text">같이타기 만드신 메이커님,<BR>요청까지 받아보세요</div>
                <div class="date"><?= $regDate ?></div>
            </div>
            <div class="line"></div>
            <div class="content">
                <div class="content_pre">
                    <div class="content_Img">
                        <img src="img/8_1.jpg" alt="8_1.jpg" style="width: 100%;">
                        <img src="img/8_2.jpg" alt="8_2.jpg" style="width: 100%;">
                    </div>
                    <div class="content_Button">
                        <a href="javascript:;"><button class="button_Give">메이커 가이드</button></a>
                        <a href="javascript:;"><button class="button_Do">같이타기 요청받기</button></a>
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
                <div>본 이벤트는 이벤트가 종료되는 날까지 매일 참여가 가능합니다.</div>
            </div>
            <div>
                <div><img src="img/disc.png" alt="disc.png"></div>
                <div>이용 완료를 하지 않더라도 만들고 대기 상태로 기다렸을 때, 투게더의 요청이 쌓이면 포인트는 즉시 지급되며, 지급된 포인트는 [메뉴]→[내 포인트]에서 확인할 수 있습니다.</div>
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