<?
include "../lib/common.php";
include "../lib/functionDB.php";  //공통 db함수

$mem_Id = trim($memId);            //아이디
$mem_Idx = memIdxInfo($mem_Id);   //회원 주아이디

if ($mem_Id != "") {  //아이디,  최근 개월 있을 경우

    $DB_con = db1();

    //수수료 조회
    $taxQuery = "";
    $taxQuery = "SELECT con_Tax FROM TB_CONFIG_EXC ";
    $taxStmt = $DB_con->prepare($taxQuery);
    $taxStmt->execute();
    $taxRow = $taxStmt->fetch(PDO::FETCH_ASSOC);
    $con_Tax = $taxRow['con_Tax'];

    /* 전체 카운트 */
    $cntQuery = "SELECT idx FROM TB_POINT_EXC WHERE mem_Idx = :mem_Idx ";

    $cntStmt = $DB_con->prepare($cntQuery);
    $cntStmt->bindparam(":mem_Idx", $mem_Idx);
    $cntStmt->execute();
    $totalCnt = $cntStmt->rowCount();

    if ($totalCnt == "") {
        $totalCnt = "0";
    } else {
        $totalCnt =  $totalCnt;
    }

    $totalCnt = (int)$totalCnt;

    /* 포인트 현황 */
    $query = "SELECT idx, exc_Idx, exc_Price, e_Disply, reg_Date FROM TB_POINT_EXC WHERE mem_Idx = :mem_Idx ORDER BY reg_Date DESC";
    //echo $hisQuery."<BR>";
    //exit;
    $stmt = $DB_con->prepare($query);
    $stmt->bindparam(':mem_Idx', $mem_Idx, PDO::PARAM_STR);
    $stmt->execute();
    $num = $stmt->rowCount();

    if ($num < 1) { //아닐경우
        $chkResult = "0";
        $listInfoResult = array("totCnt" => (int)$totalCnt);
    } else {
        $chkResult = "1";
        $listInfoResult = array("totCnt" => (int)$totalCnt);

        $data  = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {

            $idx = $row['idx'];                                // 출금요청 고유번호
            $exc_Idx = $row['exc_Idx'];                        // 출금계좌

            $exc_Price = $row['exc_Price'];                    // 출금요청금액
            $e_Disply = $row['e_Disply'];                    // 환전여부 (Y: 입금완료, N: 입금대기중, C: 환전요청취소)
            if ($e_Disply == "Y") {
                $eDisply = "완료";
            } else if ($e_Disply == "N") {
                $eDisply = "처리중";
            } else if ($e_Disply == "C") {
                $eDisply = "거절";
            }
            $reg_Date = $row['reg_Date'];                    // 등록일
            $regDate = date("y.m.d", strtotime($reg_Date));
            $reg_ExcDate = $row['reg_ExcDate'];                // 처리일 ( 환전여부값이 Y-> 입금일, C-> 요청취소일)
            $regExcDate = date("y.m.d", strtotime($reg_ExcDate));

            $conTax = $con_Tax / 100;
            $tax = $exc_Price * $conTax;
            $excPrice = (int)$exc_Price - (int)$tax;

            // : 내역상세
            $mresult = ["idx" => (int)$idx, "regDate" => (string)$reg_Date, "excPrice" => (int)$exc_Price, "conTax" => (double)$con_Tax, "price" => (int)$excPrice, "eDisply" => (string)$eDisply];
            array_push($data, $mresult);
        }

        $chkData = [];
        $chkData["result"] = true;
        $chkData["listInfo"] = $listInfoResult;  //카운트 관련
        $chkData['lists'] = $data;
    }

    if ($chkResult  == "1") {
        $output = str_replace('\\\/', '/', json_encode($chkData, JSON_UNESCAPED_UNICODE));
    } else if ($chkResult  == "0") {
        $chkData2["result"] = true;
        $chkData2["listInfo"] = $listInfoResult;  //카운트 관련
        $chkData2['lists'] = [];
        $output = str_replace('\\\/', '/', json_encode($chkData2, JSON_UNESCAPED_UNICODE));
    }

    echo  urldecode($output);

    dbClose($DB_con);
    $cntStmt = null;
    $hisStmt = null;
    $chkStmt = null;
} else {
    $result = array("result" => false, "errorMsg" => "조회가능한 정보가 없습니다. 관리자에게 문의바랍니다.");
    echo json_encode($result, JSON_UNESCAPED_UNICODE);
}
