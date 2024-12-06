<?
include "../lib/common.php";
include "../lib/functionDB.php";  //공통 db함수

$mem_Id = trim($memId);                //아이디
$chkMonth = trim($chkMonth);        //구분 (최근 1개월 : 1, 최근 3개월 : 3, 최근 6개월 : 6)
$none_Date = DU_TIME_YMDHIS;           //등록일

if ($chkMonth == "") {
    $chkMonth = "1";        //최근 1개월 1
} else {
    $chkMonth = trim($chkMonth);
}
if ($mem_Id != ""  && $chkMonth != "") {  //아이디, 개월수가 있을 경우

    $DB_con = db1();

    $mem_Idx = memIdxInfo($mem_Id);   //회원 주아이디

    /* 전체 카운트 */
    $cntQuery = "SELECT idx FROM TB_STAXISHARING WHERE  taxi_State IN ( '7', '8', '9' )  AND taxi_MemIdx = :taxi_MemIdx AND taxi_MemId = :taxi_MemId AND (taxi_MState > 5 OR taxi_MState IS NULL) AND taxi_DelBit = 'N' ";
    $cntQuery .= " AND  reg_Date > SUBDATE(NOW(), INTERVAL :chkMonth MONTH)  ";
    //echo $cntQuery."<BR>";
    //exit;
    $cntStmt = $DB_con->prepare($cntQuery);
    $cntStmt->bindparam(":taxi_MemIdx", $mem_Idx);
    $cntStmt->bindparam(":taxi_MemId", $mem_Id);
    $cntStmt->bindparam(":chkMonth", $chkMonth);
    $cntStmt->execute();
    $totalCnt = $cntStmt->rowCount();

    if ($totalCnt == "") {
        $totalCnt = "0";
    } else {
        $totalCnt =  $totalCnt;
    }

    $totalCnt = (int)$totalCnt;

    $rows = 10;  //페이지 갯수
    $total_page  = ceil($totalCnt / $rows);  // 전체 페이지 계산
    if ($page == "") {
        $page = 1;
    } // 페이지가 없으면 첫 페이지 (1 페이지)
    $page = (int)$page;

    $from_record = ($page - 1) * $rows; // 시작 열을 구함


    /* /매칭(노선)생성 이용내역 (예약요청완료, 만남중, 이동중, 완료, 취소)*/
    $hisQuery = "";
    $hisQuery = "SELECT idx, taxi_Per, taxi_Price, taxi_State, taxi_MCancle, taxi_MState, reg_CDate, reg_CMDate, reg_CYDate FROM TB_STAXISHARING WHERE  taxi_State IN ( '7', '8', '9' )  AND taxi_MemIdx = :taxi_MemIdx AND taxi_MemId = :taxi_MemId AND (taxi_MState > 5 OR taxi_MState IS NULL) AND taxi_DelBit = 'N' ";
    $hisQuery .= "  AND  reg_Date > SUBDATE(NOW(), INTERVAL :chkMonth MONTH)  ORDER BY reg_Date DESC  limit  {$from_record}, {$rows}  ";
    //echo $hisQuery."<BR>";
    //exit;
    $hisStmt = $DB_con->prepare($hisQuery);
    $hisStmt->bindparam(":taxi_MemIdx", $mem_Idx);
    $hisStmt->bindparam(':taxi_MemId', $mem_Id, PDO::PARAM_STR);
    $hisStmt->bindparam(":chkMonth", $chkMonth);
    $hisStmt->execute();
    $mNum = $hisStmt->rowCount();
    //exit; 

    if ($mNum < 1) { //아닐경우
        $chkResult = "0";
        $listInfoResult = array("totCnt" => (int)$totalCnt, "page" => (int)$page);
    } else {
        $chkResult = "1";
        $listInfoResult = array("totCnt" => (int)$totalCnt, "page" => (int)$page);

        $data  = [];
        while ($hrow = $hisStmt->fetch(PDO::FETCH_ASSOC)) {
            // print_r($hrow);
            $idx = trim($hrow['idx']);          // 고유번호
            $taxiPer = trim($hrow['taxi_Per']);         // 생성 %
            $taxiPrice = trim($hrow['taxi_Price']);     // 택시 희망 쉐어링 비용

            $taxiState = trim($hrow['taxi_State']);             // 상태값
            $taxiMCancle = trim($hrow['taxi_MCancle']);    // 취소
            $taxiMState =  trim($hrow['taxi_MState']);    // 취소이전 상태

            $reg_CDate = trim($hrow['reg_CDate']);         // 취소일
            $reg_CMDate = trim($hrow['reg_CMDate']);     // 거래취소확인일
            $reg_CYDate = trim($hrow['reg_CYDate']);     // 거래완료확인일

            //생성 정보
            $infoQuery = "";
            $infoQuery = "SELECT taxi_Type, taxi_Distance, taxi_Route FROM TB_STAXISHARING_INFO WHERE taxi_Idx = :taxi_Idx AND taxi_MemIdx = :taxi_MemIdx AND taxi_MemId = :taxi_MemId LIMIT 1 ";
            //echo $infoQuery."<BR>";
            //exit;
            $infoStmt = $DB_con->prepare($infoQuery);
            $infoStmt->bindparam(":taxi_Idx", $idx);
            $infoStmt->bindparam(":taxi_MemIdx", $mem_Idx);
            $infoStmt->bindparam(":taxi_MemId", $mem_Id);
            $infoStmt->execute();
            $infoNum = $infoStmt->rowCount();
            //echo $infoNum."<BR>";

            if ($infoNum < 1) { //아닐경우
            } else {
                while ($infoRow = $infoStmt->fetch(PDO::FETCH_ASSOC)) {
                    $taxiType =  trim($infoRow['taxi_Type']);                    //출발타입 ( 0: 바로출발, 1: 예약출발)
                    $taxi_Route =  trim($infoRow['taxi_Route']);                // 경유가능여부 ( 0: 경유가능, 1: 경유불가)

                    if ($taxi_Route == "0") {
                        $taxiRoute = true;
                    } else {
                        $taxiRoute = false;
                    }
                }
            }

            //생성 지도정보
            $mapQuery = "SELECT taxi_SaddrNm, taxi_EaddrNm FROM TB_STAXISHARING_MAP WHERE taxi_Idx = :taxi_Idx AND taxi_MemIdx = :taxi_MemIdx AND taxi_MemId = :taxi_MemId  LIMIT 1 ";
            //echo $mapQuery."<BR>";
            //exit;
            $mapStmt = $DB_con->prepare($mapQuery);
            $mapStmt->bindparam(":taxi_Idx", $idx);
            $mapStmt->bindparam(":taxi_MemIdx", $mem_Idx);
            $mapStmt->bindparam(":taxi_MemId", $mem_Id);
            $mapStmt->execute();
            $mapNum = $mapStmt->rowCount();
            //echo $mapNum."<BR>";

            if ($mapNum < 1) { //아닐경우
            } else {
                while ($mapRow = $mapStmt->fetch(PDO::FETCH_ASSOC)) {
                    $taxiSaddrNm = trim($mapRow['taxi_SaddrNm']);                      //  출발지 주소
                    $taxiEaddrNm = trim($mapRow['taxi_EaddrNm']);                      //  도착지 주소
                }
            }

            //매칭 요청자
            $chkQuery = "SELECT idx, taxi_RMemIdx, taxi_RMemId, taxi_RTPrice FROM TB_RTAXISHARING WHERE taxi_SIdx = :idx  LIMIT 1 ";
            $chkStmt = $DB_con->prepare($chkQuery);
            $chkStmt->bindparam(":idx", $idx);
            $chkStmt->execute();
            $chkNum = $chkStmt->rowCount();
            if ($chkNum < 1) { //아닐경우
                // 테스트 노선인 경우 강제로 취소 처리를 DB상으로 처리 하다보니 발생하는 오류가 있어서 우선 테스트 형식으로 오류가 나지 않게 내려보내기로 결정 2023-07-25 황상섭 처리
                if ($taxiState == "7") { //완료
                    $taxiStaeNm = "테스트완료";
                    $taxiCPrice = "0";
                    $regDate = DateHard($none_Date, 8);              // 완료일
                    $cancleReason = "테스트완료";
                } else if ($taxiState == "8") { //취소
                    $taxiStaeNm = "테스트취소";
                    $taxiCPrice = "0";
                    $regDate = DateHard($none_Date, 8);          // 취소일
                    $cancleReason = "테스트취소";
                } else if ($taxiState == "9") { //취소사유확인
                    $taxiStaeNm = "테스트취소사유확인";
                    $taxiCPrice = "0";
                    $regDate = DateHard($none_Date, 8);          // 취소일
                    $cancleReason = "테스트취소";
                } else if ($taxiState == "10") { //거래완료확인
                    $taxiStaeNm = "테스트거래완료확인";
                    $taxiCPrice = "0";
                    $regDate = DateHard($none_Date, 8);          // 취소일
                    $cancleReason = "테스트취소";
                }
            } else {
                while ($chkRrow = $chkStmt->fetch(PDO::FETCH_ASSOC)) {
                    $taxiRIdx = trim($chkRrow['idx']);       // 요청자 고유번호
                    $mem_RIdx = trim($chkRrow['taxi_RMemIdx']);        // 요청자 주아이디
                    $taxiRMemId = trim($chkRrow['taxi_RMemId']);       // 요청자 아이디
                    $taxiRTPrice = trim($chkRrow['taxi_RTPrice']);       // 요청자 경로추가요금

                    //요청자 신청 정보 가져오기
                    $infoRQuery = "SELECT taxi_MCancle, reg_RDate, reg_MDate, reg_EDate, reg_YDate, reg_CDate, reg_CMDate, reg_CYDate from TB_RTAXISHARING_INFO  WHERE taxi_RIdx = :taxi_RIdx AND taxi_RMemIdx = :taxi_RMemIdx AND taxi_RMemId = :taxi_RMemId LIMIT 1  ";
                    // echo $infoRQuery."<BR>";
                    ///exit;
                    $infoRStmt = $DB_con->prepare($infoRQuery);
                    $infoRStmt->bindparam(":taxi_RIdx", $taxiRIdx);
                    $infoRStmt->bindparam(":taxi_RMemIdx", $mem_RIdx);
                    $infoRStmt->bindparam(":taxi_RMemId", $taxiRMemId);
                    $infoRStmt->execute();
                    $infoRNum = $infoRStmt->rowCount();

                    if ($infoRNum < 1) { //아닐경우
                    } else {
                        while ($infoRRow = $infoRStmt->fetch(PDO::FETCH_ASSOC)) {
                            $taxiRMCancle = trim($infoRRow['taxi_MCancle']);         //취소 (본인 : 0, 그외 : 1)
                            $regRDate = trim($infoRRow['reg_RDate']);                //예약완료일
                            $regMDate = trim($infoRRow['reg_MDate']);                //만남중
                            $regEDate = trim($infoRRow['reg_EDate']);                //이동중
                            $regYDate = trim($infoRRow['reg_YDate']);                //완료일
                            $regCDate = trim($infoRRow['reg_CDate']);                //취소일
                            $regCMDate = trim($infoRRow['reg_CMDate']);              //거래취소사유확인
                            $regCYDate = trim($infoRRow['reg_CYDate']);              //거래완료확인

                            if ($regCDate == "") {
                                $regCDate = $reg_CDate;
                            } else {
                                $regCDate = $regCDate;
                            }
                            if ($regCMDate == "") {
                                $regCMDate = $reg_CMDate;
                            } else {
                                $regCMDate = $regCMDate;
                            }
                            if ($regCYDate == "") {
                                $regCYDate = $reg_CYDate;
                            } else {
                                $regCYDate = $regCYDate;
                            }
                        }
                    }

                    //취소시 취소 사유 확인
                    $cancleQuery = "SELECT taxi_CanRChk, taxi_CanCnt, taxi_MType, taxi_CPart, taxi_CRPart, taxi_CMemo FROM TB_SMATCH_STATE WHERE taxi_SIdx = :taxi_Sidx ";
                    $cancleStmt = $DB_con->prepare($cancleQuery);
                    $cancleStmt->bindparam(":taxi_Sidx", $idx);
                    $cancleStmt->execute();
                    $cancleNum = $cancleStmt->rowCount();

                    if ($cancleNum < 1) { //아닐경우
                        $cancleReason = "";
                    } else {
                        while ($cancleRow = $cancleStmt->fetch(PDO::FETCH_ASSOC)) {
                            $taxiCanRChk = trim($cancleRow['taxi_CanRChk']);             // 최초신청자구분 (p : 메이커, c : 투게더)
                            $taxiCanCnt = trim($cancleRow['taxi_CanCnt']);               // 취소사유 (1,2,3,4)
                            $taxiMType = trim($cancleRow['taxi_MType']);             // 최초신청자구분 (p : 메이커, c : 투게더)
                            $taxiCPart = trim($cancleRow['taxi_CPart']);               // 취소사유 (1,2,3,4)
                            $taxiCRPart = trim($cancleRow['taxi_CRPart']);             // 취소동의사유 (1,2)
                            $taxiCMemo = trim($cancleRow['taxi_CMemo']);              // 기타 취소 사유 메모
                            if ($taxiCMemo == "") {
                                $taxiCMemo = "다른 사유가 있지만 입력하지 않았습니다.";
                            }
                            /*   
                            taxi_CRPart 
                            1 : 거래취소를 원하지 않습니다.
                            2 : 거래 취소는 동일하나 다른 사유입니다
                            3 : 기타 (5분 초과 미응답) 
                            4 : 동의합니다
                            
                            메이커입장인 경우 p
                            1 : 택시가 잡히지 않습니다.
                            2 : 나의 사정으로 취소합니다.
                            3 : 투게더의 사정으로 취소합니다.    
                            
                            투게더입장인 경우 c
                            1 : 택시가 잡히지 않습니다.
                            2 : 나의 사정으로 취소합니다.
                            3 : 메이커의 사정으로 취소합니다.
                            */
                            if ($taxiCanRChk == "N" && (int)$taxiCanCnt == 2) {
                                if ($taxiMType == "p") {
                                    if ($taxiCRPart == "2") {
                                        $cancleReason = $taxiCMemo;
                                    } else {
                                        if ($taxiCPart == "1") {
                                            $cancleReason = "택시가 잡히지 않습니다.";
                                        } else if ($taxiCPart == "2") {
                                            $cancleReason = "나의 사정으로 취소합니다.";
                                        } else if ($taxiCPart == "3") {
                                            $cancleReason = "투게더의 사정으로 취소합니다.";
                                        } else {
                                            $cancleReason = "";
                                        }
                                    }
                                } else if ($taxiMType == "c") {
                                    if ($taxiCRPart == "2") {
                                        $cancleReason = $taxiCMemo;
                                    } else {
                                        if ($taxiCPart == "1") {
                                            $cancleReason = "택시가 잡히지 않습니다.";
                                        } else if ($taxiCPart == "2") {
                                            $cancleReason = "나의 사정으로 취소합니다.";
                                        } else if ($taxiCPart == "3") {
                                            $cancleReason = "메이커의 사정으로 취소합니다.";
                                        } else {
                                            $cancleReason = "";
                                        }
                                    }
                                } else {
                                    $cancleReason = "";
                                }
                            } else {
                                if ($taxiMType == "p") {
                                    if ($taxiCRPart == "2") {
                                        $cancleReason = $taxiCMemo;
                                    } else {
                                        if ($taxiCPart == "1") {
                                            $cancleReason = "택시가 잡히지 않습니다.";
                                        } else if ($taxiCPart == "2") {
                                            $cancleReason = "나의 사정으로 취소합니다.";
                                        } else if ($taxiCPart == "3") {
                                            $cancleReason = "투게더의 사정으로 취소합니다.";
                                        } else {
                                            $cancleReason = "";
                                        }
                                    }
                                } else if ($taxiMType == "c") {
                                    if ($taxiCRPart == "2") {
                                        $cancleReason = $taxiCMemo;
                                    } else {
                                        if ($taxiCPart == "1") {
                                            $cancleReason = "택시가 잡히지 않습니다.";
                                        } else if ($taxiCPart == "2") {
                                            $cancleReason = "나의 사정으로 취소합니다.";
                                        } else if ($taxiCPart == "3") {
                                            $cancleReason = "메이커의 사정으로 취소합니다.";
                                        } else {
                                            $cancleReason = "";
                                        }
                                    }
                                } else {
                                    $cancleReason = "";
                                }
                            }
                        }
                    }


                    //회원등급 조회
                    $memRLV = memLvGet($mem_RIdx);
                    //회원 수수료 조회
                    if ((int)$memRLV > 5) { // 회원등급이 추가 될 위험이 있기때문에 LV 6~14사이에 유저들은 일반유저로 칭함.
                        $mpQuery = "SELECT memDc FROM TB_MEMBER_LEVEL WHERE memLv = :memLv  LIMIT 1 ";
                        $mpStmt = $DB_con->prepare($mpQuery);
                        $mpStmt->bindparam(":memLv", $memRLV);
                        $mpStmt->execute();
                        $mpNum = $mpStmt->rowCount();

                        if ($mpNum < 1) { //아닐경우
                        } else {
                            while ($mpRow = $mpStmt->fetch(PDO::FETCH_ASSOC)) {
                                $levDc = trim($mpRow['memDc']);             // 포인트
                            }
                        }
                    } else {  //관리자 기준
                        $levDc = "10";  //10% 차감
                    }
                     
                    $taxiOrdPoint = (int)$taxiPrice - floor((int)$taxiPrice * ((int)$levDc / 100));

                    if ($taxiState == "7") { //완료
                        $taxiStaeNm = "완료";
                        $taxiCPrice = $taxiOrdPoint;
                        $regDate = DateHard($regYDate, 8);              // 완료일
                    } else if ($taxiState == "8") { //취소
                        $taxiStaeNm = "취소";
                        $taxiCPrice = "0";
                        $regDate = DateHard($regCDate, 8);          // 취소일
                    } else if ($taxiState == "9") { //취소사유확인
                        $taxiStaeNm = "취소사유확인";
                        $taxiCPrice = "0";
                        $regDate = DateHard($regCMDate, 8);          // 취소일
                    } else if ($taxiState == "10") { //거래완료확인
                        $taxiStaeNm = "거래완료확인";
                        $taxiCPrice = "0";
                        $regDate = DateHard($regCYDate, 8);          // 취소일
                    }

                    // 테스트로 DB로 취소 및 기타 처리를 하다보니 발생하는 등록일이 조회가 되지 않는 상황에서는 앱 내에서 오류가 발생하기 때문에 오늘날짜로 입력하여 처리 하도록 함 2023-07-25 황상섭 처리
                    if ($regDate == "") {
                        $regDate = DateHard($none_Date, 8);
                    }
                }
            }

            // if ($idx == 350) {
            //     echo "taxiMType : " . $taxiMType . "\n";
            //     echo "taxiCRPart : " . $taxiCRPart . "\n";
            //     echo "taxiCPart : " . $taxiCPart . "\n";
            //     echo "taxiCMemo : " . $taxiCMemo . "\n";
            //     echo "cancleReason : " . $cancleReason . "\n";
            // }
            $mresult = [
                "idx" => (int)$idx,
                "regDate" => (string)$regDate,
                "taxiStaeNm" => (string)$taxiStaeNm,
                "taxiSaddrNm" => (string)$taxiSaddrNm,
                "taxiEaddrNm" => (string)$taxiEaddrNm,
                "taxiPrice" => (int)$taxiCPrice,
                "cancleReason" => (string)$cancleReason
            ];
            array_push($data, $mresult);
        }
        $chkData = [];
        $chkData["result"] = true;
        $chkData["listInfo"] = $listInfoResult;  //카운트 관련
        $chkData['lists'] = $data;
    }

    if ($chkResult  == "1") {
        $output = str_replace('\\\/', '/', json_encode($chkData, JSON_UNESCAPED_UNICODE));
    } else if ($chkResult  == "0") {
        $chkData2["result"] = true;
        $chkData2["listInfo"] = $listInfoResult;  //카운트 관련
        $chkData2["lists"] = [];  //카운트 관련
        $output = str_replace('\\\/', '/', json_encode($chkData2, JSON_UNESCAPED_UNICODE));
    }

    echo  urldecode($output);

    dbClose($DB_con);
    $cntStmt = null;
    $hisStmt = null;
    $infoStmt = null;
    $mapStmt = null;
    $chkStmt = null;
    $mapRStmt = null;
    $infoRStmt = null;
    $memStmt = null;
    $mpStmt = null;
} else {
    $result = array("result" => false, "errorMsg" => "이용내역 정보가 없습니다. 관리자에게 문의바랍니다.");
    echo json_encode($result, JSON_UNESCAPED_UNICODE);
}
