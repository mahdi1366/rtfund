<?php
//-----------------------------
//	Programmer	: SH.Jafarkhani
//	Date		: 94.06
//-----------------------------

require_once '../header.inc.php';
require_once '../docs/import.data.php';
require_once '../../loan/request/request.class.php';
require_once '../cheque/cheque.class.php';
require_once inc_dataReader;
require_once inc_response;

class ACC_equalizations extends OperationClass{
	
	const TableName = "ACC_equalizations";
	const TableKey = "EqualizationID";
	
	public $EqualizationID;
	public $RegDate;
	public $BankID;
	public $ImportFile;
	
	function Add($pdo = null){
		
		if(!$pdo)
			$pdo = PdoDataAccess::getPdoObject();
		
		$stmt = $pdo->prepare("insert into ACC_equalizations(RegDate,BankID,ImportFile) 
			values(".PDONOW.",:b,:data)");
		
		$stmt->bindParam(":b", $this->BankID);
		$stmt->bindParam(":data", $this->ImportFile, PDO::PARAM_LOB);
		$stmt->execute();
		
		$this->EqualizationID = $pdo->lastInsertId();
		
		$daObj = new DataAudit();
		$daObj->ActionType = DataAudit::Action_add;
		$daObj->MainObjectID = $this->EqualizationID;
		$daObj->TableName = "ACC_equalizations";
		$daObj->execute();
		
		return true;
	}
}

if(!empty($_REQUEST["task"]))
	$_REQUEST["task"]();

function selectEqualizations(){
	
	$dt = PdoDataAccess::runquery_fetchMode("select EqualizationID,RegDate,BankID,BankDesc 
		from ACC_equalizations left join ACC_banks using(BankID)");
	$temp = PdoDataAccess::fetchAll($dt, $_GET["start"], $_GET["limit"]);
	echo dataReader::getJsonData($temp, $dt->rowCount(), $_GET["callback"]);
	die();
}

function showFile(){
	
	$obj = new ACC_equalizations($_REQUEST["EqualizationID"]);
	header('Content-disposition: filename=file.xls');
	header('Content-type: jpg');
	header('Pragma: no-cache');
	header('Expires: 0');
	header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
	header('Pragma: public');
	header("Content-Transfer-Encoding: binary");
	echo $obj->ImportFile;
	die();
}

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
	
	//--------------- add to equalizations -------------
	$EqualObj = new ACC_equalizations();
	$EqualObj->RegDate = PDONOW;
	$EqualObj->BankID = $BankID;
	$EqualObj->ImportFile =  fread(fopen($_FILES["attach"]["tmp_name"], 'r'), $_FILES["attach"]["size"]);
	$EqualObj->Add($pdo);	
	
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
		
	$successCount = 0;
	for ($i = 1; $i <= $data->sheets[0]['numRows']; $i++) 
	{
		$checkNo = "";		
		switch($BankID)
		{
			case "4": // اقتصاد نوین
				$TafsiliID = "";
				$TafsiliID2 = "";
				if(empty($data->sheets[0]['cells'][$i][1]))
					continue;
					
				$cellData = $data->sheets[0]['cells'][$i][1];
				if(isset($cellData) && strpos(trim($cellData), "چک عادي ش.") !== false)
				{
					$arr = preg_split("/\//", $cellData);
					$checkNo = $arr[2];
					$checkNo = substr($checkNo,0, strpos($checkNo, "صندوق پژوهش"));
				}
				break;
		}
		if($checkNo == "")
			continue;
	
		//---------------- add doc items --------------------
		$dt = PdoDataAccess::runquery("
			SELECT * FROM ACC_IncomeCheques 
				where ChequeNo=? AND ChequeStatus<>" . INCOMECHEQUE_VOSUL,array($checkNo), $pdo);
		if(count($dt) > 0)
		{
			$inChequeObj = new ACC_IncomeCheques($dt[0]["IncomeChequeID"]);
			$inChequeObj->EqualizationID = $EqualObj->EqualizationID;
			$inChequeObj->ChequeStatus = INCOMECHEQUE_VOSUL;
			$inChequeObj->Edit($pdo);
			
			$temp = $inChequeObj->GetBackPays($pdo);
			foreach($temp as $row)
			{
				$BackPayObj = new LON_BackPays($row["BackPayID"]);
				$BackPayObj->EqualizationID = $EqualObj->EqualizationID;
				$BackPayObj->Edit($pdo);
			}
			
			ACC_IncomeCheques::AddToHistory($inChequeObj->IncomeChequeID, $inChequeObj->ChequeStatus, $pdo);
	
			RegisterOuterCheque($obj->DocID, $inChequeObj, $pdo, COSTID_Bank, $TafsiliID, $TafsiliID2);
			$successCount++;
			
			$result .= "شماره چک : " . $checkNo . " به روز رسانی شد <br>";
		}
		else
		{
			$result .= "<font color=red> شماره چک : " . $checkNo . " یافت نشد </font><br>";
			
		}
	}
	
	if($successCount == 0)
		$pdo->rollBack ();
	else
		$pdo->commit();
	
	echo Response::createObjectiveResponse(true, $successCount == 0 ? "هیچ چکی به روز نگردید" : $result);
	die();
}

?>
