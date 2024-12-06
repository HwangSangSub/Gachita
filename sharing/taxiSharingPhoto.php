<?
/*======================================================================================================================

	 * 프로그램		: 메이커의 택시사진 업로드 API
	 * 페이지 설명	: 만남이후 출발단계에서 택시이미지를 업로드 하는 방식.
     * 파일명       : taxiSharingPhoto.php   

========================================================================================================================*/

include "../lib/common.php";
include "../lib/functionDB.php";  //공통 db함수
include "../lib/thumbnail.lib.php";   //썸네일

$idx = trim($idx);                        // 메이커고유번호

$DB_con = db1();
if ($idx != "") {
    // 택시확인하기.
    $sharingChkQuery = "SELECT idx, taxi_MemId, taxi_MemIdx, taxi_Price, taxi_Img FROM TB_STAXISHARING WHERE idx = :idx AND taxi_State = '6'";
    $sharingChkStmt = $DB_con->prepare($sharingChkQuery);
    $sharingChkStmt->bindparam(":idx", $idx);
    $sharingChkStmt->execute();
    $sharingChkNum = $sharingChkStmt->rowCount();
    if ($sharingChkNum < 1) {
        $result = array("result" => false, "errorMsg" => "노선상태가 이동중이 아닙니다. 확인 후 다시 시도해주세요.");
    } else {
        // 택시 이미지 경로
        $taxi_Dir = DU_DATA_PATH . '/sharing/' . $idx;
        @mkdir($taxi_Dir, 0755);
        @chmod($taxi_Dir, 0777);

        $sharingChkRow = $sharingChkStmt->fetch(PDO::FETCH_ASSOC);
        $taxi_Org_Img = $sharingChkRow['taxi_Img'];     // 이미지 파일
        $mem_Idx = $sharingChkRow['taxi_MemIdx'];       // 메이커 회원 고유번호
        $mem_Id = $sharingChkRow['taxi_MemId'];         // 메이커 회원 아이디
        $taxi_Price = $sharingChkRow['taxi_Price'];     // 메이커 요청 금액

        //이미지가 있을 경우 이미지 삭제
        if ($taxi_Org_Img != "") {
            $taxi_Org_ImgFile = $taxi_Dir . "/" . $taxi_Org_Img;
            @unlink($taxi_Org_ImgFile);
        }

        $image_regex = "/(\.(gif|jpe?g|png|webp))$/i";
        $cf_img_width = "720";
        $cf_img_height = "360";
        if (isset($_FILES['taxiImg']) && is_uploaded_file($_FILES['taxiImg']['tmp_name'])) {

            if (preg_match($image_regex, $_FILES['taxiImg']['name'])) {
                $filename = $_FILES['taxiImg']['name'];

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
                $dest_path = $taxi_Dir . '/' . $fileName;

                move_uploaded_file($_FILES['taxiImg']['tmp_name'], $dest_path);

                if (file_exists($dest_path)) {
                    $size = @getimagesize($dest_path);

                    if (!($size[2] === 1 || $size[2] === 2 || $size[2] === 3 || $size[2] === 18)) { // gif jpg png 파일이 아니면 올라간 이미지를 삭제한다.
                        @unlink($dest_path);
                    } else if ($size[0] > $cf_img_width || $size[1] > $cf_img_height) {
                        $thumb = null;
                        if ($size[2] === 2 || $size[2] === 3 || $size[2] === 18) {
                            //jpg 또는 png 파일 적용
                            $thumb = thumbnail($fileName, $taxi_Dir, $taxi_Dir, $cf_img_width, $cf_img_height, true, true);

                            if ($thumb) {
                                @unlink($dest_path);
                                rename($taxi_Dir . '/' . $thumb, $dest_path);
                            }
                        }
                        if (!$thumb) {
                            // 아이콘의 폭 또는 높이가 설정값 보다 크다면 이미 업로드 된 아이콘 삭제
                            @unlink($dest_path);
                        }
                    }
                    //=================================================================\
                    $taxiImg = $taxi_Dir . '/' . $fileName;
                    //파일저장방법 변경 _blob -------------------------------------------------------- 2019.02.19			
                    if (file_exists($taxiImg) && $fileName != "") {
                        $now_time = time() + 5;

                        //첨부파일 -> 썸네일 이미지로 변경 및 저장된 경로
                        $filename = $taxiImg;
                        $handle = fopen($filename, "rb");
                        $size =    GetImageSize($filename);
                        $width = $size[0];
                        $height = $size[1];
                        $imageblob = addslashes(fread($handle, filesize($filename)));
                        $filesize = filesize($filename);
                        $mine = $size['mime'];
                        fclose($handle);

                        $insQuery = "
                            UPDATE TB_STAXISHARING 
                            SET 
                            taxi_Img = :taxi_Img 
                            WHERE 
                                idx = :idx 
                        ";
                        $insStmt = $DB_con->prepare($insQuery);
                        $insStmt->bindparam(":taxi_Img", $now_time);
                        $insStmt->bindparam(":idx", $idx);
                        $insStmt->execute();


                        // 파일로 blob형태 이미지 저장----------Start
                        // 새로 생성되는 파일명(전체경로 포함) : $taxi_File
                        $img_txt = $now_time;
                        $taxi_File = $taxi_Dir . '/' . $img_txt;
                        $is_file_exist = file_exists($taxi_File);

                        if ($is_file_exist) {
                        } else {
                            $file = fopen($taxi_File, "w");
                            fwrite($file, $imageblob);
                            fclose($file);
                            chmod($taxi_File, 0755);
                        }

                        //신규 업로드 팝업 이미지 삭제
                        @unlink($taxiImg);
                        // 파일로 blob형태 이미지 저장----------End

                        $configQuery = "SELECT con_TaxiRate, con_TaxiResDate, con_TaxiEventRate, con_TaxiEventBit, con_TaxiEventStartDate, con_TaxiEventEndDate FROM TB_CONFIG";
                        $configStmt = $DB_con->prepare($configQuery);
                        $configStmt->execute();
                        $configNum = $configStmt->rowCount();
                        if ($configNum < 1) { //없을경우
                            $conTaxiRate = 1;
                            $conTaxiEventBit = false;
                        } else {
                            $configRow = $configStmt->fetch(PDO::FETCH_ASSOC);
                            $con_TaxiRate =  trim($configRow['con_TaxiRate']);                      // 택시이미지 포인트 더 받기 포인트 비율
                            $con_TaxiResDate =  trim($configRow['con_TaxiResDate']);                // 택시이미지 포인트 더 받기 적립예정일 (일)
                            $con_TaxiEventRate =  trim($configRow['con_TaxiEventRate']);            // 택시이미지 포인트 더 받기 이벤트시 포인트 비율
                            $con_TaxiEventBit =  trim($configRow['con_TaxiEventBit']);              // 택시이미지 포인트 더 받기 이벤트 진행 여부 (진행 : Y, 종료 : N)
                            $con_TaxiEventStartDate =  trim($configRow['con_TaxiEventStartDate']);  // 택시이미지 포인트 더 받기 이벤트 시작일
                            $con_TaxiEventEndDate =  trim($configRow['con_TaxiEventEndDate']);      // 택시이미지 포인트 더 받기 이벤트 종료일
                            if ($con_TaxiEventBit == 'Y') {

                                $nowDate = date("Y-m-d");                                            // 오늘
                                $conTaxiEventStartDate = date('Y-m-d', strtotime($con_TaxiEventStartDate));     // 이벤트시작일 
                                $conTaxiEventEndDate = date('Y-m-d', strtotime($con_TaxiEventEndDate));         // 이벤트종료일
                                if ($conTaxiEventStartDate <= $nowDate && $conTaxiEventEndDate >= $nowDate) {
                                    $conTaxiRate = $con_TaxiEventRate;
                                    $conTaxiEventBit = true;
                                } else {
                                    $conTaxiRate = $con_TaxiRate;
                                    $conTaxiEventBit = true;
                                }
                            } else {
                                $conTaxiRate = $con_TaxiRate;
                                $conTaxiEventBit = false;
                            }
                        }
                        if ($conTaxiEventBit) {

                            $now_Date = DU_TIME_YMDHIS;
                            $next_Day = date('Y-m-d', strtotime('+' . $con_TaxiResDate . ' day', strtotime($now_Date)));   // 3일 후
                            $res_Date = $next_Day . " 00:00:00";

                            //포인트 적립 예정 금액 계산
                            $taxiPrice = $taxi_Price * ($conTaxiRate / 100);    // 적립 예정 포인트  = 메이커요청금액 * (포인트비율(%) / 100)

                            //회원 포인트 조회
                            $mem_Point = memPointGet($mem_Idx);

                            // 포인트 구분 (0: +, 1: -)
                            $sign = 0;  // 적립이기 때문에 0

                            // 구분 (0: 매칭, 1: 적립, 2: 환전, 3: 추천인 적립, 4: 포인트적립(카드), 5: 신규가입 이벤트, 6.적립예정, 7:미션적립)
                            $state = 6; // 택시 인증 이미지는 적립 예정으로 처리 해야함.

                            // 구분설명
                            $taxi_SubTitle = "가치타기 인증";

                            // 메모
                            $memo = $now_Date . '
가치타기 인증으로 포인트  ' . number_format($taxiPrice) . '원을 적립' . "";

                            $pointHisInsQuery = "INSERT INTO TB_POINT_HISTORY SET taxi_Sidx = :taxi_Sidx, taxi_MemId = :taxi_MemId, taxi_MemIdx = :taxi_MemIdx, taxi_OrdPoint = :taxi_OrdPoint, taxi_OrgPoint = :taxi_OrgPoint, taxi_Memo = :taxi_Memo, taxi_Sign = :taxi_Sign, taxi_PState = :taxi_PState, taxi_SubTitle = :taxi_SubTitle, reg_Date = :reg_Date, res_Date = :res_Date";
                            $pointHisInsStmt = $DB_con->prepare($pointHisInsQuery);
                            $pointHisInsStmt->bindParam("taxi_Sidx", $idx);
                            $pointHisInsStmt->bindParam("taxi_MemId", $mem_Id);
                            $pointHisInsStmt->bindParam("taxi_MemIdx", $mem_Idx);
                            $pointHisInsStmt->bindParam("taxi_OrdPoint", $taxiPrice);
                            $pointHisInsStmt->bindParam("taxi_OrgPoint", $mem_Point);
                            $pointHisInsStmt->bindParam("taxi_Memo", $memo);
                            $pointHisInsStmt->bindParam("taxi_Sign", $sign);
                            $pointHisInsStmt->bindParam("taxi_PState", $state);
                            $pointHisInsStmt->bindParam("taxi_SubTitle", $taxi_SubTitle);
                            $pointHisInsStmt->bindParam("reg_Date", $now_Date);
                            $pointHisInsStmt->bindParam("res_Date", $res_Date);
                            $pointHisInsStmt->execute();

                            $phIdx = $DB_con->lastInsertId();  //저장된 idx 값
                        }
                        if ($phIdx > 0) {
                            $result = array("result" => true, "memPoint" => (int)$mem_Point, "resPoint" =>  (int)$taxiPrice);
                        } else {
                            $result = array("result" => false, "errorMsg" => (string)"사진업로드는 성공하였지만 포인트적립이 실패하였습니다. 다시 시도해주세요.");
                        }
                    } else {
                        $result = array("result" => false, "errorMsg" => "등록된 이미지가 없습니다. 확인 후 다시 시도해주세요.");
                    }
                } else {
                    $result = array("result" => false, "errorMsg" => "등록된 이미지가 없습니다. 확인 후 다시 시도해주세요.");
                }
            } else {
                $result = array("result" => false, "errorMsg" => "이미지가 아닙니다. 확인 후 다시 시도해주세요.");
            }
        } else {
            $result = array("result" => false, "errorMsg" => "이미지가 없습니다. 확인 후 다시 시도해주세요.");
        }
    }
    dbClose($DB_con);
    $sharingChkStmt = null;
    $insStmt = null;
} else {
    $result = array("result" => false, "errorMsg" => "ERROR #1 : 조회 정보값이 없습니다. 관리자에 문의바랍니다.");
}
echo str_replace('\\/', '/', json_encode($result, JSON_UNESCAPED_UNICODE));
