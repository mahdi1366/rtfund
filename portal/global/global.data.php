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
		
	case "changePass":
		changePass();
		
	case "DepositeFlow":
		DepositeFlow();
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

function changePass(){
	
	$dt = PdoDataAccess::runquery("select * from BSC_persons where PersonID=:p",
			array(":p" => $_SESSION['USER']["PersonID"]));
	if(count($dt) == 0)
	{ 		
		echo Response::createObjectiveResponse(false, "");
		die();
	}
	
	require_once getenv("DOCUMENT_ROOT") . '/framework/PasswordHash.php';
	$hash_cost_log2 = 8;	
	$hasher = new PasswordHash($hash_cost_log2, true);
	if (!$hasher->CheckPassword($_POST["cur_pass"], $dt[0]["UserPass"])) {

		echo Response::createObjectiveResponse(false, "CurPassError");
		die();
	}

	PdoDataAccess::RUNQUERY("update BSC_persons set UserPass=? where PersonID=?",
		array($hasher->HashPassword($_POST["new_pass"]), $_SESSION["USER"]["PersonID"]));
								
	if( ExceptionHandler::GetExceptionCount() != 0 )
	{				
		echo "CurPassError";
		die();
	}	
	echo Response::createObjectiveResponse(true, "");
	die();
}

function DepositeFlow(){
	
	$CostID = $_GET["CostID"];
	$CurYear = substr(DateModules::shNow(),0,4);

	$temp = PdoDataAccess::runquery_fetchMode("
		select d.DocDate,
			d.description,
			di.DebtorAmount,
			di.CreditorAmount,
			di.details
		from ACC_DocItems di join ACC_docs d using(DocID)
		left join ACC_tafsilis t1 on(t1.TafsiliType=1 AND di.TafsiliID=t1.TafsiliID)
		left join ACC_tafsilis t2 on(t2.TafsiliType=1 AND di.Tafsili2ID=t2.TafsiliID)
		where CycleID=:year AND CostID=:cid AND (t1.ObjectID=:pid or t2.ObjectID=:pid)
		order by DocDate
	", array(":year" => $CurYear, ":pid" => $_SESSION["USER"]["PersonID"], ":cid" => $CostID));
	//print_r(ExceptionHandler::PopAllExceptions());
	$count = $temp->rowCount();
	$temp = PdoDataAccess::fetchAll($temp, $_GET["start"], $_GET["limit"]);
	
	echo dataReader::getJsonData($temp, $count, $_GET["callback"]);
	die();	
}
?>
