<?

// 컴퓨터의 아이피와 쿠키에 저장된 아이피가 다르다면 테이블에 반영함
if (get_cookie('ck_visit_ip') != $_SERVER['REMOTE_ADDR']) {

    set_cookie('ck_visit_ip', $_SERVER['REMOTE_ADDR'], 86400); // 하루동안 저장

	$DB_con = db1();
	$sumQuery = "";
	$sumQuery = " SELECT SUM(vi_id) AS sumRow  FROM TB_VISIT  "; 
	//echo $sumQuery."<BR>";
	//exit;
	$sumStmt = $DB_con->prepare($sumQuery);
	$row = $sumStmt->fetch(PDO::FETCH_ASSOC);
	$max_vi_id = $row['sumRow'];
			
	$vi_id = $max_vi_id + 1;

    // $_SERVER 배열변수 값의 변조를 이용한 SQL Injection 공격을 막는 코드입니다. 110810
    $remote_addr = escape_trim($_SERVER['REMOTE_ADDR']);
    $referer = "";
    if (isset($_SERVER['HTTP_REFERER']))
        $referer = escape_trim(clean_xss_tags($_SERVER['HTTP_REFERER']));
    $user_agent  = escape_trim(clean_xss_tags($_SERVER['HTTP_USER_AGENT']));
    $vi_browser = '';
    $vi_os = '';
    $vi_device = '';
	$vi_date = DU_TIME_YMD;
	$vi_time = DU_TIME_HIS;

/*
    if(version_compare(phpversion(), '5.3.0', '>=') && defined('DU_BROWSCAP_USE') && DU_BROWSCAP_USE) {
		include DU_COM."/visit_browscap.php";
    }
*/

	try { 
		$visit_query = " insert TB_VISIT (  vi_ip, vi_date, vi_time, vi_referer, vi_agent, vi_browser, vi_os, vi_device ) values ( '{$remote_addr}', '".DU_TIME_YMD."', '".DU_TIME_HIS."', '{$referer}', '{$user_agent}', '{$vi_browser}', '{$vi_os}', '{$vi_device}' ) ";
		//echo $visit_query."<BR>";
		//exit;
		$DB_con->exec($visit_query);
		$result = "OK";
	} catch(PDOException $e)	{ //ip동일한 값 중복일 경우 저장안됨
		//echo $e->getMessage();   
		$result = "FAIL";
	}

    // 정상으로 INSERT 되었다면 방문자 합계에 반영
    if ($result == "OK") { 

		try { 
			$visit_query2 = " insert TB_VISIT_SUM ( vs_count, vs_date) values ( 1, '".DU_TIME_YMD."' ) ";
			$DB_con->exec($visit_query2);
			$result2 = "OK";
		} catch(PDOException $e)	{
			//echo $e->getMessage();   
			$result2 = "FAIL";
		}

        // DUPLICATE 오류가 발생한다면 이미 날짜별 행이 생성되었으므로 UPDATE 실행
        if ($result2 == "FAIL") {
            $visit_query3 = " update TB_VISIT_SUM set vs_count = vs_count + 1 where vs_date = '".DU_TIME_YMD."' ";
			$DB_con->exec($visit_query3);
        }

    }

	dbClose($DB_con);
	$stmt = null;


}
?>
