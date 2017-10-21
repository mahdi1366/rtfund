<?php
//---------------------------
// programmer:	Mahdipour
// create Date:	90.09
//---------------------------
require_once '../../../header.inc.php';
require_once '../class/tax_table_types.class.php';
require_once(inc_response);
require_once inc_dataReader;

$task = isset ( $_POST ["task"] ) ? $_POST ["task"] : (isset ( $_GET ["task"] ) ? $_GET ["task"] : "");

switch ( $task) {
		
	case "selectAll" :
		  selectAll();
		  
	case "saveTax":
		  saveTax();
		
	case "deleteTax":
		  deleteTax();
}

function selectAll()
{
			
    $where = 'where ( 1=1 )';
    $where .=  dataReader::makeOrder();    
	$temp = manage_tax_table_types::GetAll($where);
    $no = count($temp);
    
	$temp = array_slice($temp, $_GET["start"], $_GET["limit"]);

	echo dataReader::getJsonData ( $temp, $no, $_GET ["callback"] );
	die ();
}

function saveTax()
{    
    $obj = new manage_tax_table_types();
    
    PdoDataAccess::FillObjectByJsonData($obj, $_POST["record"]);
	
	
    if($obj->tax_table_type_id == ""){
 	   $return = $obj->AddTax();
    }
	else
		$return = $obj->EditTax($obj->tax_table_type_id);
	
	if($return)
		echo Response::createResponse(true,$obj->tax_table_type_id);
	else
		echo Response::createResponse(false ,'');
	die();
}

function deleteTax()
{
    $obj = new manage_tax_table_types();
    $obj->tax_table_type_id = $_POST["tax_table_type_id"];
    $obj->person_type = $_POST["person_type"];

    $return =  $obj->RemoveTax($obj->tax_table_type_id , $obj->person_type )  ;

    if($return)
		echo Response::createResponse(true,$obj->tax_table_type_id);
	else
		echo Response::createResponse(false , ExceptionHandler::ConvertExceptionsToJsObject());
	die();
}
























?>