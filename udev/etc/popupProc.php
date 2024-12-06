<?
include "../../udev/lib/common.php";
include "../../lib/alertLib.php";
include "../../lib/thumbnail.lib.php";   //썸네일

$DB_con = db1();

if ($mode == "mod") {

	$query = "SELECT popup_Img FROM TB_CONFIG_POPUP WHERE idx = :idx";
	$stmt = $DB_con->prepare($query);
	$stmt->bindparam(":idx", $idx);
	//$idx = trim($idx);
	$stmt->execute();
	$row = $stmt->fetch(PDO::FETCH_ASSOC);
	$popup_Img = trim($row['popup_Img']);
}

// 배너 이미지 경로
$file_dir = DU_DATA_PATH . '/popup';

//기존 파일
$org_popup_ImgFile = $file_dir . '/' . $popup_Img;

// 파일삭제
if ($del_popImg) {
	$file_img1 = $file_dir . '/' . $popup_Img;
	@unlink($file_img1);
	del_thumbnail(dirname($file_img1), basename($file_img1));
	$popImg = '';
} else {
	$popImg = "$popImg";
}


// 이미지 업로드 
$image_regex = "/(\.(gif|jpe?g|png|webp))$/i";

$cf_img_width = "1387";
$cf_img_height = "1000";

if (isset($_FILES['popImg']) && is_uploaded_file($_FILES['popImg']['tmp_name'])) {  //이미지 업로드 성공일 경우


	if (preg_match($image_regex, $_FILES['popImg']['name'])) {

		@mkdir($file_dir, 0755);
		//@chmod($file_dir, 0644);

		$filename = $_FILES['popImg']['name'];

		//php파일도 getimagesize 에서 Image Type Flag 를 속일수 있다
		if (!preg_match('/\.(gif|jpe?g|png|webp)$/i', $filename)) {
			return '';
		}

		$pattern = "/[#\&\+\-%@=\/\\:;,'\"\^`~\|\!\?\*\$#<>\(\)\[\]\{\}]/";
		$filename = preg_replace("/\s+/", "", $filename);
		$filename = preg_replace($pattern, "", $filename);

		$filename = preg_replace_callback(
			"/[가-힣]+/",
			function ($matches) {
                    return base64_encode($matches[0]);
                },
			$filename
		);

		$filename = preg_replace($pattern, "", $filename);

		// 동일한 이름의 파일이 있으면 파일명 변경
		if (is_file($dir . '/' . $filename)) {
			for ($i = 0; $i < 20; $i++) {
				$prepend = str_replace('.', '_', microtime(true)) . '_';

				if (is_file($dir . '/' . $prepend . $filename)) {
					usleep(mt_rand(100, 10000));
					continue;
				} else {
					break;
				}
			}
		}

		$fileName = $prepend . $filename;
		$dest_path = $file_dir . '/' . $fileName;

		move_uploaded_file($_FILES['popImg']['tmp_name'], $dest_path);

		if (file_exists($dest_path)) {
			$size = @getimagesize($dest_path);

			if (!($size[2] === 1 || $size[2] === 2 || $size[2] === 3 || $size[2] === 18)) { // gif jpg png 파일이 아니면 올라간 이미지를 삭제한다.
				@unlink($dest_path);
			} else if ($size[0] > $cf_img_width || $size[1] > $cf_img_height) {
				$thumb = null;
				if ($size[2] === 2 || $size[2] === 3 || $size[2] === 18) {
					//jpg 또는 png 파일 적용
					$thumb = thumbnail($fileName, $file_dir, $file_dir, $cf_img_width, $cf_img_height, true, true);

					if ($thumb) {
						@unlink($dest_path);
						rename($file_dir . '/' . $thumb, $dest_path);
					}
				}
				if (!$thumb) {
					// 아이콘의 폭 또는 높이가 설정값 보다 크다면 이미 업로드 된 아이콘 삭제
					@unlink($dest_path);
				}
			}
			//=================================================================\
		}

		$popup_Img = $fileName;
	}
}


if ($popup_Img != "") {
	$popup_Img = $popup_Img;
} else {
	$popup_Img = $popImg;
}

//새로운 팝업 이미지경로 출력
$new_Popup_Img = $file_dir . '/' . $fileName;

if ($mode == "reg") {

	$reg_Date = DU_TIME_YMDHIS;		   //등록일

	$insQuery = "INSERT INTO TB_CONFIG_POPUP (popup_Title, popup_Img, popup_Url, popup_Bit, reg_Date, end_Date) VALUES (:popup_Title, :popup_Img, :popup_Url, :popup_Bit, :reg_Date, :end_Date)";
	// exit;
	$stmt = $DB_con->prepare($insQuery);
	$stmt->bindParam(":popup_Title", $popupTitle);
	$stmt->bindParam(":popup_Img", $popup_Img);
	$stmt->bindParam(":popup_Url", $popupUrl);
	$stmt->bindParam(":popup_Bit", $popupBit);
	$stmt->bindparam(":end_Date", $endDate);
	$stmt->bindParam(":reg_Date", $reg_Date);
	$stmt->execute();
	$DB_con->lastInsertId();

	$preUrl = "popupList.php?page=$page&$qstr";
	$message = "reg";
	proc_msg($message, $preUrl);
} else if ($mode == "mod") { //수정일경우

	$upQquery = "
			UPDATE 
				TB_CONFIG_POPUP 
			SET
				popup_Title = :popup_Title, 
				popup_Url = :popup_Url, 
				popup_Bit = :popup_Bit,
                end_Date = :end_Date
			WHERE 
				idx = :idx 
			LIMIT 1";
	$upStmt = $DB_con->prepare($upQquery);
	$upStmt->bindparam(":popup_Title", $popupTitle);
	$upStmt->bindparam(":popup_Url", $popupUrl);
	$upStmt->bindparam(":popup_Bit", $popupBit);
	$upStmt->bindparam(":end_Date", $endDate);
	$upStmt->bindParam(":idx", $idx);
	$upStmt->execute();


	//파일저장방법 변경 _blob -------------------------------------------------------- 2019.02.19			
	if (file_exists($new_Popup_Img) && $fileName != "") {
		$now_time = time() + 5;

		//첨부파일 -> 썸네일 이미지로 변경 및 저장된 경로
		$filename = $new_Popup_Img;
		$handle = fopen($filename, "rb");
		$size =	GetImageSize($filename);
		$width = $size[0];
		$height = $size[1];
		$imageblob = addslashes(fread($handle, filesize($filename)));
		$filesize = filesize($filename);
		$mine = $size['mime'];
		fclose($handle);


		$upQuery = "
				UPDATE TB_CONFIG_POPUP 
				SET 
					popup_Img ='" . $now_time . "' 
				WHERE 
					idx ='" . $idx . "' 
			";
		$DB_con->exec($upQuery);


		// 파일로 blob형태 이미지 저장----------S
		// 새로 생성되는 파일명(전체경로 포함) : $m_file
		$img_txt = $now_time;
		$m_file = $file_dir . '/' . $img_txt;
		$is_file_exist = file_exists($m_file);

		if ($is_file_exist) {
			//echo 'Found it';
		} else {
			//echo 'Not found.';
			$file = fopen($m_file, "w");
			fwrite($file, $imageblob);
			fclose($file);
			chmod($m_file, 0755);
		}

		//기존 파일 삭제
		@unlink($org_popup_ImgFile);
		//신규 업로드 팝업 이미지 삭제
		@unlink($new_Popup_Img);
		// 파일로 blob형태 이미지 저장----------E

	}

	//파일저장방법 변경 _blob --------------------------------------------------------


	$preUrl = "popupList.php?page=$page&$qstr";
	$message = "mod";
	proc_msg($message, $preUrl);
} else {  //삭제일경우

	$array = explode('/', $chk);

	foreach ($array as $k => $v) {
		$chkIdx = $v;


		$filequery = "";
		$filequery = "SELECT popup_Img FROM TB_CONFIG_POPUP WHERE idx = :idx";
		$fileStmt = $DB_con->prepare($filequery);
		$fileStmt->bindparam(":idx", $chkIdx);
		$fileStmt->execute();
		$fileRow = $fileStmt->fetch(PDO::FETCH_ASSOC);
		$banDImgFile = trim($fileRow['popup_Img']);

		if ($banDImgFile != "") {
			// 배너 이미지 경로
			$file_dir = DU_DATA_PATH . '/banner';

			// 파일삭제
			$file_img1 = $file_dir . '/' . $banDImgFile;
			@unlink($file_img1);
			del_thumbnail(dirname($file_img1), basename($file_img1));
		}

		//이벤트 배너 삭제
		$delQuery = "DELETE FROM TB_CONFIG_POPUP WHERE idx = :idx";
		$delStmt = $DB_con->prepare($delQuery);
		$delStmt->bindparam(":idx", $chkIdx);
		$delStmt->execute();
	}
	echo "success";
}

dbClose($DB_con);
$stmt = null;
$upStmt = null;
$fileStmt = null;
$delStmt = null;
