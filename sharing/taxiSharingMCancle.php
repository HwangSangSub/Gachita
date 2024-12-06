<?

/*======================================================================================================================

* 프로그램			: 매칭 생성자, 요청자 이동 중 취소 상태 접수 팝업
* 페이지 설명		: 매칭 생성자, 요청자 이동 중 취소 상태 접수 팝업
* 파일명            : taxiSharingMCancle.php

========================================================================================================================*/


include "../lib/common.php";
include "../lib/functionDB.php";  //공통 db함수

$idx = trim($idx);           //매칭생성,요청 고유번호
$cidx = trim($chkIdx);   //매칭 1번 거절후 넘어올 매칭 취소 고유번호
$mode = trim($mode);       //구분  (p: 생성자, c: 신청자)

if ($idx != "" && $mode != "") {  //매칭생성 고유번호, 구분값이  있을 경우

    $DB_con = db1();

    //기타 정보
    $conQuery = "";
    $conQuery = "SELECT con_mTxt FROM TB_CONFIG WHERE 1 = 1 LIMIT 1"; //기타문구
    $conStmt = $DB_con->prepare($conQuery);
    $conStmt->execute();
    $conNum = $conStmt->rowCount();
    //echo $conNum."<BR>";
    //exit;

    if ($conNum < 1) { //아닐경우
    } else {
        while ($conRow = $conStmt->fetch(PDO::FETCH_ASSOC)) {
            $conMTxt = trim($conRow['con_mTxt']);                    // 취소 안내 문구
        }

        $conMTxt = str_replace("\r", "", $conMTxt);
    }

    if ($mode == "p") { //생성자일 경우
        $viewQuery = "";
        $viewQuery = "SELECT taxi_MemId FROM TB_STAXISHARING WHERE idx = :idx AND taxi_State = '6' LIMIT 1 ";  //이동중일경우
        //echo $viewQuery."<BR>";
        //exit;
        $viewStmt = $DB_con->prepare($viewQuery);
        $viewStmt->bindparam(":idx", $idx);
        $viewStmt->execute();
        $num = $viewStmt->rowCount();
        //echo $num."<BR>";

        if ($num < 1) { //아닐경우
            $result = array("result" => "error", "errorMsg" => "잘못된 접근입니다. 현재 매칭 중인 이동 노선이 없습니다.");
            echo str_replace('\\/', '/', json_encode($result, JSON_UNESCAPED_UNICODE));
            exit;
        } else {
            while ($row = $viewStmt->fetch(PDO::FETCH_ASSOC)) {
                $taxiMemId =  trim($row['taxi_MemId']);    // 생성자아이디
            }
            $mem_Idx = memIdxInfo($taxiMemId);   //회원 고유번호

            //거절 1번후 상태 체크
            $chkSQuery = "";
            if ($cidx != "") { //매칭취소 고유번호가 있을 경우
                $chkSQuery = "SELECT idx, taxi_MType, taxi_Disply FROM TB_SMATCH_STATE WHERE idx = :idx LIMIT 1 ";
            } else {
                $chkSQuery = "SELECT idx, taxi_MType, taxi_Disply FROM TB_SMATCH_STATE WHERE taxi_SIdx = :taxi_SIdx LIMIT 1 ";
            }

            //echo $viewQuery."<BR>";
            //exit;
            $chkSQStmt = $DB_con->prepare($chkSQuery);

            if ($cidx != "") { //매칭취소 고유번호가 있을 경우
                $chkSQStmt->bindparam(":idx", $cidx);
            } else {
                $chkSQStmt->bindparam(":taxi_SIdx", $idx);
            }


            $chkSQStmt->execute();
            $chkSQNum = $chkSQStmt->rowCount();

            if ($chkSQNum < 1) { //없을 경우엔 저장

                // 저장여부 확인
                $chkQuery = "";
                $chkQuery = "INSERT INTO TB_SMATCH_STATE (taxi_SIdx, taxi_MemId, taxi_MemIdx, taxi_MType, taxi_CanChk, taxi_Disply ) ";
                $chkQuery .= " SELECT :taxi_SIdx, :taxi_MemId, :taxi_MemIdx, :taxi_MType, 'Y', 'Y' FROM DUAL ";
                $chkQuery .= " WHERE NOT EXISTS (SELECT * FROM TB_SMATCH_STATE WHERE taxi_MType = :taxi_MType AND taxi_CanChk = 'Y' AND taxi_SIdx = :taxi_SIdx AND taxi_MemId = :taxi_MemId AND taxi_MemIdx = :taxi_MemIdx) ";

                //echo $chkQuery."<BR>";
                //exit;
                $chkStmt = $DB_con->prepare($chkQuery);
                $chkStmt->bindparam(":taxi_SIdx", $idx);
                $chkStmt->bindparam(":taxi_MemId", $taxiMemId);
                $chkStmt->bindparam(":taxi_MemIdx", $mem_Idx);
                $chkStmt->bindparam(":taxi_MType", $mode);
                $chkStmt->execute();
                $chkNum = $chkStmt->rowCount();
                $DB_con->lastInsertId();

                $mIdx = $DB_con->lastInsertId();  //저장된 idx 값

                if ($chkNum < 1) { //아닐경우

                    //취소신청 여부
                    $viewQuery2 = "";
                    $viewQuery2 = "SELECT idx FROM TB_SMATCH_STATE WHERE taxi_SIdx = :taxi_SIdx AND taxi_Disply = 'Y' LIMIT 1 ";
                    //echo $viewQuery."<BR>";
                    //exit;
                    $viewStmt2 = $DB_con->prepare($viewQuery2);
                    if ($mode == "p") { //생성자일 경우
                        $viewStmt2->bindparam(":taxi_SIdx", $idx);
                    } else { //요청자일 경우
                        $viewStmt2->bindparam(":taxi_SIdx", $taxiSIdx);
                    }
                    $viewStmt2->execute();
                    $vNum = $viewStmt2->rowCount();
                    //echo $vNum."<BR>";

                    if ($vNum < 1) { //아닐경우
                    } else {
                        while ($vrow = $viewStmt2->fetch(PDO::FETCH_ASSOC)) {
                            $chkIdx =  trim($vrow['idx']);    // 취소신청 고유번호
                        }
                    }

                    $result = array("result" => false, "errorMsg" => "상대방이 취소 요청중인  상태입니다.", "chkIdx" => (int)$chkIdx);
                } else {
                    $chkIdx =  $mIdx;    //취소신청 고유번호
                    $result = array("result" => true, "conMTxt" => (string)$conMTxt, "chkIdx" => (int)$chkIdx);
                }
            } else { //등록된 정보가 있을 경우

                while ($chkSqRow = $chkSQStmt->fetch(PDO::FETCH_ASSOC)) {
                    $chkIdx =  trim($chkSqRow['idx']);               // 취소신청 고유번호
                    $taxiOMType =  trim($chkSqRow['taxi_MType']);    // 요청자 예전값
                    $chkDisply =  trim($chkSqRow['taxi_Disply']);    // 상태여부 (Y : 취소불가, N : 취소 상태가능)
                }

                if ($chkDisply == "Y") { //취소불가
                    $result = array("result" => false, "errorMsg" => "상대방이 취소 요청중인  상태입니다..", "chkIdx" => (int)$chkIdx);
                } else {

                    //투게더 변경
                    $upMQquery = "UPDATE TB_SMATCH_STATE SET taxi_MType = 'p', taxi_OMType = :taxi_OMType, taxi_Disply = 'Y' WHERE idx = :idx LIMIT 1";
                    $upMStmt = $DB_con->prepare($upMQquery);
                    $upMStmt->bindparam(":taxi_OMType", $taxiOMType);
                    $upMStmt->bindparam(":idx", $chkIdx);
                    $upMStmt->execute();

                    $result = array("result" => true, "conMTxt" => (string)$conMTxt, "chkIdx" => (int)$chkIdx);
                }
            }
        }
    } else { //요청자일 경우

        $viewQuery = "";
        $viewQuery = "SELECT idx, taxi_SIdx, taxi_MemId, taxi_RMemId FROM TB_RTAXISHARING WHERE idx = :idx AND taxi_RState = '6' LIMIT 1 ";  //이동중일경우
        //echo $viewQuery."<BR>";
        //` exit;
        $viewStmt = $DB_con->prepare($viewQuery);
        $viewStmt->bindparam(":idx", $idx);
        $viewStmt->execute();
        $num = $viewStmt->rowCount();
        //echo $num."<BR>";
        // exit;
        if ($num < 1) { //아닐경우
            $result = array("result" => false, "errorMsg" => "잘못된 접근입니다. 현재 매칭 중인 이동 노선이 없습니다.");
            echo str_replace('\\/', '/', json_encode($result, JSON_UNESCAPED_UNICODE));
            exit;
        } else {

            while ($row = $viewStmt->fetch(PDO::FETCH_ASSOC)) {
                $taxiSIdx =  trim($row['taxi_SIdx']);      // 생성자 고유번호
                $taxiMemId =  trim($row['taxi_MemId']);    // 생성자 아이디
                $taxiRIdx =  trim($row['idx']);            // 요청자 고유번호
                $taxiRMemId =  trim($row['taxi_RMemId']);  // 요청자 아이디
            }
            $mem_Idx = memIdxInfo($taxiMemId);   //생성회원 고유번호
            $mem_RIdx = memIdxInfo($taxiRMemId);   //요청회원 고유번호


            //거절 1번후 상태 체크
            $chkSQuery = "";
            if ($cidx != "") { //매칭취소 고유번호가 있을 경우
                $chkSQuery = "SELECT idx, taxi_MType, taxi_Disply FROM TB_SMATCH_STATE WHERE idx = :idx LIMIT 1 ";
            } else {
                $chkSQuery = "SELECT idx, taxi_MType, taxi_Disply FROM TB_SMATCH_STATE WHERE taxi_SIdx = :taxi_SIdx LIMIT 1 ";
            }


            //echo $viewQuery."<BR>";
            //exit;
            $chkSQStmt = $DB_con->prepare($chkSQuery);

            if ($cidx != "") { //매칭취소 고유번호가 있을 경우
                $chkSQStmt->bindparam(":idx", $cidx);
            } else {
                $chkSQStmt->bindparam(":taxi_SIdx", $taxiSIdx);
            }


            $chkSQStmt->execute();
            $chkSQNum = $chkSQStmt->rowCount();

            if ($chkSQNum < 1) { //없을 경우엔 저장

                // 저장여부 확인
                $chkQuery = "";
                $chkQuery = "INSERT INTO TB_SMATCH_STATE (taxi_RIdx, taxi_RMemId, taxi_RMemIdx, taxi_SIdx, taxi_MemId, taxi_MemIdx, taxi_MType, taxi_CanChk, taxi_Disply) ";
                $chkQuery .= " SELECT :taxi_RIdx, :taxi_RMemId, :taxi_RMemIdx, :taxi_SIdx, :taxi_MemId, :taxi_MemIdx, :taxi_MType, 'Y', 'Y' FROM DUAL ";
                $chkQuery .= " WHERE NOT EXISTS (SELECT * FROM TB_SMATCH_STATE WHERE taxi_MType = :taxi_MType AND taxi_CanChk = 'Y' AND taxi_SIdx = :taxi_SIdx AND taxi_MemId = :taxi_MemId AND taxi_MemIdx = :taxi_MemIdx) ";

                //echo $chkQuery."<BR>";
                //exit;
                $chkStmt = $DB_con->prepare($chkQuery);
                $chkStmt->bindparam(":taxi_RIdx", $taxiRIdx);
                $chkStmt->bindparam(":taxi_RMemId", $taxiRMemId);
                $chkStmt->bindparam(":taxi_RMemIdx", $mem_RIdx);
                $chkStmt->bindparam(":taxi_SIdx", $taxiSIdx);
                $chkStmt->bindparam(":taxi_MemId", $taxiMemId);
                $chkStmt->bindparam(":taxi_MemIdx", $mem_Idx);
                $chkStmt->bindparam(":taxi_MType", $mode);
                $chkStmt->execute();
                $chkNum = $chkStmt->rowCount();
                $DB_con->lastInsertId();

                $mIdx = $DB_con->lastInsertId();  //저장된 idx 값

                if ($chkNum < 1) { //아닐경우

                    //취소신청 여부
                    $viewQuery2 = "";
                    $viewQuery2 = "SELECT idx FROM TB_SMATCH_STATE WHERE taxi_SIdx = :taxi_SIdx AND taxi_Disply = 'Y' LIMIT 1 ";
                    //echo $viewQuery."<BR>";
                    //exit;
                    $viewStmt2 = $DB_con->prepare($viewQuery2);
                    if ($mode == "p") { //생성자일 경우
                        $viewStmt2->bindparam(":taxi_SIdx", $idx);
                    } else { //요청자일 경우
                        $viewStmt2->bindparam(":taxi_SIdx", $taxiSIdx);
                    }
                    $viewStmt2->execute();
                    $vNum = $viewStmt2->rowCount();
                    //echo $vNum."<BR>";

                    if ($vNum < 1) { //아닐경우
                    } else {
                        while ($vrow = $viewStmt2->fetch(PDO::FETCH_ASSOC)) {
                            $chkIdx =  trim($vrow['idx']);    // 취소신청 고유번호
                        }
                    }

                    $result = array("result" => false, "errorMsg" => "상대방이 취소 요청중인  상태입니다...", "chkIdx" => (int)$chkIdx);
                } else {
                    $chkIdx =  $mIdx;    //취소신청 고유번호
                    $result = array("result" => true, "conMTxt" => (string)$conMTxt, "chkIdx" => (int)$chkIdx);
                }
            } else {
                while ($chkSqRow = $chkSQStmt->fetch(PDO::FETCH_ASSOC)) {
                    $chkIdx =  trim($chkSqRow['idx']);               // 취소신청 고유번호
                    $taxiOMType =  trim($chkSqRow['taxi_MType']);    // 요청자 예전값
                    $chkDisply =  trim($chkSqRow['taxi_Disply']);    // 상태여부 (Y : 취소불가, N : 취소 상태가능)
                }

                if ($chkDisply == "Y") { //취소불가
                    $result = array("result" => false, "errorMsg" => "상대방이 취소 요청중인  상태입니다....", "chkIdx" => (int)$chkIdx);
                } else {


                    //투게더 변경
                    $upMQquery = "UPDATE TB_SMATCH_STATE SET taxi_RIdx = :taxi_RIdx, taxi_RMemId = :taxi_RMemId, taxi_MType = 'c', taxi_OMType = :taxi_OMType, taxi_Disply = 'Y' WHERE idx = :idx LIMIT 1";
                    $upMStmt = $DB_con->prepare($upMQquery);
                    $upMStmt->bindparam(":taxi_RIdx", $taxiRIdx);
                    $upMStmt->bindparam(":taxi_RMemId", $taxiRMemId);
                    $upMStmt->bindparam(":taxi_OMType", $taxiOMType);
                    $upMStmt->bindparam(":idx", $chkIdx);
                    $upMStmt->execute();

                    $result = array("result" => true, "conMTxt" => (string)$conMTxt, "chkIdx" => (int)$chkIdx);
                }
            }
        }
    }



    dbClose($DB_con);
    $conStmt = null;
    $viewStmt = null;
    $chkStmt = null;
    $viewStmt2 = null;
    $upMStmt = null;

    echo str_replace('\\/', '/', json_encode($result, JSON_UNESCAPED_UNICODE));
} else {
    $result = array("result" => false, "errorMsg" => "조회 정보값이 없습니다. 관리자에게 문의바랍니다.");
    echo json_encode($result, JSON_UNESCAPED_UNICODE);
}
