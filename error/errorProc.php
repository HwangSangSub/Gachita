<?
/*======================================================================================================================

* 프로그램			: 에러 상태 저장
* 페이지 설명		: 에러 상태 저장
* 파일명                 : errorProc.php

========================================================================================================================*/

include "../lib/common.php";

$mem_Id  = trim($memId);           //아이디(회원)
$taxi_SIdx = trim($sidx);          //생성자 idx
$taxi_RIdx = trim($ridx);          //요청자 idx
$taxi_Os   = trim($os);            //휴대폰 os
$taxi_Brand = trim($brand);        //휴대폰 브랜드
$taxi_Model = trim($model);        //휴대폰 모델
$taxi_Sdk = trim($sdk);            //휴대폰 Sdk
$taxi_Release = trim($release);    //앱 릴리즈
$taxi_Version = trim($version);    // 앱 버전
$bContent = trim($bContent);       // 에러 내용

$DB_con = db1();

//회원정보
$memQuery = "";
$memQuery = "SELECT idx, mem_NickNm from TB_MEMBERS WHERE mem_Id = :mem_Id AND b_Disply = 'N' LIMIT 1";
$memStmt = $DB_con->prepare($memQuery);
$memStmt->bindparam(":mem_Id", $mem_Id);
$memStmt->execute();
$vnum = $memStmt->rowCount();
//echo $vnum."<BR>";
//exit;

if ($vnum < 1) { //아닐경우
} else {
    while ($vrow = $memStmt->fetch(PDO::FETCH_ASSOC)) {
        $memIdx = trim($vrow['idx']);   //회원 고유 아이디
        $memNickNm = trim($vrow['mem_NickNm']);
    }
}

$regDate = DU_TIME_YMDHIS;  //시간등록

$b_Content = $bContent;

if ($ie) { //익슬플로러일경우
    $b_Content = iconv('euc-kr', 'utf-8', $bContent);
}
if ($taxi_SIdx == '') {
    $taxi_SIdx = 0;
}
if ($taxi_RIdx == '') {
    $taxi_RIdx = 0;
}

//문의상담이 있을경우
$taxi_Content = str_replace("^", "&", $b_Content);


$insQuery = "INSERT INTO TB_ERROR_LOG (taxi_SIdx, taxi_RIdx, taxi_MemIdx, taxi_MemId, taxi_Os, taxi_Brand, taxi_Model, taxi_Sdk, taxi_Release, taxi_Version, taxi_Content, reg_Date)
     VALUES (:taxi_SIdx, :taxi_RIdx, :taxi_MemIdx, :taxi_MemId, :taxi_Os, :taxi_Brand, :taxi_Model, :taxi_Sdk, :taxi_Release, :taxi_Version, :taxi_Content, :reg_Date)";
//echo $insQuery."<BR>";
//exit;
$stmt = $DB_con->prepare($insQuery);
$stmt->bindParam("taxi_SIdx", $taxi_SIdx);
$stmt->bindParam("taxi_RIdx", $taxi_RIdx);
$stmt->bindParam("taxi_MemIdx", $memIdx);
$stmt->bindParam("taxi_MemId", $mem_Id);
$stmt->bindParam("taxi_Os", $taxi_Os);
$stmt->bindParam("taxi_Brand", $taxi_Brand);
$stmt->bindParam("taxi_Model", $taxi_Model);
$stmt->bindParam("taxi_Sdk", $taxi_Sdk);
$stmt->bindParam("taxi_Release", $taxi_Release);
$stmt->bindParam("taxi_Version", $taxi_Version);
$stmt->bindParam("taxi_Content", $taxi_Content);
$stmt->bindParam("reg_Date", $regDate);
$stmt->execute();
$DB_con->lastInsertId();

$mIdx = $DB_con->lastInsertId();  //저장된 idx 값

$result = array("result" => true, "idx" => (int)$mIdx);

dbClose($DB_con);
$memStmt = null;
$bStmt = null;
$stmt = null;

echo json_encode($result, JSON_UNESCAPED_UNICODE);
