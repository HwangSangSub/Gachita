
<div id="to_content"><a href="#container">본문 바로가기</a></div>

<header id="hd">
    <h1>가치타관리자</h1>
    <div id="hd_top">
        <button type="button" id="btn_gnb" class="btn_gnb_close ">메뉴</button>
       <div id="logo"><a href="<?=DU_UDEV_DIR?>"><img src="<?=DU_UDEV_DIR?>/common/img/logo.png" alt="가치타관리자"></a></div>

        <div id="tnb">
            <ul>
                <!-- <li class="tnb_li"><a href="/taxi" class="tnb_shop" target="_blank" title="쇼핑몰 바로가기">쇼핑몰 바로가기</a></li> -->
                <li class="tnb_li"><a href="<?=DU_UDEV_DIR?>" class="tnb_community" target="_blank" title="관리자 메인 바로가기">관리자 메인 바로가기</a></li>
				<li class="tnb_li"><a href="javascript:window.open('/udev/etc/pushReg.php','푸시전송하기','width=600,height=520,left=600');" title="푸시전송하기"class="tnb_service">푸시 보내기</a></li>
                <li class="tnb_li"><a href="<?=DU_UDEV_DIR?>/taxiSharing/taxiSharingList.php" class="tnb_service">매칭 관리</a></li>
                <li class="tnb_li"><button type="button" class="tnb_mb_btn">관리자<span class="./img/btn_gnb.png">메뉴열기</span></button>
                    <ul class="tnb_mb_area">

						<? if ($du_udev['id'] != "") {?>
                        <li><a href="<?=DU_UDEV_DIR?>/member/memberReg.php?mode=mod&id=<?=$du_udev['id']?>">관리자정보</a></li>
						<? } ?>
                        <li id="tnb_logout"><a href="<?=DU_UDEV_DIR?>/logOut.php">로그아웃</a></li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>