	<nav id="gnb" class="gnb_large ">
		<h2>관리자 주메뉴</h2>
		<ul class="gnb_ul">
			<? if ($du_udev['lv'] != 2) { //최고권한관리자 	
			?>
				<? if ($du_udev['lv'] == 0) { //최고권한관리자 	
				?>
					<li class="gnb_li <? if ($menu == "1") { ?>on<? } ?>">
						<button type="button" class="btn_op menu-100 menu-order-1" title="환경설정">환경설정</button>
						<div class="gnb_oparea_wr">
							<div class="gnb_oparea">
								<h3>환경설정</h3>
								<ul>

									<li data-menu="1">
										<a href="<?= DU_UDEV_DIR ?>/config/configReg.php" class="gnb_2da <? if ($menu == "1" && $smenu == "1") { ?>on<? } ?>">환경설정</a>
									</li>
									<li data-menu="7">
										<a href="<?= DU_UDEV_DIR ?>/config/configExcReg.php" class="gnb_2da <? if ($menu == "1" && $smenu == "7") { ?>on<? } ?>">포인트출금 환경설정</a>
									</li>
									<li data-menu="2">
										<a href="<?= DU_UDEV_DIR ?>/config/configEtcReg.php" class="gnb_2da <? if ($menu == "1" && $smenu == "2") { ?>on<? } ?>">기타 환경설정</a>
									</li>
									<li data-menu="8">
										<a href="<?= DU_UDEV_DIR ?>/config/configMissionList.php" class="gnb_2da <? if ($menu == "1" && $smenu == "8") { ?>on<? } ?>">미션관리</a>
									</li>
									<li data-menu="9">
										<a href="<?= DU_UDEV_DIR ?>/config/configOxList.php" class="gnb_2da <? if ($menu == "1" && $smenu == "9") { ?>on<? } ?>">OX퀴즈관리</a>
									</li>
									<li data-menu="4">
										<a href="<?= DU_UDEV_DIR ?>/config/sessionFileDel.php" class="gnb_2da <? if ($menu == "1" && $smenu == "3") { ?>on<? } ?>">세션파일 일괄삭제</a>
									</li>
									<li data-menu="5">
										<a href="/phpinfo.php" class="gnb_2da gnb_grp_div" target="_blank">phpinfo()</a>
									</li>
								</ul>
							</div>
						</div>
					</li>
				<? } ?>

				<li class="gnb_li <? if ($menu == "2") { ?>on<? } ?>">
					<button type="button" class="btn_op menu-200 menu-order-2" title="회원관리">회원관리</button>
					<div class="gnb_oparea_wr">
						<div class="gnb_oparea">
							<h3>회원관리</h3>
							<ul>
								<li data-menu="1">
									<a href="<?= DU_UDEV_DIR ?>/member/memManagerList.php" class="gnb_2da <? if ($menu == "2" && $smenu == "1") { ?>on<? } ?>">회원등급 관리</a>
								</li>
								<li data-menu="6">
									<a href="<?= DU_UDEV_DIR ?>/member/memberAdminList.php" class="gnb_2da <? if ($menu == "2" && $smenu == "6") { ?>on<? } ?>">단체 관리</a>
								</li>
								<li data-menu="2">
									<a href="<?= DU_UDEV_DIR ?>/member/memberList.php" class="gnb_2da <? if ($menu == "2" && $smenu == "2") { ?>on<? } ?>">회원 관리</a>
								</li>
								<li data-menu="3">
									<a href="<?= DU_UDEV_DIR ?>/member/memberLeaveList.php" class="gnb_2da <? if ($menu == "2" && $smenu == "3") { ?>on<? } ?>">탈퇴회원 관리</a>
								</li>
								<li data-menu="4">
									<a href="<?= DU_UDEV_DIR ?>/member/pointList.php" class="gnb_2da <? if ($menu == "2" && $smenu == "4") { ?>on<? } ?>">포인트 관리</a>
								</li>
								<li data-menu="4">
									<a href="<?= DU_UDEV_DIR ?>/member/memStatList.php" class="gnb_2da <? if ($menu == "2" && $smenu == "7") { ?>on<? } ?>">회원통계</a>
								</li>
							</ul>
						</div>
					</div>
				</li>

				<li class="gnb_li <? if ($menu == "6") { ?>on<? } ?>">
					<button type="button" class="btn_op menu-300 menu-order-3" title="게시판관리">게시판관리</button>
					<div class="gnb_oparea_wr">
						<div class="gnb_oparea">
							<h3>게시판관리</h3>
							<ul>
								<li data-menu="3">
									<? if ($du_udev['lv'] == 0) { //최고권한관리자 	
									?>
										<a href="<?= DU_UDEV_DIR ?>/boardM/boardManagerList.php" class="gnb_2da <? if ($menu == "6" && $smenu == "1") { ?>on<? } ?>">게시판 환경설정 관리</a>
									<? } else { ?>
										<a href="<?= DU_UDEV_DIR ?>/boardM/boardManagerList.php" class="gnb_2da <? if ($menu == "6" && $smenu == "1") { ?>on<? } ?>">게시판 관리</a>
									<? } ?>
								</li>
								<li data-menu="3">
									<a href="/board/boardList.php?board_id=1" target="_BLANK" class="gnb_2da <? if ($menu == "6" && $smenu == "2") { ?>on<? } ?>">공지사항 바로가기</a>
								</li>
								<li data-menu="3">
									<a href="/board/boardList.php?board_id=2" target="_BLANK" class="gnb_2da <? if ($menu == "6" && $smenu == "3") { ?>on<? } ?>">자주묻는질문 바로가기</a>
								</li>
							</ul>
						</div>
					</div>
				</li>

				<li class="gnb_li <? if ($menu == "3") { ?>on<? } ?>">
					<button type="button" class="btn_op menu-500 menu-order-5" title="기타관리">기타관리</button>
					<div class="gnb_oparea_wr">
						<div class="gnb_oparea">
							<h3>기타관리</h3>
							<ul>
								<li data-menu="5">
									<a href="<?= DU_UDEV_DIR ?>/etc/cardList.php" class="gnb_2da <? if ($menu == "3" && $smenu == "1") { ?>on<? } ?>">결제카드 관리</a>
								</li>
								<li data-menu="5">
									<a href="<?= DU_UDEV_DIR ?>/etc/eventList.php" class="gnb_2da <? if ($menu == "3" && $smenu == "2") { ?>on<? } ?>">이벤트 관리</a>
								</li>
								<li data-menu="5">
									<a href="<?= DU_UDEV_DIR ?>/etc/eventBannerList.php" class="gnb_2da <? if ($menu == "3" && $smenu == "3") { ?>on<? } ?>">배너 관리</a>
								</li>
								<li data-menu="5">
									<a href="<?= DU_UDEV_DIR ?>/etc/popupList.php" class="gnb_2da <? if ($menu == "3" && $smenu == "4") { ?>on<? } ?>">팝업 관리</a>
								</li>
								<li data-menu="5">
									<a href="<?= DU_UDEV_DIR ?>/etc/inquiryList.php" class="gnb_2da <? if ($menu == "3" && $smenu == "5") { ?>on<? } ?>">문의리스트 관리</a>
								</li>
								<li data-menu="5">
									<a href="<?= DU_UDEV_DIR ?>/etc/cardStatList.php" class="gnb_2da <? if ($menu == "3" && $smenu == "7") { ?>on<? } ?>">카드등록통계</a>
								</li>
								<li data-menu="5">
									<a href="<?= DU_UDEV_DIR ?>/etc/taxiCallList.php" class="gnb_2da <? if ($menu == "3" && $smenu == "8") { ?>on<? } ?>">택시호출관리</a>
								</li>
							</ul>
						</div>
					</div>
				</li>



				<li class="gnb_li <? if ($menu == "4") { ?>on<? } ?>">
					<button type="button" class="btn_op menu-400 menu-order-4" title="매칭관리">매칭관리</button>
					<div class="gnb_oparea_wr">
						<div class="gnb_oparea">
							<h3>매칭관리</h3>
							<ul>
								<li data-menu="4">
									<a href="<?= DU_UDEV_DIR ?>/taxiSharing/taxiSharingList.php" class="gnb_2da <? if ($menu == "4" && $smenu == "1") { ?>on<? } ?>">매칭 관리</a>
								</li>
								<li data-menu="4">
									<a href="<?= DU_UDEV_DIR ?>/taxiSharing/taxiSharingCList.php" class="gnb_2da <? if ($menu == "4" && $smenu == "2") { ?>on<? } ?>">취소 내역</a>
								</li>
								<li data-menu="4">
									<a href="<?= DU_UDEV_DIR ?>/taxiSharing/taxiSharingCRList.php" class="gnb_2da <? if ($menu == "4" && $smenu == "3") { ?>on<? } ?>">취소처리 관리</a>
								</li>
								<li data-menu="4">
									<a href="<?= DU_UDEV_DIR ?>/taxiSharing/taxiSharingRList.php" class="gnb_2da <? if ($menu == "4" && $smenu == "4") { ?>on<? } ?>">완료처리 관리</a>
								</li>
								<li data-menu="4">
									<a href="<?= DU_UDEV_DIR ?>/taxiSharing/taxiSharingComList.php" class="gnb_2da <? if ($menu == "4" && $smenu == "5") { ?>on<? } ?>">완료처리 내역</a>
								</li>
								<li data-menu="4">
									<a href="<?= DU_UDEV_DIR ?>/taxiSharing/taxiSharungStatList.php" class="gnb_2da <? if ($menu == "4" && $smenu == "6") { ?>on<? } ?>">매칭통계</a>
								</li>
							</ul>
						</div>
					</div>
				</li>

				<li class="gnb_li <? if ($menu == "5") { ?>on<? } ?>">
					<button type="button" class="btn_op menu-600 menu-order-6" title="주문관리">주문관리</button>
					<div class="gnb_oparea_wr">
						<div class="gnb_oparea">
							<h3>주문관리</h3>
							<ul>
								<li data-menu="6">
									<a href="<?= DU_UDEV_DIR ?>/order/orderList.php" class="gnb_2da <? if ($menu == "5" && $smenu == "1") { ?>on<? } ?>">주문 관리</a>
								</li>
							</ul>
						</div>
					</div>
				</li>

				<li class="gnb_li <? if ($menu == "7") { ?>on<? } ?>">
					<button type="button" class="btn_op menu-700 menu-order-7" title="정산관리">정산관리</button>
					<div class="gnb_oparea_wr">
						<div class="gnb_oparea">
							<h3>정산관리</h3>
							<ul>
								<li data-menu="7">
									<a href="<?= DU_UDEV_DIR ?>/account/profitList.php" class="gnb_2da <? if ($menu == "7" && $smenu == "1") { ?>on<? } ?>">수익 관리</a>
								</li>
								<li data-menu="7">
									<a href="<?= DU_UDEV_DIR ?>/account/pointExcList.php" class="gnb_2da <? if ($menu == "7" && $smenu == "2") { ?>on<? } ?>">포인트 출금 관리</a>
								</li>
								<li data-menu="7">
									<a href="<?= DU_UDEV_DIR ?>/account/pointStatList.php" class="gnb_2da <? if ($menu == "7" && $smenu == "3") { ?>on<? } ?>">수익통계</a>
								</li>
							</ul>
						</div>
					</div>
				</li>

				<li class="gnb_li <? if ($menu == "10") { ?>on<? } ?>">
					<button type="button" class="btn_op menu-400 menu-order-4" title="푸시관리">푸시관리</button>
					<div class="gnb_oparea_wr">
						<div class="gnb_oparea">
							<h3>푸시관리</h3>
							<ul>
								<li data-menu="10">
									<a href="<?= DU_UDEV_DIR ?>/push/pushList.php" class="gnb_2da <? if ($menu == "10" && $smenu == "1") { ?>on<? } ?>">푸시 관리</a>
								</li>
								<li data-menu="10">
									<a href="<?= DU_UDEV_DIR ?>/push/pushDisableList.php" class="gnb_2da <? if ($menu == "10" && $smenu == "2") { ?>on<? } ?>">수신거부리스트</a>
								</li>


							</ul>
						</div>
					</div>
				</li>
			<? } ?>
		</ul>
	</nav>

	</header>
	<script>
		jQuery(function($) {

			var menu_cookie_key = 'g5_admin_btn_gnb';

			$(".tnb_mb_btn").click(function() {
				$(".tnb_mb_area").toggle();
			});

			$("#btn_gnb").click(function() {

				var $this = $(this);

				try {
					if (!$this.hasClass("btn_gnb_open")) {
						set_cookie(menu_cookie_key, 1, 60 * 60 * 24 * 365);
					} else {
						delete_cookie(menu_cookie_key);
					}
				} catch (err) {}

				$("#container").toggleClass("container-small");
				$("#gnb").toggleClass("gnb_small");
				$this.toggleClass("btn_gnb_open");

			});

			$(".gnb_ul li .btn_op").click(function() {
				$(this).parent().addClass("on").siblings().removeClass("on");
			});

		});
	</script>