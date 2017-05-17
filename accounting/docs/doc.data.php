<?php
//-----------------------------
//	Programmer	: SH.Jafarkhani
//	Date		: 94.06
//-----------------------------

require_once '../header.inc.php';
require_once 'doc.class.php';
require_once 'import.data.php';
require_once '../baseinfo/baseinfo.class.php';
require_once inc_dataReader;
require_once inc_response;

if(!empty($_REQUEST["task"]))
	$_REQUEST["task"]();

function selectDocs() {
	$where = " sd.CycleID=:cid AND sd.BranchID=:b ";
	$whereParam = array(":cid" => $_SESSION["accounting"]["CycleID"], 
						":b" => $_SESSION["accounting"]["BranchID"]);
	if (isset($_GET["fields"]) && !empty($_GET["query"])) {
		switch ($_GET["fields"]) {
			case "DocID":
				$where .= " AND sd.DocID = :tl";
				$whereParam[":tl"] = $_GET["query"];
				break;
			case "DocDate":
				$where .= " AND sd.DocDate = :tl";
				$whereParam[":tl"] = DateModules::shamsi_to_miladi($_GET["query"]);
				break;
			case "description":
				$where .= " AND sd.description like :d";
				$whereParam[":d"] = "%" . $_GET["query"] . "%";
				break;
			/*case "storeDocID":
				$where .= " AND sd.storeDocID = :d";
				$whereParam[":d"] = $_GET["query"];
				break;*/
		}
	}
	$where .= dataReader::makeOrder();
	$temp = ACC_docs::GetAll($where, $whereParam);
	$no = $temp->rowCount();
	$temp = PdoDataAccess::fetchAll($temp, $_GET["start"], $_GET["limit"]);
	echo dataReader::getJsonData($temp, $no, $_GET ["callback"]);
	die();
}

function saveDoc() {
	
	$obj = new ACC_docs();
	PdoDataAccess::FillObjectByArray($obj, $_POST);
	$obj->CycleID = $_SESSION["accounting"]["CycleID"];
	$obj->BranchID = $_SESSION["accounting"]["BranchID"];

	if ($obj->DocID == "") {
		$obj->RegDate = DateModules::Now();
		$obj->regPersonID = $_SESSION['USER']["PersonID"];
		$obj->DocDate = empty($obj->DocDate) ? PDONOW : $obj->DocDate;
		$return = $obj->Add();
	} 
	else {		
		$return = $obj->Edit();
	}

	if (!$return) {
		echo Response::createObjectiveResponse(false, ExceptionHandler::GetExceptionsToString());
		die();
	}

	/*$dt = PdoDataAccess::runquery("select count(*) from ACC_docs where DocID<? 
		AND CycleID=" . $_SESSION["accounting"]["CycleID"] . 
		" AND BranchID=" . $_SESSION["accounting"]["BranchID"] , array($obj->DocID));
	echo Response::createObjectiveResponse(true, $dt[0][0] + 1);*/
	echo Response::createObjectiveResponse(true, "");
	die();
}

function removeDoc() {
	
	$result = ACC_docs::Remove($_REQUEST["DocID"]);
	echo Response::createObjectiveResponse($result, ExceptionHandler::GetExceptionsToString());
	die();
}

function confirm() {

	if(ACC_cycles::IsClosed())
	{
		echo Response::createObjectiveResponse(false, "دوره مالی جاری بسته شده است و قادر به اعمال تغییرات نمی باشید");
		die();	
	}
	
	$status = "CONFIRM";
	if(isset($_POST["undo"]) && $_POST["undo"] == "true")
		$status = "RAW";
	
	//------------ check for register deposite -------------
	if($status == "RAW")
	{
		$dt = PdoDataAccess::runquery("select DocID,group_concat(TafsiliID) tafs from ACC_DocItems where DocID=? 
			AND CostID in(".COSTID_ShortDeposite.",".COSTID_LongDeposite.") ", array($_POST["DocID"]));
		if(count($dt) > 0)
		{
			$dt = PdoDataAccess::runquery("select LocalNo from ACC_docs join ACC_DocItems using(DocID) where 
				TafsiliID in(" . $dt[0]["tafs"] . ") AND
				DocID>? AND CycleID=" . $_SESSION["accounting"]["CycleID"] . "
				AND BranchID=" . $_SESSION["accounting"]["BranchID"] . "
				AND DocType=" . DOCTYPE_DEPOSIT_PROFIT, array($_POST["DocID"]));
			if(count($dt) > 0)
			{
				echo Response::createObjectiveResponse(false, "سند سپرده با شماره " . $dt[0][0] . " بر اساس این سند صادر شده و قادر به برگشت این سند نمی باشید.");
				die();						
			}
		}
	}
	
	$obj = new ACC_docs();
	$obj->DocID = $_POST["DocID"];
	$obj->DocStatus = $status;
	$obj->Edit();
	
	echo Response::createObjectiveResponse(true, "");
	die();
}

function archive() {

	PdoDataAccess::runquery("update ACC_docs set DocStatus='ARCHIVE' where DocID=" . $_POST["DocID"]);
	echo "true";
	die();
}

function regResid() {
	$pdo = PdoDataAccess::getPdoObject();
	/* @var $pdo PDO */
	$pdo->beginTransaction();

	$obj = new ACC_docs();
	$obj->CycleID = $_SESSION["accounting"]["CycleID"];
	$obj->storeID = $_POST["storeID"];
	$obj->doc_type = 20;
	$obj->reg_date = PDONOW;
	$obj->doc_date = PDONOW;
	$obj->ref_DocID = $_POST["DocID"];

	if (isset($_POST["desc_storeID"]))
		$obj->desc_storeID = $_POST["desc_storeID"];

	$result = $obj->Add($pdo);
	if (!$result) {
		$pdo->rollBack();
		echo "false";
		die();
	}

	$query = "insert into store_doc_items(CycleID,DocID,itemID,itemCount,buyPrice,
			coverPrice,expireDate,salePrice,tax)
		select " . $obj->CycleID . "," . $obj->DocID . ",itemID,itemCount,buyPrice,
			coverPrice,expireDate,salePrice,tax
		from store_doc_items where DocID=?";
	$result = PdoDataAccess::runquery($query, array($_POST["DocID"]), $pdo);
	if ($result === false) {
		print_r(ExceptionHandler::PopAllExceptions());
		$pdo->rollBack();
		echo "false";
		die();
	}

	$obj2 = new ACC_docs();
	$obj2->DocID = $_POST["DocID"];
	$obj2->ref_DocID = $obj->DocID;
	$result = $obj2->Edit(false, true, $pdo);
	if (!$result) {
		$pdo->rollBack();
		echo "false";
		die();
	}
	$pdo->commit();
	echo "true";
	die();
}

function CopyDoc(){
	
	if(ACC_cycles::IsClosed())
	{
		echo Response::createObjectiveResponse(false, "دوره مالی جاری بسته شده است و قادر به اعمال تغییرات نمی باشید");
		die();	
	}
	
	$RefDocID = $_POST["DocID"];
	
	$pdo = PdoDataAccess::getPdoObject();
	$pdo->beginTransaction();
	
	$hobj = new ACC_docs();
	$hobj->CycleID = $_SESSION["accounting"]["CycleID"];
	$hobj->BranchID = $_SESSION["accounting"]["BranchID"];
	$hobj->RegDate = DateModules::Now();
	$hobj->regPersonID = $_SESSION['USER']["PersonID"];
	$hobj->DocDate = PDONOW;
	$hobj->Add($pdo);
	
	$dt = PdoDataAccess::runquery("select * from ACC_DocItems where DocID=?", array($RefDocID));
	foreach($dt as $row)
	{
		$obj = new ACC_DocItems();
		PdoDataAccess::FillObjectByArray($obj, $row);
		$obj->DocID = $hobj->DocID;
		unset($obj->ItemID);
		$obj->locked = "NO";
		unset($obj->SourceType);	
		unset($obj->SourceID);
		unset($obj->SourceID2);
		unset($obj->SourceID3);
		$obj->Add($pdo);
	}
	
	$result = ExceptionHandler::GetExceptionCount() == 0;
	if($result)
		$pdo->commit();
	echo Response::createObjectiveResponse($result, "");
	die();
}

function GetLastLocalNo(){
	
	echo ACC_docs::GetLastLocalNo();
	die();
	
}

function GetSearchCount() {
    
	$query = "select count(*) as CurrentPage 
		from ACC_docs dh
		where BranchID=:b AND CycleID=:c AND LocalNo < :n ";
	
    $param = array(":c" => $_SESSION["accounting"]["CycleID"], 
					":b" => $_SESSION["accounting"]["BranchID"],
					":n" => $_REQUEST['Number']);
	
	$dt = PdoDataAccess::runquery($query, $param);
    echo Response::createObjectiveResponse(true, $dt[0]['CurrentPage']);
    die();
}

function TotalConfirm(){
	
	if(ACC_cycles::IsClosed())
	{
		echo Response::createObjectiveResponse(false, "دوره مالی جاری بسته شده است و قادر به اعمال تغییرات نمی باشید");
		die();	
	}
	
	$where = "";
	$param = array(
		":c" => $_SESSION["accounting"]["CycleID"],
		":b" => $_SESSION["accounting"]["BranchID"]
	);
	if(!empty($_POST["FromDate"]))
	{
		$where .= " AND DocDate >= :fd";
		$param[":fd"] = DateModules::shamsi_to_miladi($_POST["FromDate"], "-");
	}
	if(!empty($_POST["ToDate"]))
	{
		$where .= " AND DocDate <= :td";
		$param[":td"] = DateModules::shamsi_to_miladi($_POST["ToDate"], "-");
	}
	if(!empty($_POST["FromNo"]))
	{
		$where .= " AND LocalNo >= :fl";
		$param[":fl"] = $_POST["FromNo"];
	}
	if(!empty($_POST["ToNo"]))
	{
		$where .= " AND LocalNo <= :tl";
		$param[":tl"] = $_POST["ToNo"];
	}
	
	$dt = PdoDataAccess::runquery("select DocID from ACC_docs where DocStatus='RAW' AND CycleID=:c AND BranchID=:b " . $where, $param);
	if(count($dt) == 0)
	{
		echo Response::createObjectiveResponse(false, "هیچ سندی یافت نشد");
		die();
	}
	
	foreach($dt as $row)
	{
		$obj = new ACC_docs();
		$obj->DocID = $row["DocID"];
		$obj->DocStatus = 'CONFIRM';
		$obj->Edit();
	}
	
	echo Response::createObjectiveResponse(ExceptionHandler::GetExceptionCount() == 0, "");
	die();
}

//............................

function selectDocItems() {
	$where = "DocID=:did";
	$whereParam = array(":did" => $_REQUEST["DocID"]);

	if (isset($_GET["fields"]) && !empty($_GET["query"])) {
		switch ($_GET["fields"]) {
			case "TafsiliID":
				$where .= " AND t.tafsiliDesc like :tl";
				$whereParam[":tl"] = "%" . $_GET["query"] . "%";
				break;
			case "moinID":
				$where .= " AND moinTitle like :tl";
				$whereParam[":tl"] = "%" . $_GET["query"] . "%";
				break;
			case "TafsiliID2":
				$where .= " AND t2.tafsiliDesc like :tl";
				$whereParam[":tl"] = "%" . $_GET["query"] . "%";
				break;
			case "kolID":
				$where .= " AND kolTitle like :tl";
				$whereParam[":tl"] = "%" . $_GET["query"] . "%";
				break;
			case "DebtorAmount":
				$where .= " AND DebtorAmount = :tl";
				$whereParam[":tl"] = $_GET["query"];
				break;
			case "CreditorAmount":
				$where .= " AND CreditorAmount = :tl";
				$whereParam[":tl"] = $_GET["query"];
				break;
			case "CostDesc":
				$where .= " AND (cc.CostCode like :cd or 
					concat_ws('-',b1.blockDesc,b2.BlockDesc,b3.BlockDesc,b4.BlockDesc) like :cd)";
				$whereParam[":cd"] = "%" . $_GET["query"] . "%";
				break;
		}
	}
	$where .= dataReader::makeOrder();

	$temp = ACC_DocItems::GetAll($where, $whereParam);
	//print_r(ExceptionHandler::PopAllExceptions());
	$no = $temp->rowCount();
	$temp = PdoDataAccess::fetchAll($temp, $_GET["start"], $_GET["limit"]);
	//..........................................................................
	$dt = PdoDataAccess::runquery("
		select sum(DebtorAmount) bd,sum(CreditorAmount) bs
		from ACC_DocItems
		where DocID=? 
		group by DocID", array($_REQUEST["DocID"]));
	$bdSum = count($dt) != 0 ? $dt[0]["bd"] : 0;
	$bsSum = count($dt) != 0 ? $dt[0]["bs"] : 0;
	//..........................................................................	
	echo dataReader::getJsonData($temp, $no, $_GET ["callback"], $bdSum . "," . $bsSum);
	die();
}

function saveDocItem() {

	$obj = new ACC_DocItems();
	PdoDataAccess::FillObjectByArray($obj, $_POST);

	if($obj->TafsiliType == "")
		$obj->TafsiliType = PDONULL;
	if($obj->TafsiliID == "")
		$obj->TafsiliID = PDONULL;
	if($obj->TafsiliType2 == "")
		$obj->TafsiliType2 = PDONULL;
	if($obj->TafsiliID2 == "")
		$obj->TafsiliID2 = PDONULL;
	
	if ($obj->ItemID == "")
		$return = $obj->Add();
	else
		$return = $obj->Edit();

	if (!$return) {
		echo Response::createObjectiveResponse(false, ExceptionHandler::GetExceptionsToString());
		die();
	}
	echo Response::createObjectiveResponse(true, "");
	die();
}

function removeDocItem() {
	
	$result = ACC_DocItems::Remove($_POST["ItemID"]);
	echo Response::createObjectiveResponse($result, "");
	die();
}

//............................

function selectCheques() {
	$where = "1=1";
	$whereParam = array();
	if (isset($_REQUEST["DocID"])) {
		$where .= " AND DocID=:d";
		$whereParam[":d"] = $_REQUEST["DocID"];
	}

	if (isset($_GET["fields"]) && !empty($_GET["query"])) {
		switch ($_GET["fields"]) {
			case "CheckNo":
				$where .= " AND CheckNo = :q";
				$whereParam[":q"] = $_GET["query"];
				break;
			case "reciever":
				$where .= " AND reciever like :q";
				$whereParam[":q"] = '%' . $_GET["query"] . '%';
				break;
			case "DocID":
				$where .= " AND DocID = :q";
				$whereParam[":q"] = $_GET["query"];
				break;
			case "accountTitle":
				$where .= " AND accountTitle like :q";
				$whereParam[":q"] = '%' . $_GET["query"] . '%';
				break;
			case "amount":
				$where .= " AND amount = :q";
				$whereParam[":q"] = $_GET["query"];
				break;
			case "CheckStatus":
				$where .= " AND b.title like :q";
				$whereParam[":q"] = '%' . $_GET["query"] . '%';
				break;
		}
	}

	if (!empty($_GET["date"])) {
		$where .= " AND CheckDate = :q";
		$whereParam[":q"] = DateModules::shamsi_to_miladi($_GET["date"]);
	}

	$where .= dataReader::makeOrder();

	$temp = ACC_DocCheques::GetAll($where, $whereParam);
	$no = count($temp);
	echo dataReader::getJsonData($temp, $no, $_GET["callback"]);
	die();
}

function saveChecks() {
	$obj = new ACC_DocCheques();
	PdoDataAccess::FillObjectByJsonData($obj, $_POST["record"]);
	
	//..........................................
	$query = "select * from ACC_DocCheques where CheckNo=? AND AccountID=?";
	$query .=!empty($obj->DocChequeID) ? " AND DocChequeID<>" . $obj->DocChequeID : "";

	$dt = PdoDataAccess::runquery($query, array($obj->CheckNo, $obj->AccountID));
	if (count($dt) > 0) {
		echo Response::createObjectiveResponse(false, "duplicate");
		die();
	}
	//..........................................
	if (empty($obj->DocChequeID))
	{
		unset($obj->CheckStatus);
		$return = $obj->Add();
	}
	else
		$return = $obj->Edit();

	if (!$return) {
		echo "false";
		print_r(ExceptionHandler::PopAllExceptions());
		die();
	}
	echo Response::createObjectiveResponse(true, "");
	die();
}

function removeChecks() {
	$result = ACC_DocCheques::Remove($_POST["DocChequeID"]);
	echo $result ? "true" : "conflict";
	die();
}

function RegisterCheck() {
	
	PdoDataAccess::runquery("update ACC_DocCheques set CheckStatus=1 where DocChequeID=?", array($_POST["DocChequeID"]));
	echo Response::createObjectiveResponse(PdoDataAccess::AffectedRows() == 0 ? false : true, "");
	die();
}

//............................

function openDoc() {
	
	if(ACC_cycles::IsClosed())
	{
		echo Response::createObjectiveResponse(false, "دوره مالی جاری بسته شده است و قادر به اعمال تغییرات نمی باشید");
		die();	
	}
	PdoDataAccess::runquery("update ACC_docs set DocStatus='RAW' where DocID=" . $_POST["DocID"]);
	PdoDataAccess::AUDIT("ACC_docs","باز کردن سند", $_POST["DocID"]);
	echo "true";
	die();
}

function UpdateChecks(){
	
	$AccountID = $_POST["AccountID"];
	$result = "";
	
	require_once("phpExcelReader.php");
	
	$data = new Spreadsheet_Excel_Reader();
    $data->setOutputEncoding('utf-8');
    $data->setRowColOffset(0);
	$data->read($_FILES["attach"]["tmp_name"]);
	
	for ($i = 0; $i < $data->sheets[0]['numRows']; $i++) 
	{
		
		$CheckNo = "";		
		switch($AccountID)
		{
			case "1": // ملی
				if(trim($data->sheets[0]['cells'][$i][3]) == "چك")
				{
					$CheckNo = $data->sheets[0]['cells'][$i][4];
					$CheckNo = substr($CheckNo, strlen($CheckNo) - 6);
				}
				break;
			case "5": //پاسارگاد
				if(strpos($data->sheets[0]['cells'][$i][8], "وصول چک") !== false)
				{
					$CheckNo = $data->sheets[0]['cells'][$i][7];
					$CheckNo = substr($CheckNo, strlen($CheckNo) - 6);
				}
		}
		if($CheckNo == "")
			continue;
	
		//............... add debtor rows to doc ...........................
		if($_POST["DocID"] != "")
		{
			$dt = PdoDataAccess::runquery("select * from ACC_DocCheques 
				where CheckStatus in(1,2) AND AccountID=? AND CheckNo=?", array($AccountID,$CheckNo));
			if(count($dt) > 0)
			{
				$obj = new ACC_DocItems();
				$obj->DocID = $_POST["DocID"];
				$obj->kolID = 42; // اسناد پرداختنی
				$obj->moinID = 1; // کوتاه مدت
				$obj->TafsiliID = $dt[0]["TafsiliID"];	
				$obj->DebtorAmount = $dt[0]["amount"];
				$obj->CreditorAmount = 0;
				$obj->Add();
			}
		}		
		//..................................................................

		PdoDataAccess::runquery("update ACC_DocCheques set CheckStatus=3 where AccountID=? AND CheckNo=?", array($AccountID,$CheckNo));
		if(PdoDataAccess::AffectedRows() > 0)
			$result .= "شماره چک : " . $CheckNo . " [ تعداد ردیف به روز شده : " . PdoDataAccess::AffectedRows() . "]<br>";
			
		
	}
	echo Response::createObjectiveResponse(true, $result == "" ? "هیچ چکی به روز نگردید" : $result);
	die();

	
	
	
	
	
	
	while (($row = fgetcsv($handle)) !== false) {
		
		$row[3] = iconv(mb_detect_encoding($row[3], mb_detect_order(), true), "UTF-8", $row[3]);
		echo $row[3] . "-------";
		if(trim($row[3]) == "چك")
		{
			echo $row[4] . "*********";
			$CheckNo = substr($row[4], 4);
			PdoDataAccess::runquery("update ACC_DocCheques set CheckStatus=2 where AccountID=? AND CheckNo=?", array($AccountID,(int)$CheckNo));

			if(PdoDataAccess::AffectedRows() > 0)
				$result .= "شماره چک : " . $CheckNo . " [ تعداد ردیف به روز شده : " . PdoDataAccess::AffectedRows() . "]<br>";
		}
	}
	fclose($handle);
	echo Response::createObjectiveResponse(true, $result == "" ? "هیچ چکی به روز نگردید" : $result);
	die();
}

//..........................

function RegisterEndDoc(){
	
	$dt = PdoDataAccess::runquery("select * from ACC_docs where DocType=" . DOCTYPE_ENDCYCLE . " 
		AND BranchID=? AND CycleID=?", 
			array($_SESSION["accounting"]["CycleID"],$_SESSION["accounting"]["BranchID"]));
	if(count($dt) > 0)
	{
		echo Response::createObjectiveResponse(false, "سند اختتامیه در این دوره قبلا صادر شده است");
		die();
	}
	
	$LocalNo = $_POST["LocalNo"];
	if($LocalNo != "")
	{
		$dt = PdoDataAccess::runquery("select * from ACC_docs 
			where BranchID=? AND CycleID=? AND LocalNo=?" , 

			array($_SESSION["accounting"]["BranchID"], 
				$_SESSION["accounting"]["CycleID"], 
				$LocalNo));

		if(count($dt) > 0)
		{
			echo Response::createObjectiveResponse(false, "شماره سند وارد شده موجود می باشد");
			die();
		}
	}
	
	$pdo = PdoDataAccess::getPdoObject();
	$pdo->beginTransaction();
	//---------------- account header doc --------------------
	$obj = new ACC_docs();
	$obj->LocalNo = $LocalNo;
	$obj->RegDate = PDONOW;
	$obj->regPersonID = $_SESSION['USER']["PersonID"];
	$obj->DocDate = PDONOW;
	$obj->CycleID = $_SESSION["accounting"]["CycleID"];
	$obj->BranchID = $_SESSION["accounting"]["BranchID"];
	$obj->description = "سند اختتامیه";
	$obj->DocType = DOCTYPE_ENDCYCLE;
	$result = $obj->Add($pdo);

	if (!$result) {
		$pdo->rollBack();
		print_r(ExceptionHandler::PopAllExceptions());
		echo Response::createObjectiveResponse(false, "");
		die();
	}
	
	PdoDataAccess::runquery("
		insert into ACC_DocItems(DocID,CostID,TafsiliType,TafsiliID,TafsiliType2,TafsiliID2,
			DebtorAmount,CreditorAmount,locked)
		select $obj->DocID,CostID,TafsiliType,TafsiliID,TafsiliType2,TafsiliID2,
			if( sum(CreditorAmount-DebtorAmount)>0, sum(CreditorAmount-DebtorAmount), 0 ),
			if( sum(DebtorAmount-CreditorAmount)>0, sum(DebtorAmount-CreditorAmount), 0 ),
			1
		from ACC_DocItems i
		join ACC_docs using(DocID)
		where CycleID=" . $_SESSION["accounting"]["CycleID"] . "
			AND BranchID = " . $_SESSION["accounting"]["BranchID"] . "
		group by CostID,TafsiliID,TafsiliID2	
		having sum(CreditorAmount-DebtorAmount)<>0
	", array(), $pdo);
	
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

function RegisterStartDoc(){
	
	$dt = PdoDataAccess::runquery("select * from ACC_docs where DocType=" . DOCTYPE_STARTCYCLE . "
		AND BranchID=? AND CycleID=?", 
			array($_SESSION["accounting"]["CycleID"],$_SESSION["accounting"]["BranchID"]));
	if(count($dt) > 0)
	{
		echo Response::createObjectiveResponse(false, "سند افتتاحیه در این دوره قبلا صادر شده است");
		die();
	}
	
	$LocalNo = $_POST["LocalNo"];
	if($LocalNo != "")
	{
		$dt = PdoDataAccess::runquery("select * from ACC_docs 
			where BranchID=? AND CycleID=? AND LocalNo=?" , 

			array($_SESSION["accounting"]["BranchID"], 
				$_SESSION["accounting"]["CycleID"], 
				$LocalNo));

		if(count($dt) > 0)
		{
			echo Response::createObjectiveResponse(false, "شماره سند وارد شده موجود می باشد");
			die();
		}
	}
	
	$dt = PdoDataAccess::runquery("select * from ACC_cycles where CycleID<" . 
			$_SESSION["accounting"]["CycleID"]);
	if(count($dt) == 0)
	{
		Response::createObjectiveResponse(false, "دوره ایی قبل این دوره برای صدور سند افتتاحیه موجود نمی باشد");
		die();
	}
	
	$pdo = PdoDataAccess::getPdoObject();
	$pdo->beginTransaction();
	//---------------- account header doc --------------------
	$obj = new ACC_docs();
	$obj->LocalNo = $LocalNo;
	$obj->RegDate = PDONOW;
	$obj->regPersonID = $_SESSION['USER']["PersonID"];
	$obj->DocDate = PDONOW;
	$obj->CycleID = $_SESSION["accounting"]["CycleID"];
	$obj->BranchID = $_SESSION["accounting"]["BranchID"];
	$obj->description = "سند افتتاحیه";
	$obj->DocType = DOCTYPE_STARTCYCLE;
	$result = $obj->Add($pdo);

	if (!$result) {
		$pdo->rollBack();
		print_r(ExceptionHandler::PopAllExceptions());
		echo Response::createObjectiveResponse(false, "");
		die();
	}
	
	PdoDataAccess::runquery("
		insert into ACC_DocItems(DocID,CostID,TafsiliType,TafsiliID,TafsiliType2,TafsiliID2,
			DebtorAmount,CreditorAmount,locked)
		select $obj->DocID,CostID,TafsiliType,TafsiliID,TafsiliType2,TafsiliID2,
			if( sum(DebtorAmount-CreditorAmount)>0, sum(DebtorAmount-CreditorAmount), 0 ),
			if( sum(CreditorAmount-DebtorAmount)>0, sum(CreditorAmount-DebtorAmount), 0 ),
			1
		from ACC_DocItems i
		join ACC_docs using(DocID)
		where DocType <> ".DOCTYPE_ENDCYCLE." 
			AND CycleID=" . ($_SESSION["accounting"]["CycleID"]-1) . "
			AND BranchID = " . $_SESSION["accounting"]["BranchID"] . "
		group by CostID,TafsiliID,TafsiliID2	
		having sum(CreditorAmount-DebtorAmount)<>0
	", array(), $pdo);
	
	if(ExceptionHandler::GetExceptionCount() > 0)
	{
		$pdo->rollBack();
		print_r(ExceptionHandler::PopAllExceptions());
		echo Response::createObjectiveResponse(false, "");
		die();
	}
	
	if(PdoDataAccess::AffectedRows($pdo) == 0)
	{
		$pdo->rollBack();
		echo Response::createObjectiveResponse(false, "ردیفی برای صدور سند افتتاحیه یافت نشد");
		die();
	}
	
	$pdo->commit();
	echo Response::createObjectiveResponse(true, "");
	die();
}

function ComputeDoc(){
	
	require_once 'import.data.php';
	
	switch($_POST["ComputeType"])
	{
		case "ShareProfit":
			ComputeShareProfit();
			break;
	}
}

//.................................

function GetAccountSummary($ReturnMode = false, $BranchID = "", $where = "", $param = array()){
	
	$param = array(":c" => $_SESSION["accounting"]["CycleID"], 
		":b" => $BranchID != "" ? $BranchID : $_SESSION["accounting"]["BranchID"]);
	
	if(!empty($_GET["fields"]) && !empty($_GET["query"]))
	{
		$where .= " AND " . $_GET["fields"] . " like :f";
		$param[":f"] = "%" . $_GET["query"] . "%";
	}	
	
	$temp = PdoDataAccess::runquery_fetchMode("
		select t.TafsiliID,t.TafsiliDesc, 
			ifnull(pasandaz.amount,0) pasandaz,
			ifnull(kootah.amount,0) kootah,
			ifnull(boland.amount,0) boland,
			ifnull(jari.amount,0) jari,
			packNo
			
		from ACC_tafsilis t 
			left join BSC_persons p on(t.TafsiliType=".TAFTYPE_PERSONS." AND t.ObjectID=p.PersonID)
			left join DMS_packages pk on(pk.BranchID=:b AND pk.PersonID=p.PersonID)

			left join (select TafsiliID,sum(CreditorAmount-DebtorAmount) amount
						from ACC_DocItems join ACC_docs using(DocID)
						where TafsiliType=".TAFTYPE_PERSONS." AND BranchID=:b AND CycleID=:c AND CostID=".COSTID_saving."
						group by TafsiliID
			)pasandaz on(pasandaz.TafsiliID=t.TafsiliID)
			left join (select TafsiliID,sum(CreditorAmount-DebtorAmount) amount
						from ACC_DocItems join ACC_docs using(DocID)
						where TafsiliType=".TAFTYPE_PERSONS." AND BranchID=:b AND CycleID=:c AND CostID=".COSTID_ShortDeposite."
						group by TafsiliID
			)kootah on(kootah.TafsiliID=t.TafsiliID)
			left join (select TafsiliID,sum(CreditorAmount-DebtorAmount) amount
						from ACC_DocItems join ACC_docs using(DocID)
						where TafsiliType=".TAFTYPE_PERSONS." AND BranchID=:b AND CycleID=:c AND CostID=".COSTID_LongDeposite."
						group by TafsiliID
			)boland on(boland.TafsiliID=t.TafsiliID)
			left join (select TafsiliID,sum(CreditorAmount-DebtorAmount) amount
						from ACC_DocItems join ACC_docs using(DocID)
						where TafsiliType=".TAFTYPE_PERSONS." AND BranchID=:b AND CycleID=:c AND CostID=".COSTID_current."
						group by TafsiliID
			)jari on(jari.TafsiliID=t.TafsiliID)
		where TafsiliType=" . TAFTYPE_PERSONS . $where . dataReader::makeOrder(), $param);
	
	if($ReturnMode)
		return $temp;
	//echo PdoDataAccess::GetLatestQueryString();
	$no = $temp->rowCount();
	$temp = PdoDataAccess::fetchAll($temp, $_GET["start"], $_GET["limit"]);
	echo dataReader::getJsonData($temp, $no, $_GET ["callback"]);
	die();
	
}

function GetAccountFlow() {
	
	$CostID = $_REQUEST["BaseCostID"];
	$TafsiliID = $_REQUEST["TafsiliID"];
	
	$query = "select d.*,di.*
		from ACC_DocItems di
			join ACC_docs d using(DocID)
		where d.BranchID=:b AND 
			di.CostID=:cost AND di.TafsiliType = :t AND di.TafsiliID=:tid " . dataReader::makeOrder();
	
	$param = array(
		":b" => $_SESSION["accounting"]["BranchID"],
		":cost" => $CostID,
		":t" => TAFTYPE_PERSONS,
		":tid" => $TafsiliID
	);
	
	$temp = PdoDataAccess::runquery_fetchMode($query, $param);
	$no = $temp->rowCount();
	
	//------------------------------------------------
	$BlockedAmount = ACC_CostBlocks::GetBlockAmount($CostID, TAFTYPE_PERSONS, $TafsiliID);
	//------------------------------------------------
	echo dataReader::getJsonData($temp->fetchAll(), $no, $_GET ["callback"],$BlockedAmount);
	die();
}

function RegisterInOutAccountDoc() {
	
	$CostID = $_REQUEST["BaseCostID"];
	$BaseTafsiliID = $_REQUEST["BaseTafsiliID"];
	
	$mode = $_POST["mode"]*1;
	if($mode < 0)
	{
		$query = "select ifnull(sum(CreditorAmount-DebtorAmount),0) remaindar
		from ACC_DocItems di
			join ACC_docs d using(DocID)
		where d.CycleID=:c AND d.BranchID=:b AND 
			di.CostID=:cost AND di.TafsiliType = :t AND di.TafsiliID=:tid";
		$param = array(
			":c" => $_SESSION["accounting"]["CycleID"],
			":b" => $_SESSION["accounting"]["BranchID"],
			":cost" => $CostID,
			":t" => TAFTYPE_PERSONS,
			":tid" => $BaseTafsiliID
		);
		
		$dt = PdoDataAccess::runquery($query, $param);
		//echo PdoDataAccess::GetLatestQueryString();
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
	$obj->description = $mode > 0 ? "واریز به حساب" : "برداشت از حساب";
	if(!$obj->Add($pdo))
	{
		echo Response::createObjectiveResponse(false,"خطا در ایجاد سند");
		die();
	}
	
	//-------------------------------------------------
		
	$itemObj = new ACC_DocItems();
	$itemObj->DocID = $obj->DocID;
	$itemObj->CostID = $CostID;
	$itemObj->DebtorAmount = $mode > 0 ? 0 : $_POST["amount"];
	$itemObj->CreditorAmount = $mode > 0 ? $_POST["amount"] : 0;
	$itemObj->TafsiliType = TAFTYPE_PERSONS;
	$itemObj->TafsiliID = $BaseTafsiliID;
	$itemObj->details = $_POST["description"];
	if(!$itemObj->Add($pdo))
	{
		echo Response::createObjectiveResponse(false,  ExceptionHandler::GetExceptionsToString());
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
		$itemObj->TafsiliType2 = TAFTYPE_ACCOUNTS;
		$itemObj->TafsiliID2 = $_POST["TafsiliID2"];
	}
	if(!$itemObj->Add($pdo))
	{
		echo Response::createObjectiveResponse(false,  ExceptionHandler::GetExceptionsToString());
		die();
	}
	
	$pdo->commit();
	echo Response::createObjectiveResponse(true,"");
	die();
}

function GetSubjects(){
	
	$dt = PdoDataAccess::runquery("select * from BaseInfo where typeID=73 AND IsActive='YES'");
	echo dataReader::getJsonData($dt, count($dt), $_GET["callback"]);
	die();
}

//.................................

function GetAllCostBlocks(){
	
	$temp = ACC_CostBlocks::Get();
	$dt = PdoDataAccess::fetchAll($temp, $_GET["start"], $_GET["limit"]);
	
	echo dataReader::getJsonData($dt, $temp->rowCount(), $_GET["callback"]);
	die();
}
?>