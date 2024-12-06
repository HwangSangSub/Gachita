<?
/*======================================================================================================================

* 프로그램			: 가이드 페이지 (웹뷰)
* 페이지 설명		: 가이드 페이지
* 파일명           : guide.php

========================================================================================================================*/
include "./udev/lib/common.php";
include DU_COM . "/functionDB.php";

$id = trim($_REQUEST['id']);    // 가이드 선택값.
if ($id == '') {
    $id = 'content_Common';
}
?>
<!DOCTYPE html>
<html>

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, target-densitydpi=medium-dpi" />
    <title>이용 가이드</title>
    <script src="https://unpkg.com/swiper/swiper-bundle.min.js"></script>
    <link rel="stylesheet" href="https://unpkg.com/swiper/swiper-bundle.min.css">
    <link rel="StyleSheet" HREF="../board/css/common.css" type="text/css" title="Global CSS">
    <link rel="StyleSheet" HREF="../../common/css/pretendard/pretendard.css" type="text/css" title="Global CSS">
    <link rel="StyleSheet" HREF="../board/css/board-style.css" type="text/css" title="Global CSS">
    <link rel="StyleSheet" HREF="../board/css/jquery-ui-1.11.1.css" type="text/css" title="Global CSS">
    <script language='javascript' src="../board/js/jquery-1.11.0.min.js" type="text/javascript"></script>
    <script language='javascript' src="../board/js/jquery-ui-1.11.1.js" type="text/javascript"></script>
    <script language='javascript' src="../board/js/jquery.animate-enhanced.js"></script>
    <script language='javascript' src="../board/js/jquery.form.js" type="text/javascript"></script>
    <script language='javascript' src="../board/js/common.js" type="text/javascript"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/meyer-reset/2.0/reset.min.css">
    <style>
        :root {
            --nav: #A1A1A1;
            --nav-background-color: #FFFFFF;
            --nav-active: #323232;
            --nav-active-border: #323232;
            --font-white: #FFFFFF;
            --button-blue: #326CF9;
            --footer-line: #EBEBEB;
            --footer-font: #626262;

            --font-nav: 14.5px;
            --font-content-button: 17px;

            --lineheight-nav: 17px;
        }


        body {
            width: 100%;
            height: 100%;
            display: flex;
            flex-wrap: wrap;
            background: #FFFFFF 0% 0% no-repeat padding-box;
            align-content: space-between;
            justify-content: center;
            overflow-x: hidden !important;
        }

        .warp {
            width: 100%;
            display: flex;
            flex-wrap: wrap;
            align-content: space-around;
            flex-direction: column;
            justify-content: flex-start;
            overflow-x: hidden !important;
        }

        .swiper-container {
            width: 100%;
            position: relative;
        }

        .swiper-slide {
            text-align: center;
            font-size: 18px;
            background: var(--font-white);

            /* Center slide text vertically */
            display: flex;
            justify-content: center;
            align-items: center;
        }

        /* 추가된 스타일 */
        .swiper-pagination {
            position: absolute;
        }

        .swiper-pagination-bullet {
            width: 10px;
            height: 10px;
            background-color: var(--font-white);
            opacity: 0.5;
            border-radius: 50%;
            margin: 0 5px;
        }

        .swiper-pagination-bullet-active {
            opacity: 1;
        }

        .warp>.swiper-container>.top_Img {
            width: 100%;
        }

        .warp>.swiper-container>.top_Img>img {
            overflow: hidden;
        }

        /* 상단 이미지 슬라이드 처리 시작 */
        .warp>.swiper-container>.top_Img>.mySlides {

            border-radius: 3%;
            width: 100%;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .warp>.swiper-container>.top_Img>.fade {
            animation-name: fade;
            animation-duration: 1.5s;
        }

        @keyframes fade {
            from {
                opacity: 0.4
            }

            to {
                opacity: 1
            }
        }

        /* 상단 이미지 슬라이드 처리 종료 */
        .warp>.top_Nav {
            position: relative;
            width: 100%;
            display: flex;
            justify-content: center;
            align-items: center;
            background-color: var(--nav-background-color);
        }

        .warp>.top_Nav.fixed {
            position: fixed;
            top: 0;
            width: 100%;
            z-index: 9999;
        }

        .warp>.top_Nav>div {
            width: 33%;
            padding: 5% 7%;
            text-align: center;
            font-family: var(--font-family-pretendard-regular);
            font-size: var(--font-nav);
            color: var(--nav);
            line-height: var(--lineheight-nav);
        }

        .warp>.top_Nav>.active {
            padding: 5% 7%;
            font-family: var(--font-family-pretendard-semibold);
            font-size: var(--font-nav);
            color: var(--nav-active);
            border-bottom: 3px solid var(--nav-active-border);
        }

        .warp>.content {
            width: 100%;
            text-align: center;
            padding-top: 7%;
        }

        .warp>.content>div {
            width: 100%;
            font-family: var(--font-family-pretendard-medium);
            font-size: var(--font-size-xxs);
            color: var(--content);
            line-height: 25px;
        }

        .warp>.content>div>img {
            width: 100%;
            margin-bottom: 5%;
        }

        .warp>.content>div>a {
            width: 90%;
        }

        .warp>.content>.active {
            display: flex !important;
            flex-direction: column;
            flex-wrap: nowrap;
            align-items: center;
        }

        .warp>.content>:not(.active) {
            display: none;
        }

        .warp>.content>div>a>img {
            width: 100%;
        }

        .warp>.footer {
            text-align: center;
            padding: 5% 3% 15% 3%;
            width: 100%;
            width: 95%;
        }

        .warp>.footer>div {
            padding-top: 13%;
            border-top: var(--footer-line) 1.5px solid;
        }

        .warp>.footer>div>span {
            font-family: var(--font-family-pretendard-semibold);
            font-size: var(--font-size-xxs);
            color: var(--footer-font);
            line-height: 25px;
        }

        .warp>.footer>div>a {
            width: 100%;
        }

        .warp>.footer>div>a>img {
            padding-top: 4%;
        }

    </style>
    <script type="text/javascript">
        var slideInterval; // 슬라이드 자동 전환 인터벌 변수
        var toggle = true;
        $(document).ready(function() {
            const mySwiper = new Swiper('.swiper-container', {
                spaceBetween: 30,
                centeredSlides: true,
                pagination: {
                    el: '.swiper-pagination',
                    clickable: true
                },
                autoplay: {
                    delay: 3000,
                    disableOnInteraction: true // 쓸어 넘기거나 버튼 클릭 시 자동 슬라이드 정지.
                },
                loop: true
            })

            const swiperContainer = document.querySelector('.swiper-container')

            // 데스크톱에서는 포커스가 빠진 경우 자동 슬라이드 재생.
            swiperContainer.addEventListener('focusout', () => {
                setTimeout(() => {
                    if (swiperContainer.querySelector(':focus') === null) {
                        mySwiper.autoplay.start()
                    }
                }, 100)
            })

            // 모바일에서는 화면을 움직이면 자동 슬라이드 기능 재생.
            document.addEventListener('touchmove', () => {
                mySwiper.autoplay.start()
            })

            $(window).scroll(function() {
                var elements = document.getElementsByClassName('top_Img');
                var element = elements[0];
                var height = element.offsetHeight; // 요소의 내용, 패딩, 테두리, 스크롤바를 포함한 총 높이
                var clientHeight = element.clientHeight; // 요소의 내용과 패딩을 포함한 높이
                var nav = $('.top_Nav');
                var top = $(window).scrollTop();
                var navh = $('.top_Nav').height();
                if (top > clientHeight) {
                    nav.addClass('fixed');
                    $('.content').css({'padding-top':navh});
                } else {
                    nav.removeClass('fixed');
                    $('.content').css({'padding-top':-navh});
                }

            });
            $(".top_Nav>div").click(function() {
                //네비게이션 선택 클래스 초기화
                $(".top_Nav>div").removeClass("active");
                //내용 부분 선택 클래스 초기화
                $(".content>div").removeClass("active");
                // 선택한 네비게이션 버튼 클래스 추가
                $(this).addClass("active");
                // 선택한 네비게이션의 id값을 구함
                var selId = $(this).attr('id');
                $('#selId').val(selId);
                // 알아낸 id값의 내용을 보이게 클래스 추가
                $('.' + selId).addClass("active");
                $(this)[0].scrollIntoView({
                    behavior: 'smooth'
                });
                // 스크롤 위치 확인
                var scrollPosition = $(window).scrollTop();
                var elementPosition = $(".content").offset().top;

                // 스크롤 위치가 요소 위치보다 아래에 있는 경우에만 스크롤 조정
                if (scrollPosition > elementPosition) {
                    $("html, body").stop().animate({
                        scrollTop: elementPosition
                    },800);
                }
            });
        });
        // URL에서 쿼리 파라미터를 가져오는 함수
        function getQueryParam(name) {
            var urlParams = new URLSearchParams(window.location.search);
            return urlParams.get(name);
        }

        // 스크롤링 함수
        function scrollToTag(tagId) {
            var element = document.getElementById(tagId);
            if (element) {
                element.scrollIntoView({
                    behavior: 'smooth'
                });
            }
        }
    </script>
</head>

<body onload="scrollToTag(getQueryParam('id'))">
    <div class="warp">
        <div class="swiper-container">
            <div class="swiper-wrapper top_Img">
                <div class="swiper-slide mySlides fade">
                    <a href="<?= $_SERVER['PHP_SELF'] ?>?id=content_Common"><img src="./data/guide/guide_top_img_1.jpg" alt="guide_top_img_1" style="width: 100%;" /></a>
                </div>
                <div class="swiper-slide mySlides fade">
                    <a href="<?= $_SERVER['PHP_SELF'] ?>?id=content_Maker"><img src="./data/guide/guide_top_img_2.jpg" alt="guide_top_img_2" style="width: 100%;" /></a>
                </div>
                <div class="swiper-slide mySlides fade">
                    <a href="<?= $_SERVER['PHP_SELF'] ?>?id=content_Together"><img src="./data/guide/guide_top_img_3.jpg" alt="guide_top_img_3" style="width: 100%;" /></a>
                </div>
            </div>
            <div class="swiper-pagination"></div>
        </div>
        <div class="top_Nav">
            <div id="content_Common" <?= ($id == 'content_Common' ? 'class="active"' : '') ?>>공통 편</div>
            <div id="content_Maker" <?= ($id == 'content_Maker' ? 'class="active"' : '') ?>>메이커 편</div>
            <div id="content_Together" <?= ($id == 'content_Together' ? 'class="active"' : '') ?>>투게더 편</div>
        </div>
        <div class="content">
            <div class="content_Common <?= ($id == 'content_Common' ? 'active' : '') ?>">
                <img src="./data/guide/common/1.jpg" alt="1.jpg" />
                <a href="javascript:window.flutter_inappwebview.callHandler('push', false, '/edit/profile');"><img src="./data/guide/button/profile_btn.jpg" alt="profile_btn.jpg" /></a>
                <img src="./data/guide/common/2.gif" alt="2.gif" />
                <a href="javascript:window.flutter_inappwebview.callHandler('push', false, '/my/grade');"><img src="./data/guide/button/grade_btn.jpg" alt="grade_btn.jpg" /></a>
                <img src="./data/guide/common/3.jpg" alt="3.jpg" />
                <a href="javascript:window.flutter_inappwebview.callHandler('push', false, '/notice');"><img src="./data/guide/button/notice_btn.jpg" alt="notice_btn.jpg" /></a>
                <img src="./data/guide/common/4.jpg" alt="4.jpg" />
                <a href="javascript:window.flutter_inappwebview.callHandler('push', false, '/fav/location');"><img src="./data/guide/button/fav_btn.jpg" alt="fav_btn.jpg" /></a>
                <img src="./data/guide/common/5.jpg" alt="5.jpg" />
                <img src="./data/guide/common/6.jpg" alt="6.jpg" />
            </div>
            <div class="content_Maker <?= ($id == 'content_Maker' ? 'active' : '') ?>">
                <img src="./data/guide/maker/1.jpg" alt="1.jpg" />
                <img src="./data/guide/maker/2.jpg" alt="2.jpg" />
                <img src="./data/guide/maker/3.jpg" alt="3.jpg" />
                <img src="./data/guide/maker/4.jpg" alt="4.jpg" />
                <img src="./data/guide/maker/5.jpg" alt="5.jpg" />
                <img src="./data/guide/maker/6.jpg" alt="6.jpg" />
                <a href="javascript:window.flutter_inappwebview.callHandler('push', false, '/my/grade');"><img src="./data/guide/button/grade_btn.jpg" alt="grade_btn.jpg" /></a>
            </div>
            <div class="content_Together <?= ($id == 'content_Together' ? 'active' : '') ?>">
                <img src="./data/guide/together/1.jpg" alt="1.jpg" />
                <img src="./data/guide/together/2.jpg" alt="2.jpg" />
                <img src="./data/guide/together/3.jpg" alt="3.jpg" />
                <img src="./data/guide/together/4.jpg" alt="4.jpg" />
                <img src="./data/guide/together/5.jpg" alt="5.jpg" />
            </div>
        </div>
        <div class="footer">
            <div>
                <span>찾는 내용이 없으신가요?</span>
                <a href="javascript:window.flutter_inappwebview.callHandler('push', false, '/user/support');"><img src="./data/guide/button/footer_btn_1.jpg" alt="footer_btn_1.jpg" /></a>
            </div>
        </div>
    </div>
</body>

</html>