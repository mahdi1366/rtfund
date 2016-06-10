<?php
//-----------------------------
//	Programmer	: SH.Jafarkhani
//	Date		: 1395.03
//-----------------------------
include('header.inc.php');
require_once 'management/framework.class.php';

$systems = FRW_access::getAccessSystems();
/*
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
	$icon = (!$icon) ? "/generalUI/ext4/resources/themes/icons/star.gif" : 
		"/generalUI/ext4/resources/themes/icons/$icon";

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

$SystemName = $menus[0]["SysName"];*/
?>
<html>
	<head>
		<meta content="text/html; charset=utf-8" http-equiv="Content-Type"/>	
		<title><?= SoftwareName ?></title>
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
	<script type="text/javascript">
	//-----------------------------
	//	Programmer	: SH.Jafarkhani
	//	Date		: 1394.06
	//-----------------------------

	function compareObject(o1, o2){
		for(var p in o1){
			if(o1[p] !== o2[p]){
				return false;
			}
		}
		for(var p in o2){
			if(o1[p] !== o2[p]){
				return false;
			}
		}
		return true;
	}

	FrameWorkClass.prototype = {
		TabsArray : new Array(),
		centerPanel : "",
		menuItems : "",
		StartPage : ""
	};

	function FrameWorkClass()
	{
		//this.items = new Array(< ?= $menuStr ?>);

		this.ExpireInterval = setInterval(function(){

			Ext.Ajax.request({
				url : "header.inc.php",
				method : "POST",

				success : function(response)
				{
					if(response.responseText.trim() != "")
					{
						document.getElementById("LoginExpire").style.display = "";					
						clearInterval(framework.ExpireInterval);
					}
				}
			});

		}, 5*60000); // in milisecond

		this.centerPanel = new Ext.TabPanel({
			region: 'center',
			enableTabScroll : true,
			resizeTabs      : true,
			deferredRender: false,
			autoScroll : true,
			minTabWidth: 120,
			tabWidth: 'auto'
		});
		
		this.northPanel = new Ext.panel.Panel({
			region: 'north',
			//split: true,
			height: 200,
			//minSize: 200,
			//maxSize: 200,
			collapsible: true,	
			layout:'accordion',
			fill: true,	  
			defaults : {
				hideCollapseTool : true
			},
				
			items : [{
				xtype : 'panel',
				layout: 'fit',
				title: 'اطلاعات پایه',
				items :[{
					xtype : 'menu',
					//layout : "hbox",
					floating: false,
					bodyStyle : 'background-color:white !important;',
					items :[
						{text: 'انواع وام'},
						{text: 'مدیریت ذینفعان'},
						{text: 'مدیریت ذینفعان'},
						{text: 'مدیریت ذینفعان'},
						{text: 'مدیریت ذینفعان'},
						{text: 'مدیریت ذینفعان'},
						{text: 'مدیریت ذینفعان'},
						{text: 'مدیریت ذینفعان'},
						{text: 'مدیریت ذینفعان'},
						{text: 'مدیریت ذینفعان'},
						{text: 'مدیریت ذینفعان'}
					]
				}]
			}]
		});

		this.view = new Ext.Viewport({
			layout: 'border',
			renderTo : document.body,
			items: [this.northPanel,
			 
			//------------------------------------------------------------------
			this.centerPanel
			//------------------------------------------------------------------
				
			]
		});
	}

	FrameWorkClass.prototype.OpenPage = function(itemURL, itemTitle, params)
	{
		if(itemURL == "")
			return;

		if(arguments.length < 3)
			params = {};

		itemURL = this.formatUrl(itemURL);

		var id = "ext_tab_" + Ext.MD5(itemURL);
		params.ExtTabID = id;

		if(this.TabsArray[id])
		{
			this.centerPanel.setActiveTab(id);
			if(itemTitle != "")
				this.centerPanel.items.get(id).setTitle(itemTitle);

			if(!compareObject(this.TabsArray[id].params, params))
			{
				Ext.getCmp(id).close();
				/*Ext.getCmp(id).loader.load({
					url: itemURL,
					method: "POST",
					params : newParam,
					text: "در حال بار گذاری...",
					scripts: true
				});
				this.TabsArray[id].params = params;*/
			}
			else
				return;
		}

		this.TabsArray[id] =
		{
			params : params,
			itemURL : itemURL,
			title : itemTitle
		}

		var newTab = this.centerPanel.add({
			title: itemTitle,
			id: id,
			bodyCfg: {style: "padding:10px;background-color:white"},
			closable: true,
			autoScroll : true,
			loader : {
				url: itemURL,
				method: "POST",
				params : params,
				text: "در حال بار گذاری...",
				scripts: true
			},
			listeners : {
				beforeclose : function(){
					this.destroy();
					delete framework.TabsArray[id];
					return true;
				}
			}
		}).show();

		newTab.loader.load();
	}

	FrameWorkClass.prototype.CloseTab = function(TabID)
	{
		delete framework.TabsArray[TabID];
		//this.centerPanel.getItem(TabID).close();
	// this.centerPanel.getItem(TabID).destroy();
	this.centerPanel.items.get(TabID).destroy();
	}

	FrameWorkClass.prototype.logout = function()
	{
		Ext.Ajax.request({
			url : "/framework/logout.php",
			method : "POST",

			success : function()
			{
				window.location = "/framework/login.php";
			}
		});
	}

	FrameWorkClass.prototype.home = function()
	{
		window.location = document.location;
	}

	FrameWorkClass.prototype.formatUrl = function(url)
	{
		var list = url.split("/");
		var list2 = new Array();
		for(var i=1; i<list.length; i++)
		{
			if(list[i] == "..")
				list2.pop();
			else
				list2.push(list[i]);
		}

		return "/" + list2.join("/");
	}

	FrameWorkClass.SystemLoad = function(){};
	
	</script>

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
		}, 7);
	</script>

		<div id="LoginExpire" style="display : none;">
			<div style="color: red; height: 40px; width: 100%; z-index: 99999; position: fixed; 
				 background-color: white; text-align: center; font-weight: bold;cursor:pointer"
				 onclick="window.location='/framework/login.php'">
				<br>زمان انتظار شما به پایان رسیده است لطفا مجدد وارد شوید</div>
			<div style="position: fixed;top: 40;left: 0;height:100%;width:100%;z-index: 9999999;
			background-color : #999;opacity: 0.7;filter: alpha(opacity=70);-moz-opacity: 0.7; /* mozilla */"></div>
		</div>
		
	</body>
</html>
