<?
include "../../udev/lib/common.php";
include "../../lib/alertLib.php";

$del_bmImg1 = trim($del_bmImg1);			// 즐겨찾는 상단 배너 1번 이미지 (삭제 여부)
$org_bookmarkImg1 = trim($bookmarkImg1);	// 즐겨찾는 상단 배너 1번 이미지 (기존 이미지)

$del_bmImg2 = trim($del_bmImg2);			// 즐겨찾는 상단 배너 2번 이미지 (삭제 여부)
$org_bookmarkImg2 = trim($bookmarkImg2);	// 즐겨찾는 상단 배너 2번 이미지 (기존 이미지)

$del_bmImg3 = trim($del_bmImg3);			// 즐겨찾는 상단 배너 3번 이미지 (삭제 여부)
$org_bookmarkImg3 = trim($bookmarkImg3);	// 즐겨찾는 상단 배너 3번 이미지 (기존 이미지)

$con_ImgUp =  trim($conImgUp);				// 이미지 업로드 확장자
$con_TxtFilter = trim($conTxtFilter);		// 단어 필터링
$con_Agree = trim($conAgree);				// 회원가입약관
$con_Privacy = trim($conPrivacy);			// 개인정보취급방침

$type = trim($type);						// 삭제시 공지사항/이벤트 확인
$noticeIdx = trim($noticeIdx);				// 상단고정 해제할 공지사항번호
$eventIdx = trim($eventIdx);				// 상단고정 해제할 이벤트번호

// 공지사항 상단 고정
$conTopNotice1 = trim($conTopNotice1);		// 1번 상단 고정 공지사항
$conTopNotice2 = trim($conTopNotice2);		// 2번 상단 고정 공지사항
$conTopNotice3 = trim($conTopNotice3);		// 3번 상단 고정 공지사항
$conTopNoticeArray = array($conTopNotice1, $conTopNotice2, $conTopNotice3);

// 이벤트 상단 고정
$conTopEvent1 = trim($conTopEvent1);		// 1번 상단 고정 이벤트
$conTopEvent2 = trim($conTopEvent2);		// 2번 상단 고정 이벤트
$conTopEvent3 = trim($conTopEvent3);		// 3번 상단 고정 이벤트
$conTopEventArray = array($conTopEvent1, $conTopEvent2, $conTopEvent3);

// 배너 이미지 경로
$file_dir = DU_DATA_PATH . '/bookmark';
@mkdir($file_dir, 0755);
$pattern = "/[#\&\+\-%@=\/\\:;,'\"\^`~\|\!\?\*\$#<>\(\)\[\]\{\}]/";
// 이미지 업로드 
$image_regex = "/(\.(gif|jpe?g|png|webp))$/i";

$cf_img_width = "1000";
$cf_img_height = "790";

$DB_con = db1();

if ($mode == "reg") {

	$insQuery = "INSERT INTO TB_CONFIG_ETC (con_ImgUp, con_TxtFilter, con_Agree, con_Privacy ) VALUES (:con_ImgUp, :con_TxtFilter, :con_Agree, :con_Privacy)";
	$stmt = $DB_con->prepare($insQuery);
	$stmt->bindParam("con_ImgUp", $con_ImgUp);
	$stmt->bindParam("con_TxtFilter", $con_TxtFilter);
	$stmt->bindParam("con_Agree", $con_Agree);
	$stmt->bindParam("con_Privacy", $con_Privacy);
	$stmt->execute();
	$DB_con->lastInsertId();

	$preUrl = "configEtcReg.php";
	$message = "reg";
	proc_msg($message, $preUrl);
} else if ($mode == "mod") { //수정일경우

	// 즐겨찾는 상단 배너 1번 이미지 시작 ------------------------------------------------------------------
	if ($org_bookmarkImg1 == '') {
		$org_bmImg1 = '';
	} else {
		$org_bmImg1 = $file_dir . '/' . $org_bookmarkImg1;	// 기존 이미지 위치
	}

	// 파일삭제여부 확인 하여 삭제 처리
	if ($del_bmImg1) {
		if ($org_bookmarkImg1 != '') {
			$file_Img1 = $org_bmImg1;
			@unlink($file_Img1);
			del_thumbnail(dirname($file_Img1), basename($file_Img1));
			$bm_Img1 = '';
		}
	} else {
		if ($org_bookmarkImg1 == '') {
			$bm_Img1 = '';
		} else {
			$bm_Img1 = $org_bmImg1;
		}
	}

	if (isset($_FILES['bmImg1']) && is_uploaded_file($_FILES['bmImg1']['tmp_name'])) {  //이미지 업로드 성공일 경우


		if (preg_match($image_regex, $_FILES['bmImg1']['name'])) {

			$filename1 = $_FILES['bmImg1']['name'];

			//php파일도 getimagesize 에서 Image Type Flag 를 속일수 있다
			if (!preg_match('/\.(gif|jpe?g|png|webp)$/i', $filename1)) {
				return '';
			}

			$filename1 = preg_replace("/\s+/", "", $filename1);
			$filename1 = preg_replace($pattern, "", $filename1);

			$filename1 = preg_replace_callback(
				"/[가-힣]+/",
				function ($matches) {
					return base64_encode($matches[0]);
				},
				$filename1
			);


			$filename1 = preg_replace($pattern, "", $filename1);

			// 동일한 이름의 파일이 있으면 파일명 변경
			if (is_file($file_dir . '/' . $filename1)) {
				for ($i = 0; $i < 20; $i++) {
					$prepend = str_replace('.', '_', microtime(true)) . '_';

					if (is_file($file_dir . '/' . $prepend . $filename1)) {
						usleep(mt_rand(100, 10000));
						continue;
					} else {
						break;
					}
				}
			}

			$fileName1 = $prepend . $filename1;
			$dest_path1 = $file_dir . '/' . $fileName1;

			move_uploaded_file($_FILES['bmImg1']['tmp_name'], $dest_path1);

			if (file_exists($dest_path1)) {
				$size = @getimagesize($dest_path1);

				if (!($size[2] === 1 || $size[2] === 2 || $size[2] === 3)) { // gif jpg png 파일이 아니면 올라간 이미지를 삭제한다.
					@unlink($dest_path1);
				} else if ($size[0] > $cf_img_width || $size[1] > $cf_img_height) {
					$thumb = null;
					if ($size[2] === 2 || $size[2] === 3) {
						//jpg 또는 png 파일 적용
						$thumb = thumbnail($fileName1, $file_dir, $file_dir, $cf_img_width, $cf_img_height, true, true);

						if ($thumb) {
							@unlink($dest_path1);
							rename($file_dir . '/' . $thumb, $dest_path1);
						}
					}
					if (!$thumb) {
						// 아이콘의 폭 또는 높이가 설정값 보다 크다면 이미 업로드 된 아이콘 삭제
						@unlink($dest_path1);
					}
				}
				//=================================================================\
			}

			$chk_Img1 = $fileName1;
		}
	}

	//새로운 팝업 이미지경로 출력
	$chk_Bm_Img1 = $file_dir . '/' . $fileName1;


	//파일저장방법 변경 _blob -------------------------------------------------------- 2019.02.19

	if (file_exists($chk_Bm_Img1) && $fileName1 != "") {
		$new_FileName1 = time() . "_1";

		//첨부파일 -> 썸네일 이미지로 변경 및 저장된 경로
		$Chk_Filename1 = $chk_Bm_Img1;
		$handle1 = fopen($Chk_Filename1, "rb");
		$size1 =	GetImageSize($Chk_Filename1);
		$width = $size1[0];
		$height = $size1[1];
		$imageBlob1 = addslashes(fread($handle1, filesize($Chk_Filename1)));
		$filesize = filesize($Chk_Filename1);
		$mine = $size1['mime'];
		fclose($handle1);


		$upQuery1 = "
		UPDATE TB_CONFIG_BOOKMARK 
		SET 
			bm_Img_1 ='" . $new_FileName1 . "' 
		WHERE 
			idx = 1
		LIMIT 1
	";
		$DB_con->exec($upQuery1);


		// 파일로 blob형태 이미지 저장----------S
		// 새로 생성되는 파일명(전체경로 포함) : $m_file
		$img_Txt1 = $new_FileName1;
		$m_File1 = $file_dir . '/' . $img_Txt1;
		$is_file_exist1 = file_exists($m_File1);

		if ($is_File_Exist1) {
			//echo 'Found it';
		} else {
			//echo 'Not found.';
			$file1 = fopen($m_File1, "w");
			fwrite($file1, $imageBlob1);
			fclose($file1);
			chmod($m_File1, 0755);
		}

		//기존 파일 삭제
		@unlink($org_bmImg1);
		//신규 업로드 팝업 이미지 삭제
		@unlink($chk_Bm_Img1);
	}

	// 즐겨찾는 상단 배너 2번 이미지 시작 ------------------------------------------------------------------

	if ($org_bookmarkImg2 == '') {
		$org_bmImg2 = '';
	} else {
		$org_bmImg2 = $file_dir . '/' . $org_bookmarkImg2;	// 기존 이미지 위치
	}

	// 파일삭제여부 확인 하여 삭제 처리
	if ($del_bmImg2) {
		if ($org_bookmarkImg2 != '') {
			$file_Img2 = $org_bmImg2;
			@unlink($file_Img2);
			del_thumbnail(dirname($file_Img2), basename($file_Img2));
			$bm_Img2 = '';
		}
	} else {
		if ($org_bookmarkImg2 == '') {
			$bm_Img2 = '';
		} else {
			$bm_Img2 = $org_bmImg2;
		}
	}

	if (isset($_FILES['bmImg2']) && is_uploaded_file($_FILES['bmImg2']['tmp_name'])) {  //이미지 업로드 성공일 경우


		if (preg_match($image_regex, $_FILES['bmImg2']['name'])) {

			$filename2 = $_FILES['bmImg2']['name'];

			//php파일도 getimagesize 에서 Image Type Flag 를 속일수 있다
			if (!preg_match('/\.(gif|jpe?g|png|webp)$/i', $filename2)) {
				return '';
			}

			$filename2 = preg_replace("/\s+/", "", $filename2);
			$filename2 = preg_replace($pattern, "", $filename2);

			$filename2 = preg_replace_callback(
				"/[가-힣]+/",
				function ($matches) {
					return base64_encode($matches[0]);
				},
				$filename2
			);


			$filename2 = preg_replace($pattern, "", $filename2);

			// 동일한 이름의 파일이 있으면 파일명 변경
			if (is_file($file_dir . '/' . $filename2)) {
				for ($i = 0; $i < 20; $i++) {
					$prepend = str_replace('.', '_', microtime(true)) . '_';

					if (is_file($file_dir . '/' . $prepend . $filename2)) {
						usleep(mt_rand(100, 10000));
						continue;
					} else {
						break;
					}
				}
			}

			$fileName2 = $prepend . $filename2;
			$dest_path2 = $file_dir . '/' . $fileName2;

			move_uploaded_file($_FILES['bmImg2']['tmp_name'], $dest_path2);

			if (file_exists($dest_path2)) {
				$size = @getimagesize($dest_path2);

				if (!($size[2] === 1 || $size[2] === 2 || $size[2] === 3)) { // gif jpg png 파일이 아니면 올라간 이미지를 삭제한다.
					@unlink($dest_path2);
				} else if ($size[0] > $cf_img_width || $size[1] > $cf_img_height) {
					$thumb = null;
					if ($size[2] === 2 || $size[2] === 3) {
						//jpg 또는 png 파일 적용
						$thumb = thumbnail($fileName2, $file_dir, $file_dir, $cf_img_width, $cf_img_height, true, true);

						if ($thumb) {
							@unlink($dest_path2);
							rename($file_dir . '/' . $thumb, $dest_path2);
						}
					}
					if (!$thumb) {
						// 아이콘의 폭 또는 높이가 설정값 보다 크다면 이미 업로드 된 아이콘 삭제
						@unlink($dest_path2);
					}
				}
				//=================================================================\
			}

			$chk_Img2 = $fileName2;
		}
	}

	//새로운 팝업 이미지경로 출력
	$chk_Bm_Img2 = $file_dir . '/' . $fileName2;
	//파일저장방법 변경 _blob -------------------------------------------------------- 2019.02.19
	if (file_exists($chk_Bm_Img2) && $fileName2 != "") {
		$new_FileName2 = time() . "_2";

		//첨부파일 -> 썸네일 이미지로 변경 및 저장된 경로
		$Chk_Filename2 = $chk_Bm_Img2;
		$handle2 = fopen($Chk_Filename2, "rb");
		$size2 =	GetImageSize($Chk_Filename2);
		$width = $size2[0];
		$height = $size2[1];
		$imageBlob2 = addslashes(fread($handle2, filesize($Chk_Filename2)));
		$filesize = filesize($Chk_Filename2);
		$mine = $size2['mime'];
		fclose($handle2);


		$upQuery2 = "
		UPDATE TB_CONFIG_BOOKMARK 
		SET 
			bm_Img_2 ='" . $new_FileName2 . "' 
		WHERE 
			idx = 1
		LIMIT 1
	";
		$DB_con->exec($upQuery2);


		// 파일로 blob형태 이미지 저장----------S
		// 새로 생성되는 파일명(전체경로 포함) : $m_file
		$img_Txt2 = $new_FileName2;
		$m_File2 = $file_dir . '/' . $img_Txt2;
		$is_file_exist2 = file_exists($m_File2);

		if ($is_File_Exist2) {
			//echo 'Found it';
		} else {
			//echo 'Not found.';
			$file2 = fopen($m_File2, "w");
			fwrite($file2, $imageBlob2);
			fclose($file2);
			chmod($m_File2, 0755);
		}

		//기존 파일 삭제
		@unlink($org_bmImg2);
		//신규 업로드 팝업 이미지 삭제
		@unlink($chk_Bm_Img2);
	}

	// 즐겨찾는 상단 배너 3번 이미지 시작 ------------------------------------------------------------------

	if ($org_bookmarkImg3 == '') {
		$org_bmImg3 = '';
	} else {
		$org_bmImg3 = $file_dir . '/' . $org_bookmarkImg3;	// 기존 이미지 위치
	}

	// 파일삭제여부 확인 하여 삭제 처리
	if ($del_bmImg3) {
		if ($org_bookmarkImg3 != '') {
			$file_Img3 = $org_bmImg3;
			@unlink($file_Img3);
			del_thumbnail(dirname($file_Img3), basename($file_Img3));
			$bm_Img3 = '';
		}
	} else {
		if ($org_bookmarkImg3 == '') {
			$bm_Img3 = '';
		} else {
			$bm_Img3 = $org_bmImg3;
		}
	}

	if (isset($_FILES['bmImg3']) && is_uploaded_file($_FILES['bmImg3']['tmp_name'])) {  //이미지 업로드 성공일 경우


		if (preg_match($image_regex, $_FILES['bmImg3']['name'])) {

			$filename3 = $_FILES['bmImg3']['name'];

			//php파일도 getimagesize 에서 Image Type Flag 를 속일수 있다
			if (!preg_match('/\.(gif|jpe?g|png|webp)$/i', $filename3)) {
				return '';
			}

			$filename3 = preg_replace("/\s+/", "", $filename3);
			$filename3 = preg_replace($pattern, "", $filename3);

			$filename3 = preg_replace_callback(
				"/[가-힣]+/",
				function ($matches) {
					return base64_encode($matches[0]);
				},
				$filename3
			);


			$filename3 = preg_replace($pattern, "", $filename3);

			// 동일한 이름의 파일이 있으면 파일명 변경
			if (is_file($file_dir . '/' . $filename3)) {
				for ($i = 0; $i < 20; $i++) {
					$prepend = str_replace('.', '_', microtime(true)) . '_';

					if (is_file($file_dir . '/' . $prepend . $filename3)) {
						usleep(mt_rand(100, 10000));
						continue;
					} else {
						break;
					}
				}
			}

			$fileName3 = $prepend . $filename3;
			$dest_path3 = $file_dir . '/' . $fileName3;

			move_uploaded_file($_FILES['bmImg3']['tmp_name'], $dest_path3);

			if (file_exists($dest_path3)) {
				$size = @getimagesize($dest_path3);

				if (!($size[2] === 1 || $size[2] === 2 || $size[2] === 3)) { // gif jpg png 파일이 아니면 올라간 이미지를 삭제한다.
					@unlink($dest_path2);
				} else if ($size[0] > $cf_img_width || $size[1] > $cf_img_height) {
					$thumb = null;
					if ($size[2] === 2 || $size[2] === 3) {
						//jpg 또는 png 파일 적용
						$thumb = thumbnail($fileName3, $file_dir, $file_dir, $cf_img_width, $cf_img_height, true, true);

						if ($thumb) {
							@unlink($dest_path3);
							rename($file_dir . '/' . $thumb, $dest_path3);
						}
					}
					if (!$thumb) {
						// 아이콘의 폭 또는 높이가 설정값 보다 크다면 이미 업로드 된 아이콘 삭제
						@unlink($dest_path3);
					}
				}
				//=================================================================\
			}

			$chk_Img3 = $fileName3;
		}
	}

	//새로운 팝업 이미지경로 출력
	$chk_Bm_Img3 = $file_dir . '/' . $fileName3;


	//파일저장방법 변경 _blob -------------------------------------------------------- 2019.02.19

	if (file_exists($chk_Bm_Img3) && $fileName3 != "") {
		$new_FileName3 = time() . "_3";

		//첨부파일 -> 썸네일 이미지로 변경 및 저장된 경로
		$Chk_Filename3 = $chk_Bm_Img3;
		$handle3 = fopen($Chk_Filename3, "rb");
		$size3 =	GetImageSize($Chk_Filename3);
		$width = $size3[0];
		$height = $size3[1];
		$imageBlob3 = addslashes(fread($handle3, filesize($Chk_Filename3)));
		$filesize = filesize($Chk_Filename3);
		$mine = $size3['mime'];
		fclose($handle3);


		$upQuery3 = "
		UPDATE TB_CONFIG_BOOKMARK 
		SET 
			bm_Img_3 ='" . $new_FileName3 . "' 
		WHERE 
			idx = 1
		LIMIT 1
		";
		$DB_con->exec($upQuery3);


		// 파일로 blob형태 이미지 저장----------S
		// 새로 생성되는 파일명(전체경로 포함) : $m_file
		$img_Txt3 = $new_FileName3;
		$m_File3 = $file_dir . '/' . $img_Txt3;
		$is_file_exist3 = file_exists($m_File3);

		if ($is_File_Exist3) {
			//echo 'Found it';
		} else {
			//echo 'Not found.';
			$file3 = fopen($m_File3, "w");
			fwrite($file3, $imageBlob3);
			fclose($file3);
			chmod($m_File3, 0755);
		}

		//기존 파일 삭제
		@unlink($org_bmImg3);
		//신규 업로드 팝업 이미지 삭제
		@unlink($chk_Bm_Img3);
	}

	// $upQquery = "UPDATE TB_CONFIG_ETC SET  con_ImgUp = :con_ImgUp, con_TxtFilter = :con_TxtFilter, con_Agree = :con_Agree,  con_Privacy = :con_Privacy  WHERE  idx = :idx  LIMIT 1";
	// $upStmt = $DB_con->prepare($upQquery);
	// $upStmt->bindParam("con_ImgUp", $con_ImgUp);
	// $upStmt->bindParam("con_TxtFilter", $con_TxtFilter);
	// $upStmt->bindParam("con_Agree", $con_Agree);
	// $upStmt->bindParam("con_Privacy", $con_Privacy);
	// $upStmt->bindParam(":idx", $idx);
	// $upStmt->execute();


	// 공지사항 상단 고정
	$tNCnt = COUNT($conTopNoticeArray);
	for ($n = 0; $n < $tNCnt; $n++) {
		$t_Sort = $n + 1;
		$noticeIdx = $conTopNoticeArray[$n];
		if ($noticeIdx != "") {
			// 등록되어 있는 상단 고정 번호를 조회 후 있으면 제거한 후 받은 정보로 다시 상단 고정하기.
			// 고정 조회하기
			$chkTopNoticeQuery = "SELECT idx FROM TB_BOARD WHERE t_Disply = 'Y' AND b_Idx = 1  AND t_Sort = :t_Sort";
			$chkTopNoticeStmt = $DB_con->prepare($chkTopNoticeQuery);
			$chkTopNoticeStmt->bindParam(":t_Sort", $t_Sort);
			$chkTopNoticeStmt->execute();
			$chkTopNoticeCount = $chkTopNoticeStmt->rowCount();
			if ($chkTopNoticeCount > 0) { //있을 경우
				$chkTopNoticeRow = $chkTopNoticeStmt->fetch(PDO::FETCH_ASSOC);
				$orgTopNoticeIdx = $chkTopNoticeRow['idx'];

				$noticeRemoveTopUpQuery = "UPDATE TB_BOARD SET t_Disply = 'N', t_Sort = NULL WHERE idx = :orgTopNoticeIdx";
				$noticeRemoveTopUpStmt = $DB_con->prepare($noticeRemoveTopUpQuery);
				$noticeRemoveTopUpStmt->bindParam(":orgTopNoticeIdx", $orgTopNoticeIdx);
				$noticeRemoveTopUpStmt->execute();
			}
			$noticeUpQuery = "UPDATE TB_BOARD SET t_Disply = 'Y', t_Sort = :t_Sort WHERE idx = :noticeIdx";
			$noticeUpStmt = $DB_con->prepare($noticeUpQuery);
			$noticeUpStmt->bindParam(":t_Sort", $t_Sort);
			$noticeUpStmt->bindParam(":noticeIdx", $noticeIdx);
			$noticeUpStmt->execute();
		}
	}

	// 이벤트 상단 고정
	$tECnt = COUNT($conTopEventArray);
	for ($e = 0; $e < $tECnt; $e++) {
		$t_EventSort = $e + 1;
		$eventIdx = $conTopEventArray[$e];
		if ($eventIdx != "") {
			// 등록되어 있는 상단 고정 번호를 조회 후 있으면 제거한 후 받은 정보로 다시 상단 고정하기.
			// 고정 조회하기
			$chkTopEventQuery = "SELECT idx FROM TB_EVENT WHERE event_Tdisply = 'Y' AND event_Tsort = :event_Tsort";
			$chkTopEventStmt = $DB_con->prepare($chkTopEventQuery);
			$chkTopEventStmt->bindParam(":event_Tsort", $t_EventSort);
			$chkTopEventStmt->execute();
			$chkTopEventCount = $chkTopEventStmt->rowCount();
			if ($chkTopEventCount > 0) { //있을 경우
				$chkTopEventRow = $chkTopEventStmt->fetch(PDO::FETCH_ASSOC);
				$orgTopEventIdx = $chkTopEventRow['idx'];

				$eventRemoveTopUpQuery = "UPDATE TB_EVENT SET event_Tdisply = 'N', event_Tsort = NULL WHERE idx = :orgTopEventIdx";
				$eventRemoveTopUpStmt = $DB_con->prepare($eventRemoveTopUpQuery);
				$eventRemoveTopUpStmt->bindParam(":orgTopEventIdx", $orgTopEventIdx);
				$eventRemoveTopUpStmt->execute();
			}
			$eventUpQuery = "UPDATE TB_EVENT SET event_Tdisply = 'Y', event_Tsort = :event_Tsort WHERE idx = :eventIdx";
			$eventUpStmt = $DB_con->prepare($eventUpQuery);
			$eventUpStmt->bindParam(":event_Tsort", $t_EventSort);
			$eventUpStmt->bindParam(":eventIdx", $eventIdx);
			$eventUpStmt->execute();
		}
	}

	$preUrl = "configEtcReg.php";
	$message = "mod";
	proc_msg($message, $preUrl);
}else if($mode == "del"){
	if($type == "notice"){
		//push 발송 정보 입력
		$upNoticeQuery = " UPDATE TB_BOARD SET t_Disply = 'N', t_Sort = NULL WHERE idx = ".$noticeIdx." LIMIT 1";
		$DB_con->exec($upNoticeQuery);
	}else{
		//push 발송 정보 입력
		$upEventQuery = " UPDATE TB_EVENT SET event_Tdisply = 'N', event_Tsort = NULL WHERE idx = ".$eventIdx." LIMIT 1";
		$DB_con->exec($upEventQuery);
	}

	$preUrl = "configEtcReg.php";
	$message = "del";
	proc_msg($message, $preUrl);
}


dbClose($DB_con);
$stmt = null;
$upStmt = null;
