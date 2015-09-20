<?php
//-----------------------------
//	Programmer	: SH.Jafarkhani
//	Date		: 90.12
//-----------------------------

require_once '../../header.inc.php';
require_once '../class/banks.class.php';

$task = isset ( $_POST ["task"] ) ? $_POST ["task"] : (isset ( $_GET ["task"] ) ? $_GET ["task"] : "");

switch ( $task)
{
	case "selectBank":
		  selectBank();

	case "saveBank":
		saveBank();

	case "removeBank":
		removeBank();
}

//..........................

function selectBank()
{
	$where = "1=1";
	$where .= dataReader::makeOrder();

	$temp = manage_banks::GetAll($where);
	$no = count($temp);
	
	echo dataReader::getJsonData ($temp, $no, $_GET["callback"]);
	die ();
}

function saveBank()
{
	$obj = new manage_banks();
    PdoDataAccess::FillObjectByJsonData($obj, $_POST["record"]);

	if(empty($obj->bankID))
		$return = $obj->Add();
	else
		$return = $obj->Edit();

	if(!$return)
	{
		echo "false";
		print_r(ExceptionHandler::PopAllExceptions());
		die();
	}
	echo Response::createObjectiveResponse(true, "");
	die();
}

function removeBank()
{
	$result = manage_banks::Remove($_POST["bankID"]);
	echo $result ? "true" : "conflict";
	die();
}

?>