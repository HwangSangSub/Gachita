<?
/*======================================================================================================================

* 프로그램			: 회원탈퇴처리
* 페이지 설명		: 회원탈퇴처리
* 파일명                 : memberWithdrawalProc.php

========================================================================================================================*/

include "../lib/common.php";
include "../lib/functionDB.php";  //공통 db함수
include "../lib/functionWithdrawal.php";  //회원탈퇴 관련
include "../lib/functionMatCancle.php";  //생성자 취소관련
include "../lib/functionMatRCancle.php"; //요청자 취소관련

$mem_Id = trim($memId);                //아이디
$mem_Idx = memIdxInfo($mem_Id);   //회원 주아이디
if ($mem_Id != "") {  //아이디가 있을 경우
    $DB_con = db1();

    //매칭 대기 건수
    $chkCntRNum = sharingRCnt($mem_Idx);

    if ($chkCntRNum < "1") { //매칭요청건이 없을 경우

        //매칭 생성 테이블 조건값 체크
        $chkCntNum = sharingCnt($mem_Idx);
        if ($chkCntNum < 1) { //없을 경우 회원관련 탈퇴

            //회원 이미지 삭제
            memImgDel($mem_Idx);

            /*회원 탈퇴 및 기본정보 저장 */
            $chkProcNum = memUPDate($mem_Idx);

            if ($chkProcNum == "0") {
                $result = array("result" => false, "errorMsg" => "이미 탈퇴한 회원 혹은 가입되지 않은 회원 입니다.");
            } else {
                $msg = "회원이 정상적으로 탈퇴 처리되었습니다. 저희 가치타을 이용해 주셔서 감사합니다.";
                $result = array("result" => true, "msg" => (string)$msg);
            }
        } else { //셍성중인거나 진행중인 경우가 있을 경우

            //현재 상태값 가져오기(생성자)
            $chkMatState = matStateInfo($mem_Idx);

            if ($chkMatState != "") {

                $chkTaxiMIdx = $chkMatState['taxiMIdx']; //고유아이디
                $chkTaxiMState = $chkMatState['taxiMState']; //상태값

                if ($chkTaxiMState == "1" || $chkTaxiMState == "2" || $chkTaxiMState == "3") { //매칭중. 매칭요청, 예약요청
                    $chkMcancleNum = sharingACancle($mem_Idx, $chkTaxiMIdx);
                } else {

                    //매칭 생성 테이블 조건값 체크
                    $chkMsg = matChkState($mem_Idx);
                    $chkMcancleNum = "2"; //회원 삭제할 필요 없음.
                    $result = array("result" => false, "errorMsg" => "생성노선 (" . $chkMsg . ")이 진행중으로 탈퇴를 할 수 없습니다.");
                }

                if ($chkMcancleNum == "1") { //노선 취소 처리후 회원 삭제

                    //회원 이미지 삭제
                    memImgDel($mem_Id);

                    /*회원 탈퇴 및 기본정보 저장 */
                    $chkProcNum = memUPDate($mem_Id);

                    if ($chkProcNum == "0") {
                        $result = array("result" => false, "errorMsg" => "이미 탈퇴한 회원 혹은 가입되지 않은 회원 입니다.");
                    } else {
                        $msg = "회원이 정상적으로 탈퇴 처리되었습니다. 저희 가치타을 이용해 주셔서 감사합니다.";
                        $result = array("result" => true, "msg" => (string)$msg);
                    }
                }
            } else {
                $chkTaxiMIdx = "";
                $chkTaxiMState = "";
            }
        }
    } else { //매칭요청 건이 있을 경우

        $chkCntRNum = sharingRCnt($mem_Idx);
        $chkCntNum = sharingCnt($mem_Idx);

        if ($chkCntRNum != "0" && $chkCntNum != "0") { //매칭생성, 매칭요청 값이 다 있을 경우

            //매칭 생성 테이블 조건값 체크
            $chkMsg = matChkState($mem_Idx);

            //매칭 요청 테이블 조건값 체크
            $chkRMsg = matChkRState($mem_Idx);

            $result = array("result" => false, "errorMsg" => "생성노선 (" . $chkMsg . "), 요청노선 (" . $chkRMsg . ")이 진행중으로 탈퇴를 할 수 없습니다.");
        } else if ($chkCntRNum != "0") { //매칭요청에만 있을 경우

            //매칭 요청 테이블 조건값 체크
            $chkRMsg = matChkRState($mem_Idx);

            if ($chkRMsg == "1") { //노선 취소 처리후 회원 삭제

                //회원 이미지 삭제
                memImgDel($mem_Idx);

                /*회원 탈퇴 및 기본정보 저장 */
                $chkProcNum = memUPDate($mem_Idx);

                if ($chkProcNum == "0") {
                    $result = array("result" => false, "errorMsg" => "이미 탈퇴한 회원 혹은 가입되지 않은 회원 입니다.");
                } else {
                    $msg = "회원이 정상적으로 탈퇴 처리되었습니다. 저희 가치타을 이용해 주셔서 감사합니다.";
                    $result = array("result" => true, "msg" => $msg);
                }
            } else {
                $result = array("result" => false, "errorMsg" => "요청노선 (" . $chkRMsg . ")이 진행중으로 탈퇴를 할 수 없습니다.");
            }
        } else if ($chkCntNum != "0") { //매칭생성에만 있을 경우(일단보류)
            //매칭 생성 테이블 조건값 체크
        }
    }

    dbClose($DB_con);
    $chkCntRNum = null;
    $chkCntStmt = null;
    $chkMStmt = null;
    $chkStmt = null;
    $chkCntStmt2 = null;
    $chkStmt2 = null;
    $chkRStmt = null;

    echo str_replace('\\/', '/', json_encode($result, JSON_UNESCAPED_UNICODE));
}
