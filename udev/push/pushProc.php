<?

include "../../udev/lib/common.php";
include "../../lib/alertLib.php";
include "../../lib/thumbnail.lib.php";   //썸네일


$DB_con = db1();

$pushImgUrl = $_SERVER["DOCUMENT_ROOT"] . "/data/push"; // 이미지 경로(삭제시 필요)
// push 이미지 경로
$push_dir = DU_DATA_PATH . '/push';


// 푸시 이미지 업로드
$image_regex = "/(\.(gif|jpe?g|png|webp))$/i";

if (isset($_FILES['att_file']) && is_uploaded_file($_FILES['att_file']['tmp_name'])) {  //이미지 업로드 성공일 경우

	@unlink($push_dir . '/' . $mb_id . '.gif');

	if (preg_match($image_regex, $_FILES['att_file']['name'])) {

		@mkdir($push_dir, 0755);
		//@chmod($push_dir, 0644);
		//파일명
		$att_file = $mb_id . '.gif';

		$dest_path = $push_dir . '/' . $att_file;

		//echo $_FILES['att_file']['tmp_name']."<BR>";			
		//echo $dest_path."<BR>";
		move_uploaded_file($_FILES['att_file']['tmp_name'], $dest_path);

		if (file_exists($dest_path)) {
			$size = @getimagesize($dest_path);

			if (!($size[2] === 1 || $size[2] === 2 || $size[2] === 3 || $size[2] === 18)) { // gif jpg png 파일이 아니면 올라간 이미지를 삭제한다.
				@unlink($dest_path);
			} else if ($size[0] > $cf_img_width || $size[1] > $cf_img_height) {
				$thumb = null;
				if ($size[2] === 2 || $size[2] === 3 || $size[2] === 18) {
					//jpg 또는 png 파일 적용
					$thumb = thumbnail($att_file, $push_dir, $push_dir, $cf_img_width, $cf_img_height, true, true);

					if ($thumb) {
						@unlink($dest_path);
						rename($push_dir . '/' . $thumb, $dest_path);
					}
				}
				if (!$thumb) {
					// 아이콘의 폭 또는 높이가 설정값 보다 크다면 이미 업로드 된 아이콘 삭제
					@unlink($dest_path);
				}
			}
			//=================================================================\
		}

		$mem_ImgFile = $att_file;
	}
}


if ($mode == "reg") {		// 추가일 경우

	$DATA["push_type"]					= $push_type;
	$DATA["alarm_type"]				= $alarm_type;
	$DATA["push_shortcut"]			= $push_shortcut;
	$DATA["link_url"]						= $link_url;
	$DATA["call_book_seq"]			= $call_book_seq;
	$DATA["os_version_min"]			= "ALL";
	$DATA["prev_conn"]					= $prev_conn;
	$DATA["contents"]					= addslashes($contents);
	$DATA["status"]						= "READY";
	$DATA["log"]								= "";
	$DATA["test"]							= $test;
	if ($setDelay == "Y") {
		$DATA["delay"]						= "Y";
		$DATA["delay_send_time"]		= $send_date . " " . $send_hour . ":" . $send_minute . ":00";
	} else {
		$DATA["delay"]						= "N";
	}

	//push 발송 정보 입력
	$insQuery = " insert into TB_PUSH set ";
	$i = 0;
	foreach ($DATA as $key => $val) {
		if ($i > 0) $insQuery .= " , ";
		$insQuery .= $key . " = '" . $val . " ' ";

		$i++;
	}
	$DB_con->exec($insQuery);

	$preUrl = "pushList.php?page=$page&$qstr";
	$message = "reg";
	proc_msg($message, $preUrl);
} else if ($mode == "del") {  //삭제일경우


	//push 발송 정보 입력
	$insQuery = " delete from TB_PUSH where seq='" . $idx . "' ";
	$DB_con->exec($insQuery);

	$preUrl = "pushList.php?page=$page&$qstr";
	$message = "del";
	proc_msg($message, $preUrl);
}


dbClose($DB_con);
$stmt = null;
$upStmt = null;
$upStmt2 = null;
$chkStmt = null;
$cntStmt = null;
$upStmt = null;
