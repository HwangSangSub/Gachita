<?
/*======================================================================================================================

* 프로그램			: 관리자에게 푸시 발송	
* 페이지 설명		: 특정 부분에 사용자가 등록 및 서버가 등록 시 관리자에게 앱 푸시 안내
					- 맴버 등급 중 관리자(0, 1)를 조회하여 푸시 발송
					- 푸시 발송 방법이 다름
* 파일명          : pushAdminProc_cron.php
* 사용방법
		$msg_mode = "Pointexc";	// 모드명
		include "../admin/pushAdminProc.php";
		if ($chknum == 1) { // 관리자가 없는 경우 푸시발송이 안됨
			$result = array("result" => "success", "idx" => $mIdx ); // 각 API 성공값 부분
		} else  { // 관리자가 등록되어 있지는 않아 푸시발송은 안했지만 API는 정상처리
			$result = array("result" => "success", "idx" => $mIdx ,"errorMsg" => $errorMsg, "Msg" => "API정상처리, 푸시발송실패" );
		}
========================================================================================================================*/
if ($msg_mode == "Cancle") {
	$message = "[관리자] 취소 처리가 필요한 노선이 등록되었습니다.";			// 취소처리건
} else if ($msg_mode == "Complate") {
	$message = "[관리자] 완료 처리가 필요한 노선이 등록되었습니다.";			// 완료처리건
} else if ($msg_mode == "Newqna") {
	$message = "[관리자] 문의사항이 등록되었습니다.";						// 신규문의
} else if ($msg_mode == "Pointexc") {
	$message = "[관리자] 출금요청이 등록되었습니다.";						// 출금요청
}
$chkAdminQuery = "SELECT A.idx, A.mem_Id FROM TB_MEMBERS A WHERE A.mem_Lv IN (0, 1) AND A.b_Disply = 'N'; ";
$chkAdminStmt = $DB_con->prepare($chkAdminQuery);
$chkAdminStmt->execute();
$chkAdminNum = $chkAdminStmt->rowCount();
if ($chkAdminNum < 1) { //등록된 관리자가 없는 경우
	$errorMsg = "등록된 관리자가 없습니다.";
	$chknum = 0;
} else { // 관리자 일 경우 푸시 발송 시작
	while ($chkAdminRow = $chkAdminStmt->fetch(PDO::FETCH_ASSOC)) { //관리자 조회 후 while 시작
		$amem_Idx = $chkAdminRow['idx'];
		$amem_Id = $chkAdminRow['mem_Id'];

		//$mem_NToken = memMatchTokenInfo($taxiOSMemId);
		//토큰확인 푸시
		$memTokQuery = "SELECT mem_Token FROM TB_MEMBERS WHERE idx = :idx AND b_Disply = 'N'";
		$memTokStmt = $DB_con->prepare($memTokQuery);
		$memTokStmt->bindparam(":idx", $amem_Idx);
		$memTokStmt->execute();
		$memTokNum = $memTokStmt->rowCount();
		if ($memTokNum < 1) { //주 ID가 없을 경우 회원가입 시작
		} else {  //등록된 회원이 있을 경우
			while ($memTokRow = $memTokStmt->fetch(PDO::FETCH_ASSOC)) {
				$mem_NToken[] = $memTokRow["mem_Token"]; //토큰값
			}
		}
		
		$chkState = "999";  //거래완료
		$ntitle = "";
		$nmsg = $message;

		foreach ($mem_NToken as $k => $v) {
			$ntokens = $mem_NToken[$k];

			//알림할 내용들을 취합해서 $data에 모두 담는다. 프로젝트 의도에 따라 다른게 더 있을 수 있다.
			$ninputData = array("title" => $ntitle, "msg" => $nmsg, "state" => $chkState);

			//마지막에 알림을 보내는 함수를 실행하고 그 결과를 화면에 출력해 준다.
			$pushUrl = "https://fcm.googleapis.com/fcm/send";
			$headers = [];
			$headers[] = 'Content-Type: application/json';
			$headers[] = 'Authorization:key=' . GOOGLE_API_KEY;
			$notification = [
				'title' => $ninputData["title"],
				'body' => $ninputData["msg"],
				"state" => $ninputData["state"]
			];
			$extraNotificationData = ["message" => $notification];
			$data = array(
				"data" => $extraNotificationData,
				"notification" => $notification,
				"to"  => $ntokens, //token get on my ipad with the getToken method of cordova plugin,
			);
			$json_data =  json_encode($data, JSON_UNESCAPED_UNICODE);

			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $pushUrl);
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $json_data);

			$result = curl_exec($ch);

			if ($result === FALSE) {
				die('Curl failed: ' . curl_error($ch));
			}
			curl_close($ch);

			sleep(1);
		}
	} //관리자 조회 후 while 끝
	$chknum = 1;
	$errorMsg = "";
} // 관리자 일 경우 푸시 발송 끝
