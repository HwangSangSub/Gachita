<?
/*======================================================================================================================

* 프로그램				:  카드 정보 (등록, 수정, 삭제)
* 페이지 설명			:  카드 정보 (등록, 수정, 삭제)
* 파일명              :  cardProc.php

========================================================================================================================*/
include "../lib/common.php";
include "../lib/functionDB.php";  //공통 db함수
include "../order/lib/TPAY.LIB.php";  //공통 db함수
include "../order/lib/tpay_proc.php"; // 아임포트 함수
include "../lib/card_password.php"; //카드정보 암호화
$mem_Id = trim($memId);
$mem_Idx = memIdxInfo($mem_Id);   //회원 주아이디 (상점고유아이디로 사용)

$idx = trim($idx);                // 계좌삭제시 계좌 고유번호

//등록 일 경우 : reg, 삭제일 경우 : del 수정은 없어야 함. 사내DB에서는 수정가능하나 빌링키 발급을 위해서는 삭제 후 재 발급 방식으로 처리해야 함.
if ($mode == "") {
    $mode = "reg";      //등록
} else {
    $mode = trim($mode);
}
$bankOName = trim($bankOName);                 //예금주
$bankName = trim($bankName);                   //은행명
$bankNumber = trim($bankNumber);               //계좌번호
$bankNumber2 = base64_encode(openssl_encrypt($bankNumber, 'aes-256-cbc', $password, OPENSSL_RAW_DATA, $iv)); //계좌번호 암호화

$reg_Date = DU_TIME_YMDHIS;           //등록일

$DB_con = db1();
//신규버전의 경우에는 기존과 동일하게 처리.
if ($mem_Id != "" && $mode != "") {  //아이디랑 등록,수정 삭제 여부가 경우

    if ($mode == "reg") {
        //등록된 계좌 수 확인하기.
        $bankCntQuery = "SELECT COUNT(idx) AS cnt FROM TB_PAYMENT_BANK WHERE bank_Mem_Idx = :bank_Mem_Idx";
        $bankCntStmt = $DB_con->prepare($bankCntQuery);
        $bankCntStmt->bindparam(":bank_Mem_Idx", $mem_Idx);
        $bankCntStmt->execute();
        $bankCntNum = $bankCntStmt->rowCount();
        if($bankCntNum < 1){
            $bankCnt = 0;
        }else{
            $bankCntRow = $bankCntStmt->fetch(PDO::FETCH_ASSOC);
            $bankCnt = $bankCntRow['cnt'];                    // 등록된 계좌 수
        }
        if($bankCnt < 3){ // 최대 3개만 등록가능
            //등록된 계좌 확인하기
            $bankChkQuery = "SELECT idx FROM TB_PAYMENT_BANK WHERE bank_Number = :bank_Number AND bank_Name = :bank_Name AND bank_Mem_Idx = :bank_Mem_Idx";
            $bankChkStmt = $DB_con->prepare($bankChkQuery);
            $bankChkStmt->bindparam(":bank_Number", $bankNumber2);
            $bankChkStmt->bindparam(":bank_Name", $bankName);
            $bankChkStmt->bindparam(":bank_Mem_Idx", $mem_Idx);
            $bankChkStmt->execute();
            $bankChkNum = $bankChkStmt->rowCount();
            
            if($bankChkNum > 0){ //이미 등록된 계좌안내하기
                $result = array("result" => false, "errorMsg" => "이미 같은 계좌가 등록되어 있습니다. 확인 후 다시 시도해주세요.");
            }else{ //계좌 등록하기
                $bankInsQuery = "INSERT INTO TB_PAYMENT_BANK SET bank_Mem_Idx = :bank_Mem_Idx, bank_Mem_Id = :bank_Mem_Id, bank_OName = :bank_OName, bank_Name = :bank_Name, bank_Number = :bank_Number, reg_Date = :reg_Date";
                $bankInsStmt = $DB_con->prepare($bankInsQuery);
                $bankInsStmt->bindparam(":bank_Mem_Idx", $mem_Idx);
                $bankInsStmt->bindparam(":bank_Mem_Id", $mem_Id);
                $bankInsStmt->bindparam(":bank_OName", $bankOName);
                $bankInsStmt->bindparam(":bank_Name", $bankName);
                $bankInsStmt->bindparam(":bank_Number", $bankNumber2);
                $bankInsStmt->bindparam(":reg_Date", $reg_Date);
                try {
                    $bankInsStmt->execute();
                    $bankIdx = $DB_con->lastInsertId();
                    $result = array("result" => true, "bankIdx" => $bankIdx);
                } catch (PDOException $e) {
                    $result = array("result" => false, "errorMsg" => "계좌등록에 실패하였습니다. 잠시후 다시 시도해주세요.");
                }
            }

        }else{
            $result = array("result" => false, "errorMsg" => "더이상 계좌를 등록할 수 없습니다. 등록 가능한 계좌 수는 최대 3개입니다.");
        }
    } else if($mode == "del"){
            //등록된 계좌 확인하기
            $bankChkQuery = "SELECT idx FROM TB_PAYMENT_BANK WHERE idx = :idx AND bank_Mem_Idx = :bank_Mem_Idx";
            $bankChkStmt = $DB_con->prepare($bankChkQuery);
            $bankChkStmt->bindparam(":idx", $idx);
            $bankChkStmt->bindparam(":bank_Mem_Idx", $mem_Idx);
            $bankChkStmt->execute();
            $bankChkNum = $bankChkStmt->rowCount();
            
            if($bankChkNum < 1){ //이미 등록된 계좌안내하기
                $result = array("result" => false, "errorMsg" => "본인계좌가 아닙니다. 확인 후 다시 시도해주세요.");
            }else{ //계좌 등록하기
                $bankDelQuery = "DELETE FROM TB_PAYMENT_BANK WHERE idx = :idx AND bank_Mem_Idx = :bank_Mem_Idx";
                $bankDelStmt = $DB_con->prepare($bankDelQuery);
                $bankDelStmt->bindparam(":idx", $idx);
                $bankDelStmt->bindparam(":bank_Mem_Idx", $mem_Idx);
                try {
                    $bankDelStmt->execute();
                    $result = array("result" => true);
                } catch (PDOException $e) {
                    $result = array("result" => false, "errorMsg" => "계좌삭제에 실패하였습니다. 잠시후 다시 시도해주세요.");
                }
            }
    }else{
        $result = array("result" => false, "errorMsg" => "요청구분값이 없습니다. 확인 후 다시 시도해주세요.");
    }
} else {
    $result = array("result" => false, "errorMsg" => "조회요청값이 없습니다. 확인 후 다시 시도해주세요.");
}

echo str_replace('\\/', '/', json_encode($result, JSON_UNESCAPED_UNICODE));
