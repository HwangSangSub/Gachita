<?

/*======================================================================================================================

* 프로그램			: 매칭 생성자 등록 및 매칭중 삭제 화면
* 페이지 설명		: 매칭 생성자 등록 및 매칭중 삭제 화면
* 파일명                 : taxiSharingProc.php

========================================================================================================================*/
include "../lib/common.php";
include "../lib/functionDB.php";  //공통 db함수
include "../lib/sharing_send.php";  //현황확인을 위한 함수

$mem_Id = trim($memId);                //아이디
$mode = trim($mode);         //등록 일 경우 : reg, 수정일 경우 : mod, 삭제일 경우 : del
if ($mode == "") {
    $mode = "reg";      //등록
} else {
    $mode = trim($mode);
    $idx = trim($idx);                     //고유번호 idx (수정일 경우 필수)
}

$taxi_MemId = trim($mem_Id);                        //매창생성자아이디
$taxi_SaddrNm = trim($taxiSaddrNm);          //출발지 검색어

if ($ie) { //익슬플로러일경우
    $taxi_SaddrNm = iconv('euc-kr', 'utf-8', $taxi_SaddrNm);
}

$taxi_SaddrNm = str_replace("null", "", $taxi_SaddrNm);
//$taxi_SaddrNm = str_replace(" ","",$taxi_SaddrNm);

$taxi_Saddr = trim($taxiSaddr);                         //출발지

if ($ie) { //익슬플로러일경우
    $taxi_Saddr = iconv('euc-kr', 'utf-8', $taxi_Saddr);
}

$taxi_Saddr = str_replace("null", "", $taxi_Saddr);
//$taxi_Saddr = str_replace(" ","",$taxi_Saddr);

$taxi_Sdong = trim($taxiSdong);                         //출발지 동명

if ($ie) { //익슬플로러일경우
    $taxi_Sdong = iconv('euc-kr', 'utf-8', $taxi_Sdong);
}

$taxi_Sdong = str_replace("null", "", $taxi_Sdong);
//$taxi_Sdong = str_replace(" ","",$taxi_Sdong);

$taxi_SLat = trim($taxiStartLat);          //출발지 구글 위도
$taxi_SLng = trim($taxiStartLng);        //출발지 구글 경도

$res_lat = $taxi_SLat;
$res_lon = $taxi_SLng;
$taxi_EaddrNm = trim($taxiEaddrNm);          //목적지 검색어

if ($ie) { //익슬플로러일경우
    $taxi_EaddrNm = iconv('euc-kr', 'utf-8', $taxi_EaddrNm);
}

$taxi_EaddrNm = str_replace("null", "", $taxi_EaddrNm);
//$taxi_EaddrNm = str_replace(" ","",$taxi_EaddrNm);

$taxi_Eaddr = trim($taxiEaddr);                         //목적지

if ($ie) { //익슬플로러일경우
    $taxi_Eaddr = iconv('euc-kr', 'utf-8', $taxi_Eaddr);
}

$taxi_Eaddr = str_replace("null", "", $taxi_Eaddr);
//$taxi_Eaddr = str_replace(" ","",$taxi_Eaddr);

$taxi_Edong = trim($taxiEdong);                         //목적지 동명

if ($ie) { //익슬플로러일경우
    $taxi_Edong = iconv('euc-kr', 'utf-8', $taxi_Edong);
}

$taxi_Edong = str_replace("null", "", $taxi_Edong);
//$taxi_Edong = str_replace(" ","",$taxi_Edong);

$taxi_ELat = trim($taxiEndLat);          //목적지 구글 위도
$taxi_ELng = trim($taxiEndLng);        //목적지 구글 경도

$taxi_Distance = trim($taxiDistance);          //총거리
$taxi_Type = trim($taxiType);             //출발타입 ( 0: 바로출발, 1: 예약출발)

if ($taxi_Type == "0") {
    $chkDate = DU_TIME_YMDHIS;         //현재시간
    $chDate = explode(" ", $chkDate);
    $taxi_SDay = trim($chDate[0]);                //출발일
    $taxi_STime = trim($chDate[1]);           //출발시간
    $taxi_SDate = $chkDate;
} else {
    $taxi_SDay = trim($taxiSDay);                //출발일
    $taxi_STime = trim($taxiSTime);         //출발시간
    $taxi_SDate = $taxi_SDay . " " . $taxi_STime . ":00";
}

$taxi_TPrice = trim($taxiTPrice);        //총 예상비용
$taxi_Price = trim($taxiPrice);            //희망 쉐어 비용
$taxi_Per = trim($taxiPer);                //희망 쉐어링 비용적용율
$taxi_ATime = trim($taxiATime);                //택시 총 예상시간
$taxi_MCnt = trim($taxiMCnt);        //인원수
$taxi_Route = trim($taxiRoute);        // 경유가능여부 ( 0: 경유가능, 1: 경유불가)
$taxi_SexBit = trim($taxiSexBit);        // 선호매칭 ( 0: 성별무관, 1: 여자만)		==> 여자인 경우에만 선택가능
if ($taxi_SexBit == "") {
    $taxi_SexBit = 0;
}

$taxi_Memo = trim($taxiMemo);        // 하고싶은말
$reg_Date = DU_TIME_YMDHIS;           //등록일
$res_bit = 0; // 성공여부 (0: 실패, 1: 성공)


if ($mem_Id != "" && $mode != "") {  //아이디랑 등록,수정 삭제 여부가 경우

    $DB_con = db1();

    //회원 고유 아이디
    $mSidQuery = "SELECT idx, mem_NickNm, mem_Os, mem_MPush from TB_MEMBERS WHERE mem_Id = :mem_Id AND b_Disply = 'N' ";
    $mSidStmt = $DB_con->prepare($mSidQuery);
    $mSidStmt->bindparam("mem_Id", $mem_Id);
    $mSidStmt->execute();
    $mSidNum = $mSidStmt->rowCount();

    if ($mSidNum < 1) { //아닐경우
    } else {
        while ($mSidRow = $mSidStmt->fetch(PDO::FETCH_ASSOC)) {
            $memIdx = $mSidRow['idx'];         //회원고유아이디
            $memNickNm = $mSidRow['mem_NickNm'];   //회원 닉네임
            $memOs = $mSidRow['mem_Os'];         //os구분  (0 : 안드로이드, 1: 아이폰)
            $memMPush = $mSidRow['mem_MPush'];     //푸시발송여부  (0 : 발송, 1: 발송불가)
        }
    }

    //등록일 경우
    if ($mode == "reg") {

        //정보값 가져오기
        $memQuery = "SELECT mem_Sex, mem_Seat from TB_MEMBERS_INFO  WHERE mem_Id = :mem_Id ";
        $stmt = $DB_con->prepare($memQuery);
        $stmt->bindparam("mem_Id", $mem_Id);
        $stmt->execute();
        $num = $stmt->rowCount();

        if ($num < 1) { //아닐경우
        } else {
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $taxi_Sex = $row['mem_Sex'];            //성별 ( 0: 남자, 1: 여자)
                $taxi_Seat = $row['mem_Seat'];            //좌석 ( 0: 앞좌석, 1: 뒷좌석)
            }
        }

        $chkTaxiState = makerState($taxi_MemId);
        $chkTaxiStateR = togetherState($taxi_MemId);

        //테스트로 무조건 생성가능하게 처리 추후 규정정해서 변경해야함.
        // $chkTaxiState = 0;
        // $chkTaxiStateR = 0;

        // echo $taxi_MemId."\n";
        // echo $chkTaxiState."\n";
        // echo $chkTaxiStateR."\n";
        // exit;
        if ($chkTaxiState < 1 && $chkTaxiStateR < 1) {  // 이동중, 완료, 취소일 경우 생성 가능

            $taxi_State = "1"; //매칭중

            $taxi_SPosition = "POINT(" . $taxi_SLng . " " . $taxi_SLat . ")"; //출발지 위경도ㄴㄴㅁㄴㅇㅁㄴㅇ
            $taxi_EPosition = "POINT(" . $taxi_ELng . " " . $taxi_ELat . ")"; //도착지 위경도
            /* 경로를 미리 조회해서 서버에 저장하기. */
            $startX = $taxi_SLng;
            $startY = $taxi_SLat;
            $viaX = $_GET["viaX"];
            $viaY = $_GET["viaY"];
            $endX = $taxi_ELng;
            $endY = $taxi_ELat;

            if ($viaX != "" && $viaY != "") {
                $remote_file = "https://apis.openapi.sk.com/tmap/routes?version=1&searchOption=1&format=json&reqCoordType=WGS84GEO&endX=" . $endX . "&endY=" . $endY . "&startX=" . $startX . "&startY=" . $startY . "&appKey=d966c545-ca3a-4d13-8dec-e80a39e78861&passList=" . $viaX . "," . $viaY;
            } else {
                $remote_file = "https://apis.openapi.sk.com/tmap/routes?version=1&searchOption=1&format=json&reqCoordType=WGS84GEO&endX=" . $endX . "&endY=" . $endY . "&startX=" . $startX . "&startY=" . $startY . "&appKey=d966c545-ca3a-4d13-8dec-e80a39e78861";
            }

            $ch = curl_init($remote_file);
            curl_setopt($ch, CURLOPT_TIMEOUT, 50);
            curl_setopt($ch, CURLOPT_FILE, $fp);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
            curl_setopt($ch, CURLOPT_ENCODING, "");
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $contents = curl_exec($ch);
            $contents_json = json_decode($contents, true); // 결과값을 파싱
            curl_close($ch);

            $contents_result = $contents_json['features'];
            // echo count($contents_result);
            $pointArr = [];
            $lineArr = [];
            for ($i = 0; $i < count($contents_result); $i++) {
                // print_r($contents_result[$i]['geometry']);
                $type = $contents_result[$i]['geometry']['type'];
                $coordinates = $contents_result[$i]['geometry']['coordinates'];
                // print_r($coordinates);
                if ($type == 'Point') {
                    array_push($pointArr, $coordinates);
                } else if ($type == 'LineString') {
                    array_push($lineArr, $coordinates);
                } else {
                }
            }
            //쉐어링 매칭생성 기본테이블
            $insQuery = "INSERT INTO TB_STAXISHARING (taxi_MemIdx, taxi_MemId, taxi_Saddr, taxi_Eaddr, taxi_SPosition, taxi_EPosition, taxi_TPrice, taxi_Price, taxi_Per, taxi_Memo, taxi_ATime, taxi_SDate, taxi_State, taxi_Os, reg_Date)
                     VALUES (:taxi_MemIdx, :taxi_MemId, :taxi_Saddr, :taxi_Eaddr, ST_GeomFromText(:taxi_SPosition), ST_GeomFromText(:taxi_EPosition), :taxi_TPrice, :taxi_Price, :taxi_Per, :taxi_Memo, :taxi_ATime, :taxi_SDate, :taxi_State, :taxi_Os, :reg_Date)";
            //echo $insQuery."<BR>";
            //exit;
            $stmt = $DB_con->prepare($insQuery);
            $stmt->bindParam("taxi_MemIdx", $memIdx);
            $stmt->bindParam("taxi_MemId", $taxi_MemId);
            $stmt->bindParam("taxi_Saddr", $taxi_Saddr);
            $stmt->bindParam("taxi_Eaddr", $taxi_Eaddr);
            $stmt->bindParam('taxi_SPosition', $taxi_SPosition, PDO::PARAM_STR);
            $stmt->bindParam('taxi_EPosition', $taxi_EPosition, PDO::PARAM_STR);
            $stmt->bindParam("taxi_TPrice", $taxi_TPrice);
            $stmt->bindParam("taxi_Price", $taxi_Price);
            $stmt->bindParam("taxi_Per", $taxi_Per);
            $stmt->bindParam("taxi_Memo", $taxi_Memo);
            $stmt->bindParam("taxi_ATime", $taxi_ATime);
            $stmt->bindParam("taxi_SDate", $taxi_SDate);
            $stmt->bindParam("taxi_State", $taxi_State);
            $stmt->bindParam("taxi_Os", $memOs);
            $stmt->bindParam("reg_Date", $reg_Date);
            $stmt->execute();
            $DB_con->lastInsertId();

            $mIdx = $DB_con->lastInsertId();  //idx 값던져 주기 위해서 추가해줌 2018-08-07

            if ($stmt->rowCount() > 0) { //삽입 성공

                //쉐어링 매칭생성 정보테이블
                $insInFoQuery = "INSERT INTO TB_STAXISHARING_INFO (taxi_MemIdx, taxi_Idx, taxi_MemId, taxi_Type, taxi_MCnt, taxi_Distance, taxi_Route, taxi_Sex, taxi_SexBit, taxi_Seat)
                             VALUES (:taxi_MemIdx, :taxi_Idx, :taxi_MemId, :taxi_Type, :taxi_MCnt, :taxi_Distance, :taxi_Route, :taxi_Sex, :taxi_SexBit, :taxi_Seat)";
                $stmtInfo = $DB_con->prepare($insInFoQuery);
                $stmtInfo->bindParam("taxi_MemIdx", $memIdx);
                $stmtInfo->bindParam("taxi_Idx", $mIdx);
                $stmtInfo->bindParam("taxi_MemId", $taxi_MemId);
                $stmtInfo->bindParam("taxi_Type", $taxi_Type);
                $stmtInfo->bindParam("taxi_MCnt", $taxi_MCnt);
                $stmtInfo->bindParam("taxi_Distance", $taxi_Distance);
                $stmtInfo->bindParam("taxi_Route", $taxi_Route);
                $stmtInfo->bindParam("taxi_Sex", $taxi_Sex);
                $stmtInfo->bindParam("taxi_SexBit", $taxi_SexBit);
                $stmtInfo->bindParam("taxi_Seat", $taxi_Seat);
                $stmtInfo->execute();

                //쉐어링 매칭생성 주소 테이블
                $insMapQuery = "INSERT INTO TB_STAXISHARING_MAP (taxi_MemIdx, taxi_Idx, taxi_MemId, taxi_SaddrNm, taxi_Saddr, taxi_Sdong, taxi_SLat, taxi_SLng, taxi_EaddrNm, taxi_Eaddr, taxi_Edong, taxi_ELat, taxi_ELng, taxi_Point, taxi_Line)
                             VALUES (:taxi_MemIdx, :taxi_Idx, :taxi_MemId, :taxi_SaddrNm, :taxi_Saddr, :taxi_Sdong, :taxi_SLat, :taxi_SLng, :taxi_EaddrNm, :taxi_Eaddr, :taxi_Edong, :taxi_ELat, :taxi_ELng, :taxi_Point, :taxi_Line)";
                $stmtMap = $DB_con->prepare($insMapQuery);
                $stmtMap->bindParam("taxi_MemIdx", $memIdx);
                $stmtMap->bindParam("taxi_Idx", $mIdx);
                $stmtMap->bindParam("taxi_MemId", $taxi_MemId);
                $stmtMap->bindParam("taxi_SaddrNm", $taxi_SaddrNm);
                $stmtMap->bindParam("taxi_Saddr", $taxi_Saddr);
                $stmtMap->bindParam("taxi_Sdong", $taxi_Sdong);
                $stmtMap->bindParam("taxi_SLat", $taxi_SLat);
                $stmtMap->bindParam("taxi_SLng", $taxi_SLng);
                $stmtMap->bindParam("taxi_EaddrNm", $taxi_EaddrNm);
                $stmtMap->bindParam("taxi_Eaddr", $taxi_Eaddr);
                $stmtMap->bindParam("taxi_Edong", $taxi_Edong);
                $stmtMap->bindParam("taxi_ELat", $taxi_ELat);
                $stmtMap->bindParam("taxi_ELng", $taxi_ELng);
                $stmtMap->bindParam("taxi_Point", json_encode($pointArr));
                $stmtMap->bindParam("taxi_Line", json_encode($lineArr));
                $stmtMap->execute();

                //미션 확인하기. (메이커로 노선만들기)
                makerRoom($memIdx, $mIdx);
                $result = array("result" => true, "idx" => (int)$mIdx);
                $res_bit = 1; // 성공여부 (0: 실패, 1: 성공)
            } else { //등록시 에러
                $result = array("result" => false, "errorMsg" => "노선 등록중에 에러가 발생했습니다. 관리자에게 문의해주세요.");
            }
        } else { //아닐 경우
            if ($chkTaxiState > 0 && $chkTaxiStateR == 0) {
                if ($chkTaxiState == "1") {
                    $chkStatNm = "매칭중인";
                } else if ($chkTaxiState == "2") {
                    $chkStatNm = "매칭요청중인";
                } else if ($chkTaxiState == "3") {
                    $chkStatNm = "예약요청중인";
                } else if ($chkTaxiState == "4") {
                    $chkStatNm = "예약요청완료중인";
                } else if ($chkTaxiState == "5") {
                    $chkStatNm = "만남중인";
                } else if ($chkTaxiState == "6") {
                    $chkStatNm = "이동중인";
                } else if ($chkTaxiState == "9") {
                    $chkStatNm = "취소사유확인중인";
                } else if ($chkTaxiState == "10") {
                    $chkStatNm = "거래완료확인중인";
                }
            } else if ($chkTaxiState == 0 && $chkTaxiStateR > 0) {
                if ($chkTaxiStateR == "1") {
                    $chkStatNm = "매칭요청중인";
                } else if ($chkTaxiStateR == "2") {
                    $chkStatNm = "예약요청중인";
                } else if ($chkTaxiStateR == "4") {
                    $chkStatNm = "예약요청완료중인";
                } else if ($chkTaxiStateR == "5") {
                    $chkStatNm = "만남중인";
                } else if ($chkTaxiStateR == "6") {
                    $chkStatNm = "이동중인";
                } else if ($chkTaxiStateR == "9") {
                    $chkStatNm = "취소사유확인중인";
                } else if ($chkTaxiStateR == "10") {
                    $chkStatNm = "거래완료확인중인";
                }
            } else {
                $chkStatNm = "관리자 확인중인";
            }

            $result = array("result" => false, "errorMsg" => "현재 " . $chkStatNm . " 노선이 있어서 중복으로 노선 등록을 할 수 없습니다.");
        }

        //삭제일 경우
    } else if ($mode == "del") {

        $chkCntQuery = "SELECT count(taxi_MemId) AS num FROM TB_STAXISHARING WHERE taxi_MemIdx = :taxi_MemIdx AND taxi_MemId = :taxi_MemId  AND idx = :idx AND taxi_State = '1' "; //매칭중 취소
        $stmt = $DB_con->prepare($chkCntQuery);
        $stmt->bindParam("taxi_MemIdx", $memIdx);
        $stmt->bindparam("taxi_MemId", $taxi_MemId);
        $stmt->bindparam("idx", $idx);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $num = $row['num'];

        if ($num < 1) { //요청 대기자가 있을 경우
            $result = array("result" => false, "errorMsg" => "없는 노선이거나 취소할 수 없는 노선입니다. 확인 후 다시 시도해주세요.");
        } else {  // 매칭생성,매칭중 일 경우 수정 가능

            $chkQuery = "SELECT taxi_SLat, taxi_SLng from TB_STAXISHARING_MAP WHERE taxi_Idx = :idx "; //매칭중 취소
            $chkstmt = $DB_con->prepare($chkQuery);
            $chkstmt->bindparam("idx", $idx);
            $chkstmt->execute();
            // echo "14124124";
            $chkrow = $chkstmt->fetch(PDO::FETCH_ASSOC);
            $taxi_SLat = $chkrow['taxi_SLat'];
            $taxi_SLng = $chkrow['taxi_SLng'];
            $res_lat = $taxi_SLat;
            $res_lon = $taxi_SLng;

            //쉐어링 매칭생성 기본테이블
            $delQquery = "UPDATE TB_STAXISHARING SET taxi_State = '8', taxi_MState = '1',  reg_CDate = NOW() WHERE idx = :idx LIMIT 1";
            $delStmt = $DB_con->prepare($delQquery);
            $delStmt->bindParam("idx", $idx);
            $delStmt->execute();
            $result = array("result" => true);
            $res_bit = 1; // 성공여부 (0: 실패, 1: 성공).
        }
    }

    dbClose($DB_con);
    $stmt = null;
    $chkCntStmt = null;
    $chkStmt = null;
    $chkCntStmt2 = null;
    $chkStmt2 = null;
    $stmtInfo = null;
    $stmtEtc = null;
    $stmtMap = null;
    $delStmt = null;
    $delInfoStmt = null;
    $delMapStmt = null;
} else {
    $result = array("result" => false);
}
echo str_replace('\\/', '/', json_encode($result, JSON_UNESCAPED_UNICODE));

// 성공할 경우 curl로 현황 동기화
if ($res_bit == 1) {
    common_Form(array("lat" => (float)$res_lat, "lon" => (float)$res_lon));
}
