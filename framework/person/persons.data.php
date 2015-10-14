<?php
//-------------------------
// programmer:	Jafarkhani
// Create Date:	89.11
//-------------------------
include('../header.inc.php');
require_once 'persons.class.php';
include_once inc_dataReader;
require_once inc_response;

if(isset($_REQUEST["task"]))
{
	switch ($_REQUEST["task"])
	{
		case "SavePerson":
			SavePerson();
			
		case "DeletePerson":
			DeletePerson();
			
		case "ResetPass":
			ResetPass();
			
		case "selectPersons":
			selectPersons();
	}
}

function selectPersons(){
	
	$where = "1=1";
	$param = array();
	
	if(!empty($_REQUEST["UserType"]))
	{
		switch($_REQUEST["UserType"])
		{
			case "IsAgent":		$where .= " AND IsAgent='YES'";break;
			case "IsCustomer":	$where .= " AND IsCustomer='YES'";break;
		}
	}
	else
		$where .= " AND IsStaff='YES'";
	
	
	if(!empty($_REQUEST["query"]))
	{
		$where .= " AND concat(fname,' ',lname) like :p";
		$param[":p"] = "%" . $_REQUEST["query"] . "%";
	}
	
	$temp = BSC_persons::SelectAll($where, $param);
	$no = count($temp);
	$temp = PdoDataAccess::fetchAll($temp, $_GET["start"], $_GET["limit"]);
	echo dataReader::getJsonData($temp, $no, $_GET["callback"]);
	die();
}

function SavePerson(){
	
	$obj = new BSC_persons();
	PdoDataAccess::FillObjectByJsonData($obj, $_POST["record"]);
	
	if($obj->PersonID > 0)
		$result = $obj->EditPerson();
	else 
		$result = $obj->AddPerson();
	
	echo Response::createObjectiveResponse($result, "");
	die();
}

function DeletePerson(){
	
	$result = BSC_persons::DeletePerson($_POST["PersonID"]);
	echo Response::createObjectiveResponse($result, "");
	die();
}

function ResetPass(){
	
	$result = BSC_persons::ResetPass($_POST["PersonID"]);
	echo Response::createObjectiveResponse($result, "");
	die();
}

?>
