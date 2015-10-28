<?php
//-----------------------------
//	Programmer	: SH.Jafarkhani
//	Date		: 94.06
//-----------------------------

require_once '../header.inc.php';
require_once 'doc.class.php';
require_once inc_dataReader;
require_once inc_response;

$task = isset($_POST ["task"]) ? $_POST ["task"] : (isset($_GET ["task"]) ? $_GET ["task"] : "");

switch ($task) {
	
	case "RegisterStartDoc":
		RegisterStartDoc();
}

function RegisterPayPartDoc($ReqObj, $PartObj, $pdo){
		
	/*$LocalNo = $_POST["LocalNo"];
	if($LocalNo != "")
	{
		$dt = PdoDataAccess::runquery("select * from ACC_docs 
			where BranchID=? AND CycleID=? AND LocalNo=?" , 

			array($_SESSION["accounting"]["BranchID"], 
				$_SESSION["accounting"]["CycleID"], 
				$LocalNo));

		if(count($dt) > 0)
		{
			echo Response::createObjectiveResponse(false, "شماره برگه وارد شده موجود می باشد");
			die();
		}
	}*/
	
	$CycleID = substr($PartObj->PayDate, 0 , 4);
	
	
	//---------------- account header doc --------------------
	$obj = new ACC_docs();
	$obj->RegDate = PDONOW;
	$obj->regPersonID = $_SESSION['USER']["PersonID"];
	$obj->DocDate = PDONOW;
	$obj->CycleID = $CycleID;
	$obj->BranchID = $ReqObj->BranchID;
	$obj->description = "پرداخت مرحله وام";
	$obj->DocType = "NORMAL";
	$obj->SourceType = "PAY_PART";
	$obj->SourceID = $PartObj->PartID;
	
	$result = $obj->Add($pdo);

	if (!$result) {
		$pdo->rollBack();
		print_r(ExceptionHandler::PopAllExceptions());
		echo Response::createObjectiveResponse(false, "");
		die();
	}
	
	//-------------------------------------------------------
	
	
	
	if(ExceptionHandler::GetExceptionCount() > 0)
	{
		$pdo->rollBack();
		echo Response::createObjectiveResponse(false, "");
		die();
	}
	
	if(PdoDataAccess::AffectedRows($pdo) == 0)
	{
		$pdo->rollBack();
		echo Response::createObjectiveResponse(false, "ردیفی برای صدور سند اختتامیه یافت نشد");
		die();
	}
	
	$pdo->commit();
	echo Response::createObjectiveResponse(true, "");
	die();
}

?>