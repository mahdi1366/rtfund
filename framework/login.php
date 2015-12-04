<?php
//-----------------------------
//	Programmer	: SH.Jafarkhani
//	Date		: 94.06
//-----------------------------

require_once 'configurations.inc.php';
set_include_path(get_include_path() . PATH_SEPARATOR . getenv("DOCUMENT_ROOT") . "/generalClasses");
require_once 'PDODataAccess.class.php';
require_once 'PasswordHash.php';

session_start();

$return = "";

if(isset($_POST["UserName"]))
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
			if($temp[0]["IsActive"] == "NO")
			{
				echo '<meta http-equiv="content-type" content="text/html; charset=utf-8" />';
				echo "<body dir=rtl><center><br><br><br><font style='font-family:b titr;'>";
				echo "یوزر شما غیر فعال شده است. لطفا جهت پیگیری با صندوق تماس حاصل فرمایید.";
				echo "</font></center></body>";
				die();
			}
		
			$_SESSION['USER'] = $temp[0];
			$_SESSION['USER']["fullname"] = $temp[0]["fname"] . " " . $temp[0]["lname"];
			$_SESSION['USER']["framework"] = true;
			$_SESSION['USER']["portal"] = false;
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
     <title>نرم افزار <?= SoftwareName?></title>
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
		width : 500px;	
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
		content: url("icons/cross.png");
		padding-right: 6px;
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
  </style>
  </head>

  <body dir="rtl" style="margin:0"  onkeydown="pressing(event);" onload="return BodyLoad();">

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
							<form method="post" id="MainForm" onsubmit="return loginFN();">
								<div class="title" > ورود به پرتال </div>
								<br>
								<div style="font-size: 12px">جهت ورود به پرتال نام کاربری و کلمه عبور خود را وارد کنید.
									درغیر اینصورت با زدن دکمه ثبت نام به پرتال وارد شوید.</div>
								<br>
								<div id="UserNameDiv"><input type="text" name="UserName" class="textfield" id="UserName" 
									placeholder="کلمه کاربری ..." required="required" dir="ltr" /></div>
								<div id="PasswordDiv"><input type="password" class="textfield" id="password" 
									placeholder="رمز عبور ..." required="required" dir="ltr"/></div>
								<button type="submit" style="width:80px" class="btn  ">ورود</button>
								<div class="forget">&nbsp;| رمز عبور را فراموش کرده ام </div>
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