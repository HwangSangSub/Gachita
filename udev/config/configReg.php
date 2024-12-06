<?
$menu = "1";
$smenu = "1";

include "../common/inc/inc_header.php";  //헤더 1

$titNm = "환경설정";

$DB_con = db1();

$query = "";
$query = "SELECT idx, con_Distance, con_SharingD, con_SharingS, con_ETime, con_FTime, con_MaxDc, con_Sec, con_GpsTime, con_GpsPTime, con_GpsYTime, con_GpsRegTime, con_BtnTime, con_OrdFCnt, con_mTxt, conComp1_Max_D, conComp1_H, conComp2_Min_D, conComp2_Max_D, conComp2_H, conComp3_Min_D, conComp3_Max_D, conComp3_H, conComp4_Min_D, conComp4_H, conRecom_RC, conRecom_RP, conRecom_BRC, conRecom_BRP, con_ChCnt, con_ChPoint, con_NewEventBit, con_NewEventDate, con_NewEventPoint, con_AddrMaxCnt, con_GuideUrl, con_TaxiRate, con_TaxiResDate, con_TaxiEventRate, con_TaxiEventBit, con_TaxiEventStartDate, con_TaxiEventEndDate, con_MinPriceRate, con_MaxPriceRate, con_IntervalPriceRate, con_TodayOx FROM TB_CONFIG";
$stmt = $DB_con->prepare($query);
$stmt->execute();

$row = $stmt->fetch(PDO::FETCH_ASSOC);

$idx = $row['idx'];
$con_SharingD = $row['con_SharingD'];							// 만남완료변수설정(거리_m)
$con_SharingS = $row['con_SharingS'];							// 만남완료변수설정(시간_초)
$con_ETime = $row['con_ETime'];									// 노선유효시간(분)
$con_FTime = $row['con_FTime'];									// 매칭중노선취소시간_유효시간지난경우(시)
$con_Distance = $row['con_Distance'];							// 매칭가능거리(m)
$con_MaxDc = $row['con_MaxDc'];									// 매칭증가요금(%)
$con_Sec = $row['con_Sec'];										// 매칭현황시간(초)
$con_GpsTime = $row['con_GpsTime'];								// GPS신호 재 탐색시간(분단위)
$con_GpsPTime = $row['con_GpsPTime'];							// GPS동일위치 재탐색 시간(분단위)
$con_GpsYTime = $row['con_GpsYTime'];							// GPS동일위치 경고 이후 상대방알림시간(분단위)
$con_GpsRegTime = $row['con_GpsRegTime'];						// GPS위치기록시간 (분단위)
$con_BtnTime = $row['con_BtnTime'];								// 바로양도 제한시간(%단위)
$con_OrdFCnt = $row['con_OrdFCnt'];								// 결제실패 재시도 횟수
$con_mTxt = $row['con_mTxt'];									// 취소안내문구
$conComp1_Max_D = $row['conComp1_Max_D'];						// 조건 1 : 최대거리
$conComp1_H = $row['conComp1_H'];								// 조건 1 : 제한시간
$conComp2_Min_D = $row['conComp2_Min_D'];						// 조건 2 : 최소거리
$conComp2_Max_D = $row['conComp2_Max_D'];						// 조건 2 : 최대거리
$conComp2_H = $row['conComp2_H'];								// 조건 2 : 제한시간
$conComp3_Min_D = $row['conComp3_Min_D'];						// 조건 3 : 최소거리
$conComp3_Max_D = $row['conComp3_Max_D'];						// 조건 3 : 최대거리
$conComp3_H = $row['conComp3_H'];								// 조건 3 : 제한시간
$conComp4_Min_D = $row['conComp4_Min_D'];						// 조건 4 : 최소거리
$conComp4_H = $row['conComp4_H'];								// 조건 4 : 제한시간

// 추천인 적립
$con_ChCnt = $row['con_ChCnt'];									// 추천가능횟수
$con_ChPoint = $row['con_ChPoint'];								// 추천적립율(%)

// 신규 가입 이벤트
$con_NewEventBit = $row['con_NewEventBit'];						// 신규가입 이벤트 진행 여부(0: 진행안함, 1: 진행)
$con_NewEventDate = $row['con_NewEventDate'];					// 신규가입 이벤트 종료일
$con_NewEventPoint = $row['con_NewEventPoint'];					// 신규가입시 지급할 포인트

// 즐겨찾기 주소 최대 등록 수
$con_AddrMaxCnt = $row['con_AddrMaxCnt'];						// 즐겨찾기 주소 최대 등록 가능 수

// 가이드웹뷰경로
$con_GuideUrl = $row['con_GuideUrl'];							// 가이드웹뷰경로

// 가치타기 택시 인증 이벤트
$con_TaxiRate = $row['con_TaxiRate'];							// 가치타기 택시 인증 이벤트 포인트 비율
$con_TaxiResDate = $row['con_TaxiResDate'];						// 가치타기 택시 인증 이벤트 적립예정일 (일)
$con_TaxiEventRate = $row['con_TaxiEventRate'];					// 가치타기 택시 인증 이벤트 추가 적립 이벤트시 포인트 비율
$con_TaxiEventBit = $row['con_TaxiEventBit'];					// 가치타기 택시 인증 이벤트 추가 적립 이벤트 진행 여부 (진행 : Y, 종료 : N)
$con_TaxiEventStartDate = $row['con_TaxiEventStartDate'];		// 가치타기 택시 인증 이벤트 추가 적립 이벤트 시작일
$con_TaxiEventEndDate = $row['con_TaxiEventEndDate'];			// 가치타기 택시 인증 이벤트 추가 적립 이벤트 종료일

// 메이커 생성 요금
$con_MinPriceRate = $row['con_MinPriceRate'];					// 메이커 생성 최소 요금(%)
$con_MaxPriceRate = $row['con_MaxPriceRate'];					// 메이커 생성 최대 요금(%)
$con_IntervalPriceRate = $row['con_IntervalPriceRate'];			// 메이커 생성 구간별 요금(%)

// 오늘의 OX 퀴즈 문제
$con_TodayOx = $row['con_TodayOx'];								// 오늘의 OX 퀴즈 문제 (TB_OX 테이블 참조)

if ($idx == "") {
	$conRecom_BRPode = "reg";
} else {
	$mode = "mod";
}

dbClose($DB_con);
$stmt = null;

include "../common/inc/inc_gnb.php";  //헤더 
include "../common/inc/inc_menu.php";  //메뉴 

?>

<div id="wrapper">

	<div id="container" class="">
		<h1 id="container_title"><?= $titNm ?></h1>
		<div class="container_wr">
			<form name="fmember" id="fmember" action="configProc.php" onsubmit="return f_submit(this);" method="post" enctype="multipart/form-data" autocomplete="off">
				<input type="hidden" name="mode" id="mode" value="<?= $mode ?>">
				<input type="hidden" name="idx" id="idx" value="<?= $idx ?>">

				<div class="tbl_frm01 tbl_wrap">
					<table>
						<caption><?= $titNm ?></caption>
						<colgroup>
							<col class="grid_4">
							<col>
							<col class="grid_4">
							<col>
						</colgroup>
						<tbody>
							<tr>
								<th scope="row" colspan="2"><label for="conSharingD">만남완료변수설정 (거리)</label></th>
								<td><input type="text" name="conSharingD" id="conSharingD" class="frm_input" size="15" maxlength="20" value="<?= $con_SharingD ?>"> m</td>
								<th scope="row"><label for="conSharingS">만남완료변수설정 (재요청시간)</label></th>
								<td><input type="text" name="conSharingS" id="conSharingS" class="frm_input" size="15" maxlength="20" value="<?= $con_SharingS ?>"> 초</td>
							</tr>
							<tr>
								<th scope="row" colspan="2"><label for="conETime">노선유효시간</label></th>
								<td><input type="text" name="conETime" id="conETime" class="frm_input" size="15" maxlength="20" value="<?= $con_ETime ?>"> 분</td>
								<th scope="row"><label for="conFTime">매칭중 노선 취소 시간(유효시간 초과한 경우)</label></th>
								<td><input type="text" name="conFTime" id="conFTime" class="frm_input" size="15" maxlength="20" value="<?= $con_FTime ?>"> 시간</td>
							</tr>
							<tr>
								<th scope="row" colspan="2"><label for="con_GpsPTime">GPS동일위치 재탐색 시간</label></th>
								<td><input type="text" name="con_GpsPTime" id="con_GpsPTime" class="frm_input" size="15" maxlength="20" value="<?= $con_GpsPTime ?>"> 분</td>
								<th scope="row"><label for="con_GpsYTime">GPS동일위치 경고 이후 상대방알림시간</label></th>
								<td><input type="text" name="con_GpsYTime" id="con_GpsYTime" class="frm_input" size="15" maxlength="20" value="<?= $con_GpsYTime ?>"> 분</td>
							</tr>
							<tr>
								<th scope="row" colspan="2"><label for="con_BtnTime">바로양도 버튼클릭 제한시간(도착예상시간%)</label></th>
								<td><input type="text" name="con_BtnTime" id="con_BtnTime" class="frm_input" size="15" maxlength="20" value="<?= $con_BtnTime ?>"> %</td>
								<th scope="row"><label for="conDistance">나와의 매칭제한거리</label></th>
								<td><input type="text" name="conDistance" id="conDistance" class="frm_input" size="15" maxlength="20" value="<?= $con_Distance ?>"> m</td>
							</tr>
							<tr>
								<th scope="row" colspan="2"><label for="conGpsRegTime">GPS 위치기록시간</label></th>
								<td><input type="text" name="conGpsRegTime" id="conGpsRegTime" class="frm_input" size="15" maxlength="20" value="<?= $con_GpsRegTime ?>"> 분</td>
								<th scope="row"><label for="conMaxDc">매칭증가 한계 요금</label></th>
								<td><input type="text" name="conMaxDc" id="conMaxDc" class="frm_input" size="15" maxlength="20" value="<?= $con_MaxDc ?>"> %</td>
							</tr>
							<tr>
								<th scope="row" colspan="2"><label for="conSec">현황 주기 시간</label></th>
								<td><input type="text" name="conSec" id="conSec" class="frm_input" size="15" maxlength="20" value="<?= $con_Sec ?>"> 초</td>
								<th scope="row"><label for="con_GpsTime">GPS신호 재탐색 시간</label></th>
								<td><input type="text" name="con_GpsTime" id="con_GpsTime" class="frm_input" size="15" maxlength="20" value="<?= $con_GpsTime ?>"> 분</td>
							</tr>
							<tr>
								<th scope="row" colspan="2"><label for="conOrdFCnt">결제 실패 재시도 가능 횟수</label></th>
								<td><input type="text" name="conOrdFCnt" id="conOrdFCnt" class="frm_input" size="15" maxlength="20" value="<?= $con_OrdFCnt ?>"> 회</td>
								<th scope="row" style="width:250px;"><label for="conAddrMaxCnt">즐겨찾기 주소 최대 등록 수</label></th>
								<td><input type="text" name="conAddrMaxCnt" id="conAddrMaxCnt" class="frm_input" size="15" maxlength="20" value="<?= $con_AddrMaxCnt ?>"> 개</td>
							</tr>
							<tr>
								<th scope="row" colspan="2"><label for="conGuideUrl">가이드웹뷰경로</label></th>
								<td colspan="3"><input type="text" name="conGuideUrl" id="conGuideUrl" class="frm_input" size="100" value="<?= $con_GuideUrl ?>"></td>
							</tr>
							<tr>
								<th scope="row" colspan="2"><label for="conTodayOx">오늘의 OX 퀴즈</label></th>
								<td colspan="3"><input type="text" name="conTodayOx" id="conTodayOx" class="frm_input" size="10" onclick="window.open('selectOx.php','OX 퀴즈 선택','width=800,height=800,top=100,left=100');" value="<?= $con_TodayOx ?>"></td>
							</tr>
							<tr>
								<th scope="row" rowspan="4"><label for="conComp">완료변수설정</label></th>
								<th style="width:80px;"><span>조건 1</span></th>
								<td colspan="3">
									이동거리가 <input type="text" class="frm_input" id="conComp1_Max_D" name="conComp1_Max_D" size="15" value="<?= $conComp1_Max_D ?>" /> Km 미만 인 경우 <input type="text" class="frm_input" id="conComp1_H" name="conComp1_H" size="15" value="<?= $conComp1_H ?>" /> 시간
								</td>
							</tr>
							<tr>
								<th style="width:80px;"><span>조건 2</span></th>
								<td colspan="3">
									이동거리가 <input type="text" class="frm_input" id="conComp2_Min_D" name="conComp2_Min_D" size="15" value="<?= $conComp2_Min_D ?>" /> Km 이상 <input type="text" class="frm_input" id="conComp2_Max_D" name="conComp2_Max_D" size="15" value="<?= $conComp2_Max_D ?>" /> Km 미만 인 경우 <input type="text" class="frm_input" id="conComp2_H" name="conComp2_H" size="15" value="<?= $conComp2_H ?>" /> 시간
								</td>
							</tr>
							<tr>
								<th style="width:80px;"><span>조건 3</span></th>
								<td colspan="3">
									이동거리가 <input type="text" class="frm_input" id="conComp3_Min_D" name="conComp3_Min_D" size="15" value="<?= $conComp3_Min_D ?>" /> Km 이상 <input type="text" class="frm_input" id="conComp3_Max_D" name="conComp3_Max_D" size="15" value="<?= $conComp3_Max_D ?>" /> Km 미만 인 경우 <input type="text" class="frm_input" id="conComp3_H" name="conComp3_H" size="15" value="<?= $conComp3_H ?>" /> 시간
								</td>
							</tr>
							<tr>
								<th style="width:80px;"><span>조건 4</span></th>
								<td colspan="3">
									이동거리가 <input type="text" class="frm_input" id="conComp4_Min_D" name="conComp4_Min_D" size="15" value="<?= $conComp4_Min_D ?>" /> Km 이상 인 경우 <input type="text" class="frm_input" id="conComp4_H" name="conComp4_H" size="15" value="<?= $conComp4_H ?>" /> 시간
								</td>
							</tr>
							<tr>
								<th scope="row" rowspan="2"><label for="conRecom">추천인</label></th>
								<th style="width:100px;"><span>추천 가능 수</span></th>
								<td colspan="3">
									<input type="text" name="conChCnt" id="conChCnt" class="frm_input" size="15" maxlength="20" value="<?= $con_ChCnt ?>"> 회
								</td>
							</tr>
							<tr>
								<th style="width:100px;"><span>추천 적립율</span></th>
								<td colspan="3">
									<input type="text" name="conChPoint" id="conChPoint" class="frm_input" size="15" maxlength="20" value="<?= $con_ChPoint ?>"> %
								</td>
							</tr>
							<tr>
								<th scope="row" rowspan="3"><label for="conNewEvent">신규가입 이벤트</label></th>
								<th style="width:160px;"><span>진행여부</span></th>
								<td colspan="3">
									<input type="radio" name="conNewEventBit" value="1" id="con_NewEventBit1" <?= ($con_NewEventBit == "1") ? "checked" : ""; ?> required class="required" />
									<label for="con_NewEventBit1">진행</label>
									<input type="radio" name="conNewEventBit" value="0" id="con_NewEventBit0" <?= ($con_NewEventBit == "0") ? "checked" : ""; ?> required class="required" />
									<label for="con_NewEventBit0">진행안함</label>
								</td>
							</tr>
							<tr>
								<th style="width:160px;"><span>이벤트 종료일</span></th>
								<td colspan="3">
									<input type="date" class="frm_input" id="conNewEventDate" name="conNewEventDate" size="15" value="<?= $con_NewEventDate ?>" />
								</td>
							</tr>
							<tr>
								<th style="width:160px;"><span>지급할 포인트</span></th>
								<td colspan="3">
									<input type="text" class="frm_input" id="conNewEventPoint" name="conNewEventPoint" size="15" value="<?= $con_NewEventPoint ?>" />
								</td>
							</tr>
							<tr>
								<th scope="row" rowspan="4"><label for="conTaxiEvent">가치타기 택시 <BR> 인증 이벤트</label></th>
								<th style="width:160px;"><span>포인트 비율 (상시)</span></th>
								<td>
									<input type="text" class="frm_input" id="conTaxiRate" name="conTaxiRate" size="15" value="<?= $con_TaxiRate ?>" /> %
								</td>
								<th style="width:160px;"><span>적립 예정일 (상시)</span></th>
								<td>
									<input type="text" class="frm_input" id="conTaxiResDate" name="conTaxiResDate" size="10" value="<?= $con_TaxiResDate ?>" /> 일
								</td>
							</tr>
							<tr>
								<th style="width:160px;"><span>추가 적립 이벤트 <BR> 진행여부</span></th>
								<td colspan="3">
									<input type="radio" name="conTaxiEventBit" value="1" id="con_TaxiEventBitY" <?= ($con_TaxiEventBit == "Y") ? "checked" : ""; ?> required class="required" />
									<label for="con_TaxiEventBitY">진행</label>
									<input type="radio" name="conTaxiEventBit" value="0" id="con_TaxiEventBitN" <?= ($con_TaxiEventBit == "N") ? "checked" : ""; ?> required class="required" />
									<label for="con_TaxiEventBitN">진행안함</label>
								</td>
							</tr>
							<tr>
								<th style="width:160px;"><span>추가 적립 이벤트 <BR> 포인트 비율</span></th>
								<td colspan="3">
									<input type="text" class="frm_input" id="conTaxiEventRate" name="conTaxiEventRate" size="15" value="<?= $con_TaxiEventRate ?>" /> %
								</td>
							</tr>
							<tr>
								<th style="width:160px;"><span>추가 적립 이벤트 <BR> 시작일</span></th>
								<td>
									<input type="date" class="frm_input" id="conTaxiEventStartDate" name="conTaxiEventStartDate" size="10" value="<?= $con_TaxiEventStartDate ?>" />
								</td>
								<th style="width:160px;"><span>추가 적립 이벤트 <BR> 종료일</span></th>
								<td>
									<input type="date" class="frm_input" id="conTaxiEventEndDate" name="conTaxiEventEndDate" size="10" value="<?= $con_TaxiEventEndDate ?>" />
								</td>
							</tr>
							<tr>
								<th scope="row" rowspan="3"><label for="conPriceRate">메이커 요청요금 구간</label></th>
								<th style="width:160px;"><span>메이커 요청요금 <BR> 최소(%)</span></th>
								<td colspan="3">
									<input type="text" class="frm_input" id="conMinPriceRate" name="conMinPriceRate" size="15" value="<?= $con_MinPriceRate ?>" /> %
								</td>
							</tr>
							<tr>
								<th style="width:160px;"><span>메이커 요청요금 <BR> 최대(%)</span></th>
								<td colspan="3">
									<input type="text" class="frm_input" id="conMaxPriceRate" name="conMaxPriceRate" size="15" value="<?= $con_MaxPriceRate ?>" /> %
								</td>
							</tr>
							<tr>
								<th style="width:160px;"><span>메이커 요청요금 <BR> 간격(%)</span></th>
								<td colspan="3">
									<input type="text" class="frm_input" id="conIntervalPriceRate" name="conIntervalPriceRate" size="15" value="<?= $con_IntervalPriceRate ?>" /> %
								</td>
							</tr>
							<tr>
								<th scope="row" colspan="2"><label for="con_mTxt">취소 안내 문구</label></th>
								<td colspan="3"><textarea name="con_mTxt" id="con_mTxt"><?= $con_mTxt ?></textarea></td>
							</tr>
						</tbody>
					</table>
				</div>

				<div class="btn_fixed_top">
					<input type="submit" value="확인" class="btn_submit btn" accesskey='s'>
				</div>
			</form>


			<script>
				function f_submit(f) {
					return true;
				}
			</script>

		</div>

		<? include "../common/inc/inc_footer.php";  //푸터 
		?>