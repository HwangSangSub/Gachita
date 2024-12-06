<?

/*======================================================================================================================

* 프로그램			: 매칭 생성, 매칭 요청 시간 지난간거 삭제 취소
* 페이지 설명		: 매칭 생성, 매칭 요청 시간 지난간거 삭제 취소
* 파일명                 : taxiSharingStaeDelproc.php

========================================================================================================================*/


    $alKDB_con = db1();
    //매칭 생성 조회
    
    // 메이커 상태값
    $alDkStaeSql = "  , ( SELECT taxi_Type FROM TB_STAXISHARING_INFO WHERE TB_STAXISHARING.taxi_SMemId = TB_STAXISHARING_INFO.taxi_SMemId AND TB_STAXISHARING.taxi_MemId = TB_STAXISHARING_INFO.taxi_MemId limit 1 ) AS taxiType  ";
    $alDkchkCntQuery = "";
    $alDkchkCntQuery = "SELECT idx, taxi_SMemId, taxi_MemId, taxi_State, taxi_SDate, taxi_Os, DATE_ADD(taxi_SDate, INTERVAL -30 MINUTE) AS chkDate, DATE_ADD(taxi_SDate, INTERVAL 30 MINUTE) AS chkDate2 ";
    $alDkchkCntQuery .= " {$alDkStaeSql} from TB_STAXISHARING WHERE taxi_State NOT IN ('7', '8', '9', '10') ORDER BY idx ASC";  //완료, 취소, 취소사유확인, 거래완료확인 제외한 나머지
    
    //echo $alDchkCntQuery."<BR>";
    //exit;
    $alDkchkStmt = $alKDB_con->prepare($alDkchkCntQuery);
    $alDkchkStmt->execute();
    $alDkchkNum = $alDkchkStmt->rowCount();
    
    if($alDkchkNum < 1)  { //아닐경우
    } else {
        
        while($alDkScRow=$alDkchkStmt->fetch(PDO::FETCH_ASSOC)) {
            $alDkidx = trim($alDkScRow['idx']);		        	//생성자 고유번호
            $alDktaxiSMemId = trim($alDkScRow['taxi_SMemId']);	//생성자 회원 고유 아이디
            $alDktaxiMemId = trim($alDkScRow['taxi_MemId']);	    //생성자 회원 아이디
            $alDktaxiType = trim($alDkScRow['taxiType']);		//출발타입 ( 0: 바로출발, 1: 예약출발 )
            $alDktaxiState = trim($alDkScRow['taxi_State']);	    //상태값
            $alDktaxiSDate = trim($alDkScRow['taxi_SDate']);	    //출발시간
            $alDktaxiOs = trim($alDkScRow['taxi_Os']);	    //출발시간
            $alDkchkDate = trim($alDkScRow['chkDate']);	    //출발시간
            $alDkchkDate2 = trim($alDkScRow['chkDate2']);	    //출발시간
            
            /*
            if ($alDktaxiOs == "1") { //아이폰
                $alDktaxiOsNm = "아이폰";
            } else {
                $alDktaxiOsNm = "안드로이드";
            }
            echo "================================="."<br>";
            echo "idx=".$alDkidx."<BR>";
            echo "taxiSMemId=".$alDktaxiSMemId."<BR>";
            echo "taxiMemId=".$alDktaxiMemId."<BR>";
            echo "taxiType=".$alDktaxiType."<BR>";
            echo "taxiState=".$alDktaxiState."<BR>";
            echo "taxiSDate=".$alDktaxiSDate."<BR>";
            
            
            echo "taxiOs=".$alDktaxiOs." (".$alDktaxiOsNm.")<BR>";
            echo "taxiSDate=".$alDkchkDate."<BR>";
            echo "taxiSDate=".$alDkchkDate2."<BR>";
            echo "================================="."<br>";
            */
            
            if ($alDktaxiType == "0") { //예약출발일경우
                //$alDkchkDate = "2018-09-20 18:30:00";
                //echo DU_TIME_YMDHIS."<BR>";
                //echo $alDkchkDate."<BR>";
             
                if ( DU_TIME_YMDHIS < $alDkchkDate ) { //출발 30분전 체크
                    $alDkchkNum = "1";
                 }  else {  //지났을 경우
                    $alDkchkNum = "0";
                 }
 
             } else { //즉시출발일 아닐 경우
                 if ( DU_TIME_YMDHIS < $alDkchkDate2 ) { //출발 30분후 체크
                    $alDkchkNum = "1";
                 }  else {  //지났을 경우
                    $alDkchkNum = "0";
                 }
             }
             
                
             if ($alDkchkNum == "0") { //지났을 경우 삭제
                 
                 if ($alDktaxiState >= "4") { //예약요청완료 부터 시작 (비정상적인 종료 차후 처리)
                     
                 } else { //매칭중, 매칭요청, 예약요청
					
                     //매칭요청 기본 취소처리
                     $alDkdelRQquery = "UPDATE TB_RTAXISHARING SET taxi_RState = '8' WHERE taxi_SIdx = :taxi_SIdx LIMIT 1";
                     $alDkdelRStmt = $alKDB_con->prepare($alDkdelRQquery);
                     $alDkdelRStmt->bindparam(":taxi_SIdx",$alDkidx);
                     $alDkdelRStmt->execute();
                     
                     //매칭요청 정보 취소일, 취소사요 기록
                     $alDkdelRQquery2 = "UPDATE TB_RTAXISHARING_INFO SET reg_CDate = now(), taxi_RMemo = '생성노선 유효시간 만료로 인한 취소' WHERE taxi_SIdx = :taxi_SIdx LIMIT 1";
                     $alDkdelRStmt2 = $alKDB_con->prepare($alDkdelRQquery2);
                     $alDkdelRStmt2->bindparam(":taxi_SIdx",$alDkidx);
                     $alDkdelRStmt2->execute();
                     
                     //쉐어링 매칭생성 취소처리
                     $alDkdelQquery = "UPDATE TB_STAXISHARING SET taxi_State = '8', reg_CDate = NOW() WHERE idx = :idx LIMIT 1";
                     $alDkdelStmt = $alKDB_con->prepare($alDkdelQquery);
                     $alDkdelStmt->bindParam(":idx", $alDkidx);
                     $alDkdelStmt->execute();
                     
                     
                 }
                 
                 
             }

                    
        }
        
        
        
        
        dbClose($alKDB_con);
        $alDkchkStmt = null;
        $alDkdelRStmt = null;
        $alDkdelRStmt2 = null;
        $alDkdelRStmt3 = null;
        $alDkdelStmt = null;
        $alDkdelStmt2 = null;
        $alDkdelStmt3 = null;
        
        
        
    
    }
    




?>