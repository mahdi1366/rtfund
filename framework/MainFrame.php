<?php
//-----------------------------
//	Programmer	: SH.Jafarkhani
//	Date		: 1390-02
//-----------------------------
include('header.inc.php');
require_once 'management/framework.class.php';

if(empty($_REQUEST["SystemID"]))
	die();

$_SESSION['USER']["RecentSystems"][ $_REQUEST["SystemID"] ] = true;
$_SESSION["SystemID"] = $_REQUEST["SystemID"];

$menus = FRW_access::getAccessMenus($_REQUEST["SystemID"]);

$groupArr = array();
$menuStr = "";

for ($i = 0; $i < count($menus); $i++) {
	
	if (!isset($groupArr[ $menus[$i]["GroupID"] ] )) {
		if ($i > 0) {
			$menuStr = substr($menuStr, 0, strlen($menuStr) - 1);
			$menuStr .= "]}]},";
		}
		$menuStr .= "{
			xtype : 'panel',
			layout: 'fit',
			title: '" . $menus[$i]["GroupDesc"] . "',
			items :[{
				xtype : 'menu',
				floating: false,
				bodyStyle : 'background-color:white !important;',
				items :[";
		
		$groupArr[ $menus[$i]["GroupID"] ] = true;
	}

	$icon = $menus[$i]['icon'];
	$icon = (!$icon) ? "/framework/icons/star.gif" : "/framework/icons/$icon";

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

	$menuStr .= "{
		text: '" . $menus[$i]["MenuDesc"] . "',
		handler: function(){
			framework.OpenPage('" . $link_path . "','" . $menus[$i]["MenuDesc"] . "'," . $param . ");
		},
		icon: '" . $icon . "'
	},";
}

if ($menuStr != "") {
	$menuStr = substr($menuStr, 0, strlen($menuStr) - 1);
	$menuStr .= "]}]}";
}
//------------------------------------------------------------------------------
$sysArray = "";
$sysArray1 = "";
$sysArray2 = "";
$syslist = FRW_access::getAccessSystems();
if (count($syslist) > 1) {
	for ($i = 0; $i < count($syslist); $i++) {
		if (isset($_SESSION['USER']["RecentSystems"][$syslist[$i]['SystemID']])) {
			$sysArray1 .= "
				{
					text: '<span style=color:#2D5696 ><b>" . $syslist[$i]['SysName'] . "</b></span>',
					icon: '/generalUI/ext4/resources/themes/icons/arrow-left.gif',
					handler: function(){
						window.location = '/" . $syslist[$i]['SysPath']  . "/start.php?SystemID=" . $syslist[$i]['SystemID'] . "';
					}
				},";
		}
		else
			$sysArray2 .= "
				{
					text: '" . $syslist[$i]['SysName'] . "',
					icon: '/generalUI/ext4/resources/themes/icons/arrow-left.gif',
					handler: function(){
						window.location = '/" . $syslist[$i]['SysPath']  . "/start.php?SystemID=" . $syslist[$i]['SystemID'] . "';
					}
				},";
	}
	
	$sysArray = $sysArray1 . "'-'," . substr($sysArray2, 0, strlen($sysArray2) - 1);
}

if(count($menus) == 0)
{
	echo "<script>window.location='/framework/login.php';</script>";
	die();
}

$SystemName = $menus[0]["SysName"];
?>
<html>
	<head>
		<meta content="text/html; charset=utf-8" http-equiv="Content-Type"/>	
		<title><?php echo $menus[0]["SysName"]; ?></title>
		<link rel="stylesheet" type="text/css" href="/generalUI/ext4/resources/css/Loading.css" />
		<link rel="stylesheet" type="text/css" href="/generalUI/ext4/resources/css/ext-all.css" />

		<style type="text/css">
			html, body {
				font:normal 11px tahoma;
				margin:0;
				padding:0;
				border:0 none;
				overflow:hidden;
				height:100%;
			}
		</style>
	</head>
	<body dir="rtl">
		<div id="loading-mask"></div>
		<div id="loading">
			<div class="loading-indicator">در حال بارگذاری سیستم . . .
				<img src="/generalUI/ext4/resources/themes/icons/loading-balls.gif" style="margin-right:8px;" align="absmiddle"/></div>
		</div>

		<link rel="stylesheet" type="text/css" href="/generalUI/ext4/resources/css/icons.css" />
		<script type="text/javascript" src="/generalUI/ext4/resources/ext-all.js"></script>

		<link rel="stylesheet" type="text/css" href="/generalUI/ext4/resources/css/ext-rtl.css" />
		<script type="text/javascript" src="/generalUI/ext4/resources/ext-extend.js"></script>
		<script type="text/javascript" src="/generalUI/ext4/ux/component.js"></script>
		<script type="text/javascript" src="/generalUI/ext4/ux/message.js"></script>
		<script type="text/javascript" src="/generalUI/ext4/ux/grid/SearchField.js"></script>
		<script type="text/javascript" src="/generalUI/ext4/ux/TreeSearch.js"></script>
		<script type="text/javascript" src="/generalUI/ext4/ux/CurrencyField.js"></script>
		<script type="text/javascript" src="/generalUI/ext4/ux/grid/ExtraBar.js"></script>
		<script type="text/javascript" src="/generalUI/ext4/ux/grid/gridprinter/Printer.js"></script>
<?php
require_once 'MainFrame.js.php';
?>

		<script>
			var required = '<span style="color:red;font-weight:bold" data-qtip="فیلد اجباری">*</span>';
			Ext.QuickTips.init();
			var framework;
			setTimeout(function(){
				Ext.get('loading').remove();
				Ext.get('loading-mask').fadeOut({
					remove:true
				});
				framework = new FrameWorkClass();
				if(FrameWorkClass.StartPage != "" && FrameWorkClass.StartPage != undefined)
					framework.OpenPage(FrameWorkClass.StartPage, "صفحه اصلی");
				FrameWorkClass.SystemLoad();
			}, 700);
		</script>

	</body>
</html>
