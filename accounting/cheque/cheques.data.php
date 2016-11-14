<?php
//---------------------------
// programmer:	Jafarkhani
// create Date:	95.04
//---------------------------
 
require_once '../header.inc.php';
require_once(inc_response);
require_once inc_dataReader;
require_once '../docs/doc.class.php';
require_once "../../loan/request/request.class.php";
require_once "../docs/import.data.php";
require_once 'cheque.class.php';

$task = isset($_REQUEST['task']) ? $_REQUEST['task'] : '';
if(!empty($task))
	$task();

function selectIncomeCheques() {

	$param = array();
	$query = "select * from (
		select p.BackPayID , 0 OuterChequeID,
			concat_ws(' ',fname,lname,CompanyName) fullname,
			ChequeNo,
			'وام' CostDesc,
			p.PayDate ChequeDate,
			PayAmount ChequeAmount,
			b.BankDesc, 
			ChequeStatus,
			bi2.InfoDesc ChequeStatusDesc,
			t.docs
		from LON_BackPays p 
		join LON_requests using(RequestID)
		join BSC_persons on(LoanPersonID=PersonID)
		left join ACC_banks b on(ChequeBank=BankID)
		left join BaseInfo bi2 on(bi2.TypeID=4 AND bi2.InfoID=p.ChequeStatus)
		left join (
			select SourceID, group_concat(distinct LocalNo) docs
			from ACC_DocItems join ACC_docs using(DocID)
			where SourceType='" . DOCTYPE_DOCUMENT . "' 
			group by SourceID
		)t on(BackPayID=t.SourceID)
		where ChequeNo>0
		
		UNION ALL
		
		select 0 BackPayID, OuterChequeID , 
			TafsiliDesc fullname,
			ChequeNo,
			concat_ws('-', b1.blockDesc, b2.blockDesc, b3.blockDesc) CostDesc,
			ChequeDate,
			ChequeAmount,
			b.BankDesc, 
			ChequeStatus,
			bi2.InfoDesc ChequeStatusDesc,
			t.docs
		from ACC_OuterCheques 
		left join ACC_tafsilis using(TafsiliID)
		join ACC_CostCodes cc using(CostID)
		left join ACC_blocks b1 on(cc.level1=b1.BlockID)
		left join ACC_blocks b2 on(cc.level2=b2.BlockID)
		left join ACC_blocks b3 on(cc.level3=b3.BlockID)
		
		left join ACC_banks b on(ChequeBank=BankID)
		left join BaseInfo bi2 on(bi2.TypeID=4 AND bi2.InfoID=ChequeStatus)
		left join (
			select SourceID, group_concat(distinct LocalNo) docs
			from ACC_DocItems join ACC_docs using(DocID)
			where SourceType='" . DOCTYPE_OUTERCHEQUE . "' 
			group by SourceID
		)t on(OuterChequeID=t.SourceID)
		
	)t
	where 1=1";
	
	//.........................................................
	if(!empty($_POST["FromNo"]))
	{
		$query .= " AND ChequeNo >= :cfn";
		$param[":cfn"] = $_POST["FromNo"];
	}
	if(!empty($_POST["ToNo"]))
	{
		$query .= " AND ChequeNo <= :ctn";
		$param[":ctn"] = $_POST["ToNo"];
	}
	if(!empty($_POST["FromDate"]))
	{
		$query .= " AND ChequeDate >= :fd";
		$param[":fd"] = DateModules::shamsi_to_miladi($_POST["FromDate"], "-");
	}
	if(!empty($_POST["ToDate"]))
	{
		$query .= " AND ChequeDate <= :td";
		$param[":td"] = DateModules::shamsi_to_miladi($_POST["ToDate"], "-");
	}
	if(!empty($_POST["FromAmount"]))
	{
		$query .= " AND ChequeAmount >= :fa";
		$param[":fa"] = preg_replace('/,/', "", $_POST["FromAmount"]);
	}
	if(!empty($_POST["ToAmount"]))
	{
		$query .= " AND ChequeAmount <= :ta";
		$param[":ta"] = preg_replace('/,/', "", $_POST["ToAmount"]);
	}
	if(!empty($_POST["ChequeBank"]))
	{
		$query .= " AND ChequeBank = :cb";
		$param[":cb"] = $_POST["ChequeBank"];
	}
	if(!empty($_POST["ChequeBranch"]))
	{
		$query .= " AND ChequeBranch like :cb";
		$param[":cb"] = "%" . $_POST["ChequeBranch"] . "%";
	}
	if(!empty($_POST["ChequeStatus"]))
	{
		$query .= " AND ChequeStatus = :cst";
		$param[":cst"] = $_POST["ChequeStatus"];
	}
	//.........................................................
	if (isset($_GET["fields"]) && !empty($_GET["query"])) {
		$query .= " AND " . $_GET["fields"] . " like :f";
		$param[":f"] = "%" . $_GET["query"] . "%";
	}
	//.........................................................
	$query .= dataReader::makeOrder();
	$temp = PdoDataAccess::runquery_fetchMode($query, $param);
	
	//print_r(ExceptionHandler::PopAllExceptions());
	//echo PdoDataAccess::GetLatestQueryString();
	
	$no = $temp->rowCount();
	$temp = PdoDataAccess::fetchAll($temp, $_GET["start"], $_GET["limit"]);
	echo dataReader::getJsonData($temp, $no, $_GET["callback"]);
	die();
}

function selectOutcomeCheques(){
	
	$query = "
		select c.*,d.LocalNo,d.DocDate,a.*,b.InfoDesc as StatusDesc,t.TafsiliDesc,bankDesc

		from ACC_DocCheques c
		left join ACC_tafsilis t using(tafsiliID)
		join ACC_docs d using(DocID)
		join ACC_accounts a using(AccountID)
		join ACC_banks bb using(BankID)
		join BaseInfo b on(b.typeID=4 AND b.infoID=CheckStatus)

		where CycleID=:c AND BranchID=:b";

	$whereParam = array(
		":c" => $_SESSION["accounting"]["CycleID"],
		":b" => $_SESSION["accounting"]["BranchID"]
	);
	if(!empty($_POST["FromDocNo"]))
	{
		$query .= " AND d.LocalNo >= :fdno ";
		$whereParam[":fdno"] = $_POST["FromDocNo"];
	}
	if(!empty($_POST["ToDocNo"]))
	{
		$query .= " AND d.LocalNo <= :tdno ";
		$whereParam[":tdno"] = $_POST["ToDocNo"];
	}
	if(!empty($_POST["DFromDate"]))
	{
		$query .= " AND d.DocDate >= :fdd ";
		$whereParam[":fdd"] = DateModules::shamsi_to_miladi($_POST["DFromDate"], "-");
	}
	if(!empty($_POST["DToDate"]))
	{
		$query .= " AND d.DocDate <= :tdd ";
		$whereParam[":tdd"] = DateModules::shamsi_to_miladi($_POST["DToDate"], "-");
	}
	if(!empty($_POST["FromDate"]))
	{
		$query .= " AND c.checkDate >= :fd ";
		$whereParam[":fd"] = DateModules::shamsi_to_miladi($_POST["FromDate"], "-");
	}
	if(!empty($_POST["ToDate"]))
	{
		$query .= " AND c.checkDate <= :td ";
		$whereParam[":td"] = DateModules::shamsi_to_miladi($_POST["ToDate"], "-");
	}
	if(!empty($_POST["CheckStatus"]))
	{
		$query .= " AND c.CheckStatus = :cs ";
		$whereParam[":cs"] = $_POST["CheckStatus"];
	}
	if(!empty($_POST["FromCheckNo"]))
	{
		$query .= " AND c.checkNo >= :fcn ";
		$whereParam[":fcn"] = $_POST["FromCheckNo"];
	}
	if(!empty($_POST["ToCheckNo"]))
	{
		$query .= " AND c.checkNo <= :tcn ";
		$whereParam[":tcn"] = $_POST["ToCheckNo"];
	}
	if(!empty($_POST["FromAmount"]))
	{
		$query .= " AND c.amount >= :fa ";
		$whereParam[":fa"] = preg_replace('/,/', "", $_POST["FromAmount"]);
	}
	if(!empty($_POST["ToAmount"]))
	{
		$query .= " AND c.amount <= :ta ";
		$whereParam[":ta"] = preg_replace('/,/', "", $_POST["ToAmount"]);
	}
	if(!empty($_POST["bankID"]))
	{
		$query .= " AND a.bankID = :b ";
		$whereParam[":b"] = $_POST["bankID"];
	}
	if(!empty($_POST["accountID"]))
	{
		$query .= " AND c.accountID = :ac ";
		$whereParam[":ac"] = $_POST["accountID"];
	}
	if(!empty($_POST["tafsiliID"]))
	{
		$query .= " AND c.tafsiliID = :taf ";
		$whereParam[":taf"] = $_POST["tafsiliID"];
	}
	$query .= dataReader::makeOrder();

	$dataTable = PdoDataAccess::runquery($query, $whereParam);
	//echo PdoDataAccess::GetLatestQueryString();
	echo dataReader::getJsonData($dataTable, count($dataTable), $_GET["callback"]);
	die();
}

function SelectIncomeChequeStatuses() {
	
	$temp = PdoDataAccess::runquery("select * from BaseInfo where TypeID=4");

	echo dataReader::getJsonData($temp, count($temp), $_GET['callback']);
	die();
}

function SelectChequeStatuses(){
	
	$dt = PdoDataAccess::runquery("select * from ACC_ChequeStatuses");
	echo dataReader::getJsonData($dt, count($dt), $_GET["callback"]);
	die();
}

function SaveChequeStatus(){
	
	PdoDataAccess::runquery("insert into ACC_ChequeStatuses(SrcID,DstID) values(?,?)", 
		array($_POST["SrcID"],$_POST["DstID"]));
	echo Response::createObjectiveResponse(true, "");
	die();
}

function DeleteChequeStatuses(){
	
	PdoDataAccess::runquery("delete from ACC_ChequeStatuses where RowID=?", 
		array($_POST["RowID"]));
	echo Response::createObjectiveResponse(true, "");
	die();
}

function selectValidChequeStatuses(){
	
	$SrcID = $_REQUEST["SrcID"];
	$temp = PdoDataAccess::runquery("
		select InfoID,InfoDesc 
		from BaseInfo join ACC_ChequeStatuses on(SrcID=? AND DstID=InfoID)
		where typeID=4", array($SrcID));
	echo dataReader::getJsonData($temp, count($temp), $_GET["callback"]);
	die();
}

function ChangeChequeStatus(){
	
	$BackPayID = $_POST["BackPayID"];
	$OuterChequeID = $_POST["OuterChequeID"];
	$Status = $_POST["StatusID"];
	
	$TafsiliID = isset($_POST["TafsiliID"]) ? $_POST["TafsiliID"] : "";
	$TafsiliID2 = isset($_POST["TafsiliID2"]) ? $_POST["TafsiliID2"] : "";
	$BranchID = isset($_POST["BranchID"]) ? $_POST["BranchID"] : "";
	$FirstCostID = isset($_POST["FirstCostID"]) ? $_POST["FirstCostID"] : "";
	$SecondCostID = isset($_POST["SecondCostID"]) ? $_POST["SecondCostID"] : "";
	
	$pdo = PdoDataAccess::getPdoObject();
	$pdo->beginTransaction();
	
	if($BackPayID*1 > 0)
	{
		$obj = new LON_BackPays($BackPayID);
		$obj->ChequeStatus = $Status;
		$result = $obj->Edit($pdo);

		if($Status == OUERCHEQUE_VOSUL)
		{
			$ReqObj = new LON_requests($obj->RequestID);
			$PersonObj = new BSC_persons($ReqObj->ReqPersonID);

			if($PersonObj->IsSupporter == "YES")
				$result = RegisterSHRTFUNDCustomerPayDoc(null, $obj,
						$_POST["CostID"], 
						$TafsiliID, 
						$TafsiliID2, 
						isset($_POST["CenterAccount"]) ? true : false,
						$BranchID,
						$FirstCostID,
						$SecondCostID, $pdo);
			else
				$result = RegisterCustomerPayDoc(null, $obj, 
						$_POST["CostID"], 
						$TafsiliID, 
						$TafsiliID2, 
						isset($_POST["CenterAccount"]) ? true : false,
						$BranchID,
						$FirstCostID,
						$SecondCostID, $pdo);
			if(!$result)
			{
				$pdo->rollback();
				echo Response::createObjectiveResponse(false, ExceptionHandler::GetExceptionsToString());
				die();
			}
		}
		if(array_search($Status, array(OUERCHEQUE_EBTAL,OUERCHEQUE_MOSTARAD,
			OUERCHEQUE_BARGHASHTI_MOSTARAD,OUERCHEQUE_MAKHDOOSH)) !== false)
		{
			$result = RegisterOuterCheque($obj, "","","","", $pdo);
		}	
	}
	else
	{
		$obj = new ACC_OuterCheques($OuterChequeID);
		$obj->ChequeStatus = $Status;
		$result = $obj->Edit($pdo);

		$result = RegisterOuterCheque($obj, 
			$TafsiliID,
			$TafsiliID2,
			isset($_POST["CenterAccount"]) ? true : false,
			$BranchID, $pdo);
		if(!$result)
		{
			$pdo->rollback();
			echo Response::createObjectiveResponse(false, ExceptionHandler::GetExceptionsToString());
			die();
		}		
	}
	
	ACC_OuterCheques::AddToHistory($BackPayID, $OuterChequeID, $Status, $pdo);
	
	$pdo->commit();
	echo Response::createObjectiveResponse($result, "");
	die();
}

//...........................................

function SaveOuterCheque(){
	
	if(isset($_POST["ChangingCheque"]))
	{
		$pdo = PdoDataAccess::getPdoObject();
		$pdo->beginTransaction();
		
		if($_POST["RefBackPayID"] != "0")
			$Refobj = new LON_BackPays($_POST["RefBackPayID"]);
		else
			$Refobj = new ACC_OuterCheques($_POST["RefOuterChequeID"]);		
		$Refobj->ChequeStatus = OUERCHEQUE_CHANGE;
		$Refobj->Edit();
		ACC_OuterCheques::AddToHistory($_POST["RefBackPayID"], $_POST["RefOuterChequeID"], $Refobj->ChequeStatus, $pdo);
		
		if($_POST["RefBackPayID"] != "0")
		{
			$obj = new LON_BackPays();
			PdoDataAccess::FillObjectByObject($Refobj, $obj);
			unset($obj->BackPayID);
			PdoDataAccess::FillObjectByArray($obj, $_POST);
			$obj->PayAmount = $_POST["ChequeAmount"];
			$obj->ChequeStatus = OUERCHEQUE_NOTVOSUL;
			$obj->Add($pdo);
			ACC_OuterCheques::AddToHistory($obj->BackPayID, 0, $obj->ChequeStatus, $pdo);
		}		
		else
		{
			$obj = new ACC_OuterCheques();
			PdoDataAccess::FillObjectByObject($Refobj, $obj);
			$obj->OuterChequeID = "";
			PdoDataAccess::FillObjectByArray($obj, $_POST);
			$obj->CostID = $Refobj->CostID;
			$obj->ChequeStatus = OUERCHEQUE_NOTVOSUL;
			$obj->Add($pdo);
			ACC_OuterCheques::AddToHistory(0, $obj->OuterChequeID, $obj->ChequeStatus, $pdo);
		}	
		
		
		$Docobj = new ACC_docs();
		$Docobj->RegDate = PDONOW;
		$Docobj->regPersonID = $_SESSION['USER']["PersonID"];
		$Docobj->DocDate = PDONOW;
		$Docobj->CycleID = $_SESSION["accounting"]["CycleID"];
		$Docobj->BranchID = $_SESSION["accounting"]["BranchID"];
		$Docobj->DocType = DOCTYPE_OUTERCHEQUE;
		$Docobj->description = "تعویض چک  شماره " . $Refobj->ChequeNo . " به چک شماره " . $obj->ChequeNo;
		if(!$Docobj->Add($pdo))
		{
			ExceptionHandler::PushException("خطا در ایجاد سند");
			return false;
		}
		
		if(!RegisterOuterCheque($Refobj, 0,0,0,0, $pdo, $Docobj->DocID))
		{
			echo Response::createObjectiveResponse(false, "");
			die();
		}
		
		if(!RegisterOuterCheque($obj,0,0,0,0, $pdo, $Docobj->DocID))
		{
			echo Response::createObjectiveResponse(false, "");
			die();
		}
		$pdo->commit();
		echo Response::createObjectiveResponse(true, "");
		die();
	}
	
	$obj = new ACC_OuterCheques();
	PdoDataAccess::FillObjectByArray($obj, $_POST);
	
	$pdo = PdoDataAccess::getPdoObject();
	$pdo->beginTransaction();
	
	$obj->ChequeStatus = OUERCHEQUE_NOTVOSUL;
	if(!$obj->Add($pdo))
	{
		//print_r(ExceptionHandler::PopAllExceptions());
		echo Response::createObjectiveResponse(false, ExceptionHandler::GetExceptionsToString());
		die();
	}
	
	ACC_OuterCheques::AddToHistory(0, $obj->OuterChequeID, $obj->ChequeStatus, $pdo);
	
	if(!RegisterOuterCheque($obj, "","","","", $pdo))
	{
		echo Response::createObjectiveResponse(false,ExceptionHandler::GetExceptionsToString());
		die();
	}
	
	$pdo->commit();
	echo Response::createObjectiveResponse(true, "");
	die();
}

?>
