<?php
//---------------------------
// programmer:	Jafarkhani
// create Date:	91.04
//---------------------------

require_once '../../header.inc.php';
require_once '../class/bases.class.php';

require_once(inc_response);
require_once inc_dataReader;
require_once inc_PDODataAccess;

$task = isset($_POST ["task"]) ? $_POST ["task"] : (isset($_GET ["task"]) ? $_GET ["task"] : "");

switch ($task) {

	case "selectBases":
		selectBases();

	case "SaveBase":
		SaveBase();

	case "removeBase":
		removeBase();
}

function selectBases() {
	$where = " (1=1) ";
	$whereParam = array();

	$field = isset($_GET ["fields"]) ? $_GET ["fields"] : "";

	if (isset($_GET ["query"]) && $_GET ["query"] != "") {
		switch ($field) {
			case "title" :
				$where .= " AND title LIKE :qry ";
				$whereParam[":qry"] = "%" . $_GET["query"] . "%";
				break;
			case "BaseValue":
				$where .= " AND BaseValue = :qry ";
				$whereParam[":qry"] = $_GET["query"];
				break;
			case "typeName":
				$where .= " AND i.Title like :qry ";
				$whereParam[":qry"] = "%" . $_GET["query"] . "%";
				break;
			case "fullName":
				$where .= " AND concat(pfname,' ',plname) like :qry ";
				$whereParam[":qry"] = "%" . $_GET["query"] . "%";
				break;
			case "ExecuteDate":
				$where .= " AND ExecuteDate = :qry ";
				$whereParam[":qry"] = DateModules::shamsi_to_miladi($_GET["query"]);
				break;
		}
	}

	$where .= dataReader::makeOrder();

	$temp = manage_bases::GetAll($where, $whereParam);
	$no = count($temp);

	$temp = array_slice($temp, $_GET["start"], $_GET["limit"]);

	echo dataReader::getJsonData($temp, $no, $_GET ["callback"]);
	die();
}

function SaveBase() {
	
	$obj = new manage_bases();
	PdoDataAccess::FillObjectByJsonData($obj, $_POST["record"]);

	// بررسی گروه های تشویقی که بیشتر از 5 برای یک نفر نباشد
	/*if(in_array($obj->BaseType, array(27,23,24,21,3,4,5)))
	{
		$query = "select sum(BaseValue) from bases 
			where	PersonID=? AND 
					BaseType in(27,23,24,21,3,4,5) AND 
					BaseStatus = 'NORMAL'
					";
		$st = PdoDataAccess::runquery($query, array($obj->PersonID));
		if(count($st) > 0 && (int)$st[0][0] + (int)$obj->BaseValue > 5)
		{
			echo Response::createObjectiveResponse(false, "OverMaxCGroup");
			die();
		}
	}*/
	//----------------------------
	
	if (empty($obj->RowID))
	{
		$obj->RegDate = PDONOW;
		$return = $obj->Add();
	}
	else
	{
		unset($obj->RegDate);
		$return = $obj->Edit();
	}
	
	if (!$return) {
		echo Response::createObjectiveResponse($return, ExceptionHandler::GetExceptionsToString());
		die();
	}
	echo Response::createObjectiveResponse(true, "");
	die();
}

function removeBase() {
	
	$return = manage_bases::Remove($_POST["RowID"]);
	Response::createObjectiveResponse($return, (!$return ? ExceptionHandler::popExceptionDescription() : ""));
	die();
}

?>