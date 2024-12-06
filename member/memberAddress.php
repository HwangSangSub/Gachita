<?
include "../lib/common.php";
include "../lib/functionDB.php";            //공통 db함수
include "../order/lib/tpay_proc.php";       // 아임포트 함수

$mem_Id = trim($memId);                     //회원아이디
$mem_Idx = memIdxInfo($mem_Id);             //회원 주아이디

$addr_Idx = trim($addrIdx);                 //주소고유번호    

$mode = trim($mode);                        //모드(sel : 조회, reg : 등록, mod : 수정, del : 삭제)

$mem_AddrNickNm = trim($memAddrNickNm);     // 별칭
$mem_AddrNm = trim($memAddrNm);             // 주소 명
$mem_Addr = trim($memAddr);                 // 주소
$mem_Dong = trim($memDong);                 // 동명
$mem_Lat = trim($memLat);                   // 위도
$mem_Lng = trim($memLng);                   // 경도

$tmap_key = "l7xx9dc45675484b429189bdddc5f4885e5d";

$DB_con = db1();
if ($mem_Id != "") {  //아이디가 있을 경우

    // 주소 최대 등록 수 확인하기.
    $configQuery = "SELECT con_AddrMaxCnt FROM TB_CONFIG";
    $configStmt = $DB_con->prepare($configQuery);
    $configStmt->execute();
    $configRow = $configStmt->fetch(PDO::FETCH_ASSOC);
    $con_AddrMaxCnt = trim($configRow['con_AddrMaxCnt']);
    $addr_Cnt = 0;
    if ($mode == "reg") {
        $memAddrCntQuery = "SELECT COUNT(idx) AS addr_Cnt FROM TB_MEMBERS_MAP WHERE mem_Idx = :mem_Idx";
        $memAddrCntStmt = $DB_con->prepare($memAddrCntQuery);
        $memAddrCntStmt->bindparam(":mem_Idx", $mem_Idx);
        $memAddrCntStmt->execute();
        $memAddrCntRow = $memAddrCntStmt->fetch(PDO::FETCH_ASSOC);
        $addr_Cnt = trim($memAddrCntRow['addr_Cnt']);
        if ((int)$addr_Cnt >= (int)$con_AddrMaxCnt) {
            $result = array("result" => false, "errorMsg" => "더 이상 등록할 수 없습니다. 최대 등록 가능 수는 " . number_format($con_AddrMaxCnt) . "개 입니다.");
        } else {
            $addrInsQuery = "INSERT INTO TB_MEMBERS_MAP SET mem_Idx = :mem_Idx, mem_Id = :mem_Id, mem_AddrNickNm = :mem_AddrNickNm, mem_AddrNm = :mem_AddrNm, mem_Addr = :mem_Addr, mem_Dong = :mem_Dong, mem_Lat = :mem_Lat, mem_Lng = :mem_Lng";
            $addrInsStmt = $DB_con->prepare($addrInsQuery);
            $addrInsStmt->bindparam(":mem_Idx", $mem_Idx);
            $addrInsStmt->bindparam(":mem_Id", $mem_Id);
            $addrInsStmt->bindparam(":mem_AddrNickNm", $mem_AddrNickNm);
            $addrInsStmt->bindparam(":mem_AddrNm", $mem_AddrNm);
            $addrInsStmt->bindparam(":mem_Addr", $mem_Addr);
            $addrInsStmt->bindparam(":mem_Dong", $mem_Dong);
            $addrInsStmt->bindparam(":mem_Lat", $mem_Lat);
            $addrInsStmt->bindparam(":mem_Lng", $mem_Lng);
            $addrInsStmt->execute();

            $result = array("result" => true);

            if ($mem_AddrNickNm == "집") {
                $tmap_res = tmap_Api('https://apis.openapi.sk.com/tmap/geo/reversegeocoding', array("version" => "1", "lat" => $mem_Lat, "lon" => $mem_Lng, "coordType" => "WGS84GEO", "addressType" => "A02", "newAddressExtend" => "Y"), $tmap_key);
                $city_do = $tmap_res["addressInfo"]["city_do"];
                $gu_gun = $tmap_res["addressInfo"]["gu_gun"];
                $do = "";
                $si = "";
                switch ($city_do) {
                    case "서울특별시":
                        $do = "경기도";
                        $si = $city_do;
                        break;
                    case "인천광역시":
                        $do = "경기도";
                        $si = $city_do;
                        break;
                    case "대전광역시":
                        $do = "충청남도";
                        $si = $city_do;
                        break;
                    case "세종특별자치시":
                        $do = "충청남도";
                        $si = $city_do;
                        break;
                    case "광주광역시":
                        $do = "전라남도";
                        $si = $city_do;
                        break;
                    case "대구광역시":
                        $do = "경상북도";
                        $si = $city_do;
                        break;
                    case "울산광역시":
                        $do = "경상북도";
                        $si = $city_do;
                        break;
                    case "부산광역시":
                        $do = "경상남도";
                        $si = $city_do;
                        break;
                    default:
                        $do = $city_do;
                        $si = $gu_gun;
                        break;
                }
                // 통계 시도 구분 값 확인
                $dosiChkQuery = "SELECT addr_Cnt FROM TB_ADDR_STAT WHERE do = :do AND si = :si";
                $dosiChkStmt = $DB_con->prepare($dosiChkQuery);
                $dosiChkStmt->bindparam(":do", $do);
                $dosiChkStmt->bindparam(":si", $si);
                $dosiChkStmt->execute();
                $dosiChkCnt = $dosiChkStmt->rowCount();

                if ($dosiChkCnt > 0) {
                    $dosiChkRow = $dosiChkStmt->fetch(PDO::FETCH_ASSOC);
                    $addr_Cnt = $dosiChkRow['addr_Cnt'];                        // 주소 통계
                    $statCnt = (int)$addr_Cnt + 1;

                    // 지역 수 증가
                    $dosiUpQuery = "UPDATE TB_ADDR_STAT SET addr_Cnt = :addr_Cnt, stat_Date = NOW() WHERE do = :do AND si = :si LIMIT 1";
                    $dosiUpStmt = $DB_con->prepare($dosiUpQuery);
                    $dosiUpStmt->bindparam(":addr_Cnt", $statCnt);
                    $dosiUpStmt->bindparam(":do", $do);
                    $dosiUpStmt->bindparam(":si", $si);
                    $dosiUpStmt->execute();
                } else {
                    // 지역 최초 추가
                    $dosiInsQuery = "INSERT INTO TB_ADDR_STAT SET do = :do, si = :si, addr_Cnt = 1, reg_Date = NOW(), stat_Date = NOW()";
                    $dosiInsStmt = $DB_con->prepare($dosiInsQuery);
                    $dosiInsStmt->bindparam(":do", $do);
                    $dosiInsStmt->bindparam(":si", $si);
                    $dosiInsStmt->execute();
                }
            }
        }
    } else if ($mode == "mod") {
        $memAddrChkQuery = "SELECT idx FROM TB_MEMBERS_MAP WHERE idx = :addr_Idx";
        $memAddrChkStmt = $DB_con->prepare($memAddrChkQuery);
        $memAddrChkStmt->bindparam(":addr_Idx", $addr_Idx);
        $memAddrChkStmt->execute();
        $memAddrChkNum = $memAddrChkStmt->rowCount();

        if ($memAddrChkNum < 1) { //아닐경우
            $result = array("result" => false, "errorMsg" => "등록된 즐겨찾기가 아닙니다. 확인 후 다시 시도해주세요.");
        } else {
            $addrUpQuery = "UPDATE TB_MEMBERS_MAP SET mem_AddrNickNm = :mem_AddrNickNm, mem_AddrNm = :mem_AddrNm, mem_Addr = :mem_Addr, mem_Dong = :mem_Dong, mem_Lat = :mem_Lat, mem_Lng = :mem_Lng WHERE mem_Idx = :mem_Idx AND idx = :addr_Idx LIMIT 1";
            $addrUpStmt = $DB_con->prepare($addrUpQuery);
            $addrUpStmt->bindparam(":mem_AddrNickNm", $mem_AddrNickNm);
            $addrUpStmt->bindparam(":mem_AddrNm", $mem_AddrNm);
            $addrUpStmt->bindparam(":mem_Addr", $mem_Addr);
            $addrUpStmt->bindparam(":mem_Dong", $mem_Dong);
            $addrUpStmt->bindparam(":mem_Lat", $mem_Lat);
            $addrUpStmt->bindparam(":mem_Lng", $mem_Lng);
            $addrUpStmt->bindparam(":mem_Idx", $mem_Idx);
            $addrUpStmt->bindparam(":addr_Idx", $addr_Idx);
            $addrUpStmt->execute();

            $result = array("result" => true);

            if ($mem_AddrNickNm == "집") {
                $tmap_res = tmap_Api('https://apis.openapi.sk.com/tmap/geo/reversegeocoding', array("version" => "1", "lat" => $mem_Lat, "lon" => $mem_Lng, "coordType" => "WGS84GEO", "addressType" => "A02", "newAddressExtend" => "Y"), $tmap_key);
                $city_do = $tmap_res["addressInfo"]["city_do"];
                $gu_gun = $tmap_res["addressInfo"]["gu_gun"];
                $do = "";
                $si = "";
                switch ($city_do) {
                    case "서울특별시":
                        $do = "경기도";
                        $si = $city_do;
                        break;
                    case "인천광역시":
                        $do = "경기도";
                        $si = $city_do;
                        break;
                    case "대전광역시":
                        $do = "충청남도";
                        $si = $city_do;
                        break;
                    case "세종특별자치시":
                        $do = "충청남도";
                        $si = $city_do;
                        break;
                    case "광주광역시":
                        $do = "전라남도";
                        $si = $city_do;
                        break;
                    case "대구광역시":
                        $do = "경상북도";
                        $si = $city_do;
                        break;
                    case "울산광역시":
                        $do = "경상북도";
                        $si = $city_do;
                        break;
                    case "부산광역시":
                        $do = "경상남도";
                        $si = $city_do;
                        break;
                    default:
                        $do = $city_do;
                        $si = $gu_gun;
                        break;
                }
                // 통계 시도 구분 값 확인
                $dosiChkQuery = "SELECT addr_Cnt FROM TB_ADDR_STAT WHERE do = :do AND si = :si";
                $dosiChkStmt = $DB_con->prepare($dosiChkQuery);
                $dosiChkStmt->bindparam(":do", $do);
                $dosiChkStmt->bindparam(":si", $si);
                $dosiChkStmt->execute();
                $dosiChkCnt = $dosiChkStmt->rowCount();

                if ($dosiChkCnt > 0) {
                    $dosiChkRow = $dosiChkStmt->fetch(PDO::FETCH_ASSOC);
                    $addr_Cnt = $dosiChkRow['addr_Cnt'];                        // 주소 통계
                    $statCnt = (int)$addr_Cnt + 1;

                    // 지역 수 증가
                    $dosiUpQuery = "UPDATE TB_ADDR_STAT SET addr_Cnt = :addr_Cnt, stat_Date = NOW() WHERE do = :do AND si = :si LIMIT 1";
                    $dosiUpStmt = $DB_con->prepare($dosiUpQuery);
                    $dosiUpStmt->bindparam(":addr_Cnt", $statCnt);
                    $dosiUpStmt->bindparam(":do", $do);
                    $dosiUpStmt->bindparam(":si", $si);
                    $dosiUpStmt->execute();
                } else {
                    // 지역 최초 추가
                    $dosiInsQuery = "INSERT INTO TB_ADDR_STAT SET do = :do, si = :si, addr_Cnt = 1, reg_Date = NOW(), stat_Date = NOW()";
                    $dosiInsStmt = $DB_con->prepare($dosiInsQuery);
                    $dosiInsStmt->bindparam(":do", $do);
                    $dosiInsStmt->bindparam(":si", $si);
                    $dosiInsStmt->execute();
                }
            }
        }
    } else if ($mode == "del") {
        $memAddrChkQuery = "SELECT idx FROM TB_MEMBERS_MAP WHERE idx = :addr_Idx";
        $memAddrChkStmt = $DB_con->prepare($memAddrChkQuery);
        $memAddrChkStmt->bindparam(":addr_Idx", $addr_Idx);
        $memAddrChkStmt->execute();
        $memAddrChkNum = $memAddrChkStmt->rowCount();

        if ($memAddrChkNum < 1) { //아닐경우
            $result = array("result" => false, "errorMsg" => "등록된 즐겨찾기가 아닙니다. 확인 후 다시 시도해주세요.");
        } else {
            $addrUpQuery = "DELETE FROM TB_MEMBERS_MAP WHERE mem_Idx = :mem_Idx AND idx = :addr_Idx LIMIT 1";
            $addrUpStmt = $DB_con->prepare($addrUpQuery);
            $addrUpStmt->bindparam(":mem_Idx", $mem_Idx);
            $addrUpStmt->bindparam(":addr_Idx", $addr_Idx);
            $addrUpStmt->execute();

            $result = array("result" => true);
        }
    } else if ($mode == "sel") {
        $memAddrChkQuery = "SELECT idx FROM TB_MEMBERS_MAP WHERE mem_Idx = :mem_Idx";
        $memAddrChkStmt = $DB_con->prepare($memAddrChkQuery);
        $memAddrChkStmt->bindparam(":mem_Idx", $mem_Idx);
        $memAddrChkStmt->execute();
        $memAddrChkNum = $memAddrChkStmt->rowCount();

        if ($memAddrChkNum < 1) { //아닐경우
            $result = array("result" => true, "totCnt" => 0, "lists" => []);
        } else {
            $addrSelQuery = "SELECT idx, mem_AddrNickNm, mem_AddrNm, mem_Addr, mem_Dong, mem_Lat, mem_Lng FROM TB_MEMBERS_MAP WHERE mem_Idx = :mem_Idx";
            $addrSelStmt = $DB_con->prepare($addrSelQuery);
            $addrSelStmt->bindparam(":mem_Idx", $mem_Idx);
            $addrSelStmt->execute();
            $addr_List = [];
            while ($addrSelRow = $addrSelStmt->fetch(PDO::FETCH_ASSOC)) {
                $idx = trim($addrSelRow['idx']);
                $mem_AddrNickNm = trim($addrSelRow['mem_AddrNickNm']);
                $mem_AddrNm = trim($addrSelRow['mem_AddrNm']);
                $mem_Addr = trim($addrSelRow['mem_Addr']);
                $mem_Dong = trim($addrSelRow['mem_Dong']);
                $mem_Lat = trim($addrSelRow['mem_Lat']);
                $mem_Lng = trim($addrSelRow['mem_Lng']);
                $data = ["idx" => (int)$idx, "memAddrNickNm" => (string)$mem_AddrNickNm, "memAddrNm" => (string)$mem_AddrNm, "memAddr" => (string)$mem_Addr,  "memDong" => (string)$mem_Dong, "memLat" => (float)$mem_Lat, "memLng" => (float)$mem_Lng];
                array_push($addr_List, $data);
            }

            $chkData["result"] = true;
            $chkData["totCnt"] = (int)$memAddrChkNum;  //현재카운트
            $chkData['lists'] = $addr_List;
            $output = str_replace('\\\/', '/', json_encode($chkData, JSON_UNESCAPED_UNICODE));
        }
    } else {
        $result = array("result" => false, "errorMsg" => "모드값이 없습니다. 관리자에게 문의바랍니다.");
    }



    dbClose($DB_con);
    $stmt = null;
    $meInfoStmt = null;
    $mEtcStmt = null;
    $mMapStmt = null;
    $chktmt = null;
    $upStmt3 = null;
} else {
    $result = array("result" => false, "errorMsg" => "조회 정보값이 없습니다. 관리자에게 문의바랍니다.");
}
if ($output == "") {
    echo json_encode($result, JSON_UNESCAPED_UNICODE);
} else {
    echo urldecode($output);
}
