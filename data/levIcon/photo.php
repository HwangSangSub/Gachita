<?
include "../../lib/dbcon.php";
//db연결
$DB_con = db1();

$id = $_GET['id'];
//$m_file = $_SERVER["DOCUMENT_ROOT"].'/data/member/'.$img_txt;
$m_file = $_SERVER["DOCUMENT_ROOT"].'/data/levIcon/'.$id;
$is_file_exist = file_exists($m_file);
if ($is_file_exist) {	
	$handle = fopen($m_file, "rb");
    $contents = fread($handle, filesize($m_file));
    fclose($handle);
	Header("Content-type:  image/png");
    print stripslashes($contents);
} else {
	echo "no file";
}

?>