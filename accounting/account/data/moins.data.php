<?php
//-----------------------------
//	Programmer	: SH.Jafarkhani
//	Date		: 90.12
//-----------------------------

require_once '../../header.inc.php';
require_once '../class/moins.class.php';

$task = isset ( $_POST ["task"] ) ? $_POST ["task"] : (isset ( $_GET ["task"] ) ? $_GET ["task"] : "");

switch ( $task)
{
	case "selectMoin":
		  selectMoin();

	case "saveMoin":
		saveMoin();

	case "removeMoin":
		removeMoin();
}

//..........................

function selectMoin()
{
	$where = "1=1";
	$whereParam = array();
	
	if(isset($_REQUEST["kolID"]))
	{
		$where .= " AND acc_moins.kolID=:c";
		$whereParam[":c"] = $_REQUEST["kolID"];
	}
	
	if(!empty($_GET["query"]))
	{
		switch((isset($_GET["fields"]) ? $_GET["fields"] : ""))
		{
			case "MoinTitle":
			case "":
				$where .= " AND MoinTitle like :tl";
				$whereParam[":tl"] = "%" . $_GET["query"] . "%";
				break;
		}
	}
	$where .= dataReader::makeOrder();
	
	$temp = manage_moins::GetAll($where, $whereParam);
	$no = count($temp);
	$temp = array_slice($temp, $_GET["start"], $_GET["limit"]);
	echo dataReader::getJsonData ($temp, $no, $_GET["callback"]);
	die ();
}

function saveMoin()
{
	$obj = new manage_moins();
	PdoDataAccess::FillObjectByJsonData($obj, $_POST["record"]);
	
	if($_POST["oldID"] != $obj->moinID)
	{
		$obj2 = new manage_moins($obj->kolID, $obj->moinID);
		if($obj2->moinID != "")
		{
			echo Response::createObjectiveResponse(false, "duplicate");
			die();
		}
	}
	
	if(empty($_POST["oldID"]))
		$return = $obj->Add();
	else
		$return = $obj->Edit($_POST["oldID"]);

	if(!$return)
	{
		echo Response::createObjectiveResponse(false, "used");
		die();
	}
	echo Response::createObjectiveResponse(true, "");
	die();
}

function removeMoin()
{
	$result = manage_moins::Remove($_POST["kolID"], $_POST["moinID"]);
	echo $result ? "true" : "conflict";
	die();
}

?>