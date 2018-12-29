<?php
//-----------------------------
//	Programmer	: SH.Jafarkhani
//	Date		: 97.09
//-----------------------------
 
require_once '../header.inc.php';
require_once getenv("DOCUMENT_ROOT") . '/framework/person/persons.class.php';
require_once getenv("DOCUMENT_ROOT") . '/loan/loan/loan.class.php';
require_once getenv("DOCUMENT_ROOT") . '/loan/request/compute.inc.php';
require_once getenv("DOCUMENT_ROOT") . '/accounting/docs/doc.class.php';
require_once getenv("DOCUMENT_ROOT") . '/accounting/baseinfo/baseinfo.class.php';

require_once inc_dataReader;
require_once inc_response;

function CheckCloseCycle($CycleID = ""){
	
	if(ACC_cycles::IsClosed($CycleID))
	{
		echo Response::createObjectiveResponse(false, "دوره مالی جاری بسته شده است و قادر به اعمال تغییرات نمی باشید");
		die();	
	}
}

function FindCostID($costCode){
	
	$dt = PdoDataAccess::runquery("select * from ACC_CostCodes where IsActive='YES' AND CostCode=?",
		array($costCode));
	
	return count($dt) == 0 ? false : $dt[0]["CostID"];
}

function FindTafsiliID($TafsiliCode, $TafsiliType){
	
	if($TafsiliType == TAFTYPE_PERSONS)
		$dt = PdoDataAccess::runquery("select * from ACC_tafsilis "
			. "where IsActive='YES' AND ObjectID=? AND TafsiliType=?",
		array($TafsiliCode, $TafsiliType));
	else
		$dt = PdoDataAccess::runquery("select * from ACC_tafsilis "
			. "where IsActive='YES' AND TafsiliCode=? AND TafsiliType=?",
		array($TafsiliCode, $TafsiliType));
	
	if(count($dt) == 0)
	{
		ExceptionHandler::PushException("تفصیلی مربوطه یافت نشد.[" . $TafsiliType . "-" .  $TafsiliCode . "]");
		return false;
	}
	
	return $dt[0]["TafsiliID"];
}
 
//---------------------------------------------------------------
/**
 * صدور سند تعهدی پرداخت وام
 * 
 * @param type $ReqObj
 * @param type $PartObj
 * @param type $PayObj
 * @param type $BankTafsili
 * @param type $AccountTafsili
 * @param type $ChequeNo
 * @param type $pdo
 * @param type $DocID
 */
function COM_RegisterPayLoan($ReqObj, $PartObj, $PayObj, $BankTafsili, $AccountTafsili, $ChequeNo, $pdo, $DocID=""){
	
	/* @var $ReqObj LON_requests */
	/* @var $PartObj LON_ReqParts */
	/* @var $PayObj LON_payments */
	
	$EventID = 1010101;
	
	$obj = new ExecuteEvent($EventID, $ReqObj->BranchID);
	$obj->DocObj = new ACC_docs($DocID);
	$obj->EventFunction = "EventComputeItems::PayLoan";
	$obj->EventFunctionParams = array($ReqObj, $PartObj, $PayObj);
	$obj->Sources = array($ReqObj->RequestID, $PartObj->PartID, $PayObj->PayID);
	$obj->tafsilis = array(
		TAFTYPE_BANKS => $BankTafsili,
		TAFTYPE_ACCOUNTS => $AccountTafsili
	);
	
	$result = $obj->RegisterEventDoc($pdo);
	if(!$result)
		return false;
	
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
	
	return $obj->DocObj->DocID;
}


?>
