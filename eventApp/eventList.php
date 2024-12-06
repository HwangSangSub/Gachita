<?
//이벤트 정보
include "../lib/common.php";

$DB_con = db1();

$eventQuery = "SELECT idx, ban_Type, ban_Url, ban_ImgFile FROM TB_BANNER WHERE b_Disply = 'Y' ORDER BY idx DESC";
$stmt = $DB_con->prepare($eventQuery);
$stmt->execute();
$num = $stmt->rowCount();

if ($num < 1) { //아닐경우
    $result = array("result" => false, "errorMsg" => "잘못된 접근입니다. 해당 배너가 없습니다.");
} else {
    $data = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {

        $idx = $row['idx'];                                                        // 고유번호
        // 이미지 경로 (/data/banner)

        $imgUrl = "/data/banner/photo.php?id=";
        $banImgFile = $row['ban_ImgFile'];                    // 배너이미지
        //$banImgFile = "banner_ex.png";										
        $banImgFile = $imgUrl . $banImgFile;
        if ($row['ban_Type'] == "1") { //내부일경우 
            $banUrl = APP_URL . "/eventApp/eventView.php?idx=" . $idx;               // 배너 url
        } else { //외부
            $banUrl = $row['ban_Url'];               // 배너 url
        }

        $mresult = ["idx" => (int)$idx, "banImgFile" => (string)$banImgFile, "banUrl" => (string)$banUrl];

        array_push($data, $mresult);
    }
    $result = array("result" => true, "lists" => $data);
}

dbClose($DB_con);
$stmt = null;

$output = str_replace('\/', '/', json_encode($result, JSON_UNESCAPED_UNICODE + JSON_PRETTY_PRINT));
echo $output;
