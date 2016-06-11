<?php
//-----------------------------
//	Programmer	: SH.Jafarkhani
//	Date		: 1395.03
//-----------------------------
include('header.inc.php');
require_once 'management/framework.class.php';

$systems = FRW_access::getAccessSystems();

$menuStr = "";

foreach($systems as $sysRow)
{
	$menuStr .= "{text: '" . $sysRow["SysName"] . "'";
	
	$menus = FRW_access::getAccessMenus($sysRow["SystemID"]);
	if(count($menus) > 0)
		$menuStr .= ",menu : {plain: true,xtype : 'menu',items:[";
	
	//........................................................
	$groupArr = array();
	foreach($menus as $row)
	{
		if (!isset($groupArr[ $row["GroupID"] ] )) 
		{
			if(count($groupArr) > 0)
			{
				$menuStr = substr($menuStr, 0, strlen($menuStr) - 1);
				$menuStr .= "]},";
			}
			$menuStr .= "{text : '" . $row["GroupDesc"] . "', menu :[";
			$groupArr[$row["GroupID"] ] = true;
		}
		
		$icon = $row['icon'];
		$icon = (!$icon) ? "/generalUI/ext4/resources/themes/icons/star.gif" : "/generalUI/ext4/resources/themes/icons/$icon";
		$link_path = "/" .$row['SysPath'] . "/" . $row['MenuPath'];
		//--------- extract params --------------
		$param = "{";
		$param .= "MenuID : " . $row['MenuID'] . ",";
		if (strpos($link_path, "?") !== false) {
			$arr = preg_split('/\?/', $link_path);
			$link_path = $arr[0];
			$arr = preg_split('/\&/', $arr[1]);
			for ($k = 0; $k < count($arr); $k++)
				$param .= str_replace("=", ":'", $arr[$k]) . "',";
		}
		$param = substr($param, 0, strlen($param) - 1);
		$param .= "}";
		//---------------------------------------		

		$menuStr .= "{
			text: '" . $row["MenuDesc"] . "',
			handler: function(){
				framework.OpenPage('" . $link_path . "','" . $row["MenuDesc"] . "'," . $param . ");
			},
			icon: '" . $icon . "'
		},";
	}
	$menuStr .= "]}";
	//........................................................
	if(count($menus) > 0)
		$menuStr .= "]}";
	
	$menuStr .= "},'-',";
}
if ($menuStr != "") {
	$menuStr = substr($menuStr, 0, strlen($menuStr) - 1);
}

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

		//----------------------------------------------------------
		
		this.centerPanel = new Ext.TabPanel({
			region: 'center',
			enableTabScroll : true,
			resizeTabs      : true,
			deferredRender: false,
			autoScroll : true,
			minTabWidth: 120,
			tabWidth: 'auto'
		});
		
		//----------------------------------------------------------
		
		this.northPanel = new Ext.panel.Panel({
			region: 'north',
			fill: true,	  
			items : [{
				xtype : 'panel',
				border : false,
				layout: 'fit',
				style : "margin-bottom:10px",
				html : "<br><?= SoftwareName ?><hr>",
				bbar : [<?= $menuStr ?>]
				
			}]
		});
		
		//----------------------------------------------------------
		
		this.EastPanel = new Ext.panel.Panel({
			region: 'east',
			split: true,
			collapsible: true,			  
			width: 185,
			minSize: 185,
			maxSize: 200,
			fill: true,	  
			bodyStyle : "text-align:center",
			defaults : {
				hideCollapseTool : true
			},
			items : [
			{
				xtype : "container",
				width : 185,
				contentEl : document.getElementById("clock")
			},
			new Ext.picker.SHDate(),
			{
				xtype : "fieldset",
				contentEl : document.getElementById("clock")
			}]
		});
		
		//----------------------------------------------------------
		
		this.view = new Ext.Viewport({
			layout: 'border',
			renderTo : document.body,
			items: [this.northPanel,this.centerPanel,this.EastPanel]
		});
		
		//----------------------------------------------------------
		
		this.canvas = document.getElementById("canvas");
		this.ctx = this.canvas.getContext("2d");
		this.radius = this.canvas.height / 2;
		this.ctx.translate(this.radius, this.radius);
		this.radius = this.radius * 0.90;
		setInterval(this.drawClock, 1000);
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
	
	//..........................................................................

	FrameWorkClass.prototype.drawClock = function() {
		
		framework.drawFace(framework.ctx, framework.radius);
		framework.drawNumbers(framework.ctx, framework.radius);
		framework.drawTime(framework.ctx, framework.radius);
	}

	FrameWorkClass.prototype.drawFace = function(ctx, radius) {
		var grad;
		ctx.beginPath();
		ctx.arc(0, 0, radius, 0, 2*Math.PI);
		ctx.fillStyle = 'white';
		ctx.fill();
		grad = ctx.createRadialGradient(0,0,radius*0.95, 0,0,radius*1.05);
		grad.addColorStop(0, 'white');
		grad.addColorStop(0, '#333');
		grad.addColorStop(0.5, 'white');
		grad.addColorStop(1, '#333');
		ctx.strokeStyle = grad;
		ctx.lineWidth = radius*0.1;
		ctx.stroke();
		ctx.beginPath();
		ctx.arc(0, 0, radius*0.1, 0, 2*Math.PI);
		ctx.fillStyle = '#333';
		ctx.fill();
	}

	FrameWorkClass.prototype.drawNumbers = function(ctx, radius) {
	var ang;
	var num;
	ctx.font = radius*0.15 + "px arial";
	ctx.textBaseline="middle";
	ctx.textAlign="center";
	for(num = 1; num < 13; num++){
		ang = num * Math.PI / 6;
		ctx.rotate(ang);
		ctx.translate(0, -radius*0.85);
		ctx.rotate(-ang);
		ctx.fillText(num.toString(), 0, 0);
		ctx.rotate(ang);
		ctx.translate(0, radius*0.85);
		ctx.rotate(-ang);
	}
	}

	FrameWorkClass.prototype.drawTime = function(ctx, radius){
		var now = new Date();
		var hour = now.getHours();
		var minute = now.getMinutes();
		var second = now.getSeconds();
		//hour
		hour=hour%12;
		hour=(hour*Math.PI/6)+
		(minute*Math.PI/(6*60))+
		(second*Math.PI/(360*60));
		this.drawHand(ctx, hour, radius*0.5, radius*0.07);
		//minute
		minute=(minute*Math.PI/30)+(second*Math.PI/(30*60));
		this.drawHand(ctx, minute, radius*0.8, radius*0.07);
		// second
		second=(second*Math.PI/30);
		this.drawHand(ctx, second, radius*0.9, radius*0.02);
	}

	FrameWorkClass.prototype.drawHand = function(ctx, pos, length, width) {
		ctx.beginPath();
		ctx.lineWidth = width;
		ctx.lineCap = "round";
		ctx.moveTo(0,0);
		ctx.rotate(pos);
		ctx.lineTo(0, -length);
		ctx.stroke();
		ctx.rotate(-pos);
	}

</script>
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
	
	<div id="clock"><canvas id="canvas" width="130" height="130"></canvas></div>
	</body>
</html>