<?
include "../../udev/lib/common.php";
include "../../lib/alertLib.php";

$idx = trim($idx);											//설정고유번호
$mode = trim($mode);										// 모드 (수정만 가능)

$con_Distance = trim($conDistance);
$con_MaxDc = trim($conMaxDc);
$con_mTxt = trim($con_mTxt);
$con_Sec = trim($conSec);
$con_GpsTime = trim($con_GpsTime);							//GPS신호 재 탐색 시간(분단위)
$con_GpsPTime = trim($con_GpsPTime);						//GPS신호 재 탐색시간(분단위)
$con_GpsYTime = trim($con_GpsYTime);						//GPS동일위치 경고 이후 상대방알림시간(분단위)
$con_GpsRegTime = trim($conGpsRegTime);						//GPS 위치기록시간 (분단위)
$con_BtnTime = trim($con_BtnTime);							//바로양도 제한시간(%단위)
$con_OrdFCnt = trim($conOrdFCnt);							//결제실패재시도횟수
$con_SharingD = trim($conSharingD);
$con_SharingS = trim($conSharingS);
$con_ETime = trim($conETime);								//노선유효시간
$con_FTime = trim($conFTime);								//매칭중노선취소시간_유효기간만료 후 (시간단위)
$conComp1_Max_D = trim($conComp1_Max_D);					//조건 1 : 최대거리
$conComp1_H = trim($conComp1_H);							//조건 1 : 제한시간
$conComp2_Min_D = trim($conComp2_Min_D);					//조건 2 : 최소거리
$conComp2_Max_D = trim($conComp2_Max_D);					//조건 2 : 최대거리
$conComp2_H = trim($conComp2_H);							//조건 2 : 제한시간
$conComp3_Min_D = trim($conComp3_Min_D);					//조건 3 : 최소거리
$conComp3_Max_D = trim($conComp3_Max_D);					//조건 3 : 최대거리
$conComp3_H = trim($conComp3_H);							//조건 3 : 제한시간
$conComp4_Min_D = trim($conComp4_Min_D);					//조건 4 : 최소거리
$conComp4_H = trim($conComp4_H);							//조건 4 : 제한시간

$conChCnt = trim($conChCnt);								//추천 가능 수
$conChPoint = trim($conChPoint);							//추천 적립율

$conNewEventBit	= trim($conNewEventBit);					// 신규가입 진행여부(0: 진행안함, 1:진행)
$conNewEventDate = trim($conNewEventDate);					// 신규가입 이벤트
$conNewEventPoint = trim($conNewEventPoint);				// 신규가입시 지급할 포인트

$conGuideUrl = trim($conGuideUrl);							// 웹뷰가이드 경로

// 즐겨찾기 주소 최대 등록 수
$conAddrMaxCnt =trim($conAddrMaxCnt);						// 즐겨찾기 주소 최대 등록 가능 수

// 가치타기 택시 인증 이벤트
$conTaxiRate = trim($conTaxiRate);							// 가치타기 택시 인증 이벤트 포인트 비율
$conTaxiResDate = trim($conTaxiResDate);					// 가치타기 택시 인증 이벤트 적립예정일 (일)
$conTaxiEventRate = trim($conTaxiEventRate);				// 가치타기 택시 인증 이벤트 추가 적립 이벤트시 포인트 비율
$conTaxiEventBit = trim($conTaxiEventBit);					// 가치타기 택시 인증 이벤트 추가 적립 이벤트 진행 여부 (진행 : Y, 종료 : N)
$conTaxiEventStartDate = trim($conTaxiEventStartDate);		// 가치타기 택시 인증 이벤트 추가 적립 이벤트 시작일
$conTaxiEventEndDate = trim($conTaxiEventEndDate);			// 가치타기 택시 인증 이벤트 추가 적립 이벤트 종료일

// 메이커 생성 요금
$conMinPriceRate = trim($conMinPriceRate);					// 메이커 생성 최소 요금(%)
$conMaxPriceRate = trim($conMaxPriceRate);					// 메이커 생성 최대 요금(%)
$conIntervalPriceRate = trim($conIntervalPriceRate);		// 메이커 생성 구간별 요금(%)

$DB_con = db1();

if ($mode == '') {
	$mode = "mod";
}
if ($mode == "reg") {

} else if ($mode == "mod") { //수정일경우
	$upQquery = "
			UPDATE TB_CONFIG 
			SET 
				con_SharingD = :con_SharingD, 
				con_SharingS = :con_SharingS, 
				con_ETime = :con_ETime, 
				con_FTime = :con_FTime,
				con_Distance = :con_Distance, 
				con_MaxDc = :con_MaxDc, 
				con_Sec = :con_Sec, 
				con_GpsTime = :con_GpsTime, 
				con_GpsPTime = :con_GpsPTime, 
				con_GpsYTime = :con_GpsYTime, 
				con_GpsRegTime = :con_GpsRegTime, 
				con_BtnTime = :con_BtnTime, 
				con_OrdFCnt = :con_OrdFCnt,
				con_mTxt = :con_mTxt, 
				conComp1_Max_D = :conComp1_Max_D, 
				conComp1_H = :conComp1_H, 
				conComp2_Min_D = :conComp2_Min_D, 
				conComp2_Max_D = :conComp2_Max_D, 
				conComp2_H = :conComp2_H, 
				conComp3_Min_D = :conComp3_Min_D, 
				conComp3_Max_D = :conComp3_Max_D, 
				conComp3_H = :conComp3_H, 
				conComp4_Min_D = :conComp4_Min_D, 
				conComp4_H = :conComp4_H, 
				con_ChCnt = :con_ChCnt,
				con_ChPoint = :con_ChPoint,
				con_NewEventBit = :con_NewEventBit,
				con_NewEventDate = :con_NewEventDate,
				con_NewEventPoint = :con_NewEventPoint,
				con_AddrMaxCnt = :con_AddrMaxCnt,
				con_GuideUrl = :con_GuideUrl,
				con_TaxiRate = :con_TaxiRate, 
				con_TaxiEventRate = :con_TaxiEventRate,
				con_TaxiResDate = :con_TaxiResDate,
				con_TaxiEventBit = :con_TaxiEventBit,
				con_TaxiEventStartDate = :con_TaxiEventStartDate,
				con_TaxiEventEndDate = :con_TaxiEventEndDate,
				con_MinPriceRate = :con_MinPriceRate,
				con_MaxPriceRate = :con_MaxPriceRate,
				con_IntervalPriceRate = :con_IntervalPriceRate
			WHERE 
				idx = :idx  
			LIMIT 1";

	$upStmt = $DB_con->prepare($upQquery);
	$upStmt->bindParam(":con_SharingD", $con_SharingD);
	$upStmt->bindParam(":con_SharingS", $con_SharingS);
	$upStmt->bindParam(":con_ETime", $con_ETime);
	$upStmt->bindParam(":con_FTime", $con_FTime);
	$upStmt->bindParam(":con_Distance", $con_Distance);
	$upStmt->bindParam(":con_MaxDc", $con_MaxDc);
	$upStmt->bindParam(":con_Sec", $con_Sec);
	$upStmt->bindParam(":con_GpsTime", $con_GpsTime);
	$upStmt->bindParam(":con_GpsPTime", $con_GpsPTime);
	$upStmt->bindParam(":con_GpsYTime", $con_GpsYTime);
	$upStmt->bindParam(":con_GpsRegTime", $con_GpsRegTime);
	$upStmt->bindParam(":con_BtnTime", $con_BtnTime);
	$upStmt->bindParam(":con_OrdFCnt", $con_OrdFCnt);
	$upStmt->bindParam(":con_mTxt", $con_mTxt);
	$upStmt->bindParam(":conComp1_Max_D", $conComp1_Max_D);
	$upStmt->bindParam(":conComp1_H", $conComp1_H);
	$upStmt->bindParam(":conComp2_Min_D", $conComp2_Min_D);
	$upStmt->bindParam(":conComp2_Max_D", $conComp2_Max_D);
	$upStmt->bindParam(":conComp2_H", $conComp2_H);
	$upStmt->bindParam(":conComp3_Min_D", $conComp3_Min_D);
	$upStmt->bindParam(":conComp3_Max_D", $conComp3_Max_D);
	$upStmt->bindParam(":conComp3_H", $conComp3_H);
	$upStmt->bindParam(":conComp4_Min_D", $conComp4_Min_D);
	$upStmt->bindParam(":conComp4_H", $conComp4_H);
	$upStmt->bindParam(":con_ChCnt", $conChCnt);
	$upStmt->bindParam(":con_ChPoint", $conChPoint);
	$upStmt->bindParam(":con_NewEventBit", $conNewEventBit);
	$upStmt->bindParam(":con_NewEventDate", $conNewEventDate);
	$upStmt->bindParam(":con_NewEventPoint", $conNewEventPoint);
	$upStmt->bindParam(":con_AddrMaxCnt", $conAddrMaxCnt);
	$upStmt->bindParam(":con_GuideUrl", $conGuideUrl);
	$upStmt->bindParam(":con_TaxiRate", $conTaxiRate);
	$upStmt->bindParam(":con_TaxiEventRate", $conTaxiEventRate);
	$upStmt->bindParam(":con_TaxiResDate", $conTaxiResDate);
	$upStmt->bindParam(":con_TaxiEventBit", $conTaxiEventBit);
	$upStmt->bindParam(":con_TaxiEventStartDate", $conTaxiEventStartDate);
	$upStmt->bindParam(":con_TaxiEventEndDate", $conTaxiEventEndDate);
	$upStmt->bindParam(":con_MinPriceRate", $conMinPriceRate);
	$upStmt->bindParam(":con_MaxPriceRate", $conMaxPriceRate);
	$upStmt->bindParam(":con_IntervalPriceRate", $conIntervalPriceRate);
	$upStmt->bindParam(":idx", $idx);
	$upStmt->execute();

	$preUrl = "configReg.php";
	$message = "mod";
	proc_msg($message, $preUrl);
}


dbClose($DB_con);
$stmt = null;
$stmt1 = null;
$upStmt = null;
