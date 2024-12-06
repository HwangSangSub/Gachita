<?php
if(!(version_compare(phpversion(), '5.3.0', '>=') && defined('DU_BROWSCAP_USE') && DU_BROWSCAP_USE))
    return;

// Browscap 포인트 파일이 있으면 실행
if(defined('DU_VISIT_BROWSCAP_USE') && DU_VISIT_BROWSCAP_USE && is_file(DU_DATA_PATH.'/cache/browscap_cache.php')) {

	include DU_COM."/browscap/Browscap.php";

    $browscap = new phpbrowscap\Browscap(DU_DATA_PATH.'/cache');
    $browscap->doAutoUpdate = false;
    $browscap->cacheFilename = 'browscap_cache.php';

    $info = $browscap->getBrowser($_SERVER['HTTP_USER_AGENT']);

    $vi_browser = $info->Comment;
    $vi_os = $info->Platform;
    $vi_device = $info->Device_Type;
}
?>