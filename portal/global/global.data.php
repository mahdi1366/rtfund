<?php
//---------------------------
// programmer:	Jafarkhani
// create Date:	94.06
//---------------------------
require_once '../header.inc.php';
require_once getenv("DOCUMENT_ROOT") . '/framework/person/persons.class.php';
require_once inc_response;
require_once inc_dataReader;

$task = isset($_POST["task"]) ? $_POST["task"] : (isset($_GET["task"]) ? $_GET["task"] : "");

switch ($task)
{
	case "SelectPersonInfo":
		SelectPersonInfo();
		
	case "SavePersonalInfo":
		SavePersonalInfo();
		
	case "AccDocFlow":
		AccDocFlow();
}

function SelectPersonInfo(){
	
	$temp = BSC_persons::SelectAll("PersonID=?", array($_SESSION["USER"]["PersonID"]));
	$temp = PdoDataAccess::fetchAll($temp, 0, 1);
	echo dataReader::getJsonData($temp, 1, $_GET["callback"]);
	die();
}

function SavePersonalInfo(){
	
	$obj = new BSC_persons();
	$obj->PersonID = $_SESSION["USER"]["PersonID"];
	PdoDataAccess::FillObjectByArray($obj, $_POST);
	
	$result = $obj->EditPerson();
	echo Response::createObjectiveResponse($result, "");
	die();
}

function AccDocFlow(){
	
	$CostID = $_REQUEST["CostID"];
	$CurYear = substr(DateModules::shNow(),0,4);

	$temp = PdoDataAccess::runquery("
		select 
			d.LocalNo,
			d.DocDate,
			d.description,
			di.DebtorAmount,
			di.CreditorAmount,
			di.details
		from ACC_DocItems di join ACC_docs d using(DocID)
		left join ACC_tafsilis t1 on(t1.TafsiliType=1 AND di.TafsiliID=t1.TafsiliID)
		left join ACC_tafsilis t2 on(t2.TafsiliType=1 AND di.TafsiliID2=t2.TafsiliID)
		where CostID=:cid AND (t1.ObjectID=:pid or t2.ObjectID=:pid)
			/*AND DocStatus in('CONFIRM','ARCHIVE')*/
		order by DocDate
	", array(/*":year" => $CurYear, */":pid" => $_SESSION["USER"]["PersonID"], ":cid" => $CostID));
	
	$PreSum = 0;
	for($i=0; $i < $_GET["start"]*1 + $_GET["limit"]*1; $i++)
	{
		$PreSum += $temp[$i]["CreditorAmount"] - $temp[$i]["DebtorAmount"];
		$temp[$i]["Remainder"] = $PreSum;		
	}
	
	$temp = array_slice($temp, $_GET["start"], $_GET["limit"]);
	
	echo dataReader::getJsonData($temp, count($temp), $_GET["callback"]);
	die();	
}



?>
