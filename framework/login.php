<?php
//-----------------------------
//	Programmer	: SH.Jafarkhani
//	Date		: 94.06
//-----------------------------

ini_set("display_errors", "On");

require_once 'configurations.inc.php';
set_include_path(get_include_path() . PATH_SEPARATOR . getenv("DOCUMENT_ROOT") . "/generalClasses");
require_once 'PDODataAccess.class.php';
require_once 'PasswordHash.php';

session_start();

$return = "";

if(isset($_POST["UserName"]))
{
	
	require_once 'PasswordHash.php';
	
	$user = $_POST["UserName"];
	$pass = $_POST["md5Pass"];
	
	$temp = PdoDataAccess::runquery("select * from FRW_persons where userID=?", array($user));
	if(count($temp) == 0)
	{
		$return = "WrongUserName";
	}
	else
	{
		// Base-2 logarithm of the iteration count used for password stretching
		$hash_cost_log2 = 8;	
		$hasher = new PasswordHash($hash_cost_log2, true);
		if (!$hasher->CheckPassword($pass, $temp[0]["UserPass"])) {
		
			$return = "WrongPassword";		
		}
		else
		{
		
			$_SESSION['USER'] = $temp[0];
			$_SESSION['USER']["fullname"] = $temp[0]["fname"] . " " . $temp[0]["lname"];
			//..........................................................
			if (!empty($_SERVER['HTTP_X_FORWARDED_FOR']))
				if ( strlen($_SERVER['HTTP_X_FORWARDED_FOR']) > 15 )
					$_SESSION['LIPAddress'] = substr($_SERVER['HTTP_X_FORWARDED_FOR'] , 0,strpos($_SERVER['HTTP_X_FORWARDED_FOR'],','));
				else
					$_SESSION['LIPAddress'] = ($_SERVER['HTTP_X_FORWARDED_FOR']);
			else
				$_SESSION['LIPAddress'] = $_SERVER['REMOTE_ADDR'];
			//..........................................................
			header("location: systems.php");
		}
	}
}

?>
<html>
  <head>
    <meta http-equiv="content-type" content="text/html; charset=utf-8" />
    <title>ورود به سیستم</title>
    <style>
      
		.btn { display: inline-block; *display: inline; *zoom: 1; padding: 4px 10px 4px; margin-bottom: 0; font-size: 13px; line-height: 18px; color: #333333; text-align: center;text-shadow: 0 1px 1px rgba(255, 255, 255, 0.75); vertical-align: middle; background-color: #f5f5f5; background-image: -moz-linear-gradient(top, #ffffff, #e6e6e6); background-image: -ms-linear-gradient(top, #ffffff, #e6e6e6); background-image: -webkit-gradient(linear, 0 0, 0 100%, from(#ffffff), to(#e6e6e6)); background-image: -webkit-linear-gradient(top, #ffffff, #e6e6e6); background-image: -o-linear-gradient(top, #ffffff, #e6e6e6); background-image: linear-gradient(top, #ffffff, #e6e6e6); background-repeat: repeat-x; filter: progid:dximagetransform.microsoft.gradient(startColorstr=#ffffff, endColorstr=#e6e6e6, GradientType=0); border-color: #e6e6e6 #e6e6e6 #e6e6e6; border-color: rgba(0, 0, 0, 0.1) rgba(0, 0, 0, 0.1) rgba(0, 0, 0, 0.25); border: 1px solid #e6e6e6; -webkit-border-radius: 4px; -moz-border-radius: 4px; border-radius: 4px; -webkit-box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.2), 0 1px 2px rgba(0, 0, 0, 0.05); -moz-box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.2), 0 1px 2px rgba(0, 0, 0, 0.05); box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.2), 0 1px 2px rgba(0, 0, 0, 0.05); cursor: pointer; *margin-left: .3em; }
		.btn:hover, .btn:active, .btn.active, .btn.disabled, .btn[disabled] { background-color: #e6e6e6; }
		.btn-large { padding: 9px 14px; font-size: 15px; line-height: normal; -webkit-border-radius: 5px; -moz-border-radius: 5px; border-radius: 5px; }
		.btn:hover { color: #333333; text-decoration: none; background-color: #e6e6e6; background-position: 0 -15px; -webkit-transition: background-position 0.1s linear; -moz-transition: background-position 0.1s linear; -ms-transition: background-position 0.1s linear; -o-transition: background-position 0.1s linear; transition: background-position 0.1s linear; }
		.btn-primary, .btn-primary:hover { text-shadow: 0 -1px 0 rgba(0, 0, 0, 0.25); color: #ffffff; }
		.btn-primary.active { color: rgba(255, 255, 255, 0.75); }
		.btn-primary { background-color: #4a77d4; background-image: -moz-linear-gradient(top, #6eb6de, #4a77d4); background-image: -ms-linear-gradient(top, #6eb6de, #4a77d4); background-image: -webkit-gradient(linear, 0 0, 0 100%, from(#6eb6de), to(#4a77d4)); background-image: -webkit-linear-gradient(top, #6eb6de, #4a77d4); background-image: -o-linear-gradient(top, #6eb6de, #4a77d4); background-image: linear-gradient(top, #6eb6de, #4a77d4); background-repeat: repeat-x; filter: progid:dximagetransform.microsoft.gradient(startColorstr=#6eb6de, endColorstr=#4a77d4, GradientType=0);  border: 1px solid #3762bc; text-shadow: 1px 1px 1px rgba(0,0,0,0.4); box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.2), 0 1px 2px rgba(0, 0, 0, 0.5); }
		.btn-primary:hover, .btn-primary:active, .btn-primary.active, .btn-primary.disabled, .btn-primary[disabled] { filter: none; background-color: #4a77d4; }
		.btn-block { width: 100%; display:block; }

		* { -webkit-box-sizing:border-box; -moz-box-sizing:border-box; -ms-box-sizing:border-box; -o-box-sizing:border-box; box-sizing:border-box; }

		html { width: 100%; height:100%; overflow:hidden; }

		body { 
			width: 100%;
			height:100%;
			font-family: 'Open Sans', sans-serif;
			/*background: #092756;
			background: -moz-radial-gradient(0% 100%, ellipse cover, rgba(104,128,138,.4) 10%,rgba(138,114,76,0) 40%),-moz-linear-gradient(top,  rgba(57,173,219,.25) 0%, rgba(42,60,87,.4) 100%), -moz-linear-gradient(-45deg,  #670d10 0%, #092756 100%);
			background: -webkit-radial-gradient(0% 100%, ellipse cover, rgba(104,128,138,.4) 10%,rgba(138,114,76,0) 40%), -webkit-linear-gradient(top,  rgba(57,173,219,.25) 0%,rgba(42,60,87,.4) 100%), -webkit-linear-gradient(-45deg,  #670d10 0%,#092756 100%);
			background: -o-radial-gradient(0% 100%, ellipse cover, rgba(104,128,138,.4) 10%,rgba(138,114,76,0) 40%), -o-linear-gradient(top,  rgba(57,173,219,.25) 0%,rgba(42,60,87,.4) 100%), -o-linear-gradient(-45deg,  #670d10 0%,#092756 100%);
			background: -ms-radial-gradient(0% 100%, ellipse cover, rgba(104,128,138,.4) 10%,rgba(138,114,76,0) 40%), -ms-linear-gradient(top,  rgba(57,173,219,.25) 0%,rgba(42,60,87,.4) 100%), -ms-linear-gradient(-45deg,  #670d10 0%,#092756 100%);
			background: -webkit-radial-gradient(0% 100%, ellipse cover, rgba(104,128,138,.4) 10%,rgba(138,114,76,0) 40%), linear-gradient(to bottom,  rgba(57,173,219,.25) 0%,rgba(42,60,87,.4) 100%), linear-gradient(135deg,  #670d10 0%,#092756 100%);
			filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='#3E1D6D', endColorstr='#092756',GradientType=1 );*/
			background-image : url('icons/bg.jpg');
		}
		.login { 
			background: rgba(255, 255, 255, 0.3) none repeat scroll 0 0;
			border-radius: 20px;
			height: 350px;
			left: 50%;
			margin: -200px 0 0 -200px;
			padding: 40px;
			position: absolute;
			top: 50%;
			width: 400px;
		}
		.login h1 { color: #fff; text-shadow: 0 0 10px rgba(0,0,0,0.3); letter-spacing:1px; text-align:center; }

		input { 
			width: 100%; 
			margin-bottom: 10px; 
			background: rgba(0,0,0,0.3);
			border: none;
			outline: none;
			padding: 10px;
			font-size: 13px;
			color: #fff;
			text-shadow: 1px 1px 1px rgba(0,0,0,0.3);
			border: 1px solid rgba(0,0,0,0.3);
			border-radius: 4px;
			box-shadow: inset 0 -5px 45px rgba(100,100,100,0.2), 0 1px 1px rgba(255,255,255,0.2);
			-webkit-transition: box-shadow .5s ease;
			-moz-transition: box-shadow .5s ease;
			-o-transition: box-shadow .5s ease;
			-ms-transition: box-shadow .5s ease;
			transition: box-shadow .5s ease;
			vertical-align: middle;
		}
		
		.wrong input{
			width: 90%; 
			border: 1px solid #9E423E;
		}
		.wrong::after {
			content : url(icons/cross.png);
			padding-left: 6px;
			color: #f5443b !important;
		}
		
		input:focus { box-shadow: inset 0 -5px 45px rgba(100,100,100,0.4), 0 1px 1px rgba(255,255,255,0.2); }
    </style>
    <script>
	!function(){function e(e,r){return[].slice.call((r||document).querySelectorAll(e))}if(window.addEventListener){var r=window.StyleFix={link:function(e){try{if("stylesheet"!==e.rel||e.hasAttribute("data-noprefix"))return}catch(t){return}var n,i=e.href||e.getAttribute("data-href"),a=i.replace(/[^\/]+$/,""),o=(/^[a-z]{3,10}:/.exec(a)||[""])[0],s=(/^[a-z]{3,10}:\/\/[^\/]+/.exec(a)||[""])[0],l=/^([^?]*)\??/.exec(i)[1],u=e.parentNode,p=new XMLHttpRequest;p.onreadystatechange=function(){4===p.readyState&&n()},n=function(){var t=p.responseText;if(t&&e.parentNode&&(!p.status||p.status<400||p.status>600)){if(t=r.fix(t,!0,e),a){t=t.replace(/url\(\s*?((?:"|')?)(.+?)\1\s*?\)/gi,function(e,r,t){return/^([a-z]{3,10}:|#)/i.test(t)?e:/^\/\//.test(t)?'url("'+o+t+'")':/^\//.test(t)?'url("'+s+t+'")':/^\?/.test(t)?'url("'+l+t+'")':'url("'+a+t+'")'});var n=a.replace(/([\\\^\$*+[\]?{}.=!:(|)])/g,"\\$1");t=t.replace(RegExp("\\b(behavior:\\s*?url\\('?\"?)"+n,"gi"),"$1")}var i=document.createElement("style");i.textContent=t,i.media=e.media,i.disabled=e.disabled,i.setAttribute("data-href",e.getAttribute("href")),u.insertBefore(i,e),u.removeChild(e),i.media=e.media}};try{p.open("GET",i),p.send(null)}catch(t){"undefined"!=typeof XDomainRequest&&(p=new XDomainRequest,p.onerror=p.onprogress=function(){},p.onload=n,p.open("GET",i),p.send(null))}e.setAttribute("data-inprogress","")},styleElement:function(e){if(!e.hasAttribute("data-noprefix")){var t=e.disabled;e.textContent=r.fix(e.textContent,!0,e),e.disabled=t}},styleAttribute:function(e){var t=e.getAttribute("style");t=r.fix(t,!1,e),e.setAttribute("style",t)},process:function(){e("style").forEach(StyleFix.styleElement),e("[style]").forEach(StyleFix.styleAttribute)},register:function(e,t){(r.fixers=r.fixers||[]).splice(void 0===t?r.fixers.length:t,0,e)},fix:function(e,t,n){for(var i=0;i<r.fixers.length;i++)e=r.fixers[i](e,t,n)||e;return e},camelCase:function(e){return e.replace(/-([a-z])/g,function(e,r){return r.toUpperCase()}).replace("-","")},deCamelCase:function(e){return e.replace(/[A-Z]/g,function(e){return"-"+e.toLowerCase()})}};!function(){setTimeout(function(){},10),document.addEventListener("DOMContentLoaded",StyleFix.process,!1)}()}}(),function(e){function r(e,r,n,i,a){if(e=t[e],e.length){var o=RegExp(r+"("+e.join("|")+")"+n,"gi");a=a.replace(o,i)}return a}if(window.StyleFix&&window.getComputedStyle){var t=window.PrefixFree={prefixCSS:function(e,n){var i=t.prefix;if(t.functions.indexOf("linear-gradient")>-1&&(e=e.replace(/(\s|:|,)(repeating-)?linear-gradient\(\s*(-?\d*\.?\d*)deg/gi,function(e,r,t,n){return r+(t||"")+"linear-gradient("+(90-n)+"deg"})),e=r("functions","(\\s|:|,)","\\s*\\(","$1"+i+"$2(",e),e=r("keywords","(\\s|:)","(\\s|;|\\}|$)","$1"+i+"$2$3",e),e=r("properties","(^|\\{|\\s|;)","\\s*:","$1"+i+"$2:",e),t.properties.length){var a=RegExp("\\b("+t.properties.join("|")+")(?!:)","gi");e=r("valueProperties","\\b",":(.+?);",function(e){return e.replace(a,i+"$1")},e)}return n&&(e=r("selectors","","\\b",t.prefixSelector,e),e=r("atrules","@","\\b","@"+i+"$1",e)),e=e.replace(RegExp("-"+i,"g"),"-"),e=e.replace(/-\*-(?=[a-z]+)/gi,t.prefix)},property:function(e){return(t.properties.indexOf(e)?t.prefix:"")+e},value:function(e){return e=r("functions","(^|\\s|,)","\\s*\\(","$1"+t.prefix+"$2(",e),e=r("keywords","(^|\\s)","(\\s|$)","$1"+t.prefix+"$2$3",e)},prefixSelector:function(e){return e.replace(/^:{1,2}/,function(e){return e+t.prefix})},prefixProperty:function(e,r){var n=t.prefix+e;return r?StyleFix.camelCase(n):n}};!function(){var e={},r=[],n=getComputedStyle(document.documentElement,null),i=document.createElement("div").style,a=function(t){if("-"===t.charAt(0)){r.push(t);var n=t.split("-"),i=n[1];for(e[i]=++e[i]||1;n.length>3;){n.pop();var a=n.join("-");o(a)&&-1===r.indexOf(a)&&r.push(a)}}},o=function(e){return StyleFix.camelCase(e)in i};if(n.length>0)for(var s=0;s<n.length;s++)a(n[s]);else for(var l in n)a(StyleFix.deCamelCase(l));var u={uses:0};for(var p in e){var f=e[p];u.uses<f&&(u={prefix:p,uses:f})}t.prefix="-"+u.prefix+"-",t.Prefix=StyleFix.camelCase(t.prefix),t.properties=[];for(var s=0;s<r.length;s++){var l=r[s];if(0===l.indexOf(t.prefix)){var c=l.slice(t.prefix.length);o(c)||t.properties.push(c)}}"Ms"!=t.Prefix||"transform"in i||"MsTransform"in i||!("msTransform"in i)||t.properties.push("transform","transform-origin"),t.properties.sort()}(),function(){function e(e,r){return i[r]="",i[r]=e,!!i[r]}var r={"linear-gradient":{property:"backgroundImage",params:"red, teal"},calc:{property:"width",params:"1px + 5%"},element:{property:"backgroundImage",params:"#foo"},"cross-fade":{property:"backgroundImage",params:"url(a.png), url(b.png), 50%"}};r["repeating-linear-gradient"]=r["repeating-radial-gradient"]=r["radial-gradient"]=r["linear-gradient"];var n={initial:"color","zoom-in":"cursor","zoom-out":"cursor",box:"display",flexbox:"display","inline-flexbox":"display",flex:"display","inline-flex":"display",grid:"display","inline-grid":"display","min-content":"width"};t.functions=[],t.keywords=[];var i=document.createElement("div").style;for(var a in r){var o=r[a],s=o.property,l=a+"("+o.params+")";!e(l,s)&&e(t.prefix+l,s)&&t.functions.push(a)}for(var u in n){var s=n[u];!e(u,s)&&e(t.prefix+u,s)&&t.keywords.push(u)}}(),function(){function r(e){return a.textContent=e+"{}",!!a.sheet.cssRules.length}var n={":read-only":null,":read-write":null,":any-link":null,"::selection":null},i={keyframes:"name",viewport:null,document:'regexp(".")'};t.selectors=[],t.atrules=[];var a=e.appendChild(document.createElement("style"));for(var o in n){var s=o+(n[o]?"("+n[o]+")":"");!r(s)&&r(t.prefixSelector(s))&&t.selectors.push(o)}for(var l in i){var s=l+" "+(i[l]||"");!r("@"+s)&&r("@"+t.prefix+s)&&t.atrules.push(l)}e.removeChild(a)}(),t.valueProperties=["transition","transition-property"],e.className+=" "+t.prefix,StyleFix.register(t.prefixCSS)}}(document.documentElement);
	</script>
	<? require_once 'md5.php'; ?>
	<script>
		function pressing(e)
		{
			var c = (e.keyCode)? e.keyCode: (e.charCode)? e.charCode: e.which;
			if(c == 13)
			{
				loginFN();
				document.getElementById('MainForm').submit();
				return false;
			}
		}
		function loginFN()
		{
			document.getElementById("md5Pass").value = MD5(document.getElementById('password').value);
			document.getElementById('password').value = '***************';
		}
		
		function BodyLoad(){
			
			document.getElementById('UserName').focus();
			var error = '<?= $return ?>';
			if(error == "WrongUserName")
				document.getElementById("UserNameDiv").className = "wrong";
			if(error == "WrongPassword")
			{
				document.getElementById("PasswordDiv").className = "wrong";
				document.getElementById("UserName").value = "<?= isset($_POST["UserName"]) ? $_POST["UserName"] : "" ?>";
				document.getElementById('password').focus();
			}	
		}
	</script>
  </head>

  <body onkeydown="pressing(event);" onload="return BodyLoad();">

    <div class="login">
		<div style="color: white; font-family: tahoma; font-size: 14px; font-weight: bold; text-align: center; line-height: 30px; padding-bottom: 30px;">نرم افزار جامع 
		<br>صندوق پژوهش و فناوری خراسان رضوی</div>
		<form method="post" id="MainForm" onsubmit="return loginFN();">
			<div id="UserNameDiv"><input type="text" name="UserName" id="UserName" placeholder="کلمه کاربری ..." required="required" class="wrong" /></div>
			<div id="PasswordDiv"><input type="password" name="password" id="password" placeholder="رمز عبور ..." required="required" /></div>
			<button type="submit" class="btn btn-primary btn-block btn-large ">ورود</button>
			<input type="hidden" id="md5Pass" name="md5Pass">
		</form>
	</div>
  </body>
</html>