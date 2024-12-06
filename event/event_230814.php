<?
/*======================================================================================================================

* 프로그램			: 이벤트 상세 페이지
* 페이지 설명		: 가치타기로 택시 이용 인증하면 1%적립
* 파일명            : event_230814.php

========================================================================================================================*/
include "../udev/lib/common.php";
include DU_COM . "/functionDB.php";

$DB_con = db1();
$mem_Id = trim($memId);             // 회원 아이디
$mem_Idx = memIdxInfo($mem_Id);     // 회원 고유번호 

$makerChk = makerChk($mem_Idx);     // 노선 생성 여부 (1: 노선 생성, 0: 노선 미생성)

$query = "SELECT event_Title, event_Url, event_EndBit, reg_Date, end_Date FROM TB_EVENT WHERE idx = 2";
$stmt = $DB_con->prepare($query);
$stmt->execute();
$row = $stmt->fetch(PDO::FETCH_ASSOC);

$reg_Date = $row['reg_Date'];
$regDate = date("y.m.d", strtotime($reg_Date));
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
        $(document).ready(function() {
            $('.button_Do').click(function() {
                var makerChk = $("#makerChk").val(); // 메이커노선생성여부확인 (1:생성, 0:미생성)
                var title = ''; // 플러터 팝업 굵은 글씨
                var contents = ''; // 플러터 팝업 그외 텍스트
                var confirmText = '확인'; // 확인 버튼 텍스트 
                var cancelText = '취소'; //취소 버튼 텍스트
                var confirmColor = '#326CF9'; // 확인 버튼 색깔 ex) #326CF9(파랑), #EE5055(빨강) 생략시 파랑으로 기본 적용
                if (makerChk == true) {
                    title = "이미 내가 만든 같이 타기 노선이 있습니다.";
                    cancelText = "확인";
                    confirmText = "내 노선 보기";
                    window.flutter_inappwebview.callHandler('checkPopup', title, contents, confirmText, cancelText, confirmColor).then(function(result) {
                        if (result) { // 내 노선 보기 누를시
                            // 메이커 대기 화면으로 이동
                            window.flutter_inappwebview.callHandler('push', true, '/maker/wait/mode');
                        } else {
                            return false;
                        }
                    });
                } else {
                    // 만들기 페이지로 이동
                    window.flutter_inappwebview.callHandler('push', true, '/create/route');
                }
            });
        });
    </script>
</head>

<body>
    <div class="du01">
        <input type="hidden" id="makerChk" name="makerChk" value="<?= $makerChk ?>" />
        <div class="title">
            <div class="text">메이커님 ‘같이 타기’ 인증하고<BR>포인트 1% 추가 적립 받으세요!</div>
            <div class="date"><?= $regDate ?></div>
        </div>
        <div class="line"></div>
        <div class="content">
            <div class="content_pre">
                <div class="content_Img">
                    <img src="img/event_230814/01.jpg" alt="01.jpg" style="width: 100%;">
                    <img src="img/event_230814/02.jpg" alt="02.jpg" style="width: 100%;">
                    <img src="img/event_230814/03.jpg" alt="03.jpg" style="width: 100%;">
                    <img src="img/event_230814/04.jpg" alt="04.jpg" style="width: 100%;">
                    <img src="img/event_230814/05.jpg" alt="05.jpg" style="width: 100%;">
                </div>
                <div class="content_Button">
                    <a href="javascript:;" class="button_Do"><button>‘같이 타기’ 인증 참여하기</button></a>
                </div>
            </div>
        </div>
    </div>
    <div class="footer">
        <div>유의사항</div>
        <div>
            <div>
                <div><img src="img/disc.png" alt="disc.png"></div>
                <div>허위 사진이나 부적절한 게시물은 서비스 이용 약관이나<BR>관련 법률에 따라 제재를 받을 수 있습니다.</div>
            </div>
            <div>
                <div><img src="img/disc.png" alt="disc.png"></div>
                <div>포인트 적립은 인증 이미지 제출 이후 72시간 이내 적립되며,<BR>[메뉴] > [내 포인트] > [적립 예정 포인트]에서 확인할 수 있습니다.</div>
            </div>
            <div>
                <div><img src="img/disc.png" alt="disc.png"></div>
                <div>부적절한 방법으로 미션에 참여했다고 판단되는 경우,<BR>포인트 지급을 거부 및 회수하거나 서비스 이용을 제한할 수 있습니다.</div>
            </div>
            <div>
                <div><img src="img/disc.png" alt="disc.png"></div>
                <div>본 이벤트는 사전 공지 없이 변경되거나 종료될 수 있습니다.</div>
            </div>
        </div>
    </div>
</body>

</html>