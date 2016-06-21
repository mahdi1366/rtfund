<?php
//---------------------------
// programmer:	jafarkhani
// create Date:	90.08
//---------------------------
require_once '../../../header.inc.php';
require_once '../class/management_extra_bylaw.class.php';
require_once(inc_response);
require_once inc_dataReader;
require_once inc_PDODataAccess;
require_once inc_manage_post;

$task = isset ( $_POST ["task"] ) ? $_POST ["task"] : (isset ( $_GET ["task"] ) ? $_GET ["task"] : "");

switch ( $task)
{
	case "selectAll":
	      selectAll();

	case "save":
		save();

	case "delete":
		delete();

	case "copy":
		copyBylaw();

	//------------------------------

	case "selectAllItems":
	      selectAllItems();

	case "saveItem":
		saveItem();

	case "deleteItem":
		deleteItem();
}

function selectAll()
{
	$where = "1=1";
	$where .=  dataReader::makeOrder(); 

	$temp = management_extra_bylaw::GetAll($where);

	echo dataReader::getJsonData ( $temp, count($temp), $_GET ["callback"] );
	die ();
}

function save()
{
	$obj = new management_extra_bylaw();
	PdoDataAccess::FillObjectByJsonData($obj, $_POST["record"]);

	if(empty($obj->bylaw_id))
		$return = $obj->ADD();
	else
		$return = $obj->Edit();

	if(!$return)
	{
		echo Response::createObjectiveResponse($return, ExceptionHandler::GetExceptionsToString());
		die();
	}
	echo Response::createObjectiveResponse(true, "");
	die();
}

function delete()
{
	if($_POST["deleteItemsFlag"] == "true")
	{
		PdoDataAccess::runquery("delete from managmnt_extra_bylaw_items where bylaw_id=?", array($_POST["bylaw_id"]));
	}
	else
	{
		$temp = PdoDataAccess::runquery("select * from managmnt_extra_bylaw_items where bylaw_id=?", array($_POST["bylaw_id"]));
		if(count($temp) > 0)
		{
			echo Response::createObjectiveResponse(false, "NOT-EMPTY");
			die();
		}
	}
	$ret = management_extra_bylaw::Remove($_POST["bylaw_id"]);
	echo Response::createObjectiveResponse($ret, implode(ExceptionHandler::PopAllExceptions()));
	die();
}

function copyBylaw()
{
	$obj = new management_extra_bylaw();
	PdoDataAccess::FillObjectByJsonData($obj, $_POST["record"]);

	$reference_bylaw_id = $obj->bylaw_id;
	$obj->bylaw_id = null;
	
	$return = $obj->ADD();

	if(!$return)
	{
		echo Response::createObjectiveResponse($return, ExceptionHandler::GetExceptionsToString());
		die();
	}

	$query = "insert into managmnt_extra_bylaw_items
		select " . $obj->bylaw_id . ",post_id,value from managmnt_extra_bylaw_items where bylaw_id=" . $reference_bylaw_id;

	PdoDataAccess::runquery($query);

	echo Response::createObjectiveResponse(true, "");
	die();

}
//-----------------------------------------------

function selectAllItems()
{
	$where = "bylaw_id= :bylawid";

	$whereParam = array();
	$whereParam[":bylawid"] = $_POST["bylaw_id"];
	//-----------------------
	
	$field = isset ( $_GET ["fields"] ) ? $_GET ["fields"] : "";
	if (isset ( $_GET ["query"] ) && $_GET ["query"] != "")
	{
		switch ( $field)
		{
			case "post_id" :
				$where .= " AND p.post_id = :postid " ;
				$whereParam[":postid"] = $_GET["query"];
				break;
			break;
			case "post_title" :
				$where .= " AND concat(p.post_no,'-',p.title) LIKE :title ";
				$whereParam[":title"] = "%" . $_GET["query"] . "%";
				break;
			case "value" :
				$where .= " AND value = :val";
				$whereParam[":val"] = $_GET["query"];
				break;
		}
	}

	$where .=  dataReader::makeOrder(); 

	$temp = management_extra_bylaw_items::GetAll($where, $whereParam);
	$no = count($temp);

	$temp = array_slice($temp, $_GET["start"], $_GET["limit"]);
	
	echo dataReader::getJsonData ($temp, $no, $_GET ["callback"] );
	die ();
}

function saveItem()
{   
	$obj = new management_extra_bylaw_items();
	PdoDataAccess::FillObjectByJsonData($obj, $_POST["record"]);

	$return = $obj->ReplaceItem();

	if(!$return)
	{
		echo Response::createObjectiveResponse($return, ExceptionHandler::GetExceptionsToString());
		die();
	}
	echo Response::createObjectiveResponse(true, "");
	die();
}

function deleteItem()
{
	$ret = management_extra_bylaw_items::Remove($_POST["bylaw_id"], $_POST["post_id"]);
	echo Response::createObjectiveResponse($ret, implode(ExceptionHandler::PopAllExceptions()));
	die();
}
?>