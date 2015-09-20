<?php
//-----------------------------
//	Programmer	: SH.Jafarkhani
//	Date		: 90.12
//-----------------------------

require_once '../../header.inc.php';
require_once '../class/accounts.class.php';

$task = isset ( $_POST ["task"] ) ? $_POST ["task"] : (isset ( $_GET ["task"] ) ? $_GET ["task"] : "");

switch ( $task)
{
	case "selectAccount":
		  selectAccount();

	case "saveAccount":
		saveAccount();

	case "removeAccount":
		removeAccount();
}

//..........................

function selectAccount()
{
	$where = "1=1";
	$where .= dataReader::makeOrder();

	$temp = manage_accounts::GetAll($where);
	$no = count($temp);
	
	echo dataReader::getJsonData ($temp, $no, $_GET["callback"]);
	die ();
}

function saveAccount()
{
	$obj = new manage_accounts();
    PdoDataAccess::FillObjectByJsonData($obj, $_POST["record"]);

	if(empty($obj->accountID))
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

function removeAccount()
{
	$result = manage_accounts::Remove($_POST["accountID"]);
	echo $result ? "true" : "conflict";
	die();
}

?>