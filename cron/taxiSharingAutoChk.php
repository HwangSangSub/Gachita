#!/usr/bin/php -q
<?php
/*======================================================================================================================

* 프로그램			: 예상도착시간의 노선이 있는 경우 이동 중 시작 시간과 예상 도착시ss간의 정각때 10분 후 자동양도 안내, 10분이 이미 지났다면 자동양도하기.
* 페이지 설명		: 유효시간이 지난 매칭중 노선 취소(삭제)처리
* 파일명          : taxiSharingAutoChk.php

========================================================================================================================*/

// register_globals off 처리
if (isset($_GET)) {
    @extract($_GET);
}
if (isset($_POST)) {
    @extract($_POST);
}
if (isset($_SERVER)) {
    @extract($_SERVER);
}
if (isset($_ENV)) {
    @extract($_ENV);
}
if (isset($_SESSION)) {
    @extract($_SESSION);
}
if (isset($_COOKIE)) {
    @extract($_COOKIE);
}
if (isset($_REQUEST)) {
    @extract($_REQUEST);
}
if (isset($_FILES)) {
    @extract($_FILES);
}

ob_start();

header('Content-Type: text/html; charset=utf-8');
$gmnow = gmdate('D, d M Y H:i:s') . ' GMT';
header('Expires: 0'); // rfc2616 - Section 14.21
header('Last-Modified: ' . $gmnow);
header('Cache-Control: no-store, no-cache, must-revalidate'); // HTTP/1.1
header('Cache-Control: pre-check=0, post-check=0, max-age=0'); // HTTP/1.1
header('Pragma: no-cache'); // HTTP/1.0

//구글 fcm키
define("GOOGLE_API_KEY", "AAAAQ5PRua4:APA91bHIqpvIHy5sm_Av5GYw1o3qO3gZxorKjfHnbXN_G17YiEf_qnaH-5n34dsbUJ1YmqBNjAaGAAY6hrJ4VmL2ntidTTMF_FXOYh_xcH4X-od_bdHVmj5iyqmAeYnLXqprP_FWA1mD");
include 'inc/dbcon.php';
require '/var/www/gachita/vendor/autoload.php';

use Google\Cloud\Firestore\FirestoreClient;

function fire_Get($dbname)
{
    // Firestore 클라이언트 객체 생성
    $firestore = new FirestoreClient([
        'projectId' => 'gachi-5246d',
    ]);
    // "chat" 컬렉션의 모든 문서 가져오기
    $dbname = 'chat';
    $collection = $firestore->collection($dbname);
    $documents = $collection->documents();

    // foreach ($documents as $document) {
    //     // 문서 데이터 가져오기
    //     $data = $document->data();
    //     // 데이터 처리
    //     // ...
    // }
}
//양도가 완료된 경우 파이어스토어에 chat > 노선생성고유번호 > 필드값 : complete : true 추가하기.
function fire_Complete_Set($idx)
{
    // Firestore 클라이언트 객체 생성
    $firestore = new FirestoreClient([
        'projectId' => 'gachi-5246d',
    ]);

    // 업데이트할 문서의 참조 가져오기
    $documentRef = $firestore->collection('chat')->document($idx);
    // 업데이트할 필드와 값 지정
    $documentRef->set([
        'complete' => true
    ], ['merge' => true]);
}

$DB_con = db1();

$now_Time = date('Y-m-d H:i:s', time());     //등록일

//성공여부 (0: 실패, 1: 성공)
$res_bit1 = 0; //대기모드 제외 노선
$res_bit2 = 0; //대기모드
$Query = "SELECT rt.idx, rt.taxi_MemId, rt.taxi_RMemId, rt.taxi_MemIdx, rt.taxi_RMemIdx, rt.taxi_RState, rt.reg_Date, info.taxi_MoveCnt, rt.taxi_RATime / 60 AS taxi_RATime, TIMESTAMPDIFF(MINUTE, info.reg_EDate ,NOW()) AS half_Time FROM TB_RTAXISHARING AS rt INNER JOIN TB_RTAXISHARING_INFO AS info ON rt.idx = info.taxi_RIdx WHERE rt.taxi_RState = '6' ORDER BY info.reg_EDate ASC";
$Stmt = $DB_con->prepare($Query);
$Stmt->execute();
$num = $Stmt->rowCount();
$cnt = 0;
if ($num < 1) { //아닐경우
    $result = array("result" => false, "errorMsg" => "이동중인 노선이 없습니다.");
} else {
    while ($row = $Stmt->fetch(PDO::FETCH_ASSOC)) {
        $taxi_RIdx =  $row['idx'];                          // 투게더 고유번호
        $taxi_MemId =  $row['taxi_MemId'];                  // 메이커 아이디
        $taxi_RMemId =  $row['taxi_RMemId'];                // 투게더 아이디
        $taxi_MemIdx =  $row['taxi_MemIdx'];                // 메이커 고유번호
        $taxi_RMemIdx =  $row['taxi_RMemIdx'];              // 투게더 고유번호
        $taxi_RState =  $row['taxi_RState'];                // 투게더 상태 값
        $taxi_MoveCnt =  $row['taxi_MoveCnt'];              // 자동완료 푸시 보낸 횟수
        if ($taxi_MoveCnt == "") {
            $taxi_MoveCnt = 0;
        }
        $taxi_RATime =  $row['taxi_RATime'];                // 목적지 이동 예상 시간 (분)
        $half_Time =  $row['half_Time'];                    // 이동중 상태에서 현재시간 계산 (분)
        $total_Time = (int) $half_Time - (int)$taxi_RATime; // 시간차이 계산
        $reg_Date =  $row['reg_Date'];
        //시간 차이를 계산해서  10분이상 이면서 10분전에 자동양도 안내를 한 경우 자동 양도. 
        // $total_Time = 10;
        if ((int)$total_Time >= 10 && (int)$taxi_MoveCnt == 1) {

            //주문정보 가져옴
            $orderChkQuery = "SELECT taxi_OrdNo FROM TB_ORDER WHERE taxi_RIdx = :taxi_RIdx LIMIT 1 ";
            $orderChkStmt = $DB_con->prepare($orderChkQuery);
            $orderChkStmt->bindparam(":taxi_RIdx", $taxi_RIdx);
            $orderChkStmt->execute();
            $orderChkRow = $orderChkStmt->fetch(PDO::FETCH_ASSOC);
            $taxiOrdNo = trim($orderChkRow['taxi_OrdNo']);                    // 투게더 주문번호

            //주문정보 가져옴
            $viewQuery = "SELECT taxi_SIdx, taxi_RIdx, taxi_OrdPrice, taxi_OrdPoint, taxi_OMemIdx, taxi_OrdMemId, taxi_OrdSMemId, taxi_OSMemIdx, taxi_OrdType FROM TB_ORDER WHERE taxi_OrdNo = :taxi_OrdNo AND taxi_OrdState = '1'  LIMIT 1  ";
            $viewStmt = $DB_con->prepare($viewQuery);
            $viewStmt->bindparam(":taxi_OrdNo", $taxiOrdNo);
            $viewStmt->execute();
            $num = $viewStmt->rowCount();

            if ($num > 0) { //아닐경우
                while ($row = $viewStmt->fetch(PDO::FETCH_ASSOC)) {
                    $taxiSIdx = trim($row['taxi_SIdx']);                    // 메이커 고유번호
                    $taxiRIdx = trim($row['taxi_RIdx']);                    // 투게더 고유번호
                    $taxi_OrdPrice = trim($row['taxi_OrdPrice']);           // 카드결제금액
                    $taxi_OrdPoint = trim($row['taxi_OrdPoint']);           // 사용한포인트
                    $taxiOrdSMemId = trim($row['taxi_OrdSMemId']);          // 메이커 아이디
                    $taxiOrdMemId = trim($row['taxi_OrdMemId']);            // 투게더 아이디
                    $taxiOSMemIdx = trim($row['taxi_OSMemIdx']);            // 메이커 고유아이디
                    $taxiOMemIdx = trim($row['taxi_OMemIdx']);              // 투게더 고유아이디
                    $taxi_OrdType = trim($row['taxi_OrdType']);             // 결제방식
                }
                $taxiSOrdPoint = (int)$taxi_OrdPrice + (int)$taxi_OrdPoint; // 총 요청 금액

                //메이커 닉네임 확인
                $mNickQuery = "SELECT mem_NickNm, mem_LV FROM TB_MEMBERS WHERE idx = :taxi_OSMemIdx AND b_Disply = 'N' LIMIT 1";
                $mNickStmt = $DB_con->prepare($mNickQuery);
                $mNickStmt->bindparam(":taxi_OSMemIdx", $taxiOSMemIdx);
                $mNickStmt->execute();
                $mNickRow = $mNickStmt->fetch(PDO::FETCH_ASSOC);
                $memNickNm =  trim($mNickRow['mem_NickNm']);    // 닉네임 확인
                $memMLV =  trim($mNickRow['mem_LV']);    // 닉네임 확인

                //투게더 닉네임 확인
                $tNickQuery = "SELECT mem_NickNm FROM TB_MEMBERS WHERE idx = :taxi_OMemIdx AND b_Disply = 'N' LIMIT 1";
                $tNickStmt = $DB_con->prepare($tNickQuery);
                $tNickStmt->bindparam(":taxi_OMemIdx", $taxiOMemIdx);
                $tNickStmt->execute();
                $tNickRow = $tNickStmt->fetch(PDO::FETCH_ASSOC);
                $memRNickNm =  trim($tNickRow['mem_NickNm']);    // 닉네임 확인

                $tmemEtcQuery = "SELECT mem_Point FROM TB_MEMBERS_ETC WHERE mem_Idx = :taxi_OMemIdx  ORDER BY idx DESC LIMIT 1 ";
                $tmemEtcStmt = $DB_con->prepare($tmemEtcQuery);
                $tmemEtcStmt->bindparam(":taxi_OMemIdx", $taxiOMemIdx);
                $tmemEtcStmt->execute();
                $tmemEtcNum = $tmemEtcStmt->rowCount();

                if ($tmemEtcNum < 1) { //아닐경우
                } else {
                    while ($tmemEtcRow = $tmemEtcStmt->fetch(PDO::FETCH_ASSOC)) {
                        $memRPoint = trim($tmemEtcRow['mem_Point']);              // 투게더 포인트
                    }
                }
                // 메이커 수수료 조회
                if ($memMLV != "") {
                    $mpQuery = "SELECT memDc FROM TB_MEMBER_LEVEL WHERE memLv = :memLv  LIMIT 1 ";
                    $mpStmt = $DB_con->prepare($mpQuery);
                    $mpStmt->bindparam(":memLv", $memMLV);
                    $mpStmt->execute();
                    $mpNum = $mpStmt->rowCount();

                    if ($mpNum < 1) { //아닐경우
                    } else {
                        while ($mpRow = $mpStmt->fetch(PDO::FETCH_ASSOC)) {
                            $levDc = trim($mpRow['memDc']);             // 포인트 수수료
                        }
                    }
                } else {  //관리자 기준
                    $levDc = "10";  //10% 차감
                }
                $taxiPoint = $taxiSOrdPoint - floor($taxiSOrdPoint * ($levDc / 100));  // 요청요금 = 택시요금 - 택시요금의 %요금==> 퍼센트 요금이란 택시요금에서 생성자가 입력한 요청비율(%)를 구한 요금

                //양도처리 내역 저장
                //메이커 포인트내역
                if ($taxiOSMemIdx <> "") {

                    $taxi_Sign = "0"; // +기호
                    $taxi_PState = "0"; //매칭
                    //1400 요청 포인트 1400 수수료 금액
                    $taxi_Memo = date('Y-m-d H:i:s', time()) . '
투게더(' . $memRNickNm . ') 님이 나눠내기한 포인트 총 ' . number_format($taxiSOrdPoint) . '에서 수수료 ' . $levDc . '%를 차감한 ' . number_format($taxiPoint) . '포인트를 적립' . "";
                    //echo $taxi_Memo."<BR>";
                    //exit;

                    //메이커 포인트내역 등록 여부 체크
                    $cntQuery = "SELECT count(idx)  AS num FROM TB_POINT_HISTORY WHERE taxi_SIdx = :taxi_SIdx AND taxi_RIdx = :taxi_RIdx AND taxi_OrdNo = :taxi_OrdNo AND taxi_MemId = :taxi_MemId AND taxi_MemIdx = :taxi_MemIdx ";
                    $cntStmt = $DB_con->prepare($cntQuery);
                    $cntStmt->bindparam(":taxi_SIdx", $taxiSIdx);
                    $cntStmt->bindparam(":taxi_RIdx", $taxiRIdx);
                    $cntStmt->bindparam(":taxi_OrdNo", $taxiOrdNo);
                    $cntStmt->bindparam(":taxi_MemId", $taxiOrdSMemId);
                    $cntStmt->bindparam(":taxi_MemIdx", $taxiOSMemIdx);
                    $cntStmt->execute();
                    $cntRow = $cntStmt->fetch(PDO::FETCH_ASSOC);
                    $totalCnt = $cntRow['num'];

                    if ($totalCnt == "") {
                        $totalCnt = "0";
                    } else {
                        $totalCnt =  $totalCnt;
                    }

                    //포인트 내역 중복 등록을 맞기 위해서 체크 함
                    if ($totalCnt < 1) {

                        //메이커 포인트, 매칭성공횟수 내역 조회
                        $pointQuery = "SELECT mem_Point, mem_MatCnt FROM TB_MEMBERS_ETC WHERE mem_Idx = :taxi_OSMemIdx  ORDER BY idx DESC  LIMIT 1 ";
                        $pointStmt = $DB_con->prepare($pointQuery);
                        $pointStmt->bindparam(":taxi_OSMemIdx", $taxiOSMemIdx);
                        $pointStmt->execute();
                        $pointNum = $pointStmt->rowCount();

                        if ($pointNum < 1) { //아닐경우
                        } else {
                            while ($pointRow = $pointStmt->fetch(PDO::FETCH_ASSOC)) {
                                $sum_M_Point = trim($pointRow['mem_Point']);    //포인트
                                $mem_M_MatCnt = trim($pointRow['mem_MatCnt']);  //매칭성공횟수
                            }
                        }

                        $insQuery = "INSERT INTO TB_POINT_HISTORY (taxi_SIdx, taxi_RIdx, taxi_OrdNo, taxi_MemId, taxi_MemIdx, taxi_OrdPoint, taxi_OrgPoint, taxi_Memo, taxi_Sign, taxi_PState, taxi_OrdType, reg_Date) VALUES (:taxi_SIdx, :taxi_RIdx, :taxi_OrdNo, :taxi_MemId, :taxi_MemIdx, :taxi_OrdPoint, :taxi_OrgPoint, :taxi_Memo, :taxi_Sign, :taxi_PState, :taxi_OrdType, :reg_Date)";
                        $stmt = $DB_con->prepare($insQuery);
                        $stmt->bindParam("taxi_SIdx", $taxiSIdx);
                        $stmt->bindParam("taxi_RIdx", $taxiRIdx);
                        $stmt->bindParam("taxi_OrdNo", $taxiOrdNo);
                        $stmt->bindParam("taxi_MemId", $taxiOrdSMemId);
                        $stmt->bindParam("taxi_MemIdx", $taxiOSMemIdx);
                        $stmt->bindParam("taxi_OrdPoint", $taxiPoint);
                        $stmt->bindParam("taxi_OrgPoint", $sumPoint);
                        $stmt->bindParam("taxi_Memo", $taxi_Memo);
                        $stmt->bindParam("taxi_Sign", $taxi_Sign);
                        $stmt->bindParam("taxi_PState", $taxi_PState);
                        $stmt->bindParam("taxi_OrdType", $taxi_OrdType);
                        $stmt->bindParam("reg_Date", $reg_Date);
                        $stmt->execute();
                        // try {
                        //     $stmt->execute();
                        //     echo "Data inserted successfully.";
                        // } catch (PDOException $e) {
                        //     echo "PDO Exception: " . $e->getMessage();
                        // }
                        $DB_con->lastInsertId();

                        //총포인트 조회
                        if (!$sum_M_Point > 0) {
                            $sum_MPoint = "0";
                        } else { //포인트가 있을 경우
                            $sum_MPoint =  $sum_M_Point;
                        }

                        //양도금액 포함 포인트 (생성장의 경우는 적립
                        $totMPoint = $sum_MPoint + $taxiPoint;        //현재포인트 = 보유포인트 + 쉐어링요금에서 수수료를 차감한 금액을 더해줌 

                        //매칭횟수
                        $tot_M_MatCnt = (int)$mem_M_MatCnt + 1;

                        //포인트 금액 변경
                        $upmPQquery = "UPDATE TB_MEMBERS_ETC SET mem_MatCnt = :mem_MatCnt, mem_Point = :mem_Point WHERE mem_Idx = :mem_Idx AND mem_Id = :mem_Id  ORDER BY idx DESC  LIMIT 1";
                        //echo $upmPQquery."<BR>";
                        //exit;
                        $upmPStmt = $DB_con->prepare($upmPQquery);
                        $upmPStmt->bindparam(":mem_MatCnt", $tot_M_MatCnt);
                        $upmPStmt->bindparam(":mem_Point", $totMPoint);
                        $upmPStmt->bindparam(":mem_Id", $taxiOrdSMemId);
                        $upmPStmt->bindparam(":mem_Idx", $taxiOSMemIdx);
                        $upmPStmt->execute();
                    }
                }


                //투게더 포인트내역
                if ($taxiOMemIdx <> "") {

                    if ($taxi_OrdPrice > 0) {
                        $taxi_Sign = "0"; // +기호
                        $taxi_PState = "4"; //매칭

                        $taxi_CMemo = date('Y-m-d H:i:s', time()) . '
카드결제로 인하여 ' . number_format($taxi_OrdPrice) . '포인트를 적립' . "";

                        //투게더 포인트내역 등록 여부 체크
                        $cntMQuery = "";
                        $cntMQuery = "SELECT count(idx)  AS num FROM TB_POINT_HISTORY WHERE taxi_SIdx = :taxi_SIdx AND taxi_RIdx = :taxi_RIdx AND taxi_OrdNo = :taxi_OrdNo AND taxi_MemId = :taxi_MemId AND taxi_MemIdx = :taxi_MemIdx AND taxi_OrdType = :taxi_OrdType ";
                        $cntMStmt = $DB_con->prepare($cntMQuery);
                        $cntMStmt->bindparam(":taxi_SIdx", $taxiSIdx);
                        $cntMStmt->bindparam(":taxi_RIdx", $taxiRIdx);
                        $cntMStmt->bindparam(":taxi_OrdNo", $taxiOrdNo);
                        $cntMStmt->bindparam(":taxi_MemId", $taxiOrdMemId);
                        $cntMStmt->bindparam(":taxi_MemIdx", $taxiOMemIdx);
                        $cntMStmt->bindparam(":taxi_OrdType", $taxi_OrdType);
                        $cntMStmt->execute();
                        $cntRow = $cntMStmt->fetch(PDO::FETCH_ASSOC);
                        $totalCnt = $cntRow['num'];

                        if ($totalCnt == "") {
                            $totalCnt = "0";
                        } else {
                            $totalCnt =  $totalCnt;
                        }

                        //포인트 내역 중복 등록을 맞기 위해서 체크 함
                        if ($totalCnt < 1) {
                            $insQuery = "INSERT INTO TB_POINT_HISTORY (taxi_SIdx, taxi_RIdx, taxi_OrdNo, taxi_MemId, taxi_MemIdx, taxi_OrdPoint, taxi_OrgPoint, taxi_Memo, taxi_Sign, taxi_PState, taxi_OrdType, reg_Date) VALUES (:taxi_SIdx, :taxi_RIdx, :taxi_OrdNo, :taxi_MemId, :taxi_MemIdx, :taxi_OrdPoint, :taxi_OrgPoint, :taxi_Memo, :taxi_Sign, :taxi_PState, :taxi_OrdType, :reg_Date)";
                            //echo $insQuery."<BR>";
                            //exit;
                            $mstmt = $DB_con->prepare($insQuery);
                            $mstmt->bindParam("taxi_SIdx", $taxiSIdx);
                            $mstmt->bindParam("taxi_RIdx", $taxiRIdx);
                            $mstmt->bindParam("taxi_OrdNo", $taxiOrdNo);
                            $mstmt->bindParam("taxi_MemId", $taxiOrdMemId);
                            $mstmt->bindParam("taxi_MemIdx", $taxiOMemIdx);
                            $mstmt->bindParam("taxi_OrdPoint", $taxi_OrdPrice);
                            $mstmt->bindParam("taxi_OrgPoint", $memRPoint);
                            $mstmt->bindParam("taxi_Memo", $taxi_CMemo);
                            $mstmt->bindParam("taxi_Sign", $taxi_Sign);
                            $mstmt->bindParam("taxi_PState", $taxi_PState);
                            $mstmt->bindParam("taxi_OrdType", $taxi_OrdType);
                            $mstmt->bindParam("reg_Date", $reg_Date);
                            $mstmt->execute();
                            $DB_con->lastInsertId();

                            //양도금액 포함 포인트(요청자의 경우 +	)
                            $totRPoint = $memRPoint + $taxi_OrdPrice; // 현재포인트 = 보유포인트 + 사용포인트

                            //포인트 변경
                            $upmsPQquery = "UPDATE TB_MEMBERS_ETC SET mem_Point = :mem_Point WHERE mem_Id = :mem_Id AND mem_Idx = :mem_Idx ORDER BY idx DESC  LIMIT 1";
                            $upmsPStmt = $DB_con->prepare($upmsPQquery);
                            $upmsPStmt->bindparam(":mem_Point", $totRPoint);
                            $upmsPStmt->bindparam(":mem_Id", $taxiOrdMemId);
                            $upmsPStmt->bindparam(":mem_Idx", $taxiOMemIdx);
                            $upmsPStmt->execute();
                        }
                    }

                    $taxi_Sign = "1"; // -기호
                    $taxi_PState = "0"; //매칭
                    $taxi_TOrdPoint = (int)$taxiSOrdPoint;
                    $taxi_CMemo = date('Y-m-d H:i:s', time()) . '
메이커(' . $memNickNm . ')님이 요청한 ' . number_format($taxi_TOrdPoint) . '포인트를 나눠 내기.';
                    //투게더 포인트내역 등록 여부 체크
                    $cntMQuery = "";
                    $cntMQuery = "SELECT count(idx)  AS num FROM TB_POINT_HISTORY WHERE taxi_SIdx = :taxi_SIdx AND taxi_RIdx = :taxi_RIdx AND taxi_OrdNo = :taxi_OrdNo AND taxi_MemId = :taxi_MemId AND taxi_MemIdx = :taxi_MemIdx AND taxi_OrdType = :taxi_OrdType AND taxi_Sign = 1 AND taxi_PState <> '5'";
                    $cntMStmt = $DB_con->prepare($cntMQuery);
                    $cntMStmt->bindparam(":taxi_SIdx", $taxiSIdx);
                    $cntMStmt->bindparam(":taxi_RIdx", $taxiRIdx);
                    $cntMStmt->bindparam(":taxi_OrdNo", $taxiOrdNo);
                    $cntMStmt->bindparam(":taxi_MemId", $taxiOrdMemId);
                    $cntMStmt->bindparam(":taxi_MemIdx", $taxiOMemIdx);
                    $cntMStmt->bindparam(":taxi_OrdType", $taxi_OrdType);
                    $cntMStmt->execute();
                    $cntRow = $cntMStmt->fetch(PDO::FETCH_ASSOC);
                    $totalCnt = $cntRow['num'];

                    if ($totalCnt == "") {
                        $totalCnt = "0";
                    } else {
                        $totalCnt =  $totalCnt;
                    }

                    //포인트 내역 중복 등록을 맞기 위해서 체크 함
                    if ($totalCnt < 1) {
                        if($totRPoint == "" || (int)$totRPoint == 0){
                            $totRPoint = $memRPoint;
                        }
                        $insQuery = "INSERT INTO TB_POINT_HISTORY (taxi_SIdx, taxi_RIdx, taxi_OrdNo, taxi_MemId, taxi_MemIdx, taxi_OrdPoint, taxi_OrgPoint, taxi_Memo, taxi_Sign, taxi_PState, taxi_OrdType, reg_Date) VALUES (:taxi_SIdx, :taxi_RIdx, :taxi_OrdNo, :taxi_MemId, :taxi_MemIdx, :taxi_OrdPoint, :taxi_OrgPoint, :taxi_Memo, :taxi_Sign, :taxi_PState, :taxi_OrdType, :reg_Date)";
                        //echo $insQuery."<BR>";
                        //exit;
                        $mstmt = $DB_con->prepare($insQuery);
                        $mstmt->bindParam("taxi_SIdx", $taxiSIdx);
                        $mstmt->bindParam("taxi_RIdx", $taxiRIdx);
                        $mstmt->bindParam("taxi_OrdNo", $taxiOrdNo);
                        $mstmt->bindParam("taxi_MemId", $taxiOrdMemId);
                        $mstmt->bindParam("taxi_MemIdx", $taxiOMemIdx);
                        $mstmt->bindParam("taxi_OrdPoint", $taxi_TOrdPoint);
                        $mstmt->bindParam("taxi_OrgPoint", $totRPoint);
                        $mstmt->bindParam("taxi_Memo", $taxi_CMemo);
                        $mstmt->bindParam("taxi_Sign", $taxi_Sign);
                        $mstmt->bindParam("taxi_PState", $taxi_PState);
                        $mstmt->bindParam("taxi_OrdType", $taxi_OrdType);
                        $mstmt->bindParam("reg_Date", $reg_Date);
                        $mstmt->execute();
                        $DB_con->lastInsertId();

                        //매칭성공횟수
                        if (!$membMatCnt > 0) {
                            $membMatCnt = "0";
                        } else { //포인트가 있을 경우
                            $membMatCnt =  $membMatCnt;
                        }

                        //매칭횟수
                        $mtotMatCnt = $membMatCnt + 1;

                        //양도금액 포함 포인트(요청자의 경우 차감 으로 -)
                        $totRRPoint = (int)$totRPoint - (int)$taxi_TOrdPoint; // 현재포인트 = 보유포인트 - (사용포인트) ==>미르페이가 있으면

                        //매칭 횟수, 포인트 변경
                        $upmsPQquery = "UPDATE TB_MEMBERS_ETC SET mem_MatCnt = :mem_MatCnt, mem_Point = :mem_Point WHERE mem_Id = :mem_Id AND mem_Idx = :mem_Idx ORDER BY idx DESC  LIMIT 1";
                        //echo $upmsPQquery."<BR>";
                        //exit;
                        $upmsPStmt = $DB_con->prepare($upmsPQquery);
                        $upmsPStmt->bindparam(":mem_MatCnt", $mtotMatCnt);
                        $upmsPStmt->bindparam(":mem_Point", $totRRPoint);
                        $upmsPStmt->bindparam(":mem_Id", $taxiOrdMemId);
                        $upmsPStmt->bindparam(":mem_Idx", $taxiOMemIdx);
                        $upmsPStmt->execute();
                    }
                }
                $profitMoney = number_format(floor($taxiSOrdPoint * ($levDc / 100)));
                $taxi_SMemo = date('Y-m-d H:i:s', time()) . '
투게더(' . $memRNickNm . ') 님이 메이커(' . $memNickNm . ')님에게 요청한 ' . number_format($taxiSOrdPoint) . '포인트에서 수수료 ' . $levDc . '%의 수익인 ' . number_format($profitMoney) . '포인트를 적립' . "";

                //본사 수익 내역 등록 여부 체크
                $cntPQuery = "";
                $cntPQuery = "SELECT count(idx)  AS num FROM TB_PROFIT_POINT WHERE taxi_SIdx = :taxi_SIdx AND taxi_RIdx = :taxi_RIdx AND taxi_OrdNo = :taxi_OrdNo AND taxi_MemId = :taxi_MemId  AND taxi_RMemId = :taxi_RMemId AND taxi_MemIdx = :taxi_MemIdx AND taxi_RMemIdx = :taxi_RMemIdx";
                $cntPStmt = $DB_con->prepare($cntPQuery);
                $cntPStmt->bindparam(":taxi_SIdx", $taxiSIdx);
                $cntPStmt->bindparam(":taxi_RIdx", $taxiRIdx);
                $cntPStmt->bindparam(":taxi_OrdNo", $taxiOrdNo);
                $cntPStmt->bindparam(":taxi_MemId", $taxiOrdSMemId);
                $cntPStmt->bindparam(":taxi_RMemId", $taxiOrdMemId);
                $cntPStmt->bindparam(":taxi_MemIdx", $taxiOSMemIdx);
                $cntPStmt->bindparam(":taxi_RMemIdx", $taxiOMemIdx);
                $cntPStmt->execute();
                $cntPRow = $cntPStmt->fetch(PDO::FETCH_ASSOC);
                $totalCnt = $cntPRow['num'];

                if ($totalCnt == "") {
                    $totalCnt = "0";
                } else {
                    $totalCnt =  $totalCnt;
                }

                //본사 수익 내역 중복 등록을 맞기 위해서 체크 함
                if ($totalCnt < 1) {

                    //본사 수익 내역 저장
                    $insQuery = "INSERT INTO TB_PROFIT_POINT (taxi_SIdx, taxi_RIdx, taxi_OrdNo, taxi_MemId, taxi_RMemId, taxi_MemIdx, taxi_RMemIdx, taxi_OrdSPoint, taxi_OrdTPoint, taxi_OrdMPoint, taxi_Memo, reg_Date)
             VALUES (:taxi_SIdx, :taxi_RIdx, :taxi_OrdNo, :taxi_MemId, :taxi_RMemId, :taxi_MemIdx, :taxi_RMemIdx, :taxi_OrdSPoint, :taxi_OrdTPoint, :taxi_OrdMPoint, :taxi_Memo, :reg_Date)";
                    //echo $insQuery."<BR>";
                    //exit;
                    $pstmt = $DB_con->prepare($insQuery);
                    $pstmt->bindParam("taxi_SIdx", $taxiSIdx);
                    $pstmt->bindParam("taxi_RIdx", $taxiRIdx);
                    $pstmt->bindParam("taxi_OrdNo", $taxiOrdNo);
                    $pstmt->bindParam("taxi_MemId", $taxiOrdSMemId);
                    $pstmt->bindParam("taxi_RMemId", $taxiOrdMemId);
                    $pstmt->bindparam("taxi_MemIdx", $taxiOSMemIdx);
                    $pstmt->bindparam("taxi_RMemIdx", $taxiOMemIdx);
                    $pstmt->bindParam("taxi_OrdSPoint", $profitMoney);
                    $pstmt->bindParam("taxi_OrdTPoint", $taxiSOrdPoint);
                    $pstmt->bindParam("taxi_OrdMPoint", $taxiPoint);
                    $pstmt->bindParam("taxi_Memo", $taxi_SMemo);
                    $pstmt->bindParam("reg_Date", $reg_Date);
                    $pstmt->execute();
                    $DB_con->lastInsertId();

                    //매칭생성 완료 상태로 변경
                    $upPQquery = "UPDATE TB_STAXISHARING SET taxi_State = '7' WHERE idx = :idx  LIMIT 1";
                    $upPStmt = $DB_con->prepare($upPQquery);
                    $upPStmt->bindparam(":idx", $taxiSIdx);
                    $upPStmt->execute();

                    //투게더 완료 날짜 업데이트
                    $upMQquery2 = "UPDATE TB_RTAXISHARING_INFO SET reg_YDate = :reg_YDate WHERE taxi_RIdx = :taxi_RIdx AND taxi_RMemId = :taxi_RMemId LIMIT 1";
                    $upMStmt2 = $DB_con->prepare($upMQquery2);
                    $upMStmt2->bindparam(":reg_YDate", $reg_Date);
                    $upMStmt2->bindparam(":taxi_RIdx", $taxiRIdx);
                    $upMStmt2->bindparam(":taxi_RMemId", $taxiOrdMemId);
                    $upMStmt2->execute();

                    //투게더 완료 상태로 변경
                    $upMQquery = "UPDATE TB_RTAXISHARING SET taxi_RState = '7' WHERE idx = :idx AND taxi_RMemId = :taxi_RMemId LIMIT 1";
                    $upMStmt = $DB_con->prepare($upMQquery);
                    $upMStmt->bindparam(":idx", $taxiRIdx);
                    $upMStmt->bindparam(":taxi_RMemId", $taxiOrdMemId);
                    $upMStmt->execute();

                    //주문서 신청 완료 상태 변경
                    $upOquery = "UPDATE TB_ORDER SET taxi_OrdState = '2'  WHERE taxi_OrdNo = :taxi_OrdNo  LIMIT 1";
                    $upOStmt = $DB_con->prepare($upOquery);
                    $upOStmt->bindparam(":taxi_OrdNo", $taxiOrdNo);
                    $upOStmt->execute();

                    $memMTokQuery = "SELECT mem_Token FROM TB_MEMBERS WHERE idx = :taxiOSMemIdx AND b_Disply = 'N'";
                    $memMTokStmt = $DB_con->prepare($memMTokQuery);
                    $memMTokStmt->bindparam(":taxiOSMemIdx", $taxiOSMemIdx);
                    $memMTokStmt->execute();
                    $memMTokNum = $memMTokStmt->rowCount();
                    if ($memMTokNum < 1) { //주 ID가 없을 경우 회원가입 시작
                    } else {  //등록된 회원이 있을 경우
                        while ($memMTokRow = $memMTokStmt->fetch(PDO::FETCH_ASSOC)) {
                            $mem_MToken[] = $memMTokRow["mem_Token"]; //토큰값
                        }
                    }

                    // 푸시를 보낸내역을 등록
                    $pushAddQuery = "UPDATE TB_RTAXISHARING_INFO SET taxi_MoveCnt = 2 WHERE taxi_RIdx = :taxi_RIdx LIMIT 1";
                    $pushAddStmt = $DB_con->prepare($pushAddQuery);
                    $pushAddStmt->bindparam(":taxi_RIdx", $taxi_RIdx);
                    $pushAddStmt->execute();

                    $mchkState = "7";  //거래완료
                    $mtitle = "🚐버스보다 빠르고 🚕택시보다 저렴하게";
                    $mmsg = "포인트가 정상적으로 적립되었습니다 이용해주셔서 감사합니다.";

                    foreach ($mem_MToken as $k => $v) {
                        $mtokens = $mem_MToken[$k];

                        //알림할 내용들을 취합해서 $data에 모두 담는다. 프로젝트 의도에 따라 다른게 더 있을 수 있다.
                        $minputData = array("title" => $mtitle, "msg" => $mmsg, "state" => $mchkState);

                        if($minputData["title"] != ""){
                            $title = $minputData["title"];
                        }else{
                            $title = "🚐버스보다 빠르고 🚕택시보다 저렴하게";
                        }
                        $msg = ($minputData["msg"] == "" ? "" : $minputData["msg"]);
                        $addmsg = ($minputData["addmsg"] == "" ? "" : $minputData["addmsg"]);
                        $state = ($minputData["state"] == "0" ? "" : $minputData["state"]);
                        $lat = ($minputData["lat"] == "" ? NULL : $minputData["lat"]);
                        $lng = ($minputData["lng"] == "" ? NULL : $minputData["lng"]);
                        $image = ($minputData["imageUrl"] == "" ? "" : $minputData["imageUrl"]);
                        $notice = ($minputData["id"] == "" ? NULL : $minputData["id"]);
                        $sharingIdx = ($minputData["sharingIdx"] == "" ? NULL : $minputData["sharingIdx"]);
                    
                        //푸시 사용 내역 (2: 새로고침, 9 :로그아웃, 997 : 채팅)
                        $insPsMQuery = "INSERT INTO TB_PUSH_HISTORY (mem_Idx, push_Title, push_Msg, push_AddMsg, push_Img, push_NoticeIdx, push_SharingIdx, push_State, push_Lat, push_Lng, reg_Date) VALUES (:mem_Idx, :push_Title, :push_Msg, :push_AddMsg, :push_Img, :push_NoticeIdx, :push_SharingIdx, :push_State, :push_Lat, :push_Lng, NOW())";
                        // echo $insPsQuery;
                        $insPsMStmt = $DB_con->prepare($insPsMQuery);
                        $insPsMStmt->bindparam(":mem_Idx", $taxiOSMemIdx);
                        $insPsMStmt->bindparam(":push_Title", $title);
                        $insPsMStmt->bindparam(":push_Msg", $msg);
                        $insPsMStmt->bindparam(":push_AddMsg", $addmsg);
                        $insPsMStmt->bindparam(":push_Img", $image);
                        $insPsMStmt->bindparam(":push_NoticeIdx", $notice);
                        $insPsMStmt->bindparam(":push_SharingIdx", $sharingIdx);
                        $insPsMStmt->bindparam(":push_State", $state);
                        $insPsMStmt->bindparam(":push_Lat", $lat);
                        $insPsMStmt->bindparam(":push_Lng", $lng);
                        $insPsMStmt->execute();

                        $pushUrl = "https://fcm.googleapis.com/fcm/send";
                        $headers = [];
                        $headers[] = 'Content-Type: application/json';
                        $headers[] = 'Authorization:key=' . GOOGLE_API_KEY;

                        $notification = [
                            'title' => $minputData["title"],
                            'body' => $minputData["msg"],
                            "state" => $minputData["state"]
                        ];
                        $extraNotificationData = ["message" => $notification];
                        $data = array(
                            "data" => $extraNotificationData,
                            "notification" => $notification,
                            "to"  => $mtokens, //token get on my ipad with the getToken method of cordova plugin,
                        );
                        //$json_data = json_encode($data);
                        $json_data =  json_encode($data, JSON_UNESCAPED_UNICODE);
                        //print_r($json_data);

                        $ch = curl_init();
                        curl_setopt($ch, CURLOPT_URL, $pushUrl);
                        curl_setopt($ch, CURLOPT_POST, true);
                        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                        curl_setopt($ch, CURLOPT_POSTFIELDS, $json_data);

                        $result = curl_exec($ch);

                        if ($result === FALSE) {
                            die('Curl failed: ' . curl_error($ch));
                        }
                        curl_close($ch);

                        sleep(1);
                    } //생성자 푸시 끝

                    $memRTokQuery = "SELECT mem_Token FROM TB_MEMBERS WHERE idx = :taxiOMemIdx AND b_Disply = 'N'";
                    $memRTokStmt = $DB_con->prepare($memRTokQuery);
                    $memRTokStmt->bindparam(":taxiOMemIdx", $taxiOMemIdx);
                    $memRTokStmt->execute();
                    $memRTokNum = $memRTokStmt->rowCount();
                    if ($memRTokNum < 1) { //주 ID가 없을 경우 회원가입 시작
                    } else {  //등록된 회원이 있을 경우
                        while ($memRTokRow = $memRTokStmt->fetch(PDO::FETCH_ASSOC)) {
                            $mem_RToken[] = $memRTokRow["mem_Token"]; //토큰값
                        }
                    }

                    $rchkState = "7";  //거래완료
                    $rtitle = "🚐버스보다 빠르고 🚕택시보다 저렴하게";
                    $rmsg = "포인트가 정상적으로 전달되었습니다. 이용해주셔서 감사합니다.";
                    foreach ($mem_RToken as $k2 => $v2) {
                        $rtokens = $mem_RToken[$k2];

                        //알림할 내용들을 취합해서 $data에 모두 담는다. 프로젝트 의도에 따라 다른게 더 있을 수 있다.
                        $rinputData = array("title" => $rtitle, "msg" => $rmsg, "state" => $rchkState);

                        if($rinputData["title"] != ""){
                            $title = $rinputData["title"];
                        }else{
                            $title = "🚐버스보다 빠르고 🚕택시보다 저렴하게";
                        }
                        $msg = ($rinputData["msg"] == "" ? "" : $rinputData["msg"]);
                        $addmsg = ($rinputData["addmsg"] == "" ? "" : $rinputData["addmsg"]);
                        $state = ($rinputData["state"] == "0" ? "" : $rinputData["state"]);
                        $lat = ($rinputData["lat"] == "" ? NULL : $rinputData["lat"]);
                        $lng = ($rinputData["lng"] == "" ? NULL : $rinputData["lng"]);
                        $image = ($rinputData["imageUrl"] == "" ? "" : $rinputData["imageUrl"]);
                        $notice = ($rinputData["id"] == "" ? NULL : $rinputData["id"]);
                        $sharingIdx = ($rinputData["sharingIdx"] == "" ? NULL : $rinputData["sharingIdx"]);
                    
                        //푸시 사용 내역 (2: 새로고침, 9 :로그아웃, 997 : 채팅)
                        $insPsRQuery = "INSERT INTO TB_PUSH_HISTORY (mem_Idx, push_Title, push_Msg, push_AddMsg, push_Img, push_NoticeIdx, push_SharingIdx, push_State, push_Lat, push_Lng, reg_Date) VALUES (:mem_Idx, :push_Title, :push_Msg, :push_AddMsg, :push_Img, :push_NoticeIdx, :push_SharingIdx, :push_State, :push_Lat, :push_Lng, NOW())";
                        // echo $insPsQuery;
                        $insPsRStmt = $DB_con->prepare($insPsRQuery);
                        $insPsRStmt->bindparam(":mem_Idx", $taxiOMemIdx);
                        $insPsRStmt->bindparam(":push_Title", $title);
                        $insPsRStmt->bindparam(":push_Msg", $msg);
                        $insPsRStmt->bindparam(":push_AddMsg", $addmsg);
                        $insPsRStmt->bindparam(":push_Img", $image);
                        $insPsRStmt->bindparam(":push_NoticeIdx", $notice);
                        $insPsRStmt->bindparam(":push_SharingIdx", $sharingIdx);
                        $insPsRStmt->bindparam(":push_State", $state);
                        $insPsRStmt->bindparam(":push_Lat", $lat);
                        $insPsRStmt->bindparam(":push_Lng", $lng);
                        $insPsRStmt->execute();

                        $pushUrl = "https://fcm.googleapis.com/fcm/send";
                        $headers = [];
                        $headers[] = 'Content-Type: application/json';
                        $headers[] = 'Authorization:key=' . GOOGLE_API_KEY;

                        $notification = [
                            'title' => $rinputData["title"],
                            'body' => $rinputData["msg"],
                            "state" => $rinputData["state"]
                        ];
                        $extraNotificationData = ["message" => $notification];
                        $data = array(
                            "data" => $extraNotificationData,
                            "notification" => $notification,
                            "to"  => $rtokens, //token get on my ipad with the getToken method of cordova plugin,
                        );
                        //$json_data = json_encode($data);
                        $json_data =  json_encode($data, JSON_UNESCAPED_UNICODE);
                        //print_r($json_data);

                        $ch = curl_init();
                        curl_setopt($ch, CURLOPT_URL, $pushUrl);
                        curl_setopt($ch, CURLOPT_POST, true);
                        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                        curl_setopt($ch, CURLOPT_POSTFIELDS, $json_data);

                        $result = curl_exec($ch);

                        if ($result === FALSE) {
                            die('Curl failed: ' . curl_error($ch));
                        }
                        curl_close($ch);

                        sleep(1);
                    }
                    //요청자 푸시 끝
                }

                //파이어베이스에 해당 노선의 종료값 전송
                fire_Complete_Set($taxiSIdx);

                if ($taxi_OrdType == "0" || $taxi_OrdType == "1") {
                    // 추천인이 있는지 확인
                    $member_Ch_Query = "SELECT mem_ChCode FROM TB_MEMBERS_ETC WHERE mem_Idx = :mem_Idx";
                    $member_Ch_Stmt = $DB_con->prepare($member_Ch_Query);
                    $member_Ch_Stmt->bindparam(":mem_Idx", $taxiOMemIdx);
                    $member_Ch_Stmt->execute();
                    $member_Ch_Num = $member_Ch_Stmt->rowCount();
                    if ($member_Ch_Num > 0) {
                        $member_Ch_Row = $member_Ch_Stmt->fetch(PDO::FETCH_ASSOC);
                        $mem_Ch_Idx = $member_Ch_Row['mem_ChCode'];         //추천한 회원 고유번호
                        // 추천인이 있다면 적립% 확인
                        $config_Query = "SELECT con_ChPoint FROM TB_CONFIG";
                        $config_Stmt = $DB_con->prepare($config_Query);
                        $config_Stmt->execute();
                        $config_Num = $config_Stmt->rowCount();
                        if ($config_Num > 0) {
                            $config_Row = $config_Stmt->fetch(PDO::FETCH_ASSOC);
                            $con_ChPoint = $config_Row['con_ChPoint'];         //공통 추천 적립율
                        }
                        $mem_Ch_Rate_Query = "SELECT mem_ChRate FROM TB_MEMBERS_ETC WHERE mem_Idx = :mem_Ch_Idx";
                        $mem_Ch_Rate_Stmt = $DB_con->prepare($mem_Ch_Rate_Query);
                        $mem_Ch_Rate_Stmt->bindparam(":mem_Ch_Idx", $mem_Ch_Idx);
                        $mem_Ch_Rate_Stmt->execute();
                        $mem_Ch_Rate_Num = $mem_Ch_Rate_Stmt->rowCount();
                        if ($mem_Ch_Rate_Num > 0) {
                            $mem_Ch_Rate_Row = $mem_Ch_Rate_Stmt->fetch(PDO::FETCH_ASSOC);
                            $mem_ChRate = $mem_Ch_Rate_Row['mem_ChRate'];         //개인 추천 적립율
                        }
                        if ($mem_ChRate == 0) {
                            $chPointRate = $con_ChPoint;
                        } else {
                            $chPointRate = $mem_ChRate;
                        }
                        $pointRate = $chPointRate / 100;
                        $chPoint = $pointRate * $taxi_OrdPrice;     // 카드 결제 금액에 대해서만 초대한 사람에게 적립
                        //회원아이디 조회하기.
                        $member_Sel_Query = "SELECT m.mem_Id, me.mem_Point FROM TB_MEMBERS AS m INNER JOIN TB_MEMBERS_ETC AS me ON m.idx = me.mem_Idx WHERE m.idx = :mem_Ch_Idx";
                        $member_Sel_Stmt = $DB_con->prepare($member_Sel_Query);
                        $member_Sel_Stmt->bindparam(":mem_Ch_Idx", $mem_Ch_Idx);
                        $member_Sel_Stmt->execute();
                        $member_Sel_Num = $member_Sel_Stmt->rowCount();
                        if ($member_Sel_Num > 0) {
                            $member_Sel_Row = $member_Sel_Stmt->fetch(PDO::FETCH_ASSOC);
                            $mem_Ch_Id = $member_Sel_Row['mem_Id'];         //추천한 회원 고유번호
                            $mem_Ch_Point = $member_Sel_Row['mem_Point'];         //추천한 회원 고유번호
                            $memChPoint = $mem_Ch_Point + $chPoint;

                            // 적립%만큼 추천인에게 포인트 적립하기
                            $mem_Etc_Ch_Query = "UPDATE TB_MEMBERS_ETC SET mem_Point = :mem_Point WHERE mem_Idx = :mem_Ch_Idx LIMIT 1";
                            $mem_Etc_Ch_Stmt = $DB_con->prepare($mem_Etc_Ch_Query);
                            $mem_Etc_Ch_Stmt->bindparam(":mem_Point", $memChPoint);
                            $mem_Etc_Ch_Stmt->bindparam(":mem_Ch_Idx", $mem_Ch_Idx);
                            $mem_Etc_Ch_Stmt->execute();
                        }

                        // 적립한 포인트 푸시내역 남기기.
                        $taxi_Ch_Sign = "0"; // +기호
                        $taxi_Ch_State = "3"; //추천인적립
                        $taxi_Ch_Memo = date('Y-m-d H:i:s', time()) . '
추천인의 매칭성공으로 인한 포인트 적립';
                        $m_Ch_Query = "INSERT INTO TB_POINT_HISTORY (taxi_SIdx, taxi_RIdx, taxi_OrdNo, taxi_MemId, taxi_MemIdx, taxi_OrdPoint, taxi_OrgPoint, taxi_Memo, taxi_Sign, taxi_PState, taxi_OrdType, reg_Date) VALUES (:taxi_SIdx, :taxi_RIdx, :taxi_OrdNo, :taxi_MemId, :taxi_MemIdx, :taxi_OrdPoint, :taxi_OrgPoint, :taxi_Memo, :taxi_Sign, :taxi_PState, :taxi_OrdType, :reg_Date)";
                        //echo $insQuery."<BR>";
                        //exit;
                        $m_Ch_Stmt = $DB_con->prepare($m_Ch_Query);
                        $m_Ch_Stmt->bindParam("taxi_SIdx", $taxiSIdx);
                        $m_Ch_Stmt->bindParam("taxi_RIdx", $taxiRIdx);
                        $m_Ch_Stmt->bindParam("taxi_OrdNo", $taxiOrdNo);
                        $m_Ch_Stmt->bindParam("taxi_MemId", $mem_Ch_Id);
                        $m_Ch_Stmt->bindParam("taxi_MemIdx", $mem_Ch_Idx);
                        $m_Ch_Stmt->bindParam("taxi_OrdPoint", $chPoint);
                        $m_Ch_Stmt->bindParam("taxi_OrgPoint", $mem_Ch_Point);
                        $m_Ch_Stmt->bindParam("taxi_Memo", $taxi_Ch_Memo);
                        $m_Ch_Stmt->bindParam("taxi_Sign", $taxi_Ch_Sign);
                        $m_Ch_Stmt->bindParam("taxi_PState", $taxi_Ch_State);
                        $m_Ch_Stmt->bindParam("taxi_OrdType", $taxi_OrdType);
                        $m_Ch_Stmt->bindParam("reg_Date", $reg_Date);
                        $m_Ch_Stmt->execute();
                    }
                }
            }
            // 시간 차이를 계산해서 정각인 경우 10분 뒤 자동 양도 안내.
        } else if ((int)$total_Time == 0) {

            // 푸시를 보낸내역을 등록
            $pushAddQuery = "UPDATE TB_RTAXISHARING_INFO SET taxi_MoveCnt = 1 WHERE taxi_RIdx = :taxi_RIdx LIMIT 1";
            $pushAddStmt = $DB_con->prepare($pushAddQuery);
            $pushAddStmt->bindparam(":taxi_RIdx", $taxi_RIdx);
            $pushAddStmt->execute();

            //메이커 푸시 보내기.
            $mSidQuery = "SELECT mem_Os, mem_MPush, mem_Token from TB_MEMBERS WHERE idx = :mem_Idx AND b_Disply = 'N' ";
            $mSidStmt = $DB_con->prepare($mSidQuery);
            $mSidStmt->bindparam(":mem_Idx", $taxi_MemIdx);
            $mSidStmt->execute();
            $mSidNum = $mSidStmt->rowCount();

            if ($mSidNum < 1) { //아닐경우
            } else {
                while ($mSidRow = $mSidStmt->fetch(PDO::FETCH_ASSOC)) {
                    $mem_MToken[] = $mSidRow["mem_Token"]; //토큰값
                    $rmemOs = $mSidRow['mem_Os'];         //os구분  (0 : 안드로이드, 1: 아이폰)
                    $rmemMPush = $mSidRow['mem_MPush'];     //푸시발송여부  (0 : 발송, 1: 발송불가)

                    $rchkState = "0";  
                    $rtitle = "🚐버스보다 빠르고 🚕택시보다 저렴하게";
                    $rmsg = "잘도착하셨나요? 10분후 포인트가 적립될 예정입니다.";


                    foreach ($mem_MToken as $k => $v) {
                        $mtokens = $mem_MToken[$k];

                        //알림할 내용들을 취합해서 $data에 모두 담는다. 프로젝트 의도에 따라 다른게 더 있을 수 있다.
                        $minputData = array("title" => $rtitle, "msg" => $rmsg, "state" => $rchkState);

                        if($minputData["title"] != ""){
                            $title = $minputData["title"];
                        }else{
                            $title = "🚐버스보다 빠르고 🚕택시보다 저렴하게";
                        }
                        $msg = ($minputData["msg"] == "" ? "" : $minputData["msg"]);
                        $addmsg = ($minputData["addmsg"] == "" ? "" : $minputData["addmsg"]);
                        $state = ($minputData["state"] == "0" ? "" : $minputData["state"]);
                        $lat = ($minputData["lat"] == "" ? NULL : $minputData["lat"]);
                        $lng = ($minputData["lng"] == "" ? NULL : $minputData["lng"]);
                        $image = ($minputData["imageUrl"] == "" ? "" : $minputData["imageUrl"]);
                        $notice = ($minputData["id"] == "" ? NULL : $minputData["id"]);
                        $sharingIdx = ($minputData["sharingIdx"] == "" ? NULL : $minputData["sharingIdx"]);
                    
                        //푸시 사용 내역 (2: 새로고침, 9 :로그아웃, 997 : 채팅)
                        $insPsMQuery = "INSERT INTO TB_PUSH_HISTORY (mem_Idx, push_Title, push_Msg, push_AddMsg, push_Img, push_NoticeIdx, push_SharingIdx, push_State, push_Lat, push_Lng, reg_Date) VALUES (:mem_Idx, :push_Title, :push_Msg, :push_AddMsg, :push_Img, :push_NoticeIdx, :push_SharingIdx, :push_State, :push_Lat, :push_Lng, NOW())";
                        // echo $insPsQuery;
                        $insPsMStmt = $DB_con->prepare($insPsMQuery);
                        $insPsMStmt->bindparam(":mem_Idx", $taxi_MemIdx);
                        $insPsMStmt->bindparam(":push_Title", $title);
                        $insPsMStmt->bindparam(":push_Msg", $msg);
                        $insPsMStmt->bindparam(":push_AddMsg", $addmsg);
                        $insPsMStmt->bindparam(":push_Img", $image);
                        $insPsMStmt->bindparam(":push_NoticeIdx", $notice);
                        $insPsMStmt->bindparam(":push_SharingIdx", $sharingIdx);
                        $insPsMStmt->bindparam(":push_State", $state);
                        $insPsMStmt->bindparam(":push_Lat", $lat);
                        $insPsMStmt->bindparam(":push_Lng", $lng);
                        $insPsMStmt->execute();

                        $pushUrl = "https://fcm.googleapis.com/fcm/send";
                        $headers = [];
                        $headers[] = 'Content-Type: application/json';
                        $headers[] = 'Authorization:key=' . GOOGLE_API_KEY;

                        $notification = [
                            'title' => $minputData["title"],
                            'body' => $minputData["msg"],
                            "state" => $minputData["state"]
                        ];
                        $extraNotificationData = ["message" => $notification];
                        $data = array(
                            "data" => $extraNotificationData,
                            "notification" => $notification,
                            "to"  => $mtokens, //token get on my ipad with the getToken method of cordova plugin,
                        );
                        //$json_data = json_encode($data);
                        $json_data =  json_encode($data, JSON_UNESCAPED_UNICODE);
                        //print_r($json_data);

                        $ch = curl_init();
                        curl_setopt($ch, CURLOPT_URL, $pushUrl);
                        curl_setopt($ch, CURLOPT_POST, true);
                        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                        curl_setopt($ch, CURLOPT_POSTFIELDS, $json_data);

                        $result = curl_exec($ch);

                        if ($result === FALSE) {
                            die('Curl failed: ' . curl_error($ch));
                        }
                        curl_close($ch);

                        sleep(1);
                    }
                }
            } //메이커 푸시 끝
            
            //투게더 푸시 보내기
            $rSidQuery = "SELECT mem_Os, mem_MPush, mem_Token from TB_MEMBERS WHERE idx = :mem_Idx AND b_Disply = 'N'; ";
            $rSidStmt = $DB_con->prepare($rSidQuery);
            $rSidStmt->bindparam(":mem_Idx", $taxi_RMemIdx);
            $rSidStmt->execute();
            $rSidNum = $rSidStmt->rowCount();

            if ($rSidNum < 1) { //아닐경우
            } else {
                while ($rSidRow = $rSidStmt->fetch(PDO::FETCH_ASSOC)) {
                    $mem_RToken[] = $rSidRow["mem_Token"]; //토큰값
                    $rmemOs = $rSidRow['mem_Os'];         //os구분  (0 : 안드로이드, 1: 아이폰)
                    $rmemMPush = $rSidRow['mem_MPush'];     //푸시발송여부  (0 : 발송, 1: 발송불가)

                    $rchkState = "0"; 
                    $rtitle = "🚐버스보다 빠르고 🚕택시보다 저렴하게";
                    $rmsg = "잘도착하셨나요? 10분후 포인트가 상대방에게 전달될 예정입니다.";


                    foreach ($mem_RToken as $k2 => $v2) {
                        $rtokens = $mem_RToken[$k2];

                        //알림할 내용들을 취합해서 $data에 모두 담는다. 프로젝트 의도에 따라 다른게 더 있을 수 있다.
                        $rinputData = array("title" => $rtitle, "msg" => $rmsg, "state" => $rchkState);

                        if($rinputData["title"] != ""){
                            $title = $rinputData["title"];
                        }else{
                            $title = "🚐버스보다 빠르고 🚕택시보다 저렴하게";
                        }
                        $msg = ($rinputData["msg"] == "" ? "" : $rinputData["msg"]);
                        $addmsg = ($rinputData["addmsg"] == "" ? "" : $rinputData["addmsg"]);
                        $state = ($rinputData["state"] == "0" ? "" : $rinputData["state"]);
                        $lat = ($rinputData["lat"] == "" ? NULL : $rinputData["lat"]);
                        $lng = ($rinputData["lng"] == "" ? NULL : $rinputData["lng"]);
                        $image = ($rinputData["imageUrl"] == "" ? "" : $rinputData["imageUrl"]);
                        $notice = ($rinputData["id"] == "" ? NULL : $rinputData["id"]);
                        $sharingIdx = ($rinputData["sharingIdx"] == "" ? NULL : $rinputData["sharingIdx"]);
                    
                        //푸시 사용 내역 (2: 새로고침, 9 :로그아웃, 997 : 채팅)
                        $insPsRQuery = "INSERT INTO TB_PUSH_HISTORY (mem_Idx, push_Title, push_Msg, push_AddMsg, push_Img, push_NoticeIdx, push_SharingIdx, push_State, push_Lat, push_Lng, reg_Date) VALUES (:mem_Idx, :push_Title, :push_Msg, :push_AddMsg, :push_Img, :push_NoticeIdx, :push_SharingIdx, :push_State, :push_Lat, :push_Lng, NOW())";
                        // echo $insPsQuery;
                        $insPsRStmt = $DB_con->prepare($insPsRQuery);
                        $insPsRStmt->bindparam(":mem_Idx", $taxiOMemIdx);
                        $insPsRStmt->bindparam(":push_Title", $title);
                        $insPsRStmt->bindparam(":push_Msg", $msg);
                        $insPsRStmt->bindparam(":push_AddMsg", $addmsg);
                        $insPsRStmt->bindparam(":push_Img", $image);
                        $insPsRStmt->bindparam(":push_NoticeIdx", $notice);
                        $insPsRStmt->bindparam(":push_SharingIdx", $sharingIdx);
                        $insPsRStmt->bindparam(":push_State", $state);
                        $insPsRStmt->bindparam(":push_Lat", $lat);
                        $insPsRStmt->bindparam(":push_Lng", $lng);
                        $insPsRStmt->execute();

                        $pushUrl = "https://fcm.googleapis.com/fcm/send";
                        $headers = [];
                        $headers[] = 'Content-Type: application/json';
                        $headers[] = 'Authorization:key=' . GOOGLE_API_KEY;

                        $notification = [
                            'title' => $rinputData["title"],
                            'body' => $rinputData["msg"],
                            "state" => $rinputData["state"]
                        ];
                        $extraNotificationData = ["message" => $notification];
                        $data = array(
                            "data" => $extraNotificationData,
                            "notification" => $notification,
                            "to"  => $rtokens, //token get on my ipad with the getToken method of cordova plugin,
                        );
                        //$json_data = json_encode($data);
                        $json_data =  json_encode($data, JSON_UNESCAPED_UNICODE);
                        //print_r($json_data);

                        $ch = curl_init();
                        curl_setopt($ch, CURLOPT_URL, $pushUrl);
                        curl_setopt($ch, CURLOPT_POST, true);
                        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                        curl_setopt($ch, CURLOPT_POSTFIELDS, $json_data);

                        $result = curl_exec($ch);

                        if ($result === FALSE) {
                            die('Curl failed: ' . curl_error($ch));
                        }
                        curl_close($ch);

                        sleep(1);
                    }
                }
            } //투게더 푸시 보내기 끝
        } else {
        }

        $cnt++;
    }
    if ($cnt == 0) {
        $result = array("result" => false, "errorMsg" => "노선은 있으나 조건에 만족하는 매칭 노선이 없습니다.");
    } else {
        $result = array("result" => true, "cnt" => (int)$cnt);
    }
}

dbClose($DB_con);
$Stmt = null;
$chkLocStmt1 = null;
$chkLocStmt2 = null;
$upMStmt11 = null;
$upMStmt22 = null;
$upMStmt33 = null;
$Stmt3 = null;
$delMStmt = null;
$conStmt = null;
$chkRStmt = null;
$alDkchkStmt = null;
$alDkdelRStmt = null;
$alDkdelRStmt2 = null;
$alDkdelRStmt3 = null;
$alDkdelStmt = null;
$alDkdelStmt2 = null;
$alDkdelStmt3 = null;
$selRStmt = null;
$rSidStmt = null;
$memRTokStmt = null;
echo "
" . str_replace('\\/', '/', json_encode($result, JSON_UNESCAPED_UNICODE));


?>