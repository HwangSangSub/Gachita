<?

include "../../udev/lib/common.php";
include "../../lib/alertLib.php";
include "../../lib/thumbnail.lib.php";   //썸네일

$mode = $mode;

if ($mode == "") {
    $mode = "reg";
} else {
    $mode = $mode;
}


if ($mode != "allDel") {
}


$DB_con = db1();

if ($mode == "mod") {

    $query = "";
    $query = "SELECT taxi_Img FROM TB_TAXICALL WHERE idx = :idx";
    $stmt = $DB_con->prepare($query);
    $stmt->bindparam(":idx", $idx);
    //$idx = trim($idx);
    $stmt->execute();

    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    $taxi_Img = trim($row['taxi_Img']);
}

// 쿠폰 이미지 경로
$file_dir = $_SERVER["DOCUMENT_ROOT"] . '/data/taxicall';

$org_taxi_ImgFile = $file_dir . '/' . $taxi_Img;

// 파일삭제
if ($del_taxiImg) {
    $file_img1 = $file_dir . '/' . $taxi_ImgFile;
    @unlink($file_img1);
    del_thumbnail(dirname($file_img1), basename($file_img1));
    $upQuery = "";
    $upQuery = "UPDATE TB_TAXICALL SET taxi_Img = ''  WHERE idx = :idx";
    $upStmt = $DB_con->prepare($upQuery);
    $upStmt->bindparam(":idx", $idx);
    $upStmt->execute();
    $taxi_Img = '';
} else {
    $taxi_Img = "$taxi_ImgFile";
}

// 이미지 업로드 
$image_regex = "/(\.(gif|jpe?g|png|webp))$/i";

$cf_img_width = "720";
$cf_img_height = "300";
if (isset($_FILES['taxi_Img']) && is_uploaded_file($_FILES['taxi_Img']['tmp_name'])) {  //이미지 업로드 성공일 경우


    if (preg_match($image_regex, $_FILES['taxi_Img']['name'])) {

        @mkdir($file_dir, 0755);
        // @chmod($file_dir, 0777);

        $filename = $_FILES['taxi_Img']['name'];

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

        move_uploaded_file($_FILES['taxi_Img']['tmp_name'], $dest_path);

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

        $taxi_Img = $fileName;
    }
}
//새로운 팝업 이미지경로 출력
$taxi_Img = $file_dir . '/' . $fileName;
if ($mode == "reg") {

    //쿠폰번호
    $reg_Date = DU_TIME_YMDHIS;           //등록일

    $couType = "0";

    $insQuery = "
				INSERT INTO TB_TAXICALL ( taxi_Name, taxi_Type, taxi_locat, taxi_Tel, taxi_And_Install, taxi_Ios_Install, taxi_Ios, taxi_Memo, taxi_UseBit,reg_Date ) 
                VALUES ( :taxi_Name, :taxi_Type, :taxi_locat, :taxi_Tel, :taxi_And_Install, :taxi_Ios_Install, :taxi_Ios, :taxi_Memo, :taxi_UseBit,:reg_Date )";

    $stmt = $DB_con->prepare($insQuery);
    $stmt->bindParam(":taxi_Name", $taxiName);
    $stmt->bindParam(":taxi_Type", $taxiType);
    $stmt->bindParam(":taxi_locat", $taxilocat);
    $stmt->bindParam(":taxi_Tel", $taxiTel);
    $stmt->bindParam(":taxi_And_Install", $taxiAndInstall);
    $stmt->bindParam(":taxi_Ios_Install", $taxiIosInstall);
    $stmt->bindParam(":taxi_Ios", $taxiIos);
    $stmt->bindParam(":taxi_Memo", $taxiMemo);
    $stmt->bindParam(":taxi_UseBit", $taxiUseBit);
    $stmt->bindParam("reg_Date", $reg_Date);

    $stmt->execute();
    $taxi_Call_Id = $DB_con->lastInsertId();

    //파일저장방법 변경 _blob -------------------------------------------------------- 2019.02.19			
    if (file_exists($member_img) && $fileName != "") {
        $now_time = time() + 5;

        //첨부파일 -> 썸네일 이미지로 변경 및 저장된 경로
        $filename = $member_img;
        $handle = fopen($filename, "rb");
        $size =    GetImageSize($filename);
        $width = $size[0];
        $height = $size[1];
        $imageblob = addslashes(fread($handle, filesize($filename)));
        $filesize = filesize($filename);
        $mine = $size['mime'];
        fclose($handle);


        $insQuery = "
					UPDATE TB_TAXICALL 
					set 
                    taxi_Img ='" . $now_time . "' 
					where 
						idx ='" . $taxi_Call_Id . "' 
				";
        $DB_con->exec($insQuery);


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
        @unlink($org_taxi_ImgFile);
        //신규 업로드 팝업 이미지 삭제
        @unlink($taxi_Img);
        // 파일로 blob형태 이미지 저장----------E

    }

    //파일저장방법 변경 _blob --------------------------------------------------------

    $preUrl = "taxiCallList.php?page=$page&$qstr";
    $message = "reg";
    proc_msg($message, $preUrl);
} else if ($mode == "mod") { //수정일경우		


    //파일저장방법 변경 _blob -------------------------------------------------------- 2019.02.19			
    if (file_exists($taxi_Img) && $fileName != "") {
        $now_time = time() + 5;

        //첨부파일 -> 썸네일 이미지로 변경 및 저장된 경로
        $filename = $taxi_Img;
        $handle = fopen($filename, "rb");
        $size =    GetImageSize($filename);
        $width = $size[0];
        $height = $size[1];
        $imageblob = addslashes(fread($handle, filesize($filename)));
        $filesize = filesize($filename);
        $mine = $size['mime'];
        fclose($handle);


        $insQuery = "
					UPDATE TB_TAXICALL 
					set 
                        taxi_Img ='" . $now_time . "' 
					where 
						idx ='" . $idx . "' 
				";
        $DB_con->exec($insQuery);


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
        @unlink($org_taxi_ImgFile);
        //신규 업로드 팝업 이미지 삭제
        @unlink($taxi_Img);
        // 파일로 blob형태 이미지 저장----------E

    }

    //파일저장방법 변경 _blob --------------------------------------------------------

    $upQuery = "
				UPDATE 
                    TB_TAXICALL 
				SET 
                    taxi_Name = :taxi_Name,
                    taxi_Type = :taxi_Type,
					taxi_locat = :taxi_locat,
					taxi_Tel = :taxi_Tel, 
					taxi_And_Install = :taxi_And_Install, 
					taxi_Ios_Install = :taxi_Ios_Install,
					taxi_Ios = :taxi_Ios,
					taxi_Memo = :taxi_Memo,
					taxi_UseBit = :taxi_UseBit
				WHERE 
					idx = :idx 
				LIMIT 1";
    $upStmt = $DB_con->prepare($upQuery);
    $upStmt->bindparam(":taxi_Name", $taxiName);
    $upStmt->bindparam(":taxi_Type", $taxiType);
    $upStmt->bindparam(":taxi_locat", $taxilocat);
    $upStmt->bindParam(":taxi_Tel", $taxiTel);
    $upStmt->bindParam(":taxi_And_Install", $taxiAndInstall);
    $upStmt->bindParam(":taxi_Ios_Install", $taxiIosInstall);
    $upStmt->bindParam(":taxi_Ios", $taxiIos);
    $upStmt->bindParam(":taxi_Memo", $taxiMemo);
    $upStmt->bindParam(":taxi_UseBit", $taxiUseBit);
    $upStmt->bindParam(":idx", $idx);
    $upStmt->execute();

    $preUrl = "taxiCallList.php?page=$page&$qstr";
    $message = "mod";
    proc_msg($message, $preUrl);
} else {  //삭제일경우

    //쿠론 삭제
    $delQuery = "DELETE FROM TB_TAXICALL WHERE idx = :idx";
    $delStmt = $DB_con->prepare($delQuery);
    $delStmt->bindparam(":idx", $idx);
    $delStmt->execute();

    $preUrl = "taxiCallList.php?page=$page&$qstr";
    $message = "del";
    proc_msg($message, $preUrl);
}


dbClose($DB_con);
$stmt = null;
$cntStmt = null;
$upStmt = null;
$conStmt = null;
$fileStmt = null;
$delStmt = null;
