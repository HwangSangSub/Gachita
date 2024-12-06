<?
include "../../lib/dbcon.php";
$id = $_GET['id'];
//db연결
$DB_con = db1();

$query= "SELECT taxi_Img FROM TB_STAXISHARING WHERE idx = :idx "; 
$stmt = $DB_con->prepare($query);
$stmt->bindparam(":idx", $id);	
$stmt->execute();
$row = $stmt->fetch(PDO::FETCH_ASSOC);

// 파일로 blob형태 이미지 출력
$taxi_Img = $row['taxi_Img'];
$m_file = $_SERVER["DOCUMENT_ROOT"].'/data/sharing/'.$id.'/'.$taxi_Img;

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
dbClose($DB_con);
$stmt = null;
?>