<?
	include "../lib/common.php";
	include "../lib/alertLib.php";
	
	$idx = trim($idx);  //게시판 Idx

	$titNm = "이벤트 배너";

	$DB_con = db1();

	
	//조회수 업데이트
	$upQuery = "UPDATE TB_BANNER SET ban_ReadCnt = $ban_ReadCnt + 1 WHERE idx = :idx LIMIT 1";
	$upStmt = $DB_con->prepare($upQuery);
	$upStmt->bindparam(":idx",$idx);
	$upStmt->execute();
	//조회수 업데이트 끝

	$query = "";
	$query = " SELECT ban_Title, ban_ImgFile, ban_Content, reg_Date FROM TB_BANNER WHERE idx = :idx LIMIT 1 ";
	$qStmt = $DB_con->prepare($query);
	$qStmt->bindparam(":idx",$idx);
	$qStmt->execute();
	$qNum = $qStmt->rowCount();
	
	if($qNum < 1)  { //아닐경우
		$message = "잘못된 접근 방식입니다.";
		proc_msg3($message);
	} else {
	    
	    //조회수 업데이트
	    $bquery = "";
	    $bquery = "SELECT ban_ReadCnt FROM TB_BANNER WHERE idx = :idx LIMIT 1";
	    $bStmt = $DB_con->prepare($bquery);
	    $bStmt->bindparam(":idx",$idx);
	    $bStmt->execute();
	    $bNum = $bStmt->rowCount();
	    
	    if($bNum < 1)  { //아닐경우
	    } else {
	        while($bsRow=$bStmt->fetch(PDO::FETCH_ASSOC)) {
	            $ban_ReadCnt = trim($bsRow['ban_ReadCnt']);
	        }
	    }
	    
	    
		while($v=$qStmt->fetch(PDO::FETCH_ASSOC)) {
		    $ban_Title = trim($v['ban_Title']);
		    $ban_ImgFile = trim($v['ban_ImgFile']);
		    $ban_Content = htmlspecialchars_decode(trim($v['ban_Content']));
			$reg_Date = trim($v['reg_Date']);
		}

	}

	
	dbClose($DB_con);
	$upStmt = null;
	$qStmt = null;
	$bStmt = null;
	
	include "eventHead.php";  //헤더
?>

    <content>
        <div class="contents">		
		
		<div>
			<ul class="title_h2">
				<li class="float_l">
					<h2><?=$titNm?></h2>			
				</li>
			</ul>
		</div>
			<div class="du01">
				
				<ul class="view_contents">
					<li class="title">
						<p>
						<span class="title"><?=$ban_Title?></span>
						</p>
						
						<div class="admin_l">
						<p>
						<span class="date"><?= DateHard($reg_Date,1) ?></span>
						</p>
						</div>
					</li>
					
					<li class="m_content">
						<p>
						<? 
						
					   	   if($ban_ImgFile == "")  { //아닐경우
						   } else {
								$imgUrl = "/data/banner/";
								
								$bFName = $ban_ImgFile;
								$fname = explode(".", $ban_ImgFile);
								$fileExt = strtolower($fname[count($fname)-1]);   //확장자 구하는것

								If ($fileExt == "gif" || $fileExt == "jpeg" || $fileExt == "jpg" || $fileExt == "png" || $fileExt == "bmp") {  //확장자 이미지 체크
						?>
								  <img src="<?=$imgUrl?><?=$ban_ImgFile?>" class="thumb"></br></br>
						<?
								}
						  }
						?>
						</p>
						<p><?=$ban_Content?></p>

					</li>
				</ul>
			</div>
			
			
			
			
        </div>
    </content>

</body>
</html>