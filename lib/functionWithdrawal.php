<?
/*======================================================================================================================

* 프로그램			: 회원 탈퇴 관련 함수
* 페이지 설명		: 회원 탈퇴 관련 함수

========================================================================================================================*/


/*매칭요청 진행 건수 */
function sharingRCnt($mem_Idx)
{

    $fDB_con = db1();

    //매칭요청 진행 건수
    $chkCntQuery = "SELECT count(idx) AS num from TB_RTAXISHARING WHERE taxi_RMemIdx = :taxi_RMemIdx AND taxi_RState NOT IN ( '7', '8' ) "; //완료, 취소를 제외한 경우
    $chkCntRStmt = $fDB_con->prepare($chkCntQuery);
    $chkCntRStmt->bindparam(":taxi_RMemIdx", $mem_Idx);
    $chkCntRStmt->execute();
    $chkCntRrow = $chkCntRStmt->fetch(PDO::FETCH_ASSOC);
    $chkCntRNum = $chkCntRrow['num'];

    if ($chkCntRNum <> "") {
        $chkCntRNum = $chkCntRNum;
    } else {
        $chkCntRNum = 0;
    }

    return $chkCntRNum;

    dbClose($fDB_con);
    $chkCntRStmt = null;
}


function sharingRWaitCnt($mem_Idx)
{

    $fDB_con = db1();

    //매칭요청 진행 건수
    $chkCntQuery = "SELECT taxi_SIdx from TB_RTAXISHARING WHERE taxi_RMemIdx = :taxi_RMemIdx AND taxi_RState IN ( '1', '2' ) "; //완료, 취소를 제외한 경우
    $chkCntRStmt = $fDB_con->prepare($chkCntQuery);
    $chkCntRStmt->bindparam(":taxi_RMemIdx", $mem_Idx);
    $chkCntRStmt->execute();
    $chkCntRrow = $chkCntRStmt->fetch(PDO::FETCH_ASSOC);
    $taxi_SIdx = $chkCntRrow['taxi_SIdx'];

    if ($taxi_SIdx <> "") {
        $taxi_SIdx = $taxi_SIdx;
    } else {
        $taxi_SIdx = "";
    }

    return $taxi_SIdx;

    dbClose($fDB_con);
    $chkCntRStmt = null;
}



/*매칭생성 진행 건수 */
function sharingCnt($mem_Idx)
{

    $fDB_con = db1();

    $chkCntQuery = "SELECT count(taxi_MemId) AS num from TB_STAXISHARING WHERE taxi_MemIdx = :taxi_MemIdx AND taxi_State NOT IN ( '7', '8' ) ";
    $chkCntStmt = $fDB_con->prepare($chkCntQuery);
    $chkCntStmt->bindparam(":taxi_MemIdx", $mem_Idx);
    $chkCntStmt->execute();
    $chkCntRow = $chkCntStmt->fetch(PDO::FETCH_ASSOC);
    $chkCntNum = $chkCntRow['num'];

    return $chkCntNum;

    dbClose($fDB_con);
    $chkCntStmt = null;
}

/*회원 이미지 삭제 */
function memImgDel($mem_Idx)
{

    $fDB_con = db1();

    $chkMQuery = " SELECT mem_ImgFile FROM TB_MEMBERS WHERE idx = :mem_Idx ";
    //echo $chkMQuery."<BR>";
    //exit;
    $chkMStmt = $fDB_con->prepare($chkMQuery);
    $chkMStmt->bindparam(":mem_Idx", $mem_Idx);
    $chkMStmt->execute();
    $chkMNum = $chkMStmt->rowCount();
    //echo $chkNum."<BR>";
    //exit;

    if ($chkMNum < 1) { //이미지가 없을 경우
    } else {  // 이미지가 있을 경우

        $mbImgUrl = $_SERVER["DOCUMENT_ROOT"] . "/data/member"; // 이미지 경로(삭제시 필요)

        while ($chkMRow = $chkMStmt->fetch(PDO::FETCH_ASSOC)) {
            $memImgFile = trim($chkMRow['mem_ImgFile']);
        }

        //회원 이미지 삭제
        @unlink("$mbImgUrl/$memImgFile");
    }


    dbClose($fDB_con);
    $chkMStmt = null;
}


/*회원 탈퇴 및 기본정보 저장 */
function memUPDate($mem_Idx)
{
    $fDB_con = db1();
    $memId = memIdInfo($mem_Idx);

    //회원 기타 조회 후 임시 보관
    $memQuery  = "";
    $memQuery  = " SELECT A.idx, A.mem_Tel, A.mem_Lv, A.mem_Nm, A.mem_NickNm, A.mem_CertId, B.mem_Point, B.mem_MatCnt, B.mem_McCnt FROM TB_MEMBERS A ";
    $memQuery .= " LEFT OUTER JOIN TB_MEMBERS_ETC B ";
    $memQuery .= " ON B.mem_Idx = A.idx ";
    $memQuery .= " WHERE B.mem_Id = A.mem_Id AND B.mem_Idx = A.idx ";
    $memQuery .= "AND A.idx = :mem_Idx AND A.b_Disply = 'N' ";
    $memStmt = $fDB_con->prepare($memQuery);
    $memStmt->bindparam(":mem_Idx", $mem_Idx);
    $memStmt->execute();
    $memNum = $memStmt->rowCount();


    if ($memNum < 1) { //아닐경우
        return "0";
    } else {
        $mem_Id = ""; //회원아이디
        $mem_Nm = ""; //이름
        $mem_NickNm = ""; //닉네임
        $mem_Birth = ""; //생년월일
        $mem_Tel = ""; //전화번호
        $mem_ImgFile = ""; //회원이미지
        $mem_Lv = 0;  //회원레벨
        $mem_Point = 0;  //포인트
        $mem_MatCnt = 0; //매칭카운트 성공 횟수
        $mem_McCnt = 0;  //매칭거절 횟수
        $b_Disply = "Y";  //탈퇴
        $reg_Date = DU_TIME_YMDHIS;     //등록일
        $mem_SecedeEtc = DU_TIME_YMDHIS . " 본인이 탈퇴 처리함.";

        while ($memRow = $memStmt->fetch(PDO::FETCH_ASSOC)) {
            $memTel = trim($memRow['mem_Tel']);          // 연락처
            $memLv = trim($memRow['mem_Lv']);          // 등급
            $memNm = trim($memRow['mem_Nm']);          // 등급
            $memNickNm = trim($memRow['mem_NickNm']);          // 등급
            $memCertId = trim($memRow['mem_CertId']);          // 등급
            $memPoint = trim($memRow['mem_Point']);      // 포인트
            if ($memPoint == "") {
                $memPoint = 0;
            }
            $memMatCnt = trim($memRow['mem_MatCnt']); // 매칭성공횟수
            if ($memMatCnt == "") {
                $memMatCnt = 0;
            }
            $memMcCnt = trim($memRow['mem_McCnt']);      // 매칭거절횟수
            if ($memMcCnt == "") {
                $memMcCnt = 0;
            }

            //메이커 포인트내역 등록 여부 체크
            $cntMemQuery = "";
            $cntMemQuery = "SELECT count(idx) AS num FROM TB_MEMWITHDRAWL WHERE mem_Id = :mem_Id AND mem_Idx = :mem_Idx ";
            $cntMemStmt = $fDB_con->prepare($cntMemQuery);
            $cntMemStmt->bindParam("mem_Id", $mem_Id);
            $cntMemStmt->bindParam("mem_Idx", $mem_Idx);
            $cntMemStmt->execute();
            $cntMemRow = $cntMemStmt->fetch(PDO::FETCH_ASSOC);
            $totalMemCnt = $cntMemRow['num'];


            if ($totalMemCnt == "") {
                $totalMemCnt = "0";
            } else {
                $totalMemCnt =  $totalMemCnt;
            }
            //회원 탈퇴 기본 저장 중복 등록을 맞기 위해서 체크 함
            if ($totalMemCnt < 1) {
                //회원 탈퇴 기본 정보 저장
                $insMemQuery = "INSERT INTO TB_MEMWITHDRAWL (mem_Idx, mem_Id, mem_Tel, mem_Nm, mem_NickNm, mem_CertId, mem_Point, mem_Lv, mem_MatCnt, mem_McCnt, reg_date )
                         VALUES (:mem_Idx, :mem_Id, :mem_Tel, :mem_Nm, :mem_NickNm, :mem_CertId, :mem_Point, :mem_Lv, :mem_MatCnt, :mem_McCnt, :reg_Date)";
                $sMemtmt = $fDB_con->prepare($insMemQuery);
                $sMemtmt->bindParam(":mem_Idx", $mem_Idx);
                $sMemtmt->bindParam(":mem_Id", $memId);
                $sMemtmt->bindParam(":mem_Tel", $memTel);
                $sMemtmt->bindParam(":mem_Nm", $memNm);
                $sMemtmt->bindParam(":mem_NickNm", $memNickNm);
                $sMemtmt->bindParam(":mem_CertId", $memCertId);
                $sMemtmt->bindParam(":mem_Point", $memPoint);
                $sMemtmt->bindParam(":mem_Lv", $memLv);
                $sMemtmt->bindParam(":mem_MatCnt", $memMatCnt);
                $sMemtmt->bindParam(":mem_McCnt", $memMcCnt);
                $sMemtmt->bindParam(":reg_Date", $reg_Date);
                $sMemtmt->execute();
                //회원기본 테이블
                $upMemQuery = "UPDATE TB_MEMBERS SET mem_Id = :mem_Id, mem_Nm = :mem_Nm, mem_NickNm = :mem_NickNm, mem_Birth = :mem_Birth, mem_Tel = :mem_Tel, mem_ImgFile = :mem_ImgFile, mem_Lv = :mem_Lv, b_Disply = :b_Disply WHERE idx = :mem_Idx LIMIT 1";
                $upMemStmt = $fDB_con->prepare($upMemQuery);
                $upMemStmt->bindparam(":mem_Id", $mem_Id);
                $upMemStmt->bindparam(":mem_Nm", $mem_Nm);
                $upMemStmt->bindparam(":mem_NickNm", $mem_NickNm);
                $upMemStmt->bindparam(":mem_Birth", $mem_Birth);
                $upMemStmt->bindparam(":mem_Tel", $mem_Tel);
                $upMemStmt->bindparam(":mem_Tel", $mem_Tel);
                $upMemStmt->bindparam(":mem_ImgFile", $mem_ImgFile);
                $upMemStmt->bindparam(":mem_Lv", $mem_Lv);
                $upMemStmt->bindparam(":b_Disply", $b_Disply);
                $upMemStmt->bindparam(":mem_Idx", $mem_Idx);
                $upMemStmt->execute();

                //회원 정보테이블 업데이트
                $upMemQuery2 = "UPDATE TB_MEMBERS_INFO SET mem_Id = :mem_Id, mem_SecedeEtc = :mem_SecedeEtc, leaved_Date = :leaved_Date WHERE mem_Idx = :mem_Idx LIMIT 1";
                $upMemStmt2 = $fDB_con->prepare($upMemQuery2);
                $upMemStmt2->bindparam(":mem_Id", $mem_Id);
                $upMemStmt2->bindparam(":mem_SecedeEtc", $mem_SecedeEtc);
                $upMemStmt2->bindparam(":leaved_Date", $reg_Date);
                $upMemStmt2->bindparam(":mem_Idx", $mem_Idx);
                $upMemStmt2->execute();

                //회원 기타 업데이트
                $upMemQuery3 = "UPDATE TB_MEMBERS_ETC SET mem_Id = :mem_Id, mem_Point = :mem_Point, mem_Point = :mem_Point, mem_MatCnt = :mem_MatCnt, mem_McCnt = :mem_McCnt WHERE mem_Idx = :mem_Idx LIMIT 1";
                $upMemStmt3 = $fDB_con->prepare($upMemQuery3);
                $upMemStmt3->bindparam(":mem_Id", $mem_Id);
                $upMemStmt3->bindparam(":mem_Point", $mem_Point);
                $upMemStmt3->bindParam(":mem_MatCnt", $mem_MatCnt);
                $upMemStmt3->bindParam(":mem_McCnt", $mem_McCnt);
                $upMemStmt3->bindparam(":mem_Idx", $mem_Idx);
                $upMemStmt3->execute();

                dbClose($fDB_con);
                $memStmt = null;
                $sMemtmt = null;
                $upMemStmt = null;
                $upMemStmt2 = null;
                $upMemStmt3 = null;

                return "1";
            } else {
                return "0";
            }
        }
    }
}


//현재 상태값 가져오기(생성자)
function matStateInfo($mem_Idx)
{
    $fDB_con = db1();

    //현재 상태값 가져오기
    $chkMatQuery = "SELECT idx, taxi_State from TB_STAXISHARING WHERE taxi_MemIdx = :taxi_MemIdx AND taxi_State NOT IN ('7', '8')";
    $chkMatStmt = $fDB_con->prepare($chkMatQuery);
    $chkMatStmt->bindparam(":taxi_MemIdx", $mem_Idx);
    $chkMatStmt->execute();
    $chkMatNum = $chkMatStmt->rowCount();

    if ($chkMatNum < 1) { //아닐경우
    } else {
        while ($chkMatRow = $chkMatStmt->fetch(PDO::FETCH_ASSOC)) {
            $taxiMIdx = $chkMatRow['idx'];
            $taxiMState = $chkMatRow['taxi_State'];

            $matinfo['taxiMIdx'] = $taxiMIdx;     // 생성자 idx
            $matinfo['taxiMState'] = $taxiMState;    // 회원 등급 관련 점수
        }
    }

    return $matinfo;

    dbClose($fDB_con);
    $chkMatStmt = null;
}


//매칭 생성 상태 조건값 체크
function matChkState($mem_Idx)
{
    $fDB_con = db1();

    $chkMatStaQuery = "SELECT idx, taxi_State from TB_STAXISHARING WHERE taxi_MemIdx = :taxi_MemIdx "; //매칭대기, 매칭요청, 예약요청, 완료, 취소, 거절 사용 ;
    //echo $chkMQuery."<BR>";
    //exit;
    $chkMatStaStmt = $fDB_con->prepare($chkMatStaQuery);
    $chkMatStaStmt->bindparam(":taxi_MemIdx", $mem_Idx);
    $chkMatStaStmt->execute();
    $chkMatStaSNum = $chkMatStaStmt->rowCount();

    if ($chkMatStaSNum < 1) { //아닐경우
    } else {

        while ($chkMatStaSrow = $chkMatStaStmt->fetch(PDO::FETCH_ASSOC)) {
            $taxiMatState = $chkMatStaSrow['taxi_State'];
            $chkMatState[] = $taxiMatState;
        }
    }

    $chkMatTarr = array(1, 2, 3, 7, 8);   //매칭생성 취소 가능 상태
    $chkMatNarr = array(4, 5, 6, 9, 10);  //매칭생성 취소 불가능 상태

    //매칭요청 생성 가능 체크
    $intensMat = array_intersect($chkMatTarr, $chkMatState);
    //print_r($intens)."<BR>";
    //exit;

    if (isset($intensMat) == true) {
        $chkMatArrCnt = count($intensMat);
    } else {
        $chkMatArrCnt = 0;
    }

    if ($chkMatArrCnt != "") {
        $chkMatSState = "1";
    } else {
        $chkMatSState = "0";
    }

    //매칭요청 생성 불가능 체크
    $nsIntensMat = array_intersect($chkMatNarr, $chkMatState);
    //print_r($nsIntensMat)."<BR>";
    //exit;

    if (isset($nsIntensMat) == true) {
        $chkMatArrNCnt = count($nsIntensMat);
    } else {
        $chkMatArrNCnt = 0;
    }

    if ($chkMatArrNCnt != "") {
        $chkNMatState = "1";
    } else {
        $chkNMatState = "0";
    }

    if ($chkMatSState == 1 && $chkNMatState == 1) {  //취소사유확인 중이거나 거래완료 확인중인건 체크

        $chkMStr = implode(",", $nsIntensMat);

        $chkMStr2 = array("4", "5", "6", "9", "10");
        $chkMStr3 = array("예약요청완료", "만남중", "이동중", "취소사유확인", "거래완료확인");
        $chkMsg = str_replace($chkMStr2, $chkMStr3, $chkMStr);
    } else {
        $chkMsg = "";  //에러
    }

    return $chkMsg;


    dbClose($fDB_con);
    $chkMatStaStmt = null;
}


//매칭 요청 상태 조건값 체크
function matChkRState($mem_Idx)
{
    $fDB_con = db1();

    $chkMatRStaQuery = "SELECT taxi_RState from TB_RTAXISHARING WHERE taxi_RMemIdx = :taxi_RMemIdx "; //매칭대기, 매칭요청, 예약요청, 완료, 취소, 거절 사용 ;
    //echo $chkMatRStaQuery."<BR>";
    //exit;
    $chkMatRStaStmt = $fDB_con->prepare($chkMatRStaQuery);
    $chkMatRStaStmt->bindparam(":taxi_RMemIdx", $mem_Idx);
    $chkMatRStaStmt->execute();
    $chkMatRStaSNum = $chkMatRStaStmt->rowCount();

    if ($chkMatRStaSNum < 1) { //아닐경우
    } else {

        while ($chkMatRStaSrow = $chkMatRStaStmt->fetch(PDO::FETCH_ASSOC)) {
            $taxiMatRState = $chkMatRStaSrow['taxi_RState'];
            $chkMatRState[] = $taxiMatRState;
        }
    }

    $chkMatRTarr = array(1, 2, 3, 7, 8);   //매칭요청 취소 가능 상태
    $chkMatRNarr = array(4, 5, 6, 9, 10);  //매칭요청 취소 불가능 상태

    //매칭요청 생성 가능 체크
    $intensMatR = array_intersect($chkMatRTarr, $chkMatRState);
    //print_r($intens)."<BR>";
    //exit;

    if (isset($intensMatR) == true) {
        $chkMatRArrCnt = count($intensMatR);
    } else {
        $chkMatRArrCnt = 0;
    }

    if ($chkMatRArrCnt != "") {
        $chkMatSRState = "1";
    } else {
        $chkMatSRState = "0";
    }

    //매칭요청 생성 불가능 체크
    $nsIntensMatR = array_intersect($chkMatRNarr, $chkMatRState);
    //print_r($nsIntensMatR)."<BR>";
    //exit;

    if (isset($nsIntensMatR) == true) {
        $chkMatRArrNCnt = count($nsIntensMatR);
    } else {
        $chkMatRArrNCnt = 0;
    }

    if ($chkMatRArrNCnt != "") {
        $chkNMatRState = "1";
    } else {
        $chkNMatRState = "0";
    }

    if ($chkMatSRState == 1 && $chkNMatRState == 1) {  //취소사유확인 중이거나 거래완료 확인중인건 체크

        $chkRMStr = implode(",", $nsIntensMatR);
        $chkRMStr2 = array("4", "5", "6", "9", "10");
        $chkRMStr3 = array("예약요청완료", "만남중", "이동중", "취소사유확인", "거래완료확인");
        $chkRMsg = str_replace($chkRMStr2, $chkRMStr3, $chkRMStr);
    } else if ($chkMatSRState == 1) {  //요청 삭제 가능한건들 있을 경우
        $chkMRcancleNum = sharingRACancle($mem_Idx);
        $chkRMsg = $chkMRcancleNum;  //정상 (1)

    } else {
        $chkRMsg = "";  //에러
    }

    return $chkRMsg;


    dbClose($fDB_con);
    $chkMatRStaStmt = null;
}
