<?php

//---------------------------
// programmer:	Mahdipour
// create Date:	91.01.22
//---------------------------
require_once '../../header.inc.php';
require_once '../class/subtract_info.class.php';
require_once(inc_response);
require_once inc_dataReader;

$task = isset($_POST ["task"]) ? $_POST ["task"] : (isset($_GET ["task"]) ? $_GET ["task"] : "");

switch ($task) {

	case "searchSubItem" :
		searchSubItem();

	case "SaveSubInfo":
		SaveSubInfo();
            
        case "removeSI":
                removeSI() ; 
}

function searchSubItem() {
	$where = dataReader::makeOrder();
	$temp = manage_subInfo::GetAll($where);
	$no = count($temp);

	$temp = array_slice($temp, $_GET["start"], $_GET["limit"]);

	echo dataReader::getJsonData($temp, $no, $_GET ["callback"]);
	die();
}

function SaveSubInfo() {
	$obj = new manage_subInfo();
	PdoDataAccess::FillObjectByJsonData($obj, $_POST["record"]);

	if ($_GET['newMode'] == 1) {
		$return = $obj->Add();
	} else {		
		$return = $obj->Edit();
	}

	if ($return)
		echo Response::createResponse(true, $obj->SalaryItemTypeID);
	else
		echo Response::createResponse(false, ExceptionHandler::ConvertExceptionsToJsObject());
	die();
}

function removeSI()
{
	$return = manage_subInfo::Remove($_POST["sid"] , $_POST["pty"]);
	Response::createObjectiveResponse($return, (!$return ? ExceptionHandler::popExceptionDescription() : ""));
	die();
}



?>