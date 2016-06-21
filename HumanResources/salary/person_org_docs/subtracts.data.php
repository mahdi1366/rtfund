<?php
//-----------------------------
//	Programmer	: SH.Jafarkhani
//	Date		: 90.10
//-----------------------------

require_once '../../header.inc.php';
require_once 'subtracts.class.php';

require_once inc_dataReader;
require_once inc_response;

$task = isset ( $_POST ["task"] ) ? $_POST ["task"] : (isset ( $_GET ["task"] ) ? $_GET ["task"] : "");

switch ( $task)
{
	case "AllSubtracts":
		  AllSubtracts();

	case "saveSubtract":
		saveSubtract();

	case "RemoveSubtract":
		RemoveSubtract();
		
	case "AllSubtractFlows":
		AllSubtractFlows();
		
	case "saveSubtractFlow":
		saveSubtractFlow();
		
	case "RemoveSubtractFlow":
		RemoveSubtractFlow();
}

//..........................

function AllSubtracts()
{

	$where = "PersonID=:pid AND subtract_type=:stype";
	$whereParam = array(":pid" => $_REQUEST["PersonID"], ":stype" => $_REQUEST["subtract_type"]);
	
	$where .= dataReader::makeOrder();

	$temp = manage_subtracts::GetAll($_REQUEST["subtract_type"], $_REQUEST["PersonID"], $where, $whereParam);
	$no = count($temp);
	
	for($i=0; $i < $no; $i++)
	{
		if($temp[$i]["IsFinished"] != "0")
			$temp[$i]["IsEditable"] = false;
		else
			$temp[$i]["IsEditable"] = manage_subtracts::IsEditable($temp[$i]["subtract_id"]);
	}
	
	echo dataReader::getJsonData ($temp, $no, $_GET["callback"]);
	die ();
}

function saveSubtract()
{
	$obj = new manage_subtracts();
    PdoDataAccess::FillObjectByArray($obj, $_POST);

	if(empty($obj->subtract_id))
	{
		$obj->reg_date = PDONOW;
		$return = $obj->Add();
	}
	else
		$return = $obj->Edit();

	echo Response::createObjectiveResponse($return, "");
	die();
}

function RemoveSubtract()
{
	$result = manage_subtracts::Remove($_POST["subtract_id"]);
	echo Response::createObjectiveResponse($result, "");
	die();
}

function AllSubtractFlows()
{
	$where = "subtract_id=:sid";
	$whereParam = array(":sid" => $_REQUEST["subtract_id"]);
	
	$where .= dataReader::makeOrder();

	$temp = manage_subtract_flows::GetAll($where, $whereParam);
	$no = count($temp);
	
	for($i=0; $i < $no; $i++)
		$temp[$i]["IsEditable"] = manage_subtract_flows::IsEditable($temp[$i]["row_no"]);
	
	echo dataReader::getJsonData ($temp, $no, $_GET["callback"]);
	die ();
}

function saveSubtractFlow()
{
	$obj = new manage_subtract_flows();
    PdoDataAccess::FillObjectByJsonData($obj, $_POST["record"]);

	$obj->flow_date = PDONOW;

	if($obj->row_no > 0)
		$return = $obj->Edit();
	else
		$return = $obj->Add();
		
	echo Response::createObjectiveResponse($return, "");
	die();
}

function RemoveSubtractFlow()
{
	$result = manage_subtract_flows::Remove($_POST["row_no"]);
	echo Response::createObjectiveResponse($result, "");
	die();
}

?>