<?php
//-----------------------------
//	Programmer	: SH.Jafarkhani
//	Date		: 94.06
//-----------------------------

require_once '../header.inc.php';
require_once '../docs/doc.class.php';
require_once '../docs/doc.data.php';
require_once inc_dataReader;
require_once inc_response;

if(!empty($_REQUEST["task"]))
	$_REQUEST["task"]();

function Equalization_UpdateChecks(){
	
	$BankID = $_POST["BankID"];
	$result = "";
	
	require_once("phpExcelReader.php");
	
	$data = new Spreadsheet_Excel_Reader();
    $data->setOutputEncoding('utf-8');
    $data->setRowColOffset(0);
	$data->read($_FILES["attach"]["tmp_name"]);
	
	$pdo = PdoDataAccess::getPdoObject();
	$pdo->beginTransaction();
	
	//----------- insert DocHeader --------------------
	$obj = new ACC_docs();
	$obj->RegDate = PDONOW;
	$obj->regPersonID = $_SESSION['USER']["PersonID"];
	$obj->DocDate = PDONOW;
	$obj->CycleID =  $_SESSION["accounting"]["CycleID"];
	$obj->BranchID = $_SESSION["accounting"]["BranchID"];
	$obj->DocType = DOCTYPE_EQUALCHECKS;
	$obj->description = "مغایرت گیری بانکی / به روز رسانی چک ها ";
	if(!$obj->Add($pdo))
	{
		ExceptionHandler::PushException("خطا در ایجاد سند");
		return false;
	}
	//--------------------------------------------------
	$CostCode_guaranteeAmount = FindCostID("904-02");
	$CostCode_guaranteeCount = FindCostID("904-01");
	$CostCode_guaranteeAmount2 = FindCostID("905-02");
	$CostCode_guaranteeCount2 = FindCostID("905-01");
	//--------------------------------------------------
	$SumAmount = 0;
	$countAmount = 0;
	
	for ($i = 1; $i <= $data->sheets[0]['numRows']; $i++) 
	{
		$checkNo = "";		
		switch($BankID)
		{
			case "4": // اقتصاد نوین
				if(empty($data->sheets[0]['cells'][$i][1]))
					continue;
					
				$cellData = $data->sheets[0]['cells'][$i][1];
				if(isset($cellData) && strpos(trim($cellData), "چک عادي ش.") !== false)
				{
					$arr = preg_split("/\//", $cellData);
					$checkNo = $arr[1];
				}
				break;
		}
		if($checkNo == "")
			continue;
	
		//---------------- add doc header --------------------
		$dt = PdoDataAccess::runquery("
			SELECT PayAmount,BackPayID,PartID,LoanPersonID ,RequestID, p.IsSupporter
				FROM LON_BackPays join LON_ReqParts using(PartID)
				join LON_requests using(RequestID)
				left join BSC_persons p on(p.PersonID=ReqPersonID)
				where ChequeNo=? AND ChequeStatus<>2",	array($checkNo), $pdo);

		if(count($dt) > 0)
		{
			$LoanPersonTafsili = FindTafsiliID($ReqObj->LoanPersonID, TAFTYPE_PERSONS);
			if(!$LoanPersonTafsili)
			{
				echo Response::createObjectiveResponse(false, "تفصیلی مربوط به شماره چک " . $checkNo . "یافت نشد.");
				die();
			}
			$itemObj = new ACC_DocItems();
			$itemObj->DocID = $obj->DocID;
			$itemObj->CostID = $CostCode_guaranteeAmount;
			$itemObj->DebtorAmount = 0;
			$itemObj->CreditorAmount = $dt[0]["PayAmount"];
			$itemObj->TafsiliType = TAFTYPE_PERSONS;
			$itemObj->TafsiliID = $LoanPersonTafsili;
			$itemObj->SourceType = DOCTYPE_DOCUMENT;
			$itemObj->SourceID = $row["BackPayID"];
			$itemObj->details = " وصول چک از طریق مغایرت بانکی";
			$itemObj->Add($pdo);
			
			$SumAmount += $row["ParamValue"]*1;
			$countAmount++;
			
			$PartObj = new LON_ReqParts($obj->PartID);
			$ReqObj = new LON_requests($PartObj->RequestID);
			$PersonObj = new BSC_persons($ReqObj->ReqPersonID);
			if($dt[0]["IsSupporter"] == "YES")
				$result = RegisterSHRTFUNDCustomerPayDoc(null, $obj, $_POST["BankTafsili"], $_POST["AccountTafsili"],  $pdo);
			else
				$result = RegisterCustomerPayDoc(null, $obj, $_POST["BankTafsili"], $_POST["AccountTafsili"],  $pdo);
			if(!$result)
			{
				$pdo->rollback();
				echo Response::createObjectiveResponse(false, "خطا در صدور سند حسابداری");
				die();
			}
			
			$result .= "شماره چک : " . $checkNo . " [ چک وام شماره " . 
					$dt[0]["RequestID"] . " به روز رسانی شد ]<br>";
		}
		else
		{
			$result .= "شماره چک : " . $checkNo . " [ ردیفی با این شماره چک یافت نشد ]<br>";
			
		}
	}
	//---------------------------------------------------------
	if($SumAmount > 0)
	{
		unset($itemObj->ItemID);
		unset($itemObj->TafsiliType);
		unset($itemObj->TafsiliID);
		unset($itemObj->TafsiliType2);
		unset($itemObj->TafsiliID2);
		unset($itemObj->details);
		$itemObj->CostID = $CostCode_guaranteeAmount2;
		$itemObj->DebtorAmount = $SumAmount;
		$itemObj->CreditorAmount = 0;	
		$itemObj->Add($pdo);

		unset($itemObj->ItemID);
		$itemObj->CostID = $CostCode_guaranteeCount;
		$itemObj->DebtorAmount = 0;
		$itemObj->CreditorAmount = $countAmount;	
		$itemObj->Add($pdo);

		unset($itemObj->ItemID);
		$itemObj->CostID = $CostCode_guaranteeCount2;
		$itemObj->DebtorAmount = $countAmount;
		$itemObj->CreditorAmount = 0;
		$itemObj->Add($pdo);
	}
		
	echo Response::createObjectiveResponse(true, $result == "" ? "هیچ چکی به روز نگردید" : $result);
	die();
}

?>
