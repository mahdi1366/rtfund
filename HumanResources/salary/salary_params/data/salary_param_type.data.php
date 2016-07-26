<?php
//---------------------------
// programmer:	Mahdipour
// create Date:	90.02
//---------------------------
require_once '../../../header.inc.php';
require_once '../class/salary_param_types.class.php';
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
  
	$temp = manage_salary_param_types::GetAll();
	$no = count($temp);
	
	$temp = array_slice($temp, $_GET["start"], $_GET["limit"]);
	
	echo dataReader::getJsonData ( $temp, $no, $_GET ["callback"] );
	die ();
}

function saveParam()
{    
    $obj = new manage_salary_param_types();
    PdoDataAccess::FillObjectByJsonData($obj, $_POST["record"]);
	$obj->person_type = 3 ; 	
	
    if($obj->param_type == ""){
 	   $return = $obj->AddParam();
    }
	else
		$return = $obj->EditParam($obj->param_type);
	
	if($return)
		echo Response::createResponse(true,$obj->param_type);
	else
		echo Response::createResponse(false ,'');
	die();
}

function deleteParam()
{
    $obj = new manage_salary_param_types();
    $obj->param_type = $_POST["param_type"];
    $obj->person_type = $_POST["person_type"];

    $return =  $obj->RemoveParam($obj->param_type , $obj->person_type )  ;
    
    if($return)
		echo Response::createResponse(true,$obj->param_type);
	else
		echo Response::createResponse(false ,'');
	die();
}
























?>