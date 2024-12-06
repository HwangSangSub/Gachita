<?

/*======================================================================================================================

* 프로그램			: 채팅푸시
* 페이지 설명		: 채팅푸시
* 파일명                 : taxiSharingChatPush.php

========================================================================================================================*/

include "../lib/common.php";
include "../lib/functionDB.php";  //공통 db함수

$idx = trim($idx);		// 매칭고유번호
$msg = trim($msg);	// 메세지내용
//$push_msg = trim($msg);	// 메세지내용
$lng = trim($lng);		// 구글 경도
$lat = trim($lat);		// 구글 위도
$mode = trim($mode);	// 회원구분 (p: 생성자 / c: 요청자) ==> 소문자 필수
$push_msg = $msg;


if ($idx != "" && $mode != "") {  //매칭고유번호, 회원구분

	$DB_con = db1();

	if ($mode == "c") {		//요청자 인 경우
		$chkQuery = "SELECT taxi_SIdx, taxi_MemId, taxi_MemIdx from TB_RTAXISHARING WHERE idx = :idx AND taxi_RState ='5' LIMIT 1 ";
		$stmt = $DB_con->prepare($chkQuery);
		$stmt->bindparam(":idx", $idx);
		$stmt->execute();
		$num = $stmt->rowCount();
	} else if ($mode == "p") {		//생성자 인 경우
		$chkQuery = "SELECT idx, taxi_RMemId, taxi_RMemIdx from TB_RTAXISHARING WHERE taxi_SIdx = :taxi_SIdx AND taxi_RState ='5' LIMIT 1 ";
		$stmt = $DB_con->prepare($chkQuery);
		$stmt->bindparam(":taxi_SIdx", $idx);
		$stmt->execute();
		$num = $stmt->rowCount();
	} else {
		$result = array("result" => false, "errorMsg" => "회원구분이 없습니다.");
	}

	if ($num < 1) { //만남 중 노선이 없는 경우(채팅 불가능)
		$result = array("result" => false, "errorMsg" => "메시지를 보낼 수 있는 상태가 아닙니다. (만남 중인 노선이 없습니다.)");
	} else {  // 만남 중 노선이 있는 경우 (채팅 가능)
		if ($mode == "c") {		//요청자 인 경우
			while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
				$taxiMemId = trim($row['taxi_MemId']);			// 매칭신청 상태값
				$taxiMemIdx = trim($row['taxi_MemIdx']);               // 생성자 닉네임
			}
		} else if ($mode == "p") {
			while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
				$taxiMemId = trim($row['taxi_RMemId']);			// 생성자 고유번호
				$taxiMemIdx = trim($row['taxi_RMemIdx']);			// 매칭신청 상태값
			}
		}
		$chkstate = "997";
		if ($lat != "" && $lng != "") {
			$pushmsg = "상대방이 위치를 공유했습니다.";
			$addmsg = $push_msg;
		} else {
			$pushmsg = $push_msg;
		}
		/*푸시 관련 시작*/
		$mem_Token = memMatchTokenInfo($taxiMemIdx);

		$title = "";
		$msg = $pushmsg;
		foreach ($mem_Token as $k => $v) {
			$tokens = $mem_Token[$k];

			//알림할 내용들을 취합해서 $data에 모두 담는다. 프로젝트 의도에 따라 다른게 더 있을 수 있다.
			if ($lat != "" && $lng != "") {
				$inputData = array("title" => $title, "msg" => $msg, "addmsg" => $addmsg, "state" => $chkstate, "lat" => $lat, "lng" => $lng, "sharingIdx" => $idx);
			} else {
				$inputData = array("title" => $title, "msg" => $msg, "state" => $chkstate, "sharingIdx" => $idx);
			}

			//마지막에 알림을 보내는 함수를 실행하고 그 결과를 화면에 출력해 준다.
			$presult = send_Push($tokens, $inputData);
			// echo $presult;
		}
		/*푸시 끝*/
		$result = array("result" => true);
	}

	dbClose($DB_con);
	$stmt = null;
	$mSidStmt = null;
} else {
	$result = array("result" => false);
}

echo str_replace('\\/', '/', json_encode($result, JSON_UNESCAPED_UNICODE));
