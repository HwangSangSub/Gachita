<?
	// 아임포트 RES_Key, RES_Secret 값 선언
	$imp_key = "8794927633087998";
	$imp_secret = "5E36m7pcRnDPCmsYGuMsqMl4i6BqSNoXss4Ozu1rRFgt1IsHdeaE0vIjCkJu78sAAxoa1qBS5Rm7AOOO";

	// 아임포트 통신을 위한 토큰값 발급
	function get_Token_PayForm($Token_url, $Token_param=array()){
		$Token_url = $Token_url.'?'.http_build_query($Token_param, '', '&');
		$Token_ch = curl_init();
		curl_setopt($Token_ch, CURLOPT_URL, $Token_url);
		curl_setopt($Token_ch, CURLOPT_POST, 1);
		curl_setopt($Token_ch, CURLOPT_POSTFIELDS, http_build_query($Token_param));
		curl_setopt($Token_ch, CURLOPT_HEADER, false);//헤더 정보를 보내도록 함(*필수)
		curl_setopt($Token_ch, CURLOPT_RETURNTRANSFER, true);
		//curl_setopt($Token_ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($Token_ch, CURLOPT_SSL_VERIFYPEER, 0);
		$Token_contents = curl_exec($Token_ch); 
		$Token_contents_json = json_decode($Token_contents, true); // 결과값을 파싱
		curl_close($Token_ch);
		return $Token_contents_json['response']['access_token'];
	}	

	function test_Token_PayForm($Token_url, $Token_param=array()){
		$Token_url = $Token_url.'?'.http_build_query($Token_param, '', '&');
		$Token_ch = curl_init();
		curl_setopt($Token_ch, CURLOPT_URL, $Token_url);
		curl_setopt($Token_ch, CURLOPT_POST, 1);
		curl_setopt($Token_ch, CURLOPT_POSTFIELDS, http_build_query($Token_param));
		curl_setopt($Token_ch, CURLOPT_HEADER, false);//헤더 정보를 보내도록 함(*필수)
		curl_setopt($Token_ch, CURLOPT_RETURNTRANSFER, true);
		//curl_setopt($Token_ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($Token_ch, CURLOPT_SSL_VERIFYPEER, 0);
		$Token_contents = curl_exec($Token_ch); 
		$Token_contents_json = json_decode($Token_contents, true); // 결과값을 파싱
		curl_close($Token_ch);
		return $Token_contents_json;
	}
	//공통 폼 (결제에 사용중)
	function common_Form($url, $param=array(), $access_token_value){
		$url = $url.'?'.http_build_query($param, '', '&');
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
		$contents_json = json_decode($contents, true); // 결과값을 파싱
		curl_close($ch);
		return $contents_json;
	}	
	function test_common_Form($url, $param=array(), $access_token_value){
		$url = $url.'?'.http_build_query($param, '', '&');
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
		$contents_json = json_decode($contents, true); // 결과값을 파싱
		curl_close($ch);
		return $contents_json['response'];
	}
	function pay_Order_PayForm($Order_url, $Order_param=array(), $access_token_value){
		$Order_url = $Order_url.'?'.http_build_query($Order_param, '', '&');
		$Order_ch = curl_init();
		curl_setopt($Order_ch, CURLOPT_URL, $Order_url);
		curl_setopt($Order_ch, CURLOPT_POST, 1);
		curl_setopt($Order_ch, CURLOPT_POSTFIELDS, http_build_query($Order_param));
		curl_setopt($Order_ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($Order_ch, CURLOPT_HEADER, false);//헤더 정보를 보내도록 함(*필수)
		curl_setopt($Order_ch, CURLOPT_HTTPHEADER, array('Authorization:'.$access_token_value));
		//curl_setopt($Order_ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($Order_ch, CURLOPT_SSL_VERIFYPEER, 0);
		$Order_contents = curl_exec($Order_ch); 
		$Order_contents_json = json_decode($Order_contents, true); // 결과값을 파싱
		curl_close($Order_ch);
		return $Order_contents_json;
	}
	// 카드등록 시 사용 (빌링키 발급)
	function set_Billing_Key($Billing_url, $Billing_param=array(), $access_token_value){
		$Billing_url = $Billing_url.'?'.http_build_query($Billing_param, '', '&');
		$Billing_ch = curl_init();
		curl_setopt($Billing_ch, CURLOPT_URL, $Billing_url);
		curl_setopt($Billing_ch, CURLOPT_POST, 1);
		curl_setopt($Billing_ch, CURLOPT_POSTFIELDS, $Billing_param);
		curl_setopt($Billing_ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($Billing_ch, CURLOPT_HEADER, false);//헤더 정보를 보내도록 함(*필수)
		curl_setopt($Billing_ch, CURLOPT_HTTPHEADER, array('Authorization:'.$access_token_value));
		//curl_setopt($Billing_ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($Billing_ch, CURLOPT_SSL_VERIFYPEER, 0);
		$Billing_contents = curl_exec($Billing_ch); 
		$Billing_contents_json = json_decode($Billing_contents, true); // 결과값을 파싱
		curl_close($Billing_ch);
		//echo print_r($Billing_contents_json);
		return $Billing_contents;
	}
	// 카드삭제 시 사용 (빌링키 발급삭제)
	function Del_Billing_Key($Billing_Del_url, $Billing_Del_param=array(), $access_token_value){
		$Billing_Del_url = $Billing_Del_url.'?'.http_build_query($Billing_Del_param, '', '&');
		$Billing_Del_ch = curl_init();
		curl_setopt($Billing_Del_ch, CURLOPT_URL, $Billing_Del_url);
		curl_setopt($Billing_Del_ch, CURLOPT_POST, 1);
		curl_setopt($Billing_Del_ch, CURLOPT_POSTFIELDS, http_build_query($Billing_Del_param));
		curl_setopt($Billing_Del_ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($Billing_Del_ch, CURLOPT_HEADER, false);//헤더 정보를 보내도록 함(*필수)
		curl_setopt($Billing_Del_ch, CURLOPT_HTTPHEADER, array('Authorization:'.$access_token_value));
		curl_setopt($Billing_Del_ch, CURLOPT_CUSTOMREQUEST, "DELETE"); //삭제한다는 정보를 넘기기 위해서
		//curl_setopt($Billing_Del_ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($Billing_Del_ch, CURLOPT_SSL_VERIFYPEER, 0);
		$Billing_Del_contents = curl_exec($Billing_Del_ch); 
		$Billing_Del_contents_json = json_decode($Billing_Del_contents, true); // 결과값을 파싱
		curl_close($Billing_Del_ch);
		// return $Billing_Del_contents_json;
		return $Billing_Del_contents_json['code'];
	}
	// 본인인증 조회시 사용
	function certifi_Chk($url, $access_token_value){
		$url = $url;
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HEADER, false);//헤더 정보를 보내도록 함(*필수)
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Authorization:'.$access_token_value));
		//curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		$contents = curl_exec($ch); 
		$contents_json = json_decode($contents, true); // 결과값을 파싱
		curl_close($ch);
		return $contents_json;
	}

	//공통 폼 (결제에 사용중)
	function tmap_Api($url, $param=array(), $access_token_value){
		$url = $url.'?'.http_build_query($param, '', '&');
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($param));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HEADER, false);//헤더 정보를 보내도록 함(*필수)
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('appKey:'.$access_token_value));
		//curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		$contents = curl_exec($ch); 
		$contents_json = json_decode($contents, true); // 결과값을 파싱
		curl_close($ch);
		return $contents_json;
	}	
?>
