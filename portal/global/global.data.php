<?php
//---------------------------
// programmer:	Jafarkhani
// create Date:	94.06
//---------------------------
require_once '../header.inc.php';
require_once getenv("DOCUMENT_ROOT") . '/framework/person/persons.class.php';
require_once inc_response;
require_once inc_dataReader;

$task = isset($_POST["task"]) ? $_POST["task"] : (isset($_GET["task"]) ? $_GET["task"] : "");

switch ($task)
{
	case "SelectPersonInfo":
		SelectPersonInfo();
		
	case "SavePersonalInfo":
		SavePersonalInfo();
		
	case "changePass":
		changePass();
}

function SelectPersonInfo(){
	
	$temp = BSC_persons::SelectAll("PersonID=?", array($_SESSION["USER"]["PersonID"]));
	$temp = PdoDataAccess::fetchAll($temp, 0, 1);
	echo dataReader::getJsonData($temp, 1, $_GET["callback"]);
	die();
}

function SavePersonalInfo(){
	
	$obj = new BSC_persons();
	$obj->PersonID = $_SESSION["USER"]["PersonID"];
	PdoDataAccess::FillObjectByArray($obj, $_POST);
	
	$result = $obj->EditPerson();
	echo Response::createObjectiveResponse($result, "");
	die();
}

function changePass(){
	
	$dt = PdoDataAccess::runquery("select * from BSC_persons where PersonID=:p",
			array(":p" => $_SESSION['USER']["PersonID"]));
	if(count($dt) == 0)
	{ 		
		echo Response::createObjectiveResponse(false, "");
		die();
	}
	
	require_once getenv("DOCUMENT_ROOT") . '/framework/PasswordHash.php';
	$hash_cost_log2 = 8;	
	$hasher = new PasswordHash($hash_cost_log2, true);
	if (!$hasher->CheckPassword($_POST["cur_pass"], $dt[0]["UserPass"])) {

		echo Response::createObjectiveResponse(false, "CurPassError");
		die();
	}

	PdoDataAccess::RUNQUERY("update BSC_persons set UserPass=? where PersonID=?",
		array($hasher->HashPassword($_POST["new_pass"]), $_SESSION["USER"]["PersonID"]));
								
	if( ExceptionHandler::GetExceptionCount() != 0 )
	{				
		echo "CurPassError";
		die();
	}	
	echo Response::createObjectiveResponse(true, "");
	die();
}


?>
