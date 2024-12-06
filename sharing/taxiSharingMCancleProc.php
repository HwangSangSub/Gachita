<?

/*======================================================================================================================

* 프로그램			: 매칭 생성자, 요청자 이동 중 취소 상태 접수 처리 화면
* 페이지 설명		: 매칭 생성자, 요청자 이동 중 취소 상태 접수 처리 화면
* 파일명                 : taxiSharingMCancleProc.php

========================================================================================================================*/

include "../lib/common.php";
include "../lib/functionDB.php";  //공통 db함수

$chkIdx = trim($chkIdx);        // 취소신청 고유번호
$part = trim($part);        // 구분 (  1 : 택시가 잡히지 않습니다.   2 : 나의 사정으로 취소합니다.   3 : 메이커의 사정으로 취소합니다. 4 : 취소를 원하지 않습니다)

if ($chkIdx != "" && $part != "") {  //취소신청 고유번호, 구분값이  있을 경우

    $DB_con = db1();

    if ($part == "4") { //취소 신청 취소
        //취소 요청 테이블 취소 확인자 추가
        $chkCntQquery = "SELECT taxi_CanCnt, taxi_CanRCnt FROM TB_SMATCH_STATE WHERE idx = :idx LIMIT 1;";
        $chkCntStmt = $DB_con->prepare($chkCntQquery);
        $chkCntStmt->bindparam(":idx", $chkIdx);
        $chkCntStmt->execute();
        while ($chkCntrow = $chkCntStmt->fetch(PDO::FETCH_ASSOC)) {
            $taxi_CanCnt =  $chkCntrow['taxi_CanCnt'];        // 취소 횟수
            if ($taxi_CanCnt == "") {
                $taxiCanCnt = 0;
            } else {
                $taxiCanCnt = $taxi_CanCnt;
            }
            $taxi_CanRCnt =  $chkCntrow['taxi_CanRCnt'];    // 요청자 취소 횟수
            if ($taxi_CanRCnt == "") {
                $taxiCanRCnt = 0;
            } else {
                $taxiCanRCnt = $taxi_CanRCnt;
            }
            $tot_CanCnt = (int)$taxiCanCnt + (int)$taxiCanRCnt;
        }
        if ($tot_CanCnt > 0) {
            //취소신청 테이블 삭제
            $delQquery = "UPDATE TB_SMATCH_STATE SET taxi_Disply = 'N' WHERE idx = :idx LIMIT 1 ";
            $delStmt = $DB_con->prepare($delQquery);
            $delStmt->bindparam(":idx", $chkIdx);
            $delStmt->execute();
        } else {
            //취소신청 테이블 삭제
            $delQquery = "DELETE FROM TB_SMATCH_STATE WHERE idx = :idx LIMIT 1 ";
            $delStmt = $DB_con->prepare($delQquery);
            $delStmt->bindparam(":idx", $chkIdx);
            $delStmt->execute();
        }
        $result = array("result" => true, "chkIdx" => (int)$chkIdx);
    } else { //4번을 제외한 경우

        $regDate = DU_TIME_YMDHIS;  //시간등록

        //회원 정보 보여줌
        $viewQuery = "";
        $viewQuery = "SELECT taxi_SIdx, taxi_MemId, taxi_MemIdx, taxi_RIdx, taxi_RMemId, taxi_RMemIdx, taxi_MType, taxi_CanChk, taxi_CanRChk, taxi_CPart, taxi_CRPart, taxi_CMemo FROM TB_SMATCH_STATE WHERE idx = :idx AND taxi_CanChk = 'Y' LIMIT 1 ";
        //echo $viewQuery."<BR>";
        //exit;
        $viewStmt = $DB_con->prepare($viewQuery);
        $viewStmt->bindparam(":idx", $chkIdx);
        $viewStmt->execute();
        $num = $viewStmt->rowCount();
        //echo $num."<BR>";

        if ($num < 1) { //아닐경우
        } else {
            while ($row = $viewStmt->fetch(PDO::FETCH_ASSOC)) {
                $taxiSIdx =  trim($row['taxi_SIdx']);                // 생성자 고유번호
                $taxiMemId =  trim($row['taxi_MemId']);       // 생성자 아이디
                $taxiMemIdx =  trim($row['taxi_MemIdx']);       // 생성자 아이디
                $taxiRIdx =  trim($row['taxi_RIdx']);               // 요청자 고유번호
                $taxiRMemId =  trim($row['taxi_RMemId']);  // 요청자 아이디
                $taxiRMemIdx =  trim($row['taxi_RMemIdx']);  // 요청자 아이디
                $taxiMType = trim($row['taxi_MType']);        // 생성자 :p, 요청자 : c
                $taxiCanChk = trim($row['taxi_CanChk']);        // 취소여부( Y,N )
                $taxiCanRChk = trim($row['taxi_CanRChk']);        // 취소동의여부( Y,N )
                $taxiCPart = trim($row['taxi_CPart']);        // 취소사유(1,2,3,4)
                $taxiCRPart = trim($row['taxi_CRPart']);        // 취소동의사유(1,2)
                $taxiCMemo = trim($row['taxi_CMemo']);        // 기타 취소 사유 메모
                if ($taxiMType == "p") {
                    $MemId = $taxiMemId;
                    $MemIdx = $taxiMemIdx;
                } else {
                    $MemId = $taxiRMemId;
                    $MemIdx = $taxiRMemIdx;
                }
            }

            /*사유기록*/
            $cancleRQuery = "
				INSERT INTO TB_CANCLE_REASON (cancle_Idx, cancle_MemId, cancle_MemIdx, cancle_MType, cancle_CanChk, cancle_CanRChk, cancle_CPart, cancle_CRPart, cancle_CMemo, reg_Date) 
				VALUES (:cancle_Idx, :cancle_MemId, :cancle_MemIdx, :cancle_MType, :cancle_CanChk, :cancle_CanRChk, :cancle_CPart, :cancle_CRPart, :cancle_CMemo, :reg_Date)";
            $cancleRStmt = $DB_con->prepare($cancleRQuery);
            $cancleRStmt->bindparam(":cancle_Idx", $chkIdx);
            $cancleRStmt->bindparam(":cancle_MemId", $MemId);
            $cancleRStmt->bindparam(":cancle_MemIdx", $MemIdx);
            $cancleRStmt->bindparam(":cancle_MType", $taxiMType);
            $cancleRStmt->bindparam(":cancle_CanChk", $taxiCanChk);
            $cancleRStmt->bindparam(":cancle_CanRChk", $taxiCanRChk);
            $cancleRStmt->bindparam(":cancle_CPart", $part);
            $cancleRStmt->bindparam(":cancle_CRPart", $taxiCRPart);
            $cancleRStmt->bindparam(":cancle_CMemo", $taxiCMemo);
            $cancleRStmt->bindparam(":reg_Date", $regDate);
            $cancleRStmt->execute();
            $cancleRNum = $cancleRStmt->rowCount();
            $DB_con->lastInsertId();

            $cancleIdx = $DB_con->lastInsertId();  //저장된 idx 값


            if ($taxiMType == "p") { //취소 요청자가 생성자일 경우

                //요청자 정보 가져오기
                $infoRQuery = "SELECT idx, taxi_RMemId, taxi_RMemIdx from TB_RTAXISHARING WHERE taxi_SIdx = :taxi_SIdx AND taxi_RState = '6'  LIMIT 1 ";
                $infoRStmt = $DB_con->prepare($infoRQuery);
                $infoRStmt->bindparam(":taxi_SIdx", $taxiSIdx);
                $infoRStmt->execute();
                $infoRNum = $infoRStmt->rowCount();

                if ($infoRNum < 1) { //아닐경우
                } else {
                    while ($infoRow = $infoRStmt->fetch(PDO::FETCH_ASSOC)) {
                        $taxiRIdx =  trim($infoRow['idx']);              // 요청자 고유번호
                        $taxiRMemId =  trim($infoRow['taxi_RMemId']);    // 요청자 아이디
                        $taxiRMemIdx =  trim($infoRow['taxi_RMemIdx']);    // 요청자 아이디
                    }

                    //취소 요청 테이블 취소 확인자 추가
                    $upMQquery = "UPDATE TB_SMATCH_STATE SET taxi_RIdx = :taxi_RIdx, taxi_RMemId = :taxi_RMemId WHERE idx = :idx  LIMIT 1";
                    $upMStmt = $DB_con->prepare($upMQquery);
                    $upMStmt->bindparam(":taxi_RIdx", $taxiRIdx);
                    $upMStmt->bindparam(":taxi_RMemId", $taxiRMemId);
                    $upMStmt->bindparam(":idx", $chkIdx);
                    $upMStmt->execute();
                }
            }


            if ($taxiMType == "p") { //취소 요청자가 생성자일 경우

                if ($part == "3") { //상대방 사유로 인한 취소
                    $taxiCSIdx = $taxiRIdx;       //매칭요청 고유번호
                    $taxiCMemId = $taxiRMemId;     //매칭요청 아이디
                    $taxiCRIdx = $taxiSIdx;       //매칭생성 고유번호
                    $taxiCRMemId = $taxiMemId;   //매칭생성 아이디
                } else {
                    $taxiCSIdx = $taxiSIdx;       //매칭생성 고유번호
                    $taxiCMemId = $taxiMemId;     //매칭생성 아이디
                    $taxiCRIdx = $taxiRIdx;       //매칭요청 고유번호
                    $taxiCRMemId = $taxiRMemId;   //매칭요청 아이디
                }
            } else { //취소 요청자가 요청자일 경우

                if ($part == "3") { //상대방 사유로 인한 취소
                    $taxiCSIdx = $taxiSIdx;       //매칭생성 고유번호
                    $taxiCMemId = $taxiMemId;     //매칭생성 아이디
                    $taxiCRIdx = $taxiRIdx;       //매칭요청 고유번호
                    $taxiCRMemId = $taxiRMemId;   //매칭요청 아이디
                } else {
                    $taxiCSIdx = $taxiRIdx;      //매칭요청 고유번호
                    $taxiCMemId = $taxiRMemId;   //매칭요청 아이디
                    $taxiCRIdx = $taxiSIdx;      //매칭생성 고유번호
                    $taxiCRMemId = $taxiMemId;   //매칭생성 아이디
                }
            }



            //취소 요청 테이블 변경
            $upPQquery = "UPDATE TB_SMATCH_STATE SET taxi_CPart = :taxi_CPart, reg_CDate = :reg_CDate WHERE idx = :idx  LIMIT 1";
            $upPStmt = $DB_con->prepare($upPQquery);
            $upPStmt->bindparam(":taxi_CPart", $part);
            $upPStmt->bindparam(":reg_CDate", $regDate);
            $upPStmt->bindparam(":idx", $chkIdx);
            $upPStmt->execute();
        }

        $result = array("result" => true, "chkIdx" => (int)$chkIdx, "taxiMType" => (string)$taxiMType, "taxiCSIdx" => (int)$taxiCSIdx, "taxiCMemId" => (string)$taxiCMemId, "taxiCRIdx" => (int)$taxiCRIdx, "taxiCRMemId" => (string)$taxiCRMemId, "regDate" => (string)$regDate);
    }

    dbClose($DB_con);
    $delStmt = null;
    $viewStmt = null;
    $upCPStmt = null;
    $infoRStmt = null;
    $upMStmt = null;
    $upPStmt = null;

    echo str_replace('\\/', '/', json_encode($result, JSON_UNESCAPED_UNICODE));
} else {
    $result = array("result" => false, "errorMsg" => "조회 정보값이 없습니다. 관리자에게 문의바랍니다.");
    echo json_encode($result, JSON_UNESCAPED_UNICODE);
}
