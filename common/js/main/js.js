$(document).ready(function(){

  /* 화면 리사이징 */
  let ht = $(window).height();
  $('.full_sc').height(ht);
  $(window).resize(function(){
      let ht = $(window).height();
      $('.full_sc').height(ht);
  })

  // let hw = $(window).width();
  // $('header').width(hw);
  // $(window).resize(function(){
  //     let hw = $(window).width();
  //     $('header').width(hw);
  // })

  let mh = $('.main_t').height();

  $(window).scroll(function(){
    let sc = $(this).scrollTop();
    let windowWidth = $(window).width();  
    
    // 화면이 800px 이상일때 
    if (windowWidth >= 800) {
      // 스크롤 위치에 따른 menu 스타일 변경
      if (sc < mh) {
        $('header').css({'background-color':'', 'border-bottom':'none'})
        $('nav ul li a').css({'color':''})
        $(".menu h1 img").attr("src", "common/img/main/logo.png");
        $(".google").attr("src", "common/img/main/button01_(playstore).svg");
        $(".apple").attr("src", "common/img/main/button02_(appstore).svg");
      }
      if (sc >= mh) {
        $('header').css({'background-color':'var(--white-color-menu)', 'border-bottom':'0.5px solid #BCBCBC99'})
        $('nav ul li a').css({'color':'var(--black-color-000)'})
        $(".menu h1 img").attr("src", "common/img/main/logo_2.png");
        $(".google").attr("src", "common/img/main/button01_2(playstore).svg");
        $(".apple").attr("src", "common/img/main/button02_2(appstore).svg");
      }
      if (sc >= mh + 1300) {
        $('header').css({'background-color':'', 'border-bottom':'none'})
        $('nav ul li a').css({'color':''})
        $(".menu h1 img").attr("src", "common/img/main/logo.png");
        $(".google").attr("src", "common/img/main/button01_(playstore).svg");
        $(".apple").attr("src", "common/img/main/button02_(appstore).svg");
      }
      if (sc >= mh + 3000) {
        $('header').css({'background-color':'var(--white-color-menu)', 'border-bottom':'0.5px solid #BCBCBC99'})
        $('nav ul li a').css({'color':'var(--black-color-000)'})
        $(".menu h1 img").attr("src", "common/img/main/logo_2.png");
        $(".google").attr("src", "common/img/main/button01_2(playstore).svg");
        $(".apple").attr("src", "common/img/main/button02_2(appstore).svg");
      }
    }

    // 화면이 800px 이상이 아닐때(800이하)
    else{
      // 스크롤 위치에 따른 menu 스타일 변경
      if (sc < mh) {
        $('header').css({'background-color':'', 'border-bottom':'none'})
        $('nav ul li a').css({'color':''})
        $(".menu h1 img").attr("src", "common/img/main/logo.png");
        $(".google").attr("src", "common/img/main/button01_(playstore).svg");
        $(".apple").attr("src", "common/img/main/button02_(appstore).svg");
      }
      if (sc >= mh) {
        $('header').css({'background-color':'var(--white-color-menu)', 'border-bottom':'0.5px solid #BCBCBC99'})
        $('nav ul li a').css({'color':'var(--black-color-000)'})
        $(".menu h1 img").attr("src", "common/img/main/logo_2.png");
        $(".google").attr("src", "common/img/main/button01_2(playstore).svg");
        $(".apple").attr("src", "common/img/main/button02_2(appstore).svg");
      }
      if (sc >= mh + 1300) {
        $('header').css({'background-color':'', 'border-bottom':'none'})
        $('nav ul li a').css({'color':''})
        $(".menu h1 img").attr("src", "common/img/main/logo.png");
        $(".google").attr("src", "common/img/main/button01_(playstore).svg");
        $(".apple").attr("src", "common/img/main/button02_(appstore).svg");
      }
      if (sc >= mh + 2950) {
        $('header').css({'background-color':'var(--white-color-menu)', 'border-bottom':'0.5px solid #BCBCBC99'})
        $('nav ul li a').css({'color':'var(--black-color-000)'})
        $(".menu h1 img").attr("src", "common/img/main/logo_2.png");
        $(".google").attr("src", "common/img/main/button01_2(playstore).svg");
        $(".apple").attr("src", "common/img/main/button02_2(appstore).svg");
      }

    }
  });

  // 모바일 메뉴 버튼 설정
  var a = 0;
  $('.menu_btn').click(function(){
  a++; if(a>1)a=0;
  if(a==1){
      $('.sub_menu').css({'display':'block'})
      $('.menu_btn img').removeClass('on')
      $('.menu_btn img').eq(1).addClass('on')
  }
  else{
      $('.sub_menu').css({'display':'none'})
      $('.menu_btn img').removeClass('on')
      $('.menu_btn img').eq(0).addClass('on')
  }
  })


  // 택시비 색상변경 설정(화면 중앙에 addClass값 부여)
  function highlightCenterLi() {
    var $liList = $('.main_m_middle li');
    var windowOffset = $(window).scrollTop() + ($(window).height() / 2);
    
    var closestLi = null;
    var minDistance = Number.POSITIVE_INFINITY;
    $liList.each(function(index) {
        var liOffset = $(this).offset().top;
        var distance = Math.abs(windowOffset - liOffset);
        if (distance < minDistance) {
            closestLi = $(this);
            minDistance = distance;
        }
    });
    $liList.removeClass('highlight');
    closestLi.addClass('highlight');
  }
  $(window).on('scroll', highlightCenterLi);
  const article = document.querySelector('article');
  const moveMe = document.getElementById('moveMe');
  const options = {
    threshold: 0.5 // Intersection ratio at which the callback is triggered
  };
  const callback = (entries, observer) => {
    entries.forEach(entry => {
      // if (entry.isIntersecting) {
      //   moveMe.style.transform = 'translatey(0)';
      //   moveMe.style.opacity = '1';
      // }
    });
  };
  const observer = new IntersectionObserver(callback, options);
  observer.observe(article);




  // mobile 애니메이션 효과
  const phoneElements = document.querySelectorAll('.phone');
  const textElements = document.querySelectorAll('.m_text');
  function isElementInViewport(el) {
      const rect = el.getBoundingClientRect();
      return rect.top <= window.innerHeight && rect.bottom >= 0;
  }
  
  function handleScroll() {

    phoneElements.forEach(phone => {
      if (isElementInViewport(phone)) {
        if (phone.classList.contains('pimg01')) {
            phone.classList.add('visible');
        }
        if (phone.classList.contains('pimg02')) {
            phone.classList.add('visible2');
        }
        if (phone.classList.contains('pimg03')) {
            phone.classList.add('visible3');
        }
        if (phone.classList.contains('pimg04')) {
            phone.classList.add('visible4');
        }
      }
    });

    textElements.forEach(m_text => {
      if (isElementInViewport(m_text)) {
        if (m_text.classList.contains('mtxt01')) {
            m_text.classList.add('perceptible1');
        }
        if (m_text.classList.contains('mtxt02')) {
            m_text.classList.add('perceptible2');
        }
      }
    });

  }
  window.addEventListener('scroll', handleScroll);
  handleScroll();






});