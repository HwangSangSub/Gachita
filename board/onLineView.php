<?
/*======================================================================================================================

* 프로그램			: 문의사항 상세페이지 (웹뷰)
* 페이지 설명		: 문의사항 상세페이지 (웹뷰)
* 파일명          : onLineView.php

========================================================================================================================*/

include "../udev/lib/common.php";
include DU_COM . "/functionDB.php";

$DB_con = db1();

$idx = trim($idx);

//페이지 추가하기.
$nquery = " SELECT idx, b_Cate, b_Content, reg_Date, b_RContent, b_State, b_RDate FROM TB_ONLINE WHERE idx = :idx ORDER BY idx DESC";
$nqStmt = $DB_con->prepare($nquery);
$nqStmt->bindparam(":idx", $idx);
$nqStmt->execute();
$Ncounts = $nqStmt->rowCount();

//카테고리 확인
$query = "SELECT b_CateChk, b_CateName FROM TB_BOARD_SET WHERE b_Idx = 2 ORDER BY idx DESC";
$stmt = $DB_con->prepare($query);
$stmt->execute();
$num = $stmt->rowCount();

if ($num < 1) { //아닐경우
} else {
    $data = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {

        $b_CateChk = $row['b_CateChk'];
        if ($b_CateChk == 'N') {
            $result = array("result" => false, "errorMsg" => "등록된 카테고리가 없습니다. 확인 후 다시 시도해주세요.");
        } else {
            $b_CateName = $row['b_CateName'];
            $chk = explode("&", $b_CateName);
            for ($i = 0; $i < count($chk); $i++) {
                $cateNo = $i + 1;
                $cate = array("cateNo" => (int)$cateNo, "cateName" => (string)$chk[$i]);
                array_push($data, $cate);
            }
        }
    }
}
?>
<!DOCTYPE html>
<html>

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, target-densitydpi=medium-dpi" />
    <title>문의사항</title>
    <link rel="StyleSheet" HREF="css/common.css" type="text/css" title="Global CSS">
    <link rel="StyleSheet" HREF="../common/css/pretendard/pretendard.css" type="text/css" title="Global CSS">
    <link rel="StyleSheet" HREF="css/board-style.css" type="text/css" title="Global CSS">
    <link rel="StyleSheet" HREF="css/jquery-ui-1.11.1.css" type="text/css" title="Global CSS">
    <script language='javascript' src="js/jquery-1.11.0.min.js" type="text/javascript"></script>
    <script language='javascript' src="js/jquery-ui-1.11.1.js" type="text/javascript"></script>
    <script language='javascript' src="js/jquery.animate-enhanced.js"></script>
    <script language='javascript' src="js/jquery.form.js" type="text/javascript"></script>
    <script language='javascript' src="js/common.js" type="text/javascript"></script>
    <style>
        :root {
            --category: #F0F8FD;
            --title: #5C6F9E;
            --date: #8A91A1;
            --line: #E1E1E1;
            --answer-text: #777777;
            --answer-wait-back: #E5F1FC;
            --answer-wait: #4081BE;
            --answer-comp: #E9E9E9;
            --content: #5A5C5E;
            --footer: #5C6F9E;

            --font-size-l: 40px;
            --font-size-m: 20px;
            --font-size-s: 18px;
            --font-size-xl: 48px;
            --font-size-xs: 17px;
            --font-size-xxs: 15.5px;
            --font-size-xxxs: 15px;
        }

        body {
            top: 0px;
            left: 0px;
            width: 100%;
            height: 100vh;
            display: flex;
            flex-wrap: wrap;
            background: #FFFFFF 0% 0% no-repeat padding-box;
            opacity: 1;
        }

        .du01 {
            width: 100%;
            padding: 30px;
            display: flex;
            flex-wrap: wrap;
            justify-content: flex-start;
            flex-direction: column;
        }

        .cate_title {
            display: flex;
            justify-content: center;
            align-content: flex-start;
            flex-direction: column;
            flex-wrap: wrap;
            margin-bottom: 21px;
        }

        .cate_title>.category {
            padding: 7px 12px;
            border-radius: 5px;
            background-color: var(--category);
            font-family: var(--font-family-pretendard-semibold) !important;
            font-size: var(--font-size-xxxs);
            color: var(--title);
            line-height: 18px;
        }

        .title {
            margin-bottom: 21px;
        }

        .text {
            font-family: var(--font-family-pretendard-semibold) !important;
            font-size: var(--font-size-xs);
            color: var(--title);
            line-height: 24px;
            padding-bottom: 12px;
        }

        .date {
            padding-top: 21px;
            font-family: var(--font-family-pretendard-medium) !important;
            font-size: var(--font-size-xxs);
            line-height: 19px;
            color: var(--date);
        }

        .line {
            border-top: 1px solid var(--line);
        }

        .content {
            padding-top: 30px;
        }


        .content>.content_pre {
            width: 100%;
            font-family: var(--font-family-pretendard-medium) !important;
            font-size: var(--font-size-xxxs);
            color: var(--content);
            line-height: 25px;
            /* white-space: pre-wrap; */
        }

        .answer {
            display: flex;
            margin-bottom: 22px;
            justify-content: flex-start;
            text-align: center;
            line-height: 25px;
        }

        .answer>.answer_name {
            font-family: var(--font-family-pretendard-semibold) !important;
            font-size: var(--font-size-xxs);
            color: var(--answer-text);
            line-height: 26px;
        }

        .answer>.answer_img {
            align-items: center;
            display: flex;
            margin: 5px;
            line-height: 26px;
        }

        .answer>.answer_img>img {
            height: 4px;
            width: 5px;
        }

        .answer>.answer_wait {
            border-radius: 3px;
            background-color: var(--answer-wait-back);
            font-family: var(--font-family-pretendard-semibold) !important;
            color: var(--answer-wait);
            font-size: var(--font-size-xxxs);
            line-height: 26px;
        }

        .answer>.answer_wait>span {
            margin: 4px 10px;
            font-size: var(--font-size-xxxs);
            line-height: 26px;
        }

        .answer>.answer_comp {
            border-radius: 3px;
            background-color: var(--answer-comp);
            font-family: var(--font-family-pretendard-semibold) !important;
            color: var(--answer-text);
            font-size: var(--font-size-xxxs);
            line-height: 26px;
        }

        .answer>.answer_comp>span {
            padding: 4px 16px;
            font-size: var(--font-size-xxxs);
            line-height: 26px;
        }

        .footer {
            margin-top: 22px;
        }

        .footer>div {
            font-family: var(--font-family-pretendard-regular) !important;
            font-size: var(--font-size-xxs);
            color: var(--footer);
        }

        .del_btn {
            height: 40px;
            width: 90%;
            margin-left: 5%;
            border: solid 1px rgba(0, 0, 0, 0.1);
            border-radius: 0.2em;
            display: flex;
            justify-content: center;
            align-items: center;
            position: absolute;
            bottom: 4%;
        }

        .del_btn p {
            font-weight: bold;
            font-family: var(--font-family-pretendard-semibold) !important;
        }

        /* .del_btn:hover{
            border: solid 1px rgba(50, 108, 249, 1);
        } */
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
            $('.del_btn').click(function() {
                var idx = $("#onLineIdx").val(); // 문의 고유번호
                var title = ''; // 플러터 팝업 굵은 글씨
                var contents = ''; // 플러터 팝업 그외 텍스트
                var confirmText = '확인'; // 확인 버튼 텍스트 
                var cancelText = '취소'; //취소 버튼 텍스트
                var confirmColor = '#EE5055'; // 확인 버튼 색깔 ex) #326CF9(파랑), #EE5055(빨강) 생략시 파랑으로 기본 적용
                title = "문의 내역 삭제";
                contents = "삭제 후에는 복구할 수 없습니다.";
                cancelText = "취소";
                confirmText = "삭제하기";
                window.flutter_inappwebview.callHandler('checkPopup', title, contents, confirmText, cancelText, confirmColor).then(function(result) {
                    if (result) { // 내 노선 보기 누를시
                        $.ajax({
                            type: "POST",
                            url: "onLineProc.php",
                            data: {
                                idx: idx,
                                mode: "del"
                            },
                            dataType: "json",
                            success: function(data) {
                                if (data.result) {
                                    // 문의하기 새로고침
                                    window.flutter_inappwebview.callHandler('inqueryRefresh');
                                    // 웹뷰 닫기
                                    window.flutter_inappwebview.callHandler('close');
                                } else {
                                    title = "문의하기 삭제에 실패하였습니다.\n관리자에게 문의바랍니다.";
                                    contents = "";
                                    window.flutter_inappwebview.callHandler('popup', title, contents);
                                    return false;
                                }
                            },
                            error: function(xhr, status, error) {
                                title = "오류가 발생하였습니다.\n관리자에게 문의바랍니다.";
                                contents = "";
                                window.flutter_inappwebview.callHandler('popup', title, contents);
                                return false;
                            }
                        });
                    } else {
                        return false;
                    }
                });
            });
        });
    </script>
</head>

<body>
    <div class="du01">
        <input type="hidden" id="onLineIdx" name="onLineIdx" value="<?= $idx ?>" />
        <?
        if ($Ncounts < 1) { //없을 경우
        } else {
            while ($v = $nqStmt->fetch(PDO::FETCH_ASSOC)) {
                $title = $v['b_Title'];
                $b_Cate = $v['b_Cate']; //문의 카테고리
                $cate = (int)$b_Cate - 1;
                // $content = $v['b_Content']; //문의 내용
                $b_Content = html_Decode($v['b_Content']);
                $reg_Date = $v['reg_Date']; //등록일
                $regDate = date("y.m.d", strtotime($reg_Date));
                // $b_RContent = $v['b_RContent']; //답변내용
                $b_RContent = html_Decode($v['b_RContent']);
                $b_RDate = $v['b_RDate']; //답변등록일
                $b_State = $v['b_State']; //상태값(0:답변대기, 1:답변완료)
                if ($b_State == 0) {
                    $bClass = "answer_wait";
                    $bState = "대기중";
                } else {
                    $bClass = "answer_comp";
                    $bState = "완료";
                }
                $bRDate = date("y.m.d", strtotime($b_RDate));
                if ($b_Cate != "0") {
        ?>
                    <div class="cate_title">
                        <div class="category"><?= $data[$cate]['cateName'] ?></div>
                    </div>
                <?
                }
                ?>
                <div class="title">
                    <div class="text"><?= $b_Content ?></div>
                    <div class="date"><?= $regDate ?></div>
                </div>
                <div class="line"></div>
                <div class="content">
                    <div class="answer">
                        <div class="answer_name">답변</div>
                        <div class="answer_img"><img src="images/down_2x.png" /></div>
                        <div class="<?= $bClass ?>"><span><?= $bState ?></span></div>
                    </div>
                    <? if ($b_State == 1) { ?>
                        <div class="content_pre"><?= $b_RContent ?></div>
                        <div class="date"><?= $bRDate ?></div>
                    <? } ?>
                </div>
        <?
            }
        }
        ?>
    </div>
    <div class="del_btn">
        <p>삭제하기</p>
    </div>
</body>

</html>