<?php
//-----------------------------
//	Programmer	: SH.Jafarkhani
//	Date		: 1394.06
//-----------------------------
require_once "header.inc.php";
require_once $address_prefix . '/framework/management/framework.class.php';

$SystemID = 1000; // portal

$menus = FRW_access::getPortalMenus($SystemID);
$groupArr = array();
$menuStr = "";

$colors = array("1E8BC3", "F86924", "FF9F00", "35BC7A");
$colorIndex = 0;

for ($i = 0; $i < count($menus); $i++) {
	
	if (!isset($groupArr[ $menus[$i]["GroupID"] ] )) {
		
		$menuStr .= '<div class="menuHeaders">' . $menus[$i]["GroupDesc"] . '</div>';		
		$groupArr[ $menus[$i]["GroupID"] ] = true;
	}

	$icon = $menus[$i]['icon'];
	$link_path = "/" . $menus[$i]['SysPath'] . "/" . $menus[$i]['MenuPath'];
	$param = "{";
	$param .= "MenuID : " . $menus[$i]['MenuID'] . ",";

	//--------- extract params --------------
	if (strpos($link_path, "?") !== false) {
		$arr = preg_split('/\?/', $link_path);
		$link_path = $arr[0];
		$arr = preg_split('/\&/', $arr[1]);
		for ($k = 0; $k < count($arr); $k++)
			$param .= str_replace("=", ":'", $arr[$k]) . "',";
	}
	$param = substr($param, 0, strlen($param) - 1);
	//---------------------------------------
	$param .= "}";
	
	$menuStr .= '<div class="menuItem" onclick="portal.OpenPage(\'' . $link_path . "'," . $param . ');"> 
					<div class="menuIcon" style="color:#' . $colors[$colorIndex] . '">
						<span class="fa fa-' . $icon . '"></span></div> 
					<div class="menuText">' . $menus[$i]["MenuDesc"] . '</div>
			   </div>';
	
	$colorIndex = $colorIndex+1 == count($colors) ? 0 : $colorIndex+1;
}

?>
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
		<title><?= SoftwareName?></title>
		<link rel="stylesheet" type="text/css" href="ext/portal.css" />
		<link rel="stylesheet" type="text/css" href="/generalUI/ext4/resources/css/Loading.css" />
		<link rel="stylesheet" type="text/css" href="/generalUI/ext4/resources/css/ext-all.css" />
	</head>
<body dir=rtl>
	<!--<div id="loading-mask"></div>
	<div id="loading">
		<div class="loading-indicator">در حال بارگذاری سیستم . . .
			<img src="/generalUI/ext4/resources/themes/icons/loading-balls.gif" style="margin-right:8px;" align="absmiddle"/></div>
	</div>-->
	
	<center>
		<div class="header">
			<a href='http://krrtf.ir/' target='_blank' title='' >
				<div class="headerLogo">
				<?= SoftwareName?>
				</div>
			</a>
			<!----------------------------------------------------------------->
			<div class="headerItems" style="color:#1E8BC3;" 
				 onclick="portal.OpenPage('/framework/person/PersonInfo.php');">
				<span class="fa fa-user"></span><br>
				<font style="font-family:tahoma;font-size:12px;font-weight:bold">اطلاعات شخصی</font>
			</div>
			<!----------------------------------------------------------------->
			<div class="headerItems" style="color:#F86924;" 
				 onclick="portal.OpenPage('/portal/global/ChangePassword.php');">
				<span class="fa fa-key"></span><br>
				<font style="font-family:tahoma;font-size:12px;font-weight:bold">تغییر رمز عبور</font>
			</div>
			<!----------------------------------------------------------------->
			<div class="headerItems" style="color:#FF9F00"
				 onclick="portal.OpenPage('/office/workflow/MyRequests.php');">
				<span class="fa fa-list-alt"></span><br>
				<font style="font-family:tahoma;font-size:12px;font-weight:bold">فرم ها</font>
			</div>
			<!----------------------------------------------------------------->
			<div class="headerItems" style="color:#35BC7A"
				 onclick="portal.OpenPage('/portal/global/VoteForms.php');">
				<span class="fa fa-pencil-square-o"></span><br>
				<font style="font-family:tahoma;font-size:12px;font-weight:bold">نظر سنجی</font>
			</div>
			<!----------------------------------------------------------------->
			<div class="headerItems" style="color:#1E8BC3"
				 onclick="portal.OpenPage('/portal/global/letters.php');">
				<span class="fa fa-envelope-o "></span><br>
				<font style="font-family:tahoma;font-size:12px;font-weight:bold">نامه ها</font>
			</div>
		</div>
     <div class="main">
			
          <div id="mainPortalFrame" class="mainFrame" ></div>
		  <div align='right' class="UserInfoBox blueText">
			  <img style='width: 35px; float: right; vertical-align: middle; margin-top: 3px;' 
				   src='/framework/icons/user.png'>
			  <img style='width: 22px; float: left; vertical-align: middle; margin-top: 10px;cursor: pointer' 
				 src='/framework/icons/exit.png' onclick="portal.OpenPage('/portal/logout.php');"> 
				<?= $_SESSION['USER']["fullname"] ?>
				<br> شناسه : <?= $_SESSION['USER']["UserName"]?>			
			</div>
		<div class="menu" ><?= $menuStr?></div>
     </div>
		<!--
		 <table class ="example_3">
			 <td width="33%" style="background-color:#35bc7a;">
			 </td>
			 <td width="12px"></td>
			 <td width="33%" style="background-color:#f86924;"></td>
			 <td width="12px"></td>
			 <td width="" style="background-color:#ff9f00;"></td>
		 </table>
  -->
     <div class="footer">
		 <img id='nbpewmcswmcsnbpedrft' style='cursor:pointer' 
onclick='window.open("http://trustseal.enamad.ir/Verify.aspx?id=28821&p=wkynaqgwaqgwwkynnbpd", "Popup",
	"toolbar=no, location=no, statusbar=no, menubar=no, scrollbars=1, resizable=0, width=580, height=600, top=30")' 
	alt='' src='http://trustseal.enamad.ir/logo.aspx?id=28821&p=qesgukaqukaqqesglznb'/>
     </div>
  <div class="copyright" style="font-family: tahoma" align=center>کلیه حقوق مادی و معنوی این پورتال محفوظ می باشد</div>          
  </center>
	<link rel="stylesheet" type="text/css" href="/generalUI/ext4/resources/css/icons.css" />
	<link rel="stylesheet" type="text/css" href="/generalUI/ext4/resources/css/ext-rtl.css" />
	<script type="text/javascript" src="/generalUI/ext4/resources/ext-all.js"></script>
	<script type="text/javascript" src="/generalUI/ext4/resources/ext-extend.js"></script>
	<script type="text/javascript" src="/generalUI/ext4/ux/grid/SearchField.js"></script>
	<script type="text/javascript" src="/generalUI/ext4/ux/CurrencyField.js"></script>
	<script type="text/javascript" src="/generalUI/ext4/ux/grid/ExtraBar.js"></script>
	<script>
			var required = '<span style="color:red;font-weight:bold" data-qtip="فیلد اجباری">*</span>';
			var portal;
			setTimeout(function(){
				try {
					Ext.onReady(function(){
						Ext.QuickTips.init();
						/*Ext.get('loading').remove();
						Ext.get('loading-mask').fadeOut({
							remove:true
						});*/
						portal = new PortalClass();
						portal.OpenPage("/portal/FirstPage.php");
						//PortalClass.SystemLoad();
					});
				}
				catch(err){}
			}, 700);
		</script>
	<? require_once 'ext/index.js.php'; ?>
</body>
</html>
