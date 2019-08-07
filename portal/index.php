<?php
//-----------------------------
//	Programmer	: SH.Jafarkhani
//	Date		: 1394.06
//-----------------------------
require_once "header.inc.php";
require_once $address_prefix . '/framework/management/framework.class.php';
require_once $address_prefix . '/framework/person/persons.class.php';
//---------------- last login --------------
$dt = PdoDataAccess::runquery("select max(AttemptTime) from FRW_LoginAttempts where PersonID=?",
		array($_SESSION["USER"]["PersonID"]));
$lastDate = $dt[0][0];
//--------------- pending user --------------------
$personObj = new BSC_persons($_SESSION["USER"]["PersonID"]);
if($personObj->IsActive == "NO")
	die();
if($personObj->IsActive == "PENDING")
	$menuStr = "";
else
	$menuStr = CreateMenuStr();
	
function CreateMenuStr(){
	$menus = FRW_access::getPortalMenus();

	//------------- one sended loans ---------------------
	//51,209
	$Menu51 = false;
	for($i=0; $i < count($menus); $i++)
	{
		if($menus[$i]["MenuID"] == "51")
			$Menu51 = true;

		if($menus[$i]["MenuID"] == "209")
		{
			if($Menu51)
				$menus = array_merge(array_slice($menus, 0, $i-1) , array_slice ($menus, $i+1) );
			break;
		}
	}
	//----------------------------------------------------

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
		$link_path = "../" . $menus[$i]['MenuPath'];
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

		$icon = strpos($icon, "far") !== false ? $icon : "fas fa-" . $icon; 
		$menuStr .= '<div class="menuItem" onclick="portal.OpenPage(\'' . $link_path . "'," . $param . ');"> 
						<i style="color:#' . $colors[$colorIndex] . '" class="menuIcon ' . $icon . '"></i>
						<div class="menuText">' . $menus[$i]["MenuDesc"] . '</div>
				   </div>';

		$colorIndex = $colorIndex+1 == count($colors) ? 0 : $colorIndex+1;
	}
	
	return $menuStr;
}

?>
<html >
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
		<title><?= SoftwareName?></title>
		<link rel="stylesheet" type="text/css" href="ext/portal.css?v=1" />
		<link rel="stylesheet" type="text/css" href="/generalUI/ext4/resources/css/Loading.css" />
		<link rel="stylesheet" type="text/css" href="/generalUI/ext4/resources/css/ext-all.css" />
	</head>
<body dir=rtl>
	<div id="loading-mask"></div>
	<div id="loading">
		<div class="loading-indicator">در حال بارگذاری سیستم . . .
			<img src="/generalUI/ext4/resources/themes/icons/loading-balls.gif" style="margin-right:8px;" align="absmiddle"/></div>
	</div>
	<center>
		<table style="width:1024px;height:100%">
			 <thead>
				<tr style="border-bottom: 1px solid white;height: 100px">
					<th colspan="2">
						<div class="portalHeader">
							<table width="100%" style="height: 100%">
								<tr>
									<td style="vertical-align: bottom;padding-bottom:20px">
										<div class="HeaderMenu" onclick="portal.OpenPage('/portal/FirstPage.php')">
											<i class="fas fa-home"></i>صفحه نخست</div>
										<div class="HeaderMenu" onclick="window.open('http://krrtf.ir')"><i class="fab fa-internet-explorer"></i>سایت صندوق</div>
										<div class="HeaderMenu"><i class="fas fa-question "></i>راهنمای استفاده از خدمات</div>
										<div class="HeaderMenu" onclick="portal.OpenPage('/portal/contact.php')"><i class="fas fa-phone "></i>تماس با ما</div>
									</td>
									<td width="100px" align="center" style="vertical-align: middle;font-weight: bold">
										<?= DateModules::shNow(); ?><br>
										<div id="portal_clock"></div>
										<script>
											function startTime() {
												var today = new Date();
												var h = today.getHours();
												var m = today.getMinutes();
												var s = today.getSeconds();
												m = checkTime(m);
												s = checkTime(s);
												document.getElementById('portal_clock').innerHTML =
												h + ":" + m + ":" + s;
												var t = setTimeout(startTime, 500);
											}
											function checkTime(i) {
												if (i < 10) {i = "0" + i};  // add zero in front of numbers < 10
												return i;
											}
											startTime();
										</script>
									</td>
									<td width="100px"><img style="margin-top:5px;width:90px;" 
														   src="/framework/icons/logo-small.png"></td>
								</tr>
							</table>
						</div>
					</th>
				</tr>
			  </thead>
			<tr>
				<td style="height: 120px;border-left: 1px solid #ddd">
					<div align='center' class="UserInfoBox">
						<?= $_SESSION['USER']["fullname"] ?> خوش آمدید.					
						<br>
						<img class='PortalPersonPicStyle' 
							 src="/framework/person/showImage.php?PersonID=<?= $_SESSION['USER']["PersonID"]?>" />
						<table width="90%" style="margin-bottm:5px;">
							<tr>
								<td align="right" style="color:white">آخرین ورود <br> 
									<font style="color: #FAC570">
									<?= substr($lastDate,10) ?> - <?= DateModules::miladi_to_shamsi($lastDate) ?>
									</font>
									</td>
								<td align="left">
									<img style='width: 22px; vertical-align: middle; margin-top: 10px;cursor: pointer' 
									src='/framework/icons/exit.png' onclick="portal.OpenPage('/framework/logout.php');" /> 
								</td>
							</tr>
						</table>
					</div>
				</td>
				<td rowspan="2" style="background-color: white;vertical-align: top;min-height: 500px">
					<div id="mainPortalFrame" class="mainFrame"></div>
				</td>
			</tr>
			<tr>
				<td style="background-color: white;vertical-align: top;border-left: 1px solid #ddd">
					<div class="menu" ><?= $menuStr?></div>
				</td>
			</tr>
			<tfoot>
				<tr>
					<td colspan="2"><div class="copyright" style="font-family: tahoma" align=center>کلیه حقوق مادی و معنوی این پورتال محفوظ می باشد</div></td>
				  </tr>
			</tfoot>
		</table>
		
		
	</center>
	<link rel="stylesheet" type="text/css" href="/generalUI/icons/icons.css" />
	<link rel="stylesheet" type="text/css" href="/generalUI/ext4/resources/css/ext-rtl.css" />
	<script type="text/javascript" src="/generalUI/ext4/resources/ext-all.js"></script>
	<script type="text/javascript" src="/generalUI/ext4/resources/ext-extend.js"></script>
	<script type="text/javascript" src="/generalUI/ext4/ux/grid/SearchField.js"></script>
	<script type="text/javascript" src="/generalUI/ext4/ux/CurrencyField.js"></script>
	<script type="text/javascript" src="/generalUI/ext4/ux/grid/ExtraBar.js"></script>
	<script type="text/javascript" src="/generalUI/ext4/ux/ImageViewer.js"></script>
	<script type="text/javascript" src="/generalUI/pdfobject.js"></script>
	<script type="text/javascript" src="/generalUI/ext4/ux/grid/excel.js"></script>
	<script type="text/javascript" src="/generalUI/jquery-3.4.1.min.js"></script>
	<script>
			var required = '<span style="color:red;font-weight:bold" data-qtip="فیلد اجباری">*</span>';
			var portal;
			setTimeout(function(){
				try {
					Ext.onReady(function(){
						Ext.QuickTips.init();
						Ext.get('loading').remove();
						Ext.get('loading-mask').fadeOut({
							remove:true
						});
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
