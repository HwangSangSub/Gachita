<?

/*======================================================================================================================

* 프로그램			: 도착지 근처 검색 목록
* 페이지 설명		: 도착지 근처 검색 목록

========================================================================================================================*/

include "../lib/common.php";


$startLng = trim($startLng);             // 현재 위치 경도
$startLat = trim($startLat);             // 현재 위치 위도
$endLng = trim($endLng);                 // 도착치 위치 경도
$endLat = trim($endLat);                 // 도착지 위치 위도
//$sort = trim($sort);                 	// 정렬순 (0 : 가까운순, 1: 최근생성 )
//$chkDistance = trim($chkDistance);	 // 거리 (1, 3, 5 )    // 거리 (1, 3, 5 ) ex) 0.5 => 500m 동일
//목적지 기준 3Km
$chkDistance = "3000"; //5km => 5000
//출발지 기준 1KM
$schkDistance = "1000"; //5km => 5000
$sort = "0";

if ($startLng != "" && $startLat != "" && $endLng != ""  && $endLat != "") {  // 현재위치 좌표, 도착지 좌표

    if ($sort == "0" || $sort == "") {
        $sort  = "0";
    } else if ($sort == "1") {
        $sort  = "1";
    }

    $DB_pcon = db1(); //프로시져
    $DB_con = db1();

    /* 전체 카운트 */
    $cntQuery = "";
    $cntQuery = "CALL get_MapCnt(:lon, :lat, :lon2, :lat2, :mbr_length, :smbr_length) ";
    $cntStmt = $DB_pcon->prepare($cntQuery);
    $cntStmt->bindparam(":lon", $startLng, PDO::PARAM_STR);
    $cntStmt->bindparam(":lat", $startLat, PDO::PARAM_STR);
    $cntStmt->bindparam(":lon2", $endLng, PDO::PARAM_STR);
    $cntStmt->bindparam(":lat2", $endLat, PDO::PARAM_STR);
    $cntStmt->bindparam(":mbr_length", $chkDistance, PDO::PARAM_INT);
    $cntStmt->bindparam(":smbr_length", $schkDistance, PDO::PARAM_INT);
    $cntStmt->execute();
    $totalCnt = $cntStmt->rowCount();

    $cntStmt = null;

    if ($totalCnt == "") {
        $totalCnt = "0";
    } else {
        $totalCnt =  $totalCnt;
    }

    /* 매칭대기 목록 */
    $viewQuery = "";
    $viewQuery = "CALL get_MapList(:lon, :lat, :lon2, :lat2, :mbr_length, :smbr_length, :sort) ";
    $viewStmt = $DB_pcon->prepare($viewQuery);
    $viewStmt->bindparam(":lon", $startLng, PDO::PARAM_STR);
    $viewStmt->bindparam(":lat", $startLat, PDO::PARAM_STR);
    $viewStmt->bindparam(":lon2", $endLng, PDO::PARAM_STR);
    $viewStmt->bindparam(":lat2", $endLat, PDO::PARAM_STR);
    $viewStmt->bindparam(":mbr_length", $chkDistance, PDO::PARAM_INT);
    $viewStmt->bindparam(":smbr_length", $schkDistance, PDO::PARAM_INT);
    $viewStmt->bindparam(":sort", $sort, PDO::PARAM_STR);
    $viewStmt->execute();
    $num = $viewStmt->rowCount();

    if ($num < 1) { //아닐경우

        $result = array("result" => true, "totCnt" => (int)$totalCnt, "data" => []);
        echo json_encode($result, JSON_UNESCAPED_UNICODE);
    } else {

        $data  = [];

        while ($row = $viewStmt->fetch(PDO::FETCH_ASSOC)) {

            $idx = trim($row['idx']);                // 고유번호
            $taxiMemIdx =  trim($row['taxi_MemIdx']);                    //회원고유번호
            $dist = trim($row['dist']);                // 상대방과 나 사이 거리
            $dist2 = trim($row['dist2']);            // 상대방 목적지 내 목적지 거리
            $chkDate = trim($row['chkDate']);          //  30분전
            $chkDate2 = trim($row['chkDate2']);  //  30분후
            $taxiPrice = trim($row['taxi_Price']);    // 희망쉐어링 요금
            $taxi_Per = trim($row['taxi_Per']);        // 쉐어링 적용률
            $taxiMemo = trim($row['taxi_Memo']);

            //상대방과 나 사이 거리
            if ($dist <= "1000") {
                $taxiSDistance = $dist;    // 미터
                $lineSDistance = round($taxiSDistance, 0);    // 미터를 km로 변환
            } else {
                $taxiSDistance = $dist / 1000.0;
                $lineSDistance = round($taxiSDistance, 0);    // 미터를 km로 변환
            }


            //상대방 목적지 내 목적지 거리
            if ($dist2 <= "1000") {
                $taxiDistance = $dist2;    // 미터
                $lineRDistance = round($taxiDistance, 0) . "m";    // 미터를 km로 변환
            } else {
                $taxiDistance = $dist2 / 1000.0;
                $lineRDistance = round($taxiDistance, 0) . "km";    // 미터를 km로 변환
            }


            //생성 정보
            $infoQuery = "";
            $infoQuery = "SELECT taxi_Type, taxi_Distance, taxi_Route FROM TB_STAXISHARING_INFO WHERE taxi_Idx = :taxi_Idx LIMIT 1 ";
            //echo $infoQuery."<BR>";
            //exit;
            $infoStmt = $DB_con->prepare($infoQuery);
            $infoStmt->bindparam(":taxi_Idx", $idx);
            $infoStmt->execute();
            $infoNum = $infoStmt->rowCount();
            //echo $infoNum."<BR>";

            if ($infoNum < 1) { //아닐경우
            } else {
                while ($infoRow = $infoStmt->fetch(PDO::FETCH_ASSOC)) {
                    $taxiType =  trim($infoRow['taxi_Type']);                    //출발타입 ( 0: 바로출발, 1: 예약출발)
                    $taxi_Route =  trim($infoRow['taxi_Route']);                // 경유가능여부 ( 0: 경유가능, 1: 경유불가)
                }
            }


            if ($taxi_Route == "0") { //경유 가능일 경우
                $taxiRPrice = $taxiRTPrice * ($taxiPer / 100);   //요청자 요금
                //echo $taxiRPrice."<BR>";
                if ($taxiRPrice > $taxiPrice) { // 요청자금액이 클 경우
                    $taxiPrice = $taxiRPrice;         // 요청자(결제금액)
                } else {
                    $taxiPrice = $taxiPrice;         // 희망쉐어금액(결제금액)
                }
            } else {
                $taxiPrice = $taxiPrice;                      // 택시 희망 쉐어링 비용
            }

            $taxiPrice = (int)$taxiPrice;
            if ($taxiType == "0") { //바로출발 일 경우
                $chkDate = $chkDate2;             //30분후 시간
            } else {
                $chkDate = $chkDate;     //30분전 시간
            }
            //생성 지도정보
            $mapQuery = "";
            $mapQuery = "SELECT taxi_SLat, taxi_SLng, taxi_ELat, taxi_ELng, taxi_Sdong, taxi_Saddr, taxi_SaddrNm, taxi_Edong, taxi_Eaddr, taxi_EaddrNm FROM TB_STAXISHARING_MAP WHERE taxi_Idx = :taxi_Idx  LIMIT 1 ";
            //echo $mapQuery."<BR>";
            //exit;

            $mapStmt = $DB_con->prepare($mapQuery);
            $mapStmt->bindparam(":taxi_Idx", $idx);
            $mapStmt->execute();
            $mapNum = $mapStmt->rowCount();

            if ($mapNum < 1) { //아닐경우
            } else {
                while ($mapRow = $mapStmt->fetch(PDO::FETCH_ASSOC)) {
                    $taxiSdong = trim($mapRow['taxi_Sdong']);                      //  출발지 동명
                    $taxiSaddrNm = trim($mapRow['taxi_SaddrNm']);
                    if ($taxiSaddrNm != "") {
                        $taxiSdong = $taxiSaddrNm;                      //  도착지 동명
                    } else {
                        $taxiSdong = trim($mapRow['taxi_Sdong']);                      //  도착지 동명
                    }
                    $taxiEaddrNm = trim($mapRow['taxi_EaddrNm']);
                    if ($taxiEaddrNm != "") {
                        $taxiEdong = $taxiEaddrNm;                      //  도착지 동명
                    } else {
                        $taxiEdong = trim($mapRow['taxi_Edong']);                      //  도착지 동명
                    }
                    $taxiSaddr = trim($mapRow['taxi_Saddr']);
                    $taxiEaddr = trim($mapRow['taxi_Eaddr']);
                    $taxiSLat = trim($mapRow['taxi_SLat']);
                    $taxiSLng = trim($mapRow['taxi_SLng']);
                    $taxiELat = trim($mapRow['taxi_ELat']);
                    $taxiELng = trim($mapRow['taxi_ELng']);
                }
            }

            $memChkQuery = "SELECT A.mem_CharBit, A.mem_CharIdx, B.mem_profile_update FROM TB_MEMBERS AS A LEFT OUTER JOIN TB_MEMBER_PHOTO AS B ON A.idx = B.mem_Idx WHERE A.idx = :taxi_MemIdx";
            $memChkStmt = $DB_con->prepare($memChkQuery);
            $memChkStmt->bindparam(":taxi_MemIdx", $taxiMemIdx);
            $memChkStmt->execute();
            $memChkNum = $memChkStmt->rowCount();
            if ($memChkNum < 1) { //아닐경우
            } else {
                while ($memChkRow = $memChkStmt->fetch(PDO::FETCH_ASSOC)) {

                    $mem_CharBit = $memChkRow['mem_CharBit'];            // 캐릭터프로필 선택 여부( 0: 미선택, 1: 선택)
                    $mem_CharIdx = $memChkRow['mem_CharIdx'];            // 캐릭터프로필 고유번호
                    $mem_profile_update = trim($memChkRow['mem_profile_update']);

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
                        if ($mem_profile_update == '') {
                            $memImgFile = '';
                        } else {
                            $memImgFile = '/data/member/photo.php?id=' . $mem_profile_update;
                        }
                    }
                }
            }
            //, "taxiSdong" => (string)$taxiSdong, "taxiSaddrNm" => (string)$taxiSaddrNm, "lineSDistance" => (int)$lineSDistance, "lineRDistance" => (int)$lineRDistance, "taxiSLat" => (float)$taxiSLat, "taxiSLng" => (float)$taxiSLng, "taxiELat" => (float)$taxiELat, "taxiELng" => (float)$taxiELng, "taxiEaddr" => (string)$taxiEaddr
            $result = ["idx" => (int)$idx, "memImgFile" => (string)$memImgFile, "taxiMemo" => (string)$taxiMemo, "taxiEaddrNm" => (string)$taxiEaddrNm, "taxiPrice" => (int)$taxiPrice, "chkDate" => (string)$chkDate];
            array_push($data, $result);
        }


        $chkData["result"] = true;
        $chkData["totCnt"] = (int)$totalCnt;  //현재카운트
        $chkData['data'] = $data;
        $output = str_replace('\\\/', '/', json_encode($chkData, JSON_UNESCAPED_UNICODE + JSON_PRETTY_PRINT));
    }

    echo urldecode($output);

    dbClose($DB_con);
    dbClose($DB_pcon);
    $mapStmt = null;
    $viewStmt = null;
} else {
    $result = array("result" => false, "errorMsg" => "조회 정보값이 없습니다. 관리자에게 문의바랍니다.");
    echo json_encode($result, JSON_UNESCAPED_UNICODE);
}
