<?
/*======================================================================================================================

* 프로그램		:  전체회원(탈퇴제외)메세지 보내기
* 페이지 설명	:  전체회원(탈퇴제외)메세지 보내기

========================================================================================================================*/

header('Expires: 0'); // rfc2616 - Section 14.21
header('Cache-Control: no-store, no-cache, must-revalidate'); // HTTP/1.1
header('Cache-Control: pre-check=0, post-check=0, max-age=0'); // HTTP/1.1
header('Pragma: no-cache'); // HTTP/1.0

include "../../udev/lib/common.php";
include "../../lib/functionDB.php";  //공통 db함수
include "../../lib/thumbnail.lib.php";   //썸네일
// include "../../lib/twofactor.php";    //구글 two_factor 인증 방식
$DB_con = db1();
$notice = trim($selectNotice);    //공지사항 번호
// print_r($_REQUEST);
// print_r($_FILES['sendImg']);
// 푸시메세지 변경
// echo $MDmsg;

//회원 고유 아이디
$mDSidQuery = "SELECT m.idx, m.mem_Os, m.mem_MPush, m.mem_Token from TB_MEMBERS m INNER JOIN TB_MEMBERS_ETC me ON m.idx = me.mem_Idx WHERE m.b_Disply = 'N' AND m.mem_Id <> 'NULL' AND m.mem_Token IS NOT NULL6";
//11290 황상섭 idx
//10773 권대리 개인폰 idx
//10780 권대리 컴퓨터 idx
// AND m.mem_Id = '01055499171'
// echo $mDSidQuery;
$mDSidStmt = $DB_con->prepare($mDSidQuery);
$mDSidStmt->execute();
$mDSidNum = $mDSidStmt->rowCount();
// echo "AFadsfadsfads";
// echo $mDSidNum;
if ($mDSidNum < 1) { //아닐경우
    echo "푸시발송에 실패했습니다.";
} else {
    // 배너 이미지 경로
    $file_dir = DU_DATA_PATH . '/push';


    // 이미지 업로드 
    $image_regex = "/(\.(gif|jpe?g|png|webp))$/i";

    // $cf_img_width = "540";
    // $cf_img_height = "180";

    if (isset($_FILES['sendImg']) && is_uploaded_file($_FILES['sendImg']['tmp_name'])) {  //이미지 업로드 성공일 경우


        if (preg_match($image_regex, $_FILES['sendImg']['name'])) {

            // @mkdir($file_dir, 0755);
            //@chmod($file_dir, 0644);

            $filename = $_FILES['sendImg']['name'];

            //php파일도 getimagesize 에서 Image Type Flag 를 속일수 있다
            if (!preg_match('/\.(gif|jpe?g|png|webp)$/i', $filename)) {
                return '';
            }

            $pattern = "/[#\&\+\-%@=\/\\:;,'\"\^`~\|\!\?\*\$#<>\(\)\[\]\{\}]/";
            $filename = preg_replace("/\s+/", "", $filename);
            $filename = preg_replace($pattern, "", $filename);

            $filename = preg_replace_callback(
                "/[가-힣]+/",
                function ($matches) {
                    return base64_encode($matches[0]);
                },
                $filename
            );

            $filename = preg_replace($pattern, "", $filename);

            // 동일한 이름의 파일이 있으면 파일명 변경
            if (is_file($dir . '/' . $filename)) {
                for ($i = 0; $i < 20; $i++) {
                    $prepend = str_replace('.', '_', microtime(true)) . '_';

                    if (is_file($dir . '/' . $prepend . $filename)) {
                        usleep(mt_rand(100, 10000));
                        continue;
                    } else {
                        break;
                    }
                }
            }

            $fileName = $prepend . $filename;
            $dest_path = $file_dir . '/' . $fileName;

            move_uploaded_file($_FILES['sendImg']['tmp_name'], $dest_path);
            $sendImgFile = $fileName;
        }
    }


    if ($sendImgFile != "") {
        $sendImgFile = $sendImgFile;
    } else {
        $sendImgFile = $sendImg;
    }
    $pushImg = "https://" . $_SERVER['HTTP_HOST'] . "/data/push/" . $sendImgFile;

    while ($mDSidRow = $mDSidStmt->fetch(PDO::FETCH_ASSOC)) {
        $mem_Idx = $mDSidRow['idx'];                                    //회원고유번호
        $memDOs = $mDSidRow['mem_Os'];                            //os구분  (0 : 안드로이드, 1: 아이폰)
        $memDMPush = $mDSidRow['mem_MPush'];                //푸시발송여부  (0 : 발송, 1: 발송불가)
        $mem_MDToken = $mDSidRow['mem_Token'];                //토큰값

        $mDtitle = "";
        $mDmsg = $sandMsg;

        //푸시 사용 내역 (2: 새로고침, 9 :로그아웃)
        $insPsQuery = "INSERT INTO TB_PUSH_HISTORY (mem_Idx, :push_Title, push_Msg, push_Img, push_Type, push_NoticeIdx, reg_Date) VALUES (:mem_Idx, :push_Title, :push_Msg, :push_Img, '9', :push_NoticeIdx, NOW())";
        $insPsStmt = $DB_con->prepare($insPsQuery);
        $insPsStmt->bindparam(":mem_Idx", $mem_Idx);
        $insPsStmt->bindparam(":push_Title", $push_Title);
        $insPsStmt->bindparam(":push_Msg", $sandMsg);
        $insPsStmt->bindparam(":push_Img", $pushImg);
        $insPsStmt->bindparam(":push_NoticeIdx", $notice);
        $insPsStmt->execute();
        $mState = "0";  //2,9

        $mDtokens = $mem_MDToken;

        //알림할 내용들을 취합해서 $data에 모두 담는다. 프로젝트 의도에 따라 다른게 더 있을 수 있다.
        $mDinputData = array("title" => $mDtitle, "body" => $mDmsg, "state" => $mState, "type" => "notice", "id" => $notice, "imageUrl" => $pushImg);
        // print_r($mDinputData);
        $rResult = send_Push($mDtokens, $mDinputData);
        //echo $mDpresult;
    }
    echo "푸시를 보냈습니다.";
} // 푸시끝

dbClose($DB_con);
$mDSidStmt = null;
$stmt = null;
