<?php
require_once '../../header.inc.php';
require_once '../class/study_fields.class.php';
require_once '../class/study_branch.class.php';
require_once(inc_response);
require_once inc_dataReader;
require_once inc_PDODataAccess;

$task = isset ( $_POST ["task"] ) ? $_POST ["task"] : (isset ( $_GET ["task"] ) ? $_GET ["task"] : "");

switch ( $task) {
	

	case "selectfields":
		  selectfields();

	case "SaveField":
          SaveField();

	case "removeField":
		  removeField();
          
    case "selectbanchs":
          selectbanchs();

    case "SaveBranch":
          SaveBranch();

    case "removeBranch":
          removeBranch();
	      
}
function selectfields()
{
	$where = " (1=1) ";
	$whereParam = array();

	$field = isset ( $_GET ["fields"] ) ? $_GET ["fields"] : "";

	if (isset ( $_GET ["query"] ) && $_GET ["query"] != "") {
			switch ( $field) {
				case "ptitle" :
					$where .= " AND ptitle LIKE :qry " ;
					$whereParam[":qry"] = "%" . $_GET["query"] . "%";

				break;
				case "sfid" :
					$where .= " AND sfid = :qry " ;
					$whereParam[":qry"] = $_GET["query"] ;

				break;
				
			}
		}

	$where .=  dataReader::makeOrder(); 

	$temp = manage_study_fields::GetAll($where,$whereParam);
	$no = count($temp);

	$temp = array_slice($temp, $_GET["start"], $_GET["limit"]);
	
	echo dataReader::getJsonData ($temp, $no, $_GET ["callback"] );
	die ();
}

function selectbanchs()
{
	$where = " sfid =". $_GET['sfid'];
	$where .=  dataReader::makeOrder();
    
    $temp = manage_study_branch::GetAll($where);
	$no = count($temp);

	$temp = array_slice($temp, $_GET["start"], $_GET["limit"]);

	echo dataReader::getJsonData ($temp, $no, $_GET ["callback"] );
	die ();
}

function SaveField()
{
	$obj = new manage_study_fields();
    PdoDataAccess::FillObjectByJsonData($obj, $_POST["record"]);
	
	if(empty($obj->sfid))
		$return = $obj->Add();
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

function SaveBranch()
{ 
	$obj = new manage_study_branch();
    PdoDataAccess::FillObjectByJsonData($obj, $_POST["record"]);

	if(empty($obj->sbid))
		$return = $obj->Add();
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

function removeField()
{
	$return = manage_study_fields::Remove($_POST["sfid"]);
	Response::createObjectiveResponse($return, (!$return ? ExceptionHandler::popExceptionDescription() : ""));
	die();
}

function removeBranch()
{
    $return = manage_study_branch::Remove($_POST["sfid"],$_POST["sbid"]);
	Response::createObjectiveResponse($return, (!$return ? ExceptionHandler::popExceptionDescription() : ""));
	die();
}


	