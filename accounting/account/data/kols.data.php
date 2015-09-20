<?php
//-----------------------------
//	Programmer	: SH.Jafarkhani
//	Date		: 90.12
//-----------------------------

require_once '../../header.inc.php';
require_once '../class/kols.class.php';

$task = isset ( $_POST ["task"] ) ? $_POST ["task"] : (isset ( $_GET ["task"] ) ? $_GET ["task"] : "");

switch ( $task)
{
	case "selectKol":
		  selectKol();

	case "saveKol":
		saveKol();

	case "removeKol":
		removeKol();
}

//..........................

function selectKol()
{
	$whereParam = array();
	$where = "1=1";
	
	if(!empty($_GET["query"]))
	{
		switch((isset($_GET["fields"]) ? $_GET["fields"] : ""))
		{
			case "KolTitle":
				$where .= " AND KolTitle like :tl";
				$whereParam[":tl"] = "%" . $_GET["query"] . "%";
				break;
			case "":
				$where .= " AND (KolTitle like :tl OR kolID = :t2)";
				$whereParam[":tl"] = "%" . $_GET["query"] . "%";
				$whereParam[":t2"] = $_GET["query"];
				break;
		}
	}
	$where .= dataReader::makeOrder();

	$temp = manage_kols::GetAll($where, $whereParam);
	$no = count($temp);
	$temp = array_slice($temp, $_GET["start"], $_GET["limit"]);
	echo dataReader::getJsonData ($temp, $no, $_GET["callback"]);
	die ();
}

function saveKol()
{
	$obj = new manage_kols();
	PdoDataAccess::FillObjectByJsonData($obj, $_POST["record"]);
	
	if($_POST["oldKolID"] != $obj->kolID)
	{
		$obj2 = new manage_kols($obj->kolID);
		if($obj2->kolID != "")
		{
			echo Response::createObjectiveResponse(false, "duplicate");
			die();
		}
	}
	
	if(empty($_POST["oldKolID"]))
		$return = $obj->Add();
	else
		$return = $obj->Edit($_POST["oldKolID"]);

	if(!$return)
	{
		echo Response::createObjectiveResponse(false, "used");
		die();
	}
	echo Response::createObjectiveResponse(true, "");
	die();
}

function removeKol()
{
	$result = manage_kols::Remove($_POST["kolID"]);
	echo $result ? "true" : "conflict";
	die();
}

?>