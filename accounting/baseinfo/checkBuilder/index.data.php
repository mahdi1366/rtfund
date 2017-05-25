<?php
//-----------------------------
//	Programmer	: SH.Jafarkhani
//	Date		: 92.02
//-----------------------------

$task = isset($_REQUEST["task"]) ? $_REQUEST["task"] : "";

switch ($task) {
	
	case "getFileContent":
		getFileContent();
		
	case "SaveBackground":
		SaveBackground();
		
	case "SaveCheck":
		SaveCheck();
}

function getFileContent(){
	
	$checkID = $_REQUEST["ChequeBookID"];
//	if($checkID==41) $checkID=25;
	$filename = "output/" . $checkID . ".html";
	if(!file_exists($filename))
	{
		$defaultContent = '<div id="returnDIV" style="position:absolute;top:0;left:0; width: 100%;height:100%">
				<div align="right" style="width: 680px; height: 250px; position: relative;" id="fb_div_0"></div></div>';
		$fp = fopen($filename, "w");
		fwrite($fp,'<body dir="rtl">@'. $defaultContent	. '@</body>');
		fclose($fp);
		echo $defaultContent;
		die();
	}
	
	$retrun = file_get_contents($filename, "r");
	$arr = preg_split('/@/', $retrun);
	echo stripcslashes($arr[1]);
	die();
}

function SaveBackground(){
	
	$checkID = $_REQUEST["ChequeBookID"];
//	if($checkID==41) $checkID=25;
	$st = split ( '\.', $_FILES ['imageAttach']['name'] );
	$extension = $st [count ( $st ) - 1];	
	$extension = strtolower($extension);	
	
	$filename = "backgrounds/" . $checkID . "." . $extension;
	$fp = fopen($filename, "w");
	fwrite($fp,fread(fopen($_FILES ['imageAttach']['tmp_name'], 'r' ), $_FILES ['imageAttach']['size']));
	fclose($fp);
	die();
}

function SaveCheck(){
	ini_set("display_errors","On");
	$checkID = $_REQUEST["ChequeBookID"];
//	if($checkID==41) $checkID=25;
	$filename = "output/" . $checkID . ".html";
	$Content = $_POST["content"];
	$Content = preg_replace("/&quot;/", "'", $Content);
	$fp = fopen($filename, "w");
	fwrite($fp,'<body dir="rtl">@'. $Content . '@</body>');
	fclose($fp);
	die();
}
?>
