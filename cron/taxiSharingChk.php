#!/usr/bin/php -q
<?php
/*======================================================================================================================

* 프로그램			: 유효시간이 지난 매칭중 노선 취소(삭제)처리  (매시간마다 적용)
* 페이지 설명		: 유효시간이 지난 매칭중 노선 취소(삭제)처리
* 파일명          : taxiSharingChk.php

========================================================================================================================*/

// register_globals off 처리
if (isset($_GET)) {
    @extract($_GET);
}
if (isset($_POST)) {
    @extract($_POST);
}
if (isset($_SERVER)) {
    @extract($_SERVER);
}
if (isset($_ENV)) {
    @extract($_ENV);
}
if (isset($_SESSION)) {
    @extract($_SESSION);
}
if (isset($_COOKIE)) {
    @extract($_COOKIE);
}
if (isset($_REQUEST)) {
    @extract($_REQUEST);
}
if (isset($_FILES)) {
    @extract($_FILES);
}

ob_start();

header('Content-Type: text/html; charset=utf-8');
$gmnow = gmdate('D, d M Y H:i:s') . ' GMT';
header('Expires: 0'); // rfc2616 - Section 14.21
header('Last-Modified: ' . $gmnow);
header('Cache-Control: no-store, no-cache, must-revalidate'); // HTTP/1.1
header('Cache-Control: pre-check=0, post-check=0, max-age=0'); // HTTP/1.1
header('Pragma: no-cache'); // HTTP/1.0

//구글 fcm키
// define("GOOGLE_API_KEY", "AAAAQbbxhtg:APA91bG_3pJJuVpf3gAvnxvbvY7Bw0fh2riGqUuXfzC7qbQ5Q2U8LM5WPbaWNYmn-jIJty2WtUJX6-rpWBi1nJIV3UPuXGmJiCXQw5zOZqQc5gqMRKJzxBh2bCZV3yYx39Z29U3iH13c");
define("GOOGLE_API_KEY", "AAAAQ5PRua4:APA91bHIqpvIHy5sm_Av5GYw1o3qO3gZxorKjfHnbXN_G17YiEf_qnaH-5n34dsbUJ1YmqBNjAaGAAY6hrJ4VmL2ntidTTMF_FXOYh_xcH4X-od_bdHVmj5iyqmAeYnLXqprP_FWA1mD");
include 'inc/dbcon.php';


$DB_con = db1();

$now_Time = date('Y-m-d H:i:s', time());	 //등록일

//성공여부 (0: 실패, 1: 성공)
$res_bit1 = 0; //대기모드 제외 노선
$res_bit2 = 0; //대기모드

//완료변수설정 값 조회	

$conQuery = "SELECT con_FTime FROM TB_CONFIG";
$conStmt = $DB_con->prepare($conQuery);
$conStmt->execute();
while ($conrow = $conStmt->fetch(PDO::FETCH_ASSOC)) {
	(int)$con_FTime = (int)$conrow['con_FTime'] * 60;				//매칭중 노선취소시간(유효시간 지난경우) ==> 취소시간 * 60 분으로 전환
	$conFTime = (int)$con_FTime + 30;								//30 더한 이유는 기존 노선유효시간이 30분 임으로 그 이후부터 시간 측정하기 위함
}
//이동중 상태인 노선 조회
$Query2 = "SELECT idx, taxi_MemId, taxi_MemIdx, taxi_State, taxi_SDate, reg_Date FROM TB_STAXISHARING WHERE taxi_State IN ('1', '2', '3') ORDER BY idx ASC;";
//echo $Query1."<BR>";
//exit;
$Stmt = $DB_con->prepare($Query2);
$Stmt->execute();
$num = $Stmt->rowCount();
//echo $num."<BR>";

$cnt = 0;

if ($num < 1) { //아닐경우
	$result = array("result" => false, "errorMsg" => "매칭중, 매칭요청, 예약요청 노선이 없습니다.");
} else {
	while ($row = $Stmt->fetch(PDO::FETCH_ASSOC)) {
		$taxi_SIdx =  $row['idx'];						// 쉐어링 생성자 idx
		$taxi_MemId =  $row['taxi_MemId'];				// 쉐어링 생성자 아이디
		$taxi_MemIdx =  $row['taxi_MemIdx'];			// 쉐어링 생성자 고유아이디
		$taxi_State =  $row['taxi_State'];				// 쉐어링 매칭값
		$taxi_SDate =  $row['taxi_SDate'];
		$reg_Date =  $row['reg_Date'];

		$chkLocQuery1 = "SELECT taxi_SLng, taxi_SLat FROM TB_STAXISHARING_MAP WHERE taxi_Idx = :taxi_SIdx LIMIT 1;";
		$chkLocStmt1 = $DB_con->prepare($chkLocQuery1);
		$chkLocStmt1->bindparam(":taxi_SIdx", $taxi_SIdx);
		$chkLocStmt1->execute();
		while ($chkLocrow1 = $chkLocStmt1->fetch(PDO::FETCH_ASSOC)) {
			$res_lat1 =  $chkLocrow1['taxi_SLat'];				// 쉐어링 위치(Lat)
			$res_lon1 =  $chkLocrow1['taxi_SLng'];				// 쉐어링 위치(Lng)
			$param1 = array("lat" => $res_lat1, "lon" => $res_lon1);
		}

		//요청자 노선 조회
		$chkRQuery = "";
		$chkRQuery .= "SELECT idx, taxi_RMemId, taxi_RMemIdx FROM TB_RTAXISHARING WHERE taxi_SIdx = :taxi_SIdx ;";
		//echo $Query1."<BR>";
		//exit;
		$chkRStmt = $DB_con->prepare($chkRQuery);
		$chkRStmt->bindparam(":taxi_SIdx", $taxi_SIdx);
		$chkRStmt->execute();
		$chkRnum = $chkRStmt->rowCount();
		if ($chkRnum < 1) {	// 요청자가 없는 경우
			(int)$time = strtotime($now_Time) - strtotime($taxi_SDate);
			(int)$time_min = ceil($time / (60));
			if ((int)$time_min > (int)$conFTime) {
				$taxiMChk = "1";
			} else {
				$taxiMChk = "0";
			}
			if ($taxiMChk == "1") {
				//쉐어링 매칭생성 기본테이블
				//메이커 취소처리
				$upMQquery11 = "UPDATE TB_STAXISHARING SET taxi_State = '8', taxi_MState = :taxi_MState, reg_CDate = NOW() WHERE idx = :idx LIMIT 1";
				$upMStmt11 = $DB_con->prepare($upMQquery11);
				$upMStmt11->bindparam(":idx", $taxi_SIdx);
				$upMStmt11->bindparam(":taxi_MState", $taxi_State);
				$upMStmt11->execute();
				$cnt++;
			}
		} else { //요청자가 있는 경우
			(int)$time = strtotime($now_Time) - strtotime($taxi_SDate);
			(int)$time_min = ceil($time / (60));

			if ((int)$time_min > (int)$conFTime) {
				$taxiMChk = "1";
			} else {
				$taxiMChk = "0";
			}
			if ($taxiMChk == "1") {
				//요청자 노선 조회
				$selRQuery = "";
				$selRQuery .= "	SELECT idx, taxi_RMemId, taxi_RMemIdx FROM TB_RTAXISHARING WHERE taxi_SIdx = :taxi_SIdx LIMIT 1;";
				//echo $Query1."<BR>";
				//exit;
				$selRStmt = $DB_con->prepare($selRQuery);
				$selRStmt->bindparam(":taxi_SIdx", $taxi_SIdx);
				$selRStmt->execute();
				while ($selRrow = $selRStmt->fetch(PDO::FETCH_ASSOC)) {
					$taxi_RMemId = $selRrow['taxi_RMemId'];
					$taxi_RMemIdx = $selRrow['taxi_RMemIdx'];
				}
				//요청자 푸시
				$memRTokQuery = "SELECT mem_Token FROM TB_MEMBERS WHERE idx = :mem_Idx AND b_Disply = 'N';";
				$memRTokStmt = $DB_con->prepare($memRTokQuery);
				$memRTokStmt->bindparam(":mem_Idx", $taxi_RMemIdx);
				$memRTokStmt->execute();
				$memRTokNum = $memRTokStmt->rowCount();
				if ($memRTokNum < 1) { //주 ID가 없을 경우 회원가입 시작
				} else {  //등록된 회원이 있을 경우
					while ($memRTokRow = $memRTokStmt->fetch(PDO::FETCH_ASSOC)) {
						$mem_RToken[] = $memRTokRow["mem_Token"]; //토큰값
					}
				}
				
				$rchkState = "8";  //거래완료
				$rtitle = "🚐버스보다 빠르고 🚕택시보다 저렴하게";
				$rmsg = "요청노선의 유효시간이 초과되어 요청이 취소되었습니다.";

				foreach ($mem_RToken as $k2 => $v2) {
					$rtokens = $mem_RToken[$k2];

					//알림할 내용들을 취합해서 $data에 모두 담는다. 프로젝트 의도에 따라 다른게 더 있을 수 있다.
					$rinputData = array("title" => $rtitle, "msg" => $rmsg, "state" => $rchkState);

					//마지막에 알림을 보내는 함수를 실행하고 그 결과를 화면에 출력해 준다.
					$pushUrl = "https://fcm.googleapis.com/fcm/send";
					$headers = [];
					$headers[] = 'Content-Type: application/json';
					$headers[] = 'Authorization:key=' . GOOGLE_API_KEY;

					$notification = [
						'title' => $rinputData["title"],
						'body' => $rinputData["msg"],
						"state" => $rinputData["state"]
					];
					$extraNotificationData = ["message" => $notification];
					$data = array(
						"data" => $extraNotificationData,
						"notification" => $notification,
						"to"  => $rtokens, //token get on my ipad with the getToken method of cordova plugin,
					);
					//$json_data = json_encode($data);
					$json_data =  json_encode($data, JSON_UNESCAPED_UNICODE);
					//print_r($json_data);

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
				//요청자 푸시 끝

				//메이커 취소처리
				$upMQquery11 = "UPDATE TB_STAXISHARING SET taxi_State = '8', taxi_MState = :taxi_MState, reg_CDate = NOW() WHERE idx = :idx LIMIT 1";
				$upMStmt11 = $DB_con->prepare($upMQquery11);
				$upMStmt11->bindparam(":idx", $taxi_SIdx);
				$upMStmt11->bindparam(":taxi_MState", $taxi_State);
				$upMStmt11->execute();

				//투게더 취소처리
				$upMQquery22 = "UPDATE TB_RTAXISHARING SET taxi_RState = '8' WHERE taxi_SIdx = :taxi_SIdx LIMIT 1";
				$upMStmt22 = $DB_con->prepare($upMQquery22);
				$upMStmt22->bindparam(":taxi_SIdx", $taxi_SIdx);
				$upMStmt22->execute();

				$upMQquery33 = "UPDATE TB_RTAXISHARING_INFO SET reg_CDate = now(), taxi_RMemo = '관리자로 인한 취소' WHERE taxi_SIdx = :taxi_SIdx LIMIT 1";
				$upMStmt33 = $DB_con->prepare($upMQquery33);
				$upMStmt33->bindparam(":taxi_SIdx", $taxi_SIdx);
				$upMStmt33->execute();
				$cnt++;
			}
		}
	}
	if ($cnt == 0) {
		$result = array("result" => false, "errorMsg" => "노선은 있으나 조건에 만족하는 매칭 노선이 없습니다.");
	} else {
		$result = array("result" => true, "cnt" => (int)$cnt);
		$res_bit1 = 1;
	}
}

dbClose($DB_con);
$Stmt = null;
$chkLocStmt1 = null;
$chkLocStmt2 = null;
$upMStmt11 = null;
$upMStmt22 = null;
$upMStmt33 = null;
$Stmt3 = null;
$delMStmt = null;
$conStmt = null;
$chkRStmt = null;
$alDkchkStmt = null;
$alDkdelRStmt = null;
$alDkdelRStmt2 = null;
$alDkdelRStmt3 = null;
$alDkdelStmt = null;
$alDkdelStmt2 = null;
$alDkdelStmt3 = null;
$selRStmt = null;
$rSidStmt = null;
$memRTokStmt = null;
echo "
" . str_replace('\\/', '/', json_encode($result, JSON_UNESCAPED_UNICODE));
echo "
" . str_replace('\\/', '/', json_encode($result2, JSON_UNESCAPED_UNICODE));
?>