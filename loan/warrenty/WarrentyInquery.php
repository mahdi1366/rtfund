<?php
//-----------------------------
//	Programmer	: SH.Jafarkhani
//	Date		: 96.11
//-----------------------------

require_once getenv("DOCUMENT_ROOT") . '/framework/configurations.inc.php';
require_once getenv("DOCUMENT_ROOT") . '/generalClasses/PDODataAccess.class.php';
require_once getenv("DOCUMENT_ROOT") . '/generalClasses/ReportGenerator.class.php';
require_once getenv("DOCUMENT_ROOT") . '/definitions.inc.php';

if(!empty($_POST["RequestID"]))
{
	$dt = PdoDataAccess::runquery("select w.*,NationalID from WAR_requests w join BSC_persons using(PersonID) where RefRequestID=?",
			array((int)$_POST["RequestID"]));
	if(count($dt) == 0)
	{
		echo "ضمانت نامه ایی با این شماره در سیستم صندوق ثبت نشده است";
		die();
	}
	
	if($dt[0]["NationalID"] != $_POST["NationalID"])
	{
		echo $dt[0]["NationalID"] . "<br>";
		echo "کدملی / شناسه ملی وارد شده مطابق به ضمانت نامه موجود نمی باشد";
		die();
	}
	ReportGenerator::BeginReport();
	
	$rpt = new ReportGenerator();
	$rpt->mysql_resource = $dt;
	
	$rpt->addColumn("شماره ضمانت نامه", "RefRequestID");
	$rpt->addColumn("مبلغ ضمانت نامه", "amount", "ReportMoneyRender");
	$rpt->addColumn("تاریخ شروع", "StartDate", "ReportDateRender");
	$rpt->addColumn("تاریخ پایان", "StartDate", "ReportDateRender");

	echo $rpt->generateReport();
	
	require_once 'PrintWarrenty.php';
	
	die();
}
?>
<html>
  <head>
    <meta http-equiv="content-type" content="text/html; charset=utf-8" />
	<link rel="stylesheet" type="text/css" href="/generalUI/fonts/fonts.css" />
    <title></title>
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
			font-family: nazanin, sans-serif;
			font-size: 14px;
			font-weight: bold;
			/*background: #092756;
			background: -moz-radial-gradient(0% 100%, ellipse cover, rgba(104,128,138,.4) 10%,rgba(138,114,76,0) 40%),-moz-linear-gradient(top,  rgba(57,173,219,.25) 0%, rgba(42,60,87,.4) 100%), -moz-linear-gradient(-45deg,  #670d10 0%, #092756 100%);
			background: -webkit-radial-gradient(0% 100%, ellipse cover, rgba(104,128,138,.4) 10%,rgba(138,114,76,0) 40%), -webkit-linear-gradient(top,  rgba(57,173,219,.25) 0%,rgba(42,60,87,.4) 100%), -webkit-linear-gradient(-45deg,  #670d10 0%,#092756 100%);
			background: -o-radial-gradient(0% 100%, ellipse cover, rgba(104,128,138,.4) 10%,rgba(138,114,76,0) 40%), -o-linear-gradient(top,  rgba(57,173,219,.25) 0%,rgba(42,60,87,.4) 100%), -o-linear-gradient(-45deg,  #670d10 0%,#092756 100%);
			background: -ms-radial-gradient(0% 100%, ellipse cover, rgba(104,128,138,.4) 10%,rgba(138,114,76,0) 40%), -ms-linear-gradient(top,  rgba(57,173,219,.25) 0%,rgba(42,60,87,.4) 100%), -ms-linear-gradient(-45deg,  #670d10 0%,#092756 100%);
			background: -webkit-radial-gradient(0% 100%, ellipse cover, rgba(104,128,138,.4) 10%,rgba(138,114,76,0) 40%), linear-gradient(to bottom,  rgba(57,173,219,.25) 0%,rgba(42,60,87,.4) 100%), linear-gradient(135deg,  #670d10 0%,#092756 100%);
			filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='#3E1D6D', endColorstr='#092756',GradientType=1 );*/
			background-image : url('icons/bg.jpg');
		}
		.mainPanel { 
			background-color: #EDFFFF;
			border-radius: 20px;
			height: 350px;
			left: 50%;
			margin: -230px 0 0 -230px;
			padding: 20px;
			position: absolute;
			top: 50%;
			width: 460px;
			box-shadow: 10px 10px 5px #aaaaaa;
		}
		.title {font-family: titr; font-size: 18px; color: #0F5163; font-weight: normal;
			   text-align: center; line-height: 30px; padding-bottom: 30px;}
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
		
		input:focus { box-shadow: inset 0 -5px 45px rgba(100,100,100,0.4), 0 1px 1px rgba(255,255,255,0.2); }
    </style>
  </head>

  <body dir="rtl">
    <div class="mainPanel">
		<div class="title" >	استعلام ضمانت نامه <br><?= SoftwareName ?>
		<br></div>
		<form method="post" id="MainForm">
			شماره ضمانت نامه :
			<input type="text" name="RequestID" id="RequestID"  required="required"  />
			شناسه ملی / کد ملی ذینفع :
			<input type="text" name="NationalID" id="NationalID"  required="required" />
			اعداد تصویر زیر را وارد کنید :
			<button type="submit" class="btn btn-primary btn-block btn-large ">استعلام</button>
		</form>
	</div>
  </body>
</html>