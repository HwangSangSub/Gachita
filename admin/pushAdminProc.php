<?
/*======================================================================================================================

* 프로그램			: 관리자에게 푸시 발송	
* 페이지 설명		: 특정 부분에 사용자가 등록 및 서버가 등록 시 관리자에게 앱 푸시 안내
					- 맴버 등급 중 관리자(0, 1)를 조회하여 푸시 발송
* 파일명          : pushAdminProc.php
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
		$mem_NToken = memMatchTokenInfo($amem_Idx);
		$chkState = "999";  //관리자 안내
		$ntitle = "";
		$nmsg = $message;
		foreach ($mem_NToken as $k => $v) {
			$ntokens = $mem_NToken[$k];
			$ninputData = array("title" => $ntitle, "msg" => $nmsg, "state" => $chkState);
			$nresult = send_Push($ntokens, $ninputData);
		}
	} //관리자 조회 후 while 끝
	$chknum = 1;
	$errorMsg = "";
} // 관리자 일 경우 푸시 발송 끝
