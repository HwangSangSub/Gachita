<?
/*======================================================================================================================

* 프로그램			: DB 내용 불러올 함수
* 페이지 설명		: DB 내용 불러올 함수

========================================================================================================================*/


/*회원 등급 값 가져오기 */
function memLvInfo($chkNum)
{

    $fDB_con = db1();

    //회원 등급 기준 조회
    $mpQuery = "";
    $mpQuery = "SELECT memLv, memMatCnt FROM TB_MEMBER_LEVEL WHERE memLv <> '1' ORDER BY memLv ASC ";
    $mpStmt = $fDB_con->prepare($mpQuery);
    $mpStmt->bindparam(":memMatCnt", $totMemNum);
    $mpStmt->execute();
    $mpNum = $mpStmt->rowCount();

    if ($mpNum < 1) { //아닐경우
    } else {
        while ($mpRow = $mpStmt->fetch(PDO::FETCH_ASSOC)) {
            $memLv = trim($mpRow['memLv']);             // 포인트
            $memMatCnt = trim($mpRow['memMatCnt']);             // 포인트

            if ($chkNum >= "500") {
                $memLv = "7";
            } else if ($chkNum >= "400") {
                $memLv = "8";
            } else if ($chkNum >= "300") {
                $memLv = "9";
            } else if ($chkNum >= "200") {
                $memLv = "10";
            } else if ($chkNum >= "100") {
                $memLv = "11";
            } else if ($chkNum >= "50") {
                $memLv = "12";
            } else if ($chkNum >= "10") {
                $memLv = "13";
            } else {
                $memLv = "14";
            }
        }

        return $memLv;
    }

    dbClose($fDB_con);
    $mpStmt = null;
}

/*회원 주 아이디 가져오기 */
function memIdxInfo($mem_Id)
{

    $fDB_con = db1();

    $memTQuery = "SELECT idx FROM TB_MEMBERS WHERE mem_Id = :mem_Id AND b_Disply = 'N' LIMIT 1";
    $memTStmt = $fDB_con->prepare($memTQuery);
    $memTStmt->bindparam(":mem_Id", $mem_Id);
    $memTStmt->execute();
    $memTNum = $memTStmt->rowCount();

    if ($memTNum < 1) { //주 ID가 없을 경우 회원가입 시작
    } else {  //등록된 회원이 있을 경우
        while ($memTRow = $memTStmt->fetch(PDO::FETCH_ASSOC)) {
            $mem_Idx = $memTRow['idx'];           //체크 랜덤아이디
        }
        return $mem_Idx;
    }

    dbClose($fDB_con);
    $memTStmt = null;
}

/*회원 주 아이디 가져오기 */
function memIdInfo($mem_Idx)
{

    $fDB_con = db1();

    $memTQuery = "SELECT mem_Id FROM TB_MEMBERS WHERE idx = :idx AND b_Disply = 'N' LIMIT 1";
    $memTStmt = $fDB_con->prepare($memTQuery);
    $memTStmt->bindparam(":idx", $mem_Idx);
    $memTStmt->execute();
    $memTNum = $memTStmt->rowCount();

    if ($memTNum < 1) { //주 ID가 없을 경우 회원가입 시작
    } else {  //등록된 회원이 있을 경우
        while ($memTRow = $memTStmt->fetch(PDO::FETCH_ASSOC)) {
            $mem_Id = $memTRow['mem_Id'];           //체크 랜덤아이디
        }
        return $mem_Id;
    }

    dbClose($fDB_con);
    $memTStmt = null;
}

/*회원 주 아이디 가져오기 */
function memIdxInfoToken($tokens)
{

    $fDB_con = db1();

    $memTQuery = "SELECT idx FROM TB_MEMBERS WHERE mem_Token = :mem_Token AND b_Disply = 'N' LIMIT 1";
    $memTStmt = $fDB_con->prepare($memTQuery);
    $memTStmt->bindparam(":mem_Token", $tokens);
    $memTStmt->execute();
    $memTNum = $memTStmt->rowCount();

    if ($memTNum < 1) { //주 ID가 없을 경우 회원가입 시작
    } else {  //등록된 회원이 있을 경우
        while ($memTRow = $memTStmt->fetch(PDO::FETCH_ASSOC)) {
            $mem_Idx = $memTRow['idx'];           //체크 랜덤아이디
        }
        return $mem_Idx;
    }

    dbClose($fDB_con);
    $memTStmt = null;
}

/*회원 닉네임 가져오기 */
function memNickInfo($mem_Id)
{

    $fDB_con = db1();

    $memNmQuery = "SELECT mem_NickNm FROM TB_MEMBERS WHERE mem_Id = :mem_Id AND b_Disply = 'N' LIMIT 1";
    $memNmStmt = $fDB_con->prepare($memNmQuery);
    $memNmStmt->bindparam(":mem_Id", $mem_Id);
    $memNmStmt->execute();
    $memNmNum = $memNmStmt->rowCount();

    if ($memNmNum < 1) {
    } else {  //등록된 회원이 있을 경우
        while ($memNmRow = $memNmStmt->fetch(PDO::FETCH_ASSOC)) {
            $mem_NickNm = $memNmRow['mem_NickNm'];           //체크 랜덤아이디
        }
        return $mem_NickNm;
    }

    dbClose($fDB_con);
    $memNmStmt = null;
}

/*회원 닉네임 가져오기 */
function memIdxNickInfo($mem_Idx)
{

    $fDB_con = db1();

    $memNmQuery = "SELECT mem_NickNm FROM TB_MEMBERS WHERE idx = :mem_Idx AND b_Disply = 'N' LIMIT 1";
    $memNmStmt = $fDB_con->prepare($memNmQuery);
    $memNmStmt->bindparam(":mem_Idx", $mem_Idx);
    $memNmStmt->execute();
    $memNmNum = $memNmStmt->rowCount();

    if ($memNmNum < 1) {
    } else {  //등록된 회원이 있을 경우
        while ($memNmRow = $memNmStmt->fetch(PDO::FETCH_ASSOC)) {
            $mem_NickNm = $memNmRow['mem_NickNm'];           //체크 랜덤아이디
        }
        return $mem_NickNm;
    }

    dbClose($fDB_con);
    $memNmStmt = null;
}

/*회원 디바이스 아이디 가져오기 */
function memDeviceIdInfo($mem_Id)
{

    $fDB_con = db1();

    $memDeQuery = "SELECT mem_DeviceId FROM TB_MEMBERS WHERE mem_Id = :mem_Id AND b_Disply = 'N' LIMIT 1";
    $memDeStmt = $fDB_con->prepare($memDeQuery);
    $memDeStmt->bindparam(":mem_Id", $mem_Id);
    $memDeStmt->execute();
    $memDeNum = $memDeStmt->rowCount();

    if ($memDeNum < 1) { //없을 경우
    } else {  //등록된 회원이 있을 경우
        while ($memDeRow = $memDeStmt->fetch(PDO::FETCH_ASSOC)) {
            $memDeviceId = $memDeRow['mem_DeviceId'];           //체크 랜덤아이디
        }
        return $memDeviceId;
    }

    dbClose($fDB_con);
    $memDeStmt = null;
}

/* 매칭 회원 토큰 값 가져오기 */
function memMatchTokenInfo($mem_Idx)
{

    $fDB_con = db1();

    $memTokQuery = "SELECT mem_Token FROM TB_MEMBERS WHERE idx = :mem_Idx AND b_Disply = 'N'";
    $memTokStmt = $fDB_con->prepare($memTokQuery);
    $memTokStmt->bindparam(":mem_Idx", $mem_Idx);
    $memTokStmt->execute();
    $memTokNum = $memTokStmt->rowCount();

    $tokens = array();
    if ($memTokNum < 1) { //주 ID가 없을 경우 회원가입 시작
    } else {  //등록된 회원이 있을 경우
        while ($memTokRow = $memTokStmt->fetch(PDO::FETCH_ASSOC)) {
            $tokens[] = $memTokRow["mem_Token"]; //토큰값
        }
        return $tokens;
    }


    dbClose($fDB_con);
    $memTokStmt = null;
}



/* 이벤트 공지 회원 토큰 값 가져오기 */
function memNoticeTokenInfo($mem_Idx)
{

    $fDB_con = db1();

    $memTokQuery = "SELECT mem_Token FROM TB_MEMBERS WHERE idx = :mem_Idx AND mem_NPush = '0' AND b_Disply = 'N'";
    $memTokStmt = $fDB_con->prepare($memTokQuery);
    $memTokStmt->bindparam(":mem_Idx", $mem_Idx);
    $memTokStmt->execute();
    $memTokNum = $memTokStmt->rowCount();

    $tokens = array();
    if ($memTokNum < 1) { //주 ID가 없을 경우 회원가입 시작
    } else {  //등록된 회원이 있을 경우
        while ($memTokRow = $memTokStmt->fetch(PDO::FETCH_ASSOC)) {
            $tokens[] = $memTokRow["mem_Token"]; //토큰값
        }
        return $tokens;
    }


    dbClose($fDB_con);
    $memTokStmt = null;
}

/* 푸시내역 저장 */
function pushHistoryReg($tokens, $data)
{
    $fDB_con = db1();

    $mem_Idx = memIdxInfoToken($tokens);   //회원 주아이디
    if($data["title"] != ""){
        $title = $data["title"];
    }else{
        $title = "🚐버스보다 빠르고 🚕택시보다 저렴하게";
    }
    $msg = ($data["msg"] == "" ? "" : $data["msg"]);
    $addmsg = ($data["addmsg"] == "" ? "" : $data["addmsg"]);
    $state = ($data["state"] == "0" ? "" : $data["state"]);
    $lat = ($data["lat"] == "" ? NULL : $data["lat"]);
    $lng = ($data["lng"] == "" ? NULL : $data["lng"]);
    $image = ($data["imageUrl"] == "" ? "" : $data["imageUrl"]);
    $notice = ($data["id"] == "" ? NULL : $data["id"]);
    $sharingIdx = ($data["sharingIdx"] == "" ? NULL : $data["sharingIdx"]);

    //푸시 사용 내역 (2: 새로고침, 9 :로그아웃, 997 : 채팅)
    $insPsQuery = "INSERT INTO TB_PUSH_HISTORY (mem_Idx, push_Title, push_Msg, push_AddMsg, push_Img, push_NoticeIdx, push_SharingIdx, push_State, push_Lat, push_Lng, reg_Date) VALUES (:mem_Idx, :push_Title, :push_Msg, :push_AddMsg, :push_Img, :push_NoticeIdx, :push_SharingIdx, :push_State, :push_Lat, :push_Lng, NOW())";
    // echo $insPsQuery;
    $insPsStmt = $fDB_con->prepare($insPsQuery);
    $insPsStmt->bindparam(":mem_Idx", $mem_Idx);
    $insPsStmt->bindparam(":push_Title", $title);
    $insPsStmt->bindparam(":push_Msg", $msg);
    $insPsStmt->bindparam(":push_AddMsg", $addmsg);
    $insPsStmt->bindparam(":push_Img", $image);
    $insPsStmt->bindparam(":push_NoticeIdx", $notice);
    $insPsStmt->bindparam(":push_SharingIdx", $sharingIdx);
    $insPsStmt->bindparam(":push_State", $state);
    $insPsStmt->bindparam(":push_Lat", $lat);
    $insPsStmt->bindparam(":push_Lng", $lng);
    $insPsStmt->execute();
    dbClose($fDB_con);
    $insPsStmt = null;
}

//새로운 푸시 메시지 전송(최종) 2023-06-05 이후 이것만 사용함.
function send_Push($tokens, $data)
{
    pushHistoryReg($tokens, $data);
    if($data["title"] != ""){
        $title = $data["title"];
    }else{
        $title = "🚐버스보다 빠르고 🚕택시보다 저렴하게";
    }
    $pushUrl = "https://fcm.googleapis.com/fcm/send";
    $headers = [];
    $headers[] = 'Content-Type: application/json';
    $headers[] = 'Authorization:key=' . GOOGLE_API_KEY;
    $imageUrl = $data["imageUrl"];
    //푸시데이터에서 위경도값이 있으면 같이 보내기.
    if ($data['lat'] != "" && $data['lng'] != "") {
        $notification = [
            'title' => $title,
            'body' => $data["body"],
            "msg" => $data["addmsg"],
            "state" => $data["state"],
            "lat" => $data["lat"],
            "lng" => $data["lng"],
            "image" => $imageUrl
        ];
    } else if ($data['type'] != "" && $data['id'] != "") {
        $notification = [
            'title' => $title,
            'body' => $data["body"],
            "msg" => $data["addmsg"],
            "state" => $data["state"],
            "type" => $data["type"],
            "id" => $data["id"],
            "image" => $imageUrl
        ];
    } else if ($data["imageUrl"] != "") {
        $notification = [
            'title' => $title,
            'body' => $data["body"],
            "state" => $data["state"],
            "image" => $imageUrl
        ];
    } else {
        $notification = [
            'title' => $title,
            'body' => $data["msg"],
            "state" => $data["state"]
        ];
    }
    $extraNotificationData = ["message" => $notification];
    $data = array(
        "data" => $notification,
        "notification" => $notification,
        "to"  => $tokens //token get on my ipad with the getToken method of cordova plugin,
    );
    $json_data =  json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
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

    return $result;
}

//취소 신청자 회원정보
function memMatCInfo($mem_Id)
{

    $mem_Idx = memIdxInfo($mem_Id);   //회원 주아이디
    $fDB_con = db1();

    $mnSql = "  , ( SELECT mem_NickNm FROM TB_MEMBERS WHERE TB_MEMBERS.idx = TB_MEMBERS_ETC.mem_Idx AND TB_MEMBERS.mem_Id = TB_MEMBERS_ETC.mem_Id AND TB_MEMBERS.b_Disply = 'N' limit 1 ) AS memNickNm  ";
    $memQuery = "";
    $memQuery = "SELECT mem_McCnt {$mnSql} FROM TB_MEMBERS_ETC WHERE mem_Idx = :mem_Idx AND mem_Id = :mem_Id  LIMIT 1 ";
    //echo $memQuery."<BR>";
    //exit;
    $memStmt = $fDB_con->prepare($memQuery);
    $memStmt->bindparam(":mem_Idx", $mem_Idx);
    $memStmt->bindparam(":mem_Id", $mem_Id);
    $memStmt->execute();
    $memNum = $memStmt->rowCount();

    if ($memNum < 1) { //아닐경우
    } else {

        while ($memRow = $memStmt->fetch(PDO::FETCH_ASSOC)) {
            $memNickNm = trim($memRow['memNickNm']);        // 취소신청자 닉네임
            $$memMcCnt = trim($memRow['mem_McCnt']);          // 회원 매칭 취소 횟수

            if ($memNickNm == "") {
                $memNickNm = "탈퇴회원";
            } else {
                $memNickNm = $memNickNm;
            }

            if ($memMcCnt == "") {
                $memMcCnt = "0";
            } else {
                $memMcCnt =  $memMcCnt;
            }

            $dinfo['memNickNm'] = $memNickNm;        // 취소신청자 닉네임
            $dinfo['memMcCnt'] = $memMcCnt;          // 회원 매칭 취소 횟수

        }

        return $dinfo;
    }

    dbClose($fDB_con);
    $memStmt = null;
}

/* 성별값 가져오기 */
function memSexInfo($mem_Id)
{

    $mem_Idx = memIdxInfo($mem_Id);   //회원 주아이디
    $fDB_con = db1();

    $mSexQuery = "";
    $mSexQuery = "SELECT mem_Sex FROM TB_MEMBERS_INFO WHERE mem_Idx = :mem_Idx LIMIT 1 ";
    $mSexStmt = $fDB_con->prepare($mSexQuery);
    $mSexStmt->bindparam(":mem_Idx", $mem_Idx);
    $mSexStmt->execute();
    $mSexNum = $mSexStmt->rowCount();

    if ($mSexNum < 1) { //아닐경우
    } else {
        while ($mSexRow = $mSexStmt->fetch(PDO::FETCH_ASSOC)) {
            $mem_Sex =  trim($mSexRow['mem_Sex']);    // 패널티 제목
        }

        return $mem_Sex;
    }

    dbClose($fDB_con);
    $mSexStmt = null;
}

function number2hangul($number)
{

    $num = array('0', '1', '2', '3', '4', '5', '6', '7', '8', '9');
    $unit4 = array('', '만', '억', '조', '경');
    $unit1 = array('', '십', '백', '천');

    $res = array();

    $number = str_replace(',', '', $number);
    $split4 = str_split(strrev((string)$number), 4);

    for ($i = 0; $i < count($split4); $i++) {
        $temp = array();
        $split1 = str_split((string)$split4[$i], 1);
        for ($j = 0; $j < count($split1); $j++) {
            $u = (int)$split1[$j];
            if ($u > 0) $temp[] = $num[$u] . $unit1[$j];
        }
        if (count($temp) > 0) $res[] = implode('', array_reverse($temp)) . $unit4[$i];
    }
    return implode('', array_reverse($res));
}

/* 회원고유번호로 회원 이미지 조회하기. */
function getMemberImg($mem_Idx)
{
    $fDB_con = db1();

    $memChkQuery = "SELECT A.mem_CharBit, A.mem_CharIdx, B.mem_profile_update FROM TB_MEMBERS AS A LEFT OUTER JOIN TB_MEMBER_PHOTO AS B ON A.idx = B.mem_Idx WHERE A.idx = :taxi_MemIdx";
    $memChkStmt = $fDB_con->prepare($memChkQuery);
    $memChkStmt->bindparam(":taxi_MemIdx", $mem_Idx);
    $memChkStmt->execute();
    $memChkNum = $memChkStmt->rowCount();
    if ($memChkNum < 1) { //아닐경우
    } else {
        while ($memChkRow = $memChkStmt->fetch(PDO::FETCH_ASSOC)) {

            $mem_CharBit = $memChkRow['mem_CharBit'];            // 캐릭터프로필 선택 여부( 0: 미선택, 1: 선택)
            $mem_CharIdx = $memChkRow['mem_CharIdx'];            // 캐릭터프로필 고유번호
            $mem_profile_update = trim($memChkRow['mem_profile_update']);

            if ($mem_CharIdx == "") {
                $memCharIdx = "";
            } else {
                $memCharIdx = $mem_CharIdx;
            }
            if ($mem_CharBit == "1") {
                $profileQuery = "SELECT con_ProfileNo, con_ProfileImg FROM TB_CONFIG_PROFILE WHERE con_ProfileBit = 'Y' AND con_ProfileNo = :memCharIdx ORDER BY con_ProfileSort ASC";
                $profileStmt = $fDB_con->prepare($profileQuery);
                $profileStmt->bindparam(":memCharIdx", $memCharIdx);
                $profileStmt->execute();
                $profileRow = $profileStmt->fetch(PDO::FETCH_ASSOC);
                $profile_Img = $profileRow['con_ProfileImg'];

                $imgUrl = "/data/config/profile/";
                $profileImg = $imgUrl . $profile_Img;

                $memImgFile = $profileImg;
            } else {
                if ($mem_profile_update == '') {
                    $memImgFile = '';
                } else {
                    $memImgFile = '/data/member/photo.php?id=' . $mem_profile_update;
                }
            }
        }

        return $memImgFile;
    }
}

//회원등급조회
function memLvGet($mem_Idx)
{

    $fDB_con = db1();

    $memTQuery = "SELECT mem_Lv FROM TB_MEMBERS WHERE idx = :idx AND b_Disply = 'N' LIMIT 1";
    $memTStmt = $fDB_con->prepare($memTQuery);
    $memTStmt->bindparam(":idx", $mem_Idx);
    $memTStmt->execute();
    $memTNum = $memTStmt->rowCount();

    if ($memTNum < 1) { //주 ID가 없을 경우 회원가입 시작
    } else {  //등록된 회원이 있을 경우
        while ($memTRow = $memTStmt->fetch(PDO::FETCH_ASSOC)) {
            $mem_Lv = $memTRow['mem_Lv'];           //체크 랜덤아이디
        }
        return $mem_Lv;
    }

    dbClose($fDB_con);
    $memTStmt = null;
}

//회원포인트조회
function memPointGet($mem_Idx)
{

    $fDB_con = db1();

    $memTQuery = "SELECT mem_Point FROM TB_MEMBERS_ETC WHERE mem_Idx = :idx LIMIT 1";
    $memTStmt = $fDB_con->prepare($memTQuery);
    $memTStmt->bindparam(":idx", $mem_Idx);
    $memTStmt->execute();
    $memTNum = $memTStmt->rowCount();

    if ($memTNum < 1) { //주 ID가 없을 경우 회원가입 시작
    } else {  //등록된 회원이 있을 경우
        while ($memTRow = $memTStmt->fetch(PDO::FETCH_ASSOC)) {
            $mem_Point = $memTRow['mem_Point'];           //체크 랜덤아이디
        }
        return $mem_Point;
    }

    dbClose($fDB_con);
    $memTStmt = null;
}

//등급별 수수료차감율
function memLvDcGet($mem_Lv)
{

    $fDB_con = db1();

    $memTQuery = "SELECT memDc FROM TB_MEMBER_LEVEL WHERE memLv = :mem_Lv LIMIT 1";
    $memTStmt = $fDB_con->prepare($memTQuery);
    $memTStmt->bindparam(":mem_Lv", $mem_Lv);
    $memTStmt->execute();
    $memTNum = $memTStmt->rowCount();

    if ($memTNum < 1) { //주 ID가 없을 경우 회원가입 시작
    } else {  //등록된 회원이 있을 경우
        while ($memTRow = $memTStmt->fetch(PDO::FETCH_ASSOC)) {
            $memDc = $memTRow['memDc'];           //체크 랜덤아이디
        }
        return $memDc;
    }

    dbClose($fDB_con);
    $memTStmt = null;
}

//노선생성내역중 (1: 매칭중, 2: 매칭요청, 3: 예약요청, 4: 예약요청완료, 5: 만남중, 6: 이동중, 9:취소완료처리, 10:거래완료처리) 건 이 있다면 생성불가처리.
function makerState($taxi_MemId)
{
    $memIdx = memIdxInfo($taxi_MemId);
    $fDB_con = db1();

    $chkCntNum = 0;
    //매칭 생성 테이블 조건값 체크
    $chkCntQuery = "SELECT count(idx)  AS num from TB_STAXISHARING WHERE taxi_MemIdx = :taxi_MemIdx AND taxi_MemId = :taxi_MemId AND taxi_State IN ('1', '2', '3', '4', '5', '6', '9', '10') ";
    $chkCntStmt = $fDB_con->prepare($chkCntQuery);
    $chkCntStmt->bindParam("taxi_MemIdx", $memIdx);
    $chkCntStmt->bindparam("taxi_MemId", $taxi_MemId);
    $chkCntStmt->execute();
    $chkCntRow = $chkCntStmt->fetch(PDO::FETCH_ASSOC);
    $chkCntNum = $chkCntRow['num'];

    if ($chkCntNum > 0) {
        //현재 상태값 가져오기
        $chkQuery = "SELECT taxi_State from TB_STAXISHARING WHERE taxi_MemIdx = :taxi_MemIdx AND taxi_MemId = :taxi_MemId AND taxi_State IN  ('1', '2', '3', '4', '5', '6', '9', '10') ";
        $chkStmt = $fDB_con->prepare($chkQuery);
        $chkStmt->bindParam("taxi_MemIdx", $memIdx);
        $chkStmt->bindparam("taxi_MemId", $taxi_MemId);
        $chkStmt->execute();
        $chkNum = $chkStmt->rowCount();

        if ($chkNum < 1) { //아닐경우
        } else {
            while ($chkRow = $chkStmt->fetch(PDO::FETCH_ASSOC)) {
                $taxiState = $chkRow['taxi_State'];
            }
        }
        return $taxiState;
    } else {
        return $chkCntNum;
    }


    dbClose($fDB_con);
    $chkCntStmt = null;
}


//노선생성내역중 (1: 매칭중, 2: 매칭요청, 3: 예약요청, 4: 예약요청완료, 5: 만남중, 6: 이동중, 9:취소완료처리, 10:거래완료처리) 건 이 있다면 생성불가처리.
function togetherState($taxi_MemId)
{
    $memIdx = memIdxInfo($taxi_MemId);
    $fDB_con = db1();

    $chkCntNum = 0;
    //매칭 생성 테이블 조건값 체크
    $chkCntQuery = "SELECT count(idx) AS num FROM TB_RTAXISHARING WHERE taxi_RMemIdx = :taxi_RMemIdx AND taxi_RMemId = :taxi_RMemId AND taxi_RState IN ('1', '2', '4', '5', '6', '9', '10') ";
    $chkCntStmt = $fDB_con->prepare($chkCntQuery);
    $chkCntStmt->bindParam("taxi_RMemIdx", $memIdx);
    $chkCntStmt->bindparam("taxi_RMemId", $taxi_MemId);
    $chkCntStmt->execute();
    $chkCntRow = $chkCntStmt->fetch(PDO::FETCH_ASSOC);
    $chkCntNum = $chkCntRow['num'];

    if ($chkCntNum > 0) {
        //현재 상태값 가져오기
        $chkQuery = "SELECT taxi_RState FROM TB_RTAXISHARING WHERE taxi_RMemIdx = :taxi_RMemIdx AND taxi_RMemId = :taxi_RMemId AND taxi_RState IN ('1', '2', '4', '5', '6', '9', '10') ";
        $chkStmt = $fDB_con->prepare($chkQuery);
        $chkStmt->bindParam("taxi_RMemIdx", $memIdx);
        $chkStmt->bindparam("taxi_RMemId", $taxi_MemId);
        $chkStmt->execute();
        $chkNum = $chkStmt->rowCount();

        if ($chkNum < 1) { //아닐경우
        } else {
            while ($chkRow = $chkStmt->fetch(PDO::FETCH_ASSOC)) {
                $taxiState = $chkRow['taxi_RState'];
            }
        }
        return $taxiState;
    } else {
        return $chkCntNum;
    }


    dbClose($fDB_con);
    $chkCntStmt = null;
}

// 회원의 매칭 수량 확인
function compSharingCnt($mem_Idx)
{
    $fDB_con = db1();

    $makerNum = 0;
    $togetherNum = 0;

    //메이커 완료내역
    $makerQuery = "SELECT COUNT(idx) AS num FROM TB_STAXISHARING WHERE taxi_MemIdx = :taxi_MemIdx AND taxi_State = 7";
    $makerStmt = $fDB_con->prepare($makerQuery);
    $makerStmt->bindParam("taxi_MemIdx", $mem_Idx);
    $makerStmt->execute();
    $makerRow = $makerStmt->fetch(PDO::FETCH_ASSOC);
    $makerNum = $makerRow['num'];

    //투게더 완료내역
    $togetherQuery = "SELECT COUNT(idx) AS num FROM TB_RTAXISHARING WHERE taxi_RMemIdx = :taxi_RMemIdx AND taxi_RState = 7";
    $togetherStmt = $fDB_con->prepare($togetherQuery);
    $togetherStmt->bindParam("taxi_RMemIdx", $mem_Idx);
    $togetherStmt->execute();
    $togetherRow = $togetherStmt->fetch(PDO::FETCH_ASSOC);
    $togetherNum = $togetherRow['num'];

    $totalNum = (int)$makerNum + (int)$togetherNum;

    if ((int)$totalNum == 1) {
        return true;
    } else {
        return false;
    }

    dbClose($fDB_con);
    $makerStmt = null;
    $togetherStmt = null;
}

/**
 * 메이커 이동중 확인하기.
 *
 * @param [int] $idx
 * @return boolean
 */
function makerMoveChk($idx)
{
    $fDB_con = db1();

    $sharingChkQuery = "SELECT taxi_State FROM TB_STAXISHARING WHERE idx = :idx";
    $sharingChkStmt = $fDB_con->prepare($sharingChkQuery);
    $sharingChkStmt->bindParam("idx", $idx);
    $sharingChkStmt->execute();
    $sharingChkNum = $sharingChkStmt->rowCount();

    if ($sharingChkNum < 1) { //아닐경우
        return false;
    } else {
        while ($sharingChkRow = $sharingChkStmt->fetch(PDO::FETCH_ASSOC)) {
            $taxi_State = $sharingChkRow['taxi_State'];
            if ($taxi_State == '5') {
                return true;
            } else {
                return false;
            }
        }
    }
    dbClose($fDB_con);
    $sharingChkStmt = null;
}

/**
 * 투게더 이동중 확인하기.
 *
 * @param [int] $idx
 * @return boolean
 */
function togetherMoveChk($idx)
{
    $fDB_con = db1();

    $sharingChkQuery = "SELECT taxi_RState FROM TB_RTAXISHARING WHERE taxi_SIdx = :idx";
    $sharingChkStmt = $fDB_con->prepare($sharingChkQuery);
    $sharingChkStmt->bindParam("idx", $idx);
    $sharingChkStmt->execute();
    $sharingChkNum = $sharingChkStmt->rowCount();

    if ($sharingChkNum < 1) { //아닐경우
        return false;
    } else {
        while ($sharingChkRow = $sharingChkStmt->fetch(PDO::FETCH_ASSOC)) {
            $taxi_RState = $sharingChkRow['taxi_RState'];
            if ($taxi_RState == '5') {
                return true;
            } else {
                return false;
            }
        }
    }
    dbClose($fDB_con);
    $sharingChkStmt = null;
}

/**
 * 미션정보확인하기.
 *
 * @param [int] $mission_Idx
 * @return array
 */
function missionInfoChk($mission_Idx)
{
    $fDB_con = db1();

    //미션정보 확인하기
    $missionInfoQuery = "SELECT m_Name, m_Type, m_SPoint, m_FPoint, m_GiveType, m_DCnt, m_SCnt, m_ResType, reg_Date, end_Date, m_Link  FROM TB_MISSION WHERE idx = :mission_Idx";
    $missionInfoStmt = $fDB_con->prepare($missionInfoQuery);
    $missionInfoStmt->bindParam("mission_Idx", $mission_Idx);
    $missionInfoStmt->execute();
    $missionInfoNum = $missionInfoStmt->rowCount();
    if ($missionInfoNum > 0) {
        $missionInfoRow = $missionInfoStmt->fetch(PDO::FETCH_ASSOC);
        $m_Name = $missionInfoRow['m_Name'];                    // 미션제목
        $m_Type = $missionInfoRow['m_Type'];                    // 미션타입(1:친해지기미션, 2: 매일미션, 3: 한달미션)
        $m_SPoint = $missionInfoRow['m_SPoint'];                // 미션보상포인트(성공, 정답)
        $m_FPoint = $missionInfoRow['m_FPoint'];                // 미션보상포인트(오답)
        $m_GiveType = $missionInfoRow['m_GiveType'];            // 미션보상지급방법(0: 즉시,  1: 받기클릭, 2: 적립예정)
        $m_DCnt = $missionInfoRow['m_DCnt'];                    // 하루최대가능 수
        $m_SCnt = $missionInfoRow['m_SCnt'];                    // 미션수행횟수
        $m_ResType = $missionInfoRow['m_ResType'];              // 예정일방식(0: 즉시, 1: 3일, 2: 7일, 3: 1달)
        $reg_Date = $missionInfoRow['reg_Date'];                // 미션등록일
        $end_Date = $missionInfoRow['end_Date'];                // 미션종료일
        $m_Link = $missionInfoRow['m_Link'];                    // 링크페이지
        $result = array("mName" => (string)$m_Name, "mType" => (string)$m_Type, "mGiveType" => (string)$m_GiveType, "mSPoint" => (int)$m_SPoint, "mFPoint" => (int)$m_FPoint, "mDCnt" => (int)$m_DCnt, "mSCnt" => (int)$m_SCnt, "mResType" => (string)$m_ResType, "regDate" => (string)$reg_Date, "endDate" => (string)$end_Date, "mLink" => (string)$m_Link);
    } else {
        $result = '';
    }

    return $result;

    dbClose($fDB_con);
    $missionInfoStmt = null;
}

/**
 * 미션 내역 확인 하기
 *
 * @param [int] $mission_Idx
 * @param [int] $mem_Idx
 * @return boolean
 */
function missionHistoryChk($mission_Idx, $mem_Idx)
{
    $fDB_con = db1();
    //미션기록 초기화
    $mCnt = 0;
    //미션정보 확인하기.
    $mission = missionInfoChk($mission_Idx);

    if ($mission != "") {
        $mType = $mission['mType'];                 // 미션타입(1:친해지기미션, 2: 매일미션, 3: 한달미션)
        $mGiveType = $mission['mGiveType'];         // 미션보상지급방법(0: 즉시,  1: 받기클릭, 2: 적립예정)       
        $mSPoint = $mission['mSPoint'];             // 미션보상포인트(성공, 정답)         
        $mFPoint = $mission['mFPoint'];             // 미션보상포인트(오답)
        $mDCnt = $mission['mDCnt'];                 // 하루최대가능 수
        $mSCnt = $mission['mSCnt'];                 // 미션수행횟수

        $reg_Date = DU_TIME_YMDHIS;                       // 등록일
        $now_Month = date('Y-m', strtotime($reg_Date));   // 이번달
        $now_Day = date('Y-m-d', strtotime($reg_Date));   // 오늘

        //미션 확인하기.
        if ($mType == '1') {    // 친해지기 미션 (최초 1회)
            $missionChkQuery = "SELECT COUNT(idx) AS mCnt FROM TB_MISSION_HISTORY WHERE mission_Idx = :mission_Idx AND mem_Idx = :mem_Idx";
        } else if ($mType == '2') { // 매일 미션 (하루 기준)
            $missionChkQuery = "SELECT COUNT(idx) AS mCnt FROM TB_MISSION_HISTORY WHERE mission_Idx = :mission_Idx AND mem_Idx = :mem_Idx AND DATE_FORMAT(reg_Date, '%Y-%m-%d') = :now_Day";
        } else if ($mType == '3') { // 한달 미션 (한달 기준)
            $missionChkQuery = "SELECT COUNT(idx) AS mCnt FROM TB_MISSION_HISTORY WHERE mission_Idx = :mission_Idx AND mem_Idx = :mem_Idx AND DATE_FORMAT(reg_Date, '%Y-%m') = :now_Month";
        }
        $missionChkStmt = $fDB_con->prepare($missionChkQuery);
        $missionChkStmt->bindParam("mission_Idx", $mission_Idx);
        $missionChkStmt->bindParam("mem_Idx", $mem_Idx);

        if ($mType == '2') { // 매일 미션 (매일 1번)
            $missionChkStmt->bindParam("now_Day", $now_Day);
        } else if ($mType == '3') {
            $missionChkStmt->bindParam("now_Month", $now_Month);
        }
        $missionChkStmt->execute();
        $missionChkRow = $missionChkStmt->fetch(PDO::FETCH_ASSOC);
        $mCnt = $missionChkRow['mCnt']; // 미션 성공 수
    } else {
        $mCnt = 1;
    }
    return $mCnt;
    dbClose($fDB_con);
    $missionChkStmt = null;
}

/**
 * 미션기록 등록하기.
 *
 * @param [int] $mission_Idx    // 미션고유번호
 * @param [int] $mem_Idx        // 회원고유번호
 * @param [int] $taxi_SIdx      // 메이커노선고유번호
 * @param [int] $taxi_RIdx      // 투게더노선고유번호
 * @return boolean
 */
function missionInsHistory($mission_Idx, $mem_Idx, $taxi_SIdx = "", $taxi_RIdx = "")
{
    $fDB_con = db1();

    //회원아이디 조회
    $mem_Id = memIdInfo($mem_Idx);

    if ($taxi_SIdx != "") {
        $taxi_SIdx_Query = ", taxi_SIdx = :taxi_SIdx";
    } else {
        $taxi_SIdx_Query = "";
    }
    if ($taxi_RIdx != "") {
        $taxi_RIdx_Query = ", taxi_RIdx = :taxi_RIdx";
    } else {
        $taxi_RIdx_Query = "";
    }

    //미션성공내역 기록
    $missionInsQuery = "INSERT INTO TB_MISSION_HISTORY SET mission_Idx = :mission_Idx, mem_Idx = :mem_Idx, mem_Id = :mem_Id {$taxi_SIdx_Query} {$taxi_RIdx_Query}";
    $missionInsStmt = $fDB_con->prepare($missionInsQuery);
    $missionInsStmt->bindParam("mission_Idx", $mission_Idx);
    $missionInsStmt->bindParam("mem_Idx", $mem_Idx);
    $missionInsStmt->bindParam("mem_Id", $mem_Id);
    if ($taxi_SIdx != "") {
        $missionInsStmt->bindParam("taxi_SIdx", $taxi_SIdx);
    }
    if ($taxi_RIdx != "") {
        $missionInsStmt->bindParam("taxi_RIdx", $taxi_RIdx);
    }
    $missionInsStmt->execute();

    $mhIdx = $fDB_con->lastInsertId();  //저장된 idx 값

    if ($mhIdx > 0) {
        return true;
    } else {
        return false;
    }

    dbClose($fDB_con);
    $missionInsStmt = null;
}

/**
 * 미션포인트 지급하기.
 *
 * @param [int] $mission_Idx
 * @param [int] $mem_Idx
 * @param [string] $mode (성공 : true, 실패 : false)
 * @return boolean
 */
function missionMemberPointGive($mission_Idx, $mem_Idx, $mode)
{
    $fDB_con = db1();

    if ($mission_Idx == 4) {
        //OX 퀴즈인 경우 
        $memo_OkName = "정답";
        $memo_NoName = "오답";
    } else {
        $memo_OkName = "성공";
        $memo_NoName = "실패";
    }
    //미션확인하기.
    $mission = missionInfoChk($mission_Idx);

    if ($mission != "") {
        $mName = $mission['mName'];                 // 미션제목 
        $mGiveType = $mission['mGiveType'];         // 미션보상지급방법(0: 즉시,  1: 받기클릭, 2: 적립예정)       
        $mSPoint = $mission['mSPoint'];             // 미션보상포인트(성공, 정답)         
        $mFPoint = $mission['mFPoint'];             // 미션보상포인트(오답)    

        if ($mode) {  // 미션 성공시
            $memPoint = $mSPoint;
            $memo = DU_TIME_YMDHIS . '
미션 (' . $mName . ') ' . $memo_OkName . '으로 포인트  ' . number_format($memPoint) . '원을 적립' . "";
        } else {  // 미션 실패시
            $memPoint = $mFPoint;
            $memo = DU_TIME_YMDHIS . '
미션 (' . $mName . ') ' . $memo_NoName . '으로 포인트  ' . number_format($memPoint) . '원을 적립' . "";
        }

        if ($mGiveType == "2") {
            $sign = "0";
            $state = "6";
            pointInsHistory($taxi_SIdx = null, $taxi_RIdx = null, $mission_Idx, $mem_Idx, $memPoint, $sign, $state, $memo);
        } else {
            $sign = "0";
            $state = "7";
            pointInsHistory($taxi_SIdx = null, $taxi_RIdx = null, $mission_Idx, $mem_Idx, $memPoint, $sign, $state, $memo);
        }
        return true;
    } else {
        return false;
    }

    dbClose($fDB_con);
}

/**
 * 포인트 히스토리 등록 및 지급하기.
 *
 * @param [int] $taxi_SIdx              // 메이커고유번호
 * @param [int] $taxi_RIdx              // 투게더고유번호
 * @param [int] $mission_Idx            // 미션고유번호
 * @param [int] $mem_Idx                // 회원고유번호
 * @param [int] $mem_Point              // 기존 보유 포인트
 * @param [int] $point                  // 변경될 포인트
 * @param [string] $sign                // 포인트 구분 (0: +, 1: -)
 * @param [string] $state               // 구분 (0: 매칭, 1: 적립, 2: 환전, 3: 추천인 적립, 4: 포인트적립(카드), 5: 신규가입 이벤트, 6.적립예정, 7:미션적립)
 * @param [string] $memo                // 포인트 메모
 * @return boolean
 */
function pointInsHistory($taxi_SIdx, $taxi_RIdx, $mission_Idx, $mem_Idx, $point, $sign, $state, $memo)
{
    $fDB_con = db1();

    //회원아이디 조회
    $mem_Id = memIdInfo($mem_Idx);

    //회원포인트 확인하기.
    $mem_Point = memPointGet($mem_Idx);

    //미션확인하기.
    $mission = missionInfoChk($mission_Idx);
    $res_Type = $mission['mResType'];

    // 등록일
    $reg_Date = DU_TIME_YMDHIS;
    if ($res_Type == '3') {
        $next_Month = date('Y-m', strtotime('+1 month', strtotime($reg_Date)));   // 다음달
        $res_Date = $next_Month . "-01 00:00:00";
    } else if ($res_Type == '2') {
        $next_Week = date('Y-m-d', strtotime('+1 week', strtotime($reg_Date)));   // 1주일 후
        $res_Date = $next_Week . " 00:00:00";
    } else if ($res_Type == '1') {
        $next_3Day = date('Y-m-d', strtotime('+3 day', strtotime($reg_Date)));   // 3일 후
        $res_Date = $next_3Day . " 00:00:00";
    } else {
        $res_Date = "";
    }
    //메이커생성번호
    if ($taxi_SIdx > 0) {
        $sQuery = "taxi_Sidx = :taxi_Sidx, ";
    } else {
        $sQuery = "";
    }

    //투게더생성번호
    if ($taxi_RIdx > 0) {
        $rQuery = "taxi_RIdx = :taxi_RIdx, ";
    } else {
        $rQuery = "";
    }

    //미션고유번호
    if ($mission_Idx > 0) {
        $mQuery = "mission_Idx = :mission_Idx, ";
    } else {
        $mQuery = "";
    }

    //적립예정인 경우 적립예정일 추가하기.
    if ($state == '6') {
        $resQuery = " , res_Date = :res_Date";
    } else {
        $resQuery = "";
    }

    $pointHisInsQuery = "INSERT INTO TB_POINT_HISTORY SET {$sQuery} {$rQuery} {$mQuery} taxi_MemId = :taxi_MemId, taxi_MemIdx = :taxi_MemIdx, taxi_OrdPoint = :taxi_OrdPoint, taxi_OrgPoint = :taxi_OrgPoint, taxi_Memo = :taxi_Memo, taxi_Sign = :taxi_Sign, taxi_PState = :taxi_PState, reg_Date = :reg_Date {$resQuery}";
    $pointHisInsStmt = $fDB_con->prepare($pointHisInsQuery);

    if ($taxi_SIdx > 0) {
        $pointHisInsStmt->bindParam("taxi_Sidx", $taxi_Sidx);
    }
    if ($taxi_RIdx > 0) {
        $pointHisInsStmt->bindParam("taxi_RIdx", $taxi_RIdx);
    }
    if ($mission_Idx > 0) {
        $pointHisInsStmt->bindParam("mission_Idx", $mission_Idx);
    }

    $pointHisInsStmt->bindParam("taxi_MemId", $mem_Id);
    $pointHisInsStmt->bindParam("taxi_MemIdx", $mem_Idx);
    $pointHisInsStmt->bindParam("taxi_OrdPoint", $point);
    $pointHisInsStmt->bindParam("taxi_OrgPoint", $mem_Point);
    $pointHisInsStmt->bindParam("taxi_Memo", $memo);
    $pointHisInsStmt->bindParam("taxi_Sign", $sign);
    $pointHisInsStmt->bindParam("taxi_PState", $state);
    $pointHisInsStmt->bindParam("reg_Date", $reg_Date);
    if ($state == '6') {
        $pointHisInsStmt->bindParam("res_Date", $res_Date);
    }
    $pointHisInsStmt->execute();
    $phIdx = $fDB_con->lastInsertId();  //저장된 idx 값

    //6.적립예정 적립예정이 아닌경우에는 바로 포인트 적용하기.
    if ($state != '6') {
        if ($phIdx > 0) {
            memPointUp($mem_Idx, $sign, $point);
        }
    }

    dbClose($fDB_con);
    $pointHisInsStmt = null;
}

/**
 * 포인트 변경
 *
 * @param [int] $mem_Idx  회원고유번호
 * @param [string] $sign  포인트 구분 (0: +, 1: -)
 * @param [int] $point    포인트
 * @return boolean
 */
function memPointUp($mem_Idx, $sign, $point)
{
    $fDB_con = db1();

    //회원포인트 확인하기.
    $mem_Point = memPointGet($mem_Idx);

    if ($sign == 0) {
        $memPoint = (int)$mem_Point + (int)$point;
    } else {
        $memPoint = (int)$mem_Point - (int)$point;
    }

    // 수정일
    $mod_Date = DU_TIME_YMDHIS;

    $memPointUpQuery = "UPDATE TB_MEMBERS_ETC SET mem_Point = :mem_Point WHERE mem_Idx = :mem_Idx LIMIT 1";
    $memPointUpStmt = $fDB_con->prepare($memPointUpQuery);
    $memPointUpStmt->bindParam("mem_Point", $memPoint);
    $memPointUpStmt->bindParam("mem_Idx", $mem_Idx);
    $memPointUpStmt->execute();

    $memModQuery = "UPDATE TB_MEMBERS_INFO SET mod_Date = :mod_Date WHERE mem_Idx = :mem_Idx LIMIT 1";
    $memModStmt = $fDB_con->prepare($memModQuery);
    $memModStmt->bindParam("mod_Date", $mod_Date);
    $memModStmt->bindParam("mem_Idx", $mem_Idx);
    $memModStmt->execute();

    dbClose($fDB_con);
    $memPointUpStmt = null;
    $memModStmt = null;
}

/**
 * 메이커 같이타기 만들기 미션 기록
 *
 * @param [int] $mem_Idx  회원고유번호
 * @param [int] $taxi_Idx 노선고유번호
 * @return void
 */
function makerRoom($mem_Idx, $taxi_Idx)
{
    $fDB_con = db1();
    $mission_Idx = 6;
    $mChk = missionHistoryChk($mission_Idx, $mem_Idx);
    if ($mChk < 1) {
        // 미션기록
        $hChk = missionInsHistory($mission_Idx, $mem_Idx, $taxi_Idx, '');

        if ($hChk) { // 미션기록완료
            //포인트 지급
            $pGive = missionMemberPointGive($mission_Idx, $mem_Idx, true);
            if ($pGive) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    } else {
        return false;
    }
    dbClose($fDB_con);
}

/**
 * 메이커 같이타기 만들기 미션 기록 확인하여 팝업표시 유무
 *
 * @param [string] $mem_Idx
 * @param [string] $taxi_Idx
 * @return void
 */
function makerRoomChk($mem_Idx, $taxi_Idx)
{
    $fDB_con = db1();

    $reg_Date = DU_TIME_YMDHIS;                       // 등록일
    $now_Day = date('Y-m-d', strtotime($reg_Date));   // 오늘
    
    $sharingChkQuery = "SELECT idx FROM TB_MISSION_HISTORY WHERE mission_Idx = 6 AND mem_Idx = :mem_Idx AND taxi_SIdx = :taxi_SIdx AND DATE_FORMAT(reg_Date, '%Y-%m-%d') = :now_Day";
    $sharingChkStmt = $fDB_con->prepare($sharingChkQuery);
    $sharingChkStmt->bindParam("mem_Idx", $mem_Idx);
    $sharingChkStmt->bindParam("taxi_SIdx", $taxi_Idx);
    $sharingChkStmt->bindParam("now_Day", $now_Day);
    $sharingChkStmt->execute();
    $sharingChkNum = $sharingChkStmt->rowCount();

    if ($sharingChkNum > 0) { //아닐경우
        return true;
    } else {
        return false;
    }
    dbClose($fDB_con);
}

/**
 * 투게더 가치타기 신청하기 미션
 *
 * @param [int] $mem_Idx
 * @param [int] $taxi_SIdx
 * @param [int] $taxi_RIdx
 * @return void
 */
function togetherRoom($mem_Idx, $taxi_SIdx, $taxi_RIdx)
{
    $fDB_con = db1();
    $mission_Idx = 7;
    $mChk = missionHistoryChk($mission_Idx, $mem_Idx);
    if ($mChk < 1) {
        // 미션기록
        $hChk = missionInsHistory($mission_Idx, $mem_Idx, $taxi_SIdx, $taxi_RIdx);

        if ($hChk) { // 미션기록완료
            //포인트 지급
            $pGive = missionMemberPointGive($mission_Idx, $mem_Idx, true);
        }
    }
    dbClose($fDB_con);
}

/**
 * 투게더 가치타기 신청하기 미션 기록 확인하여 팝업표시 유무 
 *
 * @param [int] $mem_Idx             // 회원고유번호
 * @param [int] $taxi_SIdx           // 메이커노선고유번호
 * @param [int] $taxi_RIdx           // 투게더노선고유번호
 * @return boolean
 */
function togetherRoomChk($mem_Idx, $taxi_SIdx, $taxi_RIdx)
{
    $fDB_con = db1();

    $reg_Date = DU_TIME_YMDHIS;                       // 등록일
    $now_Day = date('Y-m-d', strtotime($reg_Date));   // 오늘
    
    $sharingChkQuery = "SELECT idx FROM TB_MISSION_HISTORY WHERE mission_Idx = 7 AND mem_Idx = :mem_Idx AND taxi_SIdx = :taxi_SIdx AND taxi_RIdx = :taxi_RIdx AND DATE_FORMAT(reg_Date, '%Y-%m-%d') = :now_Day";
    $sharingChkStmt = $fDB_con->prepare($sharingChkQuery);
    $sharingChkStmt->bindParam("mem_Idx", $mem_Idx);
    $sharingChkStmt->bindParam("taxi_SIdx", $taxi_SIdx);
    $sharingChkStmt->bindParam("taxi_RIdx", $taxi_RIdx);
    $sharingChkStmt->bindParam("now_Day", $now_Day);
    $sharingChkStmt->execute();
    $sharingChkNum = $sharingChkStmt->rowCount();

    if ($sharingChkNum > 0) { //아닐경우
        return true;
    } else {
        return false;
    }
    dbClose($fDB_con);
}


/**
 * 메이커 가치타기 요청받기 미션
 *
 * @param [int] $mem_Idx
 * @param [int] $taxi_SIdx           // 메이커노선고유번호
 * @param [int] $taxi_RState         // 요청상태값
 * @return boolean
 */
function makerTogetherRoom($mem_Idx, $taxi_SIdx, $taxi_RState)
{
    $fDB_con = db1();
    $mission_Idx = 8;
    $mChk = missionHistoryChk($mission_Idx, $mem_Idx);
    if ($mChk < 1) {
        // 미션기록 가능
        $hChk = missionInsHistory($mission_Idx, $mem_Idx, $taxi_SIdx);
        if ($hChk) { // 미션기록완료
            //포인트 지급
            missionMemberPointGive($mission_Idx, $mem_Idx, true);

            $mem_Token = memMatchTokenInfo($mem_Idx);
            $title = "가치타 요청받기 미션 완료 🎉🥳";
            $msg = "포인트 10원을 적립해드렸어요.";
            foreach ($mem_Token as $k => $v) {
                $tokens = $mem_Token[$k];
                $inputData = array("title" => $title, "msg" => $msg, "state" => $taxi_RState);
                $presult = send_Push($tokens, $inputData);
            }
        }
    }
    dbClose($fDB_con);
}

/**
 * html 이스케이프로로 보호된 것을 HTML로 변환하기.
 *
 * @param [string] $text        // html content내용
 * @return string
 */
function html_Decode($text)
{
    $decodedHtml = str_replace(
        array('\\"', '\\/', '\\n', '\\r', '\\t', '\\\'', '<br>', '&nbsp;'),
        array('"', '/', "\n", "\r", "\t", "'", "<br>", '&nbsp;'),
        $text
    );
    return $decodedHtml;
}

/**
 * 메이커노선생성확인
 *
 * @param [int] $memIdx
 * @return boolean
 */
function makerChk($memIdx)
{
    $fDB_con = db1();

    $chkCntNum = 0;
    //매칭 생성 테이블 조건값 체크
    $chkCntQuery = "SELECT count(idx) AS num FROM TB_STAXISHARING WHERE taxi_MemIdx = :taxi_MemIdx AND taxi_State IN ('1', '2') ";
    $chkCntStmt = $fDB_con->prepare($chkCntQuery);
    $chkCntStmt->bindParam("taxi_MemIdx", $memIdx);
    $chkCntStmt->execute();
    $chkCntRow = $chkCntStmt->fetch(PDO::FETCH_ASSOC);
    $chkCntNum = $chkCntRow['num'];

    if ($chkCntNum > 0) {
        return true;
    } else {
        return false;
    }


    dbClose($fDB_con);
    $chkCntStmt = null;
}