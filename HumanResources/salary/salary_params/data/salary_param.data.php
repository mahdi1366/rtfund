<?php
//---------------------------
// programmer:	Jafarkhani
// create Date:	89.03
//---------------------------
require_once '../../../header.inc.php';
require_once '../class/salary_params.class.php';
require_once(inc_response);
require_once inc_dataReader;

$task = isset ( $_POST ["task"] ) ? $_POST ["task"] : (isset ( $_GET ["task"] ) ? $_GET ["task"] : "");

switch ( $task) {
		
	case "selectAll" :
		  selectAll();
		  
	case "saveParam":
		saveParam();
		
	case "deleteParam":
		deleteParam();
}

function selectAll()
{
	
	
	$temp = manage_salary_params::GetAll($_GET["person_type"],$_GET["param_type"] , dataReader::makeOrder());
	$no = count($temp);
	

	$temp = array_slice($temp, $_GET["start"], $_GET["limit"]);	
	echo dataReader::getJsonData ( $temp, $no, $_GET ["callback"] );
	die ();
}

function saveParam()
{
    
    $obj = new manage_salary_params();
    
	PdoDataAccess::FillObjectByJsonData($obj, $_POST["record"]);
	$obj->param_type = 	$_POST["param_type"];
    $obj->person_type = 	3 ;

    if($obj->param_id == ""){
 	   $return = $obj->AddParam();
    }
	else
		$return = $obj->EditParam($obj->param_id);
	
	if($return)
		echo Response::createResponse(true,$obj->param_type);
	else
		echo Response::createResponse(false ,ExceptionHandler::GetExceptionsToString());
	die();
	
}

function deleteParam()
{
    $obj = new manage_salary_params();
    $obj->param_id = $_POST["param_id"];
    echo $obj->RemoveParam($obj->param_id) ? "true" : ExceptionHandler::GetExceptionsToString("\n") ;
	die();
}
























?>