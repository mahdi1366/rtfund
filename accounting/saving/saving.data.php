<?php
//-----------------------------
//	Programmer	: SH.Jafarkhani
//	Date		: 94.06
//-----------------------------

require_once '../header.inc.php';
require_once '../docs/doc.class.php';
require_once '../docs/import.data.php';
require_once inc_dataReader;
require_once inc_response;

$task = isset($_POST ["task"]) ? $_POST ["task"] : (isset($_GET ["task"]) ? $_GET ["task"] : "");
if(!empty($task))
	$task();

function selectPersons() {
	
	$query = "select PackNo,p.PersonID,concat_ws(' ', fname,lname,CompanyName) fullname 
		from ACC_DocItems di
			join ACC_docs d using(DocID)
			join ACC_tafsilis t on(di.TafsiliID=t.TafsiliID)
			join BSC_persons p on(t.ObjectID=p.PersonID)
			left join DMS_packages k on(p.PersonID=k.PersonID AND k.BranchID=d.BranchID)
		where d.CycleID=:c AND d.BranchID=:b AND 
			di.CostID=:cost AND di.TafsiliType = :t";
			
	$param = array(
		":c" => $_SESSION["accounting"]["CycleID"],
		":b" => $_SESSION["accounting"]["BranchID"],
		":cost" => COSTID_saving,
		":t" => TAFTYPE_PERSONS
	);
	if(!empty($_REQUEST["query"]))
	{
		$query .= " AND ( concat_ws(' ', fname,lname,CompanyName) like :p or PackNo = :p )";
		$param[":p"] = "%" . $_REQUEST["query"] . "%";
	}
	
	$query .= " group by PersonID";
	
	$temp = PdoDataAccess::runquery_fetchMode($query, $param);
	
	$no = $temp->rowCount();
	$temp = PdoDataAccess::fetchAll($temp, $_GET["start"], $_GET["limit"]);
	echo dataReader::getJsonData($temp, $no, $_GET ["callback"]);
	die();
}

function GetSavingFlow() {
	
	$query = "select d.*,di.*
		from ACC_DocItems di
			join ACC_docs d using(DocID)
			join ACC_tafsilis t on(di.TafsiliID=t.TafsiliID)
			join BSC_persons p on(t.ObjectID=p.PersonID)
		where d.CycleID=:c AND d.BranchID=:b AND 
			di.CostID=:cost AND di.TafsiliType = :t AND p.PersonID=:p";
	
	$param = array(
		":c" => $_SESSION["accounting"]["CycleID"],
		":b" => $_SESSION["accounting"]["BranchID"],
		":cost" => COSTID_saving,
		":t" => TAFTYPE_PERSONS,
		":p" => $_REQUEST["PersonID"]
	);
	
	$temp = PdoDataAccess::runquery_fetchMode($query, $param);
	$no = $temp->rowCount();
	$temp = PdoDataAccess::fetchAll($temp, $_GET["start"], $_GET["limit"]);
	echo dataReader::getJsonData($temp, $no, $_GET ["callback"]);
	die();
}

function RegisterDoc() {
	
	$mode = $_POST["mode"]*1;
	if($mode < 0)
	{
		$query = "select ifnull(sum(CreditorAmount-DebtorAmount),0) remaindar
		from ACC_DocItems di
			join ACC_docs d using(DocID)
			join ACC_tafsilis t on(di.TafsiliID=t.TafsiliID)
			join BSC_persons p on(t.ObjectID=p.PersonID)
		where d.CycleID=:c AND d.BranchID=:b AND 
			di.CostID=:cost AND di.TafsiliType = :t AND p.PersonID=:p";
		$param = array(
			":c" => $_SESSION["accounting"]["CycleID"],
			":b" => $_SESSION["accounting"]["BranchID"],
			":cost" => COSTID_saving,
			":t" => TAFTYPE_PERSONS,
			":p" => $_REQUEST["PersonID"]
		);
		
		$dt = PdoDataAccess::runquery($query, $param);
		if($_POST["amount"] > $dt[0][0]*1)
		{
			echo Response::createObjectiveResponse(false,"مبلغ وارد شده بیشتر از مانده حساب می باشد");
			die();
		}
	}
	
	$pdo = PdoDataAccess::getPdoObject();
	$pdo->beginTransaction();
	
	//---------------- add doc header --------------------
	$obj = new ACC_docs();
	$obj->RegDate = PDONOW;
	$obj->regPersonID = $_SESSION['USER']["PersonID"];
	$obj->DocDate = PDONOW;
	$obj->CycleID = $_SESSION["accounting"]["CycleID"];
	$obj->BranchID = $_SESSION["accounting"]["BranchID"];
	$obj->DocType = $mode > 0 ? DOCTYPE_SAVING_IN : DOCTYPE_SAVING_OUT;
	$obj->description = $mode > 0 ? "واریز به پس انداز" : "برداشت از پس انداز";
	
	if(!$obj->Add($pdo))
	{
		echo Response::createObjectiveResponse(false,"خطا در ایجاد سند");
		die();
	}
	
	//-------------------------------------------------
	
	$PersonTafsili = FindTafsiliID($_POST["PersonID"], TAFTYPE_PERSONS);
	if(!$PersonTafsili)
	{
		echo Response::createObjectiveResponse(false,"تفصیلی مربوطه یافت نشد");
		die();
	}
	
	$itemObj = new ACC_DocItems();
	$itemObj->DocID = $obj->DocID;
	$itemObj->CostID = COSTID_saving;
	$itemObj->DebtorAmount = $mode > 0 ? 0 : $_POST["amount"];
	$itemObj->CreditorAmount = $mode > 0 ? $_POST["amount"] : 0;
	$itemObj->TafsiliType = TAFTYPE_PERSONS;
	$itemObj->TafsiliID = $PersonTafsili;
	if(!$itemObj->Add($pdo))
	{
		echo Response::createObjectiveResponse(false,"خطا در ایجاد ردیف سند");
		die();
	}
	
	$itemObj = new ACC_DocItems();
	$itemObj->DocID = $obj->DocID;
	$itemObj->CostID = $_POST["CostID"];
	$itemObj->DebtorAmount = $mode > 0 ? $_POST["amount"] : 0;
	$itemObj->CreditorAmount = $mode > 0 ? 0 : $_POST["amount"];
	if($itemObj->CostID == COSTID_Bank)
	{
		$itemObj->TafsiliType = TAFTYPE_BANKS;
		$itemObj->TafsiliID = $_POST["TafsiliID"];
	}
	if(!$itemObj->Add($pdo))
	{
		echo Response::createObjectiveResponse(false,"خطا در ایجاد ردیف سند");
		die();
	}
	
	$pdo->commit();
	echo Response::createObjectiveResponse(true,"");
	die();
}

?>