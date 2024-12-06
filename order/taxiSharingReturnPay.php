<?

/*======================================================================================================================

* 프로그램			: 카드 결제 처리 페이지
* 페이지 설명		: 카드 결제 처리 페이지
* 파일명                 : taxiSharingReturnPay.php
*
========================================================================================================================*/

include "../lib/common.php";
include "../lib/functionDB.php";  //공통 db함수
include "lib/TPAY.LIB.php";  //공통 db함수


$payMethod = "1";                           //결제수단 (1: card)
$mid = trim($mid);                          //상점아이디
$moid = trim($moid);                        //주문번호
$tid = trim($tid);                          //결제시 생성되는 거래 ID (결제 취소 테스트 사용)
$mallUserId = trim($mallUserId);         //주문자 아이디
$amt = trim($amt);                          //주문금액
$buyerName = trim($buyerName);              //구매자명
$buyerTel = trim($buyerTel);                //구매자연락처
$buyerEmail = trim($buyerEmail);            //구매자 이메일
$mallReserved = trim($mallReserved);        //상점예비정보
$goodsName = trim($goodsName);              //상품명
$authDate = trim($authDate);                //승인일자
$authCode = trim($authCode);                //승인번호
$fnCd = trim($fnCd);                        //결제사 코드
$fnName = trim($fnName);                    //결제사명
$resultCd = trim($resultCd);                //결과코드
$resultMsg = trim($resultMsg);              //결제메시지
$errorCd = trim($errorCd);                  //대외기관 에러코드
$errorMsg = trim($errorMsg);                //대외기관 에러메시지
$vbankNum = trim($vbankNum);                //가상계좌번호
$vbankExpDate = trim($vbankExpDate);        //가상계좌만료날짜
$ediDate = trim($ediDate);                  //암호화된 시간



//회원사 DB에 저장되어있던 값
$amtDb = ""; //금액
$moidDb = ""; //moid

$mKey = "NWedcIVvVwwATYHSpuZIdUw8KoM9oNcqaHs6xq3CP+JHo+Nu6uel0rhK6yLqiihwsIFkdabxmfsBHBWXn7Or7g==";  //상점키
// $mKey = "VXFVMIZGqUJx29I/k52vMM8XG4hizkNfiapAkHHFxq0RwFzPit55D3J3sAeFSrLuOnLNVCIsXXkcBfYK1wv8kQ==";	//상점 테스트키
$encryptor = new Encryptor($mKey, $ediDate);
$decAmt = $encryptor->decData($amt);
$decMoid = $encryptor->decData($moid);
$DB_con = db1();

//주문 정보 저장
$chkOrdQuery = "SELECT taxi_OrdNo, taxi_SIdx, taxi_RIdx, taxi_OSMemIdx, taxi_OrdSMemId, taxi_OMemIdx, taxi_OrdMemId, taxi_OrdPrice from TB_ORDER WHERE taxi_OrdNo = :taxi_OrdNo ";
$chkOrdStmt = $DB_con->prepare($chkOrdQuery);
$chkOrdStmt->bindParam(":taxi_OrdNo", $decMoid);
$chkOrdStmt->execute();
$chkOrdNum = $chkOrdStmt->rowCount();

if ($chkOrdNum < 1) {  //없을 경우
  // $result = array("result" => "error", "errorMsg" => "위변조 데이터를 오류입니다.");
} else {
  //결제결과 수신 여부 알림
  //ResultConfirm::send($tid, "000");

  while ($chkOrdRow = $chkOrdStmt->fetch(PDO::FETCH_ASSOC)) {
    $taxi_OrdNo = trim($chkOrdRow['taxi_OrdNo']);         // 주문번호
    $taxi_SIdx = trim($chkOrdRow['taxi_SIdx']);             // 생성자고유 idx
    $taxi_RIdx = trim($chkOrdRow['taxi_RIdx']);             // 요청자고유 idx
    $taxi_OSMemIdx = trim($chkOrdRow['taxi_OSMemIdx']);       // 생성자 고유 아이디
    $taxi_OrdSMemId = trim($chkOrdRow['taxi_OrdSMemId']);     // 생성자 아이디
    $taxi_OMemIdx = trim($chkOrdRow['taxi_OMemIdx']);         // 주문자 고유 아이디
    $taxi_OrdPrice = trim($chkOrdRow['taxi_OrdPrice']);       // 주문 금액

  }


  //주문정보 저장
  $reg_Date = DU_TIME_YMDHIS;       //등록일
  $taxi_OrdState = "1"; //결제완료

  //주문정보 업데이트
  $upOrdQuery = "UPDATE TB_ORDER SET taxi_OrdNickNm = :taxi_OrdNickNm, taxi_OrdTel = :taxi_OrdTel, taxi_OrdEmail = :taxi_OrdEmail, taxi_CancleTid = :taxi_CancleTid, taxi_OrdState = :taxi_OrdState, reg_Date = :reg_Date
         WHERE taxi_OrdNo = :taxi_OrdNo LIMIT 1";
  $upOrdStmt = $DB_con->prepare($upOrdQuery);
  $upOrdStmt->bindparam(":taxi_OrdNickNm", $buyerName);
  $upOrdStmt->bindparam(":taxi_OrdTel", $buyerTel);
  $upOrdStmt->bindparam(":taxi_OrdEmail", $buyerEmail);
  $upOrdStmt->bindparam(":taxi_CancleTid", $tid);
  $upOrdStmt->bindparam(":taxi_OrdState", $taxi_OrdState);
  $upOrdStmt->bindparam(":reg_Date", $reg_Date);
  $upOrdStmt->bindparam(":taxi_OrdNo", $taxi_OrdNo);
  $upOrdStmt->execute();

  //투게더 이동중 날짜 업데이트
  $upMQquery = "UPDATE TB_RTAXISHARING_INFO SET reg_EDate = :reg_EDate WHERE taxi_RIdx = :taxi_RIdx AND taxi_RMemId = :taxi_RMemId LIMIT 1";
  $upMStmt = $DB_con->prepare($upMQquery);
  $upMStmt->bindparam(":reg_EDate", $reg_Date);
  $upMStmt->bindparam(":taxi_RIdx", $taxi_RIdx);
  $upMStmt->bindparam(":taxi_RMemId", $taxi_OrdMemId);
  $upMStmt->execute();

  //투게더 이동중 상태로 변경
  $upPQquery = "UPDATE TB_RTAXISHARING SET taxi_RState = '6' WHERE idx = :idx AND taxi_RMemId = :taxi_RMemId LIMIT 1";
  $upPStmt = $DB_con->prepare($upPQquery);
  $upPStmt->bindparam(":idx", $taxi_RIdx);
  $upPStmt->bindparam(":taxi_RMemId", $taxi_OrdMemId);
  $upPStmt->execute();

  //메이커 이동중 상태로 변경
  $upSQquery = "UPDATE TB_STAXISHARING SET taxi_State = '6' WHERE idx = :idx AND taxi_MemId = :taxi_MemId LIMIT 1";
  $upSStmt = $DB_con->prepare($upSQquery);
  $upSStmt->bindparam(":idx", $taxi_SIdx);
  $upSStmt->bindparam(":taxi_MemId", $taxi_OrdSMemId);
  $upSStmt->execute();

?>


  <!DOCTYPE html>
  <html>

  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0, user-scalable=no" />
    <meta name="apple-mobile-web-app-capable" content="yes" />
    <meta name="apple-mobile-web-app-status-bar-style" content="black" />
    <link rel="apple-touch-icon" href="" />
    <link rel="apple-touch-startup-image" href="" />
    <link rel="stylesheet" href="css/orderPay.css">
    <script type="text/javascript" src="https://code.jquery.com/jquery-1.12.4.min.js"></script>
    <script type="text/javascript" src="https://service.iamport.kr/js/iamport.payment-1.1.5.js"></script>
    <script type="text/javascript">
      function submitForm() {

        if (/Android/i.test(navigator.userAgent)) {
          // 안드로이드
          Android.payDone();
        } else if (/iPhone|iPad|iPod/i.test(navigator.userAgent)) {
          callIPhone();
        } else {
          // 그 외 디바이스
          Android.payDone();
        }


      }

      function callIPhone() {
        try {
          window.webkit.messageHandlers.callIPhoneNative.postMessage("done");
        } catch (err) {
          alert(err);
        }
      }
    </script>

    <title>가치타 확인</title>
  </head>

  <body>
    <form id="transMgr" name="transMgr" method="post">

      <h3 class="TitleBar">가치타 결제 완료 확인</h3>
      <div class="selectList">
        <ul>
          <li class="selectBar">
            <p><?= number_format($taxi_OrdPrice) ?> 원 결제가 완료되었습니다.</p>
          </li>
        </ul>
        <ul>
          <li class="selectBar">
            <input type="button" id="submitBtn" value="확인" onclick="submitForm();">
          </li>
        </ul>
      </div>

    </form>
  </body>

  </html>


<?


}


dbClose($DB_con);
$chkOrdStmt = null;
$upOrdStmt = null;
$upMStmt = null;
$upPStmt = null;
$upSStmt = null;

//echo str_replace('\\/', '/', json_encode($result, JSON_UNESCAPED_UNICODE));
?>