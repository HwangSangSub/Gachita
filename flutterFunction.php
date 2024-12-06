<?
/*======================================================================================================================

* 프로그램			: 플러터 스크립트 호출
* 페이지 설명		: 플러터 스크립트 호출
* 파일명           : flutterFunction.php

========================================================================================================================*/
include "./udev/lib/common.php";
include "./lib/functionDB.php";  //공통 db함수

$type = trim($type);        // 호출 타입(app: 앱 내 특정 페이지 열기, web: 외부링크로 웹페이지 열기, popup: 팝업창 열기)
$url = trim($url);          // 호출할 페이지 주소 (타입이 app 인 경우 앱내 페이지 경로, web인 경우는 외부링크로 웹페이지 열기)
switch ($type) {
    case "app":
        //호출 타입이 앱 내 특정페이지 열기를 할 경우
        echo "<script>window.flutter_inappwebview.callHandler('push', true, '" . $url . "');</script>";
        break;
    case "web":
        // 외부 링크로 웹페이지 열기를 할 경우
        echo "<script>window.flutter_inappwebview.callHandler('link', " . $url . ");</script>";
        break;
    case "popup":
        //팝업을 호출 할 경우
        break;
    default:
        break;
}
