<?
include "../../udev/lib/common.php";
include "../../lib/alertLib.php";
include "../../lib/thumbnail.lib.php";   //썸네일


$DB_con = db1();

$idx = trim($idx); 								// 등급고유번호
$mode = trim($mode); 							// 모드구분(reg : 등록, mod : 수정)
$mem_Lv =  trim($memLv);							// 등급
$memLv_Name = trim($memLvName);					// 등급명
$memLv_Nick = trim($memLvNick);					// 등급구분
$memLv_MatName = trim($memLvMatName);			// 등급달성조건명
$memIconFile = trim($memIconFile);				// 등급아이콘이미지
$memIconInfoFile = trim($memIconInfoFile);		// 등급아이콘정보이미지
$code_sel_Color = trim($codeselColor);			// 선택한 등급 색상
$memLvColor = trim($memLvColor);				// 현재 등급 색상
if($code_sel_Color == ""){
	$memLv_Color = $memLvColor;
}else{
	$memLv_Color = $code_sel_Color;
}
echo $memLv_Color;
$memMatCnt = trim($memMatCnt);					// 등급 조건
$memDc = trim($memDc);							// 등급 수수료율


if ($mode == "mod") { //수정일경우
	$memQuery = "SELECT memIconFile, memIconInfoFile FROM TB_MEMBER_LEVEL WHERE idx = :idx ";
	$stmt = $DB_con->prepare($memQuery);
	$stmt->bindparam(":idx", $idx);
	$stmt->execute();
	$num = $stmt->rowCount();

	while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
		$memOrgIconFile = $row['memIconFile'];     // 이미지 파일
		$memOrgIconInfoFile = $row['memIconInfoFile'];     // 이미지 파일
	}
}else{
	$insQuery = "INSERT INTO TB_MEMBER_LEVEL 
		SET memLv = :memLv, 
			memLv_Name = :memLv_Name, 
			memLv_Nick = :memLv_Nick, 
			memLv_MatName = :memLv_MatName, 
			memLv_Color = :memLv_Color, 
			memMatCnt = :memMatCnt, 
			memDc = :memDc ";
	$stmt = $DB_con->prepare($insQuery);
	$stmt->bindparam(":memLv", $mem_Lv);
	$stmt->bindParam(":memLv_Name", $memLv_Name);
	$stmt->bindParam(":memLv_Nick", $memLv_Nick);
	$stmt->bindParam(":memLv_MatName", $memLv_MatName);
	$stmt->bindParam(":memLv_Color", $memLv_Color);
	$stmt->bindParam(":memMatCnt", $memMatCnt);
	$stmt->bindParam(":memDc", $memDc);
	$stmt->execute();
	$idx = $DB_con->lastInsertId();
}


// 회원 등급이미지 경로
$lever_Dir = DU_DATA_PATH . '/levIcon';

//이미지가 있을 경우 이미지 삭제
if ($del_memIconFile == TRUE) {
	@unlink($lever_Dir . "/" . $memOrgIconFile);
	$mem_IconFile = "";
}

//이미지가 있을 경우 이미지 삭제
if ($del_memIconFile == TRUE) {
	@unlink($lever_Dir . "/" . $memOrgIconInfoFile);
	$mem_IconInfoFile = "";
}


// 이미지 업로드
$image_regex = "/(\.(gif|jpe?g|png|webp))$/i";

$cf_img_width = "132";
$cf_img_height = "132";

if (isset($_FILES['memIconFile']) && is_uploaded_file($_FILES['memIconFile']['tmp_name'])) {

	if (preg_match($image_regex, $_FILES['memIconFile']['name'])) {
		$filename = $_FILES['memIconFile']['name'];

		//php파일도 getimagesize 에서 Image Type Flag 를 속일수 있다
		if (!preg_match('/\.(gif|jpe?g|png|webp)$/i', $filename)) {
			return '';
		}

		$pattern = "/[#\&\+\-%@=\/\\:;,'\"\^`~\|\!\?\*\$#<>\(\)\[\]\{\}]/";
		$filename = preg_replace("/\s+/", "", $filename);
		$filename = preg_replace($pattern, "", $filename);

		$filename = preg_replace_callback("/[가-힣]+/", function ($matches) {
			return base64_encode($matches[0]);
		}, $filename);

		$filename = preg_replace($pattern, "", $filename);
		$fileName = $filename;
		$dest_path = $lever_Dir . '/' . $fileName;

		move_uploaded_file($_FILES['memIconFile']['tmp_name'], $dest_path);

		if (file_exists($dest_path)) {
			$size = @getimagesize($dest_path);

			$cf_img_width = $size[0];
			$cf_img_height = $size[1];
			if (!($size[2] === 1 || $size[2] === 2 || $size[2] === 3 || $size[2] === 18)) { // gif jpg png 파일이 아니면 올라간 이미지를 삭제한다.
				@unlink($dest_path);
			} else if ($size[0] > $cf_img_width || $size[1] > $cf_img_height) {
				$thumb = null;
				if ($size[2] === 2 || $size[2] === 3 || $size[2] === 18) {
					//jpg 또는 png 파일 적용
					$thumb = thumbnail($fileName, $lever_Dir, $lever_Dir, $cf_img_width, $cf_img_height, true, true);

					if ($thumb) {
						@unlink($dest_path);
						rename($lever_Dir . '/' . $thumb, $dest_path);
					}
				}
				if (!$thumb) {
					// 아이콘의 폭 또는 높이가 설정값 보다 크다면 이미 업로드 된 아이콘 삭제
					@unlink($dest_path);
				}
			}
			//=================================================================\
			$icon_File = $lever_Dir . '/' . $fileName;
			//파일저장방법 변경 _blob -------------------------------------------------------- 2019.02.19			
			if (file_exists($icon_File) && $fileName != "") {
				$now_time = time() + 5;

				//첨부파일 -> 썸네일 이미지로 변경 및 저장된 경로
				$filename = $icon_File;
				$handle = fopen($filename, "rb");
				$size =    GetImageSize($filename);
				$width = $size[0];
				$height = $size[1];
				$imageblob = addslashes(fread($handle, filesize($filename)));
				$filesize = filesize($filename);
				$mine = $size['mime'];
				fclose($handle);

				$insQuery = "
					UPDATE TB_MEMBER_LEVEL 
					SET 
						memIconFile = :memIconFile 
					WHERE 
						idx = :idx 
				";
				$insStmt = $DB_con->prepare($insQuery);
				$insStmt->bindparam(":memIconFile", $now_time);
				$insStmt->bindparam(":idx", $idx);
				$insStmt->execute();


				// 파일로 blob형태 이미지 저장----------Start
				// 새로 생성되는 파일명(전체경로 포함) : $taxi_File
				$img_txt = $now_time;
				$taxi_Icon_File = $lever_Dir . '/' . $img_txt;
				$is_file_exist = file_exists($taxi_Icon_File);

				if ($is_file_exist) {
				} else {
					$file = fopen($taxi_Icon_File, "w");
					fwrite($file, $imageblob);
					fclose($file);
					chmod($taxi_Icon_File, 0755);
				}

				//신규 업로드 팝업 이미지 삭제
				@unlink($icon_File);
				// 파일로 blob형태 이미지 저장----------End
			}
		}
	}
}

if (isset($_FILES['memIconInfoFile']) && is_uploaded_file($_FILES['memIconInfoFile']['tmp_name'])) {

	if (preg_match($image_regex, $_FILES['memIconInfoFile']['name'])) {
		$filename_Info = $_FILES['memIconInfoFile']['name'];

		//php파일도 getimagesize 에서 Image Type Flag 를 속일수 있다
		if (!preg_match('/\.(gif|jpe?g|png|webp)$/i', $filename_Info)) {
			return '';
		}

		$pattern = "/[#\&\+\-%@=\/\\:;,'\"\^`~\|\!\?\*\$#<>\(\)\[\]\{\}]/";
		$filename_Info = preg_replace("/\s+/", "", $filename_Info);
		$filename_Info = preg_replace($pattern, "", $filename_Info);

		$filename_Info = preg_replace_callback("/[가-힣]+/", function ($matches) {
			return base64_encode($matches[0]);
		}, $filename_Info);

		$filename_Info = preg_replace($pattern, "", $filename_Info);
		$filename_Info = $filename_Info;
		$dest_path = $lever_Dir . '/' . $filename_Info;

		move_uploaded_file($_FILES['memIconInfoFile']['tmp_name'], $dest_path);

		if (file_exists($dest_path)) {
			$size = @getimagesize($dest_path);

			$cf_img_width = $size[0];
			$cf_img_height = $size[1];
			if (!($size[2] === 1 || $size[2] === 2 || $size[2] === 3 || $size[2] === 18)) { // gif jpg png 파일이 아니면 올라간 이미지를 삭제한다.
				@unlink($dest_path);
			} else if ($size[0] > $cf_img_width || $size[1] > $cf_img_height) {
				$thumb = null;
				if ($size[2] === 2 || $size[2] === 3 || $size[2] === 18) {
					//jpg 또는 png 파일 적용
					$thumb = thumbnail($filename_Info, $lever_Dir, $lever_Dir, $cf_img_width, $cf_img_height, true, true);

					if ($thumb) {
						@unlink($dest_path);
						rename($lever_Dir . '/' . $thumb, $dest_path);
					}
				}
				if (!$thumb) {
					// 아이콘의 폭 또는 높이가 설정값 보다 크다면 이미 업로드 된 아이콘 삭제
					@unlink($dest_path);
				}
			}
			//=================================================================\
			$icon_Info_File = $lever_Dir . '/' . $filename_Info;
			//파일저장방법 변경 _blob -------------------------------------------------------- 2019.02.19			
			if (file_exists($icon_Info_File) && $filename_Info != "") {
				$now_time2 = time() + 10;

				//첨부파일 -> 썸네일 이미지로 변경 및 저장된 경로
				$filename_Info = $icon_Info_File;
				$handle = fopen($filename_Info, "rb");
				$size =    GetImageSize($filename_Info);
				$width = $size[0];
				$height = $size[1];
				$imageblob = addslashes(fread($handle, filesize($filename_Info)));
				$filesize = filesize($filename_Info);
				$mine = $size['mime'];
				fclose($handle);

				$insQuery2 = "
					UPDATE TB_MEMBER_LEVEL 
					SET 
						memIconInfoFile = :memIconInfoFile 
					WHERE 
						idx = :idx 
				";
				$insStmt2 = $DB_con->prepare($insQuery2);
				$insStmt2->bindparam(":memIconInfoFile", $now_time2);
				$insStmt2->bindparam(":idx", $idx);
				$insStmt2->execute();


				// 파일로 blob형태 이미지 저장----------Start
				// 새로 생성되는 파일명(전체경로 포함) : $taxi_File
				$img_txt2 = $now_time2;
				$taxi_icon_Info_File = $lever_Dir . '/' . $img_txt2;
				$is_file_exist = file_exists($taxi_icon_Info_File);

				if ($is_file_exist) {
				} else {
					$file = fopen($taxi_icon_Info_File, "w");
					fwrite($file, $imageblob);
					fclose($file);
					chmod($taxi_icon_Info_File, 0755);
				}

				//신규 업로드 팝업 이미지 삭제
				@unlink($icon_Info_File);
				// 파일로 blob형태 이미지 저장----------End
			}
		}
	}
}

if ($mode == "reg") {  //등록일 경우
	if($idx > 0){
		$preUrl = "memManagerList.php?page=$page&$qstr";
		$message = "reg";
		proc_msg($message, $preUrl);
	}

} else if ($mode == "mod") { //수정일경우

	$upQquery = "UPDATE TB_MEMBER_LEVEL 
		SET memLv = :memLv, 
			memLv_Name = :memLv_Name, 
			memLv_Nick = :memLv_Nick, 
			memLv_MatName = :memLv_MatName, 
			memLv_Color = :memLv_Color, 
			memMatCnt = :memMatCnt, 
			memDc = :memDc 
		WHERE idx =  :idx 
		LIMIT 1";
	$upStmt = $DB_con->prepare($upQquery);
	$upStmt->bindparam(":memLv", $mem_Lv);
	$upStmt->bindParam(":memLv_Name", $memLv_Name);
	$upStmt->bindParam(":memLv_Nick", $memLv_Nick);
	$upStmt->bindParam(":memLv_MatName", $memLv_MatName);
	$upStmt->bindParam(":memLv_Color", $memLv_Color);
	$upStmt->bindParam(":memMatCnt", $memMatCnt);
	$upStmt->bindParam(":memDc", $memDc);
	$upStmt->bindParam(":idx", $idx);
	$upStmt->execute();

	$preUrl = "memManagerList.php?page=$page&$qstr";
	$message = "mod";
	proc_msg($message, $preUrl);
} else {  //삭제일경우

	$check = trim($chk);
	$array = explode('/', $check);

	foreach ($array as $k => $v) {
		$idx = $v;
		$delQquery = "DELETE FROM TB_MEMBER_LEVEL WHERE idx =  :idx LIMIT 1";

		$delStmt = $DB_con->prepare($delQquery);
		$delStmt->bindParam(":idx", $idx);
		$delStmt->execute();
	}

	echo "success";
}

dbClose($DB_con);
$stmt = null;
$upStmt = null;
$delStmt = null;
