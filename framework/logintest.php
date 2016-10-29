<?php
//-----------------------------
//	Programmer	: SH.Jafarkhani & rasoul abdolahi
//	Date		: 95.05
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
	unset($_SESSION['USER']["portal"]);
	
	echo "true";
	die();
}

?>







<html>
    <head>
        <meta charset="utf-8">
        <title>پرتال مشتریان | ورود</title>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <link rel="stylesheet" type="text/css" href="https://support.chargoon.com/css/style.css">
        <link rel="stylesheet" type="text/css" href="https://support.chargoon.com/css/media-query.css">
        <script type="text/javascript" src="https://support.chargoon.com/js/jquery.min.js"></script>
        <script type="text/javascript" src="https://support.chargoon.com/js/modernizer.js"></script>

        <script type="text/javascript">
            $(function() {
                $("#username").focus();
            })

        </script>
    </head>

    <body class="login">
        <div class="main-container clearfix">
			<div class="login-container">
				<h1>
				<img src="../framework/icons/login-logo.png">
				</h1>
               <li></li>
                    <li></li>
                    <li><br /><br /><br /><br /><br /><br /><br /></li>

								<div id="div_forget" class="forget">&nbsp; </div>
								
								
								
                    
                    
                    
                </ul>
               
                                </form>
			</div>
        </div>
		<script type="text/javascript">
			if (navigator.userAgent.toLowerCase().indexOf("chrome") >= 0) {
				$(window).load(function(){
					// setTimeout(function(){
					// 	$('input[name="username"], input[name="password"]').each(function(){
					// 		var text = $(this).val();
					// 		var name = $(this).attr('name');
					// 		$(this).after(this.outerHTML).remove();
					// 	});
					// }, 300);
					setTimeout(function(){
						$('.login-container').fadeIn(300);
					}, 350);
				});
			} else {
				setTimeout(function(){
					$('.login-container').fadeIn(300);
				}, 350);
			}
		</script>
    </body>
</html>



















<html>
  <head>
    <meta http-equiv="content-type" content="text/html; charset=utf-8" />
	<meta http-equiv="expires" content="now">
	<meta http-equiv="pragma" content="no-cache">
    <title>نرم افزار <?= SoftwareName?></title>
	<? require_once 'md5.php'; ?>
	<style>
	  body td div{
		  font-family: b koodak;
	  }
	.headerDiv{
		height: 100px;
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
		height : 50px;
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
		border:   transparent;
	 background-color: transparent;
		font-size: 12px;
		padding: 5px;
		margin-bottom: 10px;
		vertical-align:   middle;
		width: 50px;
		font-family: b koodak;
		
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
		background-color:   black;
	    border-bottom-color: aqua;
		border: 5px;
		cursor:   !important;
		border-radius: 10px;
		color: white;
		float:   none;
		font-size: 14px;
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
						window.location = "desktop.php";
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
		<div >
			<div style="width:800;right : 20%;position: absolute;" align="right">
				<br>
			
				</div>
		</div>
		<div align="center">
		<div class="mainDiv">
			<table width="60%"> 
				<tr>
					<td width="70px" style="vertical-align: middle;">
			
					</td>
					<td style="vertical-align: top;">
						<div id="loginDIV">
							<form id="mainForm" method="post" autocomplete="off">
								<div class="title" >  </div>
								<br>
								<div style="font-size: 12px"></div>
								<br>
								<div id="div_lock" style="display:none;color : red;font-size: 12px" font-family="b koodak">به دلیل چندین تلاش ناموفق برای ورود به سیستم، امکان ورود به سیستم برای 15 دقیقه قفل خواهد شد.</div>
								
								
								
								<div id="UserNameDiv"><input autocomplete="off" type="text" class="textfield" name="UserName" id="UserName" font-family="b koodak"
									placeholder="کلمه کاربری ..." required="required" dir="ltr" /></div>
								<div id="PasswordDiv"><input autocomplete="off" type="password" class="textfield" id="password" 
									placeholder="رمز عبور ..." required="required" dir="ltr"/></div>
								<button id="btn_enter" onclick="loginFN();" type="button" style="width:80px" class="btn  ">ورود</button>	
								
								
								
								
								<input type="hidden" id="md5Pass" name="md5Pass">
							</form>
						</div>										
					</td>
				</tr>
			</table>
		</div>
		</div>
		
<br>
</div>
	</center>	  
  </body>
</html>