<?
	//공통 폼 (노선생성, 생성노선취소, 만남단계, 유효시간체크 후 취소처리(cron) 사용) - 남경태부장 요청 / 황상섭대리 작업 / 작업일 : 2019-04-22
	function common_Form($param=array()){
		$url = 'http://'.$_SERVER["HTTP_HOST"].'/now/send.php?'.http_build_query($param, '', '&');
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($param));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HEADER, false);//헤더 정보를 보내도록 함(*필수)
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Authorization:'.$access_token_value));
		//curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		$contents = curl_exec($ch);
		//$contents_json = json_decode($contents, true); // 결과값을 파싱
		curl_close($ch);
		//return $contents_json;
	}
?>
