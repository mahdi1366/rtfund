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

function MakeWhere(&$where , &$param){
	
	//.........................................................
	if(!empty($_POST["FromNo"]))
	{
		$where .= " AND ChequeNo >= :cfn";
		$param[":cfn"] = $_POST["FromNo"];
	}
	if(!empty($_POST["ToNo"]))
	{
		$where .= " AND ChequeNo <= :ctn";
		$param[":ctn"] = $_POST["ToNo"];
	}
	if(!empty($_POST["FromDate"]))
	{
		$where .= " AND ChequeDate >= :fd";
		$param[":fd"] = DateModules::shamsi_to_miladi($_POST["FromDate"], "-");
	}
	if(!empty($_POST["ToDate"]))
	{
		$where .= " AND ChequeDate <= :td";
		$param[":td"] = DateModules::shamsi_to_miladi($_POST["ToDate"], "-");
	}
	if(!empty($_POST["FromAmount"]))
	{
		$where .= " AND ChequeAmount >= :fa";
		$param[":fa"] = preg_replace('/,/', "", $_POST["FromAmount"]);
	}
	if(!empty($_POST["ToAmount"]))
	{
		$where .= " AND ChequeAmount <= :ta";
		$param[":ta"] = preg_replace('/,/', "", $_POST["ToAmount"]);
	}
	if(!empty($_POST["ChequeBank"]))
	{
		$where .= " AND ChequeBank = :cb";
		$param[":cb"] = $_POST["ChequeBank"];
	}
	if(!empty($_POST["ChequeBranch"]))
	{
		$where .= " AND ChequeBranch like :cb";
		$param[":cb"] = "%" . $_POST["ChequeBranch"] . "%";
	}
	if(!empty($_POST["ChequeStatus"]))
	{
		$where .= " AND ChequeStatus = :cst";
		$param[":cst"] = $_POST["ChequeStatus"];
	}
	//.........................................................
	if (isset($_GET["fields"]) && !empty($_GET["query"])) {
		$where .= " AND " . $_GET["fields"] . " like :f";
		$param[":f"] = "%" . $_GET["query"] . "%";
	}
}

function selectIncomeCheques() {
	
	$where = "1=1";
	$param = array();
	
	MakeWhere($where, $param);
	
	$query = "
		select IncomeChequeID,
			ChequeNo,
			case when i.CostID is null then group_concat(t2.TafsiliDesc SEPARATOR '<br>')
				else t1.TafsiliDesc end fullname,
			case when i.CostID is null then group_concat(concat_ws('-', bb1.blockDesc, bb2.blockDesc) SEPARATOR '<br>') 
				else concat_ws('-', b1.blockDesc, b2.blockDesc, b3.blockDesc) end CostDesc,
			ChequeDate,
			ChequeAmount,
			b.BankDesc, 
			ChequeStatus,
			t3.TafsiliDesc ChequeStatusDesc,
			t.docs
			
		from ACC_IncomeCheques i
			left join ACC_tafsilis t1 using(TafsiliID)
			left join ACC_CostCodes cc using(CostID)
			left join ACC_blocks b1 on(cc.level1=b1.BlockID)
			left join ACC_blocks b2 on(cc.level2=b2.BlockID)
			left join ACC_blocks b3 on(cc.level3=b3.BlockID)
			
			left join LON_BackPays bp using(IncomeChequeID)
			left join LON_requests using(RequestID)
			left join LON_loans l using(LoanID)
			left join ACC_CostCodes cc2 on(cc2.level1=" . BLOCKID_LOAN . " AND cc2.level2=l.blockID)
			left join ACC_blocks bb1 on(cc2.level1=bb1.BlockID)
			left join ACC_blocks bb2 on(cc2.level2=bb2.BlockID)
			left join ACC_tafsilis t2 on(t2.TafsiliType=".TAFTYPE_PERSONS." AND t2.ObjectID=LoanPersonID)
		
		left join ACC_banks b on(ChequeBank=BankID)
		left join ACC_tafsilis t3 on(t3.TafsiliType=".TAFTYPE_ChequeStatus." AND t3.TafsiliID=ChequeStatus)
		left join (
			select SourceID, group_concat(distinct LocalNo) docs
			from ACC_DocItems join ACC_docs using(DocID)
			where SourceType='" . DOCTYPE_INCOMERCHEQUE . "' 
			group by SourceID
		)t on(IncomeChequeID=t.SourceID)
		
		where " . $where . " 
		group by IncomeChequeID";
	
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
		select TafsiliID,TafsiliDesc
		from ACC_tafsilis join ACC_ChequeStatuses on(SrcID=? AND DstID=TafsiliID)
		where TafsiliType=" . TAFTYPE_ChequeStatus, array($SrcID));
	echo dataReader::getJsonData($temp, count($temp), $_GET["callback"]);
	die();
}

//...........................................

function SaveIncomeCheque(){
	
	if(isset($_POST["ChangingCheque"]))
	{
		$pdo = PdoDataAccess::getPdoObject();
		$pdo->beginTransaction();
		
		if($_POST["RefBackPayID"] != "0")
			$Refobj = new LON_BackPays($_POST["RefBackPayID"]);
		else
			$Refobj = new ACC_IncomeCheques($_POST["RefIncomeChequeID"]);		
		$Refobj->ChequeStatus = INCOMECHEQUE_CHANGE;
		$Refobj->Edit();
		ACC_IncomeCheques::AddToHistory($_POST["RefIncomeChequeID"], $Refobj->ChequeStatus, $pdo);
		
		if($_POST["RefBackPayID"] != "0")
		{
			$obj = new LON_BackPays();
			PdoDataAccess::FillObjectByObject($Refobj, $obj);
			unset($obj->BackPayID);
			PdoDataAccess::FillObjectByArray($obj, $_POST);
			$obj->PayAmount = $_POST["ChequeAmount"];
			$obj->ChequeStatus = INCOMECHEQUE_NOTVOSUL;
			$obj->Add($pdo);
			ACC_IncomeCheques::AddToHistory($obj->BackPayID, 0, $obj->ChequeStatus, $pdo);
		}		
		else
		{
			$obj = new ACC_IncomeCheques();
			PdoDataAccess::FillObjectByObject($Refobj, $obj);
			$obj->IncomeChequeID = "";
			PdoDataAccess::FillObjectByArray($obj, $_POST);
			$obj->CostID = $Refobj->CostID;
			$obj->ChequeStatus = INCOMECHEQUE_NOTVOSUL;
			$obj->Add($pdo);
			ACC_IncomeCheques::AddToHistory($obj->IncomeChequeID, $obj->ChequeStatus, $pdo);
		}	
		
		$Docobj = new ACC_docs();
		$Docobj->RegDate = PDONOW;
		$Docobj->regPersonID = $_SESSION['USER']["PersonID"];
		$Docobj->DocDate = PDONOW;
		$Docobj->CycleID = $_SESSION["accounting"]["CycleID"];
		$Docobj->BranchID = $_SESSION["accounting"]["BranchID"];
		$Docobj->DocType = DOCTYPE_INCOMERCHEQUE;
		$Docobj->description = "تعویض چک  شماره " . $Refobj->ChequeNo . " به چک شماره " . $obj->ChequeNo;
		if(!$Docobj->Add($pdo))
		{
			ExceptionHandler::PushException("خطا در ایجاد سند");
			return false;
		}
		
		if(!RegisterOuterCheque($Docobj->DocID,$Refobj,$pdo))
		{
			echo Response::createObjectiveResponse(false, "");
			die();
		}
		
		if(!RegisterOuterCheque($Docobj->DocID, $obj,$pdo))
		{
			echo Response::createObjectiveResponse(false, "");
			die();
		}
		$pdo->commit();
		echo Response::createObjectiveResponse(true, "");
		die();
	}
	//....................................................
	
	$pdo = PdoDataAccess::getPdoObject();
	$pdo->beginTransaction();
	
	$obj = new ACC_IncomeCheques();
	PdoDataAccess::FillObjectByArray($obj, $_POST);
	$obj->ChequeStatus = INCOMECHEQUE_NOTVOSUL;
	if(!$obj->Add($pdo))
	{
		echo Response::createObjectiveResponse(false, ExceptionHandler::GetExceptionsToString());
		die();
	}
	//................. add back pays ........................
	if(!empty($_POST["parts"]))
	{
		$parts = json_decode($_POST["parts"]);
		foreach($parts as $partStr)
		{
			$arr = preg_split("/_/", $partStr);
			$RequestID = $arr[0];
			$PayAmount = $arr[1];

			$bobj = new LON_BackPays();
			$bobj->PayDate = $obj->ChequeDate;
			$bobj->IncomeChequeID = $obj->IncomeChequeID;
			$bobj->RequestID = $RequestID;
			$bobj->PayAmount = $PayAmount;
			$bobj->PayType = BACKPAY_PAYTYPE_CHEQUE;
			$bobj->IsGroup = "YES";
			$bobj->Add($pdo);
		}
	}
	//.......................................................
	
	ACC_IncomeCheques::AddToHistory($obj->IncomeChequeID, $obj->ChequeStatus, $pdo);
	
	if(!RegisterOuterCheque("",$obj,$pdo))
	{
		print_r(ExceptionHandler::PopAllExceptions());
		echo Response::createObjectiveResponse(false,ExceptionHandler::GetExceptionsToString());
		die();
	}
	
	$pdo->commit();
	echo Response::createObjectiveResponse(true, "");
	die();
}

function ChangeChequeStatus(){
	
	$IncomeChequeID = $_POST["IncomeChequeID"];
	$Status = $_POST["StatusID"];
	
	$TafsiliID = isset($_POST["TafsiliID"]) ? $_POST["TafsiliID"] : "";
	$TafsiliID2 = isset($_POST["TafsiliID2"]) ? $_POST["TafsiliID2"] : "";
	$BranchID = isset($_POST["BranchID"]) ? $_POST["BranchID"] : "";
	$FirstCostID = isset($_POST["FirstCostID"]) ? $_POST["FirstCostID"] : "";
	$SecondCostID = isset($_POST["SecondCostID"]) ? $_POST["SecondCostID"] : "";
	
	$pdo = PdoDataAccess::getPdoObject();
	$pdo->beginTransaction();
	
	$obj = new ACC_IncomeCheques($IncomeChequeID);
	$obj->ChequeStatus = $Status;
	$result = $obj->Edit($pdo);
	
	$result = RegisterOuterCheque("",$obj, $pdo,
		$_POST["CostID"], 
		$TafsiliID,
		$TafsiliID2,
		isset($_POST["CenterAccount"]) ? true : false,
		$BranchID,
		$FirstCostID,
		$SecondCostID);
	if(!$result)
	{
		$pdo->rollback();
		echo Response::createObjectiveResponse(false, ExceptionHandler::GetExceptionsToString());
		die();
	}		

	ACC_IncomeCheques::AddToHistory($IncomeChequeID, $Status, $pdo);
	
	$pdo->commit();
	echo Response::createObjectiveResponse($result, "");
	die();
}

function ReturnLatestOperation(){
	
	$OuterObj = new ACC_IncomeCheques($_POST["IncomeChequeID"]);
	
	$pdo = PdoDataAccess::getPdoObject();
	$pdo->beginTransaction();
	
	$dt = PdoDataAccess::runquery("select max(DocID) docID from ACC_DocItems di
		where SourceType=" . DOCTYPE_INCOMERCHEQUE . " AND SourceID=?",	
			array($OuterObj->IncomeChequeID), $pdo);
	$DocID = $dt[0][0];	
	
	if($DocID > 0)
	{
		$temp = PdoDataAccess::runquery("select TafsiliID2 from ACC_DocItems where DocID=? AND 
		SourceType=? AND SourceID=?", array($DocID, DOCTYPE_INCOMERCHEQUE, $OuterObj->IncomeChequeID));
		$OuterObj->ChequeStatus = $temp[0][0];		
		
		PdoDataAccess::runquery("delete from ACC_DocItems 
			where DocID=? AND SourceType=" . DOCTYPE_INCOMERCHEQUE . " AND SourceID=?",	
				array($DocID, $OuterObj->IncomeChequeID), $pdo);

		PdoDataAccess::runquery("delete d from ACC_docs d left join ACC_DocItems using(DocID)
			where DocID=? AND ItemID is null",	array($DocID), $pdo);
	}
	else
		$OuterObj->ChequeStatus = INCOMECHEQUE_NOTVOSUL;
	
	$OuterObj->Edit($pdo);
	
	ACC_IncomeCheques::AddToHistory($OuterObj->IncomeChequeID, $OuterObj->ChequeStatus , $pdo);

	if(ExceptionHandler::GetExceptionCount() > 0)
	{
		$pdo->rollBack();
		print_r(ExceptionHandler::PopAllExceptions());
		echo Response::createObjectiveResponse(false, "خطا در برگشت");
		die();
	}
	
	$pdo->commit();
	echo Response::createObjectiveResponse(true, "");
	die();	
}

//...........................................


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

?>
