<?

include "../../../udev/lib/common.php";

//암호화할 때:  
$password = trim($user_pw);

$DB_con = db1();

//관리자아이디 조회
$query = "";
$query = "SELECT A.idx, A.mem_Id FROM TB_MEMBERS A WHERE A.mem_Lv IN (0, 1, 2) AND A.b_Disply = 'N'; ";;
$stmt = $DB_con->prepare($query);
$stmt->execute();
$chkAdminNum = $stmt->rowCount();
if ($chkAdminNum < 1) {
	$chknum = 0;
} else {
	$chknum = 1;
	$admin_list = array();				//관리자 아이디를 저장하기 위한 배열 선언
	while ($chkAdminRow = $stmt->fetch(PDO::FETCH_ASSOC)) {
		array_push($admin_list, $chkAdminRow['mem_Id']);			//관리자 아이디를 배열에 저장
		//$amem_Id = $chkAdminRow['mem_Id'];
	}
}
if (in_array($du_udev['id'], $admin_list) || in_array($user_id, $admin_list)) {
	$memSql = "  , ( SELECT login_Cnt FROM TB_MEMBERS_ETC WHERE TB_MEMBERS_ETC.mem_Idx = TB_MEMBERS.idx limit 1 ) AS login_Cnt  ";
	$query = "SELECT idx, mem_Id, mem_Pwd, mem_Lv, mem_NickNm {$memSql} FROM TB_MEMBERS  WHERE mem_Id = :mem_Id AND b_Disply = 'N' AND mem_Lv IN (0,1,2)";
	$stmt = $DB_con->prepare($query);
	$stmt->bindparam(":mem_Id", $user_id);
	$user_id = trim($user_id);
	$stmt->execute();
	$num = $stmt->rowCount();

	if ($num < 1) { //아닐경우
		echo "error";
	} else {

		while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {

			$mem_Idx = $row['idx'];	           // 회원고유 아이디
			$hash = $row['mem_Pwd'];

			if (password_verify($password, $hash)) { // 비밀번호가 일치하는지 비교합니다. 

				echo "success";  // 비밀번호가 맞음 

				$login_Cnt = $row['login_Cnt'];      // 로그인 횟수
				$login_Cnt = $login_Cnt + 1;

				# 마지막 로그인 시간을 업데이트 한다.
				$upQquery = "UPDATE TB_MEMBERS_INFO SET login_Date = now() WHERE  mem_Idx = :mem_Idx  LIMIT 1";
				$upStmt = $DB_con->prepare($upQquery);
				$upStmt->bindparam(":mem_Idx", $mem_Idx);
				$upStmt->execute();

				$upQquery2 = "UPDATE TB_MEMBERS_ETC SET login_Cnt = :login_Cnt WHERE mem_Idx =  :mem_Idx LIMIT 1";
				$upStmt2 = $DB_con->prepare($upQquery2);
				$upStmt2->bindparam(":login_Cnt", $login_Cnt);
				$upStmt2->bindparam(":mem_Idx", $mem_Idx);
				$upStmt2->execute();

				$mem_Id = $user_id;									   // 아이디
				$mem_Pwd = $row['mem_Pwd'];	           // 비밀번호
				$mem_NickNm = $row['mem_NickNm'];      // 닉네임
				$mem_Lv = $row['mem_Lv'];      // 등급

				setcookie("du_udev[id]", $mem_Id, false, "/");
				setcookie("du_udev[midx]", $mem_Idx, false, "/");
				setcookie("du_udev[pw]", $mem_Pwd, false, "/");
				setcookie("du_udev[nickNm]", $mem_NickNm, false, "/");
				setcookie("du_udev[lv]", $mem_Lv, false, "/");
			} else {
				echo "error";  // 비밀번호가 틀림 
			}
		}
	}
} else {
	echo "error2";
}

dbClose($DB_con);
$stmt = null;
$upStmt = null;
$upStmt2 = null;
