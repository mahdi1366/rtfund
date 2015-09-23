<?php
//---------------------------
// programmer:	Jafarkhani
// create Date:	94.06
//---------------------------

require_once 'header.inc.php';
require_once inc_dataReader;

$task = $_REQUEST["task"];

switch($task)
{
	case "SelectPosts":
		SelectPosts();
}

function SelectPosts(){
	
	$temp = PdoDataAccess::runquery("select * from BSC_posts");
	echo dataReader::getJsonData($temp, count($temp), $_REQUEST["callback"]);
	die();
}
?>
