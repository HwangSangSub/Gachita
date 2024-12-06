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

$DB_con = db1();

$reg_Date = DU_TIME_YMDHIS;           //등록일
if ($mode == "mod") {

    $query = "SELECT m_Img FROM TB_MISSION WHERE idx = :idx";
    $stmt = $DB_con->prepare($query);
    $stmt->bindparam(":idx", $idx);
    //$idx = trim($idx);
    $stmt->execute();

    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    $m_Img = trim($row['m_Img']);
}

// 쿠폰 이미지 경로
$file_dir = $_SERVER["DOCUMENT_ROOT"] . '/data/mission';

$org_m_ImgFile = $file_dir . '/' . $m_Img;

// 파일삭제
if ($del_m_Img) {
    $file_img = $file_dir . '/' . $m_ImgFile;
    @unlink($file_img);
    del_thumbnail(dirname($file_img), basename($file_img));
    $upQuery = "";
    $upQuery = "UPDATE TB_MISSION SET m_Img = ''  WHERE idx = :idx";
    $upStmt = $DB_con->prepare($upQuery);
    $upStmt->bindparam(":idx", $idx);
    $upStmt->execute();
    $m_Img = '';
} else {
    $m_Img = "$m_ImgFile";
}

// 이미지 업로드 
$image_regex = "/(\.(gif|jpe?g|png|webp))$/i";
$cf_img_width = "720";
$cf_img_height = "300";
if (isset($_FILES['m_Img']) && is_uploaded_file($_FILES['m_Img']['tmp_name'])) {  //이미지 업로드 성공일 경우

    if (preg_match($image_regex, $_FILES['m_Img']['name'])) {

        @mkdir($file_dir, 0755);
        // @chmod($file_dir, 0777);

        $filename = $_FILES['m_Img']['name'];

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

        move_uploaded_file($_FILES['m_Img']['tmp_name'], $dest_path);

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

        $m_Img = $fileName;
    }
}
//새로운 팝업 이미지경로 출력
$m_Img = $file_dir . '/' . $fileName;
if ($mode == "reg") {


    $couType = "0";

    $insQuery = "INSERT INTO TB_MISSION 
    SET m_Group = :m_Group
        , m_Type = :m_Type
        , m_Name = :m_Name
        , m_Status = :m_Status
        , m_SPoint = :m_SPoint
        , m_FPoint = :m_FPoint
        , m_GiveType = :m_GiveType
        , m_DCnt = :m_DCnt
        , m_SCnt = :m_SCnt
        , m_Locat = :m_Locat
        , m_Link = :m_Link
        , reg_Date = :reg_Date";

    $stmt = $DB_con->prepare($insQuery);
    $stmt->bindParam(":m_Group", $mGroup);
    $stmt->bindParam(":m_Type", $mType);
    $stmt->bindParam(":m_Name", $mName);
    $stmt->bindParam(":m_Status", $mStatus);
    $stmt->bindParam(":m_SPoint", $mSPoint);
    $stmt->bindParam(":m_FPoint", $mFPoint);
    $stmt->bindParam(":m_GiveType", $mGiveType);
    $stmt->bindParam(":m_DCnt", $mDCnt);
    $stmt->bindParam(":m_SCnt", $mSCnt);
    $stmt->bindParam(":m_Locat", $mLocat);
    $stmt->bindParam(":m_Link", $mLink);
    $stmt->bindParam(":reg_Date", $reg_Date);

    $stmt->execute();
    $mIdx = $DB_con->lastInsertId();

    //파일저장방법 변경 _blob -------------------------------------------------------- 2019.02.19			
    if (file_exists($m_Img) && $fileName != "") {
        $now_time = time() + 5;

        //첨부파일 -> 썸네일 이미지로 변경 및 저장된 경로
        $filename = $m_Img;
        $handle = fopen($filename, "rb");
        $size =    GetImageSize($filename);
        $width = $size[0];
        $height = $size[1];
        $imageblob = addslashes(fread($handle, filesize($filename)));
        $filesize = filesize($filename);
        $mine = $size['mime'];
        fclose($handle);


        $insQuery = "
					UPDATE TB_MISSION 
					SET 
                    m_Img = '" . $now_time . "' 
					WHERE 
						idx ='" . $mIdx . "' 
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
        @unlink($org_m_ImgFile);
        //신규 업로드 팝업 이미지 삭제
        @unlink($m_Img);
        // 파일로 blob형태 이미지 저장----------E

    }

    //파일저장방법 변경 _blob --------------------------------------------------------

    $preUrl = "configMissionList.php?page=$page&$qstr";
    $message = "reg";
    proc_msg($message, $preUrl);
} else if ($mode == "mod") { //수정일경우		


    //파일저장방법 변경 _blob -------------------------------------------------------- 2019.02.19			
    if (file_exists($m_Img) && $fileName != "") {
        $now_time = time() + 5;

        //첨부파일 -> 썸네일 이미지로 변경 및 저장된 경로
        $filename = $m_Img;
        $handle = fopen($filename, "rb");
        $size =    GetImageSize($filename);
        $width = $size[0];
        $height = $size[1];
        $imageblob = addslashes(fread($handle, filesize($filename)));
        $filesize = filesize($filename);
        $mine = $size['mime'];
        fclose($handle);


        $insQuery = "
					UPDATE TB_MISSION 
					SET 
                        m_Img ='" . $now_time . "' 
					WHERE 
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
        @unlink($org_m_ImgFile);
        //신규 업로드 팝업 이미지 삭제
        @unlink($m_Img);
        // 파일로 blob형태 이미지 저장----------E

    }

    //파일저장방법 변경 _blob --------------------------------------------------------

    $upQuery = "
				UPDATE 
                    TB_MISSION 
				SET m_Group = :m_Group
                    , m_Type = :m_Type
                    , m_Name = :m_Name
                    , m_Status = :m_Status
                    , m_SPoint = :m_SPoint
                    , m_FPoint = :m_FPoint
                    , m_GiveType = :m_GiveType
                    , m_DCnt = :m_DCnt
                    , m_SCnt = :m_SCnt
                    , m_Locat = :m_Locat
                    , m_Link = :m_Link
				WHERE 
					idx = :idx 
				LIMIT 1";
    $upStmt = $DB_con->prepare($upQuery);
    $upStmt->bindParam(":m_Group", $mGroup);
    $upStmt->bindParam(":m_Type", $mType);
    $upStmt->bindParam(":m_Name", $mName);
    $upStmt->bindParam(":m_Status", $mStatus);
    $upStmt->bindParam(":m_SPoint", $mSPoint);
    $upStmt->bindParam(":m_FPoint", $mFPoint);
    $upStmt->bindParam(":m_GiveType", $mGiveType);
    $upStmt->bindParam(":m_DCnt", $mDCnt);
    $upStmt->bindParam(":m_SCnt", $mSCnt);
    $upStmt->bindParam(":m_Locat", $mLocat);
    $upStmt->bindParam(":m_Link", $mLink);
    // $upStmt->bindParam(":end_Date", $end_Date);
    $upStmt->bindParam(":idx", $idx);
    $upStmt->execute();

    $preUrl = "configMissionList.php?page=$page&$qstr";
    $message = "mod";
    proc_msg($message, $preUrl);
} else {  //삭제일경우

    //쿠론 삭제
    $delQuery = "UPDATE TB_MISSION SET m_Status = '3' WHERE idx = :idx";
    $delStmt = $DB_con->prepare($delQuery);
    $delStmt->bindparam(":idx", $idx);
    $delStmt->execute();

    $preUrl = "configMissionList.php?page=$page&$qstr";
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
