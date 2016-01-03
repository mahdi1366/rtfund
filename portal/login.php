<?php
//-----------------------------
//	Programmer	: SH.Jafarkhani
//	Date		: 94.06
//-----------------------------

require_once getenv("DOCUMENT_ROOT") . '/framework/configurations.inc.php';
set_include_path(get_include_path() . PATH_SEPARATOR . getenv("DOCUMENT_ROOT") . "/generalClasses");
require_once 'PDODataAccess.class.php';
require_once 'DataAudit.class.php';
require_once getenv("DOCUMENT_ROOT") . '/framework/PasswordHash.php';

require_once getenv("DOCUMENT_ROOT") . '/framework/session.php';
session::sec_session_start();

$return = "";
//------------- register ------------------
if(isset($_POST["email"]))
{
	require_once getenv("DOCUMENT_ROOT") . '/framework/person/persons.class.php';
	
	$user = $_POST["UserName"];
	$pass = $_POST["md5Pass"];
	
	$return = session::register($user, $pass);
	if($return === true)
		header("location: index.php");
}
//------------- login ------------------
else if(isset($_POST["UserName"]))
{
	$user = $_POST["UserName"];
	$pass = $_POST["md5Pass"];
	
	$result = session::login($user, $pass);
	if($result !== true)
	{
		echo $return;
		$return = $result;
	}
	else
	{
		$_SESSION['USER']["framework"] = false;
		$_SESSION['USER']["portal"] = true;
		header("location: index.php");
		die();
	}
}
//------------- forget pass --------------------
else if(isset($_REQUEST["forgetStep1"]))
{
	if(!empty($_POST["ForgetUserName"]))
	{
		echo session::getEmail($_POST["ForgetUserName"], true);
		die();			
	}
	
	echo "WrongUserName";die();
}
else if(isset($_REQUEST["forgetStep2"]))
{
	$email = session::getEmail($_POST["ForgetUserName"]);
	if($email != $_POST["forgetemail"])
	{
		echo "WrongEmail";
		die();	
	}
	session::SendNewPass($_POST["ForgetUserName"]);
	echo "true";
	die();
}
?>
<html>
  <head>
    <meta http-equiv="content-type" content="text/html; charset=utf-8" />
    <title>پرتال <?= SoftwareName?></title>
  	<? require_once 'md5.php'; ?>
	<script>
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
		}
		
		function RegisterFN()
		{
			document.getElementById("md5Pass2").value = MD5(document.getElementById('password1').value);
		}
				
		function BodyLoad(){
			
			document.getElementById('UserName').focus();
			var error = '<?= $return ?>';
			if(error == "WrongUserName")
				document.getElementById("UserNameDiv").className = "wrong";
			if(error == "WrongPassword")
			{
				document.getElementById("PasswordDiv").className = "wrong2";
				document.getElementById("UserName").value = "<?= isset($_POST["UserName"]) ? $_POST["UserName"] : "" ?>";
				document.getElementById('password').focus();
			}	
			if(error == "DuplicateUserName")
			{
				Register(true);
				document.getElementById("UserNameDiv2").className = "wrong";
				<?if(isset($_POST["email"])){?>
					document.getElementById("isReal").checked = ('<?= $_POST["IsReal"]?>' == "YES") ? true : false;
					document.getElementById("noReal").checked = ('<?= $_POST["IsReal"]?>' == "YES") ? false : true;
					
					document.getElementById("fname").value = "<?= $_POST["fname"] ?>";
					document.getElementById("lname").value = "<?= $_POST["lname"] ?>";
					document.getElementById("CompanyName").value = "<?= $_POST["CompanyName"] ?>";
					document.getElementById("email").value = "<?= $_POST["email"] ?>";
					document.getElementById("UserName2").value = "";
					document.getElementById("UserName2").placeholder = "کلمه کاربری تکراری می باشد";
				<?}?>
			}
			if(error == "TooMuchAttempt")
			{
				document.getElementById("loginDescDIV").style.display = "block";
				document.getElementById("loginDIV").style.display = "none";
			}
			
		}
		
		function Register(registerMode){
		
			document.getElementById("loginDIV").style.display = registerMode ? "none" : "block";
			document.getElementById("registerDIV").style.display = registerMode ? "block" : "none";
		}
		
		function ChangePersonType(elem){
			document.getElementById("fname").style.display = elem.value == "YES" ? "block" : "none";
			document.getElementById("lname").style.display = elem.value == "YES" ? "block" : "none";
			document.getElementById("CompanyName").style.display = elem.value == "YES" ? "none" : "block";
			
			document.getElementById("fname").required = elem.value == "YES" ? "required" : "";
			document.getElementById("lname").required = elem.value == "YES" ? "required" : "";
			document.getElementById("CompanyName").required = elem.value == "YES" ? "" : "required";
		}
		
		function ConfirmPassword(input) {
			if (input.value != document.getElementById('password1').value) {
				input.setCustomValidity('Password Must be Matching.');
			} else {
				input.setCustomValidity('');
			}
		}
		
		function ForgetPass(step){
			
			if(step == 0)
			{
				document.getElementById("loginDIV").style.display = "block";
				document.getElementById("forgetDIV").style.display ="none";
				document.getElementById("descDIV").innerHTML = "ابتدا کلمه کاربری خود را وارد کنید" + "<br><br>";
				document.getElementById("ForgetUserName").style.display = "block";
				document.getElementById("loginBTN").style.display = "none";	
				document.getElementById("step1BTN").style.display = "block";	
				document.getElementById("ForgetUserName").value = "";
				document.getElementById("forgetemail").value = "";
				return;
			}
			if(step == 1)
			{
				document.getElementById("loginDIV").style.display = "none";
				document.getElementById("forgetDIV").style.display ="block";
				return;
			}
			if(step == 2)
			{
				xmlhttp.onreadystatechange = function()
				{
					if (xmlhttp.readyState==4 && xmlhttp.status==200)
					{
						document.getElementById("ajax-loading").style.display = "none";
						if(xmlhttp.responseText == "WrongUserName")
						{						
							document.getElementById("errorDIV").innerHTML = "کلمه کاربری وارد شده وجود ندارد";
							return;
						}
						if(xmlhttp.responseText == "EmptyEmail")
						{
							document.getElementById("errorDIV").innerHTML = 
								"ایمیل شما برای ارسال رمز عبور جدید در سیستم ثبت نشده است"+
								"برای بازیابی رمز با صندوق تماس بگیرید ";
							document.getElementById("step1BTN").style.display = "none";
							return;
						}
						document.getElementById("errorDIV").innerHTML = "";
						document.getElementById("ForgetUserName").style.display = "none";
						document.getElementById("descDIV").innerHTML = "ابمیل خود را که مطابق با الگوی زیر می باشد وارد کنید" +
							"<br><br>" + xmlhttp.responseText + "<br><br>";
						document.getElementById("forgetemail").style.display = "block";
						document.getElementById("step1BTN").style.display = "none";
						document.getElementById("step2BTN").style.display = "block";					
					}
				}

				xmlhttp.open("POST","login.php?forgetStep1=true",true);
				document.getElementById("ajax-loading").style.display = "block";
				xmlhttp.send(new FormData(document.getElementById("forgetForm")));
			}
			if(step == 3)
			{				
				xmlhttp.onreadystatechange = function()
				{
					if (xmlhttp.readyState==4 && xmlhttp.status==200)
					{
						document.getElementById("ajax-loading").style.display = "none";
						if(xmlhttp.responseText == "WrongEmail")
						{						
							document.getElementById("errorDIV").innerHTML = "ایمیل وارد شده صحیح نمی باشد";
							return;
						}
						
						document.getElementById("errorDIV").innerHTML = "";
						document.getElementById("forgetemail").style.display = "none";
						document.getElementById("descDIV").innerHTML = "رمز عبور جدید به ایمیل شما ارسال گردید" +
							"<br><br>";
						document.getElementById("step2BTN").style.display = "none";
						document.getElementById("loginBTN").style.display = "block";					
					}
				}

				xmlhttp.open("POST","login.php?forgetStep2=true",true);
				document.getElementById("ajax-loading").style.display = "block";
				xmlhttp.send(new FormData(document.getElementById("forgetForm")));
			}			
		}
		
	</script>
	 <style>
	  body td div{
		  font-family: tahoma;
		  font-size: 12px;
	  }
	.headerDiv{
		height: 100px;
		width : 100%;
		background-color: #88a401;
		border-bottom: 15px solid #314e00;
	}
	.footerDiv{
		background-color: #324e03;
		position: absolute;
		bottom: 0;
		font-family: tahoma;
		font-size: 12px;
		height : 50px;
		width : 100%;
		color : white;		
	}
	.mainDiv{
		width : 533px;	
		right : 35%;
		top : 130px;
		position: absolute;

	}
	.title{
		font-family: tahoma;
		font-size: 14px;
		font-weight: bold;
		color: #74a724;
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
		background-color: #74a724;
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
		background-color: #679caa;
	}
	.forget{
		cursor: pointer;
		color: #74a724;
		font-family: tahoma;
		font-size: 11px;
		font-weight: bold;
		margin-top: 10px;
		margin-right: 4px;
	}
	.forget:hover{
		color: #76c9df;
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
  </head> 
  <body dir="rtl" style="margin:0" onload="return BodyLoad();">
	<center>
		<div class="headerDiv" align="center">
			<div style="width:800;right : 20%;position: absolute;" align="right">
				<img width="130px" src="../framework/icons/LoginLogo.png"></div>
		</div>
		<div align="center">
		<div class="mainDiv">
			<table width="100%">
				<tr>
					<td width="200px" style="vertical-align: middle;"><img src="../framework/icons/keys.jpg"</td>
					<td style="vertical-align: top;">
						
						<div id="loginDescDIV" style="color:red;display:none">
							<br><br>به دلیل تعداد زیاد تلاش ناموفق جهت ورود به سیستم، <br>
							به مدت 10 دقیقه ورود به سیستم برای شما قفل خواهد بود<br>
						</div>
						
						<div id="loginDIV">
							<form method="post" id="MainForm" onsubmit="return loginFN();">
								<div class="title" > ورود به پرتال </div>
								<br>
								<div style="font-size: 12px">جهت ورود به پرتال نام کاربری و کلمه عبور خود را وارد کنید.
									درغیر اینصورت با زدن دکمه ثبت نام به پرتال وارد شوید.</div>
								<br>
								<div id="UserNameDiv"><input type="text" name="UserName" class="textfield" id="UserName" 
									placeholder="کلمه کاربری ..." required="required" dir="ltr" /></div>
								<div id="PasswordDiv"><input onkeydown="pressing(event);" type="password" class="textfield" id="password" 
									placeholder="رمز عبور ..." required="required" dir="ltr"/></div>
								<button type="submit" style="width:80px" class="btn  ">ورود</button>
								<div onclick="ForgetPass(1);" class="forget">&nbsp;| رمز عبور را فراموش کرده ام </div>
								<br><div onclick="Register(true)" class="forget">ثبت نام در سامانه</div>
								<input type="hidden" id="md5Pass" name="md5Pass">
							</form>
						</div>
						<div  id="registerDIV" style="display:none;" align="right">
							<div class="title">ثبت نام در پرتال</div>
							<form method="post" id="MainForm2" onsubmit="return RegisterFN();">
								<div style="margin-bottom:10px;direction:rtl;font-size: 13px">
									<input type="radio" id="isReal" name="IsReal" value="YES" onclick="ChangePersonType(this);" checked> شخص حقیقی
									<input type="radio" id="noReal" name="IsReal" value="NO" onclick="ChangePersonType(this);" value="LEGAL"> شخص حقوقی
								</div>
								<input type="text" name="fname" class="textfield" id="fname" placeholder="نام ..." required="required" dir="rtl"/>
								<input type="text" name="lname" class="textfield" id="lname" placeholder="نام خانوادگی ..." required="required" dir="rtl"/>
								<input type="text" name="CompanyName" style="display:none" class="textfield" id="CompanyName" 
									   placeholder="نام شرکت ..." dir="rtl"/>
								<input type="email" name="email" id="email" class="textfield" placeholder="پست الکترونیک ..." required="required" dir="ltr"/>
								<div id="UserNameDiv2"><input type="text" id="UserName2" name="UserName" class="textfield" 
										placeholder="کلمه کاربری ..." required="required" dir="ltr"/></div>
								<input type="password" class="textfield" id="password1" placeholder="رمز عبور ..." required="required" dir="ltr"/>
								<input type="password" class="textfield" id="password2" placeholder="تکرار رمز عبور ..." required="required" 
									   dir="ltr" oninput="ConfirmPassword(this)"/><br>
								<input type="hidden" id="md5Pass2" name="md5Pass">
								<button type="submit" style="float:right;width:100px" class="btn"> ثبت نام </button>
								<button type="button" onclick="Register(false)" class="btn" style="width: 58px;margin-right:40px">بازگشت</button>
							</form>
						</div>	
						<div id="ajax-loading"><div></div><span>در حال انجام عملیات . . .</span></div>
						<div id="forgetDIV" style="display:none">
							<form id="forgetForm">
								<div style="font-size: 12px" id="descDIV">ابتدا کلمه کاربری خود را وارد کنید
								<br><br></div>
								<input type="text" id="ForgetUserName" name="ForgetUserName" class="textfield" 
								placeholder="کلمه کاربری ..." required="required" dir="ltr"/>
								<input type="text" id="forgetemail" name="forgetemail" style="display:none" class="textfield" 
										placeholder="ایمیل ..." dir="ltr"/>
								<div id="errorDIV" style="color:red;font-size: 12px"></div>	<br>								
							<button type="button" onclick="ForgetPass(2)" style="float:right;width:100px" id="step1BTN" class="btn"> مرحله بعد </button>
							<button type="button" onclick="ForgetPass(3)" style="float:right;width:100px;display:none" id="step2BTN" class="btn"> ارسال ایمیل </button>
							<button type="button" onclick="ForgetPass(0)" style="float:right;width:100px;display:none" id="loginBTN" class="btn"> ورود </button>
							
							</form>
						</div>
					</td>
				</tr>
			</table>
		</div>
		</div>
		<div class="footerDiv">
<br>© کلیه حقوق این نرم افزار محفوظ است.</div>
	</center>	  
</body>
</html>