<?
include "../lib/common.php";
include "../lib/functionDB.php";  //공통 db함수

$mem_Id = trim($memId);                //아이디

if ($mem_Id != "") {  //아이디가 있을 경우

    $DB_con = db1();

    $mem_Idx = memIdxInfo($mem_Id);   //회원 주아이디

    $mnSql = "  , ( SELECT memLv_Name FROM TB_MEMBER_LEVEL WHERE TB_MEMBER_LEVEL.memLv = TB_MEMBERS.mem_Lv limit 1 ) AS memLvName  ";
    $mnCSql = "  , ( SELECT memLv_Color FROM TB_MEMBER_LEVEL WHERE TB_MEMBER_LEVEL.memLv = TB_MEMBERS.mem_Lv limit 1 ) AS memLvColor  ";
    $mnISql = "  , ( SELECT memIconInfoFile FROM TB_MEMBER_LEVEL WHERE TB_MEMBER_LEVEL.memLv = TB_MEMBERS.mem_Lv limit 1 ) AS memLvImg  ";
    $mnDcSql = "  , ( SELECT memDc FROM TB_MEMBER_LEVEL WHERE TB_MEMBER_LEVEL.memLv = TB_MEMBERS.mem_Lv LIMIT 1 ) AS memLvDc  ";
    $mImgSql = "  , ( SELECT mem_profile_update FROM TB_MEMBER_PHOTO WHERE TB_MEMBER_PHOTO.mem_Idx = TB_MEMBERS.idx LIMIT 1 ) AS mem_ImgFile  ";
    $memQuery = "SELECT idx, mem_NickNm, mem_Tel, mem_CharBit, mem_CharIdx, mem_Birth, mem_Lv, mem_Code, mem_NPush, mem_MPush, mem_CertBit {$mnSql} {$mnCSql} {$mnISql} {$mnDcSql} {$mImgSql} FROM TB_MEMBERS WHERE idx = :mem_Idx AND mem_Id = :mem_Id AND b_Disply = 'N' ";
    $stmt = $DB_con->prepare($memQuery);
    $stmt->bindparam(":mem_Idx", $mem_Idx);
    $stmt->bindparam(":mem_Id", $mem_Id);
    $stmt->execute();
    $num = $stmt->rowCount();

    if ($num < 1) { //아닐경우
        // $result = array("result" => true, "totCnt" => (int)$num);
        $result = array("result" => false, "errorMsg" => "등록된 회원이 아닙니다.");
    } else {

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {

            $mem_Id = $mem_Id;                             // 아이디
            $mem_NickNm = $row['mem_NickNm'];              // 닉네임
            $mem_Tel = $row['mem_Tel'];                    // 전화번호
            $mem_ImgFile = $row['mem_ImgFile'];            // 이미지 경로 (/data/member)
            $mem_CharBit = $row['mem_CharBit'];            // 캐릭터프로필 선택 여부( 0: 미선택, 1: 선택)
            $mem_CharIdx = $row['mem_CharIdx'];            // 캐릭터프로필 고유번호
            if ($mem_CharIdx == "") {
                $memCharIdx = "";
            } else {
                $memCharIdx = $mem_CharIdx;
            }
            if ($mem_CharBit == "1") {
                $profileQuery = "SELECT con_ProfileNo, con_ProfileImg FROM TB_CONFIG_PROFILE WHERE con_ProfileBit = 'Y' AND con_ProfileNo = :memCharIdx ORDER BY con_ProfileSort ASC";
                $profileStmt = $DB_con->prepare($profileQuery);
                $profileStmt->bindparam(":memCharIdx", $memCharIdx);
                $profileStmt->execute();
                $profileRow = $profileStmt->fetch(PDO::FETCH_ASSOC);
                $profile_Img = $profileRow['con_ProfileImg'];

                $imgUrl = "/data/config/profile/";
                $profileImg = $imgUrl . $profile_Img;

                $memImgFile = $profileImg;
            } else {
                if ($mem_ImgFile == '') {
                    $memImgFile = '';
                } else {
                    $memImgFile = '/data/member/photo.php?id=' . $mem_ImgFile;
                }
            }
            $mem_Birth = $row['mem_Birth'];                // 생년월일
            if ($mem_Birth == '') {
                $memBirth = '';
                $memAge = '';
            } else {
                $memBirth = $mem_Birth;
                $now          = date('Ymd');
                $birthday     = date('Ymd', strtotime($mem_Birth));
                $age           = floor(($now - $birthday) / 10000);
                $memAge = (string)$age . "세";
            }
            $mem_Lv = $row['mem_Lv'];                        // 등급
            $memLvName = $row['memLvName'];                  // 등급명
            $memLvColor = $row['memLvColor'];                // 등급색상
            $memLvDc = $row['memLvDc'];                      // 등급수수료
            $memLvImg = "/data/levIcon/photo.php?id=" . $row['memLvImg']; // 등급이미지
            $mem_Code = $row['mem_Code'];                    // 단체코드
            if ($mem_Code == '') {
                $memCode = '';
            } else {
                $mcQuery = "SELECT code FROM TB_FRD_CODE WHERE idx = :idx LIMIT 1";
                $mcStmt = $DB_con->prepare($mcQuery);
                $mcStmt->bindparam(":idx", $mem_Code);
                $mcStmt->execute();
                $mcRow = $mcStmt->fetch(PDO::FETCH_ASSOC);
                $mem_Code = trim($mcRow['code']);                            // 단체코드
                $memCode = $mem_Code;
            }
            $memNPush = $row['mem_NPush'];                //이벤트 공지 알림
            if ($memNPush == "0") {
                $memNPush = true;
            } else {
                $memNPush = false;
            }
            $memMPush = $row['mem_MPush'];                //매칭 및 쪽지 알림
            if ($memMPush == "0") {
                $memMPush = true;
            } else {
                $memMPush = false;
            }
            $mem_CertBit = $row['mem_CertBit'];            //본인인증여부
            if ($mem_CertBit == "0") {
                $mem_CertBit = false;
            } else {
                $mem_CertBit = true;
            }

            //회원 정보
            $mInfoQuery = "SELECT mem_Sex, mem_Seat, mem_Email FROM TB_MEMBERS_INFO WHERE mem_Id = :mem_Id AND mem_Idx = :mem_Idx LIMIT 1";
            $meInfoStmt = $DB_con->prepare($mInfoQuery);
            $meInfoStmt->bindparam(":mem_Id", $mem_Id);
            $meInfoStmt->bindparam(":mem_Idx", $mem_Idx);
            $meInfoStmt->execute();
            $infoNum = $meInfoStmt->rowCount();
            //echo $infoNum."<BR>";

            if ($infoNum < 1) { //아닐경우
            } else {
                while ($ifnoRow = $meInfoStmt->fetch(PDO::FETCH_ASSOC)) {
                    $mem_Sex = trim($ifnoRow['mem_Sex']);                            // 성별 (0:남자 , 1:여자)
                    $mem_Seat = trim($ifnoRow['mem_Seat']);                        // 좌석 (0:앞자리 , 1:뒷자리)
                    $mem_Email = trim($ifnoRow['mem_Email']);                   // 이메일주소
                }
            }

            //회원 기타 정보
            $mEtcQuery = "SELECT mem_Point, mem_Card, mem_CardBit FROM TB_MEMBERS_ETC  WHERE mem_Id = :mem_Id AND mem_Idx = :mem_Idx LIMIT 1";
            $mEtcStmt = $DB_con->prepare($mEtcQuery);
            $mEtcStmt->bindparam(":mem_Id", $mem_Id);
            $mEtcStmt->bindparam(":mem_Idx", $mem_Idx);
            $mEtcStmt->execute();
            $etcNum = $mEtcStmt->rowCount();
            //echo $etcNum."<BR>";
            //exit;

            if ($etcNum < 1) { //아닐경우
            } else {
                while ($etcRow = $mEtcStmt->fetch(PDO::FETCH_ASSOC)) {
                    $mem_Point = trim($etcRow['mem_Point']);            // 포인트
                    $mem_Card = trim($etcRow['mem_Card']);                // 카드등록여부 (0: 미등록 , 1: 등록)
                    if ($mem_Card == "0") {
                        $mem_Card = false;
                    } else {
                        $mem_Card = true;
                    }
                    $mem_CardBit = trim($etcRow['mem_CardBit']);        //카드재등록필요여부(0: 미필요, 1: 필요)
                    if ($mem_CardBit == "0") {
                        $mem_CardBit = true;
                    } else {
                        $mem_CardBit = false;
                    }

                    if ($mem_Point  == "") {
                        $memPoint     = "0";
                    } else {
                        $memPoint     = $mem_Point;
                    }
                }
            }
            $reviewBit = compSharingCnt($mem_Idx);
            
            //적립예정포인트
            $mem_ResPoint = "";
            if ($mem_ResPoint  == "") {
                $memResPoint     = "0";
            } else {
                $memResPoint     = $mem_ResPoint;
            }
        }


        $result = array(
            "result" => true, "memIdx" => (int)$mem_Idx, "memId" => (string)$mem_Id, "memNickNm" => (string)$mem_NickNm, "memTel" => (string)$mem_Tel, "memBirth" => (string)$memBirth, "memAge" => (int)$memAge, "memSex" => (string)$mem_Sex, "memSeat" => (string)$mem_Seat,
            "memEmail" => (string)$mem_Email, "memCharIdx" => $memCharIdx, "memImgFile" => (string)$memImgFile, "memLv" => (string)$mem_Lv, "mem_Card" => $mem_Card, "mem_CardBit" => $mem_CardBit, "memPoint" => (int)$memPoint, "memCode" => (string)$memCode,
            "memLvName" => (string)$memLvName, "memLvColor" => (string)$memLvColor, "memLvImg" => (string)$memLvImg, "memLvDc" =>(int)$memLvDc, "memNPush" => (bool)$memNPush, "memMPush" => (bool)$memMPush, "memCertBit" => (bool)$mem_CertBit, "reviewBit" => (bool)$reviewBit, "memResPoint" => (int)$memResPoint
        );
    }

    echo json_encode($result, JSON_UNESCAPED_UNICODE);

    dbClose($DB_con);
    $stmt = null;
    $meInfoStmt = null;
    $mEtcStmt = null;
    $mMapStmt = null;
    $chktmt = null;
    $upStmt3 = null;
} else {
    $result = array("result" => false, "errorMsg" => "조회 정보값이 없습니다. 관리자에게 문의바랍니다.");
    echo json_encode($result, JSON_UNESCAPED_UNICODE);
}
