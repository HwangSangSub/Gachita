<?
/*======================================================================================================================

* 프로그램			: 택시호출 조회
* 페이지 설명		: 택시호출 조회
* 파일명           : taxiSharingCall.php

========================================================================================================================*/
include "../lib/common.php";
include "../order/lib/tpay_proc.php"; // 아임포트 함수

$DB_con = db1();
// version=1&lat=35.920005879741176&lon=128.637741007290200&coordType=WGS84GEO&addressType=A02&newAddressExtend=Y
$sel_lat = trim($lat);
$sel_lng = trim($lng);
$sel_Type = trim($type);

$tmap_key = "l7xx9dc45675484b429189bdddc5f4885e5d";

$tmap_res = tmap_Api('https://apis.openapi.sk.com/tmap/geo/reversegeocoding', array("version" => "1", "lat" => $sel_lat, "lon" => $sel_lng, "coordType" => "WGS84GEO", "addressType" => "A02", "newAddressExtend" => "Y"), $tmap_key);

//print_r($tmap_res["addressInfo"]);
$city_do = $tmap_res["addressInfo"]["city_do"];
$gu_gun = $tmap_res["addressInfo"]["gu_gun"];
switch ($city_do) {
    case "강원도":
        $taxilocat = $gu_gun;
        break;
    case "충청북도":
        $taxilocat = $gu_gun;
        break;
    case "충청남도":
        $taxilocat = $gu_gun;
        break;
    case "전라북도":
        $taxilocat = $gu_gun;
        break;
    case "전라남도":
        $taxilocat = $gu_gun;
        break;
    case "경상북도":
        $taxilocat = $gu_gun;
        break;
    case "경상남도":
        $taxilocat = $gu_gun;
        break;
    case "제주특별자치도":
        $taxilocat = $gu_gun;
        break;
    default:
        $taxilocat = $city_do;
        break;
}

$chkData["result"] = true;

if($sel_Type == 'and'){
    $iosQuery = "AND taxi_And_Install <> ''";
}else{
    $iosQuery = "AND taxi_Ios_Install <> ''";
}
//추천콜 전화번호 확인하기
$callMainQuery = "SELECT idx, taxi_Name, taxi_Tel FROM TB_TAXICALL WHERE taxi_UseBit = 0 AND taxi_Type IN (2, 0) AND taxi_locat IN ('전국', :taxi_locat) {$iosQuery} ORDER BY taxi_CallCnt DESC LIMIT 1";
$callMainStmt = $DB_con->prepare($callMainQuery);
$callMainStmt->bindparam(":taxi_locat", $taxilocat);
$callMainStmt->execute();
$callMainRow = $callMainStmt->fetch(PDO::FETCH_ASSOC);
$idx = $callMainRow['idx'];                                                // 고유번호
$taxi_Name = $callMainRow['taxi_Name'];                    // 호출명
$taxi_Tel = $callMainRow['taxi_Tel'];                    // 호출전화번호

$chkData["topCallIdx"] = (int)$idx;
$chkData["topCallName"] = (string)$taxi_Name;
$chkData["topCallTel"] = (string)$taxi_Tel;

//앱리스트
$appQuery = "SELECT  idx, taxi_Name, taxi_And_Install, taxi_Ios_Install, taxi_Ios, taxi_Img FROM TB_TAXICALL WHERE taxi_UseBit = 0 AND taxi_Type IN (2, 1) AND taxi_locat IN ('전국', :taxi_locat) {$iosQuery} ORDER BY CASE WHEN taxi_locat = '전국' THEN 1 ELSE 999 END, taxi_CallCnt DESC";
$appStmt = $DB_con->prepare($appQuery);
$appStmt->bindparam(":taxi_locat", $taxilocat);
$appStmt->execute();
$appNum = $appStmt->rowCount();

$appdata  = [];
if ($appNum < 1) { //아닐경우
} else {

    $chkAppResult = "1";
    while ($appRow = $appStmt->fetch(PDO::FETCH_ASSOC)) {

        $idx = $appRow['idx'];                                                // 고유번호
        $taxi_Name = $appRow['taxi_Name'];                    // 호출명
        $taxi_And_Install = $appRow['taxi_And_Install'];                    // 택시호출 앱 안드로이드 설치 주소
        $taxi_Ios_Install = $appRow['taxi_Ios_Install'];            // 택시호출 앱 애플 설치 주소
        $taxi_Ios = $appRow['taxi_Ios'];                            // 카드 택시호출 앱 애플 스키마 주소
        $taxi_Img = $appRow['taxi_Img'];                            // 카드 택시호출 앱 이미지
        if ($taxi_Img != "") {
            $taxiImg = "https://" . $_SERVER['HTTP_HOST'] . "/data/taxicall/photo.php?id=" . $taxi_Img;
        } else {
            $taxiImg = "";
        }

        $appresult = array("appIdx" => (int)$idx, "taxi_Name" => (string)$taxi_Name, "taxi_And_Install" => (string)$taxi_And_Install, "taxi_Ios_Install" => (string)$taxi_Ios_Install, "taxi_Ios" => (string)$taxi_Ios, "taxi_Img" => (string)$taxiImg);
        array_push($appdata, $appresult);
    }
}

//콜리스트 (가치타 2.0에서는 사용안함)
// $callQuery = "SELECT  idx, taxi_Name, taxi_Tel from TB_TAXICALL WHERE taxi_UseBit = 0 AND taxi_Type IN (2, 0) AND taxi_locat = :taxi_locat ORDER BY taxi_CallCnt DESC ";
// $callStmt = $DB_con->prepare($callQuery);
// $callStmt->bindparam(":taxi_locat", $taxilocat);
// $callStmt->execute();
// $callNum = $callStmt->rowCount();

// $calldata  = [];
// if ($callNum < 1) { //아닐경우
// } else {
//     while ($callRow = $callStmt->fetch(PDO::FETCH_ASSOC)) {

//         $idx = $callRow['idx'];                                                // 고유번호
//         $taxi_Name = $callRow['taxi_Name'];                    // 호출명
//         $taxi_Tel = $callRow['taxi_Tel'];                    // 호출전화번호

//         $callresult = array("callIdx" => (int)$idx, "taxi_Name" => (string)$taxi_Name, "taxi_Tel" => (string)$taxi_Tel);
//         array_push($calldata, $callresult);
//     }
// }


$chkData['appList'] = $appdata;
// $chkData['callList'] = $calldata;

$output = str_replace('\\\/', '/', json_encode($chkData, JSON_UNESCAPED_UNICODE));
echo  urldecode($output);

dbClose($DB_con);
$stmt = null;
