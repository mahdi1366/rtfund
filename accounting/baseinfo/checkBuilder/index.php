<?php
//-----------------------------
//	Programmer	: SH.Jafarkhani
//	Date		: 92.02
//-----------------------------

$checkID = $_REQUEST["ChequeBookID"];

?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<title>طراحی فرم</title>
    <link rel="stylesheet" type="text/css" href="css/Loading.css" />
    <link rel="stylesheet" type="text/css" href="extjs/css/ext-all.css" />
</head>
<body dir="rtl">
<div id="loading-mask"></div>
<div id="loading">
  <div class="loading-indicator">در حال بارگذاری فرم ساز ....
  <img src="icons/loading-balls.gif" style="margin-right:8px;" align="absmiddle"/></div>
</div>
<script type="text/javascript" src="extjs/adapter/ext/ext-base.js"></script>
<script type="text/javascript" src="extjs/ext-all.js"></script>
<script id="js/Ext.ux.Util.js" src="js/Ext.ux.Util.js" type="text/javascript"></script>
<script src="js/Designer-all.js" type="text/javascript"></script>
<script type="text/javascript" src="js/grid.js"></script>

<link rel="stylesheet" type="text/css" href="css/Designer.css" />
<link rel="stylesheet" type="text/css" href="main.css" />
<script language="JavaScript">
	
	var chequeID = <?= $checkID?>;
	//var ImagePath = "../ImageGallery/FormPhotos/" + TableCode + "/";
	
	//var TableCode = 1623;
    //var TableName = 'nazerin_nezam_mohandesi';
    //var FormID = 3;
    //var FormName = 'تست';
	var FormItems = new Array(
		{id: "101", text: "سال", type: "label"},
		{id: "102", text: "ماه" , type: "label"},
		{id: "103", text: "روز", type: "label"},
		{id: "116", text: "تاریخ", type: "label"},
		{id: "104", text: "تاریخ حروفی", type: "label"},
		{id: "105", text: "مبلغ ریالی", type: "label"},
		{id: "106", text:"مبلغ حروفی", type: "label"},
		{id: "107", text:"در وجه", type: "label"},
		{id: "114", text:"شماره برگه", type: "label"},
		{id: "115", text:"پرفراژ", type: "label", style : "background-color:black;color:white;text-align:center;font-size:14px;font-weight:bold"},
		{id: "117", text:"شماره چک", type: "label", style : "color:red;font-weight:bold;font-size:20px;"},
		{id: "108", text:"امضا1", type: "label"},
		{id: "109", text:"سمت1", type: "label"},
		{id: "110", text:"امضا2", type: "label"},
		{id: "111", text:"سمت2", type: "label"},
		{id: "112", text:"امضا3", type: "label"},
		{id: "113", text:"سمت3", type: "label"},
		{id: "118", text:"امضا4", type: "label"},
		{id: "119", text:"سمت4", type: "label"});
	
var now = new Date();
document.write('<script src="rightPanel.js?' + now.getTime() + '"><\/script>');
</script>
<? require_once 'index.js.php'; ?>
<div id="fb_content"></div>
<img id="imgSelectElement" src="icons/layout_edit.png" style="cursor:pointer;top:-20;left:-20;position:absolute;z-index:9999">

<div id="returnDIV" style='position:absolute;top:0;left:0; width: 100%;height:100%'></div>
<div id="PropertyDiv">
	<table width="100%">
		<tr>
			<td>عرض:</td>			
			<td><input class="x-form-text x-form-field" style="width:50px" type="text"
				id="width" onchange="changeStyle(this);"></td>
			<td>ارتفاع:</td>
			<td><input class="x-form-text x-form-field" style="width:50px" type="text"
				id="height" onchange="changeStyle(this);"></td>
			<td colspan="2" style="height:21px">
				<input type="button" class="button" id="autoSize" onclick="changeStyle(this);" value="اندازه اتوماتیک"</td>
			<td>جهت :</td>
			<td>
				<select class="x-form-text x-form-field" id="direction" onchange="changeStyle(this);">
					<option value="rtl">راست به چپ</option>
					<option value="ltr">چپ به راست</option>
				</select></td>
			<td>چینش:</td>
			<td>
				<select class="x-form-text x-form-field" id="textAlign" onchange="changeStyle(this);">
					<option value="right">راست چین</option>
					<option value="left">چپ چین</option>
					<option value="center">وسط چین</option>
				</select></td>
		</tr>
		<tr>
			<td>فونت:</td>
			<td><select class="x-form-text x-form-field" id="fontFamily" onchange="changeStyle(this);">
					<option value="titr">Titr</option>
					<option value="nazanin">Nazanin</option>
				</select></td>
			<td>اندازه فونت:</td>
			<td><input type="text" id="fontSize" style="width:50px" class="x-form-text x-form-field"
				onchange="changeStyle(this);"></td>
			<td><input type="checkbox" id="fontWeight" onchange="changeStyle(this);"> درشت</td>
			<td colspan="5"><input type="checkbox" id="fontStyle" onchange="changeStyle(this);"> کج</td>
			<!--<td>رنگ فونت:</td>
			<td><input type="text" id="fontColor"></td>
			<td>رنگ زمینه:</td>
			<td><input type="text" id="BackColor"></td>-->
		</tr>
		<tr id="tr_image">
			<td colspan="10">
				<hr>
				<form id="saveImageForm">
					تصویر : 
					<input type="file" name="imageAttach" id="imageAttach">
					<input type="button" class="button" value="ذخیره عکس" onclick="saveImage();">
				</form>
			</td>
		</tr>
	</table>
</div>
</body>
</html>
