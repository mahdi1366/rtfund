<?php
//---------------------------
// programmer:	Jafarkhani
// create Date:	94.07
//---------------------------
require_once '../header.inc.php';
require_once 'vote.class.php';
require_once inc_dataReader;
require_once inc_response;

$task = isset($_POST["task"]) ? $_POST["task"] : (isset($_GET["task"]) ? $_GET["task"] : "");
if(!empty($task))
	$task();

function SelectAllForms(){
	
	$dt = VOT_forms::Get();
	$no = $dt->rowCount();
	$dt = PdoDataAccess::fetchAll($dt, $_GET["start"], $_GET["limit"]);
	
	echo dataReader::getJsonData($dt, $no, $_GET["callback"]);
	die();
}

function SaveForm(){
	
	$obj = new VOT_forms();
	PdoDataAccess::FillObjectByJsonData($obj, $_POST["record"]);
	
	if($obj->FormID > 0)
		$result = $obj->Edit();
	else
		$result = $obj->Add();
	
	echo Response::createObjectiveResponse($result, "");
	die();
}

function DeleteForm(){
	
	$obj = new VOT_forms($_POST["FormID"]);
	$result =  $obj->Remove();
	echo Response::createObjectiveResponse($result, "");
	die();
}

function selectFormItems(){
	
	$dt = PdoDataAccess::runquery("select * from VOT_FormItems 
		where FormID=?", array($_GET["FormID"]));
	echo dataReader::getJsonData($dt, count($dt), $_GET["callback"]);
	die();
}

//............................

function SelectItems(){
	
	$dt = VOT_FormItems::Get(" AND FormID=? " . dataReader::makeOrder(), array($_GET["FormID"]));
	echo dataReader::getJsonData($dt->fetchAll(), $dt->rowCount(), $_GET["callback"]);
	die();
}

function SaveItem(){
	
	$obj = new VOT_FormItems();
	PdoDataAccess::FillObjectByJsonData($obj, $_POST["record"]);
	
	if($obj->ItemID > 0)
		$result = $obj->Edit();
	else
	{
		$dt = PdoDataAccess::runquery("select ifnull(max(ordering),0) 
			from VOT_FormItems where FormID=?",	array($obj->FormID));
		$obj->ordering = $dt[0][0]*1 + 1;
		
		$result = $obj->Add();
	}
	echo Response::createObjectiveResponse($result, "");
	die();
}

function DeleteItem(){
	
	$obj = new VOT_FormItems($_POST["ItemID"]);
	$result =  $obj->Remove();
	echo Response::createObjectiveResponse($result, "");
	die();
}

function MoveItem(){
	
	$FormID = $_POST["FormID"];
	$ItemID = $_POST["ItemID"];
	$ordering = $_POST["ordering"];
	$direction = $_POST["direction"];
	
	$direction = $direction == "-1" ? "-1" : "+1";
	
	PdoDataAccess::runquery("update VOT_FormItems 
		set ordering=ordering $direction
		where FormID=? AND ItemID=?",
			array($FormID, $ItemID));
		
	PdoDataAccess::runquery("update VOT_FormItems 
			set ordering=? 
			where FormID=? AND ItemID<>? AND ordering=? ",
			array($ordering, $FormID, $ItemID, $ordering*1 + $direction*1));
	
	echo Response::createObjectiveResponse(true, "");
	die();
}


?>