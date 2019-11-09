<?php
//---------------------------
// programmer:	Jafarkhani
// create Date: 97.12
//---------------------------
 
require_once '../header.inc.php';
require_once '../commitment/ExecuteEvent.class.php';
require_once '../loan/request/request.class.php';

ini_set("display_errors", "On");
ini_set('max_execution_time', 30000);
ini_set('memory_limit','2000M');
header("X-Accel-Buffering: no");
ob_start();
set_time_limit(0);
error_reporting(0);

global $GFromDate;
$GFromDate = '2019-03-21'; //1398/01/01
global $GToDate;
$GToDate = '2019-10-19'; //1398/07/27

$reqs = PdoDataAccess::runquery_fetchMode(" select * from tmp_warDocs ");
//echo PdoDataAccess::GetLatestQueryString();
$pdo = PdoDataAccess::getPdoObject();

$DocObj = array();

while($requset=$reqs->fetch())
{
	$pdo->beginTransaction();
	$reqObj = new WAR_requests($requset["SourceID2"]);
	
	echo "-------------- " . $reqObj->RequestID . "  " . $reqObj->RefRequestID . " -----------------<br>";
	ob_flush();flush();
	
	if($requset["SourceType"] == "13")
	{
		if($reqObj->RefRequestID != $reqObj->RequestID)
		{
			Extend($reqObj, $DocObj[ $reqObj->RequestID ], $pdo);
			die();
		}
		//else
		//	Register($reqObj, $DocObj[ $reqObj->RequestID ], $pdo);
		
		$DocObj[ $reqObj->RequestID ] = null;
	}	
	
	/*if($requset["SourceType"] == "18")
	{
		cancel($reqObj, $DocObj[ $reqObj->RequestID ], $pdo);
		$DocObj[ $reqObj->RequestID ] = null;
	}*/
	
	$pdo->commit();

	EventComputeItems::$LoanComputeArray = array();
	EventComputeItems::$LoanPuresArray = array();
}

function Register($reqObj, &$DocObj, $pdo){
		
	switch($reqObj->TypeID)
	{
		case "2" : $EventID= EVENT_WAR_REG_2 ;break;
		case "3" : $EventID= EVENT_WAR_REG_3 ;break;
		case "4" : $EventID= EVENT_WAR_REG_4 ;break;
		case "6" : $EventID= EVENT_WAR_REG_6 ;break;
		case "7" : $EventID= EVENT_WAR_REG_7 ;break;
		default  : $EventID= EVENT_WAR_REG_other ;break;
	}

	$eventobj = new ExecuteEvent($EventID);
	$eventobj->DocObj = $DocObj;
	$eventobj->DocDate = $reqObj->StartDate;
	$eventobj->Sources = array($reqObj->RequestID);
	$result = $eventobj->RegisterEventDoc($pdo);
	if($result)
		$DocObj = $eventobj->DocObj;
	echo "تخصیص ضمانتنامه : " . ($result ? "true" : "false") . "<br>";
	if(ExceptionHandler::GetExceptionCount() > 0)
	{
		print_r(ExceptionHandler::PopAllExceptions());
		$pdo->rollBack();
		return;
	}
	ob_flush();flush();
	
}

function Extend($reqObj , &$DocObj, $pdo){
	
	switch($reqObj->TypeID)
	{
		case "2" : $EventID= EVENT_WAR_EXTEND_2 ;break;
		case "3" : $EventID= EVENT_WAR_EXTEND_3 ;break;
		case "4" : $EventID= EVENT_WAR_EXTEND_4 ;break;
		case "6" : $EventID= EVENT_WAR_EXTEND_6 ;break;
		case "7" : $EventID= EVENT_WAR_EXTEND_7 ;break;
		default  : $EventID= EVENT_WAR_EXTEND_other ;break;
	}

	$eventobj = new ExecuteEvent($EventID);
	$eventobj->DocObj = $DocObj;
	$eventobj->DocDate = $reqObj->StartDate;
	$eventobj->Sources = array($reqObj->RequestID);
	$result = $eventobj->RegisterEventDoc($pdo);
	if($result)
		$DocObj = $eventobj->DocObj;
	echo "تمدید ضمانتنامه  : " . ($result ? "true" : "false") . "<br>";
	if(ExceptionHandler::GetExceptionCount() > 0)
	{
		print_r(ExceptionHandler::PopAllExceptions());
		$pdo->rollBack();
		return;
	}
	ob_flush();flush();
	
}

function cancel($reqObj , &$DocObj, $pdo){
	
	switch($reqObj->TypeID)
	{
		case "2" : $EventID=  EVENT_WAR_CANCEL_2; break;
		case "3" : $EventID= EVENT_WAR_CANCEL_3 ;break;
		case "4" : $EventID= EVENT_WAR_CANCEL_4 ;break;
		case "6" : $EventID= EVENT_WAR_CANCEL_6 ;break;
		case "7" : $EventID= EVENT_WAR_CANCEL_7 ;break;
		default  : $EventID= EVENT_WAR_CANCEL_other ;break;
	}

	$eventobj = new ExecuteEvent($EventID);
	$eventobj->DocObj = $DocObj;
	$eventobj->DocDate = $reqObj->CancelDate;
	$eventobj->Sources = array($reqObj->RequestID);
	$result = $eventobj->RegisterEventDoc($pdo);
	if($result)
		$DocObj = $eventobj->DocObj;
	echo "تمدید ضمانتنامه  : " . ($result ? "true" : "false") . "<br>";
	if(ExceptionHandler::GetExceptionCount() > 0)
	{
		print_r(ExceptionHandler::PopAllExceptions());
		$pdo->rollBack();
		return;
	}
	ob_flush();flush();
	
}
?>
