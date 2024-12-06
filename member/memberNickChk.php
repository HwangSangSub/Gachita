<?
include "../lib/common.php";
include "../lib/functionDB.php";  //공통 db함수

$mem_NickNm = trim($memNickNm);                //아이디



if ($mem_NickNm != "") {  //아이디가 있을 경우

    $DB_con = db1();

    $memQuery = "SELECT idx FROM TB_MEMBERS WHERE mem_NickNm = :mem_NickNm AND b_Disply = 'N' ";
    $stmt = $DB_con->prepare($memQuery);
    $stmt->bindparam(":mem_NickNm", $mem_NickNm);
    $stmt->execute();
    $num = $stmt->rowCount();

    if ($num > 0) { //아닐경우
        $result = array("result" => false, "errorMsg" => "이미 사용중인 닉네임입니다.");
    } else {
        $result = array("result" => true);
    }

    dbClose($DB_con);
    $stmt = null;
    $mInfoStmt = null;
    $upStmt = null;
    $upStmt2 = null;
} else {
    // $result = array("result" => false, "errorMsg" => $memNickNm);
    $result = array("result" => false, "errorMsg" => "회원닉네임이 없습니다. 확인 후 다시 시도해주세요.");
}
echo json_encode($result);
