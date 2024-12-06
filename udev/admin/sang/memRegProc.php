<?
include "../../../udev/lib/common.php";
include "../../../lib/alertLib.php";
include "../../../lib/thumbnail.lib.php";   //썸네일

//$mem_Id = sprintf('%09d',rand(000000000,999999999));				//아이디
$loginId = trim($loginId);                //관리자아이디
$mem_Id = trim($mem_Id);                //아이디

if ($memPwd == "") {
    $mem_Pwd = $memPwd;
} else {
    $mem_Pwd = password_hash($memPwd, PASSWORD_DEFAULT);  // 비밀번호 암호화 
}

$mem_NickNm = trim($mem_NickNm);            //닉네임
$mem_Nm = trim($mem_Nm);        //이름
$mem_SnsChk = 'Kakao';                //SNS체크여부 (Kakao, google)
$mem_Tel = trim($mem_Tel);          //연락처
$mem_Email = '';                            //이메일
$mem_Sex = 0;                            //성별 ( 0: 남자, 1:여자)
$mem_Seat = 1;                            //좌석 ( 0: 앞자리, 1: 뒷자리)
$mem_Os = 1;                                //Os운영체제 (0: 안드로이드,1: 아이폰, 2:기타(웹만사용)  )
$mem_Lv = trim($mem_Lv);            // 등급

if ($mem_Lv == '') {
    $memLv = 14;
} else {
    $memLv = $mem_Lv;
}

if ($ie) { //익슬플로러일경우
    $mem_NickNm = iconv('euc-kr', 'utf-8', $mem_NickNm);
} else {
    $mem_NickNm = $mem_NickNm;
}


if ($mem_Id != "" && $mem_NickNm != "" && $mem_SnsChk != "") {

    $DB_con = db1();

    $memQuery = "SELECT b_Disply FROM TB_MEMBERS WHERE mem_Id = :mem_Id";
    $stmt = $DB_con->prepare($memQuery);
    $stmt->bindparam(":mem_Id", $mem_Id);
    $stmt->execute();
    $num = $stmt->rowCount();

    if ($num < 1) { //주 ID가 없을 경우 회원가입 시작       
        //회원코드
        $mem_Code = get_code();

        $cntQuery = "";
        $cntQuery = "SELECT count(idx)  AS num FROM TB_MEMBERS WHERE mem_Code = :mem_Code ";
        $cntStmt = $DB_con->prepare($cntQuery);
        $cntStmt->bindparam(":mem_Code", $mem_Code);
        $cntStmt->execute();
        $row = $cntStmt->fetch(PDO::FETCH_ASSOC);
        $vnum = $row['num'];

        if ($vnum > 1) { //있을 경우
        } else {


            $b_Disply = "N";                                                 //탈퇴여부
            $reg_Date = DU_TIME_YMDHIS;                                         //등록일

            //회원 기본테이블 저장
            $insQuery = "INSERT INTO TB_MEMBERS (mem_Id, mem_Nm, mem_NickNm, mem_Pwd, mem_Tel, mem_CertBit, mem_Birth, mem_Lv, b_Disply, mem_Os, mem_Code, reg_date ) VALUES (:mem_Id, :mem_Nm, :mem_NickNm, :mem_Pwd, :mem_Tel, '1', '', :mem_Lv, :b_Disply, :mem_Os, :mem_Code, :reg_Date)";
            $stmt = $DB_con->prepare($insQuery);
            $stmt->bindParam("mem_Id", $mem_Id);
            $stmt->bindParam("mem_Nm", $mem_Nm);
            $stmt->bindParam("mem_NickNm", $mem_NickNm);
            $stmt->bindParam("mem_Pwd", $mem_Pwd);
            $stmt->bindParam("mem_Tel", $mem_Tel);
            $stmt->bindParam("mem_Lv", $memLv);
            $stmt->bindParam("b_Disply", $b_Disply);
            $stmt->bindParam("mem_Os", $mem_Os);
            $stmt->bindParam("mem_Code", $mem_Code);
            $stmt->bindParam("reg_Date", $reg_Date);
            $stmt->execute();

            $mIdx = $DB_con->lastInsertId();  //저장된 idx 값

            if ($stmt->rowCount() > 0) { //삽입 성공

                //회원 정보테이블 저장
                $insInFoQuery = "INSERT INTO TB_MEMBERS_INFO (mem_Idx, mem_Id, mem_SnsChk, mem_Email, mem_Sex, mem_Seat ) VALUES (:mem_Idx, :mem_Id, :mem_SnsChk, :mem_Email, :mem_Sex, :mem_Seat )";
                $stmtInfo = $DB_con->prepare($insInFoQuery);
                $stmtInfo->bindParam("mem_Idx", $mIdx);
                $stmtInfo->bindParam("mem_Id", $mem_Id);
                $stmtInfo->bindParam("mem_SnsChk", $mem_SnsChk);
                $stmtInfo->bindParam("mem_Email", $mem_Email);
                $stmtInfo->bindparam(":mem_Sex", $mem_Sex);
                $stmtInfo->bindparam(":mem_Seat", $mem_Seat);
                $stmtInfo->execute();

                //회원 기타테이블 저장
                $insEtcQuery = "INSERT INTO TB_MEMBERS_ETC (mem_Idx, mem_Id) VALUES (:mem_Idx, :mem_Id)";
                $stmtEtc = $DB_con->prepare($insEtcQuery);
                $stmtEtc->bindParam("mem_Idx", $mIdx);
                $stmtEtc->bindParam("mem_Id", $mem_Id);
                $stmtEtc->execute();

                //회원 주소테이블 저장
                $insMapQuery = "INSERT INTO TB_MEMBERS_MAP (mem_Idx, mem_Id) VALUES (:mem_Idx, :mem_Id)";
                $stmtMap = $DB_con->prepare($insMapQuery);
                $stmtMap->bindParam("mem_Idx", $mIdx);
                $stmtMap->bindParam("mem_Id", $mem_Id);
                $stmtMap->execute();

                //사용기록 남기기!
                $deve_Locat = "회원등록";            //사용위치
                $deve_Memo = "관리자 (" . $loginId . ")가 신규회원 (" . $mem_Id . ")을 등록완료함";        //메모
                $develop_log_query = "
					INSERT INTO TB_DEVELOP_LOG(login_Id, deve_Locat, deve_Memo, reg_Date)
					VALUES (:login_Id, :deve_Locat, :deve_Memo, :reg_Date);";
                $develop_Stmt = $DB_con->prepare($develop_log_query);
                $develop_Stmt->bindparam(":login_Id", $loginId);
                $develop_Stmt->bindparam(":deve_Locat", $deve_Locat);
                $develop_Stmt->bindparam(":deve_Memo", $deve_Memo);
                $develop_Stmt->bindparam(":reg_Date", $reg_Date);
                $develop_Stmt->execute();
            } else { //등록시 에러
                //사용기록 남기기!
                $deve_Locat = "회원등록";            //사용위치
                $deve_Memo = "관리자 (" . $loginId . ")가 신규회원 (" . $mem_Id . ")을 등록시도 하였으나 실패";        //메모
                $develop_log_query = "
					INSERT INTO TB_DEVELOP_LOG(login_Id, deve_Locat, deve_Memo, reg_Date)
					VALUES (:login_Id, :deve_Locat, :deve_Memo, :reg_Date);";
                $develop_Stmt = $DB_con->prepare($develop_log_query);
                $develop_Stmt->bindparam(":login_Id", $loginId);
                $develop_Stmt->bindparam(":deve_Locat", $deve_Locat);
                $develop_Stmt->bindparam(":deve_Memo", $deve_Memo);
                $develop_Stmt->bindparam(":reg_Date", $reg_Date);
                $develop_Stmt->execute();
                echo '<script>alert("회원등록 실패");history.back();</script>';
            }
        }
    } else {  //등록된 회원이 있을 경우
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $bDisply = $row['b_Disply'];           //탈퇴여부
        }

        //사용기록 남기기!
        $deve_Locat = "회원등록";            //사용위치
        $deve_Memo = "관리자 (" . $loginId . ")가 신규회원 (" . $mem_Id . ")을 등록시도 하였으나 실패 (등록되어 있는 아이디)";        //메모
        $develop_log_query = "
			INSERT INTO TB_DEVELOP_LOG(login_Id, deve_Locat, deve_Memo, reg_Date)
			VALUES (:login_Id, :deve_Locat, :deve_Memo, :reg_Date);";
        $develop_Stmt = $DB_con->prepare($develop_log_query);
        $develop_Stmt->bindparam(":login_Id", $loginId);
        $develop_Stmt->bindparam(":deve_Locat", $deve_Locat);
        $develop_Stmt->bindparam(":deve_Memo", $deve_Memo);
        $develop_Stmt->bindparam(":reg_Date", $reg_Date);
        $develop_Stmt->execute();
    }


    dbClose($DB_con);
    $stmt = null;
    $cntMStmt = null;
    $stmtInfo = null;
    $stmtEtc = null;
    $stmtMap = null;
    $upStmt = null;
    $upStmt2 = null;
    $develop_Stmt = null;

    echo '<script>alert("회원등록 성공");history.back();</script>';
} else { //빈값일 경우
    echo '<script>alert("회원등록 실패");history.back();</script>';
}
