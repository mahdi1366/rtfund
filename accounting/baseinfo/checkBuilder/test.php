<?php
if(isset($_REQUEST["task"]) && $_REQUEST["task"] = "getContent")
{
	$retrun = file_get_contents("../test.html", "r");
	$arr = preg_split('/@/', $retrun);
	echo stripcslashes($arr[1]);
	die();
}
$fp = fopen("../test.html", "w");
fwrite($fp,

'<body dir="rtl"><link rel="stylesheet" type="text/css" href="formBuilder/extjs/css/ext-all.css" />@'.
stripcslashes($_REQUEST["content"]) . '@</body>');

fclose($fp);

?>