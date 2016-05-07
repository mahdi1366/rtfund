<?php
//-------------------------
// programmer:	Jafarkhani
// create Date: 95.02
//-------------------------
include_once('../../header.inc.php');
include_once inc_dataReader;
include_once inc_response;
include_once 'traffic.class.php';

$task = $_REQUEST["task"];
switch ($task) {

	default : 
		eval($task. "();");
}

function AddTraffic(){
	
	$obj = new ATN_traffic();
	$obj->TrafficDate = PDONOW;
	$obj->TrafficTime = DateModules::NowTime();
	$obj->IsSystemic = "YES";
	$obj->PersonID = $_SESSION["USER"]["PersonID"];
	$result = $obj->Add();
	
	//print_r(ExceptionHandler::PopAllExceptions());
	echo Response::createObjectiveResponse($result, ExceptionHandler::GetExceptionsToString());
	die();
}

?>
