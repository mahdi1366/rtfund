<?php
//---------------------------
// developer:	Sh.Jafarkhani
// Date:		97.10
//---------------------------
ini_set("display_errors", "On");
require_once '../header.inc.php';
require_once(inc_response);
require_once(inc_dataReader);
require_once './baseinfo/baseinfo.class.php';
require_once '../loan/request/request.class.php';
require_once './ComputeItems.class.php';
require_once './ExecuteEvent.class.php';
	
$task = isset($_REQUEST['task']) ? $_REQUEST['task'] : '';

switch ($task) {
	
	case "selectEventRows":
	case "RegisterEventDoc":
	
		$task();
}

function selectEventRows(){
	
	$EventID = $_REQUEST["EventID"]*1;
	$where = " er.IsActive='YES' AND EventID=? ";
	$where .= " order by CostType,CostCode";
	$list = COM_EventRows::SelectAll($where, array($EventID));
	
	//-------------- set source objects ----------------
	$SourceObjects = array();
	$fn = "";
	switch($EventID)
	{
		case EVENT_LOAN_PAYMENT:
			$ReqObj = new LON_requests((int)$_REQUEST["RequestID"]);
			$PartObj = LON_ReqParts::GetValidPartObj($ReqObj->RequestID);
			$PayObj = new LON_payments((int)$_REQUEST["PayID"]);
			$SourceObjects = array($ReqObj, $PartObj, $PayObj);
			$fn = "EventComputeItems::PayLoan";
			break;
		case EVENT_LOAN_BACKPAY:
			$ReqObj = new LON_requests((int)$_REQUEST["RequestID"]);
			$PartObj = LON_ReqParts::GetValidPartObj($ReqObj->RequestID);
			$BackPayObj = new LON_BackPays((int)$_REQUEST["BackPayID"]);
			$SourceObjects = array($ReqObj, $PartObj, $BackPayObj);
			$fn = "EventComputeItems::LoanBackPay";
			break;
	}
	//--------------- get compute items values -----------
	$computedValues = array();
	for($i=0; $i < count($list); $i++)
	{
		if($list[$i]["ComputeItemID"]*1 > 0 && $fn != "")
		{
			if(isset($computedValues[ $list[$i]["ComputeItemID"] ]))
				$value = $computedValues[ $list[$i]["ComputeItemID"] ];
			else
			{
				$value = call_user_func($fn, $list[$i]["ComputeItemID"], $SourceObjects);
			}
			$computedValues[ $list[$i]["ComputeItemID"] ] = $value;
			
			if($list[$i]["CostType"] == "DEBTOR")
				$list[$i]["DebtorAmount"] = $value;
			else
				$list[$i]["CreditorAmount"] = $value;
		}
		if($list[$i]["Tafsili"]*1 > 0)
		{
			$res = EventComputeItems::GetTafsilis($list[$i]["Tafsili"],$SourceObjects);
			$list[$i]["TafsiliValue1"] = $res[2];
		}
		if($list[$i]["Tafsili2"]*1 > 0)
		{
			$res = EventComputeItems::GetTafsilis($list[$i]["Tafsili2"],$SourceObjects);
			$list[$i]["TafsiliValue2"] = $res[2];
		}
		if($list[$i]["Tafsili3"]*1 > 0)
		{
			$res = EventComputeItems::GetTafsilis($list[$i]["Tafsili3"],$SourceObjects);
			$list[$i]["TafsiliValue3"] = $res[2];
		}
	}
	
	//print_r(ExceptionHandler::PopAllExceptions());
	//echo PdoDataAccess::GetLatestQueryString();
	echo dataReader::getJsonData($list, count($list), $_GET['callback']);
	die();
}

function RegisterEventDoc(){
	
	$EventID = (int)$_POST["EventID"];
	$SourceIDs = $_POST["SourcesArr"];
	
	$pdo = PdoDataAccess::getPdoObject();
	$pdo->beginTransaction();
	
	$obj = new ExecuteEvent($EventID);
	$obj->SetSources($SourceIDs);
	$result = $obj->RegisterEventDoc($pdo);
	if(!$result)
	{
		$pdo->rollBack();
		print_r(ExceptionHandler::PopAllExceptions());
		Response::createObjectiveResponse(false, ExceptionHandler::GetExceptionsToString());
		die();
	}
	
	$pdo->commit();
	Response::createObjectiveResponse(true, "سند شماره " . $obj->DocObj->LocalNo . " با موفقیت صادر گردید");
	die();
	
	/*
	//-------------- ایجاد چک --------------------
	
	$dt = PdoDataAccess::runquery("select * from ACC_tafsilis where TafsiliType=" . TAFTYPE_ACCOUNTS . 
			" AND TafsiliID=? ", array($AccountTafsili));
	$AccountID = (count($dt) > 0) ? $dt[0]["ObjectID"] : "";
	
	$chequeObj = new ACC_DocCheques();
	$chequeObj->DocID = $obj->DocObj->DocID;
	$chequeObj->CheckDate = $PayObj->PayDate;
	$chequeObj->amount = EventComputeItems::PayLoan(3, $obj->EventFunctionParams);
	$chequeObj->TafsiliID = EventComputeItems::GetTafsilis(EventComputeItems::Tafsili_LoanPersonID, TAFTYPE_PERSONS);
	$chequeObj->CheckNo = $ChequeNo;
	$chequeObj->AccountID = $AccountID ;
	$chequeObj->description = " پرداخت وام شماره " . $ReqObj->RequestID;
	$chequeObj->Add($pdo);
	
	//---------------------------------------------------------
	
	if(ExceptionHandler::GetExceptionCount() > 0)
		return false;
	
	return $obj->DocObj->DocID;*/
	
}

?>