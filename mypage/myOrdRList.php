<?
include "../lib/common.php";
include "../lib/functionDB.php";  //공통 db함수

$mem_Id = trim($memId);				//아이디
$chkMonth = trim($chkMonth);		//구분 (최근 1개월 : 1, 최근 3개월 : 3, 최근 6개월 : 6)
$none_Date = DU_TIME_YMDHIS;           //등록일

if ($chkMonth == "") {
	$chkMonth = "1";		//최근 1개월 1
} else {
	$chkMonth = trim($chkMonth);
}

if ($mem_Id != ""  && $chkMonth != "") {  //아이디, 개월수가 있을 경우

	$DB_con = db1();

	$mem_Idx = memIdxInfo($mem_Id);   //회원 주아이디

	/* 전체 카운트 */
	$cntQuery = "SELECT r.idx FROM TB_RTAXISHARING AS r INNER JOIN TB_STAXISHARING AS s ON r.taxi_SIdx = s.idx AND (s.taxi_MState > 5 OR s.taxi_MState IS NULL)  WHERE r.taxi_RState IN ( '7', '8', '9' ) AND r.taxi_RMemIdx = :taxi_RMemIdx  AND r.taxi_DelBit = 'N'";
	$cntQuery .= " AND  r.reg_Date > SUBDATE(NOW(), INTERVAL :chkMonth MONTH)  ";

	$cntStmt = $DB_con->prepare($cntQuery);
	$cntStmt->bindparam(":taxi_RMemIdx", $mem_Idx);
	$cntStmt->bindparam(":chkMonth", $chkMonth);
	$cntStmt->execute();
	$totalCnt = $cntStmt->rowCount();

	if ($totalCnt == "") {
		$totalCnt = "0";
	} else {
		$totalCnt =  $totalCnt;
	}

	$totalCnt = (int)$totalCnt;

	$rows = 10;  //페이지 갯수
	$total_page  = ceil($totalCnt / $rows);  // 전체 페이지 계산
	if ($page == "") {
		$page = 1;
	} // 페이지가 없으면 첫 페이지 (1 페이지)
	$page = (int)$page;

	$from_record = ($page - 1) * $rows; // 시작 열을 구함

	/* /매칭(노선)요청자 이용내역 (예약요청완료, 만남중, 이동중, 완료, 취소)*/
	$hisQuery = "SELECT r.idx, r.taxi_SIdx, r.taxi_MemIdx, r.taxi_MemId, r.taxi_RTPrice, r.taxi_RState FROM TB_RTAXISHARING AS r INNER JOIN TB_STAXISHARING AS s ON r.taxi_SIdx = s.idx AND (s.taxi_MState > 5 OR s.taxi_MState IS NULL) WHERE r.taxi_RState IN ( '7', '8', '9' ) AND r.taxi_RMemIdx = :taxi_RMemIdx  AND r.taxi_DelBit = 'N'";
	$hisQuery .= "  AND  r.reg_Date > SUBDATE(NOW(), INTERVAL :chkMonth MONTH)  ORDER BY r.reg_Date DESC limit  {$from_record}, {$rows}  ";
	//echo $hisQuery."<BR>";
	//exit;
	$hisStmt = $DB_con->prepare($hisQuery);
	$hisStmt->bindparam(":taxi_RMemIdx", $mem_Idx);
	$hisStmt->bindparam(":chkMonth", $chkMonth);
	$hisStmt->execute();
	$mNum = $hisStmt->rowCount();

	if ($mNum < 1) { //아닐경우
		$chkResult = "0";
		$listInfoResult = array("totCnt" => (int)$totalCnt, "page" => (int)$page);
	} else {
		$chkResult = "1";
		$listInfoResult = array("totCnt" => (int)$totalCnt, "page" => (int)$page);

		$data  = [];
		while ($hrow = $hisStmt->fetch(PDO::FETCH_ASSOC)) {

			$idx = trim($hrow['idx']);							// 요청 고유번호
			$taxiSIdx = trim($hrow['taxi_SIdx']);				// 생성 고유번호
			$taxiMemIdx = trim($hrow['taxi_MemIdx']);	        // 생성자 고유번호 아이디
			$taxiMemId = trim($hrow['taxi_MemId']);	            // 생성자
			$taxiRTPrice = trim($hrow['taxi_RTPrice']);	        // 요청자 경로추가요금
			$taxiRState = trim($hrow['taxi_RState']);		    // 상태값

			if ($taxiRState == '8') {
				//메이커노선의 전 상태가 이동중 이후 것만 출력되게 처리.
				$makerQuery = "SELECT taxi_MState FROM TB_STAXISHARING WHERE idx = :taxi_Idx";
				$makerStmt = $DB_con->prepare($makerQuery);
				$makerStmt->bindparam(":taxi_Idx", $taxiSIdx);
				$makerStmt->execute();
				$makerRow = $makerStmt->fetch(PDO::FETCH_ASSOC);
				$taxiMState =  trim($makerRow['taxi_MState']);						//취소전 상태 값.

				if ((int)$taxiMState > 5) {
					$continue = true;
				} else {
					$continue = false;
				}
			} else {
				$continue = true;
			}
			if (!$continue) {
				continue;
			} else {
				//생성자 기본정보
				$macQuery = "";
				$macQuery = "SELECT taxi_Per, taxi_Price, taxi_MCancle FROM TB_STAXISHARING WHERE idx = :taxi_Idx AND taxi_MemIdx = :taxi_MemIdx AND taxi_MemId = :taxi_MemId  LIMIT 1 ";
				//$macQuery = "SELECT taxi_Price FROM TB_STAXISHARING WHERE idx = $taxiSIdx AND taxi_MemId = $taxiMemId  LIMIT 1 ";
				//echo $macQuery."<BR>";
				//exit;
				$macStmt = $DB_con->prepare($macQuery);
				$macStmt->bindparam(":taxi_Idx", $taxiSIdx);
				$macStmt->bindparam(":taxi_MemIdx", $taxiMemIdx);
				$macStmt->bindparam(":taxi_MemId", $taxiMemId);
				$macStmt->execute();
				$macNum = $macStmt->rowCount();
				//echo $macNum."<BR>";
				//exit;

				if ($macNum < 1) { //아닐경우
				} else {
					while ($macRow = $macStmt->fetch(PDO::FETCH_ASSOC)) {
						$taxiPer = trim($macRow['taxi_Per']);		        // 생성 % 	
						$taxiPrice = trim($macRow['taxi_Price']);		    // 희망쉐어링비용
						$taxiMCancle = trim($macRow['taxi_MCancle']);	    // 취소 (본인 : 0, 그외 : 1)	
					}
				}

				//생성자 지도정보
				$mapQuery = "SELECT taxi_SaddrNm, taxi_EaddrNm  FROM TB_STAXISHARING_MAP WHERE taxi_Idx = :taxi_Idx AND taxi_MemIdx = :taxi_MemIdx AND taxi_MemId = :taxi_MemId  LIMIT 1 ";
				//echo $mapQuery."<BR>";
				//exit;
				$mapStmt = $DB_con->prepare($mapQuery);
				$mapStmt->bindparam(":taxi_Idx", $taxiSIdx);
				$mapStmt->bindparam(":taxi_MemIdx", $taxiMemIdx);
				$mapStmt->bindparam(":taxi_MemId", $taxiMemId);
				$mapStmt->execute();
				$mapNum = $mapStmt->rowCount();
				//echo $mapNum."<BR>";

				if ($mapNum < 1) { //아닐경우
				} else {
					while ($mapRow = $mapStmt->fetch(PDO::FETCH_ASSOC)) {
						$taxiSaddrNm = trim($mapRow['taxi_SaddrNm']);					  //  출발지 주소
						$taxiEaddrNm = trim($mapRow['taxi_EaddrNm']);					  //  도착지 주소
					}
				}

				//요청자 신청 정보 가져오기
				$infoRQuery = "SELECT taxi_MState, taxi_MCancle, reg_RDate, reg_MDate, reg_EDate, reg_YDate, reg_CDate, reg_CMDate, reg_CYDate from TB_RTAXISHARING_INFO  WHERE taxi_RIdx = :taxi_RIdx AND taxi_RMemIdx = :taxi_RMemIdx AND taxi_RMemId = :taxi_RMemId LIMIT 1  ";
				$infoRStmt = $DB_con->prepare($infoRQuery);
				$infoRStmt->bindparam(":taxi_RIdx", $idx);
				$infoRStmt->bindparam(":taxi_RMemIdx", $mem_Idx);
				$infoRStmt->bindparam(":taxi_RMemId", $mem_Id);
				$infoRStmt->execute();
				$infoRNum = $infoRStmt->rowCount();

				if ($infoRNum < 1) { //아닐경우
				} else {
					while ($infoRRow = $infoRStmt->fetch(PDO::FETCH_ASSOC)) {
						$taxiMState = trim($infoRRow['taxi_MState']);	    	//요청 취소 이전 상태값
						$taxiRMCancle = trim($infoRRow['taxi_MCancle']);         //취소 (본인 : 0, 그외 : 1)
						$regRDate = trim($infoRRow['reg_RDate']);				//예약완료일	
						$regMDate = trim($infoRRow['reg_MDate']);				//만남중		
						$regEDate = trim($infoRRow['reg_EDate']);				//이동중
						$regYDate = trim($infoRRow['reg_YDate']);				//완료일
						$regCDate = trim($infoRRow['reg_CDate']);				//취소일
						$regCMDate = trim($infoRRow['reg_CMDate']);				//거래취소사유확인
						$regCYDate = trim($infoRRow['reg_CYDate']);				//거래완료확인

					}
				}

				//취소시 취소 사유 확인
				$cancleQuery = "SELECT taxi_CanRChk, taxi_CanCnt, taxi_MType, taxi_CPart, taxi_CRPart, taxi_CMemo FROM TB_SMATCH_STATE WHERE taxi_RIdx = :taxi_Ridx ";
				$cancleStmt = $DB_con->prepare($cancleQuery);
				$cancleStmt->bindparam(":taxi_Ridx", $idx);
				$cancleStmt->execute();
				$cancleNum = $cancleStmt->rowCount();

				if ($cancleNum < 1) { //아닐경우
					$cancleReason = "";
				} else {
					while ($cancleRow = $cancleStmt->fetch(PDO::FETCH_ASSOC)) {
						$taxiCanRChk = trim($cancleRow['taxi_CanRChk']);             // 최초신청자구분 (p : 메이커, c : 투게더)
						$taxiCanCnt = trim($cancleRow['taxi_CanCnt']);               // 취소사유 (1,2,3,4)
						$taxiMType = trim($cancleRow['taxi_MType']);             // 최초신청자구분 (p : 메이커, c : 투게더)
						$taxiCPart = trim($cancleRow['taxi_CPart']);               // 취소사유 (1,2,3,4)
						$taxiCRPart = trim($cancleRow['taxi_CRPart']);             // 취소동의사유 (1,2)
						$taxiCMemo = trim($cancleRow['taxi_CMemo']);              // 기타 취소 사유 메모
						if($taxiCMemo == ""){
							$taxiCMemo = "다른 사유가 있지만 입력하지 않았습니다.";
						}
						// if ($idx == 125) {
						//     echo $taxiMType . "\n";
						//     echo $taxiCRPart . "\n";
						//     echo $taxiCPart . "\n";
						//     echo $taxiCMemo . "\n";
						//     echo $cancleReason . "\n";
						//     exit;
						// }
						/*   
                            taxi_CRPart 
                            1 : 거래취소를 원하지 않습니다.
                            2 : 거래 취소는 동일하나 다른 사유입니다
                            3 : 기타 (5분 초과 미응답) 
                            4 : 동의합니다
                            
                            메이커입장인 경우 p
                            1 : 택시가 잡히지 않습니다.
                            2 : 나의 사정으로 취소합니다.
                            3 : 투게더의 사정으로 취소합니다.    
                            
                            투게더입장인 경우 c
                            1 : 택시가 잡히지 않습니다.
                            2 : 나의 사정으로 취소합니다.
                            3 : 메이커의 사정으로 취소합니다.
                            */
						if ($taxiCanRChk == "N" && (int)$taxiCanCnt == 2) {
							if ($taxiMType == "p") {
								if ($taxiCRPart == "2") {
									$cancleReason = $taxiCMemo;
								} else {
									if ($taxiCPart == "1") {
										$cancleReason = "택시가 잡히지 않습니다.";
									} else if ($taxiCPart == "2") {
										$cancleReason = "나의 사정으로 취소합니다.";
									} else if ($taxiCPart == "3") {
										$cancleReason = "투게더의 사정으로 취소합니다.";
									} else {
										$cancleReason = "";
									}
								}
							} else if ($taxiMType == "c") {
								if ($taxiCRPart == "2") {
									$cancleReason = $taxiCMemo;
								} else {
									if ($taxiCPart == "1") {
										$cancleReason = "택시가 잡히지 않습니다.";
									} else if ($taxiCPart == "2") {
										$cancleReason = "나의 사정으로 취소합니다.";
									} else if ($taxiCPart == "3") {
										$cancleReason = "메이커의 사정으로 취소합니다.";
									} else {
										$cancleReason = "";
									}
								}
							} else {
								$cancleReason = "";
							}
						} else {
							if ($taxiMType == "p") {
								if ($taxiCRPart == "2") {
									$cancleReason = $taxiCMemo;
								} else {
									if ($taxiCPart == "1") {
										$cancleReason = "택시가 잡히지 않습니다.";
									} else if ($taxiCPart == "2") {
										$cancleReason = "나의 사정으로 취소합니다.";
									} else if ($taxiCPart == "3") {
										$cancleReason = "투게더의 사정으로 취소합니다.";
									} else {
										$cancleReason = "";
									}
								}
								// if ($taxiCRPart == "1") {
								//     if ($taxiCPart == "1") {
								//         $cancleReason = "택시가 잡히지 않습니다.";
								//     } else if ($taxiCPart == "2") {
								//         $cancleReason = "나의 사정으로 취소합니다.";
								//     } else if ($taxiCPart == "3") {
								//         $cancleReason = "투게더의 사정으로 취소합니다.";
								//     } else {
								//         $cancleReason = "";
								//     }
								// } else if ($taxiCRPart == "2") {
								//     $cancleReason = "";
								// } else if ($taxiCRPart == "3") {
								//     $cancleReason = "";
								// } else if ($taxiCRPart == "4") {
								//     $cancleReason = "";
								// } else {
								//     $cancleReason = "";
								// }
							} else if ($taxiMType == "c") {
								if ($taxiCRPart == "2") {
									$cancleReason = $taxiCMemo;
								} else {
									if ($taxiCPart == "1") {
										$cancleReason = "택시가 잡히지 않습니다.";
									} else if ($taxiCPart == "2") {
										$cancleReason = "나의 사정으로 취소합니다.";
									} else if ($taxiCPart == "3") {
										$cancleReason = "메이커의 사정으로 취소합니다.";
									} else {
										$cancleReason = "";
									}
								}
							} else {
								$cancleReason = "";
							}
						}
					}
				}

				//결제타입
				$ordQuery = "SELECT taxi_OrdPrice, taxi_OrdPoint FROM TB_ORDER WHERE taxi_SIdx = :taxi_SIdx AND taxi_RIdx = :taxi_RIdx LIMIT 1"; //투게더 정보
				$ordStmt = $DB_con->prepare($ordQuery);
				$ordStmt->bindparam(":taxi_SIdx", $taxiSIdx);
				$ordStmt->bindparam(":taxi_RIdx", $idx);
				$ordStmt->execute();
				$ordnum = $ordStmt->rowCount();
				//echo $vnum."<BR>";

				if ($ordnum < 1) { //아닐경우
					$taxiOrdPrice = 0;					// 카드결제금액
					$taxiOrdPoint = 0;					// 사용한포인트
				} else {
					while ($ordrow = $ordStmt->fetch(PDO::FETCH_ASSOC)) {
						$taxiOrdPrice = trim($ordrow['taxi_OrdPrice']);					// 카드결제금액
						$taxiOrdPoint = trim($ordrow['taxi_OrdPoint']);					// 사용한포인트
					}
				}

				if ($taxiRState == "7") { //완료
					$taxiStaeNm = "완료";
					$taxiPrice = $taxiOrdPrice;
					$taxiPoint = $taxiOrdPoint;
					$regDate = DateHard($regYDate, 8);			  // 완료일
				} else if ($taxiRState == "8") { //취소
					$taxiStaeNm = "취소";
					$taxiPrice = $taxiOrdPrice;
					$taxiPoint = $taxiOrdPoint;
					$regDate = DateHard($regCDate, 8);		  // 취소일
				} else if ($taxiRState == "9") { //취소사유확인
					$taxiStaeNm = "취소사유확인";
					$taxiPrice = $taxiOrdPrice;
					$taxiPoint = $taxiOrdPoint;
					$regDate = DateHard($regCMDate, 8);		  // 취소일
				} else if ($taxiRState == "10") { //거래완료확인
					$taxiStaeNm = "거래완료확인";
					$taxiPrice = $taxiOrdPrice;
					$taxiPoint = $taxiOrdPoint;
					$regDate = DateHard($regCYDate, 8);		  // 취소일
				}

				if($regDate == ""){
					$regDate = $none_Date;
				}

				$mresult = [
					"idx" => (int)$idx,
					"regDate" => (string)$regDate,
					"taxiStaeNm" => (string)$taxiStaeNm,
					"taxiSaddrNm" => (string)$taxiSaddrNm,
					"taxiEaddrNm" => (string)$taxiEaddrNm,
					"taxiPoint" => (int)$taxiPoint,
					"taxiPrice" => (int)$taxiPrice,
					"cancleReason" => (string)$cancleReason
				];
				array_push($data, $mresult);
			}
		}

		$chkData = [];
		$chkData["result"] = true;
		$chkData["listInfo"] = $listInfoResult;  //카운트 관련
		$chkData['lists'] = $data;
	}

	if ($chkResult  == "1") {
		$output = str_replace('\\\/', '/', json_encode($chkData, JSON_UNESCAPED_UNICODE));
	} else if ($chkResult  == "0") {
		$chkData2["result"] = true;
		$chkData2["listInfo"] = $listInfoResult;  //카운트 관련
		$chkData['lists'] = [];
		$output = str_replace('\\\/', '/', json_encode($chkData2, JSON_UNESCAPED_UNICODE));
	}

	echo  urldecode($output);
	dbClose($DB_con);
	$cntStmt = null;
	$hisStmt = null;
	$mapRStmt = null;
	$infoStmt = null;
	$mapStmt = null;
	$chkStmt = null;
	$infoRStmt = null;
	$memStmt = null;
	$mpStmt = null;
	$ordStmt = null;
} else {
	$result = array("result" => false, "errorMsg" => "조회 정보가 없습니다. 확인 후 다시 시도해주세요.");
	echo json_encode($result, JSON_UNESCAPED_UNICODE);
}
