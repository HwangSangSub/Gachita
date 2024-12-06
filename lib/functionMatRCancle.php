 <?

    /*======================================================================================================================

* 프로그램			: 요청자 노선 취소 관련 함수
* 페이지 설명		: 요청자 노선 취소 관련 함수

========================================================================================================================*/

    /*생성 노선 매칭중, 매칭요청, 예약요청 진행 건수 삭제 */
    function sharingRACancle($mem_Idx)
    {

        $fMatDB_con = db1();

        //진행 요청중인 상태값 가져오기
        $chkMatRCanQuery = "SELECT idx, taxi_SIdx, taxi_MemId, taxi_MemIdx, taxi_RMemIdx, taxi_RState from TB_RTAXISHARING WHERE taxi_RMemIdx = :taxi_RMemIdx AND taxi_RState IN ('1', '2')"; //매칭대기, 매칭요청, 예약요청, 완료, 취소, 거절 사용 ;
        $chkMatRCanStmt = $fMatDB_con->prepare($chkMatRCanQuery);
        $chkMatRCanStmt->bindparam(":taxi_RMemIdx", $mem_Idx);
        $chkMatRCanStmt->execute();
        $chkMatRCanNum = $chkMatRCanStmt->rowCount();

        if ($chkMatRCanNum < 1) { //아닐경우
        } else {

            while ($chkMatRCanSrow = $chkMatRCanStmt->fetch(PDO::FETCH_ASSOC)) {
                $chkRIdx = $chkMatRCanSrow['idx'];         //요청 고유번호
                $chkSIdx = $chkMatRCanSrow['taxi_SIdx'];     //생성자 고유번호
                $taxiMemCId = $chkMatRCanSrow['taxi_MemId'];     //생성자 아이디
                $taxiMemIdx = $chkMatRCanSrow['taxi_MemIdx'];   //생성자 고유아이디
                $taxiRMemId = $chkMatRCanSrow['taxi_RMemId'];   //요청자 고유아이디
                $taxiMatRCState = $chkMatRCanSrow['taxi_RState'];  //요청자 상태값

                if ($taxiMatRCState == "1") {
                    $statNm = "생성노선에";
                } else if ($taxiMatRCState == "2") {
                    $statNm = "예약노선에";
                }

                $mem_Token = memMatchTokenInfo($taxiMemIdx);
                $title = "";
                $msg = $statNm . " 매칭요청이 취소되었습니다.";

                foreach ($mem_Token as $k => $v) {
                    $tokens = $mem_Token[$k];
                    $inputData = array("title" => $title, "msg" => $msg, "state" => "0");
                    $presult = send_Push($tokens, $inputData);
                }


                //매칭요청 기본 삭제
                $delQquery = "DELETE FROM TB_RTAXISHARING WHERE taxi_RMemIdx = :taxi_RMemIdx AND taxi_RMemId = :taxi_RMemId AND taxi_SIdx = :taxi_SIdx AND idx = :idx AND taxi_RState IN ('1', '2') LIMIT 1";
                $delStmt = $fMatDB_con->prepare($delQquery);
                $delStmt->bindparam(":taxi_RMemIdx", $taxiRMemIdx);
                $delStmt->bindparam(":taxi_RMemId", $mem_Id);
                $delStmt->bindparam(":taxi_SIdx", $chkSIdx);
                $delStmt->bindparam(":idx", $chkRIdx);
                $delStmt->execute();

                //매칭요청 정보 삭제
                $delQquery2 = "DELETE FROM TB_RTAXISHARING_INFO WHERE taxi_RMemIdx = :taxi_RMemIdx AND taxi_RMemId = :taxi_RMemId  AND taxi_SIdx = :taxi_SIdx AND taxi_RIdx = :taxi_RIdx LIMIT 1";
                $delStmt2 = $fMatDB_con->prepare($delQquery2);
                $delStmt2->bindparam(":taxi_RMemIdx", $taxiRMemIdx);
                $delStmt2->bindparam(":taxi_RMemId", $mem_Id);
                $delStmt2->bindparam(":taxi_SIdx", $chkSIdx);
                $delStmt2->bindparam(":taxi_RIdx", $chkRIdx);
                $delStmt2->execute();

                //매칭요청 지도 삭제
                $delQquery3 = "DELETE FROM TB_RTAXISHARING_MAP WHERE taxi_RMemIdx = :taxi_RMemIdx AND taxi_RMemId = :taxi_RMemId  AND taxi_SIdx = :taxi_SIdx AND taxi_RIdx = :taxi_RIdx LIMIT 1";
                $delStmt3 = $fMatDB_con->prepare($delQquery3);
                $delStmt3->bindparam(":taxi_RMemIdx", $taxiRMemIdx);
                $delStmt3->bindparam(":taxi_RMemId", $mem_Id);
                $delStmt3->bindparam(":taxi_SIdx", $chkSIdx);
                $delStmt3->bindparam(":taxi_RIdx", $chkRIdx);
                $delStmt3->execute();

                //투게더 건수 조회
                $cntQuery = "SELECT count(taxi_MemId) AS num from TB_RTAXISHARING WHERE idx = :idx AND taxi_RState = '1'";
                $cntStmt = $fMatDB_con->prepare($cntQuery);
                $cntStmt->bindparam(":idx", $chkRIdx);
                $cntStmt->execute();
                $cntRow = $cntStmt->fetch(PDO::FETCH_ASSOC);
                $cntNum = $cntRow['num'];

                if ($cntNum > 1) { //아닐 경우
                } else {  // 매칭신청건이 없을 경우

                    $upPQquery = "UPDATE TB_STAXISHARING SET taxi_State = '1' WHERE idx = :idx  LIMIT 1";
                    $upPStmt = $fMatDB_con->prepare($upPQquery);
                    $upPStmt->bindparam(":idx", $chkSIdx);
                    $upPStmt->execute();
                }
            }


            return "1";
        }



        dbClose($fMatDB_con);
        $chkMatRCanStmt = null;
        $mSidStmt = null;
        $delStmt = null;
        $delStmt2 = null;
        $cntStmt = null;
        $upPStmt = null;
    }



    ?>