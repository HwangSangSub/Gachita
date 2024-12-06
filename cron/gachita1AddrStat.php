#!/usr/bin/php -q
<?php
/*======================================================================================================================

* 프로그램			: 사무실 메인 입구 통계현황정리(주소) (매일 00:00분 처리)
* 페이지 설명		: 사무실 메인 입구 통계현황정리(주소)
* 파일명            : gachitaTotalStat.php

========================================================================================================================*/

// register_globals off 처리
if (isset($_GET)) {
    @extract($_GET);
}
if (isset($_POST)) {
    @extract($_POST);
}
if (isset($_SERVER)) {
    @extract($_SERVER);
}
if (isset($_ENV)) {
    @extract($_ENV);
}
if (isset($_SESSION)) {
    @extract($_SESSION);
}
if (isset($_COOKIE)) {
    @extract($_COOKIE);
}
if (isset($_REQUEST)) {
    @extract($_REQUEST);
}
if (isset($_FILES)) {
    @extract($_FILES);
}

ob_start();

header('Content-Type: text/html; charset=utf-8');
$gmnow = gmdate('D, d M Y H:i:s') . ' GMT';
header('Expires: 0'); // rfc2616 - Section 14.21
header('Last-Modified: ' . $gmnow);
header('Cache-Control: no-store, no-cache, must-revalidate'); // HTTP/1.1
header('Cache-Control: pre-check=0, post-check=0, max-age=0'); // HTTP/1.1
header('Pragma: no-cache'); // HTTP/1.0

include 'inc/dbcon.php';

function tmap_Api($url, $param = array(), $access_token_value)
{
    $url = $url . '?' . http_build_query($param, '', '&');
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($param));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HEADER, false); //헤더 정보를 보내도록 함(*필수)
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('appKey:' . $access_token_value));
    //curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    $contents = curl_exec($ch);
    $contents_json = json_decode($contents, true); // 결과값을 파싱
    curl_close($ch);
    return $contents_json;
}

$gachita2 = db1();  // 가치타 2.0
$gachita1 = db2();  // 가치타 1.0

$tmap_key = "l7xx9dc45675484b429189bdddc5f4885e5d";

$memberChkQuery = "SELECT mem_SId FROM TB_MEMBERS WHERE b_Disply = 'N' AND mem_Id IS NOT NULL AND mem_Id <> 'NULL' AND mem_CertBit = '1' AND mem_Tel NOT IN ('01071291105', '01075320156', '01049421907', '01088434516', '01051327245', '01068475900', '01089040532', '01055499171', '01067778383', '01024279233', '01055970410', '01090957526')";
$memberChkStmt = $gachita1->prepare($memberChkQuery);
$memberChkStmt->execute();
$memberChkNum = $memberChkStmt->rowCount();
if ($memberChkNum > 0) {
    while ($memberChkRow = $memberChkStmt->fetch(PDO::FETCH_ASSOC)) {
        $mem_SId = $memberChkRow['mem_SId'];
        $addrChkQuery = "SELECT mem_Haddr, mem_HLat, mem_HLng FROM TB_MEMBERS_MAP WHERE mem_SId = :mem_SId AND mem_Haddr <> '' AND mem_Haddr IS NOT NULL AND mem_HLat IS NOT NULL AND mem_HLng IS NOT NULL";
        $addrChkStmt = $gachita1->prepare($addrChkQuery);
        $addrChkStmt->bindparam(":mem_SId", $mem_SId);
        $addrChkStmt->execute();
        $addrChkNum = $addrChkStmt->rowCount();
        if ($addrChkNum > 0) {
            while ($addrChkRow = $addrChkStmt->fetch(PDO::FETCH_ASSOC)) {
                $mem_Addr = $addrChkRow['mem_Haddr'];                // 주소
                $mem_Lat = $addrChkRow['mem_HLat'];                  // 주소 위도
                $mem_Lng = $addrChkRow['mem_HLng'];                  // 주소 경도

                $tmap_res = tmap_Api('https://apis.openapi.sk.com/tmap/geo/reversegeocoding', array("version" => "1", "lat" => $mem_Lat, "lon" => $mem_Lng, "coordType" => "WGS84GEO", "addressType" => "A02", "newAddressExtend" => "Y"), $tmap_key);

                $city_do = $tmap_res["addressInfo"]["city_do"];
                $gu_gun = $tmap_res["addressInfo"]["gu_gun"];
                $do = "";
                $si = "";
                switch ($city_do) {
                    case "서울특별시":
                        $do = "경기도";
                        $si = $city_do;
                        break;
                    case "인천광역시":
                        $do = "경기도";
                        $si = $city_do;
                        break;
                    case "대전광역시":
                        $do = "충청남도";
                        $si = $city_do;
                        break;
                    case "세종특별자치시":
                        $do = "충청남도";
                        $si = $city_do;
                        break;
                    case "광주광역시":
                        $do = "전라남도";
                        $si = $city_do;
                        break;
                    case "대구광역시":
                        $do = "경상북도";
                        $si = $city_do;
                        break;
                    case "울산광역시":
                        $do = "경상북도";
                        $si = $city_do;
                        break;
                    case "부산광역시":
                        $do = "경상남도";
                        $si = $city_do;
                        break;
                    default:
                        $do = $city_do;
                        $si = $gu_gun;
                        break;
                }
                // 통계 시도 구분 값 확인
                $dosiChkQuery = "SELECT addr_Cnt FROM TB_ADDR_STAT WHERE do = :do AND si = :si";
                $dosiChkStmt = $gachita2->prepare($dosiChkQuery);
                $dosiChkStmt->bindparam(":do", $do);
                $dosiChkStmt->bindparam(":si", $si);
                $dosiChkStmt->execute();
                $dosiChkCnt = $dosiChkStmt->rowCount();

                if ($dosiChkCnt > 0) {
                    $dosiChkRow = $dosiChkStmt->fetch(PDO::FETCH_ASSOC);
                    $addr_Cnt = $dosiChkRow['addr_Cnt'];                        // 주소 통계
                    $statCnt = (int)$addr_Cnt + 1;

                    // 지역 수 증가
                    $dosiUpQuery = "UPDATE TB_ADDR_STAT SET addr_Cnt = :addr_Cnt, stat_Date = NOW() WHERE do = :do AND si = :si LIMIT 1";
                    $dosiUpStmt = $gachita2->prepare($dosiUpQuery);
                    $dosiUpStmt->bindparam(":addr_Cnt", $statCnt);
                    $dosiUpStmt->bindparam(":do", $do);
                    $dosiUpStmt->bindparam(":si", $si);
                    $dosiUpStmt->execute();
                } else {
                    // 지역 최초 추가
                    $dosiInsQuery = "INSERT INTO TB_ADDR_STAT SET do = :do, si = :si, addr_Cnt = 1, reg_Date = NOW(), stat_Date = NOW()";
                    $dosiInsStmt = $gachita2->prepare($dosiInsQuery);
                    $dosiInsStmt->bindparam(":do", $do);
                    $dosiInsStmt->bindparam(":si", $si);
                    $dosiInsStmt->execute();
                }
            }
        }
    }
}
dbClose($gachita1);
dbClose($gachita2);
$memberChkStmt = null;
$addrChkStmt = null;
$dosiChkStmt = null;
$dosiUpStmt = null;
$dosiInsStmt = null;
?>