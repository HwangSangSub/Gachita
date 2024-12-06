<?
/*
    디바이스 확인 후 각 스토어 페이지로 이동하기. 
    앱 평가 미션용 페이지
*/
if (stristr($_SERVER['HTTP_USER_AGENT'], 'ipad')) {
    $device = "ipad";
?>
    <meta http-equiv="refresh" content="0;URL='https://apps.apple.com/it/app/%EA%B0%80%EC%B9%98%ED%83%80/id1666745532'">
<?
} else if (
    stristr($_SERVER['HTTP_USER_AGENT'], 'iphone') ||
    strstr($_SERVER['HTTP_USER_AGENT'], 'iphone')
) {
    $device = "iphone";
?>
    <meta http-equiv="refresh" content="0;URL='https://apps.apple.com/it/app/%EA%B0%80%EC%B9%98%ED%83%80/id1666745532'">
<?
} else if (stristr($_SERVER['HTTP_USER_AGENT'], 'blackberry')) {
    $device = "blackberry";
?>
    <meta http-equiv="refresh" content="0;URL='https://play.google.com/store/apps/details?id=kr.gachita.gachita'">
<?
} else if (stristr($_SERVER['HTTP_USER_AGENT'], 'android')) {
    $device = "android";
?>
    <meta http-equiv="refresh" content="0;URL='https://play.google.com/store/apps/details?id=kr.gachita.gachita'">
<?
} else {
    $device = "etc";
?>
    <meta http-equiv="refresh" content="0;URL='https://gachita.kr/'">
<?
}
?>