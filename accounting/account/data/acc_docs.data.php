<?php

//-----------------------------
//	Programmer	: SH.Jafarkhani
//	Date		: 90.10
//-----------------------------

require_once '../../header.inc.php';
require_once '../class/acc_docs.class.php';

$task = isset($_POST ["task"]) ? $_POST ["task"] : (isset($_GET ["task"]) ? $_GET ["task"] : "");

switch ($task) {
	case "selectDocs":
		selectDocs();

	case "saveDoc":
		saveDoc();

	case "removeDoc":
		removeDoc();

	case "selectDocItems":
		selectDocItems();

	case "saveDocItem":
		saveDocItem();

	case "removeDocItem":
		removeDocItem();

	case "confirm":
		confirm();
		
	case "archive":
		archive();

	//...........................

	case "selectChecks":
		selectChecks();

	case "saveChecks":
		saveChecks();

	case "removeChecks":
		removeChecks();
		
	case "RegisterCheck":
		RegisterCheck();

	//............................

	case "selectFactorChecks":
		selectFactorChecks();

	case "saveRecieveCheck":
		saveRecieveCheck();

	//............................

	case "registerDailyAccDoc":
		registerDailyAccDoc();

	case "checkAccDocExistance":
		checkAccDocExistance();

	case "StoreDocRegister":
		StoreDocRegister();
	//............................

	case "shareBack":
		shareBack();

	case "ExtraSubDoc":
		ExtraSubDoc();

	case "shareCreateDoc":
		shareCreateDoc();

	case "openDoc":
		openDoc();

	case "UpdateChecks":
		UpdateChecks();
		
	case "copyDoc":
		copyDoc();
		
	//............................
		
	case "sharesCompute":
		sharesCompute();
		
	case "RegisterEndDoc":
		RegisterEndDoc();
		
	case "RegisterStartDoc":
		RegisterStartDoc();
}

function selectDocs() {
	$where = " sd.cycleID=:cid";
	$whereParam = array(":cid" => $_SESSION["CYCLE"]);
	if (isset($_GET["fields"]) && !empty($_GET["query"])) {
		switch ($_GET["fields"]) {
			case "docID":
				$where .= " AND sd.docID = :tl";
				$whereParam[":tl"] = $_GET["query"];
				break;
			case "docDate":
				$where .= " AND sd.docDate = :tl";
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
	$temp = manage_acc_docs::GetAll($where, $whereParam);
	$no = count($temp);
	$temp = array_slice($temp, $_GET["start"], $_GET["limit"]);
	echo dataReader::getJsonData($temp, $no, $_GET ["callback"]);
	die();
}

function saveDoc() {
	if ($_POST["ref_docID"] != "") {
		$query = "select * from acc_docs where docID=?";
		$dt = PdoDataAccess::runquery($query, array($_POST["ref_docID"]));
		if (count($dt) == 0) {
			echo Response::createObjectiveResponse(false, "INVALID_ref");
			die();
		}
		if ($dt[0]["docStatus"] != "CONFIRM") {
			echo Response::createObjectiveResponse(false, "NOTCONFIRM_ref");
			die();
		}
	}
	$pdo = PdoDataAccess::getPdoObject();
	/* @var $pdo PDO */

	$pdo->beginTransaction();

	$obj = new manage_acc_docs();
	PdoDataAccess::FillObjectByArray($obj, $_POST);
	$obj->cycleID = $_SESSION["CYCLE"];

	$obj->_oldDocID = $_POST["oldDocID"];
	if ($obj->_oldDocID == "") {
		$obj->regDate = DateModules::Now();
		$obj->regPersonID = $_SESSION['USER']["personID"];
		$return = $obj->Add($pdo);
	} else {
		if ($obj->docID != $obj->_oldDocID) {
			$dt = PdoDataAccess::runquery("select * from acc_docs where docID=?", array($obj->docID), $pdo);
			if (count($dt) != 0) {
				$pdo->rollBack();
				echo Response::createObjectiveResponse(false, "duplicate");
				die();
			}
		}
		$return = $obj->Edit(false, false, $pdo);
	}

	if (!$return) {
		$pdo->rollBack();
		print_r(ExceptionHandler::PopAllExceptions());
		echo Response::createObjectiveResponse(false, "");
		die();
	}

	$dt = PdoDataAccess::runquery("select count(*) from acc_docs where DocID<? AND cycleID=" . $_SESSION["CYCLE"], array($obj->docID), $pdo);
	$pdo->commit();
	echo Response::createObjectiveResponse(true, $dt[0][0] + 1);
	die();
}

function removeDoc() {
	$result = manage_acc_docs::Remove($_REQUEST["docID"]);
	echo $result ? "true" : "false";
	die();
}

function confirm() {

	PdoDataAccess::runquery("update acc_docs set docStatus='CONFIRM' where docID=" . $_POST["docID"]);
	echo "true";
	die();
}

function archive() {

	PdoDataAccess::runquery("update acc_docs set docStatus='ARCHIVE' where docID=" . $_POST["docID"]);
	echo "true";
	die();
}

function regResid() {
	$pdo = PdoDataAccess::getPdoObject();
	/* @var $pdo PDO */
	$pdo->beginTransaction();

	$obj = new manage_acc_docs();
	$obj->cycleID = $_SESSION["CYCLE"];
	$obj->storeID = $_POST["storeID"];
	$obj->doc_type = 20;
	$obj->reg_date = PDONOW;
	$obj->doc_date = PDONOW;
	$obj->ref_docID = $_POST["docID"];

	if (isset($_POST["desc_storeID"]))
		$obj->desc_storeID = $_POST["desc_storeID"];

	$result = $obj->Add($pdo);
	if (!$result) {
		$pdo->rollBack();
		echo "false";
		die();
	}

	$query = "insert into store_doc_items(cycleID,docID,itemID,itemCount,buyPrice,
			coverPrice,expireDate,salePrice,tax)
		select " . $obj->cycleID . "," . $obj->docID . ",itemID,itemCount,buyPrice,
			coverPrice,expireDate,salePrice,tax
		from store_doc_items where docID=?";
	$result = PdoDataAccess::runquery($query, array($_POST["docID"]), $pdo);
	if ($result === false) {
		print_r(ExceptionHandler::PopAllExceptions());
		$pdo->rollBack();
		echo "false";
		die();
	}

	$obj2 = new manage_acc_docs();
	$obj2->docID = $_POST["docID"];
	$obj2->ref_docID = $obj->docID;
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

function copyDoc(){
	
	PdoDataAccess::runquery("insert into acc_doc_items
		SELECT :dst,rowID,kolID,moinID,tafsiliID,tafsili2ID,bdAmount,bsAmount,details,locked FROM acc_doc_items where docID=:src",
		array(":src" => $_POST["src_DocID"], ":dst" => $_POST["dst_DocID"]));
	
	echo PdoDataAccess::GetLatestQueryString();
	print_r(ExceptionHandler::PopAllExceptions());
	echo PdoDataAccess::AffectedRows();
	
	echo Response::createObjectiveResponse(true, "");
	die();
}

//............................

function selectDocItems() {
	$where = "docID=:did";
	$whereParam = array(":did" => $_REQUEST["docID"]);

	if (isset($_GET["fields"]) && !empty($_GET["query"])) {
		switch ($_GET["fields"]) {
			case "tafsiliID":
				$where .= " AND t.tafsiliTitle like :tl";
				$whereParam[":tl"] = "%" . $_GET["query"] . "%";
				break;
			case "moinID":
				$where .= " AND moinTitle like :tl";
				$whereParam[":tl"] = "%" . $_GET["query"] . "%";
				break;
			case "tafsili2ID":
				$where .= " AND 2t.tafsiliTitle like :tl";
				$whereParam[":tl"] = "%" . $_GET["query"] . "%";
				break;
			case "kolID":
				$where .= " AND kolTitle like :tl";
				$whereParam[":tl"] = "%" . $_GET["query"] . "%";
				break;
			case "bdAmount":
				$where .= " AND bdAmount = :tl";
				$whereParam[":tl"] = $_GET["query"];
				break;
			case "bsAmount":
				$where .= " AND bsAmount = :tl";
				$whereParam[":tl"] = $_GET["query"];
				break;
		}
	}
	$where .= dataReader::makeOrder();

	$temp = manage_acc_doc_items::GetAll($where, $whereParam);
	$no = $temp->rowCount();
	$temp = PdoDataAccess::fetchAll($temp, $_GET["start"], $_GET["limit"]);
	//..........................................................................
	$dt = PdoDataAccess::runquery("
		select sum(bdAmount) bd,sum(bsAmount) bs
		from acc_doc_items
		where docID=? 
		group by docID", array($_REQUEST["docID"]));
	$bdSum = count($dt) != 0 ? $dt[0]["bd"] : 0;
	$bsSum = count($dt) != 0 ? $dt[0]["bs"] : 0;
	//..........................................................................	
	echo dataReader::getJsonData($temp, $no, $_GET ["callback"], $bdSum . "," . $bsSum);
	die();
}

function saveDocItem() {

	$obj = new manage_acc_doc_items();
	PdoDataAccess::FillObjectByJsonData($obj, $_POST["record"]);

	if ($obj->tafsiliID == "")
		$obj->tafsiliID = PDONULL;
	if ($obj->tafsili2ID == "")
		$obj->tafsili2ID = PDONULL;

	if ($obj->rowID == "0")
		$return = $obj->Add();
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

function removeDocItem() {
	$result = manage_acc_doc_items::Remove($_POST["docID"], $_POST["rowID"]);
	print_r(ExceptionHandler::PopAllExceptions());
	echo $result ? "true" : "false";
	die();
}

//............................

function selectChecks() {
	$where = "1=1";
	$whereParam = array();
	if (isset($_REQUEST["docID"])) {
		$where .= " AND docID=:d";
		$whereParam[":d"] = $_REQUEST["docID"];
	}

	if (isset($_GET["fields"]) && !empty($_GET["query"])) {
		switch ($_GET["fields"]) {
			case "checkNo":
				$where .= " AND checkNo = :q";
				$whereParam[":q"] = $_GET["query"];
				break;
			case "reciever":
				$where .= " AND reciever like :q";
				$whereParam[":q"] = '%' . $_GET["query"] . '%';
				break;
			case "docID":
				$where .= " AND docID = :q";
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
			case "checkStatus":
				$where .= " AND b.title like :q";
				$whereParam[":q"] = '%' . $_GET["query"] . '%';
				break;
		}
	}

	if (!empty($_GET["date"])) {
		$where .= " AND checkDate = :q";
		$whereParam[":q"] = DateModules::shamsi_to_miladi($_GET["date"]);
	}

	$where .= dataReader::makeOrder();

	$temp = manage_acc_checks::GetAll($where, $whereParam);
	$no = count($temp);
	if (isset($_GET["start"]))
		$temp = array_slice($temp, $_GET["start"], $_GET["limit"]);

	echo dataReader::getJsonData($temp, $no, $_GET["callback"]);
	die();
}

function saveChecks() {
	$obj = new manage_acc_checks();
	PdoDataAccess::FillObjectByJsonData($obj, $_POST["record"]);
	
	//..........................................
	$query = "select * from acc_checks where checkNo=? AND accountID=?";
	$query .=!empty($obj->checkID) ? " AND checkID<>" . $obj->checkID : "";

	$dt = PdoDataAccess::runquery($query, array($obj->checkNo, $obj->accountID));
	if (count($dt) > 0) {
		echo Response::createObjectiveResponse(false, "duplicate");
		die();
	}
	//..........................................
	if($obj->description == "")
	{
		$dt = PdoDataAccess::runquery("
			select CompanyBillNo,CompanyBillDate
			from store_docs s 
				join acc_docs c on(c.DocID=s.accDocID) 
			where c.docID=?", array($obj->docID));
		if(count($dt) > 0)
			$obj->description = "بابت فاکتور شماره " . $dt[0][0];
	}
	//..........................................
	if($obj->amount == 0)
	{
		$dt = PdoDataAccess::runquery("
			select i.* from acc_doc_items i left join acc_checks c on(i.docID=c.DocID and i.tafsiliID=c.tafsiliID)
				where kolID=42 AND moinID=1 and checkID is null and i.docID=?", array($obj->docID));
		
		if(count($dt) > 0)
		{
			$obj->tafsiliID = $dt[0]["tafsiliID"];
			$obj->amount = $dt[0]["bdAmount"] + $dt[0]["bsAmount"];
		}
		
	}	
	//..........................................
	if (empty($obj->checkID))
	{
		unset($obj->checkStatus);
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
	$result = manage_acc_checks::Remove($_POST["checkID"]);
	echo $result ? "true" : "conflict";
	die();
}

function RegisterCheck() {
	
	PdoDataAccess::runquery("update acc_checks set checkStatus=1 where checkID=?", array($_POST["checkID"]));
	echo Response::createObjectiveResponse(PdoDataAccess::AffectedRows() == 0 ? false : true, "");
	die();
}

//............................

function selectFactorChecks() {
	$where = "";
	$whereParam = array();

	if (isset($_GET["fields"]) && !empty($_GET["query"])) {
		switch ($_GET["fields"]) {
			case "checkNo":
				$where .= " AND checkNo = :q";
				$whereParam[":q"] = $_GET["query"];
				break;
			case "factorIDs":
				$where .= " AND factorID = :q";
				$whereParam[":q"] = $_GET["query"];
				break;
			case "accountNo":
				$where .= " AND accountNo = :q";
				$whereParam[":q"] = $_GET["query"];
				break;
			case "bankTitle":
				$where .= " AND bankTitle like :q";
				$whereParam[":q"] = '%' . $_GET["query"] . '%';
				break;
			case "branch":
				$where .= " AND branch like :q";
				$whereParam[":q"] = '%' . $_GET["query"] . '%';
				break;
			case "amount":
				$where .= " AND amount = :q";
				$whereParam[":q"] = $_GET["query"];
				break;
			case "checkStatus":
				$where .= " AND b.title like :q";
				$whereParam[":q"] = '%' . $_GET["query"] . '%';
				break;
		}
	}
	if (!empty($_GET["date"])) {
		$where .= " AND checkDate = :q";
		$whereParam[":q"] = DateModules::shamsi_to_miladi($_GET["date"]);
	}

	$where .= dataReader::makeOrder();

	$query = "select fc.*,b.bankTitle,if(f.PersonID is null,f.customerName,sh.fullname) as customerName,
      concat(fc.factorID,if(t.factorIDs is not null,concat(',',t.factorIDs),'')) as factorIDs,
	  bf.title
		from factor_checks fc
		left join (
			select primaryFactorID, group_concat(factorID) as factorIDs
			from factor_checks where cycleID=" . $_SESSION["CYCLE"] . " AND primaryFactorID is not null
			group by primaryFactorID) t on(fc.factorID=t.primaryFactorID)
		join sale_factors f using(factorID)
		left join shareholders sh on(sh.PersonID=f.personID)
		join basic_info bf on(bf.typeID=3 AND bf.infoID=checkStatus)
		join banks b using(bankID)

    where fc.cycleID=" . $_SESSION["CYCLE"] . " AND fc.primaryFactorID is null" . $where;

	$temp = PdoDataAccess::runquery($query, $whereParam);
	$no = count($temp);
	$temp = array_slice($temp, $_GET["start"], $_GET["limit"]);

	echo dataReader::getJsonData($temp, $no, $_GET["callback"]);
	die();
}

function saveRecieveCheck() {
	$obj = json_decode(stripslashes($_POST["record"]));
	$query = "
		update factor_checks set 
	
		checkStatus=" . $obj->checkStatus . " ,
		payOffDate='" . DateModules::shamsi_to_miladi($obj->payOffDate) . "',
		accDocID=" . $obj->accDocID . "
		
		where cycleID=" . $_SESSION["CYCLE"] . " AND rowID=" . $obj->rowID;

	PdoDataAccess::runquery($query);
	echo Response::createObjectiveResponse(ExceptionHandler::GetExceptionCount() == 0, "");
	die();
}

//............................

function registerDailyAccDoc() {
	$date = $_POST["dayDate"];

	//----------------- Account details  ---------------------
	$query = "select s.* ,p.title posTitle,p2.title posTitle2,o.title as BonTitle, concat(fname,' ',lname) cashierName, 
				p.bankID posBankID,p2.bankID pos2BankID
		from sale_factors s
			left join pos_devices p using(posID)
			left join pos_devices p2 on(s.PosID2=p2.posID)
			left join other_pays o using(otherPayID)
			left join persons ps on(ps.personID=s.cashier)
		where substr(s.reg_date,1,10)=? ";
	$whereParam = array(DateModules::shamsi_to_miladi($date, "-"));
	$temp = PdoDataAccess::runquery($query, $whereParam);
	if (count($temp) == 0) {
		echo Response::createObjectiveResponse(false, "EmptyFactor");
		die();
	}
		
	//--------- select information of bill -------------------
	$billDT = PdoDataAccess::runquery("
		select group_concat(description SEPARATOR '\n') description, sum(BillAmount) BillAmount , bankID, bankTitle
		from DailyDescription 
		join banks using(BankID)
		where DayDate=?		
		group by bankID", 
		array(DateModules::shamsi_to_miladi($date, "-")));
	$billArr = array();
	foreach($billDT as $row)
		$billArr[$row["bankID"]] = $row;
	//------------------------------------------------

	$pdo = PdoDataAccess::getPdoObject();
	/* @var $pdo PDO */
	$pdo->beginTransaction();
	//---------------- account header doc --------------------
	$obj = new manage_acc_docs();
	$obj->regDate = PDONOW;
	$obj->regPersonID = $_SESSION['USER']["personID"];
	$obj->docDate = DateModules::shamsi_to_miladi($date);
	$obj->cycleID = $_SESSION["CYCLE"];
	$obj->description = "سند فروش روزانه - بدهکاران";
	$obj->docType = "CASH";
	$result = $obj->Add($pdo);

	if (!$result) {
		$pdo->rollBack();
		echo "1:";
		print_r(ExceptionHandler::PopAllExceptions());
		echo Response::createObjectiveResponse(false, "");
		die();
	}
	
	//------------------------------------------------
	$q2 = "select sf.*,b.title as boothTitle, s.factor_type
		from sale_factor_items sf
			join sale_factors s using(factorID)
			left join booths b using(boothID)
		where boothID is not NULL AND substr(s.reg_date,1,10)=?";
	$whereParam = array(DateModules::shamsi_to_miladi($date, "-"));
	$dt = PdoDataAccess::runquery($q2, $whereParam);
	
	$booths = array();
	for($j=0; $j<count($dt); $j++)
	{
		if(!isset($booths[$dt[$j]["boothTitle"]]))
			$booths[$dt[$j]["boothTitle"]] = 0;

		$booths[$dt[$j]["boothTitle"]] += ($dt[$j]["factor_type"] == "SALE" ? 1 : -1)*(int)$dt[$j]["salePrice"];
	}
	
	//------------------------------------------------
	$storeDocs = PdoDataAccess::runquery("
		select round(sum(
			(buyPrice - buyPrice * ifnull(sdi.discount,0)/100) * itemCount
		)) - TotalDiscount
		from store_doc_items sdi join store_docs using(docID)
		where doc_date=? AND cashPay=1

		", array(DateModules::shamsi_to_miladi($date, "-")));

	$storeDocsSum = count($storeDocs) == 0 ? 0 : $storeDocs[0][0];
	
	//------------------------------------------------
	/*$pos = array();
	$checks = array();
	$letters = array();
	$others = array();
	$cashAmount = 0;

	$totalFactorPrice = 0;
	$totalFactorDiscount = 0;
	$transferAmount = 0;
	$totalFactorTax = 0;
	$totalReturn = 0;

	for ($i = 0; $i < count($temp); $i++) {
		$totalAmount = (int) $temp[$i]["factorPrice"] + (int) $temp[$i]["factorTax"] +
				(int) $temp[$i]["transferAmount"] + (int) $temp[$i]["profitAmount"] -
				(int) $temp[$i]["factorDiscount"] - (int) $temp[$i]["otherDiscount"] -
				(int) $temp[$i]["customerDiscount"];

		if ($temp[$i]["factor_type"] == "SALE") {
			$totalFactorPrice += (int) $temp[$i]["factorPrice"];
			$totalFactorDiscount += (int) $temp[$i]["factorDiscount"] + (int) $temp[$i]["otherDiscount"] + (int) $temp[$i]["customerDiscount"];
			$totalFactorTax += (int) $temp[$i]["factorTax"];
			$transferAmount += (int) $temp[$i]["transferAmount"];
		} else {
			$totalReturn = $totalReturn + $totalAmount;
			$cashAmount = $cashAmount - $totalAmount;
			$totalFactorTax -= (int) $temp[$i]["factorTax"];
			continue;
		}

		if ($temp[$i]["posID"] != "" && !isset($pos[$temp[$i]["posTitle"]]))
			$pos[$temp[$i]["posTitle"]] = 0;
		if ($temp[$i]["PosID2"] != "" && !isset($pos[$temp[$i]["posTitle2"]]))
			$pos[$temp[$i]["posTitle2"]] = 0;
		//----------------------------------------------

		if ($temp[$i]["pay_type"] != "CASH") {
			$dt = PdoDataAccess::runquery(
							"select * from factor_checks 
						join banks using(bankID)
						where cycleID=" . $_SESSION["CYCLE"] . " AND factorID=" . $temp[$i]["factorID"] . "
							AND primaryFactorID is null");

			if (count($dt) != 0) {
				$checks = array_merge($checks, $dt);
			}
			//..................................................................
			$dt = PdoDataAccess::runquery(
							"select l.*, if(s.PersonID is null,s.customerName,sh.fullname) as customerName,
						u.title as unitTitle
						from factor_letters l
						join sale_factors s using(factorID)
						left join shareholders sh on(sh.PersonID=s.personID)
						left join units u on(u.unitID=l.unitID)
						where factorID=" . $temp[$i]["factorID"]);
			if (count($dt) != 0) {
				$dt[0]["amount"] = $totalAmount - (int) $temp[$i]["cashAmount"];
				$letters = array_merge($letters, $dt);
			}
			//..................................................................
			if ($temp[$i]["BonTitle"] != "" && !isset($others[$temp[$i]["BonTitle"]]))
				$others[$temp[$i]["BonTitle"]] = 0;
			$others[$temp[$i]["BonTitle"]] = (int) $others[$temp[$i]["BonTitle"]] + (int) $temp[$i]["otherPayAmount"];
			//..................................................................
			$cashAmount += ($temp[$i]["cashAmount"] != 0) ?
					(int) $temp[$i]["cashAmount"] : $totalAmount - (int) $temp[$i]["posAmount"] - (int) $temp[$i]["PosAmount2"] - (int) $temp[$i]["otherPayAmount"];
			if ($temp[$i]["posID"] != "") {
				$pos[$temp[$i]["posTitle"]] = (int) $pos[$temp[$i]["posTitle"]] + (int) $temp[$i]["posAmount"];
			}
			if ($temp[$i]["PosID2"] != "") {
				$pos[$temp[$i]["posTitle2"]] = (int) $pos[$temp[$i]["posTitle2"]] + (int) $temp[$i]["PosAmount2"];
			}
		} else {
			if ($temp[$i]["posID"] != "") {
				if ((int) $temp[$i]["posAmount"] == 0) {
					$pos[$temp[$i]["posTitle"]] = (int) $pos[$temp[$i]["posTitle"]] + $totalAmount;
				} else {
					$pos[$temp[$i]["posTitle"]] = (int) $pos[$temp[$i]["posTitle"]] + (int) $temp[$i]["posAmount"];
					if ($temp[$i]["PosID2"] != "")
						$pos[$temp[$i]["posTitle2"]] = (int) $pos[$temp[$i]["posTitle2"]] + (int) $temp[$i]["PosAmount2"];
					$cashAmount += $totalAmount - (int) $temp[$i]["posAmount"] - (int) $temp[$i]["PosAmount2"];
				}
			}
			else
				$cashAmount = $cashAmount + $totalAmount;
		}
	}*/
	
	$pos = array();
	$posTitles = array();
	$checks = array();
	$letters = array();
	$others = array();
	$cashAmount = 0;

	$totalFactorPrice = 0;
	$totalFactorDiscount = 0;
	$d1=$d2=$d3=0;
	$totalFactorTax = 0;
	$totalReturn = 0;

	for($i=0; $i < count($temp); $i++)
	{
		$totalAmount = (int)$temp[$i]["factorPrice"] + (int)$temp[$i]["factorTax"] + 
					(int)$temp[$i]["transferAmount"] + (int)$temp[$i]["profitAmount"] -
					(int)$temp[$i]["factorDiscount"] - (int)$temp[$i]["otherDiscount"] - 
					(int)$temp[$i]["customerDiscount"];

		if($temp[$i]["factor_type"] != "RETURN")
		{
			$totalFactorPrice += (int)$temp[$i]["factorPrice"] + (int)$temp[$i]["profitAmount"] + (int)$temp[$i]["transferAmount"] + (int)$temp[$i]["factorTax"];
			$totalFactorDiscount += (int)$temp[$i]["factorDiscount"] + (int)$temp[$i]["otherDiscount"] + (int)$temp[$i]["customerDiscount"];
			$d1 += (int)$temp[$i]["factorDiscount"];
			$d2 += (int)$temp[$i]["otherDiscount"];
			$d3 += (int)$temp[$i]["customerDiscount"];
			$totalFactorTax += (int)$temp[$i]["factorTax"];
		}
		else
		{
			$totalReturn += $totalAmount;
			if($temp[$i]["pay_type"] == "CASH")
				$cashAmount -= $totalAmount;
			$totalFactorTax -= (int)$temp[$i]["factorTax"];
			continue;
		}

		if($temp[$i]["posID"] != "" && !isset($pos[$temp[$i]["posBankID"]]))
			$pos[$temp[$i]["posBankID"]] = 0;

		if($temp[$i]["PosID2"] != "" && !isset($pos[$temp[$i]["pos2BankID"]]))
			$pos[$temp[$i]["pos2BankID"]] = 0;

		//----------------------------------------------

		if($temp[$i]["pay_type"] == "NCASH")
		{
			$dt = PdoDataAccess::runquery(
					"select c.*,if(sh.personID is null,customerName,sh.fullname) as customer
						from factor_checks c
						join banks using(bankID)
						join sale_factors f using(factorID)
						left join shareholders sh on(sh.personID=f.PersonID)
						where c.cycleID=" . $_SESSION["CYCLE"] . " AND c.factorID=" . $temp[$i]["factorID"] . "
							AND primaryFactorID is null");

			if(count($dt) != 0)
			{
				$checks = array_merge($checks, $dt);
			}
			//..................................................................
			$dt = PdoDataAccess::runquery(
					"select l.*, if(s.PersonID is null,s.customerName,sh.fullname) as customerName,
						u.title as unitTitle
						from factor_letters l
						join sale_factors s using(factorID)
						left join shareholders sh on(sh.PersonID=s.personID)
						left join units u on(u.unitID=l.unitID)
						where factorID=" . $temp[$i]["factorID"]);
			if(count($dt) != 0)
			{
				//$dt[0]["amount"] = $totalAmount - (int)$temp[$i]["cashAmount"];
				$letters = array_merge($letters, $dt);
			}
			//..................................................................
			if($temp[$i]["BonTitle"] != "")
			{
				if(!isset($others[$temp[$i]["BonTitle"]]))
					$others[$temp[$i]["BonTitle"]] = 0;
				$others[$temp[$i]["BonTitle"]] = (int)$others[$temp[$i]["BonTitle"]] + (int)$temp[$i]["otherPayAmount"];
			}
			//..................................................................
			$cashAmount += (int)$temp[$i]["cashAmount"];

			if($temp[$i]["posID"] != "")
			{
				$pos[$temp[$i]["posBankID"]] = (int)$pos[$temp[$i]["posBankID"]] + (int)$temp[$i]["posAmount"];
				$posTitles[$temp[$i]["posBankID"]] = $temp[$i]["posTitle"];
			}
			if($temp[$i]["PosID2"] != "")
			{
				$pos[$temp[$i]["pos2BankID"]] = (int)$pos[$temp[$i]["pos2BankID"]] + (int)$temp[$i]["PosAmount2"];
				$posTitles[$temp[$i]["pos2BankID"]] = $temp[$i]["posTitle2"];
			}
		}
		else
		{
			if($temp[$i]["posID"] != "" && $temp[$i]["posID"] != "0")
			{
				if((int)$temp[$i]["posAmount"] == 0)
				{
					$pos[$temp[$i]["posBankID"]] = (int)$pos[$temp[$i]["posBankID"]] + $totalAmount - $temp[$i]["cashAmount"];
					$cashAmount += (int)$temp[$i]["cashAmount"];
					$posTitles[$temp[$i]["posBankID"]] = $temp[$i]["posTitle"];
				}
				else
				{
					$pos[$temp[$i]["posBankID"]] = (int)$pos[$temp[$i]["posBankID"]] + (int)$temp[$i]["posAmount"];
					if($temp[$i]["PosID2"] != "")
						$pos[$temp[$i]["pos2BankID"]] = (int)$pos[$temp[$i]["pos2BankID"]] + (int)$temp[$i]["PosAmount2"];
					$cashAmount += $totalAmount - (int)$temp[$i]["posAmount"] - (int)$temp[$i]["PosAmount2"];
					$posTitles[$temp[$i]["pos2BankID"]] = $temp[$i]["posTitle2"];
				}
			}
			else
			{
				$cashAmount += $totalAmount;
			}		
		}
	}
	$shareBack = PdoDataAccess::runquery("
		select sum(amount)
		from DailyOperation 
		where operation='SHAREBACK' and DayDate=?

	", array(DateModules::shamsi_to_miladi($date, "-")));
	$shareBackSum = count($shareBack) == 0 ? 0 : $shareBack[0][0];
	//------------------------------------------------
	$Extra = PdoDataAccess::runquery("
		select sum(amount) 
		from DailyOperation 
		where operation='EXTRA' and DayDate=? and CashPay=1
	", array(DateModules::shamsi_to_miladi($date, "-")));

	$Sub = PdoDataAccess::runquery("
		select sum(amount)
		from DailyOperation 
		where operation='SUB' and DayDate=? and CashPay=1
	", array(DateModules::shamsi_to_miladi($date, "-")));

	$addToCash = count($Sub) == 0 ? 0 : $Sub[0][0];
	$subOfCash = count($Extra) == 0 ? 0 : $Extra[0][0];
	//------------------------------------------------
	$shareCreate = PdoDataAccess::runquery("
		select sum(amount) 
		from DailyOperation 
		where operation='SHARECREATE' and DayDate=? and CashPay=1	
	", array(DateModules::shamsi_to_miladi($date, "-")));

	$shareCreateSum = count($shareCreate) == 0 ? 0 : $shareCreate[0][0];
	//------------------------------------------------
	$InvalidChecks = PdoDataAccess::runquery("
		select fc.*,if(sh.personID is null,f.customerName,sh.fullname) as customer,bankTitle

		from sale_factors f
		join factor_checks fc on(f.ref_factorID=fc.factorID)
		join banks using(bankID)
		left join shareholders sh on(sh.personID=f.PersonID)

		where f.factor_type='RETURN' AND fc.validity='INVALID' AND substr(f.reg_date,1,10)=? ", 
		array(DateModules::shamsi_to_miladi($date, "-")));

	$InvalidChecksSum = 0;
	foreach($InvalidChecks as $check)
		$InvalidChecksSum += (float)$check["amount"];

	$InvalidLetters = PdoDataAccess::runquery("
		select fl.*,if(f.PersonID is null,f.customerName,sh.fullname) as customerName,u.title as unitTitle

		from sale_factors f
		join factor_letters fl on(f.ref_factorID=fl.factorID)
		left join shareholders sh on(sh.PersonID=f.personID)
		left join units u on(u.unitID=fl.unitID)

		where factor_type='RETURN' AND fl.validity='INVALID' AND substr(f.reg_date,1,10)=? 

	", array(DateModules::shamsi_to_miladi($date, "-")));

	$InvalidLettersSum = 0;
	foreach($InvalidLetters as $letter)
		$InvalidLettersSum += (float)$letter["amount"];

	//---------- store docs pay --------------
	if ($storeDocsSum != 0) {
		$dobj = new manage_acc_doc_items();
		$dobj->docID = $obj->docID;
		$dobj->bdAmount = $storeDocsSum;
		$dobj->locked = "1";
		$dobj->details = "مبلغ پرداختی فاکتور های خرید";
		$result = $dobj->Add($pdo);
		if (!$result) {
			$pdo->rollBack();
			echo "3:";
			print_r(ExceptionHandler::PopAllExceptions());
			echo Response::createObjectiveResponse(false, "");
			die();
		}
	}
	//------- pos -----------------
	foreach ($pos as $bankID => $posAmount) {
		if($bankID == "" || $posAmount == "0")
			continue;
		$dobj = new manage_acc_doc_items();
		$dobj->docID = $obj->docID;
		$dobj->locked = "1";
		$dobj->bdAmount = (int)$posAmount + (int)(isset($billArr[$bankID]) ? $billArr[$bankID]["BillAmount"] : 0);
		if(isset($billArr[$bankID]))
			unset($billArr[$bankID]);
		$dobj->details = "دستگاه خودپرداز : " . $posTitles[$bankID];
		$result = $dobj->Add($pdo);
		if (!$result) {
			$pdo->rollBack();
			echo "4:";
			print_r(ExceptionHandler::PopAllExceptions());
			echo Response::createObjectiveResponse(false, "");
			die();
		}
	}
	//------------ if bill bank not included in poses ---------------
	if(count($billArr) > 0)
	{
		foreach ($billArr as $bill)
		{
			$dobj = new manage_acc_doc_items();
			$dobj->docID = $obj->docID;
			$dobj->locked = "1";
			$dobj->bdAmount = $bill["BillAmount"];
			$dobj->details =  "فیش واریزی به بانک " . $bill["bankTitle"];
			$result = $dobj->Add($pdo);
			if (!$result) {
				$pdo->rollBack();
				echo "4:";
				print_r(ExceptionHandler::PopAllExceptions());
				echo Response::createObjectiveResponse(false, "");
				die();
			}
		}
	}
	
	//---------- checks -----------------
	$checkAmount = 0;
	foreach ($checks as $check)
		$checkAmount += (int) $check["amount"];
	if ($checkAmount != 0) {
		$dobj = new manage_acc_doc_items();
		$dobj->docID = $obj->docID;
		$dobj->locked = "1";
		$dobj->bdAmount = $checkAmount;
		$dobj->details = "چک های دریافتی";
		$result = $dobj->Add($pdo);
		if (!$result) {
			$pdo->rollBack();
			echo "5:";
			print_r(ExceptionHandler::PopAllExceptions());
			echo Response::createObjectiveResponse(false, "");
			die();
		}
	}
	//---------- letters -----------------
	$uniqueLetters = array();
	foreach ($letters as $letter) {
		if(!isset($uniqueLetters[$letter["customerName"]]))
			$uniqueLetters[$letter["customerName"]] = $letter;
		else
			$uniqueLetters[$letter["customerName"]]["amount"] += $letter["amount"];
	}
	
	foreach ($uniqueLetters as $letter) {
		$dobj = new manage_acc_doc_items();
		$dobj->docID = $obj->docID;
		$dobj->locked = "1";
		$dobj->bdAmount = $letter["amount"];
		$dobj->details = "بدهکار : " . $letter["customerName"] . " [ " . $letter["unitTitle"] . " ]";
		$result = $dobj->Add($pdo);
		if (!$result) {
			$pdo->rollBack();
			echo "6:";
			print_r(ExceptionHandler::PopAllExceptions());
			echo Response::createObjectiveResponse(false, "");
			die();
		}
	}
	//------------- others ----------------
	foreach ($others as $othertitle => $otherAmount) {
		$dobj = new manage_acc_doc_items();
		$dobj->docID = $obj->docID;
		$dobj->locked = "1";
		$dobj->bdAmount = $otherAmount;
		$dobj->details = $othertitle;
		$result = $dobj->Add($pdo);
		if (!$result) {
			$pdo->rollBack();
			echo "7:";
			print_r(ExceptionHandler::PopAllExceptions());
			echo Response::createObjectiveResponse(false, "");
			die();
		}
	}
	//---------- sub or extra of cash -----------------
	$cashAmount = $cashAmount - $storeDocsSum - $shareBackSum - $subOfCash + $addToCash + $shareCreateSum + $InvalidChecksSum + $InvalidLettersSum;
	$dobj = new manage_acc_doc_items();
	$dobj->docID = $obj->docID;
	$dobj->locked = "1";
	
	$dobj->bdAmount = ($cashAmount > $billDT[0]["BillAmount"]) ? $cashAmount - $billDT[0]["BillAmount"]*1 : 0;
	$dobj->bsAmount = ($cashAmount < $billDT[0]["BillAmount"]) ? $billDT[0]["BillAmount"]*1 - $cashAmount : 0;
	$dobj->details = ($cashAmount > $billDT[0]["BillAmount"]) ? "مبلغ کسری صندوق" : "مبلغ اضافی صندوق";
	$result = $dobj->Add($pdo);
	if (!$result) {
		$pdo->rollBack();
		echo "9:";
		print_r(ExceptionHandler::PopAllExceptions());
		echo Response::createObjectiveResponse(false, "");
		die();
	}
	//---------  tax -----------------
	
	$obj2 = new manage_acc_docs();
	$obj2->regDate = PDONOW;
	$obj2->regPersonID = $_SESSION['USER']["personID"];
	$obj2->docDate = DateModules::shamsi_to_miladi($date);
	$obj2->cycleID = $_SESSION["CYCLE"];
	$obj2->description = "سند فروش روزانه - مالیات";
	$obj2->docType = "CASH";
	$result = $obj2->Add($pdo);

	if (!$result) {
		$pdo->rollBack();
		echo "8:";
		print_r(ExceptionHandler::PopAllExceptions());
		echo Response::createObjectiveResponse(false, "");
		die();
	}
	
	$dobj = new manage_acc_doc_items();
	$dobj->docID = $obj2->docID;
	$dobj->locked = "1";
	$dobj->bsAmount = $totalFactorTax;
	$dobj->details = "مالیات بر ارزش افزوده";
	$result = $dobj->Add($pdo);
	if (!$result) {
		$pdo->rollBack();
		echo "9:";
		print_r(ExceptionHandler::PopAllExceptions());
		echo Response::createObjectiveResponse(false, "");
		die();
	}
	//--------------------------------------------------------
		
	foreach($booths as $boothTitle => $boothAmount)
	{
		$dobj = new manage_acc_doc_items();
		$dobj->docID = $obj2->docID;
		$dobj->locked = "1";
		if($boothAmount > 0)
			$dobj->bsAmount = $boothAmount;
		else
			$dobj->bdAmount = -1 * $boothAmount;
		
		$dobj->details = $boothTitle;
		$result = $dobj->Add($pdo);
		if (!$result) {
			$pdo->rollBack();
			echo "15:";
			print_r(ExceptionHandler::PopAllExceptions());
			echo Response::createObjectiveResponse(false, "");
			die();
		}
	}

	//-------------- return factors rows --------------
		
	$dt_returnRows = PdoDataAccess::runquery("
		select sum(c.amount) chkAmount,l.*
		from sale_factors sf
		 left join factor_checks c on(sf.ref_factorID=c.factorID)
		 left join factor_letters l on(sf.ref_factorID=l.factorID)
		where sf.factor_type='RETURN' AND c.validity='INVALID' AND l.validity='INVALID' 
			AND substr(reg_date,1,10)=? ", array(DateModules::shamsi_to_miladi($date, "-")), $pdo);
	
	if (count($dt_returnRows) > 0) {
		
		if (($dt_returnRows[0]["chkAmount"] != "" && $dt_returnRows[0]["chkAmount"] != "0") || $dt_returnRows[0]["letterID"] != "") {
			$obj2 = new manage_acc_docs();
			$obj2->regDate = PDONOW;
			$obj2->regPersonID = $_SESSION['USER']["personID"];
			$obj2->docDate = DateModules::shamsi_to_miladi($date);
			$obj2->cycleID = $_SESSION["CYCLE"];
			$obj2->description = "سند فروش روزانه - بدهکاریهای باطل شده فاکتور های مرجوعی";
			$obj2->docType = "CASH";
			$result = $obj2->Add($pdo);

			if (!$result) {
				$pdo->rollBack();
				echo "10:";
				print_r(ExceptionHandler::PopAllExceptions());
				echo Response::createObjectiveResponse(false, "");
				die();
			}
		}
		
		if ($dt_returnRows[0]["chkAmount"] != "" && $dt_returnRows[0]["chkAmount"] != "0") {
			$dobj = new manage_acc_doc_items();
			$dobj->docID = $obj2->docID;
			$dobj->locked = "1";
			$dobj->bsAmount = $dt_returnRows[0]["chkAmount"];
			$dobj->details = "چک های باطل شده";
			$result = $dobj->Add($pdo);
			if (!$result) {
				$pdo->rollBack();
				echo "11:";
				print_r(ExceptionHandler::PopAllExceptions());
				echo Response::createObjectiveResponse(false, "");
				die();
			}
		}
		foreach ($dt_returnRows as $letter) {
			if ($letter["letterID"] == "")
				continue;
			$dobj = new manage_acc_doc_items();
			$dobj->docID = $obj2->docID;
			$dobj->locked = "1";
			$dobj->bsAmount = $letter["amount"];
			$dobj->details = "بدهکاری باطل شده از فاکتور برگشتی : " . $letter["customerName"] . " [ " . $letter["unitTitle"] . " ]";
			$result = $dobj->Add($pdo);
			if (!$result) {
				$pdo->rollBack();
				echo "12:";
				print_r(ExceptionHandler::PopAllExceptions());
				echo Response::createObjectiveResponse(false, "");
				die();
			}
		}
	}
	//------------ Daily Operations -------------------
	$dt_returnRows = PdoDataAccess::runquery("
		select d.* ,concat(p.fname,' ',p.lname) cashName
		from DailyOperation d 
			join persons p on(d.cashier=p.personID) 
		where DayDate=? ", 
			array(DateModules::shamsi_to_miladi($date, "-")), $pdo);
	if (count($dt_returnRows) > 0) {
		foreach ($dt_returnRows as $operation) {
			
			$dobj = new manage_acc_doc_items();
			$dobj->docID = $obj->docID;
			$dobj->locked = "1";
			switch ($operation["operation"])
			{
				case "SHAREBACK":
					$dobj->bdAmount = $operation["amount"];
					$dobj->details = "استرداد سهام" .	" توسط "    	. $operation["cashName"];
					break;
				case "EXTRA":
					$dobj->bdAmount = $operation["amount"];
					$dobj->details = "اضافی موجودی" .	" توسط "    	. $operation["cashName"];
					break;
				case "SUB":
					$dobj->bsAmount = $operation["amount"];
					$dobj->details = "کسری موجودی" .	" توسط "    	. $operation["cashName"];
					break;
				case "SHARECREATE":
					$dobj->bsAmount = $operation["amount"];
					$dobj->details = "افتتاح سهام" .	" توسط "    	. $operation["cashName"];
					break;				
			}
			$dobj->tafsiliID = $operation["TafsiliID"];
			$result = $dobj->Add($pdo);
			if (!$result) {
				$pdo->rollBack();
				echo "13:";
				print_r(ExceptionHandler::PopAllExceptions());
				echo Response::createObjectiveResponse(false, "");
				die();
			}
		}
	}
	//------------ set payConfirm = 1 -----------------
	$query = "update sale_factors set payConfirm=1
		where substr(reg_date,1,10)=? ";
	$whereParam = array(DateModules::shamsi_to_miladi($date, "-"));
	PdoDataAccess::runquery($query, $whereParam);
	if (ExceptionHandler::GetExceptionCount() != 0) {
		$pdo->rollBack();
		echo "14:";
		print_r(ExceptionHandler::PopAllExceptions());
		echo Response::createObjectiveResponse(false, "");
		die();
	}

	$pdo->commit();
	echo Response::createObjectiveResponse(true, $obj->docID);
	die();
}

function StoreDocRegister(){
	
	$date = DateModules::shamsi_to_miladi($_POST["date"]);
	
	//--------------------- check register in date before ----------------------
	
	$docs = PdoDataAccess::runquery("select * from acc_docs 
		where cycleID=? AND docDate=? AND docType='SUMMARY'", array($_SESSION["CYCLE"], $date));
	if(count($docs) > 0)
	{
		echo Response::createObjectiveResponse(false, "خطا : سند مربوط به این تاریخ قبلا صادر شده است");
		die();
	}
	
	//--------------------------------------------------------------------------
	
	$docs = PdoDataAccess::runquery("select * from acc_docs 
		where cycleID=? AND docDate=? AND docType='BUY'", array($_SESSION["CYCLE"], $date));
	if(count($docs) == 0)
	{
		echo Response::createObjectiveResponse(false, "خطا : در این تاریخ سند فاکتور خرید وجود ندارد");
		die();
	}
	
	$pdo = PdoDataAccess::getPdoObject();
	/* @var $pdo PDO */
	$pdo->beginTransaction();
	//---------------- account header doc --------------------
	$obj = new manage_acc_docs();
	$obj->cycleID = $_SESSION["CYCLE"];
	$obj->docID = $_POST["DocID"];
	$obj->regPersonID = $_SESSION['USER']["personID"];
	$obj->docDate = $date;
	$obj->docType = "SUMMARY";
	$obj->docStatus = "CONFIRM";
	$obj->description = "سند قطعی از فاکتورهای خرید مربوط به تاریخ [ " . $_POST["date"] . " ]";
	$obj->regDate = DateModules::Now();
		
	$result = $obj->Add($pdo);

	if (!$result) {
		$pdo->rollBack();
		print_r(ExceptionHandler::PopAllExceptions());
		echo Response::createObjectiveResponse(false, "خطا در ایجاد سند پایه");
		die();
	}
	
	//$index = 0;
	for($i=0; $i < count($docs); $i++)
	{
		if($docs[$i]["docStatus"] != 'CONFIRM')
		{
			$pdo->rollBack();
			echo Response::createObjectiveResponse(false, "خطا : سند شماره " . $docs[$i]["docID"] . " تایید نشده است.");
			die();
		}
		
		/*PdoDataAccess::runquery("insert into acc_doc_items select " . $obj->docID . ",rowID + $index,
				kolID,moinID,tafsiliID,tafsili2ID,bdAmount,bsAmount,details,1
			from acc_doc_items where docID=" . $docs[$i]["docID"] . "
				AND (bdAmount>0 OR bsAmount>0)", array(), $pdo);
		if(ExceptionHandler::GetExceptionCount() > 0)
		{
			$pdo->rollBack();
			print_r(ExceptionHandler::PopAllExceptions());
			echo Response::createObjectiveResponse(false, "خطا در اضافه ردیف های سند");
			die();
		}
		
		$index = PdoDataAccess::GetLastID("acc_doc_items", "rowID", "docID=?", array($obj->docID), $pdo);
		*/
		
		PdoDataAccess::runquery("update acc_docs set ref_docID=" . $obj->docID . " where docID=" . $docs[$i]["docID"], array(), $pdo);
		if(ExceptionHandler::GetExceptionCount() > 0)
		{
			$pdo->rollBack();
			echo Response::createObjectiveResponse(false, "خطا در تنظیم سند عطف");
			die();
		}
	}
	
	$pdo->commit();
	echo Response::createObjectiveResponse(true, "");
	die();
}

//............................

function checkAccDocExistance() {
	$date = $_POST["dayDate"];

	$temp = PdoDataAccess::runquery("select * from acc_docs
		where docDate=? AND docType='CASH'", array(DateModules::shamsi_to_miladi($date, "-")));

	echo Response::createObjectiveResponse((count($temp) == 0 ? true : false), "");
	die();
}

function shareBack() {
	/*$pdo = PdoDataAccess::getPdoObject();
	$pdo->beginTransaction();
	//---------------- account header doc --------------------
	$obj = new manage_acc_docs();
	$obj->regDate = PDONOW;
	$obj->regPersonID = $_SESSION['USER']["personID"];
	$obj->docDate = PDONOW;
	$obj->cycleID = $_SESSION["CYCLE"];
	$obj->description = "استرداد سهام";
	$obj->docType = "SHAREBACK";
	$result = $obj->Add($pdo);

	if (!$result) {
		$pdo->rollBack();
		print_r(ExceptionHandler::PopAllExceptions());
		echo Response::createObjectiveResponse(false, "");
		die();
	}

	//---------  tax -----------------
	$dobj = new manage_acc_doc_items();
	$dobj->docID = $obj->docID;
	$dobj->locked = "1";
	$dobj->bdAmount = $_POST["remainder"];
	$dobj->tafsiliID = $_POST["TafsiliID"];
	$dobj->details = "استرداد سهام";
	$result = $dobj->Add($pdo);
	if (!$result) {
		$pdo->rollBack();
		print_r(ExceptionHandler::PopAllExceptions());
		echo Response::createObjectiveResponse(false, "");
		die();
	}

	$pdo->commit();
	echo Response::createObjectiveResponse(true, "");
	die();*/
	
	PdoDataAccess::runquery("update acc_tafsilis set IsActive=0 where tafsiliID=?", array($_POST["TafsiliID"]));
	
	$result = AddDailyOperation($_POST["DayDate"], "SHAREBACK", $_POST["remainder"], $_POST["TafsiliID"]);
	echo Response::createObjectiveResponse($result, "");
	die();
}

function ExtraSubDoc() {
	/*$pdo = PdoDataAccess::getPdoObject();
	$pdo->beginTransaction();
	//---------------- account header doc --------------------
	$obj = new manage_acc_docs();
	$obj->regDate = PDONOW;
	$obj->regPersonID = $_SESSION['USER']["personID"];
	$obj->docDate = PDONOW;
	$obj->cycleID = $_SESSION["CYCLE"];
	$obj->description = "کسری / اضافی سهامدار";
	$obj->docType = "EXTRASUB";
	$obj->CashPay = isset($_POST["CashPay"]) ? "0" : "1";
	
	$result = $obj->Add($pdo);

	if (!$result) {
		$pdo->rollBack();
		print_r(ExceptionHandler::PopAllExceptions());
		echo Response::createObjectiveResponse(false, "");
		die();
	}

	//---------  tax -----------------
	$dobj = new manage_acc_doc_items();
	$dobj->docID = $obj->docID;
	$dobj->locked = "1";
	if ($_POST["amount_type"] == "1") {
		$dobj->bdAmount = $_POST["amount"];
		$dobj->details = "اضافی موجودی";
	} else {
		$dobj->bsAmount = $_POST["amount"];
		$dobj->details = "کسری موجودی";
	}
	$dobj->tafsiliID = $_POST["tafsiliID"];
	$result = $dobj->Add($pdo);
	if (!$result) {
		$pdo->rollBack();
		print_r(ExceptionHandler::PopAllExceptions());
		echo Response::createObjectiveResponse(false, "");
		die();
	}

	$pdo->commit();
	*/
	
	$dt = PdoDataAccess::runquery("SELECT * from DailyDescription where IsConfirmed=1 AND DayDate=?",
			array(DateModules::shamsi_to_miladi($_POST["DayDate"], "-")));
	if(count($dt) > 0)
	{
		echo Response::createObjectiveResponse(false, "ConfirmError");
		die();
	}
	
	
	$op = ($_POST["amount_type"] == "1") ? "EXTRA" : "SUB";
	$result = AddDailyOperation($_POST["DayDate"], $op, $_POST["amount"], $_POST["tafsiliID"], isset($_POST["CashPay"]) ? "0" : "1");
	if(!$result)
	{
		echo Response::createObjectiveResponse(false, "");
		die();
	}
	$rowNo = PdoDataAccess::InsertID();
	//----------- register sale factor -------------
	require_once '../../sale/class/sale_factors.class.php';
	require_once '../class/tafsilis.class.php';
	
	$otafObj = new manage_tafsilis($_POST["tafsiliID"]);
	
	if(isset($_POST["CashPay"]))
	{
		$obj = new manage_sale_factors();
		$obj->cycleID = $_SESSION["CYCLE"];
		$obj->cashier = $_SESSION['USER']["personID"];
		$obj->reg_date = DateModules::shamsi_to_miladi($_POST["DayDate"],"-");
		$obj->factor_type = "OTHER";
		$obj->storeID = "3";
		$obj->factor_status = "CLOSE";
		$obj->customerName = "تفصیلی " . $_POST["tafsiliID"] . " [" . $otafObj->tafsiliTitle . "] بابت کسری پرداختی";
		$obj->factorPrice = $_POST["amount"];
		$obj->Add();
	}
	echo Response::createObjectiveResponse(true, $rowNo);
	die();	
}

function shareCreateDoc() {
	
	require_once '../../sale/class/shareholders.class.php';
	
	require_once '../class/tafsilis.class.php';
	$tafObj = new manage_tafsilis();
	$tafObj->tafsiliTitle = $_POST["tafsiliName"];
	$tafObj->description = "ثبت سهامدار جدید توسط صندوق";
	$tafObj->TafsiliType = 'shareholder';
	$tafObj->Add();
		
	$shObj = new manage_shareholders();
	$shObj->fullname = $_POST["tafsiliName"];
	$shObj->reg_date = DateModules::shamsi_to_miladi($_POST["DayDate"],"-");
	$shObj->sex = "2";
	$shObj->TafsiliID = $tafObj->tafsiliID;
	$shObj->HrmsPersonID = "0";
	$shObj->Add();
	
	/*$pdo = PdoDataAccess::getPdoObject();
	$pdo->beginTransaction();
	//---------------- account header doc --------------------
	$obj = new manage_acc_docs();
	$obj->regDate = PDONOW;
	$obj->regPersonID = $_SESSION['USER']["personID"];
	$obj->docDate = PDONOW;
	$obj->cycleID = $_SESSION["CYCLE"];
	$obj->description = "افتتاح سهام";
	$obj->docType = "SHARECREATE";
	$obj->CashPay = isset($_POST["CashPay"]) ? "0" : "1";
	
	$result = $obj->Add($pdo);

	if (!$result) {
		$pdo->rollBack();
		print_r(ExceptionHandler::PopAllExceptions());
		echo Response::createObjectiveResponse(false, "");
		die();
	}

	//---------  tax -----------------
	$dobj = new manage_acc_doc_items();
	$dobj->docID = $obj->docID;
	$dobj->locked = "1";
	$dobj->bsAmount = $_POST["amount"];
	$dobj->details = "افتتاح سهام";
	$dobj->tafsiliID = $tafObj->tafsiliID;
	$result = $dobj->Add($pdo);
	if (!$result) {
		$pdo->rollBack();
		print_r(ExceptionHandler::PopAllExceptions());
		echo Response::createObjectiveResponse(false, "");
		die();
	}

	$pdo->commit();
	*/
	
	$result = AddDailyOperation($_POST["DayDate"], "SHARECREATE", $_POST["amount"], $tafObj->tafsiliID, isset($_POST["CashPay"]) ? "0" : "1");
	if(!$result)
	{
		echo Response::createObjectiveResponse(false, "");
		die();
	}
	//----------- register sale factor -------------
	require_once '../../sale/class/sale_factors.class.php';
	
	if(isset($_POST["CashPay"]))
	{
		$obj = new manage_sale_factors();
		$obj->cycleID = $_SESSION["CYCLE"];
		$obj->cashier = $_SESSION['USER']["personID"];
		$obj->reg_date = PDONOW;
		$obj->factor_type = "OTHER";
		$obj->storeID = "3";
		$obj->personID = $shObj->personID;
		$obj->factor_status = "CLOSE";
		$obj->customerName = "تفصیلی " . $tafObj->tafsiliID . " [ " . $tafObj->tafsiliTitle . " ] بابت افتتاح حساب";
		$obj->factorPrice = $_POST["amount"];
		$obj->Add();
	}
	echo Response::createObjectiveResponse(true, "");
	die();
}

function openDoc() {
	PdoDataAccess::runquery("update acc_docs set docStatus='RAW' where docID=" . $_POST["docID"]);
	PdoDataAccess::AUDIT("acc_docs","باز کردن سند", $_POST["docID"]);
	echo "true";
	die();
}

function UpdateChecks(){
	
	$accountID = $_POST["accountID"];
	$result = "";
	
	require_once("phpExcelReader.php");
	
	$data = new Spreadsheet_Excel_Reader();
    $data->setOutputEncoding('utf-8');
    $data->setRowColOffset(0);
	$data->read($_FILES["attach"]["tmp_name"]);
	
	for ($i = 0; $i < $data->sheets[0]['numRows']; $i++) 
	{
		
		$checkNo = "";		
		switch($accountID)
		{
			case "1": // ملی
				if(trim($data->sheets[0]['cells'][$i][3]) == "چك")
				{
					$checkNo = $data->sheets[0]['cells'][$i][4];
					$checkNo = substr($checkNo, strlen($checkNo) - 6);
				}
				break;
			case "5": //پاسارگاد
				if(strpos($data->sheets[0]['cells'][$i][8], "وصول چک") !== false)
				{
					$checkNo = $data->sheets[0]['cells'][$i][7];
					$checkNo = substr($checkNo, strlen($checkNo) - 6);
				}
		}
		if($checkNo == "")
			continue;
	
		//............... add debtor rows to doc ...........................
		if($_POST["DocID"] != "")
		{
			$dt = PdoDataAccess::runquery("select * from acc_checks 
				where checkStatus in(1,2) AND accountID=? AND checkNo=?", array($accountID,$checkNo));
			if(count($dt) > 0)
			{
				$obj = new manage_acc_doc_items();
				$obj->docID = $_POST["DocID"];
				$obj->kolID = 42; // اسناد پرداختنی
				$obj->moinID = 1; // کوتاه مدت
				$obj->tafsiliID = $dt[0]["tafsiliID"];	
				$obj->bdAmount = $dt[0]["amount"];
				$obj->bsAmount = 0;
				$obj->Add();
			}
		}		
		//..................................................................

		PdoDataAccess::runquery("update acc_checks set checkStatus=3 where accountID=? AND checkNo=?", array($accountID,$checkNo));
		if(PdoDataAccess::AffectedRows() > 0)
			$result .= "شماره چک : " . $checkNo . " [ تعداد ردیف به روز شده : " . PdoDataAccess::AffectedRows() . "]<br>";
			
		
	}
	echo Response::createObjectiveResponse(true, $result == "" ? "هیچ چکی به روز نگردید" : $result);
	die();

	
	
	
	
	
	
	while (($row = fgetcsv($handle)) !== false) {
		
		$row[3] = iconv(mb_detect_encoding($row[3], mb_detect_order(), true), "UTF-8", $row[3]);
		echo $row[3] . "-------";
		if(trim($row[3]) == "چك")
		{
			echo $row[4] . "*********";
			$checkNo = substr($row[4], 4);
			PdoDataAccess::runquery("update acc_checks set checkStatus=2 where accountID=? AND checkNo=?", array($accountID,(int)$checkNo));

			if(PdoDataAccess::AffectedRows() > 0)
				$result .= "شماره چک : " . $checkNo . " [ تعداد ردیف به روز شده : " . PdoDataAccess::AffectedRows() . "]<br>";
		}
	}
	fclose($handle);
	echo Response::createObjectiveResponse(true, $result == "" ? "هیچ چکی به روز نگردید" : $result);
	die();
}

//.........................

function AddDailyOperation($DayDate,$Operation,$amount,$TafsiliID, $cashPay = "1")
{
	$query = "insert into DailyOperation(DayDate,Operation,amount,TafsiliID,CashPay,cashier)
		values(?,?,?,?,?," . $_SESSION['USER']["personID"] . ")";
	
	PdoDataAccess::runquery($query, array(DateModules::shamsi_to_miladi($DayDate,"-"), $Operation, $amount, $TafsiliID, $cashPay));
	return PdoDataAccess::AffectedRows() != 0 ? true : false;
}

//..........................

function sharesCompute(){
	
	$pdo = PdoDataAccess::getPdoObject();
	$pdo->beginTransaction();
	
	$query = "CREATE temporary table tmp_shares as 
		SELECT t.tafsiliID, 
			case when reg_date is not null and substring(g2j(reg_date)*1, 1, 4) >= substring(g2j(now()), 1, 4) then
					0
				when reg_date is not null and substring(g2j(reg_date)*1+1, 1, 4) >= substring(g2j(now()), 1, 4) then
					sum(ifnull(bsAmount,0)-ifnull(bdAmount,0))/2
				else
					sum(ifnull(bsAmount,0)-ifnull(bdAmount,0))
				end amount

		FROM acc_doc_items
		join acc_tafsilis t using(tafsiliID)
		join shareholders sh on(sh.tafsiliID = t.tafsiliID)

		where kolID=60 AND moinID=1 AND t.tafsiliID is not null

		group by t.tafsiliID";
	PdoDataAccess::runquery($query);
	
	//......................................................
	
	$dt = PdoDataAccess::runquery("select sum(amount) from tmp_shares");
	$totalShares = $dt[0][0];
	$totalProfit = str_replace( ",", "", $_POST["totalProfit"])*1;
	$partAmount = str_replace( ",", "", $_POST["partAmount"])*1;
	//......................................................
	$dobj = new manage_acc_doc_items();
	$dobj->docID = $_POST["docID"];
	$dobj->bdAmount = $totalProfit;
	$dobj->bsAmount = 0;
	$dobj->locked = "1";
	$dobj->details = "تقسیسم سود سهام";
	$result = $dobj->Add($pdo);
	if (!$result) {
		$pdo->rollBack();
		print_r(ExceptionHandler::PopAllExceptions());
		echo Response::createObjectiveResponse(false, "");
		die();
	}
	
	$hobj = new manage_acc_docs();
	$hobj->docID = $_POST["docID"];
	$hobj->docType = "SHARE";
	$hobj->Edit();
	//......................................................
	$tatoalParts = $totalShares / $partAmount;
	$eachShareAmount = $totalProfit / $tatoalParts;
	
	$allParts = PdoDataAccess::runquery("select * from tmp_shares");
	foreach ($allParts as $personPart)
	{
		$part = $personPart["amount"]*1 / $partAmount;
		$amount = round($part * $eachShareAmount);
		
		$dobj = new manage_acc_doc_items();
		$dobj->docID = $_POST["docID"];
		$dobj->kolID = 60;
		$dobj->moinID = 1;
		$dobj->tafsiliID = $personPart["tafsiliID"];
		$dobj->bdAmount = 0;
		$dobj->bsAmount = $amount;
		$dobj->locked = "1";
		$dobj->details = "تقسیسم سود سهام";
		$result = $dobj->Add($pdo);
		if (!$result) {
			$pdo->rollBack();
			print_r(ExceptionHandler::PopAllExceptions());
			echo Response::createObjectiveResponse(false, "");
			die();
		}
	}
	
	$pdo->commit();
	echo Response::createObjectiveResponse(true, "");
	die();
}

function RegisterEndDoc(){
	
	$DocID = $_POST["DocID"];
	$dt = PdoDataAccess::runquery("select * from acc_docs where DocID=?" , array($DocID));
	if(count($dt) > 0)
	{
		echo Response::createObjectiveResponse(false, "شماره برگه وارد شده موجود می باشد");
		die();
	}
	
	$pdo = PdoDataAccess::getPdoObject();
	/* @var $pdo PDO */
	$pdo->beginTransaction();
	//---------------- account header doc --------------------
	$obj = new manage_acc_docs();
	$obj->docID = $DocID;
	$obj->regDate = PDONOW;
	$obj->regPersonID = $_SESSION['USER']["personID"];
	$obj->docDate = DateModules::shamsi_to_miladi($date);
	$obj->cycleID = $_SESSION["CYCLE"];
	$obj->description = "سند اختتامیه";
	$obj->docType = "ENDCYCLE";
	$result = $obj->Add($pdo);

	if (!$result) {
		$pdo->rollBack();
		print_r(ExceptionHandler::PopAllExceptions());
		echo Response::createObjectiveResponse(false, "");
		die();
	}
	
	PdoDataAccess::runquery("
		insert into acc_doc_items
		SELECT $DocID,@i:=@i+1,t1.* FROM
		(

		select KolID,moinID,tafsiliID,Tafsili2ID,
			if( sum(bsAmount-bdAmount)>0, sum(bsAmount-bdAmount), 0 ),
			if( sum(bdAmount-bsAmount)>0, sum(bdAmount-bsAmount), 0 ),
			'',1
		from acc_doc_items i
		join acc_docs using(DocID)
		where cycleID=" . $_SESSION["CYCLE"] . "
		group by KolID,moinID,tafsiliID,Tafsili2ID
	
	)t1
	,(select @i:=0)t2;", array(), $pdo);
	
	if(ExceptionHandler::GetExceptionCount() > 0)
	{
		$pdo->rollBack();
		print_r(ExceptionHandler::PopAllExceptions());
		echo Response::createObjectiveResponse(false, "");
		die();
	}
	
	$pdo->commit();
	echo Response::createObjectiveResponse(true, "");
	die();
}

function RegisterStartDoc(){
	
	$DocID = $_POST["DocID"];
	$dt = PdoDataAccess::runquery("select * from acc_docs where DocID=?" , array($DocID));
	if(count($dt) > 0)
	{
		echo Response::createObjectiveResponse(false, "شماره برگه وارد شده موجود می باشد");
		die();
	}
	
	$pdo = PdoDataAccess::getPdoObject();
	/* @var $pdo PDO */
	$pdo->beginTransaction();
	//---------------- account header doc --------------------
	$obj = new manage_acc_docs();
	$obj->docID = $DocID;
	$obj->regDate = PDONOW;
	$obj->regPersonID = $_SESSION['USER']["personID"];
	$obj->docDate = DateModules::shamsi_to_miladi($date);
	$obj->cycleID = $_SESSION["CYCLE"];
	$obj->description = "سند افتتاحیه";
	$obj->docType = "STARTCYCLE";
	$result = $obj->Add($pdo);

	if (!$result) {
		$pdo->rollBack();
		print_r(ExceptionHandler::PopAllExceptions());
		echo Response::createObjectiveResponse(false, "");
		die();
	}
	
	PdoDataAccess::runquery("
		insert into acc_doc_items
		SELECT $DocID,@i:=@i+1,t1.* FROM
		(

		select KolID,moinID,tafsiliID,Tafsili2ID,
			if( sum(bdAmount-bsAmount)>0, sum(bdAmount-bsAmount), 0 ),
			if( sum(bsAmount-bdAmount)>0, sum(bsAmount-bdAmount), 0 ),
			'',1
		from acc_doc_items i
		join acc_docs using(DocID)
		where cycleID=" . ($_SESSION["CYCLE"]-1) . " AND docType<>'ENDCYCLE'
		group by KolID,moinID,tafsiliID,Tafsili2ID
	
	)t1
	,(select @i:=0)t2;", array(), $pdo);
	
	if(ExceptionHandler::GetExceptionCount() > 0)
	{
		$pdo->rollBack();
		print_r(ExceptionHandler::PopAllExceptions());
		echo Response::createObjectiveResponse(false, "");
		die();
	}
	
	$pdo->commit();
	echo Response::createObjectiveResponse(true, "");
	die();
}
?>