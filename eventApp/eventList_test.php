<?
//이벤트 정보
include "../lib/common.php";

$DB_con = db1();

$eventQuery = "SELECT idx, ban_Type, ban_Url, ban_ImgFile from TB_BANNER WHERE b_Disply = 'Y' ORDER BY idx DESC" ;
$stmt = $DB_con->prepare($eventQuery);
$stmt->execute();
$num = $stmt->rowCount();

if($num < 1)  { //아닐경우
    $result = array("result" => "error", "errorMsg" => "잘못된 접근입니다. 해당 배너가 없습니다." );
} else {
    $banner = [];
    while($row=$stmt->fetch(PDO::FETCH_ASSOC)) {
        $banner_list = [];
        $idx = $row['idx'];														// 고유번호
        // 이미지 경로 (/data/banner)
        
        $imgUrl = "/data/banner/";
        $banImgFile = $row['ban_ImgFile'];					// 배너이미지
		//$banImgFile = "banner_ex.png";										
        $banImgFile = "http://".$_SERVER["HTTP_HOST"]."/data/banner/photo.php?id=".$banImgFile;
        
        if ($row['ban_Type'] == "1") { //내부일경우 
            $banUrl = "http://".$_SERVER["HTTP_HOST"]."/eventApp/eventView.php?idx=".$idx;               // 배너 url
        } else { //외부
            $banUrl = $row['ban_Url'];               // 배너 url
        }
        
		$banner_list = array("idx" => $idx, "banImgFile" => $banImgFile, "banUrl" => $banUrl);
		array_push($banner, $banner_list);
    }
    $result = array("result" => "success", "banner" => $banner);
    
}

dbClose($DB_con);
$stmt = null;

$output = str_replace('\/', '/', json_encode($result, JSON_UNESCAPED_UNICODE+JSON_PRETTY_PRINT));
echo $output; 



?>