<?

/*======================================================================================================================

* 프로그램			: 매칭 생성자, 요청자 이동 중 취소 상태 접수 진행 후 상대방 확인 창 팝업
* 페이지 설명		: 매칭 생성자, 요청자 이동 중 취소 상태 접수 진행 후 상대방 확인 창 팝업
* 파일명                 : taxiSharingMRCancle.php

========================================================================================================================*/


include "../lib/common.php";
include "../lib/functionDB.php";  //공통 db함수

$mem_Id = trim($memId);        //아이디
$idx = trim($idx);            //매칭생성,요청 고유번호
//$chkIdx = trim($chkIdx);	// 취소신청 고유번호
$mode = trim($mode);        //구분  (p: 생성자, c: 신청자)


if ($mem_Id != "" && $idx != "" && $mode != "") {  //회원아이디, 매칭생성,요청 고유번호, 구분값이  있을 경우

    $DB_con = db1();

    //취소정보
    $viewQuery = "";
    if ($mode == "p") { //생성자일 경우
        $viewQuery = "SELECT idx, taxi_CPart, taxi_SIdx, taxi_MemId, taxi_RIdx, taxi_RMemId, taxi_MType FROM TB_SMATCH_STATE WHERE taxi_MemId = :mem_Id AND taxi_SIdx = :idx AND taxi_CanChk = 'Y' LIMIT 1 ";
    } else { //요청자일 경우
        $viewQuery = "SELECT idx, taxi_CPart, taxi_SIdx, taxi_MemId, taxi_RIdx, taxi_RMemId, taxi_MType FROM TB_SMATCH_STATE WHERE taxi_RMemId = :mem_Id AND taxi_RIdx = :idx AND taxi_CanChk = 'Y' LIMIT 1 ";
    }
    //echo $viewQuery."<BR>";
    //exit;
    $viewStmt = $DB_con->prepare($viewQuery);
    $viewStmt->bindparam(":idx", $idx);
    $viewStmt->bindparam(":mem_Id", $mem_Id);
    $viewStmt->execute();
    $num = $viewStmt->rowCount();
    //echo $num."<BR>";

    if ($num < 1) { //아닐경우
        $result = array("result" => false, "errorMsg" => "잘못된 접근입니다. 요청중인 취소건이 없습니다.");
        echo str_replace('\\/', '/', json_encode($result, JSON_UNESCAPED_UNICODE));
        exit;
    } else {
        while ($row = $viewStmt->fetch(PDO::FETCH_ASSOC)) {
            $chkIdx =  trim($row['idx']);                 // 취소 고유번호
            $taxiCPart = trim($row['taxi_CPart']);        // 취소 사유
            $taxiSIdx =  trim($row['taxi_SIdx']);         // 생성자 고유번호
            $taxiMemId =  trim($row['taxi_MemId']);       // 생성자 아이디
            $taxiRIdx =  trim($row['taxi_RIdx']);         // 요청자 고유번호
            $taxiRMemId =  trim($row['taxi_RMemId']);     // 요청자 아이디
            $taxiMType = trim($row['taxi_MType']);        // 생성자 :p, 요청자 : c

        }
    }


    if ($taxiMType == "p") { //취소 요청자가 생성자일 경우
        $taxiCSIdx = $taxiSIdx;       //매칭생성 고유번호
        $taxiCMemId = $taxiMemId;     //매칭생성 아이디
        $taxiCRIdx = $taxiRIdx;       //매칭요청 고유번호
        $taxiCRMemId = $taxiRMemId;   //매칭요청 아이디
    } else { //취소 요청자가 요청자일 경우
        $taxiCSIdx = $taxiRIdx;       //매칭요청 고유번호
        $taxiCMemId = $taxiRMemId;     //매칭요청 아이디
        $taxiCRIdx = $taxiSIdx;       //매칭생성 고유번호
        $taxiCRMemId = $taxiMemId;   //매칭생성 아이디
    }


    //회원정보
    $memQuery = "";
    $memQuery = "SELECT mem_NickNm FROM TB_MEMBERS WHERE mem_Id = :mem_Id  LIMIT 1 ";
    $memStmt = $DB_con->prepare($memQuery);
    $memStmt->bindparam(":mem_Id", $taxiCRMemId);
    $memStmt->execute();
    $memNum = $memStmt->rowCount();

    if ($memNum < 1) { //아닐경우
    } else {
        while ($memRow = $memStmt->fetch(PDO::FETCH_ASSOC)) {
            $memNickNm = trim($memRow['mem_NickNm']);              // 닉네임
        }
    }


    //취소사유
    if ($taxiCPart == "1") {
        $taxiCanNm = "택시가 잡히지 않아서";
        $taxiChkNm = $taxiCanNm . " 취소 ";
    } else if ($taxiCPart == "2") {
        $taxiCanNm = "상대방의 사유로 인한";  //취소 최초 신청자
        $taxiChkNm = $memNickNm . "님 " . $taxiCanNm . " 취소 ";
    } else if ($taxiCPart == "3") {
        $taxiCanNm = "본인의 사유로 인한"; //취소 본인 (휴대폰 꺼져있을 경우, 기타)
        $taxiChkNm = $memNickNm . "님 " . $taxiCanNm . " 취소 ";
    }

    $result = array("result" => true, "chkIdx" => (int)$chkIdx, "memNickNm" => (string)$memNickNm, "taxiCPart" => (string)$taxiCPart, "taxiChkNm" => (string)$taxiChkNm, "taxiCSIdx" => (int)$taxiCSIdx, "taxiCMemId" => (string)$taxiCMemId, "taxiCRIdx" => (int)$taxiCRIdx, "taxiCRMemId" => (string)$taxiCRMemId);


    dbClose($DB_con);
    $conStmt = null;
    $viewStmt = null;
    $chkStmt = null;
    $viewStmt2 = null;

    echo str_replace('\\/', '/', json_encode($result, JSON_UNESCAPED_UNICODE));
} else {
    $result = array("result" => false, "errorMsg" => "조회 정보값이 없습니다. 관리자에게 문의바랍니다.");
    echo json_encode($result, JSON_UNESCAPED_UNICODE);
}
