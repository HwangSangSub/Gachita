<?
	$taxi_Idx = trim($idx);							// 노선번호
	$taxi_MemType = trim($mode);				    // 노선타입( p : 요청자 // c : 생성자)

	if ( $taxi_Idx != "" && $taxi_MemType != "") {  // 위도, 경도, km

		$DB_con = db1();

		if($taxi_MemType == "c"){		//요청자 인 경우
			$chkQuery = "SELECT idx, reg_Date from TB_RTAXISHARING WHERE idx = :idx AND taxi_RState IN ('6', '7', '8', '9', '10' ) LIMIT 1 " ;
			$chkstmt = $DB_con->prepare($chkQuery);
			$chkstmt->bindparam(":idx",$taxi_Idx);
			$chkstmt->execute();
			$num = $chkstmt->rowCount();
		}else if($taxi_MemType == "p"){
			$chkQuery = "SELECT idx, reg_Date from TB_RTAXISHARING WHERE taxi_SIdx = :taxi_SIdx AND taxi_RState IN ('6', '7', '8', '9', '10' ) LIMIT 1 " ;
			$chkstmt = $DB_con->prepare($chkQuery);
			$chkstmt->bindparam(":taxi_SIdx",$taxi_Idx);
			$chkstmt->execute();
			$num = $chkstmt->rowCount();
		}else{
			$result = array("result" => "error","errorMsg" => "회원구분이 없습니다." );
		}


		if($num < 1)  { //아닐경우
			$result = array("result" => "error", "errorMsg" => "이동중 또는 이동완료 된 노선이 아닙니다. #1 ");
			$chkResult = "0";
		} else {
			while($Row=$chkstmt->fetch(PDO::FETCH_ASSOC)) {
				$reg_Date = $Row['reg_Date'];		// 푸시발송수
			}
			$regDate = date("Ymd",strtotime ($reg_Date));
			$TableName = "TB_SHARING_GPS_".$regDate;


			$chkTQuery = "SHOW TABLES LIKE '".$TableName."';";
			$chkTStmt = $DB_con->prepare($chkTQuery);
			$chkTStmt->execute();
			$chkTnum = $chkTStmt->rowCount();

			if($chkTnum < 0){
				$result = array("result" => "error");
			}else{
				/* 전체 카운트 */
				$Query = "SELECT taxi_Lng, taxi_Lat, reg_Date ";
				$Query .= " FROM ".$TableName." ";
				$Query .= " WHERE taxi_Idx = :taxi_Idx AND taxi_MemType = :taxi_MemType ORDER BY reg_Date ASC ; ";
				echo $cntQuery."<BR>";
				//exit;
				$Stmt = $DB_con->prepare($Query);
				$Stmt->bindparam(":taxi_Idx",$taxi_Idx);
				$Stmt->bindparam(":taxi_MemType",$taxi_MemType);
				$Stmt->execute();
				$num = $Stmt->rowCount();
				if($num < 0){
					$result = "{lat: ".$lat.", lng: ".$lng."}";
				}else{
					$chkResult = "1";
					$result  = "";
					$marker  = "";
					$cnt = 0;
					while($row=$Stmt->fetch(PDO::FETCH_ASSOC)) {

						$taxiLat = trim($row['taxi_Lat']);	      // 위도
						$taxiLng = trim($row['taxi_Lng']);	      // 경도
						$lat = round($taxiLat, 3);
						$lng = round($taxiLng, 3);
						$reg_Date = trim($row['reg_Date']);	      // 경도
						if($cnt < 1){
							$result1 = "{lat: ".$lat.", lng: ".$lng."}";
							$f_result = $result1;
							$result = $result.$result1;
							$marker1 = "var marker = new google.maps.Marker({position: ".$result1.", map: map, title: '".$reg_Date."'});";
							$marker = $marker.$marker1;
						}else{
							$result1 = ", {lat: ".$lat.", lng: ".$lng."}";
							$m_result = "{lat: ".$lat.", lng: ".$lng."}";
							$result = $result.$result1;
							$marker1 = "var marker = new google.maps.Marker({position: ".$m_result.", map: map, title: '".$reg_Date."'});";
							$marker = $marker.$marker1;
						}
						$cnt++;
					}
					$chkData['data'] = $result;
				}
			}
        }
/*
		if ($chkResult  == "1"  ) {
			$output = str_replace('\\\/', '/', json_encode($chkData, JSON_UNESCAPED_UNICODE));
		} else {
			echo json_encode($result, JSON_UNESCAPED_UNICODE);
		}
*/

		dbClose($DB_con);
		$chkstmt = null;
		$chkTStmt = null;
		$stmt = null;

		// echo  urldecode($output);

	} else {
		$result = array("result" => "error");
		//echo json_encode($result, JSON_UNESCAPED_UNICODE);

	}






?>
