<?php
//---------------------------
// programmer:	Jafarkhani
// create Date:	94.06
//---------------------------
require_once '../header.inc.php';
require_once 'people.class.php';
require_once inc_response;
require_once inc_dataReader;

$task = isset($_POST["task"]) ? $_POST["task"] : (isset($_GET["task"]) ? $_GET["task"] : "");

switch ($task)
{
	case "SelectPeopleInfo":
		SelectPeopleInfo();
		
	case "SavePersonalInfo":
		SavePersonalInfo();
		
	case "changePass":
		changePass();
}

function SelectPeopleInfo(){
	$dt = PdoDataAccess::runquery("select UserName,fullname,NationalID,EconomicID,PhoneNo,mobile,address,email 
		from BSC_peoples where PeopleID=?", array($_SESSION["USER"]["PeopleID"]));
	
	echo dataReader::getJsonData($dt, count($dt), $_REQUEST["callback"]);
	die();
}

function SavePersonalInfo(){
	
	$obj = new BSC_peoples();
	$obj->PeopleID = $_SESSION["USER"]["PeopleID"];
	PdoDataAccess::FillObjectByArray($obj, $_POST);
	
	$result = $obj->EditPeople();
	echo Response::createObjectiveResponse($result, "");
	die();
}

function changePass(){
	
	$dt = PdoDataAccess::runquery("select * from BSC_peoples where PeopleID=:p",
			array(":p" => $_SESSION['USER']["PeopleID"]));
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

	PdoDataAccess::RUNQUERY("update BSC_peoples set UserPass=? where PeopleID=?",
		array($hasher->HashPassword($_POST["new_pass"]), $_SESSION["USER"]["PeopleID"]));
								
	if( ExceptionHandler::GetExceptionCount() != 0 )
	{				
		echo "CurPassError";
		die();
	}	
	echo Response::createObjectiveResponse(true, "");
	die();
}


?>
