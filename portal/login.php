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

session_start();

$return = "";
//------------- register ------------------
if(isset($_POST["email"]))
{
	require_once getenv("DOCUMENT_ROOT") . '/person/persons.class.php';
	
	$user = $_POST["UserName"];
	$pass = $_POST["md5Pass"];
	
	$temp = PdoDataAccess::runquery("select * from BSC_persons where UserName=?", array($user));
	if(count($temp) > 0)
	{
		$return = "DuplicateUserName";
	}
	else
	{
		$hash_cost_log2 = 8;	
		$hasher = new PasswordHash($hash_cost_log2, true);
		
		$obj = new BSC_persons();
		PdoDataAccess::FillObjectByArray($obj, $_POST);
		$obj->UserPass = $hasher->HashPassword($pass);
		$obj->IsCustomer = "YES";
		$obj->AddPerson();
		
		$temp = PdoDataAccess::runquery("select * from BSC_persons where UserName=?", array($user));
		$_SESSION['USER'] = $temp[0];
		$_SESSION['USER']["framework"] = true;
		$_SESSION['USER']["portal"] = true;
		//..........................................................
		if (!empty($_SERVER['HTTP_X_FORWARDED_FOR']))
			if ( strlen($_SERVER['HTTP_X_FORWARDED_FOR']) > 15 )
				$_SESSION['LIPAddress'] = substr($_SERVER['HTTP_X_FORWARDED_FOR'] , 0,strpos($_SERVER['HTTP_X_FORWARDED_FOR'],','));
			else
				$_SESSION['LIPAddress'] = ($_SERVER['HTTP_X_FORWARDED_FOR']);
		else
			$_SESSION['LIPAddress'] = $_SERVER['REMOTE_ADDR'];
		//..........................................................
		header("location: index.php");
		
	}
}
//------------- login ------------------
else if(isset($_POST["UserName"]))
{
	$user = $_POST["UserName"];
	$pass = $_POST["md5Pass"];
	
	$temp = PdoDataAccess::runquery("select * from BSC_persons where UserName=?", array($user));
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
			$_SESSION['USER']["framework"] = false;
			$_SESSION['USER']["portal"] = true;
			//..........................................................
			if (!empty($_SERVER['HTTP_X_FORWARDED_FOR']))
				if ( strlen($_SERVER['HTTP_X_FORWARDED_FOR']) > 15 )
					$_SESSION['LIPAddress'] = substr($_SERVER['HTTP_X_FORWARDED_FOR'] , 0,strpos($_SERVER['HTTP_X_FORWARDED_FOR'],','));
				else
					$_SESSION['LIPAddress'] = ($_SERVER['HTTP_X_FORWARDED_FOR']);
			else
				$_SESSION['LIPAddress'] = $_SERVER['REMOTE_ADDR'];
			//..........................................................
			header("location: index.php");
		}
	}
}

?>
<html>
  <head>
    <meta http-equiv="content-type" content="text/html; charset=utf-8" />
    <title>پرتال <?= SoftwareName?></title>
    <style>
      
	html {
		height: 100%;
		overflow: hidden;
		width: 100%;
	}
	body {
		height: 100%;
		width: 100%;
		font-family: tahoma;
		font-size : 12px;
	}
	.login {
		background-color: #e9e9e9;
		border: 0;
		border-radius: 30px;
		left: 45%;
		margin: -200px 0 0 -200px;
		position: absolute;
		top: 45%;
		width: 440px;
		box-shadow: 2px 2px 5px #c1c1c1;
	}
	.headerText {
		color: #f44b38;
		font-family: tahoma;
		font-weight: bold;
		padding-bottom: 20px;
		text-align: center;
	}
	.textfield {
		background-color: #cfcfcf;
		border: 1px solid #cacaca;
		border-radius: 25px;
		color: #555;
		font-size: 13px;
		margin-bottom: 10px;
		padding: 10px;
		vertical-align: middle;
		width: 100%;
	}
	.wrong input {
		border: 1px solid #9e423e;
		width: 90%;
	}
	.wrong::after {
		color: #f5443b !important;
		content: url("ext/cross.png");
		padding-left: 6px;
	}
	
	.btn{
		background-color: #f45b4b;
		border: 0 none;
		cursor: pointer;
		border-radius: 25px;
		color: white;
		float: right;
		font-size: 14px;
		font-weight: bold;
		height: 40px;
		width: 100px;
	}
	.btn2{
		background-color: #f45b4b;
		border: 0 none;
		cursor: pointer;
		border-radius: 25px;
		color: white;
		float: right;
		font-size: 14px;
		font-weight: bold;
		height: 35px;
		width: 150px;
		float : left;
	}
	.btn:hover , .btn2:hover{
		background-color: #28d8d8;
	}
	.forget{
		cursor: pointer;
		color: #e84f3d;
		float: right;
		font-family: tahoma;
		font-size: 11px;
		font-weight: bold;
		height: 40px;
		margin-right: 4px;
		margin-top: 13px;
	}
	.forget:hover{
		color: #28d8d8;
	}	
	.footer{
		background-color: #f45b4b;
		border-radius: 0 0 30px 30px;
		border-top: 1px solid #a72d1e;
		color: white;
		font-family: tahoma;
		font-size: 14px;
		font-weight: bold;
		height: 86px;
		line-height: 25px;
		margin-top: 10px;
		padding-right: 20px;
		padding-top: 20px;
		text-align: right;
		width: 420px;
	}
    </style>
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
				document.getElementById("PasswordDiv").className = "wrong";
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
					document.getElementById("company").value = "<?= $_POST["company"] ?>";
					document.getElementById("email").value = "<?= $_POST["email"] ?>";
					document.getElementById("UserName2").value = "";
					document.getElementById("UserName2").placeholder = "کلمه کاربری تکراری می باشد";
				<?}?>
			}
			
		}
		
		function Register(registerMode){
		
			document.getElementById("loginDIV").style.display = registerMode ? "none" : "block";
			document.getElementById("registerDIV").style.display = registerMode ? "block" : "none";
		}
		
		function ChangePersonType(elem){
			document.getElementById("fname").style.display = elem.value == "YES" ? "block" : "none";
			document.getElementById("lname").style.display = elem.value == "YES" ? "block" : "none";
			document.getElementById("company").style.display = elem.value == "YES" ? "none" : "block";
			
			document.getElementById("fname").required = elem.value == "YES" ? "required" : "";
			document.getElementById("lname").required = elem.value == "YES" ? "required" : "";
			document.getElementById("company").required = elem.value == "YES" ? "" : "required";
		}
		
		function ConfirmPassword(input) {
			if (input.value != document.getElementById('password1').value) {
				input.setCustomValidity('Password Must be Matching.');
			} else {
				input.setCustomValidity('');
			}
		}
	</script>
  </head>

  <body onkeydown="pressing(event);" onload="return BodyLoad();">

    <div class="login">
		<div  id="loginDIV" style="padding: 40px 40px 50px;">
			<div class="headerText">ورود به پرتال</div>
			<form method="post" id="MainForm" onsubmit="return loginFN();">
				<div id="UserNameDiv"><input type="text" name="UserName" class="textfield" id="UserName" placeholder="کلمه کاربری ..." required="required" /></div>
				<div id="PasswordDiv"><input type="password" class="textfield" id="password" placeholder="رمز عبور ..." required="required" /></div>
				<button type="submit" class="btn  ">ورود</button>
				<div class="forget"> رمز عبور را فراموش کرده ام |</div>
				<input type="hidden" id="md5Pass" name="md5Pass">
				<br><br>
				<button type="button" onclick="Register(true)" class="btn2">ثبت نام در سامانه</button>
			</form>
		</div>
		
		<div  id="registerDIV" style="display:none;padding: 40px 40px 30px;" align="right">
			<div class="headerText">ثبت نام در پرتال</div>
			<form method="post" id="MainForm2" onsubmit="return RegisterFN();">
				<div style="margin-bottom:10px;direction:rtl">
					<input type="radio" id="isReal" name="IsReal" value="YES" onclick="ChangePersonType(this);" checked> حقیقی
					<input type="radio" id="noReal" name="IsReal" value="NO" onclick="ChangePersonType(this);" value="LEGAL"> حقوقی
				</div>
				<input type="text" name="fname" class="textfield" id="fname" placeholder="نام ..." required="required" dir="rtl"/>
				<input type="text" name="lname" class="textfield" id="lname" placeholder="نام خانوادگی ..." required="required" dir="rtl"/>
				<input type="text" name="CompanyName" style="display:none" class="textfield" id="company" placeholder="نام شرکت ..." dir="rtl"/>
				<input type="email" name="email" id="email" class="textfield" placeholder="پست الکترونیک ..." required="required" />
				<div id="UserNameDiv2"><input type="text" id="UserName2" name="UserName" class="textfield" placeholder="کلمه کاربری ..." required="required" /></div>
				<input type="password" class="textfield" id="password1" placeholder="رمز عبور ..." required="required" />
				<input type="password" class="textfield" id="password2" placeholder="تکرار رمز عبور ..." required="required" oninput="ConfirmPassword(this)"/>
				<input type="hidden" id="md5Pass2" name="md5Pass">
				
				<button type="submit" style="float:right;width:100px" class="btn2"> ثبت نام </button>
				<button type="button" onclick="Register(false)" class="btn2" style="font-size: 20px;font-weight: bolder;padding-bottom: 3px;width: 38px;">←</button>
			</form>
		</div>
		<div class="footer">پرتال جامع	
			<br><?= SoftwareName?></div>
	</div>
  </body>
</html>