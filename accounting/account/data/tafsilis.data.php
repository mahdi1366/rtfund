<?php
//-----------------------------
//	Programmer	: SH.Jafarkhani
//	Date		: 90.12
//-----------------------------

require_once '../../header.inc.php';
require_once '../class/tafsilis.class.php';

$task = isset ( $_POST ["task"] ) ? $_POST ["task"] : (isset ( $_GET ["task"] ) ? $_GET ["task"] : "");

switch ( $task)
{
	case "selectTafsili":
		  selectTafsili();

	case "saveTafsili":
		saveTafsili();

	case "removeTafsili":
		removeTafsili();
		
	case "getTafsiliRemainder":
		getTafsiliRemainder();
		
	case "selectShareHolders":
		selectShareHolders();
}

//..........................

function selectTafsili()
{
	$whereParam = array();
	if(!isset($_REQUEST["all"]))
		$where = "IsActive=1";
	else
		$where = "1=1";
	
	if(!empty($_GET["query"]))
	{
		switch((isset($_GET["fields"]) ? $_GET["fields"] : ""))
		{
			case "tafsiliTitle":
				$where .= " AND tafsiliTitle like :tl";
				$whereParam[":tl"] = "%" . $_GET["query"] . "%";
				break;
			case "description":
				$where .= " AND description  like :t";
				$whereParam[":t"] = "%" . $_GET["query"] . "%";
				break;
			case "tafsiliID":
				$where .= " AND tafsiliID=:t";
				$whereParam[":t"] = $_GET["query"];
				break;
			case "":
				$where .= " AND (tafsiliTitle like :f  OR tafsiliID = :f2)";
				$whereParam[":f"] = "%" . $_GET["query"] . "%";
				$whereParam[":f2"] = $_GET["query"];
		}
	}
	
	if(!empty($_POST["from_tafsiliID"]))
	{
		$where .= " AND tafsiliID >= :ft";
		$whereParam[":ft"] = $_POST["from_tafsiliID"];
	}
	if(!empty($_POST["to_tafsiliID"]))
	{
		$where .= " AND tafsiliID <= :tt";
		$whereParam[":tt"] = $_POST["to_tafsiliID"];
	}
	
	$where .= dataReader::makeOrder();

	$temp = manage_tafsilis::GetAll($where,$whereParam);
	$no = count($temp);
	$temp = array_slice($temp, $_GET["start"], $_GET["limit"]);
	echo dataReader::getJsonData ($temp, $no, $_GET["callback"]);
	die ();
}

function saveTafsili()
{
	$obj = new manage_tafsilis();
	PdoDataAccess::FillObjectByJsonData($obj, $_POST["record"]);
	
	if($_POST["oldID"] != $obj->tafsiliID)
	{
		$obj2 = new manage_tafsilis($obj->tafsiliID);
		if($obj2->tafsiliID != "")
		{
			echo Response::createObjectiveResponse(false, "duplicate");
			die();
		}
	}
	
	if(empty($_POST["oldID"]))
	{
		$query = "select * from acc_tafsilis where tafsiliTitle = ?";
		$dt = PdoDataAccess::runquery($query, array($obj->tafsiliTitle));
		if(count($dt) != 0)
		{
			echo Response::createObjectiveResponse(false, "duplicate");
			die();
		}
		$return = $obj->Add();
	}
	else
		$return = $obj->Edit($_POST["oldID"]);

	if(!$return)
	{
		print_r(ExceptionHandler::PopAllExceptions());
		echo Response::createObjectiveResponse(false, "used");
		die();
	}
	echo Response::createObjectiveResponse(true, "");
	die();
}

function removeTafsili()
{
	$result = manage_tafsilis::Remove($_POST["tafsiliID"]);
	echo $result ? "true" : "conflict";
	die();
}

function getTafsiliRemainder()
{
	$tafsiliID = $_POST["tafsiliID"];

	$query = "select * from acc_doc_items i join acc_docs a using(docID)
		where a.docType='SHAREBACK' and i.tafsiliID=?";
	$dt = PdoDataAccess::runquery($query, array($tafsiliID));
	if(count($dt) != 0)
	{
		echo "DONE_BEFOR";
		die();
	}
	
	$query = "
		select sum(bsAmount)-sum(bdAmount)
		from acc_doc_items join acc_docs using(docID)
		where (tafsiliID=:t or tafsili2ID=:t) AND docStatus <> 'DELETED' AND kolID=60 AND moinID=1";
	
	$dt = PdoDataAccess::runquery($query,array(":t" => $tafsiliID));
	if(count($dt) == 0)
		echo 0;
	else 
		echo $dt[0][0];
	die();
}

function selectShareHolders()
{
	$whereParam = array();
	if(!isset($_REQUEST["all"]))
		$where = "TafsiliType='shareholder' AND IsActive=1";
	else
		$where = "1=1";
	
	if(!empty($_GET["query"]))
	{
		switch((isset($_GET["fields"]) ? $_GET["fields"] : ""))
		{
			case "tafsiliTitle":
				$where .= " AND tafsiliTitle like :tl";
				$whereParam[":tl"] = "%" . $_GET["query"] . "%";
				break;
			case "description":
				$where .= " AND description  like :t";
				$whereParam[":t"] = "%" . $_GET["query"] . "%";
				break;
			case "tafsiliID":
				$where .= " AND tafsiliID=:t";
				$whereParam[":t"] = $_GET["query"];
				break;
			case "":
				$where .= " AND (tafsiliTitle like :f  OR tafsiliID = :f2)";
				$whereParam[":f"] = "%" . $_GET["query"] . "%";
				$whereParam[":f2"] = $_GET["query"];
		}
	}
	
	if(!empty($_POST["from_tafsiliID"]))
	{
		$where .= " AND tafsiliID >= :ft";
		$whereParam[":ft"] = $_POST["from_tafsiliID"];
	}
	if(!empty($_POST["to_tafsiliID"]))
	{
		$where .= " AND tafsiliID <= :tt";
		$whereParam[":tt"] = $_POST["to_tafsiliID"];
	}
	
	$where .= dataReader::makeOrder();

	$temp = manage_tafsilis::GetAll($where,$whereParam);
	$no = count($temp);
	$temp = array_slice($temp, $_GET["start"], $_GET["limit"]);
	echo dataReader::getJsonData ($temp, $no, $_GET["callback"]);
	die ();
}

?>