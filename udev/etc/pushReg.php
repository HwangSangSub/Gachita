<?
include "../../udev/lib/common.php";
include "../../lib/functionDB.php";
$base_url = $PHP_SELF;
$DB_con = db1();
$noticeQuery = "SELECT b_NIdx, b_Title FROM TB_BOARD WHERE b_Idx = 1 AND b_Disply = 'Y'";
$noticeStmt = $DB_con->prepare($noticeQuery);
$noticeStmt->execute();
$noticeNum = $noticeStmt->rowCount();
if ($noticeNum < 1) { //아닐경우
    $option = "<option value=''>등록된 공지가 없습니다.</option>";
} else {
    $option = "<option value=''>이동할 공지를 선택해주세요.</option>";
    while ($noticeRow = $noticeStmt->fetch(PDO::FETCH_ASSOC)) {
        $b_NIdx = $noticeRow['b_NIdx'];                                      //공지사항 고유번호
        $b_Title = $noticeRow['b_Title'];                                    //공지사항 제목
        $option .= "<option value='" . $b_NIdx . "'>" . $b_Title . "</option>";
    }
}

?>

<!doctype html>
<html lang="ko">

<head>
    <meta charset="utf-8">
    <meta http-equiv="imagetoolbar" content="no">
    <meta http-equiv="X-UA-Compatible" content="IE=Edge">
    <title>푸시보내기</title>
    <link rel="stylesheet" href="<?= DU_UDEV_DIR ?>/common/css/admin.css">
    <link type="text/css" href="//ajax.googleapis.com/ajax/libs/jqueryui/1.9.2/themes/base/jquery-ui.css" rel="stylesheet" />
    <!--[if lte IE 8]>
		<script src="<?= DU_UDEV_DIR ?>/common/js/html5.js"></script>
		<![endif]-->
    <script src="<?= DU_UDEV_DIR ?>/common/js/jquery-1.8.3.min.js"></script>
    <script src="<?= DU_UDEV_DIR ?>/common/js/jquery.menu.js?ver=<?= rand(); ?>"></script>
    <script src="<?= DU_UDEV_DIR ?>/common/js/common.js?ver=<?= rand(); ?>"></script>
    <script src="<?= DU_UDEV_DIR ?>/common/js/wrest.js?ver=<?= rand(); ?>"></script>
    <script src="<?= DU_UDEV_DIR ?>/common/js/placeholders.min.js"></script>
    <link rel="stylesheet" href="<?= DU_UDEV_DIR ?>/common/js/font-awesome/css/font-awesome.min.css">
    <link type="text/css" href="//ajax.googleapis.com/ajax/libs/jqueryui/1.9.2/themes/base/jquery-ui.css" rel="stylesheet" />
    <script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jqueryui/1.9.2/jquery-ui.min.js"></script>
    <script type="text/javascript">
        function regPush() {
            // var queryString = $("form[name=regPush]").serialize();
            var url = $("#regPush").attr("action");
            var form = $('#regPush')[0];
            var formData = new FormData(form);
            $.ajax({
                url: url,
                type: 'POST',
                // dataType: 'text',
                data: formData,
                cache: false,
                contentType: false,
                processData: false,
                success: function(data) {
                    alert(data);
                    // opener.document.location.reload();
                    // self.close();
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    alert("에러 발생~~ \n" + textStatus + " : " + errorThrown);
                    // location.reload();
                }
            });
        }
    </script>
</head>

<body style="background-color:#fff;font-size:15px;">
    <div style="text-align:center;height:50px;font-size:30px;font-weight:bold;">
        <span>전체 알림 관리</span>
    </div>
    <div class="tbl_head01 tbl_wrap">
        <form name="regPush" id="regPush" action="/udev/etc/pushProc.php" enctype="multipart/form-data" method="POST">
            <table>
                <tbody>
                    <tr>
                        <th style="width:130px;">공지바로가기</th>
                        <td>
                            <select name="selectNotice" id="selectNotice">
                                <?= $option ?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th>내용</th>
                        <td>
                            <textarea name="sandMsg" id="sandMsg"></textarea>
                        </td>
                    </tr>
                    <tr style="border-bottom:5px solid black;">
                        <th scope="row"><label for="sendImg">이미지</label></th>
                        <td>
                            <!-- <span class="frm_info">이미지 크기는 <strong>넓이 540픽셀 높이 180픽셀</strong>로 해주세요.</span> -->
                            <input type="file" name="sendImg" id="sendImg">
                        </td>
                    </tr>
                </tbody>
            </table>
        </form>
    </div>
    <div style="text-align:center;height:50px;font-size:20px;">
        <span class="frm_info">알림을 전송하시겠습니까?</span>
        <a href="javascript:regPush();" class="btn btn_02">전송</a>
        <a href="javascript:opener.document.location.reload();self.close();" class="btn btn_03">취소</a>
    </div>
</body>

</html>
<?
dbClose($DB_con);
?>