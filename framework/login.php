<?php
//-----------------------------
//	Programmer	: SH.Jafarkhani
//	Date		: 94.06
//-----------------------------

require_once 'configurations.inc.php';
set_include_path(get_include_path() . PATH_SEPARATOR . getenv("DOCUMENT_ROOT") . "/generalClasses");
require_once 'PDODataAccess.class.php';
require_once 'PasswordHash.php';

require_once getenv("DOCUMENT_ROOT") . '/framework/session.php';
session::sec_session_start();

if(isset($_POST["UserName"]))
{
	$user = $_POST["UserName"];
	$pass = $_POST["md5Pass"];
	
	$result = session::login($user, $pass);			
	if($result !== true)
	{
		echo $result;
		die();
	}
	
	$_SESSION['USER']["framework"] = true;
	$_SESSION['USER']["portal"] = false;
	
	echo "true";
	die();
}

?>
<html>
  <head>
    <meta http-equiv="content-type" content="text/html; charset=utf-8" />
	<meta http-equiv="expires" content="now">
	<meta http-equiv="pragma" content="no-cache">
    <title>نرم افزار <?= SoftwareName?></title>
	<? require_once 'md5.php'; ?>
	<style>
	  body td div{
		  font-family: tahoma;
	  }
	.headerDiv{
		height: 180px;
		width : 100%;
		background-color: #63d1d0;
		border-bottom: 15px solid #368cbf;
	}
	.footerDiv{
		background-color: #388ebf;
		position: absolute;
		bottom: 0;
		font-family: tahoma;
		font-size: 12px;
		height : 100px;
		width : 100%;
		color : white;		
	}
	.mainDiv{
		width : 533px;	
		right : 35%;
		top : 35%;
		position: absolute;

	}
	.title{
		font-family: tahoma;
		font-size: 14px;
		font-weight: bold;
		color: #5f99c2;
	}
	.textfield {
		border: 1px solid #cacaca;
		font-size: 12px;
		padding: 5px;
		margin-bottom: 10px;
		vertical-align: middle;
		width: 200px;
		font-family: tahoma;
	}
	.wrong input {
		border: 1px solid #9e423e;
	}
	.wrong::after {
		color: #f5443b !important;
		content: "کلمه کاربری اشتباه است";
		padding-right: 6px;
		vertical-align: super;
		font-size: 11px;
	}
	
	.wrong2 input {
		border: 1px solid #9e423e;
	}
	.wrong2::after {
		color: #f5443b !important;
		content: "رمز عبور اشتباه است";
		padding-right: 6px;
		vertical-align: super;
		font-size: 11px;
	}

	.btn{
		background-color: #5f99c2;
		border: 0 none;
		cursor: pointer;
		border-radius: 10px;
		color: white;
		float: right;
		font-size: 12px;
		font-weight: bold;
		height: 30px;
		font-family: tahoma;
	}

	.btn:hover {
		background-color: #63d1d0;
	}
	.forget{
		cursor: pointer;
		color: #5f99c2;
		font-family: tahoma;
		font-size: 11px;
		font-weight: bold;
		margin-top: 10px;
		margin-right: 4px;
	}
	.forget:hover{
		color: #63d1d0;
	}	
	
	#ajax-loading{
		position: fixed;
		top: 0;
		left: 0;
		height:100%;
		width:100%;
		z-index: 9999999;
		background-color : #999;
		opacity: 0.7;
		filter: alpha(opacity=70); /* ie */
		-moz-opacity: 0.7; /* mozilla */    
		display : none;
	}
	#ajax-loading div{
		position: absolute;
		width: 30px;
		height: 30px;
		left: 50%;
		top: 10%;
		margin: 0 0 0 -15px;
		border: 8px solid #fff;
		border-right-color: transparent;
		border-radius: 50%;
		box-shadow: 0 0 25px 2px #eee;
		-webkit-animation: spin 1s linear infinite;
		-moz-animation: spin 1s linear infinite;
		-ms-animation: spin 1s linear infinite;
		-o-animation: spin 1s linear infinite;
		animation: spin 1s linear infinite;
	} 
	#ajax-loading span{
		position: absolute;
		left: 44%;
		top: 20%;
		font-family: tahoma;
		color: white;
		direction : rtl;
		font-weight : bold;
	}
	@-webkit-keyframes spin
	{
		from { -webkit-transform: rotate(0deg); opacity: 0.4; }
		50% { -webkit-transform: rotate(180deg); opacity: 1; }
		to { -webkit-transform: rotate(360deg); opacity: 0.4; }
	}
	@-moz-keyframes spin
	{
		from { -moz-transform: rotate(0deg); opacity: 0.4; }
		50% { -moz-transform: rotate(180deg); opacity: 1; }
		to { -moz-transform: rotate(360deg); opacity: 0.4; }
	}
	@-ms-keyframes spin
	{
		from { -ms-transform: rotate(0deg); opacity: 0.4; }
		50% { -ms-transform: rotate(180deg); opacity: 1; }
		to { -ms-transform: rotate(360deg); opacity: 0.4; }
	}
	@-o-keyframes spin
	{
		from { -o-transform: rotate(0deg); opacity: 0.4; }
		50% { -o-transform: rotate(180deg); opacity: 1; }
		to { -o-transform: rotate(360deg); opacity: 0.4; }
	}
	@keyframes spin
	{
		from { transform: rotate(0deg); opacity: 0.2; }
		50% { transform: rotate(180deg); opacity: 1; }
		to { transform: rotate(360deg); opacity: 0.2; }
	} 
	</style>
	<script>
		function pressing(e)
		{
			var c = (e.keyCode)? e.keyCode: (e.charCode)? e.charCode: e.which;
			if(c == 13)
			{
				loginFN();				
			}
		}
		function loginFN()
		{
			
			if(document.getElementById('UserName').value == "")
			{
				document.getElementById("UserNameDiv").className = "wrong";
				document.getElementById('UserName').focus();
				return;
			}
			if(document.getElementById('password').value == "")
			{
				document.getElementById("PasswordDiv").className = "wrong2";
				document.getElementById('password').focus();
				return;
			}
			
			document.getElementById("UserNameDiv").className = "";
			document.getElementById("PasswordDiv").className = "";
			document.getElementById("md5Pass").value = MD5(document.getElementById('password').value);
			
			var xmlhttp;
			if (window.XMLHttpRequest)
			{
				// code for IE7+, Firefox, Chrome, Opera, Safari
				xmlhttp = new XMLHttpRequest();
			}
			else
			{
				// code for IE6, IE5
				xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
			}
			xmlhttp.onreadystatechange = function()
			{
				if (xmlhttp.readyState==4 && xmlhttp.status==200)
				{
					if(xmlhttp.responseText == "true")
					{
						document.getElementById('UserName').value == "";
						document.getElementById('password').value == "";
						document.getElementById('md5Pass').value == "";
						window.location = "systems.php";
						return;
					}
					error = xmlhttp.responseText;
					document.getElementById("ajax-loading").style.display = "none";
					if(error == "WrongUserName")
						document.getElementById("UserNameDiv").className = "wrong";
					if(error == "WrongPassword")
					{
						document.getElementById("PasswordDiv").className = "wrong2";
						document.getElementById('password').focus();
					}	
					if(error == "TooMuchAttempt")
					{
						document.getElementById("div_lock").style.display = "block";
						document.getElementById("PasswordDiv").remove();
						document.getElementById("UserNameDiv").remove();
						document.getElementById("btn_enter").remove();
						document.getElementById("div_forget").remove();
					}
					if(error == "InActiveUser")
					{
						document.getElementById("div_lock").style.display = "block";
						document.getElementById("div_lock").innerHTML = "شناسه شما غیر فعال شده است. برای اطلاعات بیشتر با صندوق تماس حاصل فرمایید"
						document.getElementById("PasswordDiv").remove();
						document.getElementById("UserNameDiv").remove();
						document.getElementById("btn_enter").remove();
						document.getElementById("div_forget").remove();
					}					
				}
			}
			xmlhttp.open("POST","login.php",true);
			document.getElementById("ajax-loading").style.display = "block";
			xmlhttp.send(new FormData(document.getElementById("mainForm")));
		}		
		
		function BodyLoad(){
			
			document.getElementById('UserName').focus();
		}
	</script> 
  </head>

  <body dir="rtl" style="margin:0"  onkeydown="pressing(event);" onload="return BodyLoad();">
	<div id="ajax-loading"><div></div><span>در حال ورود به سیستم . . .</span></div>
	  <center>
		<div class="headerDiv" align="center">
			<div style="width:800;right : 20%;position: absolute;" align="right"><br><br><img width="180px" src="../framework/icons/LoginLogo.png"></div>
		</div>
		<div align="center">
		<div class="mainDiv">
			<table width="100%"> 
				<tr>
					<td width="200px" style="vertical-align: middle;"><img src="../framework/icons/keys.jpg"</td>
					<td style="vertical-align: top;">
						<div id="loginDIV">
							<form id="mainForm" method="post" autocomplete="off">
								<div class="title" > ورود به پرتال </div>
								<br>
								<div style="font-size: 12px">جهت ورود به پرتال نام کاربری و کلمه عبور خود را وارد کنید.
									درغیر اینصورت با زدن دکمه ثبت نام به پرتال وارد شوید.</div>
								<br>
								<div id="div_lock" style="display:none;color : red;font-size: 12px">به دلیل چندین تلاش ناموفق برای ورود به سیستم، امکان ورود به سیستم برای 15 دقیقه قفل خواهد شد.</div>
								<div id="UserNameDiv"><input autocomplete="off" type="text" class="textfield" name="UserName" id="UserName" 
									placeholder="کلمه کاربری ..." required="required" dir="ltr" /></div>
								<div id="PasswordDiv"><input autocomplete="off" type="password" class="textfield" id="password" 
									placeholder="رمز عبور ..." required="required" dir="ltr"/></div>
								<button id="btn_enter" onclick="loginFN();" type="button" style="width:80px" class="btn  ">ورود</button>
								<div id="div_forget" class="forget">&nbsp;| رمز عبور را فراموش کرده ام </div>
								<input type="hidden" id="md5Pass" name="md5Pass">
							</form>
						</div>										
					</td>
				</tr>
			</table>
		</div>
		</div>
		<div class="footerDiv"> <br><br>
<br>© کلیه حقوق این نرم افزار محفوظ است.</div>
	</center>	  
  </body>
</html>