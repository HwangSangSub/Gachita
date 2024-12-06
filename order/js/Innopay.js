/**
 * Innopay 연동 js 스크립트
 * Create 2017.06.23
 * 스크립트 변경 불가
 */
var ediDate='';
var merchantKey;
//var payActionUrl="http://localhost:8080";
var payActionUrl=".";
var device;
var keyStr = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=";

//ediDate 셋팅
function setEdiDate(x) {
	ediDate = x;
}
function setEdiDate(){
	var dateObj = new Date();
	var hours = dateObj.getHours();
	var minutes = dateObj.getMinutes();
	var seconds = dateObj.getSeconds();

	hours = hours ? hours : 0; // the hour '0' should be '12'
	hours = hours < 10 ? '0' + hours : hours;
	minutes = minutes < 10 ? '0' + minutes : minutes;
	seconds = seconds < 10 ? '0' + seconds : seconds;

	var strTime = hours.toString() + minutes.toString() + seconds.toString();

	var month = dateObj.getMonth() + 1;
	month = month < 10 ? '0' + month.toString() : month.toString();
	var date = dateObj.getDate();
	date = date < 10 ? '0' + date.toString() : date.toString();
	ediDate=dateObj.getFullYear()+month+date+strTime;
}
// merchantKey 셋팅
function setMKey(x) {
	merchantKey = x;
}
// payActionUrl 셋팅
function setPayActionUrl(x) {
	payActionUrl = x;
}

/**
 * 결제모듈 호출 함수
 * @returns {Boolean}
 */
function goPay(f) {
	setEdiDate();
	checkDevice(f);
	var dvcStr = f.device.value;
	if(dvcStr=='pc') setLayer();
	merchantKey=frm.MerchantKey.value;
	f.ediDate.value = ediDate;
	if(checkData(f)){
		makeEncKey(f);
		f.action = 'http://pg.innopay.co.kr/pay/interfaceURL.jsp';
		if(f.FORWARD.value == 'Y'){
			var left = (screen.Width - 680)/2;
			var top = (screen.Height - 650)/2;
			var winopts= "left="+left+",top="+top+",toolbar=no,location=no,directories=no, status=no,menubar=no,scrollbars=no, resizable=no,width=680,height=650";			
			var InnopayWin =  window.open("", "payWindow", winopts);
			$(".popup_notice").css('display','block');
			$(".popup_notice .text").center();
	        $('body').css('overflow','hidden');
			//document.charset = "utf-8";
			f.target = "payWindow";
			f.submit();
		}else{
			//document.charset = "utf-8";
			f.submit();
		}
		return false;
	}else{
		return false;
	}
}

function setLayer(){
	var obj = document.body;
	var popDiv = document.createElement('div');
	popDiv.setAttribute('id','infiniPopLayer');
	var inHtml = '';
	inHtml+="<div class='popup_notice'>";
	inHtml+="<div class='dim'></div>";
	inHtml+="<div class='text'>";
	inHtml+="<p>팝업창에서 결제가 진행중입니다.</p><br><span>결제를 변경하시려면 팝업창의 결제를 취소해주세요.</span>";
	inHtml+="</div></div>";
	popDiv.innerHTML=inHtml;
	obj.appendChild(popDiv);
}

/*popup 중앙위치하기*/  
jQuery.fn.center = function(){
  var p_width = this.width();
  var p_height = this.height();
  this.css("top", ($(window).height() / 2 ) - ( p_height / 2));
  this.css("left", ($(window).width() / 2 ) - ( p_width / 2));
  return this;
};
/*
 *  결제 요청 device 구분 function (모바일/PC , 브라우져 구분) 
 */
function checkDevice(frm){
	var UserAgent = navigator.userAgent; 
	// 모바일/PC 구분
	if (UserAgent.match(/iPhone|iPod|Android|Windows CE|BlackBerry|Symbian|Windows Phone|webOS|Opera Mini|Opera Mobi|POLARIS|IEMobile|lgtelecom|nokia|SonyEricsson/i) != null 
		|| UserAgent.match(/LG|SAMSUNG|Samsung/) != null){
		frm.device.value = "mobile";
	}else{
		frm.device.value = "pc";
	}
	// 브라우져 구분
	if( navigator.appName.indexOf("Microsoft") > -1 ) {
		if( navigator.appVersion.indexOf("MSIE 7") > -1 ) {
			frm.BrowserType.value = "MSIE 7";
		}
		else if( navigator.appVersion.indexOf(navigator.appVersion.indexOf( "MSIE 6" ) > -1) ) {
			frm.BrowserType.value = "MSIE 6";
		}
	}
	return;
}

/*
 *  기본 결제 데이터 검증 function
 */
function checkData(frm){
	var BuyerEmail = frm.BuyerEmail.value;
	var Moid = frm.Moid.value;
	var MID = frm.MID.value;
	var Amt = frm.Amt.value;
	// 상점아이디 검증
	if(MID==undefined||MID=='') {
	    alert("상점 MID 를 입력해 주세요.");
	    return false;
	}
	// 결제요청금액 검증
	if(Amt==undefined||Amt=='') {
	    alert("결제요청금액을 입력해 주세요.");
	    return false;
	}
	// 전문생성일시 검증 
	if(ediDate==undefined||ediDate=='') {
	    alert("결제요청일시를 확인해 주세요.");
	    return false;
	}
	// 상점 MID Key 검증 
	if(merchantKey==undefined||merchantKey=='') {
	    alert("상점 Key를 확인해 주세요.");
	    return false;
	}	
	// 메일주소 검증
	// 메일주소 형식 검증
	if(BuyerEmail!=undefined&&BuyerEmail!=''){
		if(!EmailCheck(BuyerEmail)) {
			alert("구매자메일주소가 형식에 맞지 않습니다.");
			return false;
		}	
	}
	// 주문번호 특수문자 체크
	if(isSpecial(Moid)) {
		alert("주문번호에는 특수문자가 허용되지 않습니다.");
		return false;
	}
	return true;
}

/*
 *  거래 검증용 데이터 생성 function 
 */
function makeEncKey(frm){
	var strKey = '';
	var MID = frm.MID.value;
	var Amt = frm.Amt.value;
	strKey = ediDate + MID + Amt + merchantKey;
	frm.EncryptData.value = encode64(MD5(strKey));
}
/*
 *  특수 문자 체크
 */
function isSpecial(checkStr) {
	var checkOK = "~`':;{}[]<>,.!@#$%^&*()_+|\\/?";
	for (var i = 0;  i < checkStr.length;  i++)	{
		ch = checkStr.charAt(i);
		for (var j = 0;  j < checkOK.length;  j++) {
			if (ch == checkOK.charAt(j)) {return true; break;}
		}
	}
	return false;
}
/*
 *  E-Mail 형식 확인
 */
function EmailCheck(arg_v) {
	var	vValue = "";
	if(arg_v.indexOf("@") < 0) return false;
	for(var i = 0; i < arg_v.length; i++) {
		vValue = arg_v.charAt(i);
		if (AlphaCheck(vValue) == false  && NumberCheck(vValue) == false && EmailSpecialCheck(vValue) == false )
			return false;
	}
	return true;
}
/*
 *  영문 판별
 */
function AlphaCheck(arg_v) {
	var alphaStr = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
	if(alphaStr.indexOf(arg_v)<0) return false;
	else return true;
}
/*
 *  숫자 판별
 */
function NumberCheck(arg_v) {
	var numStr = "0123456789";
	if(numStr.indexOf(arg_v)<0) return false;
	else return true;
}
/*
 *  Email 특수 문자 체크
 */
function EmailSpecialCheck(arg_v) {
	var SpecialStr = "_-@.";
	if(SpecialStr.indexOf(arg_v)<0) return false;
	else return true;
}
/*
 * Base64 Encode / Decode 함수
 */
function encode64(input) {
   var output = "";
   var chr1, chr2, chr3;
   var enc1, enc2, enc3, enc4;
   var i = 0;
   do {
      chr1 = input.charCodeAt(i++);
      chr2 = input.charCodeAt(i++);
      chr3 = input.charCodeAt(i++);
      enc1 = chr1 >> 2;
      enc2 = ((chr1 & 3) << 4) | (chr2 >> 4);
      enc3 = ((chr2 & 15) << 2) | (chr3 >> 6);
      enc4 = chr3 & 63;
      if (isNaN(chr2)) {
         enc3 = enc4 = 64;
      } else if (isNaN(chr3)) {
         enc4 = 64;
      }
      output = output + keyStr.charAt(enc1) + keyStr.charAt(enc2) + 
         keyStr.charAt(enc3) + keyStr.charAt(enc4);
   } while (i < input.length);
   return output;
}
/****************************************************************************************
 * 입력필드(사용자가 키보드를 처서 입력하는)의 입력값이 숫자만 들어가도록 할 때 사용된다.
 * 사용예 : <input type="text" name="text" onKeyUp="javascript:numOnly(this,document.frm,true);">
 * 여기서 this는 오브젝트를 뜻하므로 그냥 사용하면 되고, document 다음의 frm 대신에
 * 자신이 사용한 form 이름을 적어준다.
 * 마지막 파라미터로 true,false 를 줄 수 있는데 true로 주면 금액등에 쓰이는 3자리마다 콤마를
 * false 로 주면 그냥 숫자만 입력하게 한다.
 ****************************************************************************************/
function numOnly(obj, frm, isCash) {
	if (event.keyCode == 9 || event.keyCode == 37 || event.keyCode == 39) return;
	var returnValue = "";
	for (var i = 0; i < obj.value.length; i++){
		if (obj.value.charAt(i) >= "0" && obj.value.charAt(i) <= "9"){
			returnValue += obj.value.charAt(i);
		}else{
			returnValue += "";
		}
	}
	if (isCash){
		obj.value = cashReturn(returnValue);
		return;
	}
	obj.focus();
	obj.value = returnValue;
}
/****************************************************************************************
 * 금액을 위한 함수, 코더들은 이 function을 직접 부를 필요 없다. numOnly함수에 마지막
 * 파라미터를 true로 주고 numOnly를 부른다.
 ****************************************************************************************/
function cashReturn(numValue) {
	var cashReturn = "";
	for (var i = numValue.length-1; i >= 0; i--){
		cashReturn = numValue.charAt(i) + cashReturn;
		if (i != 0 && i%3 == numValue.length%3) cashReturn = "," + cashReturn;
	}
	return cashReturn;
}
/**
*
*  MD5 (Message-Digest Algorithm)
*  http://www.webtoolkit.info/
*
**/
 
var MD5 = function (string) {
 
	function RotateLeft(lValue, iShiftBits) {
		return (lValue<<iShiftBits) | (lValue>>>(32-iShiftBits));
	}
 
	function AddUnsigned(lX,lY) {
		var lX4,lY4,lX8,lY8,lResult;
		lX8 = (lX & 0x80000000);
		lY8 = (lY & 0x80000000);
		lX4 = (lX & 0x40000000);
		lY4 = (lY & 0x40000000);
		lResult = (lX & 0x3FFFFFFF)+(lY & 0x3FFFFFFF);
		if (lX4 & lY4) {
			return (lResult ^ 0x80000000 ^ lX8 ^ lY8);
		}
		if (lX4 | lY4) {
			if (lResult & 0x40000000) {
				return (lResult ^ 0xC0000000 ^ lX8 ^ lY8);
			} else {
				return (lResult ^ 0x40000000 ^ lX8 ^ lY8);
			}
		} else {
			return (lResult ^ lX8 ^ lY8);
		}
 	}
 
 	function F(x,y,z) { return (x & y) | ((~x) & z); }
 	function G(x,y,z) { return (x & z) | (y & (~z)); }
 	function H(x,y,z) { return (x ^ y ^ z); }
	function I(x,y,z) { return (y ^ (x | (~z))); }
 
	function FF(a,b,c,d,x,s,ac) {
		a = AddUnsigned(a, AddUnsigned(AddUnsigned(F(b, c, d), x), ac));
		return AddUnsigned(RotateLeft(a, s), b);
	};
 
	function GG(a,b,c,d,x,s,ac) {
		a = AddUnsigned(a, AddUnsigned(AddUnsigned(G(b, c, d), x), ac));
		return AddUnsigned(RotateLeft(a, s), b);
	};
 
	function HH(a,b,c,d,x,s,ac) {
		a = AddUnsigned(a, AddUnsigned(AddUnsigned(H(b, c, d), x), ac));
		return AddUnsigned(RotateLeft(a, s), b);
	};
 
	function II(a,b,c,d,x,s,ac) {
		a = AddUnsigned(a, AddUnsigned(AddUnsigned(I(b, c, d), x), ac));
		return AddUnsigned(RotateLeft(a, s), b);
	};
 
	function ConvertToWordArray(string) {
		var lWordCount;
		var lMessageLength = string.length;
		var lNumberOfWords_temp1=lMessageLength + 8;
		var lNumberOfWords_temp2=(lNumberOfWords_temp1-(lNumberOfWords_temp1 % 64))/64;
		var lNumberOfWords = (lNumberOfWords_temp2+1)*16;
		var lWordArray=Array(lNumberOfWords-1);
		var lBytePosition = 0;
		var lByteCount = 0;
		while ( lByteCount < lMessageLength ) {
			lWordCount = (lByteCount-(lByteCount % 4))/4;
			lBytePosition = (lByteCount % 4)*8;
			lWordArray[lWordCount] = (lWordArray[lWordCount] | (string.charCodeAt(lByteCount)<<lBytePosition));
			lByteCount++;
		}
		lWordCount = (lByteCount-(lByteCount % 4))/4;
		lBytePosition = (lByteCount % 4)*8;
		lWordArray[lWordCount] = lWordArray[lWordCount] | (0x80<<lBytePosition);
		lWordArray[lNumberOfWords-2] = lMessageLength<<3;
		lWordArray[lNumberOfWords-1] = lMessageLength>>>29;
		return lWordArray;
	};
 
	function WordToHex(lValue) {
		var WordToHexValue="",WordToHexValue_temp="",lByte,lCount;
		for (lCount = 0;lCount<=3;lCount++) {
			lByte = (lValue>>>(lCount*8)) & 255;
			WordToHexValue_temp = "0" + lByte.toString(16);
			WordToHexValue = WordToHexValue + WordToHexValue_temp.substr(WordToHexValue_temp.length-2,2);
		}
		return WordToHexValue;
	};
 
	function Utf8Encode(string) {
		string = string.replace(/\r\n/g,"\n");
		var utftext = "";
 
		for (var n = 0; n < string.length; n++) {
 
			var c = string.charCodeAt(n);
 
			if (c < 128) {
				utftext += String.fromCharCode(c);
			}
			else if((c > 127) && (c < 2048)) {
				utftext += String.fromCharCode((c >> 6) | 192);
				utftext += String.fromCharCode((c & 63) | 128);
			}
			else {
				utftext += String.fromCharCode((c >> 12) | 224);
				utftext += String.fromCharCode(((c >> 6) & 63) | 128);
				utftext += String.fromCharCode((c & 63) | 128);
			}
 
		}
 
		return utftext;
	};
 
	var x=Array();
	var k,AA,BB,CC,DD,a,b,c,d;
	var S11=7, S12=12, S13=17, S14=22;
	var S21=5, S22=9 , S23=14, S24=20;
	var S31=4, S32=11, S33=16, S34=23;
	var S41=6, S42=10, S43=15, S44=21;
 
	string = Utf8Encode(string);
 
	x = ConvertToWordArray(string);
 
	a = 0x67452301; b = 0xEFCDAB89; c = 0x98BADCFE; d = 0x10325476;
 
	for (k=0;k<x.length;k+=16) {
		AA=a; BB=b; CC=c; DD=d;
		a=FF(a,b,c,d,x[k+0], S11,0xD76AA478);
		d=FF(d,a,b,c,x[k+1], S12,0xE8C7B756);
		c=FF(c,d,a,b,x[k+2], S13,0x242070DB);
		b=FF(b,c,d,a,x[k+3], S14,0xC1BDCEEE);
		a=FF(a,b,c,d,x[k+4], S11,0xF57C0FAF);
		d=FF(d,a,b,c,x[k+5], S12,0x4787C62A);
		c=FF(c,d,a,b,x[k+6], S13,0xA8304613);
		b=FF(b,c,d,a,x[k+7], S14,0xFD469501);
		a=FF(a,b,c,d,x[k+8], S11,0x698098D8);
		d=FF(d,a,b,c,x[k+9], S12,0x8B44F7AF);
		c=FF(c,d,a,b,x[k+10],S13,0xFFFF5BB1);
		b=FF(b,c,d,a,x[k+11],S14,0x895CD7BE);
		a=FF(a,b,c,d,x[k+12],S11,0x6B901122);
		d=FF(d,a,b,c,x[k+13],S12,0xFD987193);
		c=FF(c,d,a,b,x[k+14],S13,0xA679438E);
		b=FF(b,c,d,a,x[k+15],S14,0x49B40821);
		a=GG(a,b,c,d,x[k+1], S21,0xF61E2562);
		d=GG(d,a,b,c,x[k+6], S22,0xC040B340);
		c=GG(c,d,a,b,x[k+11],S23,0x265E5A51);
		b=GG(b,c,d,a,x[k+0], S24,0xE9B6C7AA);
		a=GG(a,b,c,d,x[k+5], S21,0xD62F105D);
		d=GG(d,a,b,c,x[k+10],S22,0x2441453);
		c=GG(c,d,a,b,x[k+15],S23,0xD8A1E681);
		b=GG(b,c,d,a,x[k+4], S24,0xE7D3FBC8);
		a=GG(a,b,c,d,x[k+9], S21,0x21E1CDE6);
		d=GG(d,a,b,c,x[k+14],S22,0xC33707D6);
		c=GG(c,d,a,b,x[k+3], S23,0xF4D50D87);
		b=GG(b,c,d,a,x[k+8], S24,0x455A14ED);
		a=GG(a,b,c,d,x[k+13],S21,0xA9E3E905);
		d=GG(d,a,b,c,x[k+2], S22,0xFCEFA3F8);
		c=GG(c,d,a,b,x[k+7], S23,0x676F02D9);
		b=GG(b,c,d,a,x[k+12],S24,0x8D2A4C8A);
		a=HH(a,b,c,d,x[k+5], S31,0xFFFA3942);
		d=HH(d,a,b,c,x[k+8], S32,0x8771F681);
		c=HH(c,d,a,b,x[k+11],S33,0x6D9D6122);
		b=HH(b,c,d,a,x[k+14],S34,0xFDE5380C);
		a=HH(a,b,c,d,x[k+1], S31,0xA4BEEA44);
		d=HH(d,a,b,c,x[k+4], S32,0x4BDECFA9);
		c=HH(c,d,a,b,x[k+7], S33,0xF6BB4B60);
		b=HH(b,c,d,a,x[k+10],S34,0xBEBFBC70);
		a=HH(a,b,c,d,x[k+13],S31,0x289B7EC6);
		d=HH(d,a,b,c,x[k+0], S32,0xEAA127FA);
		c=HH(c,d,a,b,x[k+3], S33,0xD4EF3085);
		b=HH(b,c,d,a,x[k+6], S34,0x4881D05);
		a=HH(a,b,c,d,x[k+9], S31,0xD9D4D039);
		d=HH(d,a,b,c,x[k+12],S32,0xE6DB99E5);
		c=HH(c,d,a,b,x[k+15],S33,0x1FA27CF8);
		b=HH(b,c,d,a,x[k+2], S34,0xC4AC5665);
		a=II(a,b,c,d,x[k+0], S41,0xF4292244);
		d=II(d,a,b,c,x[k+7], S42,0x432AFF97);
		c=II(c,d,a,b,x[k+14],S43,0xAB9423A7);
		b=II(b,c,d,a,x[k+5], S44,0xFC93A039);
		a=II(a,b,c,d,x[k+12],S41,0x655B59C3);
		d=II(d,a,b,c,x[k+3], S42,0x8F0CCC92);
		c=II(c,d,a,b,x[k+10],S43,0xFFEFF47D);
		b=II(b,c,d,a,x[k+1], S44,0x85845DD1);
		a=II(a,b,c,d,x[k+8], S41,0x6FA87E4F);
		d=II(d,a,b,c,x[k+15],S42,0xFE2CE6E0);
		c=II(c,d,a,b,x[k+6], S43,0xA3014314);
		b=II(b,c,d,a,x[k+13],S44,0x4E0811A1);
		a=II(a,b,c,d,x[k+4], S41,0xF7537E82);
		d=II(d,a,b,c,x[k+11],S42,0xBD3AF235);
		c=II(c,d,a,b,x[k+2], S43,0x2AD7D2BB);
		b=II(b,c,d,a,x[k+9], S44,0xEB86D391);
		a=AddUnsigned(a,AA);
		b=AddUnsigned(b,BB);
		c=AddUnsigned(c,CC);
		d=AddUnsigned(d,DD);
	}
 
	var temp = WordToHex(a)+WordToHex(b)+WordToHex(c)+WordToHex(d);
 
	return temp.toLowerCase();
};
