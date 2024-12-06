<?
/*======================================================================================================================

* 프로그램			: 매칭 요청자 요청 처리 화면 ( 취소사유확인 및 거래완료 확인 중 요청 생성 불가)
* 페이지 설명		: 매칭 요청자 요청 처리 화면 ( 취소사유확인 및 거래완료 확인 중 요청 생성 불가)
* 파일명                 : taxiSharingMProc.php

========================================================================================================================*/

include "../lib/common.php";
include "../lib/functionDB.php";  //공통 db함수

$mem_Id = trim($memId);                //아이디
$idx = trim($idx);                                    //고유번호


if ($mem_Id != "" && $idx != "") {  //아이디 여부가 경우

    $mem_Idx = memIdxInfo($mem_Id);   //회원 주아이디    

    $taxi_SaddrNm = trim($taxiSaddrNm);          //경유지 검색어

    if ($ie) { //익슬플로러일경우
        $taxiRSaddNm = iconv('euc-kr', 'utf-8', $taxi_SaddrNm);
    } else {
        $taxiRSaddNm = $taxi_SaddrNm;
    }

    $taxiRSaddNm = str_replace("null", "", $taxiRSaddNm);
    //$taxiRSaddNm = str_replace(" ","",$taxiRSaddNm);

    $taxi_Saddr = trim($taxiSaddr);                         //경유지 주소

    if ($ie) { //익슬플로러일경우
        $taxiRSaddr = iconv('euc-kr', 'utf-8', $taxi_Saddr);
    } else {
        $taxiRSaddr = $taxi_Saddr;
    }

    $taxiRSaddr = str_replace("null", "", $taxiRSaddr);
    //$taxiRSaddr = str_replace(" ","",$taxiRSaddr);

    $taxi_RSdong = trim($taxiSdong);                         //경유지 동명

    if ($ie) { //익슬플로러일경우
        $taxiRSdong = iconv('euc-kr', 'utf-8', $taxi_RSdong);
    } else {
        $taxiRSdong = $taxi_RSdong;
    }
    $taxiRSdong = str_replace("null", "", $taxiRSdong);
    //$taxiRSdong = str_replace(" ","",$taxiRSdong);

    $taxiRSLat = $taxiRSLat;   //경유지 구글 위도
    $taxiRSLng = $taxiRSLng;   //경유지 구글 경도
    $taxi_RMCnt = trim($taxiMCnt);        //인원수
    $taxi_RTPrice = trim($taxiRTPrice);        //중간 매칭 거리 택시 요금
    $taxi_RATime = trim($taxiRATime);    //경유지 추가 예상 시간
    $taxi_RUPoint = trim($taxiRUPoint); //요청자 사용 포인트
    if ($taxi_RUPoint == "") {
        $taxi_RUPoint =  0;
    }

    $card_Idx = trim($cardIdx); // 카드고유번호
    if ($card_Idx == "") {
        $card_Idx = "";
    }

    $taxi_RMemo = trim($taxiMemo); //요청자 메모

    $DB_con = db1();

    //현재 매칭 생성자 상태값 체크 (중복인데도 일단 코드를 집어넣습니다.)
    $chkSharing = "SELECT taxi_State , taxi_MemId  FROM TB_STAXISHARING WHERE idx = :idx "; //예약요청완료, 만남중, 이동중
    $chkSharingStmt = $DB_con->prepare($chkSharing);
    $chkSharingStmt->bindparam(":idx", $idx);
    $chkSharingStmt->execute();
    $chkSharingRow =   $chkSharingStmt->fetch(PDO::FETCH_ASSOC);
    $state = $chkSharingRow["taxi_State"];
    $taxiMemId = $chkSharingRow["taxi_MemId"];
    $notAllowedStates = array("4", "5", "6", "7", "8", "9", "10");
    if ($taxiMemId == $mem_Id) {
        $result = array("result" => false, "errorMsg" => "내가 생성한 노선입니다.");
        goto JSON_PRINT;
    }
    if (in_array($state, $notAllowedStates)) {
        $result = array("result" => false, "errorMsg" => "현재 노선을 신청할수 없는 상태입니다.");
        goto JSON_PRINT;
    }

    //현재 매칭 생성자 상태값 체크
    $chkMMCntQuery = "SELECT count(idx) AS num from TB_STAXISHARING WHERE idx = :idx AND taxi_State IN ( '7', '8', '9', '10' ) "; //예약요청완료, 만남중, 이동중
    $chkMCntStmt = $DB_con->prepare($chkMMCntQuery);
    $chkMCntStmt->bindparam(":idx", $idx);
    $chkMCntStmt->execute();
    $chkMCntRow = $chkMCntStmt->fetch(PDO::FETCH_ASSOC);
    $chkMCntNum = $chkMCntRow['num'];

    if ($chkMCntNum <> "") {
        $chkMCntNum = $chkMCntNum;
    } else {
        $chkMCntNum = 0;
    }

    if ($chkMCntNum > 0) { //아닐경우
        $result = array("result" => false, "errorMsg" => "현재 매칭진행 중인 노선이어서 매칭요청을 할 수 없습니다.");
    } else {

        //현재 요청자가 신청한  건수
        $chkCntQuery = "SELECT count(idx) AS num from TB_RTAXISHARING WHERE taxi_RMemId = :taxi_RMemId AND taxi_RState NOT IN ( '4', '5', '6', '7', '8' ) "; //매칭대기, 매칭요청, 예약요청, 완료, 취소, 거절 사용
        $chkCntRStmt = $DB_con->prepare($chkCntQuery);
        $chkCntRStmt->bindparam(":taxi_RMemId", $mem_Id);
        $chkCntRStmt->execute();
        $chkCntRrow = $chkCntRStmt->fetch(PDO::FETCH_ASSOC);
        $chkCntRNum = $chkCntRrow['num'];

        if ($chkCntRNum <> "") {
            $chkCntRNum = $chkCntRNum;
        } else {
            $chkCntRNum = 0;
        }
        //현재 상태값 가져오기
        $chkRQuery = "SELECT taxi_RState from TB_RTAXISHARING WHERE taxi_RMemId = :taxi_RMemId "; //매칭대기, 매칭요청, 예약요청, 완료, 취소, 거절 사용 ;
        $chkRStmt = $DB_con->prepare($chkRQuery);
        $chkRStmt->bindparam(":taxi_RMemId", $mem_Id);
        $chkRStmt->execute();
        $chkRNum = $chkRStmt->rowCount();

        if ($chkRNum < 1) { //아닐경우
        } else {
            while ($chkRrow = $chkRStmt->fetch(PDO::FETCH_ASSOC)) {
                $taxiRState = $chkRrow['taxi_RState'];
                $chkRState[] = $taxiRState;
            }
        }
        $chkTarr = array(1, 2, 3, 7, 8, 9, 10);   //매칭요청 생성 가능 상태
        $chkNarr = array(4, 5, 6);  //매칭요청 생성 불가능 상태

        //매칭요청 생성 가능 체크
        if (is_array($chkRState) == 1) {
            $intens = array_intersect($chkTarr, $chkRState);
            if (isset($intens) == true) {
                $chkArrCnt = count($intens);
            } else {
                $chkArrCnt = 0;
            }
        } else {
            $chkArrCnt = 0;
        }


        if ($chkArrCnt != "") {
            $chkState = "1";
        } else {
            $chkState = "0";
        }

        //매칭요청 생성 불가능 체크
        if (is_array($chkRState) == 1) {
            $nsIntens = array_intersect($chkNarr, $chkRState);

            if (isset($nsIntens) == true) {
                $chkArrNCnt = count($nsIntens);
            } else {
                $chkArrNCnt = 0;
            }
        } else {
            $chkArrNCnt = 0;
        }

        if ($chkArrNCnt != "") {
            $chkNState = "1";

            foreach ($nsIntens as $k => $k_value) {
                $chkRState = $k_value;
            }
        } else {
            $chkNState = "0";
        }


        if ($chkState == "1" && $chkNState == "1") {  //취소사유확인 중이거나 거래완료 확인중인건 체크
            $result = array("result" => false, "errorMsg" => "현재 만남중 혹은 취소사유확인 혹은 거래완료확인 중인 건이 있어서 같이타기를 할 수 없습니다.");
        } else {

            //매칭 중복 건수 비교
            $mstateCntQuery = "SELECT count(idx) AS num FROM TB_RTAXISHARING WHERE taxi_SIdx = :taxi_SIdx AND taxi_RMemId = :taxi_RMemId AND taxi_RState IN ( '1', '2', '3' ) "; //매칭요청, 예약요청, 거절 일 경우만 접수 가능
            $mchkStmt = $DB_con->prepare($mstateCntQuery);
            $mchkStmt->bindparam(":taxi_SIdx", $idx);
            $mchkStmt->bindparam(":taxi_RMemId", $mem_Id);
            $mchkStmt->execute();
            $mchkRow = $mchkStmt->fetch(PDO::FETCH_ASSOC);
            $mchkNum = $mchkRow['num'];

            if ($mchkNum <> "") {
                $mchkNum = $mchkNum;
            } else {
                $mchkNum = 0;
            }

            if ($mchkNum < "1") { //요청이 가능할 경우

                // 매칭거리
                $viewQuery = "";
                $viewQuery = "SELECT taxi_MemIdx, taxi_MemId, taxi_TPrice, taxi_ATime, taxi_State, taxi_SDate, ";
                $viewQuery .= " DATE_ADD(taxi_SDate, INTERVAL -30 MINUTE) AS chkDate, DATE_ADD(taxi_SDate, INTERVAL 30 MINUTE) AS chkDate2 ";
                $viewQuery .= " FROM TB_STAXISHARING WHERE idx = :idx  AND taxi_MemId <> :taxi_MemId ORDER BY idx DESC LIMIT 1 ";
                $viewStmt = $DB_con->prepare($viewQuery);
                $viewStmt->bindparam(":idx", $idx);
                $viewStmt->bindparam(":taxi_MemId", $mem_Id);

                $viewStmt->execute();
                $vnum = $viewStmt->rowCount();

                if ($vnum < "1") { //아닐경우
                    $result = array("result" => false, "errorMsg" => "상대방이 노선을 취소하셨습니다.");
                } else {

                    while ($vrow = $viewStmt->fetch(PDO::FETCH_ASSOC)) {
                        $taxiMemIdx = trim($vrow['taxi_MemIdx']);      //생성자 고유 아이디
                        $taxi_MemId =  trim($vrow['taxi_MemId']);      // 회원아이디
                        $taxi_TPrice = trim($vrow['taxi_TPrice']);      // 처음 생성  목적지 기본 택시요금
                        $taxiATime = trim($vrow['taxi_ATime']);          // 생성자 택시 예상 시간
                        $taxiState = trim($vrow['taxi_State']);          //상태값
                        $taxiSDate = trim($vrow['taxi_SDate']);          //생성시간
                        $chkDate = trim($vrow['chkDate']);              //예약전 30분
                        $chkDate2 = trim($vrow['chkDate2']);          //예약후 30분
                    }

                    //시간정보 가져오기
                    $shInfoQuery = "SELECT taxi_Type, taxi_Route, taxi_Distance FROM TB_STAXISHARING_INFO  WHERE taxi_Idx = :taxi_Idx AND taxi_MemId = :taxi_MemId  ";
                    $shInfoSmt = $DB_con->prepare($shInfoQuery);
                    $shInfoSmt->bindparam(":taxi_Idx", $idx);
                    $shInfoSmt->bindparam(":taxi_MemId", $taxi_MemId);
                    $shInfoSmt->execute();
                    $shInfoNum = $shInfoSmt->rowCount();

                    if ($shInfoNum < 1) { //아닐경우
                    } else {
                        while ($shInfoRow = $shInfoSmt->fetch(PDO::FETCH_ASSOC)) {
                            $taxiType = trim($shInfoRow['taxi_Type']);      //출발타입 ( 0: 바로출발, 1: 예약출발 )
                            $taxiRoute = trim($shInfoRow['taxi_Route']);     // 경유가능여부 ( 0: 경유가능, 1: 경유불가)
                            $taxiSDistance = trim($shInfoRow['taxi_Distance']);     // 생성자 총거리
                        }
                    }

                    $taxiRDistance = $taxiDistance;                //나와의 예상 거리
                    if ($taxiRDistance == '') {
                        $taxiRDistance = 0;
                    }
                    if ($taxiRoute == "1") {  //경유불가

                        //생성자 지도 가져오기
                        $smapQuery = "SELECT taxi_SaddrNm, taxi_Saddr, taxi_Sdong, taxi_SLng, taxi_SLat  FROM TB_STAXISHARING_MAP  WHERE taxi_Idx = :taxi_Idx AND taxi_MemId = :taxi_MemId  ";
                        $smapSmt = $DB_con->prepare($smapQuery);
                        $smapSmt->bindparam(":taxi_Idx", $idx);
                        $smapSmt->bindparam(":taxi_MemId", $taxi_MemId);
                        $smapSmt->execute();
                        $smapNum = $smapSmt->rowCount();

                        if ($smapNum < 1) { //아닐경우
                        } else {
                            while ($smapRow = $smapSmt->fetch(PDO::FETCH_ASSOC)) {
                                $taxiAaddrNm = trim($smapRow['taxi_SaddrNm']);            //출발지검색명
                                $taxiAaddr = trim($smapRow['taxi_Saddr']);                        //출발지주소
                                $taxiAdong = trim($smapRow['taxi_Sdong']);                     //출발지 동명
                                $taxi_SLng = trim($smapRow['taxi_SLng']);                         //출발지 구글 경도
                                $taxi_SLat = trim($smapRow['taxi_SLat']);                            //출발지 구글 위도
                            }
                        }
                    }


                    if ($taxiRoute == "0") {  //경유가능
                        $taxi_RATime = $taxi_RATime;        //총 경유지  예상 시간
                        $taxiTDistance = $taxiTDistance;   //총거리
                        $taxiRSaddNm = $taxiRSaddNm;    //경유지 검색명
                        $taxiRSaddr = $taxiRSaddr;                //경유지 주소
                        $taxiRSdong = $taxiRSdong;                //경유지 동명
                        $taxiRSLat = $taxiRSLat;                    //경유지 구글 위도
                        $taxiRSLng = $taxiRSLng;                    //경유지 구글 경도
                        //경유지가 없음(건너띄기 한 경우)
                        if ($taxiRSaddr == '') {
                            $taxiRTPrice =  $taxi_TPrice;        //총 거리 택시 요금
                        } else {
                            $taxiRTPrice =  $taxi_RTPrice;        //총 거리 택시 요금
                        }
                    } else { //경유불가
                        $taxiRTPrice =  $taxi_TPrice;                    //총 거리 택시 요금
                        $taxi_RATime = $taxiATime;                    //총 경유지  예상 시간
                        $taxiTDistance = $taxiSDistance;           //총거리
                        $taxiRSaddNm = $taxiAaddrNm;            //경유지 검색명
                        $taxiRSaddr = $taxiAaddr;                        //경유지 주소
                        $taxiRSdong = $taxiAdong;                //경유지 동명
                        $taxiRSLat = $taxi_SLat;                    //경유지 구글 위도
                        $taxiRSLng = $taxi_SLng;                    //경유지 구글 경도
                    }

                    $reg_Date = DU_TIME_YMDHIS;           //등록일

                    //회원 고유 아이디
                    $memRQuery = "SELECT mem_Os from TB_MEMBERS WHERE mem_Id = :mem_Id AND b_Disply = 'N' ";
                    $memRStmt = $DB_con->prepare($memRQuery);
                    $memRStmt->bindparam(":mem_Id", $mem_Id);
                    $memRStmt->execute();
                    $memRNum = $memRStmt->rowCount();

                    if ($memRNum < 1) { //아닐경우
                    } else {
                        while ($memRRow = $memRStmt->fetch(PDO::FETCH_ASSOC)) {
                            $taxiROs = $memRRow['mem_Os'];  // os구분  (0 : 안드로이드, 1: 아이폰)
                        }
                    }

                    //정보값 가져오기
                    $mnSql = "  , ( SELECT mem_NickNm FROM TB_MEMBERS WHERE TB_MEMBERS.mem_Id = TB_MEMBERS_INFO.mem_Id AND TB_MEMBERS.b_Disply = 'N' limit 1 ) AS memNickNm  ";
                    $memQuery = "";
                    $memQuery = "SELECT mem_Sex, mem_Seat {$mnSql} FROM TB_MEMBERS_INFO WHERE mem_Id = :mem_Id ";
                    $memStmt = $DB_con->prepare($memQuery);
                    $memStmt->bindparam(":mem_Id", $mem_Id);
                    $memStmt->execute();
                    $memNum = $memStmt->rowCount();

                    if ($memNum < 1) { //아닐경우
                    } else {
                        while ($memRow = $memStmt->fetch(PDO::FETCH_ASSOC)) {
                            $taxi_RSex = $memRow['mem_Sex'];     //성별 ( 0: 남자, 1: 여자)
                            $taxi_RSeat = $memRow['mem_Seat'];     //좌석 ( 0: 앞좌석, 1: 뒷좌석)
                            $memNickNm = $memRow['memNickNm'];     //요청자 닉네임
                        }
                    }

                    if ($taxiType == "1") { //예약출발일경우
                        if (DU_TIME_YMDHIS < $chkDate) { //출발 30분전일경우
                            $chkState = "3";     //예약요청상태 변경
                        } else {
                            $chkState = "3";     //예약요청상태 변경
                        }
                        $taxi_RState = "2"; //예약요청
                        $statNm = "예약노선에";
                    } else {
                        $chkState = "2";     //매칭요청상태 변경
                        $taxi_RState = "1";  //매칭요청
                        $statNm = "생성노선에";
                    }
                    //생성자 상태값 변경
                    if ($taxiState == "1") {  //매칭중 상태일 경우만 저장
                        $upQquery = "UPDATE TB_STAXISHARING SET taxi_State = :taxi_State WHERE idx = :idx LIMIT 1";
                        $upStmt = $DB_con->prepare($upQquery);
                        $upStmt->bindparam(":taxi_State", $chkState);
                        $upStmt->bindparam(":idx", $idx);
                        $upStmt->execute();
                    }

                    if ($taxi_RATime == "") {
                        $taxi_RATime = 0;
                    }
                    //쉐어링 요청 기본테이블
                    if ($card_Idx == "") {
                        $insQuery = "INSERT INTO TB_RTAXISHARING (taxi_SIdx, taxi_MemIdx, taxi_RMemIdx, taxi_MemId, taxi_RMemId, taxi_RSaddr, taxi_RTPrice, taxi_RUPoint, taxi_RMemo, taxi_RATime, taxi_RDistance, taxi_TDistance, taxi_RState, taxi_ROs, reg_Date)
                        VALUES (:taxi_SIdx, :taxi_MemIdx, :taxi_RMemIdx, :taxi_MemId, :taxi_RMemId, :taxi_RSaddr, :taxi_RTPrice, :taxi_RUPoint, :taxi_RMemo, :taxi_RATime, :taxi_RDistance, :taxi_TDistance, :taxi_RState, :taxi_ROs, :reg_Date)";
                        // echo $insQuery."<BR>";
                        // exit;

                        $stmt = $DB_con->prepare($insQuery);
                        $stmt->bindParam("taxi_SIdx", $idx);
                        $stmt->bindParam("taxi_MemIdx", $taxiMemIdx);
                        $stmt->bindParam("taxi_RMemIdx", $mem_Idx);
                        $stmt->bindParam("taxi_MemId", $taxi_MemId);
                        $stmt->bindParam("taxi_RMemId", $mem_Id);
                        $stmt->bindParam("taxi_RSaddr", $taxiRSaddr);
                        $stmt->bindParam("taxi_RTPrice", $taxiRTPrice);
                        $stmt->bindParam("taxi_RUPoint", $taxi_RUPoint);
                        $stmt->bindParam("taxi_RMemo", $taxi_RMemo);
                        $stmt->bindParam("taxi_RATime", $taxi_RATime);
                        $stmt->bindParam("taxi_RDistance", $taxiRDistance);
                        $stmt->bindParam("taxi_TDistance", $taxiTDistance);
                        $stmt->bindParam("taxi_RState", $taxi_RState);
                        $stmt->bindParam("taxi_ROs", $taxiROs);
                        $stmt->bindParam("reg_Date", $reg_Date);
                        $stmt->execute();
                    } else {
                        $insQuery = "INSERT INTO TB_RTAXISHARING (taxi_SIdx, taxi_MemIdx, taxi_RMemIdx, taxi_MemId, taxi_RMemId, taxi_RSaddr, taxi_RTPrice, taxi_RUPoint, taxi_RMemo, taxi_CardIdx, taxi_RATime, taxi_RDistance, taxi_TDistance, taxi_RState, taxi_ROs, reg_Date)
                    VALUES (:taxi_SIdx, :taxi_MemIdx, :taxi_RMemIdx, :taxi_MemId, :taxi_RMemId, :taxi_RSaddr, :taxi_RTPrice, :taxi_RUPoint, :taxi_RMemo, :taxi_CardIdx, :taxi_RATime, :taxi_RDistance, :taxi_TDistance, :taxi_RState, :taxi_ROs, :reg_Date)";

                        $stmt = $DB_con->prepare($insQuery);
                        $stmt->bindParam("taxi_SIdx", $idx);
                        $stmt->bindParam("taxi_MemIdx", $taxiMemIdx);
                        $stmt->bindParam("taxi_RMemIdx", $mem_Idx);
                        $stmt->bindParam("taxi_MemId", $taxi_MemId);
                        $stmt->bindParam("taxi_RMemId", $mem_Id);
                        $stmt->bindParam("taxi_RSaddr", $taxiRSaddr);
                        $stmt->bindParam("taxi_RTPrice", $taxiRTPrice);
                        $stmt->bindParam("taxi_RUPoint", $taxi_RUPoint);
                        $stmt->bindParam("taxi_RMemo", $taxi_RMemo);
                        $stmt->bindParam("taxi_CardIdx", $card_Idx);
                        $stmt->bindParam("taxi_RATime", $taxi_RATime);
                        $stmt->bindParam("taxi_RDistance", $taxiRDistance);
                        $stmt->bindParam("taxi_TDistance", $taxiTDistance);
                        $stmt->bindParam("taxi_RState", $taxi_RState);
                        $stmt->bindParam("taxi_ROs", $taxiROs);
                        $stmt->bindParam("reg_Date", $reg_Date);
                        $stmt->execute();
                    }
                    $DB_con->lastInsertId();

                    $mIdx = $DB_con->lastInsertId(); //파이어베이스에 값던져 주기 위해서 추가해줌

                    if ($stmt->rowCount() > 0) { //삽입 성공

                        //쉐어링 매칭요청 정보테이블
                        $insInFoQuery = "INSERT INTO TB_RTAXISHARING_INFO (taxi_SIdx, taxi_RIdx, taxi_MemIdx, taxi_RMemIdx, taxi_MemId, taxi_RMemId, taxi_RMCnt, taxi_RSex, taxi_RSeat)
                       VALUES (:taxi_SIdx, :taxi_RIdx, :taxi_MemIdx, :taxi_RMemIdx, :taxi_MemId, :taxi_RMemId, :taxi_RMCnt, :taxi_RSex, :taxi_RSeat)";
                        $stmtInfo = $DB_con->prepare($insInFoQuery);
                        $stmtInfo->bindParam("taxi_SIdx", $idx);
                        $stmtInfo->bindParam("taxi_RIdx", $mIdx);
                        $stmtInfo->bindParam("taxi_MemIdx", $taxiMemIdx);
                        $stmtInfo->bindParam("taxi_RMemIdx", $mem_Idx);
                        $stmtInfo->bindParam("taxi_MemId", $taxi_MemId);
                        $stmtInfo->bindParam("taxi_RMemId", $mem_Id);
                        $stmtInfo->bindParam("taxi_RMCnt", $taxi_RMCnt);
                        $stmtInfo->bindParam("taxi_RSex", $taxi_RSex);
                        $stmtInfo->bindParam("taxi_RSeat", $taxi_RSeat);
                        $stmtInfo->execute();

                        //쉐어링 매칭요청 주소 테이블
                        $insMapQuery = "INSERT INTO TB_RTAXISHARING_MAP (taxi_SIdx, taxi_RIdx, taxi_MemIdx, taxi_RMemIdx, taxi_MemId, taxi_RMemId, taxi_RSaddNm, taxi_RSaddr, taxi_RSdong, taxi_RSLat, taxi_RSLng)
                       VALUES (:taxi_SIdx, :taxi_RIdx, :taxi_MemIdx, :taxi_RMemIdx, :taxi_MemId, :taxi_RMemId, :taxi_RSaddNm, :taxi_RSaddr, :taxi_RSdong, :taxi_RSLat, :taxi_RSLng)";
                        $stmtMap = $DB_con->prepare($insMapQuery);
                        $stmtMap->bindParam("taxi_SIdx", $idx);
                        $stmtMap->bindParam("taxi_RIdx", $mIdx);
                        $stmtMap->bindParam("taxi_MemIdx", $taxiMemIdx);
                        $stmtMap->bindParam("taxi_RMemIdx", $mem_Idx);
                        $stmtMap->bindParam("taxi_MemId", $taxi_MemId);
                        $stmtMap->bindParam("taxi_RMemId", $mem_Id);
                        $stmtMap->bindParam("taxi_RSaddNm", $taxiRSaddNm);
                        $stmtMap->bindParam("taxi_RSaddr", $taxiRSaddr);
                        $stmtMap->bindParam("taxi_RSdong", $taxiRSdong);
                        $stmtMap->bindParam("taxi_RSLat", $taxiRSLat);
                        $stmtMap->bindParam("taxi_RSLng", $taxiRSLng);
                        $stmtMap->execute();
                        
                        //미션 확인하기. (투게더로 요청하기)
                        togetherRoom($mem_Idx, $idx, $mIdx);

                        //첫번째 미션인지 확인하기.
                        $togetherRoomChk = togetherRoomChk($mem_Idx, $idx, $mIdx);

                        //미션 확인하기. (메이커 요청받기)
                        makerTogetherRoom($taxiMemIdx, $idx, $taxi_RState);

                        $result = array("result" => true, "idx" => (int)$mIdx, "togetherRoomChk" => $togetherRoomChk);

                        // 투게더에게 신청받은 경우 메이커에게 푸시 발송
                        $mem_Token = memMatchTokenInfo($taxiMemIdx);
                        $title = "";
                        $msg = $statNm . " 요청을 신청받았습니다.";
                        foreach ($mem_Token as $k => $v) {
                            $tokens = $mem_Token[$k];
                            $inputData = array("title" => $title, "msg" => $msg, "state" => $taxi_RState);
                            $presult = send_Push($tokens, $inputData);
                        }

                    } else { //등록시 에러
                        $result = array("result" => false, "errorMsg" => "매칭 요청중 에러가 발생했습니다. 관리자에게 문의해주세요.");
                    }
                }

                dbClose($DB_con);
                $chkRPointStmt = null;
                $chkMCntStmt = null;
                $chkCntRStmt = null;
                $mchkStmt = null;
                $viewStmt = null;
                $shInfoSmt = null;
                $smapSmt = null;
                $memRStmt = null;
                $memStmt = null;
                $upStmt = null;
                $stmt = null;
                $chkStmt = null;
                $stmtInfo = null;
                $stmtMap = null;
            } else {
                //현재 상태값 가져오기
                $chkRQuery = "SELECT taxi_RState FROM TB_RTAXISHARING WHERE taxi_RMemId = :taxi_RMemId AND taxi_RState NOT IN ( '4', '5', '6', '7', '8', '9', '10' ) "; //매칭대기, 매칭요청, 예약요청, 완료, 취소, 거절 사용 ;
                $chkRStmt = $DB_con->prepare($chkRQuery);
                $chkRStmt->bindparam(":taxi_RMemId", $mem_Id);
                $chkRStmt->execute();
                $chkRNum = $chkRStmt->rowCount();

                if ($chkRNum <> "") {
                    $chkRNum = 1;
                } else {
                    $chkRNum = 0;
                }

                if ($chkRNum < 1) { //아닐경우
                } else {
                    while ($chkRrow = $chkRStmt->fetch(PDO::FETCH_ASSOC)) {
                        $taxiRState = $chkRrow['taxi_RState'];
                    }
                }

                if ($taxiRState == "1") {
                    $chkStatNm = "현재 매칭요청중인 노선입니다.";
                } else if ($taxiRState == "2") {
                    $chkStatNm = "현재 예약요청중인 노선입니다.";
                } else if ($taxiRState == "3") {
                    $chkStatNm = "현재 거절 중인 노선입니다.";
                }

                $result = array("result" => false, "errorMsg" => $chkStatNm . " 새로운 노선을 요청을 해주세요.");
            }
        }
    }
} else {
    $result = array("result" => false);
}
JSON_PRINT:
echo str_replace('\\/', '/', json_encode($result, JSON_UNESCAPED_UNICODE));
