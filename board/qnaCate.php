<?
/*======================================================================================================================

* 프로그램			: 자주 묻는 질문 카테고리
* 페이지 설명		: 자주 묻는 질문 카테고리
* 파일명          : qnaCate.php

========================================================================================================================*/

//이벤트 정보
include "../lib/common.php";

$DB_con = db1();

$query = "SELECT b_CateChk, b_CateName FROM TB_BOARD_SET WHERE b_Idx = 2 ORDER BY idx DESC";
$stmt = $DB_con->prepare($query);
$stmt->execute();
$num = $stmt->rowCount();

if ($num < 1) { //아닐경우
    $result = array("result" => false, "errorMsg" => "등록된 게시판설정값이 없습니다. 확인 후 다시 시도해주세요.");
} else {
    $data = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {

        $b_CateChk = $row['b_CateChk'];
        if($b_CateChk == 'N'){
            $result = array("result" => false, "errorMsg" => "등록된 카테고리가 없습니다. 확인 후 다시 시도해주세요.");
        }else{
            $b_CateName = $row['b_CateName'];
            $chk = explode("&", $b_CateName);
            for($i = 0; $i < count($chk); $i++){
                $cateNo = $i + 1;
                $cate = array("cateNo" => (int)$cateNo, "cateName" => (string)$chk[$i]);
                array_push($data, $cate);
            }
            $result = array("result" => true, "lists" => $data);
        }
    }
}

dbClose($DB_con);
$stmt = null;

echo str_replace('\/', '/', json_encode($result, JSON_UNESCAPED_UNICODE + JSON_PRETTY_PRINT));