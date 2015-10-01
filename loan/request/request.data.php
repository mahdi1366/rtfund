<?php
//-------------------------
// programmer:	Jafarkhani
// create Date: 94.06
//-------------------------
include_once('../header.inc.php');
include_once inc_dataReader;
include_once inc_response;
include_once 'request.class.php';

$task = $_REQUEST["task"];
switch ($task) {
		
	case "SaveLoanRequest":
		SaveLoanRequest();
		
	case "SelectRequests":
		SelectRequests();
}

function SaveLoanRequest(){
	
	$obj = new LON_requests();
	PdoDataAccess::FillObjectByArray($obj, $_POST);
	$obj->PersonID = $_SESSION["USER"]["PersonID"];
	
	$result = $obj->AddRequest();
	
	echo Response::createObjectiveResponse($result, $obj->RequestID);
	die();
}

function SelectRequests(){
	
	$dt = LON_requests::SelectAll("r.PersonID=? " . dataReader::makeOrder(), 
			array($_SESSION["USER"]["PersonID"]));
	echo dataReader::getJsonData($dt, count($dt), $_GET["callback"]);
	die();
}


?>
