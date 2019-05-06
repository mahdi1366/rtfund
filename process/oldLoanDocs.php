<?php
//---------------------------
// programmer:	Jafarkhani
// create Date: 97.12
//---------------------------

require_once '../header.inc.php';
require_once '../commitment/ExecuteEvent.class.php';
require_once '../loan/request/request.class.php';

ini_set('max_execution_time', 3000);
ini_set('memory_limit','1000M');

$reqs = PdoDataAccess::runquery(" select r.RequestID from LON_requests r
	join LON_ReqParts p on(r.RequestID=p.RequestID AND IsHistory='NO')
	where PartDate<'2019-03-21' /*AND r.RequestID=534*/ AND
		ComputeMode='NEW' AND IsEnded='NO' AND StatusID=" . LON_REQ_STATUS_CONFIRM );

$pdo = PdoDataAccess::getPdoObject();

foreach($reqs as $requset)
{
	$pdo->beginTransaction();
	echo "-------------- " . $requset["RequestID"] . " -----------------<br>";
	ob_flush();flush();
	
	$reqObj = new LON_requests($requset["RequestID"]);
	$partObj = LON_ReqParts::GetValidPartObj($requset["RequestID"]);
	
	//----------------- رویداد عقد قرارداد----------------------------
	if($reqObj->ReqPersonID*1 == 0)
		$EventID = EVENT_LOANCONTRACT_innerSource;
	else
	{
		if($reqObj->FundGuarantee == "YES")
			$EventID = EVENT_LOANCONTRACT_agentSource_committal;
		else
			$EventID = EVENT_LOANCONTRACT_agentSource_non_committal;
	}

	$eventobj = new ExecuteEvent($EventID);
	$eventobj->Sources = array($reqObj->RequestID, $partObj->PartID);
	$result = $eventobj->RegisterEventDoc($pdo);
	
	echo "عقد قرارداد : " . ($result ? "true" : "false") . "<br>";
	if(ExceptionHandler::GetExceptionCount() > 0)
	{
		print_r(ExceptionHandler::PopAllExceptions());
		$pdo->rollBack();
		continue;
	}
	ob_flush();flush();
	//---------------- رویدادهای پرداخت وام----------------------------
	if($reqObj->ReqPersonID*1 > 0)
		$EventID = EVENT_LOANPAYMENT_agentSource;
	else
		$EventID = EVENT_LOANPAYMENT_innerSource;
	$pays = PdoDataAccess::runquery("select * from LON_payments where RequestID=?", array($reqObj->RequestID));
	
	$eventobj = new ExecuteEvent($EventID);
	foreach($pays as $pay)
	{
		$eventobj->Sources = array($reqObj->RequestID, $partObj->PartID, $pay["PayID"]);
		$result = $eventobj->RegisterEventDoc($pdo);
	}
	echo "پرداخت وام : " . ($result ? "true" : "false") . "<br>";
	if(ExceptionHandler::GetExceptionCount() > 0)
	{
		print_r(ExceptionHandler::PopAllExceptions());
		$pdo->rollBack();
		continue;
	}
	ob_flush();flush();
	//---------------  رویدادهای بازپرداخت----------------------------
	$backpays = LON_BackPays::GetRealPaid($reqObj->RequestID);
	$DocObj = null;
	foreach($backpays as $bpay)
	{
		if($reqObj->ReqPersonID*1 > 0)
		{
			if($reqObj->FundGuarantee == "YES")
			{
				if($bpay["IncomeChequeID"]*1 > 0)
					$EventID = EVENT_LOANBACKPAY_agentSource_committal_cheque;
				else
					$EventID = EVENT_LOANBACKPAY_agentSource_committal_non_cheque;
			}
			else
			{
				if($bpay["IncomeChequeID"]*1 > 0)
					$EventID = EVENT_LOANBACKPAY_agentSource_non_committal_cheque;
				else
					$EventID = EVENT_LOANBACKPAY_agentSource_non_committal_non_cheque;
			}
		}
		else
		{
			if($bpay["IncomeChequeID"]*1 > 0)
				$EventID = EVENT_LOANBACKPAY_innerSource_cheque;
			else
				$EventID = EVENT_LOANBACKPAY_innerSource_non_cheque;
		}
		$eventobj = new ExecuteEvent($EventID);
		$eventobj->Sources = array($reqObj->RequestID, $partObj->PartID, $bpay["BackPayID"]);
		$eventobj->DocObj = $DocObj;
		$result = $eventobj->RegisterEventDoc($pdo);
		$DocObj = $eventobj->DocObj;
	}
	echo "بازپرداخت : " . ($result ? "true" : "false") . "<br>";
	if(ExceptionHandler::GetExceptionCount() > 0)
	{
		print_r(ExceptionHandler::PopAllExceptions());
		$pdo->rollBack();
		continue;
	}
	ob_flush();flush();
	//---------------  رویدادهای روزانه----------------------------
	/*$params = array();
	$days = PdoDataAccess::runquery_fetchMode("select * from jdate where Jdate between ? AND '1397/12/29'", 
			DateModules::miladi_to_shamsi($partObj->PartDate), $pdo);
	echo "days : " . $days->rowCount() . "<br>";
	ob_flush();flush();
	echo " ";
	ob_flush();flush();
	if($reqObj->ReqPersonID*1 == 0)
		$EventID = EVENT_LOANDAILY_innerSource;
	else
	{
		if($reqObj->FundGuarantee == "YES")
			$EventID = EVENT_LOANDAILY_agentSource_committal;
		else
			$EventID = EVENT_LOANDAILY_agentSource_non_committal;
	}
	$EventObj = new ExecuteEvent($EventID);
	while($day = $days->fetch())
	{
		$EventObj->Sources = array($reqObj->RequestID, $partObj->PartID, $day["gdate"]);
		$result = $EventObj->RegisterEventDoc($pdo);
	}
	echo "روزانه : " . ($result ? "true" : "false") . "<br>";
	if(ExceptionHandler::GetExceptionCount() > 0)
	{
		print_r(ExceptionHandler::PopAllExceptions());
		$pdo->rollBack();
		continue;
	}
	ob_flush();flush();
	*/
	//--------------------------------------------------
	$pdo->commit();
	
	EventComputeItems::$LoanBackPayArray = array();
	EventComputeItems::$LoanPuresArray = array();
}
?>
