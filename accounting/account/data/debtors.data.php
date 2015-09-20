<?php

//-----------------------------
//	Programmer	: SH.Jafarkhani
//	Date		: 90.10
//-----------------------------

ini_set("display_errors", "On");

require_once '../../header.inc.php';
require_once '../class/acc_docs.class.php';

$task = isset($_POST ["task"]) ? $_POST ["task"] : (isset($_GET ["task"]) ? $_GET ["task"] : "");

switch ($task) {
	case "GetSalaryExcel":
		GetSalaryExcel();
		
	case "RegisterDebtorsDoc":
		RegisterDebtorsDoc();
		
	case "ExcelRegisterDebtorsDoc":
		ExcelRegisterDebtorsDoc();
}

function GetSalaryExcel() {
	
	$month = $_REQUEST["month"];
		
	$query = "
		SELECT fl.factorID, sh.fullname,p.staff_id,if(fromDate>= j2g(1392,$month,1), 1,2) loadType,fl.amount,fromDate,toDate
			
		FROM factor_letters fl
		join sale_factors using(factorID)
		left join shareholders sh using(personID)
		left join PersonInfo p on(sh.HrmsPersonID=p.PersonID)
		
		where fromDate<=j2g(" . $_SESSION["YEAR"] . ",$month,31) AND  toDate>j2g(" . $_SESSION["YEAR"] . ",$month,1)";
	$data = PdoDataAccess::runquery($query);
	//print_r($data); die();
	
	$worksheet = "";
	require_once 'excel.php';
	require_once "php_writeexcel-0.3.0/class.writeexcel_workbook.inc.php";
	require_once "php_writeexcel-0.3.0/class.writeexcel_worksheet.inc.php";
	$tempAddress = "/tmp/temp.xls"; //"d:/webserver/temp/temp.xls"
	$workbook = &new writeexcel_workbook($tempAddress);
	$worksheet = & $workbook->addworksheet("Sheet1");
	
	$worksheet->write(0, 0, "کد پرسنلی");
	$worksheet->write(0, 1, "نام و نام خانوادگی");
	$worksheet->write(0, 2, "کد قلم");
	$worksheet->write(0, 3, "وام");
	$worksheet->write(0, 4, "ثبت یا گردش");
	$worksheet->write(0, 5, "کد بانک");
	$worksheet->write(0, 6, "مبلغ وام");
	$worksheet->write(0, 7, "مبلغ قسط");
	$worksheet->write(0, 8, "تاریخ شروع");
	$worksheet->write(0, 9, "تاریخ پایان");
	
	for($rowIndex=1,$i=0; $i < count($data); $i++)
	{
		$row = $data[$i];
				
		$month = (int)substr($row["toDate"], 5, 2) - (int)substr($row["fromDate"], 5, 2) + 1;
		$month = ($month <= 0) ? 12 + $month : $month;
		$amount = round((int)$row["amount"]/$month);
		
		if($row["loadType"] == "2")
		{
			if($row["amount"] - $month*$amount == 0)
				continue;
			else
				$amount += $row["amount"] - $month*$amount;
		}	
		
		if($row["staff_id"] == "")
		{
			$worksheet->write($rowIndex, 0, "برای نامه مربوط به فاکتور شماره " . $row["factorID"] . " کد پرسنلی یافت نشد");
			$rowIndex++;
			continue;
		}
		
		$worksheet->write($rowIndex, 0, $row["staff_id"]);
		$worksheet->write($rowIndex, 1, $row["fullname"]);
		$worksheet->write($rowIndex, 2, ""); // کد قلم
		$worksheet->write($rowIndex, 3, 1); // وام
		$worksheet->write($rowIndex, 4, $row["loadType"]); // ثبت یا گردش
		$worksheet->write($rowIndex, 5, ""); // کد بانک
		$worksheet->write($rowIndex, 6, $row["amount"]); // مبلغ وام
		$worksheet->write($rowIndex, 7, $amount);
		
		if($row["loadType"] == "1")
		{
			$worksheet->write($rowIndex, 8, DateModules::miladi_to_shamsi($row["fromDate"]));
			$worksheet->write($rowIndex, 9, DateModules::miladi_to_shamsi($row["toDate"]));
		}
		
		$rowIndex++;
	}

	$workbook->close();

	header("Content-type: application/ms-excel");
	header("Content-disposition: inline; filename=excel.xls");
	echo file_get_contents($tempAddress);
	unlink($tempAddress);
	die();
}

function RegisterDebtorsDoc(){
	
	$month = $_POST["month"];
	$dt = manage_acc_docs::GetAll("sd.DocTypeInfo=?", array($month));
	if(count($dt) > 0)
	{
		echo Response::createObjectiveResponse(false, "RegBefore");
		die();
	}
	
	$pdo = PdoDataAccess::getPdoObject();
	/* @var $pdo PDO */
	$pdo->beginTransaction();
	
	
	//---------------- account header doc --------------------
	$obj = new manage_acc_docs();
	
	if(!empty($_REQUEST["DocID"]))
	{
		$obj->docID = $_REQUEST["DocID"];
		$dt = manage_acc_docs::GetAll("sd.DocID=?", array($obj->docID));
	}

	if(count($dt) == 0 || empty($_REQUEST["DocID"]))
	{
		$obj->regDate = PDONOW;
		$obj->regPersonID = $_SESSION['USER']["personID"];
		$obj->docDate = DateModules::Now();
		$obj->cycleID = $_SESSION["CYCLE"];
		$obj->docType = "DEBTORS";
		$obj->description = "سند بدهکاران مربوط به ماه " . DateModules::GetMonthName($month);
		$obj->DocTypeInfo = $month;
		
		$result = $obj->Add($pdo);

		if (!$result) {
			$pdo->rollBack();
			print_r(ExceptionHandler::PopAllExceptions());
			echo Response::createObjectiveResponse(false, "");
			die();
		}
	}
	//---------------- add items ----------------------------
	$date = substr(DateModules::shNow(),0,4) . "/" . ($month*1 < 10 ? "0" : "") . $month;
	$query = "
		SELECT fl.factorID,sh.TafsiliID,fl.amount,fromDate,toDate, if(substring(g2j(fromDate),1,7)='$date', 1, 0) lastMonth
			
		FROM factor_letters fl
		join sale_factors using(factorID)
		left join shareholders sh using(personID)
		
		where substring(g2j(fromDate),1,7)<='$date' AND  substring(g2j(fromDate),1,7)>='$date'";
	$data = PdoDataAccess::runquery($query);
	
	for($i=0; $i < count($data); $i++)
	{
		$row = $data[$i];
		
		$month = (int)substr($row["toDate"], 5, 2) - (int)substr($row["fromDate"], 5, 2) + 1;
		$month = ($month < 0) ? 12 + $month : $month;
		$amount = round((int)$row["amount"]/$month);
		
		if($row["lastMonth"] == "1")
			$amount += $row["amount"] - $month*$amount;
		
		$dobj = new manage_acc_doc_items();
		$dobj->docID = $obj->docID;
		$dobj->bsAmount = $amount;
		$dobj->tafsiliID = $row["TafsiliID"];
		$dobj->locked = "1";
		$dobj->details = "قسط مربوط به نامه مربوط به فاکتور شماره " . $row["factorID"];
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

function ExcelRegisterDebtorsDoc(){
	
	$month = $_POST["month"];
		
	$pdo = PdoDataAccess::getPdoObject();
	/* @var $pdo PDO */
	$pdo->beginTransaction();
	
	//---------------- account header doc --------------------
	$obj = new manage_acc_docs();
	
	if(!empty($_REQUEST["DocID"]))
	{
		$obj->docID = $_REQUEST["DocID"];
		$dt = manage_acc_docs::GetAll("sd.DocID=?", array($obj->docID));
	}

	if(count($dt) == 0 || empty($_REQUEST["DocID"]))
	{
		$obj->regDate = PDONOW;
		$obj->regPersonID = $_SESSION['USER']["personID"];
		$obj->docDate = DateModules::Now();
		$obj->cycleID = $_SESSION["CYCLE"];
		$obj->docType = "DEBTORS";
		$obj->description = "سند بدهکاران مربوط به ماه " . DateModules::GetMonthName($month);
		$obj->DocTypeInfo = $month;
		
		$result = $obj->Add($pdo);

		if (!$result) {
			$pdo->rollBack();
			print_r(ExceptionHandler::PopAllExceptions());
			echo Response::createObjectiveResponse(false, "");
			die();
		}
	}
	//---------------- add items ----------------------------
	require_once "phpExcelReader.php";
	
	$data = new Spreadsheet_Excel_Reader();
	$data->setOutputEncoding('utf-8');
	$data->setRowColOffset(0);
	$data->read($_FILES["attach"]["tmp_name"]);

	$errors = array();
	for ($i = 0; $i < $data->sheets[0]['numRows']; $i++) 
	{	
		$row = $data->sheets[0]['cells'][$i];
		
		$dt = PdoDataAccess::runquery("
			SELECT TafsiliID 
			FROM shareholders
			join PersonInfo p on(p.PersonID=HrmsPersonID)
			where TafsiliID>0 AND p.staff_id=?", array($row[1]));
		if(count($dt) == 0)
		{
			if($row[2] != "" && $row[3] != "")
				$errors[] = "ردیف " . ($i+1) . " مربوط به " . $row[2] . " " . $row[3] . " صادر نشد ." ;
			continue;
		}
		
		$dobj = new manage_acc_doc_items();
		$dobj->docID = $obj->docID;
		$dobj->bsAmount = $row[5];
		$dobj->tafsiliID = $dt[0][0];
		$dobj->locked = "1";
		
		$result = $dobj->Add($pdo);
		if (!$result) {
			$pdo->rollBack();
			print_r(ExceptionHandler::PopAllExceptions());
			echo Response::createObjectiveResponse(false, "");
			die();
		}		
	}
	
	$pdo->commit();
	echo Response::createObjectiveResponse(true, json_encode($errors));
	die();
}


?>