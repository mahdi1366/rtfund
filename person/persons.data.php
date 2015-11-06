<?php
//-------------------------
// programmer:	Jafarkhani
// Create Date:	89.11
//-------------------------
include('header.inc.php');
require_once 'persons.class.php';
require_once '../framework/PasswordHash.php';
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
			case "IsStaff":		$where .= " AND IsStaff='YES'";break;
		}
	}
	
	if(!empty($_REQUEST["PersonID"]))
	{
		$where .= " AND PersonID=:p";
		$param[":p"] = $_REQUEST["PersonID"];
	}
	
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
	
	$hash_cost_log2 = 8;	
	$hasher = new PasswordHash($hash_cost_log2, true);
	$obj->UserPass = $hasher->HashPassword(md5("123456"));
	$obj->IsStaff = "YES";
	$obj->IsReal = "YES";
	
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
