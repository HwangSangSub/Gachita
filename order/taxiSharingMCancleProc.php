<?

/*======================================================================================================================

* 프로그램			: 매칭 생성자, 요청자 이동 중 취소 상태 접수 진행 후 상대방 취소 거절, 무응답 처리 화면
* 페이지 설명		: 매칭 생성자, 요청자 이동 중 취소 상태 접수 진행 후 상대방 취소 거절, 무응답 처리 화면
* 파일명                 : taxiSharingMCancleProc.php
*
========================================================================================================================*/

include "../lib/common.php";
include "../lib/functionDB.php";  //공통 db함수

$chkIdx = trim($chkIdx);       // 취소신청 고유번호
$part = trim($part);           // 구분 (1 : 거래취소를 원하지 않습니다, 3: 기타 (5분 초과 미응답))

if ($chkIdx != "" && $part != "") {  //취소신청 고유번호, 구분값이  있을 경우

    $DB_con = db1();

    $regDate = DU_TIME_YMDHIS;  //시간등록

    $cancleCQuery = "SELECT idx FROM TB_CANCLE_REASON WHERE cancle_Idx = :cancle_Idx ORDER BY idx DESC LIMIT 1;";
    $cancleCStmt = $DB_con->prepare($cancleCQuery);
    $cancleCStmt->bindparam(":cancle_Idx", $chkIdx);
    $cancleCStmt->execute();
    while ($cancleCRow = $cancleCStmt->fetch(PDO::FETCH_ASSOC)) {
        $last_CancleIdx = $cancleCRow['idx'];     // 해당노선의 취소사유 테이블 고유번호 조회 (최근등록) ==> 한개의 노선에서 취소요청은 동시에 진행되지 않아 처리함
    }

    /*사유기록*/
    $cancleUQuery = "
		UPDATE TB_CANCLE_REASON SET cancle_CanRChk = 'N', cancle_CRPart = :cancle_CRPart WHERE idx = :idx;";
    $cancleUStmt = $DB_con->prepare($cancleUQuery);
    $cancleUStmt->bindparam(":idx", $last_CancleIdx);
    $cancleUStmt->bindparam(":cancle_CRPart", $part);
    $cancleUStmt->execute();

    //거절 횟수 및 기타 정보
    $cntQuery = "";
    $cntQuery = "SELECT taxi_CanCnt, taxi_CanRCnt, taxi_SIdx, taxi_MemId, taxi_MemIdx, taxi_RIdx, taxi_RMemId, taxi_RMemIdx, taxi_MType, taxi_OMType, taxi_CPart FROM TB_SMATCH_STATE WHERE idx = :idx  LIMIT 1 ";
    $cntStmt = $DB_con->prepare($cntQuery);
    $cntStmt->bindparam(":idx", $chkIdx);
    $cntStmt->execute();
    $cntNum = $cntStmt->rowCount();

    if ($cntStmt < 1) { //아닐경우
        $taxi_CanCnt = "0";
    } else {
        while ($cntRow = $cntStmt->fetch(PDO::FETCH_ASSOC)) {
            $taxi_CanCnt = $cntRow['taxi_CanCnt'];     // 거절 횟수(최초요청자)
            $taxi_CanRCnt = $cntRow['taxi_CanRCnt'];   // 거절 횟수(그 후 요청자)
            $taxiSIdx =  $cntRow['taxi_SIdx'];         // 생성자 고유번호
            $taxiMemId =  $cntRow['taxi_MemId'];       // 생성자 아이디
            $taxiMemIdx =  $cntRow['taxi_MemIdx'];       // 생성자 아이디
            $taxiRIdx =  $cntRow['taxi_RIdx'];         // 요청자 고유번호
            $taxiRMemId =  $cntRow['taxi_RMemId'];     // 요청자 아이디
            $taxiRMemIdx =  $cntRow['taxi_RMemIdx'];     // 요청자 아이디
            $taxiMType = $cntRow['taxi_MType'];        // 생성자 :p, 요청자 : c
            $taxiOMType = $cntRow['taxi_OMType'];      // 생성자 :p, 요청자 : c
            $taxiCPart = $cntRow['taxi_CPart'];        // 취소 사유
        }
    }

    if ($taxiMType == "p") { //취소 요청자가 생성자일 경우

        if ($taxiCPart == "3") { //상대방 사유로 인한 취소
            $taxiCSIdx = $taxiRIdx;       //매칭요청 고유번호
            $taxiCMemId = $taxiRMemId;     //매칭요청 아이디
            $taxiCMemIdx = $taxiRMemIdx;     //매칭요청 아이디
            $taxiCRIdx = $taxiSIdx;       //매칭생성 고유번호
            $taxiCRMemId = $taxiMemId;   //매칭생성 아이디
            $taxiCRMemIdx = $taxiMemIdx;   //매칭생성 아이디
            $taxiMcancle = "1";     //요청자 본인 취소
            $taxiRMCancle = "1";    //요청자가 취소(요청자)
        } else {
            $taxiCSIdx = $taxiSIdx;       //매칭생성 고유번호
            $taxiCMemId = $taxiMemId;     //매칭생성 아이디
            $taxiCMemIdx = $taxiMemIdx;     //매칭생성 아이디
            $taxiCRIdx = $taxiRIdx;       //매칭요청 고유번호
            $taxiCRMemId = $taxiRMemId;   //매칭요청 아이디
            $taxiCRMemIdx = $taxiRMemIdx;   //매칭요청 아이디
            $taxiMcancle = "0";     //생성자 본인 취소
            $taxiRMCancle = "1";    //생성자가 취소(요청자)
        }
    } else { //취소 요청자가 요청자일 경우

        if ($taxiCPart == "3") { //상대방 사유로 인한 취소
            $taxiCSIdx = $taxiSIdx;       //매칭생성 고유번호
            $taxiCMemId = $taxiMemId;     //매칭생성 아이디
            $taxiCMemIdx = $taxiMemIdx;     //매칭생성 아이디
            $taxiCRIdx = $taxiRIdx;       //매칭요청 고유번호
            $taxiCRMemId = $taxiRMemId;   //매칭요청 아이디
            $taxiCRMemIdx = $taxiRMemIdx;   //매칭요청 아이디
            $taxiMcancle = "0";     //요청자 본인 취소
            $taxiRMCancle = "1";    //메이커가 취소(요청자)
        } else {
            $taxiCSIdx = $taxiRIdx;      //매칭요청 고유번호
            $taxiCMemId = $taxiRMemId;   //매칭요청 아이디
            $taxiCMemIdx = $taxiRMemIdx;   //매칭요청 아이디
            $taxiCRIdx = $taxiSIdx;      //매칭생성 고유번호
            $taxiCRMemId = $taxiMemId;   //매칭생성 아이디
            $taxiCRMemIdx = $taxiMemIdx;   //매칭생성 아이디
            $taxiMcancle = "1";     //생성자 본인 취소
            $taxiRMCancle = "1";    //생성자가 취소(요청자)
        }
    }

    //거절 횟수 조회
    if ($part == "3") {
        $chkSUp = "2";
    } else {
        if ($taxiOMType != "") {  //두번째 취소 일 경우
            if ($taxiMType != $taxiOMType) { //이전 취소요청 타입이 다를경우
                $chkUp = "1"; //새로운 요청타입
                $taxiCanCnt = $taxi_CanRCnt + 1;
            } else {
                $chkUp = "0";
                $taxiCanCnt = $taxi_CanCnt + 1;
            }
        } else { //없을 경우
            $chkUp = "0";
            $taxiCanCnt = $taxi_CanCnt + 1;
        }


        if ($taxiOMType != "") {  //두번째 취소 일 경우
            if ($taxi_CanCnt == $taxiCanCnt) {
                $chkSUp = "2";  //최초 거절자 거절횟수 1, 두번째 거절 횟수 1
            }
        }
    }
    if ($taxiCanCnt < "3") { //거절횟수가 2번일 경우

        if ($chkUp == "0") {
            //거절 업데이트
            $upCQquery = "UPDATE TB_SMATCH_STATE SET taxi_Disply = 'N', taxi_CanRChk ='N', taxi_CRPart = :taxi_CRPart, taxi_CanCnt = :taxi_CanCnt, reg_CRDate = :reg_CRDate WHERE idx = :idx  LIMIT 1";
            $upCStmt = $DB_con->prepare($upCQquery);
            $upCStmt->bindparam(":taxi_CRPart", $part);
            $upCStmt->bindparam(":taxi_CanCnt", $taxiCanCnt);
            $upCStmt->bindparam(":reg_CRDate", $regDate);
            $upCStmt->bindparam(":idx", $chkIdx);
            $upCStmt->execute();
        } else { //다를 경우 (요청값이 다를 경우)

            //거절 업데이트
            $upCQquery = "UPDATE TB_SMATCH_STATE SET taxi_Disply = 'N', taxi_CanRChk ='N', taxi_CRPart = :taxi_CRPart, taxi_CanRCnt = :taxi_CanRCnt, taxi_OMType = :taxi_OMType, reg_CRDate = :reg_CRDate WHERE idx = :idx  LIMIT 1";
            $upCStmt = $DB_con->prepare($upCQquery);
            $upCStmt->bindparam(":taxi_CRPart", $part);
            $upCStmt->bindparam(":taxi_CanRCnt", $taxiCanCnt);
            $upCStmt->bindparam(":taxi_OMType", $taxiMType);
            $upCStmt->bindparam(":reg_CRDate", $regDate);
            $upCStmt->bindparam(":idx", $chkIdx);
            $upCStmt->execute();
        }
    }

    if ($taxiCanCnt == "3") {
        $taxiCanCnt = "2";
    }

    if ($taxiCanCnt == "2" || $chkSUp == "2" || $part == "3") { //거절횟수가 2번일 경우, 최초 거절자 거절횟수 1, 두번째 거절 횟수 1

        //투게더 거래취소확인 상태 변경
        $upMQquery = "UPDATE TB_RTAXISHARING SET taxi_CanChk = 'N', taxi_RState = '9' WHERE idx = :idx AND taxi_RMemIdx = :taxi_RMemIdx AND taxi_RState = '6' LIMIT 1";   //이동중 거래취소확인 상태 변경
        //echo $upMQquery."<BR>";
        //exit;
        $upMStmt = $DB_con->prepare($upMQquery);
        $upMStmt->bindparam(":idx", $taxiRIdx);
        $upMStmt->bindparam(":taxi_RMemIdx", $taxiRMemIdx);
        $upMStmt->execute();


        //투게더 취소 기타 변경
        $upMQquery2 = "UPDATE TB_RTAXISHARING_INFO SET taxi_MCancle = :taxi_MCancle, taxi_MState = '6', reg_CMDate = :reg_CMDate WHERE taxi_RIdx = :taxi_RIdx AND taxi_RMemIdx = :taxi_RMemIdx LIMIT 1";
        //echo $upMQquery2."<BR>";
        //exit;
        $upMStmt2 = $DB_con->prepare($upMQquery2);
        $upMStmt2->bindparam(":taxi_MCancle", $taxiRMCancle);
        $upMStmt2->bindparam(":reg_CMDate", $regDate);
        $upMStmt2->bindparam(":taxi_RIdx", $taxiRIdx);
        $upMStmt2->bindparam(":taxi_RMemIdx", $taxiRMemIdx);
        $upMStmt2->execute();


        //메이커 이동중 거래 취소 상태로 변경
        $upPQquery = "UPDATE TB_STAXISHARING SET taxi_State = '9', taxi_MCancle = :taxi_MCancle, taxi_MState = '6', reg_CMDate = :reg_CMDate WHERE idx = :idx  AND taxi_MemIdx = :taxi_MemIdx AND taxi_State = '6' LIMIT 1";
        //echo $upPQquery."<BR>";
        $upPStmt = $DB_con->prepare($upPQquery);
        $upPStmt->bindparam(":taxi_MCancle", $taxiMcancle);
        $upPStmt->bindparam(":reg_CMDate", $regDate);
        $upPStmt->bindparam(":idx", $taxiSIdx);
        $upPStmt->bindparam(":taxi_MemIdx", $taxiMemIdx);
        $upPStmt->execute();

        //푸시보내기
        if ($taxiMemIdx != "") {

            /*푸시 관련 시작*/
            $mem_Token = memMatchTokenInfo($taxiMemIdx);

            $title = "";
            $msg = "취소처리확인 건으로 변경되었습니다. (이용내역에서 확인가능)";

            foreach ($mem_Token as $k => $v) {
                $tokens = $mem_Token[$k];
                $inputData = array("title" => $title, "msg" => $msg, "state" => "0");
                $presult = send_Push($tokens, $inputData);
            }
        }

        if ($taxiRMemIdx != "") {

            /*푸시 관련 시작*/
            $mem_MDToken = memMatchTokenInfo($taxiRMemIdx);

            $mDtitle = "";
            $mDmsg = "취소처리확인 건으로 변경되었습니다. (이용내역에서 확인가능)";

            foreach ($mem_MDToken as $k => $v) {
                $mDtokens = $mem_MDToken[$k];
                $mDinputData = array("title" => $mDtitle, "msg" => $mDmsg, "state" => "0");
                $mDpresult = send_Push($mDtokens, $mDinputData);
            }
        }
    }

    if ($chkUp == "0") {
        $result = array("result" => true, "chkIdx" => (int)$chkIdx, "taxiCanCnt" => (int)$taxiCanCnt, "taxiCSIdx" => (int)$taxiCSIdx, "taxiCMemId" => (string)$taxiCMemId, "taxiCRIdx" => (int)$taxiCRIdx, "taxiCRMemId" => (string)$taxiCRMemId, "regDate" => (string)$regDate);
    } else {
        $result = array("result" => true, "chkIdx" => (int)$chkIdx, "taxiCanCnt" => (int)$taxi_CanCnt, "taxiRCanCnt" => (int)$taxiCanCnt, "taxiCSIdx" => (int)$taxiCSIdx, "taxiCMemId" => (string)$taxiCMemId, "taxiCRIdx" => (int)$taxiCRIdx, "taxiCRMemId" => (string)$taxiCRMemId, "regDate" => (string)$regDate);
    }


    dbClose($DB_con);
    $cntStmt = null;
    $upCStmt = null;
    $upMStmt = null;
    $upMStmt2 = null;
    $upPStmt = null;
    $cancleCStmt = null;
    $cancleUStmt = null;

    echo str_replace('\\/', '/', json_encode($result, JSON_UNESCAPED_UNICODE));
} else {
    $result = array("result" => false, "errorMsg" => "조회정보값이 없습니다. 확인 후 다시 시도해주세요.");
    echo json_encode($result, JSON_UNESCAPED_UNICODE);
}
