<?
include "../lib/common.php";
include "../../lib/alertLib.php";
include "../../lib/functionDB.php";  //공통 db함수
if ($du_udev['lv'] == "0" || $du_udev['lv'] == "1" || $du_udev['lv'] == "2") { //최고권한관리자 	
} else {
    $message = "adminChk";
    $preUrl = "/udev";
    proc_msg($message, $preUrl);
}

$begin_time = get_microtime();
?>

<!doctype html>
<html lang="ko">

<head>
    <meta charset="utf-8">
    <meta http-equiv="imagetoolbar" content="no">
    <meta http-equiv="X-UA-Compatible" content="IE=Edge">
    <title>가치타관리자</title>
    <link rel="stylesheet" href="<?= DU_UDEV_DIR ?>/common/css/admin.css">
    <link type="text/css" href="//ajax.googleapis.com/ajax/libs/jqueryui/1.9.2/themes/base/jquery-ui.css" rel="stylesheet" />
    <!--[if lte IE 8]>
<script src="<?= DU_UDEV_DIR ?>/common/js/html5.js"></script>
<![endif]-->
    <link rel="shortcut icon" type="image/png" href="<?= DU_UDEV_DIR ?>/common/img/favicon.png" />
    <script src="<?= DU_UDEV_DIR ?>/common/js/jquery-3.7.0.min.js"></script>
    <script src="<?= DU_UDEV_DIR ?>/common/js/jquery.menu.js?ver=<?= rand(); ?>"></script>
    <script src="<?= DU_UDEV_DIR ?>/common/js/common.js?ver=<?= rand(); ?>"></script>
    <script src="<?= DU_UDEV_DIR ?>/common/js/wrest.js?ver=<?= rand(); ?>"></script>
    <script src="<?= DU_UDEV_DIR ?>/common/js/placeholders.min.js"></script>
    <link rel="stylesheet" href="<?= DU_UDEV_DIR ?>/common/js/font-awesome/css/font-awesome.min.css">
    <link type="text/css" href="//ajax.googleapis.com/ajax/libs/jqueryui/1.9.2/themes/base/jquery-ui.css" rel="stylesheet" />

    <script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jqueryui/1.9.2/jquery-ui.min.js"></script>
    <script>
        jQuery(function($) {
            $.datepicker.regional["ko"] = {
                closeText: "닫기",
                prevText: "이전달",
                nextText: "다음달",
                currentText: "오늘",
                monthNames: ["1월(JAN)", "2월(FEB)", "3월(MAR)", "4월(APR)", "5월(MAY)", "6월(JUN)", "7월(JUL)", "8월(AUG)", "9월(SEP)", "10월(OCT)", "11월(NOV)", "12월(DEC)"],
                monthNamesShort: ["1월", "2월", "3월", "4월", "5월", "6월", "7월", "8월", "9월", "10월", "11월", "12월"],
                dayNames: ["일", "월", "화", "수", "목", "금", "토"],
                dayNamesShort: ["일", "월", "화", "수", "목", "금", "토"],
                dayNamesMin: ["일", "월", "화", "수", "목", "금", "토"],
                weekHeader: "Wk",
                dateFormat: "yymmdd",
                firstDay: 0,
                isRTL: false,
                showMonthAfterYear: true,
                yearSuffix: ""
            };
            $.datepicker.setDefaults($.datepicker.regional["ko"]);
        });
    </script>
</head>

<body>
    <div id="hd_login_msg"><?= $du_udev["nickNm"] ?>님 로그인 중 <a href="<?= DU_UDEV_DIR ?>/logout.php">로그아웃</a></div>
    <script>
        var tempX = 0;
        var tempY = 0;

        function imageview(id, w, h) {

            menu(id);

            var el_id = document.getElementById(id);

            //submenu = eval(name+".style");
            submenu = el_id.style;
            submenu.left = tempX - (w + 11);
            submenu.top = tempY - (h / 2);

            selectBoxVisible();

            if (el_id.style.display != 'none')
                selectBoxHidden(id);
        }
    </script>