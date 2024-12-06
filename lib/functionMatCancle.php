 <?

    /*======================================================================================================================

* 프로그램			: 생성자 노선 취소 관련 함수
* 페이지 설명		: 생성자 노선 취소 관련 함수

========================================================================================================================*/

    /*생성 노선 매칭중, 매칭요청, 예약요청 진행 건수 삭제 */
    function sharingACancle($mem_Idx, $idx)
    {
        $fMatDB_con = db1();


        $chkCntQuery = "SELECT count(taxi_MemId) AS num from TB_STAXISHARING WHERE taxi_MemIdx = :taxi_MemIdx AND taxi_MemId = :taxi_MemId AND idx = :idx AND taxi_State IN ('1', '2', '3')  LIMIT 1   "; //매칭요청, 예약요청
        //echo $chkCntQuery."<BR>";
        //exit;
        $stmt = $fMatDB_con->prepare($chkCntQuery);
        $stmt->bindparam(":taxi_MemIdx", $mem_Idx);
        $stmt->bindparam(":idx", $idx);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $num = $row['num'];

        if ($num < 1) { //매칭값이 맞지 않을 경우

        } else {  // 매칭생성,매칭중 일 경우 수정 가능

            //해당 생성노선에 따른 요청자 정보 값 조회

            // 메이커 상태값
            $mStaeSql = "  , ( SELECT taxi_State FROM TB_STAXISHARING WHERE TB_STAXISHARING.taxi_MemIdx = TB_RTAXISHARING.taxi_MemIdx AND TB_STAXISHARING.taxi_MemId = TB_RTAXISHARING.taxi_MemId limit 1 ) AS taxiState  ";
            $matchQuery = "SELECT idx, taxi_RMemIdx, taxi_RMemId, taxi_RState {$mStaeSql} from TB_RTAXISHARING WHERE taxi_SIdx = :taxi_SIdx AND taxi_MemIdx = :taxi_MemIdx AND taxi_MemId = :taxi_MemId ";
            $matStmt = $fMatDB_con->prepare($matchQuery);
            $matStmt->bindparam(":taxi_SIdx", $idx);
            $matStmt->bindparam(":taxi_MemIdx", $mem_Idx);
            $matStmt->execute();
            $matNum = $matStmt->rowCount();

            if ($matNum < 1) { //요청한 신청한 건수가 없음
            } else {  // 요청한 신청건수 가 있을 경우 삭제

                while ($matRow = $matStmt->fetch(PDO::FETCH_ASSOC)) {
                    $taxiMRIdx = trim($matRow['idx']);               // 투게더 고유번호
                    $taxiRMemIdx = trim($matRow['taxi_RMemIdx']);   // 투게더 고유 아이디
                    $taxiRMemId = trim($matRow['taxi_RMemId']);    // 투게더 아이디
                    $taxiState = trim($matRow['taxiState']);       // 메이커 상태값
                    $taxiRState = trim($matRow['taxi_RState']);    // 투게더 상태

                    if ($taxiState == "1") {
                        $statNm = "노선을";
                    } else if ($taxiState == "2") {
                        $statNm = "노선을";
                    } else if ($taxiState == "3") {
                        $statNm = "예약노선을";
                    }

                    if ($taxiRState != "3") { //거절이 아닌경우 만 제외

                        $mem_Token = memMatchTokenInfo($taxiRMemIdx);
                        $title = "";
                        $msg = $statNm . " 메이커가 취소하였습니다.";

                        foreach ($mem_Token as $k => $v) {
                            $tokens = $mem_Token[$k];
                            $inputData = array("title" => $title, "msg" => $msg, "state" => "0");
                            $presult = send_Push($tokens, $inputData);
                        }
                    }
                }
            }

            //매칭요청 기본 삭제
            $delRQquery = "UPDATE TB_RTAXISHARING SET taxi_RState = '8' WHERE taxi_SIdx = :taxi_SIdx LIMIT 1";
            $delRStmt = $fMatDB_con->prepare($delRQquery);
            $delRStmt->bindparam(":taxi_SIdx", $idx);
            $delRStmt->execute();

            //매칭요청 정보 삭제
            $delRQquery2 = "UPDATE TB_RTAXISHARING_INFO SET reg_CDate = now(), taxi_RMemo = '메이커로 인한 취소' WHERE taxi_SIdx = :taxi_SIdx LIMIT 1";
            $delRStmt2 = $fMatDB_con->prepare($delRQquery2);
            $delRStmt2->bindparam(":taxi_SIdx", $idx);
            $delRStmt2->execute();

            //쉐어링 매칭생성 기본테이블
            $delQquery = "UPDATE TB_STAXISHARING SET taxi_State = '8', reg_CDate = NOW() WHERE idx = :idx LIMIT 1";
            $delStmt = $fMatDB_con->prepare($delQquery);
            $delStmt->bindParam(":idx", $idx);
            $delStmt->execute();

            return "1";
        }

        dbClose($fMatDB_con);
        $stmt = null;
        $mSidStmt = null;
        $matStmt = null;
        $delRStmt = null;
        $delRStmt2 = null;
        $delRStmt3 = null;
        $delStmt = null;
        $delStmt2 = null;
        $delStmt3 = null;
    }


    ?>