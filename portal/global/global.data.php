<?php
//---------------------------
// programmer:	Jafarkhani
// create Date:	94.06
//---------------------------
require_once '../header.inc.php';
require_once inc_response;
require_once inc_dataReader;

$task = isset($_POST["task"]) ? $_POST["task"] : (isset($_GET["task"]) ? $_GET["task"] : "");

switch ($task)
{
	case "SelectPeopleInfo":
		SelectPeopleInfo();
}

function SelectPeopleInfo()
{
	$dt = PdoDataAccess::runquery("select UserName,fullname,NationalID,EconomicID,PhoneNo,mobile,address,email 
		from BSC_peoples where PeopleID=?", array($_SESSION["USER"]["PeopleID"]));
	
	echo dataReader::getJsonData($dt, count($dt), $_REQUEST["callback"]);
	die();
}

?>
