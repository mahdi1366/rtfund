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

global $GToDate;
//$GToDate = '2018-03-20'; //1396/12/29
$GToDate = '2019-03-20'; //1397/12/29

$reqs = PdoDataAccess::runquery_fetchMode(" select r.RequestID from LON_requests r
	join LON_ReqParts p on(r.RequestID=p.RequestID AND IsHistory='NO')
	where PartDate<='$GToDate'  " . 
		(!empty($_REQUEST["ReqID"]) ? " AND r.RequestID >= ".$_REQUEST["ReqID"] : "" ) . "
		AND ComputeMode<>'NEW' AND IsEnded='NO' AND StatusID=" . LON_REQ_STATUS_CONFIRM . " 
		order by RequestID");
//echo PdoDataAccess::GetLatestQueryString();
$pdo = PdoDataAccess::getPdoObject();

$DocObj = array();

while($requset=$reqs->fetch())
{
	$pdo->beginTransaction();
	echo "-------------- " . $requset["RequestID"] . " -----------------<br>";
	ob_flush();flush();
	
	$reqObj = new LON_requests($requset["RequestID"]);
	$partObj = LON_ReqParts::GetValidPartObj($requset["RequestID"]);
	
	$DocObj[ $reqObj->RequestID ] = null;
	
	Allocate($reqObj, $partObj, $DocObj[ $reqObj->RequestID ], $pdo);
	$DocObj[ $reqObj->RequestID ] = null;
	Contract($reqObj, $partObj, $DocObj[ $reqObj->RequestID ], $pdo);
	$DocObj[ $reqObj->RequestID ] = null;
	Payment($reqObj, $partObj, $DocObj[ $reqObj->RequestID ], $pdo);
	$DocObj[ $reqObj->RequestID ] = null;
	PaymentCheque($reqObj, $partObj, $DocObj[ $reqObj->RequestID ], $pdo);
	$DocObj[ $reqObj->RequestID ] = null;
	BackPayCheques($reqObj, $partObj, $DocObj[ $reqObj->RequestID ], $pdo);
	$DocObj[ $reqObj->RequestID ] = null;
	BackPay($reqObj, $partObj, $DocObj[ $reqObj->RequestID ], $pdo);
	$DocObj[ $reqObj->RequestID ] = null;
	DailyIncome($reqObj, $partObj, $pdo);
	DailyWage($reqObj, $partObj, $DocObj[ $reqObj->RequestID ], $pdo);
	$DocObj[ $reqObj->RequestID ] = null;
	
	//--------------------------------------------------
	$pdo->commit();

	EventComputeItems::$LoanComputeArray = array();
	EventComputeItems::$LoanPuresArray = array();
}
/*$arr = get_defined_vars();
print_r($arr);
die();*/
/**
 * رویداد تخصیص 
 */
function Allocate($reqObj , $partObj, &$DocObj, $pdo){
	
	global $GToDate;
	if($reqObj->ReqPersonID*1 == 0)
		return;
	
	$EventID = EVENT_LOAN_ALLOCATE;

	$eventobj = new ExecuteEvent($EventID);
	$eventobj->DocObj = $DocObj;
	$eventobj->Sources = array($reqObj->RequestID, $partObj->PartID);
	$result = $eventobj->RegisterEventDoc($pdo);
	if($result)
		$DocObj = $eventobj->DocObj;
	echo "تخصیص وام : " . ($result ? "true" : "false") . "<br>";
	if(ExceptionHandler::GetExceptionCount() > 0)
	{
		print_r(ExceptionHandler::PopAllExceptions());
		$pdo->rollBack();
		return;
	}
	ob_flush();flush();
	
}

/**
 * رویداد عقد قرارداد
 */
function Contract($reqObj , $partObj, &$DocObj, $pdo){
	
	global $GToDate;
	
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
	$eventobj->DocObj = $DocObj;
	$eventobj->Sources = array($reqObj->RequestID, $partObj->PartID);
	$result = $eventobj->RegisterEventDoc($pdo);
	if($result)
		$DocObj = $eventobj->DocObj;
	echo "عقد قرارداد : " . ($result ? "true" : "false") . "<br>";
	if(ExceptionHandler::GetExceptionCount() > 0)
	{
		print_r(ExceptionHandler::PopAllExceptions());
		$pdo->rollBack();
		return;
	}
	ob_flush();flush();
}

/**
 * رویدادهای پرداخت وام
 */
function Payment($reqObj , $partObj, &$DocObj, $pdo){
	
	global $GToDate;
	
	if($reqObj->ReqPersonID*1 > 0)
		$EventID = EVENT_LOANPAYMENT_agentSource;
	else
		$EventID = EVENT_LOANPAYMENT_innerSource;
	
	$pays = PdoDataAccess::runquery("select * from LON_payments "
			. " where PayDate<='$GToDate' AND RequestID=?", array($reqObj->RequestID));
	
	foreach($pays as $pay)
	{
		$eventobj = new ExecuteEvent($EventID);
		$eventobj->DocObj = $DocObj;
		$eventobj->Sources = array($reqObj->RequestID, $partObj->PartID, $pay["PayID"]);
		$result = $eventobj->RegisterEventDoc($pdo);
		if($result)
			$DocObj = $eventobj->DocObj;
	}
	echo "پرداخت وام : " . ($result ? "true" : "false") . "<br>";
	if(ExceptionHandler::GetExceptionCount() > 0)
	{
		print_r(ExceptionHandler::PopAllExceptions());
		$pdo->rollBack();
		return;
	}
	ob_flush();flush();
}

/**
 * وصول چک وام 
 */
function PaymentCheque($reqObj , $partObj, &$DocObj, $pdo){
	
	global $GToDate;
	
	$pays = PdoDataAccess::runquery("select * from LON_payments "
			. " where PayDate<='$GToDate' AND RequestID=?", array($reqObj->RequestID));	
	
	foreach($pays as $pay)
	{
		$eventobj = new ExecuteEvent(EVENT_LOANCHEQUE_payed);
		$eventobj->DocObj = $DocObj;
		$eventobj->Sources = array($reqObj->RequestID, $partObj->PartID, $pay["PayID"]);
		$eventobj->AllRowsAmount = $pay["PayAmount"];
		$result = $eventobj->RegisterEventDoc($pdo);		
		if($result)
			$DocObj = $eventobj->DocObj;
	}
	echo "وصول چک وام : " . ($result ? "true" : "false") . "<br>";
	if(ExceptionHandler::GetExceptionCount() > 0)
	{
		print_r(ExceptionHandler::PopAllExceptions());
		$pdo->rollBack();
		return;
	}
	ob_flush();flush();
}

/**
 * رویدادهای دریافت چک
 */
function BackPayCheques($reqObj , $partObj, &$DocObj, $pdo){
	
	global $GToDate;
	$result = true;
	$cheques = PdoDataAccess::runquery(
			"select * from LON_BackPays
				join ACC_IncomeCheques i using(IncomeChequeID) 
				where RequestID=? order by PayDate"
			, array($reqObj->RequestID));
	foreach($cheques as $bpay)
	{
		if($reqObj->ReqPersonID*1 > 0)
			$EventID = EVENT_LOANCHEQUE_agentSource;
		else
			$EventID = EVENT_LOANCHEQUE_innerSource;
		
		$eventobj = new ExecuteEvent($EventID);
		$eventobj->Sources = array($reqObj->RequestID, $bpay["IncomeChequeID"]);
		$eventobj->DocObj = $DocObj;
		$eventobj->AllRowsAmount = $bpay["PayAmount"];
		$result = $eventobj->RegisterEventDoc($pdo);
		if($result)
			$DocObj = $eventobj->DocObj;
	}
	echo "دریافت چک : " . ($result ? "true" : "false") . "<br>";
	if(ExceptionHandler::GetExceptionCount() > 0)
	{
		print_r(ExceptionHandler::PopAllExceptions());
		$pdo->rollBack();
		return;
	}
	ob_flush();flush();
	//--------------در جریان وصول------------------------
	$cheques = PdoDataAccess::runquery(
			"select * from LON_BackPays
				join ACC_IncomeCheques i using(IncomeChequeID) 
				where RequestID=? AND ChequeStatus <>".INCOMECHEQUE_NOTVOSUL." order by PayDate"
			, array($reqObj->RequestID));
	foreach($cheques as $bpay)
	{
		if($reqObj->ReqPersonID*1 > 0)
			$EventID = EVENT_CHEQUE_SENDTOBANK_agent;
		else
			$EventID = EVENT_CHEQUE_SENDTOBANK_inner;
		
		$eventobj = new ExecuteEvent($EventID);
		$eventobj->Sources = array($reqObj->RequestID, $bpay["IncomeChequeID"]);
		$eventobj->DocObj = $DocObj;
		$eventobj->AllRowsAmount = $bpay["PayAmount"];
		$result = $eventobj->RegisterEventDoc($pdo);
		if($result)
			$DocObj = $eventobj->DocObj;
	}
	echo " چکهای درجریان وصول : " . ($result ? "true" : "false") . "<br>";
	if(ExceptionHandler::GetExceptionCount() > 0)
	{
		print_r(ExceptionHandler::PopAllExceptions());
		$pdo->rollBack();
		return;
	}
	ob_flush();flush();
	//--------------برگشتی------------------------
	$cheques = PdoDataAccess::runquery(
			"select * from LON_BackPays
				join ACC_IncomeCheques i using(IncomeChequeID) 
				where RequestID=? AND ChequeStatus=".INCOMECHEQUE_BARGASHTI." order by PayDate"
			, array($reqObj->RequestID));
	foreach($cheques as $bpay)
	{
		if($reqObj->ReqPersonID*1 > 0)
			$EventID = EVENT_CHEQUE_BARGASHT_agent;
		else
			$EventID = EVENT_CHEQUE_BARGASHT_inner;
		
		$eventobj = new ExecuteEvent($EventID);
		$eventobj->Sources = array($reqObj->RequestID, $bpay["IncomeChequeID"]);
		$eventobj->DocObj = $DocObj;
		$eventobj->AllRowsAmount = $bpay["PayAmount"];
		$result = $eventobj->RegisterEventDoc($pdo);
		if($result)
			$DocObj = $eventobj->DocObj;
	}
	echo " چکهای برگشتی : " . ($result ? "true" : "false") . "<br>";
	if(ExceptionHandler::GetExceptionCount() > 0)
	{
		print_r(ExceptionHandler::PopAllExceptions());
		$pdo->rollBack();
		return;
	}
	ob_flush();flush();
	//----------------مسترد و تعویض----------------------------
	$cheques = PdoDataAccess::runquery(
			"select * from LON_BackPays
				join ACC_IncomeCheques i using(IncomeChequeID) 
				where RequestID=? AND ChequeStatus in(".INCOMECHEQUE_CHANGE.",".INCOMECHEQUE_MOSTARAD.")
				order by PayDate"
			, array($reqObj->RequestID));
	foreach($cheques as $bpay)
	{
		if($reqObj->ReqPersonID*1 > 0)
			$EventID = EVENT_CHEQUE_MOSTARAD_agent;
		else
			$EventID = EVENT_CHEQUE_MOSTARAD_inner;
		
		$eventobj = new ExecuteEvent($EventID);
		$eventobj->Sources = array($reqObj->RequestID, $bpay["IncomeChequeID"]);
		$eventobj->DocObj = $DocObj;
		$eventobj->AllRowsAmount = $bpay["PayAmount"];
		$result = $eventobj->RegisterEventDoc($pdo);
		if($result)
			$DocObj = $eventobj->DocObj;
	}
	echo " چکهای مسترد : " . ($result ? "true" : "false") . "<br>";
	if(ExceptionHandler::GetExceptionCount() > 0)
	{
		print_r(ExceptionHandler::PopAllExceptions());
		$pdo->rollBack();
		return;
	}
	ob_flush();flush();
	//-------------------برگشتی مسترد-------------------------
	$cheques = PdoDataAccess::runquery(
			"select * from LON_BackPays
				join ACC_IncomeCheques i using(IncomeChequeID) 
				where RequestID=? AND ChequeStatus=".INCOMECHEQUE_BARGASHTI_MOSTARAD." order by PayDate"
			, array($reqObj->RequestID));
	foreach($cheques as $bpay)
	{
		if($reqObj->ReqPersonID*1 > 0)
			$EventID = EVENT_CHEQUE_BARGASHTMOSTARAD_agent;
		else
			$EventID = EVENT_CHEQUE_BARGASHTMOSTARAD_inner;
		
		$eventobj = new ExecuteEvent($EventID);
		$eventobj->Sources = array($reqObj->RequestID, $bpay["IncomeChequeID"]);
		$eventobj->DocObj = $DocObj;
		$eventobj->AllRowsAmount = $bpay["PayAmount"];
		$result = $eventobj->RegisterEventDoc($pdo);
		if($result)
			$DocObj = $eventobj->DocObj;
	}
	echo " چکهای برگشتی مسترد : " . ($result ? "true" : "false") . "<br>";
	if(ExceptionHandler::GetExceptionCount() > 0)
	{
		print_r(ExceptionHandler::PopAllExceptions());
		$pdo->rollBack();
		return;
	}
	ob_flush();flush();
}

/**
 * رویدادهای بازپرداخت
 */
function BackPay($reqObj , $partObj, &$DocObj, $pdo){
	global $GToDate;
	$result = true;
	$backpays = PdoDataAccess::runquery(
			"select * from LON_BackPays
				left join ACC_IncomeCheques i using(IncomeChequeID) 
				where RequestID=? AND PayDate<='$GToDate'
			AND if(PayType=" . BACKPAY_PAYTYPE_CHEQUE . ",ChequeStatus=".INCOMECHEQUE_VOSUL.",1=1)
			order by PayDate", array($reqObj->RequestID));
	
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
		if($result)
			$DocObj = $eventobj->DocObj;
	}
	echo "بازپرداخت : " . ($result ? "true" : "false") . "<br>";
	if(ExceptionHandler::GetExceptionCount() > 0)
	{
		print_r(ExceptionHandler::PopAllExceptions());
		$pdo->rollBack();
		return;
	}
	ob_flush();flush();
}

/**
 * رویدادهای روزانه
 */
function DailyIncome($reqObj , $partObj, $pdo){
	
	global $GToDate;
	$JToDate = DateModules::miladi_to_shamsi($GToDate);
	 
	$days = PdoDataAccess::runquery_fetchMode("select * from jdate where Jdate between ? AND '".$JToDate."'", 
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
	$EventObj->ComputedItems[ "80" ] = 0;
	$EventObj->ComputedItems[ "81" ] = 0;
	while($day = $days->fetch())
	{
		$EventObj->Sources = array($reqObj->RequestID, $partObj->PartID, $day["gdate"]);
		$EventObj->ComputedItems[ 80 ] += EventComputeItems::LoanDaily("80",$EventObj->Sources)*1;
		$EventObj->ComputedItems[ 81 ] += EventComputeItems::LoanDaily("81",$EventObj->Sources)*1;
	}
	$result = $EventObj->RegisterEventDoc($pdo);
	echo "روزانه : " . ($result ? "true" : "false") . "<br>";
	if(ExceptionHandler::GetExceptionCount() > 0)
	{
		print_r(ExceptionHandler::PopAllExceptions());
		$pdo->rollBack();
		return;
	}
	ob_flush();flush();
}

/**
 * 	رویدادهای روزانه تاخیر و جریمه
 */
function DailyWage($reqObj , $partObj, &$DocObj, $pdo){
	
	global $GToDate;	
	$result = true;
	$computeArr = LON_Computes::ComputePayments($reqObj->RequestID, $GToDate);
	$totalLate = 0;
	$totalPenalty = 0;
	
	foreach($computeArr as $row)
	{
		if($row["type"] == "installment" && $row["InstallmentID"]*1 > 0 && count($row["pays"])>0)
		{
			foreach($row["pays"] as $pay)
			{
				$totalLate += $pay["cur_late"]*1;
				$totalPenalty += $pay["cur_pnlt"]*1;
			}
		}
	}
	$EventObj1 = new ExecuteEvent($reqObj->ReqPersonID*1 == 0 ? EVENT_LOANDAILY_innerLate : 
																EVENT_LOANDAILY_agentlate);
	$EventObj1->ComputedItems[ 82 ] = round(($partObj->FundWage/$partObj->CustomerWage)*$totalLate);
	$EventObj1->ComputedItems[ 83 ] = $totalLate - round(($partObj->FundWage/$partObj->CustomerWage)*$totalLate);
	if($EventObj1->ComputedItems[ 82 ] > 0 || $EventObj1->ComputedItems[ 83 ] > 0)
	{
		$EventObj1->DocObj = $DocObj;
		$EventObj1->Sources = array($reqObj->RequestID, $partObj->PartID, $GToDate);
		$result = $EventObj1->RegisterEventDoc($pdo);
		if($result)
			$DocObj = $EventObj1->DocObj;
		echo "شناسایی کارمزد تاخیر : " . ($result ? "true" : "false"). "<br>"; 
		if(ExceptionHandler::GetExceptionCount() > 0)
		{
			print_r(ExceptionHandler::PopAllExceptions());
			$pdo->rollBack();
			return;
		}
		ob_flush();flush();
	}
	//-----------------------------------------------------
	$EventObj2 = new ExecuteEvent($reqObj->ReqPersonID*1 == 0 ? EVENT_LOANDAILY_innerPenalty : 
																EVENT_LOANDAILY_agentPenalty);
	$EventObj2->ComputedItems[ 84 ] = $partObj->ForfeitPercent == 0? 0 :
			round(($partObj->FundForfeitPercent/$partObj->ForfeitPercent)*$totalPenalty);
	$EventObj2->ComputedItems[ 85 ] = $partObj->ForfeitPercent == 0? 0 : 
			$totalPenalty - round(($partObj->FundForfeitPercent/$partObj->ForfeitPercent)*$totalPenalty);
	
	if($EventObj2->ComputedItems[ 84 ] > 0 || $EventObj2->ComputedItems[ 85 ] > 0)
	{
		$EventObj2->DocObj = $DocObj;
		$EventObj2->Sources = array($reqObj->RequestID, $partObj->PartID, $GToDate);
		$result = $EventObj2->RegisterEventDoc($pdo);
		if($result)
			$DocObj = $EventObj2->DocObj;
		echo "شناسایی جریمه تاخیر : " . ($result ? "true" : "false") . "<br>";
		if(ExceptionHandler::GetExceptionCount() > 0)
		{
			print_r(ExceptionHandler::PopAllExceptions());
			$pdo->rollBack();
			return;
		}
		ob_flush();flush();
	}
}
?>
