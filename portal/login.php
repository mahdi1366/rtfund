<?php
//-----------------------------
//	Programmer	: SH.Jafarkhani
//	Date		: 94.06
//-----------------------------

require_once getenv("DOCUMENT_ROOT") . '/framework/configurations.inc.php';
set_include_path(get_include_path() . PATH_SEPARATOR . getenv("DOCUMENT_ROOT") . "/generalClasses");
require_once 'PDODataAccess.class.php';
require_once getenv("DOCUMENT_ROOT") . '/framework/PasswordHash.php';

session_start();

$return = "";

if(isset($_POST["UserName"]))
{
	$user = $_POST["UserName"];
	$pass = $_POST["md5Pass"];
	
	$temp = PdoDataAccess::runquery("select * from BSC_peoples where UserName=?", array($user));
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
	}
	.login {
		background-color: #e9e9e9;
		border: 0;
		border-radius: 30px;
		height: 370px;
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
	input {
		background-color: #cfcfcf;
		border: 1px solid #cacaca;
		border-radius: 25px;
		color: #555;
		font-size: 13px;
		margin-bottom: 15px;
		padding: 13px;
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
	.btn:hover{
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
		<div style="padding:40px">
			<div class="headerText">ورود به پرتال</div>
			<form method="post" id="MainForm" onsubmit="return loginFN();">
				<div id="UserNameDiv"><input type="text" name="UserName" id="UserName" placeholder="کلمه کاربری ..." required="required" /></div>
				<div id="PasswordDiv"><input type="password" name="password" id="password" placeholder="رمز عبور ..." required="required" /></div>
				<button type="submit" class="btn  ">ورود</button>
				<div class="forget"> رمز عبور را فراموش کرده ام |</div>
				<input type="hidden" id="md5Pass" name="md5Pass">
			</form>
		</div>
		<div class="footer">پرتال جامع
			<br><?= SoftwareName?></div>
	</div>
  </body>
</html>