<?php
//---------------------------
// programmer:	Jafarkhani
// create Date:	90.07
//---------------------------

$task = $_REQUEST["task"];

switch($task)
{
	case "presave";
	      presave();

	case "finalSave";
	      finalSave();
}

function presave()
{
	$index = 0;
	$returnStr = "";

	$st = split ( '\.', $_FILES["attach"]['name'] );
	$extension = $st [count ( $st ) - 1];
	$extension = strtolower($extension);
	$date = getdate();
	$filename = $date[0] . $index++ . "." . $extension;

	$fp = fopen("../../ResearchDocuments/" . $filename, "w");
	fwrite($fp, fread(fopen($_FILES["attach"]['tmp_name'], 'r'), $_FILES["attach"]['size']));
	fclose($fp);

	echo $filename;
	die();
}

function finalSave()
{
	$x1      = $_POST['x1'];
	$y1      = $_POST['y1'];
	$width   = $_POST['width'];
	$height  = $_POST['height'];

	$srcImg  = imagecreatefromjpeg($_POST["imagePath"]);
	$newImg  = imagecreatetruecolor($width, $height);

	imagecopyresampled($newImg, $srcImg, 0, 0, $x1, $y1, $width, $height, $width, $height);

	imagejpeg($newImg, $_POST["imagePath"]);

	$source = $_POST["source"];
	$tmp = preg_split('/\?/', $source);
	
	$params = preg_split('/\&/', $tmp[1]);
	for($i=0; $i < count($params); $i++)
	{
		$tp = preg_split('/=/', $params[$i]);
		$_GET[$tp[0]] = $tp[1];
	}
	
	require_once $tmp[0];

	unlink($_POST["imagePath"]);

	die();
}














?>
